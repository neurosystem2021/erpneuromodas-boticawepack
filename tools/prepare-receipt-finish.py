from pathlib import Path
import subprocess
root=Path('integration/receipt-finish');root.mkdir(parents=True,exist_ok=True)
for name in ['imprimir/boletaelectronica.php','imprimir/facturaelectronica.php','imprimir/formatear-productos.php']:
 p=root/name;p.parent.mkdir(parents=True,exist_ok=True);p.write_bytes(subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C','C:/xampp/htdocs/boticav1','show','imceden:'+name]))
p=root/'imprimir/index.php';s=Path('C:/xampp/htdocs/boticav1/imprimir/index.php').read_text(encoding='utf-8')
s=s.replace("$idDocVenta = $_GET['IdDocVenta'];", "$idDocVenta = (int) $_GET['IdDocVenta'];",1)
needle='$productos = mysqli_fetch_all($resultDet, MYSQLI_ASSOC);'
s=s.replace(needle,needle+'''
  if (!$docVenta) { http_response_code(404); exit('No se encontró el comprobante.'); }
  require_once __DIR__ . '/formatear-productos.php';
  $productos = fn_formatear_productos_impresion($productos);
  $sqlPagos = "SELECT mp.MetodoPago,pd.Importe,pd.NroTarjeta FROM Ve_DocVentaMetodoPagoDet pd INNER JOIN Ve_DocVentaMetodoPago mp ON mp.IdMetodoPago=pd.IdMetodoPago WHERE pd.IdDocVenta=".$idDocVenta." ORDER BY FIELD(pd.IdMetodoPago,1,4,2,3)";
  $resultPagos = getSQLResultSet($sqlPagos);
  $metodosPago = $resultPagos ? mysqli_fetch_all($resultPagos,MYSQLI_ASSOC) : array();
''',1)
a=s.index("  } elseif ($docVenta['CodSunat'] == 03) {");b=s.index("  } elseif ($docVenta['CodSunat'] == 07) {",a)
s=s[:a]+'''  } elseif ($docVenta['CodSunat'] == 03) {
    include_once('boletaelectronica.php');
  } elseif ($docVenta['CodSunat'] == 01) {
    include_once('facturaelectronica.php');
'''+s[b:]
a=s.index("    document.addEventListener('DOMContentLoaded', function() {");b=s.index('  </script>',a)
s=s[:a]+'''    window.nstPrintReceipt = async function() {
      const images = Array.from(document.querySelectorAll('.ticket img'));
      await Promise.all(images.map(image => image.complete ? Promise.resolve() : new Promise(resolve => { image.addEventListener('load',resolve,{once:true}); image.addEventListener('error',resolve,{once:true}); setTimeout(resolve,5000); })));
      if (document.fonts && document.fonts.ready) await document.fonts.ready;
      window.focus(); window.print();
    };
    <?php if (!isset($_GET['preview'])): ?>
    if(document.readyState === 'complete')window.nstPrintReceipt();else window.addEventListener('load',window.nstPrintReceipt,{once:true});
    <?php endif; ?>
'''+s[b:]
p.write_text(s,encoding='utf-8')
p=root/'imprimir/comprobanteelectronico.php';s=Path('C:/xampp/htdocs/boticav1/imprimir/comprobanteelectronico.php').read_text(encoding='utf-8')
s=s.replace("$producto['ProductoModelo'] . ' ' . $producto['ProductoMedicion']", "($producto['ProductoModelo'] ?? '') . ' ' . ($producto['ProductoMedicion'] ?? '')")
s=s.replace('object-fit:cover;object-position:center 49%;','object-fit:contain;object-position:center;')
s=s.replace('.product-name{font-size:8.3px;', '.product-name{font-size:9.2px;')
s=s.replace('@page{size:80mm auto;margin:0}', '@page{size:80mm 297mm;margin:4mm}')
s=s.replace('html,body{width:80mm!important;', 'html,body{width:72mm!important;max-width:100%!important;')
s=s.replace('.ticket{width:80mm;max-width:none;height:auto;min-height:0;margin:0;padding:3mm 4mm;', '.ticket{width:72mm;max-width:100%;height:auto;min-height:0;margin:0;padding:0;')
s=s.replace('break-inside:avoid;page-break-inside:avoid}.no-print', 'break-inside:auto;page-break-inside:auto}.items tr,.validation,.summary-total{break-inside:avoid}.no-print')
s=s.replace('<main class="ticket">','<div class="no-print receipt-actions"><button type="button" onclick="window.nstPrintReceipt ? window.nstPrintReceipt() : window.print()">Imprimir comprobante</button></div>\n<main class="ticket">',1)
s=s.replace('.ticket{width:80mm;', '.receipt-actions{text-align:center;margin-bottom:12px}.receipt-actions button{border:0;border-radius:10px;background:#164f67;color:white;padding:11px 18px;font-weight:bold;cursor:pointer}\n.ticket{width:80mm;',1)
p.write_text(s,encoding='utf-8')
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8')
needle='      await this.axios\n        .post(this.API + "ventas", this.venta)'
assert needle in s
s=s.replace(needle,'''      const printWindow = window.open('about:blank', '_blank');
      if (printWindow) { printWindow.opener = null; printWindow.document.write('<p>Guardando venta y preparando comprobante...</p>'); }
'''+needle,1)
a=s.index('          window.open(\n            "/imprimir/index.php?IdDocVenta="',s.index('const printWindow ='))
b=s.index('          setTimeout(() => {',a)
s=s[:a]+'''          const printUrl = '/imprimir/index.php?IdDocVenta=' + response.data.insertId + '&IdOfertas=' + IdOfertasString;
          if (printWindow && !printWindow.closed) printWindow.location.replace(printUrl);
          else this.$toasted.show('Venta guardada. Pulse Imprimir última Venta para abrir el comprobante.');
'''+s[b:]
a=s.index('        .catch(error => {',s.index('const printWindow ='))
s=s[:a]+s[a:].replace('        .catch(error => {','        .catch(error => {\n          if (printWindow && !printWindow.closed) printWindow.close();',1)
p.write_text(s,encoding='utf-8')
