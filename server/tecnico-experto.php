<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){header('Location: /');exit;}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Técnico experto</title><link rel="stylesheet" href="/resources/font-awesome/css/font-awesome.css"><link rel="stylesheet" href="/resources/css/bootstrap.css"><link rel="stylesheet" href="/resources/css/main.css"><script src="/resources/js/jquery-3.2.1.min.js"></script><script src="/resources/js/main.js"></script><script src="/resources/js/bootstrap.js"></script></head><body><?php include 'header.php'; ?><iframe title="Técnico experto" src="/nuevo/index.html?v=<?php echo (int)filemtime(__DIR__.'/../nuevo/index.html'); ?>#/tecnico-experto" style="display:block;width:100%;height:calc(100vh - 90px);border:0"></iframe></body></html>
