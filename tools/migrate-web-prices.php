<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8', 'root', '', array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION));
foreach (array('PrecioWeb','PrecioAnterior') as $column) {
 $check=$db->query("SHOW COLUMNS FROM Gen_Producto LIKE '$column'");
 if (!$check->fetch()) $db->exec("ALTER TABLE Gen_Producto ADD COLUMN `$column` DECIMAL(18,4) NULL DEFAULT NULL");
 echo "$column ready\n";
}
$db->beginTransaction();
$id=$db->query('SELECT IdProducto FROM Gen_Producto LIMIT 1')->fetchColumn();
$db->exec("UPDATE Gen_Producto SET PrecioWeb=80,PrecioAnterior=100 WHERE IdProducto=".(int)$id);
$r=$db->query("SELECT COALESCE(PrecioWeb,PrecioContado) precio,PrecioAnterior anterior FROM Gen_Producto WHERE IdProducto=".(int)$id)->fetch(PDO::FETCH_ASSOC);
if ((float)$r['precio']!==80.0 || (float)$r['anterior']!==100.0) throw new Exception('Price test failed');
$db->rollBack();echo "Price persistence verified; test rolled back\n";
