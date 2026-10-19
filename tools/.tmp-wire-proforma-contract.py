from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf-8')
needle='<button class="btn btn-outline-primary" @click="imprimir">Imprimir</button>'
assert needle in s
s=s.replace(needle,needle+'<button v-if="proforma.Presentacion===\'sistemas\'" class="btn btn-outline-primary" @click="contractOpen=true">Generar contrato</button>',1)
marker='    <article id="invoice-container"'
s=s.replace(marker,'    <b-modal v-model="contractOpen" title="Contrato de licencia y servicios de sistemas" size="lg" hide-footer lazy modal-class="quote-contract-modal"><proforma-contrato v-if="contractOpen" :quote-id="idProforma" :quote="proforma" /></b-modal>\n'+marker,1)
s=s.replace("import ProformaEditor from '@/views/ProformaEditor.vue';", "import ProformaEditor from '@/views/ProformaEditor.vue';\nimport ProformaContrato from '@/components/ProformaContrato.vue';")
s=s.replace('components:{ProformaEditor}', 'components:{ProformaEditor,ProformaContrato}').replace('editOpen:false,', 'contractOpen:false,editOpen:false,',1)
p.write_text(s,encoding='utf-8')
p=Path('integration/proforma-edit/proforma-contract.php');s=p.read_text(encoding='utf-8').replace("header('Content-Disposition: inline; filename=\"contrato-sistemas-'.(int)$cid.'.pdf\"');", "header('Content-Disposition: '.(isset($_GET['descargar'])?'attachment':'inline').'; filename=\"contrato-sistemas-'.(int)$cid.'.pdf\"');");p.write_text(s,encoding='utf-8')
