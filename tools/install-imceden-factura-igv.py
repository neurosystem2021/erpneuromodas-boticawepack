from pathlib import Path
import datetime, difflib, hashlib, re, shutil, subprocess, sys

root = Path(__file__).resolve().parent.parent
live = Path('C:/xampp/htdocs/boticav1')
target = live / 'api/index.php'
original = target.read_text(encoding='utf-8-sig')
candidate = original
for method, route, marker, typevar, seriesvar in [
    ('get', '/ventas/numero', "    $numero = $selectNumero['NuevoNumero'];", '$idTipoDoc', '$serie'),
    ('post', '/ventas', "    $numero = $selectNumero['NuevoNumero'] ? $selectNumero['NuevoNumero'] : 1;", '$idTipoDoc', '$serie'),
]:
    pattern = r"\$app->"+method+r"\('"+re.escape(route)+r"',.*?\n\}\);"
    match = re.search(pattern, candidate, re.S)
    if not match or match[0].count(marker) != 1:
        raise RuntimeError('Unexpected numbering route: '+route)
    addition = "\n    require_once __DIR__ . '/factura-igv.php';\n    $numeroFactura = imcFacturaIgvNumero($this->db, "+typevar+", "+seriesvar+");\n    if ($numeroFactura !== null) $numero = $numeroFactura;"
    replacement = match[0].replace(marker, marker+addition)
    candidate = candidate[:match.start()]+replacement+candidate[match.end():]
stage = root / 'tmp/imceden-factura-igv'
stage.mkdir(parents=True, exist_ok=True)
(stage / 'index.php').write_text(candidate, encoding='utf-8')
module = root / 'server/facturacion/factura-igv.php'
for file in [stage / 'index.php', module]:
    subprocess.run(['C:/xampp/php/php.exe', '-l', str(file)], check=True)
patch = ''.join(difflib.unified_diff(original.splitlines(True), candidate.splitlines(True), fromfile='a/api/index.php', tofile='b/api/index.php'))
(root / 'server/patches/imceden-factura-igv.patch').write_text(patch, encoding='utf-8')
if '--prepare-only' in sys.argv:
    print('IMCEDEN invoice code prepared. No database changes.')
    sys.exit(0)
for repo in [root, live]:
    branch = subprocess.check_output(['git', '-c', 'safe.directory='+repo.as_posix(), '-C', str(repo), 'branch', '--show-current'], text=True).strip()
    if branch != 'imceden':
        raise RuntimeError('Both repositories must remain on imceden.')
if target.read_text(encoding='utf-8-sig') != original:
    raise RuntimeError('Concurrent API change.')
backup = Path('C:/xampp/accounting-private') / ('imceden-factura-igv-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'))
backup.mkdir(parents=True)
for source, destination in [(module, live / 'api/factura-igv.php'), (stage / 'index.php', target)]:
    if destination.exists():
        shutil.copy2(destination, backup / destination.name)
    shutil.copyfile(source, destination)
    if hashlib.sha256(source.read_bytes()).digest() != hashlib.sha256(destination.read_bytes()).digest():
        raise RuntimeError('Installed checksum mismatch.')
print('IMCEDEN invoice code installed. Backup: '+str(backup)+'. Database activation is separate; no invoices issued.')
