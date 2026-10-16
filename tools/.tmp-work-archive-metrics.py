from pathlib import Path
p=Path('integration/diario/diario-api.php');s=p.read_text(encoding='utf-8')
s=s.replace("$params=array_merge($params,[(string)($p['archivadas']??'0')==='1'?1:0,$to,$from,$from,$to,$from,$to]);", "$archiveParam=count($params);$params=array_merge($params,[(string)($p['archivadas']??'0')==='1'?1:0,$to,$from,$from,$to,$from,$to]);",1)
old="return $r->withJson(['tareas'=>$rows,'limitado'=>$truncated,'ahora'=>diarioNow()]);"
new="""$reportSql=preg_replace('/ AND COALESCE\\(t\\.Archivada,0\\)=[01]/','',$sql);$reportParams=$params;$reportParams[$archiveParam]=0;
  $s=$this->db->prepare($reportSql.' ORDER BY t.Inicio DESC,t.Id DESC LIMIT 2001');$s->execute($reportParams);$reportRows=$s->fetchAll(PDO::FETCH_ASSOC);if(count($reportRows)>2000){array_pop($reportRows);$truncated=true;}
  return $r->withJson(['tareas'=>$rows,'indicadores'=>$reportRows,'limitado'=>$truncated,'ahora'=>diarioNow()]);"""
if old not in s:raise RuntimeError('Diary response marker missing')
s=s.replace(old,new,1);p.write_text(s,encoding='utf-8')
p=Path('src/views/DiarioActividades.vue');s=p.read_text(encoding='utf-8')
s=s.replace(':tasks="tasks" :periodo=', ':tasks="tasks" :all-tasks="reportTasks" :periodo=',1)
s=s.replace('tasks:[],limited:false','tasks:[],reportTasks:[],limited:false',1)
s=s.replace('completionRate(){const n=this.tasks.filter', 'completionRate(){const n=this.reportTasks.filter',1).replace('Math.round(this.completed.length*100/n)',"Math.round(this.reportTasks.filter(t=>t.Estado==='concluida').length*100/n)",1)
s=s.replace('totalSeconds(){return this.tasks.reduce', 'totalSeconds(){return this.reportTasks.reduce',1)
s=s.replace('team(){return productivity(this.tasks,this.now)}', 'team(){return productivity(this.reportTasks,this.now)}',1)
s=s.replace("this.tasks=r.tareas;this.limited=r.limitado;", "this.tasks=r.tareas;this.reportTasks=r.indicadores||r.tareas;this.limited=r.limitado;",1)
s=s.replace('<span>Actividades</span><strong>{{tasks.length}}</strong>', '<span>Actividades del período</span><strong>{{reportTasks.length}}</strong>',1)
s=s.replace('<span>Concluidas</span><strong>{{completed.length}}</strong>', '<span>Concluidas, incluidas archivadas</span><strong>{{reportTasks.filter(t=>t.Estado===\'concluida\').length}}</strong>',1)
s=s.replace('<diario-rendimiento v-if="workspace===\'mia\'" :tasks="tasks"', '<diario-rendimiento v-if="workspace===\'mia\'" :tasks="reportTasks"',1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/DiarioArea.vue');s=p.read_text(encoding='utf-8')
s=s.replace('props:{tasks:{type:Array,default:()=>[]},', 'props:{allTasks:{type:Array,default:null},tasks:{type:Array,default:()=>[]},',1)
s=s.replace('activeTasks(){return this.tasks.filter', 'activeTasks(){return (this.allTasks||this.tasks).filter',1)
s=s.replace('rating:t.Calidad,archived:', 'rating:Number(t.Calidad)>0?Number(t.Calidad):null,archived:',1)
p.write_text(s,encoding='utf-8')
p=Path('tools/test-diario-area.php');s=p.read_text(encoding='utf-8');needle="check(count(array_filter($activeRows['tareas'],fn($t)=>$t['Id']==$oldId))===0,'archived task hidden from active board');"
s=s.replace(needle,needle+"check(count(array_filter($activeRows['indicadores'],fn($t)=>$t['Id']==$oldId))===1,'archived task retained in performance indicators');",1);p.write_text(s,encoding='utf-8')
