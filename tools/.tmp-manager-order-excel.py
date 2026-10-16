from pathlib import Path
p=Path('integration/management-report/api/management-report-excel.php');s=p.read_text(encoding='utf-8')
a=s.index("    $summary->getColumnDimension('A')");b=s.index('    $columns=',a)
s=s[:a]+"    foreach (array('A'=>46,'B'=>21,'C'=>21,'D'=>27,'E'=>23) as $column=>$width) $summary->getColumnDimension($column)->setWidth($width);\n"+s[b:]
a=s.index('    $metrics=array(');b=s.index('    $header($sales,1',a)
block="""    $header($summary,5,array('Línea de negocio','Servicios','Mercadería','Sin clasificar','TOTAL EMPRESA'));
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
    $validation=$summary->getCell('B31')->getDataValidation();$validation->setType(\\PhpOffice\\PhpSpreadsheet\\Cell\\DataValidation::TYPE_WHOLE)->setOperator(\\PhpOffice\\PhpSpreadsheet\\Cell\\DataValidation::OPERATOR_BETWEEN)->setFormula1(1)->setFormula2(366)->setAllowBlank(false)->setShowErrorMessage(true)->setErrorTitle('Días de proyección')->setError('Ingresa de 1 a 366 días.');
    $text($summary,'D29','Base: promedio del período.');$text($summary,'D30','Incluye días sin actividad.');$text($summary,'D31','No es un saldo bancario.');
    foreach (array('B6:E8','B13:B16','B20:B25','E20:E22','B29:B32') as $range) {$summary->getStyle($range)->getNumberFormat()->setFormatCode($money);$summary->getStyle($range)->getAlignment()->setHorizontal('right');}
    $summary->getStyle('B9:E9')->getNumberFormat()->setFormatCode('0.0%;(0.0%);"-"');$summary->getStyle('B9:E9')->getFont()->setItalic(true);$summary->getStyle('B29')->getNumberFormat()->setFormatCode('0');$summary->getStyle('B31')->getNumberFormat()->setFormatCode('0');
    foreach (array('A8:E8','A16:B16','A25:B25','A32:B32') as $range) {$summary->getStyle($range)->getFont()->setBold(true);$summary->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8F1F5');}
    $text($summary,'A35','Utilidad neta estimada según costos, gastos y personal registrados. Ver criterios y cobertura.');
    $text($summary,'A36','Pagos de compras a proveedores se muestran como salidas sin duplicar el costo vendido.');
    $summary->getStyle('A35:A36')->getFont()->setItalic(true)->setSize(9);
"""
s=s[:a]+block+s[b:]
s=s.replace("$row=count($report['notas'])+6; $header", "$row=count($report['notas'])+6; $text($criteria,'A'.($row-1),'Regla general por tipo. Las excepciones por movimiento se muestran en Caja y bancos.'); $header")
p.write_text(s,encoding='utf-8')
p=Path('tools/test-management-excel.php');s=p.read_text(encoding='utf-8').replace("['B9'=>'venta','B16'=>'bruta','B19'=>'neta','B24'=>'cobrosDigitales','B26'=>'salidasExternas']", "['E6'=>'venta','E8'=>'bruta','B16'=>'neta','E20'=>'cobrosDigitales','B25'=>'salidasExternas']");p.write_text(s,encoding='utf-8')
p=Path('tmp/management-artifact/verify.mjs');s=p.read_text(encoding='utf-8').replace("Resumen!A14:B26","Resumen!A5:E16").replace('tableMaxCols:2','tableMaxCols:5');p.write_text(s,encoding='utf-8')
p=Path('tools/test-management-browser.cjs');s=p.read_text(encoding='utf-8').replace("'#mg-cards .mg-card').count(),8", "'#mg-cards .mg-card').count(),4").replace("page.locator('#mg-outflow').innerText()", "page.locator('#mg-control').innerText()").replace("assert.equal(await page.locator('#mg-daily tbody tr').count(),2)", "await page.locator('#mg-zero-days').check();assert.equal(await page.locator('#mg-daily tbody tr').count(),2)");p.write_text(s,encoding='utf-8')
