from pathlib import Path
p=Path('integration/proforma-edit/proforma-pdf.php');s=p.read_text(encoding='utf8')
s=s.replace('.totals{width:230px;margin:12px 0 15px auto;page-break-inside:avoid}', '.summary{width:100%;margin:12px 0 15px;page-break-inside:avoid}.summary>tbody>tr>td{vertical-align:top}.totals{width:100%;margin:0;page-break-inside:avoid}')
s=s.replace('<table class="totals"><tr><td>Subtotal', '<table class="summary"><tr><td width="55%"></td><td width="45%"><table class="totals"><tr><td>Subtotal')
s=s.replace("</td></tr></table>'.$conditions.'<div class=\"footer\">", "</td></tr></table></td></tr></table>'.$conditions.$payment.'<div class=\"footer\">")
s=s.replace(" $html='<!doctype", " $paymentFile=dirname(__DIR__).'/resources/images/cuentas.png';$payment=is_file($paymentFile)?'<h2>Información de pago</h2><div style=\"margin:8px 0;page-break-inside:avoid\"><img src=\"data:image/png;base64,'.base64_encode(file_get_contents($paymentFile)).'\" style=\"width:100%;max-height:95px\"></div>':'';\n $html='<!doctype")
a=s.index('function qpEscape(');b=s.index('try{',a);func=s[a:b];s=s[:a]+s[b:]
a=s.index(' $logo=');b=s.index("\n require_once __DIR__.'/sunat/plugins",a);block=s[a:b];block=block.replace("$logo=dirname(__DIR__).'/nuevo/static/images/proforma-logo-color.png'", "$logo=$logoPath ?: dirname(__DIR__).'/nuevo/static/images/proforma-logo-color.png'")
helper="<?php\n"+func+"function quotePdfHtml(array $quote,array $products,array $company,?string $logoPath=null): string {\n $systems=($quote['Presentacion']??'productos')==='sistemas';\n"+block+"\n return $html;\n}\n"
Path('integration/proforma-edit/proforma-pdf-render.php').write_text(helper,encoding='utf8')
s=s[:a]+" require_once __DIR__.'/proforma-pdf-render.php';$html=quotePdfHtml($quote,$products,$company);"+s[b:];p.write_text(s,encoding='utf8')
p=Path('tools/install-proforma-files.php');s=p.read_text(encoding='utf8').replace("['proforma-attachment.php','proforma-pdf.php']", "['proforma-attachment.php','proforma-pdf.php','proforma-pdf-render.php']");p.write_text(s,encoding='utf8')
