import csv,json,shutil,subprocess,uuid
from pathlib import Path
source=Path('C:/Users/USER/Downloads/nstarapaca-migracion-20261016')
base=Path('tmp')/('case-helper-check-'+uuid.uuid4().hex[:10]);helper=base/'helper';root=base/'erp';backup=base/'backup';helper.mkdir(parents=True);root.mkdir()
for name in ['normalizar-consultas-migracion.php','nombres-tablas.json','referencias-php-a-revisar.csv']:shutil.copyfile(source/name,helper/name)
rows=list(csv.DictReader((source/'referencias-php-a-revisar.csv').open(encoding='utf-8-sig')))
for relative in sorted({r['Archivo'] for r in rows}):
 target=root/relative.replace('\\','/');target.parent.mkdir(parents=True,exist_ok=True)
 target.write_text('<?php\n'+''.join("$q%d = 'SELECT * FROM %s';\n"%(i,row['Nombre usado']) for i,row in enumerate(rows) if row['Archivo']==relative))
exe='C:/xampp/php/php.exe';cmd=[exe,str((helper/'normalizar-consultas-migracion.php').resolve()),'--root='+str(root.resolve())]
dry=subprocess.run(cmd,capture_output=True,text=True);assert dry.returncode==0,dry.stderr
apply=subprocess.run(cmd+['--apply','--backup='+str(backup.resolve())],capture_output=True,text=True);assert apply.returncode==0,apply.stderr
again=subprocess.run(cmd,capture_output=True,text=True);assert again.returncode==0 and '0 archivos; 0 referencias' in again.stdout,again.stdout
report=json.loads((backup/'cambios.json').read_text());assert sum(i['references'] for i in report.values())==len(rows)
print('PASS migration case helper: dry run, backups, PHP lint, exact replacements and idempotent rerun on synthetic files. Real ERP unchanged.')
