<?php
function diarioTicketReport($db, $identity, $input)
{
    $from=diarioDate(($input['from']??substr(diarioNow(),0,7).'-01').' 00:00:00');
    $to=diarioDate(($input['to']??substr(diarioNow(),0,10)).' 23:59:59');
    if($from>$to || strtotime($to)-strtotime($from)>366*86400)throw new InvalidArgumentException('Consulta hasta un año de actividades.');
    if(($input['mode']??'mine')!=='team')$identity['admin']=false;
    [$scope,$args]=diarioScope($identity);
    $where=[$scope,'t.Inicio>=?','t.Inicio<=?'];array_push($args,$from,$to);
    $archive=$input['archived']??'active';
    if(!in_array($archive,['active','only','all'],true))throw new InvalidArgumentException('Filtro de archivo inválido.');
    if($archive!=='all')$where[]='COALESCE(t.Archivada,0)='.($archive==='only'?'1':'0');
    $user=$input['user']??'';
    if($user!=='' && $identity['admin']){$where[]='(t.Usuario=? OR t.IdEmpleado IN(SELECT IdEmpleado FROM Rhu_DiarioVinculo WHERE Usuario=?))';array_push($args,diarioText($user,255,true),$user);}
    $states=['pending'=>"t.Estado IN ('pendiente','en_curso','pausada')",'attended'=>"t.Estado='concluida'",'cancelled'=>"t.Estado='cancelada'",'all'=>'1=1'];
    $state=$input['state']??'all';if(!isset($states[$state]))throw new InvalidArgumentException('Estado inválido.');$where[]=$states[$state];
    $join=' FROM Rhu_DiarioTarea t WHERE '.implode(' AND ',$where);
    $s=$db->prepare('SELECT COUNT(*)'.$join);$s->execute($args);$total=(int)$s->fetchColumn();
    $s=$db->prepare("SELECT COALESCE(t.Usuario,(SELECT Usuario FROM Rhu_DiarioVinculo WHERE IdEmpleado=t.IdEmpleado LIMIT 1),CONCAT('Empleado ',t.IdEmpleado)) owner,t.Estado status,COALESCE(t.Archivada,0) archived,COUNT(*) total".$join.' GROUP BY owner,t.Estado,COALESCE(t.Archivada,0)');$s->execute($args);$summary=$s->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($input['before'])){if(!ctype_digit((string)$input['before']))throw new InvalidArgumentException('Página inválida.');$join.=' AND t.Id<?';$args[]=(int)$input['before'];}
    $s=$db->prepare("SELECT t.Id id,COALESCE(NULLIF(t.Ticket,''),CONCAT('ACT-',LPAD(t.Id,6,'0'))) code,t.Titulo subject,t.Estado status,COALESCE(t.Usuario,(SELECT Usuario FROM Rhu_DiarioVinculo WHERE IdEmpleado=t.IdEmpleado LIMIT 1),CONCAT('Empleado ',t.IdEmpleado)) owner,t.Inicio created_at,t.FinReal closed_at,COALESCE(t.Archivada,0) archived,t.Version version,t.Proyecto company".$join.' ORDER BY t.Id DESC LIMIT 101');$s->execute($args);$rows=$s->fetchAll(PDO::FETCH_ASSOC);$more=count($rows)>100;if($more)array_pop($rows);
    foreach($rows as &$row){$row['source']='internal';$row['can_start']=!$row['archived']&&in_array($row['status'],['pendiente','pausada'],true);$row['can_pause']=!$row['archived']&&$row['status']==='en_curso';$row['can_archive']=!$row['archived']&&$row['status']==='concluida';$row['can_restore']=(bool)$row['archived'];}unset($row);
    return ['rows'=>$rows,'summary'=>$summary,'total'=>$total,'next'=>$more?end($rows)['id']:null];
}

function diarioRegisterTicketReport($app,$auth)
{
    $app->get('/diario/tickets-reporte',function($q,$r){return $r->withJson(diarioTicketReport($this->db,$q->getAttribute('diario'),$q->getQueryParams()));})->add($auth);
    $app->get('/diario/tareas/{id:[0-9]+}',function($q,$r,$args){return $r->withJson(diarioTask($this->db,$args['id'],$q->getAttribute('diario')));})->add($auth);
}
