<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$q=$db->query('SELECT IdImpresora,NombreImpresora,Ip,Puerto FROM Gen_Impresora WHERE IdImpresora=1');$old=$q->fetch(PDO::FETCH_ASSOC);
if(!$old||(int)$old['Puerto']!==9100||!in_array($old['Ip'],['192.168.1.123','192.168.1.68'],true))throw new RuntimeException('Printer changed; inspect before updating');
if($old['Ip']==='192.168.1.68'){echo "Printer catalog already points to 192.168.1.68:9100.\n";exit;}
file_put_contents(__DIR__.'/../output/printer-address-before-20261016.json',json_encode($old,JSON_PRETTY_PRINT));
$update=$db->prepare('UPDATE Gen_Impresora SET Ip=? WHERE IdImpresora=? AND Ip=? AND Puerto=?');$update->execute(['192.168.1.68',1,$old['Ip'],9100]);if($update->rowCount()!==1)throw new RuntimeException('Concurrent printer change');
echo "Printer 1 updated to 192.168.1.68:9100. Category mappings unchanged.\n";
