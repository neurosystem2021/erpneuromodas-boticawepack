const columns = ['IdProducto', 'CodigoBarra', 'Producto', 'Marca', 'Unidad', 'CantidadPedido', 'PrecioUnitario', 'StockActual', 'StockMinimo', 'Objetivo'];
function cellValue(cell) {
  const value = cell.value;
  if (value && typeof value === 'object') throw new Error('El pedido no admite fórmulas ni celdas especiales. Usa valores simples.');
  return value;
}
async function createOrder(Excel, rows, snapshot) {
  const workbook = new Excel.Workbook();
  workbook.creator = 'NST';
  workbook.calcProperties.fullCalcOnLoad = true;
  const sheet = workbook.addWorksheet('Pedido');
  workbook.views = [{ activeTab: 0 }];
  const info = workbook.addWorksheet('Datos');
  info.state = 'veryHidden';
  info.addRows([
    ['NST_PEDIDO_V1'], ['IdAlmacen', Number(snapshot.warehouseId)], ['Moneda', 'PEN'],
    ['VentasDesde', snapshot.from], ['VentasHasta', snapshot.to], ['Almacen', snapshot.warehouse],
    ['Instrucciones', 'Edita CantidadPedido y PrecioUnitario en Pedido. Cantidad 0 omite el producto. Precios en soles, sin IGV. Carga este archivo en Registro de compras; revisa antes de guardar.']
  ]);
  info.getColumn(1).width = 24; info.getColumn(2).width = 110;
  const reportColumns = ['Proveedor', 'CodigoBarra', 'Producto', 'CantidadPedido', 'PrecioUnitario', 'Importe', 'StockActual', 'StockMinimo', 'IdProducto', 'Marca', 'Unidad', 'Objetivo', 'RucProveedor', 'IdProveedor'];
  sheet.addRow(reportColumns);
  const ordered = rows.slice().sort((a, b) => {
    const nameA = String(a.ProveedorReposicion || '').trim(), nameB = String(b.ProveedorReposicion || '').trim();
    return Number(!nameA) - Number(!nameB) || nameA.localeCompare(nameB, 'es', { sensitivity: 'base' }) || Number(a.IdProveedorReposicion || 0) - Number(b.IdProveedorReposicion || 0) || String(a.Producto || '').localeCompare(String(b.Producto || ''), 'es');
  });
  if (snapshot.pendingRows && snapshot.pendingRows.length) {
    const pending = workbook.addWorksheet('Pendientes');
    pending.addRow(['Proveedor', 'RUC proveedor', 'IdProducto', 'CodigoBarra', 'Producto', 'Cantidad sugerida', 'Ordenes pendientes', 'Estado']);
    snapshot.pendingRows.slice().sort((a, b) => String(a.ProveedorReposicion || '').localeCompare(String(b.ProveedorReposicion || ''), 'es')).forEach(row => {
      pending.addRow([row.ProveedorReposicion || 'Sin proveedor registrado', String(row.RucProveedorReposicion || ''), Number(row.IdProducto), String(row.CodigoBarra || ''), row.Producto, row.suggested, Number(row.OrdenesPendientes), 'Ya pedido: excluido del nuevo pedido']);
    });
    [32, 18, 12, 18, 52, 20, 20, 44].forEach((width, index) => { pending.getColumn(index + 1).width = width; });
    pending.views = [{ state: 'frozen', ySplit: 1 }];
    pending.autoFilter = 'A1:H' + pending.rowCount;
    pending.getRow(1).eachCell(cell => { cell.font = { bold: true, color: { argb: 'FFFFFFFF' } }; cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10465A' } }; });
    if (!rows.length) workbook.views = [{ activeTab: 2 }];
  }
  let lastSupplier = null, group = 0;
  ordered.forEach((row, index) => {
    const name = row.ProveedorReposicion || 'Sin proveedor registrado';
    const key = name + '|' + (row.IdProveedorReposicion || 0);
    if (key !== lastSupplier) { group++; lastSupplier = key; }
    const quantity = Number(row.suggested) || 0, price = Number(row.PrecioCosto) || 0;
    const line = sheet.addRow([name, String(row.CodigoBarra || ''), row.Producto, quantity, price,
      { formula: 'D' + (index + 2) + '*E' + (index + 2), result: quantity * price },
      row.current, row.minimum, Number(row.IdProducto), row.ProductoMarca || '', row.ProductoMedicion || '', row.target,
      String(row.RucProveedorReposicion || ''), Number(row.IdProveedorReposicion) || 0]);
    line.font = { name: 'Calibri', size: 10 };
    line.alignment = { vertical: 'middle', wrapText: true };
    line.height = 30;
    line.eachCell(cell => { cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: group % 2 ? 'FFF0F7FA' : 'FFFFFFFF' } }; });
    [4, 5].forEach(column => { line.getCell(column).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFF2CC' } }; });
  });
  sheet.views = [{ state: 'frozen', ySplit: 1, zoomScale: 85 }];
  sheet.autoFilter = { from: 'A1', to: 'N' + (rows.length + 1) };
  [25, 16, 46, 13, 14, 14, 11, 11, 12, 16, 12, 12, 16, 12].forEach((width, index) => { sheet.getColumn(index + 1).width = width; });
  for (let column = 9; column <= 14; column++) sheet.getColumn(column).hidden = true;
  sheet.getRow(1).eachCell(cell => { cell.font = { bold: true, color: { argb: 'FFFFFFFF' } }; cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10465A' } }; });
  sheet.getRow(1).height = 28;
  sheet.getRow(1).alignment = { vertical: 'middle', wrapText: true };
  ['D', 'G', 'H'].forEach(column => { sheet.getColumn(column).numFmt = '0.####'; });
  ['E', 'F'].forEach(column => { sheet.getColumn(column).numFmt = '#,##0.00'; });
  sheet.pageSetup = { orientation: 'landscape', paperSize: 9, fitToPage: true, fitToWidth: 1, fitToHeight: 0, printTitlesRow: '1:1', printArea: 'A1:H' + (rows.length + 1) };
  sheet.headerFooter = { oddHeader: '&CPedido de reposición - ' + String(snapshot.warehouse || '').replace(/&/g, '&&'), oddFooter: '&LPEN - Precios sin IGV&R Página &P de &N' };
  for (let row = 2; row <= rows.length + 1; row++) {
    sheet.getCell('D' + row).dataValidation = { type: 'decimal', operator: 'greaterThanOrEqual', formulae: [0], showErrorMessage: true, error: 'Usa una cantidad mayor o igual a cero.' };
    sheet.getCell('E' + row).dataValidation = { type: 'decimal', operator: 'greaterThanOrEqual', formulae: [0], showErrorMessage: true, error: 'Usa un precio mayor o igual a cero.' };
  }
  return workbook.xlsx.writeBuffer();
}
async function readOrder(Excel, buffer) {
  const workbook = new Excel.Workbook();
  await workbook.xlsx.load(buffer);
  const info = workbook.getWorksheet('Datos');
  const sheet = workbook.getWorksheet('Pedido');
  if (!info || !sheet || cellValue(info.getCell('A1')) !== 'NST_PEDIDO_V1') throw new Error('Selecciona el Excel generado desde Reposición / rotación.');
  if (sheet.rowCount > 5001) throw new Error('El pedido admite hasta 5000 filas.');
  const headers = [];
  sheet.getRow(1).eachCell((cell, column) => { headers[column - 1] = cellValue(cell); });
  const positions = columns.map(name => {
    const index = headers.indexOf(name);
    if (index < 0 || headers.lastIndexOf(name) !== index) throw new Error('No cambies los encabezados del pedido Excel.');
    return index + 1;
  });
  const warehouseId = Number(cellValue(info.getCell('B2')));
  if (!Number.isInteger(warehouseId) || warehouseId <= 0 || cellValue(info.getCell('B3')) !== 'PEN') throw new Error('Los datos de almacén o moneda del pedido no son válidos.');
  const rows = [], ids = new Set();
  for (let index = 2; index <= sheet.rowCount; index++) {
    const values = positions.map(column => cellValue(sheet.getRow(index).getCell(column)));
    if (values.every(value => value === null || value === '')) continue;
    const id = Number(values[0]), quantity = Number(values[5]), price = Number(values[6]);
    if (!Number.isInteger(id) || id <= 0 || values[5] === null || values[5] === '' || !Number.isFinite(quantity) || quantity < 0 || !Number.isFinite(price) || price < 0) throw new Error('Fila ' + index + ': revisa ID, cantidad y precio (valores numéricos sin negativos).');
    if (!quantity) continue;
    if (ids.has(id)) throw new Error('Fila ' + index + ': producto duplicado en el pedido.');
    ids.add(id);
    const item = { id, code: String(values[1] == null ? '' : values[1]), quantity, price };
    ['Proveedor', 'RucProveedor'].forEach(name => {
      const column = headers.indexOf(name);
      if (column >= 0) item[name] = String(cellValue(sheet.getRow(index).getCell(column + 1)) || '');
    });
    const supplierColumn = headers.indexOf('IdProveedor');
    if (supplierColumn >= 0) {
      const supplierId = Number(cellValue(sheet.getRow(index).getCell(supplierColumn + 1)));
      if (!Number.isInteger(supplierId) || supplierId < 0) throw new Error('Fila ' + index + ': proveedor inválido.');
      if (supplierId > 0) item.supplierId = supplierId;
    }
    rows.push(item);
  }
  if (!rows.length) throw new Error('El pedido no contiene cantidades mayores que cero.');
  return { warehouseId, currency: 'PEN', rows };
}
function supplierRows(order, selectedId) {
  if (!order.rows.some(row => row.supplierId)) return order.rows;
  if (!Number(selectedId)) throw new Error('Selecciona el proveedor en Registro de compras antes de cargar este pedido.');
  const rows = order.rows.filter(row => Number(row.supplierId) === Number(selectedId));
  if (!rows.length) throw new Error('El pedido no contiene productos para el proveedor seleccionado. Revisa el proveedor de la compra.');
  return rows;
}
export { createOrder, readOrder, supplierRows };
