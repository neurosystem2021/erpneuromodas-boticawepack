from pathlib import Path
p=Path('static/css/pos-layout-fix.css')
s=p.read_text(encoding='utf-8')+'''
/* Keep row gain immediately to the left of the blue edit action. */
.pos-page .pos-cart-component .pos-cart-row-actions{display:flex!important;flex-direction:row!important;align-items:center!important;justify-content:flex-end!important;gap:5px!important;flex-wrap:nowrap!important}
.pos-page .pos-cart-component .pos-cart-row-action-buttons{display:contents!important}
.pos-page .pos-cart-component .pos-cart-row-actions .is-delete{order:0!important;flex-shrink:0!important}
.pos-page .pos-cart-component .pos-cart-row-actions .pos-cart-gain{order:1!important;display:inline-flex!important;align-items:center!important;white-space:nowrap!important;margin:0!important;padding:3px 6px!important;font-size:10px!important;line-height:1.2!important;flex-shrink:0!important}
.pos-page .pos-cart-component .pos-cart-row-actions .is-edit{order:2!important;flex-shrink:0!important}
'''
p.write_text(s,encoding='utf-8')
