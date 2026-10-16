<?php
// Live integration preflight: prepare + cancel only. Never submit a verified confirmation.
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
ob_start();session_id('aliPagoPreflight'.bin2hex(random_bytes(8)));session_start();$_SESSION['user']='superadmin';$_SESSION['expire']=time()+300;$cookie='PHPSESSID='.session_id();session_write_close();
function preflightCall($path,$cookie,$body=null,$csrf=''){$h=curl_init('http://localhost/api/index.php/'.$path);curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_COOKIE=>$cookie,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Ali-CSRF: '.$csrf]]);if($body!==null)curl_setopt_array($h,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($body)]);$raw=curl_exec($h);$code=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);return [$code,json_decode($raw,true)];}
function preflightCheck($ok,$msg){if(!$ok)throw new Exception($msg);}
try{
 [$code,$config]=preflightCall('ali/config',$cookie);preflightCheck($code===200,'live config');$csrf=$config['csrf'];$warehouse=(int)$config['pos']['warehouse'];
 [$code,$rows]=preflightCall('productos/stock/'.$warehouse.'?limit=1000',$cookie);preflightCheck($code===200&&is_array($rows),'existing stock handler');$product=null;
 foreach($rows as $row){$count=$db->prepare('SELECT COUNT(*) FROM Gen_Producto_Serie WHERE IdProducto=?');$count->execute([(int)$row['IdProducto']]);if(empty($row['EsPadre'])&&empty($row['EsHabitacion'])&&(float)$row['PrecioContado']>0&&(float)$row['PrecioContado']>=(float)$row['PrecioCosto']&&((float)$row['stock']>=1||in_array($row['CodigoBarra'],['MANODEOBRA','COSTODELIVERY']))&&!(int)$count->fetchColumn()){$product=$row;break;}}
 $expectStockRejection=$product===null;
 if($expectStockRejection)foreach($rows as $row){if(empty($row['EsPadre'])&&empty($row['EsHabitacion'])&&(float)$row['PrecioContado']>0&&(float)$row['PrecioContado']>=(float)$row['PrecioCosto']){$product=$row;break;}}
 preflightCheck($product!==null,'No suitable product for nonfinancial preflight');
 $client=$db->query("SELECT IdCliente FROM Ve_DocVentaCliente WHERE (Anulado IS NULL OR Anulado<>1) AND DniRuc REGEXP '^[0-9]{8}$' LIMIT 1")->fetchColumn();$type=$db->query("SELECT IdTipoDoc FROM Ve_DocVentaTipoDoc WHERE CodSunat='03' LIMIT 1")->fetchColumn();preflightCheck($client&&$type,'existing client and document type');
 $price=(float)$product['PrecioContado'];$id=bin2hex(random_bytes(4)).'-'.bin2hex(random_bytes(2)).'-4'.bin2hex(random_bytes(2));$id=substr($id,0,18).'-8'.substr(bin2hex(random_bytes(2)),0,3).'-'.bin2hex(random_bytes(6));
 $sale=['almacen'=>['IdAlmacen'=>$warehouse],'cliente'=>['IdCliente'=>(int)$client],'tipoVenta'=>['IdTipoDoc'=>(int)$type],'productos'=>[['IdProducto'=>(int)$product['IdProducto'],'cantidad'=>1,'precio'=>$price,'descuento'=>0,'TieneIgv'=>0]],'pago'=>['efectivo'=>['monto'=>$price]],'totalCart'=>$price];
 [$code,$prepared]=preflightCall('ali/pago/preparar',$cookie,['operation'=>$id,'sale'=>$sale],$csrf);
 if($expectStockRejection){preflightCheck($code===409&&strpos($prepared['error']??'','Stock insuficiente')!==false,'stock rejection integration: '.($prepared['error']??$code));echo 'PASS actual stock adapter and live checkout validation reject insufficient inventory. Positive preparation/concurrency use isolated fixtures; no live sale or ALI operation created.'.PHP_EOL;return;}
 preflightCheck($code===200,'prepare integration: '.($prepared['error']??$code));
 [$code,$again]=preflightCall('ali/pago/preparar',$cookie,['operation'=>$id,'sale'=>$sale],$csrf);preflightCheck($code===200&&$again['operation']===$prepared['operation'],'same preflight operation');
 [$code]=preflightCall('ali/pago/confirmar',$cookie,['operation'=>$id,'verified'=>false],$csrf);preflightCheck($code===409,'unverified confirmation denied');
 [$code,$cancelled]=preflightCall('ali/pago/cancelar',$cookie,['operation'=>$id],$csrf);preflightCheck($code===200&&!empty($cancelled['cancelled']),'cancel preflight');
 [$code,$state]=preflightCall('ali/pago/estado/'.$id,$cookie);preflightCheck($code===200&&$state['state']==='cancelada'&&$state['result']===null,'cancelled without sale');
 echo 'PASS actual stock and sales-service adapter discovery, live canonical preparation, repeated operation, unverified payment denied, cancelled state. One cancelled ALI audit record; no sale, payment, sequence or SUNAT writes.'.PHP_EOL;
}finally{session_start();$_SESSION=[];session_destroy();ob_end_flush();}
