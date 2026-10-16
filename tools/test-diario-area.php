<?php
ob_start();$sessionPath=__DIR__.'/../tmp/diario-test-sessions';if(!is_dir($sessionPath))mkdir($sessionPath,0700,true);session_save_path($sessionPath);
// Temporary tables shadow production tables on this connection. No production rows change.
require 'C:/xampp/htdocs/boticav1/config/database.php';
require 'C:/xampp/htdocs/boticav1/api/vendor/autoload.php';
$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$definition=$db->query('SHOW CREATE TABLE Seg_Usuario')->fetch(PDO::FETCH_NUM)[1];$db->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$definition));
$db->exec("INSERT INTO Seg_Usuario(Usuario,IdUsuarioPerfil,NombreUsuario,Anulado) VALUES('superadmin',1,'Superadmin fixture',0),('diario-admin',1,'Admin test',0),('diario-worker',2,'Worker test',0),('diario-other',2,'Other test',0)");
$definition=$db->query('SHOW CREATE TABLE Rhu_Empleado')->fetch(PDO::FETCH_NUM)[1];$db->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$definition));$db->exec("INSERT INTO Rhu_Empleado(IdEmpleado,Empleado,Anulado) VALUES(999901,'Employee test',0)");
$sql=str_replace('CREATE TABLE IF NOT EXISTS','CREATE TEMPORARY TABLE',file_get_contents(__DIR__.'/../integration/diario/schema.sql'));$db->exec($sql);$db->exec(str_replace('CREATE TABLE IF NOT EXISTS','CREATE TEMPORARY TABLE',file_get_contents(__DIR__.'/../integration/diario/upgrade-soporte.sql')));$db->exec(str_replace('CREATE TABLE IF NOT EXISTS','CREATE TEMPORARY TABLE',file_get_contents(__DIR__.'/../integration/diario/upgrade-operaciones.sql')));define('DIARIO_UPLOAD_DIR',__DIR__.'/../tmp/diario-upload-tests');
$app=new Slim\App(['settings'=>['displayErrorDetails'=>true]]);$app->getContainer()['db']=$db;$register=require __DIR__.'/../integration/diario/diario-api.php';$register($app);
session_id('diario-unit-'.bin2hex(random_bytes(6)));session_start();$_SESSION['diario_csrf']='fixture-token';$_SESSION['expire']=time()+600;session_write_close();
function check($test,$message){if(!$test)throw new RuntimeException($message);}
function callDiary($method,$path,$user,$body=null,$csrf='fixture-token',$uploads=[]){
 global $app;session_start();$_SESSION['user']=$user;session_write_close();
 $env=Slim\Http\Environment::mock(['REQUEST_METHOD'=>$method,'REQUEST_URI'=>'/diario/'.$path]);$q=Slim\Http\Request::createFromEnvironment($env)->withHeader('X-Diario-CSRF',$csrf);if($body!==null)$q=$q->withParsedBody($body);if($uploads)$q=$q->withUploadedFiles($uploads);$r=$app->process($q,new Slim\Http\Response());return [$r->getStatusCode(),json_decode((string)$r->getBody(),true),(string)$r->getBody(),$r];
}
try{
 $payload=['TipoTrabajo'=>'soporte','Proyecto'=>'Fixture project','Ticket'=>'SUP-42','EstimadoMin'=>60,'Objetivo'=>10,'Unidad'=>'clientes','Puntos'=>10,'Titulo'=>'Task fixture','Indicaciones'=>'Assigned instructions','Inicio'=>diarioNow(),'Fin'=>(new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->modify('+2 hours')->format('Y-m-d H:i:s'),'Responsable'=>'u:diario-worker','Repeticion'=>'diaria','Anticipacion'=>15,'Frecuencia'=>1];
 [$code,$ctx]=callDiary('GET','contexto','diario-worker');check($code===200&&!$ctx['admin']&&!isset($ctx['usuarios']),'worker metadata privacy');check($ctx['nombre']==='Worker test'&&$ctx['user']==='diario-worker'&&array_key_exists('perfil',$ctx),'session identity from authenticated account');
 [$code]=callDiary('POST','tareas','diario-worker',$payload,'bad');check($code===403,'CSRF');
 [$code]=callDiary('POST','tareas','diario-admin',array_merge($payload,['Frecuencia'=>0]));check($code===422,'zero reminder interval rejected');[$code,$created]=callDiary('POST','tareas','diario-admin',$payload);check($code===200&&$created['Id'],'admin assignment');$id=$created['Id'];
 [$code,$rows]=callDiary('GET','tareas?desde='.substr($payload['Inicio'],0,10).'&hasta='.substr($payload['Fin'],0,10),'diario-worker');check($code===200&&count($rows['tareas'])===1,'owner list');check($rows['tareas'][0]['Ticket']==='SUP-'.str_pad((string)$id,6,'0',STR_PAD_LEFT),'automatic ticket generated from unique task ID');
 [$code,$rows]=callDiary('GET','tareas?desde='.substr($payload['Inicio'],0,10).'&hasta='.substr($payload['Fin'],0,10),'diario-other');check($code===200&&count($rows['tareas'])===0,'other user isolated');
 [$code]=callDiary('POST','tareas/'.$id,'diario-other',['accion'=>'concluir','Version'=>1]);check($code===422,'cross-user mutation refused');
 [$code]=callDiary('POST','tareas/'.$id,'diario-worker',array_merge($payload,['accion'=>'editar','Version'=>1]));check($code===422,'assigned instructions protected');
 [$code]=callDiary('POST','tareas/'.$id,'diario-worker',['accion'=>'iniciar','Version'=>1]);check($code===200,'start');
 $db->exec("UPDATE Rhu_DiarioTarea SET EnCursoDesde=DATE_SUB('".diarioNow()."',INTERVAL 120 SECOND) WHERE Id=".(int)$id);
 [$code]=callDiary('POST','tareas/'.$id,'diario-worker',['accion'=>'pausar','Version'=>2]);check($code===200,'pause');$row=$db->query('SELECT * FROM Rhu_DiarioTarea WHERE Id='.(int)$id)->fetch(PDO::FETCH_ASSOC);check((int)$row['Segundos']>=120&&$row['EnCursoDesde']===null,'elapsed time accumulated');
 [$code,$result]=callDiary('POST','tareas/'.$id,'diario-worker',['accion'=>'concluir','Version'=>3,'Resultado'=>'Completed fixture','Realizado'=>8]);check($code===200&&$result['siguiente'],'complete creates recurrence');
 [$code]=callDiary('POST','tareas/'.$id,'diario-worker',['accion'=>'concluir','Version'=>3,'Resultado'=>'Completed fixture','Realizado'=>8]);check($code===422,'duplicate completion refused');check((int)$db->query('SELECT COUNT(*) FROM Rhu_DiarioTarea WHERE Anterior='.(int)$id)->fetchColumn()===1,'one recurrence only');check((float)$db->query('SELECT Objetivo FROM Rhu_DiarioTarea WHERE Anterior='.(int)$id)->fetchColumn()===10.0,'recurrence retains goal');
 [$code]=callDiary('POST','vinculo','diario-worker',['IdEmpleado'=>999901,'Usuario'=>'diario-worker']);check($code===403,'mapping admin only');
 [$code]=callDiary('POST','vinculo','diario-admin',['IdEmpleado'=>999901,'Usuario'=>'diario-worker']);check($code===200,'mapping');
 [$code,$e]=callDiary('POST','tareas','diario-admin',array_merge($payload,['Responsable'=>'e:999901']));check($code===200,'employee assignment');
 [$code,$events]=callDiary('GET','tareas/'.$e['Id'].'/eventos','diario-worker');check($code===200&&count($events)===1,'linked employee access');
 [$code]=callDiary('POST','tareas/'.$e['Id'],'diario-worker',['accion'=>'nota','Version'=>1,'Texto'=>'Progress fixture']);check($code===200,'progress note');
 [$code,$reminders]=callDiary('GET','recordatorios','diario-other');check($code===200&&count($reminders['tareas'])===0,'reminders restricted to owner');
 [$code]=callDiary('POST','tareas','diario-admin',array_merge($payload,['Fin'=>'2020-01-01T00:00']));check($code===422,'invalid date range');
 [$code]=callDiary('POST','tareas','diario-admin',array_merge($payload,['Inicio'=>'2026-02-30T08:00']));check($code===422,'invalid calendar date');

 [$code]=callDiary('POST','tareas/'.$id,'diario-worker',['accion'=>'evaluar','Version'=>4,'Calidad'=>5,'Reconocimiento'=>'Great work']);check($code===422,'workers cannot validate their own score');
 [$code]=callDiary('POST','tareas/'.$id,'diario-admin',['accion'=>'evaluar','Version'=>4,'Calidad'=>4,'Reconocimiento'=>'Good result with evidence']);check($code===200,'admin review');
 [$code,$mine]=callDiary('GET','tareas?vista=mia','diario-admin');check($code===200&&count($mine['tareas'])===0,'admin personal work view stays own');
 $personal=['Titulo'=>'Private fixture','Detalle'=>'Personal secret','Fecha'=>diarioNow(),'Repeticion'=>'diaria','Anticipacion'=>5,'Frecuencia'=>1];
 [$code,$saved]=callDiary('POST','personales','diario-worker',$personal);check($code===200,'personal reminder creation');$personalId=$saved['Id'];
 [$code,$list]=callDiary('GET','personales','diario-admin');check($code===200&&count($list['recordatorios'])===0,'personal privacy even from admin');
 [$code]=callDiary('POST','personales/'.$personalId,'diario-admin',['accion'=>'concluir','Version'=>1]);check($code===422,'admin cannot mutate worker personal reminder');
 [$code,$reminders]=callDiary('GET','recordatorios','diario-worker');check($code===200&&count(array_filter($reminders['tareas'],fn($t)=>($t['Tipo']??'')==='personal'))===1,'owner personal alerts');
 [$code,$reminders]=callDiary('GET','recordatorios','diario-admin');check($code===200&&count(array_filter($reminders['tareas'],fn($t)=>($t['Tipo']??'')==='personal'))===0,'no admin leak in alerts');
 [$code,$done]=callDiary('POST','personales/'.$personalId,'diario-worker',['accion'=>'concluir','Version'=>1]);check($code===200&&$done['siguiente'],'personal recurrence');
 [$code]=callDiary('POST','personales/'.$personalId,'diario-worker',['accion'=>'concluir','Version'=>1]);check($code===422,'personal duplicate completion');

 [$code]=callDiary('GET','dashboard','diario-worker');check($code===403,'dashboard unavailable to employees');
 [$code]=callDiary('GET','dashboard','diario-admin');check($code===403,'general dashboard reserved to named superadmin');
 [$code,$dashboard]=callDiary('GET','dashboard','superadmin');check($code===200&&$dashboard['resumen']['validadas']===1,'full dashboard summary');check(abs($dashboard['resumen']['logrados']-6.4)<0.001,'weighted dashboard goal score');
 [$code,$filtered]=callDiary('GET','dashboard?responsable=u:diario-worker&calidad=4','superadmin');check($code===200&&$filtered['resumen']['total']===1,'responsible and grade filters');
 [$code,$empty]=callDiary('GET','dashboard?desde=2030-01-01&hasta=2030-01-02','superadmin');check($code===200&&$empty['resumen']['total']===0,'date range filters');
 for($n=0;$n<105;$n++){$stmt=$db->prepare('INSERT INTO Rhu_DiarioTarea(Usuario,Titulo,Indicaciones,Inicio,Fin,Creador,Creada,Modificada) VALUES(?,?,?,?,?,?,?,?)');$stmt->execute(['diario-worker','=formula fixture','temporary',$payload['Inicio'],$payload['Fin'],'diario-admin',diarioNow(),diarioNow()]);}
 [$code,$first]=callDiary('GET','dashboard?pagina=1','superadmin');[$code,$second]=callDiary('GET','dashboard?pagina=2','superadmin');check(count($first['actividades'])===100&&$first['resumen']['total']>100&&$second['resumen']['total']===$first['resumen']['total']&&count($first['actividades'])+count($second['actividades'])===$first['resumen']['total'],'pagination preserves full summary totals');
 [$code,$unused,$csv]=callDiary('GET','reporte.csv','superadmin');check($code===200&&substr($csv,0,3)==="\xEF\xBB\xBF"&&strpos($csv,"'=formula fixture")!==false,'complete CSV with formula injection protection');
 [$code]=callDiary('GET','reporte.csv','diario-worker');check($code===403,'CSV superadmin only');

 $image=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7oQAAAAASUVORK5CYII=');$file=tempnam($sessionPath,'image');file_put_contents($file,$image);
 $upload=new Slim\Http\UploadedFile($file,'proof.png','image/png',strlen($image));
 [$code,$uploaded]=callDiary('POST','tareas/'.$e['Id'].'/adjuntos','diario-worker',null,'fixture-token',['imagen'=>$upload]);check($code===200&&$uploaded['Id'],'image upload');$attachmentId=$uploaded['Id'];
 [$code,$attachments]=callDiary('GET','tareas/'.$e['Id'].'/adjuntos','diario-worker');check($code===200&&count($attachments['adjuntos'])===1&&$attachments['adjuntos'][0]['VistaPrevia'],'attachment list and preview');
 [$code]=callDiary('GET','adjuntos/'.$attachmentId,'diario-other');check($code===422,'image cannot be read by another user');
 [$code,$unused,$bytes,$response]=callDiary('GET','adjuntos/'.$attachmentId.'?vista=1','diario-worker');check($code===200&&$bytes===$image&&$response->getHeaderLine('Content-Type')==='image/png','authenticated image content');$response->getBody()->close();
 [$code]=callDiary('POST','adjuntos/'.$attachmentId.'/eliminar','diario-worker',[]);check($code===200,'owner deletion');
 $file=tempnam($sessionPath,'svg');$svg='<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';file_put_contents($file,$svg);$upload=new Slim\Http\UploadedFile($file,'proof.svg','image/svg+xml',strlen($svg));
 [$code,$savedSvg]=callDiary('POST','tareas/'.$e['Id'].'/adjuntos','diario-worker',null,'fixture-token',['imagen'=>$upload]);check($code===200,'SVG upload');
 [$code,$unused,$bytes,$response]=callDiary('GET','adjuntos/'.$savedSvg['Id'].'?vista=1','diario-worker');check(strpos($response->getHeaderLine('Content-Disposition'),'attachment')===0&&$response->getHeaderLine('Content-Type')==='application/octet-stream','SVG is downloaded without active inline content');$response->getBody()->close();callDiary('POST','adjuntos/'.$savedSvg['Id'].'/eliminar','diario-worker',[]);
 $file=tempnam($sessionPath,'bad');file_put_contents($file,'<?php echo 123;');$upload=new Slim\Http\UploadedFile($file,'proof.png','image/png',15);[$code]=callDiary('POST','tareas/'.$e['Id'].'/adjuntos','diario-worker',null,'fixture-token',['imagen'=>$upload]);check($code===422,'executable disguised as image rejected');unlink($file);
 $file=tempnam($sessionPath,'locked');file_put_contents($file,$image);$upload=new Slim\Http\UploadedFile($file,'proof.png','image/png',strlen($image));[$code]=callDiary('POST','tareas/'.$id.'/adjuntos','diario-worker',null,'fixture-token',['imagen'=>$upload]);check($code===422,'reviewed evidence locked');unlink($file);
 [$code,$technical]=callDiary('GET','dashboard?tipo=soporte&buscar=Fixture%20project','superadmin');check($code===200&&$technical['resumen']['soporteResuelto']===1&&$technical['resumen']['estimadoMin']>=60,'support filters and work estimates');

 $key='11111111-2222-4333-8444-555555555555';$contact=['Cliente'=>'Fixture client','Canal'=>'remoto','Resultado'=>'resuelto','Detalle'=>'Fixed test issue','Clave'=>$key];
 [$code,$contactSaved]=callDiary('POST','tareas/'.$e['Id'].'/atenciones','diario-worker',$contact);check($code===200,'support contact');
 [$code,$retry]=callDiary('POST','tareas/'.$e['Id'].'/atenciones','diario-worker',$contact);check($code===200&&$retry['Id']===$contactSaved['Id'],'contact idempotency');
 [$code,$daily]=callDiary('GET','soporte/diario','diario-worker');check($code===200&&count($daily['dias'])===1&&(int)$daily['dias'][0]['Atenciones']===1&&(int)$daily['dias'][0]['CasosResueltos']===1,'daily counts');
 [$code,$daily]=callDiary('GET','soporte/diario','diario-other');check($code===200&&count($daily['dias'])===0,'private worker support counts');
 [$code]=callDiary('POST','tareas/'.$e['Id'].'/atenciones','diario-other',$contact);check($code===422,'other worker cannot register contact');
 $prefs=['titulo'=>'My work','nombres'=>['pendiente'=>'Backlog','en_curso'=>'Doing','pausada'=>'Waiting','concluida'=>'Done','cancelada'=>'Archived']];[$code]=callDiary('POST','preferencias','diario-worker',$prefs);check($code===200,'editable board headers');
 [$code,$ctx]=callDiary('GET','contexto','diario-worker');check($ctx['tablero']['nombres']['pendiente']==='Backlog','headers persist');[$code,$ctx]=callDiary('GET','contexto','diario-other');check(empty($ctx['tablero']),'headers private by user');
 foreach(['Seg_UsuarioPerfil','Seg_UsuarioModulo','Seg_UsuarioModulo_has_UsuarioPerfil'] as $table){$def=$db->query('SHOW CREATE TABLE '.$table)->fetch(PDO::FETCH_NUM)[1];$db->exec(str_replace('CREATE TABLE','CREATE TEMPORARY TABLE',$def));}
 $db->exec("INSERT INTO Seg_UsuarioPerfil(IdUsuarioPerfil,UsuarioPerfil,Anulado) VALUES(1,'ADMINISTRADOR',0),(2,'VENDEDOR',0)");require __DIR__.'/install-diario-profile.php';$profile=installDiaryProgrammerProfile($db);$again=installDiaryProgrammerProfile($db);check($profile===$again,'programmer profile created once');
 $db->prepare('INSERT INTO Seg_Usuario(Usuario,IdUsuarioPerfil,Anulado) VALUES(?,?,0)')->execute(['programmer-fixture',$profile]);[$code,$ctx]=callDiary('GET','contexto','programmer-fixture');check($code===200&&$ctx['programador']&&!$ctx['admin'],'programmer not admin');check((int)$db->query('SELECT COUNT(*) FROM Seg_UsuarioModulo_has_UsuarioPerfil WHERE IdUsuarioPerfil='.(int)$profile)->fetchColumn()===1,'programmer only diary module granted');

 [$code]=callDiary('POST','preferencias','diario-worker',array_merge($prefs,['tema'=>'uva','acento'=>'#FACC15']));check($code===200,'theme and custom accent');[$code,$ctx]=callDiary('GET','contexto','diario-worker');check($ctx['tablero']['tema']==='uva'&&$ctx['tablero']['acento']==='#FACC15','appearance persisted');
 [$code]=callDiary('POST','preferencias','diario-worker',array_merge($prefs,['acento'=>'url(javascript:alert(1))']));check($code===422,'unsafe CSS color rejected');
 $environment=['activo'=>true,'fondo'=>'#e9efff','fondoFin'=>'#d9f4f1','encabezado'=>'#2454ce','panel'=>'#ffffff'];[$code]=callDiary('POST','preferencias','diario-worker',array_merge($prefs,['entorno'=>$environment]));check($code===200,'environment colors accepted');[$code,$ctx]=callDiary('GET','contexto','diario-worker');check($ctx['tablero']['entorno']===$environment,'environment colors persisted');[$code]=callDiary('POST','preferencias','diario-worker',array_merge($prefs,['entorno'=>array_merge($environment,['panel'=>'url(evil)'])]));check($code===422,'unsafe environment color rejected');
 [$code]=callDiary('POST','preferencias','diario-worker',array_merge($prefs,['fondo'=>'data:image/svg+xml;base64,PHN2Zz48L3N2Zz4=']));check($code===422,'non-raster background rejected');
 if(function_exists('imagecreatetruecolor')){$im=imagecreatetruecolor(2,2);ob_start();imagejpeg($im);$jpeg=ob_get_clean();imagedestroy($im);$background='data:image/jpeg;base64,'.base64_encode($jpeg);[$code]=callDiary('POST','preferencias','diario-worker',array_merge($prefs,['fondo'=>$background]));check($code===200,'optimized background stored');[$code,$ctx]=callDiary('GET','contexto?ligero=1','diario-worker');check(!isset($ctx['tablero']['fondo']),'reminders use lightweight metadata');}
 [$code]=callDiary('GET','contexto','');check($code===401,'login required');
 [$a,$b]=diarioNext('2026-10-01 08:00:00','2026-10-01 09:00:00','semanal','2026-10-14 10:00:00');check($a==='2026-10-15 08:00:00'&&$b==='2026-10-15 09:00:00','weekly next future occurrence');

 $db->exec("INSERT INTO Rhu_DiarioTarea(Usuario,Titulo,Indicaciones,Inicio,Fin,Creador,Creada,Modificada) VALUES('diario-worker','OLDER BACKLOG FIXTURE','Follow up','2026-09-01 08:00:00','2026-09-02 17:00:00','diario-worker','2026-09-01','2026-09-01')");$oldId=(int)$db->lastInsertId();diarioEvent($db,$oldId,'diario-worker','nota','Progress in current month');
 [$code,$backlog]=callDiary('GET','tareas?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');check($code===200&&count(array_filter($backlog['tareas'],fn($t)=>$t['Titulo']==='OLDER BACKLOG FIXTURE'))===1,'older unfinished tasks remain visible');
 [$code,$progress]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');check($code===200&&isset($progress['diario']),'period progress endpoint');check(count(array_filter($progress['avances'],fn($event)=>$event['Texto']==='Progress in current month'))===1,'progress grouped by actual date rather than planned task dates');
 [$code,$otherProgress]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-other');check($code!==200||count(array_filter($otherProgress['avances']??[],fn($event)=>$event['Texto']==='Progress in current month'))===0,'other user cannot see task progress');

 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'archivar','Version'=>1]);check($code===422,'unfinished task cannot be archived through API');
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'concluir','Version'=>1,'Resultado'=>'Work completed','Realizado'=>0,'Evidencia'=>'']);check($code===200,'fixture finished with outcome');
 [$code,$beforeArchive]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'archivar','Version'=>2]);check($code===200,'completed task can be archived');
 [$code,$activeRows]=callDiary('GET','tareas?vista=mia','diario-worker');check(count(array_filter($activeRows['tareas'],fn($t)=>$t['Id']==$oldId))===0,'archived task hidden from active board');check(count(array_filter($activeRows['indicadores'],fn($t)=>$t['Id']==$oldId))===1,'archived task retained in performance indicators');
 [$code,$archivedRows]=callDiary('GET','tareas?vista=mia&archivadas=1','diario-worker');check(count(array_filter($archivedRows['tareas'],fn($t)=>$t['Id']==$oldId))===1,'archived task visible in archive');
 [$code,$afterArchive]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');check(array_sum(array_column($beforeArchive['diario'],'concluidas'))===array_sum(array_column($afterArchive['diario'],'concluidas')),'archiving does not remove completed work from period totals');
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'restaurar','Version'=>3]);check($code===200,'archived completed task can be restored');
 echo "PASS finished-only archive, active/archive separation, restoration and preserved performance history.\n";

 echo "PASS backlog carryover, period progress by actual event dates and owner privacy.\n";

 echo "PASS: permissions, CSRF, owner isolation, employee links, protected instructions, elapsed time, notes, dates and duplicate-safe daily/weekly recurrence. Private personal reminders, owner-only work views and admin-reviewed goals verified. Temporary tables only.\n";
}finally{if(session_status()!==PHP_SESSION_ACTIVE)session_start();session_destroy();}
