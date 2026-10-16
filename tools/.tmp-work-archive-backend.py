from pathlib import Path
p=Path('integration/diario/diario-api.php');s=p.read_text(encoding='utf-8')
needle="  if(!empty($p['responsable'])&&$i['admin'])"
insert="  $archived=(string)($p['archivadas']??'0')==='1';$sql.=' AND COALESCE(t.Archivada,0)='.($archived?'1':'0');\n"
s=s.replace(needle,insert+needle,1)
old="WHERE '.$scope.' AND (t.Estado IN"
s=s.replace(old,"WHERE '.$scope.' AND ((COALESCE(t.Archivada,0)=1 AND ?=1) OR t.Estado IN",1)
s=s.replace("$params=array_merge($params,[$to,$from,$from,$to,$from,$to]);", "$params=array_merge($params,[(string)($p['archivadas']??'0')==='1'?1:0,$to,$from,$from,$to,$from,$to]);",1)
needle="  elseif($action==='nota')"
block="""  elseif(in_array($action,['archivar','restaurar'],true)){
   if($t['Estado']!=='concluida')throw new InvalidArgumentException('Solo se pueden archivar tareas concluidas.');
   $archived=$action==='archivar'?1:0;
   $s=$db->prepare('UPDATE Rhu_DiarioTarea SET Archivada=?,ArchivadaPor=?,ArchivadaFecha=?,Version=Version+1,Modificada=? WHERE Id=?');$s->execute([$archived,$archived?$i['user']:null,$archived?$now:null,$now,$id]);diarioEvent($db,$id,$i['user'],$action,$archived?'Tarea concluida archivada; historial conservado':'Tarea restaurada del archivo');
  }
"""
s=s.replace(needle,block+needle,1);p.write_text(s,encoding='utf-8')
p=Path('integration/helpdesk/support-api.php');s=p.read_text(encoding='utf-8').replace("'notification_read'] as $action", "'notification_read','archive','restore'] as $action");p.write_text(s,encoding='utf-8')
p=Path('integration/helpdesk/support-service.php');s=p.read_text(encoding='utf-8')
s=s.replace("['detail','message','transition','assign','worklog','survey']", "['detail','message','transition','assign','worklog','survey','archive','restore']",1)
needle="  if($action==='message'){"
block="""  if(in_array($action,['archive','restore'],true)){
   if(!$staff||($role==='technician'&&(int)$t['technician_id']!==$i['principal']))throw new InvalidArgumentException('Solo el responsable o supervisor puede archivar.');
   if($t['status']!=='closed')throw new InvalidArgumentException('Solo se archivan tickets cerrados y confirmados por el cliente.');
   if((int)($p['version']??0)!==(int)$t['version'])throw new InvalidArgumentException('El ticket cambió. Actualiza antes de archivar.');
   $archived=$action==='archive';supportQuery($db,'UPDATE support_tickets SET archived_at=?,archived_by=?,version=version+1,updated_at=? WHERE id=?',[$archived?$now:null,$archived?$i['principal']:null,$now,$t['id']]);supportAudit($db,$j,$action,$t['id']);
  }elseif($action==='message'){
"""
if needle not in s:raise RuntimeError('Support action boundary missing')
s=s.replace(needle,block,1)
needle="  foreach(['status','priority','category_id','tenant_id','technician_id']"
insert="  $archive=$p['archived']??'active';if(!in_array($archive,['active','only','all'],true))throw new InvalidArgumentException('Filtro de archivo inválido.');if($archive!=='all')$where[]='t.archived_at IS '.($archive==='only'?'NOT ':'').'NULL';\n"
s=s.replace(needle,insert+needle,1)
needle="supportAudit($db,$j,'status_changed'"
s=s.replace(needle,"if($next==='reopened')supportQuery($db,'UPDATE support_tickets SET archived_at=NULL,archived_by=NULL WHERE id=?',[$t['id']]);"+needle,1)
p.write_text(s,encoding='utf-8')
