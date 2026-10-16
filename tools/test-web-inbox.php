<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$base=" FROM Ve_Proforma p WHERE p.UsuarioReg='TIENDA' AND COALESCE(p.Anulado,0)=0";
$noBill=" AND NOT EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)";
$all=(int)$db->query('SELECT COUNT(*)'.$base)->fetchColumn();$pending=(int)$db->query('SELECT COUNT(*)'.$base.$noBill)->fetchColumn();$billed=(int)$db->query('SELECT COUNT(*)'.$base.str_replace('NOT EXISTS','EXISTS',$noBill))->fetchColumn();
if($all!==$pending+$billed)throw new Exception('Invalid partitions');
$db->beginTransaction();try{
$id=$db->query('SELECT p.IdProforma'.$base.$noBill.' LIMIT 1')->fetchColumn();
if($id){$stmt=$db->prepare("UPDATE Ve_Proforma p SET p.Anulado=1 WHERE p.IdProforma=? AND p.UsuarioReg='TIENDA' AND COALESCE(p.Anulado,0)=0".$noBill);$stmt->execute(array($id));if($stmt->rowCount()!==1)throw new Exception('Delete failed');}
}finally{$db->rollBack();}
echo "Partitions and reversible removal OK: $pending open, $billed billed.\n";
