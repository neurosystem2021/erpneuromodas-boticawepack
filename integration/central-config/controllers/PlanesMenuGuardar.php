<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
function planResponder($ok,$message,$status=200){http_response_code($status);echo json_encode(array('ok'=>$ok,'message'=>$message),JSON_UNESCAPED_UNICODE);exit;}
if ($_SERVER['REQUEST_METHOD']!=='POST') planResponder(false,'Método no permitido.',405);
if (strtolower(trim((string)($_SESSION['user']??'')))!=='superadmin') planResponder(false,'Solo SUPERADMIN puede configurar los planes.',403);
if (empty($_POST['csrf'])||empty($_SESSION['planes_menu_csrf'])||!hash_equals($_SESSION['planes_menu_csrf'],$_POST['csrf'])) planResponder(false,'La sesión de seguridad venció. Actualice la página.',403);
$activo=mb_substr(trim((string)($_POST['plan_activo']??'')),0,60);
$planes=json_decode((string)($_POST['planes_json']??''),true);
if ($activo===''||!is_array($planes)||!isset($planes[$activo])) planResponder(false,'Seleccione un plan válido.',422);
$limpios=array();
foreach(array_slice($planes,0,30,true) as $nombre=>$plan){
  $nombre=preg_replace('/[^\pL\pN _-]/u','',mb_substr(trim((string)$nombre),0,60));if($nombre===''||!is_array($plan))continue;
  $disabled=array_values(array_unique(array_slice(array_filter(array_map('strval',$plan['disabled']??array())),0,300)));
  $groups=array_values(array_unique(array_slice(array_filter(array_map('strval',$plan['disabled_groups']??array())),0,30)));
  $limpios[$nombre]=array('disabled'=>$disabled,'disabled_groups'=>$groups);
}
if(!isset($limpios[$activo])) planResponder(false,'El plan activo no es válido.',422);
require_once __DIR__.'/../models/DBManager.php';$db=getMysqliLink();$idEmpresa=1;
$meta=array('menu_plan_activo'=>$activo,'menu_planes_json'=>json_encode($limpios,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$db->begin_transaction();
try{$sel=$db->prepare('SELECT IdEmpresaConfig FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey=? ORDER BY IdEmpresaConfig ASC LIMIT 1');$upd=$db->prepare('UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresaConfig=?');$ins=$db->prepare('INSERT INTO Gen_EmpresaConfig (IdEmpresa,MetaKey,MetaValue) VALUES (?,?,?)');foreach($meta as $k=>$v){$sel->bind_param('is',$idEmpresa,$k);$sel->execute();$row=$sel->get_result()->fetch_assoc();if($row){$id=(int)$row['IdEmpresaConfig'];$upd->bind_param('si',$v,$id);$upd->execute();}else{$ins->bind_param('iss',$idEmpresa,$k,$v);$ins->execute();}}$db->commit();planResponder(true,'Plan y módulos guardados correctamente.');}catch(Throwable $e){$db->rollback();planResponder(false,'No se pudo guardar la configuración: '.$e->getMessage(),500);}
