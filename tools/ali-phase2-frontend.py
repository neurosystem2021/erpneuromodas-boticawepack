from pathlib import Path
p=Path('src/utils/aliCommands.js');s=p.read_text(encoding='utf-8')
s=s.replace(" if(/^(?:acepta|", " if(/^(?:silencio|deja de escuchar|dejar de escuchar|desactivate|apagate|apagar microfono|detener microfono|deten el microfono)$/.test(text))return {type:'stopAssistant'};\n if(/^(?:acepta|",1)
s=s.replace("accept:finish};}", "accept:finish||/^(?:busca|buscar) /.test(text)};}",1)
s=s.replace("|buscame|encuentra|agrega|agregar)", "|buscame|encuentra|muestrame|muestra|mostrar|agrega|agregar)",1)
s=s.replace("el producto|un producto|producto|el|", "el producto|un producto|productos?|el|",1)
s+='''
export function aliNormalizeProductQuery(raw) {
 const numbers={uno:1,una:1,dos:2,tres:3,cuatro:4,cinco:5,seis:6,siete:7,ocho:8,nueve:9,diez:10};
 return String(raw||'').normalize('NFD').replace(/[\\u0300-\\u036f]/g,'').replace(/['"\\\\%_]/g,' ')
  .replace(/\\bcamaras\\b/gi,'camara').replace(/\\bhik vision\\b/gi,'hikvision').replace(/\\bdaua\\b/gi,'dahua')
  .replace(/\\b(?:de\\s+)?(uno|una|dos|tres|cuatro|cinco|seis|siete|ocho|nueve|diez|\\d+)\\s*(?:mega ?pixeles|megapixel(?:es)?|mp)\\b/gi,(_,n)=>(numbers[n.toLowerCase()]||n)+'MP')
  .replace(/\\s+/g,' ').trim().slice(0,160);
}
'''
p.write_text(s,encoding='utf-8')

p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
s=s.replace('<div class="ali-status" aria-live="polite">', '<div class="ali-status" aria-live="polite"><strong class="ali-state-label">{{ stateLabel }}</strong> ',1)
s=s.replace('<label><input type="checkbox" v-model="readAloud">', '<button type="button" class="ali-stop-all" @click="emergencyStop()">Detener micrófono</button><label><input type="checkbox" v-model="continuous"> Escucha continua</label><label><input type="checkbox" v-model="readAloud">',1)
s=s.replace(' <b-modal v-model="open"', ' <span class="ali-operation-state" aria-live="polite">{{ stateLabel }}</span>\n <b-modal v-model="open"',1)
s=s.replace('  <details><summary>¿Qué le puedo decir a ALI?', '  <details class="ali-command-history"><summary>Últimas órdenes</summary><ol><li v-for="(entry,i) in history" :key="i"><span>{{ entry.command }}</span> · {{ entry.result }}</li></ol><small>Historial temporal de esta página. Los números largos se ocultan.</small></details>\n  <details><summary>¿Qué le puedo decir a ALI?',1)
s=s.replace('props:{execute:{type:Function,required:true}}', "props:{execute:{type:Function,required:true},warehouse:{type:Number,default:0}}",1)
s=s.replace("open:false,wakeEnabled:false", "open:false,continuous:true,history:[],operationState:'disabled',wakeEnabled:false",1)
s=s.replace('computed:{videoLink()', "computed:{stateLabel(){if(this.operationState==='error')return 'Error';if(this.pending)return 'Esperando confirmación';if(this.busy)return this.operationState==='executing'?'Ejecutando':'Procesando';if(this.talking)return 'Respondiendo';if(this.listening)return 'Escuchando';if(this.wakeEnabled)return 'Esperando Hola ALI';return 'Desactivado'},videoLink()",1)
s=s.replace('watch:{voiceStyle()', "watch:{continuous(){this.savePreferences();if(this.wakeEnabled||this.listening)this.emergencyStop()},voiceStyle()",1)
s=s.replace("if(typeof value.readAloud==='boolean')", "if(typeof value.continuous==='boolean')this.continuous=value.continuous;if(typeof value.readAloud==='boolean')",1)
s=s.replace('readAloud:this.readAloud}', 'readAloud:this.readAloud,continuous:this.continuous}',1)
marker=' noteInteraction(event)'
s=s.replace(marker,''' emergencyStop(message='Micrófono desactivado. Pulsa Activar para volver a escuchar.'){
  this._runVersion=(this._runVersion||0)+1;this._dictationQueue=[];this.pending='';this.activeField='';this.barcodeBuffer='';this.busy=false;this.stopWake();this.stopVoice();this.operationState='disabled';this.status=message;this.execute({type:'cancelSearch'}).catch(()=>{});this.execute({type:'cancel'}).catch(()=>{});
 },
 recordCommand(command,result){const clean=v=>String(v||'').replace(/\\b\\d{6,}\\b/g,'••••').slice(0,150);this.history.unshift({command:clean(command),result:clean(result)});this.history=this.history.slice(0,10)},
 async authorize(action){if(!this.config.csrf)await this.loadConfig();return this.request('autorizar',{action:action.type,warehouse:this.warehouse})},
'''+marker,1)
s=s.replace("async request(path,body){const response=await fetch", "async request(path,body){let response;try{response=await fetch",1)
s=s.replace("body:body?JSON.stringify(body):undefined});const data=", "body:body?JSON.stringify(body):undefined})}catch(e){throw new Error('Sin conexión con el ERP. Revisa la red y vuelve a intentar.')}const data=",1)
s=s.replace("if(!response.ok)throw new Error(data.error||'No se pudo completar la solicitud.');return data", "if(!response.ok){const error=new Error(data.error||'No se pudo completar la solicitud.');if(response.status===401||response.status===403){error.auth=true;this.emergencyStop(error.message)}throw error}return data",1)
s=s.replace("async loadConfig(){try{this.config=await this.request('config')}catch(e){this.status=e.message}}", "async loadConfig(){try{this.config=await this.request('config')}catch(e){this.status=e.message;this.operationState='error'}}",1)
s=s.replace("toggleWake(){if(this.wakeEnabled){this.stopWake();this.stopVoice();this.status='Activación por voz apagada.';return}", "toggleWake(){if(this.wakeEnabled||this.listening){this.emergencyStop();return}this.operationState='listening';if(!this.continuous){this.awake=true;this.toggleVoice();return}",1)
s=s.replace("this.lastHeard=raw;\n if(/^(?:muchas", "this.lastHeard=raw;\n if(parseAliCommand(raw).type==='stopAssistant'){this.emergencyStop();return}\n if(/^(?:muchas",1)
s=s.replace("if(this.busy){this._dictationQueue", "if(this.busy){if(this._activeAction==='search'&&parseAliCommand(raw,{field:this.activeField}).type==='search'){this._dictationQueue=[];this.execute({type:'cancelSearch'}).catch(()=>{})}this._dictationQueue",1)
a=s.index(' async run(){');b=s.index(' learn(product)',a)
s=s[:a]+''' async run(){
  const entered=this.command;let action=parseAliCommand(entered,{field:this.activeField});
  if(action.type==='stopAssistant'){this.emergencyStop();return}
  if(this.busy)return;
  this.armIdle(true);
  if(action.type==='sleep'){this.handleWakeText('Gracias ALI');return}
  if(action.type==='unknown'){this.say('Con gusto. Puedes decir buscar cámaras, buscar por nombre, método de pago Yape, DNI y su número, o total.');return}
  if(action.type==='greet')this.awake=true;
  const version=this._runVersion=(this._runVersion||0)+1;this._activeAction=action.type;this.busy=true;this.operationState='processing';
  try{
   const acceptAfter=action.type==='barcodeInput'&&action.accept;
   if(action.type==='barcodeInput'){const number=action.replace?action.number:this.barcodeBuffer+action.number;if(number.length>40)throw Error('El código es demasiado largo. Di borrar código para empezar otra vez.');action={type:'barcodeDraft',number};}
   if(action.type==='backspace'||action.type==='clearField'){if(this.activeField!=='barcode')throw Error('Selecciona código de barras antes de corregir.');action={type:'barcodeDraft',number:action.type==='backspace'?this.barcodeBuffer.slice(0,-1):''};}
   if(action.type==='accept'&&this.activeField==='barcode'){if(!this.barcodeBuffer)throw Error('Primero dicta el código.');action={type:'barcode',number:this.barcodeBuffer};}
   await this.authorize(acceptAfter?{type:'barcode'}:action);if(version!==this._runVersion)return;
   this.operationState='executing';
   if(action.type==='add'&&this.results[action.index-1])action.product=this.results[action.index-1];
   let result=action.type==='greet'?{message:aliGreeting(this.config.user)}:action.type==='encouragement'?{message:aliEncouragement(this.config.user)}:await this.execute(action);
   if(version!==this._runVersion||result.superseded)return;
   if(acceptAfter)result=await this.execute({type:'barcode',number:action.number});
   if(version!==this._runVersion)return;
   if(result.field!==undefined)this.activeField=result.field;if(result.buffer!==undefined)this.barcodeBuffer=result.buffer;if(result.results)this.results=result.results;if(result.pending!==undefined)this.pending=result.pending;if(result.product)this.learn(result.product);
   this.recordCommand(entered,result.message||'Listo.');
   if(result.silent)this.status=result.message;else this.say(result.message||'Listo.');
  }catch(e){if(version!==this._runVersion)return;this.operationState='error';this.recordCommand(entered,e.message);if(e.auth)this.status=e.message;else this.say(e.message)}
  finally{
   if(version===this._runVersion){this.busy=false;this.armIdle();if(!this.continuous&&!this.wakeEnabled)this.awake=false;if(this.awake&&this._dictationQueue&&this._dictationQueue.length){const next=this._dictationQueue.shift();this.$nextTick(()=>{this.command=next;this.run()})}}
  }
 },
'''+s[b:]
s=s.replace("if(this.listening){this.stopVoice();return}", "if(this.listening){this.emergencyStop();return}",1)
s=s.replace('</style>', '''.ali-operation-state{position:fixed;right:18px;bottom:32px;max-width:260px;font-size:11px;color:#17455d;background:#ffffffee;border-radius:8px;padding:2px 8px;pointer-events:none}.ali-state-label{display:block;font-size:11px;text-transform:uppercase;margin-bottom:4px}.ali-command-history{margin:12px 0}.ali-command-history li{font-size:12px;margin:7px 0;overflow-wrap:anywhere}.ali-modal .ali-stop-all{background:#b52e3b!important}@media(max-width:767px){.ali-operation-state{bottom:111px;right:10px;max-width:220px}}
</style>''',1)
p.write_text(s,encoding='utf-8')

p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8')
s=s.replace('<ali-assistant :execute="aliExecute"', '<ali-assistant :execute="aliExecute" :warehouse="Number((ventas[index] && ventas[index].almacen || {}).IdAlmacen) || 0"',1)
s=s.replace('import AliAssistant from', "import {aliNormalizeProductQuery} from '@/utils/aliCommands';\nimport AliAssistant from",1)
s=s.replace("      const sale=this.ventas[this.index];\n      if(!sale)", "      if(action.type==='cancelSearch'){this._aliSearchSeq=(this._aliSearchSeq||0)+1;if(this._aliSearchCancel)this._aliSearchCancel('superseded');return {silent:true,message:''};}\n      const sale=this.ventas[this.index];\n      if(!sale)",1)
a=s.index("        const query=String(action.query||'').replace");b=s.index("        if(!query)",a)
s=s[:a]+"        const query=aliNormalizeProductQuery(action.query);\n"+s[b:]
s=s.replace("        const response=await this.axios(this.API+'productos/stock/'+warehouse,{params:{q:query,limit:25,sortBy:'stock',sortDesc:1}});", """        const seq=this._aliSearchSeq=(this._aliSearchSeq||0)+1;if(this._aliSearchCancel)this._aliSearchCancel('superseded');
        const source=this.axios.CancelToken.source();this._aliSearchCancel=source.cancel;
        let response;try{response=await this.axios(this.API+'productos/stock/'+warehouse,{params:{q:query,limit:25,sortBy:'stock',sortDesc:1},cancelToken:source.token});}catch(error){if(this.axios.isCancel(error)||seq!==this._aliSearchSeq)return {superseded:true,silent:true};throw error;}
        if(seq!==this._aliSearchSeq)return {superseded:true,silent:true};this._aliSearchCancel=null;""",1)
s=s.replace("      if(action.type==='pay'||action.type==='confirm'){", "      if(action.type==='pay'||action.type==='confirm'){\n        throw new Error('El cobro por ALI requiere la fase de confirmación e idempotencia. Usa el botón Pagar del POS.');",1)
a=s.index("        if(!client||!client.IdCliente){");b=s.index('        await this.setCliente(client);',a)
s=s[:a]+"        if(!client||!client.IdCliente)throw new Error('El cliente no está registrado. Revisa el formulario de clientes del POS antes de guardarlo.');\n"+s[b:]
p.write_text(s,encoding='utf-8')
