<?php
ob_start();
require 'C:/xampp/htdocs/boticav1/config/database.php';
function requestMig($path,$cookie='',$post=null){$base=getenv('NEURO_MIGRATION_TEST_BASE')?:'http://localhost:8099/';if(getenv('NEURO_MIGRATION_TEST_BASE')){$path=preg_replace('~^api\.php~','api/migracion.php',$path);if($path==='index.php')$path='views/migrador-historico.php';}$h=curl_init($base.$path);curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>$cookie,CURLOPT_TIMEOUT=>45]);if($post!==null)curl_setopt_array($h,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode($post)]);$raw=curl_exec($h);$status=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);return [$status,json_decode($raw,true)];}
function requireMig($v){if(!$v)throw new RuntimeException('Installed guard or read check failed');}
function sessionMig($user){session_id('migrationCheck'.bin2hex(random_bytes(12)));session_start();$_SESSION=['user'=>$user,'expire'=>time()+180];$id=session_id();session_write_close();return $id;}
$sessions=[];
try{
 [$code]=requestMig('index.php');requireMig($code===401||$code===302);[$code]=requestMig('api.php/ali/migracion?action=history');requireMig($code===401);
 $other=appPdoConnection()->query("SELECT Usuario FROM Seg_Usuario WHERE IdUsuarioPerfil=1 AND LOWER(Usuario)<>'superadmin' AND COALESCE(Anulado,0)=0 LIMIT 1")->fetchColumn();requireMig((bool)$other);$sessions[]=sessionMig($other);$cookie='PHPSESSID='.end($sessions);
 [$code]=requestMig('index.php',$cookie);requireMig($code===403);[$code]=requestMig('api.php/ali/migracion?action=history',$cookie);requireMig($code===403);[$code]=requestMig('api.php/ali/migracion',$cookie,['action'=>'simulate']);requireMig($code===403);
 $sessions[]=sessionMig('superadmin');$cookie='PHPSESSID='.end($sessions);[$code,$config]=requestMig('api.php/ali/config',$cookie);requireMig($code===200&&$config['user']==='superadmin');
 [$code,$info]=requestMig('api.php/ali/migracion?action=info',$cookie);requireMig($code===200&&$info['mode']==='isolated_copy'&&strpos($info['target'],'neuro_mig_test_')===0);
 [$code,$history]=requestMig('api.php/ali/migracion?action=history',$cookie);requireMig($code===200&&count($history['runs'])===1);$id=$history['runs'][0]['id'];
 [$code,$status]=requestMig('api.php/ali/migracion?action=status&id='.$id,$cookie);requireMig($code===200&&$status['status']==='simulated'&&$status['stored_rows']===0&&count($status['issues'])===20);
 [$code,$report]=requestMig('api.php/ali/migracion?action=report&id='.$id,$cookie);requireMig($code===200&&$report['operational_tables_written']===false);
 [$code]=requestMig('api.php/ali/migracion?action=batch&id='.$id,$cookie);requireMig($code===405);
 [$code]=requestMig('api.php/ali/migracion',$cookie,['action'=>'approve','id'=>$id,'plan_hash'=>$status['plan_hash']]);requireMig($code===403);
 echo "PASS installed application: anonymous denied, another administrator denied (screen/API/write), superadmin reads copy/simulation/report, GET writes and missing CSRF denied. No migration approved or records loaded.\n";
}finally{foreach($sessions as $id){session_id($id);session_start();$_SESSION=[];session_destroy();}ob_end_flush();}
