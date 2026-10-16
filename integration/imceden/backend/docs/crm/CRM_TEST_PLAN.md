# Plan de pruebas CRM

## Automatizadas

- `C:\xampp\php\php.exe tests\crm\run.php`
- Sintaxis PHP para archivos nuevos y `views/header.php`.
- `npm ci && npm run build && npm audit --audit-level=high` dentro de `crm`.
- Regresión de las pruebas existentes bajo `tests/product` y `tests/sunat`.

## Manuales en ambiente de prueba

1. Con flag apagado, confirmar que no existe menú CRM y la API responde 404.
2. Con flag encendido y sesión vencida, confirmar redirección/401.
3. Iniciar sesión y comprobar panel, búsqueda y listas.
4. Confirmar que un tenant no observa registros de otro.
5. Ejecutar sincronización dos veces y confirmar que no duplica clientes.
6. Confirmar que ventas, inventario y facturación siguen operando.
7. Volver a apagar el flag y comprobar reversibilidad inmediata.

No se ejecutaron migraciones ni pruebas contra producción durante el desarrollo.
