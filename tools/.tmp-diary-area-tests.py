from pathlib import Path
s=Path('tools/test-diario.php').read_text(encoding='utf-8-sig')
marker=' echo "PASS: permissions'
extra="""
 $db->exec("INSERT INTO Rhu_DiarioTarea(Usuario,Titulo,Indicaciones,Inicio,Fin,Creador,Creada,Modificada) VALUES('diario-worker','OLDER BACKLOG FIXTURE','Follow up','2026-09-01 08:00:00','2026-09-02 17:00:00','diario-worker','2026-09-01','2026-09-01')");$oldId=(int)$db->lastInsertId();diarioEvent($db,$oldId,'diario-worker','nota','Progress in current month');
 [$code,$backlog]=callDiary('GET','tareas?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');check($code===200&&count(array_filter($backlog['tareas'],fn($t)=>$t['Titulo']==='OLDER BACKLOG FIXTURE'))===1,'older unfinished tasks remain visible');
 [$code,$progress]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-worker');check($code===200&&isset($progress['diario']),'period progress endpoint');check(count(array_filter($progress['avances'],fn($event)=>$event['Texto']==='Progress in current month'))===1,'progress grouped by actual date rather than planned task dates');
 [$code,$otherProgress]=callDiary('GET','avances?desde=2026-10-01&hasta=2026-10-31&vista=mia','diario-other');check($code!==200||count(array_filter($otherProgress['avances']??[],fn($event)=>$event['Texto']==='Progress in current month'))===0,'other user cannot see task progress');
 echo "PASS backlog carryover, period progress by actual event dates and owner privacy.\\n";
"""
if marker not in s:raise RuntimeError('Diary test marker missing')
s=s.replace(marker,extra+'\n'+marker,1);Path('tools/test-diario-area.php').write_text(s,encoding='utf-8')
