<?php include __DIR__ . "/../www/_inc.php";
//echo $_SERVER["DOCUMENT_ROOT"];
//$isLogin = $user->uno;
?><!DOCTYPE HTML>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="pragma" content="no-cache" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="google" content="notranslate" />
<title>VDCS Latest</title>
<link rel="shortcut icon" href="https://gw.htenc.com/HI_64x64.png" />
<link data-n-head="ssr" data-hid="canonical" rel="canonical" href="https://bootstrap-vue.org/docs/components/pagination">
<script type="text/javascript" src="/jquery/jquery-3.6.0.min.js"></script>
<!--
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> //<script src="https://cdn.jsdelivr.net/npm/vue@2.7.13/dist/vue.js"></script>
<script>
$(document).ready(function() {
    const { createApp } = Vue;

  createApp({
    data() {
      return {
        message: 'Hello Vue!'
      };
    }
  }).mount('#app');
});
</script>
-->
<script type="importmap">
  {
    "imports": {
      "vue": "https://unpkg.com/vue@3/dist/vue.esm-browser.js"
      //, "jw-vue-pagination" : "./js/bootstrap-vue/src/components/pagination/pagination.js"
    }
  }
</script>
<script type="module">
import { createApp } from 'vue';
//import { JwPagination } from 'jw-vue-pagination';
//import JwPagination from './js/bootstrap-vue/src/components/pagination/pagination.js';
//Vue.component('jw-pagination', JwPagination);
const app = createApp({ //const app = createApp({});
  data() {
    return {
      message: 'Hello Vue!'
    };
  }
});

$(document).ready(function() {
    app.mount('#app');
    $("#app").css("display","block");
});
  
</script>
<script>

</script>
</head>
<body>
<div id="app" style="display:none">
    {{ message }}
</div>

</body>
</html>



