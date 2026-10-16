from pathlib import Path
import subprocess
root=Path.cwd();backend=Path('C:/xampp/htdocs/boticav1')
for label,repo,files in [('frontend',root,['src/views/Remision.vue','src/views/RemisionLista.vue','src/views/RemisionVer.vue','src/views/reportes/RemisionListado.vue','src/legacy-migrations/remision-modern.js','src/legacy-migrations/remision-modern.css','src/router/index.js']),('backend',backend,['api/index.php','api/sunat/controller/controller_cpe_guia.php','api/sunat/plugins/dompdf/guia_remision.php','api/sunat/plugins/dompdf/remision.php','data/remision','imprimir/guiaremision.php','imprimir/remision.php','imprimir/remisionticket.php','views/remision.php','views/remisionLista.php'])]:
 for name in files:
  out=root/'integration/remision-imceden'/label/name;out.parent.mkdir(parents=True,exist_ok=True)
  out.write_bytes(subprocess.check_output(['git','-c','safe.directory='+repo.as_posix(),'-C',str(repo),'show','imceden:'+name]))
 print(label,len(files),'staged')
