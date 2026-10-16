from pathlib import Path
import subprocess
for name in ['components/CodigoBarra.js','components/CodigoBarraLista.js','views/codigoBarra.php','resources/css/etiqueta-moderna.css']:
 p=Path('integration/central-config')/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C','C:/xampp/htdocs/boticav1','show','imceden:'+name]))
