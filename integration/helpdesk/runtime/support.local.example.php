<?php
// Copy to ERP/config/support.local.php ONLY when using separate MySQL credentials.
// Do not commit that private copy. Complete registration using tools/support/setup.php.
return [
    'database' => 'fact_nstarapaca_soporte',
    'connection' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'user' => 'USUARIO_MYSQL_SOPORTE',
        'pass' => 'CONFIGURAR_EN_EL_SERVIDOR',
        'charset' => 'utf8mb4',
    ],
];
