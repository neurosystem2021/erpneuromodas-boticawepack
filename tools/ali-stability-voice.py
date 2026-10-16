from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8');s=s.replace("import {parseAliCommand}","import {parseAliCommand,parseAliWake}",1)
s=s.replace("wakeEnabled ? (awake ? 'ALI activa · Apagar voz' : 'Esperando Hola ALI · Apagar voz')", "wakeEnabled ? (talking ? 'ALI está hablando · Apagar voz' : (micState==='starting'||micState==='restarting' ? 'Reactivando micrófono…' : (awake ? 'ALI escucha órdenes · Apagar voz' : 'Escuchando: di Hola ALI · Apagar voz')))",1)
s=s.replace("{{ listening ? 'Te escucho' : (busy ? 'Procesando…' : 'Dime tu orden') }}", "{{ talking ? 'Estoy hablando' : (listening ? 'Te escucho' : (busy ? 'Procesando…' : 'Preparando micrófono…')) }}",1)
s=s.replace('<small v-if="command" class="ali-inline-heard">', '<p v-if="voiceNotice" class="ali-inline-voice-error" role="status">{{ voiceNotice }}</p><small class="ali-inline-heard">Voz: {{ voiceChoice===\'browser\' ? (browserVoice || \'del equipo\') : voiceChoice + \' · OpenAI\' }}</small><small v-if="command" class="ali-inline-heard">',1)
s=s.replace(' <b-modal v-model="open"', ' <small v-if="wakeEnabled && !awake && lastHeard" class="ali-wake-heard">Escuché: {{ lastHeard }}</small>\n <b-modal v-model="open"',1)
s=s.replace("open:false,wakeEnabled:false,awake:false", "open:false,wakeEnabled:false,awake:false,talking:false,micState:'off',lastHeard:''",1)
s=s.replace("},mounted(){document.addEventListener", "},watch:{voiceChoice(){this.voicePreferenceChanged()},browserVoice(){this.savePreferences()},readAloud(){this.savePreferences();if(!this.readAloud){this.cancelAudio();this._speaking=false;this.talking=false;this.scheduleWake()}}},mounted(){this.loadPreferences();document.addEventListener",1)
a=s.index(' trackField(event)');s=s[:a]+''' loadPreferences(){try{const value=JSON.parse(localStorage.getItem('aliVoicePreferences')||'{}');if(['browser','coral','nova','shimmer'].includes(value.voice))this.voiceChoice=value.voice;if(typeof value.browserVoice==='string')this.browserVoice=value.browserVoice;if(typeof value.readAloud==='boolean')this.readAloud=value.readAloud;}catch(e){}},
 savePreferences(){try{localStorage.setItem('aliVoicePreferences',JSON.stringify({voice:this.voiceChoice,browserVoice:this.browserVoice,readAloud:this.readAloud}))}catch(e){}},
 voicePreferenceChanged(){this.savePreferences();this.cancelAudio();this._speaking=false;this.talking=false;this.voiceNotice='';this.scheduleWake()},
 armVoiceDeadline(job,text){clearTimeout(this._speechTimer);this._speechTimer=setTimeout(()=>{if(job!==this._voiceJob)return;this.cancelAudio();this._speaking=false;this.talking=false;this.voiceNotice='La reproducción de voz se detuvo. El micrófono vuelve a escuchar.';this.scheduleWake()},Math.min(90000,Math.max(15000,text.length*90+30000)))},
''' +s[a:]
s=s.replace('this._speaking=true;', 'this._speaking=true;this.talking=true;')
s=s.replace('this._speaking=false;', 'this._speaking=false;this.talking=false;')
s=s.replace("this._speaking=true;this.talking=true;if(this.voiceChoice", "this._speaking=true;this.talking=true;this.armVoiceDeadline(job,text);if(this.voiceChoice",1)
s=s.replace("cancelAudio(){this._voiceJob", "cancelAudio(){clearTimeout(this._speechTimer);this._voiceJob",1)
s=s.replace("stopWake(){this.wakeEnabled=false;this.awake=false;this.pauseWake()}", "stopWake(){this.wakeEnabled=false;this.awake=false;this.micState='off';this.pauseWake()}",1)
s=s.replace("this.wakeEnabled=true;this.awake=false;this.status=", "this.wakeEnabled=true;this.awake=false;this._wakeFailures=0;this.lastHeard='';this.status=",1)
s=s.replace("this._wakeTimer=setTimeout(()=>this.startWake(),300)", "this.micState='restarting';this._wakeTimer=setTimeout(()=>this.startWake(),150)",1)
a=s.index(' startWake(){');b=s.index(' stopVoice(){',a)
s=s[:a]+''' startWake(){if(!this.wakeEnabled||this._wakeRecognition||this._speaking)return;const API=window.SpeechRecognition||window.webkitSpeechRecognition;if(!API){this.stopWake();return}const speech=new API();this._wakeRecognition=speech;this.micState='starting';speech.lang='es-PE';speech.continuous=true;speech.interimResults=true;
 speech.onstart=()=>{if(this._wakeRecognition===speech){this.listening=this.awake;this.micState=this.awake?'listening':'waiting'}};
 speech.onresult=e=>{if(this._wakeRecognition!==speech)return;this._wakeFailures=0;for(let i=e.resultIndex||0;i<e.results.length;i++){const row=e.results[i],raw=row[0].transcript;this.lastHeard=raw;if(!this.awake){this.handleWakeText(raw);if(row.isFinal!==false){this._wakeFragment=raw;this._wakeFragmentAt=Date.now()}}else if(row.isFinal!==false)this.handleWakeText(raw);if(this._wakeRecognition!==speech)break}};
 speech.onend=()=>{if(this._wakeRecognition!==speech)return;this._wakeRecognition=null;this.listening=false;this.scheduleWake()};
 speech.onerror=e=>{if(this._wakeRecognition!==speech)return;if(e.error==='no-speech'||e.error==='aborted')return;if(e.error==='network'&&(this._wakeFailures||0)<3){this._wakeFailures=(this._wakeFailures||0)+1;this.pauseWake();this.micState='restarting';this.status='Reconectando el servicio de voz…';this._wakeTimer=setTimeout(()=>this.startWake(),this._wakeFailures*1000);return}this.stopWake();this.micState='error';this.status=e.error==='not-allowed'?'Permite el micrófono en el navegador y vuelve a activar Hola ALI.':'El servicio de voz se detuvo: '+e.error+'. Vuelve a activar la escucha.';};
 try{speech.start()}catch(e){this.stopWake();this.micState='error';this.status='No se pudo activar el micrófono. Intenta nuevamente.'}},
 handleWakeText(raw){const text=String(raw||'').normalize('NFD').replace(/[\\u0300-\\u036f]/g,'').toLowerCase().replace(/[.,!?¿¡]/g,' ').replace(/\\s+/g,' ').trim();this.lastHeard=raw;
 if(/^(?:muchas )?gracias (?:ali|aly|ally|alli|al i)$/.test(text)){if(this.awake){this.awake=false;this._dictationQueue=[];this.activeField='';this.barcodeBuffer='';this.pending='';this.execute({type:'cancel'}).catch(()=>{});this.open=false;this.say('De nada. Aquí estaré cuando digas Hola ALI.')}return}
 const wake=parseAliWake(raw,this._wakeFragment,Date.now()-(this._wakeFragmentAt||0));
 if(wake.wake){this.awake=true;this.micState='listening';this._wakeFragment='';this.command=wake.command||'Hola ALI';this.run();return}
 if(!this.awake)return;if(this.busy){if(this.activeField==='barcode'){this._dictationQueue=this._dictationQueue||[];if(this._dictationQueue.length<10)this._dictationQueue.push(raw)}return}this.command=raw;this.run()},
''' +s[b:]
s=s.replace('</style>', '''.ali-inline-voice-error{max-width:265px;background:#fff1d4;border:1px solid #e6c36c;border-radius:9px;padding:7px 10px;font-size:11px;color:#825c13;margin:5px 0}.ali-wake-heard{position:fixed;right:16px;bottom:30px;max-width:270px;padding:4px 8px;border-radius:8px;background:#fffffff0;color:#416276;font-size:10px;overflow-wrap:anywhere}@media(max-width:767px){.ali-inline-voice-error{max-width:195px}.ali-wake-heard{bottom:110px;right:10px;max-width:225px}}
</style>''')
p.write_text(s,encoding='utf-8')
