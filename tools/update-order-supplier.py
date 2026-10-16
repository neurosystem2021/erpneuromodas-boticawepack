from pathlib import Path
root=Path('integration/order-supplier/api')
p=root/'sunat/plugins/dompdf/purchaseOrderReport.php'
s=p.read_text(encoding='utf-8')
s=s.replace("$amount=(float)$item['Cantidad']*(float)$item['Precio'];$total+=$amount;",'')
s=s.replace("<td class=\"num\">'.$money($item['Precio']).'</td><td class=\"num strong\">'.$money($amount).'</td>",'')
s=s.replace("$logoPath=realpath(__DIR__.'/../../../../resources/images/logo-ticket.png');","$logoPath=$company['LogoPrincipal'] ?? realpath(__DIR__.'/../../../../resources/images/logo.png');")
a=s.index('<th width="10%">'); b=s.index('</tr></thead>',a)
s=s[:a]+'<th width="12%">C&#243;digo</th><th width="73%">Descripci&#243;n</th><th width="15%" class="num">Cantidad</th>'+s[b:]
a=s.index('<table class="totals">');b=s.index('<div class="note">',a)
s=s[:a]+'''<table class="totals"><tr><td class="summary"><b>'.count($items).' productos solicitados</b><br>Cantidad total: '.$e($units).' unidades</td></tr></table>
 '''+s[b:]
p.write_text(s,encoding='utf-8')
p=root/'index.php';s=p.read_text(encoding='utf-8')
s=s.replace("$createdBy=$_SESSION['user'] ?? null;","$createdBy=is_string($_SESSION['user'] ?? null) ? trim($_SESSION['user']) : '';")
start=s.index("        $idOrdenCompra = (int)$request->getParam('id');",s.index("$app->get('/reporte/ordencomprapdf'"))
end=s.index('        $rutapdf =',start)
s=s[:start]+'''        require_once __DIR__.'/purchase-order-report.php';
        $data=nstSupplierOrderData($this->db,(int)$request->getParam('id'));
        $ordencompra=$data['ordencompra'];
'''+s[end:]
start=s.index("    $idOrdenCompra =",s.index("$app->get('/reporte/ordencompra/id'"));end=s.index('\n});',start)
s=s[:start]+'''    require_once __DIR__.'/purchase-order-report.php';
    $data=nstSupplierOrderData($this->db,(int)$request->getParam('idordencompra'));
    $book=nstSupplierOrderWorkbook($data);
    $stream=fopen('php://temp','r+');
    (new \\PhpOffice\\PhpSpreadsheet\\Writer\\Xlsx($book))->save($stream);
    rewind($stream);
    $response->getBody()->write(stream_get_contents($stream));fclose($stream);
    return $response->withHeader('Content-Type','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')->withHeader('Content-Disposition','attachment; filename="OrdenCompra_'.$data['ordencompra']['Anio'].'_'.$data['ordencompra']['Numero'].'.xlsx"');
'''+s[end:]
p.write_text(s,encoding='utf-8')
p=root/'order-create.php';s=p.read_text(encoding='utf-8').replace("$createdBy=$_SESSION['user'] ?? null;","$createdBy=is_string($_SESSION['user'] ?? null) ? trim($_SESSION['user']) : '';");p.write_text(s,encoding='utf-8')
