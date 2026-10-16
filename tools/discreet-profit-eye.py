from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8').replace("</i> GANANCIA</span>","</i></span>");p.write_text(s,encoding='utf-8')
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');s=s.replace('<td  colspan="2">G. G</td>', '''<td colspan="2"><button type="button" class="pos-total-gain-toggle" :aria-expanded="String(!!ventas[index].MostrarGananciaTotal)" :aria-label="ventas[index].MostrarGananciaTotal ? 'OCULTAR GANANCIA TOTAL' : 'MOSTRAR GANANCIA TOTAL'" title="GANANCIA TOTAL" @click="$set(ventas[index], 'MostrarGananciaTotal', !ventas[index].MostrarGananciaTotal)"><i :class="ventas[index].MostrarGananciaTotal ? 'fas fa-eye-slash' : 'fas fa-eye'"></i></button></td>''')
a=s.index('class="pos-total-gain-toggle"');b=s.index('<td>',a);s=s[:b]+s[b:].replace('<td>','<td><span v-if="ventas[index].MostrarGananciaTotal">',1);c=s.index('</td>',b);s=s[:c]+'</span>'+s[c:];p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8')+'''
/* Discreet reveal controls: only an eye until clicked. */
.pos-page .pos-cart-component .pos-cart-row-actions .pos-cart-gain{min-width:26px!important;width:auto!important;min-height:28px!important;height:auto!important;max-height:none!important;flex-direction:row!important;padding:3px 5px!important;gap:5px!important;background:transparent!important;border:0!important;box-shadow:none!important;color:#8b99a3!important;cursor:pointer}
.pos-page .pos-cart-component .pos-cart-gain .pos-gain-label{font-size:12px!important;letter-spacing:0!important;color:#8b99a3!important}
.pos-page .pos-cart-component .pos-cart-gain strong{font-size:11px!important;color:#526a79!important}
.pos-page .pos-total-gain-toggle{padding:4px 6px!important;border:0!important;background:transparent!important;color:#8b99a3!important;box-shadow:none!important;font-size:12px!important;cursor:pointer}
.pos-page .pos-total-gain-toggle:hover,.pos-page .pos-cart-component .pos-cart-gain:hover{color:#36576b!important;background:#f4f7fa!important;border-radius:6px!important}
''';p.write_text(s,encoding='utf-8')
