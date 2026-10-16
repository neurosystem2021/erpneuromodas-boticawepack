# Publicación del migrador en el ERP web

Este paquete incorpora **Seguridad → Migrador de compras y ventas**, visible y accesible únicamente para el usuario autenticado `superadmin`. Usa las sesiones y permisos existentes del ERP; otros administradores reciben 403 incluso mediante URL o API directa. No requiere un servidor de pruebas en otro puerto ni rutas de Windows.

## Qué se publica

Subir mediante SFTP/FTP o administrador de archivos del hosting, conservando carpetas:

- `api/migracion.php` y `api/migration/`: API exclusiva del módulo. No reemplaza `api/index.php`, el POS ni el router ALI existente.
- `migration-ui/`: pantalla y Vue incluidos, sin necesidad de instalar Node en el hosting.
- `views/migrador-historico.php`: entrada autenticada integrada al encabezado del ERP.

Hacer respaldo de archivos antes de publicar. No subir las plantillas privadas como configuración activa al directorio web. El ZIP no contiene contraseñas, certificados, respaldo de la base ni datos de clientes.

En `views/header.php`, dentro del menú Seguridad, agregar el contenido de `menu-superadmin.snippet.php`. No reemplazar el header completo. El enlace incluido supone ERP en la raíz del dominio; si está en una subcarpeta, ajustar únicamente el href, por ejemplo `/erp/views/migrador-historico.php`. El iframe y API del módulo usan rutas relativas.

Después, ingresar como superadmin, recargar y abrir **Seguridad → Migrador de compras y ventas**. Sin perfil privado la pantalla mostrará que falta configurar; la ejecución seguirá deshabilitada. Las credenciales se configuran en el servidor, nunca en el navegador.

## Configuración privada del servidor

1. Preparar y verificar una copia restaurada de la base del ERP en ese hosting, con cuenta MySQL limitada a la copia. No utilizar la base de producción como destino. El módulo también bloquea automáticamente el nombre de la base configurada actualmente en el ERP.
2. Crear carpeta privada fuera de todos los directorios públicos, por ejemplo `/home/USUARIO/neuro-private/migration`, con permisos 0700 y archivos 0600 según el usuario de PHP. No usar `public_html`, `www`, `htdocs`, `migration-ui` ni `api` para respaldos o datos históricos.
3. Copiar allí `profile.example.json` y `backup-manifest.example.json` desde las plantillas, completar valores reales y mantener `enable_copy_writes=false`. No marcar la restauración verificada sin haber comprobado conteos, huellas, integridad y una restauración completa.
4. Colocar el snapshot revisado de `fact_nst_COMPLETA.sql` en esa carpeta privada, conservar su SHA-256 y el respaldo verificado de la copia. No ejecutar el dump antiguo contra producción. El paquete instala el módulo, **no incluye ni sube automáticamente el SQL o snapshot histórico**.
5. Configurar `NEURO_MIGRATION_CONFIG` con la ruta absoluta del perfil, mediante el entorno de PHP del hosting. Alternativamente, configurar `NEURO_PRIVATE_DIR` y `migration/active-profile.txt` con la ruta del perfil. Si no hay variables de entorno, se busca `neuro-private/migration/active-profile.txt` en el directorio padre de DOCUMENT_ROOT, siempre fuera del área pública. El puntero contiene solo la ruta del perfil privado.
6. Autorizar únicamente el ensayo sobre la copia y habilitar su escritura. Instalar auditoría desde Terminal/cPanel/SSH, en la raíz del ERP:

```sh
php tools/migration/install-copy.php --copy=USUARIO_neuro_migration_test
```

Los scripts incluidos usan rutas relativas al paquete. Si el hosting no ofrece terminal, todavía se necesita un procedimiento administrativo para instalar el esquema en la copia; no hay instalador público anónimo ni se ejecuta DDL automáticamente desde el panel.

## Uso y límites

Seleccionar módulos → comparar estructuras → vista previa → simular → resolver incidencias → aprobar exclusivamente la copia → ejecutar lotes → descargar reporte. La escritura del plan de simulación y auditoría ocurre solo en la copia configurada. La carga no se aprueba automáticamente al instalar.

El modo implementado es **archivo histórico separado**, con equivalencias de IDs, preservación de originales, lotes, reanudación y reversión. No inserta en las tablas operativas, no actualiza stock, no crea cobros y no envía documentos a SUNAT. No está habilitada una migración definitiva a las tablas operativas del ERP; esa fase requiere resolver incidencias y autorización explícita independiente.

El servidor debe contar con PHP compatible con el ERP (probado con PHP 7.4), PDO MySQL, mbstring, JSON, sesiones y Slim 3 existente en `api/vendor`. La simulación del archivo completo requiere hasta 512 MB de memoria de PHP. El plan grande se almacena por filas en `Mig_PlanRow` para respetar límites de paquetes MySQL. Probar límites de tiempo y memoria en el hosting antes de cargar históricos; no se certifica un hosting remoto que aún no se ha inspeccionado.

Este paquete no publica por sí solo el ERP en un dominio. Se necesitan los datos del servidor de destino y su acceso autorizado para instalarlo y verificarlo allí.
