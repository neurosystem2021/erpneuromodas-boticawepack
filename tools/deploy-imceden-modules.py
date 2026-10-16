import pathlib,shutil,datetime,subprocess,re
root=pathlib.Path(__file__).resolve().parent.parent;stage=root/'integration/imceden/backend';target=pathlib.Path('C:/xampp/htdocs/boticav1');stamp=datetime.datetime.now().strftime('%Y%m%d-%H%M%S');backup=target/('.imceden-modules-backup-'+stamp)
exclude={'api/index.php','views/header.php','views/linker.php','views/lo_confrontacion.php','.gitignore'}
files=[p for p in stage.rglob('*') if p.is_file() and p.relative_to(stage).as_posix() not in exclude]
# Validate every PHP before touching the live system.
for p in files:
 if p.suffix=='.php':
  r=subprocess.run(['C:/xampp/php/php.exe','-l',str(p)],capture_output=True,text=True)
  if r.returncode:raise RuntimeError(r.stdout+r.stderr)
backup.mkdir()
for p in files:
 rel=p.relative_to(stage);dest=target/rel
 if dest.exists():b=backup/rel;b.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(dest,b)
 dest.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,dest)
# Keep local secrets outside version control and HTTP access.
ign=target/'.gitignore';text=ign.read_text(encoding='utf-8-sig') if ign.exists() else ''
for line in ['config/*.local.php','config/*.local.json','storage/','whatsapp-gateway/node_modules/','whatsapp-gateway/tokens/','whatsapp-gateway/sessions/']:
 if line not in text:text+='\n'+line
ign.write_text(text,encoding='utf-8')
(target/'config/.htaccess').write_text('Require all denied\nDeny from all\n',encoding='utf-8')
api=target/'api/index.php';text=api.read_text(encoding='utf-8-sig');shutil.copy2(api,backup/'api-index.php')
if "require_once __DIR__ . '/imceden-modules.php';" not in text:
 marker="$app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], '/{routes:.+}'"
 if marker not in text:raise RuntimeError('API registration boundary missing')
 text=text.replace(marker,"require_once __DIR__ . '/imceden-modules.php';\n\n"+marker,1);api.write_text(text,encoding='utf-8')
header=target/'views/header.php';text=header.read_text(encoding='utf-8-sig');shutil.copy2(header,backup/'header.php')
if '/views/ReporteSireMovimiento.php' not in text:text=text.replace('REGISTRO DE MOVIMIENTO </a></li>','REGISTRO DE MOVIMIENTO </a></li>\n<li><a href="/views/ReporteSireMovimiento.php">SIRE COMPRAS</a></li>',1)
if '/views/VentasApp.php' not in text:text=text.replace('Punto de venta</a></li>','Punto de venta</a></li>\n<li><a href="/views/VentasApp.php">VENTAS APP</a></li>\n<li><a href="/views/TiendaVirtual.php">TIENDA VIRTUAL</a></li>\n<li><a href="/views/ReporteSireVentas.php">SIRE VENTAS</a></li>',1)
if '/views/KpiDashboard.php' not in text:
 items=''.join('<li><a href="/views/KpiDashboard.php'+('#'+key if key else '')+'">'+label+'</a></li>\n' for key,label in [('', 'DASHBOARD KPI'),('ventas','KPI VENTAS'),('productos','KPI PRODUCTOS'),('inventario','KPI INVENTARIO'),('vendedores','KPI VENDEDORES'),('clientes','KPI CLIENTES'),('sucursales','KPI SUCURSALES'),('alertas','KPI ALERTAS')])
 text=text.replace('Dashboard</a></li>','Dashboard</a></li>\n'+items,1)
if '/views/CRM.php' not in text:text=text.replace('<ul class="nav navbar-nav">','<ul class="nav navbar-nav">\n<li><a href="/views/CRM.php"><i class="fa fa-comments"></i> CRM</a></li>',1)
header.write_text(text,encoding='utf-8')
# Reuse existing module permissions for equivalent additions; CRM requires its own grant or administrator.
v=target/'views/validateUser.php';text=v.read_text(encoding='utf-8-sig');shutil.copy2(v,backup/'validateUser.php')
if 'IMCEDEN MODULE PERMISSIONS' not in text:
 text=text.replace("$permisos = $_SESSION['permisos'];","""$permisos = $_SESSION['permisos'] ?? array();
// IMCEDEN MODULE PERMISSIONS: additions inherit only their matching existing scope.
$imcedenPermissionParents = array('ReporteSireMovimiento.php'=>'ReporteRegMovimiento.php','ReporteSireVentas.php'=>'ReporteRegistroVentas.php','VentasApp.php'=>'V_VentaForm.php','TiendaVirtual.php'=>'V_VentaForm.php','KpiDashboard.php'=>'Dashboard.php');
if (!isset($permisos[$modulo]) && isset($imcedenPermissionParents[$modulo]) && isset($permisos[$imcedenPermissionParents[$modulo]])) $permisos[$modulo] = $permisos[$imcedenPermissionParents[$modulo]];
""",1);v.write_text(text,encoding='utf-8')
print('Deployed',len(files),'module files. Backup:',backup)
r=subprocess.run(['C:/xampp/php/php.exe',str(root/'tools/migrate-imceden-modules.php')]);r.check_returncode()
