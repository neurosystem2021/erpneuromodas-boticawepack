from pathlib import Path
root=Path(__file__).resolve().parent.parent
p=root/'src/views/Ventas.vue';raw=p.read_bytes()
def replace(old,new):
 global raw
 old=old.encode('utf-8');new=new.encode('utf-8');assert raw.count(old)==1,old;raw=raw.replace(old,new)
replace('<b-container class="body pos-page" fluid>','<b-container class="body pos-page" fluid>\n  <buscador-productos-ia v-model="productExplorerOpen" />')
replace('<b-tabs card pills class="pos-tabs" v-model="index" justified>','<b-tabs card pills class="pos-tabs" v-model="index" justified>\n      <template slot="tabs"><li role="presentation" class="nav-item pos-product-ai-launcher"><button type="button" class="pos-product-ai-search" title="Buscar productos con IA" aria-label="Buscar productos con IA" @click.stop="productExplorerOpen=true" @keydown.stop><svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5" stroke-linecap="round"/></svg><span>IA</span></button></li></template>')
replace('import ProformaPresentacion from "@/components/ProformaPresentacion.vue";','import ProformaPresentacion from "@/components/ProformaPresentacion.vue";\nimport BuscadorProductosIA from "@/components/BuscadorProductosIA.vue";')
replace('quotePhoneReady:false,quotePresentationOpen:false,','productExplorerOpen:false,quotePhoneReady:false,quotePresentationOpen:false,')
replace('  components: {\r\n    ProformaPresentacion,','  components: {\r\n    BuscadorProductosIA,\r\n    ProformaPresentacion,')
raw+=b'\n<style>\n.pos-page .pos-tabs .nav-pills .nav-item.pos-product-ai-launcher{margin-left:auto!important;flex:0 0 auto!important;display:flex;align-items:center;justify-content:flex-end;padding:0 3px}.pos-product-ai-search{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-width:64px;min-height:34px;padding:5px 11px;border:1px solid #64d3eb;border-radius:9px;background:linear-gradient(115deg,#087d98,#454598);color:#fff;font-size:12px;font-weight:800;line-height:1;box-shadow:0 2px 8px #00cbd522;cursor:pointer}.pos-product-ai-search:hover{background:#176a95;color:#fff}.pos-product-ai-search:focus-visible{outline:3px solid #8deaf2;outline-offset:2px}.pos-product-ai-search svg{flex-shrink:0}@media(max-width:600px){.pos-product-ai-search{min-width:56px;min-height:32px;padding:4px 8px;font-size:11px;gap:4px}}\n</style>\n'
p.write_bytes(raw)
p=root/'integration/ali-api.php';raw=p.read_bytes();old=b"require_once __DIR__.'/ali-product-labor.php';";assert raw.count(old)==1;raw=raw.replace(old,old+b"\nrequire_once __DIR__.'/ali-product-explorer.php';")
old=b"$app->post('/ali/producto',function($q,$r){";assert raw.count(old)==1;raw=raw.replace(old,old+b"if(in_array($q->getParsedBody()['mode']??'',['explore','explore_images'],true))return aliProductExplorer($this->db,$q,$r);")
p.write_bytes(raw)
print('Added independent IA lookup at end of sale tabs; existing product search and sale actions unchanged.')
