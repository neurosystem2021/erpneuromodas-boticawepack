<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){header('Location: /');exit;}
if(strcasecmp(trim((string)$_SESSION['user']),'superadmin')!==0){http_response_code(403);exit('Acceso exclusivo para superadmin.');}
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../api/ali-pos-security.php';
if(empty(aliPosCapabilities(appPdoConnection(),$_SESSION['user'])['admin'])){http_response_code(403);exit('Solo administradores.');}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Migrador histórico</title><link rel="stylesheet" href="/resources/font-awesome/css/font-awesome.css"><link rel="stylesheet" href="/resources/css/bootstrap.css"><link rel="stylesheet" href="/resources/css/main.css"><script src="/resources/js/jquery-3.2.1.min.js"></script><script src="/resources/js/main.js"></script><script src="/resources/js/bootstrap.js"></script></head><body><?php include __DIR__.'/header.php'; ?><iframe title="Migrador histórico" src="../migration-ui/index.html" style="display:block;width:100%;height:calc(100vh - 90px);border:0"></iframe></body></html>
