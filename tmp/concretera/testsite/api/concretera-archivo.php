<?php
// Applied explicitly to current commercial/treasury reads, never to stock or numbering.
function cxArchiveSelectSql($sql,$enabled) {
    if(!$enabled||!is_string($sql)||!preg_match('/^\s*\(*\s*SELECT\b/i',$sql))return $sql;
    $views=['ve_docventa'=>'Cx_VentasActuales','lo_movimiento'=>'Cx_ComprasActuales','lo_ordencompra'=>'Cx_OrdenesCompraActuales','ve_proforma'=>'Cx_ProformasActuales','cb_cajabanco'=>'Cx_CajaActual'];
    $keywords=['WHERE','INNER','LEFT','RIGHT','FULL','CROSS','JOIN','ON','GROUP','ORDER','LIMIT','HAVING','UNION','FOR','USE','FORCE','IGNORE','AS'];
    // String literals and comments are consumed unchanged, including text supplied as a search value.
    $pattern='~\x27(?:\x27\x27|\\\\.|[^\x27\\\\])*\x27|"(?:""|\\\\.|[^"\\\\])*"|/\*.*?\*/|--[^\r\n]*|\b(FROM|JOIN)\s+(`?)(Ve_DocVenta|Lo_Movimiento|Lo_OrdenCompra|Ve_Proforma|Cb_CajaBanco)\b\2(?=\s*(?:(?:AS\s+)?([A-Za-z_][A-Za-z0-9_]*))?)~is';
    return preg_replace_callback($pattern,function($m)use($views,$keywords){
        if(empty($m[1]))return $m[0];
        $alias=$m[4]??'';$hasAlias=$alias!==''&&!in_array(strtoupper($alias),$keywords,true);
        return $m[1].' `'.$views[strtolower($m[3])].'`'.($hasAlias?'':' AS `'.$m[3].'`');
    },$sql);
}
function cxArchiveCommercialSql($sql) {
    return cxArchiveSelectSql($sql,cxArchiveEnabled());
}
function cxArchiveEnabled() {
    return function_exists('appDatabaseConfig')&&function_exists('appLocalDatabaseConfig')
        &&appDatabaseConfig()['dbname']==='fact_wuanukomix'&&!empty(appLocalDatabaseConfig()['wuanuko_multiissuer']);
}
function cxArchiveCloseExclusion($entity) {
    if(!cxArchiveEnabled())return '';
    $map=['VENTA'=>'Ve_DocVenta.idDocVenta','CAJA'=>'Cb_CajaBanco.IdCajaBanco'];
    if(!isset($map[$entity]))throw new InvalidArgumentException('Entidad de cierre no permitida.');
    return " AND NOT EXISTS (SELECT 1 FROM Cx_ArchivoERP a WHERE a.IdEmpresa=2 AND a.Entidad='".$entity."' AND a.Clave=CAST(".$map[$entity]." AS CHAR))";
}
