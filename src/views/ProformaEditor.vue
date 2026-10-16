<template>
<main class="quote-editor">
 <h2>Editar proforma {{ quote.Anio }}-{{ quote.Numero }}</h2>
 <p>Guarda los cambios conservando el número de esta proforma.</p>
 <b-alert :show="!!error" variant="danger">{{ error }}</b-alert>
 <b-alert :show="!!success" variant="success">{{ success }}</b-alert>
 <section v-if="ready">
 <div class="quote-summary"><strong>{{ quote.Cliente }}</strong><span>{{ quote.Documento || quote.DniRuc }} ? Proforma {{ quote.Anio }}-{{ quote.Numero }}</span></div>
  <b-form-group label="Buscar productos por nombre o código"><div class="d-flex"><b-form-input v-model="query" @input="scheduleSearch" @keyup.enter="search" placeholder="Nombre o código"/><b-button @click="search" :disabled="busy">Buscar</b-button></div></b-form-group>
  <div v-for="p in results" :key="p.IdProducto" class="quote-result"><span><b>{{ p.CodigoBarra || p.IdProducto }}</b> · {{ p.Producto }} <small>S/ {{ Number(p.PrecioContado || p.PrecioVenta || p.Precio || 0).toFixed(2) }}</small></span><b-button size="sm" @click="add(p)">{{ replacingIndex === null ? "Agregar" : "Reemplazar" }}</b-button></div>
  <small v-if="searching">Buscando...</small><small v-else-if="searched && !results.length">Sin resultados para esta búsqueda.</small>
  <p v-if="replacingIndex !== null">Elige el producto que reemplazará la fila {{ replacingIndex + 1 }}. <b-button size="sm" @click="replacingIndex=null">Cancelar cambio</b-button></p>
  <h5>Productos</h5>
  <div class="table-responsive"><table class="table table-sm"><thead><tr><th>Producto</th><th>Presentación</th><th>Cant.</th><th>Precio</th><th>Desc.</th><th>Subtotal</th><th></th></tr></thead><tbody><tr v-for="(p,i) in products" :key="i"><td><strong>{{ p.Producto }}</strong><small class="d-block">{{ p.CodigoBarra || p.IdProducto }}</small></td><td>{{ presentation(p) }}</td><td><b-form-input type="number" min="0.001" step="any" v-model.number="p.cantidad"/></td><td><b-form-input type="number" min="0" step="0.01" v-model.number="p.precio"/></td><td><b-form-input type="number" min="0" step="0.01" v-model.number="p.descuento"/></td><td>{{ amount(p).toFixed(2) }}</td><td class="quote-row-actions"><b-button size="sm" variant="outline-primary" title="Reemplazar producto" @click="replacingIndex=i;query='';results=[]">Cambiar</b-button><b-button size="sm" variant="danger" title="Quitar producto" @click="remove(i)"><i class="fas fa-trash"></i></b-button></td></tr></tbody></table></div>
  <div class="text-right"><strong>Total: S/ {{ total.toFixed(2) }}</strong></div>
  <div class="quote-footer"><b-button v-if="embedded" variant="secondary" :disabled="busy" @click="$emit('cancel')">Cancelar</b-button><b-button variant="success" :disabled="busy" @click="save">{{ busy ? 'Guardando...' : 'Guardar Cambios' }}</b-button><b-button v-if="!embedded" variant="outline-secondary" :href="'/nuevo/index.html#/reportes/proforma/'+quote.IdProforma" target="_blank">Ver proforma</b-button></div>
 </section>
</main>
</template>
<script>
import config from '@/store/config.js';
export default {
 props:{quoteId:[String,Number],embedded:Boolean},
 data(){return {quote:{},products:[],results:[],replacingIndex:null,searching:false,searched:false,query:'',document:'',ready:false,busy:false,error:'',success:''};},
 beforeDestroy(){clearTimeout(this._searchTimer);this._sequence=(this._sequence||0)+1;},
 computed:{total(){return this.products.reduce((sum,p)=>sum+this.amount(p),0);}},
 async mounted(){try{const r=await this.axios.get(config.API+'proformas/id/'+(this.quoteId || this.$route.params.id));this.quote=r.data;this.products=(r.data.productos||[]).map(p=>({...p,cantidad:Number(p.Cantidad),precio:Number(p.Precio),descuento:Number(p.Descuento||0),disabledProf:Number(p.DisabledTotal||0)}));this.ready=true;}catch(e){this.error='No se pudo abrir la proforma.';}},
 methods:{
  presentation(p){try{const v=typeof p.Presentacion==='string'?JSON.parse(p.Presentacion):p.Presentacion;return (v&&v.nombre)||p.ProductoPresentacion||'UNIDAD';}catch(e){return p.ProductoPresentacion||'UNIDAD';}},
  async remove(i){const p=this.products[i];const result=await this.$swal({title:'¿Quitar producto?',text:p.Producto,icon:'warning',showCancelButton:true,confirmButtonText:'Sí, quitar',cancelButtonText:'Cancelar'});if(result.isConfirmed)this.products.splice(i,1);},
  amount(p){return (Number(p.cantidad)||0)*(Number(p.precio)||0)-(Number(p.descuento)||0);},
  async findClient(){this.error='';try{const r=await this.axios.get(config.API+'clientes/'+encodeURIComponent(this.document.trim()));if(!r.data.IdCliente)throw Error();this.quote={...this.quote,IdCliente:r.data.IdCliente,Cliente:r.data.Cliente};}catch(e){this.error='No se encontró el cliente. Revisa su DNI o RUC.';}},
  scheduleSearch(){clearTimeout(this._searchTimer);this._searchTimer=setTimeout(()=>this.search(),250);},
  async search(){clearTimeout(this._searchTimer);const q=this.query.trim();const sequence=this._sequence=(this._sequence||0)+1;this.error='';this.results=[];this.searched=false;if(!q)return;this.searching=true;try{const r=await this.axios.get('/api/proforma-edit.php',{params:{q}});if(sequence!==this._sequence)return;this.results=Array.isArray(r.data)?r.data:[];this.searched=true;}catch(e){if(sequence===this._sequence)this.error='No se pudieron buscar los productos.';}finally{if(sequence===this._sequence)this.searching=false;}},
  add(p){const row={...p,Descripcion:'',cantidad:1,precio:Number(p.PrecioContado||p.PrecioVenta||p.Precio||0),descuento:0,disabledProf:0};if(this.replacingIndex===null){const existing=this.products.find(v=>String(v.IdProducto)===String(p.IdProducto));if(existing)existing.cantidad=Number(existing.cantidad)+1;else this.products.push(row);}else{row.cantidad=this.products[this.replacingIndex].cantidad;this.$set(this.products,this.replacingIndex,row);this.replacingIndex=null;}this.results=[];this.query='';this.searched=false;},
  async save(){if(this.busy)return;this.busy=true;this.error='';this.success='';try{const token=await this.axios.get('/api/proforma-edit.php');await this.axios.post('/api/proforma-edit.php',{IdProforma:this.quote.IdProforma,cliente:{IdCliente:this.quote.IdCliente},descripcion:this.quote.Descripcion,diasValidosProf:this.quote.DiasValidos,productos:this.products},{headers:{'X-Proforma-CSRF':token.data.csrf}});this.success='Cambios guardados en la misma proforma.';this.$emit('saved');}catch(e){this.error=(e.response&&e.response.data&&e.response.data.error)||'No se pudo guardar. Tus cambios siguen en pantalla.';}finally{this.busy=false;}}
 }
};
</script>
<style scoped>
.quote-editor{max-width:1200px;margin:24px auto;padding:24px;background:#fff;color:#173f50;border-radius:16px}.quote-editor .btn{margin:4px}.quote-editor td:first-child{min-width:280px}.quote-editor td input{min-width:95px}.quote-result{display:flex;justify-content:space-between;padding:8px;border-bottom:1px solid #ddd}

.quote-editor{max-width:1100px!important;margin:12px auto!important;padding:16px!important;font-size:13px!important}
.quote-editor h2{font-size:20px!important;margin:0 0 6px!important}.quote-editor h3{font-size:19px!important;margin:10px 0!important}.quote-editor p{margin-bottom:10px!important}
.quote-meta{display:grid;grid-template-columns:2fr 2fr 100px;gap:12px;align-items:end}
.quote-editor >>> .form-group{margin-bottom:10px!important}.quote-editor >>> legend,.quote-editor >>> label{font-size:12px!important;margin-bottom:3px!important}
.quote-editor >>> .form-control{height:32px!important;min-height:32px!important;padding:5px 8px!important;font-size:13px!important;border-radius:6px!important}
.quote-editor >>> textarea.form-control{height:32px!important;min-height:32px!important}.quote-editor >>> .btn{font-size:12px!important;padding:5px 9px!important;min-height:30px!important;border-radius:6px!important;margin:2px!important;white-space:nowrap}
.quote-editor >>> .table th,.quote-editor >>> .table td{padding:7px 6px!important;font-size:12px!important;vertical-align:top!important}.quote-editor >>> .table th{height:auto!important}.quote-editor td input{min-width:70px;max-width:110px}.quote-editor td:first-child{width:48%;min-width:260px}.quote-editor >>> .custom-control{min-height:19px!important;margin-top:3px}.quote-editor >>> .custom-control-label{font-size:11px!important}
.quote-result{padding:4px 8px;gap:10px}.quote-result small{white-space:nowrap;color:#526b7b}
@media(max-width:700px){.quote-meta{grid-template-columns:1fr}.quote-editor{margin:6px!important;padding:10px!important}.quote-editor .table{min-width:760px}}
.quote-summary{display:flex;justify-content:space-between;gap:12px;padding:8px 0 12px;color:#173f50}.quote-summary span{font-size:12px}
.quote-footer{display:flex;justify-content:flex-end;gap:8px;border-top:1px solid #ddd;padding-top:12px;margin-top:12px}.quote-editor td:first-child{width:36%;min-width:220px}.quote-editor h5{font-size:15px;margin:10px 0}.quote-row-actions{white-space:nowrap}.quote-editor .quote-summary{padding-top:0}
</style>
