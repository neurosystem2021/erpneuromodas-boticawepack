from pathlib import Path
import json
root=Path(__file__).resolve().parent.parent
p=root/'static/data/proforma-services.json'
catalog=json.loads(p.read_text(encoding='utf-8'))
catalog.extend([
 dict(id='sunat_ruc',name='Consulta SUNAT / RUC',description='Consulta de datos del RUC mediante el servicio contratado.',target='alcance',text='Consulta de datos de RUC para el registro de clientes y proveedores mediante el servicio contratado, sujeta a la disponibilidad del proveedor.'),
 dict(id='reniec',name='Consulta de identidad / RENIEC',description='Consulta de datos de identidad según el acceso autorizado del proveedor.',target='alcance',text='Consulta de datos de identidad para el registro de clientes mediante un proveedor con acceso autorizado. Cobertura y disponibilidad dependen del servicio contratado.'),
 dict(id='vps',name='Servidor VPS',description='Alojamiento del sistema en servidor virtual privado.',target='entregables',text='Servicio de servidor virtual privado (VPS) para alojar el sistema. Recursos, administración, ubicación y período contratado se detallan en las condiciones de infraestructura.'),
 dict(id='domain',name='Dominio',description='Registro o renovación del dominio acordado para el sistema.',target='entregables',text='Registro o renovación del dominio acordado para el sistema, sujeto a disponibilidad del nombre. Titularidad, extensión y período se especifican antes de la contratación.'),
 dict(id='storage',name='Almacenamiento',description='Espacio para información, documentos y respaldos del sistema.',target='entregables',text='Almacenamiento para la información, documentos y respaldos del sistema. Capacidad, retención y ampliaciones se especifican en las condiciones de infraestructura.'),
 dict(id='annual_billing',name='Primer año de facturación electrónica incluido',description='La licencia incluye la primera anualidad; selecciona el valor anual para su renovación.',target='condiciones',text='')
])
p.write_text(json.dumps(catalog,ensure_ascii=False,indent=2)+'\n',encoding='utf-8')
p=root/'src/utils/proformaServices.js'
s=p.read_text(encoding='utf-8').replace('applyProposalServices(value,ids)', 'applyProposalServices(value,ids,options={})')
s=s.replace("String(next[key]||'').split(/\\r?\\n\\s*\\r?\\n/)","String(next[key]||'').replace(/\\r\\n/g,'\\n').split(/\\n\\s*\\n/)")
s=s.replace("const selected=selectedProposalServices(ids);", "const selected=selectedProposalServices(ids).map(item=>{if(item.id!=='annual_billing')return item;const amount=Number(options.annualFee);if(!Number.isFinite(amount)||amount<=0)throw Error('Selecciona el valor anual de facturación electrónica.');return {...item,text:'El precio de la licencia incluye el primer año del servicio de facturación electrónica, cuyo valor anual es S/ '+amount.toFixed(2)+'. Esta anualidad no se cobra adicionalmente al adquirir la licencia. A partir del segundo año, la renovación de este servicio es anual por S/ '+amount.toFixed(2)+', según las condiciones acordadas.'};});")
p.write_text(s,encoding='utf-8')
p=root/'src/components/ProformaPresentacion.vue'
s=p.read_text(encoding='utf-8')
s=s.replace('supportOptions:proposalServices,','annualFee:\'\',supportOptions:proposalServices,',1)
s=s.replace('      <section v-if="supportRecommendation.length"', '''      <label v-if="supportSelected.includes('annual_billing')" class="proposal-annual-fee">Valor anual de facturación electrónica (S/)<select v-model="annualFee" :disabled="busy || disabled"><option value="">Seleccionar valor anual</option><option v-for="amount in [1000,900,800,700,600,500]" :key="amount" :value="amount">S/ {{amount.toFixed(2)}} al año</option></select><small>Primer año incluido en el precio de la licencia; renovación desde el segundo año. No modifica el total de la proforma.</small></label>
      <section v-if="supportRecommendation.length"''',1)
s=s.replace('applyProposalServices(data.draft,this.supportSelected)','applyProposalServices(data.draft,this.supportSelected,{annualFee:this.annualFee})')
s=s.replace('applyProposalServices(this.value,this.supportSelected)','applyProposalServices(this.value,this.supportSelected,{annualFee:this.annualFee})')
s=s.replace('applyProposalServices(this.draft,this.supportSelected)','applyProposalServices(this.draft,this.supportSelected,{annualFee:this.annualFee})')
s=s.replace('soportes:this.supportSelected,actual:', 'soportes:this.supportSelected,anualidad_facturacion:this.supportSelected.includes(\'annual_billing\')?Number(this.annualFee):null,actual:')
s=s.replace("this.supportSelected=Array.isArray(t.soportes)", "this.annualFee=t.anualidad_facturacion||'';this.supportSelected=Array.isArray(t.soportes)")
s=s.replace("if(!data.template){this.supportSelected=defaultProposalServices();return;}","if(!data.template){this.supportSelected=defaultProposalServices();this.annualFee='';return;}")
# Validate fee before any operation that composes selected services.
s=s.replace(" methods:{set("," methods:{servicesReady(){if(this.supportSelected.includes('annual_billing')&&!([1000,900,800,700,600,500].includes(Number(this.annualFee)))){this.error='Selecciona el valor anual de facturación electrónica antes de continuar.';return false;}return true;},set(")
s=s.replace("async generate(recommend=false){if(this.busy || this.disabled)return;", "async generate(recommend=false){if(this.busy || this.disabled)return;if(!this.servicesReady())return;")
s=s.replace("async saveTemplate(){if(!this.templateId||this.disabled||this.busy)return;", "async saveTemplate(){if(!this.templateId||this.disabled||this.busy)return;if(!this.servicesReady()){this.templateMessage=this.error;return;}")
s=s.replace("guidedDraft(){if(!this.rubro.trim())", "guidedDraft(){if(!this.servicesReady())return;if(!this.rubro.trim())")
s=s.replace("applyServices(){if(this.disabled||this.busy)return;", "applyServices(){if(this.disabled||this.busy||!this.servicesReady())return;")
s=s.replace("applyDraft(){if(!this.reviewed || !this.draft || this.disabled)return;", "applyDraft(){if(!this.reviewed || !this.draft || this.disabled || !this.servicesReady())return;")
s=s.replace('</style>', '.proposal-annual-fee{padding:10px;background:#fff4f6;border-left:3px solid #b1243e}.proposal-annual-fee small{display:block;margin-top:5px;font-size:10px;font-weight:400;line-height:1.5}\n</style>')
p.write_text(s,encoding='utf-8')
p=root/'integration/ali-proposal-assistant.php'
s=p.read_text(encoding='utf-8')
needle=" $current=$body['actual']??[];"
validation=""" $annual=$body['anualidad_facturacion']??null;$result['anualidad_facturacion']=null;if(in_array('annual_billing',array_column($result['soportes'],'id'),true)){if(!is_numeric($annual)||!in_array((float)$annual,[1000.0,900.0,800.0,700.0,600.0,500.0],true))throw new InvalidArgumentException('Selecciona el valor anual de facturación electrónica.');$result['anualidad_facturacion']=(float)$annual;}
"""
s=s.replace(needle,validation+needle,1)
s=s.replace("'soportes'=>array_column($input['soportes'],'id'),'actual'", "'soportes'=>array_column($input['soportes'],'id'),'anualidad_facturacion'=>$input['anualidad_facturacion'],'actual'")
s=s.replace('Los servicios seleccionados en solicitud.soportes', 'Si annual_billing esta seleccionado, el precio de la licencia incluye la primera anualidad de facturacion electronica: no sumes ese valor otra vez al precio. solicitud.anualidad_facturacion es el importe anual y la renovacion desde el segundo ano. No inventes precio de licencia si no consta en datos. SUNAT/RUC y RENIEC son consultas mediante proveedor autorizado contratado, no acreditan afiliacion oficial. VPS, dominio y almacenamiento solo si se seleccionan y sin inventar recursos ni capacidades. Los servicios seleccionados en solicitud.soportes')
p.write_text(s,encoding='utf-8')
