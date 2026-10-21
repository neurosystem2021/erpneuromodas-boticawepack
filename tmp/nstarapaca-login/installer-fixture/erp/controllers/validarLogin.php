<?php
ini_set('display_errors','0');
if(session_status()!==PHP_SESSION_ACTIVE)session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require_once __DIR__.'/nst-login-auth.php';
list($status,$body)=nstLoginResponse($_SERVER['REQUEST_METHOD']??'GET',$_POST,function(){
    require_once dirname(__DIR__).'/config/database.php';
    return appPdoConnection();
});
http_response_code($status);
if($status===405)header('Allow: POST');
echo json_encode($body,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
