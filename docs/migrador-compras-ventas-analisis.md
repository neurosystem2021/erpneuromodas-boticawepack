> SUPERADO: este informe analiza fact_nst (2).sql. El origen vigente es fact_nst_COMPLETA.sql; consultar [analisis vigente](migrador-base-completa-analisis.md).

# Migrador Factura.vip → ERP Neurosof: análisis previo

Fecha de revisión: 15/10/2026, America/Lima. Estado: **NO APTO PARA MIGRACIÓN DEFINITIVA**.

## Alcance realizado

Se inspeccionó la conexión efectiva del ERP instalado en `C:\xampp\htdocs\boticav1`, que apunta a la base **nst**. Se leyó exclusivamente el archivo autorizado `C:\Users\USER\Downloads\fact_nst (2).sql`, sin importarlo ni ejecutar su SQL. Se consultaron metadatos y conteos mediante SELECT/SHOW y una transacción de solo lectura. No se crearon ni repararon tablas, ni se modificaron datos de producción. Los scripts y reportes se guardaron únicamente en el repositorio.

Los conteos son una fotografía preliminar: las tablas MyISAM no proporcionan una instantánea transaccional coherente ante escrituras concurrentes. La validación de aprobación debe repetirse sobre un respaldo consistente y una copia aislada.

## Hallazgos que bloquean la aprobación

1. **Ventas ausentes en el origen elegido.** El dump define `Ve_DocVenta` y `Ve_DocVentaDet`, pero no contiene INSERT para ellas: cero cabeceras y cero detalles. Incluye 4.811 pagos y 23 notas de crédito. No se debe fabricar una venta, borrar pagos o asignarlos por coincidencia de ID. Se necesita recuperar las cabeceras y detalles desde un respaldo íntegro o desde la base antigua.
2. **Tablas dañadas en destino.** MariaDB devuelve error 145 al inspeccionar `Gen_Moneda`, `Lo_OrdenCompra`, `Lo_OrdenCompraDet` y `tblKardexValor`. Su estructura no pudo verificarse por completo. No son columnas eliminadas: son tablas NO VERIFICABLES. No se ejecutó REPAIR TABLE. Cualquier reparación debe probarse primero sobre una copia respaldada, comprobando pérdida de filas e índices antes/después.
3. **Relaciones huérfanas preexistentes en destino.** Hay 4.728 filas de pagos sin venta y 23 notas de crédito sin venta. Las cinco ventas y cinco detalles actuales tienen clientes/productos/cabeceras relacionados. No se debe arreglar una relación huérfana enlazando al nuevo ID numéricamente igual.
4. **El historial afecta el inventario.** `Gen_Producto_Totales` es una vista calculada desde ventas y movimientos, con tipos `VaRegVenta`/`VaRegCompra`, anulaciones, notas de crédito y componentes de kits. La API también calcula existencias directamente sobre esas tablas. Insertar históricos allí cambia stock aunque no se actualice `prodstock`. Una exclusión solo en la vista sería insuficiente.
5. **Compras no transaccionales.** `Lo_Movimiento` y `Lo_MovimientoDetalle` son MyISAM. Un rollback no revierte sus escrituras. No se autoriza carga directa con la promesa de recuperación transaccional; se requiere staging InnoDB y una estrategia explícita para la copia de destino.
6. **Reenvío fiscal sin mecanismo verificado.** Conservar `Hash_cpe`, `Hash_cdr`, respuestas y estados SUNAT no basta para garantizar exclusión de las colas de envío. Deben identificarse y bloquearse todos los caminos de emisión/reenvío antes de insertar históricos en tablas operativas.

## Estructuras comparadas

Origen: 76 tablas físicas. Destino: 120 objetos (incluye la vista de stock). Las 76 tablas antiguas tienen un nombre correspondiente en destino, ignorando mayúsculas/minúsculas. Hay 44 objetos adicionales. Ninguna tabla antigua aparece eliminada por nombre. Esta equivalencia no implica equivalencia de registros ni IDs.

En las tablas legibles no se detectaron columnas antiguas ausentes ni cambios de tipo con el comparador preliminar. Se conservaron las definiciones originales, índices y claves en los JSON para revisión completa. Las cuatro tablas dañadas quedan fuera de cualquier conclusión de compatibilidad de columnas. La revisión de claves foráneas encontró siete relaciones declaradas en destino; las relaciones lógicas del ERP son más amplias y deben validarse explícitamente.

Campos añadidos en producto: `Imagen2`, `Imagen3`, `FechaOfertaInicio`, `FechaOfertaFin`, `IdProductoTalla`, `CodeBarModelo`, `PrecioWeb`, `PrecioAnterior`, `OfertaWeb`, `EsServicioManoObra`. Deben conservarse en productos ya existentes; no sobrescribirlos con defaults del origen. `Ve_Proforma` incorpora `WebCargado`.

Los objetos nuevos de ALI, CRM, diario, planillas, cuentas de tienda y auditoría no deben ser reemplazados por el dump antiguo. El listado completo está en `migration-schema-comparison.json`.

## Conteos relevantes del SQL autorizado

| Entidad | Filas origen | Filas destino preliminares |
|---|---:|---:|
| Ventas | 0 | 5 |
| Detalles de venta | 0 | 5 |
| Pagos de venta | 4.811 | 4.733 |
| Notas de crédito relacionadas | 23 | 23 |
| Productos | 2.857 | 2.847 |
| Clientes | 4.310 | 4.275 |
| Proveedores | 207 | 207 |
| Movimientos logísticos | 867 | 3 |
| Detalles de movimiento | 3.338 | 4 |
| Órdenes de compra | 1 | No verificable |
| Detalles de órdenes | 199 | No verificable |
| prodstock | 122 | 113 |

Los 867 movimientos se desglosan en 250 compras con factura (160 USD, 90 PEN), una compra con boleta PEN, 392 ingresos, 64 traslados y 160 salidas. Las relaciones cabecera/detalle de movimientos y ?rdenes y los productos de movimientos no presentan hu?rfanos en el SQL analizado. `VaRegCompra=1` tambi?n est? presente en tipos que no son compras, por lo que no sirve como clasificador contable ?nico.

La suma de pagos del origen es 1.357.112,07, **sin moneda atribuible ni cabeceras para conciliar**: no representa un total de ventas certificado. La única orden de compra declara total cero pese a tener 199 detalles; debe recalcularse y explicarse sin asumir recepción. No se detectaron grupos de comprobantes duplicados en las cinco ventas actuales ni códigos de barras duplicados no vacíos en productos actuales. Esto no valida cruces origen/destino.

## Mapeo propuesto (pendiente de aprobar y completar)

| Origen | Destino propuesto | Regla y conflicto |
|---|---|---|
| Gen_Producto | Gen_Producto | Mapa de ID histórico primero; código de barras no vacío exacto y único como candidato. Marca/modelo/unidad como evidencia adicional. No unir solo por nombre ni sobrescribir ficha/precios actuales. |
| Ve_DocVentaCliente | Ve_DocVentaCliente | Mapa histórico; DNI/RUC normalizado y válido como candidato. Documento vacío o genérico requiere revisión. |
| Lo_Proveedor | Lo_Proveedor | Mapa histórico; RUC válido único y revisión de razón social. |
| Lo_Almacen | Lo_Almacen | Equivalencia explícita por sede/almacén; nunca asumir igualdad de ID. |
| Ve_DocVentaTipoDoc / PuntoVenta | Homólogas | Código SUNAT, serie y punto emisor verificados. No alterar correlativos activos para acomodar el historial. |
| Ve_DocVenta | Archivo histórico aislado inicialmente | Sin datos en dump. Identidad: origen, emisor, tipo, serie, número; conservar estado y referencias fiscales. No emitir nuevamente. |
| Ve_DocVentaDet | Archivo de detalles históricos | ID venta/producto remapeados, cantidades/precios/descuentos originales. No recalcular impuestos históricos con reglas actuales. |
| Ve_DocVentaMetodoPago | Catálogo existente | Equivalencia semántica, no por número. |
| Ve_DocVentaMetodoPagoDet | Archivo de pagos históricos | Clave compuesta y huella de origen; pagos sin cabecera en cuarentena. No descartar ni asignar automáticamente. |
| Ve_DocVentaNotaCredito | Archivo fiscal histórico | Validar `idDocVenta`, comprobante modificado, motivo, hashes y estados; bloquear huérfanos. |
| Lo_OrdenCompra / Det | Órdenes históricas | Pedido, no compra recibida ni asiento contable. Tabla destino dañada. |
| Lo_Movimiento / Detalle / Tipo / Impuesto | Archivo logístico histórico | Investigar tipos de compra, devoluciones, traslados y ajustes; detalles se enlazan por `Hash`/`hashMovimiento`, no ID de orden. |
| Gen_ProductoDet / Compuesto / Serie | Relaciones históricas | Remapear todos los productos, series, movimiento y venta; no duplicar consumo de kits. |
| prodstock | Evidencia de conciliación | Snapshot auxiliar, no fuente de saldo automática; carece de dimensión de almacén. |
| Preórdenes / proformas / cierres / remisiones | Dependencias según referencias | Preservar `IdPreOrden`, `IdProforma`, `IdCierre`, `IdPedido`, `IdOferta`, comisionista y documentos modificados mediante mapeo o excepción explícita. |

Los nombres del destino reportados físicamente son minúsculas en este Windows. El módulo debe usar nombres descubiertos/validados y no depender de la insensibilidad a mayúsculas para funcionar en Linux.

## Diseño de corrección y desarrollo posterior

Primero archivar fielmente el origen en staging privado InnoDB, conservando fila original, hash, lote y versión de mapeo. El modo inicial será **histórico sin afectar stock**, con tablas históricas separadas y consultas de reportes expresamente integradas. La incorporación a tablas operativas solo procederá después de implementar y probar exclusión de inventario, cobros y SUNAT en todos los caminos pertinentes. No cambiar tipos documentales, estados ni fechas para simular esa exclusión.

Tablas propuestas (todavía NO creadas): `Mig_Run`, `Mig_Batch`, `Mig_IdMap`, `Mig_RowAudit`, `Mig_Issue`, `Mig_Reconciliation`, y staging/histórico por entidad. `Mig_IdMap` tendrá clave única por sistema origen + entidad + ID original, ID nuevo, huella, resolución y ejecución. Los detalles sin PK requieren identidad estable que preserve multiplicidad: entidad, clave del padre, ordinal de la fila en el archivo identificado por SHA-256 y hash; no deduplicar líneas legítimamente iguales.

Lotes con checkpoints y estados pendiente/en curso/aplicado/fallido; reintentos idempotentes; cambios al origen/mapeo invalidan simulación previa. Desambiguación humana de coincidencias múltiples. Decimales exactos para conciliación aunque el origen almacene FLOAT. Fechas con zona original documentada, sin desplazar silenciosamente horas. Colaciones latin1/utf8 verificadas con muestras y preservación del valor bruto ante codificación dudosa.

Panel administrativo propuesto: origen mediante perfil de conexión del servidor (secretos fuera de Vue/logs), módulos, comparación, simulación, vista previa con datos sensibles restringidos, avance por lotes, incidencias, historial y descarga del reporte. El botón ejecutar permanecerá bloqueado en producción, sin simulación reconciliada, sin respaldo verificado o con incidencias no justificadas. La aprobación requiere usuario administrador y autorización explícita de destino/ejecución; no debe aceptar un simple parámetro del navegador como autorización.

## Validación y reversión

Antes de aprobar: conteos por módulo y estado, cabeceras/detalles, totales por moneda/estado/emisor y periodo, descuentos/impuestos, pagos y crédito, anulaciones, referencias SUNAT, productos/clientes/proveedores, duplicados y relaciones. Medir saldos por producto/almacén antes y después: modo histórico exige diferencia cero. Verificar que ninguna fila migrada entre a procesos de envío SUNAT o cobranza actual. Excepciones documentadas requieren responsable, motivo, evidencia y resolución; no esconder faltantes como advertencias aprobables.

El respaldo debe incluir esquema, datos y artefactos fiscales/adjuntos existentes, con SHA-256 y prueba de restauración aislada. Para MyISAM se necesita una ventana consistente o bloqueo administrado autorizado; `--single-transaction` solo no garantiza consistencia. No se ha tomado respaldo todavía porque esta fase no escribe bases ni archivos de producción.

Reversión propuesta: en copia aislada, retirar exclusivamente filas insertadas por el run, en orden inverso de dependencias, comprobando huellas y ausencia de referencias nuevas. Nunca borrar ni restaurar por IDs supuestos. Si una fila cambió después o hay efectos en tablas no transaccionales, bloquear reversión automática y restaurar la copia desde respaldo verificado. En producción, cualquier reversión también requerirá autorización explícita.

## Archivos reproducibles

- `tools/analyze-migration-target.php`: metadatos/conteos de destino, solo lectura; registra tablas no inspeccionables.
- `tools/analyze-migration-integrity.php`: validaciones agregadas actuales, solo lectura.
- `tools/compare-migration-schema.py`: comparación offline con el SQL autorizado.
- `tools/analyze-migration-source.py`: lectura offline de INSERT VALUES, conteos y relaciones; no ejecuta SQL.
- `output/migration-target-schema.json`, `migration-schema-comparison.json`, `migration-source-validation.json`, `migration-target-validation.json`: evidencia técnica.

Los scripts PHP usan la configuración privada existente sin imprimir credenciales. Los reportes contienen estructura y agregados, no nombres/documentos de clientes ni tokens. El parser es para el formato INSERT VALUES de este dump y no sustituye la validación de una restauración aislada completa. No es todavía un importador ni un panel terminado.

## Paso necesario

Recuperar un respaldo que incluya las ventas y detalles faltantes. Preparar y autorizar una copia aislada de la base nueva para ensayar reparación y staging. No es posible cumplir una migración completa de ventas usando exclusivamente el archivo autorizado actual.
