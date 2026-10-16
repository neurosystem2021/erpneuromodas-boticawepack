from pathlib import Path
css='''
/* Compact physical count: product, quantity and remove share one row. */
.inventory-app.confront-page .inventory-count-card{grid-template-columns:minmax(0,1fr) 150px 32px!important;min-height:0!important;height:auto!important;gap:10px!important;padding:10px 12px!important;margin-top:6px!important;border-radius:10px!important;position:relative!important}
.inventory-app.confront-page .inventory-product-info{grid-column:auto!important;min-width:0!important;padding:0!important;margin:0!important}
.inventory-app.confront-page .inventory-product-info h2{font-size:12px!important;line-height:1.35!important;margin:3px 0!important}
.inventory-app.confront-page .inventory-product-info small{font-size:10px!important;line-height:1.2!important}
.inventory-app.confront-page .inventory-product-info span{font-size:8px!important;line-height:1.2!important;color:#a21f3a!important}
.inventory-app.confront-page .inventory-quantity{display:flex!important;gap:4px!important;align-items:center!important;grid-column:auto!important;width:auto!important;margin:0!important}
.inventory-app.confront-page .inventory-quantity button{width:40px!important;min-width:40px!important;flex:0 0 40px!important;height:40px!important;padding:0!important;font-size:20px!important;border-radius:8px!important;background:#fff0f4!important;border:1px solid #efcad4!important;color:#b21f3b!important}
.inventory-app.confront-page .inventory-quantity input{width:62px!important;flex:1!important;height:40px!important;font-size:15px!important;padding:2px!important;border-radius:8px!important;border-color:#efcad4!important;color:#772039!important}
.inventory-app.confront-page .inventory-remove{position:static!important;width:32px!important;height:40px!important;padding:0!important;grid-column:auto!important;align-self:center!important;margin:0!important}
@media(max-width:600px){
.inventory-app.confront-page .inventory-count-card{grid-template-columns:minmax(0,1fr) 116px 28px!important;gap:5px!important;padding:9px 8px!important}
.inventory-app.confront-page .inventory-product-info h2{font-size:10px!important;line-height:1.35!important}
.inventory-app.confront-page .inventory-quantity button{width:34px!important;min-width:34px!important;flex-basis:34px!important;height:40px!important}
.inventory-app.confront-page .inventory-quantity input{width:40px!important;font-size:14px!important}
.inventory-app.confront-page .inventory-remove{width:28px!important;height:40px!important}
.inventory-app.confront-page .inventory-list-heading{grid-template-columns:minmax(0,1fr) 149px!important;padding:9px 8px!important;gap:5px!important;font-size:9px!important}
.inventory-app.confront-page .inventory-app-actions{padding:8px 0!important;margin-top:8px!important}
}
'''
p=Path('static/css/pos-layout-fix.css');p.write_text(p.read_text(encoding='utf-8-sig')+css,encoding='utf-8')
p=Path('src/views/reportes/Confrontacion.vue');s=p.read_text(encoding='utf-8');s=s.replace('</style>',css+'\n</style>');p.write_text(s,encoding='utf-8')
