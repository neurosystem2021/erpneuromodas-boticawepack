<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();foreach($db->query("SELECT p.Producto,o.IdProforma FROM Gen_Producto p LEFT JOIN Ve_PreOrden o ON o.IdPreOrden=p.IdPreOrden WHERE p.EsHabitacion=1 AND p.EstadoHabitacion<>1 AND p.Anulado=0")->fetchAll(PDO::FETCH_ASSOC) as $r)echo json_encode($r),PHP_EOL;
