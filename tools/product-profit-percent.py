from pathlib import Path
p=Path('src/views/Productos.vue');s=p.read_text(encoding='utf-8')
s=s.replace('      <template slot="PrecioContado"', '''      <template slot="UtilidadPorcentaje" slot-scope="data"><span class="product-profit-percent" :class="{ 'is-loss': utilidadPorcentaje(data.item) !== null && utilidadPorcentaje(data.item) < 0 }" title="UTILIDAD SOBRE COSTO: (VENTA − COSTO) ÷ COSTO × 100">{{ utilidadPorcentaje(data.item) === null ? '—' : utilidadPorcentaje(data.item).toFixed(2) + '%' }}</span></template>
      <template slot="PrecioContado"''',1)
line='{ key: "PrecioContado", label: "Venta", sortable: true, thClass: "text-right", tdClass: "text-right" },'
s=s.replace(line,line+'\n        { key: "UtilidadPorcentaje", label: "Utilidad %", thClass: "text-right", tdClass: "text-right" },',1)
s=s.replace('  methods: {','''  methods: {
    utilidadPorcentaje(item) {
      const cost = Number(item.PrecioCosto), sale = Number(item.PrecioContado);
      return Number.isFinite(cost) && Number.isFinite(sale) && cost > 0 ? (sale - cost) / cost * 100 : null;
    },''',1)
s+='''
<style scoped>
.product-profit-percent{display:inline-block;padding:5px 8px;border-radius:8px;background:#e8f7ef;color:#08784e;font-size:12px;font-weight:800;white-space:nowrap;font-variant-numeric:tabular-nums}.product-profit-percent.is-loss{background:#fff0f2;color:#bd2341}
</style>
'''
p.write_text(s,encoding='utf-8')
