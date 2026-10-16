from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
s=s.replace("document.addEventListener('focusin',this.trackField);", "document.addEventListener('focusin',this.trackField);document.addEventListener('pointerdown',this.noteInteraction,true);document.addEventListener('input',this.noteInteraction,true);document.addEventListener('keydown',this.noteInteraction,true);",1)
s=s.replace("beforeDestroy(){document.removeEventListener('focusin',this.trackField);", "beforeDestroy(){clearTimeout(this._idleTimer);document.removeEventListener('pointerdown',this.noteInteraction,true);document.removeEventListener('input',this.noteInteraction,true);document.removeEventListener('keydown',this.noteInteraction,true);document.removeEventListener('focusin',this.trackField);",1)
marker=' trackField(event)'
s=s.replace(marker,''' noteInteraction(event){if(this.awake&&event.isTrusted)this.armIdle(true)},
 armIdle(reset=false){clearTimeout(this._idleTimer);if(reset)this._idleMisses=0;if(!this.awake)return;this._idleTimer=setTimeout(()=>this.onIdle(),20000)},
 onIdle(){if(!this.awake)return;if(this.busy||this._speaking){this.armIdle();return}this._idleMisses=(this._idleMisses||0)+1;if(this._idleMisses<2){this.say('¿Sigues ahí? Estoy aquí para ayudarte.');return}this.sleepAli('Hasta luego. Que tengas un buen día. Cuando me necesites, di Hola ALI.')},
 sleepAli(message){clearTimeout(this._idleTimer);this._idleMisses=0;this.awake=false;this._dictationQueue=[];this.activeField='';this.barcodeBuffer='';this.pending='';this.command='';this.execute({type:'cancel'}).catch(()=>{});this.open=false;this.say(message)},
'''+marker,1)
s=s.replace("say(text,voiceChoice=this.voiceChoice,preview=false){this.previewVoice='';", "say(text,voiceChoice=this.voiceChoice,preview=false){clearTimeout(this._idleTimer);this.previewVoice='';",1)
s=s.replace("stopWake(){this.wakeEnabled=false;", "stopWake(){clearTimeout(this._idleTimer);this.wakeEnabled=false;",1)
s=s.replace("scheduleWake(){clearTimeout(this._wakeTimer);", "scheduleWake(){this.armIdle();clearTimeout(this._wakeTimer);",1)
a=s.index(" if(/^(?:muchas )?gracias (?:ali|aly|ally|alli|al i)$/.test(text))")
b=s.index(' const wake=parseAliWake',a)
s=s[:a]+" if(/^(?:muchas )?gracias (?:ali|aly|ally|alli|al i)$/.test(text)){if(this.awake)this.sleepAli('De nada. Aquí estaré cuando digas Hola ALI.');return}\n"+s[b:]
s=s.replace("async run(){if(this.busy)return;", "async run(){if(this.busy)return;this.armIdle(true);",1)
s=s.replace("finally{this.busy=false;if(this.awake&&this._dictationQueue", "finally{this.busy=false;this.armIdle();if(this.awake&&this._dictationQueue",1)
s=s.replace('Di “Hola ALI” para activar y “Gracias ALI” para terminar.', 'Di “Hola ALI” para activar y “Gracias ALI” para terminar.',1)
s=s.replace('También puedes pulsar Hablar con ALI para una sola orden.', 'También puedes pulsar Hablar con ALI para una sola orden. Tras dos esperas de 20 segundos sin interacción, ALI se despide y vuelve a esperar Hola ALI.',1)
p.write_text(s,encoding='utf-8')
