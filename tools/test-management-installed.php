<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';require 'C:/xampp/htdocs/boticav1/api/yape-collection.php';
$db=appPdoConnection();$id=yapeCollectionType($db);$stmt=$db->prepare('SELECT Tipo FROM Cb_TipoCajaBanco WHERE IdTipoCajaBanco=?');$stmt->execute([$id]);if((int)$stmt->fetchColumn()!==2)throw new RuntimeException('Yape is not neutral');
function hit($body,$cookie=''){ $h=curl_init('http://localhost/api/management-report.php');curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30,CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($body),CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_COOKIE=>$cookie]);$raw=curl_exec($h);$code=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);return [$code,$raw]; }
$f=['desde'=>'2026-10-01','hasta'=>'2026-10-16','proyeccion'=>30];
[$code]=hit($f);if($code!==401)throw new RuntimeException('Anonymous access not blocked');
session_id('managerCheck'.bin2hex(random_bytes(10)));session_start();$_SESSION=['user'=>'admin','idPerfil'=>1,'expire'=>time()+600];$cookie='PHPSESSID='.session_id();session_write_close();
[$code,$raw]=hit($f,$cookie);$report=json_decode($raw,true);if($code!==200||!isset($report['diario']))throw new RuntimeException('Live JSON failed '.$code);
[$code,$raw]=hit($f+['excel'=>true],$cookie);if($code!==200||substr($raw,0,2)!=='PK')throw new RuntimeException('Excel download corrupted '.$code);file_put_contents(__DIR__.'/../output/management-report-http.xlsx',$raw);
[$code]=hit($f+['clasificacion'=>['99'=>'invalid']],$cookie);if($code!==422)throw new RuntimeException('Bad classifications accepted');
file_put_contents(__DIR__.'/../tmp/management-browser-session.json',json_encode(['name'=>'PHPSESSID','value'=>substr($cookie,10),'domain'=>'localhost','path'=>'/']));
echo 'PASS anonymous protection, authenticated report, binary Excel download, validation and neutral Yape. No sale or payment created.'.PHP_EOL;