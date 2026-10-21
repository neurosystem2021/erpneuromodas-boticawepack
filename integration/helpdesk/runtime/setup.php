<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$options = getopt('', ['root:', 'check', 'apply', 'database:', 'origin:', 'company:', 'admin:', 'create-database', 'config:', 'repair-config-access']);
if (!isset($options['check']) && !isset($options['apply']) && !isset($options['repair-config-access'])) {
    echo "Diagnóstico: php tools/support/setup.php --check\n";
    echo "Instalar: php tools/support/setup.php --apply --database=BASE_SOPORTE --origin=https://su-dominio --company=1 --admin=superadmin [--create-database]\n";
    exit;
}
$root = realpath($options['root'] ?? dirname(__DIR__, 2));
if (!$root || !is_file($root . '/config/database.php') || !is_file($root . '/api/support-runtime.php')) { fwrite(STDERR, "No se encuentra el ERP. Indica --root=/ruta/del/ERP.\n"); exit(1); }
require_once $root . '/config/database.php';
require_once $root . '/api/support-runtime.php';
require_once $root . '/api/support-service.php';
require_once __DIR__ . '/config-files.php';
appLoadEnvironment();
if (isset($options['config'])) putenv('SUPPORT_RUNTIME_CONFIG=' . $options['config']);
$db = null;
$stage = 'configuration';
try {
    if (isset($options['repair-config-access'])) {
        $file = supportRuntimeConfigPath($root);
        if (!is_file($file)) throw new SupportRuntimeException('SUPPORT_CONFIG_MISSING', 'El archivo de soporte no existe. Ejecuta la primera instalación con --apply.');
        supportConfigApplyAccess($file, $root);
        echo "OK: permisos de soporte alineados con la configuración privada del ERP. No se modificaron bases ni credenciales.\n";
        exit;
    }
    if (isset($options['check'])) {
        $cfg = supportRuntimeConfig($root);
        $db = supportRuntimeConnect($cfg);
        supportRuntimeVerify($db, $cfg);
        echo "OK: soporte conectado, migraciones y empresa habilitadas. No se modificaron datos.\n";
        exit;
    }
    $file = supportRuntimeConfigPath($root);
    $cfg = is_file($file) ? supportRuntimeConfig($root, false) : [];
    if (isset($options['database']) && isset($cfg['database']) && $options['database'] !== $cfg['database']) throw new RuntimeException('La configuración existente apunta a otra base. No se cambiará automáticamente.');
    $cfg['database'] = $cfg['database'] ?? ($options['database'] ?? '');
    $connection = supportRuntimeConnectionConfig($cfg);
    $company = (string)($options['company'] ?? ($cfg['local_company'] ?? ''));
    $admin = $options['admin'] ?? '';
    $origin = rtrim($options['origin'] ?? '', '/');
    $url = parse_url($origin);
    if (!$url || ($url['scheme'] ?? '') !== 'https' || empty($url['host']) || isset($url['user']) || isset($url['pass']) || isset($url['query']) || isset($url['fragment']) || !empty($url['path'])) throw new RuntimeException('Indica --origin=https://dominio-del-centro (sin ruta).');
    if ($company === '' || $admin === '') throw new RuntimeException('Indica --company=ID_EMPRESA y --admin=USUARIO_ADMIN_ERP.');
    // Validate identities before creating or migrating anything.
    $stage = 'erp_identity';
    $erp = appPdoConnection();
    $companies = supportErpTable($erp, 'Gen_Empresa');
    if (!supportQuery($erp, 'SELECT IDEMPRESA FROM ' . $companies . ' WHERE IDEMPRESA=?', [$company])->fetchColumn()) throw new RuntimeException('La empresa indicada no existe en el ERP.');
    $users = supportErpTable($erp, 'Seg_Usuario');
    $profiles = supportErpTable($erp, 'Seg_UsuarioPerfil');
    $user = supportQuery($erp, 'SELECT u.Usuario,u.NombreUsuario,u.IdUsuarioPerfil,p.UsuarioPerfil FROM ' . $users . ' u JOIN ' . $profiles . ' p ON p.IdUsuarioPerfil=u.IdUsuarioPerfil WHERE u.Usuario=? AND IFNULL(u.Anulado,0)=0', [$admin])->fetch(PDO::FETCH_ASSOC);
    if (!$user || ((int)$user['IdUsuarioPerfil'] !== 1 && strcasecmp(trim($user['UsuarioPerfil']), 'PROGRAMADOR') !== 0)) throw new RuntimeException('El administrador inicial debe ser un usuario activo con perfil administrador o PROGRAMADOR del ERP.');
    if (isset($options['create-database'])) {
        $stage = 'database_create';
        $serverDsn = 'mysql:host=' . $connection['host'] . ';port=' . $connection['port'] . ';charset=' . $connection['charset'];
        $server = new PDO($serverDsn, $connection['user'], $connection['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . $cfg['database'] . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
        $server = null;
    }
    $stage = 'support_connection';
    $db = supportRuntimeConnect($cfg);
    $lock = 'support_setup_' . substr(hash('sha256', $cfg['database']), 0, 40);
    if ((int)supportQuery($db, 'SELECT GET_LOCK(?,10)', [$lock])->fetchColumn() !== 1) throw new RuntimeException('Otro proceso está instalando soporte. Intenta nuevamente.');
    try {
        $stage = 'schema_check';
        $tracked = supportQuery($db, "SELECT COUNT(*) FROM information_schema.TABLES WHERE BINARY TABLE_SCHEMA=BINARY DATABASE() AND TABLE_NAME='support_schema_versions'")->fetchColumn();
        $existing = supportQuery($db, "SELECT COUNT(*) FROM information_schema.TABLES WHERE BINARY TABLE_SCHEMA=BINARY DATABASE() AND TABLE_NAME LIKE 'support\\_%'")->fetchColumn();
        if (!$tracked && $existing) throw new RuntimeException('Hay tablas de soporte sin historial de migraciones. Deben revisarse antes de continuar; no se reemplazarán.');
        $db->exec('CREATE TABLE IF NOT EXISTS support_schema_versions(version VARCHAR(100) PRIMARY KEY,checksum CHAR(64) NOT NULL,applied_at DATETIME NOT NULL) ENGINE=InnoDB');
        $migrations = glob(__DIR__ . '/migrations/*.up.sql');
        if (count($migrations) !== 8) throw new RuntimeException('Faltan archivos de migración. Actualiza el repositorio completo.');
        sort($migrations);
        $stage = 'migrations';
        // Detect any changed historical migration before applying subsequent ones.
        foreach ($migrations as $migration) {
            $old = supportQuery($db, 'SELECT checksum FROM support_schema_versions WHERE version=?', [basename($migration)])->fetchColumn();
            if ($old && !hash_equals($old, hash_file('sha256', $migration))) throw new RuntimeException('La migración ya aplicada difiere: ' . basename($migration));
        }
        foreach ($migrations as $migration) {
            if (supportQuery($db, 'SELECT 1 FROM support_schema_versions WHERE version=?', [basename($migration)])->fetchColumn()) continue;
            $db->exec(file_get_contents($migration));
            supportQuery($db, 'INSERT INTO support_schema_versions VALUES(?,?,?)', [basename($migration), hash_file('sha256', $migration), supportNow()]);
            echo 'Aplicada: ' . basename($migration) . "\n";
        }
        $stage = 'registration';
        $db->beginTransaction();
        if (!empty($cfg['local_installation'])) {
            $installation = supportQuery($db, 'SELECT id,origin_https,active FROM support_installations WHERE id=? FOR UPDATE', [$cfg['local_installation']])->fetch(PDO::FETCH_ASSOC);
            if (!$installation || !$installation['active'] || $installation['origin_https'] !== $origin || (string)$cfg['local_company'] !== $company) throw new RuntimeException('La instalación existente no corresponde al origen/empresa indicados o está desactivada.');
        } else {
            $installation = supportQuery($db, 'SELECT id,origin_https,active FROM support_installations WHERE origin_https=? FOR UPDATE', [$origin])->fetch(PDO::FETCH_ASSOC);
            if ($installation && !$installation['active']) throw new RuntimeException('Esta instalación está desactivada; no se reactivará automáticamente.');
            if (!$installation) {
                $hex = bin2hex(random_bytes(16));
                $uuid = substr($hex,0,8).'-'.substr($hex,8,4).'-'.substr($hex,12,4).'-'.substr($hex,16,4).'-'.substr($hex,20);
                supportQuery($db, 'INSERT INTO support_installations(installation_uuid,origin_https,active,created_at) VALUES(?,?,1,?)', [$uuid, $origin, supportNow()]);
                $installation = ['id' => (int)$db->lastInsertId()];
            }
            $cfg['local_installation'] = (int)$installation['id'];
            $cfg['local_company'] = $company;
        }
        supportQuery($db, 'INSERT IGNORE INTO support_tenants(installation_id,external_company_id,created_at) VALUES(?,?,?)', [$cfg['local_installation'], $company, supportNow()]);
        $identity = supportIdentity($db, $cfg['local_installation'], $user['Usuario'], $company, $user['NombreUsuario'] ?: $user['Usuario']);
        supportQuery($db, "INSERT IGNORE INTO support_memberships(tenant_id,principal_id,role) VALUES(?,?,'superadmin')", [$identity['tenant'], $identity['principal']]);
        foreach (['Facturación electrónica','Problemas de SUNAT','Emisión de boletas y facturas','Inventarios y almacenes','Punto de venta','Caja y ventas','Compras','Reportes','Usuarios y permisos','Impresoras y equipos','Lentitud del sistema','Problemas de acceso','Configuración del sistema','Nuevas funcionalidades','Otros problemas'] as $category) supportQuery($db, 'INSERT IGNORE INTO support_categories(name) VALUES(?)', [$category]);
        supportAudit($db, $identity, 'central_setup', null, ['origin' => $origin, 'company' => $company]);
        $db->commit();
        // Publish config only after successful migrations/registration. Passwords stay private.
        $stage = 'configuration_write';
        $contents = '<?php' . "\nreturn " . var_export($cfg, true) . ";\n";
        if (!is_file($file) || file_get_contents($file) !== $contents) {
            if (!is_dir(dirname($file))) throw new RuntimeException('No existe el directorio privado de configuración.');
            $temp = $file . '.' . bin2hex(random_bytes(6)) . '.tmp';
            $handle = fopen($temp, 'x');
            if (!$handle) throw new RuntimeException('No se pudo escribir la configuración privada.');
            chmod($temp, 0600);
            $written = fwrite($handle, $contents);
            fclose($handle);
            try {
                if ($written !== strlen($contents)) throw new RuntimeException('No se pudo escribir toda la configuración privada.');
                supportConfigApplyAccess($temp, $root);
                if (!rename($temp, $file)) throw new RuntimeException('No se pudo publicar la configuración privada.');
            } finally { if (is_file($temp)) unlink($temp); }
        }
        // Repair older root-owned 0600 files even when their contents did not change.
        supportConfigApplyAccess($file, $root);
        supportRuntimeVerify($db, $cfg);
        echo "OK: centro instalado. Conserva tickets y permisos existentes. El administrador indicado puede gestionar técnicos y clientes.\n";
    } finally { supportQuery($db, 'SELECT RELEASE_LOCK(?)', [$lock]); }
} catch (Throwable $e) {
    if ($db && $db->inTransaction()) $db->rollBack();
    $failure = supportRuntimeFailure($e);
    if ($e instanceof PDOException) { $failure = supportSetupDatabaseFailure($e, $stage); $message = $failure['error']; }
    else $message = $e instanceof SupportRuntimeException ? $failure['error'] : $e->getMessage();
    fwrite(STDERR, $failure['code'] . ': ' . $message . "\n");
    exit(1);
}
