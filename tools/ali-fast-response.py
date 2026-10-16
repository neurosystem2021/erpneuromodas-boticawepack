from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
s=s.replace("@click=\"say('Hola, soy ALI. Estoy aquí para ayudarte. ¿Qué producto buscamos hoy?')\"", '@click="testVoice"', 1)
s=s.replace(" voicePreferenceChanged(){this.savePreferences();", " testVoice(){this._voiceRetryAt=0;this.say('Hola, soy ALI. Te escucho.')},\n voicePreferenceChanged(){this._voiceRetryAt=0;this.savePreferences();",1)
s=s.replace("this.voiceNotice='';if(!this.readAloud)", "if(this.voiceChoice!=='browser'&&Date.now()<(this._voiceRetryAt||0)){this._speaking=false;this.talking=false;this.scheduleWake();return}this.voiceNotice='';if(!this.readAloud)",1)
a=s.index(' async speakNatural(');b=s.index(' speakBrowser(',a)
s=s[:a]+''' async speakNatural(text,job){let deadline;try{
 const cacheKey=this.voiceChoice+':'+text.slice(0,800);this._voiceCache=this._voiceCache||new Map();let blob=this._voiceCache.get(cacheKey);
 if(!blob){const abort=new AbortController();this._voiceAbort=abort;deadline=setTimeout(()=>abort.abort(),8000);
 const response=await fetch('/api/index.php/ali/voz',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-Ali-CSRF':this.config.csrf},body:JSON.stringify({text:text.slice(0,800),voice:this.voiceChoice}),signal:abort.signal});
 if(!response.ok){const data=await response.json();throw new Error(data.error||'No se pudo generar la voz.')}blob=await response.blob();clearTimeout(deadline);
 if(this._voiceCache.size>=20)this._voiceCache.delete(this._voiceCache.keys().next().value);this._voiceCache.set(cacheKey,blob);}
 if(job!==this._voiceJob)return;this._voiceRetryAt=0;this._audioUrl=URL.createObjectURL(blob);const audio=new Audio(this._audioUrl);this._audio=audio;
 audio.onended=()=>{if(job!==this._voiceJob)return;this.cancelAudio();this._speaking=false;this.talking=false;this.scheduleWake()};
 audio.onerror=()=>{if(job!==this._voiceJob)return;this.cancelAudio();this._speaking=false;this.talking=false;this.voiceNotice='No se pudo reproducir el audio. Pulsa Probar voz para habilitarlo.';this.scheduleWake()};await audio.play();
 }catch(e){if(job!==this._voiceJob)return;this._voiceRetryAt=Date.now()+60000;this.voiceNotice=e.name==='AbortError'?'OpenAI tardó en responder. Puedes seguir dando órdenes o pulsar Probar voz.':e.message;this._speaking=false;this.talking=false;this.scheduleWake()}finally{clearTimeout(deadline)}},
''' +s[b:]
p.write_text(s,encoding='utf-8')
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8')
s=s.replace("'Hola, soy ALI. Estoy aquí para ayudarte. Dime: busca un producto, pago con efectivo o dime el total.'", "'Te escucho'",1)
s=s.replace("message:'Código '+action.number+' consultado. Revisa el producto y su cantidad en el carrito.'", "message:'Producto agregado.'",1)
s=s.replace("message:'Método de pago '+action.label+' seleccionado por S/ '+total.toFixed(2)", "message:action.label+' seleccionado.'",1)
p.write_text(s,encoding='utf-8')
p=Path('tools/test-ali-commands.cjs');s=p.read_text(encoding='utf-8').replace("['escriba codigo de barra'", "['Buscar producto por código','busca el producto por codigo de barras','escriba codigo de barra'",1);p.write_text(s,encoding='utf-8')
p=Path('tools/test-ali-checkout.cjs');s=p.read_text(encoding='utf-8').replace("const cart={loadCode:'',", "const cart={clearProductSearch(){},loadCode:'',",1);p.write_text(s,encoding='utf-8')
