<?php
include_once("../clases/helpers/Modal.php");
$appMode = isset($_GET['app']) && $_GET['app'] === '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#009f87">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="Inventario">
  <meta name="mobile-web-app-capable" content="yes">
  <link rel="manifest" href="/nuevo/static/inventory.webmanifest">
  <link rel="apple-touch-icon" href="/nuevo/static/logo.png">
  <title>INVENTARIO FÍSICO · NEUROSOFT</title>
  <?php include_once 'linker.php'; ?>
  <style>
    body.inventory-shell { margin: 0; background: #f4f9fc; overflow-x: hidden; }
    .inventory-shell iframe { display: block; border: 0; width: 100%; min-height: calc(100vh - 90px); }
    .inventory-shell.inventory-standalone .inventory-shell-navigation { display: none; }
    .inventory-shell.inventory-standalone iframe { height: 100dvh; min-height: 100vh; }
    .inventory-home { display: none; padding: 10px 16px; color: #087c81; background: white; font-size: 12px; font-weight: bold; }
    .inventory-standalone .inventory-home { display: block; }
    @media (max-width: 700px) { .inventory-shell-navigation { display: none; } .inventory-home { display: block; } .inventory-shell iframe { height: calc(100dvh - 36px); min-height: calc(100vh - 36px); } }
  </style>
</head>
<body class="inventory-shell<?php echo $appMode ? ' inventory-standalone' : ''; ?>">
<div class="inventory-shell-navigation"><?php include 'header.php'; ?></div>
<a class="inventory-home" href="/views/Lo_InventarioForm.php">← VOLVER AL SISTEMA</a>
<iframe id="iframeMovimiento" title="INVENTARIO FÍSICO" src="/nuevo/index.html#/reporte/confrontacion" allow="camera 'self'" onload="resizeInventory(this)"></iframe>
<script>
function resizeInventory(frame) { frame.style.height = Math.max(400, window.innerHeight - frame.getBoundingClientRect().top) + 'px'; }
window.addEventListener('resize', function () { resizeInventory(document.getElementById('iframeMovimiento')); });
</script>
</body>
</html>
