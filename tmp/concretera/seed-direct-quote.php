<?php
if(PHP_SAPI!=='cli')exit;
require __DIR__.'/../../tools/concretera/fixture.php';require __DIR__.'/../../server/concretera/api/concretera/Domain.php';
$f=json_decode(file_get_contents(__DIR__.'/fixture.json'),true);if(!empty($f['removedAt']))throw new RuntimeException('Prueba retirada');
$db=cxFixtureConnect($f['schema']);$owner=$f['guided']['owner'];$s=new CxService($db,'superadmin',true);
foreach([2,7] as $id)$s->assignReference($owner,['entidad'=>'PRODUCTO','idERP'=>$id]);$s->assignReference($owner,['entidad'=>'ALMACEN','idERP'=>1]);
$q=$db->prepare('INSERT INTO Cx_OfertaProducto(IdEmpresa,IdProducto,Version,Clase,Resistencia,Servicio,Unidad,Moneda,PrecioSinImpuesto,Fuente,UsuarioReg,FechaReg) VALUES(?,?,1,?,?,?,?,?,?,?,?,?)');
foreach([[2,'CONCRETO',210,null,'410'],[7,'SERVICIO',null,'BOMBEO','60']] as $p)$q->execute([$owner,$p[0],$p[1],$p[2],$p[3],'m3','PEN',$p[4],cxJson(['tipo'=>'PRUEBA AISLADA']),'superadmin',cxNow()]);
if($s->catalog($owner,'clientes')||$s->listing($owner,'obras'))throw new RuntimeException('La prueba debe comenzar sin cliente ni obra vinculados');
echo "PASS prueba con productos y planta, sin cliente ni obra vinculados.\n";
