from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');a=s.index("      if(['barcode','customer','documentType','focus'].includes(action.type))");s=s[:a]+'''      if(['selectCode','add','accept'].includes(action.type)){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        const autocomplete=cart&&cart.$refs.autocompleteProducto;
        const visible=autocomplete&&autocomplete.showList&&Array.isArray(autocomplete.json)?autocomplete.json:[];
        let product=action.product;
        if(action.type==='selectCode'){
          if(!visible.length)throw new Error('Abre la lista en Agregar producto para seleccionar por terminación de código.');
          const exact=visible.filter(p=>String(p.CodigoBarra||'')===action.suffix);
          const matches=exact.length?exact:visible.filter(p=>String(p.CodigoBarra||'').endsWith(action.suffix));
          if(matches.length>1)throw new Error('Hay '+matches.length+' productos con esa terminación. Dime el código completo.');
          if(!matches.length)throw new Error('No hay un producto terminado en '+action.suffix+' en la lista visible.');
          product=matches[0];
        }else if(visible.length)product=visible[action.type==='accept'?(Number(autocomplete.focusList)||0):action.index-1];
        if(!product)throw new Error('Selecciona un producto de la lista actual.');
        if(!cart)throw new Error('No se encontró el carrito.');
        if(autocomplete){autocomplete.showList=false;autocomplete.setValue(product.Producto)}
        await cart.setProducto(Object.assign({},product));
        return {field:'search',buffer:'',pending:'',message:'Seleccioné '+product.Producto+', código '+product.CodigoBarra+'. Revisa la cantidad.'};
      }
''' +s[a:]
s=s.replace("['barcode','customer','documentType','focus']", "['barcode','barcodeDraft','customer','documentType','focus']",1)
s=s.replace("          if(input){input.scrollIntoView({block:'center'});input.focus()}\n          return {message:action.field==='barcode'?'Estoy en código de barras. Di código de barras y el número.':'Estoy en el buscador. Dime qué producto buscas.'};", "          if(action.field==='barcode')cart.loadCode='';\n          if(input){input.scrollIntoView({block:'center'});input.focus()}\n          return {field:action.field,buffer:'',message:action.field==='barcode'?'Estoy en el buscador por código de barras. Dicta los números y di Aceptar para agregar.':'Estoy en Agregar producto, el buscador por nombre. Dime el producto que buscas.'};",1)
a=s.index("        if(action.type==='barcode'){");s=s[:a]+'''        if(action.type==='barcodeDraft'){
          if(!cart||!/^\\d{0,40}$/.test(action.number))throw new Error('Código inválido.');
          cart.loadCode=action.number;cart.focusBarcode();await this.$nextTick();
          this._aliDrafting=true;
          return {field:'barcode',buffer:action.number,silent:true,message:action.number?'Código escrito: '+action.number+'. Di Aceptar para agregar.':'Código vacío. Dicta los números.'};
        }
''' +s[a:]
s=s.replace("return {message:'Código '+action.number+' consultado.","this._aliDrafting=false;\n          return {field:'barcode',buffer:'',message:'Código '+action.number+' consultado.",1)
a=s.index("      if(action.type==='add'){");b=s.index("      if(action.type==='pay'||action.type==='confirm'){",a);s=s[:a]+s[b:]
s=s.replace("return {results,product:action.type==='learn'", "return {field:'search',buffer:'',results,product:action.type==='learn'",1)
s=s.replace("Di agrega resultado uno, o el número que corresponda.", "Puedes decir selecciona el que termina en y su código, o agrega el primero.",1)
s=s.replace("return {pending:'',message:'Pago con '+action.label+' seleccionado por S/ '+total.toFixed(2)}", "return {field:'payment',buffer:'',pending:'',message:'Método de pago '+action.label+' seleccionado por S/ '+total.toFixed(2)}",1)
s=s.replace("if(action.type==='methods')return {message:","if(action.type==='methods')return {field:'payment',buffer:'',message:",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8');s=s.replace("this.say(result.message||'Listo.');", "if(result.silent)this.status=result.message;else this.say(result.message||'Listo.');",1)
s=s.replace("if(this.busy)return;this.command=raw;this.run()", "if(this.busy){if(this.activeField==='barcode'){this._dictationQueue=this._dictationQueue||[];if(this._dictationQueue.length<10)this._dictationQueue.push(raw)}return}this.command=raw;this.run()",1)
s=s.replace("if(action.type==='add'&&this.results[action.index-1])action.product=this.results[action.index-1];", "if(action.type==='add'&&this.results[action.index-1])action.product=this.results[action.index-1];",1)
s=s.replace("}catch(e){this.say(e.message)}finally{this.busy=false}},\n learn(product)", "}catch(e){this.say(e.message)}finally{this.busy=false;if(this.awake&&this._dictationQueue&&this._dictationQueue.length){const next=this._dictationQueue.shift();this.$nextTick(()=>{this.command=next;this.run()})}}},\n learn(product)",1)
s=s.replace("this.awake=false;this.activeField='';this.barcodeBuffer='';", "this.awake=false;this._dictationQueue=[];this.activeField='';this.barcodeBuffer='';",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8');s=s.replace('<b-form-group class="pos-search-group">','<b-form-group class="pos-search-group">\n          <label class="pos-product-search-label" for="txtAddProduct">Buscar producto por nombre, modelo o categoría</label>',1)
idx=s.index('<b-input-group class="pos-product-search-control pos-barcode-search-control">');s=s[:idx]+'<label class="pos-product-search-label" for="txtBarcode">Buscar producto por código de barras</label>\n          '+s[idx:];s=s.replace('id="txtBarcode"','id="txtBarcode"\n              aria-label="Buscar producto por código de barras"',1);p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8')+'\n.pos-product-search-label{display:block;margin:0 0 4px 4px;color:#436277;font-size:11px;font-weight:700}\n';p.write_text(s,encoding='utf-8')
