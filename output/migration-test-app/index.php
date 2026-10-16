<?php
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){http_response_code(401);echo '<p>Inicia sesión en <a href="http://localhost/" target="_blank" rel="noopener">el ERP</a> y vuelve a este entorno de pruebas.</p>';exit;}
if(strcasecmp(trim((string)$_SESSION['user']),'superadmin')!==0){http_response_code(403);exit('Acceso exclusivo para superadmin.');}
require 'C:/xampp/htdocs/boticav1/config/database.php';require 'C:/xampp/htdocs/boticav1/api/ali-pos-security.php';
if(empty(aliPosCapabilities(appPdoConnection(),$_SESSION['user'])['admin'])){http_response_code(403);exit('Solo administradores.');}
session_write_close();
?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Migrador · entorno aislado de pruebas</title><link rel="stylesheet" href="app.css?v=<?php echo filemtime(__DIR__.'/app.css'); ?>"></head><body><div class="test-banner">ENTORNO DE PRUEBAS · archivo histórico aislado · producción sin cambios</div><div id="app"></div><script src="vue.min.js"></script><script src="app.js?v=<?php echo filemtime(__DIR__.'/app.js'); ?>"></script></body></html>
