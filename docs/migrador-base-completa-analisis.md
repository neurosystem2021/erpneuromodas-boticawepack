# Migrador: análisis del origen completo

Revisión: 15/10/2026, America/Lima. Origen vigente: `C:\Users\USER\Downloads\fact_nst_COMPLETA.sql`. Destino inspeccionado: base `nst` del ERP instalado en `C:\xampp\htdocs\boticav1`.

Este documento reemplaza las conclusiones sobre el archivo anterior `fact_nst (2).sql`. **No se ejecutó el SQL, no se importaron datos ni se reparó producción.** Los resultados se obtuvieron leyendo el archivo offline y consultando metadatos y agregados de destino en modo de solo lectura.

## Resultado

El archivo completo sí contiene ventas, detalles y pagos relacionados. Ya no aplica el bloqueo por ausencia de ventas. La migración definitiva continúa bloqueada por notas de crédito huérfanas, tablas dañadas en destino y necesidad de aislar los efectos de inventario y envío fiscal.

| Entidad | Origen completo | Destino, revisión previa |
|---|---:|---:|
| Ventas | 4.638 | 5 |
| Detalles de venta | 7.131 | 5 |
| Pagos | 4.845 | 4.733 |
| Notas de crédito relacionadas | 23 | 23 |
| Productos | 2.860 | 2.847 |
| Clientes | 4.325 | 4.275 |
| Proveedores | 207 | 207 |
| Movimientos logísticos | 867 | 3 |
| Detalles logísticos | 3.338 | 4 |
| Órdenes de compra | 1 | No verificable: tabla dañada |
| Detalles de órdenes | 199 | No verificable: tabla dañada |
| prodstock | 122 | 113 |

Los conteos de destino proceden de la revisión inicial. Las tablas MyISAM no ofrecen una instantánea transaccional coherente ante cambios concurrentes; se deben repetir las validaciones sobre una copia consistente antes de aprobar.

## Integridad e importes del origen

- Cero detalles de venta sin cabecera; cero pagos sin venta; cero detalles con producto inexistente; cero ventas sin cliente relacionado.
- Cada una de las 4.638 ventas tiene al menos un detalle y un pago.
- Persisten **23 registros de `Ve_DocVentaNotaCredito` cuyo `idDocVenta` no está entre las ventas del archivo**. Deben conservarse en cuarentena y resolverse individualmente. No crear cabeceras ficticias ni asociar por igualdad numérica con IDs actuales.
- No se detectaron grupos duplicados en la combinación tipo/punto/serie/número dentro del origen, ni códigos de barras duplicados no vacíos. Falta comprobar colisiones fiscales entre origen y destino y verificar la identidad del emisor.
- Suma aritmética de detalles (`Cantidad × Precio − Descuento`): **1.360.645,0644**. Suma de pagos: **1.360.645,07**. Cuatro documentos tienen diferencias no nulas; ninguna supera 0,01 y la mayor es 0,0040. Es consistente con redondeo, pendiente de confirmar las reglas del ERP. No se corrigieron ni redondearon los valores originales.
- `Ve_DocVenta` no declara una columna de moneda. No se supone PEN ni se certifica aún conciliación por moneda. Los grupos por tipo, estado, crédito y anulación se guardaron en el reporte JSON.
- La única orden de compra declara total cero y tiene 199 detalles: requiere explicación o conciliación, sin interpretarla como recibida.

## Compras y existencias

Los movimientos se distribuyen en 250 compras con factura (160 USD y 90 PEN), una compra con boleta PEN, 392 ingresos, 64 traslados y 160 salidas. No se consideran todos compras. `VaRegCompra=1` también aparece en tipos que no son compras; no es un clasificador contable suficiente. Las relaciones cabecera/detalle de movimientos y órdenes y los productos de movimientos no presentan huérfanos en el archivo analizado.

El stock nuevo se calcula desde ventas y movimientos, sus tipos, anulaciones, notas de crédito y componentes de kits: tanto la vista `Gen_Producto_Totales` como consultas directas de la API intervienen. Insertar históricos en tablas operativas alteraría existencias aunque no se toque `prodstock`. Este último carece de dimensión de almacén y se utilizará como evidencia auxiliar, no como saldo automático.

El modo inicial propuesto mantiene el historial en staging/archivo separado InnoDB. Su consulta se integrará explícitamente en reportes históricos. La integración en tablas operativas requiere primero excluir los históricos de todas las rutas de stock, cobranza y SUNAT, y verificar diferencia de existencias cero. No falsear tipos documentales, estados o fechas para lograr esa exclusión.

## Compatibilidad de estructura y mapeo

76 tablas físicas del origen tienen correspondencia por nombre con el destino, que contiene 120 objetos; hay 44 objetos adicionales. Esto no demuestra que los IDs coincidan. El destino físico usa nombres minúsculos en Windows: el código debe resolver nombres validados y funcionar también con sensibilidad a mayúsculas en Linux.

En las tablas inspeccionables no se detectaron columnas antiguas ausentes ni cambios de tipo con el comparador preliminar. El JSON conserva las columnas antiguas completas, claves inline/ALTER e índices actuales para revisar atributos, defaults, colaciones y restricciones. No se declara completa la compatibilidad de las cuatro tablas ilegibles.

Campos nuevos: producto incorpora imágenes adicionales, fechas de oferta, talla, código por modelo, precios/oferta web y `EsServicioManoObra`; `Ve_Proforma`, `WebCargado`; `GEN_EMPRESA`, `CELULAR` y `EMAIL`. No sobrescribir estos campos en registros actuales ni importar credenciales de empresa automáticamente. ALI, CRM, diario, planillas y demás objetos nuevos se preservan.

| Entidad origen | Destino propuesto | Regla |
|---|---|---|
| Gen_Producto | Catálogo actual + mapa histórico | ID histórico previamente mapeado; código de barras único exacto como candidato, con revisión de marca/modelo/unidad. No unir solo por nombre. |
| Ve_DocVentaCliente / Lo_Proveedor | Catálogos respectivos + mapa | DNI/RUC válido normalizado y único como candidato; documentos genéricos o ambiguos requieren revisión. |
| Lo_Almacen / tipos / puntos | Catálogos actuales | Equivalencia explícita por sede, significado, código fiscal y punto emisor. No usar los mismos números por defecto. |
| Ve_DocVenta / Det / pagos | Archivo histórico relacionado | Conservar fechas, numeración, cantidades, precios, descuentos y estados. Remapear todas las referencias, sin recalcular con reglas actuales. |
| Ve_DocVentaNotaCredito | Archivo fiscal / cuarentena | Resolver las 23 relaciones faltantes; conservar hashes, respuestas y motivos. No reenviar a SUNAT. |
| Lo_OrdenCompra / Det | Órdenes históricas | Preservar condición de pedido, sin generar recepción ni asiento. |
| Lo_Movimiento / Detalle / Tipo / Impuesto | Archivo logístico | Clasificar compra/ingreso/traslado/salida; enlazar detalles por Hash/hashMovimiento remapeado. |
| Series / kits / componentes | Relaciones históricas | Remapear productos, movimiento y venta; evitar doble consumo de componentes. |
| prodstock | Conciliación | No actualizar stock automáticamente. |
| Preórdenes / proformas / cierres / remisiones / pedidos / ofertas / comisionistas | Dependencias a resolver | Cada referencia debe mapearse o registrarse como conflicto; nunca dejar apuntando a una entidad actual por coincidencia de ID. |

## Conflictos del destino que continúan

`Gen_Moneda`, `Lo_OrdenCompra`, `Lo_OrdenCompraDet` y `tblKardexValor` devuelven error MariaDB 145, tabla marcada como dañada. No se ejecutó REPAIR. Se debe respaldar y ensayar recuperación sobre una copia, verificando filas/índices antes y después.

Existen 4.728 pagos y 23 notas de crédito sin venta en el destino actual. La carga completa puede permitir investigar su origen, pero **no debe completar esas relaciones automáticamente usando el ID antiguo**. Requieren evidencia de identidad y una resolución auditada.

Las tablas logísticas MyISAM no permiten confiar en rollback. El respaldo consistente requiere una ventana o bloqueo autorizado, además de prueba de restauración: `--single-transaction` solo no cubre MyISAM.

## Implementación y autorización

Esta entrega corresponde al análisis previo solicitado, no a un importador o panel terminado. Los siguientes artefactos se desarrollarán en el repositorio y se ensayarán en destino aislado: panel administrativo, perfiles de conexión privados, staging InnoDB, `Mig_Run`, `Mig_Batch`, `Mig_IdMap`, `Mig_RowAudit`, `Mig_Issue`, `Mig_Reconciliation`, simulación, checkpoints, reintentos idempotentes, cuarentena y reporte final.

La equivalencia tendrá clave única por sistema/entidad/ID original, ID nuevo, huella y ejecución. Las líneas sin PK necesitan identidad estable que preserve multiplicidad: archivo SHA-256, entidad, padre, ordinal y huella. No deduplicar líneas legítimamente idénticas. Secretos nunca en Vue, reportes o logs.

Aprobación bloqueada ante diferencias no justificadas, relaciones faltantes, stock alterado, emisión fiscal posible, respaldo no restaurable o destino no autorizado. La reversión retirará exclusivamente filas del run comprobando huellas y referencias posteriores; no borrará registros preexistentes. MyISAM o cambios posteriores requieren un procedimiento de restauración controlado. Toda migración definitiva y reparación en producción continúa requiriendo autorización explícita.

## Evidencia reproducible

- `tools/compare-migration-schema.py`: comparación offline; origen vigente completo.
- `tools/analyze-migration-source.py`: parsea INSERT VALUES sin ejecutarlos; conserva precisión Decimal y normaliza literales BIT. Su salida es agregada y no incluye datos personales.
- `tools/analyze-migration-target.php` y `tools/analyze-migration-integrity.php`: consultas SELECT/SHOW de destino, sin DDL ni DML.
- `output/migration-schema-comparison.json`: archivo y SHA-256, estructura y mapeo preliminar.
- `output/migration-source-validation.json`: conteos, relaciones, grupos y conciliación individual.
- `output/migration-target-schema.json` y `output/migration-target-validation.json`: evidencia de la revisión de destino.

El parser cubre el formato INSERT VALUES de este dump; no sustituye una restauración completa aislada ni una validación fiscal/contable final.
