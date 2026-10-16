<?php
// ALI delegates persistence to the existing POST /ventas handler. No fiscal calls.
class AliCheckoutError extends RuntimeException {}
function aliCheckoutAssert($ok,$message){if(!$ok)throw new AliCheckoutError($message);}
function aliCheckoutRow($db,$sql,$args=[]){$s=$db->prepare($sql);$s->execute($args);return $s->fetch(PDO::FETCH_ASSOC);}
function aliCheckoutNumber($v,$min=0,$max=10000000){aliCheckoutAssert(is_numeric($v)&&is_finite((float)$v)&&(float)$v>=$min&&(float)$v<=$max,'Importe o cantidad inválida.');return (float)$v;}
function aliCheckoutOptionalNumber($v){return aliCheckoutNumber($v===null||$v===''?0:$v);}
function aliCheckoutId($v){aliCheckoutAssert(is_numeric($v)&&(int)$v>0&&(string)(int)$v===(string)$v,'Identificador inválido.');return (int)$v;}
function aliCheckoutOperationId($id){aliCheckoutAssert(is_string($id)&&preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-4[a-f0-9]{3}-[89ab][a-f0-9]{3}-[a-f0-9]{12}$/D',$id),'Operación inválida.');return $id;}
function aliCheckoutJson($data){return json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);}
function aliCheckoutLegacy($app,$pattern,$method){
 foreach($app->getContainer()->get('router')->getRoutes() as $route){
  if($route->getPattern()===$pattern&&in_array($method,$route->getMethods(),true)){
   $fn=$app->getContainer()->get('callableResolver')->resolve($route->getCallable());
   return $fn instanceof Closure?$fn->bindTo($app->getContainer()):$fn;
  }
 }
 throw new RuntimeException('Existing POS service missing');
}
function aliCheckoutStock($stockHandler,$request,$warehouse,$ids){
 $q=$request->withParsedBody(null)->withQueryParams(['idProductos'=>$ids,'limit'=>1000]);
 $r=$stockHandler($q,new \Slim\Http\Response(),['idAlmacen'=>$warehouse]);
 $data=json_decode((string)$r->getBody(),true);
 aliCheckoutAssert($r->getStatusCode()===200&&is_array($data),'No se pudo revalidar el inventario.');
 $rows=[];foreach($data as $row)if(is_array($row)&&isset($row['IdProducto']))$rows[(int)$row['IdProducto']]=$row;
 return $rows;
}
function aliCheckoutCanonical($db,$sale,$user,$caps,$stockProvider){
 aliCheckoutAssert(!empty($caps['write']),'No tienes permiso para registrar ventas.');
 aliCheckoutAssert(is_array($sale)&&!empty($sale['productos'])&&count($sale['productos'])<=100,'Agrega productos antes de decir ALIPAGO.');
 foreach(['EsCredito','EsDelivery','IdPedido','IdOferta','IdProforma','puntosAplicados','adelanto'] as $key)aliCheckoutAssert(empty($sale[$key]),'Esta venta requiere el flujo manual del POS (crédito, pedido, oferta o adelanto).');
 aliCheckoutAssert(empty($sale['IdPreOrden'])&&empty($sale['adelantosPreOrden'])&&empty($sale['IdDocVentas']),'Las preórdenes y ofertas se cobran desde el POS manual.');
 $warehouse=aliCheckoutId($sale['almacen']['IdAlmacen']??0);
 $operator=aliCheckoutRow($db,'SELECT Usuario,IdAlmacen,IdDocVentaPuntoVenta FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER(?) AND (Anulado IS NULL OR Anulado<>1) LIMIT 1',[$user]);
 aliCheckoutAssert($operator&&$warehouse===(int)$operator['IdAlmacen'],'Selecciona el almacén asignado a tu usuario para cobrar con ALI.');
 // The current installation uses one company per database; never accept a browser company ID.
 $point=(int)$operator['IdDocVentaPuntoVenta'];aliCheckoutAssert($point>0,'Tu usuario no tiene punto de venta asignado.');
 aliCheckoutAssert(preg_match('/^[\p{L}\p{N}_.@ -]{1,80}$/u',$operator['Usuario']),'El nombre de usuario requiere revisión antes del cobro por voz.');
 $clientId=aliCheckoutId($sale['cliente']['IdCliente']??0);
 $client=aliCheckoutRow($db,'SELECT IdCliente,Cliente,DniRuc,Direccion,Direccion2,Direccion3 FROM Ve_DocVentaCliente WHERE IdCliente=? AND (Anulado IS NULL OR Anulado<>1)',[$clientId]);
 aliCheckoutAssert($client&&preg_match('/^(?:\d{8}|\d{11})$/D',(string)$client['DniRuc']),'Selecciona un cliente con documento válido.');
 $typeId=aliCheckoutId($sale['tipoVenta']['IdTipoDoc']??0);
 $type=aliCheckoutRow($db,'SELECT IdTipoDoc,CodSunat,TipoDoc FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=?',[$typeId]);
 aliCheckoutAssert($type&&in_array((string)$type['CodSunat'],['00','01','03'],true),'Ese comprobante requiere el flujo manual del POS.');
 aliCheckoutAssert((string)$type['CodSunat']!=='01'||strlen($client['DniRuc'])===11,'Para factura selecciona un cliente con RUC.');
 $series=aliCheckoutRow($db,'SELECT Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaPuntoVenta=? AND IdDocVentaTipoDoc=?',[$point,$typeId]);
 aliCheckoutAssert($series&&preg_match('/^[A-Za-z0-9_-]{1,15}$/D',(string)$series['Serie']),'No hay una serie habilitada para este comprobante.');
 $ids=[];foreach($sale['productos'] as $p){aliCheckoutAssert(is_array($p),'Producto inválido.');$id=aliCheckoutId($p['IdProducto']??0);aliCheckoutAssert(!in_array($id,$ids,true),'Hay productos repetidos. Revisa el carrito.');$ids[]=$id;}
 sort($ids);$catalog=$stockProvider($warehouse,$ids);$products=[];$items=[];$total=0;$discountTotal=0;
 foreach($sale['productos'] as $p){
  $id=(int)$p['IdProducto'];$real=$catalog[$id]??null;
  aliCheckoutAssert($real&&empty($real['Anulado']),'Un producto ya no está disponible.');
  aliCheckoutAssert(empty($real['EsPadre'])&&empty($real['EsHabitacion'])&&empty($p['EsPadre'])&&empty($p['EsHabitacion']),'Los kits y alquileres requieren revisión manual.');
  $qty=aliCheckoutNumber($p['cantidad']??0,0.001,100000);$price=aliCheckoutNumber($p['precio']??0,0.01);$cost=aliCheckoutNumber($real['PrecioCosto']??0);
  $discount=aliCheckoutOptionalNumber($p['descuento']??0);$base=round($qty*$price,2);$tax=!empty($p['TieneIgv'])?round($base*0.18,2):0;$line=round($base+$tax-$discount,2);
  aliCheckoutAssert($line>0&&$price>=$cost&&$base-$discount+0.005>=$qty*$cost,'Revisa precio, descuento y costo de los productos.');
  $service=in_array((string)($real['CodigoBarra']??''),['MANODEOBRA','COSTODELIVERY'],true);
  aliCheckoutAssert($service||(isset($real['stock'])&&is_numeric($real['stock'])&&(float)$real['stock']+0.00001>=$qty),'Stock insuficiente para '.$real['Producto'].'.');
  $serialId=empty($p['IdProductoSerie'])?null:aliCheckoutId($p['IdProductoSerie']);
  // The current POS has optional per-unit serial selection and no mandatory-serial product flag.
  // A registered serial does not make all unselected stock serialized.
  if($serialId){
   aliCheckoutAssert($serialId&&$qty===1.0,'Selecciona una serie disponible y cantidad uno para '.$real['Producto'].'.');
   $serial=aliCheckoutRow($db,'SELECT * FROM Gen_Producto_Serie WHERE IdProductoSerie=? AND IdProducto=?',[$serialId,$id]);
   aliCheckoutAssert($serial&&(int)($serial['Estado']??0)!==3&&empty($serial['IdDocVenta']),'La serie seleccionada ya no está disponible.');
   if(!empty($serial['IdMovimiento'])){
    $movement=aliCheckoutRow($db,'SELECT IdAlmacen FROM Lo_Movimiento WHERE IdMovimiento=?',[(int)$serial['IdMovimiento']]);
    aliCheckoutAssert($movement&&(int)$movement['IdAlmacen']===$warehouse,'La serie pertenece a otro almacén.');
   }
  }
  $products[]=['IdProducto'=>$id,'EsPadre'=>0,'cantidad'=>$qty,'precio'=>$price,'PrecioCosto'=>$cost,'descuento'=>$discount,'total'=>$line,'TieneIgv'=>!empty($p['TieneIgv'])?1:0,'IdProductoSerie'=>$serialId,'Descripcion'=>mb_substr((string)($p['Descripcion']??''),0,1000),'EsManoDeObra'=>$service?1:0];
  $items[]=['name'=>$real['Producto'],'quantity'=>$qty,'price'=>$price,'discount'=>$discount,'total'=>$line];$total+=$line;$discountTotal+=$discount;
 }
 $total=round($total,2);aliCheckoutAssert(abs(aliCheckoutNumber($sale['totalCart']??0)-$total)<0.005,'Los totales cambiaron. Revisa el carrito y vuelve a decir ALIPAGO.');
 $payments=[];$paymentSummary=[];$sum=0;
 foreach(['efectivo'=>[1,'Efectivo'],'visa'=>[2,'Tarjeta'],'mastercard'=>[3,'Transferencia'],'yape'=>[4,'Yape/Plin']] as $key=>$definition){
  [$id,$label]=$definition;$p=$sale['pago'][$key]??[];$amount=round(aliCheckoutOptionalNumber($p['monto']??0),2);
  $reference=trim((string)($p['descripcion']??''));aliCheckoutAssert(preg_match('/^[\p{L}\p{N} ._\/\-]{0,100}$/u',$reference),'La referencia de pago admite letras, números, espacios, guion y barra.');
  $payments[$key]=['IdMetodoPago'=>$id,'monto'=>$amount,'descripcion'=>$reference];
  if($amount>0){$enabled=aliCheckoutRow($db,'SELECT IdMetodoPago FROM Ve_DocVentaMetodoPago WHERE IdMetodoPago=?',[$id]);aliCheckoutAssert($enabled,'Método de pago no disponible.');$paymentSummary[]=['label'=>$label,'amount'=>$amount];}
  $sum+=$amount;
 }
 aliCheckoutAssert(abs($sum-$total)<0.005,'Distribuye el total en los métodos de pago antes de decir ALIPAGO.');
 $received=aliCheckoutOptionalNumber($sale['PagoCon']??$sale['pagocon']??0);if($received>0)aliCheckoutAssert($received+0.005>=$payments['efectivo']['monto'],'El efectivo recibido es insuficiente.');
 $direction=$sale['CampoDireccion']??($client['Direccion']??'');
 if(in_array($direction,['Direccion','Direccion2','Direccion3'],true))$direction=$client[$direction]??'';
 $direction=(string)$direction;$addresses=array_map(fn($key)=>(string)($client[$key]??''),['Direccion','Direccion2','Direccion3']);
 aliCheckoutAssert(in_array($direction,$addresses,true),'Selecciona una dirección registrada del cliente.');
 $commissionId=empty($sale['comisionista']['IdCliente'])?0:aliCheckoutId($sale['comisionista']['IdCliente']);
 if($commissionId){$commission=aliCheckoutRow($db,'SELECT IdCliente FROM Ve_DocVentaCliente WHERE IdCliente=? AND esComisionista=1 AND (Anulado IS NULL OR Anulado<>1)',[$commissionId]);aliCheckoutAssert($commission,'El comisionista seleccionado ya no está disponible.');}
 $payload=['almacen'=>['IdAlmacen'=>$warehouse],'puntoVenta'=>['IdDocVentaPuntoVenta'=>$point],'cliente'=>['IdCliente'=>$clientId],'tipoVenta'=>['IdTipoDoc'=>$typeId,'CodSunat'=>(string)$type['CodSunat']],'vendedor'=>['Usuario'=>$operator['Usuario']],'Serie'=>$series['Serie'],'productos'=>$products,'pago'=>$payments,'totalCart'=>$total,'PagoCon'=>$received,'EsCredito'=>0,'FechaCredito'=>date('Y-m-d'),'IdPreOrden'=>[],'adelantosPreOrden'=>[],'CampoDireccion'=>$direction,'EsDelivery'=>false,'IdPedido'=>0,'IdProforma'=>0,'IdOferta'=>null,'IdDocVentas'=>'','CodSunatModifica'=>'','NroComprobanteModifica'=>'','NotaIdMotivo'=>'','NotaDescMotivo'=>'','nombreLlevar'=>'','nombreOrganizacion'=>'','EsOrganizacion'=>0,'DireccionEntrega'=>'','descripcion'=>'','Garantia'=>(int)!empty($sale['Garantia']),'comisionista'=>['IdCliente'=>0],'puntosAplicados'=>0];
 // Preserve fields supported by the existing process; refuse silent loss of others.
 $payload['comisionista']=['IdCliente'=>$commissionId];
 aliCheckoutAssert(empty($sale['EsOrganizacion'])&&empty($sale['descripcion'])&&empty($sale['nombreLlevar']),'Esta venta contiene datos adicionales. Usa Pagar manual para conservarlos.');
 foreach(['yape','visa','mastercard'] as $key){$payload[$key.'Monto']=$payments[$key]['monto'];$payload[$key.'Descripcion']=$payments[$key]['descripcion'];}
 return ['payload'=>$payload,'summary'=>['client'=>$client['Cliente'],'document'=>$type['TipoDoc'],'series'=>$series['Serie'],'warehouse'=>$warehouse,'items'=>$items,'payments'=>$paymentSummary,'discount'=>$discountTotal,'total'=>$total,'fiscal'=>'No se enviará a SUNAT.']];
}
function aliCheckoutOwner($row,$user){aliCheckoutAssert($row&&hash_equals(mb_strtolower($row['Usuario']),mb_strtolower($user)),'Operación no disponible para esta sesión.');}
function aliCheckoutPrepare($db,$id,$user,$canonical){
 $payload=aliCheckoutJson($canonical['payload']);$hash=hash('sha256',$payload);$summary=aliCheckoutJson($canonical['summary']);
 $stmt=$db->prepare('INSERT IGNORE INTO Ali_PosOperacion (Operacion,Usuario,IdEmpresa,IdAlmacen,PayloadHash,Payload,Resumen,Estado,Expira) VALUES (?,?,1,?,?,?,?,?,DATE_ADD(NOW(),INTERVAL 5 MINUTE))');
 $stmt->execute([$id,$user,$canonical['payload']['almacen']['IdAlmacen'],$hash,$payload,$summary,'preparada']);
 $row=aliCheckoutRow($db,'SELECT *,Expira<NOW() AS Vencida FROM Ali_PosOperacion WHERE Operacion=?',[$id]);aliCheckoutOwner($row,$user);
 aliCheckoutAssert(hash_equals($row['PayloadHash'],$hash),'La venta cambió. Cancela el resumen anterior y vuelve a decir ALIPAGO.');
 aliCheckoutAssert($row['Estado']==='completada'||($row['Estado']==='preparada'&&!$row['Vencida']),'El resumen venció o fue cancelado. Vuelve a preparar el pago.');
 return ['operation'=>$id,'state'=>$row['Estado'],'summary'=>json_decode($row['Resumen'],true),'result'=>json_decode($row['Resultado']??'null',true)];
}
function aliCheckoutConfirm($db,$id,$user,$revalidate,$save){
 $lock='ali-pos-'.hash('sha256',(string)$db->query('SELECT DATABASE()')->fetchColumn());$lock=substr($lock,0,64);
 $s=$db->prepare('SELECT GET_LOCK(?,10)');$s->execute([$lock]);aliCheckoutAssert((int)$s->fetchColumn()===1,'Hay otro cobro en curso. Consulta el estado antes de intentar otra vez.');
 try{
  $db->beginTransaction();$row=aliCheckoutRow($db,'SELECT *,Expira<NOW() AS Vencida FROM Ali_PosOperacion WHERE Operacion=? FOR UPDATE',[$id]);aliCheckoutOwner($row,$user);
  if($row['Estado']==='completada'){$db->commit();return json_decode($row['Resultado'],true)+['replayed'=>true];}
  aliCheckoutAssert($row['Estado']==='preparada'&&!$row['Vencida'],'La operación venció o fue cancelada. Prepara nuevamente el pago.');
  $payload=json_decode($row['Payload'],true,512,JSON_THROW_ON_ERROR);
  // Row locks protect serials, costs and assignments during this ALI transaction.
  aliCheckoutRow($db,'SELECT Usuario FROM Seg_Usuario WHERE LOWER(Usuario)=LOWER(?) FOR UPDATE',[$user]);
  foreach($payload['productos'] as $p){aliCheckoutRow($db,'SELECT IdProducto FROM Gen_Producto WHERE IdProducto=? FOR UPDATE',[$p['IdProducto']]);if($p['IdProductoSerie'])aliCheckoutRow($db,'SELECT IdProductoSerie FROM Gen_Producto_Serie WHERE IdProductoSerie=? FOR UPDATE',[$p['IdProductoSerie']]);}
  $fresh=$revalidate($payload);aliCheckoutAssert(hash_equals($row['PayloadHash'],hash('sha256',aliCheckoutJson($fresh['payload']))),'Precio, cliente o configuración cambiaron. Cancela y revisa nuevamente.');
  $result=$save($payload);aliCheckoutAssert(is_array($result)&&!empty($result['insertId'])&&ctype_digit((string)$result['insertId']),'No se pudo registrar la venta. No se confirmó ningún cobro por ALI.');
  $result=['insertId'=>(int)$result['insertId'],'state'=>'registrada','fiscal'=>'sin_envio_sunat','operation'=>$id];
  $s=$db->prepare("UPDATE Ali_PosOperacion SET Estado='completada',IdDocVenta=?,Resultado=?,ConfirmadoPor=?,ConfirmadoEn=NOW() WHERE Operacion=?");$s->execute([$result['insertId'],aliCheckoutJson($result),$user,$id]);$db->commit();return $result;
 }catch(Throwable $e){if($db->inTransaction())$db->rollBack();throw $e;}
 finally{$s=$db->prepare('SELECT RELEASE_LOCK(?)');$s->execute([$lock]);}
}
function aliRegisterCheckout($app,$auth){
 if(!method_exists($app,'getContainer'))return; // configuration-only test harness
 $saveHandler=aliCheckoutLegacy($app,'/ventas','POST');$stockHandler=aliCheckoutLegacy($app,'/productos/stock[/{idAlmacen}]','GET');
 $app->post('/ali/pago/preparar',function($q,$r)use($stockHandler){
  try{$p=$q->getParsedBody();aliCheckoutAssert(is_array($p)&&!array_diff(array_keys($p),['operation','sale']),'Solicitud inválida.');$id=aliCheckoutOperationId($p['operation']??null);$user=$q->getAttribute('ali_user');$caps=$q->getAttribute('ali_pos');
   $previous=aliCheckoutRow($this->db,'SELECT * FROM Ali_PosOperacion WHERE Operacion=?',[$id]);
   if($previous){aliCheckoutOwner($previous,$user);if($previous['Estado']==='completada')return $r->withJson(['operation'=>$id,'state'=>'completada','summary'=>json_decode($previous['Resumen'],true),'result'=>json_decode($previous['Resultado'],true)]);}
   $data=aliCheckoutCanonical($this->db,$p['sale']??null,$user,$caps,fn($w,$ids)=>aliCheckoutStock($stockHandler,$q,$w,$ids));return $r->withJson(aliCheckoutPrepare($this->db,$id,$user,$data));
  }catch(AliCheckoutError $e){return $r->withJson(['error'=>$e->getMessage()],409);}catch(Throwable $e){return $r->withJson(['error'=>'No se pudo preparar el pago. Revisa conexión y configuración.'],503);}
 })->add($auth);
 $app->post('/ali/pago/confirmar',function($q,$r)use($saveHandler,$stockHandler){
  try{$p=$q->getParsedBody();aliCheckoutAssert(is_array($p)&&!array_diff(array_keys($p),['operation','verified'])&&($p['verified']??false)===true,'Confirma que verificaste el pago recibido.');$id=aliCheckoutOperationId($p['operation']??null);$user=$q->getAttribute('ali_user');$caps=$q->getAttribute('ali_pos');aliCheckoutAssert(!empty($caps['write']),'No tienes permiso para cobrar.');
   $result=aliCheckoutConfirm($this->db,$id,$user,fn($sale)=>aliCheckoutCanonical($this->db,$sale,$user,$caps,fn($w,$ids)=>aliCheckoutStock($stockHandler,$q,$w,$ids)),function($sale)use($saveHandler,$q){
    $hadUser=array_key_exists('User',$_SESSION);$previous=$_SESSION['User']??null;$_SESSION['User']=$q->getAttribute('ali_user');
    // Legacy header SQL interpolates CampoDireccion; quote its verified value at this boundary.
    $sale['CampoDireccion']=substr($this->db->quote($sale['CampoDireccion']),1,-1);
    try{$response=$saveHandler($q->withQueryParams([])->withParsedBody($sale),new \Slim\Http\Response());aliCheckoutAssert($response->getStatusCode()<300,'El registro de ventas rechazó la operación. Revisa el POS.');return json_decode((string)$response->getBody(),true);}
    finally{if($hadUser)$_SESSION['User']=$previous;else unset($_SESSION['User']);}
   });return $r->withJson($result);
  }catch(AliCheckoutError $e){return $r->withJson(['error'=>$e->getMessage()],409);}catch(Throwable $e){return $r->withJson(['error'=>'No se obtuvo confirmación. Consulta el estado de esta operación; no repitas el pago manualmente.','uncertain'=>true],503);}
 })->add($auth);
 $app->post('/ali/pago/cancelar',function($q,$r){try{$p=$q->getParsedBody();$id=aliCheckoutOperationId($p['operation']??null);$row=aliCheckoutRow($this->db,'SELECT * FROM Ali_PosOperacion WHERE Operacion=?',[$id]);aliCheckoutOwner($row,$q->getAttribute('ali_user'));$s=$this->db->prepare("UPDATE Ali_PosOperacion SET Estado='cancelada' WHERE Operacion=? AND Estado='preparada'");$s->execute([$id]);return $r->withJson(['cancelled'=>$s->rowCount()>0]);}catch(AliCheckoutError $e){return $r->withJson(['error'=>$e->getMessage()],409);}})->add($auth);
 $app->get('/ali/pago/estado/{operation}',function($q,$r,$args){try{$id=aliCheckoutOperationId($args['operation']);$row=aliCheckoutRow($this->db,'SELECT Estado,Resultado,Usuario FROM Ali_PosOperacion WHERE Operacion=?',[$id]);aliCheckoutOwner($row,$q->getAttribute('ali_user'));return $r->withJson(['state'=>$row['Estado'],'result'=>json_decode($row['Resultado']??'null',true)]);}catch(AliCheckoutError $e){return $r->withJson(['error'=>$e->getMessage()],409);}})->add($auth);
}
