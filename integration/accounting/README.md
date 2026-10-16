# Contabilidad integral NEUROSOFT

Implementación integrada en Vue 2/PHP del ERP. **Paquete preparado para revisión; no instalado en producción.** No se modificaron ventas, compras, stock, clientes, caja ni documentos SUNAT históricos.

- [Diagnóstico y arquitectura](docs/DIAGNOSTICO.md)
- [Manual de usuario](docs/MANUAL.md)
- [Instalación, migración histórica y operación](docs/INSTALACION.md)
- [Modelo de datos y API](docs/TECNICO.md)

## Componentes

| Área | Implementación |
|---|---|
| Empresas / sucursales | Contabilidades independientes; asignación explícita de almacén y cuenta ERP; permisos por empresa |
| PCGE | Catálogo oficial 2019 de 1.754 cuentas del capítulo II, jerarquía, naturaleza, clasificación, activación, importación CSV y extensiones propias |
| Períodos / ejercicios | Apertura de períodos mensuales por empresa, cierre, reapertura motivada, correlativos bajo bloqueo, borrador de cierre anual |
| Asientos | Partida doble, moneda original y PEN, céntimos enteros, revisión/aprobación, contabilización, reversos y auditoría |
| Integración ERP | Captura repetible de ventas, NC, compras, devoluciones y movimientos de caja/bancos asignados; revisión antes de generar; conflictos si cambia el ERP |
| Operaciones complementarias | Cobros/pagos, gastos, personal, inventarios valorizados, ajustes manuales, depreciación lineal, retenciones, percepciones y detracciones mediante cuentas configuradas |
| Libros | Diario, mayor con saldo acumulado, comprobación, registros de compras/ventas, caja y bancos, auxiliares de terceros |
| Estados | Situación financiera, resultados, flujos directos, cambios en patrimonio y comparativos mensuales/anuales, exclusivamente sobre asientos contabilizados |
| Tributos | IGV conciliado con el asiento, operaciones gravadas/exoneradas/inafectas/gratuitas, crédito fiscal revisado, NC y anulación sustentada |
| Conciliación bancaria | CSV, identificación estable, saldos de extracto, coincidencias parciales/agrupadas, diferencias y deshacer auditable |
| Exportaciones | CSV seguro para Excel, XLSX con importes numéricos, PDF; PLE Diario 5.1 con metadatos y período cerrado |
| SIRE / PLE | Conciliación con propuesta CSV normalizada y acceso a reportes SIRE existentes; no hay envío automático a SUNAT |
| Seguridad | Administrador, contador, auxiliar, auditor y gerente; sesión ERP, CSRF, control de versiones y ámbito empresarial en servidor |

## Alcance tributario y criterios explícitos

Este software no certifica el cumplimiento de una empresa ni presenta declaraciones. Régimen, obligación, rubros, crédito fiscal, vida útil y cuentas de control requieren configuración/revisión del contador. El catálogo oficial contiene códigos y denominaciones; la clasificación inicial de reporte es configurable y debe revisarse para la actividad particular. Los elementos de costos analíticos y cuentas de orden propios de cada empresa se incorporan mediante importación.

La captura programada registra automáticamente operaciones pendientes; la generación de borradores requiere revisión documental y nunca contabiliza un histórico sin aprobación. En el ERP antiguo no hay fotografía fiscal completa por línea, moneda de caja ni empresa en cada comprobante. Por eso no se reconstruyen impuestos/costos históricos con datos actuales. La caja ERP se vincula únicamente en PEN; las cuentas USD/EUR se trabajan con extractos y asientos multimoneda revisados.

El exportador PLE implementa movimientos del período y apertura inicial con 21 campos de la estructura del Diario 5.1. Requiere referencias reales y revisión de configuración. Correcciones extemporáneas 8/9, cierre electrónico SIRE, propuestas SUNAT originales sin normalizar, prorrata de crédito, renta mensual/anual y envío SOL **no se inventan ni se simulan**. Los reportes SIRE operativos existentes se conservan; sus archivos deben validarse con el programa oficial antes de presentación. La exportación contable no sustituye ese procedimiento.

## Validación automatizada

```powershell
& C:\xampp\php\php.exe integration/accounting/tests/core-test.php
node integration/accounting/tests/utilities-test.cjs
node integration/accounting/tests/browser-test.mjs
npm run build
```

El test PHP crea exclusivamente una base temporal `co_test_<identificador aleatorio>`, la elimina al finalizar y prueba integración con registros sintéticos. Incluye peticiones HTTP con sesión/CSRF y cuatro procesos simultáneos. No altera la base ERP real. Los XLSX/PDF de prueba contienen datos sintéticos y no deben confundirse con estados reales.

## Fuentes

- [MEF — PCGE Modificado 2019](https://mef.gob.pe/contenidos/conta_publ/documentac/PCGE_2019.pdf), catálogo páginas 20–61.
- [SUNAT — estructuras SIRE](https://cpe.sunat.gob.pe/estructura-de-archivos).
- [SUNAT — PLE, RS 169-2015](https://www.sunat.gob.pe/orientacion/amazon/ple/RS-169-2015-SUNAT-PLEver5.pdf), Diario 5.1.
- [SUNAT — IGV](https://emprender.sunat.gob.pe/principales-impuestos/impuesto-general-las-ventas-igv/impuesto-general-las-ventas).
- [SUNAT — detracciones, percepciones y retenciones](https://emprender.sunat.gob.pe/principales-impuestos/impuesto-general-las-ventas-igv/sistema-detracciones-percepciones-retenciones).
