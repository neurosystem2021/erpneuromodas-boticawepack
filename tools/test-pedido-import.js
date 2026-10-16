const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const compiler = require('vue-template-compiler');
const Excel = require('exceljs');
const orderUtils = require('./load-source-module')('src/utils/orderExcel.js');
let script = compiler.parseComponent(fs.readFileSync('src/components/PedidoExcelImport.vue', 'utf8')).script.content;
script = script.replace("import config from '@/store/config';", "const config = { API: '/api/' };")
  .replace("require('@/utils/orderExcel')", 'orderUtils')
  .replace("import loadExcel from '@/utils/excelLoader';", 'const loadExcel = async () => ExcelLibrary;')
  .replace('export default', 'module.exports =');
const sandbox = { module: {}, orderUtils, ExcelLibrary: Excel, FileReader: class { readAsArrayBuffer(buffer) { this.result = buffer; this.onload(); } } };
vm.runInNewContext(script, sandbox);
const definition = sandbox.module.exports;
(async () => {
  const product = { IdProducto: 9, CodigoBarra: '0009', Producto: 'Test', ControlaStock: 1, stock: 4, PrecioContado: 20 };
  const buffer = await orderUtils.createOrder(Excel, [{ ...product, suggested: 6, PrecioCosto: 12 }], { warehouseId: 2 });
  const commits = [];
  const context = Object.assign(definition.data(), { movement: { movimientoTipo: { Tipo: 0 }, almacenDestino: { IdAlmacen: 2 }, Moneda: 'PEN' }, igvflag: 1,
    axios: { get: async () => ({ data: [product] }) }, $store: { state: { cart: { productos: [] } }, commit: (name, value) => commits.push({ name, value }) }, $toasted: { success() {} } });
  Object.entries(definition.methods).forEach(([key, method]) => { context[key] = method.bind(context); });
  Object.entries(definition.computed).forEach(([key, getter]) => Object.defineProperty(context, key, { get: () => getter.call(context) }));
  const event = () => ({ target: { files: [Object.assign(buffer, { name: 'pedido.xlsx', size: buffer.length })], value: 'pedido.xlsx' } });
  await context.readFile(event());
  assert.strictEqual(context.error, '');
  assert.strictEqual(context.preview.length, 1);
  assert.strictEqual(commits.length, 0); // Preview must not mutate the purchase.
  context.apply();
  assert.strictEqual(commits.length, 1);
  assert.strictEqual(commits[0].name, 'AGREGAR_PRODUCTO');
  assert.strictEqual(commits[0].value.cantidad, 6);
  assert.strictEqual(commits[0].value.precio, 12);
  assert.strictEqual(commits[0].value.TieneIgv, 1);
  context.movement.Moneda = 'USD';
  await context.readFile(event());
  assert.match(context.error, /PEN/); assert.strictEqual(context.preview.length, 0);
  context.movement.Moneda = 'PEN'; context.movement.almacenDestino.IdAlmacen = 3;
  await context.readFile(event());
  assert.match(context.error, /almacén/);
  context.movement.almacenDestino.IdAlmacen = 2;
  context.$store.state.cart.productos = [product];
  await context.readFile(event());
  assert.strictEqual(context.error, ''); assert.strictEqual(context.preview.length, 0); assert.strictEqual(commits.length, 1);
  context.$store.state.cart.productos = [];
  context.axios.get = async () => ({ data: [{ ...product, CodigoBarra: 'changed' }] });
  await context.readFile(event());
  assert.match(context.error, /no coincide/);
  context.destination = 'order';
  context.movement = { almacen: { IdAlmacen: 2 }, proveedor: { IdProveedor: 10 } };
  context.order = { warehouseId: 2, currency: 'PEN', rows: [
    { id: 9, code: '0009', quantity: 6, price: 12, supplierId: 10, Proveedor: 'Alfa' },
    { id: 20, code: '0020', quantity: 3, price: 9, supplierId: 20, Proveedor: 'Beta' }
  ] };
  context.supplierId = 10;
  context.axios.get = async () => ({ data: [product] });
  await context.preparePreview();
  assert.strictEqual(context.error, ''); assert.strictEqual(context.preview.length, 1);
  assert.strictEqual(context.preview[0].IdProducto, 9);
  assert.strictEqual(context.supplierOptions.length, 3);
  context.axios.get = async () => ({ data: [{ ...product, OrdenesPendientes: 1 }] });
  await context.preparePreview();
  assert.strictEqual(context.error, ''); assert.strictEqual(context.preview.length, 0);
  assert.match(context.supplierNote, /1 con órdenes pendientes/);
  context.axios.get = async () => ({ data: [product] });
  await context.preparePreview();
  context.$store.state.cart.productos = [product];
  context.apply();
  assert.strictEqual(commits.length, 1); // A duplicate added after preview must also be skipped.
  context.supplierId = 20;
  await context.preparePreview();
  assert.match(context.error, /antes de cambiar de proveedor/);
  console.log('Purchase import preview, commit, currency, warehouse, duplicate, and catalog validation passed.');
  process.exit(0);
})().catch(error => { console.error(error); process.exit(1); });
