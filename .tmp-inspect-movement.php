<?php
$db = new PDO('mysql:host=127.0.0.1;dbname=nst;charset=utf8', 'root', '');
foreach (['Seg_UsuarioPerfil','Lo_Movimiento','Lo_MovimientoTipo'] as $table) {
 echo $table, "\n";
 foreach ($db->query('SHOW COLUMNS FROM '.$table)->fetchAll(PDO::FETCH_ASSOC) as $column) echo $column['Field'], ':', $column['Type'], "\n";
}
echo json_encode($db->query('SELECT * FROM Seg_UsuarioPerfil')->fetchAll(PDO::FETCH_ASSOC)), "\n";
echo json_encode($db->query('SELECT IdMovimientoTipo, TipoMovimiento, Tipo, VaRegCompra FROM Lo_MovimientoTipo')->fetchAll(PDO::FETCH_ASSOC)), "\n";
