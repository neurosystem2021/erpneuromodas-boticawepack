from pathlib import Path
import shutil
root=Path('C:/xampp/htdocs/boticav1');stage=Path('deploy-src/red-app-header');p=stage/'views/VentasApp.php';p.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(root/'views/VentasApp.php',p)
s=p.read_text(encoding='utf-8-sig')
s+='''
<!-- Red identity for Ventas App, mobile browser and APK WebView. -->
<script>document.documentElement.classList.add('va-red-app');document.querySelector('meta[name="theme-color"]')?.setAttribute('content','#b51b37');</script>
<style>
html.va-red-app .navbar.erp-topbar{background:linear-gradient(110deg,#831a31,#bb1e3b 70%,#d42c48)!important;border:0!important;border-bottom:1px solid #ed829650!important;box-shadow:0 4px 18px #80183025!important}
html.va-red-app .erp-topbar .navbar-brand{background:#ffffff12!important;border-color:#ffffff45!important;color:#fff!important;box-shadow:none!important}
html.va-red-app .erp-topbar .navbar-brand:before{background:#fff!important;box-shadow:0 0 0 4px #ffffff18!important}
html.va-red-app .erp-topbar .navbar-toggle{background:#ffffff18!important;border:1px solid #ffffff50!important;box-shadow:none!important;border-radius:10px!important}
html.va-red-app .erp-topbar .navbar-toggle:hover,html.va-red-app .erp-topbar .navbar-toggle:focus{background:#ffffff30!important}
html.va-red-app .erp-topbar .navbar-toggle .icon-bar{background:#fff!important}
@media(max-width:767px){html.va-red-app .erp-topbar .navbar-collapse{background:#941c34!important;border-color:#ffffff20!important}html.va-red-app .erp-topbar .navbar-nav>li>a{background:#ffffff0d!important;border-color:#ffffff25!important;color:#fff!important}html.va-red-app .erp-topbar .navbar-nav>li.open>a{background:#ffffff22!important}}
</style>
'''
p.write_text(s,encoding='utf-8')
