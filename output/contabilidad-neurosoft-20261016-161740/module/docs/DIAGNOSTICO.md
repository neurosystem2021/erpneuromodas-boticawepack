# Contabilidad integral NEUROSOFT

## Diagnóstico previo — 16 de octubre de 2026

Revisión de solo lectura: esquema local de 119 tablas y una vista, configuración de conexión, API Slim, Vue 2 y reportes gerenciales/SIRE. No se modificaron operaciones históricas.

El backend utiliza PHP, PDO y Slim; el frontend Vue 2 con BootstrapVue y axios. Hay tablas InnoDB y MyISAM. Los nuevos datos contables deben usar exclusivamente InnoDB y transacciones propias; no puede suponerse que una transacción contable revierta una venta histórica MyISAM.

| Origen | Identidad | Uso contable | Limitaciones a controlar |
|---|---|---|---|
| Ve_DocVenta / Ve_DocVentaDet / Ve_DocVentaTipoDoc | idDocVenta | Ventas, notas de crédito, ingreso y costo | Moneda/tasa/afectación histórica no están completas por línea; no reconstruir impuestos suponiendo configuración actual |
| Lo_Movimiento / Lo_MovimientoDetalle / Lo_MovimientoTipo | Hash | Compras y devoluciones | Tipo/fecha tributaria/moneda/TieneIgv; movimientos de almacén no son siempre compras |
| Cb_CajaBanco / Cb_CajaBancoDet / Cb_TipoCajaBanco | IdCajaBanco | Cobros, pagos, caja y bancos | Tipo 2 es neutral: Yape/Plin informativo no es egreso; concepto libre no acredita clasificación contable |
| Cb_Cuenta | IdCuenta | Cuentas bancarias y conciliación | Cuenta operativa y cuenta PCGE son entidades distintas |
| Gen_Producto | IdProducto | Clasificación mercadería/servicio | Costo actual no sustituye costo histórico; OpcContable 1/2 |
| Ve_DocVentaCliente / Lo_Proveedor | IdCliente / IdProveedor | Auxiliares de terceros | Documento de identidad y RUC requieren validación según tipo |
| GEN_EMPRESA / Gen_EmpresaConfig | IDEMPRESA | Identidad y configuración | No confundir datos públicos con tokens privados de SUNAT |
| Rhu_PlanillaPago y tablas de planilla | Identidades existentes | Personal | Pago de personal no equivale automáticamente a devengo de remuneración |

No se encontró un libro mayor central que reúna estas fuentes mediante partida doble. Los reportes gerenciales actuales calculan ventas y gastos operativos; no son estados financieros basados en asientos. Se conservarán como reportes operativos.

## Arquitectura propuesta

Módulo integrado en la API y navegación Vue existentes. Servicio contable central en PHP, migraciones versionadas Co_*, adaptador de lectura de operaciones del ERP y proceso de sincronización incremental que genera borradores revisables. No se activa contabilización histórica automática ni se cambia el sistema de facturación.

Todas las claves contables incluyen empresa. Sucursal, banco, cuenta, período, documento y rol se validan contra esa empresa en el servidor. La asignación origen-almacén/sucursal debe configurarse antes de sincronizar: las tablas históricas no permiten inferirla de forma universal.

El motor trabaja con céntimos enteros, separa importes originales de moneda funcional y valida tipo de cambio. Flujo: borrador → revisado → aprobado → contabilizado. Los asientos contabilizados son inmutables; se corrigen mediante reversos enlazados. Los correlativos se asignan bajo bloqueo de período. Una clave única de operación y su hash impiden duplicados o modificaciones silenciosas del origen.

## Fases y condiciones

1. Empresa, permisos, catálogo, configuración, períodos y asientos.
2. Integración de ventas/compras, cobros/pagos y controles de origen.
3. Libros y estados financieros sobre asientos contabilizados.
4. Tributos configurables, conciliación bancaria y auxiliares.
5. Pruebas, exportaciones, manuales y paquete de instalación revisable.

La importación histórica requiere vista previa, aprobación explícita, respaldo verificado y conciliación por período. Los datos incompletos generan pendientes, nunca importes inventados. No se despliega a producción durante el desarrollo.

## Referencias normativas consultadas

- PCGE Modificado 2019: https://mef.gob.pe/contenidos/conta_publ/pcge/PCGE_2019.pdf
- Estructuras SIRE: https://cpe.sunat.gob.pe/estructura-de-archivos
- IGV: https://emprender.sunat.gob.pe/principales-impuestos/impuesto-general-las-ventas-igv/impuesto-general-las-ventas
- Retenciones/percepciones/detracciones: https://emprender.sunat.gob.pe/principales-impuestos/impuesto-general-las-ventas-igv/sistema-detracciones-percepciones-retenciones

Régimen, afectaciones, derecho al crédito fiscal y obligación SIRE/PLE son configuraciones por empresa. Un reporte de apoyo no constituye declaración ni envío a SUNAT.
