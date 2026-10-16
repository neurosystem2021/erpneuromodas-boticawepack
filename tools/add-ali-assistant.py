from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8-sig');s=s.replace('<b-container class="body pos-page" fluid>','<b-container class="body pos-page" fluid>\n  <ali-assistant :execute="aliExecute" />',1);s=s.replace('import ProductosCarrito from', 'import AliAssistant from "@/components/AliAssistant";\nimport ProductosCarrito from',1);s=s.replace('  components: {','  components: {\n    AliAssistant,',1)
s=s.replace('  methods: {','''  methods: {
    aliSnapshot() {
      const sale=this.ventas[this.index];
      return JSON.stringify({index:this.index,sale});
    },
    async aliExecute(action) {
      const sale=this.ventas[this.index];
      if(!sale) throw new Error('Abre una venta primero.');
      const ref=this.$refs['carrito'+this.index], cart=Array.isArray(ref)?ref[0]:ref;
      const total=Number(sale.totalCart)||0;
      if(action.type==='cancel'){this._aliPayment=null;return {pending:'',message:'Cobro por voz cancelado. La venta sigue abierta.'};}
      if(action.type==='total')return {message:'El total es S/ '+total.toFixed(2)};
      if(action.type==='methods'){this.$bvModal.show('pos-mobile-payment-methods');return {message:'Selecciona la forma de pago.'};}
      if(action.type==='method'){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        this.venta=sale;this.cargarMonto(action.key);this._aliPayment=null;
        return {pending:'',message:'Pago con '+action.label+' seleccionado por S/ '+total.toFixed(2)};
      }
      if(action.type==='search'||action.type==='learn'){
        const warehouse=sale.almacen&&sale.almacen.IdAlmacen;
        if(!warehouse)throw new Error('Selecciona la tienda antes de buscar.');
        const query=String(action.query||'').replace(/[\u0027\u0022\\\\%_]/g,' ').trim();
        if(!query)throw new Error('Indica qué producto buscas.');
        const response=await this.axios(this.API+'productos/stock/'+warehouse,{params:{q:query,limit:5,sortBy:'stock',sortDesc:1}});
        const rows=Array.isArray(response.data)?response.data:(response.data.data||[]);
        const results=rows.slice(0,5);
        if(action.type==='search'&&cart){cart.query=query;cart.search();}
        return {results,product:action.type==='learn'&&results.length===1?results[0]:null,message:results.length?'Encontré '+results.length+' productos. Di agrega resultado uno, o el número que corresponda.':'No encontré productos con ese nombre.'};
      }
      if(action.type==='add'){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        if(!cart)throw new Error('No se encontró el carrito.');
        this._aliPayment=null;await cart.setProducto(Object.assign({},action.product));
        return {pending:'',message:'Producto seleccionado. Revisa cantidad, presentación y serie cuando corresponda.'};
      }
      if(action.type==='pay'){
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        if(total<=0||!sale.productos.length)throw new Error('Agrega productos antes de cobrar.');
        const labels={efectivo:'Efectivo',yape:'Yape/Plin',visa:'Tarjeta',mastercard:'Transferencia'};
        const payments=Object.keys(labels).filter(k=>Number(sale.pago[k].monto)>0).map(k=>labels[k]+': S/ '+Number(sale.pago[k].monto).toFixed(2));
        const paid=Object.keys(labels).reduce((n,k)=>n+(Number(sale.pago[k].monto)||0),0);
        if(Math.abs(paid-total)>0.01)throw new Error('Revisa los montos de pago: deben sumar S/ '+total.toFixed(2));
        this._aliPayment={snapshot:this.aliSnapshot(),expires:Date.now()+60000};
        const summary='Total S/ '+total.toFixed(2)+'. '+payments.join('. ')+'. Se registrará la venta actual. Di confirmar pago o cancelar.';
        return {pending:summary,message:summary};
      }
      if(action.type==='confirm'){
        const pending=this._aliPayment;this._aliPayment=null;
        if(!pending||pending.expires<Date.now()||pending.snapshot!==this.aliSnapshot())throw new Error('La venta cambió o no hay un cobro preparado. Di pagar para revisar nuevamente.');
        if(this.inProgress||this.bloquearInput)throw new Error('La venta está bloqueada.');
        await this.guardarVenta();return {pending:'',message:'Solicitud de cobro procesada. Revisa el resultado del punto de venta.'};
      }
      throw new Error('Comando no disponible.');
    },''',1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8');s=s.replace('<p class="pos-detail-stock">','<button type="button" class="pos-ali-learn" @click="modalImagen=false; $root.$emit(\'ali:learn\', productoDetalle)">Aprender con ALI · Información y videos</button>\n          <p class="pos-detail-stock">',1);p.write_text(s,encoding='utf-8')
for name in ['integration/ali-api.php','src/utils/aliCommands.js','src/components/AliAssistant.vue']:
 p=Path(name);p.write_text(p.read_text(encoding='utf-8-sig'),encoding='utf-8')
