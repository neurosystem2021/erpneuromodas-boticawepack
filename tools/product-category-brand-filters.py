from pathlib import Path
p=Path('src/views/Productos.vue')
s=p.read_text(encoding='utf-8')
s=s.replace('  <section class="catalog-meta"', '''  <section class="catalog-filters" aria-label="Filtros por categoría y marca">
    <label><span><i class="fas fa-layer-group" aria-hidden="true"></i> Categoría</span>
      <select v-model="categoriaId" @change="cambiarFiltros"><option value="">Todas las categorías</option><option v-for="item in categoriasFiltro" :key="item.IdProductoCategoria" :value="item.IdProductoCategoria">{{ item.ProductoCategoria }}</option></select>
    </label>
    <label><span><i class="fas fa-tag" aria-hidden="true"></i> Marca</span>
      <select v-model="marcaId" @change="cambiarFiltros"><option value="">Todas las marcas</option><option v-for="item in marcasFiltro" :key="item.IdProductoMarca" :value="item.IdProductoMarca">{{ item.ProductoMarca }}</option></select>
    </label>
    <button v-if="categoriaId || marcaId" type="button" @click="limpiarFiltros"><i class="fas fa-times" aria-hidden="true"></i> Limpiar filtros</button>
  </section>
  <section class="catalog-meta"''',1)
s=s.replace('      searchTimer: null,','      searchTimer: null,\n      categoriaId: "",\n      marcaId: "",\n      categoriasFiltro: [],\n      marcasFiltro: [],',1)
s=s.replace('    this.obtnerTipoCambio();','    this.obtnerTipoCambio();\n    this.cargarOpcionesFiltro();',1)
s=s.replace('  methods: {','''  methods: {
    cargarOpcionesFiltro() {
      [{ route: "categorias", target: "categoriasFiltro", label: "ProductoCategoria" }, { route: "marcas", target: "marcasFiltro", label: "ProductoMarca" }].forEach(option => {
        this.axios.get(this.API + option.route, { params: { limit: 10000 } }).then(response => {
          this[option.target] = (Array.isArray(response.data) ? response.data : []).filter(item => Number(item.Anulado || 0) === 0).sort((a, b) => String(a[option.label] || "").localeCompare(String(b[option.label] || ""), "es"));
        }).catch(() => this.$bvToast.toast("No se pudieron cargar las opciones de " + option.route, { title: "Filtros", variant: "danger", solid: true }));
      });
    },
    cambiarFiltros() {
      this.currentPage = 1;
      this.obtenerListado();
      this.actualizarTabla(false);
    },
    limpiarFiltros() {
      this.categoriaId = "";
      this.marcaId = "";
      this.cambiarFiltros();
    },''',1)
s=s.replace('          soloOfertas: this.soloOfertas ? 1 : 0','          categoriaId: this.categoriaId || "",\n          marcaId: this.marcaId || "",\n          soloOfertas: this.soloOfertas ? 1 : 0',1)
s += '''\n<style scoped>
.catalog-filters { display:flex; align-items:center; flex-wrap:wrap; gap:10px 18px; padding:10px 15px; background:#fff; border-bottom:1px solid #d5e6ee; }
.catalog-filters label { display:flex; align-items:center; gap:9px; margin:0; color:#31566b; font-weight:700; }
.catalog-filters label span { white-space:nowrap; }
.catalog-filters label i { color:#0c8298; margin-right:4px; }
.catalog-filters select { width:240px; max-width:100%; height:38px; padding:5px 10px; border:1px solid #c8dfe9; border-radius:10px; color:#21485e; background:#f8fbfd; }
.catalog-filters button { border:0; border-radius:8px; padding:8px 12px; color:#a2213b; background:#fff0f3; font-weight:700; }
@media(max-width:600px) { .catalog-filters { gap:8px; } .catalog-filters label { flex:1 1 140px; flex-direction:column; align-items:stretch; gap:3px; } .catalog-filters select { width:100%; } }
</style>\n'''
p.write_text(s,encoding='utf-8')
p=Path('C:/xampp/htdocs/boticav1/api/index.php')
s=p.read_text(encoding='utf-8')
start=s.index("$app->get('/productos',")
end=s.index("$app->post('/productos/oferta/desactivar'",start)
part=s[start:end]
needle="    if ((int)$request->getParam('soloOfertas') === 1) {"
assert part.count(needle)==2
part=part.replace(needle,"""    if ((int)$request->getParam('categoriaId') > 0) {
        $select .= " AND Gen_Producto.IdProductoCategoria=" . (int)$request->getParam('categoriaId');
    }
    if ((int)$request->getParam('marcaId') > 0) {
        $select .= " AND Gen_Producto.IdProductoMarca=" . (int)$request->getParam('marcaId');
    }
"""+needle)
out=Path('deploy-src/product-filters/api/index.php');out.parent.mkdir(parents=True,exist_ok=True);out.write_text(s[:start]+part+s[end:],encoding='utf-8')
