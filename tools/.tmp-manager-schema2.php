<?php require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();echo json_encode($db->query('SELECT IdTipoDoc,TipoDoc,CodSunat FROM Ve_DocVentaTipoDoc')->fetchAll(),JSON_UNESCAPED_UNICODE);foreach(['Cb_CajaBancoDet','Ve_DocVentaMetodoPagoDet','Gen_Producto'] as $t){echo PHP_EOL.$t.': '.implode(',',array_column($db->query('DESCRIBE '.$t)->fetchAll(),'Field'));}

