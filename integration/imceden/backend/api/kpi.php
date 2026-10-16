<?php

/**
 * KPI / Inteligencia de Negocio.
 * Rutas aisladas del API histórico para mantener los reportes nuevos auditables.
 */

function kpiSession()
{
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    if (empty($_SESSION['user'])) return null;
    return array(
        'user' => (string) $_SESSION['user'],
        'profile' => (int) ($_SESSION['idPerfil'] ?? 0),
        'warehouse' => (int) ($_SESSION['idAlmacen'] ?? 0),
        'admin' => (int) ($_SESSION['idPerfil'] ?? 0) === 1 || strtolower((string) $_SESSION['user']) === 'superadmin'
    );
}

function kpiFilters($request, $session)
{
    $today = new DateTime('today', new DateTimeZone('America/Lima'));
    $from = trim((string) $request->getParam('desde'));
    $to = trim((string) $request->getParam('hasta'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = $today->format('Y-m-01');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = $today->format('Y-m-d');
    if ($from > $to) { $swap = $from; $from = $to; $to = $swap; }
    $start = new DateTime($from);
    $end = new DateTime($to);
    if ($start->diff($end)->days > 731) $from = (clone $end)->modify('-731 days')->format('Y-m-d');
    $warehouse = max(0, (int) $request->getParam('almacen'));
    if (!$session['admin'] && $session['warehouse'] > 0) $warehouse = $session['warehouse'];
    $seller = trim((string) $request->getParam('vendedor'));
    if (in_array($session['profile'], array(3, 17), true)) $seller = $session['user'];
    return array(
        'from' => $from . ' 00:00:00', 'to' => $to . ' 23:59:59',
        'fromDate' => $from, 'toDate' => $to, 'warehouse' => $warehouse,
        'category' => max(0, (int) $request->getParam('categoria')),
        'brand' => max(0, (int) $request->getParam('marca')),
        'product' => max(0, (int) $request->getParam('producto')),
        'client' => max(0, (int) $request->getParam('cliente')),
        'seller' => $seller
    );
}

function kpiSaleWhere($f, &$params, $alias = 'v', $detail = 'd', $product = 'p')
{
    $params = array(':from' => $f['from'], ':to' => $f['to']);
    $where = "{$alias}.FechaDoc BETWEEN :from AND :to AND COALESCE({$alias}.Anulado,0)=0 AND td.CodSunat IN ('01','03','12')";
    $ints = array('warehouse' => array('almacen', "{$alias}.IdAlmacen"), 'category' => array('categoria', "{$product}.IdProductoCategoria"), 'brand' => array('marca', "{$product}.IdProductoMarca"), 'product' => array('producto', "{$detail}.IdProducto"), 'client' => array('cliente', "{$alias}.IdCliente"));
    foreach ($ints as $key => $definition) if ($f[$key] > 0) { $where .= ' AND ' . $definition[1] . '=:' . $definition[0]; $params[':' . $definition[0]] = $f[$key]; }
    if ($f['seller'] !== '') { $where .= " AND {$alias}.UsuarioReg=:vendedor"; $params[':vendedor'] = $f['seller']; }
    return $where;
}

function kpiRows($db, $sql, $params = array())
{
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function kpiRow($db, $sql, $params = array())
{
    $rows = kpiRows($db, $sql, $params);
    return $rows ? $rows[0] : array();
}

function kpiBaseJoins()
{
    return " FROM ve_docventa v
        JOIN ve_docventadet d ON d.IdDocVenta=v.idDocVenta
        JOIN ve_docventatipodoc td ON td.IdTipoDoc=v.IdTipoDoc
        JOIN gen_producto p ON p.IdProducto=d.IdProducto
        LEFT JOIN gen_productocategoria cat ON cat.IdProductoCategoria=p.IdProductoCategoria
        LEFT JOIN gen_productomarca marca ON marca.IdProductoMarca=p.IdProductoMarca
        LEFT JOIN lo_almacen alm ON alm.IdAlmacen=v.IdAlmacen";
}

function kpiDashboardData($db, $f, $session)
{
    $params = array(); $where = kpiSaleWhere($f, $params); $joins = kpiBaseJoins();
    $summary = kpiRow($db, "SELECT COALESCE(SUM(d.Cantidad*d.Precio-d.Descuento),0) venta,
        COALESCE(SUM(d.Cantidad*d.PrecioCosto),0) costo, COALESCE(SUM(d.Descuento),0) descuento,
        COALESCE(SUM(d.Cantidad),0) prendas, COUNT(DISTINCT v.idDocVenta) tickets {$joins} WHERE {$where}", $params);
    foreach (array('venta','costo','descuento','prendas') as $key) $summary[$key] = (float) ($summary[$key] ?? 0);
    $summary['tickets'] = (int) ($summary['tickets'] ?? 0);
    $summary['utilidad'] = $summary['venta'] - $summary['costo'];
    $summary['margen'] = $summary['venta'] != 0 ? $summary['utilidad'] / $summary['venta'] * 100 : 0;
    $summary['ticket_promedio'] = $summary['tickets'] ? $summary['venta'] / $summary['tickets'] : 0;

    $days = (new DateTime($f['fromDate']))->diff(new DateTime($f['toDate']))->days + 1;
    $previous = $f; $previous['toDate'] = (new DateTime($f['fromDate']))->modify('-1 day')->format('Y-m-d');
    $previous['fromDate'] = (new DateTime($previous['toDate']))->modify('-' . ($days - 1) . ' days')->format('Y-m-d');
    $previous['from'] = $previous['fromDate'].' 00:00:00'; $previous['to'] = $previous['toDate'].' 23:59:59';
    $pp = array(); $pw = kpiSaleWhere($previous, $pp);
    $previousRow = kpiRow($db, "SELECT COALESCE(SUM(d.Cantidad*d.Precio-d.Descuento),0) venta {$joins} WHERE {$pw}", $pp);
    $summary['venta_anterior'] = (float) ($previousRow['venta'] ?? 0);
    $summary['variacion'] = $summary['venta_anterior'] != 0 ? (($summary['venta']-$summary['venta_anterior'])/$summary['venta_anterior']*100) : ($summary['venta'] > 0 ? 100 : 0);

    $metaRow = kpiRow($db, "SELECT MetaValue FROM gen_empresaconfig WHERE MetaKey='kpi_meta_mensual' ORDER BY IdEmpresaConfig DESC LIMIT 1");
    $meta = max(0, (float) ($metaRow['MetaValue'] ?? 0));
    $month = $f; $month['from'] = date('Y-m-01 00:00:00'); $month['to'] = date('Y-m-t 23:59:59');
    $mp = array(); $mw = kpiSaleWhere($month, $mp);
    $monthRow = kpiRow($db, "SELECT COALESCE(SUM(d.Cantidad*d.Precio-d.Descuento),0) venta {$joins} WHERE {$mw}", $mp);
    $actualMeta = (float)($monthRow['venta'] ?? 0);
    $summary['meta'] = array('objetivo'=>$meta,'actual'=>$actualMeta,'porcentaje'=>$meta>0?min(999,$actualMeta/$meta*100):0,'faltante'=>max(0,$meta-$actualMeta));

    $evolution = kpiRows($db, "SELECT DATE(v.FechaDoc) fecha, COALESCE(SUM(d.Cantidad*d.Precio-d.Descuento),0) venta,
        COUNT(DISTINCT v.idDocVenta) tickets, SUM(d.Cantidad) unidades {$joins} WHERE {$where} GROUP BY DATE(v.FechaDoc) ORDER BY fecha", $params);
    $productSelect = "SELECT p.IdProducto,p.CodigoBarra codigo,p.Producto producto,COALESCE(cat.ProductoCategoria,'Sin categoría') categoria,
        COALESCE(marca.ProductoMarca,'Sin marca') marca,COALESCE(NULLIF(p.ProductoModelo,''),'Sin modelo') modelo,
        SUM(d.Cantidad) unidades,SUM(d.Descuento) descuento,SUM(d.Cantidad*d.Precio-d.Descuento) venta,SUM(d.Cantidad*d.PrecioCosto) costo,
        SUM(d.Cantidad*d.Precio-d.Descuento)-SUM(d.Cantidad*d.PrecioCosto) utilidad,
        CASE WHEN SUM(d.Cantidad*d.Precio-d.Descuento)=0 THEN 0 ELSE (SUM(d.Cantidad*d.Precio-d.Descuento)-SUM(d.Cantidad*d.PrecioCosto))/SUM(d.Cantidad*d.Precio-d.Descuento)*100 END margen";
    $top = kpiRows($db, $productSelect." {$joins} WHERE {$where} GROUP BY p.IdProducto ORDER BY unidades DESC LIMIT 50", $params);

    $dimensions = array();
    foreach (array('categorias'=>array("COALESCE(cat.ProductoCategoria,'Sin categoria')",'categoria'),'marcas'=>array("COALESCE(marca.ProductoMarca,'Sin marca')",'marca'),'modelos'=>array("COALESCE(NULLIF(p.ProductoModelo,''),'Sin modelo')",'modelo')) as $name=>$def) {
        $dimensions[$name] = kpiRows($db, "SELECT {$def[0]} etiqueta,SUM(d.Cantidad) unidades,SUM(d.Cantidad*d.Precio-d.Descuento) venta,
            SUM(d.Cantidad*d.PrecioCosto) costo,SUM(d.Cantidad*d.Precio-d.Descuento)-SUM(d.Cantidad*d.PrecioCosto) utilidad {$joins}
            WHERE {$where} GROUP BY {$def[0]} ORDER BY venta DESC", $params);
    }

    $inventoryParams = array(); $inventoryWhere = 'COALESCE(p.Anulado,0)=0';
    if ($f['warehouse'] > 0) { $inventoryWhere .= ' AND t.IdAlmacen=:iw'; $inventoryParams[':iw']=$f['warehouse']; }
    if ($f['category'] > 0) { $inventoryWhere .= ' AND p.IdProductoCategoria=:ic'; $inventoryParams[':ic']=$f['category']; }
    if ($f['brand'] > 0) { $inventoryWhere .= ' AND p.IdProductoMarca=:ib'; $inventoryParams[':ib']=$f['brand']; }
    if ($f['product'] > 0) { $inventoryWhere .= ' AND p.IdProducto=:ip'; $inventoryParams[':ip']=$f['product']; }
    $inventory = kpiRows($db, "SELECT p.IdProducto,p.CodigoBarra codigo,p.Producto producto,COALESCE(cat.ProductoCategoria,'Sin categoría') categoria,
        COALESCE(marca.ProductoMarca,'Sin marca') marca,COALESCE(NULLIF(p.ProductoModelo,''),'Sin modelo') modelo,
        alm.Almacen almacen,COALESCE(t.TotalCompras,0)-COALESCE(t.TotalVentas,0) stock,COALESCE(p.StockMinimo,0) stock_minimo,
        COALESCE(p.PrecioCosto,0) costo,COALESCE(p.PrecioContado,0) precio,
        (COALESCE(t.TotalCompras,0)-COALESCE(t.TotalVentas,0))*COALESCE(p.PrecioCosto,0) valor,
        lv.ultima_venta,DATEDIFF(CURDATE(),lv.ultima_venta) dias_sin_venta,COALESCE(lv.unidades_periodo,0) unidades_periodo
        FROM gen_producto p JOIN gen_producto_totales t ON t.IdProducto=p.IdProducto
        JOIN lo_almacen alm ON alm.IdAlmacen=t.IdAlmacen
        LEFT JOIN gen_productocategoria cat ON cat.IdProductoCategoria=p.IdProductoCategoria
        LEFT JOIN gen_productomarca marca ON marca.IdProductoMarca=p.IdProductoMarca
        LEFT JOIN (SELECT d2.IdProducto,MAX(v2.FechaDoc) ultima_venta,SUM(CASE WHEN v2.FechaDoc BETWEEN :from AND :to THEN d2.Cantidad ELSE 0 END) unidades_periodo
          FROM ve_docventadet d2 JOIN ve_docventa v2 ON v2.idDocVenta=d2.IdDocVenta JOIN ve_docventatipodoc td2 ON td2.IdTipoDoc=v2.IdTipoDoc
          WHERE COALESCE(v2.Anulado,0)=0 AND td2.CodSunat IN ('01','03','12') GROUP BY d2.IdProducto) lv ON lv.IdProducto=p.IdProducto
        WHERE {$inventoryWhere} ORDER BY valor DESC", array_merge(array(':from'=>$f['from'],':to'=>$f['to']),$inventoryParams));
    $inventoryValue=0; $critical=0; $out=0; $idleValue=0;
    foreach($inventory as &$row){
        foreach(array('stock','stock_minimo','costo','precio','valor','unidades_periodo') as $key)$row[$key]=(float)$row[$key];
        $inventoryValue += $row['valor']; if($row['stock']<=0)$out++; elseif($row['stock']<=$row['stock_minimo'])$critical++;
        if($row['stock']>0 && ($row['dias_sin_venta']===null || (int)$row['dias_sin_venta']>90))$idleValue += $row['valor'];
        $row['sell_through']=($row['unidades_periodo']+$row['stock'])>0?$row['unidades_periodo']/($row['unidades_periodo']+$row['stock'])*100:0;
        $row['rotacion']=$row['unidades_periodo']<=0?'Sin rotación':($row['sell_through']>=70?'Alta':($row['sell_through']>=35?'Media':'Baja'));
        $row['estado']=$row['stock']<=0?'Agotado':($row['stock']<=$row['stock_minimo']?'Crítico':($row['stock']<=$row['stock_minimo']*2?'Bajo':'Saludable'));
    } unset($row);
    $lowProducts=$inventory;
    usort($lowProducts,function($a,$b){$ua=(float)$a['unidades_periodo'];$ub=(float)$b['unidades_periodo'];if($ua===$ub)return (int)($b['dias_sin_venta']??99999)<=>(int)($a['dias_sin_venta']??99999);return $ua<=>$ub;});
    $lowProducts=array_slice($lowProducts,0,100);

    $sellers = kpiRows($db, "SELECT v.UsuarioReg vendedor,COALESCE(u.NombreUsuario,v.UsuarioReg) nombre,COUNT(DISTINCT v.idDocVenta) tickets,
        SUM(d.Cantidad) unidades,SUM(d.Cantidad*d.Precio-d.Descuento) venta,SUM(d.Descuento) descuento,
        SUM(d.Cantidad*d.Precio-d.Descuento)-SUM(d.Cantidad*d.PrecioCosto) utilidad {$joins}
        LEFT JOIN seg_usuario u ON u.Usuario=v.UsuarioReg WHERE {$where} GROUP BY v.UsuarioReg ORDER BY venta DESC", $params);
    $clients = kpiRows($db, "SELECT c.IdCliente,c.DniRuc documento,c.Cliente cliente,COUNT(DISTINCT v.idDocVenta) compras,
        SUM(d.Cantidad*d.Precio-d.Descuento) venta,AVG(d.Cantidad*d.Precio-d.Descuento) promedio,MIN(v.FechaDoc) primera_compra,MAX(v.FechaDoc) ultima_compra
        {$joins} JOIN ve_docventacliente c ON c.IdCliente=v.IdCliente WHERE {$where} GROUP BY c.IdCliente ORDER BY venta DESC LIMIT 100", $params);
    $branches = kpiRows($db, "SELECT alm.IdAlmacen,alm.Almacen sucursal,COUNT(DISTINCT v.idDocVenta) tickets,SUM(d.Cantidad) unidades,
        SUM(d.Cantidad*d.Precio-d.Descuento) venta,SUM(d.Cantidad*d.Precio-d.Descuento)-SUM(d.Cantidad*d.PrecioCosto) utilidad {$joins}
        WHERE {$where} GROUP BY alm.IdAlmacen ORDER BY venta DESC", $params);
    $hours = kpiRows($db, "SELECT HOUR(v.FechaDoc) hora,COUNT(DISTINCT v.idDocVenta) tickets,SUM(d.Cantidad) unidades,SUM(d.Cantidad*d.Precio-d.Descuento) venta {$joins} WHERE {$where} GROUP BY HOUR(v.FechaDoc) ORDER BY hora",$params);
    $weekdays = kpiRows($db, "SELECT WEEKDAY(v.FechaDoc) dia_num,DAYNAME(v.FechaDoc) dia,COUNT(DISTINCT v.idDocVenta) tickets,SUM(d.Cantidad) unidades,SUM(d.Cantidad*d.Precio-d.Descuento) venta {$joins} WHERE {$where} GROUP BY WEEKDAY(v.FechaDoc),DAYNAME(v.FechaDoc) ORDER BY dia_num",$params);
    $paymentParams=array(':pf'=>$f['from'],':pt'=>$f['to']); $paymentWhere="v.FechaDoc BETWEEN :pf AND :pt AND COALESCE(v.Anulado,0)=0 AND td.CodSunat IN ('01','03','12')";
    if($f['warehouse']>0){$paymentWhere.=' AND v.IdAlmacen=:pa';$paymentParams[':pa']=$f['warehouse'];}
    $payments=kpiRows($db,"SELECT mp.MetodoPago medio,SUM(pd.Importe) importe FROM ve_docventametodopagodet pd JOIN ve_docventa v ON v.idDocVenta=pd.IdDocVenta JOIN ve_docventatipodoc td ON td.IdTipoDoc=v.IdTipoDoc JOIN ve_docventametodopago mp ON mp.IdMetodoPago=pd.IdMetodoPago WHERE {$paymentWhere} GROUP BY mp.IdMetodoPago ORDER BY importe DESC",$paymentParams);
    $returnParams=array(':rf'=>$f['from'],':rt'=>$f['to']);$returnWhere="v.FechaDoc BETWEEN :rf AND :rt AND COALESCE(v.Anulado,0)=0 AND td.CodSunat='07'";
    if($f['warehouse']>0){$returnWhere.=' AND v.IdAlmacen=:ra';$returnParams[':ra']=$f['warehouse'];}
    $returns=kpiRows($db,"SELECT v.idDocVenta,CONCAT(v.Serie,'-',v.Numero) documento,v.NroComprobanteModifica referencia,v.NotaDescMotivo motivo,v.UsuarioReg vendedor,alm.Almacen sucursal,SUM(d.Cantidad) unidades,SUM(d.Cantidad*d.Precio-d.Descuento) importe FROM ve_docventa v JOIN ve_docventadet d ON d.IdDocVenta=v.idDocVenta JOIN ve_docventatipodoc td ON td.IdTipoDoc=v.IdTipoDoc LEFT JOIN lo_almacen alm ON alm.IdAlmacen=v.IdAlmacen WHERE {$returnWhere} GROUP BY v.idDocVenta ORDER BY v.FechaDoc DESC",$returnParams);
    $returnTotal=0;foreach($returns as $r)$returnTotal+=(float)$r['importe'];
    $alerts=array();
    if($critical)$alerts[]=array('level'=>'danger','title'=>$critical.' productos necesitan reposición','detail'=>'El stock es igual o menor al mínimo configurado.');
    if($out)$alerts[]=array('level'=>'danger','title'=>$out.' productos agotados','detail'=>'Actualmente no tienen unidades disponibles.');
    if($idleValue>0)$alerts[]=array('level'=>'warning','title'=>'S/ '.number_format($idleValue,2).' inmovilizados','detail'=>'Stock con más de 90 días sin venta.');
    if($summary['meta']['objetivo']>0)$alerts[]=array('level'=>$summary['meta']['porcentaje']>=100?'success':'info','title'=>'Meta mensual al '.number_format($summary['meta']['porcentaje'],1).'%','detail'=>'Faltan S/ '.number_format($summary['meta']['faltante'],2).'.');
    if($top)$alerts[]=array('level'=>'success','title'=>'Producto líder: '.$top[0]['producto'],'detail'=>number_format($top[0]['unidades'],0).' unidades vendidas en el periodo.');

    $catalogs=array(
      'almacenes'=>kpiRows($db,"SELECT IdAlmacen id,Almacen nombre FROM lo_almacen WHERE COALESCE(Anulado,0)=0 ORDER BY Almacen"),
      'categorias'=>kpiRows($db,"SELECT IdProductoCategoria id,ProductoCategoria nombre FROM gen_productocategoria WHERE COALESCE(Anulado,0)=0 ORDER BY ProductoCategoria"),
      'marcas'=>kpiRows($db,"SELECT IdProductoMarca id,ProductoMarca nombre FROM gen_productomarca WHERE COALESCE(Anulado,0)=0 ORDER BY ProductoMarca"),
      'vendedores'=>kpiRows($db,"SELECT Usuario id,NombreUsuario nombre FROM seg_usuario WHERE COALESCE(Anulado,0)=0 ORDER BY NombreUsuario")
    );
    $financial=$session['admin'] || in_array($session['profile'],array(4),true);
    if(!$financial){
      foreach(array('costo','utilidad','margen') as $key)$summary[$key]=null;
      foreach($top as &$row){$row['costo']=null;$row['utilidad']=null;$row['margen']=null;}unset($row);
      foreach($dimensions as &$group)foreach($group as &$row){$row['costo']=null;$row['utilidad']=null;}unset($group,$row);
      foreach($inventory as &$row){$row['costo']=null;$row['valor']=null;}unset($row);
      foreach($lowProducts as &$row){$row['costo']=null;$row['valor']=null;}unset($row);
      foreach($sellers as &$row)$row['utilidad']=null;unset($row);
      foreach($branches as &$row)$row['utilidad']=null;unset($row);
      $inventoryValue=null;$idleValue=null;
    }
    return array('filters'=>$f,'permissions'=>array('financial'=>$financial,'admin'=>$session['admin']),
      'summary'=>$summary,'evolution'=>$evolution,'top'=>$top,'low_products'=>$lowProducts,'dimensions'=>$dimensions,'inventory'=>$inventory,
      'inventory_summary'=>array('valor'=>$inventoryValue,'criticos'=>$critical,'agotados'=>$out,'inmovilizado'=>$idleValue),
      'sellers'=>$sellers,'clients'=>$clients,'branches'=>$branches,'hours'=>$hours,'weekdays'=>$weekdays,'payments'=>$payments,
      'returns'=>$returns,'return_summary'=>array('cantidad'=>count($returns),'importe'=>$returnTotal,'porcentaje'=>$summary['venta']>0?$returnTotal/$summary['venta']*100:0),
      'alerts'=>$alerts,'catalogs'=>$catalogs);
}

$app->get('/kpi/dashboard', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    $session=kpiSession(); if(!$session)return $response->withJson(array('error'=>'Sesión no válida.'),401);
    try{return $response->withJson(kpiDashboardData($this->db,kpiFilters($request,$session),$session));}
    catch(Throwable $e){return $response->withJson(array('error'=>'No se pudo generar el dashboard KPI.','detail'=>$e->getMessage()),500);}
});

$app->post('/kpi/meta', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    $session=kpiSession();if(!$session)return $response->withJson(array('error'=>'Sesión no válida.'),401);
    if(!$session['admin'])return $response->withJson(array('error'=>'Solo el administrador puede modificar la meta.'),403);
    $value=max(0,(float)$request->getParam('meta'));
    $stmt=$this->db->prepare("SELECT IdEmpresaConfig FROM gen_empresaconfig WHERE IdEmpresa=1 AND MetaKey='kpi_meta_mensual' LIMIT 1");$stmt->execute();$row=$stmt->fetch();
    if($row){$save=$this->db->prepare('UPDATE gen_empresaconfig SET MetaValue=? WHERE IdEmpresaConfig=?');$save->execute(array($value,$row['IdEmpresaConfig']));}
    else{$save=$this->db->prepare("INSERT INTO gen_empresaconfig(IdEmpresa,MetaKey,MetaValue) VALUES(1,'kpi_meta_mensual',?)");$save->execute(array($value));}
    return $response->withJson(array('ok'=>true,'meta'=>$value));
});

$app->get('/kpi/exportar', function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response) {
    $session=kpiSession();if(!$session)return $response->withJson(array('error'=>'Sesión no válida.'),401);
    try{
      $data=kpiDashboardData($this->db,kpiFilters($request,$session),$session);$section=strtolower((string)$request->getParam('seccion'));
      $map=array('productos'=>$data['top'],'inventario'=>$data['inventory'],'vendedores'=>$data['sellers'],'clientes'=>$data['clients'],'sucursales'=>$data['branches'],'devoluciones'=>$data['returns']);
      $rows=$map[$section]??$data['top'];$spreadsheet=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$sheet=$spreadsheet->getActiveSheet();$sheet->setTitle(ucfirst($section?:'KPI'));
      if(!$rows)$rows=array(array('mensaje'=>'Sin datos para el periodo'));
      $headers=array_keys($rows[0]);$column=1;foreach($headers as $header)$sheet->setCellValueByColumnAndRow($column++,1,strtoupper(str_replace('_',' ',$header)));
      $line=2;foreach($rows as $row){$column=1;foreach($headers as $header){$value=$row[$header]??'';$sheet->setCellValueByColumnAndRow($column++,$line,is_scalar($value)?(string)$value:'');}$line++;}
      $sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');$sheet->getStyle('A1:'.$sheet->getHighestColumn().'1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF981535');
      foreach(range('A',$sheet->getHighestColumn()) as $col)$sheet->getColumnDimension($col)->setAutoSize(true);
      aplicarIdentidadEmpresaExcel($spreadsheet,$this->db);$writer=new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);ob_start();$writer->save('php://output');$body=ob_get_clean();
      return $response->withHeader('Content-Type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')->withHeader('Content-Disposition','attachment; filename="kpi-'.$section.'-'.date('Ymd').'.xlsx"')->write($body);
    }catch(Throwable $e){return $response->withJson(array('error'=>'No se pudo exportar.','detail'=>$e->getMessage()),500);}
});
