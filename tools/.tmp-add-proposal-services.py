from pathlib import Path
import json
root=Path(__file__).resolve().parent.parent
catalog_path=root/'static/data/proforma-services.json'
catalog=json.loads(catalog_path.read_text(encoding='utf-8'))
for item in catalog:
 if item['id']=='sunat_validation':
  item['description']='Envío, seguimiento y revisión de respuestas para todos los comprobantes emitidos por el sistema contratado.'
  item['text']='Gestión del envío, seguimiento y consulta de la respuesta de SUNAT para todos los comprobantes electrónicos que emita el sistema contratado, con revisión de las incidencias reportadas durante su validación.'
 if item['id']=='backups':
  item.update(name='Copias de seguridad diarias',description='Respaldo diario de la información del sistema contratado.',text='Copias de seguridad diarias de la información del sistema contratado. El almacenamiento, la retención, los responsables y el procedimiento de recuperación se definen en la implementación.',default=True,recommended=True)
catalog.insert(3,dict(id='sunat_updates',name='Actualizaciones por cambios de SUNAT',description='Adecuaciones de facturación electrónica aplicables a todos los clientes con el servicio contratado.',target='condiciones',text='Actualización de los componentes de facturación electrónica ante los cambios técnicos exigidos por SUNAT que correspondan al sistema contratado, para todos los clientes con el servicio vigente.',default=True,recommended=True))
catalog_path.write_text(json.dumps(catalog,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
p=root/'src/components/ProformaPresentacion.vue'
s=p.read_text(encoding='utf-8')
s=s.replace('<script>\n','<script>\nimport { proposalServices, defaultProposalServices, applyProposalServices } from \'../utils/proformaServices\';\n',1)
s=s.replace('assistantOpen:true,', 'supportOptions:proposalServices,supportSelected:defaultProposalServices(),supportRecommendation:[],supportNotes:[],assistantOpen:true,',1)
marker='     <section v-if="recommendation.length"'
ui='''     <details class="proposal-services" open>
      <summary>Soporte y servicios incluidos <span>{{supportSelected.length}} seleccionados</span></summary>
      <p>Marca lo que entregarás al cliente. Las selecciones se incorporan al borrador y puedes guardarlas en la plantilla del producto.</p>
      <div class="proposal-module-actions"><button type="button" @click="supportSelected=supportOptions.filter(s=>s.recommended).map(s=>s.id)" :disabled="busy || disabled">Seleccionar recomendados</button><button type="button" @click="generate('support')" :disabled="busy || disabled || loadingCatalog">Recomendar servicios con IA</button><button type="button" @click="supportSelected=[]" :disabled="busy || disabled">Limpiar servicios</button></div>
      <div class="proposal-service-list"><label v-for="service in supportOptions" :key="service.id" :class="{'is-selected':supportSelected.includes(service.id)}"><input type="checkbox" v-model="supportSelected" :value="service.id" :disabled="busy || disabled"><span><b>{{service.name}}</b><small>{{service.description}}</small><em v-if="service.recommended">Recomendado</em></span></label></div>
      <section v-if="supportRecommendation.length" class="proposal-ai-note"><strong>Servicios sugeridos para revisar</strong><p>{{supportOptions.filter(s=>supportRecommendation.includes(s.id)).map(s=>s.name).join(' · ')}}</p><p>{{supportNotes.join(' ')}}</p><button type="button" @click="supportSelected=supportRecommendation.slice();supportRecommendation=[]">Usar estos servicios recomendados</button></section>
      <button type="button" class="proposal-apply-services" @click="applyServices" :disabled="busy || disabled">Aplicar servicios seleccionados a los textos</button>
      <p>24/7 indica el horario de atención. La validación SUNAT corresponde a los comprobantes electrónicos emitidos; no incluye declaraciones tributarias.</p>
     </details>
'''
assert marker in s
s=s.replace(marker,ui+marker,1)
s=s.replace("objetivo:recommend?'recomendar':'redactar'", "objetivo:recommend==='support'?'soportes':recommend?'recomendar':'redactar'")
s=s.replace('modulos:recommend?this.modules.map(m=>m.id):this.selected,actual:', 'modulos:recommend===true?this.modules.map(m=>m.id):this.selected,soportes:this.supportSelected,actual:')
s=s.replace("if(recommend){this.recommendation=", "if(recommend==='support'){this.supportRecommendation=(data.draft.soportes_recomendados||[]).filter(id=>this.supportOptions.some(s=>s.id===id));this.supportNotes=data.draft.pendientes||[];this.success=this.supportRecommendation.length?'Revisa los servicios sugeridos y pulsa Usar estos servicios recomendados.':'La IA no sugirió servicios adicionales. Puedes seleccionarlos manualmente.';return;}if(recommend){this.recommendation=")
s=s.replace('La IA no confirm? módulos','La IA no confirmó módulos')
s=s.replace("this.draft=data.draft;this.success=", "this.draft=applyProposalServices(data.draft,this.supportSelected);this.success=")
s=s.replace("this.selected=(t.modulos||[]).filter(id=>this.modules.some(m=>m.id===id));", "this.selected=(t.modulos||[]).filter(id=>this.modules.some(m=>m.id===id));this.supportSelected=Array.isArray(t.soportes)?t.soportes.filter(id=>this.supportOptions.some(s=>s.id===id)):defaultProposalServices();")
s=s.replace('modulos:this.selected,actual:', 'modulos:this.selected,soportes:this.supportSelected,actual:')
s=s.replace("};this.reviewed=false;this.applyMode='empty';", "};this.draft=applyProposalServices(this.draft,this.supportSelected);this.reviewed=false;this.applyMode='empty';")
s=s.replace(" applyDraft(){", " applyServices(){if(this.disabled||this.busy)return;const next=applyProposalServices(this.value,this.supportSelected);if(['alcance','entregables','condiciones'].some(k=>next[k].length>12000)){this.error='Los textos superan el espacio disponible. Resume el contenido antes de aplicar los servicios.';return;}this.$emit('input',next);this.success='Servicios aplicados al alcance, la implementación y las condiciones. Guarda la configuración para reutilizarlos con este producto.';},\n applyDraft(){")
s=s.replace('next[key]=this.draft[key];','next[key]=applyProposalServices(this.draft,this.supportSelected)[key];')
# Reset selections between products when the new product has no saved template.
s=s.replace("this.templateId=0;this.templateLoaded=false;if", "this.templateId=0;this.templateLoaded=false;this.supportSelected=defaultProposalServices();if")
css='''.proposal-services{border:1px solid #ead9df;border-radius:8px;padding:12px;margin:12px 0 16px;background:#fffcfd}.proposal-services summary{cursor:pointer;font-size:13px;font-weight:600;color:#6d4253}.proposal-services summary>span{float:right;font-size:11px;font-weight:400}.proposal-service-list{display:grid;grid-template-columns:1fr 1fr;gap:8px;max-height:300px;overflow:auto;margin:10px 0 12px;padding:2px}.proposal-service-list label{display:flex;gap:8px;padding:10px;margin:0;border:1px solid #e7dfe2;border-radius:6px;font-weight:400;background:white}.proposal-service-list label.is-selected{border-color:#d599a7;background:#fff4f6}.proposal-service-list input{flex-shrink:0;margin-top:3px}.proposal-service-list b{display:block;font-size:11px}.proposal-service-list small{display:block;font-size:10px;line-height:1.5;color:#806b75;margin-top:3px}.proposal-service-list em{display:inline-block;font-style:normal;font-size:9px;color:#a32d43;margin-top:4px}.proposal-apply-services{font-weight:600}@media(max-width:600px){.proposal-service-list{grid-template-columns:1fr}}
'''
s=s.replace('</style>',css+'</style>')
p.write_text(s,encoding='utf-8')
