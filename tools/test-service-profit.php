<?php
require __DIR__.'/../integration/service-profit/report.php';
require 'C:/xampp/htdocs/boticav1/config/database.php';
$db=appPdoConnection();
$db->exec('CREATE TEMPORARY TABLE Gen_Producto (IdProducto INT, Producto VARCHAR(80), OpcContable INT, PrecioCosto DECIMAL(12,2))');
$db->exec('CREATE TEMPORARY TABLE Ve_DocVenta (IdDocVenta INT, UsuarioReg VARCHAR(50), FechaDoc DATETIME, Anulado INT, IdTipoDoc INT)');
$db->exec('CREATE TEMPORARY TABLE Ve_DocVentaDet (IdDocVenta INT, IdProducto INT, Cantidad DECIMAL(12,2), Precio DECIMAL(12,2), PrecioCosto DECIMAL(12,2) NULL, Descuento DECIMAL(12,2))');
$db->exec("INSERT INTO Gen_Producto VALUES (1,'Servicio',2,99),(2,'Mercaderia',1,10),(3,'Servicio sin costo',2,NULL)");
$db->exec("INSERT INTO Ve_DocVenta VALUES (1,'ana','2026-10-01 00:00:00',0,3),(2,'luis','2026-10-16 23:59:59',0,3),(3,'ana','2026-10-17 00:00:00',0,3),(4,'ana','2026-10-10',1,3),(5,'ana','2026-10-10',0,11)");
$db->exec('INSERT INTO Ve_DocVentaDet VALUES (1,1,2,100,20,10),(1,2,1,200,10,0),(2,1,1,80,0,0),(2,3,1,50,NULL,0),(3,1,1,100,20,0),(4,1,1,100,20,0),(5,1,1,100,20,0)');
$f=['fechaInicio'=>'2026-10-01','fechaFin'=>'2026-10-16','ServicioFlag'=>'1','MercaderiaFlag'=>'0','usuarioReg'=>''];
$check=function($condition,$message){if(!$condition)throw new RuntimeException($message);};
$r=serviceProfitReport($db,$f);$check(count($r)===3,'service rows and dates');
$check(array_sum(array_column($r,'TotalVenta'))===320.0,'net sales after discount');
$check(array_sum(array_column($r,'TotalCosto'))===40.0,'historical cost including zero');
$check(array_sum(array_column($r,'UtilidadBruta'))===280.0,'gross profit');
$r=serviceProfitReport($db,array_merge($f,['usuarioReg'=>'ana']));$check(count($r)===1 && $r[0]['UtilidadBruta']===150.0,'exact user filter');
$r=serviceProfitReport($db,array_merge($f,['usuarioReg'=>"ana' OR 1=1 --"]));$check(count($r)===0,'bound user');
$r=serviceProfitReport($db,array_merge($f,['ServicioFlag'=>'0','MercaderiaFlag'=>'1']));$check(count($r)===1 && $r[0]['UtilidadBruta']===190.0,'merchandise separated');
try {serviceProfitReport($db,array_merge($f,['fechaFin'=>'2026-02-30']));throw new RuntimeException('invalid date accepted');}catch(InvalidArgumentException $expected){}
echo "PASS services only, all users, exact user, dates inclusive, discounts, historical/zero costs, cancelled/excluded docs, validation. Temporary tables only.\n";
