"""Normalize table identifiers in the clean export without touching SQL data literals."""
import importlib.util
import json
import re
import shutil
from pathlib import Path

root = Path(__file__).resolve().parent.parent
directory = Path((root / 'tmp/nstarapaca-production/export-directory.txt').read_text().strip())
package = directory / 'paquete'
mapping = json.loads((root / 'tmp/nstarapaca-production/nombres-tablas.json').read_text())
spec = importlib.util.spec_from_file_location('case_export', root / 'tools/prepare-case-sensitive-database.py')
helpers = importlib.util.module_from_spec(spec)
spec.loader.exec_module(helpers)
raw = (directory / 'erp.sql').read_text(encoding='utf-8')
parts = []
for literal, text in helpers.chunks(raw):
    if not literal:
        text = re.sub(r'`([^`]+)`', lambda m: '`' + mapping.get(m[1].lower(), m[1]) + '`', text)
        text = re.sub(r'\bDEFINER\s*=\s*`[^`]+`\s*@\s*`[^`]+`\s*', '', text, flags=re.I)
        text = re.sub(r'\bSQL SECURITY DEFINER\b', 'SQL SECURITY INVOKER', text, flags=re.I)
    parts.append(text)
fixed = ''.join(parts)
assert helpers.literals_hash(raw) == helpers.literals_hash(fixed), 'Data literals changed'
assert helpers.definitions(fixed) == set(mapping.values()), 'Object spelling/set mismatch'
assert not any(name.lower().startswith('cx_') for name in helpers.definitions(fixed))
assert not re.search(r'^USE\s', fixed, re.M | re.I)
(package / 'fact_nstarapaca.sql').write_text(fixed, encoding='utf-8', newline='\n')
for filename in ['nombres-tablas.json', 'referencias-php-a-revisar.csv']:
    shutil.copy2(root / 'tmp/nstarapaca-production' / filename, package / filename)
shutil.copy2(root / 'tools/normalizar-consultas-migracion.php', package / 'normalizar-consultas-migracion.php')
(package / 'verificacion-nombres.json').write_text(json.dumps({
    'objects': len(mapping), 'data_literals_unchanged_during_case_conversion': True,
    'local_definers_removed': True, 'explicit_database_use': False,
    'case_authority': 'Original ERP declarations, module migrations, and current nstarapaca queries.'
}, indent=2), encoding='utf-8')
print(json.dumps({'package': str(package), 'objects': len(mapping), 'sql_bytes': (package / 'fact_nstarapaca.sql').stat().st_size}))
