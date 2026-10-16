from pathlib import Path
s=Path('tools/test-support-service.php').read_text(encoding='utf-8-sig')
marker='echo "PASS support tenant isolation'
extra="""
$urgentPayload=$payload;$urgentPayload['priority']='critical';$urgent=supportDispatch($db,$a,'create',$urgentPayload);
ok(supportUrgentQueue($db,$tech)['total']===1,'critical queue independent from notifications');
ok(supportUrgentQueue($db,$a)['total']===0,'customer never receives staff urgent queue');
supportDispatch($db,$tech,'notification_read',['id'=>$urgent['id']]);ok(supportUrgentQueue($db,$tech)['total']===1,'reading notice is not acceptance');
supportDispatch($db,$owner,'assign',['id'=>$urgent['id'],'technician'=>$tech['principal']]);ok(supportUrgentQueue($db,$tech)['total']===1,'supervisor assignment still awaits acceptance');
$current=supportDispatch($db,$tech,'detail',['id'=>$urgent['id']]);supportDispatch($db,$tech,'transition',['id'=>$urgent['id'],'version'=>$current['ticket']['version'],'status'=>'in_progress','reason'=>'Accepted urgent request']);ok(supportUrgentQueue($db,$tech)['total']===0,'acceptance stops repeated reminder');
supportQuery($db,'INSERT INTO support_worklogs(tenant_id,ticket_id,technician_id,started_at,ended_at,description) VALUES(?,?,?,?,?,?)',[1,$urgent['id'],$tech['principal'],'2026-10-01 23:45:00','2026-10-02 00:15:00','Cross-midnight work fixture']);
$area=supportActivity($db,$tech,['from'=>'2026-10-01','to'=>'2026-10-02','mode'=>'mine']);ok($area['minutos']===30.0,'period support minutes');ok($area['diario'][0]['minutos']===15 && $area['diario'][1]['minutos']===15,'work split at Lima midnight');ok(count($area['trabajos'])===1,'work evidence exposed to author');
supportDispatch($db,$owner,'assign',['id'=>$urgent['id'],'technician'=>$otherTech['principal']]);$area=supportActivity($db,$tech,['from'=>'2026-10-01','to'=>'2026-10-02','mode'=>'mine']);ok($area['minutos']===30.0,'historical work survives reassignment');
$clientArea=supportActivity($db,$a,['from'=>'2026-10-01','to'=>'2026-10-02','mode'=>'team']);ok(count($clientArea['trabajos'])===0,'private support work not exposed to requester');
$mixed=supportIdentity($db,1,'mixed-area','1','Mixed scope');supportQuery($db,"INSERT INTO support_memberships(tenant_id,principal_id,role) VALUES(1,?,'technician'),(2,?,'company_admin')",[$mixed['principal'],$mixed['principal']]);supportQuery($db,'INSERT INTO support_worklogs(tenant_id,ticket_id,technician_id,started_at,ended_at,description) VALUES(?,?,?,?,?,?)',[2,$tb['id'],$tech['principal'],'2026-10-02 10:00:00','2026-10-02 10:30:00','PRIVATE B WORK']);$mixedArea=supportActivity($db,$mixed,['from'=>'2026-10-01','to'=>'2026-10-02','mode'=>'team']);ok(count($mixedArea['trabajos'])===0,'staff status in one tenant cannot reveal another tenant internal work');
denied($db,function()use($db,$tech){supportActivity($db,$tech,['from'=>'2026-02-30','to'=>'2026-10-02']);});
echo "PASS urgent read/accept lifecycle, customer privacy, daily/month work summaries, midnight allocation and reassignment history.\\n";
"""
if marker not in s:raise RuntimeError('Service test boundary missing')
s=s.replace(marker,extra+'\n'+marker,1)
Path('tools/test-support-area.php').write_text(s,encoding='utf-8')
