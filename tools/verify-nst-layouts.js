const fs = require('fs');
const path = require('path');
const compiler = require('vue-template-compiler');
const crypto = require('crypto');
const { parse } = require('./modernize-nst');
const before = JSON.parse(fs.readFileSync('tools/nst-layout-coverage.json', 'utf8'));
const updates = fs.existsSync('tools/nst-business-updates.json') ? JSON.parse(fs.readFileSync('tools/nst-business-updates.json', 'utf8')) : {};
const failures = [];
let compiled = 0, dialogs = 0, panels = 0, tabs = 0;
function visit(directory) {
  for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
    const file = path.join(directory, entry.name);
    if (entry.isDirectory()) visit(file);
    else if (entry.name.endsWith('.vue')) {
      const part = compiler.parseComponent(fs.readFileSync(file, 'utf8'));
      if (!part.template) continue;
      const errors = compiler.compile(part.template.content).errors;
      if (errors.length) failures.push(file + ': ' + errors.join('; '));
      compiled += 1;
      const nodes = parse(part.template.content);
      dialogs += nodes.filter(node => node.name === 'b-modal' && (node.attrs['modal-class'] || '').includes('nst-dialog')).length;
      panels += nodes.filter(node => node.name === 'section' && (node.attrs.class || '').includes('nst-panel')).length;
      tabs += (part.template.content.match(/nst-tab-icon/g) || []).length;
    }
  }
}
visit('src/views'); visit('src/components');
for (const entry of before) {
  const part = compiler.parseComponent(fs.readFileSync(entry.file, 'utf8'));
  // The purchase screen now registers the requested Excel import component.
  // Strip only that registration to verify its existing business logic stayed intact.
  let script = part.script && part.script.content;
  if (script && entry.file === 'src/views/Movimiento.vue') script = script.replace(/import PedidoExcelImport from '@\/components\/PedidoExcelImport';\r?\n/, '').replace(/    PedidoExcelImport,\r?\n/, '');
  const hash = script ? crypto.createHash('sha256').update(script).digest('hex') : null;
  if (hash !== entry.scriptSha256 && (!updates[entry.file] || hash !== updates[entry.file].scriptSha256)) failures.push('Business script changed without reviewed update: ' + entry.file);
}
const routes = [...fs.readFileSync('src/router/index.js', 'utf8').matchAll(/@\/views\/([^"']+\.vue)/g)].map(match => 'src/views/' + match[1]);
for (const file of routes) {
  const source = fs.readFileSync(file, 'utf8');
  if (!/nst-screen|pos-page|product-catalog|slow-products|printer|Proforma/.test(source)) failures.push('Route without module layout: ' + file);
}
if (failures.length) { console.error(failures.join('\n')); process.exit(1); }
const result = { compiledTemplates: compiled, routedViews: new Set(routes).size, panels, premiumDialogs: dialogs, tabs, preservedBusinessScripts: before.length - Object.keys(updates).length, updatedBusinessScripts: Object.keys(updates).length };
console.log(JSON.stringify(result, null, 2));
fs.writeFileSync('tools/nst-verification.json', JSON.stringify(result, null, 2) + '\n');
