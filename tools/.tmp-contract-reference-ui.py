from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'src/components/ProformaContrato.vue'
s=f.read_text(encoding='utf-8')
def change(old,new):
 global s
 assert s.count(old)==1,(old[:80],s.count(old))
 s=s.replace(old,new)
change('<h3>1. Datos de las partes</h3>', '''<h3>1. Identificación del contrato y las partes</h3>
    <div class="contract-grid"><label>Referencia del contrato *<input v-model.trim="form.numeroContrato" required maxlength="40" :disabled="busy" placeholder="018-2026"></label><label>Lugar de celebración<input v-model.trim="form.ciudad" maxlength="100" :disabled="busy" placeholder="Ciudad acordada"></label></div>
    <p class="contract-hint">La referencia se propone a partir de la proforma. Revisa el número y los documentos de los firmantes antes de generar el contrato.</p>''')
change('<label>Representante legal del cliente<input v-model.trim="form.representanteCliente" maxlength="180" :disabled="busy" placeholder="Cuando corresponda; de lo contrario firma el cliente"></label>', '''<div class="contract-grid"><label>Documento del consultor<input v-model.trim="form.documentoConsultor" maxlength="20" :disabled="busy" placeholder="DNI u otro documento"></label><label>Documento de la gerente general<input v-model.trim="form.documentoRepresentanteProveedor" maxlength="20" :disabled="busy" placeholder="Verifica el documento vigente"></label></div>
    <div class="contract-grid"><label>Representante legal del cliente<input v-model.trim="form.representanteCliente" maxlength="180" :disabled="busy" placeholder="Cuando corresponda; de lo contrario firma el cliente"></label><label>Documento del representante del cliente<input v-model.trim="form.documentoRepresentanteCliente" maxlength="20" :disabled="busy" placeholder="DNI u otro documento del representante"></label></div>''')
change('    <label>Acuerdos particulares<textarea', '''    <label>Período de soporte<textarea v-model.trim="form.soporteVigencia" rows="2" maxlength="1000" :disabled="busy" placeholder="Duración, inicio de cobertura y renovación acordados."/></label>
    <button type="button" class="contract-secondary" :disabled="busy" @click="useReferencePeriods">Usar plazos del modelo: soporte 12 meses e implementación 10 días</button>
    <p class="contract-hint">Al seleccionar estos plazos reemplazas los campos de soporte e implementación. Revisa el inicio y la modalidad acordados con el cliente.</p>
    <h3>4. Compromisos de esta contratación</h3>
    <p class="contract-hint">Se incorporarán únicamente los compromisos que selecciones. Los módulos, servicios SUNAT, respaldos y anualidad se toman de la proforma guardada.</p>
    <div class="contract-choice-actions"><button type="button" class="contract-secondary" :disabled="busy" @click="form.compromisos=clauseCatalog.filter(item=>item.recommended).map(item=>item.id)">Seleccionar recomendaciones del modelo</button><button type="button" class="contract-secondary" :disabled="busy" @click="form.compromisos=[]">Limpiar selección</button></div>
    <div class="contract-clause-options"><label v-for="item in clauseCatalog" :key="item.id" class="contract-check"><input type="checkbox" v-model="form.compromisos" :value="item.id" :disabled="busy"><span><b>{{item.name}}</b><small>{{item.text}}</small></span></label></div>
    <h3>5. Condiciones particulares y datos de pago</h3>
    <label>Cuentas y datos para el pago<textarea v-model.trim="form.cuentasPago" rows="3" maxlength="3000" :disabled="busy" placeholder="Titular, banco, cuenta y CCI vigentes que se incluirán en este contrato."/></label>
    <label>Acuerdos particulares<textarea''')
change("import {DEFAULT_CONSULTANT,DEFAULT_MANAGER} from '@/utils/proformaSignatories';", "import {DEFAULT_CONSULTANT,DEFAULT_MANAGER} from '@/utils/proformaSignatories';\nimport clauseCatalog from '../../static/data/proforma-contract-clauses.json';")
change("data(){return {loading:true", "data(){return {clauseCatalog,loading:true")
change("form:{cliente:''", "form:{numeroContrato:'',ciudad:'',documentoConsultor:'',documentoRepresentanteProveedor:'22641497',documentoRepresentanteCliente:'',soporteVigencia:'12 meses, contados desde la activación del servicio.',compromisos:[],cuentasPago:'',cliente:''")
change("this.form.direccion=client.direccion||'';", "this.form.direccion=client.direccion||'';this.form.numeroContrato=data.numeroContrato||String(this.quote.Numero||this.quoteId).padStart(3,'0')+'-'+(this.quote.Anio||new Date().getFullYear());")
change("methods:{money(value)", "methods:{useReferencePeriods(){this.form.soporteVigencia='12 meses, contados desde la activación del servicio.';this.form.plazoImplementacion='10 días calendario, contados desde la entrega de la información y los accesos acordados con el cliente.';},money(value)")
change('de ambas partes. Cada nueva versión', 'de las partes. Los anexos detallan los módulos y servicios acordados. Cada nueva versión')
change('</style>', '''.contract-form .contract-secondary{background:#fff;color:#a5233c;border:1px solid #dfc8d0;padding:8px 11px;font-size:11px;text-align:left}.contract-choice-actions{display:flex;flex-wrap:wrap;gap:8px;margin:12px 0}.contract-clause-options{padding:13px;border:1px solid #ead9e0;border-radius:7px;background:#fffbfc;margin-bottom:14px}.contract-clause-options .contract-check{margin-bottom:14px}.contract-clause-options .contract-check:last-child{margin-bottom:0}.contract-clause-options b{display:block;font-size:12px;color:#733d4e}.contract-clause-options small{display:block;font-size:11px;font-weight:400;color:#8b6d78;line-height:1.5;margin-top:4px}
</style>''')
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'integration/proforma-edit/proforma-contract.php'
s=f.read_text(encoding='utf-8')
old="'total'=>qcTotal($source['products'])/100,'cliente'=>"
assert old in s
s=s.replace(old,"'total'=>qcTotal($source['products'])/100,'numeroContrato'=>qcContractReference($source),'cliente'=>")
f.write_text(s,encoding='utf-8',newline='\n')
print('Contract form updated with reference model, signatory documents and explicit selectable commitments.')
