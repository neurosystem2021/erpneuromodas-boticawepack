from pathlib import Path
import shutil,datetime
root=Path('integration/receipt-finish');target=Path('C:/xampp/htdocs/boticav1');backup=target/('.receipt-finish-backup-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir()
for p in root.rglob('*.php'):
 rel=p.relative_to(root);dest=target/rel
 if dest.exists():old=backup/rel;old.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(dest,old)
 dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,dest)
print('Receipt templates deployed. Backup:',backup.name)
