<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
foreach(['Cb_CajaBanco','Cb_TipoCajaBanco','Cb_Cuenta','Ve_DocVenta','Ve_DocVentaDet'] as $table){echo $table.': '.implode(', ',array_column($db->query('DESCRIBE '.$table)->fetchAll(),'Field')).PHP_EOL;}
echo json_encode($db->query('SELECT * FROM Cb_TipoCajaBanco')->fetchAll(),JSON_UNESCAPED_UNICODE).PHP_EOL;
echo json_encode($db->query('SELECT IdTipoCajaBanco,COUNT(*) Registros,SUM(Importe) Importe FROM Cb_CajaBanco WHERE Anulado=0 GROUP BY IdTipoCajaBanco')->fetchAll(),JSON_UNESCAPED_UNICODE).PHP_EOL;
echo json_encode($db->query('SELECT IdCuenta,Cuenta FROM Cb_Cuenta')->fetchAll(),JSON_UNESCAPED_UNICODE).PHP_EOL;
