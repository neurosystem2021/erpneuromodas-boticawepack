<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
echo 'Saved contract versions: '.$db->query('SELECT COUNT(*) FROM Ve_ProformaContrato')->fetchColumn().PHP_EOL;
foreach($db->query('SELECT IdProforma,Version,Datos FROM Ve_ProformaContrato ORDER BY IdProforma,Version') as $row){$data=json_decode($row['Datos'],true);echo 'Proforma '.$row['IdProforma'].' version '.$row['Version'].' reference '.($data['form']['numeroContrato']??'(previous proposal reference)').PHP_EOL;}
