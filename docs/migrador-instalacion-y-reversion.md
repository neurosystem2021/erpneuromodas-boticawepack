> ESTADO VIGENTE ? MODULO WEB INTEGRADO: el acceso del ERP esta en **Seguridad > Migrador de compras y ventas**, exclusivamente para **superadmin**. Usa `views/migrador-historico.php`, `migration-ui/` y una API propia `api/migracion.php`; no requiere el puerto 8099. La API original de ventas conserva su hash. El acceso integrado se instalo tras la solicitud expresa del usuario de mostrarlo en el ERP web.
>
> Paquete para subir al hosting: [migrador-neurosof-web.zip](../output/migrador-neurosof-web.zip). Instrucciones especificas: [publicacion web](migrador-publicacion-web.md). El ZIP no incluye credenciales ni los datos historicos. Todavia no se ha publicado en un dominio remoto: falta identificar el servidor y su acceso autorizado.
>
> La copia y simulacion existentes siguen bloqueadas para aprobacion: 20 incidencias, 0 registros historicos cargados. Esta integracion de archivos NO autoriza la migracion definitiva ni altera datos productivos. Los parrafos siguientes conservan la historia del despliegue de pruebas anterior; prevalece este estado web integrado.

> ACTUALIZACION: entorno separado instalado en http://localhost:8099/ . Acceso exclusivo para **superadmin**. Iniciar sesion en el ERP en http://localhost/ y abrir el enlace de pruebas. La API y el menu de produccion NO se modificaron. La revision automatica rechazo publicar alli; se utilizo una aplicacion independiente sin alterar Apache.
>
> Copia MariaDB restaurada y verificada; cuatro tablas recuperadas exclusivamente en copia. Simulacion guardada: 38.095 filas, 20 grupos de incidencias, estado simulated, 0 filas en Mig_Record. No se aprobo ni ejecuto la carga historica. Las secciones siguientes describen el procedimiento y desarrollo inicial; prevalece este estado actualizado.
>
> Si se reinicia el equipo, levantar el servidor local con `powershell -ExecutionPolicy Bypass -File tools/migration/start-test-server.ps1`. Solo escucha en 127.0.0.1:8099; no habilita acceso desde la red. La conexion de origen usada es el snapshot privado del SQL completo; no se configuro una conexion MySQL en vivo al origen.
>
> El plan grande se conserva por filas en Mig_PlanRow para respetar el limite de paquetes de MariaDB. Los respaldos son privados e incluyen SQL de tablas legibles y archivos MyISAM de las cuatro danadas. Se comprobaron conteos y huellas completas de las tablas legibles y conteos de encabezados/CHECK TABLE de las recuperadas. Produccion conserva sus tablas sin reparacion.

# Migrador histórico: instalación en copia y reversión

## Estado y alcance

El módulo está desarrollado en el repositorio y preparado para integrarse en una **copia aislada** del ERP. No se han desplegado sus archivos ni creado tablas en producción. No se ha ejecutado una migración definitiva.

El modo implementado es **archivo histórico sin afectar stock**: importa registros a `Mig_Record`, conserva los originales, guarda transformaciones y equivalencias en `Mig_IdMap`, y ofrece auditoría y reportes. **No inserta en las tablas operativas `Ve_*`, `Lo_*`, `Gen_*`, caja ni SUNAT**. Los IDs nuevos son IDs del archivo histórico; no son IDs del catálogo operativo. Consultar/usar el historial mediante este módulo no convierte órdenes en recepciones ni crea nuevos cobros. No existe un modo de conciliación que actualice existencias ni una opción habilitada de migración definitiva.

La carga del historial a tablas operativas, la vinculación automática con productos/clientes actuales y su integración en reportes operativos requerirán otra fase: exclusión completa de stock, cobranza y colas fiscales, prueba de colisiones fiscales entre bases, y autorización explícita. No se presentan estas funciones como terminadas.

La simulación del archivo correcto incluye 38.095 filas de 53 entidades relacionadas, con cierre de dependencias reconocidas. Conserva 4.638 ventas, 7.131 detalles y 4.845 pagos. Detecta 20 grupos de incidencias que requieren revisar referencias, moneda histórica y órdenes con total cero; no se aprobaron esas incidencias. Los 23 registros de notas de crédito huérfanos se conservan, nunca se eliminan ni se vinculan por coincidencia de ID.

## Archivos

- Panel: `src/views/MigradorHistorico.vue`, ruta `/migrador-historico`.
- Motor: `integration/migration/Engine.php`.
- Auditoría: `integration/migration/schema.sql`.
- API: `integration/migration/runtime.php`, registrada desde `integration/ali-api.php` bajo `/ali/migracion`.
- Wrapper administrativo: `integration/migration/migrador-historico.php`, preparado para instalarse en `views/`.
- Menú administrativo preparado: entrada en `deploy-src/header.php`, Seguridad → Migrador de compras y ventas. Aplicar únicamente el cambio de menú, sin reemplazar un header instalado más reciente.
- Herramientas: `tools/migration/` y scripts de análisis `tools/analyze-migration-*.php/.py`, `tools/compare-migration-schema.py`.
- Reporte previo: [análisis del archivo completo](migrador-base-completa-analisis.md).

## Preparar destino aislado

1. Obtener respaldo consistente de esquema, datos y artefactos fiscales/adjuntos actuales. Las tablas MyISAM requieren ventana consistente o bloqueo administrado: `--single-transaction` solo no basta. La herramienta no ejecuta bloqueos ni reparaciones de producción.
2. Comprobar integridad del respaldo con SHA-256 y restaurarlo en un servidor/copia aislado. El nombre no puede ser `nst` ni `fact_nst`. No usar credenciales con permiso de escritura sobre producción. Revisar el dump antes de restaurarlo: sus USE/CREATE DATABASE no deben redirigir una restauración al nombre productivo.
3. Ensayar la recuperación de `Gen_Moneda`, `Lo_OrdenCompra`, `Lo_OrdenCompraDet` y `tblKardexValor` **solo en esa copia**, con conteos e índices antes/después. No hay un REPAIR automático porque puede perder datos. Si no se obtiene una copia completa verificada, no habilitar escritura del migrador.
4. La cuenta de destino debe tener permisos solo sobre la copia. La cuenta opcional de conexión de origen debe ser de solo lectura. No desactivar integridad ni importar usuarios/contraseñas del origen a cuentas actuales.

El migrador verifica nombre permitido, perfil de copia, manifiesto de respaldo, SHA-256 y declaración de restauración verificada. Estas declaraciones deben corresponder a evidencia real del administrador; no se generan automáticamente ni sustituyen la prueba de restauración.

## Perfil privado y origen

Guardar fuera de `C:\xampp\htdocs`, con permisos Windows limitados al administrador y servicio PHP, por ejemplo `C:\neuro-private\migration`. Nunca dejar snapshots, credenciales o respaldos en `static`, `dist`, `output`, el repositorio o una carpeta servida por Apache.

1. Ejecutar desde el repositorio:

```powershell
python tools/migration/export-snapshot.py --output C:/neuro-private/migration/source-reviewed.json
```

El exportador lee `C:\Users\USER\Downloads\fact_nst_COMPLETA.sql` offline, no ejecuta su SQL, rechaza una salida dentro del directorio web y no sobrescribe archivos existentes. Genera un snapshot privado y muestra únicamente su SHA-256. El archivo conserva datos personales necesarios; proteger su ACL de Windows, no solo atributos de archivo. Si cambia el dump, repetir comparación/validación y usar un nuevo snapshot revisado.

2. Copiar como plantilla `profile.example.json` y `backup-manifest.example.json` a la carpeta privada. Completar conexión de la copia, SHA-256 del snapshot, ruta/huella del respaldo y evidencia de restauración. Las plantillas públicas no contienen secretos reales.
3. Definir `NEURO_MIGRATION_CONFIG` para el proceso PHP/Apache con la ruta del perfil privado. Mantener `enable_copy_writes=false` hasta verificar destino, respaldo y autorización de ensayo en copia.
4. La sección opcional `source` permite comprobar una conexión de solo lectura a `fact_nst`; **el origen de la carga es el snapshot revisado del SQL**, no una lectura en vivo que pueda cambiar durante los lotes.
5. Establecer `enable_copy_writes=true` exclusivamente para la copia. Instalar la auditoría mediante CLI:

```powershell
C:/xampp/php/php.exe tools/migration/install-copy.php --copy=neuro_migration_test
```

No hay instalación automática por HTTP. DDL MySQL puede hacer commits implícitos; la instalación es idempotente y se ejecuta separada de las cargas. Las tablas `Mig_*` usan InnoDB y sus claves foráneas permanecen habilitadas.

Para el archivo real, probar PHP con al menos 512 MB de memoria en la copia: la simulación conserva filas y plan en memoria. No se promete procesamiento streaming de dumps arbitrariamente grandes.

## Instalar aplicación en la copia del ERP

Copiar `integration/migration/{Engine.php,runtime.php,schema.sql}` a `api/migration/`; integrar la llamada `neuroRegisterMigration` y su require en `api/ali-api.php` de la copia. Copiar el wrapper a `views/migrador-historico.php`. Compilar el frontend con `npm run build` y desplegar `dist` en `/nuevo` de la copia, conservando backup del frontend y publicando index al final. Aplicar la entrada administrativa de menú sin reemplazar otros cambios.

La API reutiliza sesión ERP, vencimiento, identidad, permisos administrativos comprobados en servidor y CSRF existente. Los errores HTTP son genéricos, sin DSN, claves, SQL ni registros personales. Solo administradores pueden ver las vistas previas, que eliminan campos de credenciales reconocidos. No se llama al endpoint de registrar ventas, impresoras, servicios fiscales ni mensajería.

## Operación

1. Origen y módulos: elegir ventas/compras/documentos relacionados. Catálogos requeridos se incluyen automáticamente y se expanden dependencias reconocidas.
2. Estructuras: revisar columnas, índices, claves, defaults, tipos y tablas no inspeccionables del reporte; no aprobar como equivalentes tablas dañadas. El JSON de comparación contiene el detalle técnico; la tabla visual muestra resumen.
3. Vista previa: 25 filas por página, solo administradores. No guardar capturas con información personal en repositorios públicos.
4. Transformaciones opcionales: JSON por tabla/campo, operación `copy`, `trim` o `uppercase`, con nombre destino validado. No se ejecuta SQL ni código arbitrario. Colisiones de campo se rechazan; se conserva el original. Ejemplo:

```json
{"Gen_Producto":{"Producto":{"to":"Producto","operation":"trim"}}}
```

5. Simular: guarda el plan y las incidencias en auditoría de la copia. No carga registros de negocio aún. El plan queda vinculado al SHA-256 del origen, módulos y transformaciones.
6. Resolver incidencias: fatal bloquea siempre; review requiere justificación y evidencia, con autor. Justificar no reconstruye una relación ni convierte un dato incierto en correcto: documenta su conservación aislada. Los casos que necesiten datos nuevos requieren corregir el origen y volver a simular. No aceptar justificaciones generales para aprobar una carga operativa.
7. Aprobar: únicamente la simulación exacta, con todas las incidencias resueltas/justificadas. No autoriza producción.
8. Ejecutar: lotes de 100 registros, máximo interno 500. Cada lote contiene registro, equivalencia, checkpoint y auditoría en una transacción. Un fallo revierte ese lote; el checkpoint permite reanudar. Repetir una solicitud con cursor antiguo no duplica registros. Se puede detener después del lote actual.
9. Al finalizar se validan cantidad, contenido original, transformación y cantidad de mapeos. Descargar reporte JSON con conteos, grupos de importes/estados, incidencias justificadas y eventos. No contiene las filas completas ni credenciales.

Las relaciones se guardan como equivalencias del archivo (`links_json`). Un campo `Id*` sin regla reconocida genera incidencia en lugar de apuntar silenciosamente a un ID actual. Líneas sin PK mantienen ordinal dentro del archivo revisado para no perder líneas idénticas legítimas.

La repetición de una simulación idéntica devuelve la misma ejecución. Existe unicidad de origen/plan; no se ofrece reimportación ciega de un mismo archivo. Un origen distinto requiere revisar solapamiento documental antes de cargarlo; actualmente la detección fiscal entre archivos/bases no está implementada y no habilita importación operativa.

## Reversión

El botón de reversión elimina exclusivamente registros y equivalencias de esa ejecución **del archivo histórico en la copia**, en transacción. Verifica conteos y huellas y bloquea si se alteraron originales o transformaciones. Conserva ejecución, lotes, incidencias y eventos como auditoría; no elimina ni cambia productos, ventas, pagos o existencias actuales. No equivale a restaurar una base de producción ni a revertir procesos externos: el módulo no genera esos procesos.

Ante contenido alterado, schema incompatible o recuperación no verificable, detener el ensayo y restaurar la copia desde su respaldo consistente. Una reparación o restauración de producción necesita autorización explícita independiente.

## Pruebas realizadas

```powershell
C:/xampp/php/php.exe tools/migration/test-engine.php
C:/xampp/php/php.exe tools/migration/test-runtime.php
node tools/migration/test-panel.mjs
```

Se probaron mapeos, preservación de datos y líneas repetidas, bloqueo de aprobación, huellas, lotes, solicitudes repetidas, fallo intermedio con rollback, reanudación, reversión, guardas de producción y panel responsive. La base de pruebas es SQLite en memoria; no constituye una prueba de integración MySQL/MariaDB ni de concurrencia real multiusuario.

Además se ejecutó simulación offline con el snapshot temporal privado del origen completo, sin DDL/DML de destino; el temporal se eliminó. La compilación frontend pasa. Antes de activar el ensayo se requieren pruebas sobre la copia MariaDB restaurada y revisión de las 20 incidencias; antes de cualquier migración definitiva se necesita autorización expresa y completar los bloqueos operativos indicados.
