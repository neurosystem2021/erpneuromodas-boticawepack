<?php
function nstSupplierOrderData($db,$id) {
    $q=$db->prepare('SELECT o.*,p.Proveedor,p.Ruc AS RucProveedor,p.Direccion AS DireccionProveedor,a.Almacen FROM Lo_OrdenCompra o JOIN Lo_Proveedor p ON p.IdProveedor=o.IdProveedor JOIN Lo_Almacen a ON a.IdAlmacen=o.IdAlmacen WHERE o.IdOrdenCompra=?');$q->execute([$id]);$order=$q->fetch(PDO::FETCH_ASSOC);
    if(!$order)throw new RuntimeException('Orden no encontrada');
    $q=$db->prepare('SELECT p.IdProducto,p.Codigo,p.Producto,m.ProductoMarca,d.Cantidad FROM Lo_OrdenCompraDet d JOIN Gen_Producto p ON p.IdProducto=d.IdProducto LEFT JOIN Gen_ProductoMarca m ON m.IdProductoMarca=p.IdProductoMarca WHERE d.IdOrdenCompra=? ORDER BY p.IdProducto');$q->execute([$id]);$items=$q->fetchAll(PDO::FETCH_ASSOC);
    $company=$db->query('SELECT RAZONSOCIAL,RUC,DIRECCION FROM Gen_Empresa LIMIT 1')->fetch(PDO::FETCH_ASSOC);
    $logo=$db->query("SELECT MetaValue FROM Gen_EmpresaConfig WHERE MetaKey='logo_principal' LIMIT 1")->fetchColumn();
    $base=realpath(__DIR__.'/..');$path=realpath($base.'/'.ltrim($logo ?: 'resources/images/logo.png','/'));
    $company['LogoPrincipal']=($path && strpos($path,$base.DIRECTORY_SEPARATOR)===0) ? $path : $base.'/resources/images/logo.png';
    return ['empresa'=>$company,'ordencompra'=>$order,'detalle'=>$items,'txtTIPO_PROCESO'=>'03','txtNRO_DOCUMENTO_EMPRESA'=>$company['RUC']];
}
function nstSupplierOrderWorkbook($data) {
    $b=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$s=$b->getActiveSheet();$s->setTitle('Orden de compra');$o=$data['ordencompra'];$c=$data['empresa'];
    $b->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11)->getColor()->setRGB('294455');
    foreach(['A'=>14,'B'=>34,'C'=>34,'D'=>20,'E'=>16] as $col=>$width)$s->getColumnDimension($col)->setWidth($width);
    $put=function($range,$value)use($s){if(strpos($range,':')!==false)$s->mergeCells($range);$cell=explode(':',$range)[0];$s->setCellValueExplicit($cell,(string)$value,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);};
    if(is_file($c['LogoPrincipal'])){$d=new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();$d->setPath($c['LogoPrincipal']);$d->setHeight(65);$d->setCoordinates('A1');$d->setWorksheet($s);}
    $put('C1:E2','ORDEN DE COMPRA');$put('C3:E3','N.º '.$o['Anio'].'-'.str_pad($o['Numero'],5,'0',STR_PAD_LEFT));$put('C4:E4','Fecha: '.$o['FechaOrdenCompra']);
    $put('A6:E6',$c['RAZONSOCIAL']);$put('A7:E7','RUC: '.$c['RUC']);$put('A8:E9',$c['DIRECCION']);
    $put('A11:C11','PROVEEDOR');$put('D11:E11','DESTINO / ALMACÉN');$put('A12:C12',$o['Proveedor']);$put('D12:E12',$o['Almacen']);
    $put('A13:C13','RUC: '.$o['RucProveedor']);$put('D13:E14','Elaborado por: '.($o['UsuarioReg'] ?: 'Sin registro histórico'));$put('A14:C15',$o['DireccionProveedor']);$put('D15:E15','Recojo: '.(($o['FechaRecojo'] ?? '') ?: 'Por coordinar'));
    $put('A17:E17','Detalle de productos solicitados');$put('A18','Código');$put('B18:D18','Descripción');$put('E18','Cantidad');
    $r=19;foreach($data['detalle'] as $item){$put('A'.$r,$item['Codigo'] ?: $item['IdProducto']);$put('B'.$r.':D'.$r,$item['Producto']."\n".$item['ProductoMarca']);$s->setCellValue('E'.$r,(float)$item['Cantidad']);$s->getRowDimension($r)->setRowHeight(48);if($r%2===0)$s->getStyle('A'.$r.':E'.$r)->getFill()->setFillType('solid')->getStartColor()->setRGB('F2F7FA');$r++;}
    $put('A'.($r+1).':D'.($r+1),count($data['detalle']).' productos solicitados · Cantidad total');$s->setCellValue('E'.($r+1),'=SUM(E19:E'.($r-1).')');$s->getCell('E'.($r+1))->setFormulaAttributes(['t'=>'normal']);
    $put('A'.($r+3).':E'.($r+4),'Por favor, confirmar disponibilidad y coordinar la entrega indicando el número de esta orden.');
    $s->getStyle('A1:E'.($r+4))->getAlignment()->setWrapText(true)->setVertical('center');$s->getStyle('C1:E4')->getAlignment()->setHorizontal('right');$s->getStyle('C1')->getFont()->setSize(22)->setBold(true);
    foreach(['A11:E15','A'.($r+1).':E'.($r+1)] as $range)$s->getStyle($range)->getFill()->setFillType('solid')->getStartColor()->setRGB('EEF5F8');
    $s->getStyle('A18:E18')->getFill()->setFillType('solid')->getStartColor()->setRGB('163F54');$s->getStyle('A18:E18')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');$s->getRowDimension(18)->setRowHeight(30);
    $s->getPageSetup()->setPaperSize(9)->setOrientation('portrait')->setFitToWidth(1)->setFitToHeight(0)->setPrintArea('A1:E'.($r+4));$s->setShowGridlines(false);$s->freezePane('B19');
    return $b;
}


function nstSupplierOrderPdf($data) {
    // The corporate upload is a large bitmap; allow its first resize before embedding.
    ini_set('memory_limit','512M');
    $base=__DIR__.'/sunat/plugins/dompdf';
    ob_start();
    try {
        require_once $base.'/lib/html5lib/Parser.php';
        require_once $base.'/lib/php-font-lib/src/FontLib/Autoloader.php';
        require_once $base.'/lib/php-svg-lib/src/autoload.php';
        require_once $base.'/src/Autoloader.php';
        \Dompdf\Autoloader::register();
        require_once $base.'/purchaseOrderReport.php';
        $pdf=new \Dompdf\Dompdf();$pdf->set_paper('A4','portrait');
        $pdf->load_html(purchaseOrderReportHtml($data));$pdf->render();
        return $pdf->output();
    } finally {ob_end_clean();}
}
