<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';$cfg=appDatabaseConfig();$cfg['dbname']='fact_imceden';$db=new PDO(appDatabaseDsn($cfg),$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
echo json_encode($db->query('SELECT RAZONSOCIAL FROM GEN_EMPRESA ORDER BY IDEMPRESA LIMIT 1')->fetch(),JSON_UNESCAPED_UNICODE).PHP_EOL;foreach(['Ve_DocVentaTipoDoc','Ve_DocVentaPuntoVentaDet'] as $t)echo $t.' '.json_encode($db->query('SELECT * FROM '.$t)->fetchAll(),JSON_UNESCAPED_UNICODE).PHP_EOL;
