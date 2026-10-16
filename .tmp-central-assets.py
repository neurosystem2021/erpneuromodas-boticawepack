from pathlib import Path
import re
missing=set()
for p in Path('.tmp-central-render').glob('*.html'):
 for url in re.findall(r'(?:src|href)=["\'](/resources/[^"\']+)',p.read_text(encoding='utf-8')):
  rel=url.split('?',1)[0].lstrip('/')
  if not (Path('C:/xampp/htdocs/boticav1')/rel).exists():missing.add(rel)
print('\n'.join(sorted(missing)) or 'All local image and stylesheet references exist')
