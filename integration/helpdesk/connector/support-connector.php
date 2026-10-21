<?php
if(!function_exists('appPdoConnection'))require_once __DIR__.'/../config/database.php';
// Windows can hide table-name case mismatches that fail on Linux.
function supportConnectorTable($db,$expected){
 if(!in_array($expected,['Seg_Usuario','Gen_Empresa'],true))throw new RuntimeException('Unsupported identity table');
 $s=$db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE BINARY TABLE_SCHEMA=BINARY DATABASE() AND LOWER(TABLE_NAME)=LOWER(?)');$s->execute([$expected]);$matches=$s->fetchAll(PDO::FETCH_COLUMN);
 if(in_array($expected,$matches,true))$name=$expected;
 elseif(count($matches)===1)$name=$matches[0];
 else throw new RuntimeException('Identity table unavailable or ambiguous');
 return '`'.str_replace('`','``',$name).'`';
}
// Installed in a client ERP. Private key never leaves its server.
function supportConnectorSend($config,$identity,$action,$payload){
 $url=rtrim($config['central_api'],'/').'/support/bridge';if(parse_url($url,PHP_URL_SCHEME)!=='https')throw new RuntimeException('Central support requires HTTPS');
 $raw=json_encode(['identity'=>$identity,'action'=>$action,'payload'=>$payload],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);$time=(string)time();$nonce=bin2hex(random_bytes(32));$canonical="POST\n/support/bridge\n".$time."\n".$nonce."\n".hash('sha256',$raw);$secret=base64_decode($config['private_key_base64'],true);if(!$secret||!openssl_pkey_get_private($secret))throw new RuntimeException('Invalid signing key');
 if(!openssl_sign($canonical,$signed,$secret,OPENSSL_ALGO_SHA256))throw new RuntimeException('Signing failed');$signature=base64_encode($signed);$c=curl_init($url);curl_setopt_array($c,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$raw,CURLOPT_RETURNTRANSFER=>true,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>20,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_HTTPHEADER=>['Content-Type: application/json','X-Support-installation: '.$config['uuid'],'X-Support-key: '.$config['key_id'],'X-Support-time: '.$time,'X-Support-nonce: '.$nonce,'X-Support-signature: '.$signature]]);$response=curl_exec($c);$status=curl_getinfo($c,CURLINFO_HTTP_CODE);$error=curl_errno($c);curl_close($c);if($error||$response===false)throw new RuntimeException('Central support connection unavailable');$json=json_decode($response,true);if(!is_array($json))throw new RuntimeException('Invalid central response');return [$status,$json];
}
return function($app){
 $proxy=function($q,$r,$action,$payload){try{
  if(session_status()!==PHP_SESSION_ACTIVE)session_start();$user=$_SESSION['user']??'';if(!$user||(isset($_SESSION['expire'])&&$_SESSION['expire']<time())){session_write_close();return $r->withJson(['error'=>'Inicia sesión.'],401);}
  $_SESSION['support_csrf']=$_SESSION['support_csrf']??bin2hex(random_bytes(24));$csrf=$_SESSION['support_csrf'];$branch=$_SESSION['idAlmacen']??'';session_write_close();if($q->getMethod()!=='GET'&&!hash_equals($csrf,$q->getHeaderLine('X-Support-CSRF')))return $r->withJson(['error'=>'Actualiza la página antes de guardar.'],403);
  $file=getenv('SUPPORT_CONNECTOR_CONFIG');if(!$file&&is_file(__DIR__.'/support-connector-location.php'))$file=require __DIR__.'/support-connector-location.php';if(!$file||!is_file($file))throw new RuntimeException('Private connector config unavailable');$cfg=require $file;$db=appPdoConnection();$usersTable=supportConnectorTable($db,'Seg_Usuario');$companyTable=supportConnectorTable($db,'Gen_Empresa');$s=$db->prepare('SELECT Usuario,NombreUsuario FROM '.$usersTable.' WHERE Usuario=? AND IFNULL(Anulado,0)=0');$s->execute([$user]);$u=$s->fetch(PDO::FETCH_ASSOC);if(!$u)return $r->withJson(['error'=>'Usuario deshabilitado.'],403);
  // Company is provisioned from this ERP, never from submitted form data.
  $s=$db->prepare('SELECT IDEMPRESA FROM '.$companyTable.' WHERE IDEMPRESA=?');$s->execute([$cfg['company']]);if(!$s->fetchColumn())throw new RuntimeException('Configured company not found');
  unset($payload['tenant_id']);if($action==='create')$payload['branch']=(string)$branch;
  [$status,$data]=supportConnectorSend($cfg,['user'=>$u['Usuario'],'name'=>$u['NombreUsuario']?:$u['Usuario'],'company'=>(string)$cfg['company']],$action,$payload);if($action==='context'&&$status===200){$data['csrf']=$csrf;$data['remote']=true;}return $r->withJson($data,$status)->withHeader('Cache-Control','no-store');
 }catch(Throwable $e){error_log('[SUPPORT CONNECTOR] '.get_class($e));return $r->withJson(['error'=>'No se pudo conectar con el centro de soporte. Intenta nuevamente.'],503);}};
 foreach(['context','tickets','members','notifications'] as $action)$app->get('/support/'.$action,function($q,$r)use($proxy,$action){return $proxy($q,$r,$action,$q->getQueryParams());});
 $app->get('/support/tickets/{id:[0-9]+}',function($q,$r,$args)use($proxy){return $proxy($q,$r,'detail',$args);});
 foreach(['create','message','transition','assign','worklog','survey','notification_read'] as $action)$app->post('/support/'.$action,function($q,$r)use($proxy,$action){return $proxy($q,$r,$action,$q->getParsedBody()?:[]);});
};
