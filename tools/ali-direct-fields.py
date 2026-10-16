from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');s=s.replace("if(action.type==='methods'){this.$bvModal.show('pos-mobile-payment-methods');return {message:'Selecciona la forma de pago.'};}","if(action.type==='methods')return {message:'Puedes decir método de pago Yape, efectivo, tarjeta o transferencia.'};",1)
a=s.index("      if(action.type==='search'||action.type==='learn')")
s=s[:a]+'''      if(['barcode','customer','documentType','focus'].includes(action.type)){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        if(action.type==='focus'){
          const input=cart&&(action.field==='barcode'?cart.$refs.barcodeInput:cart.$refs.autocompleteProducto.$el.querySelector('input'));
          if(input){input.scrollIntoView({block:'center'});input.focus()}
          return {message:action.field==='barcode'?'Estoy en código de barras. Di código de barras y el número.':'Estoy en el buscador. Dime qué producto buscas.'};
        }
        if(action.type==='barcode'){
          if(!/^\\d{1,40}$/.test(action.number))throw new Error('Dime el código con números o dígito por dígito.');
          if(!cart)throw new Error('No se encontró el carrito.');
          cart.loadCode=action.number;cart.focusBarcode();
          await cart.procesarCodigoBarra(action.number);cart.loadCode='';
          return {message:'Código '+action.number+' consultado. Revisa el producto y su cantidad en el carrito.'};
        }
        if(action.type==='documentType'){
          const response=await this.axios.get(this.API+'ventas/tipos',{params:{q:action.label}});
          const type=(Array.isArray(response.data)?response.data:[]).find(t=>String(t.CodSunat)===action.code);
          if(!type)throw new Error('Ese tipo de comprobante no está disponible.');
          await this.setTipoVenta(type);
          const refs=this.$refs['inputTipoVenta'+this.index],input=Array.isArray(refs)?refs[0]:refs;
          if(input)input.setValue(type.TipoDoc);
          return {message:'Seleccioné '+type.TipoDoc+'.'};
        }
        const expected=action.kind==='ruc'?11:8;
        if(!new RegExp('^\\\\d{'+expected+'}$').test(action.number))throw new Error(action.kind.toUpperCase()+' debe tener '+expected+' dígitos.');
        this.$set(sale,'MostrarClienteDatos',true);await this.$nextTick();
        const refs=this.$refs['inputCliente'+this.index],input=Array.isArray(refs)?refs[0]:refs;
        if(input){const field=input.$el.querySelector('input');if(field){field.scrollIntoView({block:'center'});field.focus()}input.setValue(action.number)}
        this.newDniRuc=action.number;this.dni=action.number;
        const response=await this.axios.get(this.API+'cliente',{params:{dni:action.number}});
        let client=response.data;
        if(!client||!client.IdCliente){
          const lookup=await this.axios.get(this.API+'consultarRUC',{params:{type:action.kind.toUpperCase(),numero:action.number}});
          const data=lookup.data||{};
          const name=action.kind==='ruc'?data.razonSocial:[data.nombres,data.apellidoPaterno,data.apellidoMaterno].filter(Boolean).join(' ');
          if(!name||typeof name!=='string')throw new Error('No encontré ese documento. Quedó escrito en el campo Cliente para revisarlo.');
          const created=await this.axios.post(this.API+'clientes',{Cliente:name.trim(),DniRuc:action.number,Direccion:data.direccion||'',Direccion2:'',Direccion3:'',Telefono:'',Email:'',Sexo:'',Ocupacion:'',FechaNacimiento:'',Nacionalidad:'',EsVIP:0,esComisionista:0,NombreComercial:''});
          client=created.data;
          if(!client||!client.IdCliente)throw new Error('No se pudo registrar el cliente consultado.');
        }
        await this.setCliente(client);
        return {message:'Cliente '+client.Cliente+' seleccionado con '+action.kind.toUpperCase()+'.'};
      }
''' +s[a:]
s=s.replace("const query=String(action.query||'').replace(/['\"\\\\%_]/g,' ').trim();", "const query=String(action.query||'').replace(/['\"\\\\%_]/g,' ').replace(/\\bcamaras\\b/gi,'camara').replace(/\\b(\\d+)\\s*(?:mp|megapixeles|megapixel)\\b/gi,'$1MP').trim();",1)
a=s.index("      if(action.type==='pay'){");b=s.index("      throw new Error('Comando no disponible.');",a)
s=s[:a]+'''      if(action.type==='pay'||action.type==='confirm'){
        if(this.inProgress||this.bloquearInput||this._aliSaving)throw new Error('La venta se está procesando.');
        if(this._aliPaidSale===sale)throw new Error('Esta venta ya fue enviada. Espera a que se abra la siguiente.');
        if(total<=0||!sale.productos.length)throw new Error('Agrega productos antes de cobrar.');
        const paid=['efectivo','yape','visa','mastercard'].reduce((n,k)=>n+(Number(sale.pago[k].monto)||0),0);
        if(Math.abs(paid-total)>0.01)throw new Error('Revisa los montos de pago: deben sumar S/ '+total.toFixed(2));
        this._aliSaving=true;const previous=this.ultimaVenta;
        try{await this.guardarVenta();if(this.ultimaVenta&&this.ultimaVenta!==previous){this._aliPaidSale=sale;return {pending:'',message:'Venta registrada por S/ '+total.toFixed(2)+'. Comprobante emitido por el punto de venta.'};}return {pending:'',message:'No se completó el cobro. Revisa el aviso del punto de venta y los datos del cliente, productos y pagos.'};}finally{this._aliSaving=false}
      }
''' +s[b:]
s=s.replace('    getNumeroVenta() {\n      this.axios','    getNumeroVenta() {\n      return this.axios',1)
a=s.index('    setTipoVenta(obj) {');b=s.index('    setDocRefOferta',a);part=s[a:b].replace('      this.getNumeroVenta();','      return this.getNumeroVenta();');s=s[:a]+part+s[b:]
p.write_text(s,encoding='utf-8')
