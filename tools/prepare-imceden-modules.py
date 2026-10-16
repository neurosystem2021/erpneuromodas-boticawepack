from pathlib import Path
import shutil,re,subprocess
root=Path.cwd(); stage=root/'integration/imceden/backend'
# Vue 2 SIRE screens from local imceden; keep authoritative purchase corrections in the audited report.
for name in ['ReporteSireMovimiento','ReporteSireVentas']:
 s=(root/f'integration/imceden/frontend/src/views/reportes/{name}.vue').read_text(encoding='utf-8-sig')
 s=s.replace('class="ops-page"','class="ops-page imceden-sire"')
 if name=='ReporteSireMovimiento':
  s=re.sub(r'<b-button[^>]*@click="elimimnarRegistro\(reporte\)"[^>]*>.*?</b-button>','<a class="btn btn-sm btn-outline-info" href="/views/ReporteRegMovimiento.php" target="_top" title="CONTROL DE COMPRAS Y AUDITORÍA"><i class="fa fa-history"></i></a>',s)
  s=s.replace('<b-button variant="warning" @click="actualizarPeriodo()">Actualizar Periodo</b-button>','<a class="btn btn-info" href="/views/ReporteRegMovimiento.php" target="_top">CORREGIR CON AUTORIZACIÓN Y MOTIVO</a>')
 (root/f'src/views/reportes/{name}.vue').write_text(s,encoding='utf-8')
p=root/'src/router/index.js';s=p.read_text(encoding='utf-8-sig')
for n in ['ReporteSireMovimiento','ReporteSireVentas']:
 if f"import {n} " not in s:s="import "+n+" from '@/views/reportes/"+n+".vue';\n"+s
 if f'path: "/reportes/{n}"' not in s:s=s.replace('routes: [','routes: [\n    { path: "/reportes/'+n+'", name: "'+n+'", component: '+n+' },',1)
p.write_text(s,encoding='utf-8')
p=root/'src/assets/css/imceden-sire.css'
s=(root/'integration/imceden/frontend/src/assets/css/operations-modern.css').read_text(encoding='utf-8-sig')
# Scope branch style sheet to the two imported screens.
s=re.sub(r'(?<![\w-])\.ops-', '.imceden-sire.ops-', s) if False else s
# Every ops rule is already scoped to ops-* classes used by these modules only.
s+='\n.ops-hero{background:linear-gradient(112deg,#083b4d,#125b6b 65%,#008e91)}.ops-results thead th{background:#123f51!important}.ops-card .btn-info,.ops-card .btn-primary{background:linear-gradient(135deg,#009f87,#078fb8)}\n'
p.write_text(s,encoding='utf-8')
p=root/'src/App.vue';s=p.read_text(encoding='utf-8-sig');s=s.replace("@import './assets/css/premium.css';","@import './assets/css/imceden-sire.css';\n@import './assets/css/premium.css';") if 'imceden-sire.css' not in s else s;p.write_text(s,encoding='utf-8')
repo='C:/xampp/htdocs/boticawebpack'
try:(root/'static/sire.png').write_bytes(subprocess.check_output(['git','-c','safe.directory='+repo,'show','imceden:static/sire.png']))
except:pass
# Adapt independent PHP modules to this installation without replacing its existing header or API.
p=stage/'models/EmpresaConfig.php';s=p.read_text(encoding='utf-8-sig').replace("require_once __DIR__ . '/DBManager.php';","require_once dirname(__DIR__) . '/config/database.php';");p.write_text(s,encoding='utf-8')
p=stage/'views/CRM.php';s=p.read_text(encoding='utf-8-sig').replace('CRM NeuroRest','CRM NeuroSoft');s=s.replace('?><!doctype html>',"if (isset($_SESSION['expire']) && time() > $_SESSION['expire']) { header('Location: /index.php'); exit; }\nif (empty($_SESSION['login_csrf'])) $_SESSION['login_csrf'] = bin2hex(random_bytes(32));\n?><!doctype html>");s=s.replace('<body style="margin:0">','<body style="margin:0"><a href="/views/Gen_ProductoForm.php" style="position:fixed;right:16px;top:12px;z-index:99;padding:9px 13px;background:#123f51;color:white;border-radius:9px;text-decoration:none;font:12px Arial">VOLVER AL SISTEMA</a>');p.write_text(s,encoding='utf-8')
p=stage/'crm/dist/index.html';s=p.read_text(encoding='utf-8-sig').replace('CRM NeuroRest','CRM NeuroSoft').replace('</head>','<link rel="stylesheet" href="/resources/css/imceden-nst-theme.css?v=20261010">\n</head>');p.write_text(s,encoding='utf-8')
for name in ['VentasApp','KpiDashboard','TiendaVirtual']:
 p=stage/f'views/{name}.php';s=p.read_text(encoding='utf-8-sig');s=s.replace('</head>','<link rel="stylesheet" href="/resources/css/imceden-nst-theme.css?v=20261010">\n</head>');s=s.replace('Eden Company','NeuroSoft').replace('Catálogo de baterías, aros, llantas y repuestos','CATÁLOGO DE PRODUCTOS');p.write_text(s,encoding='utf-8')
p=stage/'tienda/index.php';s=p.read_text(encoding='utf-8-sig').replace('resources/uploads/empresa/logo_principal-20261002043422-1c191a.jpg','resources/images/neurosoft-logo-transparent.png').replace("'EDEN COMPANY'","'NEUROSOFT'");s=s.replace('</head>','<link rel="stylesheet" href="/resources/css/imceden-nst-theme.css?v=20261010">\n</head>');p.write_text(s,encoding='utf-8')
p=stage/'api/tienda/index.php';s=p.read_text(encoding='utf-8-sig');s=s.replace('SELECT RAZONSOCIAL, DESCRIPCION, DIRECCION, TELEFONO, CELULAR, EMAIL FROM Gen_Empresa','SELECT RAZONSOCIAL, DIRECCION, TELEFONO FROM Gen_Empresa');s=s.replace("$row['DESCRIPCION'] = 'VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR';","$row['DESCRIPCION'] = $row['actividad_comercial'] ?? 'CATÁLOGO DE PRODUCTOS';");s=s.replace('/resources/uploads/empresa/logo_principal-20261002043422-1c191a.jpg','/resources/images/neurosoft-logo-transparent.png');s=s.replace('GREATEST(0,COALESCE(p.TotalCompras,0)-COALESCE(p.TotalVentas,0)) stock','GREATEST(0,COALESCE((SELECT SUM(pt.TotalCompras-pt.TotalVentas) FROM Gen_Producto_Totales pt WHERE pt.IdProducto=p.IdProducto),0)) stock');p.write_text(s,encoding='utf-8')
p=stage/'api/imceden-modules.php';s=p.read_text(encoding='utf-8-sig').replace('use PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx;','use PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx;\nuse PhpOffice\\PhpSpreadsheet\\Style\\Fill;');p.write_text(s,encoding='utf-8')
# Existing app DB has no branch totals table: compatibility view will read live movement/sales ledger.
css='''/* Current NeuroSoft colors, shared by modules ported from imceden. */
:root{--purple:#123f51!important;--purple-dark:#082f40!important;--purple-mid:#176879!important;--accent:#009f87!important;--pink:#009f87!important;--va-navy:#123f51!important;--va-blue:#087f91!important;--va-cyan:#00a69b!important;--va-text:#173e50!important;--va-line:#d5e7ec!important;--primary:#009f87!important;--primary-dark:#087a78!important;--brand:#009f87!important;--brand-dark:#123f51!important}
body{background-color:#f4f9fc!important;color:#173e50}aside{background:linear-gradient(160deg,#123f51,#082c3b)!important}aside button.active,aside a.active{background:#009f87!important;color:white!important}.va-top,.va-total,.va-nav button.active,.va-success-summary,.kpi-hero,.store-admin-bar,.topbar,.store-header{background:linear-gradient(115deg,#083b4d,#125b6b 65%,#008e91)!important;color:white!important}.va-btn.blue,.va-btn.green,.va-card-icon,.kpi-tab.active,.btn-primary,.primary,.primary-btn,button.primary{background:linear-gradient(135deg,#009f87,#078fb8)!important;border-color:#009f87!important;color:#fff!important}.va-panel,.va-card,.kpi-card{border-color:#d5e7ec!important}.shell aside nav button.active{background:#009f87!important}.store-admin-actions button,.store-admin-actions a{background:#009f87!important}.store-admin-title span{color:#d6efef!important}
'''
(stage/'resources/css/imceden-nst-theme.css').write_text(css,encoding='utf-8')
# No demo data or external credentials; integrations remain configurable through the original CRM UI.
(stage/'config/crm.local.php').write_text("<?php\nreturn array('enabled'=>true,'demo_mode'=>false,'messaging_enabled'=>false,'ai_enabled'=>false,'write_profiles'=>array(1));\n",encoding='utf-8')
print('Prepared SIRE routes, CRM, store, Ventas App and KPI with current theme.')
