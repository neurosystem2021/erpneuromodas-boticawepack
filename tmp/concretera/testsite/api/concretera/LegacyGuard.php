<?php
// Guardas limitadas a los documentos creados por concretera. Si la migración
// no está instalada, ningún flujo de otro giro cambia.
function cxLegacyLink($db,$kind,$id) {
    $table=$kind==='PROFORMA'?'Cx_PropuestaVersion':'Cx_Pedido';
    $s=$db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$s->execute([$table]);if(!(int)$s->fetchColumn())return null;
    $column=$kind==='PROFORMA'?'IdProforma':'IdPreOrden';$s=$db->prepare('SELECT IdEmpresa,'.($kind==='PROFORMA'?'IdVersion':'IdPedido').' AS Id FROM '.$table.' WHERE '.$column.'=? LIMIT 1');$s->execute([(int)$id]);return $s->fetch(PDO::FETCH_ASSOC)?:null;
}
function cxLegacyRequireUnmanaged($db,$kind,$id) {
    if((int)$id>0&&cxLegacyLink($db,$kind,$id))throw new RuntimeException('Documento de concretera: use su propuesta, pedido y liquidación en el módulo Concretera. No se permite cambiar o facturar el suministro desde el flujo genérico.',409);
}
function cxLegacyRequestGuard($db,array $params) {
    foreach(['IdProforma','idProforma'] as $key)if(isset($params[$key]))cxLegacyRequireUnmanaged($db,'PROFORMA',$params[$key]);
    foreach(['IdPreOrden','idPreOrden'] as $key)if(isset($params[$key])) {
        $rows=is_array($params[$key])?$params[$key]:[$params[$key]];
        foreach($rows as $row)cxLegacyRequireUnmanaged($db,'PREORDEN',is_array($row)?($row['IdPreOrden']??0):$row);
    }
}
function cxLegacyExclusion($db,$kind,$qualifiedId) {
    $table=$kind==='PROFORMA'?'Cx_PropuestaVersion':'Cx_Pedido';$column=$kind==='PROFORMA'?'IdProforma':'IdPreOrden';
    if(!in_array($qualifiedId,['Ve_Proforma.IdProforma','Ve_PreOrden.IdPreOrden','p.IdProforma'],true))throw new RuntimeException('Referencia de consulta inválida.');
    $s=$db->prepare('SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$s->execute([$table]);
    return (int)$s->fetchColumn()?' AND NOT EXISTS(SELECT 1 FROM '.$table.' cx WHERE cx.'.$column.'='.$qualifiedId.') ':'';
}
