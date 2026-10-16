from pathlib import Path
p=Path('src/views/reportes/Confrontacion.vue');s=p.read_text(encoding='utf-8')
s=s.replace("icon: 'question', title: 'QUITAR DEL CONTEO', text: item.Producto, showCancelButton: true, confirmButtonText: 'QUITAR', cancelButtonText: 'VOLVER', focusCancel: true", "icon: 'warning', title: 'QUITAR PRODUCTO', text: item.Producto, footer: 'SE QUITARÁ SOLO DEL CONTEO ACTUAL.', showCancelButton: true, confirmButtonText: 'SÍ, QUITAR', cancelButtonText: 'CONSERVAR', focusCancel: true, reverseButtons: true, buttonsStyling: false, customClass: { container: 'inventory-confirm-overlay', popup: 'inventory-confirm-dialog', icon: 'inventory-confirm-icon', title: 'inventory-confirm-title', content: 'inventory-confirm-content', htmlContainer: 'inventory-confirm-content', actions: 'inventory-confirm-actions', confirmButton: 'inventory-confirm-remove', cancelButton: 'inventory-confirm-cancel', footer: 'inventory-confirm-footer' }")
p.write_text(s,encoding='utf-8')
css='''
/* Inventory removal confirmation, scoped to this dialog. */
.inventory-confirm-overlay{padding:14px!important;background:rgba(50,15,26,.46)!important;backdrop-filter:blur(3px)}
.inventory-confirm-dialog.swal2-popup{width:350px!important;max-width:100%!important;padding:22px 18px 16px!important;border-radius:20px!important;border:1px solid #f3dbe3!important;box-shadow:0 24px 60px #4b112c30!important;background:#fff!important}
.inventory-confirm-dialog .inventory-confirm-icon{width:46px!important;height:46px!important;margin:0 auto 12px!important;border:0!important;background:#fff0f4!important;color:#bd1d3b!important;font-size:12px!important}
.inventory-confirm-dialog .inventory-confirm-icon .swal2-icon-content{font-size:30px!important;font-weight:600!important}
.inventory-confirm-dialog .inventory-confirm-title{font-size:17px!important;line-height:1.3!important;letter-spacing:.3px;color:#712038!important;padding:0!important;margin:0 0 10px!important}
.inventory-confirm-dialog .inventory-confirm-content,.inventory-confirm-dialog .swal2-html-container{font-size:13px!important;line-height:1.5!important;color:#69535c!important;margin:0!important;padding:0 6px!important;overflow-wrap:anywhere}
.inventory-confirm-dialog .inventory-confirm-actions{display:flex!important;gap:8px!important;width:100%!important;margin:18px 0 0!important;padding:0!important}
.inventory-confirm-dialog .inventory-confirm-remove,.inventory-confirm-dialog .inventory-confirm-cancel{flex:1!important;min-height:44px!important;padding:10px 12px!important;margin:0!important;border-radius:10px!important;font-size:11px!important;font-weight:800!important;cursor:pointer}
.inventory-confirm-dialog .inventory-confirm-remove{background:#c51d39!important;color:#fff!important;border:1px solid #c51d39!important;box-shadow:0 4px 12px #c51d3920!important}
.inventory-confirm-dialog .inventory-confirm-cancel{background:#fff!important;color:#a4223d!important;border:1px solid #eccbd4!important}
.inventory-confirm-dialog button:focus-visible{outline:3px solid #efacc0!important;outline-offset:2px!important}
.inventory-confirm-dialog .inventory-confirm-footer{font-size:9px!important;line-height:1.4!important;color:#a3838d!important;margin:14px 0 0!important;padding:12px 0 0!important;border-top:1px solid #f4e6eb!important}
'''
p=Path('static/css/pos-layout-fix.css');p.write_text(p.read_text(encoding='utf-8')+css,encoding='utf-8')
