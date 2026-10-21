"""Install only the invoice/receipt PDF layout correction, with a private backup."""
from datetime import datetime
from pathlib import Path
import hashlib
import json
import shutil
import subprocess

root = Path(__file__).resolve().parent.parent
runtime = Path('C:/xampp/htdocs/boticav1')
for repo in (root, runtime):
    branch = subprocess.check_output([
        'git', '-c', 'safe.directory=' + root.as_posix(),
        '-c', 'safe.directory=' + runtime.as_posix(),
        '-C', str(repo), 'branch', '--show-current'
    ], text=True).strip()
    if branch != 'imceden':
        raise SystemExit('Both checkouts must be on imceden.')

target = runtime / 'api/sunat/plugins/dompdf/index.php'
source = root / 'server/pdf/comprobante.php'
before = target.read_bytes()
after = source.read_bytes()
# Permit exactly the reviewed CSS changes; never replace unrelated runtime edits.
expected = before.decode('utf-8-sig').replace(
    '.footer-block { position: fixed; top: 565px; right: 30px; left: 30px; }',
    '.footer-block { margin-top: 14px; page-break-inside: avoid; }'
).replace(
    '.items { margin-top: 12px; border: 1px solid #cfdbe8; }',
    '.items { margin-top: 12px; border: 1px solid #cfdbe8; }\n'
    '.items thead { display: table-header-group; }\n'
    '.items tbody tr { page-break-inside: avoid; }'
)
if before == after:
    print('PDF layout is already installed.')
    raise SystemExit(0)
if expected.replace('\r\n', '\n') != after.decode('utf-8').replace('\r\n', '\n'):
    raise SystemExit('Runtime differs from the reviewed layout; installation stopped.')
subprocess.run(['C:/xampp/php/php.exe', '-l', str(source)], check=True)
backup = Path('C:/xampp/accounting-private') / ('imceden-pdf-layout-' + datetime.now().strftime('%Y%m%d-%H%M%S'))
backup.mkdir(parents=True, exist_ok=False)
shutil.copy2(target, backup / 'index.php')
manifest = {
    'target': str(target),
    'before_sha256': hashlib.sha256(before).hexdigest(),
    'after_sha256': hashlib.sha256(after).hexdigest(),
    'change': 'Flow totals/QR after all product rows; repeat headers on page breaks.'
}
(backup / 'manifest.json').write_text(json.dumps(manifest, indent=2), encoding='utf-8')
target.write_bytes(after)
assert target.read_bytes() == after
print(json.dumps({'backup': str(backup), **manifest}))
