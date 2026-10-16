/* Source-only layout migration. Never replaces scripts or business bindings. */
const fs = require('fs');
const path = require('path');
const compiler = require('vue-template-compiler');
const html = require('htmlparser2');
const crypto = require('crypto');

function parse(source) {
  const nodes = [], stack = [];
  let parser;
  parser = new html.Parser({
    onopentag(name, attrs) {
      const node = { name, attrs, start: parser.startIndex, openEnd: parser.endIndex + 1, children: [], parent: stack[stack.length - 1] };
      if (node.parent) node.parent.children.push(node);
      nodes.push(node); stack.push(node);
    },
    onclosetag() { const node = stack.pop(); if (node) node.end = parser.endIndex + 1; }
  }, { decodeEntities: false, lowerCaseAttributeNames: false, recognizeSelfClosing: true });
  parser.end(source);
  return nodes;
}
const inside = (node, test) => { for (let p = node.parent; p; p = p.parent) if (test(p)) return true; return false; };
const has = (node, test) => node.children.some(child => test(child) || has(child, test));
const field = node => ['b-form-group', 'b-form-input', 'b-form-select', 'autocomplete', 'input', 'select'].indexOf(node.name) >= 0;
const result = node => ['table', 'b-table', 'productos-carrito', 'productos-carrito-nuevo'].indexOf(node.name) >= 0;
const escape = value => value.replace(/&/g, '&amp;').replace(/"/g, '&quot;');
const descriptions = {
  filters: ['Filtros de consulta', 'Selecciona los criterios para consultar la información.', 'fas fa-sliders-h'],
  results: ['Detalle y resultados', 'Revisa los registros y sus acciones disponibles.', 'fas fa-list-ul'],
  products: ['Detalle de productos', 'Busca por nombre o código y revisa cantidades e importes.', 'fas fa-box-open'],
  actions: ['Confirmar operación', 'Revisa los datos antes de continuar.', 'fas fa-check-circle'],
  settings: ['Datos y configuración', 'Completa la información de esta sección.', 'fas fa-edit']
};
const sectionTitles = {
  Movimiento: ['Datos del movimiento', 'Impuestos del comprobante', 'Detalle de productos', 'Guardar movimiento'],
  Traslado: ['Ruta del traslado', 'Productos a trasladar', 'Confirmar traslado'],
  OrdenCompra: ['Almacén y proveedor', 'Productos de la orden', 'Generar orden de compra'],
  Kardex: ['Producto y periodo', 'Historial de movimientos'],
  Articulosmovimientos: ['Almacén y periodo', 'Rotación de productos'],
  Almacen: ['Resumen de existencias', 'Buscar en el inventario', 'Detalle de stock'],
  CargarComprasXml: ['Datos de la compra XML'],
  VentaReimpresion: ['Buscar comprobantes', 'Comprobantes registrados'],
  Clientes: ['Buscar clientes', 'Directorio de clientes'],
  BuscadorCajaBanco: ['Consulta de caja y bancos'],
  AgenteGeneral: ['Resumen y consulta de agentes'],
  ConfigurarOferta: ['Configurar promociones'],
  Remision: ['Datos de la guía', 'Direcciones y transporte', 'Detalle de productos'],
  RemisionLista: ['Consultar guías', 'Guías registradas'],
  RemisionTransportista: ['Transportistas y vehículos'],
  Usuario: ['Administración de accesos'],
  ReporteRegistroVentas: ['Filtros del registro de ventas', 'Comprobantes del periodo'],
  ReporteRegMovimiento: ['Filtros del registro de movimientos', 'Movimientos del periodo'],
  DeudaClientes: ['Consultar cuentas por cobrar', 'Saldos de clientes'],
  DeudaProveedores: ['Consultar cuentas por pagar', 'Saldos de proveedores']
};
const router = fs.readFileSync('src/router/index.js', 'utf8');
const views = [...new Set([...router.matchAll(/@\/views\/([^"']+\.vue)/g)].map(match => 'src/views/' + match[1]))];
const excluded = ['Ventas.vue', 'Productos.vue', 'ProductosSinMovimiento.vue', 'Impresoras.vue', 'reportes/Proforma.vue'];
const report = [];

function migrate(file, isView) {
  const original = fs.readFileSync(file, 'utf8');
  const parsed = compiler.parseComponent(original);
  if (!parsed.template) return;
  const template = parsed.template.content;
  if (template.indexOf('nst-screen') >= 0 || (!isView && template.indexOf('nst-dialog') >= 0)) return;
  const nodes = parse(template), edits = [], attrs = new Map();
  const roots = nodes.filter(node => !node.parent);
  function setAttr(node, key, value) {
    const values = attrs.get(node) || Object.assign({}, node.attrs);
    values[key] = value; attrs.set(node, values);
  }
  function addClass(node, value, key = 'class') { setAttr(node, key, [node.attrs[key], value].filter(Boolean).join(' ')); }
  function wrap(node, before, after) {
    edits.push({ start: node.start, end: node.start, value: before });
    edits.push({ start: node.end, end: node.end, value: after });
  }
  const base = path.basename(file, '.vue');
  if (isView && roots[0]) addClass(roots[0], 'nst-screen nst-screen--' + base.toLowerCase());
  // Modal classes follow the portal when BootstrapVue mounts it under body.
  nodes.filter(node => node.name === 'b-modal').forEach(node => {
    addClass(node, 'nst-dialog', 'modal-class');
    addClass(node, 'nst-dialog-body', 'body-class');
    addClass(node, 'nst-dialog-footer', 'footer-class');
    if (!node.attrs.centered) setAttr(node, 'centered', '');
  });
  if (isView) {
    const rows = nodes.filter(node => node.name === 'b-row' && !inside(node, p => p.name === 'b-modal' || p.name === 'b-row' || p.name === 'template') && !('v-else' in node.attrs) && !('v-else-if' in node.attrs) && !('v-for' in node.attrs));
    let index = 0;
    rows.forEach(row => {
      if (!has(row, field) && !has(row, result)) { if (has(row, p => p.name === 'b-button' || p.name === 'b-btn')) addClass(row, 'nst-action-bar'); return; }
      const product = has(row, p => p.name.indexOf('productos-carrito') === 0);
      const type = product ? 'products' : has(row, result) && !has(row, field) ? 'results' : has(row, field) ? 'filters' : 'settings';
      const config = descriptions[type];
      const title = (sectionTitles[base] || [])[index] || config[0];
      const heading = '<div class="nst-panel-heading"><span class="nst-panel-icon"><i class="' + config[2] + '" aria-hidden="true"></i></span><div><h2>' + title + '</h2><p>' + config[1] + '</p></div></div>';
      // Conditions stay on the original row. Never wrap a v-if/v-else chain.
      const next = nodes.find(node => node.parent === row.parent && node.start > row.end);
      if (next && ('v-else' in next.attrs || 'v-else-if' in next.attrs)) { addClass(row, 'nst-inline-panel'); return; }
      wrap(row, '<section class="nst-panel nst-panel--' + type + '">' + heading, '</section>');
      index += 1;
    });
    nodes.filter(node => result(node) && node.name !== 'productos-carrito' && node.name !== 'productos-carrito-nuevo').forEach(node => {
      if (inside(node, p => p.name === 'b-modal')) return;
      if (node.name === 'b-table' && !('responsive' in node.attrs)) setAttr(node, 'responsive', '');
      if (node.name === 'table' && !('v-else' in node.attrs) && !('v-else-if' in node.attrs)) wrap(node, '<div class="nst-table-scroll" tabindex="0" role="region" aria-label="Tabla de resultados">', '</div>');
    });
  }
  attrs.forEach((values, node) => {
    let tag = template.slice(node.start, node.openEnd);
    Object.keys(values).forEach(key => {
      if (node.attrs[key] === values[key]) return;
      const quoted = key.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
      const expression = new RegExp('(^|\\s)' + quoted + '(?:\\s*=\\s*(?:"[^"]*"|\'[^\']*\'|[^\\s>]+))?(?=\\s|/?>)');
      const attribute = key + '="' + escape(values[key]) + '"';
      if (expression.test(tag)) tag = tag.replace(expression, (_, space) => space + attribute);
      else tag = tag.replace(/\s*\/?>$/, match => ' ' + attribute + match);
    });
    edits.push({ start: node.start, end: node.openEnd, value: tag });
  });
  if (!edits.length) return;
  let updated = template;
  // Insert a wrapper before an opening-tag replacement at the same position.
  edits.sort((a, b) => b.start - a.start || b.end - a.end).forEach(edit => { updated = updated.slice(0, edit.start) + edit.value + updated.slice(edit.end); });
  const output = original.slice(0, parsed.template.start) + updated + original.slice(parsed.template.end);
  const after = compiler.parseComponent(output);
  if ((parsed.script && parsed.script.content) !== (after.script && after.script.content)) throw new Error('Business script changed: ' + file);
  const compiled = compiler.compile(after.template.content);
  if (compiled.errors.length) throw new Error(file + ': ' + compiled.errors.join('\n'));
  fs.writeFileSync(file, output, 'utf8');
  report.push({ file, panels: (updated.match(/nst-panel-heading/g) || []).length, dialogs: (updated.match(/nst-dialog"/g) || []).length, scriptSha256: parsed.script ? crypto.createHash('sha256').update(parsed.script.content).digest('hex') : null });
}
if (require.main === module) {
views.filter(file => excluded.indexOf(file.replace('src/views/', '')) < 0).forEach(file => migrate(file, true));
fs.readdirSync('src/components').filter(file => file.endsWith('.vue')).forEach(file => migrate('src/components/' + file, false));
fs.writeFileSync('tools/nst-layout-coverage.json', JSON.stringify(report, null, 2) + '\n');
console.log(JSON.stringify({ views: report.filter(item => item.file.indexOf('src/views/') === 0).length, components: report.filter(item => item.file.indexOf('src/components/') === 0).length, panels: report.reduce((sum, item) => sum + item.panels, 0), dialogs: report.reduce((sum, item) => sum + item.dialogs, 0) }));
}
module.exports = { parse, inside, has, field, result };
