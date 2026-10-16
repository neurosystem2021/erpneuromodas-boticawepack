from pathlib import Path
p=Path('src/utils/aliCommands.js');s=p.read_text(encoding='utf-8');idx=s.index('export function parseAliCommand');s=s[:idx]+'''export function aliDigits(value) {
 const words={cero:'0',uno:'1',un:'1',dos:'2',tres:'3',cuatro:'4',cinco:'5',seis:'6',siete:'7',ocho:'8',nueve:'9'};
 const parts=String(value||'').trim().split(/\\s+/).filter(Boolean);
 if(!parts.length)return '';
 const normalized=parts.map(x=>words[x]!==undefined?words[x]:x.replace(/[.,-]/g,''));
 return normalized.every(x=>/^\\d+$/.test(x))?normalized.join(''):'';
}
''' +s[idx:]
needle=" if (/^(ayuda"
a=s.index(needle)
s=s[:a]+''' const document=text.match(/^(?:(?:pon|poner|coloca|colocar|escribe|escribir|cliente|selecciona|busca)(?: el| al cliente| cliente| documento)?\\s+)?(dni|ruc)(?: del cliente| cliente)?(?: es| numero| número)?\\s+(.+)$/);
 if(document)return {type:'customer',kind:document[1],number:aliDigits(document[2])};
 const barcode=text.match(/^(?:(?:ve|ir|anda|vaya)(?: al)?\\s+)?codigo de barras?(?: y)?(?: pon| coloca| escribe| escribir| numero| busca| buscar)?\\s+(.+)$/)||text.match(/^(?:escribe|pon|coloca|busca)\\s+(?:el )?codigo(?: de barras?)?\\s+(.+)$/);
 if(barcode)return {type:'barcode',number:aliDigits(barcode[1])};
 if(/^(?:ve|ir|anda|vaya)(?: al)? (?:codigo de barras?|buscador|buscador de productos)$/.test(text))return {type:'focus',field:text.includes('codigo')?'barcode':'search'};
 const type=text.match(/^(?:(?:pon|poner|selecciona|seleccionar|cambia|cambiar|emitir|emite)(?: una| la| a)?\\s+)?(boleta|factura)(?: electronica)?$/);
 if(type)return {type:'documentType',code:type[1]==='factura'?'01':'03',label:type[1]};
''' +s[a:]
s=s.replace('metodo de pago| pago| metodo', 'metodo de pago| pago| metodo')
s=s.replace("const pay=text.match(/", "const pay=text.match(/",1)
s=s.replace("(?:pon|ponme|poner|selecciona|seleccionar|cambia)","(?:pon|ponme|poner|selecciona|seleccionar|cambia|metodo de pago|metodo)",1)
s=s.replace("const search=text.match(/", "text=text.replace(/^(?:ve|anda|vaya) al buscador(?: de productos)? y /,'').replace(/^en el buscador /,'');\n const search=text.match(/",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8');s=s.replace('@click="open=true"><i class="fas fa-microphone"></i> ALI', '@click="openSettings"><i class="fas fa-cog"></i> ALI',1)
s=s.replace('</span></div>\n <b-modal', '</span><p class="ali-inline-message" aria-live="polite">{{ status }}</p><small v-if="command" class="ali-inline-heard">Escuché: {{ command }}</small></div>\n <b-modal',1)
s=s.replace("this.awake=true;this.open=true;this.command='Hola ALI'", "this.awake=true;this.command='Hola ALI'",1)
s=s.replace("this.say('Puedes consultar información y buscar videos de '+product.Producto)","this.say('Puedes consultar información y buscar videos de '+product.Producto)")
s=s.replace("if(action.type==='unknown'){", "if(action.type==='greet'){this.awake=true}if(action.type==='unknown'){",1)
s=s.replace('voiceChoice:\'coral\',voiceNotice:', "voiceChoice:'browser',browserVoice:'',localVoices:[],voiceNotice:",1)
s=s.replace('<option value="coral">', '<option value="browser">Voz del equipo · Sin consumo API</option><option value="coral">',1)
s=s.replace('</select></label><button type="button" @click="say(', '''</select></label><label v-if="voiceChoice==='browser'">Voz del equipo<select v-model="browserVoice"><option value="">Automática</option><option v-for="voice in localVoices" :key="voice.name" :value="voice.name">{{ voice.name }}</option></select></label><button type="button" @click="say(''',1)
s=s.replace("mounted(){this.loadConfig();", "mounted(){this.refreshVoices();if(window.speechSynthesis)window.speechSynthesis.addEventListener('voiceschanged',this.refreshVoices);this.loadConfig();",1)
s=s.replace("beforeDestroy(){this.stopWake();", "beforeDestroy(){if(window.speechSynthesis)window.speechSynthesis.removeEventListener('voiceschanged',this.refreshVoices);this.stopWake();",1)
s=s.replace(' openSettings(){'," refreshVoices(){this.localVoices=window.speechSynthesis&&window.speechSynthesis.getVoices?window.speechSynthesis.getVoices().filter(v=>/^es(?:-|_)/i.test(v.lang)):[]},\n openSettings(){",1)
s=s.replace("if(this.config.enabled&&this.config.configured){this.speakNatural(text,job)}else this.speakBrowser(text,job)","if(this.voiceChoice==='browser')this.speakBrowser(text,job);else if(this.config.enabled&&this.config.configured)this.speakNatural(text,job);else{this._speaking=false;this.voiceNotice='La voz natural necesita OpenAI activado y saldo API. Puedes elegir una voz del equipo.';this.scheduleWake()}",1)
s=s.replace("this.voiceNotice=e.message+' Se usará la voz disponible en el navegador.';this.speakBrowser(text,job)","this.voiceNotice=e.message;this._speaking=false;this.scheduleWake()",1)
s=s.replace("const selected=spanish.find(v=>/", "const selected=spanish.find(v=>v.name===this.browserVoice)||spanish.find(v=>/",1)
s=s.replace('voice.pitch=1.3;voice.rate=1.02;', 'voice.pitch=1.08;voice.rate=1;',1)
s=s.replace('</style>', '''.ali-inline-message{max-width:265px;margin:6px 0 0;padding:8px 12px;background:#fffdf1;border:1px solid #edcc75;border-radius:12px;color:#69430e;font-size:12px;line-height:1.35;box-shadow:0 4px 12px #815b1622}.ali-inline-heard{max-width:265px;background:#ffffffed;padding:4px 8px;border-radius:8px;font-size:10px;color:#476270;overflow-wrap:anywhere}@media(max-width:767px){.ali-inline-message,.ali-inline-heard{max-width:195px}}
</style>''')
p.write_text(s,encoding='utf-8')
