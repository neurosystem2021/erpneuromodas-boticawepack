<?php
function supportTicketReport($db,$identity,$input)
{
    $zone=new DateTimeZone('America/Lima');$from=$input['from']??date('Y-m-01');$to=$input['to']??date('Y-m-d');
    foreach([$from,$to] as $date){$parsed=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$date,$zone);if(!$parsed||$parsed->format('Y-m-d')!==$date)throw new InvalidArgumentException('Rango de fechas inválido.');}
    if($from>$to||(new DateTimeImmutable($from,$zone))->diff(new DateTimeImmutable($to,$zone))->days>366)throw new InvalidArgumentException('Consulta hasta un año de tickets.');
    [$scope,$args]=supportScope($db,$identity);$where=[$scope,'tenant.active=1','inst.active=1','t.created_at>=?','t.created_at<=?'];array_push($args,$from.' 00:00:00',$to.' 23:59:59');
    if(($input['mode']??'mine')!=='team'){$where[]='(t.technician_id=? OR (t.technician_id IS NULL AND t.requester_id=?))';array_push($args,$identity['principal'],$identity['principal']);}
    if(!empty($input['user'])){$where[]='assignee.external_user_id=?';$args[]=supportText($input['user'],255);}
    $archive=$input['archived']??'active';if(!in_array($archive,['active','only','all'],true))throw new InvalidArgumentException('Filtro de archivo inválido.');
    if($archive!=='all')$where[]='t.archived_at IS '.($archive==='only'?'NOT ':'').'NULL';
    $validated=supportValidatedSql();
    $statusSql="CASE WHEN t.status='closed' AND NOT (".$validated.") THEN 'resolved' ELSE t.status END";
    $states=['pending'=>("(t.status IN ('new','assigned','in_progress','waiting_customer','escalated','reopened','resolved') OR (t.status='closed' AND NOT (".$validated.")))"),'attended'=>("(t.status='closed' AND (".$validated."))"),'cancelled'=>"t.status='cancelled'",'all'=>'1=1'];$state=$input['state']??'all';if(!isset($states[$state]))throw new InvalidArgumentException('Estado inválido.');$where[]=$states[$state];
    $join=' FROM support_tickets t JOIN support_tenants tenant ON tenant.id=t.tenant_id JOIN support_installations inst ON inst.id=tenant.installation_id LEFT JOIN support_principals assignee ON assignee.id=t.technician_id WHERE '.implode(' AND ',$where);
    $total=(int)supportQuery($db,'SELECT COUNT(*)'.$join,$args)->fetchColumn();
    $summary=supportQuery($db,"SELECT COALESCE(assignee.external_user_id,'Sin asignar') owner,".$statusSql." status,t.archived_at IS NOT NULL archived,COUNT(*) total".$join.' GROUP BY assignee.external_user_id,t.status,t.archived_at IS NOT NULL,('.$validated.')',$args)->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($input['before'])){if(!ctype_digit((string)$input['before']))throw new InvalidArgumentException('Página inválida.');$join.=' AND t.id<?';$args[]=(int)$input['before'];}
    $rows=supportQuery($db,"SELECT t.id,t.tenant_id,t.technician_id,t.code,t.subject,".$statusSql." status,COALESCE(assignee.external_user_id,'Sin asignar') owner,t.created_at,t.closed_at,t.archived_at IS NOT NULL archived,t.version,inst.origin_https company".$join.' ORDER BY t.id DESC LIMIT 101',$args)->fetchAll(PDO::FETCH_ASSOC);$more=count($rows)>100;if($more)array_pop($rows);
    foreach($rows as &$row){$role=supportRole($db,$identity,(int)$row['tenant_id']);$manages=in_array($role,['superadmin','supervisor'],true)||($role==='technician'&&(int)$row['technician_id']===$identity['principal']);$row['source']='support';$row['can_start']=!$row['archived']&&$manages&&$row['technician_id']&&in_array($row['status'],['new','assigned','waiting_customer','escalated','reopened'],true);$row['can_pause']=!$row['archived']&&$manages&&$row['status']==='in_progress';$row['can_archive']=!$row['archived']&&$manages&&$row['status']==='closed';$row['can_restore']=(bool)$row['archived']&&$manages;unset($row['tenant_id'],$row['technician_id']);}unset($row);
    return ['rows'=>$rows,'summary'=>$summary,'total'=>$total,'next'=>$more?end($rows)['id']:null];
}
