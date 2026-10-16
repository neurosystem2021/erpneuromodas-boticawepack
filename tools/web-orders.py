from pathlib import Path
p=Path('deploy-src/web-orders/api/pedidos-web.php');p.parent.mkdir(parents=True,exist_ok=True)
p.write_text('''<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if (empty($_SESSION['user']) || (isset($_SESSION['expire']) && time() > $_SESSION['expire'])) { http_response_code(401); echo json_encode(array('error'=>'Inicie sesión en el sistema.')); exit; }
require_once dirname(__DIR__) . '/config/database.php';
try {
 $db=appPdoConnection();
 if ($_SERVER['REQUEST_METHOD']==='POST') {
  $origin=$_SERVER['HTTP_ORIGIN'] ?? ''; if($origin && parse_url($origin,PHP_URL_HOST)!==explode(':',$_SERVER['HTTP_HOST'])[0]) {http_response_code(403);echo json_encode(array('error'=>'Origen inválido'));exit;}
  $body=json_decode(file_get_contents('php://input'),true);$id=(int)($body['id']??0);
  $stmt=$db->prepare("UPDATE Ve_Proforma SET WebCargado=1 WHERE IdProforma=? AND UsuarioReg='TIENDA' AND COALESCE(Anulado,0)=0");$stmt->execute(array($id));echo json_encode(array('ok'=>true));exit;
 }
 $q=trim((string)($_GET['q']??''));$status=(string)($_GET['estado']??'pendientes');
 $base=" FROM Ve_Proforma p LEFT JOIN Ve_DocVentaCliente c ON c.IdCliente=p.IdCliente WHERE p.UsuarioReg='TIENDA' AND COALESCE(p.Anulado,0)=0";
 $unbilled=" AND NOT EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)";
 $count=$db->query('SELECT COUNT(*)'.$base.' AND COALESCE(p.WebCargado,0)=0'.$unbilled)->fetchColumn();
 $params=array();
 if($status==='pendientes')$base.=' AND COALESCE(p.WebCargado,0)=0'.$unbilled;
 if($q!==''){$base.=' AND (c.Cliente LIKE ? OR c.DniRuc LIKE ? OR p.Numero LIKE ?)';$params=array('%'.$q.'%','%'.$q.'%','%'.$q.'%');}
 $sql="SELECT p.IdProforma,p.Numero,p.Anio,p.FechaReg,p.IdCliente,c.Cliente,c.DniRuc,p.Descripcion,p.WebCargado,
 (SELECT COALESCE(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0)),0) FROM Ve_ProformaDet d WHERE d.IdProforma=p.IdProforma) Total,
 EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0) Facturado".$base.' ORDER BY p.IdProforma DESC LIMIT 100';
 $stmt=$db->prepare($sql);$stmt->execute($params);echo json_encode(array('pendientes'=>(int)$count,'pedidos'=>$stmt->fetchAll(PDO::FETCH_ASSOC)),JSON_UNESCAPED_UNICODE);
} catch(Throwable $e){http_response_code(500);echo json_encode(array('error'=>'No se pudieron consultar los pedidos web.'));}
''',encoding='utf-8')
p=Path('src/components/PedidosWeb.vue');p.write_text('''<template>
 <div class="web-orders">
  <button class="web-orders-button" type="button" @click="abrir"><i class="fas fa-shopping-bag"></i> Pedidos tienda virtual <strong v-if="pending">{{ pending }} nuevos</strong></button>
  <b-modal v-model="show" title="Pedidos de la tienda virtual" size="xl" hide-footer modal-class="nst-dialog">
   <div class="web-orders-tools"><b-form-input v-model="query" placeholder="Buscar cliente, documento o número" @input="buscar"/><b-form-select v-model="status" @change="load"><option value="pendientes">Pendientes de cargar</option><option value="todos">Todos los pedidos</option></b-form-select><b-button @click="load">Actualizar</b-button></div>
   <p class="text-muted">Carga la proforma en una nueva venta con su cliente, cantidades y precios web. La venta actual se conserva.</p>
   <b-alert :show="!!error" variant="danger">{{ error }}</b-alert>
   <b-table :items="orders" :fields="fields" responsive small striped show-empty empty-text="No hay pedidos en esta vista.">
    <template slot="Total" slot-scope="row">S/ {{ Number(row.item.Total).toFixed(2) }}</template>
    <template slot="Estado" slot-scope="row"><span>{{ Number(row.item.Facturado) ? 'Facturado' : Number(row.item.WebCargado) ? 'Cargado en caja' : 'Nuevo' }}</span></template>
    <template slot="Acciones" slot-scope="row"><b-button size="sm" variant="outline-secondary" :href="'/nuevo/index.html#/reportes/proforma/'+row.item.IdProforma" target="_blank">Ver</b-button> <b-button size="sm" variant="danger" :disabled="busy || Number(row.item.Facturado)>0" @click="cargar(row.item)">Cargar en venta</b-button></template>
   </b-table>
  </b-modal>
 </div>
</template>
<script>
import config from '@/store/config';
export default {
 data(){return {show:false,pending:0,orders:[],query:'',status:'pendientes',error:'',busy:false,timer:null,searchTimer:null,previous:null,loading:false,fields:[{key:'Numero',label:'Proforma'},{key:'FechaReg',label:'Fecha'},{key:'Cliente',label:'Cliente'},{key:'DniRuc',label:'Documento'},{key:'Total',label:'Total'},{key:'Estado',label:'Estado'},{key:'Acciones',label:''}]};},
 mounted(){this.load();this.timer=setInterval(()=>{if(!document.hidden)this.load();},10000);},
 beforeDestroy(){clearInterval(this.timer);clearTimeout(this.searchTimer);},
 methods:{
  abrir(){this.show=true;this.load();},
  buscar(){clearTimeout(this.searchTimer);this.searchTimer=setTimeout(this.load,300);},
  async load(){if(this.loading)return;this.loading=true;try{const response=await this.axios.get('/api/pedidos-web.php',{params:{q:this.query,estado:this.status}});const count=Number(response.data.pendientes||0);if(this.previous!==null&&count>this.previous)this.$toasted.show('Nuevo pedido de la tienda virtual. Abra Pedidos tienda virtual.',{duration:7000});this.previous=count;this.pending=count;this.orders=response.data.pedidos||[];this.error='';}catch(e){if(this.show)this.error='No se pudo actualizar la bandeja. Intenta nuevamente.';}finally{this.loading=false;}},
  async cargar(order){this.busy=true;this.error='';try{const response=await this.axios.get(config.API+'proformas/id/'+order.IdProforma);const data=response.data;data.productos=(data.productos||[]).map(p=>({...p,setPrecio:true,precio:Number(p.Precio),cantidad:Number(p.Cantidad),descuento:Number(p.Descuento||0)}));if(!data.productos.length)throw Error('empty');await this.$parent.cargarPedidoWeb(data);await this.axios.post('/api/pedidos-web.php',{id:order.IdProforma});this.show=false;await this.load();this.$toasted.success('Pedido cargado en una nueva venta.');}catch(e){this.error='No se pudo cargar el pedido. Revisa el almacén e inténtalo nuevamente.';}finally{this.busy=false;}}
 }
};
</script>
<style scoped>
.web-orders{margin:8px 0}.web-orders-button{border:1px solid #efbcc6;background:#fff;color:#bf1430;border-radius:11px;padding:9px 15px;font-weight:800}.web-orders-button strong{margin-left:9px;background:#d81735;color:#fff;padding:4px 9px;border-radius:20px}.web-orders-tools{display:flex;gap:9px;margin-bottom:12px}.web-orders-tools select{max-width:240px}@media(max-width:600px){.web-orders-tools{flex-wrap:wrap}}
</style>
''',encoding='utf-8')
# Use an explicit callback prop so bootstrap modal parentage cannot break loading.
s=p.read_text(encoding='utf-8').replace(" data(){", " props: { onLoad: { type: Function, required: true } },\n data(){",1).replace('await this.$parent.cargarPedidoWeb(data);','await this.onLoad(data);');p.write_text(s,encoding='utf-8')
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');s=s.replace('<b-container class="body pos-page" fluid>','<b-container class="body pos-page" fluid>\n   <pedidos-web :on-load="cargarPedidoWeb" />',1);s=s.replace('import Proformas from "@/components/ProformaLista.vue";','import Proformas from "@/components/ProformaLista.vue";\nimport PedidosWeb from "@/components/PedidosWeb.vue";',1);s=s.replace('  components: {','  components: {\n    PedidosWeb,',1);s=s.replace('  methods: {','''  methods: {
    async cargarPedidoWeb(data) {
      if (!this.ventas[this.index].almacen || !this.ventas[this.index].almacen.IdAlmacen) throw new Error('Seleccione un almacén.');
      try {
        await this.setProforma(data);
        await this.$nextTick();
        this.$set(this.ventas[this.index], 'IdProforma', data.IdProforma);
        this.venta.IdProforma = data.IdProforma;
      } finally { this.cargando = false; }
    },''',1)
p.write_text(s,encoding='utf-8')
