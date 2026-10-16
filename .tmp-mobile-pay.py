from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');needle='<b-container class="body pos-page" fluid>'
s=s.replace(needle,needle+'''
  <div v-if="ventas.length" class="pos-mobile-payment-bar">
    <button type="button" class="pos-mobile-more" @click="mostrarAccionesMovil"><i class="fas fa-th-large" aria-hidden="true"></i><span>Más opciones</span></button>
    <button type="button" class="pos-mobile-pay" :disabled="inProgress" @click="guardarVenta"><i class="fa fa-shopping-cart" aria-hidden="true"></i><span>{{ inProgress ? 'Procesando...' : 'Pagar' }}</span><strong>S/ {{ $options.filters.redondear((ventas[index] && ventas[index].totalCart) || 0) }}</strong></button>
  </div>''',1)
s=s.replace('    async cargarPedidoWeb(data) {','''    mostrarAccionesMovil() {
      const panel = this.$el.querySelector('.pos-checkout-area');
      if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
    },
    async cargarPedidoWeb(data) {''',1)
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
/* Cobro móvil siempre accesible, con acceso a todas las acciones. */
.pos-mobile-payment-bar{display:none}
@media(max-width:767.98px){
 .pos-page{padding-bottom:calc(95px + env(safe-area-inset-bottom))!important}
 .pos-page>.pos-mobile-payment-bar{display:flex!important;position:fixed!important;left:0;right:0;bottom:0;z-index:900;gap:8px;align-items:stretch;padding:9px 10px calc(9px + env(safe-area-inset-bottom));background:#fff;border-top:1px solid #d9e7ec;box-shadow:0 -5px 20px #123e5122}
 .pos-mobile-payment-bar button{border:0;border-radius:13px;min-height:54px;font-weight:800;cursor:pointer}
 .pos-mobile-more{display:flex;flex-direction:column;justify-content:center;align-items:center;gap:5px;flex:0 0 80px;background:#eaf2f6;color:#23485d;font-size:10px}
 .pos-mobile-pay{display:flex;align-items:center;justify-content:center;gap:10px;flex:1;min-width:0;background:linear-gradient(120deg,#00a878,#18c66b);color:white;font-size:17px;box-shadow:0 3px 0 #087d62}
 .pos-mobile-pay strong{border-left:1px solid #ffffff55;padding-left:10px;font-size:16px;white-space:nowrap}
 .pos-mobile-pay:disabled{opacity:.65}
 .pos-page .pos-cart-pay-row{display:none!important}
 .pos-page .pos-mobile-cart{max-height:clamp(130px,32dvh,320px)!important;overflow-y:auto!important}
 .pos-page .pos-checkout-area{display:block!important;height:auto!important;max-height:none!important;overflow:visible!important;scroll-margin-top:85px;margin-bottom:20px!important}
 .pos-page .pos-checkout-form,.pos-page .pos-checkout-grid{height:auto!important;max-height:none!important;overflow:visible!important}
 .pos-page .pos-action-panel,.pos-page .pos-tools-panel,.pos-page .pos-summary-panel{display:block!important}
}
''';p.write_text(s,encoding='utf-8')
