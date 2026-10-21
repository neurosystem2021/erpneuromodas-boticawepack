<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
require __DIR__.'/../integration/helpdesk/support-runtime.php';
require __DIR__.'/../integration/helpdesk/support-service.php';
require __DIR__.'/../integration/helpdesk/runtime/config-files.php';
function runtimeAssert($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function runtimeCommand(array $args) {
    $process=proc_open(array_merge([PHP_BINARY],$args),[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,null,['bypass_shell'=>true]);
    fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);
    return [proc_close($process),$out.$err];
}
$pdo=appPdoConnection();$connection=appDatabaseConfig();$suffix=bin2hex(random_bytes(5));
$erp='support_fixture_erp_'.$suffix;$schema='support_fixture_help_'.$suffix;
$fixture=sys_get_temp_dir().'/nst-support-'.$suffix;
$oldEnv=getenv('SUPPORT_RUNTIME_CONFIG');
mkdir($fixture,0700,true);
try {
    foreach ([0600=>0600,0640=>0640,0644=>0644,0777=>0644,0660=>0640] as $reference=>$expected) runtimeAssert(supportConfigAccessMode($reference)===$expected,'Unsafe config permission policy');
    $pdo->exec('CREATE DATABASE `'.$erp.'`');$pdo->exec('USE `'.$erp.'`');
    $pdo->exec("CREATE TABLE GEN_EMPRESA(IDEMPRESA INT PRIMARY KEY); INSERT INTO GEN_EMPRESA VALUES(1);
CREATE TABLE Seg_UsuarioPerfil(IdUsuarioPerfil INT PRIMARY KEY,UsuarioPerfil VARCHAR(80)); INSERT INTO Seg_UsuarioPerfil VALUES(1,'ADMIN'),(2,'VENTAS');
CREATE TABLE Seg_Usuario(Usuario VARCHAR(100) PRIMARY KEY,NombreUsuario VARCHAR(100),IdUsuarioPerfil INT,Anulado TINYINT); INSERT INTO Seg_Usuario VALUES('fixture-admin','Fixture admin',1,0),('fixture-client','Fixture client',2,0)");
    runtimeAssert(supportErpTable($pdo,'Gen_Empresa')==='`'.$pdo->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND LOWER(TABLE_NAME)='gen_empresa'")->fetchColumn().'`','ERP company case resolution');
    foreach (['config','api','tools/support/migrations'] as $dir) mkdir($fixture.'/'.$dir,0700,true);
    copy('C:/xampp/htdocs/boticav1/config/database.php',$fixture.'/config/database.php');
    $connection['dbname']=$erp;
    file_put_contents($fixture.'/config/database.local.php','<?php return '.var_export($connection,true).';');chmod($fixture.'/config/database.local.php',0600);
    foreach(['support-runtime.php','support-api.php','support-service.php','support-activity.php','support-report.php','support-performance.php','support-satisfaction.php'] as $file)copy(__DIR__.'/../integration/helpdesk/'.$file,$fixture.'/api/'.$file);
    copy(__DIR__.'/../integration/helpdesk/runtime/setup.php',$fixture.'/tools/support/setup.php');
    copy(__DIR__.'/../integration/helpdesk/runtime/config-files.php',$fixture.'/tools/support/config-files.php');
    foreach(glob(__DIR__.'/../integration/helpdesk/migrations/*.up.sql') as $file)copy($file,$fixture.'/tools/support/migrations/'.basename($file));
    // Explicit missing config must not silently fall back to the Windows runtime.
    $private=$fixture.'/config/support.local.php';putenv('SUPPORT_RUNTIME_CONFIG='.$private);
    try { supportRuntimeConfig($fixture); throw new LogicException('Missing configuration accepted'); }
    catch (SupportRuntimeException $e) { runtimeAssert($e->reason==='SUPPORT_CONFIG_MISSING','Missing config diagnostic'); }
    $script=$fixture.'/tools/support/setup.php';
    $args=[$script,'--apply','--database='.$schema,'--origin=https://support-fixture.invalid','--company=1','--admin=fixture-admin','--create-database'];
    [$code,$output]=runtimeCommand($args);runtimeAssert($code===0,'Setup failed: '.$output);
    $cfg=require $private;$pdo->exec('USE `'.$schema.'`');
    supportRuntimeVerify($pdo,$cfg);
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_schema_versions')->fetchColumn()===8,'All migrations applied');
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_categories')->fetchColumn()===15,'Categories seeded');
    $admin=supportIdentity($pdo,$cfg['local_installation'],'fixture-admin','1','Admin');runtimeAssert($admin['superadmin'],'Explicit support admin');
    $client=supportIdentity($pdo,$cfg['local_installation'],'fixture-client','1','Client');runtimeAssert(!$client['superadmin'],'Client must not inherit admin');
    $category=$pdo->query('SELECT MIN(id) FROM support_categories')->fetchColumn();
    $ticket=supportDispatch($pdo,$client,'create',['category_id'=>(int)$category,'subject'=>'Runtime fixture ticket','description'=>'Isolated setup and upgrade verification','module'=>'fixture','priority'=>'low']);
    // Idempotency must preserve a ticket, existing registration and administrator.
    [$code,$output]=runtimeCommand($args);runtimeAssert($code===0,'Repeated setup failed: '.$output);
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_tickets')->fetchColumn()===1,'Setup changed tickets');
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_installations')->fetchColumn()===1,'Repeated installation');
    $before=(int)$pdo->query('SELECT COUNT(*) FROM support_audit_logs')->fetchColumn();
    [$code,$output]=runtimeCommand([$script,'--check']);runtimeAssert($code===0,'Read-only check failed: '.$output);
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_audit_logs')->fetchColumn()===$before,'Check modified audit');
    $beforeConfig=hash_file('sha256',$private);
    [$code,$output]=runtimeCommand([$script,'--repair-config-access']);runtimeAssert($code===0,'Repair access failed: '.$output);
    runtimeAssert(hash_file('sha256',$private)===$beforeConfig,'Permission repair changed configuration contents');
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_audit_logs')->fetchColumn()===$before,'Permission repair changed database');
    [$code]=runtimeCommand([$script,'--apply','--database='.$schema,'--origin=https://support-fixture.invalid','--company=1','--admin=fixture-client']);runtimeAssert($code!==0,'Non-admin bootstrap accepted');
    $original=file_get_contents($private);$cfg['connection']=$connection;$cfg['database']=$erp;file_put_contents($private,'<?php return '.var_export($cfg,true).';');
    [$code,$output]=runtimeCommand([$script,'--check']);runtimeAssert($code!==0&&strpos($output,'SUPPORT_DATABASE_NOT_SEPARATE')!==false,'Main ERP DB must not be used for support');
    file_put_contents($private,$original);
    // Unconfigured HTTP returns useful, safe JSON instead of credentials/paths.
    putenv('SUPPORT_RUNTIME_CONFIG='.$fixture.'/missing.php');
    require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
    $app=new Slim\App();$register=require __DIR__.'/../integration/helpdesk/support-api.php';$register($app);
    // The actual signed bridge creates a remote client's numbered ticket centrally.
    putenv('SUPPORT_RUNTIME_CONFIG='.$private);
    $key=openssl_pkey_new(['private_key_type'=>OPENSSL_KEYTYPE_RSA,'private_key_bits'=>2048,'config'=>__DIR__.'/../integration/helpdesk/connector/openssl.cnf']);
    $uuid='00000000-0000-4000-8000-000000000002';
    supportQuery($pdo,'INSERT INTO support_installations(installation_uuid,origin_https,active,created_at) VALUES(?,?,1,?)',[$uuid,'https://remote-fixture.invalid',supportNow()]);$remote=(int)$pdo->lastInsertId();
    supportQuery($pdo,'INSERT INTO support_tenants(installation_id,external_company_id,created_at) VALUES(?,?,?)',[$remote,'1',supportNow()]);$remoteTenant=(int)$pdo->lastInsertId();
    supportQuery($pdo,'INSERT INTO support_installation_keys(installation_id,key_id,public_key_base64,created_at) VALUES(?,?,?,?)',[$remote,'fixture',base64_encode(openssl_pkey_get_details($key)['key']),supportNow()]);
    $raw=json_encode(['identity'=>['user'=>'remote-client','company'=>'1','name'=>'Remote fixture'],'action'=>'create','payload'=>['tenant_id'=>$admin['tenant'],'role'=>'superadmin','category_id'=>(int)$category,'subject'=>'Remote fixture ticket','description'=>'Signed client to central request','module'=>'POS','priority'=>'medium']]);
    $time=(string)time();$nonce=bin2hex(random_bytes(32));openssl_sign("POST\n/support/bridge\n".$time."\n".$nonce."\n".hash('sha256',$raw),$signature,$key,OPENSSL_ALGO_SHA256);
    $stream=fopen('php://temp','r+');fwrite($stream,$raw);rewind($stream);
    $signedRequest=Slim\Http\Request::createFromEnvironment(Slim\Http\Environment::mock(['REQUEST_METHOD'=>'POST','REQUEST_URI'=>'/support/bridge']))->withBody(new Slim\Http\Body($stream));
    foreach(['installation'=>$uuid,'key'=>'fixture','time'=>$time,'nonce'=>$nonce,'signature'=>base64_encode($signature)] as $header=>$value)$signedRequest=$signedRequest->withHeader('X-Support-'.$header,$value);
    $response=$app->process($signedRequest,new Slim\Http\Response());$remoteTicket=json_decode((string)$response->getBody(),true);
    runtimeAssert($response->getStatusCode()===200&&strpos($remoteTicket['code']??'','SOP-')===0,'Signed bridge did not create a numbered ticket');
    $saved=supportQuery($pdo,'SELECT tenant_id FROM support_tickets WHERE id=?',[$remoteTicket['id']])->fetchColumn();runtimeAssert((int)$saved===$remoteTenant,'Bridge trusted forged tenant');
    runtimeAssert(supportDispatch($pdo,$admin,'tickets')['total']===2&&supportDispatch($pdo,$client,'tickets')['total']===1,'Central queue or client isolation');
    $signedRequest->getBody()->rewind();$response=$app->process($signedRequest,new Slim\Http\Response());runtimeAssert($response->getStatusCode()===422,'Signed replay accepted');
    runtimeAssert((int)$pdo->query('SELECT COUNT(*) FROM support_tickets')->fetchColumn()===2,'Replay duplicated a ticket');
    putenv('SUPPORT_RUNTIME_CONFIG='.$fixture.'/missing.php');
    $request=Slim\Http\Request::createFromEnvironment(Slim\Http\Environment::mock(['REQUEST_METHOD'=>'POST','REQUEST_URI'=>'/support/bridge']));
    $response=$app->process($request,new Slim\Http\Response());
    // Bridge failure is intentionally opaque to unauthenticated clients.
    runtimeAssert($response->getStatusCode()>=400&&strpos((string)$response->getBody(),$fixture)===false,'Safe bridge error');
    session_save_path($fixture);session_id('support-runtime-'.$suffix);session_start();$_SESSION['user']='superadmin';$_SESSION['expire']=time()+300;session_write_close();
    $request=Slim\Http\Request::createFromEnvironment(Slim\Http\Environment::mock(['REQUEST_METHOD'=>'GET','REQUEST_URI'=>'/support/context']));
    $response=$app->process($request,new Slim\Http\Response());$json=json_decode((string)$response->getBody(),true);
    runtimeAssert($response->getStatusCode()===503&&($json['code']??'')==='SUPPORT_CONFIG_MISSING','Authenticated missing config diagnostic');
    session_start();session_destroy();
    $failure=supportRuntimeFailure(new RuntimeException('PRIVATE_FIXTURE_PASSWORD'));
    runtimeAssert(strpos(json_encode($failure),'PRIVATE_FIXTURE_PASSWORD')===false,'Private error disclosure');
    echo "PASS portable configuration, separate database, 8 migrations, case mapping, admin validation, ticket creation, idempotent setup, read-only check, signed HTTP bridge, central queue, tenant isolation, replay rejection and safe errors. Isolated fixtures only.\n";
} finally {
    putenv($oldEnv===false?'SUPPORT_RUNTIME_CONFIG':'SUPPORT_RUNTIME_CONFIG='.$oldEnv);
    if($pdo->inTransaction())$pdo->rollBack();
    foreach([$schema,$erp] as $database)$pdo->exec('DROP DATABASE IF EXISTS `'.$database.'`');
    $iterator=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixture,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);
    foreach($iterator as $entry) { if($entry->isDir())rmdir($entry->getPathname());else unlink($entry->getPathname()); }
    rmdir($fixture);
}
