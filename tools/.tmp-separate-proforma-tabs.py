from pathlib import Path
p=Path('src/components/ProformaLista.vue');s=p.read_text(encoding='utf-8')
s=s.replace('      <b-tabs pills card>', '''      <div class="quote-category-tabs" role="tablist" aria-label="Tipo de proforma"><button type="button" role="tab" :aria-selected="quoteType==='productos'" :class="{active:quoteType==='productos'}" @click="changeQuoteType('productos')"><i class="fas fa-box-open" aria-hidden="true"></i> Proformas de productos</button><button type="button" role="tab" :aria-selected="quoteType==='sistemas'" :class="{active:quoteType==='sistemas'}" @click="changeQuoteType('sistemas')"><i class="fas fa-laptop-code" aria-hidden="true"></i> Proformas de sistemas</button></div>
      <p v-if="listError" class="quote-list-error" role="alert">{{listError}}</p>
      <b-tabs pills card>''',1)
s=s.replace('<b-tab title="Proformas" active>', '<b-tab title="Pendientes" active>',1).replace('<span>Proformas</span>', '<span>Pendientes · {{counts.pendientes}}</span>',1)
s=s.replace('<span>Proformas Facturadas</span>', '<span>Facturadas · {{counts.facturadas}}</span>',1).replace('<span>Proformas Favoritas</span>', '<span>Favoritas · {{counts.favoritas}}</span>',1)
s=s.replace(':fields="fields" :per-page="100"', ':fields="displayFields(fields)" :per-page="perPage"',1)
s=s.replace('@input="currentPageFav = 1"', '@input="currentPageFacturadas = 1"',1)
s=s.replace('<b-table v-if="show" id="table-proformas-facturadas"', '<b-table :key="tableKey+\'facturadas\'" v-if="show" id="table-proformas-facturadas"',1)
s=s.replace(':fields="fieldsFacturadas"', ':fields="displayFields(fieldsFacturadas)"',1)
s=s.replace('<input class="form-control" v-model="search" />','<input class="form-control" v-model="search" @input="currentPageFav=1" />',1)
s=s.replace('<b-table v-if="show" id="table-proformas" ref="tableProformas" :filter="filter" :items="filteredItems"\n              :fields="fieldsFavoritas" :per-page="100"', '<b-table :key="tableKey+\'favoritas\'" v-if="show" id="table-proformas-favoritas" ref="tableFavoritas" :filter="search" :items="myProviderFavoritas"\n              :fields="displayFields(fieldsFavoritas)" :per-page="5"',1)
s=s.replace(':total-rows="filteredItems.length"', ':total-rows="totalFavorites"',1)
slot='''              <template slot="ClienteTelefono" slot-scope="data"><div class="quote-list-phone"><span>{{data.item.ClienteTelefono || 'Sin teléfono'}}</span><b-button size="sm" variant="outline-primary" @click="phoneClientId=data.item.IdCliente;phoneOpen=true" title="Actualizar teléfono del cliente"><i class="fas fa-phone-alt" aria-hidden="true"></i> Actualizar</b-button></div></template>
'''
s=s.replace('              <template slot="Total" slot-scope="data">',slot+'              <template slot="Total" slot-scope="data">')
s=s.replace('@click="editId=data.item.IdProforma;editOpen=true"', ':disabled="!!data.item.Facturada" @click="editId=data.item.IdProforma;editOpen=true"')
s=s.replace('@click="selectProformas(data.item)"', ':disabled="!!data.item.Facturada" @click="selectProformas(data.item)"')
s=s.replace('  <b-modal v-model="editOpen"', '''  <b-modal v-model="phoneOpen" title="Actualizar teléfono del cliente" hide-footer size="md" lazy><cliente-telefono-rapido v-if="phoneOpen" :client-id="phoneClientId" required @saved="phoneOpen=false;tableKey++"/></b-modal>
  <b-modal v-model="editOpen"''',1)
s=s.replace("import ProformaEditor from '@/views/ProformaEditor.vue';", "import ProformaEditor from '@/views/ProformaEditor.vue';\nimport ClienteTelefonoRapido from '@/components/ClienteTelefonoRapido.vue';")
s=s.replace('components:{ProformaEditor}', 'components:{ProformaEditor,ClienteTelefonoRapido}',1)
start=s.index('    items() {');end=s.index('    show() {',start);s=s[:start]+s[end:]
s=s.replace('      editOpen:false,editId:null,', "      quoteType:'productos',counts:{pendientes:0,facturadas:0,favoritas:0},totalFavorites:0,listError:'',phoneOpen:false,phoneClientId:0,editOpen:false,editId:null,",1)
s=s.replace('proformaSaved(){this.editOpen=false;this.tableKey++;this.cargarFavoritas();}', 'proformaSaved(){this.editOpen=false;this.tableKey++;}',1)
start=s.index('    cargarFavoritas() {');end=s.index('    async cambiarFavorito',start)
s=s[:start]+'''    changeQuoteType(type){if(this.quoteType===type)return;this.quoteType=type;this.filter='';this.filterFav='';this.search='';this.currentPage=1;this.currentPageFacturadas=1;this.currentPageFav=1;this.counts={pendientes:0,facturadas:0,favoritas:0};this.totalRows=0;this.totalRowsFav=0;this.totalFavorites=0;this.tableKey++;},
    displayFields(fields){if(this.quoteType!=='sistemas')return fields;const out=fields.slice();out.splice(out.findIndex(f=>f.key==='Cliente')+1,0,{key:'ClienteTelefono',label:'Teléfono del cliente',thStyle:{width:'165px'}});return out;},
    cargarFavoritas(){if(this.$refs.tableFavoritas)this.$refs.tableFavoritas.refresh();},
    async quoteProvider(ctx,state){const type=this.quoteType;this.listError='';try{const r=await this.axios.get('/api/proforma-list.php',{params:{tipo:type,estado:state,q:ctx.filter||'',page:ctx.currentPage||1,limit:ctx.perPage||5}});if(type!==this.quoteType)return [];this.counts=r.data.counts;const total=Number(r.data.total)||0;if(state==='pendientes')this.totalRows=total;else if(state==='facturadas')this.totalRowsFav=total;else this.totalFavorites=total;return r.data.items||[];}catch(e){if(type===this.quoteType)this.listError=(e.response&&e.response.data&&e.response.data.error)||'No se pudo cargar el listado.';return [];}},
''' +s[end:]
s=s.replace('      this.cargarFavoritas();\n    },\n    async selectProformas', '      this.tableKey++;\n    },\n    async selectProformas',1)
start=s.index('    myProviderFacturadas(ctx) {');end=s.index('    parseVentas',start)
s=s[:start]+"    myProviderFacturadas(ctx){return this.quoteProvider(ctx,'facturadas');},\n    myProviderFavoritas(ctx){return this.quoteProvider(ctx,'favoritas');},\n"+s[end:]
start=s.index('    myProvider(ctx) {');end=s.index('    eliminarProf(data) {',start)
s=s[:start]+"    myProvider(ctx){return this.quoteProvider(ctx,'pendientes');},\n"+s[end:]
s=s.replace('<style>\ncss\n','<style>\n',1)
s=s.replace('</style>', '''.quote-category-tabs{display:flex;gap:8px;padding:10px 12px;background:#eaf4f7;flex-shrink:0;border-bottom:1px solid #d4e5ed}.quote-category-tabs button{display:flex;align-items:center;gap:8px;padding:10px 16px;border:1px solid #c9dce5;border-radius:7px;background:white;color:#28576b;font-size:13px;font-weight:600;cursor:pointer}.quote-category-tabs button.active{background:#0b718b;color:white;border-color:#0b718b}.quote-category-tabs button:last-child.active{background:#b2223e;border-color:#b2223e}.quote-list-phone{display:flex;flex-direction:column;gap:5px;align-items:flex-start}.quote-list-phone>span{font-size:12px;white-space:nowrap}.quote-list-phone .btn{padding:4px 7px!important;min-height:25px!important;font-size:10px!important}.quote-list-error{margin:8px 12px;color:#b22640;font-size:12px}@media(max-width:600px){.quote-category-tabs{flex-wrap:wrap}.quote-category-tabs button{flex:1;font-size:11px;padding:9px}}
</style>''',1)
p.write_text(s,encoding='utf-8')
