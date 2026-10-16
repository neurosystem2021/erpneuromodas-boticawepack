// Execute the deployed webpack modules, including Babel runtime dependencies.
// Template compilation alone does not catch module initialization failures.
const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const modules = {}, cache = {};
const context = { webpackJsonp: (chunks, definitions) => Object.assign(modules, definitions), console, setTimeout, clearTimeout, process };
vm.createContext(context);
vm.runInContext(fs.readFileSync('dist/static/vendor/exceljs-4.4.0.min.js', 'utf8'), context);
const Excel = context.ExcelJS;
for (const file of fs.readdirSync('dist/static/js').filter(file => /^(app|vendor).*\.js$/.test(file))) {
  vm.runInContext(fs.readFileSync('dist/static/js/' + file, 'utf8'), context);
}
function requireBuilt(id) {
  if (cache[id]) return cache[id].exports;
  const module = cache[id] = { exports: {}, i: id, l: false };
  modules[id].call(module.exports, module, module.exports, requireBuilt);
  module.l = true;
  return module.exports;
}
requireBuilt.i = value => value;
requireBuilt.o = (object, key) => Object.prototype.hasOwnProperty.call(object, key);
requireBuilt.d = (object, key, getter) => { if (!requireBuilt.o(object, key)) Object.defineProperty(object, key, { enumerable: true, get: getter }); };
requireBuilt.n = module => { const getter = module && module.__esModule ? () => module.default : () => module; requireBuilt.d(getter, 'a', getter); return getter; };
requireBuilt.h = () => '';
function find(marker) {
  const id = Object.keys(modules).find(id => modules[id].toString().includes(marker));
  assert(id, 'Compiled module missing: ' + marker);
  return requireBuilt(id);
}
(async () => {
  const rotation = find('Selecciona un periodo válido.');
  assert.strictEqual(rotation.periodDays('2026-10-01', '2026-10-10'), 10);
  const order = find('NST_PEDIDO_V1');
  const buffer = await order.createOrder(Excel, [{ IdProducto: 1, CodigoBarra: '001', Producto: 'Prueba', suggested: 3 }], { warehouseId: 1 });
  const result = await order.readOrder(Excel, buffer);
  assert.strictEqual(result.rows[0].code, '001');
  assert.strictEqual(result.rows[0].quantity, 3);
  console.log('Compiled inventory modules initialize and execute; XLSX round-trip passed.');
  process.exit(0);
})().catch(error => { console.error(error); process.exit(1); });
