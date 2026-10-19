<?php
function qpFeaturedHeader($logoHtml,$company,$contact){
 $image='';foreach([dirname(__DIR__).'/nuevo/static/images/proforma-header-wide-v3.png',dirname(__DIR__,2).'/static/images/proforma-header-wide-v3.png'] as $path)if(is_file($path)){$image='<img alt="Equipos de tecnología y seguridad" src="data:image/png;base64,'.base64_encode(file_get_contents($path)).'" style="width:300px;height:auto">';break;}
 return '<table class="brand"><tr><td width="35%">'.$logoHtml.'</td><td style="text-align:right">'.$image.'</td></tr><tr><td colspan="2" class="company" style="padding-top:4px"><b>'.qpEscape($company['RAZONSOCIAL']).'</b><br>RUC '.qpEscape($company['RUC']).' · '.qpEscape($company['DIRECCION']).'<br>'.qpEscape($contact).'</td></tr></table>';
}
function qpFeaturedImage($filename){
 if(!is_string($filename)||$filename===''||basename(str_replace('\\','/',$filename))!==$filename)return '';
 foreach([dirname(__DIR__).'/resources/images/productos/',dirname(__DIR__,2).'/resources/images/productos/'] as $root){
  $path=$root.$filename;if(!is_file($path)||filesize($path)>5000000)continue;
  $size=@getimagesize($path);if(!$size||!in_array($size['mime'],['image/png','image/jpeg','image/gif'],true))continue;
  return '<img class="featured-image" alt="Imagen del producto" src="data:'.$size['mime'].';base64,'.base64_encode(file_get_contents($path)).'">';
 }
 return '';
}
function qpFeaturedProductHtml($p,$index,$details,$line,$hidden){
 $image=qpFeaturedImage($p['Imagen']??'');
 $html='<article class="featured-product"><table class="featured-main"><tr><td class="featured-photo">'.($image?:'<span class="featured-no-photo">Imagen no disponible</span>').'</td><td class="featured-copy"><span class="featured-eyebrow">PRODUCTO '.str_pad((string)($index+1),2,'0',STR_PAD_LEFT).'</span><h3>'.qpEscape($p['Producto']).'</h3>'.($details!==''?'<h4>CARACTERÍSTICAS DEL PRODUCTO</h4><div class="featured-description">'.$details.'</div>':'').'</td></tr></table><table class="featured-pricing"><tr><td>CANTIDAD<strong>'.qpEscape($p['Cantidad']).'</strong></td>';
 if(!$hidden)$html.='<td>PRECIO UNITARIO<strong>'.qpMoney($p['Precio']).'</strong></td>'.((float)$p['Descuento']>0?'<td>DESCUENTO<strong>'.qpMoney($p['Descuento']).'</strong></td>':'').'<td class="featured-amount">IMPORTE<strong>'.qpMoney($line).'</strong></td>';
 return $html.'</tr></table></article>';
}
function qpFeaturedCss(){return '.featured-quote .summary{margin:6px 0 8px}.featured-quote .meta{margin:6px 0 10px}.featured-quote .featured-product{padding:9px;margin:8px 0}.featured-quote .featured-description{line-height:1.35}.featured-quote .featured-pricing{margin-top:7px}.featured-quote .featured-pricing td{padding:6px 9px}.featured-quote .featured-copy h3{margin-bottom:7px}.featured-quote h2{margin:8px 0 5px}.featured-quote .heading{margin:6px 0;padding-top:7px}' . '.featured-product{border:1px solid #ead5dd;border-top:3px solid #bd1d35;padding:12px;margin:10px 0 13px;page-break-inside:avoid}.featured-main{table-layout:fixed}.featured-main td{vertical-align:middle}.featured-photo{width:42%;padding-right:15px;text-align:center}.featured-image{width:240px;max-width:240px;max-height:150px;height:auto}.featured-no-photo{font-size:9px;color:#72525f}.featured-copy h3{font-size:13px;line-height:1.35;font-weight:bold;color:#39232d;margin:6px 0 10px}.featured-copy h4{font-size:7.5px;letter-spacing:.5px;font-weight:bold;color:#aa1f39;margin:0 0 6px}.featured-eyebrow{font-size:7px;letter-spacing:.7px;color:#a5243d;font-weight:bold}.featured-description{font-size:10px;line-height:1.6;font-weight:bold;color:#49343e;word-wrap:break-word}.featured-description ul{padding-left:14px;margin:5px 0}.featured-description li{margin-bottom:4px}.featured-pricing{margin-top:12px;border-top:1px solid #ead5dd;table-layout:fixed}.featured-pricing td{padding:9px 10px;font-size:7px;font-weight:bold;color:#83616f}.featured-pricing strong{display:block;font-size:11px;color:#412b36;margin-top:5px}.featured-pricing .featured-amount{background:#fff1f4;text-align:right}.featured-pricing .featured-amount strong{color:#b51e38;font-size:13px}';}
