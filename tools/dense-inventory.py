from pathlib import Path
p=Path('src/views/reportes/Confrontacion.vue');s=p.read_text(encoding='utf-8')
s=s.replace('<i class="fas fa-camera"></i><span>CÁMARA</span>','<i class="fas fa-barcode"></i><span>ESCANEAR</span>')
s=s.replace('limit: 12','limit: 25').replace('cantidad: scanned ? 1 : null','cantidad: 1').replace('PRODUCTO AGREGADO. INDICA SU CANTIDAD FÍSICA.','PRODUCTO AGREGADO · CANTIDAD 1.').replace("query: '', results:","query: '', page: 1, results:",1)
s=s.replace('computed: { counted()', 'computed: { pageCount() { return Math.max(1, Math.ceil(this.products.length / 25)); }, visibleProducts() { return this.products.slice((this.page - 1) * 25, this.page * 25); }, counted()')
s=s.replace('v-for="(item, index) in products"','v-for="(item, index) in visibleProducts"').replace('@click="removeProduct(index)"','@click="removeProduct((page - 1) * 25 + index)"')
s=s.replace('<h2>{{ item.Producto }}</h2>','<h2 :title="item.Producto">{{ item.Producto }}</h2>')
s=s.replace('<footer class="inventory-app-actions">','<nav v-if="products.length > 25" class="inventory-pagination" aria-label="PÁGINAS DE INVENTARIO"><button type="button" :disabled="page === 1" @click="page--">‹</button><span>{{ page }} / {{ pageCount }} · 25 POR PÁGINA</span><button type="button" :disabled="page >= pageCount" @click="page++">›</button></nav><footer class="inventory-app-actions">')
s=s.replace('this.products.unshift(item);','this.products.unshift(item); this.page = 1;')
s=s.replace('this.products.splice(this.products.indexOf(item), 1);','{ this.products.splice(this.products.indexOf(item), 1); this.page = Math.min(this.page, this.pageCount); }')
css='''
/* Dense rows, 25 products per page. */
.inventory-app.confront-page .inventory-count-card{display:grid!important;grid-template-columns:minmax(0,1fr) 112px 28px!important;grid-template-rows:auto!important;gap:4px!important;padding:4px 6px!important;margin:0!important;border-radius:0!important;border-width:0 0 1px!important;min-height:42px!important;align-items:center!important}
.inventory-app.confront-page .inventory-product-info{display:flex!important;align-items:center!important;gap:5px!important;grid-column:1!important;grid-row:1!important;overflow:hidden!important}
.inventory-app.confront-page .inventory-product-info small{display:block!important;flex:none!important;font-size:9px!important;max-width:42px!important;overflow:hidden!important;text-overflow:ellipsis!important}
.inventory-app.confront-page .inventory-product-info h2{display:block!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:11px!important;line-height:20px!important;margin:0!important;min-width:0!important}
.inventory-app.confront-page .inventory-product-info span{display:none!important}
.inventory-app.confront-page .inventory-quantity{grid-column:2!important;grid-row:1!important;gap:2px!important;width:112px!important}
.inventory-app.confront-page .inventory-quantity button{width:34px!important;min-width:34px!important;flex:0 0 34px!important;height:34px!important;font-size:18px!important;border-radius:6px!important}
.inventory-app.confront-page .inventory-quantity input{width:40px!important;min-width:0!important;height:34px!important;font-size:13px!important}
.inventory-app.confront-page .inventory-remove{position:static!important;grid-column:3!important;grid-row:1!important;width:28px!important;height:34px!important;align-self:center!important}
.inventory-app.confront-page .inventory-results button{display:flex!important;align-items:center!important;gap:6px!important;min-height:38px!important;padding:6px!important}.inventory-app.confront-page .inventory-results strong{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:11px!important}.inventory-app.confront-page .inventory-results small{font-size:9px!important;max-width:65px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.inventory-app.confront-page .inventory-results span{flex:none;color:#b21f3b!important;font-size:9px!important}
.inventory-pagination{display:flex;align-items:center;justify-content:center;gap:12px;padding:8px;font-size:10px;color:#9d203a}.inventory-pagination button{width:34px;height:34px;background:#fff0f4;border:1px solid #efcad4;border-radius:6px;color:#b21f3b;font-size:20px}
.inventory-app.confront-page .inventory-feedback{padding:5px 8px!important;margin:5px 0!important;font-size:10px!important}
@media(max-width:600px){.inventory-app.confront-page .inventory-settings{grid-template-columns:1fr 1fr!important;padding:8px!important;gap:6px!important}.inventory-app.confront-page .inventory-settings> a{grid-column:1/-1!important;padding:6px!important;min-height:30px!important}.inventory-app.confront-page .inventory-settings select{height:36px!important;font-size:11px!important}.inventory-app.confront-page .inventory-stats>span{padding:6px!important;display:flex!important;align-items:center!important;gap:5px!important}.inventory-app.confront-page .inventory-stats strong{font-size:17px!important}.inventory-app.confront-page .inventory-search{padding:8px!important}.inventory-app.confront-page .inventory-count-list{margin-top:6px!important}}
'''
s=s.replace('</style>',css+'\n</style>');p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');p.write_text(p.read_text(encoding='utf-8')+css,encoding='utf-8')
