from pathlib import Path

voices=['alloy','ash','ballad','coral','echo','fable','nova','onyx','sage','shimmer','verse','marin','cedar']
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
a=s.index('<label>Voz de ALI<select');b=s.index('<label v-if="voiceChoice',a)
s=s[:a]+'''<label>Voz de ALI<select v-model="voiceChoice" aria-label="Voz de ALI"><option value="browser">Voz del equipo · Sin consumo API</option><option v-for="voice in cloudVoices" :key="voice.id" :value="voice.id">{{ voice.name }}</option></select></label><label>Estilo<select v-model="voiceStyle" aria-label="Estilo de voz"><option value="natural">Natural</option><option value="dulce">Dulce y suave</option><option value="infantil">Infantil de cuento</option><option value="alegre">Alegre y animada</option></select></label>'''+s[b:]
s=s.replace('@click="testVoice">', '@click="testVoice()">',1)
marker='  <form class="ali-command"'
s=s.replace(marker,'''  <details class="ali-voice-gallery" open><summary>Comparar 13 voces de OpenAI</summary><p>Escucha la misma frase con cada voz y elige tu favorita. Las pruebas usan tu cuenta API. El estilo infantil cambia la interpretación; no corresponde a una edad específica.</p><div class="ali-voice-grid"><article v-for="voice in cloudVoices" :key="voice.id" :class="['ali-voice-card',{'is-selected':voiceChoice===voice.id}]" :data-voice="voice.id"><strong>{{ voice.name }}</strong><small v-if="voiceChoice===voice.id">Elegida para ALI</small><div><button type="button" @click="testVoice(voice.id)" :aria-label="'Escuchar '+voice.name">{{ talking && previewVoice===voice.id ? 'Reproduciendo…' : 'Escuchar' }}</button><button type="button" @click="voiceChoice=voice.id" :aria-pressed="voiceChoice===voice.id">{{ voiceChoice===voice.id ? 'Elegida' : 'Elegir' }}</button></div></article></div><button type="button" @click="stopVoice">Detener audio</button></details>
'''+marker,1)
s=s.replace("voiceChoice:'browser',browserVoice:","voiceChoice:'browser',voiceStyle:'infantil',previewVoice:'',cloudVoices:"+str([{'id':v,'name':v.capitalize()} for v in voices])+",browserVoice:",1)
s=s.replace('watch:{voiceChoice()', 'watch:{voiceStyle(){this.voicePreferenceChanged()},voiceChoice()',1)
s=s.replace("if(['browser','coral','nova','shimmer'].includes(value.voice))", "if(value.voice==='browser'||this.cloudVoices.some(v=>v.id===value.voice))",1)
s=s.replace("this.voiceChoice=value.voice;if(typeof value.browserVoice", "this.voiceChoice=value.voice;if(['natural','dulce','infantil','alegre'].includes(value.style))this.voiceStyle=value.style;if(typeof value.browserVoice",1)
s=s.replace('voice:this.voiceChoice,browserVoice:', 'voice:this.voiceChoice,style:this.voiceStyle,browserVoice:',1)
s=s.replace("testVoice(){this._voiceRetryAt=0;this.say('Hola, soy ALI. Te escucho.')}", "testVoice(voice=this.voiceChoice){this._voiceRetryAt=0;this.say('Hola, soy ALI. Te escucho. ¿Qué producto buscamos hoy?',voice,true);this.previewVoice=voice}",1)
s=s.replace(";this.$refs.configPanel.scrollIntoView({block:'start'})",' ',1)
s=s.replace('say(text){this.pauseWake();', "say(text,voiceChoice=this.voiceChoice,preview=false){this.previewVoice='';this.pauseWake();",1)
s=s.replace("if(this.voiceChoice!=='browser'&&Date.now()", "if(voiceChoice!=='browser'&&Date.now()",1)
s=s.replace("if(!this.readAloud){this._speaking=false;this.talking=false;this.scheduleWake();return}", "if(!this.readAloud&&!preview){this._speaking=false;this.talking=false;this.scheduleWake();return}",1)
s=s.replace("if(this.voiceChoice==='browser')this.speakBrowser(text,job);else if(this.config.enabled&&this.config.configured)this.speakNatural(text,job)", "if(voiceChoice==='browser')this.speakBrowser(text,job);else if(this.config.enabled&&this.config.configured)this.speakNatural(text,job,voiceChoice,this.voiceStyle)",1)
s=s.replace('async speakNatural(text,job){', 'async speakNatural(text,job,voiceChoice=this.voiceChoice,style=this.voiceStyle){',1)
s=s.replace("const cacheKey=this.voiceChoice+':'+text", "const cacheKey=voiceChoice+':'+style+':'+text",1)
s=s.replace('voice:this.voiceChoice}),signal:abort.signal', 'voice:voiceChoice,style}),signal:abort.signal',1)
s=s.replace('voice.pitch=1.2;voice.rate=1;', "voice.pitch=({natural:1,dulce:1.1,infantil:1.3,alegre:1.15})[this.voiceStyle]||1;voice.rate=this.voiceStyle==='alegre'?1.08:1;",1)
s=s.replace('</style>', '''.ali-voice-gallery{margin:16px 0;padding:14px;border:1px solid #d5e4ed;border-radius:12px;background:white}.ali-voice-gallery>p{font-size:12px;color:#416276;margin:10px 0}.ali-voice-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin:12px 0}.ali-voice-card{padding:10px;border:1px solid #d5e4ed;border-radius:10px;display:flex;flex-direction:column;gap:5px}.ali-voice-card.is-selected{border-color:#079d7b;background:#e5f6ef}.ali-voice-card>div{display:flex;gap:5px;margin-top:auto;flex-wrap:wrap}.ali-voice-card small{font-size:10px;color:#146a50}.ali-modal .ali-voice-card button{font-size:11px;padding:7px 9px}.ali-voice-card button[aria-pressed=true]{background:#137d60}@media(max-width:767px){.ali-voice-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
</style>''',1)
p.write_text(s,encoding='utf-8')

p=Path('integration/ali-api.php');s=p.read_text(encoding='utf-8')
marker='return function($app){'
s=s.replace(marker,'''function aliVoiceOptions(){return ['alloy','ash','ballad','coral','echo','fable','nova','onyx','sage','shimmer','verse','marin','cedar'];}
function aliVoiceInstructions($style){
 $styles=[
  'natural'=>'Usa la voz elegida con tono natural, conversacional y claro.',
  'dulce'=>'Usa un tono dulce, cálido y suave, con entonación amable y serena.',
  'infantil'=>'Interpreta un personaje infantil ficticio de cuento, de voz más aguda, dulce, alegre y tierna. Mantén la articulación clara sin sonar chillona ni robótica.',
  'alegre'=>'Usa un tono alegre, expresivo y animado, con ritmo ágil y energía amable.'
 ];return isset($styles[$style])?'Habla en español latinoamericano claro. '.$styles[$style].' Eres ALI, un pececito dorado amigable. Lee solo el texto indicado. No imites a ninguna persona real.':null;
}
'''+marker,1)
s=s.replace("$voice=$p['voice']??'coral';if($text===''||mb_strlen($text)>800||!in_array($voice,['coral','nova','shimmer'],true))", "$voice=$p['voice']??'coral';$style=$p['style']??'infantil';$instructions=is_string($style)?aliVoiceInstructions($style):null;if($text===''||mb_strlen($text)>800||!in_array($voice,aliVoiceOptions(),true)||$instructions===null)",1)
a=s.index("'instructions'=>'Habla en español latinoamericano claro, con una voz");b=s.index("'])]);$audio",a)
s=s[:a]+"'instructions'=>$instructions"+s[b+1:]
p.write_text(s,encoding='utf-8')
