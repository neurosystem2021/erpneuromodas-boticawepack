const fs = require('fs');
const compiler = require('vue-template-compiler');
const { parse } = require('./modernize-nst');
function visit(directory) {
  for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
    const file = directory + '/' + entry.name;
    if (entry.isDirectory()) { visit(file); continue; }
    if (!entry.name.endsWith('.vue')) continue;
    const source = fs.readFileSync(file, 'utf8');
    const part = compiler.parseComponent(source);
    if (!part.template) continue;
    const removals = parse(part.template.content).filter(node =>
      (node.attrs.class || '').split(/\s+/).some(name => ['nst-module-header', 'nst-panel-heading', 'nst-subsection-title'].includes(name)));
    if (!removals.length) continue;
    let template = part.template.content;
    removals.sort((a, b) => b.start - a.start).forEach(node => { template = template.slice(0, node.start) + template.slice(node.end); });
    const errors = compiler.compile(template).errors;
    if (errors.length) throw new Error(file + ': ' + errors.join('\n'));
    fs.writeFileSync(file, source.slice(0, part.template.start) + template + source.slice(part.template.end));
  }
}
visit('src');
console.log('Removed module banners and decorative section headings.');
