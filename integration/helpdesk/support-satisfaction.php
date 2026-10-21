<?php
function supportSatisfaction($db,$identity,$input)
{
    if(!$identity['superadmin'])throw new InvalidArgumentException('Reporte de satisfacción reservado al superadmin.');
    $zone=new DateTimeZone('America/Lima');$from=$input['from']??date('Y-m-01');$to=$input['to']??date('Y-m-d');
    foreach([$from,$to] as $value){$date=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$value,$zone);if(!$date||$date->format('Y-m-d')!==$value)throw new InvalidArgumentException('Rango de fechas inválido.');}
    if($from>$to||(new DateTimeImmutable($from,$zone))->diff(new DateTimeImmutable($to,$zone))->days>366)throw new InvalidArgumentException('Consulta hasta un año de opiniones.');
    $user=supportText($input['user']??'',255,false);$args=[$from.' 00:00:00',$to.' 23:59:59'];
    $join=' FROM support_satisfaction_surveys s JOIN support_tickets t ON t.id=s.ticket_id AND t.tenant_id=s.tenant_id JOIN support_tenants tenant ON tenant.id=t.tenant_id AND tenant.active=1 JOIN support_installations inst ON inst.id=tenant.installation_id AND inst.active=1 LEFT JOIN support_principals p ON p.id=s.technician_id JOIN support_principals customer ON customer.id=s.respondent_id WHERE s.created_at>=? AND s.created_at<=?';
    if($user!==''){$join.=' AND p.external_user_id=?';$args[]=$user;}
    $fields='COUNT(*) total,COUNT(DISTINCT s.respondent_id) customers,COALESCE(SUM(s.solution>=4),0) satisfied,COALESCE(SUM(s.solution=3),0) neutral,COALESCE(SUM(s.solution<=2),0) dissatisfied,COALESCE(SUM(s.solved=1),0) solved,COALESCE(SUM(s.solved=0),0) not_solved,COALESCE(SUM(s.recommend=1),0) recommend,ROUND(AVG(s.solution),2) average';
    $summary=supportQuery($db,'SELECT '.$fields.$join,$args)->fetch(PDO::FETCH_ASSOC);
    $users=supportQuery($db,"SELECT p.id,COALESCE(p.external_user_id,'Sin técnico confirmado') user,".$fields.$join.' GROUP BY p.id,p.external_user_id',$args)->fetchAll(PDO::FETCH_ASSOC);
    $stars=supportQuery($db,'SELECT s.solution stars,COUNT(*) total'.$join.' GROUP BY s.solution ORDER BY s.solution',$args)->fetchAll(PDO::FETCH_ASSOC);
    if(!empty($input['before'])){if(!ctype_digit((string)$input['before']))throw new InvalidArgumentException('Página inválida.');$join.=' AND s.id<?';$args[]=(int)$input['before'];}
    $rows=supportQuery($db,"SELECT s.id,s.ticket_id,t.code,s.cycle,customer.external_user_id customer,COALESCE(p.external_user_id,'Sin técnico confirmado') user,inst.origin_https company,s.created_at,s.solution stars,s.speed,s.treatment,s.solved,s.recommend,s.comment".$join.' ORDER BY s.id DESC LIMIT 101',$args)->fetchAll(PDO::FETCH_ASSOC);$more=count($rows)>100;if($more)array_pop($rows);
    return ['summary'=>$summary,'users'=>$users,'stars'=>$stars,'rows'=>$rows,'next'=>$more?end($rows)['id']:null];
}
