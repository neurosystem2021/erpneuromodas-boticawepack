from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\imprimir\imprimirReciboServicio.php');s=p.read_text(encoding='utf-8');s=s.replace("$IdProducto = $_GET['IdProducto'];","$IdProducto = (int) $_GET['IdProducto'];",1)
s=s.replace('    <div id="printer"', '''    <div class="no-print service-share"><button type="button" id="shareServiceWhatsapp">Enviar por WhatsApp</button><p id="serviceShareInfo" role="status"></p></div>
    <div id="printer"''',1)
s=s.replace('<style>','''<style>
    .service-share{margin:18px 0;text-align:center}.service-share button{padding:12px 18px;border:0;border-radius:10px;background:#08a77e;color:white;font:700 14px Arial;cursor:pointer}.service-share p{font:12px Arial;line-height:1.5}
''',1)
script='''
<script>
(function(){
  var receipt = <?php echo json_encode(array('code'=>(int)$IdProducto,'client'=>$proyecto['Cliente'] ?? '', 'document'=>$proyecto['DniRuc'] ?? '', 'description'=>$proyecto['Descripcion'] ?? ''), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?>;
  var text=['RECIBO DE SERVICIO','Código: '+receipt.code,'Cliente: '+receipt.client,'DNI/RUC: '+receipt.document,'Descripción: '+receipt.description].join('\\n');
  document.getElementById('shareServiceWhatsapp').addEventListener('click',async function(){
    var pdfUrl='/api/index.php/imprimirpdfreciboservicio/'+receipt.code;
    if(navigator.share && navigator.canShare){
      try {
        var response=await fetch(pdfUrl,{credentials:'same-origin'});
        if(!response.ok) throw new Error('pdf');
        var blob=await response.blob();
        if(blob.type.indexOf('pdf')===-1) throw new Error('pdf');
        var file=new File([blob],'recibo-servicio-'+receipt.code+'.pdf',{type:'application/pdf'});
        if(navigator.canShare({files:[file]})) { await navigator.share({files:[file],title:'Recibo de servicio',text:text});return; }
      } catch(error){ if(error.name==='AbortError') return; }
    }
    window.location.href='https://wa.me/?text='+encodeURIComponent(text);
  });
})();
</script>
'''
s += script
out=Path('integration/service-whatsapp/imprimir');out.mkdir(parents=True,exist_ok=True);(out/'imprimirReciboServicio.php').write_text(s,encoding='utf-8')
