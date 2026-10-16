<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$db->beginTransaction();
$stmt=$db->prepare("SELECT o.IdPreOrden,o.IdProforma FROM Ve_PreOrden o JOIN Gen_Producto p ON p.IdPreOrden=o.IdPreOrden WHERE p.IdProducto=4670 AND p.Producto='PROYECTO 308' FOR UPDATE");$stmt->execute();$before=$stmt->fetch(PDO::FETCH_ASSOC);
if(!$before || (int)$before['IdPreOrden']!==174 || (int)$before['IdProforma']!==10){$db->rollBack();throw new Exception('Project association differs; no change made');}
file_put_contents(__DIR__.'/../.tmp-project308-proforma-before.json',json_encode($before));
$stmt=$db->prepare('UPDATE Ve_PreOrden SET IdProforma=NULL WHERE IdPreOrden=:id AND IdProforma=:proforma');$stmt->execute(['id'=>174,'proforma'=>10]);
if($stmt->rowCount()!==1){$db->rollBack();throw new Exception('Unexpected update count');}$db->commit();
echo "Removed only incorrect proforma association from project 308. Proforma and project amounts unchanged.\n";
