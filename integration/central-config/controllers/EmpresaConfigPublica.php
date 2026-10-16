<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../models/EmpresaConfig.php';
$config = empresaConfigObtener();
$datos = empresaConfigDatosImpresion($config);
$datos['logo_proforma'] = '/' . ltrim(empresaConfigValor($config, 'imagen_proforma', empresaConfigValor($config, 'logo_principal', 'resources/images/logo.png')), '/');
echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
