<?php
  $jno = $_GET["jno"];
  $doc_no = $_GET["doc_no"];
?>
<style>
  #the-canvas {
    border: 1px solid black;
    direction: ltr;
    height: 200px !important;
  }
  .pdfLink {
    color: inherit !important;
    cursor: pointer;
  }
</style>
<script src="/pdfjs-3.0.279-dist/build/pdf.js"></script>

<!-- <h1>PDF.js Previous/Next example</h1> -->

<div>
  <span>Page: <span id="page_num"></span> / <span id="page_count"></span></span>
</div>

<canvas id="the-canvas"></canvas>
<form action="/pdfViewer.php" target="_blank" method="post" id="viewerForm" name="viewerForm">
  <div class="input-group justify-content-center" style="text-align:center !important;">
    <!-- <button id="prev" class="btn btn-sm btn btn-outline-dark">Previous</button>
    <button id="next" class="btn btn-sm btn btn-outline-dark">Next</button> -->
    <ul class="pagination pagination-sm mr-2">
      <li class='page-item' id="first"><a class="page-link pdfLink"><i class='fas fa-angle-double-left'></i></a></li>
      <li class='page-item' id="prev"><a class="page-link pdfLink"><i class='fas fa-angle-left'></i></a></li>
      <li class='page-item' id="next"><a class="page-link pdfLink"><i class='fas fa-angle-right'></i></a></li>
      <li class='page-item' id="last"><a class="page-link pdfLink"><i class='fas fa-angle-double-right'></i></a></li>
    </ul>
    <div>
      <button type="button" id="btnExpand" class="btn btn-sm btn btn-outline-dark" title="크게보기"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>
  </div>
  <input type="hidden" id="jno" name="jno" value="<?php echo $jno ?>">
  <input type="hidden" id="doc_no" name="doc_no" value="<?php echo $doc_no ?>">
</html>
<script>
// If absolute URL from the remote server is provided, configure the CORS
// header on that server.
var url = '/api/vdcs/?api_key=d6c814548eeb6e41722806a0b057da30&api_pass=BQRUQAMXBVY=&model=DOC_LE_DOWNLOAD&jno=' + '<?php echo $jno ?>' + '&doc_no=' + '<?php echo $doc_no ?>' + '&webview=Y';

// Loaded via <script> tag, create shortcut to access PDF.js exports.
var pdfjsLib = window['pdfjs-dist/build/pdf'];

// The workerSrc property shall be specified.
pdfjsLib.GlobalWorkerOptions.workerSrc = '/pdfjs-3.0.279-dist/build/pdf.worker.js';

var pdfDoc = null,
    pageNum = 1,
    pageRendering = false,
    pageNumPending = null,
    scale = 0.8,
    canvas = document.getElementById('the-canvas'),
    ctx = canvas.getContext('2d');

/**
 * Get page info from document, resize canvas accordingly, and render page.
 * @param num Page number.
 */
function renderPage(num) {
  pageRendering = true;
  // Using promise to fetch the page
  pdfDoc.getPage(num).then(function(page) {
    var viewport = page.getViewport({scale: scale});
    canvas.height = viewport.height;
    canvas.width = viewport.width;

    // Render PDF page into canvas context
    var renderContext = {
      canvasContext: ctx,
      viewport: viewport
    };
    var renderTask = page.render(renderContext);

    // Wait for rendering to finish
    renderTask.promise.then(function() {
      pageRendering = false;
      if (pageNumPending !== null) {
        // New page rendering is pending
        renderPage(pageNumPending);
        pageNumPending = null;
      }
    });

    if(num == 1) {
      $("#first").addClass("disabled");
      $("#prev").addClass("disabled");
    } else {
      $("#first").removeClass("disabled");
      $("#prev").removeClass("disabled");
    }
    if(num == pdfDoc.numPages) {
      $("#next").addClass("disabled");
      $("#last").addClass("disabled");
    } else {
      $("#next").removeClass("disabled");
      $("#last").removeClass("disabled");
    }
  });

  // Update page counters
  document.getElementById('page_num').textContent = num;
}

/**
 * If another page rendering in progress, waits until the rendering is
 * finised. Otherwise, executes rendering immediately.
 */
function queueRenderPage(num) {
  if (pageRendering) {
    pageNumPending = num;
  } else {
    renderPage(num);
  }
}

// 첫페이지
document.getElementById('first').addEventListener('click', function(e) {
  e.preventDefault();
  pageNum = 1;
  queueRenderPage(pageNum);
});
/**
 * Displays previous page.
 */
function onPrevPage() {
  if (pageNum <= 1) {
    return;
  }
  pageNum--;
  queueRenderPage(pageNum);
}
document.getElementById('prev').addEventListener('click', function(e) {
  e.preventDefault();
  onPrevPage();
});

/**
 * Displays next page.
 */
function onNextPage() {
  if (pageNum >= pdfDoc.numPages) {
    return;
  }
  pageNum++;
  queueRenderPage(pageNum);
}
document.getElementById('next').addEventListener('click', function(e) {
  e.preventDefault();
  onNextPage();
});

// 마지막 페이지
document.getElementById('last').addEventListener('click', function(e) {
  e.preventDefault();
  pageNum = pdfDoc.numPages;
  queueRenderPage(pageNum);
});

/**
 * Asynchronously downloads PDF.
 */
pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
  pdfDoc = pdfDoc_;
  document.getElementById('page_count').textContent = pdfDoc.numPages;

  // Initial/first page rendering
  renderPage(pageNum);
});

$("#btnExpand").on('click', function() {
  $("#viewerForm").submit();
});
</script>