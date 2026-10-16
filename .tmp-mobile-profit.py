from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8');needle='<strong>S/ {{ getTotal(productIndex) }}</strong>'
addition='''
            <button type="button" class="pos-mobile-profit" :class="{ 'is-loss': utilidadProducto(producto) < 0 }" :aria-expanded="String(!!producto.MostrarGanancia)" :aria-label="producto.MostrarGanancia ? 'Ocultar utilidad' : 'Mostrar utilidad'" :title="producto.MostrarGanancia ? 'Ocultar utilidad' : 'Mostrar utilidad'" @click="$set(producto, 'MostrarGanancia', !producto.MostrarGanancia)">
              <i :class="producto.MostrarGanancia ? 'fas fa-eye-slash' : 'fas fa-eye'" aria-hidden="true"></i>
              <span v-if="producto.MostrarGanancia">Utilidad S/ {{ utilidadProducto(producto) | redondear }}</span>
            </button>'''
assert needle in s;s=s.replace(needle,needle+addition,1)
s=s.replace('    getSubtotal(index) {', '''    utilidadProducto(producto) {
      return ((Number(producto.precio) || 0) - (Number(producto.PrecioCosto) || 0)) * (Number(producto.cantidad) || 0) - (Number(producto.descuento) || 0);
    },
    getSubtotal(index) {''',1)
s=s.replace('.pos-mobile-cart {', '''.pos-mobile-profit{display:inline-flex;align-items:center;justify-content:center;gap:5px;margin-top:5px;padding:4px 6px;border:0;border-radius:6px;background:transparent;color:#68808b;font-size:11px;font-weight:700;cursor:pointer}.pos-mobile-profit:hover{background:#edf4f6}.pos-mobile-profit.is-loss[aria-expanded="true"]{color:#c3223e}.pos-mobile-profit:focus-visible{outline:2px solid #68808b;outline-offset:2px}
.pos-mobile-cart {''',1)
p.write_text(s,encoding='utf-8')
