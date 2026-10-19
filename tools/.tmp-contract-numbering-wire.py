from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'integration/proforma-edit/proforma-contract.php'
s=f.read_text(encoding='utf-8')
s=s.replace("require_once __DIR__.'/proforma-contract-core.php';", "require_once __DIR__.'/proforma-contract-core.php';require_once __DIR__.'/proforma-contract-numbering.php';")
old="'numeroContrato'=>qcContractReference($source),'cliente'=>"
new="'numeroContrato'=>$numberInfo['numeroContrato'],'numeroAsignado'=>$numberInfo['numeroAsignado'],'cliente'=>"
assert old in s
s=s.replace(old,new)
old="if($method==='GET'){$source=qcSource($db,$id);"
new="if($method==='GET'){$source=qcSource($db,$id);$year=filter_var($_GET['anio']??(new DateTimeImmutable('now',new DateTimeZone('America/Lima')))->format('Y'),FILTER_VALIDATE_INT);if(!$year||$year<1900||$year>2100)qcFail(422,'Revisa el año del contrato.');$numberInfo=qcSequenceInfo($db,$id,$year);"
assert old in s
s=s.replace(old,new)
old="$input=qcInput($body,$source);$saved=qcSaveDraft"
new="$input=qcInput($body,$source);$input['numeroContrato']=qcAssignNumber($db,$id,(int)substr($input['fecha'],0,4));$saved=qcSaveDraft"
assert old in s
s=s.replace(old,new).replace("'version'=>$saved['version'],'url'", "'version'=>$saved['version'],'numeroContrato'=>$input['numeroContrato'],'url'")
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'src/components/ProformaContrato.vue'
s=f.read_text(encoding='utf-8')
s=s.replace('Referencia del contrato *<input v-model.trim="form.numeroContrato" required maxlength="40" :disabled="busy" placeholder="018-2026">', 'Número del contrato (correlativo)<input :value="form.numeroContrato" readonly aria-readonly="true" maxlength="40">')
s=s.replace('La referencia se propone a partir de la proforma. Revisa el número y los documentos de los firmantes antes de generar el contrato.', 'El correlativo es independiente de la proforma y se asigna al guardar. Las nuevas versiones conservan el mismo número. Revisa los documentos de los firmantes antes de generar el contrato.')
s=s.replace('clauseCatalog,loading:true', 'clauseCatalog,numberAssigned:false,numberSequence:0,loading:true')
s=s.replace("watch:{form:{deep:true,handler(){this.reviewed=false;}}}", "watch:{form:{deep:true,handler(){this.reviewed=false;}},'form.fecha'(){if(!this.loading&&!this.numberAssigned)this.refreshNumber();}}")
s=s.replace("this.latest=data.contract;const client", "this.latest=data.contract;this.numberAssigned=!!data.numeroAsignado;const client")
old="this.previousMode=this.form.modoAdelanto;}}catch(e)"
new="this.previousMode=this.form.modoAdelanto;}if(data.numeroContrato)this.form.numeroContrato=data.numeroContrato;}catch(e)"
assert old in s
s=s.replace(old,new)
s=s.replace('methods:{useReferencePeriods()', "methods:{async refreshNumber(){const year=String(this.form.fecha).slice(0,4);if(!/^\\d{4}$/.test(year))return;const sequence=++this.numberSequence;try{const r=await fetch('/api/proforma-contract.php?id='+encodeURIComponent(this.quoteId)+'&anio='+year,{credentials:'same-origin'});const data=await r.json();if(sequence!==this.numberSequence)return;if(!r.ok)throw Error(data.error||'No se pudo consultar el correlativo.');this.form.numeroContrato=data.numeroContrato;this.numberAssigned=!!data.numeroAsignado;}catch(e){if(sequence===this.numberSequence)this.error=e.message;}},useReferencePeriods()")
s=s.replace("this.latest={id:data.id,version:data.version,url:data.url,stale:false};", "this.latest={id:data.id,version:data.version,url:data.url,stale:false};if(data.numeroContrato){this.form.numeroContrato=data.numeroContrato;this.numberAssigned=true;}")
s=s.replace('Contrato generado. Abre el PDF', 'Contrato generado. Abre el PDF')
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'src/components/ProformaContexto.vue'
s=f.read_text(encoding='utf-8').replace('</style>', '.context-block>p,.context-block li>p{text-align:justify;text-justify:inter-word;white-space:normal;word-break:normal;overflow-wrap:break-word}.context-intro>p{text-align:left}@media(max-width:650px){.context-block>p,.context-block li>p{text-align:left}}\n</style>')
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'integration/proforma-edit/proforma-contract-template.php'
s=f.read_text(encoding='utf-8')
s=s.replace("'<h2>Alcance de la solución</h2>'.qcTextParagraphs($modules['text'])", "'<h2>Alcance de la solución</h2>'.qpContextHtml($modules['text'])")
s=s.replace("qcTextParagraphs($q['EntregablesSistema']).'</section>'", "qpContextHtml($q['EntregablesSistema'],'delivery').'</section>'")
s=s.replace("qcTextParagraphs($annual['text']);", "qpContextHtml($annual['text'],'conditions');")
s=s.replace('.clause p{text-align:justify;', '.clause p,.solution p,.module p,.module li,.commitments li,.services td{text-align:justify;')
s=s.replace(' </style></head><body>', ''' .context-block{margin:8px 0;padding:9px 11px;border:1px solid #eadce2;page-break-inside:avoid}.context-block h3{font-size:8px;letter-spacing:.6px;color:#a02037;margin:0 0 6px;page-break-after:avoid}.context-block p,.context-block li,.context-items td{font-size:9px;line-height:1.6;text-align:justify}.context-block ol{padding-left:17px;margin:4px 0}.context-block li{margin:5px 0;page-break-inside:avoid}.context-items{table-layout:fixed}.context-items td{width:50%;padding:5px 8px 5px 0}.context-items.context-single td{width:100%}.context-items tr{page-break-inside:avoid}.context-items span{color:#b5223d}.context-intro{background:#fff6f8;border-left:3px solid #b51e36}
 </style></head><body>''')
f.write_text(s,encoding='utf-8',newline='\n')
# Proposal PDF paragraphs follow the same alignment; stored texts are unchanged.
f=root/'integration/proforma-edit/proforma-pdf-render.php'
s=f.read_text(encoding='utf-8').replace('</style></head><body>', '.context-block p,.context-block li,.context-items td,.solution-detail{text-align:justify;word-wrap:break-word}</style></head><body>')
f.write_text(s,encoding='utf-8',newline='\n')
for name in ['tools/test-proforma-contract-ui.cjs','tools/.tmp-proforma-signatories-installed.cjs']:
 f=root/name;s=f.read_text(encoding='utf-8').replace('Referencia del contrato *','Número del contrato (correlativo)')
 if name.endswith('installed.cjs'):s=s.replace("'018-2026'","'141-2026'")
 f.write_text(s,encoding='utf-8',newline='\n')
print('Independent annual numbering wired; proposal and contract paragraphs/list alignment updated.')
