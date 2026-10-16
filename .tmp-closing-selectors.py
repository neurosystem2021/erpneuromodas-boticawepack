from pathlib import Path
p=Path('src/components/CatalogFilterSearch.vue');s=p.read_text(encoding='utf-8');s=s.replace(':value="opened ? query : selectedLabel"', ':disabled="disabled" :value="opened ? query : selectedLabel"',1);s=s.replace('v-if="value !== \'\'"', 'v-if="clearable && value !== \'\' && !disabled"',1);s=s.replace('<button type="button" role="option" :aria-selected="String(value === \'\')"', '<button v-if="clearable" type="button" role="option" :aria-selected="String(value === \'\')"',1)
s=s.replace('props: { value:', 'props: { disabled: {type:Boolean,default:false}, clearable: {type:Boolean,default:true}, value:',1)
s=s.replace("open() { this.opened", "open() { if(this.disabled)return; this.opened",1)
s=s.replace("chooseActive() { if (this.opened) this.choose(this.active >= 0 ? this.matches[this.active] : null); }", "chooseActive() { if (this.opened && (this.active >= 0 || this.clearable)) this.choose(this.active >= 0 ? this.matches[this.active] : null); }")
s=s.replace("choose(item) { this.$emit", "choose(item) { if(this.disabled)return; this.$emit",1);p.write_text(s,encoding='utf-8')
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8')
s=s.replace('          Turno\n          \n          <b-form-select v-model="turno" :options="turnos" />', '''          <div class="closing-selector"><label for="closing-shift"><i class="fas fa-clock" aria-hidden="true"></i> Turno de atención</label>
          <catalog-filter-search v-model="turno" :options="turnos.filter(t => t.value !== null)" id-key="value" label-key="text" input-id="closing-shift" placeholder="Seleccione un turno" :clearable="false" />
          </div>''',1)
a=s.index('          Vendedor:\n          <b-select');b=s.index('</b-select>',a)+len('</b-select>')
s=s[:a]+'''          <div class="closing-selector"><label for="closing-user"><i class="fas fa-user-tie" aria-hidden="true"></i> Usuario / vendedor</label>
          <catalog-filter-search v-model="IdVendedor" :options="lstVendedor" id-key="Usuario" label-key="NombreUsuario" input-id="closing-user" placeholder="Buscar usuario..." :disabled="bloquearVendedor" :clearable="false" @input="recargarCierre" />
          </div>'''+s[b:]
s=s.replace('import config from', 'import CatalogFilterSearch from "@/components/CatalogFilterSearch.vue";\nimport config from',1)
s=s.replace('  components: {','  components: {\n    CatalogFilterSearch,',1)
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
@media screen{
 .nst-screen--cierrecaja .closing-selector{margin:9px 0;padding:12px;background:linear-gradient(130deg,#edf5fb,#f7f8ff);border:1px solid #dce7f2;border-radius:14px}
 .nst-screen--cierrecaja .closing-selector>label{display:flex;align-items:center;gap:7px;margin:0 0 8px;font-size:12px;font-weight:800;color:#31536c}
 .nst-screen--cierrecaja .closing-selector>label i{color:#1591ae}
 .nst-screen--cierrecaja .closing-selector .filter-search{width:100%}
 .nst-screen--cierrecaja .closing-selector .filter-search-field{height:46px;background:white;border-radius:10px;border:1px solid #c9dce9;box-shadow:0 3px 9px #163f5509}
 .nst-screen--cierrecaja .closing-selector input{font-weight:700;font-size:13px;color:#204a64}
 .nst-screen--cierrecaja .closing-selector input:disabled{color:#6b8292;cursor:not-allowed}
 .nst-screen--cierrecaja .closing-selector .filter-search-options{top:calc(100% + 7px);max-height:265px;padding:6px;background:linear-gradient(145deg,#153b52,#123046);border:1px solid #447087;border-radius:13px;box-shadow:0 14px 32px #14364c40}
 .nst-screen--cierrecaja .closing-selector .filter-search-options button{padding:11px 10px;border:0;border-radius:8px;margin:2px 0;background:transparent;color:#e5f3fc;font-size:13px;font-weight:600;text-align:left}
 .nst-screen--cierrecaja .closing-selector .filter-search-options button:hover,.nst-screen--cierrecaja .closing-selector .filter-search-options button.active{background:#ffffff17;color:#fff}
 .nst-screen--cierrecaja .closing-selector .filter-search-options button[aria-selected="true"]{background:linear-gradient(110deg,#087c98,#148cbd);color:#fff;box-shadow:0 2px 8px #061c2a33}
 .nst-screen--cierrecaja .closing-selector .filter-search-options p{color:#c7dce9}
}
''';p.write_text(s,encoding='utf-8')
