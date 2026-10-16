from pathlib import Path
p=Path('integration/order-supplier/api/index.php');s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8');a=s.index("$app->get('/reporte/ordencomprapdf',");b=s.index("$app->get('/reporte/ordencompra/id',",a)
s=s[:a]+'''$app->get('/reporte/ordencomprapdf', function (Request $request, Response $response, array $args) {
    try {
        if(session_status()===PHP_SESSION_ACTIVE)session_write_close();
        require_once __DIR__.'/purchase-order-report.php';
        $data=nstSupplierOrderData($this->db,(int)$request->getParam('id'));
        $bytes=nstSupplierOrderPdf($data);
        if(substr($bytes,0,5)!=='%PDF-')throw new RuntimeException('Documento inválido');
        $order=$data['ordencompra'];
        $name='OrdenCompra_'.(int)$order['Anio'].'_'.(int)$order['Numero'].'.pdf';
        $ruc=preg_replace('/[^0-9]/','',$data['txtNRO_DOCUMENTO_EMPRESA']);
        $target=__DIR__.'/sunat/api_cpe/PRODUCCION/'.$ruc.'/'.$name;
        if(file_put_contents($target,$bytes)===false)throw new RuntimeException('No se pudo guardar el PDF');
        if($request->getParam('inline')){
            $response->getBody()->write($bytes);
            return $response->withHeader('Content-Type','application/pdf')->withHeader('Content-Disposition','inline; filename="'.$name.'"')->withHeader('Cache-Control','private, no-store');
        }
        return $response->withJson($_SERVER['HTTP_HOST'].'/api/sunat/api_cpe/PRODUCCION/'.$ruc.'/'.$name);
    } catch (\\Throwable $e) {
        if($request->getParam('inline')){
            $response->getBody()->write('No se pudo generar el PDF. Vuelva al registro e intente nuevamente.');
            return $response->withStatus(500)->withHeader('Content-Type','text/plain; charset=utf-8');
        }
        return $response->withJson(['error'=>'No se pudo generar el PDF.'],500);
    }
});

'''+s[b:];p.write_text(s,encoding='utf-8')
p=Path('src/views/reportes/Ordenes.vue');s=p.read_text(encoding='utf-8');a=s.index('    async generarPdf(');b=s.index('    actualizarDet(',a)
s=s[:a]+'''    generarPdf(IdOrdenCompra) {
      const url = `${this.API}reporte/ordencomprapdf?id=${encodeURIComponent(IdOrdenCompra)}&inline=1`;
      const preview = window.open(url, "_blank");
      if (!preview) window.location.assign(url);
    },
'''+s[b:];p.write_text(s,encoding='utf-8')
p=Path('integration/order-supplier/api/purchase-order-report.php');s=p.read_text(encoding='utf-8');s+='''
function nstSupplierOrderPdf($data) {
    $base=__DIR__.'/sunat/plugins/dompdf';
    ob_start();
    try {
        require_once $base.'/lib/html5lib/Parser.php';
        require_once $base.'/lib/php-font-lib/src/FontLib/Autoloader.php';
        require_once $base.'/lib/php-svg-lib/src/autoload.php';
        require_once $base.'/src/Autoloader.php';
        \\Dompdf\\Autoloader::register();
        require_once $base.'/purchaseOrderReport.php';
        $pdf=new \\Dompdf\\Dompdf();$pdf->set_paper('A4','portrait');
        $pdf->load_html(purchaseOrderReportHtml($data));$pdf->render();
        return $pdf->output();
    } finally {ob_end_clean();}
}
''';p.write_text(s,encoding='utf-8')
p=Path('integration/order-supplier/api/sunat/plugins/dompdf/purchaseOrderReport.php');s=p.read_text(encoding='utf-8');old="$logo=$logoPath?'data:image/png;base64,'.base64_encode(file_get_contents($logoPath)):'';"
new='''$logo='';
 if($logoPath && is_file($logoPath)) {
   $raw=file_get_contents($logoPath);
   if(function_exists('imagecreatefromstring')) {
     $source=@imagecreatefromstring($raw);
     if($source) {
       $width=imagesx($source);$height=imagesy($source);
       if($width>700) {
         $small=imagecreatetruecolor(700,max(1,(int)round($height*700/$width)));
         $white=imagecolorallocate($small,255,255,255);imagefill($small,0,0,$white);
         imagecopyresampled($small,$source,0,0,0,0,imagesx($small),imagesy($small),$width,$height);
         ob_start();imagepng($small);$raw=ob_get_clean();imagedestroy($small);
       }
       imagedestroy($source);
     }
   }
   $logo='data:image/png;base64,'.base64_encode($raw);
 }'''
assert old in s;s=s.replace(old,new);p.write_text(s,encoding='utf-8')
