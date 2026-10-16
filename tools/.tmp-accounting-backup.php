<?php
if(PHP_SAPI!=='cli')exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';
$dest='C:/xampp/accounting-private/local-install-20261016';if(!is_dir($dest))mkdir($dest,0700,true);
$config=appDatabaseConfig();$databases=['erp'=>$config['dbname']];if(false && is_file('C:/xampp/support-private/runtime.php')){$support=require 'C:/xampp/support-private/runtime.php';$databases['soporte']=$support['database'];}
$escape=function($s){return '"'.str_replace(["\\","\"","\n","\r"],["\\\\","\\\"","\\n","\\r"],$s).'"';};
$defaults=tempnam($dest,'.mysql-');file_put_contents($defaults,"[client]\nhost=".$escape($config['host'])."\nport=".(int)$config['port']."\nuser=".$escape($config['user'])."\npassword=".$escape($config['pass'])."\n");
$manifest=[];
try{foreach($databases as $label=>$name){if(!preg_match('/^[a-zA-Z0-9_]+$/',$name))throw new RuntimeException('Invalid database name');$file=$dest.'/'.$label.'.sql';if(file_exists($file))throw new RuntimeException('Export already exists: '.$label);
$args=['C:/xampp/mysql/bin/mysqldump.exe','--defaults-extra-file='.$defaults,'--lock-all-tables','--routines','--events','--triggers','--hex-blob','--default-character-set=utf8mb4','--result-file='.$file,$name];$proc=proc_open($args,[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);fclose($pipes[0]);$output=stream_get_contents($pipes[1]);$error=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);if(proc_close($proc)!==0)throw new RuntimeException('Dump failed: '.$label.' '.$error);
$manifest[$label]=['source_database'=>$name,'file'=>basename($file),'bytes'=>filesize($file),'sha256'=>hash_file('sha256',$file)];echo $label.': exported '.round(filesize($file)/1048576,2).' MiB'.PHP_EOL;
}file_put_contents($dest.'/manifest.json',json_encode($manifest,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));}finally{unlink($defaults);}
echo 'Private exports ready: '.$dest.PHP_EOL;
