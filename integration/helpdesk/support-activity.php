<?php
function supportRatings($db,$identity,$from,$to,$mine,$technician) {
    $scope=$identity['superadmin']?'1=1':"EXISTS(SELECT 1 FROM support_memberships m WHERE m.tenant_id=t.tenant_id AND m.principal_id=? AND m.active=1 AND (m.role IN ('supervisor','company_admin') OR (m.role='technician' AND COALESCE(s.technician_id,a.technician_id)=?)))";
    $args=$identity['superadmin']?array():array($identity['principal'],$identity['principal']);
    if($mine){$scope.=' AND COALESCE(s.technician_id,a.technician_id)=?';$args[]=$identity['principal'];}
    if($technician!==''){$scope.=' AND p.external_user_id=?';$args[]=$technician;}
    $sql=" FROM support_satisfaction_surveys s JOIN support_tickets t ON t.id=s.ticket_id AND t.tenant_id=s.tenant_id
        JOIN support_tenants tenant ON tenant.id=t.tenant_id AND tenant.active=1 JOIN support_installations inst ON inst.id=tenant.installation_id AND inst.active=1
        LEFT JOIN support_assignments a ON a.id=(SELECT previous.id FROM support_assignments previous WHERE previous.ticket_id=t.id AND previous.tenant_id=t.tenant_id AND previous.assigned_at<=s.created_at ORDER BY previous.assigned_at DESC,previous.id DESC LIMIT 1)
        LEFT JOIN support_principals p ON p.id=COALESCE(s.technician_id,a.technician_id) WHERE $scope AND s.created_at>=? AND s.created_at<?";
    $args=array_merge($args,array($from,$to));
    $stats=supportQuery($db,'SELECT COUNT(*) cantidad,ROUND(AVG((s.speed+s.treatment+s.solution)/3.0),2) promedio,ROUND(AVG(s.speed),2) rapidez,ROUND(AVG(s.treatment),2) trato,ROUND(AVG(s.solution),2) solucion'.$sql,$args)->fetch(PDO::FETCH_ASSOC);
    $rows=supportQuery($db,'SELECT s.created_at fecha,t.code,p.external_user_id usuario,s.speed rapidez,s.treatment trato,s.solution solucion,s.comment comentario'.$sql.' ORDER BY s.created_at DESC,s.id DESC LIMIT 50',$args)->fetchAll(PDO::FETCH_ASSOC);
    return array('resumen'=>$stats,'detalle'=>$rows,'limitado'=>(int)$stats['cantidad']>50);
}
function supportUrgentQueue($db,$identity) {
    list($scope,$args)=supportScope($db,$identity);
    $staff=$identity['superadmin'] ? '1=1' : "EXISTS(SELECT 1 FROM support_memberships m WHERE m.tenant_id=t.tenant_id AND m.principal_id=? AND m.active=1 AND m.role IN ('technician','supervisor'))";
    if (!$identity['superadmin']) $args[]=$identity['principal'];
    $where=" FROM support_tickets t JOIN support_tenants tenant ON tenant.id=t.tenant_id AND tenant.active=1 JOIN support_installations inst ON inst.id=tenant.installation_id AND inst.active=1 WHERE $scope AND $staff AND t.priority='critical' AND t.status IN ('new','assigned','reopened','escalated')";
    $count=(int)supportQuery($db,'SELECT COUNT(*)'.$where,$args)->fetchColumn();
    $rows=supportQuery($db,'SELECT t.id ticket_id,t.code,t.subject,t.status,t.version,t.priority,t.technician_id,t.created_at'.$where.' ORDER BY t.created_at,t.id LIMIT 50',$args)->fetchAll(PDO::FETCH_ASSOC);
    return array('total'=>$count,'tickets'=>$rows);
}
function supportActivity($db,$identity,$input) {
    $zone=new DateTimeZone('America/Lima');$from=$input['from']??date('Y-m-01');$to=$input['to']??date('Y-m-d');
    foreach (array($from,$to) as $value) {$d=DateTimeImmutable::createFromFormat('!Y-m-d',(string)$value,$zone);if(!$d||$d->format('Y-m-d')!==$value)throw new InvalidArgumentException('Rango de fechas inválido.');}
    $start=new DateTimeImmutable($from,$zone);$finish=(new DateTimeImmutable($to,$zone))->modify('+1 day');
    if($finish<=$start||$start->diff($finish)->days>366)throw new InvalidArgumentException('Consulta hasta un año de avances.');
    list($scope,$args)=supportScope($db,$identity);
    $mine=($input['mode']??'mine')!=='team';
    if($mine){$scope.=' AND t.technician_id=?';$args[]=$identity['principal'];}
    $technician=trim((string)($input['technician_user']??''));
    if($technician!==''){$technician=supportText($technician,255);$scope.=' AND (SELECT external_user_id FROM support_principals WHERE id=t.technician_id)=?';$args[]=$technician;}
    $joins=" FROM support_tickets t JOIN support_tenants tenant ON tenant.id=t.tenant_id AND tenant.active=1 JOIN support_installations inst ON inst.id=tenant.installation_id AND inst.active=1";
    $startSql=$start->format('Y-m-d H:i:s');$endSql=$finish->format('Y-m-d H:i:s');
    $days=array();for($d=$start;$d<$finish;$d=$d->modify('+1 day'))$days[$d->format('Y-m-d')]=array('fecha'=>$d->format('Y-m-d'),'creados'=>0,'resueltos'=>0,'minutos'=>0,'avances'=>0);
    $created=supportQuery($db,'SELECT DATE(t.created_at) fecha,COUNT(*) cantidad'.$joins.' WHERE '.$scope.' AND t.created_at>=? AND t.created_at<? GROUP BY DATE(t.created_at)',array_merge($args,[$startSql,$endSql]))->fetchAll(PDO::FETCH_ASSOC);
    foreach($created as $row)$days[$row['fecha']]['creados']=(int)$row['cantidad'];
    $resolutionScope=$identity['superadmin']?'1=1':"EXISTS(SELECT 1 FROM support_memberships m WHERE m.tenant_id=t.tenant_id AND m.principal_id=? AND m.active=1 AND (m.role IN ('supervisor','company_admin') OR (m.role='technician' AND h.actor_id=?)))";
    $resolutionArgs=$identity['superadmin']?array():array($identity['principal'],$identity['principal']);
    if($mine){$resolutionScope.=' AND h.actor_id=?';$resolutionArgs[]=$identity['principal'];}
    if($technician!==''){$resolutionScope.=' AND (SELECT external_user_id FROM support_principals WHERE id=h.actor_id)=?';$resolutionArgs[]=$technician;}
    $resolved=supportQuery($db,"SELECT DATE(h.created_at) fecha,COUNT(*) cantidad".$joins." JOIN support_ticket_status_history h ON h.ticket_id=t.id AND h.tenant_id=t.tenant_id WHERE ".$resolutionScope." AND h.next_status='resolved' AND h.created_at>=? AND h.created_at<? GROUP BY DATE(h.created_at)",array_merge($resolutionArgs,[$startSql,$endSql]))->fetchAll(PDO::FETCH_ASSOC);
    foreach($resolved as $row)$days[$row['fecha']]['resueltos']=(int)$row['cantidad'];
    $memberships=supportQuery($db,"SELECT tenant_id,role FROM support_memberships WHERE principal_id=? AND active=1 AND role IN ('technician','supervisor')",[$identity['principal']])->fetchAll(PDO::FETCH_ASSOC);
    $staffTenants=array_column($memberships,'tenant_id');$supervisorTenants=array_column(array_filter($memberships,function($row){return $row['role']==='supervisor';}),'tenant_id');
    // Historical work remains attributed to its author even if the ticket is reassigned.
    $workScope=$identity['superadmin']?'1=1':"EXISTS(SELECT 1 FROM support_memberships m WHERE m.tenant_id=t.tenant_id AND m.principal_id=? AND m.active=1 AND (m.role IN ('supervisor','company_admin') OR (m.role='technician' AND w.technician_id=?)))";
    $workArgs=$identity['superadmin']?[]:[$identity['principal'],$identity['principal']];
    if($mine){$workScope.=' AND w.technician_id=?';$workArgs[]=$identity['principal'];}
    if($technician!==''){$workScope.=' AND p.external_user_id=?';$workArgs[]=$technician;}
    $logs=supportQuery($db,'SELECT w.id,w.ticket_id,t.tenant_id,t.code,t.subject,w.started_at,w.ended_at,w.description,p.external_user_id usuario'.$joins.' JOIN support_worklogs w ON w.ticket_id=t.id AND w.tenant_id=t.tenant_id JOIN support_principals p ON p.id=w.technician_id WHERE '.$workScope.' AND w.started_at<? AND w.ended_at>? ORDER BY w.ended_at DESC,w.id DESC',array_merge($workArgs,[$endSql,$startSql]))->fetchAll(PDO::FETCH_ASSOC);
    $minutes=0;
    foreach($logs as &$log){$a=max($start->getTimestamp(),(new DateTimeImmutable($log['started_at'],$zone))->getTimestamp());$b=min($finish->getTimestamp(),(new DateTimeImmutable($log['ended_at'],$zone))->getTimestamp());$log['minutos']=max(0,$b-$a)/60;$minutes+=$log['minutos'];
        while($a<$b){$day=(new DateTimeImmutable('@'.$a))->setTimezone($zone);$next=$day->modify('tomorrow')->setTime(0,0)->getTimestamp();$stop=min($b,$next);$days[$day->format('Y-m-d')]['minutos']+=($stop-$a)/60;$a=$stop;}
        $key=substr($log['ended_at'],0,10);if(isset($days[$key]))$days[$key]['avances']++;
    }unset($log);
    $where=$scope." AND (t.status NOT IN ('resolved','closed','cancelled') OR (t.updated_at>=? AND t.updated_at<?) OR EXISTS(SELECT 1 FROM support_worklogs w WHERE w.ticket_id=t.id AND w.started_at<? AND w.ended_at>?))";
    $ticketArgs=array_merge($args,[$startSql,$endSql,$endSql,$startSql]);
    if(($input['archived']??'active')==='only'){$where=$scope.' AND t.archived_at IS NOT NULL';$ticketArgs=$args;}else $where.=' AND t.archived_at IS NULL';
    $count=(int)supportQuery($db,'SELECT COUNT(*)'.$joins.' WHERE '.$where,$ticketArgs)->fetchColumn();
    $tickets=supportQuery($db,'SELECT t.id,t.tenant_id,t.code,t.subject,t.status,t.priority,t.technician_id,t.version,t.archived_at,t.created_at,t.resolved_at,(SELECT ROUND((s.speed+s.treatment+s.solution)/3.0,2) FROM support_satisfaction_surveys s WHERE s.ticket_id=t.id AND s.tenant_id=t.tenant_id AND s.cycle=(SELECT 1+COUNT(*) FROM support_ticket_status_history h WHERE h.ticket_id=t.id AND h.tenant_id=t.tenant_id AND h.next_status=\'reopened\') ORDER BY s.id DESC LIMIT 1) rating,inst.origin_https empresa,(SELECT external_user_id FROM support_principals WHERE id=t.technician_id) responsable'.$joins.' WHERE '.$where.' ORDER BY t.updated_at DESC,t.id DESC LIMIT 500',$ticketArgs)->fetchAll(PDO::FETCH_ASSOC);
    foreach($tickets as &$ticket)$ticket['can_archive']=$identity['superadmin']||in_array($ticket['tenant_id'],$supervisorTenants)||((int)$ticket['technician_id']===$identity['principal']&&in_array($ticket['tenant_id'],$staffTenants));unset($ticket);
    $visibleLogs=array_values(array_filter($logs,function($log)use($identity,$staffTenants){return $identity['superadmin']||in_array($log['tenant_id'],$staffTenants); }));
    return array('desde'=>$from,'hasta'=>$to,'diario'=>array_values($days),'tickets'=>$tickets,'total'=>$count,'limitado'=>$count>500,'minutos'=>round($minutes,2),'avances'=>count($logs),'trabajos'=>array_slice($visibleLogs,0,200),'trabajosLimitados'=>count($visibleLogs)>200,'urgentes'=>supportUrgentQueue($db,$identity),'calificaciones'=>supportRatings($db,$identity,$startSql,$endSql,$mine,$technician));
}
