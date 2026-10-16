from pathlib import Path
p=Path('tools/test-receipt-finish.php');s=p.read_text(encoding='ascii');s=s.replace("include __DIR__.'/../integration/receipt-finish/imprimir/comprobanteelectronico.php';", "$template=file_get_contents(__DIR__.'/../integration/receipt-finish/imprimir/comprobanteelectronico.php');$template=str_replace('__DIR__',var_export($_SERVER['DOCUMENT_ROOT'].'/imprimir',true),$template);eval('?>'.$template);")
p.write_text(s,encoding='ascii')
