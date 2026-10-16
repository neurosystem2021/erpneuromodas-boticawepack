import subprocess,pathlib
root=pathlib.Path.cwd()
repos={'frontend':root,'backend':pathlib.Path('C:/xampp/htdocs/boticav1')}
for label,repo in repos.items():
    def git(*args): return subprocess.check_output(['git','-c','safe.directory='+str(repo).replace('\\','/'),'-C',str(repo),*args])
    files=git('ls-tree','-r','--name-only','imceden').decode().splitlines()
    if label=='frontend':
        chosen=[p for p in files if p in ['src/views/reportes/ReporteSireMovimiento.vue','src/views/reportes/ReporteSireVentas.vue','src/views/reportes/Confrontacion.vue','src/components/CarritoConfrontacion.vue','src/components/BarcodeCameraScanner.vue','src/App.vue','src/router/index.js','package.json'] or p.startswith('src/assets/')]
    else:
        chosen=[p for p in files if p.startswith(('api/crm/','crm/src/','crm/dist/','docs/crm/','tests/crm/','data/crm/','resources/css/ventas-app','resources/js/ventas-app')) or p in ['api/index.php','api/kpi.php','api/tienda-flyers.php','api/tienda/index.php','config/crm.php','config/database.php','controllers/crm_feature_toggle.php','views/CRM.php','views/KpiDashboard.php','views/ReporteSireMovimiento.php','views/ReporteSireVentas.php','views/TiendaVirtual.php','views/VentasApp.php','views/lo_confrontacion.php','views/header.php','views/linker.php','tasks/crm_sync.php','tasks/crm_sync_whatsapp_contacts.php','migration_tienda_cliente_cuenta.sql','migration_product_images.sql','crm/package.json','crm/package-lock.json','crm/vite.config.js','crm/index.html'] or p.startswith(('resources/css/neurollantas-store','resources/css/tienda-flyer','resources/js/neurollantas-store','resources/js/tienda-flyer','resources/images/tienda/'))]
    for p in chosen:
        target=root/'integration'/'imceden'/label/p
        target.parent.mkdir(parents=True,exist_ok=True)
        target.write_bytes(git('show','imceden:'+p))
    print(label,len(chosen),'files staged from imceden')
