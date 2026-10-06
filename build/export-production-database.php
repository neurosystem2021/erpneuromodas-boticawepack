<?php

declare(strict_types=1);

/**
 * Exporta la base local para un servidor Linux que distingue mayusculas y
 * minusculas en los nombres de tablas.
 *
 * Uso:
 *   C:\xampp\php\php.exe build\export-production-database.php
 *
 * Variables opcionales:
 *   DB_HOST, DB_PORT, DB_USER, DB_PASSWORD, DB_NAME, MYSQLDUMP_PATH
 */

date_default_timezone_set('America/Lima');

$projectRoot = dirname(__DIR__);
$outputDir = $projectRoot . DIRECTORY_SEPARATOR . 'database';
$dateStamp = date('Ymd');
$outputFile = $outputDir . DIRECTORY_SEPARATOR . "fact_imceden_produccion_{$dateStamp}.sql";
$reportFile = $outputDir . DIRECTORY_SEPARATOR . "fact_imceden_produccion_{$dateStamp}_VALIDACION.txt";

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME') ?: 'fact_imceden';
$dumpExecutable = getenv('MYSQLDUMP_PATH') ?: 'C:\\xampp\\mysql\\bin\\mysqldump.exe';

// Estos nombres coinciden con las consultas del sistema legado. Las tablas CRM
// nacieron en minusculas y se conservan asi de forma intencional.
$canonicalTables = [
    'cb_cajabanco' => 'Cb_CajaBanco',
    'cb_cajabancodet' => 'Cb_CajaBancoDet',
    'cb_cierrecaja' => 'Cb_CierreCaja',
    'cb_cuenta' => 'Cb_Cuenta',
    'cb_letracaja' => 'Cb_LetraCaja',
    'cb_tipocajabanco' => 'Cb_TipoCajaBanco',
    'crm_audit_log' => 'crm_audit_log',
    'crm_bot_rule' => 'crm_bot_rule',
    'crm_campaign' => 'crm_campaign',
    'crm_campaign_recipient' => 'crm_campaign_recipient',
    'crm_channel_connection' => 'crm_channel_connection',
    'crm_consent' => 'crm_consent',
    'crm_conversation' => 'crm_conversation',
    'crm_customer' => 'crm_customer',
    'crm_customer_event' => 'crm_customer_event',
    'crm_customer_tag' => 'crm_customer_tag',
    'crm_interaction' => 'crm_interaction',
    'crm_loyalty_account' => 'crm_loyalty_account',
    'crm_loyalty_ledger' => 'crm_loyalty_ledger',
    'crm_message' => 'crm_message',
    'crm_message_outbox' => 'crm_message_outbox',
    'crm_message_template' => 'crm_message_template',
    'crm_segment' => 'crm_segment',
    'crm_segment_member' => 'crm_segment_member',
    'crm_setting' => 'crm_setting',
    'crm_sync_run' => 'crm_sync_run',
    'crm_tag' => 'crm_tag',
    'crm_task' => 'crm_task',
    'gen_empresa' => 'GEN_EMPRESA',
    'gen_empresaconfig' => 'Gen_EmpresaConfig',
    'gen_impresora' => 'Gen_Impresora',
    'gen_moneda' => 'Gen_Moneda',
    'gen_producto' => 'Gen_Producto',
    'gen_productobloque' => 'Gen_ProductoBloque',
    'gen_productocategoria' => 'Gen_ProductoCategoria',
    'gen_productocompuesto' => 'Gen_ProductoCompuesto',
    'gen_productocompuestodet' => 'Gen_ProductoCompuestoDet',
    'gen_productodet' => 'Gen_ProductoDet',
    'gen_productoformafarmaceutica' => 'Gen_ProductoFormaFarmaceutica',
    'gen_productomarca' => 'Gen_ProductoMarca',
    'gen_productomedicion' => 'Gen_ProductoMedicion',
    'gen_productomodelo' => 'Gen_ProductoModelo',
    'gen_productotalla' => 'Gen_ProductoTalla',
    'gen_producto_totales' => 'Gen_Producto_Totales',
    'lo_almacen' => 'Lo_Almacen',
    'lo_impuesto' => 'Lo_Impuesto',
    'lo_movimiento' => 'Lo_Movimiento',
    'lo_movimientodetalle' => 'Lo_MovimientoDetalle',
    'lo_movimientodetalleimpuesto' => 'Lo_MovimientoDetalleImpuesto',
    'lo_movimientotipo' => 'Lo_MovimientoTipo',
    'lo_ordencompra' => 'Lo_OrdenCompra',
    'lo_ordencompradet' => 'Lo_OrdenCompraDet',
    'lo_ordencompramovimiento' => 'Lo_OrdenCompraMovimiento',
    'lo_proveedor' => 'Lo_Proveedor',
    'prodstock' => 'prodstock',
    'seg_usuario' => 'Seg_Usuario',
    'seg_usuariomodulo' => 'Seg_UsuarioModulo',
    'seg_usuariomodulo_has_usuarioperfil' => 'Seg_UsuarioModulo_has_UsuarioPerfil',
    'seg_usuarioperfil' => 'Seg_UsuarioPerfil',
    'sunat_resumen' => 'sunat_resumen',
    'tblkardex' => 'tblKardex',
    'tblkardexvalor' => 'tblKardexvalor',
    've_docventa' => 'Ve_DocVenta',
    've_docventacliente' => 'Ve_DocVentaCliente',
    've_docventadescuento' => 'Ve_DocVentaDescuento',
    've_docventadet' => 'Ve_DocVentaDet',
    've_docventametodopago' => 'Ve_DocVentaMetodoPago',
    've_docventametodopagodet' => 'Ve_DocVentaMetodoPagoDet',
    've_docventanotacredito' => 'Ve_DocVentaNotaCredito',
    've_docventapuntoventa' => 'Ve_DocVentaPuntoVenta',
    've_docventapuntoventadet' => 'Ve_DocVentaPuntoVentaDet',
    've_docventaremision' => 'Ve_DocVentaRemision',
    've_docventatipodoc' => 'Ve_DocVentaTipoDoc',
    've_expertodiagnostico' => 'Ve_ExpertoDiagnostico',
    've_expertodiagnosticosintomadet' => 'Ve_ExpertoDiagnosticoSintomaDet',
    've_expertosintoma' => 'Ve_ExpertoSintoma',
    've_expertotratamiento' => 'Ve_ExpertoTratamiento',
    've_preorden' => 'Ve_PreOrden',
    've_preordendet' => 'Ve_PreOrdenDet',
    've_proforma' => 'Ve_Proforma',
    've_proformadet' => 'Ve_ProformaDet',
];

if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    throw new RuntimeException("No se pudo crear el directorio de salida: {$outputDir}");
}

if (!is_file($dumpExecutable)) {
    throw new RuntimeException("No se encontro mysqldump en: {$dumpExecutable}");
}

$temporaryFile = tempnam(sys_get_temp_dir(), 'fact_prod_');
if ($temporaryFile === false) {
    throw new RuntimeException('No se pudo crear el archivo temporal.');
}

$command = [
    $dumpExecutable,
    '--protocol=tcp',
    "--host={$host}",
    "--port={$port}",
    "--user={$user}",
    '--default-character-set=utf8mb4',
    '--single-transaction',
    '--quick',
    '--skip-lock-tables',
    '--routines',
    '--triggers',
    '--events',
    '--hex-blob',
    '--add-drop-table',
    '--create-options',
    '--skip-comments',
    "--result-file={$temporaryFile}",
];

if (is_string($password) && $password !== '') {
    $command[] = "--password={$password}";
}

$command[] = $database;

$descriptors = [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];

$process = proc_open($command, $descriptors, $pipes, $projectRoot, null, ['bypass_shell' => true]);
if (!is_resource($process)) {
    @unlink($temporaryFile);
    throw new RuntimeException('No se pudo iniciar mysqldump.');
}

fclose($pipes[0]);
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

if ($exitCode !== 0) {
    @unlink($temporaryFile);
    throw new RuntimeException("mysqldump termino con codigo {$exitCode}:\n{$stderr}\n{$stdout}");
}

$sql = file_get_contents($temporaryFile);
@unlink($temporaryFile);

if ($sql === false || trim($sql) === '') {
    throw new RuntimeException('mysqldump genero un archivo vacio.');
}

preg_match_all('/CREATE TABLE\s+`([^`]+)`/i', $sql, $sourceTableMatches);
$sourceTables = array_values(array_unique($sourceTableMatches[1] ?? []));
$sourceLower = array_map(static fn(string $name): string => strtolower($name), $sourceTables);
$missingMappings = array_values(array_diff($sourceLower, array_keys($canonicalTables)));

if ($missingMappings !== []) {
    throw new RuntimeException(
        "Faltan nombres canonicos para estas tablas: " . implode(', ', $missingMappings)
    );
}

// Corrige todos los identificadores entre comillas invertidas (DDL, INSERT,
// claves foraneas y referencias internas de rutinas).
$sql = preg_replace_callback(
    '/`([^`]+)`/',
    static function (array $matches) use ($canonicalTables): string {
        $lowerName = strtolower($matches[1]);
        return '`' . ($canonicalTables[$lowerName] ?? $matches[1]) . '`';
    },
    $sql
);

if ($sql === null) {
    throw new RuntimeException('Fallo la normalizacion de identificadores SQL.');
}

// Las rutinas antiguas tambien contienen nombres de tabla sin comillas. Solo se
// reemplazan dentro de bloques DELIMITER para no tocar valores de los INSERT.
$sql = preg_replace_callback(
    '/DELIMITER\s+;;(?<body>.*?)DELIMITER\s+;/is',
    static function (array $matches) use ($canonicalTables): string {
        $body = $matches['body'];
        $tableNames = array_keys($canonicalTables);
        usort($tableNames, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($tableNames as $lowerName) {
            $pattern = '/(?<![A-Za-z0-9_])' . preg_quote($lowerName, '/') . '(?![A-Za-z0-9_])/i';
            $body = preg_replace($pattern, $canonicalTables[$lowerName], $body) ?? $body;
        }

        return 'DELIMITER ;;' . $body . 'DELIMITER ;';
    },
    $sql
);

if ($sql === null) {
    throw new RuntimeException('Fallo la normalizacion de nombres dentro de las rutinas.');
}

// Un DEFINER local suele impedir importar rutinas en hosting compartido porque
// el usuario root@localhost no existe o no se permite SUPER.
$sql = preg_replace('/\s+DEFINER\s*=\s*`[^`]*`@`[^`]*`/i', '', $sql) ?? $sql;
$sql = preg_replace('/\s+DEFINER\s*=\s*[^\s*]+/i', '', $sql) ?? $sql;

$header = implode("\n", [
    '-- IMPORT COMPANY EDEN E.I.R.L.',
    '-- RESPALDO COMPLETO LISTO PARA PRODUCCION',
    '-- Generado: ' . date('Y-m-d H:i:s') . ' (America/Lima)',
    '-- Origen: base local ' . $database,
    '-- Destino: fact_imceden',
    '--',
    '-- IMPORTANTE: este archivo conserva el uso exacto de mayusculas y',
    '-- minusculas requerido por MySQL/MariaDB en Linux (lower_case_table_names=0).',
    '-- No incluye CREATE DATABASE ni USE: seleccione fact_imceden en phpMyAdmin',
    '-- antes de ejecutar la importacion.',
    '-- ADVERTENCIA: es un respaldo completo y contiene DROP TABLE. Si se importa',
    '-- sobre una base existente reemplazara sus tablas y datos. Haga primero un',
    '-- respaldo de produccion.',
    '-- Los DEFINER locales fueron eliminados para que las rutinas se creen con',
    '-- el usuario autorizado de produccion.',
    '',
]);

$sql = $header . $sql;

if (
    preg_match('/^CREATE\s+DATABASE\b/im', $sql) === 1
    || preg_match('/^USE\s+`?' . preg_quote($database, '/') . '`?\s*;/im', $sql) === 1
) {
    throw new RuntimeException('El respaldo contiene una instruccion para crear o seleccionar la base local.');
}

preg_match_all('/CREATE TABLE\s+`([^`]+)`/i', $sql, $targetTableMatches);
$targetTables = array_values(array_unique($targetTableMatches[1] ?? []));
preg_match_all('/\bPROCEDURE\s+`?[^`\s(]+`?\s*\(/i', $sql, $procedureMatches);
$procedureCount = count($procedureMatches[0] ?? []);

$requiredChecks = [
    'GEN_EMPRESA',
    'Gen_Producto',
    'Gen_Producto_Totales',
    'Lo_Movimiento',
    'Seg_Usuario',
    'Ve_DocVenta',
    'Ve_Proforma',
];

$missingRequired = array_values(array_diff($requiredChecks, $targetTables));
$hasDefiner = preg_match('/\bDEFINER\s*=/i', $sql) === 1;
$hasLowercaseLegacyCreate = preg_match('/CREATE TABLE\s+`(?:gen_producto|lo_movimiento|ve_docventa)`/i', $sql) === 1
    && (
        strpos($sql, 'CREATE TABLE `gen_producto`') !== false
        || strpos($sql, 'CREATE TABLE `lo_movimiento`') !== false
        || strpos($sql, 'CREATE TABLE `ve_docventa`') !== false
    );

if ($missingRequired !== [] || $hasDefiner || $hasLowercaseLegacyCreate || count($targetTables) !== count($sourceTables)) {
    throw new RuntimeException('La validacion final del respaldo no fue satisfactoria.');
}

if (file_put_contents($outputFile, $sql) === false) {
    throw new RuntimeException("No se pudo escribir el respaldo: {$outputFile}");
}

$hash = hash_file('sha256', $outputFile);
$size = filesize($outputFile);

$reportLines = [
    'VALIDACION DE RESPALDO SQL PARA PRODUCCION',
    '==========================================',
    'Resultado: OK',
    'Generado: ' . date('Y-m-d H:i:s') . ' America/Lima',
    'Origen: ' . $database,
    'Destino: fact_imceden',
    'Archivo: ' . basename($outputFile),
    'Tamano: ' . number_format((int) $size, 0, '.', ',') . ' bytes',
    'SHA-256: ' . $hash,
    '',
    'Objetos incluidos:',
    '- Tablas: ' . count($targetTables),
    '- Procedimientos: ' . $procedureCount,
    '- Datos: incluidos por mysqldump',
    '',
    'Comprobaciones:',
    '[OK] Nombres canonicos con mayusculas/minusculas.',
    '[OK] Sin CREATE DATABASE ni USE de la base local.',
    '[OK] Sin DEFINER local en procedimientos.',
    '[OK] Codificacion de exportacion utf8mb4.',
    '[OK] Compatible con importacion dentro de fact_imceden.',
    '',
    'ADVERTENCIA:',
    '- Es un respaldo completo y contiene DROP TABLE.',
    '- Importarlo sobre fact_imceden reemplaza las tablas y datos existentes.',
    '- Descargue primero un respaldo completo de la base de produccion.',
    '',
    'Importacion:',
    '1. Cree o seleccione fact_imceden en phpMyAdmin.',
    '2. Abra Importar y seleccione el archivo SQL.',
    '3. No active opciones para convertir nombres a minusculas.',
];

if (file_put_contents($reportFile, implode(PHP_EOL, $reportLines) . PHP_EOL) === false) {
    throw new RuntimeException("No se pudo escribir el informe: {$reportFile}");
}

echo "OK\n";
echo "SQL: {$outputFile}\n";
echo "Informe: {$reportFile}\n";
echo 'Tablas: ' . count($targetTables) . "\n";
echo "Procedimientos: {$procedureCount}\n";
echo "SHA-256: {$hash}\n";

