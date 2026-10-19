"""Prepare a private SQL export; never change data literals or the live database."""
import argparse, csv, hashlib, json, os, re, collections
from pathlib import Path

def chunks(sql):
    start=i=0
    while i<len(sql):
        if sql[i] not in "'\"":
            i+=1;continue
        if i>start:yield False,sql[start:i]
        quote=sql[i];start=i;i+=1
        while i<len(sql):
            if sql[i]=='\\':i+=2;continue
            if sql[i]==quote:
                if i+1<len(sql) and sql[i+1]==quote:i+=2;continue
                i+=1;break
            i+=1
        yield True,sql[start:i];start=i
    if start<len(sql):yield False,sql[start:]

def literals_hash(sql):
    h=hashlib.sha256();count=0
    for literal,text in chunks(sql):
        if literal:
            encoded=text.encode('utf-8');h.update(len(encoded).to_bytes(8,'big'));h.update(encoded);count+=1
    return h.hexdigest(),count

def definitions(sql):
    return set(re.findall(r'\b(?:TABLE(?:\s+IF\s+NOT\s+EXISTS)?|VIEW)\s+`([^`]+)`',sql,re.I))

def main():
    p=argparse.ArgumentParser();p.add_argument('--directory',required=True);args=p.parse_args()
    directory=Path(args.directory);raw=(directory/'erp.sql').read_text(encoding='utf-8')
    names={name.lower() for name in definitions(raw)}
    previous=json.loads(Path('tmp/nstarapaca-case-map.json').read_text())
    canonical={}
    # Original declarations are the authority for old ERP objects.
    for source in [Path('C:/Users/USER/Downloads/fact_nst_COMPLETA.sql'),Path('C:/xampp/htdocs/boticav1/neurofac_botica.sql')]:
        text=source.read_text(encoding='utf-8-sig',errors='replace')
        for name in re.findall(r'CREATE TABLE(?: IF NOT EXISTS)? `([^`]+)`',text):
            if name.lower() in names:canonical.setdefault(name.lower(),name)
    # New accounting and activity objects retain their migration spelling.
    sources=list(Path('integration/accounting/migrations').glob('*.up.sql'))+[Path('integration/diario/schema.sql')]
    for source in sources:
        for name in re.findall(r'CREATE TABLE(?: IF NOT EXISTS)?\s+`?([A-Za-z][\w]*)',source.read_text(encoding='utf-8'),re.I):
            if name.lower() in names:canonical.setdefault(name.lower(),name)
    root=Path('C:/xampp/htdocs/boticav1');php_sources=[]
    pattern=re.compile(r'\b(?:FROM|JOIN|INTO|UPDATE|REFERENCES|TABLE(?:\s+IF\s+NOT\s+EXISTS)?)\s+`?([A-Za-z][A-Za-z0-9_]*)`?',re.I)
    votes={name:collections.Counter() for name in names}
    for folder,dirs,files in os.walk(root):
        dirs[:]=[d for d in dirs if not d.startswith('.') and d.lower() not in ['vendor','node_modules','nuevo','support-ui'] and 'backup' not in d.lower()]
        for filename in files:
            if not filename.endswith('.php') or '.bak' in filename:continue
            path=Path(folder)/filename;text=path.read_text(encoding='utf-8',errors='replace');php_sources.append((path,text))
            for match in pattern.finditer(text):
                if match[1].lower() in names:votes[match[1].lower()][match[1]]+=1
    for name in names:
        canonical.setdefault(name,votes[name].most_common(1)[0][0] if votes[name] else previous.get(name,name))
    changes=0;definers=0;output=[]
    for literal,text in chunks(raw):
        if literal:output.append(text);continue
        def replace(m):
            nonlocal changes
            name=m[1];target=canonical.get(name.lower(),name)
            if target!=name:changes+=1
            return '`'+target+'`'
        text=re.sub(r'`([^`]+)`',replace,text)
        text,n=re.subn(r'\bDEFINER\s*=\s*`[^`]+`\s*@\s*`[^`]+`\s*','',text,flags=re.I);definers+=n
        text=re.sub(r'\bSQL SECURITY DEFINER\b','SQL SECURITY INVOKER',text,flags=re.I)
        output.append(text)
    fixed=''.join(output)
    assert literals_hash(raw)==literals_hash(fixed),'SQL data literals changed'
    for name in definitions(fixed):assert name==canonical[name.lower()],('Incorrect object case',name)
    assert {name.lower() for name in definitions(fixed)}==names,'Object set changed'
    (directory/'fact_nstarapaca.sql').write_text(fixed,encoding='utf-8',newline='\n')
    (directory/'nombres-tablas.json').write_text(json.dumps(canonical,ensure_ascii=False,indent=2),encoding='utf-8')
    support=(directory/'soporte.sql').read_text(encoding='utf-8')
    support_out=[]
    for literal,text in chunks(support):
        if not literal:
            text=re.sub(r'\bDEFINER\s*=\s*`[^`]+`\s*@\s*`[^`]+`\s*','',text,flags=re.I)
            text=re.sub(r'\bSQL SECURITY DEFINER\b','SQL SECURITY INVOKER',text,flags=re.I)
        support_out.append(text)
    support_fixed=''.join(support_out);assert literals_hash(support)==literals_hash(support_fixed)
    (directory/'fact_nstarapaca_soporte.sql').write_text(support_fixed,encoding='utf-8',newline='\n')
    # Read-only report: Windows can mask case mismatches in PHP SQL queries.
    issues=[]
    for path,text in php_sources:
        for match in pattern.finditer(text):
            name=match[1];target=canonical.get(name.lower())
            if target and name!=target:issues.append([str(path.relative_to(root)),text.count('\n',0,match.start())+1,name,target])
    with (directory/'referencias-php-a-revisar.csv').open('w',encoding='utf-8-sig',newline='') as f:
        writer=csv.writer(f);writer.writerow(['Archivo','Linea','Nombre usado','Nombre correcto']);writer.writerows(issues)
    report={'objects_erp':len(names),'objects_support':len(definitions(support_fixed)),'identifier_replacements':changes,'local_definers_removed':definers,'data_literals_sha256':literals_hash(fixed)[0],'data_literal_count':literals_hash(fixed)[1],'data_literals_unchanged':True,'php_case_references_to_review':len(issues),'php_files_to_review':len({r[0] for r in issues}),'case_validation':'SQL definitions and identifier spelling checked. Windows restore checks data; Linux case-sensitive execution remains part of server migration.'}
    (directory/'verificacion-nombres.json').write_text(json.dumps(report,indent=2),encoding='utf-8')
    print(json.dumps(report,ensure_ascii=False))

if __name__=='__main__':main()
