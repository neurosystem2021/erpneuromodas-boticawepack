<?php
require __DIR__.'/../integration/proforma-edit/proforma-contract-numbering.php';
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$db->exec('CREATE TEMPORARY TABLE Ve_ContratoSerie(Anio INT PRIMARY KEY,Ultimo INT) ENGINE=InnoDB');
$db->exec('CREATE TEMPORARY TABLE Ve_ContratoNumero(IdProforma INT PRIMARY KEY,Anio INT,Numero INT,UNIQUE KEY(Anio,Numero)) ENGINE=InnoDB');
$db->exec('INSERT INTO Ve_ContratoSerie VALUES(2026,199)');
function numberCheck($ok,$message){if(!$ok)throw new RuntimeException($message);}
$preview=qcSequenceInfo($db,18,2026);numberCheck($preview['numeroContrato']==='200-2026'&&!$preview['numeroAsignado'],'Starts at 200');
qcSequenceInfo($db,19,2026);numberCheck((int)$db->query('SELECT Ultimo FROM Ve_ContratoSerie WHERE Anio=2026')->fetchColumn()===199,'Opening form consumes no numbers');
$db->beginTransaction();numberCheck(qcAssignNumber($db,18,2026)==='200-2026','First assignment');$db->commit();
$db->beginTransaction();numberCheck(qcAssignNumber($db,18,2027)==='200-2026','Versions preserve original number and year');$db->commit();
numberCheck(qcSequenceInfo($db,19,2026)['numeroContrato']==='201-2026','Next number 201');
$db->beginTransaction();numberCheck(qcAssignNumber($db,19,2026)==='201-2026','Second assignment');$db->rollBack();
numberCheck(qcSequenceInfo($db,19,2026)['numeroContrato']==='201-2026','Failed transaction consumes no number');
$db->beginTransaction();numberCheck(qcAssignNumber($db,20,2027)==='001-2027','New year starts own sequence');$db->commit();
try{qcAssignNumber($db,21,2026);throw new LogicException('Unlocked assignment allowed');}catch(RuntimeException $expected){}
echo 'PASS independent annual numbering from 200, no reservation on preview, same number for all versions, rollback without gaps, separate years and transaction requirement. TEMPORARY tables only.';
