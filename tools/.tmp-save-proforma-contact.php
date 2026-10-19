<?php
if(PHP_SAPI!=='cli')exit(1);
require 'C:/xampp/htdocs/boticav1/config/database.php';$p=appPdoConnection();$p->beginTransaction();
$row=$p->query('SELECT IDEMPRESA,RUC,TELEFONO,CELULAR,EMAIL FROM GEN_EMPRESA WHERE IDEMPRESA=1 FOR UPDATE')->fetch(PDO::FETCH_ASSOC);
if(!$row||$row['RUC']!=='20573027125')throw new RuntimeException('Unexpected company');
$dir='C:/xampp/accounting-private/proforma-contact-'.date('Ymd-His');if(!mkdir($dir,0700,true))throw new RuntimeException('Backup failed');$backup=$dir.'/empresa-contacto.json';file_put_contents($backup,json_encode($row,JSON_PRETTY_PRINT));if(json_decode(file_get_contents($backup),true)!==$row)throw new RuntimeException('Backup verification failed');
$stmt=$p->prepare('UPDATE GEN_EMPRESA SET TELEFONO=?,CELULAR=?,EMAIL=? WHERE IDEMPRESA=1');$stmt->execute(['062 511550 / 061 603490','968 411589 / 954 370221','jhonwj@hotmail.com']);$p->commit();echo 'Company contacts saved. Verified private backup: '.$dir;
