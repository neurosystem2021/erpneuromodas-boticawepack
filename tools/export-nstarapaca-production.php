<?php
// Build a clean, isolated production copy. Never delete data from nst.
if (PHP_SAPI !== 'cli') exit;
if (!in_array('--backup-only', $argv, true) && !in_array('--stock-zero', $argv, true)) {
    throw new RuntimeException('Choose --backup-only, or --stock-zero after confirmation of opening inventory.');
}
require 'C:/xampp/htdocs/boticav1/config/database.php';
function productionAssert($ok, $message) { if (!$ok) throw new RuntimeException($message); }
function productionRun(array $command, $input = null) {
    $process = proc_open($command, array(0 => $input ? array('file', $input, 'r') : array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, null, null, array('bypass_shell' => true));
    productionAssert(is_resource($process), 'Cannot start SQL tool.');
    if (!$input) fclose($pipes[0]);
    $out = stream_get_contents($pipes[1]); $error = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    productionAssert(proc_close($process) === 0, 'SQL tool failed; the live database was not modified.');
    return $out;
}
function productionSnapshot(PDO $db, array $tables) {
    $out = array();
    foreach ($tables as $table) {
        $rows = array(); $statement = $db->query('SELECT * FROM `' . $table . '`');
        while ($row = $statement->fetch(PDO::FETCH_NUM)) $rows[] = hash('sha256', serialize($row));
        sort($rows, SORT_STRING);
        $out[$table] = array('rows' => count($rows), 'sha256' => hash('sha256', implode('', $rows)), 'columns' => $db->query('SHOW COLUMNS FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC));
    }
    ksort($out); return $out;
}
$config = appDatabaseConfig();
productionAssert($config['dbname'] === 'nst' && in_array($config['host'], array('localhost', '127.0.0.1'), true) && (int)$config['port'] === 3306, 'Expected local nst connection.');
foreach (array('C:/xampp/htdocs/boticawebpack', 'C:/xampp/htdocs/boticav1') as $repo) productionAssert(trim(productionRun(array('git','-c','safe.directory=C:/xampp/htdocs/boticawebpack','-c','safe.directory=C:/xampp/htdocs/boticav1','-C',$repo,'branch','--show-current'))) === 'nstarapaca', 'Expected nstarapaca checkouts.');
$db = appPdoConnection();
productionAssert($db->query('SELECT RUC FROM Gen_Empresa WHERE IDEMPRESA=1')->fetchColumn() === '20573027125', 'Wrong company.');
$objects = $db->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
$tables = array_column($objects, 0);
productionAssert(!array_filter($tables, function ($name) { return strpos($name, 'cx_') === 0; }), 'Concretera tables are not allowed.');
$before = productionSnapshot($db, $tables);
$dir = 'C:/xampp/accounting-private/nstarapaca-produccion-' . date('Ymd-His');
productionAssert(mkdir($dir, 0700, true), 'Cannot create private export directory.');
mkdir($dir . '/paquete', 0700);
$escape = function ($value) { return '"' . str_replace(array('\\','"',"\n","\r"), array('\\\\','\\"','\\n','\\r'), $value) . '"'; };
$defaults = $dir . '/.client.cnf';
file_put_contents($defaults, "[client]\nhost=" . $escape($config['host']) . "\nport=3306\nuser=" . $escape($config['user']) . "\npassword=" . $escape($config['pass']) . "\n");
$cloneName = 'nst_prod_' . bin2hex(random_bytes(6)); $created = false;
try {
    $dump = $dir . '/nst-respaldo-completo.sql';
    $dumpArgs = array('C:/xampp/mysql/bin/mysqldump.exe','--defaults-extra-file='.$defaults,'--lock-all-tables','--routines','--events','--triggers','--hex-blob','--default-character-set=utf8mb4');
    productionRun(array_merge($dumpArgs, array('--result-file='.$dump, 'nst')));
    if (in_array('--backup-only', $argv, true)) {
        productionAssert(productionSnapshot($db, $tables) === $before, 'Source changed during backup.');
        file_put_contents($dir.'/backup-only.json', json_encode(array('source_database'=>'nst','objects'=>count($tables),'sha256'=>hash_file('sha256',$dump),'source_unchanged'=>true,'production_cleanup_not_executed'=>true), JSON_PRETTY_PRINT));
        file_put_contents('C:/xampp/htdocs/boticawebpack/tmp/nstarapaca-production/backup-directory.txt', $dir);
        echo json_encode(array('directory'=>$dir,'backup_only'=>true,'objects'=>count($tables),'source_unchanged'=>true), JSON_UNESCAPED_SLASHES) . PHP_EOL;
        return;
    }
    $db->exec('CREATE DATABASE `' . $cloneName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci'); $created = true;
    productionRun(array('C:/xampp/mysql/bin/mysql.exe','--defaults-extra-file='.$defaults,'--default-character-set=utf8mb4',$cloneName), $dump);
    $cloneConfig = $config; $cloneConfig['dbname'] = $cloneName;
    $copy = new PDO(appDatabaseDsn($cloneConfig), $config['user'], $config['pass'], array(PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC));
    productionAssert(productionSnapshot($copy, $tables) === $before, 'Restored copy differs from source.');
    productionAssert($copy->query('SELECT DATABASE()')->fetchColumn() === $cloneName, 'Wrong cleanup target.');
    $clear = explode(' ', 've_docventa ve_docventadet ve_docventametodopagodet ve_docventanotacredito ve_docventadetentrega ve_docventahistorialkit ve_docventaremision ve_docventaremisiondet remision ve_preorden ve_preordendet ve_preordendetpago ve_proforma ve_proformadet ve_proformaadjunto ve_proformacontrato ve_contratonumero ve_contratoserie lo_movimiento lo_movimientodetalle lo_movimientodetalleimpuesto lo_movimientoauditoria lo_ordencompra lo_ordencompradet cb_cierrecaja cb_letracaja cb_cajabancodet ag_cierrecaja ag_caja ag_comprobante gen_producto_serie prodstock tblkardex tblkardexvalor ali_posoperacion sunat_resumen rhu_comisionaplicacion');
    foreach ($clear as $table) productionAssert(in_array($table, $tables, true), 'Unexpected missing table: ' . $table);
    // Remove cash linked to the orders/sales being discarded, keeping independent movements.
    $copy->exec("DELETE c FROM cb_cajabanco c WHERE COALESCE(c.IdPreOrden,0)<>0 OR EXISTS (SELECT 1 FROM cb_cajabancodet d WHERE d.IdCajaBanco=c.IdCajaBanco AND d.Tipo IN ('VE','LO'))");
    $copy->exec('UPDATE cb_cajabanco SET IdCierre=NULL WHERE COALESCE(IdCierre,0)<>0');
    $copy->exec('UPDATE gen_producto SET IdPreOrden=NULL WHERE COALESCE(IdPreOrden,0)<>0');
    // Foreign-key checks are disabled only on this newly restored, disposable connection.
    $copy->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach ($clear as $table) $copy->exec('TRUNCATE TABLE `' . $table . '`');
    $copy->exec('SET FOREIGN_KEY_CHECKS=1');
    foreach ($clear as $table) productionAssert((int)$copy->query('SELECT COUNT(*) FROM `' . $table . '`')->fetchColumn() === 0, 'Table is not empty: ' . $table);
    productionAssert((int)$copy->query('SELECT COUNT(*) FROM Gen_Producto_Totales WHERE TotalCompras<>0 OR TotalVentas<>0')->fetchColumn() === 0, 'Opening stock must be zero.');
    $after = productionSnapshot($copy, $tables);
    foreach (array_diff($tables, $clear, array('cb_cajabanco','gen_producto','gen_producto_totales')) as $table) productionAssert($after[$table] === $before[$table], 'Preserved table changed: ' . $table);
    productionRun(array_merge($dumpArgs, array('--result-file='.$dir.'/erp.sql', $cloneName)));
    productionAssert(productionSnapshot($db, $tables) === $before, 'Source changed during export; review before delivery.');
    $manifest = array('source_database'=>'nst','stock'=>'zero','objects'=>count($tables),'cleared_tables'=>$clear,'before'=>$before,'after'=>$after,'full_backup_sha256'=>hash_file('sha256',$dump),'source_unchanged'=>true,'clone_restore_verified'=>true,'independent_cash_movements_preserved'=>$after['cb_cajabanco']['rows']);
    file_put_contents($dir.'/export-report.json', json_encode($manifest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    file_put_contents('C:/xampp/htdocs/boticawebpack/tmp/nstarapaca-production/export-directory.txt', $dir);
    echo json_encode(array('directory'=>$dir,'objects'=>count($tables),'cleared_tables'=>count($clear),'products'=>$after['gen_producto']['rows'],'clients'=>$after['ve_docventacliente']['rows'],'stock'=>'zero','source_unchanged'=>true,'backup_restore_verified'=>true), JSON_UNESCAPED_SLASHES) . PHP_EOL;
} finally {
    $copy = null;
    if ($created) $db->exec('DROP DATABASE `' . $cloneName . '`');
    if (is_file($defaults)) unlink($defaults);
}
