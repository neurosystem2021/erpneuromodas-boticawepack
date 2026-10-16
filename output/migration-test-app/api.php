<?php
// Standalone test application: reuse authentication libraries, never the production router.
ini_set('display_errors','0');
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
require 'C:/xampp/htdocs/boticav1/config/database.php';
require 'C:/xampp/htdocs/boticav1/api/ali-pos-security.php';
require __DIR__.'/migration/runtime.php';
$app=new Slim\App(['settings'=>['displayErrorDetails'=>false]]);
$auth=function($q,$r,$next){
 if(session_status()!==PHP_SESSION_ACTIVE)session_start();
 if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time()))return $r->withJson(['error'=>'Inicia sesion en el ERP y vuelve al entorno de pruebas.'],401);
 if(strcasecmp(trim((string)$_SESSION['user']),'superadmin')!==0)return $r->withJson(['error'=>'Acceso exclusivo para superadmin.'],403);
 $caps=aliPosCapabilities(appPdoConnection(),$_SESSION['user']);if(empty($caps['admin']))return $r->withJson(['error'=>'Solo administradores.'],403);
 $_SESSION['migration_csrf']=$_SESSION['migration_csrf']??bin2hex(random_bytes(24));$csrf=$_SESSION['migration_csrf'];$user=$_SESSION['user'];
 if($q->getMethod()!=='GET'&&!hash_equals($csrf,$q->getHeaderLine('X-Ali-CSRF')))return $r->withJson(['error'=>'Actualiza el panel de pruebas.'],403);
 session_write_close();return $next($q->withAttribute('ali_pos',$caps)->withAttribute('ali_user',$user)->withAttribute('migration_csrf',$csrf),$r);
};
$app->get('/ali/config',function($q,$r){return $r->withJson(['user'=>$q->getAttribute('ali_user'),'csrf'=>$q->getAttribute('migration_csrf'),'pos'=>$q->getAttribute('ali_pos')]);})->add($auth);
neuroRegisterMigration($app,$auth);
$app->run();
