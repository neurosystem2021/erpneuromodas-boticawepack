<?php
include_once('../views/validateUser.php');
include_once($_SERVER["DOCUMENT_ROOT"] . "/clases/BnGeneral.php");
require '../api/sunat/plugins/phpqrcode/qrlib.php';
if (isset($_GET['IdProducto'])) {
    $IdProducto = (int) $_GET['IdProducto'];
    $fechaHoraActual = date('Y-m-d H:i:s');
    $result = fn_devolverProyecto($IdProducto);
    $proyecto = mysqli_fetch_all($result, MYSQLI_ASSOC);
    $proyecto = $proyecto[0];
    // print_r($proyecto['DniRuc']);
    // print_r($proyecto);
}
?>
<style>
    .service-share{margin:18px 0;text-align:center}.service-share button{padding:12px 18px;border:0;border-radius:10px;background:#08a77e;color:white;font:700 14px Arial;cursor:pointer}.service-share p{font:12px Arial;line-height:1.5}

    * {
        font-size: 3.5mm;
        font-family: monospace;
        font-weight: bold;
    }

    body {
        display: block;
        margin: 12px;
    }

    td.tabla1,
    th.tabla1,
    tr,
    .tabla1 {
        width: 72mm;
        border-top: black 1px dashed;
        border-collapse: collapse;
    }

    .container {
        width: 75mm;
        padding: 3mm 0.5mm 3mm .5m;
        box-sizing: border-box;
    }

    .center {
        text-align: center;
    }

    .separar {
        margin: 8px 0;
        border-top: 1px dashed #000;
    }

    td.tabla1 {
        line-height: 1.48em;
        font-size: 1em;
    }

    td,
    th {
        padding: 2px;
    }

    td.cantidad {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .small {
        font-size: 12px;
    }

    h3 {
        font-size: 4mm !important;
    }

    #printers {
        width: 20%;
        text-align: center;
    }

    #printers .print {
        font-size: 4mm;
    }

    @media print {
        .no-print {
            display: none;
        }
    }

    @media print {
        .no-print {
            display: none;
        }
    }
</style>
<div class="container">
    <div class="center"><img src="/resources/images/logo-ticket.png" style="max-width:100%; width: 55mm" /></div><br>
    <div class="center">
        <span style="font-size:22px">RECIBO DE SERVICIO</span>
    </div>
    <div class="center"><b>CONSULTORA CONSTRUCTORA NEUROSYSTEM PERU S.A.C.</b></div>
    <div class="center"><b>RUC: 20573027125</b></div><br>
    <div class="center">
        <b><?php print_r($fechaHoraActual) ?></b>
    </div>
    <div class="center" style="font-size:18px">Código: <?php print  $IdProducto ?></div>

    <table width="100%">
        <tr>
            <td>DNI/RUC:</td>
            <td><?php print_r($proyecto['DniRuc']) ?></td>
        </tr>
        <tr>
            <td>Cliente:</td>
            <td><?php print_r($proyecto['Cliente']) ?></td>
        </tr>
    </table>
    <p> <?php
        print_r($proyecto['Descripcion'])
        ?> </p>


    <div class="no-print service-share"><button type="button" id="shareServiceWhatsapp">Enviar por WhatsApp</button><p id="serviceShareInfo" role="status"></p></div>
    <div id="printer" style="text-align: center;" class="no-print">
        <a class="print no-print" href='/api/index.php/imprimirpdfreciboservicio/<?php echo $IdProducto; ?>'>¡IMPRIMIR PDF!</a>
    </div>
    <div id="printerred" style="text-align: center;" class="no-print">
        <!-- <a class="print" @click="openPrint" href='neuroprinter://factura.vip'> ¡IMPRIMIR POR RED!</a> -->
    </div>
</div>



<script src="../resources/js/jquery-3.2.1.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        //window.print()

        <?php
        if (!isset($_GET['preview'])) {
            if (isset($_GET['redirect'])) {
                echo 'window.print();';
                // echo 'window.location.href="' . $_GET['redirect'] . '";';
            } else {
                echo 'window.print();';
                // echo 'window.location.href="/views/V_VentaForm.php";';
            }
        }

        ?>
        //window.location.href="/views/V_VentaForm.php";
        // window.onafterprint = function() {
        //     window.close();
        //   };

    })
</script>

<!-- <script>
    $(document).ready(function() {

        fetch('/api/index.php/empresa/id/1')
            .then(function(response) {
                return response.json();
            })
            .then(function(empresa) {
                var ruta = 'neuroprinterred://factura.vip?iddocventa=<?php echo $idDocVenta ?>&token=' + empresa.APPTOKEN;

                $('#printerred .print').attr('href', ruta)
            });
    })
</script> -->
<script>
(function(){
  var receipt = <?php echo json_encode(array('code'=>(int)$IdProducto,'client'=>$proyecto['Cliente'] ?? '', 'document'=>$proyecto['DniRuc'] ?? '', 'description'=>$proyecto['Descripcion'] ?? ''), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_INVALID_UTF8_SUBSTITUTE); ?>;
  var text=['RECIBO DE SERVICIO','Código: '+receipt.code,'Cliente: '+receipt.client,'DNI/RUC: '+receipt.document,'Descripción: '+receipt.description].join('\n');
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
