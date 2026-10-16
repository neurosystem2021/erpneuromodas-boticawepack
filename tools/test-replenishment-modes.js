const fs = require('fs'), vm = require('vm'), assert = require('assert');
const compiler = require('vue-template-compiler');
const source = compiler.parseComponent(fs.readFileSync('src/views/ReposicionStock.vue', 'utf8')).script.content
  .replace(/^import .*;\r?$/mg, '').replace(/^const .* = require\(.*\);\r?$/mg, '').replace('export default', 'module.exports =');
const context = { module: {}, ProductoForm: {} };
vm.runInNewContext(source, context);
const definition = context.module.exports;
const rows = [{ current: 2, minimum: 2, configured: true, state: 'minimum' }, { current: 1, minimum: 2, configured: true, state: 'minimum' }, { current: 9, minimum: 2, configured: true, state: 'normal' }];
assert.strictEqual(definition.computed.topRows.call({ mode: 'rotation', rows, top: 1 }).length, 1);
assert.strictEqual(definition.computed.topRows.call({ mode: 'minimum', rows, top: 1 }).length, 3);
assert.strictEqual(definition.computed.visibleRows.call({ mode: 'minimum', topRows: rows, search: '', alertsOnly: true }).length, 2);
assert.strictEqual(definition.computed.visibleRows.call({ mode: 'minimum', topRows: rows, search: '', alertsOnly: false }).length, 3);
let refreshed = false;
const state = { mode: 'rotation', alertsOnly: false, loading: false, load: () => { refreshed = true; } };
definition.methods.setMode.call(state, 'minimum');
assert.strictEqual(state.mode, 'minimum'); assert.strictEqual(state.alertsOnly, true); assert(refreshed);
console.log('Rotation and minimum modes: separate scope, thresholds, and refresh passed.');
process.exit(0);
