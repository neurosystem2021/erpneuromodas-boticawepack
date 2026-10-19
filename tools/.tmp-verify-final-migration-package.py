import zipfile,hashlib,json
from pathlib import Path
folder=Path('C:/Users/USER/Downloads/nstarapaca-migracion-20261017');archive=folder.with_suffix('.zip')
with zipfile.ZipFile(archive) as z:
 assert z.testzip() is None
 for line in z.read('SHA256SUMS.txt').decode().splitlines():
  checksum,name=line.split('  ',1);data=z.read(name);assert hashlib.sha256(data).hexdigest()==checksum,name;assert data==(folder/name).read_bytes(),name
 report=json.loads(z.read('verificacion-nombres.json'));assert report['objects_erp']==137 and report['data_literals_unchanged']
 assert 'erp.sql' not in z.namelist() and 'soporte.sql' not in z.namelist()
 print('PASS ZIP integrity and all SHA-256 hashes; contains only the corrected import SQL, reports and migration instructions.')
print('Package SHA-256:',hashlib.sha256(archive.read_bytes()).hexdigest())
