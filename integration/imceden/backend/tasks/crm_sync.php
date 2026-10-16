<?php

if (PHP_SAPI !== 'cli') exit("Sólo CLI.\n");

require_once dirname(__DIR__) . '/config/crm.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/api/crm/src/CrmDomain.php';
require_once dirname(__DIR__) . '/api/crm/src/CrmSyncService.php';

$config = crmConfig();
if (!$config['enabled']) {
    fwrite(STDERR, "CRM deshabilitado. Defina CRM_ENABLED=true para ejecutar.\n");
    exit(2);
}

$apply = in_array('--apply', $argv, true);
$service = new CrmSyncService(appMysqliConnection(), $config['tenant_id']);
$rows = $service->preview($config['sync_batch_size']);

if (!$apply) {
    echo "DRY RUN: " . count($rows) . " clientes listos. No se escribió ningún dato.\n";
    exit(0);
}

echo "Sincronizados: " . $service->apply($rows) . " clientes en tablas crm_*.\n";
