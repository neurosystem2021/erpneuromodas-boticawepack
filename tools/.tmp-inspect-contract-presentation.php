<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$s=$db->prepare('SELECT IdContrato,IdProforma,Version,Datos FROM Ve_ProformaContrato WHERE IdContrato=?');$s->execute([2]);$r=$s->fetch(PDO::FETCH_ASSOC);if(!$r)throw new RuntimeException('Contract not found');$d=json_decode($r['Datos'],true);
echo json_encode(['id'=>(int)$r['IdContrato'],'proforma'=>(int)$r['IdProforma'],'version'=>(int)$r['Version'],'template'=>$d['templateVersion']??1,'number'=>$d['form']['numeroContrato']??'','oldRubric'=>($d['consultantSignature']['sha256']??'')===hash_file('sha256',__DIR__.'/../static/images/consultor-jhon-rubrica-original.png')]);
