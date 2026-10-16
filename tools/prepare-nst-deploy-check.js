const fs = require('fs');
const compiler = require('vue-template-compiler');
const childProcess = require('child_process');
const files = childProcess.execFileSync('git', ['-c', 'safe.directory=C:/xampp/htdocs/boticawebpack', 'diff', '--name-only', '--', 'src'], { encoding: 'utf8' }).trim().split('\n').filter(file => file.endsWith('.vue'));
let restored = 0;
for (const file of files) {
  let baseline;
  try { baseline = childProcess.execFileSync('git', ['-c', 'safe.directory=C:/xampp/htdocs/boticawebpack', 'show', 'HEAD:' + file], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] }); } catch (_) { continue; }
  const source = fs.readFileSync(file, 'utf8');
  const current = compiler.parseComponent(source), before = compiler.parseComponent(baseline);
  if (current.template && before.template && current.template.content === before.template.content) {
    const output = source.slice(0, current.template.start) + baseline.slice(before.template.start, before.template.end) + source.slice(current.template.end);
    if (source !== output) { fs.writeFileSync(file, output); restored += 1; }
  }
}
const deploy = fs.readFileSync('tools/deploy-nst-premium.ps1', 'utf8');
const adapter = deploy.match(/\$adapter = @'\r?\n([\s\S]*?)\r?\n'@/)[1];
const header = fs.readFileSync('C:/xampp/htdocs/boticav1/views/header.php', 'utf8');
fs.writeFileSync('.tmp-nst-header-check.php', header + '\n' + adapter + '\n');
console.log('Restored unchanged template formatting in ' + restored + ' files; prepared PHP adapter lint.');
