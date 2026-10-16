from pathlib import Path
import shutil
stage=Path('deploy-src/app-inventory')
root=Path('C:/xampp/htdocs/boticav1')
for file in ['views/VentasApp.php','api/imceden-modules.php']:
    p=stage/file;p.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(root/file,p)
p=stage/'views/VentasApp.php'
s=p.read_text(encoding='utf-8-sig')
card='''<button class="va-card va-inventory-card" onclick="showView('inventory')"><i class="fa fa-barcode"></i><b>Confrontación de inventario</b><small>Escanea, cuenta y revisa tu almacén</small><span class="va-inventory-chip">CONTEO FÍSICO</span></button>
        '''
s=s.replace('<button class="va-card va-stock-card"',card+'<button class="va-card va-stock-card"',1)
section='''<section class="va-view" data-view="inventory"><div class="va-inventory-shell"><div class="va-inventory-toolbar"><button type="button" onclick="showView('home')" aria-label="Volver al inicio"><i class="fa fa-arrow-left"></i></button><div><small>LOGÍSTICA · MODO APP</small><h2>INVENTARIO FÍSICO</h2><p>ESCANEA, CUENTA Y REVISA</p></div><a href="/views/lo_confrontacion.php?app=1" target="_blank" rel="noopener" aria-label="Abrir inventario en pantalla completa"><i class="fa fa-expand"></i></a></div><iframe id="inventoryAppFrame" class="va-inventory-frame" title="Confrontación de inventario" allow="camera 'self'" loading="lazy"></iframe></div></section>
    '''
s=s.replace('<section class="va-view" data-view="movementRegister">',section+'<section class="va-view" data-view="movementRegister">',1)
s=s.replace("name==='dashboard'||name==='movementRegister'","name==='dashboard'||name==='movementRegister'||name==='inventory'",1)
s=s.replace("if(name==='movementRegister')loadMovementRegister();","if(name==='movementRegister')loadMovementRegister();if(name==='inventory')loadInventoryApp();",1)
s=s.replace('function loadMovementRegister(){',"function loadInventoryApp(){const frame=$('inventoryAppFrame');if(frame&&!frame.getAttribute('src'))frame.src='/nuevo/index.html#/reporte/confrontacion'}\nfunction loadMovementRegister(){",1)
s=s.replace("const ventasAppModuleDefinitions={sale:","const ventasAppModuleDefinitions={inventory:{label:'Confrontación de inventario',icon:'barcode',help:'Cámara, conteo físico y confrontación'},sale:",1)
s=s.replace("if(action.includes(\"showView('stock')\"))return'stock';","if(action.includes(\"showView('inventory')\"))return'inventory';if(action.includes(\"showView('stock')\"))return'stock';",1)
s=s.replace('<link rel="stylesheet" href="../resources/css/ventas-app-modules.css?v=20260816">','<link rel="stylesheet" href="../resources/css/ventas-app-modules.css?v=20260816">\n<link rel="stylesheet" href="../resources/css/ventas-app-inventory.css?v=20261010">',1)
s += "\n<script>if(new URLSearchParams(location.search).get('view')==='inventory')window.addEventListener('DOMContentLoaded',()=>showView('inventory'));</script>\n"
p.write_text(s,encoding='utf-8')
p=stage/'api/imceden-modules.php';s=p.read_text(encoding='utf-8-sig').replace("'stock'=>1,'dashboard'=>1","'stock'=>1,'dashboard'=>1,'inventory'=>1").replace("'purchases','stock','dashboard');","'purchases','stock','dashboard','inventory');")
p.write_text(s,encoding='utf-8')
p=stage/'resources/css/ventas-app-inventory.css';p.parent.mkdir(parents=True,exist_ok=True)
p.write_text('''
.va-card.va-inventory-card{position:relative;background:linear-gradient(120deg,#123f51,#087f91 65%,#009f87)!important;border:1px solid #ffffff66!important;color:white!important;overflow:hidden}
.va-inventory-card>i{background:#e3fff5!important;color:#086e70!important;box-shadow:0 8px 22px #003e421c!important}.va-inventory-chip{display:block;width:max-content;margin-top:12px;padding:5px 10px;border-radius:20px;background:#ffffff20;border:1px solid #ffffff40;font-size:10px;letter-spacing:1px;font-weight:800}
.va-inventory-shell{overflow:hidden;border:1px solid #d5e7ec;border-radius:24px;background:#f4f9fc;box-shadow:0 12px 40px #123f5110}
.va-inventory-toolbar{display:flex;align-items:center;gap:16px;padding:20px 24px;background:linear-gradient(115deg,#123f51,#176879);color:#fff}.va-inventory-toolbar>div{flex:1}.va-inventory-toolbar h2{margin:4px 0;font-size:22px;letter-spacing:.3px}.va-inventory-toolbar small{color:#91e1d4;font-weight:800;letter-spacing:1.5px;font-size:10px}.va-inventory-toolbar p{margin:0;font-size:11px;color:#d8f1f2}.va-inventory-toolbar button,.va-inventory-toolbar a{display:grid;place-items:center;width:42px;height:42px;flex-shrink:0;background:#ffffff12;border:1px solid #ffffff30;border-radius:12px;color:#fff;text-decoration:none}
.va-inventory-frame{display:block;border:0;width:100%;height:calc(100dvh - 190px);min-height:620px;background:#f4f9fc}
@media(max-width:700px){.va-inventory-shell{border-radius:16px}.va-inventory-toolbar{padding:14px 12px;gap:10px}.va-inventory-toolbar h2{font-size:17px}.va-inventory-frame{height:calc(100dvh - 165px);min-height:520px}.va-inventory-toolbar p{font-size:10px}}
''',encoding='utf-8')
print('Inventory module ready')
