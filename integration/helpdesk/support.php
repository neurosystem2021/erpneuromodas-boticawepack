<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&$_SESSION['expire']<time())){header('Location: /');exit;}
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Centro de soporte FACTURA.VIP</title></head><body style="margin:0"><iframe title="Centro de soporte" src="/support-ui/index.html?v=<?php echo (int)filemtime(__DIR__.'/../support-ui/index.html'); ?>#/soporte" style="display:block;width:100%;height:100vh;border:0"></iframe></body></html>
