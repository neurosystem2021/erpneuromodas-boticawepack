from pathlib import Path
p=Path('tools/test-support-area.php');s=p.read_text(encoding='utf-8');marker='echo "PASS urgent read/accept lifecycle'
extra="""
$doneTicket=supportDispatch($db,$a,'create',$payload);supportDispatch($db,$tech,'assign',['id'=>$doneTicket['id'],'technician'=>$tech['principal']]);$done=supportDispatch($db,$tech,'detail',['id'=>$doneTicket['id']]);
denied($db,function()use($db,$tech,$done){supportDispatch($db,$tech,'archive',['id'=>$done['ticket']['id'],'version'=>$done['ticket']['version']]);});
supportDispatch($db,$tech,'worklog',['id'=>$doneTicket['id'],'minutes'=>15,'description'=>'Archive lifecycle fixture']);supportDispatch($db,$tech,'transition',['id'=>$doneTicket['id'],'version'=>$done['ticket']['version'],'status'=>'resolved','reason'=>'Solution delivered']);$done=supportDispatch($db,$tech,'detail',['id'=>$doneTicket['id']]);
denied($db,function()use($db,$tech,$done){supportDispatch($db,$tech,'archive',['id'=>$done['ticket']['id'],'version'=>$done['ticket']['version']]);});
supportDispatch($db,$a,'survey',['id'=>$doneTicket['id'],'solved'=>true,'speed'=>5,'treatment'=>4,'solution'=>5,'comment'=>'Good attention']);supportDispatch($db,$a,'transition',['id'=>$doneTicket['id'],'version'=>$done['ticket']['version'],'status'=>'closed','reason'=>'Customer confirmed']);$done=supportDispatch($db,$tech,'detail',['id'=>$doneTicket['id']]);$doneVersion=$done['ticket']['version'];
$today=substr(supportNow(),0,10);$before=supportActivity($db,$owner,['from'=>$today,'to'=>$today,'mode'=>'team']);
supportDispatch($db,$tech,'archive',['id'=>$doneTicket['id'],'version'=>$doneVersion]);$archived=supportDispatch($db,$tech,'detail',['id'=>$doneTicket['id']]);ok(!empty($archived['ticket']['archived_at']),'completed ticket archived');ok(count(array_filter(supportDispatch($db,$tech,'tickets')['tickets'],fn($t)=>$t['id']==$doneTicket['id']))===0,'archive hidden from active board');ok(count(array_filter(supportDispatch($db,$tech,'tickets',['archived'=>'only'])['tickets'],fn($t)=>$t['id']==$doneTicket['id']))===1,'archive remains queryable');
$after=supportActivity($db,$owner,['from'=>$today,'to'=>$today,'mode'=>'team']);ok($before['minutos']===$after['minutos'] && $before['diario']===$after['diario'],'archive preserves historical productivity');
supportDispatch($db,$tech,'restore',['id'=>$doneTicket['id'],'version'=>$archived['ticket']['version']]);$restored=supportDispatch($db,$tech,'detail',['id'=>$doneTicket['id']]);ok($restored['ticket']['archived_at']===null,'completed ticket restored');supportDispatch($db,$tech,'archive',['id'=>$doneTicket['id'],'version'=>$restored['ticket']['version']]);$archived=supportDispatch($db,$a,'detail',['id'=>$doneTicket['id']]);supportDispatch($db,$a,'transition',['id'=>$doneTicket['id'],'version'=>$archived['ticket']['version'],'status'=>'reopened','reason'=>'Customer needs follow up']);$reopened=supportDispatch($db,$a,'detail',['id'=>$doneTicket['id']]);ok($reopened['ticket']['archived_at']===null,'reopening automatically returns ticket to active work');
supportDispatch($db,$owner,'assign',['id'=>$doneTicket['id'],'technician'=>$otherTech['principal']]);$ratings=supportRatings($db,$tech,$today.' 00:00:00',$today.' 23:59:59',true,'');ok(count(array_filter($ratings['detalle'],fn($r)=>$r['code']===$doneTicket['code'] && $r['usuario']==='tech'))===1,'rating remains attached to technician at survey time');
echo "PASS server-side archive guards, customer confirmation, restoration, reopen, unchanged work metrics and technician rating attribution.\\n";
"""
if marker not in s:raise RuntimeError('Support area test marker missing')
s=s.replace(marker,extra+'\n'+marker,1);p.write_text(s,encoding='utf-8')
p=Path('tools/test-diario-area.php');s=p.read_text(encoding='utf-8');marker=' echo "PASS backlog carryover'
extra="""
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'archivar','Version'=>1]);check($code===422,'unfinished task cannot be archived through API');
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'concluir','Version'=>1,'Resultado'=>'Work completed','Realizado'=>0,'Evidencia'=>'']);check($code===200,'fixture finished with outcome');
 [$code,$beforeArchive]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'archivar','Version'=>2]);check($code===200,'completed task can be archived');
 [$code,$activeRows]=callDiary('GET','tareas?vista=mia','diario-worker');check(count(array_filter($activeRows['tareas'],fn($t)=>$t['Id']==$oldId))===0,'archived task hidden from active board');
 [$code,$archivedRows]=callDiary('GET','tareas?vista=mia&archivadas=1','diario-worker');check(count(array_filter($archivedRows['tareas'],fn($t)=>$t['Id']==$oldId))===1,'archived task visible in archive');
 [$code,$afterArchive]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');check(array_sum(array_column($beforeArchive['diario'],'concluidas'))===array_sum(array_column($afterArchive['diario'],'concluidas')),'archiving does not remove completed work from period totals');
 [$code]=callDiary('POST','tareas/'.$oldId,'diario-worker',['accion'=>'restaurar','Version'=>3]);check($code===200,'archived completed task can be restored');
 echo "PASS finished-only archive, active/archive separation, restoration and preserved performance history.\\n";
"""
if marker not in s:raise RuntimeError('Diary area test marker missing')
s=s.replace(marker,extra+'\n'+marker,1);p.write_text(s,encoding='utf-8')
