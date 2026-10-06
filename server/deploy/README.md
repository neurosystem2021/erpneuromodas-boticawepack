# Estándar de despliegue ERP

Este directorio evita configurar permisos, logs y conexiones manualmente en cada proyecto.

## Primera instalación de un proyecto

Después de clonar o actualizar el proyecto en el servidor, ejecutar una sola vez desde su raíz:

```bash
sudo bash build/deploy/install-production.sh
```

El instalador:

- solicita las credenciales únicamente si todavía no existe `config/database.local.php`;
- mantiene las credenciales fuera de Git;
- detecta el usuario y grupo usados por OpenLiteSpeed;
- alinea el manejador web del vhost con PHP 7.4 y conserva un respaldo;
- reinicia de forma aislada el proceso LSPHP del sitio;
- crea los logs con permisos compatibles tanto con suExec como con el usuario global de OpenLiteSpeed;
- verifica las extensiones `mysqli` y `pdo_mysql`;
- prueba ambas conexiones a MySQL;
- aplica las migraciones idempotentes del punto de venta, incluidas observaciones, placa y descripcion del cierre de caja;
- reinicia OpenLiteSpeed y prueba una ruta real de la API;
- instala un hook `post-merge` para verificar automáticamente los siguientes `git pull`.

## Verificación manual

```bash
sudo bash build/deploy/deploy-production.sh
```

Si el dominio no coincide con el nombre del directorio:

```bash
sudo bash build/deploy/deploy-production.sh --domain ejemplo.factura.vip
```

La aplicación heredada está certificada con PHP 7.4. El script usa primero el binario
`/usr/local/lsws/lsphp74/bin/php` y alinea el vhost cuando este declara otro manejador.
Para omitir ese ajuste de forma explícita se puede usar `--keep-web-php`.

## Seguridad

Nunca se debe subir `config/database.local.php`, `.env` ni una clave MySQL al repositorio.
El archivo privado queda con modo `640`: el propietario corresponde al usuario del
vhost y el grupo al proceso global de OpenLiteSpeed. No queda habilitado para otros.
