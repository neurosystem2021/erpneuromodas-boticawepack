<?php
require_once __DIR__.'/Domain.php';
function cxEscape($value) {return htmlspecialchars((string)$value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function cxDisplayDecimal($value,$digits=2) {
    $digits=min(2,$digits);$p=explode('.',cxRound((string)$value,$digits));$fraction=$digits?rtrim(str_pad($p[1]??'',$digits,'0'),'0'):'';return preg_replace('/\B(?=(\d{3})+(?!\d))/',' ',$p[0]).($fraction!==''?'.'.$fraction:'');
}
function cxDocumentLogo(array $company) {
    if(!in_array($company['LogoPath']??'',['/resources/images/wuanuko-mix-logo.jpeg','/resources/images/wuanuko-mix-logo-transparente.png'],true))return '';
    $file=dirname(__DIR__,2).'/resources/images/wuanuko-mix-logo-transparente.png';
    if(is_file($file))return 'data:image/png;base64,'.base64_encode(file_get_contents($file));
    $file=dirname(__DIR__,2).'/resources/images/wuanuko-mix-logo.jpeg';
    return is_file($file)?'data:image/jpeg;base64,'.base64_encode(file_get_contents($file)):'';
}
function cxDocumentHtml(array $doc,$kind) {
    if($kind==='proposal'&&!empty($doc['Instantanea']['perfilComercial'])){require_once __DIR__.'/QuoteDocument.php';return cxWuanukoQuoteHtml($doc);}
    $isQuote=$kind==='proposal';$snapshot=$doc['Instantanea'];$company=$snapshot['empresa'];$client=$snapshot['cliente'];$work=$snapshot['obra'];if(empty($work['IdObra']))$work=array_merge($work,['Nombre'=>'Por definir al confirmar el pedido','Direccion'=>'','Contacto'=>'Pendiente','Telefono'=>'']);
    $code=($isQuote?'CW':'PW').'-'.$doc['Anio'].'-'.str_pad($doc['Numero'],5,'0',STR_PAD_LEFT);$version=$isQuote?' · VERSIÓN '.$doc['Version']:'';
    $rate=$isQuote?$doc['TasaImpuesto']:null;$tariff=$isQuote&&$doc['Tipo']==='TARIFARIO';
    $title=$isQuote?($tariff?'TARIFARIO DE SUMINISTRO':'PROFORMA DE CONCRETO'):'PEDIDO DE SUMINISTRO';
    $html='<!doctype html><html lang="es"><head><meta charset="utf-8"><title>'.cxEscape($code).'</title><style>';
    $html.='@page{margin:32px 34px 38px}body{font-family:"DejaVu Sans",sans-serif;color:#203644;font-size:9px;line-height:1.45}h1{font-size:21px;margin:4px 0 3px;color:#153c48}h2{font-size:11px;margin:17px 0 7px;color:#176779}p{margin:4px 0}table{width:100%;border-collapse:collapse;table-layout:fixed}th{background:#164f5f;color:white;font-size:8px;padding:8px 5px;text-align:left}td{padding:7px 5px;vertical-align:top;border-bottom:1px solid #dce5e8;overflow-wrap:break-word}tr{page-break-inside:avoid}.meta td{width:50%;border:1px solid #dce5e8;padding:10px}.label{font-size:7px;text-transform:uppercase;color:#4f6d78;letter-spacing:.7px}.top{border-bottom:3px solid #168395;padding-bottom:13px}.top td{border:0;padding:0}.right{text-align:right}.num{text-align:right;white-space:nowrap}.muted{color:#596f7a}.demo{font-size:10px;font-weight:bold;color:#a44800;letter-spacing:.5px;padding-bottom:7px}.status{font-weight:bold;color:#176779}.totals{width:45%;margin-left:55%;margin-top:14px}.totals td{padding:6px;border:0}.totals .grand td{font-size:13px;font-weight:bold;border-top:2px solid #176779;color:#164f5f}.conditions li{margin:4px 0}.footer{font-size:7px;color:#596f7a;margin-top:23px;border-top:1px solid #dce5e8;padding-top:8px}.terms{white-space:pre-wrap;word-wrap:break-word}.line-desc small{display:block;color:#596f7a;font-size:7px}.signature{margin-top:24px;font-size:8px}.signature td{width:50%;height:34px;vertical-align:bottom;border-bottom:1px solid #8da6b2}.no-total{font-weight:bold;color:#164f5f;margin:12px 0}.note{font-size:8px;color:#596f7a}';
    $html.='</style></head><body>';
    if(!empty($doc['EsPrueba']))$html.='<div class="demo">DATOS DE PRUEBA · NO UTILIZAR COMO ORDEN OPERATIVA</div>';
    $logo=cxDocumentLogo($company);$logoCell=$logo?'<td style="width:20%"><img src="'.$logo.'" style="width:102px;height:76px;object-fit:contain"></td>':'';
    $contacts='';if(trim((string)($company['TELEFONO']??''))!=='')$contacts.='<p class="muted">Tel.: '.cxEscape($company['TELEFONO']).'</p>';if(trim((string)($company['EMAIL']??''))!=='')$contacts.='<p class="muted">Correo: '.cxEscape($company['EMAIL']).'</p>';
    $html.='<table class="top"><tr>'.$logoCell.'<td style="width:'.($logo?'53':'64').'%"><div class="label">NEUROSOFT · CONCRETERA</div><h1'.($logo?' style="font-size:15px"':'').'>'.cxEscape($company['RAZONSOCIAL']).'</h1><p><strong>RUC '.cxEscape($company['RUC']??'').'</strong></p><p class="muted">'.cxEscape($company['DIRECCION']??'').'</p>'.$contacts.'</td><td class="right" style="width:'.($logo?'27':'36').'%"><strong>'.cxEscape($code).'</strong><p>'.cxEscape($version).'</p><p class="status">'.cxEscape($doc['EstadoEfectivo']??$doc['Estado']).'</p></td></tr></table>';
    $html.='<h2>'.$title.'</h2><table class="meta"><tr><td><div class="label">CLIENTE</div><strong>'.cxEscape($client['Cliente']).'</strong><p>Documento: '.cxEscape($client['DniRuc']).'</p><p>Dirección fiscal: '.cxEscape($client['Direccion']).'</p></td><td><div class="label">OBRA Y ENTREGA</div><strong>'.cxEscape($work['Nombre']).'</strong><p>'.cxEscape($work['Direccion']).'</p><p>'.cxEscape(implode(' / ',array_filter([$work['Distrito'],$work['Provincia'],$work['Departamento']]))).'</p><p>Contacto: '.cxEscape($work['Contacto']).' · '.cxEscape($work['Telefono']).'</p></td></tr></table>';
    $html.='<p class="muted">Moneda: '.cxEscape($doc['Moneda']).($isQuote?' · Emisión: '.cxEscape($doc['FechaEmision']).' · Vigente hasta: '.cxEscape($doc['VigenteHasta']):' · Aceptación: '.cxEscape($doc['FechaAceptacion'])).'</p>';
    if($isQuote)$html.='<p class="note">Impuesto: '.cxEscape($doc['CondicionImpuesto']).' · Tasa: '.cxDisplayDecimal($doc['TasaImpuesto'],4).'% · Tarifas '.(!empty($doc['PreciosIncluyenImpuesto'])?'con impuesto incluido':'sin impuesto incluido').'.</p>';
    $html.='<h2>'.($tariff?'ALTERNATIVAS Y TARIFAS UNITARIAS':'PRODUCTOS COTIZADOS').'</h2><table><thead><tr><th style="width:42%">Especificación / servicio</th><th class="num" style="width:12%">Cantidad</th><th style="width:10%">Unidad</th><th class="num" style="width:17%">Tarifa unitaria</th><th class="num" style="width:19%">'.($tariff?'Condición':'Importe').'</th></tr></thead><tbody>';
    foreach($doc['partidas'] as $item) {
        $source=$isQuote?$item:$item['Instantanea'];$spec=$isQuote?($item['Instantanea']['especificacion']??null):($source['Instantanea']['especificacion']??null);
        $description=$source['Descripcion'];$details=[];
        if($spec)$details[]="f'c ".cxDisplayDecimal($spec['Resistencia']).' '.$spec['UnidadResistencia'].(!empty($spec['EdadDias'])?' · '.$spec['EdadDias'].' días':'').(!empty($spec['Descarga'])?' · Descarga: '.$spec['Descarga']:'');
        if(isset($item['Instantanea']['descuentoImporte'])){if(bccomp($item['Instantanea']['descuentoImporte'],'0',2)>0)$details[]='Descuento '.cxDisplayDecimal($item['Instantanea']['descuentoImporte']);}elseif(bccomp($item['DescuentoPct'],'0',4)>0)$details[]='Descuento autorizado '.$item['DescuentoPct'].'%';
        if(!empty($item['Incluido']))$details[]='INCLUIDO SIN CARGO ADICIONAL';
        if($isQuote&&bccomp($item['Minimo'],'0',4)>0)$details[]='Mínimo pactado: '.cxDisplayDecimal($item['Minimo']).' '.$item['Unidad'];
        $quantity=$isQuote?$item['Cantidad']:$item['CantidadContratada'];
        if(!$tariff) {if($isQuote){$priceQuantity=bccomp($quantity,$item['Minimo'],4)<0?$item['Minimo']:$quantity;$value=cxAmounts($priceQuantity,$item['PrecioUnitario'],$item['DescuentoPct'],(int)$item['Incluido'],$rate,(int)$doc['PreciosIncluyenImpuesto'],$item['Instantanea']['descuentoImporte']??null);$amount=$value['total'];}else{$amount=$item['Total'];if(bccomp($item['CantidadPrecio'],$quantity,4)!==0)$details[]='Cantidad de precio (mínimo): '.cxDisplayDecimal($item['CantidadPrecio']);}}
        $html.='<tr><td class="line-desc"><strong>'.cxEscape($description).'</strong><small>'.cxEscape(implode(' · ',$details)).'</small></td><td class="num">'.($tariff?'—':cxDisplayDecimal($quantity,4)).'</td><td>'.cxEscape($item['Unidad']).'</td><td class="num">'.cxDisplayDecimal($item['PrecioUnitario'],2).'</td><td class="num">'.($tariff?(!empty($item['Incluido'])?'Incluido':'Por contratar'):cxDisplayDecimal($amount)).'</td></tr>';
    }
    $html.='</tbody></table>';
    if($tariff)$html.='<p class="no-total">Tarifario por unidad. Las alternativas no se suman como una compra.</p><p class="note">La aceptación debe elegir partidas y cantidades; el total del pedido se obtiene únicamente de lo contratado.</p>';
    else $html.='<table class="totals" style="width:100%;margin-left:0"><tr><td style="width:55%"></td><td style="width:20%">Base</td><td class="num" style="width:25%">'.cxDisplayDecimal($doc['Base']).'</td></tr><tr><td></td><td>Impuesto</td><td class="num">'.cxDisplayDecimal($doc['Impuesto']).'</td></tr><tr class="grand"><td style="border:0"></td><td>TOTAL '.cxEscape($doc['Moneda']).'</td><td class="num">'.cxDisplayDecimal($doc['Total']).'</td></tr></table>';
    $c=$doc['Condiciones'];$pay=$doc['Pago'];
    $html.='<h2>CONDICIONES DE ESTA VERSIÓN</h2><ul class="conditions"><li>Traslado: '.(!empty($c['trasladoIncluido'])?'incluido':'según servicio contratado').'. Bombeo: '.(!empty($c['bombeoIncluido'])?'incluido':'según servicio contratado').'.</li><li>Anticipación pactada: '.cxDisplayDecimal($c['anticipacionHoras']).' horas. Permanencia pactada: '.cxDisplayDecimal($c['permanenciaMinutos']).' minutos.</li><li>Ensayos acordados: '.cxEscape($c['ensayosDias']?implode(', ',$c['ensayosDias']).' días':'por confirmar').'.</li><li>Pago: '.cxEscape($pay['modalidad']).'. Adelanto pactado: '.cxDisplayDecimal($pay['adelantoPactadoPct']).'%. Condición de liberación: '.cxEscape($pay['liberacion']).'.</li></ul>';
    if($pay['vencimiento'])$html.='<p>Vencimiento pactado: '.cxEscape($pay['vencimiento']).'</p>';
    if($pay['cuentaReferencia'])$html.='<p class="terms">Cuenta de cobro: '.cxEscape($pay['cuentaReferencia']).'</p>';
    foreach(['requisitosAcceso'=>'Acceso a obra','particulares'=>'Acuerdos particulares'] as $key=>$label)if($c[$key]!=='')$html.='<p class="terms"><strong>'.$label.':</strong> '.cxEscape($c[$key]).'</p>';
    if(!$isQuote){
        $confirmation=$snapshot['aceptacion']??null;
        if($confirmation){$responsible=$confirmation['responsable'];$ref=$confirmation['proforma'];$html.='<h2>RESPONSABLE Y EVIDENCIA DE ACEPTACIÓN</h2><p>Área responsable: <strong>'.cxEscape($responsible['areaNombre']).'</strong></p><p>Confirmado por: <strong>'.cxEscape($responsible['nombre']).'</strong> ('.cxEscape($responsible['usuario']).') · Permiso: '.cxEscape($responsible['rol']).'</p><p>Proforma: <strong>'.cxEscape($ref['numero']).'</strong> · Versión '.$ref['version'].' · ERP '.$ref['idProformaERP'].'</p><p>Fecha: '.cxEscape($confirmation['fecha']).' America/Lima</p><p>Contacto que aceptó por el cliente: '.cxEscape($confirmation['contactoCliente']?:'No indicado').'</p>';if($confirmation['observaciones']!=='')$html.='<p class="terms">Observaciones: '.cxEscape($confirmation['observaciones']).'</p>';}
        else $html.='<h2>CONSTANCIA DE ACEPTACIÓN</h2><p>Aceptante: '.cxEscape($doc['Aceptante']).'</p><p class="terms">Referencia de evidencia declarada: '.cxEscape($doc['Evidencia']).'</p>';
        $html.='<p class="note">Constancia de aceptación comercial registrada por el usuario autorizado; no representa firma digital certificada ni aprobación de receta.</p>';
    }
    $html.='<p class="note">La especificación comercial no es una receta técnica aprobada. Este documento no es guía de despacho, GRE, comprobante tributario ni constancia de pago.</p><div class="footer">'.cxEscape($code.$version).' · Referencia ERP: '.cxEscape($isQuote?$doc['IdProforma']:$doc['IdPreOrden']).' · Generado '.cxEscape(cxNow()).' America/Lima · Datos persistidos y autorizados por empresa.</div></body></html>';
    return $html;
}
function cxRenderPdf($html,$orientation='portrait') {
    cxChoice($orientation,['portrait','landscape'],'la orientación del PDF');
    if(!class_exists('Dompdf\\Dompdf')) {
        $base='C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf';
        require_once $base.'/lib/php-font-lib/src/FontLib/Autoloader.php';
        require_once $base.'/lib/php-svg-lib/src/autoload.php';
        require_once $base.'/src/Autoloader.php';Dompdf\Autoloader::register();
    }
    $options=new Dompdf\Options();$options->set('defaultMediaType','print');$options->set('isRemoteEnabled',false);$options->set('isPhpEnabled',false);$options->set('defaultFont','DejaVu Sans');
    $tmp=sys_get_temp_dir().'/neurosoft-concretera-pdf';if(!is_dir($tmp)&&!mkdir($tmp,0700,true))throw new CxError('No se pudo preparar el directorio PDF.',500);$options->set('tempDir',$tmp);$options->set('fontCache',$tmp);
    // Arial is installed locally on Windows. Keep its generated metrics outside the shared library.
    $arial=strpos($html,'font-family:Arial,')!==false;$fontDir=$tmp.'/arial';
    if($arial){if(!is_dir($fontDir)&&!mkdir($fontDir,0700,true)&&!is_dir($fontDir))throw new CxError('No se pudo preparar Arial.',500);$options->set('fontDir',$fontDir);$options->set('fontCache',$fontDir);}
    $pdf=new Dompdf\Dompdf($options);
    if($arial){
        $lock=fopen($fontDir.'/registration.lock','c');if(!$lock||!flock($lock,LOCK_EX))throw new CxError('No se pudo preparar la tipografía.',500);
        try{
            // Reload after taking the lock: another request may have populated the cache.
            $metrics=$pdf->getFontMetrics();$metrics->loadFontFamilies();
            $fonts=dirname(__DIR__,2).'/resources/fonts';if(!is_file($fonts.'/arial.ttf'))$fonts=rtrim(getenv('WINDIR')?:'C:/Windows','/\\').'/Fonts';
            foreach(['normal'=>'arial.ttf','bold'=>'arialbd.ttf'] as $weight=>$file){$path=$fonts.'/'.$file;if(is_file($path)&&!$metrics->registerFont(['family'=>'Arial','weight'=>$weight,'style'=>'normal'],$path))throw new CxError('No se pudo cargar Arial para el PDF.',500);}
        }finally{flock($lock,LOCK_UN);fclose($lock);}
    }
    $pdf->loadHtml($html,'UTF-8');$pdf->setPaper('A4',$orientation);$pdf->render();return $pdf->output();
}
