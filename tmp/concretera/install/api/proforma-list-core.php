<?php
function quoteListInput($params){
 $type=$params['tipo']??'productos';$state=$params['estado']??'pendientes';$q=$params['q']??'';
 if(!in_array($type,['productos','sistemas'],true)||!in_array($state,['pendientes','facturadas','favoritas'],true)||!is_string($q)||mb_strlen($q)>160)throw new InvalidArgumentException('Revisa los filtros de las proformas.');
 $page=filter_var($params['page']??1,FILTER_VALIDATE_INT);$limit=filter_var($params['limit']??10,FILTER_VALIDATE_INT);if(!$page||$page<1||$page>100000||!$limit||$limit<1||$limit>100)throw new InvalidArgumentException('Revisa la página y la cantidad de filas.');return ['tipo'=>$type,'estado'=>$state,'q'=>trim($q),'page'=>$page,'limit'=>$limit];
}
function quoteListData($db,$input){
 $where="COALESCE(p.Anulado,0)=0 AND COALESCE(p.UsuarioReg,'')<>'TIENDA' AND COALESCE(NULLIF(p.Presentacion,''),'productos')=?";$args=[$input['tipo']];
 require_once __DIR__.'/concretera/LegacyGuard.php';$where.=cxLegacyExclusion($db,'PROFORMA','p.IdProforma');
 foreach(array_slice(preg_split('/\s+/u',$input['q']),0,8) as $term){if($term==='')continue;$where.=' AND (c.Cliente LIKE ? OR c.DniRuc LIKE ? OR p.Anio LIKE ? OR p.Numero LIKE ? OR p.Descripcion LIKE ? OR p.UsuarioReg LIKE ? OR c.Telefono LIKE ?)';for($i=0;$i<7;$i++)$args[]='%'.$term.'%';}
 $sales="SELECT v.idDocVenta FROM Ve_DocVenta v WHERE COALESCE(v.Anulado,0)=0 AND (v.IdProforma=p.IdProforma OR v.idDocVenta=p.IdDocVenta)";
 $base="SELECT p.IdProforma,COALESCE(p.EsFavoritoFlag,0) AS Favorita,EXISTS($sales) AS Facturada FROM Ve_Proforma p LEFT JOIN Ve_DocVentaCliente c ON c.IdCliente=p.IdCliente WHERE $where";
 $s=$db->prepare("SELECT COALESCE(SUM(Facturada=0),0) AS pendientes,COALESCE(SUM(Facturada=1),0) AS facturadas,COALESCE(SUM(Favorita=1),0) AS favoritas FROM ($base) t");$s->execute($args);$counts=array_map('intval',$s->fetch(PDO::FETCH_ASSOC));
 $state=$input['estado'];$extra=$state==='favoritas'?' AND COALESCE(p.EsFavoritoFlag,0)=1':($state==='facturadas'?" AND EXISTS($sales)":" AND NOT EXISTS($sales)");
 $sql="SELECT p.*,c.Cliente,c.DniRuc,c.Direccion,c.Telefono AS ClienteTelefono,EXISTS($sales) AS Facturada,(SELECT CONCAT('[',GROUP_CONCAT(DISTINCT v.idDocVenta ORDER BY v.idDocVenta),']') FROM Ve_DocVenta v WHERE COALESCE(v.Anulado,0)=0 AND (v.IdProforma=p.IdProforma OR v.idDocVenta=p.IdDocVenta)) AS VentasFacturadas,COALESCE((SELECT ROUND(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0)),2) FROM Ve_ProformaDet d WHERE d.IdProforma=p.IdProforma),0) AS Total FROM Ve_Proforma p LEFT JOIN Ve_DocVentaCliente c ON c.IdCliente=p.IdCliente WHERE $where $extra ORDER BY p.IdProforma DESC LIMIT ".(int)$input['limit'].' OFFSET '.(($input['page']-1)*$input['limit']);
 $s=$db->prepare($sql);$s->execute($args);$rows=$s->fetchAll(PDO::FETCH_ASSOC);foreach($rows as &$row){$row['IdDocVenta']=$row['VentasFacturadas']?:'[]';$row['Facturada']=(bool)$row['Facturada'];}unset($row);
 return ['items'=>$rows,'total'=>$counts[$state],'counts'=>$counts];
}
