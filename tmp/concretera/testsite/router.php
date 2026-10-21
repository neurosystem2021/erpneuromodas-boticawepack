<?php
// Isolated loopback test server. Never install this file in the ERP.
if(!in_array($_SERVER['REMOTE_ADDR'],['127.0.0.1','::1'],true)){http_response_code(403);exit;}
ini_set('session.save_path','C:/xampp/htdocs/boticawebpack/tmp/concretera/test-sessions');
if(parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)==='/__session'){
 if(!hash_equals('9c6ffadbd37716bb854eab6c93912a72cef263d65920b7a2',(string)($_SERVER['HTTP_X_TEST_NONCE']??''))){http_response_code(403);exit;}
 $p=json_decode(file_get_contents('php://input'),true);$u=$p['user']??'';
 if(!in_array($u,['superadmin','ventas1','comercial1','lectura2',''],true)){http_response_code(403);exit;}
 session_start();session_regenerate_id(true);$_SESSION=$u===''?[]:['user'=>$u,'idPerfil'=>$u==='superadmin'?1:2,'idAlmacen'=>1];
 header('Content-Type: application/json');echo json_encode(['testUser'=>$u]);exit;
}
return false;
