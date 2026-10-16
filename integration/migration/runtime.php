<?php
require_once __DIR__.'/Engine.php';
function neuroMigrationWebRoot(){return realpath($_SERVER['DOCUMENT_ROOT']??'')?:realpath(dirname(__DIR__,2));}
function neuroMigrationConfig(){
 $path=getenv('NEURO_MIGRATION_CONFIG');
 $web=neuroMigrationWebRoot();$private=getenv('NEURO_PRIVATE_DIR');
 $pointers=$private?[$private.'/migration/active-profile.txt']:[];
 if($web){$pointers[]=dirname($web).'/neuro-private/migration/active-profile.txt';$pointers[]=dirname($web,2).'/neuro-private/migration/active-profile.txt';}
 foreach($pointers as $pointer)if(!$path&&is_file($pointer))$path=trim(file_get_contents($pointer));
 if(!$path||!is_file($path))throw new RuntimeException('Configure un perfil privado del migrador.');
 $real=realpath($path);
 if($web&&strpos(strtolower(str_replace('\\','/',$real)),strtolower(str_replace('\\','/',$web)).'/')===0)throw new RuntimeException('El perfil debe estar fuera del directorio web.');
 $c=json_decode(file_get_contents($real),true,512,JSON_THROW_ON_ERROR);
 $protected=['nst','fact_nst'];if(function_exists('appDatabaseConfig'))$protected[]=appDatabaseConfig()['dbname'];
 if(($c['mode']??'')!=='isolated_copy'||empty($c['target']['database'])||in_array(strtolower($c['target']['database']),array_map('strtolower',array_merge($protected,$c['forbidden_databases']??[])),true)||!preg_match('/^[A-Za-z0-9_]+$/',$c['target']['database']))throw new RuntimeException('Solo se permite una copia aislada autorizada.');
 return $c;
}
function neuroMigrationPrivateFile($path){$real=realpath($path);if(!$real||!is_file($real))throw new RuntimeException('Falta un archivo privado.');$web=strtolower(str_replace('\\','/',neuroMigrationWebRoot()));if($web&&strpos(strtolower(str_replace('\\','/',$real)),$web.'/')===0)throw new RuntimeException('Archivo privado dentro del directorio web.');return $real;}
function neuroMigrationEngine($c){
 $t=$c['target'];$db=new PDO('mysql:host='.($t['host']??'127.0.0.1').';port='.(int)($t['port']??3306).';dbname='.$t['database'].';charset=utf8mb4',$t['user'],$t['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
 $actual=$db->query('SELECT DATABASE()')->fetchColumn();if($actual!==$t['database']||in_array(strtolower($actual),['nst','fact_nst'],true))throw new RuntimeException('Destino no autorizado.');
 $snapshotPath=neuroMigrationPrivateFile($c['snapshot']);if(!preg_match('/^[a-f0-9]{64}$/',$c['snapshot_sha256']??'')||!hash_equals($c['snapshot_sha256'],hash_file('sha256',$snapshotPath)))throw new RuntimeException('Huella del origen no verificada.');$snapshot=json_decode(file_get_contents($snapshotPath),true,512,JSON_THROW_ON_ERROR);
 $guard=function()use($c,$actual){
  if(empty($c['enable_copy_writes']))throw new RuntimeException('Escritura en copia deshabilitada.');
  $m=json_decode(file_get_contents(neuroMigrationPrivateFile($c['backup_manifest'])),true,512,JSON_THROW_ON_ERROR);
  $backup=neuroMigrationPrivateFile($m['backup_file']??'');foreach($m['physical_files']??[] as $f)if(!hash_equals($f['sha256']??'',hash_file('sha256',neuroMigrationPrivateFile($f['backup_file']??''))))throw new RuntimeException('Respaldo fisico no verificado.');
  if(($m['restored_database']??'')!==$actual||empty($m['restore_verified'])||empty($m['authorized_copy'])||!hash_equals($m['sha256']??'',hash_file('sha256',$backup)))throw new RuntimeException('Respaldo/restauracion no verificados.');
 };
 return new NeuroMigration($db,$snapshot,$guard);
}
function neuroRegisterMigration($app,$auth){
 $app->map(['GET','POST'],'/ali/migracion',function($q,$r){
  if(strcasecmp(trim((string)$q->getAttribute('ali_user')),'superadmin')!==0||empty(($q->getAttribute('ali_pos')??[])['admin']))return $r->withJson(['error'=>'Acceso exclusivo para superadmin.'],403);
  $actor=$q->getAttribute('ali_user');$p=$q->getMethod()==='GET'?$q->getQueryParams():($q->getParsedBody()?:[]);$action=$p['action']??'info';
  if($q->getMethod()==='GET'&&!in_array($action,['info','compare','preview','history','status','report','test_source'],true))return $r->withJson(['error'=>'Metodo no permitido.'],405);
  try{ini_set('memory_limit','512M');$c=neuroMigrationConfig();$engine=neuroMigrationEngine($c);
   if($action==='info')return $r->withJson(['configured'=>true,'mode'=>'isolated_copy','target'=>$c['target']['database'],'modules'=>NeuroMigration::modules(),'writes_enabled'=>!empty($c['enable_copy_writes'])]);
   if($action==='compare')return $r->withJson($engine->compare());
   if($action==='test_source'){
    $source=$c['source']??[];if(($source['database']??'')!=='fact_nst')throw new RuntimeException('Perfil de origen no configurado.');
    $origin=new PDO('mysql:host='.($source['host']??'127.0.0.1').';port='.(int)($source['port']??3306).';dbname=fact_nst;charset=utf8mb4',$source['user'],$source['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $origin->exec('SET TRANSACTION READ ONLY');$origin->beginTransaction();try{$name=$origin->query('SELECT DATABASE()')->fetchColumn();$count=$origin->query('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE()')->fetchColumn();return $r->withJson(['connected'=>true,'database'=>$name,'tables'=>(int)$count,'read_only'=>true]);}finally{$origin->rollBack();}
   }
   if($action==='preview')return $r->withJson($engine->preview($p['entity']??'',(int)($p['page']??1)));
   if($action==='simulate'){$plan=$engine->simulate($p['modules']??[], $p['transforms']??[]);$id=$engine->create($plan,$actor);return $r->withJson($engine->status($id));}
   if($action==='history')return $r->withJson(['runs'=>$engine->history()]);
   $id=$p['id']??'';if(!preg_match('/^[a-f0-9]{32}$/',$id))throw new InvalidArgumentException('Ejecucion invalida.');
   if($action==='status')return $r->withJson($engine->status($id));
   if($action==='report')return $r->withJson($engine->report($id))->withHeader('Content-Disposition','attachment; filename="migracion-'.$id.'.json"');
   if($action==='justify'){$engine->justify($id,$p['code']??'',$p['reason']??'',$actor);return $r->withJson($engine->status($id));}
   if($action==='approve'){$engine->approve($id,$actor,$p['plan_hash']??'');return $r->withJson($engine->status($id));}
   if($action==='batch')return $r->withJson($engine->batch($id,$actor,(int)($p['cursor']??0)));
   if($action==='rollback')return $r->withJson($engine->rollback($id,$actor));
   throw new InvalidArgumentException('Accion invalida.');
  }catch(Throwable $e){if($action==='info')return $r->withJson(['configured'=>false,'mode'=>'isolated_copy','modules'=>NeuroMigration::modules(),'writes_enabled'=>false,'message'=>'Configure en el servidor el perfil privado y la copia restaurada.']);$code=$e instanceof InvalidArgumentException?422:409;return $r->withJson(['error'=>'Operacion bloqueada. Revise perfil privado, respaldo, estado e incidencias.','code'=>$e instanceof PDOException?'database_unavailable':'migration_guard'], $code);}
 })->add($auth);
}
