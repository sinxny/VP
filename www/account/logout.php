<?php
session_start();
session_destroy();
?>
<script>
sessionStorage.clear();
location.href="../index.php";
</script>
