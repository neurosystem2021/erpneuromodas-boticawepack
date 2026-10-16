from pathlib import Path
import shutil
root=Path('C:/xampp/htdocs/boticav1')
stage=Path('deploy-src/mobile-inventory')
for name in ['views/VentasApp.php','resources/css/ventas-app-inventory.css']:
    p=stage/name;p.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(root/name,p)
p=stage/'views/VentasApp.php';s=p.read_text(encoding='utf-8-sig').replace('title="Confrontación de inventario" allow="camera \'self\'"','title="Confrontación de inventario" allow="camera \'self\'; microphone \'self\'"').replace("frame.src='/nuevo/index.html#/reporte/confrontacion'","frame.src='/nuevo/index.html?inventory=mobile-red#/reporte/confrontacion'").replace('ventas-app-inventory.css?v=20261010','ventas-app-inventory.css?v=20261010-red2');p.write_text(s,encoding='utf-8')
p=stage/'resources/css/ventas-app-inventory.css'
s=p.read_text(encoding='utf-8').replace('linear-gradient(120deg,#123f51,#087f91 65%,#009f87)','linear-gradient(120deg,#831b32,#c51d39 65%,#e24558)').replace('linear-gradient(115deg,#123f51,#176879)','linear-gradient(115deg,#831b32,#c51d39)').replace('#91e1d4','#ffced8').replace('#e3fff5','#fff0f4').replace('#086e70','#a31c36').replace('#d8f1f2','#ffe3e9').replace('#f4f9fc','#fff8fa')
s+='\n@media(max-width:700px){.va-view[data-view="inventory"]{margin:0!important;padding:0!important}.va-inventory-toolbar h2{font-size:15px!important}.va-inventory-toolbar{padding:12px!important}.va-inventory-frame{height:calc(100dvh - 150px)!important;min-height:480px!important}.va-inventory-shell{border-radius:14px!important}.va-inventory-toolbar button,.va-inventory-toolbar a{width:38px!important;height:38px!important}}\n'
p.write_text(s,encoding='utf-8')
print('Red mobile shell and microphone permission staged')
