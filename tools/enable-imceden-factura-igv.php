<?php
require 'C:/xampp/htdocs/boticav1/config/database.php';
$args=getopt('', ['database:','empresa:','apply']);
if (empty($args['database']) || empty($args['empresa'])) throw new RuntimeException('Indique --database y --empresa exactos para verificar el destino.');
$cfg=appDatabaseConfig();$cfg['dbname']=$args['database'];
$db=new PDO(appDatabaseDsn($cfg),$cfg['user'],$cfg['pass'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$company=(string)$db->query('SELECT RAZONSOCIAL FROM GEN_EMPRESA ORDER BY IDEMPRESA LIMIT 1')->fetchColumn();
if ($company!==$args['empresa']) throw new RuntimeException('La empresa de la base no coincide. No se modificó nada.');
$columns=$db->query('SHOW COLUMNS FROM Ve_DocVentaTipoDoc')->fetchAll(PDO::FETCH_COLUMN);
if (!in_array('Anulado',$columns,true)) throw new RuntimeException('El destino no tiene el esquema verificado de IMCEDEN. No se modificó nada.');
$before=$db->query("SELECT * FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=8 OR TipoDoc='FACTURA/IGV ELEC.'")->fetchAll();
foreach ($before as $row) if ((int)$row['IdTipoDoc']!==8 || $row['TipoDoc']!=='FACTURA/IGV ELEC.' || $row['CodSunat']!=='01' || (int)$row['TieneIgv']!==1 || $row['CodigoIgv']!=='10' || (int)$row['EsElectronico']!==1) throw new RuntimeException('El código 8 ya corresponde a otro tipo. No se sobrescribió.');
$series=$db->query('SELECT IdDocVentaPuntoVenta,IdDocVentaTipoDoc,Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaTipoDoc=8')->fetchAll();
if (!$series) throw new RuntimeException('Faltan las series de IMCEDEN para el tipo 8. No se crearon series de otra empresa.');
foreach ($series as $row) if (!preg_match('/^F[A-Z0-9]{3}$/iD',$row['Serie'])) throw new RuntimeException('Serie electrónica no válida. No se modificó nada.');
echo json_encode(['database'=>$cfg['dbname'],'empresa'=>$company,'tipoAntes'=>$before,'seriesConservadas'=>$series],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT).PHP_EOL;
if (!array_key_exists('apply',$args)) {echo "Revisión únicamente. No se modificó la base.\n";exit;}
$backup='C:/xampp/accounting-private/imceden-factura-igv-tipo-'.date('Ymd-His').'-'.bin2hex(random_bytes(3)).'.json';
if (!is_dir(dirname($backup))) throw new RuntimeException('No existe el directorio privado de respaldos.');
if (file_put_contents($backup,json_encode(['database'=>$cfg['dbname'],'empresa'=>$company,'tipoAntes'=>$before,'series'=>$series],JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT),LOCK_EX)===false) throw new RuntimeException('No se pudo guardar el respaldo.');
try {
    $db->beginTransaction();
    $db->exec(file_get_contents(__DIR__.'/../server/migrations/imceden-factura-igv.sql'));
    $after=$db->query('SELECT * FROM Ve_DocVentaTipoDoc WHERE IdTipoDoc=8')->fetch();
    if (!$after || $after['TipoDoc']!=='FACTURA/IGV ELEC.' || (int)$after['TieneIgv']!==1 || $after['CodigoIgv']!=='10' || (int)$after['Anulado']!==0) throw new RuntimeException('No se pudo verificar el tipo habilitado.');
    $check=$db->query('SELECT IdDocVentaPuntoVenta,IdDocVentaTipoDoc,Serie FROM Ve_DocVentaPuntoVentaDet WHERE IdDocVentaTipoDoc=8')->fetchAll();
    if ($check!==$series) throw new RuntimeException('Las series cambiaron durante la operación.');
    $db->commit();
    echo 'FACTURA/IGV ELEC. habilitada en '.$cfg['dbname'].'. Series conservadas; no se emitieron facturas. Respaldo: '.$backup.PHP_EOL;
} catch (Throwable $e) {if($db->inTransaction())$db->rollBack();throw $e;}
