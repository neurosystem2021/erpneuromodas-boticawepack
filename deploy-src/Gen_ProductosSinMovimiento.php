<?php
include_once("../clases/helpers/Modal.php");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Productos sin movimiento</title>
</head>
<?php include_once 'linker.php'; ?>
<body>
<?php include("header.php"); ?>
<script>
  function resizeIframe(obj) {
    obj.style.height = ($(window).height() - 70) + 'px';
  }
</script>
<iframe id="iframeProductosSinMovimiento" src="/nuevo/index.html#/productos-sin-movimiento" width="100%" frameborder="0" onload="resizeIframe(this)"></iframe>
</body>
</html>
