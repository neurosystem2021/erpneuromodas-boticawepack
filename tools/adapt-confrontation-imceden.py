from pathlib import Path
p=Path('src/views/reportes/Confrontacion.vue')
s=p.read_text(encoding='utf-8')
s=s.replace('<main class="nst-screen inventory-app">','<main class="nst-screen inventory-app confront-page" :class="{ \'confront-mobile-collapsed\': !showHeader }">\n    <button type="button" class="confront-mobile-header-toggle" :aria-expanded="String(showHeader)" @click="showHeader = !showHeader"><i :class="showHeader ? \'fas fa-eye-slash\' : \'fas fa-eye\'"></i><span>{{ showHeader ? \'OCULTAR CABECERA Y FILTROS\' : \'MOSTRAR CABECERA Y FILTROS\' }}</span><i :class="showHeader ? \'fas fa-chevron-up\' : \'fas fa-chevron-down\'"></i></button>')
a=s.index('    <header class="inventory-app-header">')
b=s.index('    <section class="inventory-settings">',a)
s=s[:a]+'''    <header class="inventory-app-header confront-hero">
      <div class="confront-hero-icon"><i class="fas fa-balance-scale"></i></div>
      <div class="confront-hero-copy"><span class="inventory-eyebrow">CONTROL DE EXISTENCIAS</span><h1>CONFRONTACIÓN DE INVENTARIO</h1><p>COMPARA EL STOCK FÍSICO CON EL SISTEMA Y GENERA EL REPORTE DE DIFERENCIAS.</p></div>
      <a class="inventory-app-link" href="/views/lo_confrontacion.php?app=1" target="_top"><i class="fas fa-mobile-alt"></i> MODO APP</a>
    </header>
    <div class="confront-workspace">
'''+s[b:]
s=s.replace('CARGAR CATEGORÍA<select','AGREGAR POR CATEGORÍA<select')
s=s.replace('    <p class="inventory-report-note">','    </div>\n    <p class="inventory-report-note">')
s=s.replace("API: config.API, warehouses:","showHeader: true, API: config.API, warehouses:")
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css')
s=p.read_text(encoding='utf-8')
s+='''
/* Confrontation arrangement from imceden, in the existing NeuroSoft palette. */
.inventory-app.confront-page { max-width: 1480px; padding: 14px 18px; }
.confront-page .confront-hero { min-height: 88px; margin-bottom: 14px; padding: 16px 22px; border-radius: 20px; background: linear-gradient(112deg,#083b4d,#125b6b 65%,#008e91); box-shadow: 0 12px 28px #123f5124; color: #fff; }
.confront-hero-icon { display: grid; place-items: center; flex: 0 0 50px; width: 50px; height: 50px; border: 1px solid #ffffff38; border-radius: 15px; background: #ffffff15; color: #a1f2e4; font-size: 21px; }
.confront-hero-copy { flex: 1; }
.confront-page .confront-hero h1 { color: white; margin: 3px 0; font-size: 25px; }
.confront-page .confront-hero .inventory-eyebrow { color: #b2eee8; font-size: 10px; }
.confront-page .confront-hero p { color: #d4eaf0; }
.confront-page .confront-hero .inventory-app-link { background: #ffffff14; color: white; border-color: #ffffff45; }
.confront-workspace { padding: 20px; border: 1px solid #dce9ee; border-radius: 18px; background: #fff; }
.confront-page .inventory-settings { padding: 0 0 16px; border: 0; border-bottom: 1px solid #dfebf0; border-radius: 0; }
.confront-page .inventory-stats { margin: 12px 0; }
.confront-page .inventory-stats span { padding: 8px 12px; background: #f5fafb; }
.confront-page .inventory-stats strong { font-size: 19px; }
.confront-page .inventory-search { padding: 14px; background: #f4f9fb; border-radius: 13px; }
.confront-page .inventory-count-list { margin-top: 14px; }
.confront-page .inventory-app-actions { padding: 12px 0; background: #fffffff5; }
.confront-mobile-header-toggle { display: none; }
@media (max-width: 767px) {
  .inventory-app.confront-page { padding: 8px; }
  .confront-mobile-header-toggle { display: flex; width: 100%; min-height: 42px; margin-bottom: 8px; padding: 9px 12px; align-items: center; gap: 9px; border: 0; border-radius: 12px; background: linear-gradient(135deg,#123f51,#009f87); color: white; font-size: 11px; font-weight: 800; }
  .confront-mobile-header-toggle span { flex: 1; text-align: left; }
  .confront-page .confront-hero { padding: 13px; gap: 10px; min-height: 78px; border-radius: 14px; }
  .confront-page .confront-hero h1 { font-size: 19px; }
  .confront-page .confront-hero p { display: none; }
  .confront-hero-icon { flex-basis: 40px; width: 40px; height: 40px; font-size: 18px; }
  .confront-workspace { padding: 10px; border-radius: 14px; }
  .confront-mobile-collapsed > .confront-hero, .confront-mobile-collapsed .inventory-settings { display: none; }
  .confront-page .inventory-settings { gap: 10px; }
  .confront-page .inventory-app-actions button { min-height: 46px; }
}
'''
p.write_text(s,encoding='utf-8')
