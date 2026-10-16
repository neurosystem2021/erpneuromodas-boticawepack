from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');a=s.index('                                              <section class="pos-payment-picker"');b=s.index('</section>',a)+len('</section>');picker=s[a:b]
needle='  <div v-if="ventas.length" class="pos-mobile-payment-bar">'
s=s.replace(needle,'''  <b-modal id="pos-mobile-payment-methods" title="Métodos de pago" hide-footer modal-class="pos-mobile-methods-modal"><template v-if="ventas[index]">'''+picker+'''</template></b-modal>
'''+needle)
s=s.replace('    <button type="button" class="pos-mobile-more"', '''    <button type="button" class="pos-mobile-methods" v-b-modal.pos-mobile-payment-methods><i class="fas fa-credit-card" aria-hidden="true"></i><span>Métodos de pago</span></button>
    <button type="button" class="pos-mobile-more"''',1)
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
@media(max-width:767.98px){
.pos-page>.pos-mobile-payment-bar{flex-wrap:wrap;padding-bottom:calc(9px + env(safe-area-inset-bottom))!important}
.pos-mobile-methods{flex:0 0 100%;display:flex;align-items:center;justify-content:center;gap:8px;background:#125879;color:#fff;min-height:38px!important}
.pos-page .pos-payment-method-trigger{display:none!important}
.pos-mobile-methods-modal .modal-dialog{width:calc(100vw - 12px)!important;max-width:calc(100vw - 12px)!important;margin:12px auto!important}
.pos-mobile-methods-modal .modal-body{padding:8px!important}
.pos-mobile-methods-modal .pos-payment-option{grid-template-columns:26px minmax(55px,1fr) 30px minmax(65px,1fr) 70px;gap:5px}
}
''';p.write_text(s,encoding='utf-8')
