<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

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
