<?php
// Reproduce the user's basket with real POS defaults. Preparation and cancellation only.
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
ob_start();session_id('aliRealDefaults'.bin2hex(random_bytes(8)));session_start();$_SESSION['user']='superadmin';$_SESSION['expire']=time()+300;$cookie='PHPSESSID='.session_id();session_write_close();
function defaultsCall($path,$cookie,$body=null,$csrf=''){$h=curl_init('http://localhost/api/index.php/'.$path);curl_setopt_array($h,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>15,CURLOPT_COOKIE=>$cookie,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Ali-CSRF: '.$csrf]]);if($body!==null)curl_setopt_array($h,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($body)]);$raw=curl_exec($h);$code=curl_getinfo($h,CURLINFO_HTTP_CODE);curl_close($h);return [$code,json_decode($raw,true)];}
function defaultsAssert($ok,$msg){if(!$ok)throw new Exception($msg);}
$id=bin2hex(random_bytes(4)).'-'.bin2hex(random_bytes(2)).'-4'.substr(bin2hex(random_bytes(2)),0,3).'-8'.substr(bin2hex(random_bytes(2)),0,3).'-'.bin2hex(random_bytes(6));$csrf='';
try{
 [$code,$config]=defaultsCall('ali/config',$cookie);defaultsAssert($code===200,'session');$csrf=$config['csrf'];$warehouse=(int)$config['pos']['warehouse'];
 [$code,$rows]=defaultsCall('productos/stock/'.$warehouse.'?idProductos%5B0%5D=1',$cookie);$p=$rows[0]??null;defaultsAssert($p&&(int)$p['IdProducto']===1,'real product');
 [$code,$client]=defaultsCall('clientes/00000000',$cookie);[$code,$commission]=defaultsCall('comisionista/0000',$cookie);defaultsAssert(!empty($client['IdCliente'])&&!empty($commission['IdCliente']),'real client and store commission');
 $sale=['almacen'=>['IdAlmacen'=>$warehouse],'cliente'=>$client,'tipoVenta'=>['IdTipoDoc'=>7],'comisionista'=>$commission,'CampoDireccion'=>$client['Direccion'],'productos'=>[['IdProducto'=>1,'cantidad'=>2,'precio'=>120,'descuento'=>'','TieneIgv'=>0,'IdProductoSerie'=>null]],'pago'=>['efectivo'=>['monto'=>''],'visa'=>['monto'=>0],'mastercard'=>['monto'=>0],'yape'=>['monto'=>240,'descripcion'=>'']],'PagoCon'=>'','totalCart'=>240];
 [$code,$response]=defaultsCall('ali/pago/preparar',$cookie,['operation'=>$id,'sale'=>$sale],$csrf);
 if(in_array('--expect-reject',$argv,true)){defaultsAssert($code!==200,'Expected previous failure');echo json_encode(['reproduced_http'=>$code,'reason'=>$response['error']??'Unknown'],JSON_UNESCAPED_UNICODE).PHP_EOL;}
 else{
  defaultsAssert($code===200,'real preparation: '.($response['error']??$code));defaultsAssert((float)$response['summary']['total']===240.0&&$response['summary']['payments'][0]['label']==='Yape/Plin','real 240 Yape summary');
  $s=$db->prepare('SELECT Payload FROM Ali_PosOperacion WHERE Operacion=?');$s->execute([$id]);$saved=json_decode($s->fetchColumn(),true);defaultsAssert((int)$saved['comisionista']['IdCliente']===(int)$commission['IdCliente']&&$saved['CampoDireccion']===$client['Direccion'],'preserved commission and actual address');
  [$code,$cancel]=defaultsCall('ali/pago/cancelar',$cookie,['operation'=>$id],$csrf);defaultsAssert($code===200&&!empty($cancel['cancelled']),'cancelled without sale');
  echo 'PASS actual local POS defaults: two cameras at 120 = 240, Yape/Plin, blank optional numbers, actual client address, TIENDA commission and unselected optional series. Prepared and cancelled; no financial registration or SUNAT call.'.PHP_EOL;
 }
}finally{session_start();$_SESSION=[];session_destroy();ob_end_flush();}
