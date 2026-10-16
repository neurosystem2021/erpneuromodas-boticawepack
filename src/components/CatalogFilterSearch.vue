<template>
  <div class="filter-search" @focusout="onBlur">
    <div class="filter-search-field">
      <i class="fas fa-search" aria-hidden="true"></i>
      <input :id="inputId" :disabled="disabled" :value="opened ? query : selectedLabel" :placeholder="placeholder" autocomplete="off" role="combobox" aria-autocomplete="list" :aria-expanded="String(opened)" :aria-controls="inputId + '-options'" @focus="open" @input="search" @keydown.down.prevent="move(1)" @keydown.up.prevent="move(-1)" @keydown.enter.prevent="chooseActive" @keydown.esc.prevent="opened = false">
      <button v-if="clearable && value !== '' && !disabled" type="button" aria-label="Limpiar selección" @click="choose(null)"><i class="fas fa-times" aria-hidden="true"></i></button>
      <i v-else class="fas fa-chevron-down" aria-hidden="true"></i>
    </div>
    <div v-if="opened" :id="inputId + '-options'" class="filter-search-options" role="listbox">
      <button v-if="clearable" type="button" role="option" :aria-selected="String(value === '')" @mousedown.prevent @click="choose(null)">{{ placeholder }}</button>
      <button v-for="(item, index) in matches" :key="item[idKey]" type="button" role="option" :aria-selected="String(String(value) === String(item[idKey]))" :class="{ active: index === active }" @mousedown.prevent @click="choose(item)">{{ item[labelKey] }}</button>
      <p v-if="!matches.length">No se encontraron coincidencias</p>
    </div>
  </div>
</template>
<script>
const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
export default {
  props: { disabled: {type:Boolean,default:false}, clearable: {type:Boolean,default:true}, value: { default: '' }, options: { type: Array, default: () => [] }, idKey: String, labelKey: String, placeholder: String, inputId: String },
  data() { return { opened: false, query: '', active: -1 }; },
  computed: {
    selectedLabel() { const item = this.options.find(item => String(item[this.idKey]) === String(this.value)); return item ? item[this.labelKey] : ''; },
    matches() { const words = normalize(this.query).trim().split(/\s+/).filter(Boolean); return this.options.filter(item => words.every(word => normalize(item[this.labelKey]).includes(word))); }
  },
  methods: {
    open() { if(this.disabled)return; this.opened = true; this.query = ''; this.active = -1; },
    search(event) { this.query = event.target.value; this.opened = true; this.active = this.matches.length ? 0 : -1; },
    move(step) { if (!this.opened) this.open(); this.active = Math.max(-1, Math.min(this.matches.length - 1, this.active + step)); this.$nextTick(() => { const active = this.$el.querySelector('.active'); if (active) active.scrollIntoView({ block: 'nearest' }); }); },
    chooseActive() { if (this.opened && (this.active >= 0 || this.clearable)) this.choose(this.active >= 0 ? this.matches[this.active] : null); },
    choose(item) { if(this.disabled)return; this.$emit('input', item ? item[this.idKey] : ''); this.$emit('change'); this.opened = false; this.query = ''; },
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
