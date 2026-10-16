from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8');s=s.replace('<small class="pos-cart-gain">G: {{ (data.item.precio - data.item.PrecioCosto) * data.item.cantidad | redondear }}</small>','<small class="pos-cart-gain" :class="{ \'is-loss\': (data.item.precio - data.item.PrecioCosto) * data.item.cantidad &lt; 0 }" title="GANANCIA BRUTA DEL PRODUCTO: (PRECIO − COSTO) × CANTIDAD"><span class="pos-gain-label">GANANCIA</span><strong>{{ (data.item.precio - data.item.PrecioCosto) * data.item.cantidad | redondear }}</strong></small>')
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8')+'''
/* Clear gross profit badge at the far right. */
.pos-page .pos-cart-component .pos-cart-row-actions .pos-cart-gain{display:flex!important;flex-direction:column!important;justify-content:center!important;gap:1px!important;min-width:70px!important;padding:5px 8px!important;border-radius:8px!important;border:1px solid #bde7d5!important;background:#edf9f2!important;color:#08764c!important;line-height:1.15!important}
.pos-page .pos-cart-component .pos-cart-gain .pos-gain-label{font-size:8px!important;font-weight:800!important;letter-spacing:.6px!important;color:#43876b!important}
.pos-page .pos-cart-component .pos-cart-gain strong{font-size:13px!important;font-weight:800!important;font-variant-numeric:tabular-nums!important}
.pos-page .pos-cart-component .pos-cart-row-actions .pos-cart-gain.is-loss{background:#fff0f2!important;border-color:#f4c8d1!important;color:#bc2540!important}.pos-page .pos-cart-component .pos-cart-gain.is-loss .pos-gain-label{color:#bc2540!important}
''';p.write_text(s,encoding='utf-8')
