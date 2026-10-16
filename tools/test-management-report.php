<?php
require __DIR__.'/../integration/management-report/api/management-report-core.php';
$f=managementFilters(['desde'=>'2026-10-01','hasta'=>'2026-10-03','proyeccion'=>30,'clasificacion'=>[5=>'traslado']]);
$sales=[['fecha'=>'2026-10-01','sunat'=>'03','cantidad'=>2,'precio'=>100,'descuento'=>10,'costoUnitario'=>20,'historico'=>20,'opc'=>2],['fecha'=>'2026-10-02','sunat'=>'01','cantidad'=>1,'precio'=>200,'descuento'=>0,'costoUnitario'=>100,'historico'=>100,'opc'=>1]];
$types=[['id'=>1,'nombre'=>'Proveedor'],['id'=>3,'nombre'=>'Gasto'],['id'=>5,'nombre'=>'Deposito'],['id'=>6,'nombre'=>'Personal'],['id'=>7,'nombre'=>'Antiguo']];
$cash=[];foreach([[1,1,300,'Compra'],[3,1,20,'Gasto'],[6,1,80,'Planilla'],[5,1,40,'Deposito'],[8,2,100,'Yape'],[7,1,50,'YAPE/PLIN - B001-1'],[2,0,60,'Adelanto']] as $i=>$e)$cash[]=['fecha'=>'2026-10-02','tipoId'=>$e[0],'direccion'=>$e[1],'importe'=>$e[2],'concepto'=>$e[3],'clienteId'=>0,'operacion'=>'Fixture','id'=>$i];
$check=function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$r=managementBuild($sales,$cash,$types,$f);$t=$r['totales'];
$check($t['venta']===390.0,'net sales');$check($t['bruta']===250.0,'gross profit');$check($t['neta']===150.0,'supplier payments not double counted');
$check($t['salidasExternas']===400.0,'true outflows');$check($t['salidasRegistradas']===440.0,'registered outflows');$check($t['cobrosDigitales']===150.0,'new and old Yape sum, not outflow');
$check(count($r['diario'])===3 && $r['diario'][2]['venta']===0.0,'zero activity days');$check($r['promedio']===50.0 && $r['proyectada']===1500.0,'calendar day projection');
$sales[0]['sunat']='07';$r=managementBuild($sales,[],$types,$f);$check($r['totales']['venta']===10.0 && $r['totales']['bruta']===-50.0,'credit notes reverse revenue and cost');$sales[0]['sunat']='03';
$sales[0]['costoUnitario']=null;$sales[0]['historico']=null;$r=managementBuild($sales,$cash,$types,$f);$check($r['totales']['neta']===null && $r['totales']['costo']===null && $r['proyectada']===null,'missing cost must not become zero');$sales[0]['costoUnitario']=20;$sales[0]['historico']=20;
$cash[]=['id'=>999,'fecha'=>'2026-10-02','tipoId'=>99,'direccion'=>1,'importe'=>40,'concepto'=>'Sin tipo','clienteId'=>0,'operacion'=>'Unknown'];$r=managementBuild($sales,$cash,$types,$f);$check(!$r['completo'],'unknown outflows block net');
$f['clasificacion']=[5=>'traslado',99=>'gasto'];$r=managementBuild($sales,$cash,[['id'=>99,'nombre'=>'Unknown']],$f);$check($r['completo'] && $r['totales']['neta']===110.0,'classification recalculates');
try{managementFilters(['desde'=>'2026-02-30','hasta'=>'2026-10-03']);throw new RuntimeException('invalid date accepted');}catch(InvalidArgumentException $expected){}
echo "PASS sales split, discounts, costs, credit notes, expense/personnel, supplier payment separation, neutral Yape, zero days, projections and incomplete data. No business data changed.\n";