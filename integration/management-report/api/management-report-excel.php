<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Fill;

function managementWorkbook(array $report) {
    $book=new Spreadsheet(); $book->getProperties()->setTitle('Consolidado gerencial de la empresa');
    $book->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
    $summary=$book->getActiveSheet(); $summary->setTitle('Resumen');
    $daily=$book->createSheet(); $daily->setTitle('Diario');
    $sales=$book->createSheet(); $sales->setTitle('Ventas');
    $cash=$book->createSheet(); $cash->setTitle('Caja y bancos');
    $criteria=$book->createSheet(); $criteria->setTitle('Criterios');
    $money='#,##0.00;[Red](#,##0.00);"-"';
    $text=function($sheet,$cell,$value) { $sheet->setCellValueExplicit($cell,(string)$value,DataType::TYPE_STRING); };
    $header=function($sheet,$row,$labels) use ($text) {
        foreach ($labels as $i=>$label) $text($sheet,\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i+1).$row,$label);
        $last=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($labels));
        $sheet->getStyle('A'.$row.':'.$last.$row)->applyFromArray(array('font'=>array('bold'=>true,'color'=>array('rgb'=>'FFFFFF')),'fill'=>array('fillType'=>Fill::FILL_SOLID,'startColor'=>array('rgb'=>'163A52')),'alignment'=>array('horizontal'=>'center','vertical'=>'center','wrapText'=>true)));
        $sheet->getRowDimension($row)->setRowHeight(34);
    };
    foreach ($book->getAllSheets() as $sheet) { $sheet->setShowGridlines(false); $sheet->getDefaultColumnDimension()->setWidth(19); }
    $text($summary,'A2','CONSOLIDADO GERENCIAL'); $summary->getStyle('A2')->getFont()->setSize(16)->setBold(true);
    $text($summary,'A3',$report['filtros']['desde'].' al '.$report['filtros']['hasta'].' · Todos los usuarios y almacenes · Soles');
    foreach (array('A'=>46,'B'=>21,'C'=>21,'D'=>27,'E'=>23) as $column=>$width) $summary->getColumnDimension($column)->setWidth($width);
    $columns=array('servicios','mercaderia','sinTipo','venta','costoServicios','costoMercaderia','costoSinTipo','costo','brutaServicios','brutaMercaderia','bruta','gastos','personal','neta','proveedores','otros','pendiente','traslados','cobrosDigitales','salidasRegistradas','salidasExternas','ingresosCaja','acumulado');
    $labels=array('Fecha','Venta servicios','Venta mercadería','Venta sin clasificar','Venta total','Costo servicios','Costo mercadería','Costo sin clasificar','Costo total','U. bruta servicios','U. bruta mercadería','U. bruta total','Gastos operativos','Pagos de personal','U. neta operativa estimada','Pagos a proveedores','Otras salidas no operativas','Salidas por clasificar','Traslados internos','Cobros Yape / Plin','Salidas registradas','Salidas externas','Ingresos caja registrados','U. neta acumulada');
    $header($daily,1,$labels); $daily->freezePane('B2'); $daily->getColumnDimension('A')->setWidth(15);
    foreach ($report['diario'] as $i=>$day) {
        $row=$i+2; $daily->setCellValue('A'.$row,Date::PHPToExcel(new DateTime($day['fecha']))); $daily->getStyle('A'.$row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
        foreach ($columns as $j=>$key) {
            $col=\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j+2);
            if ($day[$key]===null) $text($daily,$col.$row,'n.a.'); else $daily->setCellValue($col.$row,$day[$key]);
        }
        $formulas=array('E'=>"=SUM(B$row:D$row)",'I'=>"=SUM(F$row:H$row)",'J'=>"=B$row-F$row",'K'=>"=C$row-G$row",'L'=>"=E$row-I$row",'O'=>"=L$row-M$row-N$row",'U'=>"=SUM(M$row:N$row,P$row:S$row)",'V'=>"=U$row-S$row",'X'=>$i ? '=X'.($row-1).'+O'.$row : '=O'.$row);
        foreach ($formulas as $col=>$formula) if ($daily->getCell($col.$row)->getValue()!=='n.a.') $daily->setCellValue($col.$row,$formula);
    }
    $end=count($report['diario'])+1; $daily->getStyle('B2:X'.$end)->getNumberFormat()->setFormatCode($money); $daily->setAutoFilter('A1:X'.$end);
    $header($summary,5,array('Línea de negocio','Servicios','Mercadería','Sin clasificar','TOTAL EMPRESA'));
    foreach (array(6=>'Venta neta',7=>'Costo vendido',8=>'UTILIDAD BRUTA',9=>'Margen bruto') as $row=>$label) $text($summary,'A'.$row,$label);
    $source=function($cell,$column,$key) use ($summary,$text,$report,$end) {
        if ($report['totales'][$key]===null) $text($summary,$cell,'n.a.');
        else $summary->setCellValue($cell,"=SUM('Diario'!{$column}2:{$column}$end)");
    };
    foreach (array('B'=>array('B','F','J','servicios','costoServicios','brutaServicios'),'C'=>array('C','G','K','mercaderia','costoMercaderia','brutaMercaderia'),'D'=>array('D','H',null,'sinTipo','costoSinTipo',null)) as $column=>$definition) {
        $source($column.'6',$definition[0],$definition[3]);$source($column.'7',$definition[1],$definition[4]);
        if ($definition[2]) $source($column.'8',$definition[2],$definition[5]);
        elseif ($report['totales']['costoSinTipo']===null) $text($summary,$column.'8','n.a.');
        else $summary->setCellValue($column.'8','=D6-D7');
    }
    $source('E6','E','venta');$source('E7','I','costo');$source('E8','L','bruta');
    foreach (array('B','C','D','E') as $column) {
        if ($summary->getCell($column.'8')->getValue()==='n.a.') $text($summary,$column.'9','n.a.');
        else $summary->setCellValue($column.'9','=IF('.$column.'6=0,"n.a.",'.$column.'8/'.$column.'6)');
    }
    $header($summary,12,array('Resultado operativo','Importe (S/)'));
    foreach (array(13=>'Utilidad bruta total',14=>'Gastos operativos',15=>'Pagos de personal',16=>'UTILIDAD NETA OPERATIVA ESTIMADA') as $row=>$label) $text($summary,'A'.$row,$label);
    $source('B13','L','bruta');$source('B14','M','gastos');$source('B15','N','personal');$source('B16','O','neta');
    $header($summary,19,array('Salidas reales de dinero','Importe (S/)'));
    foreach (array(20=>array('Pagos a proveedores','P','proveedores'),21=>array('Gastos operativos','M','gastos'),22=>array('Pagos de personal','N','personal'),23=>array('Otras salidas no operativas','Q','otros'),24=>array('Salidas por clasificar','R','pendiente'),25=>array('TOTAL SALIDAS EXTERNAS','V','salidasExternas')) as $row=>$definition) {
        $text($summary,'A'.$row,$definition[0]);$source('B'.$row,$definition[1],$definition[2]);
    }
    $text($summary,'D19','Control de caja y bancos');$text($summary,'E19','Importe (S/)');
    $summary->duplicateStyle($summary->getStyle('A19:B19'),'D19:E19');
    foreach (array(20=>array('Cobros Yape / Plin','T','cobrosDigitales'),21=>array('Traslados internos','S','traslados'),22=>array('Ingresos registrados','W','ingresosCaja')) as $row=>$definition) {$text($summary,'D'.$row,$definition[0]);$source('E'.$row,$definition[1],$definition[2]);}
    $text($summary,'D24','Yape no es gasto ni salida.');$text($summary,'D25','No duplica las ventas.');
    $header($summary,28,array('Proyección por promedio histórico','Valor'));
    $text($summary,'A29','Días calendario del período');$summary->setCellValue('B29',$report['filtros']['dias']);
    $text($summary,'A30','Utilidad neta promedio por día');$text($summary,'A31','Días a proyectar (editable)');$summary->setCellValue('B31',$report['filtros']['proyeccion']);$text($summary,'A32','UTILIDAD NETA PROYECTADA');
    if ($report['completo']) {$summary->setCellValue('B30','=B16/B29');$summary->setCellValue('B32','=B30*B31');}
    else {$text($summary,'B30','n.a.');$text($summary,'B32','n.a.');}
    $summary->getStyle('B31')->getFont()->getColor()->setRGB('0000FF');$summary->getStyle('B31')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF2CC');
    $validation=$summary->getCell('B31')->getDataValidation();$validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_WHOLE)->setOperator(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::OPERATOR_BETWEEN)->setFormula1(1)->setFormula2(366)->setAllowBlank(false)->setShowErrorMessage(true)->setErrorTitle('Días de proyección')->setError('Ingresa de 1 a 366 días.');
    $text($summary,'D29','Base: promedio del período.');$text($summary,'D30','Incluye días sin actividad.');$text($summary,'D31','No es un saldo bancario.');
    foreach (array('B6:E8','B13:B16','B20:B25','E20:E22','B29:B32') as $range) {$summary->getStyle($range)->getNumberFormat()->setFormatCode($money);$summary->getStyle($range)->getAlignment()->setHorizontal('right');}
    $summary->getStyle('B9:E9')->getNumberFormat()->setFormatCode('0.0%;(0.0%);"-"');$summary->getStyle('B9:E9')->getFont()->setItalic(true);$summary->getStyle('B9:E9')->getAlignment()->setHorizontal('right');$summary->getStyle('B29')->getNumberFormat()->setFormatCode('0');$summary->getStyle('B31')->getNumberFormat()->setFormatCode('0');
    foreach (array('A8:E8','A16:B16','A25:B25','A32:B32') as $range) {$summary->getStyle($range)->getFont()->setBold(true);$summary->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F1F5');}
    $text($summary,'A35','Utilidad neta estimada según costos, gastos y personal registrados. Ver criterios y cobertura.');
    $text($summary,'A36','Pagos de compras a proveedores se muestran como salidas sin duplicar el costo vendido.');
    $summary->getStyle('A35:A36')->getFont()->setItalic(true)->setSize(9);
    $header($sales,1,array('Fecha','Usuario','Documento','Tipo','Producto','Cantidad','Precio unitario','Descuento registrado','Venta neta','Costo','Utilidad bruta','Origen del costo'));
    foreach ($report['ventas'] as $i=>$sale) {
        $row=$i+2; $sales->setCellValue('A'.$row,Date::PHPToExcel(new DateTime($sale['fecha'])));
        foreach (array('B'=>'usuario','C'=>'documento','D'=>'tipo','E'=>'producto','L'=>'origenCosto') as $col=>$key) $text($sales,$col.$row,$sale[$key]);
        foreach (array('F'=>'cantidad','G'=>'precio','H'=>'descuento','I'=>'venta','J'=>'costo','K'=>'bruta') as $col=>$key) if ($sale[$key]===null) $text($sales,$col.$row,'n.a.'); else $sales->setCellValue($col.$row,(float)$sale[$key]);
    }
    $sales->getColumnDimension('E')->setWidth(48); $sales->getColumnDimension('L')->setWidth(29);
    $header($cash,1,array('ID','Fecha','Usuario','Cuenta','Operación registrada','Concepto','Proveedor','Clasificación gerencial','Importe (S/)'));
    foreach ($report['caja'] as $i=>$entry) {
        $row=$i+2; $cash->setCellValue('A'.$row,(int)$entry['id']); $cash->setCellValue('B'.$row,Date::PHPToExcel(new DateTime($entry['fecha'])));
        foreach (array('C'=>'usuario','D'=>'cuenta','E'=>'operacion','F'=>'concepto','G'=>'proveedor','H'=>'categoria') as $col=>$key) $text($cash,$col.$row,$entry[$key] ?? '');
        $cash->setCellValue('I'.$row,$entry['importe']);
    }
    $cash->getColumnDimension('F')->setWidth(60); $cash->getColumnDimension('E')->setWidth(32); $cash->getColumnDimension('H')->setWidth(27);
    foreach (array(array($sales,count($report['ventas'])+1,'L','A','F','K'),array($cash,count($report['caja'])+1,'I','B','I','I')) as $config) {
        list($sheet,$last,$lastCol,$dateCol,$firstMoney,$lastMoney)=$config; $sheet->freezePane('C2');
        if ($last>1) { $sheet->setAutoFilter('A1:'.$lastCol.$last); $sheet->getStyle($dateCol.'2:'.$dateCol.$last)->getNumberFormat()->setFormatCode('dd/mm/yyyy'); $sheet->getStyle($firstMoney.'2:'.$lastMoney.$last)->getNumberFormat()->setFormatCode($money); }
    }
    $text($criteria,'A2','CRITERIOS Y COBERTURA'); $criteria->getStyle('A2')->getFont()->setSize(14)->setBold(true); $criteria->getColumnDimension('A')->setWidth(130);
    foreach ($report['notas'] as $i=>$note) { $text($criteria,'A'.($i+4),$note); $criteria->getStyle('A'.($i+4))->getAlignment()->setWrapText(true); $criteria->getRowDimension($i+4)->setRowHeight(34); }
    $row=count($report['notas'])+6; $text($criteria,'A'.($row-1),'Regla general por tipo. Las excepciones por movimiento se muestran en Caja y bancos.'); $header($criteria,$row,array('Tipo de caja','Clasificación aplicada','Importe registrado'));
    $criteria->getColumnDimension('B')->setWidth(24); $criteria->getColumnDimension('C')->setWidth(22);
    foreach ($report['clasificacion'] as $type) { ++$row; $text($criteria,'A'.$row,$type['nombre']); $text($criteria,'B'.$row,$type['categoria']); $criteria->setCellValue('C'.$row,$type['importe']); }
    $book->setActiveSheetIndex(0); return $book;
}
