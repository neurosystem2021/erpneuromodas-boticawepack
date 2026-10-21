<?php
if(!function_exists('appPdoConnection'))require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/support-service.php';
require_once __DIR__.'/support-runtime.php';
function supportDatabase(){
 $cfg=supportRuntimeConfig();$db=supportRuntimeConnect($cfg);supportRuntimeVerify($db,$cfg);return [$db,$cfg];
}
function supportVerifyBridge($db,$raw,$headers){
 if(strlen($raw)>128000||!function_exists('openssl_verify'))throw new InvalidArgumentException('Solicitud no válida.');$envelope=json_decode($raw,true);if(!is_array($envelope))throw new InvalidArgumentException('Solicitud no válida.');
 $uuid=$headers['installation']??'';$key=$headers['key']??'';$time=$headers['time']??'';$nonce=$headers['nonce']??'';$signature=base64_decode($headers['signature']??'',true);
 if(!ctype_digit((string)$time)||abs(time()-(int)$time)>120||!preg_match('/^[a-f0-9]{64}$/',$nonce)||!$signature||strlen($signature)>1024)throw new InvalidArgumentException('Firma no válida o vencida.');
 $row=supportQuery($db,'SELECT i.id,k.public_key_base64 FROM support_installations i JOIN support_installation_keys k ON k.installation_id=i.id WHERE i.installation_uuid=? AND i.active=1 AND k.key_id=? AND k.active=1 AND k.revoked_at IS NULL',[$uuid,$key])->fetch(PDO::FETCH_ASSOC);if(!$row)throw new InvalidArgumentException('Instalación no autorizada.');
 $canonical="POST\n/support/bridge\n".$time."\n".$nonce."\n".hash('sha256',$raw);$public=base64_decode($row['public_key_base64'],true);if(!$public||openssl_verify($canonical,$signature,$public,OPENSSL_ALGO_SHA256)!==1)throw new InvalidArgumentException('Firma no válida.');
 try{supportQuery($db,'INSERT INTO support_request_nonces(installation_id,nonce,expires_at) VALUES(?,?,?)',[$row['id'],$nonce,date('Y-m-d H:i:s',time()+300)]);}catch(PDOException $e){throw new InvalidArgumentException('Solicitud ya procesada.');}
 $identity=$envelope['identity']??[];$i=supportIdentity($db,$row['id'],supportText($identity['user']??'',255),supportText($identity['company']??'',100),supportText($identity['name']??'',255));
 // Tenant is resolved by the signed installation/company mapping, never a browser role.
 $payload=$envelope['payload']??[];if(!is_array($payload))throw new InvalidArgumentException('Datos inválidos.');unset($payload['tenant_id']);$payload['tenant_id']=$i['tenant'];return [$i,supportText($envelope['action']??'',40),$payload];
}
return function($app){
 $app->post('/support/bridge',function($q,$r){$db=null;try{[$db]=supportDatabase();$headers=[];foreach(['installation','key','time','nonce','signature'] as $key)$headers[$key]=$q->getHeaderLine('X-Support-'.$key);[$i,$action,$payload]=supportVerifyBridge($db,(string)$q->getBody(),$headers);return $r->withJson(supportDispatch($db,$i,$action,$payload))->withHeader('Cache-Control','no-store');}catch(InvalidArgumentException $e){if($db&&$db->inTransaction())$db->rollBack();return $r->withJson(['error'=>$e->getMessage()],422);}catch(Throwable $e){if($db&&$db->inTransaction())$db->rollBack();error_log('[SUPPORT] bridge '.get_class($e));return $r->withJson(['error'=>'No se pudo procesar la solicitud.'],500);}});
 $auth=function($q,$r,$next){$db=null;try{
  if(session_status()!==PHP_SESSION_ACTIVE)session_start();$user=$_SESSION['user']??'';if(!$user||(isset($_SESSION['expire'])&&$_SESSION['expire']<time())){session_write_close();return $r->withJson(['error'=>'Inicia sesión.'],401);}
  $_SESSION['support_csrf']=$_SESSION['support_csrf']??bin2hex(random_bytes(24));$csrf=$_SESSION['support_csrf'];$branch=(string)($_SESSION['idAlmacen']??'');session_write_close();if($q->getMethod()!=='GET'&&!hash_equals($csrf,$q->getHeaderLine('X-Support-CSRF')))return $r->withJson(['error'=>'Actualiza la página antes de guardar.'],403);
  $erp=appPdoConnection();$users=supportErpTable($erp,'Seg_Usuario');$u=supportQuery($erp,'SELECT Usuario,NombreUsuario FROM '.$users.' WHERE Usuario=? AND IFNULL(Anulado,0)=0',[$user])->fetch(PDO::FETCH_ASSOC);if(!$u)return $r->withJson(['error'=>'Usuario deshabilitado.'],403);
  [$db,$cfg]=supportDatabase();$i=supportIdentity($db,$cfg['local_installation'],$u['Usuario'],$cfg['local_company'],$u['NombreUsuario']?:$u['Usuario']);$i['csrf']=$csrf;$i['branch']=$branch;
  return $next($q->withAttribute('support_db',$db)->withAttribute('support_identity',$i),$r)->withHeader('Cache-Control','no-store');
 }catch(InvalidArgumentException $e){if($db&&$db->inTransaction())$db->rollBack();return $r->withJson(['error'=>$e->getMessage()],422);}catch(Throwable $e){if($db&&$db->inTransaction())$db->rollBack();$failure=supportRuntimeFailure($e);error_log('[SUPPORT] '.$failure['code']);return $r->withJson($failure,503);}};
 $run=function($q,$r,$action,$p){$db=$q->getAttribute('support_db');try{return $r->withJson(supportDispatch($db,$q->getAttribute('support_identity'),$action,$p));}catch(InvalidArgumentException $e){if($db->inTransaction())$db->rollBack();return $r->withJson(['error'=>$e->getMessage()],422);}catch(Throwable $e){if($db->inTransaction())$db->rollBack();error_log('[SUPPORT] action '.get_class($e));return $r->withJson(['error'=>'No se pudo guardar. Actualiza y revisa los datos.'],500);}};
 foreach(['context','tickets','members','notifications','activity','report','performance','satisfaction'] as $action)$app->get('/support/'.$action,function($q,$r)use($run,$action){return $run($q,$r,$action,$q->getQueryParams());})->add($auth);
 $app->get('/support/tickets/{id:[0-9]+}',function($q,$r,$args)use($run){return $run($q,$r,'detail',$args);})->add($auth);
 foreach(['create','message','transition','assign','worklog','survey','category','grant','installation','tenant_active','notification_read','archive','restore','performance_review'] as $action)$app->post('/support/'.$action,function($q,$r)use($run,$action){return $run($q,$r,$action,$q->getParsedBody()?:[]);})->add($auth);
};
