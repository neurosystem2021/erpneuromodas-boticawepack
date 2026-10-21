<?php
if(PHP_SAPI!=='cli')exit;require __DIR__.'/../../tools/concretera/fixture.php';require __DIR__.'/../../server/concretera/api/concretera/Domain.php';
$f=json_decode(file_get_contents(__DIR__.'/fixture.json'),true);if(!empty($f['removedAt'])||empty($f['guided']))throw new RuntimeException('Prueba no activa');$db=cxFixtureConnect($f['schema']);$owner=$f['guided']['owner'];$s=new CxService($db,'superadmin',true);$s->assignReference($owner,['entidad'=>'PRODUCTO','idERP'=>7]);
$q=$db->prepare('INSERT INTO Cx_OfertaProducto(IdEmpresa,IdProducto,Version,Clase,Resistencia,Servicio,Unidad,Moneda,PrecioSinImpuesto,Fuente,UsuarioReg,FechaReg) VALUES(?,?,1,?,?,?,?,?,?,?,?,?)');
$q->execute([$owner,2,'CONCRETO',210,null,'m3','PEN','410',cxJson(['tipo'=>'DATOS FICTICIOS']),'superadmin',cxNow()]);$q->execute([$owner,7,'SERVICIO',null,'BOMBEO','m3','PEN','60',cxJson(['tipo'=>'DATOS FICTICIOS']),'superadmin',cxNow()]);echo 'PASS solo oferta de prueba en esquema propietario'.PHP_EOL;
