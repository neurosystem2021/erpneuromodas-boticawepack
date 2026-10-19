<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach(['Lo_Movimiento','Lo_MovimientoDetalle','Gen_Producto_Totales','Lo_MovimientoTipo','Lo_Almacen'] as $table){$s=$db->prepare('SELECT TABLE_TYPE,ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=?');$s->execute([$table]);echo $table.' '.json_encode($s->fetch(PDO::FETCH_ASSOC)).PHP_EOL;echo json_encode($db->query('SHOW COLUMNS FROM '.$table)->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;}
echo 'Tipos '.json_encode($db->query('SELECT IdMovimientoTipo,TipoMovimiento,Tipo,VaRegCompra+0 AS VaRegCompra FROM Lo_MovimientoTipo WHERE Tipo IN (0,1,2)')->fetchAll(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'Triggers '.json_encode($db->query("SELECT EVENT_OBJECT_TABLE,ACTION_TIMING,EVENT_MANIPULATION FROM information_schema.TRIGGERS WHERE TRIGGER_SCHEMA=DATABASE() AND EVENT_OBJECT_TABLE IN ('Lo_Movimiento','Lo_MovimientoDetalle','Gen_Producto_Totales')")->fetchAll(PDO::FETCH_ASSOC)).PHP_EOL;

echo 'View '.json_encode($db->query('SHOW CREATE VIEW Gen_Producto_Totales')->fetch(PDO::FETCH_ASSOC),JSON_UNESCAPED_UNICODE).PHP_EOL;
echo 'Mode '.$db->query('SELECT @@SESSION.sql_mode')->fetchColumn().PHP_EOL;
