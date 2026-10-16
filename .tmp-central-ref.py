from pathlib import Path
import subprocess
for name in ['views/header.php','views/linker.php','api/index.php','config/database.php']:
 p=Path('integration/central-reference')/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C','C:/xampp/htdocs/boticav1','show','imceden:'+name]))
