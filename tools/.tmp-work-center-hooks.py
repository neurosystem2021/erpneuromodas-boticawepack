from pathlib import Path
p=Path('integration/helpdesk/support-service.php');s=p.read_text(encoding='utf-8-sig');s=s.replace('<?php','<?php\nrequire_once __DIR__.\'/support-activity.php\';',1)
s=s.replace(" if($action==='context'){", " if($action==='activity')return supportActivity($db,$i,$p);\n if($action==='context'){",1)
s=s.replace("return ['unread'=>$count,'notifications'=>$rows];", "$urgent=supportUrgentQueue($db,$i);return ['unread'=>$count,'notifications'=>$rows,'urgent_tickets'=>$urgent['tickets'],'urgent_total'=>$urgent['total']];",1);p.write_text(s,encoding='utf-8')
p=Path('integration/helpdesk/support-api.php');s=p.read_text(encoding='utf-8-sig').replace("['context','tickets','members','notifications']","['context','tickets','members','notifications','activity']");p.write_text(s,encoding='utf-8')
p=Path('integration/diario/diario-api.php');s=p.read_text(encoding='utf-8-sig').replace("require_once __DIR__.'/diario-personal.php';", "require_once __DIR__.'/diario-personal.php';\nrequire_once __DIR__.'/diario-avances.php';",1);anchor=" $app->get('/diario/tareas',"
s=s.replace(anchor," $app->get('/diario/avances',function($q,$r){return $r->withJson(diarioAvances($this->db,$q->getAttribute('diario'),$q->getQueryParams()));})->add($auth);\n"+anchor,1);p.write_text(s,encoding='utf-8')
