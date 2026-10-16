<?php
if(PHP_SAPI!=='cli')exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$stmt=$db->prepare("SELECT IdCajaBanco,Importe FROM Cb_CajaBanco WHERE COALESCE(Anulado,0)=0 AND IdTipoCajaBanco=5 AND FechaDoc >= '2026-10-01' AND FechaDoc < '2026-10-17' AND ((Concepto=? AND Importe=1000) OR (Concepto=? AND Importe=3000) OR (Concepto=? AND Importe=1900))");
$stmt->execute(['compra camaras','compra de camaras op 23456','compra de computadoras deltron']);$rows=$stmt->fetchAll(PDO::FETCH_ASSOC);
if(count($rows)!==3 || abs(array_sum(array_column($rows,'Importe'))-5900)>0.001)throw new RuntimeException('Los movimientos confirmados cambiaron; revisar antes de clasificar.');
$file='C:/xampp/htdocs/boticav1/config/management-report.local.php';$cfg=is_file($file)?require $file:[];
if(is_file($file))copy($file,'C:/xampp/htdocs/boticav1/storage/management-report-backups/classification-before-'.date('Ymd-His').'.php');
foreach($rows as $row)$cfg['movimientos'][(int)$row['IdCajaBanco']]='proveedor';
file_put_contents($file,"<?php\n// Clasificación confirmada por el usuario: compras de mercadería.\nreturn ".var_export($cfg,true).";\n");
echo "PASS 3 movimientos / S/ 5900 clasificados como proveedores. No se modificaron registros de caja.\n";