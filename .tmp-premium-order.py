from pathlib import Path
root=Path(r'C:\xampp\htdocs\boticav1');out=Path('integration/purchase-order-premium/api/sunat/plugins/dompdf');out.mkdir(parents=True,exist_ok=True)
helper='''<?php
function purchaseOrderReportHtml($cab) {
 $order=$cab['ordencompra'] ?? [];$company=$cab['empresa'] ?? [];$items=$cab['detalle'] ?? [];
 $e=function($value){return htmlspecialchars((string)$value,ENT_QUOTES,'UTF-8');};
 $money=function($value){return 'S/ '.number_format((float)$value,2);};
 $total=0;$units=0;$rows='';
 foreach($items as $i=>$item){$amount=(float)$item['Cantidad']*(float)$item['Precio'];$total+=$amount;$units+=(float)$item['Cantidad'];
 $rows.='<tr'.($i%2?' class="alternate"':'').'><td class="code">'.$e($item['Codigo'] ?: $item['IdProducto']).'</td><td class="description">'.$e($item['Producto']).'<div class="sub">'.$e($item['ProductoMarca'] ?? '').'</div></td><td class="num">'.$e($item['Cantidad']).'</td><td class="num">'.$money($item['Precio']).'</td><td class="num strong">'.$money($amount).'</td></tr>';}
 $logoPath=realpath(__DIR__.'/../../../../resources/images/logo-ticket.png');
 $logo=$logoPath?'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)):'';
 return '<!doctype html><html lang="es"><head><meta charset="utf-8"><style>
 @page{margin:32px 38px 45px}body{font-family:DejaVu Sans,sans-serif;font-size:10px;color:#294455;line-height:1.5}table{width:100%;border-collapse:collapse}.head td{vertical-align:middle;padding:0 0 17px;border-bottom:3px solid #168e99}.brand{width:51%}.brand img{width:155px;max-height:65px}.right{text-align:right}.eyebrow{font-size:8px;letter-spacing:1.3px;color:#168e99;font-weight:bold}.title{font-size:21px;color:#153e53;font-weight:bold;margin:4px 0}.number{font-size:15px;color:#153e53;font-weight:bold}.company{font-size:8px;color:#6f8794;margin-top:7px}.info{margin:20px 0 18px}.info>tbody>tr>td{width:50%;vertical-align:top;padding:13px 15px;background:#f2f7fa;border:1px solid #e1eaf0}.label{font-size:8px;color:#718996;letter-spacing:.5px;text-transform:uppercase}.value{font-size:11px;font-weight:bold;color:#234c61;margin:3px 0 9px}.meta{font-size:9px;margin:4px 0}.section{font-size:11px;font-weight:bold;color:#193e52;margin:17px 0 8px}.items{font-size:9px;table-layout:fixed}.items thead{display:table-header-group}.items th{background:#163f54;color:white;text-align:left;padding:10px 8px;font-size:8px}.items td{padding:11px 8px;border-bottom:1px solid #e2eaf0;vertical-align:top}.items tr{page-break-inside:avoid}.items .alternate{background:#f6f9fb}.items .code{font-size:8px;color:#6d8798}.num{text-align:right!important;white-space:nowrap}.strong{font-weight:bold}.sub{font-size:8px;color:#8a9ca5;margin-top:4px}.totals{margin-top:16px;page-break-inside:avoid}.totals td{vertical-align:top;padding:12px}.summary{background:#eef5f8;font-size:9px;color:#657f8e;width:57%}.grand{background:#163f54;color:white;text-align:right}.grand small{font-size:8px}.grand b{font-size:21px;display:block;margin-top:3px}.note{margin-top:20px;border-left:3px solid #168e99;padding:9px 12px;background:#f5fafb;font-size:9px;color:#617e8e}.footer{margin-top:25px;padding-top:10px;border-top:1px solid #dce8ee;font-size:8px;color:#8097a3}.footer td{width:50%}
 </style></head><body><table class="head"><tr><td class="brand">'.($logo?'<img src="'.$logo.'">':'<b>NEUROSYSTEM</b>').'<div class="company"><b>'.$e($company['RAZONSOCIAL'] ?? 'NEUROSYSTEM').'</b><br>RUC: '.$e($company['RUC'] ?? $cab['txtNRO_DOCUMENTO_EMPRESA'] ?? '').'<br>'.$e($company['DIRECCION'] ?? '').'</div></td><td class="right"><span class="eyebrow">LOGÍSTICA / ABASTECIMIENTO</span><div class="title">ORDEN DE COMPRA</div><div class="number">N.º '.$e($order['Anio']).'-'.str_pad($e($order['Numero']),5,'0',STR_PAD_LEFT).'</div><div class="company">Fecha: '.$e($order['FechaOrdenCompra']).'</div></td></tr></table>
 <table class="info"><tr><td><div class="label">Proveedor</div><div class="value">'.$e($order['Proveedor']).'</div><div class="meta">RUC: '.$e($order['RucProveedor'] ?? $items[0]['Ruc'] ?? '').'</div><div class="meta">'.$e($order['DireccionProveedor'] ?? $items[0]['Direccion'] ?? '').'</div></td><td><div class="label">Destino / almacén</div><div class="value">'.$e($order['Almacen']).'</div><div class="meta"><b>Elaborado por:</b> '.$e($order['UsuarioReg'] ?: 'Sin registro').'</div><div class="meta"><b>Fecha de recojo:</b> '.$e($order['FechaRecojo'] ?? 'Por coordinar').'</div></td></tr></table>
 <div class="section">Detalle de productos solicitados</div><table class="items"><thead><tr><th width="10%">Código</th><th width="46%">Descripción</th><th width="10%" class="num">Cant.</th><th width="16%" class="num">P. unitario</th><th width="18%" class="num">Importe</th></tr></thead><tbody>'.$rows.'</tbody></table>
 <table class="totals"><tr><td class="summary"><b>'.count($items).' productos solicitados</b><br>Cantidad total: '.$e($units).' unidades<br>Moneda: Soles (PEN)</td><td class="grand"><small>IMPORTE TOTAL DE LA ORDEN</small><br><b>'.$money($total).'</b></td></tr></table>
 <div class="note">Por favor, confirmar disponibilidad y coordinar la entrega de los productos detallados, indicando el número de esta orden.</div><table class="footer"><tr><td>Preparado por: '.$e($order['UsuarioReg'] ?: 'Sin registro').'</td><td class="right">Orden '.$e($order['Anio']).'-'.$e($order['Numero']).'</td></tr></table></body></html>';
}
'''
(out/'purchaseOrderReport.php').write_text(helper,encoding='utf-8')
wrapper='''<?php
require_once 'lib/html5lib/Parser.php';
require_once 'lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'lib/php-svg-lib/src/autoload.php';
require_once 'src/Autoloader.php';
Dompdf\\Autoloader::register();
require_once __DIR__.'/purchaseOrderReport.php';
$cab=json_decode(file_get_contents('php://input'),true);
if(!is_array($cab)||empty($cab['ordencompra'])){http_response_code(422);exit('Orden inválida');}
$html=purchaseOrderReportHtml($cab);
$dompdf=new Dompdf\\Dompdf();$dompdf->set_paper('A4','portrait');$dompdf->load_html($html);$dompdf->render();
$ruc=preg_replace('/[^0-9]/','',(string)$cab['txtNRO_DOCUMENTO_EMPRESA']);
$year=(int)$cab['ordencompra']['Anio'];$number=(int)$cab['ordencompra']['Numero'];
$target=__DIR__.'/../../api_cpe/PRODUCCION/'.$ruc.'/OrdenCompra_'.$year.'_'.$number.'.pdf';
file_put_contents($target,$dompdf->output());
'''
(out/'OrdenCompra.php').write_text(wrapper,encoding='utf-8')
p=root/'api/index.php';s=p.read_text(encoding='utf-8');a=s.index("$app->get('/reporte/ordencomprapdf'");b=s.index("$app->get('/reporte/ordencompra/id'",a);part=s[a:b].replace("$idOrdenCompra = $request->getParam('id');","$idOrdenCompra = (int)$request->getParam('id');",1).replace('SELECT Lo_OrdenCompra.*,Lo_Proveedor.Proveedor, Lo_Almacen.Almacen','SELECT Lo_OrdenCompra.*,Lo_Proveedor.Proveedor,Lo_Proveedor.Ruc AS RucProveedor,Lo_Proveedor.Direccion AS DireccionProveedor, Lo_Almacen.Almacen',1)
part=part.replace('        $data = array(','''        $empresaRow=$this->db->query('SELECT RAZONSOCIAL,RUC,DIRECCION FROM Gen_Empresa LIMIT 1')->fetch(PDO::FETCH_ASSOC);
        $data = array(
            'empresa' => $empresaRow ?: array(),''',1)
s=s[:a]+part+s[b:];p=Path('integration/purchase-order-premium/api/index.php');p.write_text(s,encoding='utf-8')
