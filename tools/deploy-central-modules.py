from pathlib import Path
import shutil,datetime,subprocess
root=Path('integration/central-config');target=Path('C:/xampp/htdocs/boticav1');backup=target/('.central-config-backup-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir()
files=[p for p in root.rglob('*') if p.is_file()]
for p in files:
 rel=p.relative_to(root);dest=target/rel
 if dest.exists():old=backup/rel;old.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(dest,old)
 dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,dest)
print('Deployed',len(files),'central configuration files. Backup:',backup.name)
