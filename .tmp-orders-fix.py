from pathlib import Path
stage=Path('deploy-src/web-orders/api');stage.mkdir(parents=True,exist_ok=True)
p=stage/'pedidos-web.php';s=Path('C:/xampp/htdocs/boticav1/api/pedidos-web.php').read_text(encoding='utf-8')
s=s.replace("$db=appPdoConnection();", "$db=appPdoConnection();\n $canDelete=strtolower((string)$_SESSION['user'])==='superadmin';")
needle='  $stmt=$db->prepare("UPDATE Ve_Proforma SET WebCargado=1'
insert='''  if (($body['accion']??'')==='eliminar') {
   if (!$canDelete) {http_response_code(403);echo json_encode(array('error'=>'Solo superadmin puede eliminar pedidos.'));exit;}
   $stmt=$db->prepare("UPDATE Ve_Proforma p SET p.Anulado=1 WHERE p.IdProforma=? AND p.UsuarioReg='TIENDA' AND COALESCE(p.Anulado,0)=0 AND NOT EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)");
   $stmt->execute(array($id));
   if(!$stmt->rowCount()){http_response_code(409);echo json_encode(array('error'=>'El pedido ya fue facturado, eliminado o no existe.'));exit;}
   echo json_encode(array('ok'=>true));exit;
  }
'''
assert needle in s;s=s.replace(needle,insert+needle)
s=s.replace("if($status==='pendientes')$base.=' AND COALESCE(p.WebCargado,0)=0'.$unbilled;", "if($status==='pendientes')$base.=$unbilled;\n if($status==='facturados')$base.=' AND EXISTS(SELECT 1 FROM Ve_DocVenta v WHERE v.IdProforma=p.IdProforma AND COALESCE(v.Anulado,0)=0)';")
s=s.replace("array('pendientes'=>(int)$count,'pedidos'", "array('puedeEliminar'=>$canDelete,'pendientes'=>(int)$count,'pedidos'")
p.write_text(s,encoding='utf-8')
p=stage/'index.php';s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8')
s=s.replace('WHERE Ve_Proforma.Anulado = 0','WHERE Ve_Proforma.Anulado = 0 AND COALESCE(Ve_Proforma.UsuarioReg,\'\') <> \'TIENDA\'')
s=s.replace('SELECT COUNT(*) as total FROM Ve_Proforma"','SELECT COUNT(*) as total FROM Ve_Proforma WHERE COALESCE(Anulado,0)=0 AND COALESCE(UsuarioReg,\'\') <> \'TIENDA\'"')
p.write_text(s,encoding='utf-8')
p=Path('src/components/PedidosWeb.vue');s=p.read_text(encoding='utf-8')
s=s.replace('<div class="web-orders-tools"><b-form-input','<div class="web-orders-tabs"><b-button :variant="status===\'pendientes\' ? \'danger\' : \'outline-secondary\'" @click="status=\'pendientes\';load()">Pedidos por atender</b-button> <b-button :variant="status===\'facturados\' ? \'danger\' : \'outline-secondary\'" @click="status=\'facturados\';load()">Facturados</b-button></div>\n   <div class="web-orders-tools"><b-form-input')
start=s.index('<b-form-select v-model="status"');end=s.index('</b-form-select>',start)+len('</b-form-select>');s=s[:start]+s[end:]
s=s.replace('<b-button size="sm" variant="danger" :disabled="busy || Number(row.item.Facturado)>0"','<b-button v-if="!Number(row.item.Facturado)" size="sm" variant="danger" :disabled="busy"')
s=s.replace('Cargar en venta</b-button></template>','Cargar en venta</b-button> <b-button v-if="canDelete && !Number(row.item.Facturado)" size="sm" variant="outline-danger" :disabled="busy" @click="eliminar(row.item)">Eliminar</b-button></template>')
s=s.replace('show:false,pending:0','show:false,canDelete:false,pending:0')
s=s.replace('this.orders=response.data.pedidos||[];', 'this.canDelete=response.data.puedeEliminar===true;this.orders=response.data.pedidos||[];')
s=s.replace('  async cargar(order)', '''  async eliminar(order){if(!window.confirm('¿Eliminar el pedido web '+order.Numero+'?'))return;this.busy=true;try{await this.axios.post('/api/pedidos-web.php',{id:order.IdProforma,accion:'eliminar'});await this.load();this.$toasted.success('Pedido eliminado.');}catch(e){this.error=(e.response&&e.response.data&&e.response.data.error)||'No se pudo eliminar el pedido.';}finally{this.busy=false;}},
  async cargar(order)''')
s=s.replace('.web-orders{margin:', '.web-orders-tabs{margin-bottom:14px}.web-orders{margin:')
p.write_text(s,encoding='utf-8')
