import re,json,collections
from pathlib import Path
sql=Path('C:/Users/USER/Downloads/nstarapaca-base-20261016/erp.sql').read_text(encoding='utf8')
names=set(re.findall(r'CREATE TABLE `([^`]+)`',sql));names.update(re.findall(r'VIEW `([^`]+)` AS',sql));canonical={}
for f in ['C:/Users/USER/Downloads/fact_nst_COMPLETA.sql','C:/xampp/htdocs/boticav1/neurofac_botica.sql']:
 for n in re.findall(r'CREATE TABLE(?: IF NOT EXISTS)? `([^`]+)`',Path(f).read_text(encoding='utf-8-sig',errors='replace')):canonical.setdefault(n.lower(),n)
votes={n:collections.Counter() for n in names}
root=Path('C:/xampp/htdocs/boticav1')
for f in [root/'api/index.php',root/'api/functions.php'] + list(Path('integration').rglob('*.sql')):
 if any('backup' in p.lower() or p.lower() in ['vendor','node_modules','.git'] for p in f.parts) or '.bak' in f.name:continue
 if not f.exists():continue
 text=f.read_text(encoding='utf8',errors='replace')
 for n in re.findall(r'\b[A-Za-z][A-Za-z0-9_]*\b',text):
  if n.lower() in votes:votes[n.lower()][n]+=1
for n in names:
 if n not in canonical:canonical[n]=votes[n].most_common(1)[0][0] if votes[n] else n
mapping={n:canonical.get(n,n) for n in sorted(names)}
Path('tmp/nstarapaca-case-map.json').write_text(json.dumps(mapping,indent=2))
for n in mapping:
 if n!=mapping[n]:print(n,'=>',mapping[n])
print('TOTAL',len(mapping),'RENAMES',sum(a!=b for a,b in mapping.items()))
