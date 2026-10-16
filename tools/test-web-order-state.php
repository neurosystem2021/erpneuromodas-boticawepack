<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$db->beginTransaction();try{
$id=$db->query("SELECT IdProforma FROM Ve_Proforma WHERE UsuarioReg='TIENDA' LIMIT 1")->fetchColumn();
if(!$id)throw new Exception('No web proforma to verify');
$stmt=$db->prepare("UPDATE Ve_Proforma SET WebCargado=1 WHERE IdProforma=? AND UsuarioReg='TIENDA'");$stmt->execute(array($id));
if((int)$db->query('SELECT WebCargado FROM Ve_Proforma WHERE IdProforma='.(int)$id)->fetchColumn()!==1)throw new Exception('Status not saved');
echo "Order loaded state verified; rolled back\n";
}finally{$db->rollBack();}
