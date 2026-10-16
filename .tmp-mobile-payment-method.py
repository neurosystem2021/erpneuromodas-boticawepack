from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');s=s.replace('class="p-1 ml-1 mt-1" :id="`popover-metodopago-', 'class="p-1 ml-1 mt-1 pos-payment-method-trigger" :id="`popover-metodopago-',1);p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
@media(max-width:767.98px){
 .pos-page>.pos-mobile-payment-bar{padding-bottom:calc(48px + env(safe-area-inset-bottom))!important}
 .pos-page .pos-payment-method-trigger{position:fixed!important;left:50%!important;bottom:calc(8px + env(safe-area-inset-bottom))!important;transform:translateX(-50%)!important;z-index:905!important;display:inline-flex!important;align-items:center;justify-content:center;gap:7px;width:auto!important;min-height:30px!important;padding:5px 13px!important;margin:0!important;border-radius:9px!important;font-size:13px!important;box-shadow:none!important}
 .pos-page .pos-payment-method-trigger::after{content:'Método de pago';font-size:12px;font-weight:700}
 .pos-page{padding-bottom:calc(130px + env(safe-area-inset-bottom))!important}
 .popover.custom-popover{z-index:1060!important;max-width:calc(100vw - 16px)!important}
}
''';p.write_text(s,encoding='utf-8')
