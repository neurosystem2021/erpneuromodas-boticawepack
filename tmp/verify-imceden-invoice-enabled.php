<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$cfg=appDatabaseConfig();$cfg['dbname']='fact_imceden';$db=new PDO(appDatabaseDsn($cfg),$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$row=$db->query("SELECT IdTipoDoc,TipoDoc,CodSunat,TieneIgv,CodigoIgv,EsElectronico,Anulado FROM Ve_DocVentaTipoDoc WHERE Anulado=0 AND TipoDoc LIKE '%FACTURA/IGV%'")->fetch();
if(!$row || (int)$row['IdTipoDoc']!==8 || (int)$row['TieneIgv']!==1 || $row['CodigoIgv']!=='10')throw new RuntimeException('No se pudo verificar el catálogo');
$series=$db->query('SELECT IdDocVentaPuntoVenta,Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaTipoDoc=8 ORDER BY IdDocVentaPuntoVenta')->fetchAll();
if(array_column($series,'Serie')!==['F104','F005'])throw new RuntimeException('Series inesperadas');
echo json_encode(['verificado'=>$row,'series'=>$series,'facturasEmitidasPorEstaVerificacion'=>0],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
