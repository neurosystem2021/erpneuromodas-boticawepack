<?php
// CLI only. Explicitly scoped to the local nst database and the nstarapaca checkouts.
if (PHP_SAPI !== 'cli') exit;
require 'C:/xampp/htdocs/boticav1/config/database.php';
function cleanupAssert($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function cleanupSnapshot(PDO $db, array $tables) {
    $snapshot = array();
    foreach ($tables as $table) {
        $rows = array();
        $statement = $db->query('SELECT * FROM `' . $table . '`');
        while ($row = $statement->fetch(PDO::FETCH_NUM)) $rows[] = hash('sha256', serialize($row));
        sort($rows, SORT_STRING);
        $snapshot[$table] = array('rows' => count($rows), 'sha256' => hash('sha256', implode('', $rows)));
    }
    ksort($snapshot);
    return $snapshot;
}
function cleanupRun(array $command, $inputFile = null) {
    $descriptors = array(0 => $inputFile ? array('file', $inputFile, 'r') : array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
    $process = proc_open($command, $descriptors, $pipes, null, null, array('bypass_shell' => true));
    cleanupAssert(is_resource($process), 'Cannot start backup verification.');
    if (!$inputFile) fclose($pipes[0]);
    $stdout = stream_get_contents($pipes[1]); $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]); fclose($pipes[2]);
    cleanupAssert(proc_close($process) === 0, 'Backup/restore command failed; database cleanup stopped.');
    return $stdout;
}
foreach (array('C:/xampp/htdocs/boticawebpack', 'C:/xampp/htdocs/boticav1') as $repo) {
    $branch = trim(cleanupRun(array('git', '-c', 'safe.directory=C:/xampp/htdocs/boticawebpack', '-c', 'safe.directory=C:/xampp/htdocs/boticav1', '-C', $repo, 'branch', '--show-current')));
    cleanupAssert($branch === 'nstarapaca', 'Both checkouts must remain on nstarapaca.');
}
$config = appDatabaseConfig();
cleanupAssert(in_array($config['host'], array('localhost', '127.0.0.1'), true) && (int)$config['port'] === 3306, 'Only the verified local server is allowed.');
$config['dbname'] = 'nst';
$connect = function ($name) use ($config) {
    $connection = $config; $connection['dbname'] = $name;
    return new PDO(appDatabaseDsn($connection), $connection['user'], $connection['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC));
};
$db = $connect('nst');
cleanupAssert($db->query('SELECT DATABASE()')->fetchColumn() === 'nst', 'Unexpected target.');
$companyRows = $db->query('SELECT IDEMPRESA,RUC FROM Gen_Empresa ORDER BY IDEMPRESA')->fetchAll(PDO::FETCH_KEY_PAIR);
cleanupAssert($companyRows == array(1 => '20573027125', 2 => '20573152531', 3 => '20602664989'), 'Unexpected company identities.');
$expected = explode(' ', 'cx_auditoria cx_configuracion cx_especificacion cx_grupo cx_grupoempresa cx_idempotencia cx_miembro cx_migracion cx_obra cx_pedido cx_pedidopartida cx_propuesta cx_propuestapartida cx_propuestaversion cx_referenciaerp cx_serie');
sort($expected);
$objects = $db->query('SHOW FULL TABLES')->fetchAll(PDO::FETCH_NUM);
$tables = array_column($objects, 0);
$concrete = array_values(array_filter($tables, function ($name) { return strpos($name, 'cx_') === 0; }));
sort($concrete);
cleanupAssert($concrete === $expected, 'Concretera schema changed since inspection.');
$fks = $db->query("SELECT TABLE_NAME,REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL")->fetchAll();
foreach ($fks as $fk) cleanupAssert(!in_array($fk['REFERENCED_TABLE_NAME'], $concrete, true) || in_array($fk['TABLE_NAME'], $concrete, true), 'A preserved table references Concretera.');
$protected = array_values(array_diff($tables, $concrete, array('gen_empresa', 'seg_usuariomodulo', 'seg_usuariomodulo_has_usuarioperfil')));
$before = cleanupSnapshot($db, $tables);
$module = $db->query("SELECT IdUsuarioModulo FROM Seg_UsuarioModulo WHERE UsuarioModulo='Concretera.php'")->fetchAll(PDO::FETCH_COLUMN);
cleanupAssert($module === array('31') || $module === array(31), 'Unexpected Concretera permission.');
$backup = 'C:/xampp/accounting-private/nstarapaca-sin-concretera-' . date('Ymd-His');
cleanupAssert(!file_exists($backup) && mkdir($backup, 0700, true), 'Cannot create private backup.');
copy('C:/xampp/htdocs/boticav1/config/database.local.php', $backup . '/database.local.php');
file_put_contents($backup . '/before.json', json_encode($before, JSON_PRETTY_PRINT));
$escape = function ($value) { return '"' . str_replace(array('\\', '"', "\n", "\r"), array('\\\\', '\\"', '\\n', '\\r'), $value) . '"'; };
$defaults = $backup . '/.client.cnf';
file_put_contents($defaults, "[client]\nhost=" . $escape($config['host']) . "\nport=3306\nuser=" . $escape($config['user']) . "\npassword=" . $escape($config['pass']) . "\n");
$testName = 'nst_cleanup_verify_' . date('Ymd_His');
$testCreated = false;
try {
    $dump = $backup . '/nst-completo.sql';
    cleanupRun(array('C:/xampp/mysql/bin/mysqldump.exe', '--defaults-extra-file=' . $defaults, '--lock-all-tables', '--routines', '--events', '--triggers', '--hex-blob', '--default-character-set=utf8mb4', '--result-file=' . $dump, 'nst'));
    cleanupAssert(filesize($dump) > 1000, 'Backup is empty.');
    $db->exec('CREATE DATABASE `' . $testName . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');
    $testCreated = true;
    cleanupRun(array('C:/xampp/mysql/bin/mysql.exe', '--defaults-extra-file=' . $defaults, '--default-character-set=utf8mb4', $testName), $dump);
    $verified = $connect($testName);
    cleanupAssert(cleanupSnapshot($verified, $tables) === $before, 'Restored backup differs from source.');
    $verified = null;
    $db->exec('DROP DATABASE `' . $testName . '`');
    $testCreated = false;
    cleanupAssert(cleanupSnapshot($db, $tables) === $before, 'Data changed during backup; cleanup stopped.');

    // Drop child tables first, retaining foreign-key checks for every operation.
    $remaining = $concrete; $dropped = array();
    while ($remaining) {
        $parents = array();
        foreach ($fks as $fk) if (in_array($fk['TABLE_NAME'], $remaining, true) && in_array($fk['REFERENCED_TABLE_NAME'], $remaining, true)) $parents[] = $fk['REFERENCED_TABLE_NAME'];
        $ready = array_values(array_diff($remaining, $parents));
        cleanupAssert(count($ready) > 0, 'Unexpected foreign-key cycle.');
        foreach ($ready as $table) {
            $db->exec('DROP TABLE `' . $table . '`');
            $dropped[] = $table;
        }
        $remaining = array_values(array_diff($remaining, $ready));
    }
    $db->beginTransaction();
    $permissions = $db->exec('DELETE FROM Seg_UsuarioModulo_has_UsuarioPerfil WHERE IdUsuarioModulo=31');
    $db->exec("DELETE FROM Seg_UsuarioModulo WHERE IdUsuarioModulo=31 AND UsuarioModulo='Concretera.php'");
    cleanupAssert($db->exec("DELETE FROM Gen_Empresa WHERE (IDEMPRESA=2 AND RUC='20573152531') OR (IDEMPRESA=3 AND RUC='20602664989')") === 2, 'Company cleanup mismatch.');
    $db->commit();
    $after = cleanupSnapshot($db, $protected);
    foreach ($protected as $table) cleanupAssert($after[$table] === $before[$table], 'Preserved data changed in ' . $table);

    // Keep the private connection settings intact; change only this installation's database.
    $localPath = 'C:/xampp/htdocs/boticav1/config/database.local.php';
    $local = file_get_contents($localPath);
    $local = preg_replace("/('dbname'\\s*=>\\s*)'[^']*'/", "\$1'nst'", $local, 1, $changed);
    cleanupAssert($changed === 1, 'Unexpected private connection structure.');
    $local = str_replace('Private configuration for the IMCEDEN installation.', 'Private configuration for the NSTARAPACA installation.', $local);
    file_put_contents($localPath, $local);
    $manifest = array('database' => 'nst', 'removed_tables' => $dropped, 'removed_companies' => array('20573152531', '20602664989'), 'removed_module' => 'Concretera.php', 'removed_permission_links' => $permissions, 'preserved_objects' => count($protected), 'backup_sha256' => hash_file('sha256', $dump), 'backup_restored_and_verified' => true, 'preserved_data_verified' => true);
    file_put_contents($backup . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    echo json_encode(array('backup' => $backup) + $manifest, JSON_UNESCAPED_SLASHES) . PHP_EOL;
} finally {
    if ($testCreated) $db->exec('DROP DATABASE `' . $testName . '`');
    if (is_file($defaults)) unlink($defaults);
}
