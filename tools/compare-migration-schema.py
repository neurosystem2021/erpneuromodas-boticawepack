import json,re,hashlib,os
from pathlib import Path
p=Path(r'C:/Users/USER/Downloads/fact_nst_COMPLETA.sql');raw=p.read_bytes();sql=raw.decode('utf-8-sig');target=json.loads(Path(os.environ.get('NEURO_MIGRATION_TARGET_SCHEMA','output/migration-target-schema.json')).read_text(encoding='utf-8'))
src={}
for m in re.finditer(r'CREATE TABLE(?: IF NOT EXISTS)?\s+`([^`]+)`\s*\((.*?)\)\s*ENGINE\s*=\s*(\w+)[^;]*;',sql,re.S|re.I):
 n,body,engine=m.groups();cols={c.group(1):c.group(2).strip().rstrip(',') for c in re.finditer(r'^\s*`([^`]+)`\s+(.+)$',body,re.M)};src[n]={'columns':cols,'engine':engine,'keys':[line.strip().rstrip(',') for line in body.splitlines() if re.match(r'\s*(PRIMARY KEY|UNIQUE KEY|KEY|CONSTRAINT)',line,re.I)]}
for m in re.finditer(r'ALTER TABLE\s+`([^`]+)`\s+(.*?);',sql,re.S|re.I):
 if m[1] in src:src[m[1]]['keys'].append(m[2].strip())
lookup={n.lower():n for n in target['tables']};mapped=[]
def source_indexes(definitions):
 result={}
 for definition in definitions:
  for m in re.finditer(r'(PRIMARY KEY|UNIQUE KEY|(?<!FOREIGN )\bKEY)\s*(?:`([^`]+)`)?\s*\(([^)]+)\)',definition,re.I):
   name='PRIMARY' if m[1].upper()=='PRIMARY KEY' else m[2]
   result[name]={'unique':m[1].upper()!='KEY','columns':re.findall(r'`([^`]+)`',m[3])}
 return result
def target_indexes(rows):
 result={}
 for row in sorted(rows,key=lambda r:(r['Key_name'],int(r['Seq_in_index']))):
  result.setdefault(row['Key_name'],{'unique':not bool(int(row['Non_unique'])),'columns':[]})['columns'].append(row['Column_name'])
 return result
for n,t in src.items():
 new=lookup.get(n.lower());d={'source':n,'target':new,'engine':t['engine']}
 if new:
  nt=target['tables'][new];
  if 'inspection_error' in nt:
   d['inspection_error']=nt['inspection_error'];mapped.append(d);continue
  nc={c['Field'].lower():c for c in nt.get('columns',[])};sc={c.lower():c for c in t['columns']};d['new_columns']=[c['Field'] for k,c in nc.items() if k not in sc];d['missing_columns']=[c for k,c in sc.items() if k not in nc];d['changed_columns']=[{'column':c,'source':v,'target':nc[c.lower()]['Type'],'nullable':nc[c.lower()]['Null'],'default':nc[c.lower()]['Default']} for c,v in t['columns'].items() if c.lower() in nc and not v.lower().startswith(nc[c.lower()]['Type'].lower())];d['source_keys']=t['keys'];d['target_indexes']=nt.get('indexes',[]);d['target_engine']=nt.get('ENGINE');d['target_rows']=nt.get('rows');d['inspection_error']=nt.get('inspection_error')
 if new and not d.get('inspection_error'):
  old_indexes=source_indexes(t['keys']);new_indexes=target_indexes(nt.get('indexes',[]))
  d['index_differences']={'added':{k:v for k,v in new_indexes.items() if k not in old_indexes},'removed':{k:v for k,v in old_indexes.items() if k not in new_indexes},'changed':{k:{'source':v,'target':new_indexes[k]} for k,v in old_indexes.items() if k in new_indexes and v!=new_indexes[k]}}
  d['attribute_differences']=[]
  for column,definition in t['columns'].items():
   current=nc.get(column.lower())
   if not current:continue
   nullable='NO' if 'NOT NULL' in definition.upper() else 'YES'
   default=re.search(r"\bDEFAULT\s+(NULL|'(?:[^']|'')*'|[^\s,]+)",definition,re.I)
   value=default[1] if default else None
   if value is not None:value=None if value.upper()=='NULL' else value.strip("'")
   if nullable!=current['Null'] or value!=(None if current['Default'] is None else str(current['Default']).strip("'")):
    d['attribute_differences'].append({'column':column,'source_nullable':nullable,'target_nullable':current['Null'],'source_default':value,'target_default':current['Default']})
  d['engine_changed']=t['engine'].lower()!=str(nt.get('ENGINE')).lower()
 mapped.append(d)
out={'source_file':p.name,'sha256':hashlib.sha256(raw).hexdigest(),'source_tables':src,'target_database':target['database'],'mapping':mapped,'new_tables':[n for n in target['tables'] if n.lower() not in {s.lower() for s in src}]}
Path('output/migration-schema-comparison.json').write_text(json.dumps(out,ensure_ascii=False,indent=2),encoding='utf-8')
print('Source tables:',len(src),'Target:',len(target['tables']),'Matching:',sum(bool(m['target']) for m in mapped))
for d in mapped:
 if re.match(r'^(Ve_|Lo_|Gen_Producto|prodstock)',d['source'],re.I):print(d['source'],'=>',d['target'],'new:',','.join(d.get('new_columns',[])),'missing:',','.join(d.get('missing_columns',[])),'type changes:',len(d.get('changed_columns',[])),'rows:',d.get('target_rows'))
print('INSPECTION ERRORS:',[(n,t['inspection_error']) for n,t in target['tables'].items() if 'inspection_error' in t])
