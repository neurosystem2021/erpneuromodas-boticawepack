<?php
ob_start();session_id('nstBankAccess'.bin2hex(random_bytes(8)));session_start();$cookie='PHPSESSID='.session_id();session_write_close();
try{
 foreach(['vendedor-prueba'=>403,'SUPERADMIN'=>200] as $user=>$expected){session_start();$_SESSION['user']=$user;session_write_close();$routes=$expected===403?['/api/index.php/bancos/reporte','/api/index.php/bancos/reporte/pdf','/views/ReporteBancos.php']:['/api/index.php/bancos/reporte'];foreach($routes as $route){$c=curl_init('http://localhost'.$route);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>$cookie,CURLOPT_TIMEOUT=>10]);$body=curl_exec($c);$status=curl_getinfo($c,CURLINFO_HTTP_CODE);curl_close($c);if($status!==$expected)throw new Exception($route.' returned '.$status.' expected '.$expected);echo $user.' '.$route.' HTTP '.$status.PHP_EOL;}}
}finally{session_start();$_SESSION=[];session_destroy();ob_end_flush();}
