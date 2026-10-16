<?php
function managementRows(PDO $db, $sql, array $params = array()) {
    $stmt = $db->prepare($sql); $stmt->execute($params); return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function managementFilters(array $input) {
    $zone = new DateTimeZone('America/Lima'); $dates = array();
    foreach (array('desde','hasta') as $key) {
        $value = (string) ($input[$key] ?? ''); $date = DateTime::createFromFormat('!Y-m-d', $value, $zone);
        if (!$date || $date->format('Y-m-d') !== $value) throw new InvalidArgumentException('Selecciona un rango de fechas válido.');
        $dates[$key] = $date;
    }
    if ($dates['desde'] > $dates['hasta']) throw new InvalidArgumentException('La fecha inicial debe ser anterior a la final.');
    if ($dates['hasta'] > new DateTime('today', $zone)) throw new InvalidArgumentException('El período histórico no puede incluir fechas futuras.');
    $days = $dates['desde']->diff($dates['hasta'])->days + 1;
    if ($days > 1096) throw new InvalidArgumentException('Selecciona un máximo de tres años por consulta.');
    $horizon = filter_var($input['proyeccion'] ?? 30, FILTER_VALIDATE_INT);
    if (!$horizon || $horizon < 1 || $horizon > 366) throw new InvalidArgumentException('La proyección debe ser de 1 a 366 días.');
    $mapping = $input['clasificacion'] ?? array();
    if (is_string($mapping)) $mapping = json_decode($mapping, true);
    if (!is_array($mapping)) throw new InvalidArgumentException('Clasificación inválida.');
    foreach ($mapping as $id => $category) {
        if (!ctype_digit((string) $id) || !in_array($category, array('gasto','personal','proveedor','traslado','otro','pendiente'), true)) throw new InvalidArgumentException('Clasificación inválida.');
    }
    $entries=$input['movimientos'] ?? array();
    if (!is_array($entries)) throw new InvalidArgumentException('Clasificación de movimientos inválida.');
    foreach ($entries as $id=>$category) if (!ctype_digit((string)$id) || !in_array($category,array('gasto','personal','proveedor','traslado','otro','pendiente'),true)) throw new InvalidArgumentException('Clasificación de movimientos inválida.');
    return array('desde'=>$dates['desde']->format('Y-m-d'),'hasta'=>$dates['hasta']->format('Y-m-d'),'fin'=>(clone $dates['hasta'])->modify('+1 day')->format('Y-m-d'),'dias'=>$days,'proyeccion'=>$horizon,'clasificacion'=>$mapping,'movimientos'=>$entries);
}
function managementDefaultCategory($id) {
    return array(1=>'proveedor',3=>'gasto',6=>'personal')[(int) $id] ?? 'pendiente';
}
function managementBuild(array $sales, array $cash, array $types, array $filters) {
    $daily = array(); $date = new DateTime($filters['desde']);
    while ($date->format('Y-m-d') <= $filters['hasta']) {
        $key = $date->format('Y-m-d');
        $daily[$key] = array('fecha'=>$key,'servicios'=>0,'mercaderia'=>0,'sinTipo'=>0,'costoServicios'=>0,'costoMercaderia'=>0,'costoSinTipo'=>0,'venta'=>0,'costo'=>0,'brutaServicios'=>0,'brutaMercaderia'=>0,'bruta'=>0,'gastos'=>0,'personal'=>0,'proveedores'=>0,'otros'=>0,'pendiente'=>0,'traslados'=>0,'cobrosDigitales'=>0,'salidasRegistradas'=>0,'salidasExternas'=>0,'ingresosCaja'=>0,'neta'=>0,'acumulado'=>0);
        $date->modify('+1 day');
    }
    $missingCost=0; $fallbackCost=0; $missingType=0; $negativeCash=0;
    foreach ($sales as &$sale) {
        $key=substr($sale['fecha'],0,10); if (!isset($daily[$key])) continue;
        $sign = $sale['sunat']==='07' ? -1 : 1;
        $sale['venta']=round($sign*((float)$sale['cantidad']*(float)$sale['precio']-(float)$sale['descuento']),2);
        $sale['costo']= $sale['costoUnitario'] === null ? null : round($sign*(float)$sale['cantidad']*(float)$sale['costoUnitario'],2);
        $sale['bruta']=$sale['costo']===null ? null : round($sale['venta']-$sale['costo'],2);
        $sale['tipo']=(int)$sale['opc']===2 ? 'Servicios' : ((int)$sale['opc']===1 ? 'Mercadería' : 'Sin clasificar');
        $sale['origenCosto']=$sale['historico']!==null ? 'Costo de la venta' : ($sale['costoUnitario']!==null ? 'Costo actual del producto' : 'Sin costo');
        if ($sale['costo']===null) $missingCost++;
        elseif ($sale['historico']===null) $fallbackCost++;
        if ($sale['tipo']==='Sin clasificar') $missingType++;
        $bucket=(int)$sale['opc']===2 ? 'servicios' : ((int)$sale['opc']===1 ? 'mercaderia' : 'sinTipo');
        $costBucket=array('servicios'=>'costoServicios','mercaderia'=>'costoMercaderia','sinTipo'=>'costoSinTipo')[$bucket];
        $daily[$key][$bucket]+=$sale['venta'];
        $daily[$key][$costBucket]+=$sale['costo'] ?? 0;
        $daily[$key]['venta']+=$sale['venta']; $daily[$key]['costo']+=$sale['costo'] ?? 0;
    }
    unset($sale);
    $classifications=array();
    foreach ($types as $type) {
        $id=(int)$type['id']; $category=$filters['clasificacion'][$id] ?? managementDefaultCategory($id);
        $classifications[$id]=array('id'=>$id,'nombre'=>$type['nombre'],'categoria'=>$category,'importe'=>0);
    }
    foreach ($cash as &$entry) {
        $key=substr($entry['fecha'],0,10); if (!isset($daily[$key])) continue;
        $amount=round((float)$entry['importe'],2); $entry['importe']=$amount;
        if ($amount < 0) $negativeCash++;
        // New neutral type and legacy automatic Yape entries refer to an existing sale.
        if ((int)$entry['direccion']===2 || ((int)$entry['tipoId']===7 && preg_match('/^YAPE\/PLIN\s*-\s*/i', $entry['concepto']) && empty($entry['clienteId']))) {
            $entry['categoria']='cobro_digital'; $daily[$key]['cobrosDigitales']+=$amount; continue;
        }
        if ((int)$entry['direccion']===0) { $entry['categoria']='ingreso'; $daily[$key]['ingresosCaja']+=$amount; continue; }
        $id=(int)$entry['tipoId'];
        if (!isset($classifications[$id])) $classifications[$id]=array('id'=>$id,'nombre'=>$entry['operacion'] ?: 'Tipo desconocido','categoria'=>$filters['clasificacion'][$id] ?? managementDefaultCategory($id),'importe'=>0);
        $category=$filters['movimientos'][$entry['id']] ?? $classifications[$id]['categoria'];
        if ($id===5 && preg_match('/^(Tarjeta|Transferencia)\s*-\s*\S+-\d+\s+Nro Operacion:/i',$entry['concepto'])) $category='traslado';
        $entry['categoria']=$category;
        $classifications[$id]['importe']+=$amount;
        $daily[$key]['salidasRegistradas']+=$amount;
        $target=array('gasto'=>'gastos','personal'=>'personal','proveedor'=>'proveedores','traslado'=>'traslados','otro'=>'otros','pendiente'=>'pendiente')[$category];
        $daily[$key][$target]+=$amount;
        if ($category!=='traslado') $daily[$key]['salidasExternas']+=$amount;
    }
    unset($entry);
    $pending=0; foreach ($daily as $day) $pending+=abs($day['pendiente']);
    $complete=$missingCost===0 && $pending<0.005 && $negativeCash===0;
    $accumulated=0; $totals=array();
    foreach ($daily as &$day) {
        $day['brutaServicios']=$day['servicios']-$day['costoServicios'];
        $day['brutaMercaderia']=$day['mercaderia']-$day['costoMercaderia'];
        $day['bruta']=$day['venta']-$day['costo'];
        $day['neta']=$complete ? $day['bruta']-$day['gastos']-$day['personal'] : null;
        if ($complete) $accumulated+=$day['neta'];
        $day['acumulado']=$complete ? $accumulated : null;
        if ($missingCost) $day['bruta']=$day['brutaServicios']=$day['brutaMercaderia']=$day['costo']=$day['costoServicios']=$day['costoMercaderia']=$day['costoSinTipo']=null;
        foreach ($day as $key=>$value) if ($key!=='fecha' && $key!=='acumulado') {
            if ($value===null) $totals[$key]=null;
            elseif (!array_key_exists($key,$totals) || $totals[$key]!==null) $totals[$key]=round(($totals[$key] ?? 0)+$value,2);
        }
        foreach ($day as $key=>$value) if (is_numeric($value) && $key!=='fecha') $day[$key]=round($value,2);
    }
    unset($day);
    $notes=array(
        'Consolidado de todos los usuarios y almacenes. Importes en soles según registros del sistema, sin ajuste adicional de IGV.',
        'Ventas válidas: factura, boleta y nota de venta. Notas de crédito restan venta y costo registrados. Se excluyen anulados, proformas y ofertas (código 99).',
        'Utilidad neta operativa estimada = utilidad bruta - gastos operativos - pagos de personal. No incluye impuestos a la renta, depreciaciones ni obligaciones no registradas.',
        'Pagos a proveedores se muestran como salidas; no se descuentan otra vez de la utilidad porque el costo vendido ya está incluido. Si un pago corresponde a un gasto, reclasifica su tipo como gasto operativo.',
        'Cobros Yape/Plin se muestran aparte: no son salidas, gastos ni ventas adicionales. Se reconoce también el registro automático antiguo YAPE/PLIN. Otros registros del antiguo tipo 7 requieren clasificación.',
        'Los registros automáticos Tarjeta/Transferencia de ventas no se consideran gasto. Los demás registros del tipo depósitos requieren clasificar su destino real; pueden ser compras, gastos o traslados.',
        'Otras salidas no operativas se muestran sin reducir utilidad. Revisa su clasificación. El detalle de caja contiene todos los registros no anulados del período.',
        'La proyección es el promedio diario del período multiplicado por los días elegidos, incluidos días sin actividad. No es una garantía ni un saldo bancario.',
        'Este reporte usa gastos y pagos registrados en Caja y Banco. Compras, planillas o deudas sin pago registrado no se convierten automáticamente en gastos del período.'
    );
    if ($missingCost) $notes[]=$missingCost.' líneas no tienen costo. La utilidad y su proyección quedan pendientes hasta completar esos costos.';
    if ($fallbackCost) $notes[]=$fallbackCost.' líneas usan costo actual del producto porque no existe costo histórico. La utilidad es estimada.';
    if ($missingType) $notes[]=$missingType.' líneas no están clasificadas como servicio o mercadería y se incluyen en Sin clasificar.';
    if ($pending>=0.005) $notes[]='Hay salidas pendientes de clasificación. Clasifica sus tipos para calcular la utilidad neta.';
    if ($negativeCash) $notes[]='Hay importes negativos en caja. Revisa sus signos antes de calcular la utilidad neta.';
    return array('filtros'=>$filters,'diario'=>array_values($daily),'totales'=>$totals,'ventas'=>$sales,'caja'=>$cash,'clasificacion'=>array_values($classifications),'notas'=>$notes,'completo'=>$complete,'promedio'=>$complete ? round($totals['neta']/$filters['dias'],2) : null,'proyectada'=>$complete ? round($totals['neta']/$filters['dias']*$filters['proyeccion'],2) : null);
}
function managementReport(PDO $db, array $input, array $defaults = array()) {
    foreach (array('clasificacion','movimientos') as $key) {
        if (isset($input[$key]) && !is_array($input[$key]) && !($key==='clasificacion' && is_string($input[$key]))) throw new InvalidArgumentException('Clasificación inválida.');
        $provided=$input[$key] ?? array();
        if (is_string($provided)) $provided=json_decode($provided,true);
        if (!is_array($provided)) throw new InvalidArgumentException('Clasificación inválida.');
        $input[$key]=array_replace($defaults[$key] ?? array(),$provided);
    }
    $filters=managementFilters($input); $params=array(':start'=>$filters['desde'],':end'=>$filters['fin']);
    $sales=managementRows($db,"SELECT d.IdDocVentaDet id,DATE(v.FechaDoc) fecha,v.UsuarioReg usuario,CONCAT(v.Serie,'-',v.Numero) documento,td.CodSunat sunat,
        p.Producto producto,p.OpcContable opc,d.Cantidad cantidad,d.Precio precio,COALESCE(d.Descuento,0) descuento,
        d.PrecioCosto historico,COALESCE(d.PrecioCosto,p.PrecioCosto) costoUnitario
        FROM Ve_DocVenta v JOIN Ve_DocVentaDet d ON d.IdDocVenta=v.idDocVenta
        JOIN Ve_DocVentaTipoDoc td ON td.IdTipoDoc=v.IdTipoDoc LEFT JOIN Gen_Producto p ON p.IdProducto=d.IdProducto
        WHERE COALESCE(v.Anulado,0)=0 AND td.CodSunat IN ('01','03','12','07')
        AND v.FechaDoc>=:start AND v.FechaDoc<:end ORDER BY v.FechaDoc,v.idDocVenta,d.IdDocVentaDet",$params);
    $cash=managementRows($db,"SELECT cb.IdCajaBanco id,DATE(cb.FechaDoc) fecha,cb.UsuarioReg usuario,cb.Concepto concepto,cb.Importe importe,
        cb.IdTipoCajaBanco tipoId,t.Tipo direccion,t.TipoCajaBanco operacion,c.Cuenta cuenta,p.Proveedor proveedor
        FROM Cb_CajaBanco cb LEFT JOIN Cb_TipoCajaBanco t ON t.IdTipoCajaBanco=cb.IdTipoCajaBanco
        LEFT JOIN Cb_Cuenta c ON c.IdCuenta=cb.IdCuenta LEFT JOIN Lo_Proveedor p ON p.IdProveedor=cb.IdProveedor
        WHERE COALESCE(cb.Anulado,0)=0 AND cb.FechaDoc>=:start AND cb.FechaDoc<:end ORDER BY cb.FechaDoc,cb.IdCajaBanco",$params);
    // Unknown cash types must be classified rather than disappearing from the report.
    foreach ($cash as &$entry) if ($entry['direccion']===null) $entry['direccion']=1;
    unset($entry);
    $types=managementRows($db,'SELECT IdTipoCajaBanco id,TipoCajaBanco nombre FROM Cb_TipoCajaBanco WHERE Tipo=1 ORDER BY IdTipoCajaBanco');
    return managementBuild($sales,$cash,$types,$filters);
}
