<?php
ob_start();
if(PHP_SAPI!=='cli')exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$user=$db->query("SELECT Usuario FROM Seg_Usuario WHERE IdUsuarioPerfil=1 AND Anulado=0 ORDER BY Usuario LIMIT 1")->fetchColumn();
session_id(bin2hex(random_bytes(20)));session_start();$_SESSION=['user'=>$user,'idPerfil'=>1,'idAlmacen'=>1,'HostUrl'=>'http://localhost/','hostUrl'=>'http://localhost/','IdDocVentaPuntoVenta'=>1,'expire'=>time()+300,'permisos'=>[],'login_csrf'=>bin2hex(random_bytes(24))];$cookie=session_name().'='.session_id();session_write_close();
$paths=['/api/crm/index.php?route=messaging/status','/api/crm/index.php?route=messaging/connection/status'];
foreach($paths as$p){$ch=curl_init('http://localhost'.$p);curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>$cookie,CURLOPT_TIMEOUT=>40]);$body=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);$json=json_decode($body,true);if(strpos($p,'/views/')===0 && strpos($p,'CRM.php')===false){$out=__DIR__.'/../.tmp-imceden-rendered';if(!is_dir($out))mkdir($out);file_put_contents($out.'/'.basename($p).'.html',$body);}echo $status.' '.$p;if(is_array($json)&&isset($json['error']))echo ' ERROR '.json_encode($json,JSON_UNESCAPED_UNICODE);elseif($status>=400||strpos($body,'Fatal error')!==false||strpos($body,'Warning:')!==false)echo ' INVALID '.substr(strip_tags($body),0,250);else { echo ' OK'; if(strpos($p,'connection/status')!==false && is_array($json)) echo ' '.json_encode($json,JSON_UNESCAPED_UNICODE); }echo "\n";}
session_id(substr($cookie,strpos($cookie,'=')+1));session_start();$_SESSION=[];session_destroy();
