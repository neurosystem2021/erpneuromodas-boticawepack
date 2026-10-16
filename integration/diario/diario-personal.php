<?php
function diarioPersonalPayload($p){
 $before=filter_var($p['Anticipacion']??15,FILTER_VALIDATE_INT);$frequency=filter_var($p['Frecuencia']??15,FILTER_VALIDATE_INT);$repeat=$p['Repeticion']??'ninguna';
 if($before===false||$before<0||$before>1440||$frequency===false||$frequency<1||$frequency>1440||!in_array($repeat,['ninguna','diaria','semanal'],true))throw new InvalidArgumentException('Revisa la frecuencia y anticipación del recordatorio.');
 return [diarioText($p['Titulo']??'',180,true),diarioText($p['Detalle']??'',5000),diarioDate($p['Fecha']??''),$before,$frequency,$repeat];
}
function diarioRegisterPersonal($app,$auth){
 $app->get('/diario/personales',function($q,$r){$i=$q->getAttribute('diario');$s=$this->db->prepare('SELECT * FROM Rhu_DiarioPersonal WHERE Usuario=? ORDER BY (Estado=\'pendiente\') DESC,CASE WHEN Estado=\'pendiente\' THEN Fecha ELSE NULL END ASC,Fecha DESC LIMIT 1001');$s->execute([$i['user']]);$rows=$s->fetchAll(PDO::FETCH_ASSOC);$limit=count($rows)>1000;if($limit)array_pop($rows);return $r->withJson(['recordatorios'=>$rows,'limitado'=>$limit]);})->add($auth);
 $app->post('/diario/personales',function($q,$r){$i=$q->getAttribute('diario');$values=diarioPersonalPayload($q->getParsedBody()?:[]);$s=$this->db->prepare('INSERT INTO Rhu_DiarioPersonal(Titulo,Detalle,Fecha,Anticipacion,Frecuencia,Repeticion,Usuario) VALUES(?,?,?,?,?,?,?)');$s->execute(array_merge($values,[$i['user']]));return $r->withJson(['Id'=>(int)$this->db->lastInsertId()]);})->add($auth);
 $app->post('/diario/personales/{id:[0-9]+}',function($q,$r,$args){
  $db=$this->db;$i=$q->getAttribute('diario');$p=$q->getParsedBody()?:[];$db->beginTransaction();$s=$db->prepare('SELECT * FROM Rhu_DiarioPersonal WHERE Id=? AND Usuario=? FOR UPDATE');$s->execute([(int)$args['id'],$i['user']]);$t=$s->fetch(PDO::FETCH_ASSOC);
  if(!$t)throw new InvalidArgumentException('Recordatorio personal no disponible.');
  if((int)($p['Version']??0)!==(int)$t['Version']||$t['Estado']!=='pendiente')throw new InvalidArgumentException('El recordatorio cambió o ya está cerrado. Actualiza la lista.');
  $action=$p['accion']??'';$next=null;
  if($action==='editar'){$values=diarioPersonalPayload($p);$s=$db->prepare('UPDATE Rhu_DiarioPersonal SET Titulo=?,Detalle=?,Fecha=?,Anticipacion=?,Frecuencia=?,Repeticion=?,Version=Version+1 WHERE Id=?');$s->execute(array_merge($values,[$t['Id']]));}
  elseif(in_array($action,['concluir','cancelar'],true)){$s=$db->prepare('UPDATE Rhu_DiarioPersonal SET Estado=?,Version=Version+1 WHERE Id=?');$s->execute([$action==='concluir'?'concluida':'cancelada',$t['Id']]);
   if($action==='concluir'&&$t['Repeticion']!=='ninguna'){[$date]=diarioNext($t['Fecha'],$t['Fecha'],$t['Repeticion'],diarioNow());$s=$db->prepare('INSERT INTO Rhu_DiarioPersonal(Titulo,Detalle,Fecha,Anticipacion,Frecuencia,Repeticion,Usuario,Anterior) VALUES(?,?,?,?,?,?,?,?)');$s->execute([$t['Titulo'],$t['Detalle'],$date,$t['Anticipacion'],$t['Frecuencia'],$t['Repeticion'],$i['user'],$t['Id']]);$next=(int)$db->lastInsertId();}
  }else throw new InvalidArgumentException('Acción no disponible.');
  $db->commit();return $r->withJson(['ok'=>true,'siguiente'=>$next]);
 })->add($auth);
}
