from pathlib import Path

p = Path('src/components/AliAssistant.vue')
s = p.read_text(encoding='utf-8')
a = s.index(' <div v-if="awake" class="ali-goldfish"')
b = s.index(' <b-modal v-model="open"', a)
s = s[:a] + ''' <div v-if="awake" class="ali-goldfish" role="status" aria-label="ALI activa, pececito dorado"><img class="ali-fish-art" src="static/images/ali-goldfish.svg?v=front-20261013" alt="ALI, pececito dorado sonriente"><strong>ALI</strong><span>Te escucho</span></div>
''' + s[b:]
s = s.replace('voice.pitch=1.08;', 'voice.pitch=1.2;')
s = s.replace('“Busca cinta”,', '“Buscar producto por código” → “Dictar número”. Dicta los números y di “Aceptar”. “Busca cinta”,', 1)
p.write_text(s, encoding='utf-8')

p = Path('src/utils/aliCommands.js')
s = p.read_text(encoding='utf-8')
s = s.replace(" if(barcodeFocus)return", " if(/^(?:buscar|busca)(?: el| un)? producto por codigo(?: de barras?)?$/.test(text))return {type:'focus',field:'barcode'};\n if(barcodeFocus)return", 1)
p.write_text(s, encoding='utf-8')

p = Path('src/components/ProductosCarritoNuevo.vue')
s = p.read_text(encoding='utf-8')
s = s.replace('              v-model="loadCode"\n', '              v-model="loadCode"\n              @focus="clearProductSearch"\n', 1)
s = s.replace('    focusBarcode(selectContent = false) {', '''    clearProductSearch() {
      this.query = "";
      const autocomplete = this.$refs.autocompleteProducto;
      if (!autocomplete) return;
      clearTimeout(autocomplete.debounceTask);
      autocomplete.setValue("");
      autocomplete.showList = false;
      autocomplete.json = [];
    },
    focusBarcode(selectContent = false) {
      this.clearProductSearch();''', 1)
s = s.replace('    async procesarCodigoBarra(codigo) {', '    async procesarCodigoBarra(codigo) {\n      this.clearProductSearch();', 1)
p.write_text(s, encoding='utf-8')

p = Path('src/views/Ventas.vue')
s = p.read_text(encoding='utf-8')
s = s.replace("if(action.field==='barcode')cart.loadCode='';", "if(action.field==='barcode'){cart.loadCode='';cart.clearProductSearch();}", 1)
s = s.replace("'Estoy en el buscador por código de barras. Dicta los números y di Aceptar para agregar.'", "'Dictar número'", 1)
s = s.replace("const found=await cart.procesarCodigoBarra(action.number);if(found===false)throw new Error('No encontré ese código. Puedes corregirlo o decir borrar código.');cart.loadCode='';", "let found;try{found=await cart.procesarCodigoBarra(action.number);}finally{cart.loadCode='';}\n          if(found===false)return {field:'barcode',buffer:'',message:'No encontré ese código. Dictar número'};", 1)
p.write_text(s, encoding='utf-8')

p = Path('integration/ali-api.php')
s = p.read_text(encoding='utf-8')
s = s.replace('con voz femenina de estilo juvenil, dulce, cálida, alegre y natural. Tono ligero y suave, sin sonar robótica, chillona ni solemne.', 'con una voz femenina ficticia de personaje infantil de cuento, dulce, alegre, tierna y natural. Tono más agudo, ligero y suave, articulación clara, sin sonar adulta, robótica, chillona ni solemne.')
p.write_text(s, encoding='utf-8')
