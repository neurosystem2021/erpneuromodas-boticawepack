<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$pdo=appPdoConnection();
function tiendaJsonError($message,$status){throw new Exception($message);}
$s=file_get_contents(__DIR__.'/../deploy-src/store-order-fix/api.php');$a=strpos($s,"    if (\$action === 'pedido') {");$b=strpos($s,"    if (\$action === 'auth-status') {",$a);$block=substr($s,$a,$b-$a);
$block=str_replace('$pdo->beginTransaction();',"$"."pdo->exec('SAVEPOINT order_test');",$block);$block=str_replace('$pdo->commit();',"$"."pdo->exec('RELEASE SAVEPOINT order_test');",$block);$block=str_replace('exit;','return;',$block);
$_SESSION=array();$_SERVER['REQUEST_METHOD']='POST';$action='pedido';
$productId=$pdo->query('SELECT IdProducto FROM Gen_Producto WHERE Anulado=0 LIMIT 1')->fetchColumn();$body=array('productos'=>array(array('IdProducto'=>$productId,'cantidad'=>2)));
$pdo->beginTransaction();try{ob_start();eval($block);$data=json_decode(ob_get_clean(),true);if(empty($data['insertId']))throw new Exception('Missing order');$id=(int)$data['insertId'];$q=$pdo->query("SELECT COUNT(*) FROM Ve_ProformaDet WHERE IdProforma=$id AND Cantidad=2")->fetchColumn();if((int)$q!==1)throw new Exception('Missing quantity');echo "Guest order and quantity persisted correctly; test rolled back.\n";}finally{if($pdo->inTransaction())$pdo->rollBack();}
