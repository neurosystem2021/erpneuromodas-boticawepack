const fs = require('fs');
const compiler = require('vue-template-compiler');
const { parse, inside, has, field } = require('./modernize-nst');
function update(file, change) {
  const source = fs.readFileSync(file, 'utf8');
  const part = compiler.parseComponent(source);
  if (!part.template) return;
  const template = change(part.template.content);
  const result = source.slice(0, part.template.start) + template + source.slice(part.template.end);
  const after = compiler.parseComponent(result);
  if ((after.script && after.script.content) !== (part.script && part.script.content)) throw new Error('Script changed: ' + file);
  const errors = compiler.compile(template).errors;
  if (errors.length) throw new Error(file + ': ' + errors.join('\n'));
  fs.writeFileSync(file, result);
}
function replaceOnce(text, old, next) {
  if (!text.includes(old)) throw new Error('Missing source anchor: ' + old.slice(0, 100));
  return text.replace(old, next);
}
function edits(text, changes) {
  changes.sort((a, b) => b.start - a.start || b.end - a.end).forEach(change => { text = text.slice(0, change.start) + change.value + text.slice(change.end); });
  return text;
}
for (const base of ['Movimiento', 'CargarComprasXml']) {
  update('src/views/' + base + '.vue', text => {
    if (text.includes('nst-document-grid')) return text;
    const nodes = parse(text);
    const row = nodes.find(node => node.name === 'b-row' && node.parent && node.parent.name === 'section');
    const cols = row.children.filter(node => node.name === 'b-col');
    const changes = [{ start: row.start, end: row.openEnd, value: '<b-row class="nst-document-grid">' }];
    cols.slice(0, 2).forEach((col, index) => {
      const open = text.slice(col.start, col.openEnd).replace('>', ' class="nst-subsection">');
      const heading = '<h3 class="nst-subsection-title"><i class="fas ' + (index ? 'fa-file-invoice' : 'fa-boxes') + '" aria-hidden="true"></i> ' + (index ? 'Comprobante y fechas' : 'Operación y proveedor') + '</h3>';
      changes.push({ start: col.start, end: col.openEnd, value: open + heading });
    });
    if (base === 'CargarComprasXml') {
      cols.slice(2).forEach(col => {
        const open = text.slice(col.start, col.openEnd);
        const value = /\sclass="/.test(open) ? open.replace(/\sclass="([^"]*)"/, ' class="$1 nst-xml-detail"') : open.replace('>', ' class="nst-xml-detail">');
        changes.push({ start: col.start, end: col.openEnd, value });
      });
    }
    return edits(text, changes);
  });
}
update('src/views/Movimiento.vue', text => text.replace('class="px-3"', 'class="px-3 nst-tax-options"'));
update('src/views/Traslado.vue', text => {
  if (text.includes('nst-route-fields')) return text.replace(/<b-row class="nst-route-fields">(\s*<b-col cols="12">\s*)<b-row>/, '<b-row>$1<b-row class="nst-route-fields">');
  text = replaceOnce(text, '<b-col md="8">', '<b-col cols="12">');
  text = replaceOnce(text, '<b-row>\n', '<b-row class="nst-route-fields">\n');
  text = replaceOnce(text, '<b-col md="4">', '<b-col md="6">');
  text = replaceOnce(text, '<b-col md="4" offset="4">', '<b-col md="6">');
  return text.replace(/<b-col md="4">\s*<\/b-col>/, '');
});
update('src/views/OrdenCompra.vue', text => text.replace('<b-col md="10">', '<b-col cols="12">'));
update('src/views/CajaBancos.vue', text => text.replace('<b-col cols="3">', '<b-col cols="12" md="3" class="nst-finance-sidebar">').replace('<b-col cols="9" class="px-5">', '<b-col cols="12" md="9" class="px-5 nst-finance-results">'));
update('src/components/ProductosCarrito.vue', text => {
  if (text.includes('Agrega el primer producto')) return text;
  text = replaceOnce(text, '</b-table>', '</b-table><div v-if="!productos.length" class="nst-empty-state"><i class="fas fa-box-open" aria-hidden="true"></i><strong>Agrega el primer producto</strong><span>Busca por nombre o escanea un código de barras para comenzar.</span></div>');
  text = replaceOnce(text, '<b-col md="3" v-if="!isVentas">', '<b-col md="3" v-if="!isVentas" class="pos-cart-summary">');
  return text.replace(/<tr>\s*(<td><b>TOTAL<\/b><\/td>)/, '<tr class="nst-grand-total">$1');
});
const viewFiles = [...fs.readFileSync('src/router/index.js', 'utf8').matchAll(/@\/views\/([^"']+\.vue)/g)].map(match => 'src/views/' + match[1]);
const components = fs.readdirSync('src/components').filter(file => file.endsWith('.vue')).map(file => 'src/components/' + file);
for (const file of [...new Set(viewFiles.concat(components))]) {
  update(file, text => {
    const nodes = parse(text), changes = [];
    nodes.filter(node => node.name === 'b-modal' && !('modal-class' in node.attrs) && !(':modal-class' in node.attrs)).forEach(node => {
      const position = text[node.openEnd - 2] === '/' ? node.openEnd - 2 : node.openEnd - 1;
      changes.push({ start: position, end: position, value: ' modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" ' });
    });
    nodes.filter(node => node.name === 'b-tab' && node.attrs.title && !node.children.some(child => child.attrs && child.attrs.slot === 'title')).forEach(node => {
      const title = node.attrs.title.replace(/&/g, '&amp;').replace(/</g, '&lt;');
      const icons = [[/caja|banco/i, 'fa-wallet'], [/gasto|salida/i, 'fa-arrow-circle-down'], [/ingreso/i, 'fa-arrow-circle-up'], [/perfil/i, 'fa-id-badge'], [/usuario/i, 'fa-users'], [/modulo|módulo/i, 'fa-th-large'], [/producto/i, 'fa-box-open'], [/venta/i, 'fa-shopping-cart'], [/resumen/i, 'fa-chart-pie'], [/configur/i, 'fa-cog']];
      const match = icons.find(item => item[0].test(title));
      changes.push({ start: node.openEnd, end: node.openEnd, value: '<template slot="title"><i class="fas ' + (match ? match[1] : 'fa-layer-group') + ' nst-tab-icon" aria-hidden="true"></i><span>' + title + '</span></template>' });
    });
    nodes.filter(node => node.name === 'b-table' && !inside(node, p => p.name === 'b-modal')).forEach(node => {
      if (!text.includes('nst-screen') || 'show-empty' in node.attrs) return;
      const position = text[node.openEnd - 2] === '/' ? node.openEnd - 2 : node.openEnd - 1;
      changes.push({ start: position, end: position, value: ' show-empty empty-text="No hay registros para los filtros seleccionados." empty-filtered-text="No se encontraron coincidencias." ' });
    });
    nodes.filter(node => node.name === 'b-col' && inside(node, p => p.name === 'b-modal') && !inside(node, p => p.name === 'b-col') && node.children.filter(child => child.name === 'b-form-group').length > 1).forEach(node => {
      if (text.slice(node.start, node.openEnd).includes('nst-editor-section')) return;
      let modal = node.parent;
      while (modal && modal.name !== 'b-modal') modal = modal.parent;
      if (modal && ((modal.attrs['modal-class'] || '').includes('pos-product-editor') || (modal.attrs['modal-class'] || '').includes('pos-customer'))) return;
      const open = text.slice(node.start, node.openEnd);
      const next = /\sclass="/.test(open) ? open.replace(/\sclass="([^"]*)"/, ' class="$1 nst-editor-section"') : open.replace('>', ' class="nst-editor-section">');
      changes.push({ start: node.start, end: node.openEnd, value: next });
    });
    return edits(text, changes);
  });
}
console.log('Refined document grids, transfer route, line items, dialogs and empty results.');
