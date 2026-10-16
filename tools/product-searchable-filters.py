from pathlib import Path
p=Path('src/components/CatalogFilterSearch.vue')
p.write_text('''<template>
  <div class="filter-search" @focusout="onBlur">
    <div class="filter-search-field">
      <i class="fas fa-search" aria-hidden="true"></i>
      <input :id="inputId" :value="opened ? query : selectedLabel" :placeholder="placeholder" autocomplete="off" role="combobox" aria-autocomplete="list" :aria-expanded="String(opened)" :aria-controls="inputId + '-options'" @focus="open" @input="search" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="chooseActive" @keydown.esc.prevent="opened = false">
      <button v-if="value !== ''" type="button" aria-label="Limpiar selección" @click="choose(null)"><i class="fas fa-times" aria-hidden="true"></i></button>
      <i v-else class="fas fa-chevron-down" aria-hidden="true"></i>
    </div>
    <div v-if="opened" :id="inputId + '-options'" class="filter-search-options" role="listbox">
      <button type="button" role="option" :aria-selected="String(value === '')" @mousedown.prevent @click="choose(null)">{{ placeholder }}</button>
      <button v-for="(item, index) in matches" :key="item[idKey]" type="button" role="option" :aria-selected="String(String(value) === String(item[idKey]))" :class="{ active: index === active }" @mousedown.prevent @click="choose(item)">{{ item[labelKey] }}</button>
      <p v-if="!matches.length">No se encontraron coincidencias</p>
    </div>
  </div>
</template>
<script>
const normalize = value => String(value || '').normalize('NFD').replace(/[\\u0300-\\u036f]/g, '').toLowerCase();
export default {
  props: { value: { default: '' }, options: { type: Array, default: () => [] }, idKey: String, labelKey: String, placeholder: String, inputId: String },
  data() { return { opened: false, query: '', active: -1 }; },
  computed: {
    selectedLabel() { const item = this.options.find(item => String(item[this.idKey]) === String(this.value)); return item ? item[this.labelKey] : ''; },
    matches() { const words = normalize(this.query).trim().split(/\\s+/).filter(Boolean); return this.options.filter(item => words.every(word => normalize(item[this.labelKey]).includes(word))); }
  },
  methods: {
    open() { this.opened = true; this.query = ''; this.active = -1; },
    search(event) { this.query = event.target.value; this.opened = true; this.active = this.matches.length ? 0 : -1; },
    move(step) { if (!this.opened) this.open(); this.active = Math.max(-1, Math.min(this.matches.length - 1, this.active + step)); this.$nextTick(() => { const active = this.$el.querySelector('.active'); if (active) active.scrollIntoView({ block: 'nearest' }); }); },
    chooseActive() { if (this.opened) this.choose(this.active >= 0 ? this.matches[this.active] : null); },
    choose(item) { this.$emit('input', item ? item[this.idKey] : ''); this.$emit('change'); this.opened = false; this.query = ''; },
    onBlur(event) { if (!this.$el.contains(event.relatedTarget)) this.opened = false; }
  }
};
</script>
<style scoped>
.filter-search { position:relative; width:270px; max-width:100%; font-weight:400; }
.filter-search-field { display:flex; align-items:center; gap:8px; height:40px; padding:0 10px; border:1px solid #c8dfe9; border-radius:10px; background:#f8fbfd; color:#0c8298; }
.filter-search-field:focus-within { border-color:#0c8298; box-shadow:0 0 0 2px #0c82981a; }
.filter-search input { min-width:0; width:100%; border:0; outline:0; background:transparent; color:#21485e; font-size:14px; }
.filter-search-field button { background:transparent; border:0; padding:4px; color:#607d8b; }
.filter-search-options { position:absolute; z-index:1050; top:calc(100% + 5px); left:0; right:0; max-height:290px; overflow:auto; border:1px solid #c8dfe9; border-radius:10px; background:#fff; box-shadow:0 10px 24px #17384c26; }
.filter-search-options button { display:block; width:100%; text-align:left; padding:10px 12px; border:0; border-bottom:1px solid #edf3f6; background:#fff; color:#21485e; font-size:14px; }
.filter-search-options button:hover,.filter-search-options button.active,.filter-search-options button[aria-selected="true"] { background:#e7f4f7; color:#086b80; }
.filter-search-options p { margin:0; padding:14px; color:#718590; font-size:13px; }
@media(max-width:600px) { .filter-search { width:100%; } }
</style>
''',encoding='utf-8')
p=Path('src/views/Productos.vue');s=p.read_text(encoding='utf-8')
a=s.index('    <label><span><i class="fas fa-layer-group"');b=s.index('    <button v-if="categoriaId || marcaId"',a)
s=s[:a]+'''    <div class="catalog-filter-control"><label for="filter-category"><i class="fas fa-layer-group" aria-hidden="true"></i> Categoría</label>
      <catalog-filter-search input-id="filter-category" v-model="categoriaId" :options="categoriasFiltro" id-key="IdProductoCategoria" label-key="ProductoCategoria" placeholder="Todas las categorías" @change="cambiarFiltros" />
    </div>
    <div class="catalog-filter-control"><label for="filter-brand"><i class="fas fa-tag" aria-hidden="true"></i> Marca</label>
      <catalog-filter-search input-id="filter-brand" v-model="marcaId" :options="marcasFiltro" id-key="IdProductoMarca" label-key="ProductoMarca" placeholder="Todas las marcas" @change="cambiarFiltros" />
    </div>
'''+s[b:]
s=s.replace('import CodigoMultiple from "@/components/CodigoMultiple";','import CodigoMultiple from "@/components/CodigoMultiple";\nimport CatalogFilterSearch from "@/components/CatalogFilterSearch";')
s=s.replace('  components: {','  components: {\n    CatalogFilterSearch,',1)
s+='''\n<style scoped>
.catalog-filter-control { display:flex; align-items:center; gap:9px; }
.catalog-filter-control label { white-space:nowrap; }
@media(max-width:600px) { .catalog-filter-control { flex:1 1 145px; flex-direction:column; align-items:stretch; gap:4px; min-width:0; } }
</style>\n'''
p.write_text(s,encoding='utf-8')
