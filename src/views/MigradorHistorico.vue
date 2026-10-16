<template>
 <section class="migration-workspace">
  <header><div><h1>Migrador de compras y ventas</h1><p>Historial Factura.vip · copia aislada · sin afectar stock ni reenviar a SUNAT</p></div><button @click="load" :disabled="busy">Actualizar</button></header>
  <p v-if="error" class="migration-error" role="alert">{{ error }}</p>
  <p v-if="!info.configured">Configura el perfil privado del servidor y una copia restaurada. No se solicitan contraseñas en este panel.</p>
  <template v-else>
   <p>Destino: <strong>{{ info.target }}</strong> · {{ info.writes_enabled ? 'Escritura autorizada en copia' : 'Solo lectura' }}</p>
   <nav><button v-for="tab in tabs" :key="tab.id" @click="active=tab.id" :aria-pressed="active===tab.id">{{ tab.label }}</button></nav>
   <article v-if="active==='source'">
    <h2>Origen y módulos</h2><p>El perfil privado conecta la copia de destino y el archivo de origen revisado. Para cambiar conexiones, el administrador modifica el perfil del servidor.</p><button @click="testSource" :disabled="busy">Probar conexión de origen</button><p v-if="sourceConnection.connected">{{ sourceConnection.database }} · {{ sourceConnection.tables }} tablas · solo lectura</p>
    <label v-for="(entities,key) in info.modules" :key="key"><input type="checkbox" :value="key" v-model="modules" :disabled="key==='catalog'"> {{ labels[key] }} ({{ entities.length }} tablas)</label>
    <h3>Transformaciones opcionales</h3><p>JSON por tabla y campo: operación copy, trim o uppercase; se conserva también la fila original.</p>
    <textarea v-model="mapping" aria-label="Mapeo de campos" rows="4" placeholder='{"Gen_Producto":{"Producto":{"to":"Producto","operation":"trim"}}}'></textarea>
    <button @click="simulate" :disabled="busy||!info.writes_enabled">Simular y registrar auditoría en copia</button>
   </article>
   <article v-if="active==='schema'"><h2>Compatibilidad</h2><button @click="compare" :disabled="busy">Comparar estructuras</button><p v-if="comparison.source_file">{{ comparison.source_file }} · {{ comparison.target_database }}</p>
    <div class="migration-table"><table><thead><tr><th>Origen</th><th>Destino</th><th>Conflictos</th></tr></thead><tbody><tr v-for="row in comparison.mapping||[]" :key="row.source"><td>{{ row.source }}</td><td>{{ row.target||'Sin correspondencia' }}</td><td>{{ row.inspection_error ? 'No verificable: tabla dañada' : 'Nuevas: '+(row.new_columns||[]).join(', ')+' · Ausentes: '+(row.missing_columns||[]).join(', ')+' · Tipos: '+(row.changed_columns||[]).length }}</td></tr></tbody></table></div>
   </article>
   <article v-if="active==='preview'"><h2>Vista previa privada</h2><select v-model="entity" @change="page=1;preview()"><option v-for="name in entities" :key="name">{{ name }}</option></select><button @click="preview" :disabled="busy">Ver registros</button><p>{{ previewData.total||0 }} registros · página {{ page }}</p><button @click="page--;preview()" :disabled="page===1||busy">Anterior</button><button @click="page++;preview()" :disabled="page*25>=previewData.total||busy">Siguiente</button><pre>{{ previewData.rows }}</pre></article>
   <article v-if="active==='run'">
    <h2>Simulación y ejecución</h2><template v-if="run.id"><p>{{ run.id }} · {{ run.status }}</p><progress :value="run.cursor_pos" :max="run.total_rows||1"></progress><p>{{ run.cursor_pos }} / {{ run.total_rows }} filas · {{ run.stored_rows }} archivadas</p>
    <ul><li v-for="issue in run.issues" :key="issue.code"><strong>{{ issue.code }}</strong> · {{ issue.severity }}<pre>{{ issue.detail_json }}</pre><p v-if="issue.justification">{{ issue.justification }}</p><template v-else-if="issue.severity==='review'&&run.status==='simulated'"><input v-model="reasons[issue.code]" placeholder="Evidencia y justificación documentada" :aria-label="'Justificación '+issue.code"><button @click="justify(issue)" :disabled="busy">Registrar justificación</button></template></li></ul>
    <button @click="approve" :disabled="busy||blocked||run.status!=='simulated'">Aprobar esta simulación en copia</button>
    <button @click="execute" :disabled="busy||!['approved','running','failed'].includes(run.status)">Ejecutar / reanudar lotes</button><button v-if="running" @click="running=false">Detener después del lote actual</button>
    <button @click="rollback" :disabled="busy||run.status==='rolled_back'">Revertir archivo de esta ejecución</button><button @click="download" :disabled="busy">Descargar reporte</button><p>La aprobación aplica únicamente a esta copia. No autoriza migración definitiva.</p>
    </template><p v-else>Selecciona módulos y ejecuta la simulación.</p>
   </article>
   <article v-if="active==='history'"><h2>Historial de ejecuciones</h2><button @click="history" :disabled="busy">Consultar</button><ul><li v-for="item in runs" :key="item.id"><button @click="selectRun(item.id)">{{ item.created_at }} · {{ item.status }} · {{ item.cursor_pos }}/{{ item.total_rows }}</button></li></ul></article>
  </template>
 </section>
</template>
<script>
import config from '@/store/config';
export default {
 data(){return {info:{},sourceConnection:{},busy:false,error:'',csrf:'',active:'source',modules:['catalog','sales','purchases','related'],mapping:'{}',comparison:{},run:{},runs:[],entity:'Ve_DocVenta',page:1,previewData:{},reasons:{},running:false,labels:{catalog:'Catálogos y relaciones',sales:'Ventas',purchases:'Compras y logística',related:'Documentos relacionados'},tabs:[{id:'source',label:'Origen y módulos'},{id:'schema',label:'Estructuras'},{id:'preview',label:'Vista previa'},{id:'run',label:'Simulación y avance'},{id:'history',label:'Historial'}]};},
 computed:{entities(){return [].concat.apply([],Object.values(this.info.modules||{}));},blocked(){return (this.run.issues||[]).some(i=>i.severity==='fatal'||!i.justification);}},
 mounted(){this.load();},beforeDestroy(){this.running=false;},
 methods:{
  async request(action,data={},write=false){this.busy=true;this.error='';try{const result=await this.axios({url:config.API+'ali/migracion',method:write?'POST':'GET',params:write?undefined:Object.assign({action},data),data:write?Object.assign({action},data):undefined,headers:{'X-Ali-CSRF':this.csrf},timeout:60000});return result.data;}catch(e){this.error=((e.response||{}).data||{}).error||'No se pudo completar la operación.';throw e;}finally{this.busy=false;}},
  async load(){try{const auth=await this.axios.get(config.API+'ali/config');this.csrf=auth.data.csrf;if(String(auth.data.user||'').trim().toLowerCase()!=='superadmin'||!((auth.data.pos||{}).admin)){this.error='Acceso exclusivo para superadmin.';return;}this.info=await this.request('info');this.runs=(await this.request('history')).runs||[];if(!this.run.id&&this.runs.length)await this.selectRun(this.runs[0].id);}catch(e){}},
  async compare(){try{this.comparison=await this.request('compare');}catch(e){}},
  async testSource(){try{this.sourceConnection=await this.request('test_source');}catch(e){}},
  async preview(){try{this.previewData=await this.request('preview',{entity:this.entity,page:this.page});}catch(e){}},
  async simulate(){try{const transforms=JSON.parse(this.mapping);this.run=await this.request('simulate',{modules:this.modules,transforms},true);this.active='run';}catch(e){if(e instanceof SyntaxError)this.error='El mapeo debe ser JSON válido.';}},
  async justify(issue){try{this.run=await this.request('justify',{id:this.run.id,code:issue.code,reason:this.reasons[issue.code]||''},true);}catch(e){}},
  async approve(){try{this.run=await this.request('approve',{id:this.run.id,plan_hash:this.run.plan_hash},true);}catch(e){}},
  async execute(){this.running=true;try{while(this.running&&['approved','running','failed'].includes(this.run.status)){this.run=await this.request('batch',{id:this.run.id,cursor:this.run.cursor_pos},true);}}catch(e){}finally{this.running=false;}},
  async rollback(){if(!window.confirm('¿Revertir exclusivamente los registros históricos de esta ejecución en la copia?'))return;try{this.run=await this.request('rollback',{id:this.run.id},true);}catch(e){}},
  async history(){try{this.runs=(await this.request('history')).runs;}catch(e){}},
  async selectRun(id){try{this.run=await this.request('status',{id});this.active='run';}catch(e){}},
  async download(){try{const data=await this.request('report',{id:this.run.id});const url=URL.createObjectURL(new Blob([JSON.stringify(data,null,2)],{type:'application/json'}));const link=document.createElement('a');link.href=url;link.download='migracion-'+this.run.id+'.json';link.click();setTimeout(()=>URL.revokeObjectURL(url),1000);}catch(e){}}
 }
};
</script>
<style scoped>
.migration-workspace{max-width:1500px;margin:20px auto;padding:20px;color:#203952}.migration-workspace header{display:flex;justify-content:space-between;gap:16px}.migration-workspace h1{font-size:25px}.migration-workspace nav{display:flex;flex-wrap:wrap;gap:8px;margin:20px 0}.migration-workspace article{background:white;border:1px solid #d0dbea;border-radius:12px;padding:20px}.migration-workspace button,.migration-workspace select,.migration-workspace input:not([type=checkbox]){padding:10px;border:1px solid #bdcce1;border-radius:7px;background:#f6f8ff;color:#243e63;margin:4px;max-width:100%}.migration-workspace button[aria-pressed=true]{background:#344e9b;color:white}.migration-workspace button:disabled{opacity:.5}.migration-workspace label{display:block;margin:12px 0}.migration-workspace textarea{width:100%;font-family:monospace}.migration-workspace pre{white-space:pre-wrap;overflow-wrap:anywhere;max-height:400px;overflow:auto;background:#f1f4fa;padding:12px}.migration-workspace progress{width:100%;height:22px}.migration-table{overflow:auto}.migration-table th,.migration-table td{padding:10px;border-bottom:1px solid #ddd;text-align:left}.migration-error{background:#ffe4e4;padding:12px;border-radius:8px}.migration-workspace li{margin:12px 0}@media(max-width:600px){.migration-workspace{padding:10px;margin:5px}.migration-workspace header{display:block}.migration-workspace article{padding:12px}.migration-workspace nav button{flex:1 1 45%}}
</style>
