from pathlib import Path
import subprocess, re, shutil, hashlib, json
from datetime import datetime
source=Path('C:/xampp/htdocs/boticawebpack')
runtime=Path('C:/xampp/htdocs/boticav1')
for repo in [source,runtime]:
 r=subprocess.run(['git','-c','safe.directory='+source.as_posix(),'-c','safe.directory='+runtime.as_posix(),'-C',str(repo),'branch','--show-current'],capture_output=True,text=True,check=True)
 if r.stdout.strip()!='imceden': raise SystemExit('Abort: both checkouts must remain on imceden.')
p=runtime/'config/database.local.php'
before=p.read_bytes()
if len(re.findall(rb"'dbname'\s*=>\s*'fact_wuanukomix'",before))!=1: raise SystemExit('Abort: unexpected database configuration.')
# Preserve credentials and all other connection settings byte for byte.
after=re.sub(rb"('dbname'\s*=>\s*)'fact_wuanukomix'",rb"\1'fact_imceden'",before,count=1)
after=re.sub(rb"^[ \t]*'wuanuko_multiissuer'\s*=>\s*true,?[^\r\n]*(?:\r?\n)?",b'',after,flags=re.M)
after=after.replace(b'// Private configuration for the Wuanuko Mix installation.',b'// Private configuration for the IMCEDEN installation.')
backup=Path('C:/xampp/accounting-private')/('imceden-conexion-'+datetime.now().strftime('%Y%m%d-%H%M%S'))
backup.mkdir(parents=True,exist_ok=False)
shutil.copy2(p,backup/'database.local.php')
manifest={'previous_database':'fact_wuanukomix','database':'fact_imceden','before_sha256':hashlib.sha256(before).hexdigest(),'after_sha256':hashlib.sha256(after).hexdigest(),'database_data_modified':False}
(backup/'manifest.json').write_text(json.dumps(manifest,indent=2),encoding='utf-8')
p.write_bytes(after)
if p.read_bytes()!=after: raise SystemExit('Configuration verification failed.')
print(json.dumps({'backup':str(backup),**manifest}))
