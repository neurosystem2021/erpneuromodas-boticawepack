# Descubrimiento técnico del CRM NeuroRest

## Alcance y entorno

- Rama aislada: `feat/crm-restaurante`.
- Worktree: `C:\xampp\htdocs\boticav1-crm`.
- No se consultó producción ni se usaron datos personales productivos.
- El CRM se diseñará desactivado por defecto y sin escritura sobre tablas operativas.

## Stack actual

- Backend: PHP procedural con Slim 3 para la API principal.
- Persistencia: MariaDB/MySQL mediante `mysqli` y `Slim\PDO\Database`.
- Frontend operativo: Vue 2 compilado y servido desde `/nuevo`.
- Entrada web tradicional: vistas PHP y navegación en `views/header.php`.
- Autenticación: sesión PHP creada en `controllers/validarLogin.php`.
- Autorización: permisos de módulo cargados en `$_SESSION['permisos']`.
- Dependencias backend: vendorizadas dentro de `api/vendor`; `composer.json` no declara paquetes.
- Pruebas: scripts PHP autónomos, sin PHPUnit.

## Estructura funcional relevante

- Clientes: `Ve_DocVentaCliente`.
- Cabecera de venta: `Ve_DocVenta`.
- Detalle de venta: `Ve_DocVentaDet`.
- Productos: `Gen_Producto` y `Gen_ProductoCategoria`.
- Sucursales/almacenes: `Lo_Almacen`.
- Usuarios y perfiles: `Seg_Usuario`, `Seg_UsuarioPerfil` y tablas de permisos.
- Empresa del despliegue: `GEN_EMPRESA`, actualmente consultada con `IDEMPRESA=1`.

## Autenticación y tenant

Cada instalación representa principalmente un restaurante y utiliza `GEN_EMPRESA.IDEMPRESA`.
El adaptador CRM tomará el usuario de la sesión existente y resolverá el `tenant_id` desde
`GEN_EMPRESA`, sin copiar contraseñas. Toda consulta CRM exigirá `tenant_id` y permiso CRM.

## Integración segura

- API aislada bajo `/api/crm`.
- SPA independiente bajo `/crm`.
- Tablas nuevas con prefijo `crm_`.
- Lectura incremental de clientes y ventas usando IDs crecientes como checkpoint.
- Ningún `INSERT`, `UPDATE` o `DELETE` sobre tablas operativas.
- Sin triggers sobre ventas, caja, pedidos o inventario.
- El menú CRM solo aparecerá con `CRM_ENABLED=true`.

## Línea base

Ejecutado con PHP local:

- `tests/product/ProductIdentifierTest.php`: OK.
- `tests/sunat/CatalogoProductoSunatImporterTest.php`: 9 pruebas OK.
- `tests/sunat/SunatProductCodeApiServiceTest.php`: OK.
- `tests/sunat/SunatProductCodeValidatorTest.php`: OK.
- `tests/sunat/Ubl21ProductIdentificationXmlTest.php`: OK.
- Sintaxis de `api/index.php`, `controllers/validarLogin.php` y `models/DBManager.php`: OK.

La prueba `tests/integration/run_sunat_catalog_database_tests.ps1` no se ejecutó porque
contiene `DROP DATABASE`, incompatible con las reglas de esta tarea.

## Hallazgos y limitaciones

- Hay credenciales preexistentes versionadas en archivos de conexión. El CRM no las replica.
- El dump histórico contiene datos no anonimizados; no se utilizará como seed del CRM.
- No existe un runner uniforme de build/lint/test para todo el monolito.
- Las tablas antiguas usan importes `float`; las tablas CRM usarán `DECIMAL`.
- No se encontró outbox operativo. Para el MVP se usará lectura incremental por ID.
- El aislamiento multiempresa del núcleo es por despliegue; el CRM añadirá defensa explícita por tenant.

## Comandos conocidos

```powershell
C:\xampp\php\php.exe tests\product\ProductIdentifierTest.php
C:\xampp\php\php.exe tests\sunat\SunatProductCodeValidatorTest.php
C:\xampp\php\php.exe -l api\index.php
```

La SPA CRM tendrá sus propios comandos `npm run dev`, `npm run build` y `npm run test`.
# Línea base de la extensión de comunicaciones — 2026-07-26

- Rama/worktree: `feat/crm-restaurante`.
- PHP CLI: `C:\xampp\php\php.exe`, versión 7.4.33.
- Frontend CRM: Vue 3.5.13 + Vite 6.4.3, separado del núcleo Vue 2.
- Pruebas iniciales: `php tests/crm/run.php` — correctas.
- Build inicial: `npm --prefix crm run build` — correcto.
- El CRM existente ya contiene campañas, consentimientos, destinatarios y
  auditoría, pero no tenía bandeja, conexión de proveedor ni adaptador IA.
- La conexión QR observada pertenece a un servicio externo; no se reutiliza
  ninguna credencial ni QR en desarrollo.
