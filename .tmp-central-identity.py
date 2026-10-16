from pathlib import Path
import re
root=Path('integration/central-config');live=Path('C:/xampp/htdocs/boticav1')
p=root/'api/index.php';s=p.read_text(encoding='utf-8');s,count=re.subn(r"define\('PAS_FIRMA',\s*([^;]+)\);",lambda m:"define('PAS_FIRMA', $empresaDato('clave_certificado', "+m[1]+"));",s,count=1);assert count==1;p.write_text(s,encoding='utf-8')
p=root/'index.php';s=(live/'index.php').read_text(encoding='utf-8');s='''<?php
require_once __DIR__ . '/models/EmpresaConfig.php';
$nstLoginConfig = empresaConfigObtener();
$nstLoginLogo = empresaConfigValor($nstLoginConfig,'logo_principal','');
?>
'''+s
s=s.replace('src="/resources/images/neurosoft-logo-transparent.png"', '''src="<?php echo $nstLoginLogo !== '' ? '/' . htmlspecialchars(ltrim($nstLoginLogo,'/'),ENT_QUOTES,'UTF-8') : '/resources/images/neurosoft-logo-transparent.png'; ?>"''',1)
s=s.replace('src="/resources/images/neurosoft-company-logo-white-soft.png"', '''src="<?php echo $nstLoginLogo !== '' ? '/' . htmlspecialchars(ltrim($nstLoginLogo,'/'),ENT_QUOTES,'UTF-8') : '/resources/images/neurosoft-company-logo-white-soft.png'; ?>"''',1)
p.write_text(s,encoding='utf-8')
print('NST login connected to company logo; signing password retains original fallback')
