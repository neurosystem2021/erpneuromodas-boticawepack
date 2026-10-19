<template>
 <section class="quote-client-phone">
  <header><div><span>CONTACTO DEL CLIENTE</span><strong>{{name || 'Teléfono para la proforma'}}</strong></div><small v-if="required">Obligatorio en sistemas</small></header>
  <p v-if="!clientId">Selecciona primero el cliente de la proforma.</p>
  <template v-else>
   <label>Teléfono / WhatsApp del cliente {{required?'*':''}}<div class="quote-phone-input"><input type="tel" autocomplete="tel" v-model="phone" maxlength="40" :disabled="loading || busy || disabled || !canWrite" placeholder="Ejemplo: 968 411 589" aria-label="Teléfono / WhatsApp del cliente"><button type="button" @click="save" :disabled="!canWrite || !valid || loading || busy || disabled || !dirty">{{busy?'Guardando…':'Guardar teléfono'}}</button><button type="button" @click="load" :disabled="loading || busy || disabled" title="Volver a cargar el teléfono">↻</button></div></label>
   <p v-if="loading" role="status">Cargando teléfono…</p><p v-else-if="ready" class="quote-phone-ok" role="status">Teléfono guardado y listo para usar.</p><p v-else-if="dirty">Guarda el teléfono para actualizar la ficha del cliente desde aquí.</p><p v-else>Completa un teléfono válido para continuar.</p>
   <p v-if="error" class="quote-phone-error" role="alert">{{error}}</p><p v-if="!loading &amp;&amp; !canWrite &amp;&amp; !error">Tu cuenta puede consultar el teléfono. Su actualización requiere permiso de escritura.</p>
  </template>
 </section>
</template>
<script>
import {customerPhoneValid} from '@/utils/customerPhone';
export default {
 props:{clientId:{type:[String,Number],default:0},required:Boolean,disabled:Boolean},
 data(){return {phone:'',savedPhone:'',name:'',loading:false,busy:false,canWrite:false,csrf:'',error:''};},
 computed:{valid(){return customerPhoneValid(this.phone);},dirty(){return this.phone!==this.savedPhone;},ready(){return !!this.clientId&&!this.loading&&!this.busy&&!this.error&&this.valid&&!this.dirty;}},
 watch:{clientId:{immediate:true,handler(){this.load();}},ready:{immediate:true,handler(value){this.$emit('status',value);}}},
 beforeDestroy(){this._sequence=(this._sequence||0)+1;},
 methods:{async load(){const seq=this._sequence=(this._sequence||0)+1;this.busy=false;this.error='';this.phone='';this.savedPhone='';this.name='';this.canWrite=false;this.$emit('status',false);if(!this.clientId){this.loading=false;return;}this.loading=true;try{const r=await fetch('/api/proforma-contact.php?id='+encodeURIComponent(this.clientId),{credentials:'same-origin'});const data=await r.json();if(seq!==this._sequence)return;if(!r.ok)throw Error(data.error||'No se pudo cargar el teléfono.');this.name=data.client.Cliente;this.phone=data.client.Telefono||'';this.savedPhone=this.phone;this.canWrite=!!data.canWrite;this.csrf=data.csrf;this.$emit('loaded',data.client);}catch(e){if(seq===this._sequence)this.error=e.message;}finally{if(seq===this._sequence)this.loading=false;}},
 async save(){if(!this.valid||!this.canWrite||this.busy||this.disabled||!this.dirty)return;const seq=this._sequence;this.busy=true;this.error='';try{const r=await fetch('/api/proforma-contact.php',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-Contact-CSRF':this.csrf},body:JSON.stringify({IdCliente:Number(this.clientId),Telefono:this.phone,anterior:this.savedPhone})});const data=await r.json();if(seq!==this._sequence)return;if(!r.ok)throw Error(data.error||'No se pudo guardar el teléfono.');this.phone=data.client.Telefono;this.savedPhone=this.phone;this.$emit('saved',data.client);}catch(e){if(seq===this._sequence)this.error=e.message;}finally{if(seq===this._sequence)this.busy=false;}}
 }
};
</script>
<style scoped>
.quote-client-phone{padding:13px 14px;border:1px solid #e6ccd5;border-left:3px solid #b3233d;border-radius:7px;background:#fffafb;margin:10px 0 15px;font-family:Arial,sans-serif;color:#513641}.quote-client-phone header{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:10px}.quote-client-phone header span{display:block;font-size:8px;letter-spacing:1px;color:#a8324b;margin-bottom:4px}.quote-client-phone header strong{font-size:12px;line-height:1.5}.quote-client-phone header small{font-size:9px;color:#a7243d;white-space:nowrap}.quote-client-phone label{display:block;font-size:11px;font-weight:600;margin:0}.quote-phone-input{display:flex;gap:7px;margin-top:5px}.quote-phone-input input{min-width:0;flex:1;border:1px solid #ddc7d0;border-radius:5px;padding:9px 10px;font:13px/1.5 Arial,sans-serif;background:white;color:#513641}.quote-phone-input button{border:1px solid #b3233d;border-radius:5px;padding:8px 10px;color:#a3233d;background:white;font:11px Arial,sans-serif;white-space:nowrap;cursor:pointer}.quote-phone-input button:disabled{opacity:.5;cursor:default}.quote-client-phone p{font-size:10px;line-height:1.5;color:#896b78;margin:7px 0 0}.quote-client-phone .quote-phone-ok{color:#31785d}.quote-client-phone .quote-phone-error{color:#b12540;background:#fff0f3;padding:7px;border-radius:4px}@media(max-width:500px){.quote-phone-input{flex-wrap:wrap}.quote-phone-input input{flex-basis:100%}.quote-client-phone header{align-items:flex-start;flex-wrap:wrap}}
</style>
