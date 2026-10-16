from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8')
a=s.index('<small class="pos-cart-gain"');b=s.index('</small>',a)+len('</small>')
s=s[:a]+'''<button type="button" class="pos-cart-gain" :class="{ 'is-loss': data.item.MostrarGanancia &amp;&amp; (data.item.precio - data.item.PrecioCosto) * data.item.cantidad &lt; 0 }" :aria-expanded="String(!!data.item.MostrarGanancia)" :aria-label="data.item.MostrarGanancia ? 'OCULTAR GANANCIA' : 'MOSTRAR GANANCIA'" :title="data.item.MostrarGanancia ? 'OCULTAR GANANCIA BRUTA' : 'MOSTRAR GANANCIA BRUTA'" @click="$set(data.item, 'MostrarGanancia', !data.item.MostrarGanancia)"><span class="pos-gain-label"><i :class="data.item.MostrarGanancia ? 'fas fa-eye-slash' : 'fas fa-eye'"></i> GANANCIA</span><strong v-if="data.item.MostrarGanancia">{{ (data.item.precio - data.item.PrecioCosto) * data.item.cantidad | redondear }}</strong></button>'''+s[b:]
p.write_text(s,encoding='utf-8')
