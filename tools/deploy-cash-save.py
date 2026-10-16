from pathlib import Path
import datetime,shutil
root=Path('integration/cash-save/api');target=Path('C:/xampp/htdocs/boticav1/api');backup=target.parent/('.cash-save-backup-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir()
for p in root.glob('*.php'):
 dest=target/p.name
 if dest.exists():shutil.copy2(dest,backup/p.name)
 shutil.copy2(p,dest)
print('Cash movement endpoint deployed; rollback backup:',backup.name)
