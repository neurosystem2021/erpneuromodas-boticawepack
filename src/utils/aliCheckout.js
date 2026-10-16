export function aliPaymentSnapshot(sale) {
 const n=v=>Number(v)||0;
 return JSON.stringify({client:n((sale.cliente||{}).IdCliente),type:n((sale.tipoVenta||{}).IdTipoDoc),warehouse:n((sale.almacen||{}).IdAlmacen),point:n((sale.puntoVenta||{}).IdDocVentaPuntoVenta),
  products:(sale.productos||[]).map(p=>({id:n(p.IdProducto),qty:n(p.cantidad),price:n(p.precio),discount:n(p.descuento),tax:!!p.TieneIgv,series:n(p.IdProductoSerie),description:p.Descripcion||''})),
  payments:['efectivo','visa','mastercard','yape'].map(k=>({key:k,amount:n(((sale.pago||{})[k]||{}).monto),reference:((sale.pago||{})[k]||{}).descripcion||''})),total:n(sale.totalCart),received:n(sale.PagoCon||sale.pagocon),
  extra:['EsCredito','IdPreOrden','adelantosPreOrden','IdPedido','IdProforma','IdOferta','IdDocVentas','EsDelivery','EsOrganizacion','nombreOrganizacion','nombreLlevar','descripcion','Garantia','puntosAplicados','CampoDireccion'].map(k=>[k,sale[k]||null]),commission:n((sale.comisionista||{}).IdCliente)});
}
async function signature(sale){const data=new TextEncoder().encode(aliPaymentSnapshot(sale));const digest=await crypto.subtle.digest('SHA-256',data);return Array.from(new Uint8Array(digest)).map(b=>b.toString(16).padStart(2,'0')).join('')}
function operationId(){const b=new Uint8Array(16);crypto.getRandomValues(b);b[6]=(b[6]&15)|64;b[8]=(b[8]&63)|128;const h=Array.from(b).map(v=>v.toString(16).padStart(2,'0')).join('');return h.slice(0,8)+'-'+h.slice(8,12)+'-'+h.slice(12,16)+'-'+h.slice(16,20)+'-'+h.slice(20)}
export default {
 data(){return {aliCheckoutReview:null,aliCheckoutError:'',aliCheckoutPreparing:false,aliCheckoutSubmitting:false}},
 methods:{
  async aliCheckoutRequest(path,body,csrf){
   let response;try{response=await this.axios({url:this.API+'ali/pago/'+path,method:body?'POST':'GET',data:body,headers:{'X-Ali-CSRF':csrf||((this.$refs.aliAssistant||{}).config||{}).csrf},timeout:25000});}
   catch(e){const data=(e.response||{}).data||{};const error=new Error(data.error||'No se obtuvo respuesta. Consulta el estado antes de repetir el pago.');error.uncertain=!e.response||data.uncertain;throw error;}
   return response.data;
  },
  aliCheckoutCommand(text){const ali=this.$refs.aliAssistant;ali.command=text;ali.run()},
  async aliPrepareCheckout(csrf){
   if(this.inProgress||this.aliCheckoutSubmitting||this.aliCheckoutPreparing)throw Error('Hay una operación en curso.');
   const sale=this.ventas[this.index];if(!sale.productos.length)throw Error('Agrega productos antes de decir ALIPAGO.');
   this.aliCheckoutPreparing=true;this.aliCheckoutError='';
   try{
    const payload=await this.guardarVenta(null,{prepare:true});if(!payload)throw Error('Revisa los datos indicados por el POS antes de cobrar.');
    const hash=await signature(sale),user=(this.$refs.aliAssistant.config||{}).user;let cached=null;try{cached=JSON.parse(sessionStorage.getItem('aliPendingCheckout')||'null')}catch(e){}
    if(this.aliCheckoutReview&&this.aliCheckoutReview.signature!==hash)throw Error('La venta cambió. Cancela el resumen anterior y vuelve a decir ALIPAGO.');
    const id=this.aliCheckoutReview?this.aliCheckoutReview.operation:cached&&cached.user===user&&cached.signature===hash?cached.operation:operationId();
    sessionStorage.setItem('aliPendingCheckout',JSON.stringify({operation:id,signature:hash,user}));
    const data=await this.aliCheckoutRequest('preparar',{operation:id,sale:payload},csrf);
    if(await signature(sale)!==hash)throw Error('La venta cambió durante la revisión. Cancela el resumen y vuelve a preparar el pago.');
    this.aliCheckoutReview={operation:id,signature:hash,sale,summary:data.summary,uncertain:false};
    if(data.state==='completada'){this.aliFinishCheckout(data.result,sale);return {pending:'',message:'Esta operación ya estaba registrada. No se volvió a cobrar.'};}
    const methods=data.summary.payments.map(p=>p.label+' S/ '+Number(p.amount).toFixed(2)).join(', ');
    const message='Total S/ '+Number(data.summary.total).toFixed(2)+'. '+methods+'. Revisa el resumen. Di confirmar pago solo si verificaste que recibiste el dinero, o cancelar.';
    this.$nextTick(()=>{const panel=this.$el.querySelector('.ali-checkout-panel');if(panel)panel.scrollIntoView({block:'nearest'})});
    return {field:'payment',buffer:'',pending:message,message,speech:'Total '+Number(data.summary.total).toFixed(2)+' soles. Revisa y confirma el pago.',fastVoice:true};
   }catch(e){this.aliCheckoutError=e.message;throw e;}finally{this.aliCheckoutPreparing=false;this.inProgress=false;}
  },
  async aliConfirmCheckout(csrf){
   const review=this.aliCheckoutReview;if(!review)throw Error('Primero di ALIPAGO y revisa el resumen de la venta.');
   if(this.inProgress||this.aliCheckoutSubmitting)throw Error('El cobro ya se está procesando.');
   if(review.uncertain)throw Error('Consulta el estado antes de volver a confirmar.');
   this.aliCheckoutSubmitting=true;this.inProgress=true;this.aliCheckoutError='';
   try{
   if(this.ventas[this.index]!==review.sale||await signature(review.sale)!==review.signature)throw Error('La venta cambió. Cancela el resumen y vuelve a decir ALIPAGO.');
   const data=await this.aliCheckoutRequest('confirmar',{operation:review.operation,verified:true},csrf);this.aliFinishCheckout(data,review.sale);return {pending:'',fastVoice:true,speech:'Venta realizada.',message:'Venta realizada.'};}
   catch(e){review.uncertain=!!e.uncertain;this.aliCheckoutError=e.message;throw e;}
   finally{this.aliCheckoutSubmitting=false;this.inProgress=false;}
  },
  aliFinishCheckout(data,sale){
   if(!data||!data.insertId)throw Error('No hay una venta confirmada. Consulta el estado.');
   this.ultimaVenta=data.insertId;if(this.enviarImpresionVenta)this.enviarImpresionVenta(data.insertId,sale.IdDocVentas||'');this.$toasted.success('Venta realizada');this.aliCheckoutReview=null;this.aliCheckoutError='';sessionStorage.removeItem('aliPendingCheckout');
   const old=this.ventas.indexOf(sale);if(old>=0){this.initForm();this.ventas.splice(old,1);this.index=this.ventas.length-1;this.venta=this.ventas[this.index];this.getVendedor();}
  },
  async aliRecoverCheckout(){
   const review=this.aliCheckoutReview;if(!review)return;
   try{const data=await this.aliCheckoutRequest('estado/'+review.operation);if(data.state==='completada'){this.aliFinishCheckout(data.result,review.sale);const ali=this.$refs.aliAssistant;ali.pending='';ali.say('La venta ya estaba registrada. No se volvió a cobrar.');}else{review.uncertain=false;this.aliCheckoutError='La operación sigue '+data.state+'. No se registró una venta por esta operación.';}}
   catch(e){this.aliCheckoutError=e.message;}
  },
  async aliCancelCheckout(csrf){
   if(this.aliCheckoutSubmitting)throw Error('El registro ya fue confirmado y está en curso. Consulta su estado.');
   const review=this.aliCheckoutReview;let cached=null;try{cached=JSON.parse(sessionStorage.getItem('aliPendingCheckout')||'null')}catch(e){}
   const id=review?review.operation:cached&&cached.operation;
   if(id){const data=await this.aliCheckoutRequest('cancelar',{operation:id},csrf);if(!data.cancelled){if(review)await this.aliRecoverCheckout();throw Error('La operación ya no está pendiente. Consulta su estado.');}}
   this.aliCheckoutReview=null;this.aliCheckoutError='';sessionStorage.removeItem('aliPendingCheckout');
  }
 }
};
