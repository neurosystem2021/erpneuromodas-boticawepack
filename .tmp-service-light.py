from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\imprimir\imprimirReciboServicio.php');s=p.read_text(encoding='utf-8');s=s.replace("require '../api/sunat/plugins/phpqrcode/qrlib.php';","if (session_status() === PHP_SESSION_ACTIVE) session_write_close();",1)
s=s.replace('    $proyecto = $proyecto[0];',"    if (!$proyecto) { http_response_code(404); echo 'No se encontró el recibo de este proyecto.'; exit; }\n    $proyecto = $proyecto[0];",1)
s=s.replace('<script src="../resources/js/jquery-3.2.1.min.js"></script>','')
s=s.replace("echo 'window.print();';","echo 'setTimeout(function(){window.print();},500);';")
s=s.replace('<div class="no-print service-share"><button','<div class="no-print service-share"><button type="button" onclick="window.print()" style="background:#23465a;margin-bottom:8px">Imprimir recibo</button> <button',1)
s=s.replace("        var response=await fetch(pdfUrl,{credentials:'same-origin'});","        var controller=new AbortController();var timeout=setTimeout(function(){controller.abort();},10000);\n        var response;try {response=await fetch(pdfUrl,{credentials:'same-origin',signal:controller.signal});}finally{clearTimeout(timeout);}",1)
out=Path('integration/service-load/imprimir/imprimirReciboServicio.php');out.parent.mkdir(parents=True,exist_ok=True);out.write_text(s,encoding='utf-8')
