from pathlib import Path
import re
root=Path('integration/central-config');live=Path('C:/xampp/htdocs/boticav1');ref=Path('integration/central-reference')
# Connect the source model to NST's database provider.
p=root/'models/EmpresaConfig.php';s=p.read_text(encoding='utf-8');s=s.replace('<?php','<?php\nrequire_once __DIR__ . "/../config/database.php";',1);p.write_text(s,encoding='utf-8')
for p in (root/'views').glob('*.php'):
 s=p.read_text(encoding='utf-8')
 if p.name!='codigoBarra.php':
  s=s.replace('<?php','<?php\nif (session_status() !== PHP_SESSION_ACTIVE) session_start();\nif (empty($_SESSION["user"]) || (isset($_SESSION["expire"]) && time() > $_SESSION["expire"])) { header("Location: /index.php"); exit; }',1)
 if p.name=='Seg_PlanesMenu.php':s=s.replace(':scope>li.navli>a[href]',':scope>li>a[href]')
 if p.name=='Seg_CorreoConfig.php':
  s=s.replace("include_once 'linker.php';", "if (strtolower(trim((string)($_SESSION['user']??''))) !== 'superadmin') { http_response_code(403); exit('Acceso solo para SUPERADMIN.'); }\ninclude_once 'linker.php';",1)
  s=s.replace('onclick="saveConfig(false)"','onclick="saveConfig(false).catch(e=>showStatus(e.message,true))"')
 if p.name=='codigoBarra.php':s=s.replace("$etiquetaLogo = 'resources/images/eden-company-monochrome.png';", "$etiquetaLogo = empresaConfigValor($etiquetaConfig, 'etiqueta_logo', empresaConfigValor($etiquetaConfig, 'logo_ticket', 'resources/images/logo-ticket.svg'));")
 p.write_text(s,encoding='utf-8')
p=root/'components/CodigoBarra.js';s=p.read_text(encoding='utf-8').replace('IMPORT COMPANY EDEN E.I.R.L.','NEUROSYSTEM');p.write_text(s,encoding='utf-8')
# Apply source plans, validity, announcements and floating payment panel without replacing NST navigation.
source=(ref/'views/header.php').read_text(encoding='utf-8');top=source[len('<?php'):source.index('define("SITE_URL"')]
start=source.index('<script>',source.index('</nav>'));end=source.index('<div id="loading">',start);features=source[start:end].replace(':scope>li.navli>a[href]',':scope>li>a[href]').replace(':scope>li.navli',':scope>li')
header=(live/'views/header.php').read_text(encoding='utf-8');header=header.replace('<?php','<?php\nif (session_status() !== PHP_SESSION_ACTIVE) session_start();\n'+top,1)
constants={'EmpresaConfig':'EmpresaConfig.php','ComunicadosVigencia':'Seg_ComunicadosVigencia.php','ConfigurarEtiquetas':'Seg_ConfigurarEtiquetas.php','ConfigurarCorreo':'Seg_CorreoConfig.php','PlanesMenu':'Seg_PlanesMenu.php'}
header=header.replace('define("SITE_URL",', '\n'.join('define("'+k+'", "/views/'+v+'");' for k,v in constants.items())+'\ndefine("SITE_URL",',1)
header=header.replace('<nav class="navbar navbar-default erp-topbar">','<nav id="navBar" class="navbar navbar-default erp-topbar">',1)
needle='<li><a href="<?php echo V_Usuario; ?>">Usuarios</a></li>'
items='\n<?php if (strtolower(trim((string)($_SESSION["user"]??""))) === "superadmin"): ?>\n'+''.join('<li class="navli" '+('data-plan-manager="1"' if k=='PlanesMenu' else '')+'><a href="/views/'+constants[k]+'"><i class="fa fa-'+icon+'"></i> '+label+'</a></li>\n' for k,label,icon in [('EmpresaConfig','Datos de empresa','building-o'),('ComunicadosVigencia','Comunicados y vigencia','bell'),('ConfigurarEtiquetas','Configurar etiquetas','tags'),('ConfigurarCorreo','Configuración de correo','envelope'),('PlanesMenu','Planes y módulos','th-large')])+'<?php endif; ?>'
assert needle in header;header=header.replace(needle,needle+items,1)
header=header.replace('</nav>','</nav>\n'+features,1)
(root/'views/header.php').write_text(header,encoding='utf-8')
# Shared API company constants keep NST's current values as fallbacks.
api=(live/'api/index.php').read_text(encoding='utf-8');sourceapi=(ref/'api/index.php').read_text(encoding='utf-8')
a=sourceapi.index('$empresaFiscal = array();');b=sourceapi.index("define('NRO_DOCUMENTO_EMPRESA'",a);provider=sourceapi[a:b]
api=api.replace("define('NRO_DOCUMENTO_EMPRESA'",provider+"define('NRO_DOCUMENTO_EMPRESA'",1)
keys={'NRO_DOCUMENTO_EMPRESA':'RUC','RAZON_SOCIAL_EMPRESA':'RAZONSOCIAL','NOMBRE_COMERCIAL_EMPRESA':'nombre_comercial','CODIGO_UBIGEO_EMPRESA':'UBIGEO','DIRECCION_EMPRESA':'DIRECCION','DEPARTAMENTO_EMPRESA':'DEPARTAMENTO','PROVINCIA_EMPRESA':'PROVINCIA','DISTRITO_EMPRESA':'DISTRITO','TELEFONOS_EMPRESA':'CELULAR','USUARIO_SOL_EMPRESA':'usuario_sol','PASS_SOL_EMPRESA':'clave_sol'}
for constant,key in keys.items():
 pattern=r"define\('"+constant+r"',\s*([^;]+)\);"
 api,count=re.subn(pattern,lambda m:"define('"+constant+"', $empresaDato('"+key+"', "+m[1]+"));",api,count=1)
api=api.replace("define('TIPO_PROCESO', '01');", "define('TIPO_PROCESO', $empresaDato('sunat_entorno', 'FE_PRODUCCION') === 'FE_PRODUCCION' ? '01' : '03');",1)
# Port the source validity guard for point of sale and APK.
a=sourceapi.index("    $esVentaDesdeApp =",sourceapi.index("$app->post('/ventas'"));b=sourceapi.index("    $vendedor =",a);guard=sourceapi[a:b]
api=api.replace("$app->post('/ventas', function (Request $request, Response $response) {", "$app->post('/ventas', function (Request $request, Response $response) {\n"+guard,1)
(root/'api').mkdir(exist_ok=True);(root/'api/index.php').write_text(api,encoding='utf-8')
print('Staged five modules and NST integration dependencies')
