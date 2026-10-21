<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function supportSetupDatabaseFailure(PDOException $error, $stage)
{
    // Do not print PDO messages: they can include usernames, SQL or connection details.
    $number = (int)($error->errorInfo[1] ?? 0);
    $state = (string)($error->errorInfo[0] ?? $error->getCode());
    $state = preg_match('/^[A-Z0-9]{5}$/D', $state) ? $state : 'N/A';
    $stages = ['erp_identity', 'database_create', 'schema_check', 'migrations', 'registration'];
    $stage = in_array($stage, $stages, true) ? $stage : 'database';
    $code = 'SUPPORT_DATABASE_OPERATION_FAILED';
    $message = 'No se pudo completar la operación de soporte. Conserva las tablas existentes y revisa esta etapa.';
    if ($number === 1045) {
        $code = 'SUPPORT_DATABASE_AUTH_FAILED';
        $message = 'MySQL rechazó la autenticación. Revisa la conexión privada utilizada por PHP CLI; ser root en Linux no da acceso root a MySQL.';
    } elseif (in_array($number, [1044, 1142, 1143, 1227], true)) {
        $code = $stage === 'database_create' ? 'SUPPORT_DATABASE_CREATE_DENIED' : 'SUPPORT_DATABASE_PERMISSION_DENIED';
        $message = $stage === 'database_create'
            ? 'MySQL denegó crear la base de soporte. Créala desde CyberPanel y concede acceso al usuario MySQL configurado en el ERP; después repite --apply sin --create-database.'
            : 'MySQL denegó una operación. El usuario de la conexión necesita permisos de lectura, escritura y migración en la base independiente de soporte.';
    } elseif ($number === 1049) {
        $code = 'SUPPORT_DATABASE_MISSING';
        $message = 'La base de datos indicada no existe. Créala y autoriza su conexión antes de repetir la instalación.';
    } elseif (in_array($number, [2002, 2003, 2006, 2013], true)) {
        $code = 'SUPPORT_DATABASE_UNREACHABLE';
        $message = 'No se pudo mantener la conexión con MySQL. Revisa el servicio, host y puerto de la configuración privada.';
    }
    return ['code'=>$code, 'error'=>$message.' [etapa='.$stage.'; MySQL='.$number.'; SQLSTATE='.$state.']'];
}

function supportConfigAccessMode($referenceMode)
{
    // Match only the read access already used by the ERP's private DB configuration.
    // Never copy executable bits or allow group/other writes.
    return 0600 | ($referenceMode & 0044);
}

function supportConfigApplyAccess($file, $root)
{
    $reference = null;
    foreach (['config/database.local.php', '.env', 'config/database.php'] as $name) {
        if (is_file($root.'/'.$name)) { $reference = $root.'/'.$name; break; }
    }
    if (!$reference) throw new RuntimeException('No se pudo identificar la configuración de referencia del ERP.');
    clearstatcache(true, $reference);
    $metadata = stat($reference);
    if (!$metadata) throw new RuntimeException('No se pudieron consultar los permisos del ERP.');
    if (DIRECTORY_SEPARATOR !== '\\') {
        // MobaXterm commonly runs as root, while PHP-FPM runs as the site owner.
        // Preserve the working DB config's owner/group when root publishes a new file.
        if (fileowner($file) !== $metadata['uid'] && !@chown($file, $metadata['uid']) && !($metadata['mode'] & 0044)) throw new RuntimeException('Ejecuta la instalación con el usuario propietario del ERP o un administrador que pueda asignarlo.');
        if (filegroup($file) !== $metadata['gid'] && !@chgrp($file, $metadata['gid']) && ($metadata['mode'] & 0040) && !($metadata['mode'] & 0004)) throw new RuntimeException('Ejecuta la instalación con el usuario de PHP o un administrador que pueda asignar el grupo del ERP.');
    }
    if (!chmod($file, supportConfigAccessMode($metadata['mode']))) throw new RuntimeException('No se pudieron establecer permisos de lectura para PHP.');
    clearstatcache(true, $file);
}
