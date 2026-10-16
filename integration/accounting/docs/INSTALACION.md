# Instalación y migración histórica controladas

## Estado del entregable

El desarrollo y las pruebas se realizaron en la rama de trabajo y bases temporales. **No se ejecutó la migración en la base ERP real ni en nstarapaca.factura.vip.** El documento solicitado prohíbe producción e históricos sin aprobación y respaldo verificado.

El paquete de release contiene código del módulo, migración, documentación, interfaz compilada y manifiesto SHA-256. No contiene bases de datos del negocio, contraseñas, sesiones, certificados ni configuración local.

## Revisión previa

1. Aprobar el diagnóstico y el mapeo de empresas/almacenes/cuentas. Verificar que los nombres del esquema ERP respetan las mayúsculas de las consultas en Linux; una exportación Windows en minúsculas no verifica esto.
2. Contador revisa PCGE, naturaleza/rubros, cuentas predeterminadas, régimen, obligación y tratamiento del IGV de la actividad.
3. Respaldar base y archivos existentes en un directorio privado, fuera del sitio web. Verificar SHA-256 **y restaurar ese respaldo en una base de prueba**. El hash comprueba integridad del archivo, no sustituye la prueba de restauración.
4. Verificar PHP 7.4+ de 64 bits, PDO MySQL, mbstring, JSON, ZIP, PhpSpreadsheet existente en `api/vendor` y TCPDF existente en `api/sunat/plugins/tcpdf`. Ejecutar lint y pruebas en staging.
5. Revisar el manifiesto y conservar respaldo de cualquier archivo que ya exista con el mismo nombre. No copiar `.env`, `database.local.php` ni configuraciones privadas del equipo local al servidor.

## Migración versionada

Primero, revisión sin aplicar cambios:

```sh
php module/accounting-install.php --app=/ruta/erp --database=BASE_APROBADA
```

Solo después de aprobación y restauración del respaldo:

```sh
php module/accounting-install.php --app=/ruta/erp --database=BASE_APROBADA \
  --apply --backup=/ruta/privada/respaldo-verificado.sql --sha256=HASH_REAL
```

La herramienta comprueba base conectada, hash del respaldo, bloqueo de migración y hashes de versiones aplicadas. Crea exclusivamente tablas nuevas `Co_*`; no usa SQL dinámico derivado de usuarios ni altera registros históricos. MySQL hace autocommit de DDL: una interrupción debe resolverse inspeccionando la migración, no borrando tablas con movimientos. Repetir una versión aplicada con hash distinto se rechaza.

No hay rollback destructivo automático. Antes de puesta en uso, se puede deshabilitar el módulo conservando sus tablas. Después de contabilizar, no eliminar tablas: restaurar únicamente dentro de un plan aprobado y reconciliado con las operaciones posteriores.

## Archivos de integración

Con la migración aplicada y autorización de publicación:

1. Copiar `module/api/accounting*.php` y `module/api/accounting-pcge2019.json` a `ERP/api/`.
2. Copiar `module/contabilidad.php` a `ERP/views/contabilidad.php`.
3. Incluir `menu.snippet.php` dentro de la lista de navegación del header existente. El menú se muestra solo a superadmin o miembros contables activos.
4. Publicar los archivos de `frontend/` en `ERP/nuevo/`, conservando las rutas. Publicar assets antes de `index.html`. No eliminar assets anteriores utilizados por sesiones abiertas.
5. Abrir `/nuevo/index.html#/contabilidad` y ejecutar la primera configuración del manual.

La publicación no debe sustituir a ciegas el header ni `api/index.php`; el controlador contable es independiente y utiliza la misma sesión/conexión del ERP.

## Datos históricos

1. Definir inicio contable y período de comparación. Decidir si se cargarán saldos iniciales revisados o todos los documentos históricos; **no ambas opciones sobre el mismo saldo**.
2. Exportar y verificar respaldo; registrar quién autoriza el lote, empresa, sucursal, fechas y hash.
3. Asignar almacenes/cuentas a empresa. Capturar por rango reducido. La captura solo lee el ERP y registra pendientes en el módulo.
4. Revisar total por fuente y documento. Los faltantes de costo, moneda, identidad, afectación o clasificación quedan pendientes. No usar el costo actual para completar un costo histórico nulo.
5. Generar borradores revisados. Comparar ventas/compras/notas, costos, cuentas por cobrar/pagar, impuestos y caja con los reportes ERP y documentos.
6. Aprobar y contabilizar por lote/período. Verificar sumas, cantidades y ausencia de duplicados. Las claves de origen y revisión permiten reintentar sin duplicar.
7. Conciliar bancos y registros fiscales. Resolver conflictos y reversar correcciones; no modificar el ERP histórico para hacer cuadrar el módulo.
8. Cerrar períodos únicamente después de la conciliación firmada.

Un cambio en un documento ERP ya capturado aparece como conflicto y exige revisión. La captura no añade cuentas por empresa adivinando el concepto de una operación.

## Sincronización programada

El job CLI se activa únicamente durante el despliegue aprobado, con un usuario ERP que ya tenga rol contable para esa empresa:

```sh
php module/accounting-sync.php --app=/ruta/erp --company=ID_EMPRESA \
  --user=USUARIO_CONTABLE --from=AAAA-MM-DD --to=AAAA-MM-DD
```

Programar en cron con rangos solapados razonables; por ejemplo, captura reciente frecuente y captura completa del período antes del cierre. El job genera pendientes, no contabiliza ni envía documentos. Salida JSON con capturados, sin cambios y conflictos; errores llevan código de salida distinto de cero. Supervisar logs fuera de web. No activar un job global para todas las empresas sin configurar sus asignaciones.

## Homologación tributaria

- Probar casos reales revisados de gravadas, exoneradas, inafectas, gratuitas, NC, notas de débito, crédito fiscal y cobros/pagos sujetos a regímenes especiales.
- Comparar con propuesta SUNAT y validar archivos en los programas oficiales PLE/PVSIRE.
- Determinar obligaciones por empresa/período con el contador. No presentar un archivo de prueba ni un reporte de apoyo como declaración.
- Las estructuras de regímenes especiales no cubiertas por un documento deben registrarse mediante ajustes revisados o ampliarse antes de su uso; no introducir tasas/saldos ficticios para completar el formato.

## Comprobación posterior

Probar con superadmin, contador, auxiliar, auditor y gerente; confirmar denegación entre empresas. Revisar una operación repetida, un período cerrado, una NC, un reverso, un banco agrupado, una factura multimoneda y exportación Excel/PDF. Confirmar que stock, cobros, facturación e impresión siguen usando sus servicios originales.
