from pathlib import Path
import json

path = Path(__file__).resolve().parents[1] / 'static/data/proforma-module-guide.json'
guide = json.loads(path.read_text(encoding='utf-8'))
details = {
 'sales': (['Buscar productos por nombre o código de barras.', 'Registrar cantidades, precios y descuentos de la operación.', 'Identificar al cliente y registrar la forma de pago.'], 'Atender una compra desde la búsqueda del producto hasta su incorporación al comprobante.'),
 'catalog': (['Identificar cada producto con su código, categoría, marca y modelo.', 'Mantener precios y presentaciones del catálogo.', 'Registrar descripciones e imágenes para consultar el producto.'], 'Preparar una ficha comercial consistente para que el vendedor encuentre el artículo correcto.'),
 'inventory': (['Consultar la cantidad disponible de cada producto.', 'Revisar existencias según el almacén seleccionado.', 'Relacionar el código y nombre del artículo con su saldo de stock.'], 'Consultar la disponibilidad antes de comprometer una entrega al cliente.'),
 'purchases': (['Identificar al proveedor y el documento de compra.', 'Detallar los productos, cantidades e importes adquiridos.', 'Consultar las compras registradas en la operación.'], 'Registrar el abastecimiento recibido y conservar su referencia de compra.'),
 'cash': (['Registrar ingresos y egresos de la operación.', 'Identificar la cuenta o caja del movimiento.', 'Consultar los movimientos financieros registrados.'], 'Revisar cómo se registró un ingreso o un pago y a qué cuenta corresponde.'),
 'reports': (['Consultar el registro de ventas realizadas.', 'Revisar los documentos, clientes e importes de las operaciones.', 'Analizar la información comercial del período consultado.'], 'Revisar las ventas de la jornada o del período para conocer el resultado comercial.'),
 'clients': (['Registrar el nombre y documento de identificación.', 'Consultar la información comercial disponible del cliente.', 'Seleccionar al cliente al preparar la operación de venta.'], 'Recuperar los datos de un cliente que vuelve a comprar, sin capturarlos nuevamente.'),
 'security': (['Registrar las cuentas de usuario del sistema.', 'Configurar el perfil y los accesos de cada persona.', 'Restringir el uso de módulos según los permisos asignados.'], 'Dar acceso al vendedor a sus funciones y reservar otras operaciones para usuarios autorizados.'),
 'transfers': (['Identificar el almacén de origen y el de destino.', 'Detallar los productos y cantidades del traslado.', 'Conservar el registro del movimiento entre almacenes.'], 'Documentar el envío de mercadería de un almacén a otro.'),
 'kardex': (['Revisar las entradas y salidas de un producto.', 'Consultar el saldo y la secuencia de movimientos.', 'Relacionar las operaciones con sus fechas y referencias.'], 'Investigar qué movimientos explican el saldo de un artículo.'),
 'purchase_orders': (['Identificar al proveedor de la orden.', 'Detallar los artículos, cantidades y precios solicitados.', 'Consultar las órdenes preparadas para la compra.'], 'Formalizar qué mercadería se solicita al proveedor antes de registrar la compra.'),
 'movements': (['Consultar las operaciones registradas de almacén.', 'Identificar los productos y cantidades de los movimientos.', 'Revisar las referencias disponibles de cada operación.'], 'Localizar el registro de una entrada o salida para revisar lo ocurrido.'),
 'preorders': (['Identificar al cliente de la solicitud.', 'Preparar el detalle de productos, cantidades y precios.', 'Mantener la operación preparada antes de su facturación.'], 'Organizar el pedido del cliente mientras se define la venta final.'),
 'profit': (['Consultar los importes de venta registrados.', 'Comparar las ventas con los costos registrados.', 'Revisar la utilidad bruta obtenida en la consulta.'], 'Identificar qué operaciones aportan utilidad según los costos que registra el negocio.'),
 'kpi': (['Revisar indicadores de ventas y rentabilidad.', 'Consultar información de productos e inventario.', 'Observar resultados por clientes, vendedores y sucursales cuando existan datos.'], 'Revisar el panorama del negocio antes de profundizar en un reporte específico.'),
 'closing': (['Identificar la operación de cierre de caja.', 'Revisar los importes registrados durante la jornada.', 'Consultar el reporte del cierre realizado.'], 'Dejar una referencia del cierre para la revisión del responsable de caja.'),
 'invoicing': (['Preparar los comprobantes de las operaciones incluidas.', 'Consultar los documentos electrónicos registrados.', 'Dar seguimiento a la información del comprobante dentro del sistema.'], 'Localizar un comprobante para revisar sus datos y su estado registrado.'),
 'accounting': (['Organizar las cuentas del plan contable.', 'Registrar y consultar los asientos contables.', 'Revisar los libros y consultas incluidos en el alcance.'], 'Consultar la información contable organizada para su revisión por el responsable.'),
 'payroll': (['Mantener la información del personal.', 'Registrar las operaciones de planilla configuradas.', 'Consultar la información disponible para su revisión.'], 'Reunir los datos de personal y planilla para facilitar el trabajo administrativo.'),
 'crm': (['Organizar los contactos comerciales.', 'Registrar información para el seguimiento de oportunidades.', 'Consultar el seguimiento comercial disponible.'], 'Retomar una conversación comercial con información organizada del contacto.'),
 'store': (['Administrar los productos presentados en la tienda virtual.', 'Mantener su información comercial disponible.', 'Consultar la información de la operación de la tienda.'], 'Mostrar al cliente los productos que el negocio decide publicar en su catálogo virtual.'),
 'projects': (['Registrar la información del proyecto.', 'Consultar los proyectos de la operación.', 'Mantener sus referencias para el seguimiento.'], 'Recuperar la información de un proyecto sin dispersarla en registros separados.'),
 'hotel': (['Consultar la información de habitaciones.', 'Organizar los registros de la operación del hospedaje.', 'Revisar la información disponible por habitación.'], 'Centralizar la consulta de habitaciones para apoyar la atención del establecimiento.'),
 'support': (['Registrar la solicitud de atención en un ticket.', 'Consultar la información de la solicitud.', 'Mantener la referencia del ticket para su seguimiento.'], 'Dar seguimiento a una solicitud con su identificación dentro del sistema.')
}
parents = {'transfers':'Inventario y stock', 'kardex':'Inventario y stock', 'movements':'Inventario y stock', 'purchase_orders':'Gestión de compras', 'preorders':'Punto de venta', 'profit':'Reportes de ventas', 'kpi':'Reportes de ventas', 'closing':'Caja y bancos'}
for module in guide['modules']:
    features, example = details[module['key']]
    module['features'] = features
    module['example'] = example
    if module['key'] in parents:
        module['parent'] = parents[module['key']]
path.write_text(json.dumps(guide, ensure_ascii=False, indent=2)+'\n', encoding='utf-8')
print('Module descriptions enriched:', len(guide['modules']))
