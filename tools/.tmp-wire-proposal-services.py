from pathlib import Path
root=Path(__file__).resolve().parent.parent
p=root/'integration/ali-proposal-assistant.php'
s=p.read_text(encoding='utf-8')
loader='''function aliProposalServiceCatalog(){
 $file=dirname(__DIR__).'/nuevo/static/data/proforma-services.json';
 if(!is_file($file))$file=dirname(__DIR__).'/static/data/proforma-services.json';
 $catalog=is_file($file)?json_decode(file_get_contents($file),true):null;
 if(!is_array($catalog))throw new RuntimeException('No se pudo cargar el catálogo de servicios.');
 return $catalog;
}
'''
s=s.replace('function aliProposalInput(',loader+'function aliProposalInput(',1)
s=s.replace("$result['objetivo']=($body['objetivo']??'redactar')==='recomendar'?'recomendar':'redactar';", "$result['objetivo']=in_array($body['objetivo']??'redactar',['redactar','recomendar','soportes'],true)?($body['objetivo']??'redactar'):'redactar';")
needle=" $current=$body['actual']??[];"
validation=""" $supportIds=$body['soportes']??[];if(!is_array($supportIds)||count($supportIds)>30)throw new InvalidArgumentException('Revisa los servicios seleccionados.');$supportMap=array_column(aliProposalServiceCatalog(),null,'id');$result['soportes']=[];foreach($supportIds as $id){if(!is_string($id)||!isset($supportMap[$id]))throw new InvalidArgumentException('Selecciona únicamente servicios disponibles.');$result['soportes'][$id]=$supportMap[$id];}$result['soportes']=array_values($result['soportes']);
"""
assert needle in s;s=s.replace(needle,validation+needle,1)
s=s.replace("'recomendados'=>['type'=>'array','items'=>['type'=>'string']]", "'recomendados'=>['type'=>'array','items'=>['type'=>'string']],'soportes_recomendados'=>['type'=>'array','items'=>['type'=>'string']]")
s=s.replace("'pendientes','recomendados'],'additionalProperties'", "'pendientes','recomendados','soportes_recomendados'],'additionalProperties'")
s=s.replace('Devuelve UNICAMENTE un objeto JSON con cinco propiedades:', 'Devuelve UNICAMENTE un objeto JSON con seis propiedades: soportes_recomendados (array de IDs de servicios),')
extra=' Los servicios seleccionados en solicitud.soportes son compromisos confirmados por el vendedor: incluyelos respetando exactamente su cobertura. Atencion 24/7 es horario de atencion, no resolucion inmediata ni SLA. Los respaldos son diarios si estan seleccionados, sin inventar retencion ni recuperacion garantizada. Validacion SUNAT cubre todos los comprobantes que emita el sistema contratado; no significa declaraciones tributarias ni aceptacion garantizada. Las actualizaciones SUNAT se aplican al sistema contratado y servicio vigente, sin prometer cumplimiento absoluto. No ofrezcas servicios no seleccionados salvo que ya figuren expresamente en textos actuales. Si objetivo es soportes, recomienda solo IDs del catalogo_servicios_disponibles pertinentes al producto; conserva los servicios confirmados seleccionados. Devuelve recomendaciones para revisar, sin convertir las opciones adicionales en compromisos de los textos. En otros objetivos soportes_recomendados sera un array vacio. Evita repetir en la prosa los servicios: sus textos oficiales se incorporan como bloques al borrador. '
s=s.replace("Es un borrador revisable y no una oferta aceptada.'", "Es un borrador revisable y no una oferta aceptada."+extra+"'")
s=s.replace("'productos_del_catalogo'=>$products]", "'productos_del_catalogo'=>$products,'catalogo_servicios_disponibles'=>$input['objetivo']==='soportes'?aliProposalServiceCatalog():[]]")
needle="$out['pendientes']=[];"
parser="$services=$data['soportes_recomendados']??[];if(!is_array($services)||count($services)>30)throw new RuntimeException('Recomendaciones de servicios no válidas.');foreach($services as $id)if(!is_string($id)||strlen($id)>80)throw new RuntimeException('Servicio recomendado no válido.');$out['soportes_recomendados']=array_values(array_unique($services));"
assert needle in s;s=s.replace(needle,parser+needle,1)
s=s.replace("return $r->withJson(['draft'=>$draft,", "$draft['soportes_recomendados']=array_values(array_intersect($draft['soportes_recomendados'],array_column(aliProposalServiceCatalog(),'id')));return $r->withJson(['draft'=>$draft,")
s=s.replace("'modulos'=>array_column($input['modulos'],'id'),'actual'", "'modulos'=>array_column($input['modulos'],'id'),'soportes'=>array_column($input['soportes'],'id'),'actual'")
p.write_text(s,encoding='utf-8')
p=root/'tools/install-proposal-assistant.php'
s=p.read_text(encoding='utf-8')
needle="copy($root.'/integration/ali-proposal-assistant.php',$live.'/ali-proposal-assistant.php');"
copy="""$serviceTarget=dirname($live).'/nuevo/static/data/proforma-services.json';if(is_file($serviceTarget))copy($serviceTarget,$backup.'/proforma-services.json');if(is_file($live.'/ali-proposal-assistant.php'))copy($live.'/ali-proposal-assistant.php',$backup.'/ali-proposal-assistant.php');$serviceSource=$root.'/static/data/proforma-services.json';if(!copy($serviceSource,$serviceTarget)||hash_file('sha256',$serviceSource)!==hash_file('sha256',$serviceTarget))throw new RuntimeException('Service catalog install failed');"""
assert needle in s;s=s.replace(needle,copy+needle,1)
p.write_text(s,encoding='utf-8')
p=root/'src/components/ProformaPresentacion.vue'
s=p.read_text(encoding='utf-8')
s=s.replace("if(!data.template)return;const t=", "if(!data.template){this.supportSelected=defaultProposalServices();return;}const t=")
needle="this.savingTemplate=true;try{await this.call({mode:'proposal_template_save'"
replacement="const finalTexts=applyProposalServices(this.value,this.supportSelected);if(['alcance','entregables','condiciones'].some(k=>finalTexts[k].length>12000)){this.templateMessage='Resume los textos antes de guardar: superan el espacio disponible.';return;}this.savingTemplate=true;try{await this.call({mode:'proposal_template_save'"
assert needle in s;s=s.replace(needle,replacement,1)
s=s.replace("actual:{alcance:this.value.alcance,entregables:this.value.entregables,condiciones:this.value.condiciones},productos:[]});this.templateLoaded=true;", "actual:{alcance:finalTexts.alcance,entregables:finalTexts.entregables,condiciones:finalTexts.condiciones},productos:[]});this.$emit('input',finalTexts);this.templateLoaded=true;")
p.write_text(s,encoding='utf-8')
