<?php
function diarioAvances($db,$identity,$input) {
    if (($input['vista']??'mia')!=='equipo')$identity['admin']=false;
    list($scope,$args)=diarioScope($identity);
    $from=diarioDate(($input['desde']??substr(diarioNow(),0,7).'-01').' 00:00:00');
    $to=diarioDate(($input['hasta']??substr(diarioNow(),0,10)).' 23:59:59');
    if($from>$to||strtotime($to)-strtotime($from)>366*86400)throw new InvalidArgumentException('Consulta hasta un año de avances.');
    $where=$scope.' AND e.Fecha>=? AND e.Fecha<=?';$args=array_merge($args,[$from,$to]);
    if(!empty($input['responsable'])&&$identity['admin']){
        if(strpos($input['responsable'],'u:')===0){$where.=' AND (t.Usuario=? OR t.IdEmpleado IN (SELECT IdEmpleado FROM Rhu_DiarioVinculo WHERE Usuario=?))';$args[]=substr($input['responsable'],2);$args[]=substr($input['responsable'],2);}
        elseif(strpos($input['responsable'],'e:')===0){$where.=' AND (t.IdEmpleado=? OR t.Usuario IN (SELECT Usuario FROM Rhu_DiarioVinculo WHERE IdEmpleado=?))';$args[]=(int)substr($input['responsable'],2);$args[]=(int)substr($input['responsable'],2);}
        else throw new InvalidArgumentException('Responsable inválido.');
    }
    $join=' FROM Rhu_DiarioEvento e JOIN Rhu_DiarioTarea t ON t.Id=e.Tarea WHERE '.$where;
    $stmt=$db->prepare("SELECT DATE(e.Fecha) fecha,COUNT(*) avances,COUNT(DISTINCT CASE WHEN e.Accion='concluida' THEN t.Id END) concluidas".$join.' GROUP BY DATE(e.Fecha) ORDER BY fecha');$stmt->execute($args);$days=$stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt=$db->prepare('SELECT e.Id,e.Fecha,e.Accion,e.Texto,e.Usuario,t.Id tarea,t.Titulo,t.Ticket,t.TipoTrabajo,t.Estado'.$join.' ORDER BY e.Fecha DESC,e.Id DESC LIMIT 501');$stmt->execute($args);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);$limited=count($rows)>500;if($limited)array_pop($rows);
    // Full histories are available per task; the area feed displays human progress and results.
    foreach($rows as &$row)if(in_array($row['Accion'],array('editada','evaluada'),true))$row['Texto']=$row['Accion']==='editada'?'Datos de la tarea actualizados':'Resultado evaluado';unset($row);
    $stmt=$db->prepare("SELECT DATE(e.Fecha) fecha,e.Usuario usuario,COUNT(*) avances,COUNT(DISTINCT CASE WHEN e.Accion='concluida' THEN t.Id END) concluidas".$join.' GROUP BY DATE(e.Fecha),e.Usuario ORDER BY fecha,e.Usuario');$stmt->execute($args);$byUser=$stmt->fetchAll(PDO::FETCH_ASSOC);
    return array('diario'=>$days,'por_usuario'=>$byUser,'avances'=>$rows,'limitado'=>$limited);
}
