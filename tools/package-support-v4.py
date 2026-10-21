"""Package only the portable connector and the assets used by its current UI."""
from pathlib import Path
import hashlib
import json
import re
import shutil
import zipfile

root = Path(__file__).resolve().parent.parent
package = root / 'output/support-connector-nst-v4'
package.mkdir(parents=True, exist_ok=True)
connector = root / 'integration/helpdesk/connector'
for name in ['INSTALACION.txt', 'install-connector.php', 'setup-connector.php', 'support-connector.php', 'openssl.cnf', 'widget.js']:
    shutil.copy2(connector / name, package / name)
ui = root / 'output/support-ui'
html = (ui / 'index.html').read_text(encoding='utf-8')
assets = re.findall(r'(?:href|src)=["\']([^"\']+)["\']', html)
ui_target = package / 'ui'
ui_target.mkdir(exist_ok=True)
shutil.copy2(ui / 'index.html', ui_target / 'index.html')
for relative in assets:
    assert not relative.startswith(('http:', 'https:', '//'))
    source = (ui / relative).resolve()
    assert source.is_relative_to(ui.resolve()) and source.is_file()
    target = ui_target / relative
    target.parent.mkdir(parents=True, exist_ok=True)
    shutil.copy2(source, target)
files = sorted(p for p in package.rglob('*') if p.is_file() and p.name != 'SHA256SUMS.txt')
assert not any(p.suffix in ['.pem', '.key'] or p.name in ['support-client.php', 'public-registration.json', '.env'] for p in files)
(package / 'SHA256SUMS.txt').write_text(''.join(hashlib.sha256(p.read_bytes()).hexdigest() + '  ' + p.relative_to(package).as_posix() + '\n' for p in files), encoding='utf-8')
archive = root / 'output/support-connector-nst-v4.zip'
with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as z:
    for p in sorted(package.rglob('*')):
        if p.is_file(): z.write(p, p.relative_to(package).as_posix())
with zipfile.ZipFile(archive) as z:
    assert z.testzip() is None
print(json.dumps({'archive': str(archive), 'bytes': archive.stat().st_size, 'files': len(files) + 1, 'private_config_or_keys_included': False}))
