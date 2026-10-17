<?php
// Restrictions are additional to existing profile and accounting permissions.
function moduleAccessKey($url){
 $path=strtolower(rtrim((string)parse_url($url,PHP_URL_PATH),'/'));parse_str((string)parse_url($url,PHP_URL_QUERY),$q);
 if($path==='/views/contabilidad.php')return $path.'?tab='.(is_string($q['tab']??null)?$q['tab']:'dashboard');
 if($path==='/views/diario.php'&&!empty($q['reportes']))return $path.'?reportes=1';
 return $path;
}
function moduleAccessPolicy(){
 static $policy=null;if($policy!==null)return $policy;
 require_once dirname(__DIR__).'/config/database.php';$db=appPdoConnection();
 $rows=$db->query("SELECT MetaKey,MetaValue FROM Gen_EmpresaConfig WHERE IdEmpresa=1 AND MetaKey IN ('menu_usuarios_json','menu_planes_json','menu_plan_activo') ORDER BY IdEmpresaConfig")->fetchAll(PDO::FETCH_KEY_PAIR);
 $users=json_decode($rows['menu_usuarios_json']??'{}',true)?:[];$plans=json_decode($rows['menu_planes_json']??'{}',true)?:[];
 return $policy=['users'=>$users,'plan'=>$plans[$rows['menu_plan_activo']??'COMPLETO']??['disabled'=>[]]];
}
function moduleAccessAllowed($user,$url,$policy=null){
 if(strcasecmp(trim($user),'superadmin')===0)return true;
 $policy=$policy??moduleAccessPolicy();$key=moduleAccessKey($url);$path=explode('?',$key)[0];
 $rules=$policy['users'][$user]??[];
 $blocked=array_merge($policy['plan']['disabled']??[],$rules['disabled']??[]);
 return !in_array($key,$blocked,true)&&!in_array($path,$blocked,true);
}
function moduleAccessRequire($url,$json=false){
 if(moduleAccessAllowed((string)($_SESSION['user']??''),$url))return;
 http_response_code(403);
 if($json){header('Content-Type: application/json; charset=utf-8');echo json_encode(['error'=>'Tu usuario no tiene acceso a este módulo.','ok'=>false,'message'=>'Tu usuario no tiene acceso a este módulo.']);}
 else echo '<!doctype html><html lang="es"><meta charset="utf-8"><title>Acceso restringido</title><body style="font:16px Arial;padding:40px"><h2>Acceso restringido</h2><p>Tu usuario no tiene acceso a este módulo. Solicita la autorización del administrador.</p><a href="/">Volver al sistema</a></body></html>';
 exit;
}
