from pathlib import Path
p=Path('src/components/ClienteTelefonoRapido.vue');s=p.read_text(encoding='utf-8').replace("+1;this.error='';this.phone=''", "+1;this.busy=false;this.error='';this.phone=''");p.write_text(s,encoding='utf-8')
p=Path('src/components/ProformaLista.vue');s=p.read_text(encoding='utf-8')
slot='''              <template slot="EsFavoritoFlag" slot-scope="data"><i @click="cambiarFavorito(data.item,data.item.EsFavoritoFlag?0:1)" :class="data.item.EsFavoritoFlag?'fas fa-star':'far fa-star'" style="cursor:pointer;color:#c79424" title="Cambiar favorito"></i></template>
'''
for marker in ['<b-tab title="Proformas Facturadas">','<b-tab title="Proformas Favoritas">']:
 start=s.index(marker); pos=s.index('              <template slot="ClienteTelefono"',start);s=s[:pos]+slot+s[pos:]
s=s.replace("displayFields(fields){if(this.quoteType!=='sistemas')return fields;const out=fields.slice();out.splice", "displayFields(fields){const out=fields.slice();if(!out.some(f=>f.key==='EsFavoritoFlag'))out.push({key:'EsFavoritoFlag',label:'Favorito',thStyle:{width:'80px'}});if(this.quoteType!=='sistemas')return out;out.splice")
p.write_text(s,encoding='utf-8')
p=Path('integration/proforma-edit/proforma-pdf-render.php');s=p.read_text(encoding='utf-8').replace("(!empty($quote['ClienteTelefono'])?", "($systems&&!empty($quote['ClienteTelefono'])?");p.write_text(s,encoding='utf-8')
