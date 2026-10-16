<template>
 <div class="web-orders">
  <button v-if="!hideButton" class="web-orders-button" type="button" @click="abrir"><i class="fas fa-shopping-bag"></i> Pedidos tienda virtual <strong v-if="pending">{{ pending }} nuevos</strong></button>
  <b-modal v-model="show" title="Pedidos de la tienda virtual" size="xl" hide-footer modal-class="nst-dialog">
   <div class="web-orders-tabs"><b-button :variant="status==='pendientes' ? 'danger' : 'outline-secondary'" @click="status='pendientes';load()">Pedidos por atender</b-button> <b-button :variant="status==='facturados' ? 'danger' : 'outline-secondary'" @click="status='facturados';load()">Facturados</b-button></div>
   <div class="web-orders-tools"><b-form-input v-model="query" placeholder="Buscar cliente, documento o número" @input="buscar"/><b-button @click="load">Actualizar</b-button></div>
   <p class="text-muted">Carga la proforma en una nueva venta con su cliente, cantidades y precios web. La venta actual se conserva.</p>
   <b-alert :show="!!error" variant="danger">{{ error }}</b-alert>
   <b-table :items="orders" :fields="fields" responsive small striped show-empty empty-text="No hay pedidos en esta vista.">
    <template slot="Total" slot-scope="row">S/ {{ Number(row.item.Total).toFixed(2) }}</template>
    <template slot="Estado" slot-scope="row"><span>{{ Number(row.item.Facturado) ? 'Facturado' : Number(row.item.WebCargado) ? 'Cargado en caja' : 'Nuevo' }}</span></template>
    <template slot="Acciones" slot-scope="row"><b-button size="sm" variant="outline-secondary" :href="'/nuevo/index.html#/reportes/proforma/'+row.item.IdProforma" target="_blank">Ver</b-button> <b-button v-if="!Number(row.item.Facturado)" size="sm" variant="danger" :disabled="busy" @click="cargar(row.item)">Cargar en venta</b-button> <b-button v-if="canDelete && !Number(row.item.Facturado)" size="sm" variant="outline-danger" :disabled="busy" @click="eliminar(row.item)">Eliminar</b-button></template>
   </b-table>
  </b-modal>
 </div>
</template>
<script>
import config from '@/store/config';
export default {
 props: { hideButton: { type: Boolean, default: false }, onLoad: { type: Function, required: true } },
 data(){return {show:false,canDelete:false,pending:0,orders:[],query:'',status:'pendientes',error:'',busy:false,timer:null,searchTimer:null,previous:null,loading:false,fields:[{key:'Numero',label:'Proforma'},{key:'FechaReg',label:'Fecha'},{key:'Cliente',label:'Cliente'},{key:'DniRuc',label:'Documento'},{key:'Total',label:'Total'},{key:'Estado',label:'Estado'},{key:'Acciones',label:''}]};},
 mounted(){this.load();this.timer=setInterval(()=>{if(!document.hidden)this.load();},10000);},
 beforeDestroy(){clearInterval(this.timer);clearTimeout(this.searchTimer);},
 methods:{
  abrir(){this.show=true;this.load();},
  buscar(){clearTimeout(this.searchTimer);this.searchTimer=setTimeout(this.load,300);},
  async load(){if(this.loading)return;this.loading=true;try{const response=await this.axios.get('/api/pedidos-web.php',{params:{q:this.query,estado:this.status}});const count=Number(response.data.pendientes||0);if(count>0&&(this.previous===null||count>this.previous))this.$toasted.show(this.previous===null?'Tiene '+count+' pedido(s) de la tienda virtual pendientes.':'Nuevo pedido de la tienda virtual.',{duration:12000,position:'top-right',action:{text:'VER PEDIDOS',onClick:(event,toast)=>{this.abrir();toast.goAway(0);}}});this.previous=count;this.pending=count;this.$emit('pending',count);this.canDelete=response.data.puedeEliminar===true;this.orders=response.data.pedidos||[];this.error='';}catch(e){if(this.show)this.error='No se pudo actualizar la bandeja. Intenta nuevamente.';}finally{this.loading=false;}},
  async eliminar(order){if(!window.confirm('¿Eliminar el pedido web '+order.Numero+'?'))return;this.busy=true;try{await this.axios.post('/api/pedidos-web.php',{id:order.IdProforma,accion:'eliminar'});await this.load();this.$toasted.success('Pedido eliminado.');}catch(e){this.error=(e.response&&e.response.data&&e.response.data.error)||'No se pudo eliminar el pedido.';}finally{this.busy=false;}},
  async cargar(order){this.busy=true;this.error='';try{const response=await this.axios.get(config.API+'proformas/id/'+order.IdProforma);const data=response.data;const customer=await this.axios.get(config.API+'cliente',{params:{idCliente:data.IdCliente}});Object.assign(data,customer.data,{IdProforma:order.IdProforma});data.productos=(data.productos||[]).map(p=>({...p,setPrecio:true,precio:Number(p.Precio),cantidad:Number(p.Cantidad),descuento:Number(p.Descuento||0)}));if(!data.productos.length)throw Error('empty');await this.onLoad(data);await this.axios.post('/api/pedidos-web.php',{id:order.IdProforma});this.show=false;await this.load();this.$toasted.success('Pedido cargado en una nueva venta.');}catch(e){this.error='No se pudo cargar el pedido. Revisa el almacén e inténtalo nuevamente.';}finally{this.busy=false;}}
 }
};
</script>
<style scoped>
.web-orders-tabs{margin-bottom:14px}.web-orders{margin:8px 0}.web-orders-button{border:1px solid #efbcc6;background:#fff;color:#bf1430;border-radius:11px;padding:9px 15px;font-weight:800}.web-orders-button strong{margin-left:9px;background:#d81735;color:#fff;padding:4px 9px;border-radius:20px}.web-orders-tools{display:flex;gap:9px;margin-bottom:12px}.web-orders-tools select{max-width:240px}@media(max-width:600px){.web-orders-tools{flex-wrap:wrap}}
</style>
