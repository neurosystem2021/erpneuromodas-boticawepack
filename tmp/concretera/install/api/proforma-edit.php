<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
session_start();
function quoteFail($code,$message){http_response_code($code);echo json_encode(['error'=>$message]);exit;}
if(empty($_SESSION['user'])||(isset($_SESSION['expire'])&&$_SESSION['expire']<time()))quoteFail(401,'Inicia sesión nuevamente.');
require_once dirname(__DIR__).'/config/database.php';
try{
 $db=appPdoConnection();$s=$db->prepare('SELECT IdUsuarioPerfil FROM Seg_Usuario WHERE Usuario=? AND COALESCE(Anulado,0)=0');$s->execute([$_SESSION['user']]);if(!$s->fetchColumn())quoteFail(403,'Usuario no autorizado.');
 if(empty($_SESSION['proforma_csrf']))$_SESSION['proforma_csrf']=bin2hex(random_bytes(24));
 if($_SERVER['REQUEST_METHOD']==='GET' && isset($_GET['q'])){
 $q=trim((string)$_GET['q']);if($q===''){echo json_encode([]);exit;}
 $where=[];$args=[];foreach(array_slice(preg_split('/\s+/u',$q),0,8) as $term){$where[]='(Producto LIKE ? OR CodigoBarra LIKE ? OR Codigo LIKE ? OR ProductoModelo LIKE ? OR CAST(IdProducto AS CHAR)=?)';array_push($args,'%'.$term.'%','%'.$term.'%','%'.$term.'%','%'.$term.'%',$term);}
 $s=$db->prepare('SELECT * FROM Gen_Producto WHERE COALESCE(Anulado,0)=0 AND '.implode(' AND ',$where).' ORDER BY Producto LIMIT 30');$s->execute($args);echo json_encode($s->fetchAll(PDO::FETCH_ASSOC),JSON_INVALID_UTF8_SUBSTITUTE);exit;
 }
 if($_SERVER['REQUEST_METHOD']==='GET'){echo json_encode(['csrf'=>$_SESSION['proforma_csrf']]);exit;}
 if($_SERVER['REQUEST_METHOD']!=='POST')quoteFail(405,'Método no permitido.');
 if(!hash_equals($_SESSION['proforma_csrf'],$_SERVER['HTTP_X_PROFORMA_CSRF']??''))quoteFail(403,'Actualiza la página e intenta nuevamente.');
 $p=json_decode(file_get_contents('php://input'),true);$id=(int)($p['IdProforma']??0);$client=(int)($p['cliente']['IdCliente']??0);$days=(int)($p['diasValidosProf']??7);$products=$p['productos']??[];
 if(!$id||!$client||$days<1||$days>3650||!is_array($products)||!count($products)||count($products)>1000)throw new InvalidArgumentException('Revisa el cliente, productos y días de validez.');
 foreach($products as $row){foreach(['cantidad','precio','descuento'] as $key){$n=$row[$key]??0;if(!is_numeric($n)||!is_finite((float)$n)||(float)$n<0)throw new InvalidArgumentException('Revisa las cantidades, precios y descuentos.');}if(empty($row['IdProducto'])||$row['cantidad']<=0||($row['descuento']??0)>$row['cantidad']*$row['precio'])throw new InvalidArgumentException('Producto o descuento inválido.');}
 require_once __DIR__.'/proforma-presentation.php';
 $presentation=array_key_exists('presentacionProforma',$p)?quotePresentationInput($p):null;
 require_once __DIR__.'/concretera/LegacyGuard.php';if(cxLegacyLink($db,'PROFORMA',$id))quoteFail(409,'Esta propuesta se gestiona por versiones en Concretera.');
 $db->beginTransaction();$s=$db->prepare('SELECT * FROM Ve_Proforma WHERE IdProforma=? FOR UPDATE');$s->execute([$id]);$quote=$s->fetch(PDO::FETCH_ASSOC);if(!$quote||!empty($quote['Anulado']))throw new InvalidArgumentException('La proforma no está disponible.');
 quoteRequireClientPhone($db,$client,$presentation['tipo']??($quote['Presentacion']??'productos'));
 $s=$db->prepare('SELECT COUNT(*) FROM Ve_DocVenta WHERE IdProforma=? AND COALESCE(Anulado,0)=0');$s->execute([$id]);if($s->fetchColumn())throw new InvalidArgumentException('La proforma ya fue facturada. Crea una nueva para cotizar cambios.');
 $s=$db->prepare('UPDATE Ve_Proforma SET IdCliente=?,DiasValidos=?,Descripcion=? WHERE IdProforma=?');$s->execute([$client,$days,(string)($p['descripcion']??''),$id]);
 $s=$db->prepare('DELETE FROM Ve_ProformaDet WHERE IdProforma=?');$s->execute([$id]);
 $s=$db->prepare('INSERT INTO Ve_ProformaDet (IdProforma,IdProducto,Descripcion,Cantidad,Precio,Descuento,DisabledTotal) VALUES (?,?,?,?,?,?,?)');foreach($products as $row)$s->execute([$id,(int)$row['IdProducto'],(string)($row['Descripcion']??''),$row['cantidad'],$row['precio'],$row['descuento']??0,!empty($row['disabledProf'])?1:0]);
 if($presentation!==null)quotePresentationSave($db,$id,$presentation);
 $db->commit();echo json_encode(['insertId'=>$id,'updated'=>true]);
}catch(InvalidArgumentException $e){if(isset($db)&&$db->inTransaction())$db->rollBack();quoteFail(422,$e->getMessage());}catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();error_log('Proforma edit: '.$e->getMessage());quoteFail(500,'No se pudo guardar la proforma. Tus cambios siguen en pantalla.');}
