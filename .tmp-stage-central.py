from pathlib import Path
import subprocess
root=Path('integration/central-config');names=['views/EmpresaConfig.php','views/Seg_ComunicadosVigencia.php','views/Seg_ConfigurarEtiquetas.php','views/Seg_CorreoConfig.php','views/Seg_PlanesMenu.php','controllers/ComunicadosVigenciaGuardar.php','controllers/EmpresaConfigGuardar.php','controllers/EmpresaConfigPublica.php','controllers/EtiquetasConfigGuardar.php','controllers/PlanesMenuGuardar.php','models/EmpresaConfig.php']
for name in names:
 p=root/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C','C:/xampp/htdocs/boticav1','show','imceden:'+name]))
