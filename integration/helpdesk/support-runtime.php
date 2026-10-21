<?php
// Portable private configuration for the central help desk. Never creates tables on HTTP requests.
class SupportRuntimeException extends RuntimeException
{
    public $reason;
    public function __construct($reason, $message) { parent::__construct($message); $this->reason = $reason; }
}

function supportRuntimeConfigPath($root = null)
{
    $root = $root ?: dirname(__DIR__);
    if (function_exists('appLoadEnvironment')) appLoadEnvironment();
    $explicit = getenv('SUPPORT_RUNTIME_CONFIG');
    if ($explicit !== false && $explicit !== '') return $explicit;
    $portable = $root . '/config/support.local.php';
    if (is_file($portable)) return $portable;
    // Compatibility with existing XAMPP installations; never used on Linux.
    if (DIRECTORY_SEPARATOR === '\\' && is_file('C:/xampp/support-private/runtime.php')) return 'C:/xampp/support-private/runtime.php';
    return $portable;
}

function supportRuntimeConfig($root = null, $registration = true)
{
    $file = supportRuntimeConfigPath($root);
    if (!is_readable($file)) throw new SupportRuntimeException('SUPPORT_CONFIG_MISSING', 'Falta configurar la conexión del centro de soporte.');
    $cfg = require $file;
    if (!is_array($cfg)) throw new SupportRuntimeException('SUPPORT_CONFIG_INVALID', 'La configuración del centro de soporte no es válida.');
    supportRuntimeValidate($cfg, $registration);
    return $cfg;
}

function supportRuntimeValidate(array $cfg, $registration = true)
{
    if (!isset($cfg['database']) || !is_string($cfg['database']) || !preg_match('/^[A-Za-z0-9_]{1,64}$/D', $cfg['database'])) {
        throw new SupportRuntimeException('SUPPORT_CONFIG_INVALID', 'Revisa el nombre de la base de soporte.');
    }
    if ($registration && (empty($cfg['local_installation']) || !ctype_digit((string)$cfg['local_installation']) || empty($cfg['local_company']))) {
        throw new SupportRuntimeException('SUPPORT_SETUP_REQUIRED', 'Falta registrar esta empresa en el centro de soporte.');
    }
    if (isset($cfg['connection']) && !is_array($cfg['connection'])) throw new SupportRuntimeException('SUPPORT_CONFIG_INVALID', 'Revisa la conexión de soporte.');
}

function supportRuntimeConnectionConfig(array $cfg)
{
    supportRuntimeValidate($cfg, false);
    $erp = appDatabaseConfig();
    $connection = array_merge($erp, array_intersect_key($cfg['connection'] ?? [], $erp));
    $connection['dbname'] = $cfg['database'];
    if (strcasecmp($connection['dbname'], $erp['dbname']) === 0 && strcasecmp($connection['host'], $erp['host']) === 0 && (int)$connection['port'] === (int)$erp['port']) {
        throw new SupportRuntimeException('SUPPORT_DATABASE_NOT_SEPARATE', 'Soporte requiere una base independiente de la base del ERP.');
    }
    if (!preg_match('/^[A-Za-z0-9._:-]+$/D', $connection['host']) || !ctype_digit((string)$connection['port']) || (int)$connection['port'] < 1 || (int)$connection['port'] > 65535 || !preg_match('/^[A-Za-z0-9_]+$/D', $connection['charset'])) {
        throw new SupportRuntimeException('SUPPORT_CONFIG_INVALID', 'Revisa el servidor, puerto y charset de soporte.');
    }
    return $connection;
}

function supportRuntimeConnect(array $cfg)
{
    $connection = supportRuntimeConnectionConfig($cfg);
    try {
        return new PDO(appDatabaseDsn($connection), $connection['user'], $connection['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_TIMEOUT => 5]);
    } catch (PDOException $e) {
        $number = (int)($e->errorInfo[1] ?? 0);
        if ($number === 1049) throw new SupportRuntimeException('SUPPORT_DATABASE_MISSING', 'La base del centro de soporte todavía no está instalada.');
        if (in_array($number, [1044, 1045], true)) throw new SupportRuntimeException('SUPPORT_DATABASE_ACCESS', 'La conexión de soporte no tiene acceso autorizado a su base de datos.');
        throw new SupportRuntimeException('SUPPORT_DATABASE_UNREACHABLE', 'No se pudo conectar con la base del centro de soporte.');
    }
}

function supportRuntimeVerify(PDO $db, array $cfg)
{
    supportRuntimeValidate($cfg);
    try {
        $version = $db->prepare('SELECT 1 FROM support_schema_versions WHERE version=?');
        $version->execute(['008_ticket_archive.up.sql']);
        if (!$version->fetchColumn()) throw new SupportRuntimeException('SUPPORT_MIGRATIONS_REQUIRED', 'Falta actualizar las tablas del centro de soporte.');
        $db->query('SELECT archived_at,archived_by,expected_resolution_at FROM support_tickets LIMIT 0');
        $s = $db->prepare('SELECT 1 FROM support_tenants t JOIN support_installations i ON i.id=t.installation_id WHERE i.id=? AND t.external_company_id=? AND i.active=1 AND t.active=1');
        $s->execute([$cfg['local_installation'], $cfg['local_company']]);
        if (!$s->fetchColumn()) throw new SupportRuntimeException('SUPPORT_REGISTRATION_INACTIVE', 'Esta empresa no está habilitada en el centro de soporte.');
    } catch (PDOException $e) {
        throw new SupportRuntimeException('SUPPORT_MIGRATIONS_REQUIRED', 'Falta instalar o actualizar las tablas del centro de soporte.');
    }
}

function supportRuntimeFailure(Throwable $e)
{
    if ($e instanceof SupportRuntimeException) return ['error' => $e->getMessage(), 'code' => $e->reason];
    return ['error' => 'Centro de soporte no disponible. Revisa el diagnóstico del servidor.', 'code' => 'SUPPORT_UNAVAILABLE'];
}

function supportErpTable(PDO $db, $expected)
{
    $s = $db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES WHERE BINARY TABLE_SCHEMA=BINARY DATABASE() AND LOWER(TABLE_NAME)=LOWER(?)');
    $s->execute([$expected]);
    $tables = $s->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) !== 1 || !preg_match('/^[A-Za-z0-9_]+$/D', $tables[0])) throw new SupportRuntimeException('SUPPORT_ERP_SCHEMA', 'No se pudo identificar una tabla del ERP para soporte.');
    return '`' . $tables[0] . '`';
}
