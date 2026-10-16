<?php

require_once dirname(__DIR__, 2) . '/api/crm/src/CrmCustomerImportService.php';

function importAssert($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$path = tempnam(sys_get_temp_dir(), 'crm-import-');
file_put_contents($path, "Nombre;Teléfono;Documento;Correo\nCliente Uno;999 888 777;12345678;cliente@example.com\nSin teléfono;;;x\n");
$service = new CrmCustomerImportService();
$result = $service->readUploadedFile(array(
    'error' => UPLOAD_ERR_OK,
    'size' => filesize($path),
    'name' => 'clientes.csv',
    'tmp_name' => $path,
));
unlink($path);

importAssert(count($result['rows']) === 1, 'Debe aceptar la fila válida.');
importAssert($result['rows'][0]['phone'] === '999888777', 'Debe normalizar el teléfono.');
importAssert($result['rejected'] === 1, 'Debe contabilizar filas rechazadas.');

echo "CrmCustomerImportTest OK\n";
