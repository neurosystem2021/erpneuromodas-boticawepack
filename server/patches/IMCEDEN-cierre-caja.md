# IMCEDEN: movimientos pendientes al cerrar caja

El cierre marcaba únicamente movimientos registrados el mismo día y con
`EsDelVendedor=1`, aunque el reporte mostraba otros movimientos pendientes.
Los vales anteriores seguían apareciendo en el siguiente turno.

La corrección de `api/index.php` vincula al cierre los movimientos pendientes
del usuario seleccionado, incluidos los vales manuales, fechas anteriores y
referencias bancarias. Los movimientos de otros usuarios y los cierres anteriores
se conservan. Los bancos mantienen su carácter informativo y no se suman al
efectivo de Caja Principal.

La consulta bancaria utiliza `IdCierre`, de modo que un turno que atraviesa la
medianoche conserva sus referencias. Al cerrar se recargan los listados existentes
de la pantalla. El cambio no altera el diseño ni requiere compilar el frontend.

Ventas y movimientos se guardan en una transacción. Si falla, se revierte esa
asignación y se retira solamente la cabecera fallida recién creada, para admitir
las instalaciones antiguas cuya cabecera todavía usa MyISAM.

## Publicación

El archivo `imceden-cash-closing.patch` contiene únicamente las tres rutas
afectadas. Aplicarlo desde la raíz del backend de IMCEDEN, después de respaldar
`api/index.php` fuera de la carpeta pública:

```sh
git apply --check /ruta/imceden-cash-closing.patch
git apply /ruta/imceden-cash-closing.patch
php -l api/index.php
```

Si la comprobación no coincide con la versión instalada, revisar las diferencias
antes de aplicarlo. No utilizar opciones para forzar el parche. Para verificar
una instalación que ya tiene el cambio:

```sh
git apply --reverse --check /ruta/imceden-cash-closing.patch
```

Aplicar el código no ejecuta cierres ni modifica movimientos existentes. Los
pendientes se asociarán cuando el usuario realice su próximo cierre normal.

## Verificación local

```powershell
C:\xampp\php\php.exe tools/test-imceden-cash-close.php
C:\xampp\php\php.exe tools/test-imceden-cash-close.php --mysql-temporary
C:\xampp\php\php.exe tools/check-imceden-cash-close.php
```

Las pruebas ejecutan las rutas reales con vales de fechas anteriores, cambios de
turno, usuarios distintos, referencias bancarias e incidencias simuladas. Usan
SQLite en memoria o tablas temporales de MySQL, incluyendo la combinación de
cabecera MyISAM y movimientos InnoDB; no ejecutan un cierre real.
