from pathlib import Path
import datetime, difflib, hashlib, re, shutil, subprocess, sys

root = Path(__file__).resolve().parent.parent
live = Path('C:/xampp/htdocs/boticav1')
target = live / 'tienda/index.php'
original = target.read_text(encoding='utf-8-sig')
candidate = original
start = candidate.index('$storeFallbackLogo = ')
end = candidate.index('\n?>', start)
candidate = candidate[:start] + "$storeLogo = '/resources/images/imceden-store.svg?v=20261018-1';\n$storeName = 'IMCEDEN';" + candidate[end:]
old = "const business=company.nombre_comercial||company.RAZONSOCIAL||'EDEN COMPANY';"
new = "const business='IMCEDEN';"
if candidate.count(old) != 1:
    raise RuntimeError('Unexpected store initializer; stopped before modifying any live file.')
candidate = candidate.replace(old, new)
old = "if(company.logo_principal)$('brandLogo').src=company.logo_principal;"
if candidate.count(old) != 1:
    raise RuntimeError('Unexpected logo initializer.')
candidate = candidate.replace(old, "$('brandLogo').src='/resources/images/imceden-store.svg?v=20261018-1';")
candidate, count = re.subn(r'(/resources/js/neurollantas-store\.js\?v=)[^"\s]+', r'\g<1>20261018-imceden-1', candidate)
if count != 1:
    raise RuntimeError('Unexpected store branding script reference.')
stage = root / 'tmp/imceden-store-branding'
stage.mkdir(parents=True, exist_ok=True)
(stage / 'index.php').write_text(candidate, encoding='utf-8')
patch = ''.join(difflib.unified_diff(original.splitlines(True), candidate.splitlines(True), fromfile='a/tienda/index.php', tofile='b/tienda/index.php'))
(root / 'server/patches/imceden-store-branding.patch').write_text(patch, encoding='utf-8')
for file in [stage / 'index.php', root / 'server/tienda/TiendaVirtual.php']:
    subprocess.run(['C:/xampp/php/php.exe', '-l', str(file)], check=True)
subprocess.run(['node', '--check', str(root / 'server/tienda/neurollantas-store.js')], check=True)
if '--prepare-only' in sys.argv:
    print('Store branding prepared; no live files changed.')
    sys.exit(0)
for repo in [root, live]:
    branch = subprocess.check_output(['git', '-c', 'safe.directory='+repo.as_posix(), '-C', str(repo), 'branch', '--show-current'], text=True).strip()
    if branch != 'imceden':
        raise RuntimeError('Both repositories must remain on imceden.')
if target.read_text(encoding='utf-8-sig') != original:
    raise RuntimeError('Concurrent store modification.')
backup = Path('C:/xampp/accounting-private') / ('imceden-store-branding-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'))
files = [
    (root / 'server/tienda/imceden-store.svg', 'resources/images/imceden-store.svg'),
    (root / 'server/tienda/neurollantas-store.js', 'resources/js/neurollantas-store.js'),
    (root / 'server/tienda/TiendaVirtual.php', 'views/TiendaVirtual.php'),
    (stage / 'index.php', 'tienda/index.php'),
]
for source, relative in files:
    destination = live / relative
    if destination.exists():
        saved = backup / relative
        saved.parent.mkdir(parents=True, exist_ok=True)
        shutil.copy2(destination, saved)
    destination.parent.mkdir(parents=True, exist_ok=True)
    shutil.copyfile(source, destination)
    if hashlib.sha256(source.read_bytes()).digest() != hashlib.sha256(destination.read_bytes()).digest():
        raise RuntimeError('Installed file checksum mismatch: '+relative)
print('IMCEDEN store identity installed. Backup: '+str(backup)+'. No company database, products, prices or order settings changed.')
