<?php
ob_start();session_id('directPrintCheck'.bin2hex(random_bytes(12)));session_start();$_SESSION=['user'=>'admin','idPerfil'=>1,'expire'=>time()+180];$cookie='PHPSESSID='.session_id();session_write_close();
function directCheck($path,$cookie='',$body=null,$header=true){$h=curl_init('http://localhost/imprimir/'.$path);curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIE=>$cookie,CURLOPT_TIMEOUT=>25]);if($body!==null)curl_setopt_array($h,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($body),CURLOPT_HTTPHEADER=>array_merge(['Content-Type: application/json'],$header?['X-Requested-With: XMLHttpRequest']:[])]);$raw=curl_exec($h);$status=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);return [$status,json_decode($raw,true)];}
try{
 [$code]=directCheck('configuracion.php');if($code!==401)throw new RuntimeException('Anonymous configuration not blocked');
 [$code,$settings]=directCheck('configuracion.php',$cookie);if($code!==200)throw new RuntimeException('Windows queue discovery failed: '.$code);$bar=null;foreach($settings['printers']??[] as $p)if($p['name']==='bar')$bar=$p;if(!$bar||$bar['port']!=='192.168.1.68')throw new RuntimeException('BAR queue/port mismatch');
 [$code]=directCheck('usb.php',$cookie,['IdDocVenta'=>0,'PrinterName'=>'bar']);if($code!==422)throw new RuntimeException('Print dependencies/validation failed: '.$code);
 [$code]=directCheck('usb.php',$cookie,['IdDocVenta'=>0,'PrinterName'=>'bar'],false);if($code!==403)throw new RuntimeException('Request guard failed');
 echo "PASS installed print dependencies and auth, Windows BAR discovery at 192.168.1.68, invalid sales rejected, request guard enforced. No real sale or print submitted.\n";
}finally{session_start();$_SESSION=[];session_destroy();ob_end_flush();}
