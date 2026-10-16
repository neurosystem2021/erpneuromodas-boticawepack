const assert = require('assert');
const Excel = require('exceljs');
const { createOrder, readOrder, supplierRows } = require('./load-source-module')('src/utils/orderExcel.js');
(async () => {
  const buffer = await createOrder(Excel, [{ IdProducto: 7, CodigoBarra: '000123', Producto: 'Producto prueba', ProductoMarca: 'Marca', suggested: 8, current: 2, minimum: 5, target: 10, PrecioCosto: 12.5 }], { warehouseId: 1, warehouse: 'Principal', from: '2026-09-11', to: '2026-10-10' });
  assert.strictEqual(Buffer.from(buffer).slice(0, 2).toString(), 'PK');
  const order = await readOrder(Excel, buffer);
  assert.deepStrictEqual(order, { warehouseId: 1, currency: 'PEN', rows: [{ id: 7, code: '000123', quantity: 8, price: 12.5, Proveedor: 'Sin proveedor registrado', RucProveedor: '' }] });
  const workbook = new Excel.Workbook(); await workbook.xlsx.load(buffer);
  const sheet = workbook.getWorksheet('Pedido');
  assert.strictEqual(workbook.worksheets[0].name, 'Pedido');
  assert.strictEqual(workbook.getWorksheet('Datos').state, 'veryHidden');
  assert.strictEqual(sheet.getColumn(9).hidden, true);
  sheet.getCell('D2').value = 4.5;
  assert.strictEqual((await readOrder(Excel, await workbook.xlsx.writeBuffer())).rows[0].quantity, 4.5);
  sheet.getCell('D2').value = -1;
  await assert.rejects(readOrder(Excel, await workbook.xlsx.writeBuffer()), /Fila 2/);
  sheet.getCell('D2').value = { formula: '1+1', result: 2 };
  await assert.rejects(readOrder(Excel, await workbook.xlsx.writeBuffer()), /fórmulas/);
  sheet.getCell('D2').value = 1;
  sheet.addRow(sheet.getRow(2).values);
  await assert.rejects(readOrder(Excel, await workbook.xlsx.writeBuffer()), /duplicado/);
  sheet.getCell('D3').value = 0;
  assert.strictEqual((await readOrder(Excel, await workbook.xlsx.writeBuffer())).rows.length, 1);
  workbook.getWorksheet('Datos').getCell('B2').value = 0;
  await assert.rejects(readOrder(Excel, await workbook.xlsx.writeBuffer()), /almacén/);
  const base = { CodigoBarra: '001', Producto: 'Producto', suggested: 3, PrecioCosto: 2 };
  const grouped = await createOrder(Excel, [
    { ...base, IdProducto: 1 },
    { ...base, IdProducto: 2, ProveedorReposicion: 'Zeta', IdProveedorReposicion: 20 },
    { ...base, IdProducto: 3, ProveedorReposicion: 'Alfa', IdProveedorReposicion: 10 }
  ], { warehouseId: 1 });
  const groupedOrder = await readOrder(Excel, grouped);
  assert.strictEqual(groupedOrder.rows[0].Proveedor, 'Alfa');
  const pendingBuffer = await createOrder(Excel, [], { warehouseId: 1, pendingRows: [{ IdProducto: 1, CodigoBarra: '001', Producto: 'Ya pedido', ProveedorReposicion: 'Alfa', RucProveedorReposicion: '00123', OrdenesPendientes: 2, suggested: 7 }] });
  const pendingBook = new Excel.Workbook(); await pendingBook.xlsx.load(pendingBuffer);
  assert.strictEqual(pendingBook.getWorksheet('Pedido').rowCount, 1);
  assert.strictEqual(pendingBook.getWorksheet('Pendientes').getCell('A2').value, 'Alfa');
  assert.strictEqual(pendingBook.getWorksheet('Pendientes').getCell('B2').value, '00123');
  assert.strictEqual(pendingBook.getWorksheet('Pendientes').getCell('G2').value, 2);
  assert.deepStrictEqual(groupedOrder.rows.map(row => row.id), [3, 2, 1]);
  assert.deepStrictEqual(supplierRows(groupedOrder, 20).map(row => row.id), [2]);
  assert.throws(() => supplierRows(groupedOrder, 0), /Selecciona el proveedor/);
  assert.throws(() => supplierRows(groupedOrder, 99), /no contiene/);
  // Existing ten-column files remain compatible.
  const legacy = new Excel.Workbook(), legacyInfo = legacy.addWorksheet('Datos'), legacySheet = legacy.addWorksheet('Pedido');
  legacyInfo.addRows([['NST_PEDIDO_V1'], ['IdAlmacen', 1], ['Moneda', 'PEN']]);
  legacySheet.addRows([['IdProducto', 'CodigoBarra', 'Producto', 'Marca', 'Unidad', 'CantidadPedido', 'PrecioUnitario', 'StockActual', 'StockMinimo', 'Objetivo'], [7, '0007', 'Anterior', '', '', 2, 3, 0, 1, 2]]);
  assert.strictEqual((await readOrder(Excel, await legacy.xlsx.writeBuffer())).rows[0].code, '0007');
  console.log('Real XLSX round-trip, leading-zero codes, editable quantities, and import validation passed.');
  process.exit(0);
})().catch(error => { console.error(error); process.exit(1); });
