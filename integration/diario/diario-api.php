<?php
require_once __DIR__.'/diario-personal.php';
require_once __DIR__.'/diario-avances.php';
require_once __DIR__.'/diario-dashboard.php';
require_once __DIR__.'/diario-adjuntos.php';
require_once __DIR__.'/diario-operaciones.php';
// Activity ownership always comes from the authenticated ERP session.
function diarioNow(){return (new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->format('Y-m-d H:i:s');}
function diarioDate($value){
 $s=str_replace('T',' ',(string)$value);if(strlen($s)===16)$s.=':00';
 $d=DateTimeImmutable::createFromFormat('!Y-m-d H:i:s',$s,new DateTimeZone('America/Lima'));
 if(!$d||$d->format('Y-m-d H:i:s')!==$s)throw new InvalidArgumentException('Ingresa una fecha y hora válidas.');return $s;
}
function diarioText($value,$max,$required=false){if(!is_string($value)||mb_strlen($value)>$max||($required&&trim($value)===''))throw new InvalidArgumentException('Revisa el texto y su longitud.');return trim($value);}
function diarioScope($identity){return $identity['admin']?['1=1',[]]:['(t.Usuario=? OR t.IdEmpleado=?)',[$identity['user'],(int)($identity['employee']??0)]];}
function diarioGoal($p,$admin,$old=null){
 $goal=$p['Objetivo']??($old['Objetivo']??0);$points=$admin?($p['Puntos']??($old['Puntos']??1)):($old['Puntos']??1);
 if(!is_numeric($goal)||!is_finite((float)$goal)||(float)$goal<0||(float)$goal>9999999999||filter_var($points,FILTER_VALIDATE_INT)===false||$points<1||$points>100)throw new InvalidArgumentException('Meta inválida o peso fuera del rango 1 a 100.');
 return [round((float)$goal,2),diarioText($p['Unidad']??($old['Unidad']??''),60), (int)$points];
}
function diarioWorkFields($db,$id,$p,$old=[]){
 $type=$p['TipoTrabajo']??($old['TipoTrabajo']??'general');$estimate=filter_var($p['EstimadoMin']??($old['EstimadoMin']??0),FILTER_VALIDATE_INT);
 if(!in_array($type,['general','programacion','soporte','mantenimiento','reunion'],true)||$estimate===false||$estimate<0||$estimate>525600)throw new InvalidArgumentException('Revisa el tipo de trabajo y el tiempo estimado.');
 $ticket=$old['Ticket']??'';if($ticket==='')$ticket=(['soporte'=>'SUP','programacion'=>'DEV','mantenimiento'=>'MNT','reunion'=>'REU'][$type]??'ACT').'-'.str_pad((string)$id,6,'0',STR_PAD_LEFT);
 $s=$db->prepare('UPDATE Rhu_DiarioTarea SET TipoTrabajo=?,Proyecto=?,Ticket=?,EstimadoMin=? WHERE Id=?');$s->execute([$type,diarioText($p['Proyecto']??($old['Proyecto']??''),180),$ticket,$estimate,$id]);
}
function diarioNext($start,$end,$repeat,$now){
 $zone=new DateTimeZone('America/Lima');$s=new DateTimeImmutable($start,$zone);$e=new DateTimeImmutable($end,$zone);$n=new DateTimeImmutable($now,$zone);
 $step=$repeat==='diaria'?'+1 day':'+1 week';do{$s=$s->modify($step);$e=$e->modify($step);}while($s<=$n);return [$s->format('Y-m-d H:i:s'),$e->format('Y-m-d H:i:s')];
}
function diarioEvent($db,$id,$user,$action,$text){$s=$db->prepare('INSERT INTO Rhu_DiarioEvento(Tarea,Usuario,Accion,Texto,Fecha) VALUES(?,?,?,?,?)');$s->execute([$id,$user,$action,$text,diarioNow()]);}
function diarioTask($db,$id,$identity,$lock=false){[$scope,$params]=diarioScope($identity);$s=$db->prepare('SELECT t.* FROM Rhu_DiarioTarea t WHERE t.Id=? AND '.$scope.($lock?' FOR UPDATE':''));$s->execute(array_merge([(int)$id],$params));$row=$s->fetch(PDO::FETCH_ASSOC);if(!$row)throw new InvalidArgumentException('Actividad no disponible para tu usuario.');return $row;}
function diarioPayload($db,$p,$identity){
 $start=diarioDate($p['Inicio']??'');$end=diarioDate($p['Fin']??'');if($end<=$start)throw new InvalidArgumentException('La fecha de fin debe ser posterior al inicio.');
 $owner=$identity['user'];$employee=null;
 if($identity['admin']){
  $person=(string)($p['Responsable']??('u:'.$owner));
  if(substr($person,0,2)==='e:'){$employee=(int)substr($person,2);$s=$db->prepare('SELECT IdEmpleado FROM Rhu_Empleado WHERE IdEmpleado=? AND IFNULL(Anulado,0)=0');$s->execute([$employee]);if(!$s->fetchColumn())throw new InvalidArgumentException('Empleado no disponible.');$owner=null;}
  elseif(substr($person,0,2)==='u:'){$s=$db->prepare('SELECT Usuario FROM Seg_Usuario WHERE Usuario=? AND IFNULL(Anulado,0)=0');$s->execute([substr($person,2)]);$owner=$s->fetchColumn();if(!$owner)throw new InvalidArgumentException('Usuario no disponible.');}
  else throw new InvalidArgumentException('Selecciona un responsable.');
 }
 $priority=$p['Prioridad']??'normal';$repeat=$p['Repeticion']??'ninguna';
  if(!in_array($priority,['normal','alta','urgente'],true)||!in_array($repeat,['ninguna','diaria','semanal'],true))throw new InvalidArgumentException('Prioridad o repetición inválida.');
 $before=filter_var($p['Anticipacion']??15,FILTER_VALIDATE_INT);$frequency=filter_var($p['Frecuencia']??15,FILTER_VALIDATE_INT);
 if($before===false||$before<0||$before>1440||$frequency===false||$frequency<1||$frequency>1440)throw new InvalidArgumentException('Anticipación: 0 a 1440 min. Repetición de avisos: 1 a 1440 min.');
 return [$owner,$employee,diarioText($p['Titulo']??'',180,true),diarioText($p['Indicaciones']??'',10000),$start,$end,$priority,$before,$frequency,$repeat];
}
return function($app){
 $auth=function($q,$r,$next){
  if(session_status()!==PHP_SESSION_ACTIVE)session_start();$user=$_SESSION['user']??'';
  if(!$user||(isset($_SESSION['expire'])&&(int)$_SESSION['expire']<time())){session_write_close();return $r->withJson(['error'=>'Inicia sesión para abrir tu diario.'],401);}
  $_SESSION['diario_csrf']=$_SESSION['diario_csrf']??bin2hex(random_bytes(24));$csrf=$_SESSION['diario_csrf'];session_write_close();
  if($q->getMethod()!=='GET'&&!hash_equals($csrf,(string)$q->getHeaderLine('X-Diario-CSRF')))return $r->withJson(['error'=>'Actualiza el diario antes de guardar.'],403);
  try{$db=$this->db;$s=$db->prepare('SELECT u.Usuario,u.NombreUsuario,u.IdUsuarioPerfil,IFNULL(p.UsuarioPerfil,\'\') UsuarioPerfil FROM Seg_Usuario u LEFT JOIN Seg_UsuarioPerfil p ON p.IdUsuarioPerfil=u.IdUsuarioPerfil WHERE u.Usuario=? AND IFNULL(u.Anulado,0)=0');$s->execute([$user]);$u=$s->fetch(PDO::FETCH_ASSOC);if(!$u)return $r->withJson(['error'=>'Usuario no disponible.'],403);
   $isSuper=(int)$u['IdUsuarioPerfil']===1&&strcasecmp($u['Usuario'],'superadmin')===0;$isProgrammer=strcasecmp(trim($u['UsuarioPerfil']),'PROGRAMADOR')===0;
   if(!$isSuper&&!$isProgrammer){$route=$q->getAttribute('route');$path=$route?$route->getPattern():$q->getUri()->getPath();if($path==='/diario/contexto')return $r->withJson(['user'=>$u['Usuario'],'nombre'=>$u['NombreUsuario'],'perfil'=>$u['UsuarioPerfil'],'admin'=>false,'superadmin'=>false,'programador'=>false,'tareasHabilitadas'=>false,'csrf'=>$csrf])->withHeader('Cache-Control','no-store');if($path==='/diario/recordatorios')return $r->withJson(['tareas'=>[],'ahora'=>diarioNow()])->withHeader('Cache-Control','no-store');return $r->withJson(['error'=>'El tablero de tareas es exclusivo de programadores y SUPERADMIN. Usa el centro de soporte para generar tus tickets.'],403);}
   $s=$db->prepare('SELECT v.IdEmpleado,IFNULL(p.Cargo,\'\') Cargo FROM Rhu_DiarioVinculo v LEFT JOIN Rhu_PlanillaPersonal p ON p.IdEmpleado=v.IdEmpleado WHERE v.Usuario=?');$s->execute([$u['Usuario']]);$person=$s->fetch(PDO::FETCH_ASSOC)?:[];
   $q=$q->withAttribute('diario',['user'=>$u['Usuario'],'nombre'=>trim($u['NombreUsuario']??'')?:$u['Usuario'],'perfil'=>$u['UsuarioPerfil'],'admin'=>$isSuper,'tareasHabilitadas'=>true,'superadmin'=>(int)$u['IdUsuarioPerfil']===1&&strcasecmp($u['Usuario'],'superadmin')===0,'programador'=>strcasecmp(trim($u['UsuarioPerfil']),'PROGRAMADOR')===0,'employee'=>(int)($person['IdEmpleado']??0),'cargo'=>$person['Cargo']??'','csrf'=>$csrf]);return $next($q,$r)->withHeader('Cache-Control','no-store');
  }catch(InvalidArgumentException $e){if(isset($db)&&$db->inTransaction())$db->rollBack();return $r->withJson(['error'=>$e->getMessage()],422);}
  catch(Throwable $e){if(isset($db)&&$db->inTransaction())$db->rollBack();error_log('[DIARIO] '.$e->getMessage());return $r->withJson(['error'=>'No se pudo completar la operación del diario.'],500);}
 };
 $app->get('/diario/contexto',function($q,$r){$i=$q->getAttribute('diario');$result=$i;$result['zona']='America/Lima';$result['ahora']=diarioNow();$s=$this->db->prepare('SELECT Datos FROM Rhu_DiarioPreferencia WHERE Usuario=?');$s->execute([$i['user']]);$result['tablero']=json_decode($s->fetchColumn()?:'{}',true)?:new stdClass();
  if($i['admin']){$result['usuarios']=$this->db->query('SELECT u.Usuario,u.NombreUsuario,v.IdEmpleado,IFNULL(p.Cargo,\'\') Cargo FROM Seg_Usuario u LEFT JOIN Rhu_DiarioVinculo v ON v.Usuario=u.Usuario LEFT JOIN Rhu_PlanillaPersonal p ON p.IdEmpleado=v.IdEmpleado WHERE IFNULL(u.Anulado,0)=0 ORDER BY u.NombreUsuario')->fetchAll(PDO::FETCH_ASSOC);$result['empleados']=$this->db->query('SELECT e.IdEmpleado,e.Empleado,v.Usuario,IFNULL(p.Cargo,\'\') Cargo FROM Rhu_Empleado e LEFT JOIN Rhu_DiarioVinculo v ON v.IdEmpleado=e.IdEmpleado LEFT JOIN Rhu_PlanillaPersonal p ON p.IdEmpleado=e.IdEmpleado WHERE IFNULL(e.Anulado,0)=0 ORDER BY e.Empleado')->fetchAll(PDO::FETCH_ASSOC);}
  if(($q->getQueryParams()['ligero']??'')==='1'&&is_array($result['tablero']))unset($result['tablero']['fondo']);return $r->withJson($result);
 })->add($auth);
 $app->post('/diario/vinculo',function($q,$r){$i=$q->getAttribute('diario');if(!$i['admin'])return $r->withJson(['error'=>'Solo administración puede vincular empleados.'],403);$p=$q->getParsedBody()?:[];$id=(int)($p['IdEmpleado']??0);$user=$p['Usuario']??'';
  $s=$this->db->prepare('SELECT IdEmpleado FROM Rhu_Empleado WHERE IdEmpleado=? AND IFNULL(Anulado,0)=0');$s->execute([$id]);if(!$s->fetchColumn())throw new InvalidArgumentException('Empleado no disponible.');
  if($user===''){$s=$this->db->prepare('DELETE FROM Rhu_DiarioVinculo WHERE IdEmpleado=?');$s->execute([$id]);}
  else{$s=$this->db->prepare('SELECT Usuario FROM Seg_Usuario WHERE Usuario=? AND IFNULL(Anulado,0)=0');$s->execute([$user]);if(!$s->fetchColumn())throw new InvalidArgumentException('Usuario no disponible.');$s=$this->db->prepare('SELECT IdEmpleado FROM Rhu_DiarioVinculo WHERE Usuario=? AND IdEmpleado<>?');$s->execute([$user,$id]);if($s->fetchColumn())throw new InvalidArgumentException('Ese usuario ya está vinculado a otro empleado.');$s=$this->db->prepare('INSERT INTO Rhu_DiarioVinculo(IdEmpleado,Usuario) VALUES(?,?) ON DUPLICATE KEY UPDATE Usuario=VALUES(Usuario)');$s->execute([$id,$user]);}
  return $r->withJson(['ok'=>true]);
 })->add($auth);
 $app->get('/diario/avances',function($q,$r){return $r->withJson(diarioAvances($this->db,$q->getAttribute('diario'),$q->getQueryParams()));})->add($auth);
 $app->get('/diario/tareas',function($q,$r){$i=$q->getAttribute('diario');$p=$q->getQueryParams();if(($p['vista']??'')==='mia')$i['admin']=false;[$scope,$params]=diarioScope($i);$from=diarioDate(($p['desde']??substr(diarioNow(),0,7).'-01').' 00:00:00');$to=diarioDate(($p['hasta']??substr(diarioNow(),0,10)).' 23:59:59');if($to<$from)throw new InvalidArgumentException('Revisa el rango de fechas.');
  $sql='SELECT t.*,(SELECT COUNT(*) FROM Rhu_DiarioAdjunto a WHERE a.Tarea=t.Id) CantidadAdjuntos,COALESCE(e.Empleado,u.NombreUsuario,t.Usuario) ResponsableNombre,e.IdEmpleado EmpleadoKey,IFNULL(p.Cargo,\'\') Cargo FROM Rhu_DiarioTarea t LEFT JOIN Seg_Usuario u ON u.Usuario=t.Usuario LEFT JOIN Rhu_DiarioVinculo v ON v.Usuario=t.Usuario LEFT JOIN Rhu_Empleado e ON e.IdEmpleado=COALESCE(t.IdEmpleado,v.IdEmpleado) LEFT JOIN Rhu_PlanillaPersonal p ON p.IdEmpleado=e.IdEmpleado WHERE '.$scope.' AND ((COALESCE(t.Archivada,0)=1 AND ?=1) OR t.Estado IN (\'pendiente\',\'en_curso\',\'pausada\') OR (t.Inicio<=? AND t.Fin>=?) OR (t.FinReal>=? AND t.FinReal<=?) OR EXISTS(SELECT 1 FROM Rhu_DiarioEvento ev WHERE ev.Tarea=t.Id AND ev.Fecha>=? AND ev.Fecha<=?))';$archiveParam=count($params);$params=array_merge($params,[(string)($p['archivadas']??'0')==='1'?1:0,$to,$from,$from,$to,$from,$to]);
  $archived=(string)($p['archivadas']??'0')==='1';$sql.=' AND COALESCE(t.Archivada,0)='.($archived?'1':'0');
  if(!empty($p['responsable'])&&$i['admin']){$employee=substr($p['responsable'],0,2)==='e:';$value=substr($p['responsable'],2);if($employee){$sql.=' AND e.IdEmpleado=?';$params[]=$value;}else{$s=$this->db->prepare('SELECT IdEmpleado FROM Rhu_DiarioVinculo WHERE Usuario=?');$s->execute([$value]);$employeeId=(int)$s->fetchColumn();$sql.=' AND (t.Usuario=? OR t.IdEmpleado=?)';$params[]=$value;$params[]=$employeeId;}}
  $s=$this->db->prepare($sql.' ORDER BY t.Inicio DESC,t.Id DESC LIMIT 2001');$s->execute($params);$rows=$s->fetchAll(PDO::FETCH_ASSOC);$truncated=count($rows)>2000;if($truncated)array_pop($rows);$reportSql=preg_replace('/ AND COALESCE\(t\.Archivada,0\)=[01]/','',$sql);$reportParams=$params;$reportParams[$archiveParam]=0;
  $s=$this->db->prepare($reportSql.' ORDER BY t.Inicio DESC,t.Id DESC LIMIT 2001');$s->execute($reportParams);$reportRows=$s->fetchAll(PDO::FETCH_ASSOC);if(count($reportRows)>2000){array_pop($reportRows);$truncated=true;}
  return $r->withJson(['tareas'=>$rows,'indicadores'=>$reportRows,'limitado'=>$truncated,'ahora'=>diarioNow()]);
 })->add($auth);
 $app->get('/diario/recordatorios',function($q,$r){$i=$q->getAttribute('diario');$i['admin']=false;[$scope,$params]=diarioScope($i);$now=diarioNow();$s=$this->db->prepare("SELECT t.* FROM Rhu_DiarioTarea t WHERE $scope AND t.Estado IN ('pendiente','en_curso','pausada') AND (t.Inicio<=DATE_ADD(?,INTERVAL t.Anticipacion MINUTE) OR t.Fin<=DATE_ADD(?,INTERVAL t.Anticipacion MINUTE)) ORDER BY t.Fin LIMIT 100");$s->execute(array_merge($params,[$now,$now]));$tasks=$s->fetchAll(PDO::FETCH_ASSOC);return $r->withJson(['tareas'=>$tasks,'ahora'=>$now]);})->add($auth);
 $app->post('/diario/tareas',function($q,$r){$i=$q->getAttribute('diario');$p=$q->getParsedBody()?:[];$values=diarioPayload($this->db,$p,$i);$goal=diarioGoal($p,$i['admin']);$now=diarioNow();$this->db->beginTransaction();$s=$this->db->prepare('INSERT INTO Rhu_DiarioTarea(Usuario,IdEmpleado,Titulo,Indicaciones,Inicio,Fin,Prioridad,Anticipacion,Frecuencia,Repeticion,Creador,Creada,Modificada,Objetivo,Unidad,Puntos) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$s->execute(array_merge($values,[$i['user'],$now,$now],$goal));$id=(int)$this->db->lastInsertId();diarioWorkFields($this->db,$id,$p);diarioEvent($this->db,$id,$i['user'],'creada','Actividad registrada');$this->db->commit();return $r->withJson(['Id'=>$id]);})->add($auth);
 $app->get('/diario/tareas/{id:[0-9]+}/eventos',function($q,$r,$args){$i=$q->getAttribute('diario');diarioTask($this->db,$args['id'],$i);$s=$this->db->prepare('SELECT * FROM Rhu_DiarioEvento WHERE Tarea=? ORDER BY Id DESC LIMIT 300');$s->execute([$args['id']]);return $r->withJson($s->fetchAll(PDO::FETCH_ASSOC));})->add($auth);
 $app->post('/diario/tareas/{id:[0-9]+}',function($q,$r,$args){
  $db=$this->db;$i=$q->getAttribute('diario');$p=$q->getParsedBody()?:[];$db->beginTransaction();$t=diarioTask($db,$args['id'],$i,true);$action=$p['accion']??'';
  if((int)($p['Version']??0)!==(int)$t['Version'])throw new InvalidArgumentException('La actividad cambió. Actualiza antes de continuar.');
  $id=$t['Id'];$now=diarioNow();$next=null;
  if($action==='evaluar'){
   if(!$i['admin']||$t['Estado']!=='concluida')throw new InvalidArgumentException('Solo administración valida actividades concluidas.');
   $quality=filter_var($p['Calidad']??null,FILTER_VALIDATE_INT);if($quality===false||$quality<1||$quality>5)throw new InvalidArgumentException('Calidad: de 1 a 5.');
   $recognition=diarioText($p['Reconocimiento']??'',5000,true);$s=$db->prepare('UPDATE Rhu_DiarioTarea SET Calidad=?,Reconocimiento=?,Evaluador=?,FechaEvaluacion=?,Version=Version+1 WHERE Id=?');$s->execute([$quality,$recognition,$i['user'],$now,$id]);diarioEvent($db,$id,$i['user'],'evaluada',json_encode(['calidad'=>$quality,'reconocimiento'=>$recognition],JSON_UNESCAPED_UNICODE));
  }
  elseif(in_array($action,['archivar','restaurar'],true)){
   if($t['Estado']!=='concluida')throw new InvalidArgumentException('Solo se pueden archivar tareas concluidas.');
   $archived=$action==='archivar'?1:0;
   $s=$db->prepare('UPDATE Rhu_DiarioTarea SET Archivada=?,ArchivadaPor=?,ArchivadaFecha=?,Version=Version+1,Modificada=? WHERE Id=?');$s->execute([$archived,$archived?$i['user']:null,$archived?$now:null,$now,$id]);diarioEvent($db,$id,$i['user'],$action,$archived?'Tarea concluida archivada; historial conservado':'Tarea restaurada del archivo');
  }
  elseif($action==='nota'){$text=diarioText($p['Texto']??'',5000,true);diarioEvent($db,$id,$i['user'],'nota',$text);}
  elseif($action==='editar'){
   if(!$i['admin']&&$t['Creador']!==$i['user'])throw new InvalidArgumentException('Las indicaciones asignadas solo las puede editar administración.');
   if(in_array($t['Estado'],['concluida','cancelada'],true))throw new InvalidArgumentException('La actividad ya está cerrada.');
   $values=diarioPayload($db,$p,$i);if(!$i['admin']){$values[0]=$t['Usuario'];$values[1]=$t['IdEmpleado'];}
   diarioWorkFields($db,$id,$p,$t);
   $goal=diarioGoal($p,$i['admin'],$t);$s=$db->prepare('UPDATE Rhu_DiarioTarea SET Objetivo=?,Unidad=?,Puntos=? WHERE Id=?');$s->execute(array_merge($goal,[$id]));
   $s=$db->prepare('UPDATE Rhu_DiarioTarea SET Usuario=?,IdEmpleado=?,Titulo=?,Indicaciones=?,Inicio=?,Fin=?,Prioridad=?,Anticipacion=?,Frecuencia=?,Repeticion=?,Version=Version+1,Modificada=? WHERE Id=?');$s->execute(array_merge($values,[$now,$id]));diarioEvent($db,$id,$i['user'],'editada',json_encode(['antes'=>array_intersect_key($t,array_flip(['Usuario','IdEmpleado','Titulo','Indicaciones','Inicio','Fin','Prioridad','Repeticion'])),'despues'=>$p],JSON_UNESCAPED_UNICODE));
  }else{
   $allowed=['iniciar'=>['pendiente','pausada'],'pausar'=>['en_curso'],'concluir'=>['pendiente','en_curso','pausada'],'cancelar'=>['pendiente','en_curso','pausada']];
   if(!isset($allowed[$action])||!in_array($t['Estado'],$allowed[$action],true))throw new InvalidArgumentException('Ese cambio de estado no está disponible.');
   if($action==='cancelar'&&!$i['admin']&&$t['Creador']!==$i['user'])throw new InvalidArgumentException('Solo administración puede cancelar una tarea asignada.');
   if($action==='concluir'){
    $result=diarioText($p['Resultado']??'',10000,true);$evidence=diarioText($p['Evidencia']??'',1000);$actual=$p['Realizado']??0;
    if(!is_numeric($actual)||!is_finite((float)$actual)||(float)$actual<0||(float)$actual>9999999999)throw new InvalidArgumentException('Resultado numérico inválido.');
    $s=$db->prepare('UPDATE Rhu_DiarioTarea SET Resultado=?,Evidencia=?,Realizado=? WHERE Id=?');$s->execute([$result,$evidence,round((float)$actual,2),$id]);
   }
   $seconds=(int)$t['Segundos'];if($t['EnCursoDesde'])$seconds+=max(0,strtotime($now.' America/Lima')-strtotime($t['EnCursoDesde'].' America/Lima'));
   $status=['iniciar'=>'en_curso','pausar'=>'pausada','concluir'=>'concluida','cancelar'=>'cancelada'][$action];$s=$db->prepare('UPDATE Rhu_DiarioTarea SET Estado=?,Segundos=?,EnCursoDesde=?,InicioReal=?,FinReal=?,Version=Version+1,Modificada=? WHERE Id=?');$s->execute([$status,$seconds,$action==='iniciar'?$now:null,$t['InicioReal']?:($action==='iniciar'?$now:null),$action==='concluir'?$now:null,$now,$id]);diarioEvent($db,$id,$i['user'],$status,diarioText($p['Texto']??'',5000));
   if($action==='concluir'&&$t['Repeticion']!=='ninguna'){
    [$start,$end]=diarioNext($t['Inicio'],$t['Fin'],$t['Repeticion'],$now);$s=$db->prepare('INSERT INTO Rhu_DiarioTarea(Usuario,IdEmpleado,Titulo,Indicaciones,Inicio,Fin,Prioridad,Anticipacion,Frecuencia,Repeticion,Creador,Creada,Modificada,Anterior,Objetivo,Unidad,Puntos) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');$s->execute([$t['Usuario'],$t['IdEmpleado'],$t['Titulo'],$t['Indicaciones'],$start,$end,$t['Prioridad'],$t['Anticipacion'],$t['Frecuencia'],$t['Repeticion'],$t['Creador'],$now,$now,$id,$t['Objetivo'],$t['Unidad'],$t['Puntos']]);$next=(int)$db->lastInsertId();diarioWorkFields($db,$next,$t);diarioEvent($db,$next,$i['user'],'creada','Siguiente repetición de #'.$id);
   }
  }
  $db->commit();return $r->withJson(['ok'=>true,'siguiente'=>$next]);
 })->add($auth);
 diarioRegisterPersonal($app,$auth);
 diarioRegisterDashboard($app,$auth);
 diarioRegisterAttachments($app,$auth);
 diarioRegisterOperations($app,$auth);
};
