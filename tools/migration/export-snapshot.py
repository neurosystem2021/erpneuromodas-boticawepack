"""Export reviewed dump as private data. SQL is parsed offline, never executed."""
import argparse,contextlib,io,json,os,runpy,hashlib,re
from pathlib import Path

parser=argparse.ArgumentParser()
parser.add_argument('--output',required=True)
args=parser.parse_args()
dest=Path(args.output).resolve()
if dest.is_relative_to(Path('C:/xampp/htdocs').resolve()):
    raise SystemExit('Private snapshot must be outside the web directory')
root=Path(__file__).resolve().parents[2]
os.chdir(root)
with contextlib.redirect_stdout(io.StringIO()):
    runpy.run_path('tools/compare-migration-schema.py')
    data=runpy.run_path('tools/analyze-migration-source.py')
comparison=json.loads(Path('output/migration-schema-comparison.json').read_text(encoding='utf-8'))
tables={}
for name,t in data['schema'].items():
    keys='\n'.join(t['keys'])
    pk=re.search(r'PRIMARY KEY\s*\(([^)]+)\)',keys,re.I)
    tables[name]={'columns':t['columns'],'primary':re.findall(r'`([^`]+)`',pk[1]) if pk else [],'rows':data['rows'].get(name,[]),'relations':[]}
    for fk in re.finditer(r'FOREIGN KEY\s*\(`([^`]+)`\)\s*REFERENCES\s*`([^`]+)`\s*\(`([^`]+)`\)',keys,re.I):
        tables[name]['relations'].append({'column':fk[1],'table':fk[2],'target_column':fk[3]})
snapshot={'format':'neuro-migration-v1','source_hash':comparison['sha256'],'source_file':comparison['source_file'],'tables':tables,'validation':data['out'],'comparison':comparison}
dest.parent.mkdir(parents=True,exist_ok=True)
# Exclusive creation prevents accidentally overwriting a reviewed source.
with dest.open('x',encoding='utf-8') as f:json.dump(snapshot,f,ensure_ascii=False)
os.chmod(dest,0o600)
print(json.dumps({'snapshot_created':True,'sha256':hashlib.sha256(dest.read_bytes()).hexdigest()}))
