<?php
require __DIR__.'/migrate-movement-audit.php';
foreach(['Gen_Producto','Gen_Empresa','Gen_EmpresaConfig','Ve_DocVenta','Ve_DocVentaDet','Ve_DocVentaCliente','Seg_Usuario'] as $t){echo $t.': ';try{echo implode(',', $db->query('SHOW COLUMNS FROM '.$t)->fetchAll(PDO::FETCH_COLUMN));}catch(Throwable $e){echo 'MISSING';}echo "\n";}
echo 'TALLA: '.$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='Gen_ProductoTalla'")->fetchColumn()."\n";
