<template>
 <section class="printer-delivery">
  <div><h2>Impresión de comprobantes · ALIPAGO</h2><p>Ticket de 80 mm al confirmar la venta. Configuración de este navegador.</p></div>
  <div class="printer-delivery-grid">
   <label>Forma de envío<select v-model="settings.mode"><option value="windows">Directa por Windows (como IMCEDEN)</option><option value="browser">Diálogo de impresión del navegador</option></select></label>
   <label>Impresora Windows<select v-model="settings.printer" :disabled="settings.mode!=='windows'"><option v-if="!printers.some(p=>p.name===settings.printer)" :value="settings.printer">{{ settings.printer }}</option><option v-for="printer in printers" :key="printer.name" :value="printer.name">{{ printer.name }} · {{ printer.port }}</option></select></label>
   <label class="printer-auto"><input type="checkbox" v-model="settings.automatic"> Imprimir automáticamente al pagar</label>
  </div>
  <p v-if="selected">{{ selected.driver }} · {{ selected.port }} · {{ selected.status }}</p>
  <p>Windows utiliza la impresora del equipo donde está instalado este servidor. En un hosting remoto, utiliza el navegador o un agente local.</p>
  <div class="printer-actions"><button @click="load" :disabled="loading">{{ loading ? 'Consultando…' : 'Detectar impresoras / actualizar estado' }}</button><button @click="save">Guardar configuración</button><button @click="test" :disabled="loading||settings.mode!=='windows'">Imprimir prueba sin valor tributario</button></div>
  <p v-if="message" role="status">{{ message }}</p>
 </section>
</template>
<script>
import {readPrintSettings} from '@/utils/printSettings';
export default {
 data(){return {settings:readPrintSettings(),printers:[],loading:false,message:''};},
 computed:{selected(){return this.printers.find(p=>p.name===this.settings.printer);}},
 mounted(){this.load();},
 methods:{async load(){this.loading=true;this.message='';try{const response=await this.axios.get('/imprimir/configuracion.php',{timeout:12000});this.printers=response.data.printers||[];if(!response.data.windows)this.message='La impresión directa Windows no está disponible en este servidor; elige navegador.';}catch(e){this.message='No se pudo consultar Windows. Verifica el servicio de impresión y la sesión.';}finally{this.loading=false;}},save(){try{localStorage.setItem('posPrintSettings',JSON.stringify(this.settings));this.message='Configuración guardada para las próximas ventas.';}catch(e){this.message='El navegador no permitió guardar la configuración.';}},async test(){this.loading=true;try{const result=await this.axios.post('/imprimir/configuracion.php',{action:'test',printer:this.settings.printer},{headers:{'X-Requested-With':'XMLHttpRequest'},timeout:20000});this.message=result.data.ok?'Prueba enviada a '+this.settings.printer:'La cola no confirmó el envío.';}catch(e){this.message='No se confirmó la prueba. Revisa la cola antes de volver a enviarla.';}finally{this.loading=false;}}}
};
</script>
<style scoped>
.printer-delivery{background:#fff;border:1px solid #c9ddea;border-radius:15px;padding:18px;margin-bottom:16px;color:#24475c}.printer-delivery h2{font-size:20px;margin:0 0 8px}.printer-delivery p{font-size:13px;margin:10px 0}.printer-delivery-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}.printer-delivery label{font-weight:600}.printer-delivery select{display:block;width:100%;padding:10px;border:1px solid #bdd2e4;border-radius:8px;margin-top:8px;background:white}.printer-auto{align-self:center}.printer-actions{display:flex;gap:10px;flex-wrap:wrap}.printer-actions button{border:0;border-radius:8px;background:#2460b7;color:#fff;padding:10px 16px}.printer-actions button:disabled{opacity:.6}@media(max-width:800px){.printer-delivery-grid{grid-template-columns:1fr}.printer-delivery{padding:12px}}
</style>
