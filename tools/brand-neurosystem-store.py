from pathlib import Path
import shutil

source=Path('C:/xampp/htdocs/boticav1')
stage=Path('deploy-src/neurosystem-store')
files=['tienda/index.php','views/TiendaVirtual.php','resources/js/neurollantas-store.js','resources/css/neurollantas-store.css']
for name in files:
    target=stage/name
    target.parent.mkdir(parents=True,exist_ok=True)
    shutil.copy2(source/name,target)

p=stage/'resources/js/neurollantas-store.js'
s=p.read_text(encoding='utf-8-sig').replace('EDEN COMPANY','NEUROSYSTEM').replace('Eden Company','NEUROSYSTEM').replace('VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR','COMPUTADORAS · LAPTOPS · ACCESORIOS')
s=s.replace("if (title) title.textContent = company.RAZONSOCIAL || company.nombre_comercial || 'NEUROSYSTEM';","if (title) title.textContent = 'NEUROSYSTEM';")
p.write_text(s,encoding='utf-8')

for name in ['tienda/index.php','views/TiendaVirtual.php']:
    p=stage/name
    s=p.read_text(encoding='utf-8-sig')
    s=s.replace('</head>','<link rel="stylesheet" href="/resources/css/neurosystem-store.css?v=20261010-3">\n</head>')
    s=s.replace('neurollantas-store.js?v=','neurollantas-store.js?v=20261010-tech-')
    s=s.replace('VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR','COMPUTADORAS · LAPTOPS · ACCESORIOS')
    s=s.replace('Prenda disponible','Producto disponible').replace('Buscando prendas','Buscando productos').replace('No encontramos prendas','No encontramos productos').replace('Conoce esta prenda','Conoce este producto')
    s=s.replace("$('brand').textContent=business","$('brand').textContent='NEUROSYSTEM'")
    s=s.replace("$('tagline').textContent=company.DESCRIPCION||$('tagline').textContent","$('tagline').textContent='COMPUTADORAS · LAPTOPS · ACCESORIOS'")
    # Called both when binding a gallery image and from an inline error handler.
    s=s.replace('function safeImage(img){img.onerror=()=>{img.onerror=null;img.src=FALLBACK}}','function safeImage(img){if(img.complete && img.naturalWidth===0){img.onerror=null;img.src=FALLBACK}else{img.onerror=function(){this.onerror=null;this.src=FALLBACK}}}')
    s=s.replace('producto-sin-imagen.svg?v=20261001-5','producto-sin-imagen.svg?v=20261010-tech')
    p.write_text(s,encoding='utf-8')

# Translate the entire store-only palette, including hardcoded gradients.
p=stage/'resources/css/neurollantas-store.css'
s=p.read_text(encoding='utf-8-sig')
import re
colors={'#075247':'#c81927','#061522':'#ffffff','#16c8f4':'#ed2638','#ffad00':'#dc1c2e','#fff3d1':'#fff0f1','#ff7a00':'#e62132','#f04438':'#bb1525','#0b2636':'#282b32','#607986':'#747781','#d8e8ed':'#eadfe1','#08283a':'#33333a','#0d8f7c':'#e72a3b','#17495a':'#4c3338','#eef8f8':'#fff4f5','#cde5e8':'#efd9dd','#0c7465':'#e12c3e','#eaf5f6':'#fff5f6','#087e75':'#bd192a','#e3f5f1':'#fff0f2','#f1f8f8':'#fff7f8','#ff9d00':'#d82334','#ff4b3b':'#ee3042','#cfe5eb':'#5c454b','#31564f':'#49383c'}
s=re.sub(r'#[0-9a-fA-F]{6}',lambda m:colors.get(m.group().lower(),m.group()),s)
p.write_text(s,encoding='utf-8')

css='''
:root{--purple:#cf1d30!important;--purple-dark:#9e1524!important;--purple-mid:#e8273c!important;--gold:#cf1d30!important;--green:#cf1d30!important;--ink:#292a31!important;--muted:#777681!important;--line:#eedfe2!important}
body{background:#faf8f9!important;color:#292a31!important}
body>header,.store-admin-bar{background:#fff!important;color:#292a31!important;border-bottom:3px solid #d51c30!important;box-shadow:0 5px 24px #6b142410!important}
body>header .head{padding:24px 28px!important}
#brand{color:#c7192d!important;font-size:clamp(26px,3vw,42px)!important;letter-spacing:-1px!important}
#tagline,.store-admin-title span{color:#66616b!important;font-size:13px!important;letter-spacing:1.2px!important}
.brand-logo{background:#fff!important;object-fit:contain!important;max-width:220px!important;height:100px!important;border:1px solid #f0e0e3!important}
.store-admin-logo{object-fit:contain!important;background:#fff!important}
.store-admin-actions button,.store-admin-actions a,.search-button,.voice-search,.add,.detail-add,.checkout,.auth-submit,.cats-arrow,.floating-cart,.cart-btn,.account-btn{background:#d51c30!important;color:#fff!important;border-color:#d51c30!important;box-shadow:0 5px 14px #ca203022!important}
.store-admin-actions button:hover,.add:hover,.search-button:hover{background:#b61729!important}
.cart-icon{background:#fff!important;color:#cf1d30!important}.cart-badge{border-color:#cf1d30!important;color:#cf1d30!important;background:#fff!important}
.store-promo-heading{background:#fff!important;color:#292a31!important;border:1px solid #efdfe2!important;box-shadow:none!important}
.store-promo-heading:before,.card:before{background:#d51c30!important}
.store-promo-message span{color:#c7192d!important;letter-spacing:1.5px!important}
.store-promo-message:before{content:'▣';display:inline-grid;place-items:center;margin-right:12px;width:34px;height:34px;border:2px solid #d51c30;border-radius:8px;color:#d51c30;font-size:25px}
.store-main-navigation a{color:#4a3038!important;background:#fff!important;border-color:#efdfe2!important}
.store-main-navigation a:hover,.store-main-navigation a.is-active{background:#d51c30!important;color:#fff!important;border-color:#d51c30!important}
.store-nav-panel{background:#fff!important;color:#292a31!important;border-color:#efdfe2!important}.store-nav-panel p{color:#6a5b61!important}.store-company-details span{background:#fff4f5!important;color:#54323b!important}
.promo-carousel{background:#fff!important;border:1px solid #f0dfe3!important}.promo-image{object-fit:contain!important;background:#fff!important}.promo-backdrop{display:none!important}.promo-corner-label{display:none!important}.promo-arrow{background:#fff!important;color:#d51c30!important;border-color:#e6c9cf!important}.promo-dots button.is-active{background:#d51c30!important}
.tools,.card,.panel,.auth-card{background:#fff!important;border-color:#ecdfe3!important}.card{box-shadow:0 6px 20px #5c192409!important}.photo,.pic{background:#fff8f9!important}.meta,.price,.product-price,.zoom{color:#c7192d!important}.photo img,.pic img{object-fit:contain!important}
.cats button{background:#fff!important;color:#6a3e48!important;border-color:#ecd5db!important}.cats button.on,.cats button.active{background:#d51c30!important;color:#fff!important}
.floating-cart strong,.floating-cart b,.floating-cart small,.cart-label strong,.cart-label small,.account-btn small{color:#fff!important}
.search-wrap input:focus{border-color:#d51c30!important;box-shadow:0 0 0 3px #d51c3012!important}
@media(max-width:640px){body>header .head{padding:18px 16px!important}.brand-logo{max-width:125px!important;height:70px!important}#brand{font-size:25px!important}.store-admin-actions{gap:6px!important}.store-admin-actions button,.store-admin-actions a{font-size:12px!important;padding:10px!important}.store-promo-heading{gap:12px!important}.store-promo-message span{font-size:15px!important}}
'''
(stage/'resources/css/neurosystem-store.css').write_text(css,encoding='utf-8')
p=stage/'resources/images/tienda/producto-sin-imagen.svg'
p.parent.mkdir(parents=True,exist_ok=True)
p.write_text('''<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600"><rect width="600" height="600" rx="36" fill="#fff7f8"/><g fill="none" stroke="#dc2035" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"><rect x="125" y="130" width="350" height="230" rx="18" fill="white"/><path d="M300 360v55m-72 0h144M95 446h410"/><rect x="257" y="201" width="86" height="86" rx="10"/><path d="M278 180v21m44-21v21m-44 86v21m44-21v21m-86-86h21m-21 44h21m86-44h21m-21 44h21"/></g><text x="300" y="500" text-anchor="middle" font-family="Arial,sans-serif" font-weight="800" font-size="32" fill="#c4192c">NEUROSYSTEM</text><text x="300" y="535" text-anchor="middle" font-family="Arial,sans-serif" font-size="17" fill="#78616a">IMAGEN NO DISPONIBLE</text></svg>''',encoding='utf-8')
print('Store assets staged:',stage)
