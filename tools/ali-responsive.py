from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
s=s.replace("talking:false,micState:", "talking:false,voiceLoading:false,micState:",1)
s=s.replace("talking && previewVoice===voice.id ? 'Reproduciendo…' : 'Escuchar'", "previewVoice===voice.id && voiceLoading ? 'Preparando…' : (talking && previewVoice===voice.id ? 'Reproduciendo…' : 'Escuchar')",1)
s=s.replace("else if(this.config.enabled&&this.config.configured)this.speakNatural(text,job,voiceChoice,this.voiceStyle);", "else if(this.config.enabled&&this.config.configured){this._speaking=false;this.talking=false;this.voiceLoading=true;this.speakNatural(text,job,voiceChoice,this.voiceStyle);this.scheduleWake()}",1)
s=s.replace("cancelAudio(){clearTimeout(this._speechTimer);", "cancelAudio(){this.voiceLoading=false;clearTimeout(this._speechTimer);",1)
s=s.replace("if(job!==this._voiceJob)return;this._voiceRetryAt=0;", "if(job!==this._voiceJob)return;this.voiceLoading=false;this.pauseWake();this._speaking=true;this.talking=true;this._voiceRetryAt=0;",1)
s=s.replace("}catch(e){if(job!==this._voiceJob)return;this._voiceRetryAt=", "}catch(e){if(job!==this._voiceJob)return;this.voiceLoading=false;this._voiceRetryAt=",1)
s=s.replace("if(!this.awake)return;if(this.busy){if(this.activeField==='barcode'){this._dictationQueue=this._dictationQueue||[];if(this._dictationQueue.length<10)this._dictationQueue.push(raw)}return}this.command=raw;", "if(!this.awake)return;this.armIdle(true);if(this.voiceLoading){this.cancelAudio();this._speaking=false;this.talking=false}if(this.busy){this._dictationQueue=this._dictationQueue||[];if(this._dictationQueue.length<10)this._dictationQueue.push(raw);return}this.command=raw;",1)
s=s.replace("this.say(this.activeField==='barcode'?'Con gusto. Estoy en código de barras. Dicta los números y di Aceptar para agregar el producto.':'Con gusto. Dime en qué campo trabajar: Agregar producto o Escriba código de barras. También puedes decir método de pago Yape/Plin, Efectivo, Tarjeta o Transferencia.')", "this.say('Con gusto. Puedes decir buscar cámaras, buscar por nombre, método de pago Yape, DNI y su número, o total.')",1)
p.write_text(s,encoding='utf-8')
p=Path('src/utils/aliCommands.js');s=p.read_text(encoding='utf-8')
s=s.replace(" if(barcodeFocus)return", " if(/^(?:buscar|busca|buscador) por codigo(?: de barras?)?$/.test(text))return {type:'focus',field:'barcode'};\n if(/^(?:buscar|busca|buscador) por nombre$|^(?:salir|sal|salir de|sal de)(?: el)? codigo(?: de barras?)?$|^(?:cambia|cambiar|vuelve|volver)(?: al)? buscador(?: de productos| por nombre)?$/.test(text))return {type:'focus',field:'search'};\n if(barcodeFocus)return",1)
s=s.replace("(?:metodos|formas) de pago$", "(?:metodos?|formas?) de pago$",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8')
s=s.replace('    search() {', '    search(rows) {',1)
s=s.replace('      const text = this.query;\n      autocomplete.setValue(text);', '''      const text = this.query;
      const suppliedRows = Array.isArray(rows);
      if (suppliedRows) {
        clearTimeout(autocomplete.debounceTask);
        autocomplete.json = rows;
        autocomplete.showList = true;
        autocomplete.focusList = 0;
      }
      autocomplete.setValue(text);''',1)
s=s.replace('        autocomplete.handleInput({ target: input });', '        if (!suppliedRows) autocomplete.handleInput({ target: input });',1)
p.write_text(s,encoding='utf-8')
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8')
s=s.replace("params:{q:query,limit:5,sortBy:'stock'", "params:{q:query,limit:25,sortBy:'stock'",1)
s=s.replace('cart.query=query;cart.search();', 'cart.query=query;cart.search(rows);',1)
s=s.replace("'Estoy en Agregar producto, el buscador por nombre. Dime el producto que buscas.'", "'Dime el producto que buscas.'",1)
p.write_text(s,encoding='utf-8')
p=Path('.tmp-order-workbook/test-ali-voice-variety.mjs');s=p.read_text(encoding='utf-8').replace("!document.querySelector('.ali-host').__vue__.talking", "!document.querySelector('.ali-host').__vue__.talking&&!document.querySelector('.ali-host').__vue__.voiceLoading");p.write_text(s,encoding='utf-8')
