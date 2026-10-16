from pathlib import Path
import subprocess
root=Path('integration/central-config')
files=subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C','C:/xampp/htdocs/boticav1','grep','-l','EmpresaConfig','imceden','--','imprimir','api/sunat/plugins/dompdf']).decode().splitlines()
for name in files:
 name=name.split(':',1)[1]
 if '.bak' in name:continue
 p=root/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C','C:/xampp/htdocs/boticav1','show','imceden:'+name]))
p=root/'api/index.php';s=p.read_text(encoding='utf-8');s=s.replace('$empresaFiscal = array();',"require_once __DIR__ . '/../config/database.php';\n$empresaFiscal = array();",1);p.write_text(s,encoding='utf-8')
p=root/'views/header.php';s=p.read_text(encoding='utf-8');s=s.replace('<li><a href="/views/CRM.php">','<li class="crm-nav-item"><a href="/views/CRM.php">',1)
s=s.replace('>NEUROSOFT</a>', '''><?php if (!empty($headerEmpresaConfig['meta']['logo_principal'])): ?><img src="/<?php echo htmlspecialchars(ltrim($headerLogoEmpresa,'/'),ENT_QUOTES,'UTF-8'); ?>" alt="<?php echo htmlspecialchars(empresaConfigValor($headerEmpresaConfig,'nombre_comercial','NEUROSOFT'),ENT_QUOTES,'UTF-8'); ?>" style="max-width:150px;max-height:42px;object-fit:contain"><?php else: ?>NEUROSOFT<?php endif; ?></a>''',1)
p.write_text(s,encoding='utf-8')
p=root/'views/codigoBarra.php';s=p.read_text(encoding='utf-8').replace('IMPORT COMPANY EDEN E.I.R.L.','NEUROSYSTEM');p.write_text(s,encoding='utf-8')
print('Company identity dependencies staged for ticket and PDF printing')
