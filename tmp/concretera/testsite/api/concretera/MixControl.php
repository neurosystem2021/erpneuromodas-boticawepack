<?php
trait CxMixControl {
    public function mixControl($company,array $p) {
        $this->permission($company);$warehouse=$this->reference($company,'ALMACEN',$p['idAlmacen']??0);
        $from=cxDate($p['desde']??'');$until=cxDate($p['hasta']??'');$first=new DateTimeImmutable($from,new DateTimeZone('America/Lima'));$last=new DateTimeImmutable($until,new DateTimeZone('America/Lima'));
        if($until<$from||$first->diff($last)->days>365)throw new CxError('Elija un período de 1 a 366 días.');
        $end=$last->modify('+1 day')->format('Y-m-d').' 00:00:00';$state=cxChoice($p['estado']??'ACTIVAS',['ACTIVAS','TODAS'],'el estado de programación');
        $issuer=empty($p['emisor'])?null:$this->issuer($company,$p['emisor']);
        $sql="SELECT p.*,o.Anio AS PedidoAnio,o.Numero AS PedidoNumero,o.IdEmpresaEmisora,o.EsPrueba AS PedidoEsPrueba,o.Instantanea AS PedidoInstantanea,c.IdCarga,c.Secuencia,c.CantidadNominal AS VolumenCarga,c.Inicio AS InicioCarga,c.FinCarga,c.Retorno,c.Estado AS EstadoCarga,c.Instantanea AS CargaInstantanea FROM Cx_Programacion p JOIN Cx_Pedido o ON o.IdEmpresa=p.IdEmpresa AND o.IdPedido=p.IdPedido LEFT JOIN Cx_CargaProgramada c ON c.IdEmpresa=p.IdEmpresa AND c.IdProgramacion=p.IdProgramacion AND c.RevisionPlan=p.Revision AND c.Estado<>'SUSTITUIDA' WHERE p.IdEmpresa=? AND p.IdAlmacen=? AND ((c.IdCarga IS NOT NULL AND c.Inicio>=? AND c.Inicio<?) OR (c.IdCarga IS NULL AND p.Inicio>=? AND p.Inicio<?))";
        $args=[$company,$warehouse,$from.' 00:00:00',$end,$from.' 00:00:00',$end];
        if($state==='ACTIVAS')$sql.=" AND p.Estado NOT IN ('CANCELADA','REPROGRAMADA')";
        if($issuer){$sql.=' AND o.IdEmpresaEmisora=?';$args[]=$issuer['IDEMPRESA'];}
        $sql.=' ORDER BY COALESCE(c.Inicio,p.Inicio),p.IdProgramacion,c.Secuencia LIMIT 2001';
        $raw=$this->run($sql,$args)->fetchAll(PDO::FETCH_ASSOC);if(count($raw)>2000)throw new CxError('El período supera 2000 cargas. Reduzca el rango para obtener un informe completo.');
        $designs=[];$owners=[];$rows=[];$materials=[];$totals=[];$days=[];$activePlans=[];
        $summary=['m3Nominal'=>'0.000000','m3Real'=>null,'cargas'=>0,'sinDividir'=>0,'sinReceta'=>0,'recetaNoAprobada'=>0,'excluidas'=>0,'insumosParciales'=>false,'incluyePruebas'=>false];
        foreach($raw as $r){
            $id=$r['IdProgramacion'];if(!isset($owners[$id]))$owners[$id]=$this->run('SELECT Area,Usuario,Nombre FROM Cx_ProgramacionResponsable WHERE IdEmpresa=? AND IdProgramacion=? ORDER BY Area',[$company,$id])->fetchAll(PDO::FETCH_ASSOC);
            $snapshot=json_decode($r['Instantanea'],true)?:[];$order=json_decode($r['PedidoInstantanea'],true)?:[];$load=json_decode($r['CargaInstantanea']??'',true)?:[];
            $d=null;if($r['IdDisenoVersion']){if(!isset($designs[$r['IdDisenoVersion']]))$designs[$r['IdDisenoVersion']]=$this->design($company,$r['IdDisenoVersion']);$d=$designs[$r['IdDisenoVersion']];}
            $recipe=$load['diseno']??$snapshot['datos']['diseno']??null;
            // Freeze recorded doses/names; do not silently substitute a different recipe/version.
            if($recipe&&(int)($recipe['IdDisenoVersion']??0)!==(int)$r['IdDisenoVersion'])$recipe=null;
            $quantity=bcadd($r['VolumenCarga']??$r['Cantidad'],'0',6);$date=$r['InicioCarga']??$r['Inicio'];
            $included=!in_array($r['Estado'],['CANCELADA','REPROGRAMADA'],true)&&!in_array($r['EstadoCarga'],['CANCELADA','SUSTITUIDA'],true);
            $code='PG-'.$r['Anio'].'-'.str_pad($r['Numero'],5,'0',STR_PAD_LEFT);$orderCode='PW-'.$r['PedidoAnio'].'-'.str_pad($r['PedidoNumero'],5,'0',STR_PAD_LEFT);
            $row=['esPrueba'=>(bool)$r['PedidoEsPrueba'],'idProgramacion'=>(int)$id,'idCarga'=>$r['IdCarga']?(int)$r['IdCarga']:null,'programacion'=>$code,'revision'=>(int)$r['Revision'],'pedido'=>$orderCode,'proforma'=>$order['aceptacion']['proforma']['numero']??'',
                'fecha'=>$date,'carga'=>$r['Secuencia']?(int)$r['Secuencia']:null,'emisor'=>$order['empresa']['RAZONSOCIAL']??'','rucEmisor'=>$order['empresa']['RUC']??'','cliente'=>$order['cliente']['Cliente']??'','documentoCliente'=>$order['cliente']['DniRuc']??'','obra'=>$order['obra']['Nombre']??'','direccionObra'=>$order['obra']['Direccion']??'',
                'm3Nominal'=>$quantity,'m3Real'=>null,'estado'=>$r['Estado'],'estadoCarga'=>$r['EstadoCarga']??'SIN_DIVIDIR','incluida'=>$included,'elemento'=>$r['Elemento'],'slump'=>$r['Slump'],'unidadSlump'=>$r['UnidadSlump'],'descarga'=>$r['Descarga'],
                'receta'=>$recipe?($recipe['Codigo'].' v'.$recipe['Version']):'Pendiente','estadoReceta'=>$d['Estado']??'SIN_RECETA','mixer'=>$load['mixer']['Codigo']??$snapshot['datos']['mixer']['Codigo']??'Pendiente','conductor'=>$load['conductor']['nombre']??$snapshot['datos']['conductor']['nombre']??$r['Conductor']??'Pendiente','responsables'=>$owners[$id],'guia'=>null,'salidaReal'=>null,'incidencias'=>[]];
            if(!$recipe||empty($recipe['componentes']))$row['incidencias'][]='Sin dosificación registrada: insumos pendientes de calcular.';
            if($d&&$d['Estado']!=='APROBADA')$row['incidencias'][]='Receta no aprobada: cantidades provisionales.';
            if(!$r['IdCarga'])$row['incidencias'][]='Volumen pendiente de dividir en cargas.';
            if($included){$activePlans[$id]=true;$summary['m3Nominal']=bcadd($summary['m3Nominal'],$quantity,6);if($r['IdCarga'])$summary['cargas']++;else $summary['sinDividir']++;
                if(!$recipe||empty($recipe['componentes'])){$summary['sinReceta']++;$summary['insumosParciales']=true;}if($d&&$d['Estado']!=='APROBADA')$summary['recetaNoAprobada']++;
                $day=substr($date,0,10);if(!isset($days[$day]))$days[$day]=['fecha'=>$day,'m3Nominal'=>'0.000000','m3Real'=>null,'cargas'=>0,'sinDividir'=>0];$days[$day]['m3Nominal']=bcadd($days[$day]['m3Nominal'],$quantity,6);if($r['IdCarga'])$days[$day]['cargas']++;else $days[$day]['sinDividir']++;
            }else $summary['excluidas']++;
            foreach($recipe['componentes']??[] as $c){
                $required=bcmul($c['CantidadNominal'],$quantity,6);$component=['programacion'=>$code,'pedido'=>$orderCode,'idCarga'=>$row['idCarga'],'carga'=>$row['carga'],'fecha'=>$date,'receta'=>$row['receta'],'emisor'=>$row['emisor'],'cliente'=>$row['cliente'],'producto'=>$c['Producto']??('Insumo '.$c['IdProducto']),'idProducto'=>(int)$c['IdProducto'],'unidad'=>$c['Unidad'],'base'=>$c['BaseDosificacion'],'dosisPorM3'=>$c['CantidadNominal'],'m3Nominal'=>$quantity,'nominal'=>$required,'corregido'=>null,'real'=>null,'diferencia'=>null,'incluida'=>$included,'provisional'=>!$d||$d['Estado']!=='APROBADA'];
                $materials[]=$component;if(!$included)continue;$key=$c['IdProducto'].'|'.$c['Unidad'].'|'.$c['BaseDosificacion'];if(!isset($totals[$key]))$totals[$key]=['idProducto'=>(int)$c['IdProducto'],'producto'=>$component['producto'],'unidad'=>$c['Unidad'],'base'=>$c['BaseDosificacion'],'nominal'=>'0.000000','corregido'=>null,'real'=>null,'diferencia'=>null,'provisional'=>false];$totals[$key]['nominal']=bcadd($totals[$key]['nominal'],$required,6);$totals[$key]['provisional']=$totals[$key]['provisional']||$component['provisional'];
            }
            if($row['esPrueba'])$summary['incluyePruebas']=true;
            $rows[]=$row;
        }
        $summary['programaciones']=count($activePlans);$alerts=$this->stockAlerts($company,$warehouse,$until);
        $plant=$this->run('SELECT Almacen FROM Lo_Almacen WHERE IdAlmacen=?',[$warehouse])->fetchColumn();
        return ['desde'=>$from,'hasta'=>$until,'consultadoEn'=>cxNow(),'zonaHoraria'=>'America/Lima','idAlmacen'=>$warehouse,'planta'=>$plant,'emisor'=>$issuer?$issuer['RAZONSOCIAL']:'Ambas empresas','estado'=>$state,'gestion'=>$this->group($company)['Nombre']??$this->company($company)['RAZONSOCIAL'],
            'resumen'=>$summary,'dias'=>array_values($days),'cargas'=>$rows,'insumos'=>$materials,'totalesInsumos'=>array_values($totals),'stockActual'=>$alerts,
            'criterio'=>'Fecha de preparación programada de cada carga, revisión vigente. Canceladas/reprogramadas se excluyen de los totales. Los valores nominales no acreditan producción ni consumo real.',
            'criterioStock'=>'Stock físico actual y reservas de la planta compartida por ambos emisores. Las alertas incluyen borradores hasta el fin del período y reservas vigentes de todas las fechas; no son saldos históricos ni se filtran por emisor.',
            'datosRealesDisponibles'=>false];
    }
}
