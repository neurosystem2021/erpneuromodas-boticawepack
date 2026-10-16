<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['user']) || (isset($_SESSION['expire']) && time() > $_SESSION['expire'])) { http_response_code(401); echo json_encode(array('error'=>'Inicie sesión en el sistema.')); exit; }
require_once dirname(__DIR__) . '/config/database.php';
try {
 $db=appPdoConnection();
 $canDelete=strtolower((string)$_SESSION['user'])==='superadmin';
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  $origin=$_SERVER['HTTP_ORIGIN'] ?? ''; if($origin && parse_url($origin,PHP_URL_HOST)!==explode(':',$_SERVER['HTTP_HOST'])[0]) {http_response_code(403);echo json_encode(array('error'=>'Origen inválido'));exit;}
  $body=json_decode(file_get_contents('php://input'),true);$id=(int)($body['id']??0);
  if (($body['accion']??'')==='eliminar') {
   if (!$canDelete) {http_response_code(403);echo json_encode(array('error'=>'Solo superadmin puede eliminar pedidos.'));exit;}
   $stmt=$db->prepare("UPDATE Ve_Proforma p SET p.Anulado=1 WHERE p.IdProforma=? AND p.UsuarioReg='TIENDA' AND COALESCE(p.Anulado,0)=0 AND NOT EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)");
   $stmt->execute(array($id));
   if(!$stmt->rowCount()){http_response_code(409);echo json_encode(array('error'=>'El pedido ya fue facturado, eliminado o no existe.'));exit;}
   echo json_encode(array('ok'=>true));exit;
  }
  $stmt=$db->prepare("UPDATE Ve_Proforma SET WebCargado=1 WHERE IdProforma=? AND UsuarioReg='TIENDA' AND COALESCE(Anulado,0)=0");$stmt->execute(array($id));echo json_encode(array('ok'=>true));exit;
 }
 $q=trim((string)($_GET['q']??''));$status=(string)($_GET['estado']??'pendientes');
 $base=" FROM Ve_Proforma p LEFT JOIN Ve_DocVentaCliente c ON c.IdCliente=p.IdCliente WHERE p.UsuarioReg='TIENDA' AND COALESCE(p.Anulado,0)=0";
 $unbilled=" AND NOT EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)";
 $count=$db->query('SELECT COUNT(*)'.$base.' AND COALESCE(p.WebCargado,0)=0'.$unbilled)->fetchColumn();
 $params=array();
 if($status==='pendientes')$base.=$unbilled;
 if($status==='facturados')$base.=' AND EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)';
 if($q!==''){$base.=' AND (c.Cliente LIKE ? OR c.DniRuc LIKE ? OR p.Numero LIKE ?)';$params=array('%'.$q.'%','%'.$q.'%','%'.$q.'%');}
 $sql="SELECT p.IdProforma,p.Numero,p.Anio,p.FechaReg,p.IdCliente,c.Cliente,c.DniRuc,p.Descripcion,p.WebCargado,
 (SELECT COALESCE(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0)),0) FROM Ve_ProformaDet d WHERE d.IdProforma=p.IdProforma) Total,
 EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0) Facturado".$base.' ORDER BY p.IdProforma DESC LIMIT 100';
 $stmt=$db->prepare($sql);$stmt->execute($params);echo json_encode(array('puedeEliminar'=>$canDelete,'pendientes'=>(int)$count,'pedidos'=>$stmt->fetchAll(PDO::FETCH_ASSOC)),JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){http_response_code(500);echo json_encode(array('error'=>'No se pudieron consultar los pedidos web.'));}
