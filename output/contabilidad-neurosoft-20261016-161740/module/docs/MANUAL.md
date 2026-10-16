# Manual de contabilidad NEUROSOFT

## Primera configuración

1. Superadmin crea la empresa con RUC válido, régimen confirmado y obligación de registros. El módulo no selecciona un régimen automáticamente.
2. Importar **PCGE 2019 · 1754 cuentas**, o el catálogo propio mediante CSV. El catálogo reducido es una alternativa de inicio, no reemplaza el catálogo oficial.
3. Revisar naturaleza, rubros y cuentas agrupadoras. Una cuenta con hijos no recibe movimientos. No se puede reclasificar una cuenta utilizada en asientos contabilizados; debe crearse otra cuenta.
4. Asignar los usuarios ERP existentes a sus roles contables. El permiso administrativo antiguo no concede automáticamente acceso a todas las contabilidades; superadmin administra el alta y los roles por empresa.
5. Registrar sucursales y el ID de almacén ERP que corresponde a cada una. Un almacén no se asigna a dos empresas.
6. Configurar cuentas predeterminadas. Seleccionar **cuentas de movimiento**, no cuentas agrupadoras. Si se usa el catálogo oficial, las subcuentas deben elegirse según la empresa; no copiar automáticamente los códigos del catálogo reducido.
7. Registrar bancos/caja contable. Una cuenta contable distinta por banco/moneda. El ID de cuenta ERP es opcional; sirve para capturar caja y solo admite PEN porque el origen no guarda moneda por movimiento.
8. Abrir los períodos necesarios. Mantener activada la revisión por otro usuario, salvo decisión explícita del administrador registrada en auditoría.

## Asiento manual

En **Asientos → Nuevo asiento**, completar fecha, sucursal, moneda, tipo de cambio, documento, glosa y líneas. Debe/haber se ingresan en moneda original, con punto decimal y hasta dos decimales. La moneda funcional es PEN. El tipo de cambio admite hasta seis decimales.

Cada línea tiene un solo importe positivo. El motor comprueba que ambas monedas estén cuadradas; si el redondeo de conversión genera una diferencia, se registra una línea explícita de redondeo/diferencia cambiaria, no se corrige ocultamente.

El identificador de operación evita que un reintento cree otra copia. Un borrador manual puede editarse; los datos del documento, fecha y moneda se conservan al editar sus líneas. Un asiento contabilizado nunca se edita.

La apertura inicial solo se usa cuando todavía no existen saldos anteriores. No se vuelve a cargar un asiento de apertura cada año si los saldos ya están en el mayor.

## Revisión, aprobación y contabilización

Flujo: **Borrador → Revisado → Aprobado → Contabilizado**. Con revisión por otro usuario activada, el creador no puede revisar/aprobar su propio asiento. El correlativo se asigna al contabilizar y es independiente por empresa/período.

Los borradores no intervienen en libros oficiales ni estados financieros. **Descartar** deja el borrador y su auditoría; no borra documentos ERP. Si ese borrador procede del ERP, resolver su situación desde Operaciones ERP antes de cerrar el período.

Para corregir un asiento contabilizado, usar **Crear reverso** con fecha de período abierto y motivo. Revisar/aprobar/contabilizar el reverso y registrar el asiento correcto. El original sigue visible. No se permite más de un reverso directo del mismo asiento.

## Compras, ventas y caja del ERP

En **Operaciones ERP**, elegir fechas y **Leer ventas, compras y caja**. Solo se leen los almacenes y cuentas vinculados a la empresa seleccionada.

**Revisar origen** muestra cliente/proveedor y productos existentes. Completar bases, afectaciones, IGV, crédito fiscal, período tributario, referencias y clasificación. El total debe coincidir con el ERP. El origen se vuelve a leer antes de generar, para detectar cambios posteriores a la captura.

- Venta: cliente por cobrar contra ingreso por mercadería/servicio e IGV. Mercadería: costo histórico contra inventario. Un costo histórico nulo bloquea; cero documentado es un costo válido.
- Compra de mercadería: cuentas de compra 60 y destino inventario/variación 20/61, además de proveedor e IGV según derecho a crédito. Comprar stock no genera doble gasto al pagarlo.
- Compra de gasto: gasto y crédito fiscal aplicable contra proveedor.
- Nota de crédito: signo inverso y comprobante referenciado. Marcar devolución de inventario solo cuando efectivamente se devuelve mercadería; una reducción de precio no supone devolución física.
- Caja: elegir cobro, pago, gasto, ingreso, devolución o personal. Cobros/pagos comerciales requieren identificar el tercero. Si falta en el ERP, indicar un ID existente revisado. No se usa el concepto libre para adivinar el gasto.
- **Yape/Plin informativo de tipo neutral no genera egreso ni ingreso contable**. El cobro real se registra una sola vez, en su operación correspondiente.
- Retenciones/detracciones se registran como operaciones separadas de liquidación contra cuentas de control configuradas. La percepción documentada puede incrementar la cuenta por cobrar/pagar y la cuenta de percepción; no se suma dos veces a ventas/compras.
- Entregas gratuitas: el valor referencial y el IGV gratuito se registran aparte. No se crea una venta ni deuda ficticia por el valor gratuito; costo y tributo asumido necesitan cuentas de gasto configuradas.

Si cambia el ERP después de generar, aparece **conflicto**. No se sobrescribe el asiento. Descartar el borrador o contabilizar el reverso según corresponda; luego **Resolver conflicto** con motivo. Se conserva la versión anterior y se permite una nueva generación revisada.

## Libros y estados

Consultar por empresa, fechas, sucursal y cuenta. Diario, mayor, balance de comprobación y estados se calculan exclusivamente con asientos contabilizados.

La situación financiera muestra activos, pasivos, patrimonio registrado y resultado aún no cerrado. Su diferencia debe ser cero. Los resultados mantienen la utilidad del ejercicio después del asiento de cierre, sin sumar dos veces la transferencia a resultados acumulados. Los comparativos anuales suman los meses incluidos en el rango; no completan meses faltantes con estimaciones.

Los flujos son de método directo. Clasificar las líneas de caja/banco como operación, inversión, financiamiento o diferencia de cambio. Las partidas sin clasificación se presentan separadamente, no se distribuyen automáticamente.

Los importes de libros/estados se exportan en PEN. El asiento conserva moneda, tipo de cambio e importes originales. Excel entrega importes numéricos y códigos de cuenta como texto; CSV neutraliza fórmulas; PDF presenta el reporte consultado.

## Tributos

El registro fiscal conserva el documento original y los importes convertidos a PEN. El IGV debe coincidir con la cuenta de IGV del asiento. Se distinguen gravadas, exoneradas, inafectas y gratuitas. Marcar crédito fiscal solo después de verificar el derecho.

La determinación mostrada es **débito menos crédito fiscal revisado, antes de arrastres, prorrata, compensaciones y renta**. No es un PDT/621 generado ni presentado.

Una anulación fiscal requiere reverso contabilizado, período tributario abierto y sustento identificado. Se conserva el registro original y su auditoría; el documento anulado se presenta con importes cero. Una NC es un documento nuevo con signo negativo, no se sustituye por esta acción.

Para conciliar SIRE/PLE, normalizar la propuesta a CSV con encabezados `documentKey,total,igv`. La clave es `documentoTercero|tipoComprobante|serie|numero`. Elegir compras o ventas y período. Importes en PEN; NC negativas; IGV de entregas gratuitas se incluye en el IGV normalizado de conciliación. Se señalan coincidencias, diferencias y documentos presentes solo en uno de los registros.

**PLE Diario 5.1:** período cerrado, obligación PLE y configuración tributaria revisada. Completar metadatos electrónicos reales en los borradores: identidad del emisor, comprobante, serie/número y referencia del registro `140100&AAAAMM00&CUO&Mcorrelativo`, o equivalente de compras. Las aperturas usan A y movimientos M. Los archivos requieren validación oficial; este módulo no implementa rectificaciones extemporáneas 8/9 ni genera su aprobación SUNAT.

## Conciliación bancaria

1. Seleccionar cuenta y rango.
2. Importar CSV con `id,date,memo,amount`; fecha AAAA-MM-DD, abonos positivos y cargos negativos, moneda de la cuenta. Usar un ID estable proporcionado por el banco. Reimportar el mismo dato no lo duplica; el mismo ID con distinto contenido se rechaza.
3. Ingresar saldos inicial/final y referencia del extracto. Sin extracto, el módulo muestra diferencia de movimientos y no presume saldo inicial cero.
4. Asociar las líneas contables con los movimientos. Se admite varios cobros en un abono y pagos parciales repartidos. Empresa, cuenta, moneda, signo y saldos pendientes deben coincidir.
5. Revisar diferencias de saldo y movimientos. Comisiones/intereses faltantes se registran como asientos, no ajustando el extracto artificialmente.
6. Deshacer requiere motivo y deja auditoría.

## Activos y cierres

Registrar costo y residual en PEN, vida útil mensual y cuentas de depreciación/acumulada. La depreciación lineal distribuye los céntimos restantes entre los primeros meses, de forma que el total no exceda costo menos residual. Repetir el mismo mes no duplica el asiento. La adquisición del activo se registra por separado.

Antes del cierre mensual: sincronizar todo el período, resolver pendientes/conflictos y borradores, revisar tributos, conciliar bancos y comprobar balance. Cerrar bloquea nuevos asientos y cambios. Reapertura exige motivo.

En diciembre, **Preparar cierre anual** genera un borrador que cancela ingresos/gastos contra resultados acumulados. Revisar, aprobar, contabilizar y cerrar períodos. No reemplaza los ajustes tributarios del ejercicio ni inventa un impuesto a la renta.

## CSV de cuentas

Encabezados: `code,name,parent,nature,class,group,postable,active`. `nature`: debit/credit. `class`: asset/liability/equity/income/expense/memo. `postable` y `active`: 1/0. Los padres deben existir en el archivo o catálogo, y ser agrupadores. Toda la importación se valida en una transacción.
