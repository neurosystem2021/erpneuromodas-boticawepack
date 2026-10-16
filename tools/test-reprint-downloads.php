<?php
ob_start();session_id('nstReprint'.bin2hex(random_bytes(8)));session_start();$_SESSION['user']='superadmin';$cookie='PHPSESSID='.session_id();session_write_close();
try{foreach(['ventas?noFecha=1&limit=10&page=1','descargarxml/1','imprimirpdf/1?descargar=1'] as $route){$c=curl_init('http://localhost/api/index.php/'.$route);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>$cookie,CURLOPT_TIMEOUT=>60,CURLOPT_HEADER=>true]);$raw=curl_exec($c);$code=curl_getinfo($c,CURLINFO_HTTP_CODE);$size=curl_getinfo($c,CURLINFO_HEADER_SIZE);curl_close($c);$headers=substr($raw,0,$size);$body=substr($raw,$size);
 if(strpos($route,'ventas?')===0){$d=json_decode($body,true);if($code!==200||!is_array($d)||!array_key_exists('Telefono',$d[0])||!array_key_exists('Email',$d[0]))throw new Exception('Contact data failed');}
 elseif(strpos($route,'descargarxml')===0){if(!in_array($code,[200,404],true))throw new Exception('XML response '.$code);if($code===404&&empty(json_decode($body,true)['error']))throw new Exception('Missing XML error');}
 else{if($code!==200||substr($body,0,5)!=='%PDF-'||strpos($headers,'attachment; filename=')===false)throw new Exception('PDF download failed '.$code);}
 echo strtok($route,'?').' HTTP '.$code.' verified'.PHP_EOL;
}}
finally{session_start();$_SESSION=[];session_destroy();ob_end_flush();}
