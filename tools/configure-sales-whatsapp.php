<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$id=(int)$db->query('SELECT IDEMPRESA FROM Gen_Empresa ORDER BY IDEMPRESA LIMIT 1')->fetchColumn();
$db->beginTransaction();try{$q=$db->prepare("SELECT COUNT(*) FROM Gen_EmpresaConfig WHERE IdEmpresa=? AND MetaKey='whatsapp_ventas'");$q->execute(array($id));
if($q->fetchColumn()){$s=$db->prepare("UPDATE Gen_EmpresaConfig SET MetaValue=? WHERE IdEmpresa=? AND MetaKey='whatsapp_ventas'");$s->execute(array('51954370221',$id));}else{$s=$db->prepare('INSERT INTO Gen_EmpresaConfig(IdEmpresa,MetaKey,MetaValue) VALUES(?,?,?)');$s->execute(array($id,'whatsapp_ventas','51954370221'));}$db->commit();echo "Temporary sales WhatsApp configured.\n";}catch(Throwable $e){$db->rollBack();throw $e;}
