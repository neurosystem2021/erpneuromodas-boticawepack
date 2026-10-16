<?php
if(PHP_SAPI!=='cli')exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';require 'C:/xampp/htdocs/boticav1/config/crm.php';require 'C:/xampp/htdocs/boticav1/api/crm/src/CrmDomain.php';require 'C:/xampp/htdocs/boticav1/api/crm/src/CrmSyncService.php';
$s=new CrmSyncService(appMysqliConnection(),1);$offset=0;$total=0;do{$rows=$s->preview(1000,$offset);if($rows)$total+=$s->apply($rows);$offset+=1000;}while(count($rows)===1000);echo 'CRM sincronizado: '.$total." clientes reales. No se enviaron mensajes.\n";
