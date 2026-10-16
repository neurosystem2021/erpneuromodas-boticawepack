<?php
include_once('../views/validateUser.php');
include_once($_SERVER["DOCUMENT_ROOT"] . "/clases/BnGeneral.php");
require '../api/sunat/plugins/phpqrcode/qrlib.php';

if (isset($_GET['IdDocVenta'])) {
  $idDocVenta = (int) $_GET['IdDocVenta'];

  $criterio = "Ve_DocVenta.IdDocVenta=$idDocVenta";
  
  $result = fn_devolverDocVenta($criterio, "");
  $docVenta = mysqli_fetch_assoc($result);

  $result = fn_devolverMetPagoDetalle($criterio, "");
  $PagoDetalle = mysqli_fetch_all($result);
  
  $criterio = "Ve_DocVentaDet.IdDocVenta=$idDocVenta";
  $resultDet = fn_devolverDocVentaDet($criterio, "Ve_DocVentaDet.IdDocVentaDet ASC");
  $productos = mysqli_fetch_all($resultDet, MYSQLI_ASSOC);
  if (!$docVenta) { http_response_code(404); exit('No se encontró el comprobante.'); }
  require_once __DIR__ . '/formatear-productos.php';
  $productos = fn_formatear_productos_impresion($productos);
  $sqlPagos = "SELECT CASE pd.IdMetodoPago WHEN 1 THEN 'Efectivo' WHEN 4 THEN 'Yape / Plin' WHEN 2 THEN 'Tarjeta' WHEN 3 THEN 'Transferencia' ELSE mp.MetodoPago END MetodoPago,pd.Importe,pd.NroTarjeta FROM Ve_DocVentaMetodoPagoDet pd INNER JOIN Ve_DocVentaMetodoPago mp ON mp.IdMetodoPago=pd.IdMetodoPago WHERE pd.IdDocVenta=".$idDocVenta." ORDER BY FIELD(pd.IdMetodoPago,1,4,2,3)";
  $resultPagos = getSQLResultSet($sqlPagos);
  $metodosPago = $resultPagos ? mysqli_fetch_all($resultPagos,MYSQLI_ASSOC) : array();


  if ($docVenta['CodSunat'] == 12 || $docVenta['CodSunat'] == 99) {
    include_once('ticket.php');
  } elseif ($docVenta['CodSunat'] == 03) {
    include_once('boletaelectronica.php');
  } elseif ($docVenta['CodSunat'] == 01) {
    include_once('facturaelectronica.php');
  } elseif ($docVenta['CodSunat'] == 07) {
    include_once('notacredito.php');
  }
  $ofertas = [];
  if (isset($_GET['IdOfertas']) &&  $docVenta['CodSunat'] != 99) {
    $IdOferta = $_GET['IdOfertas'];
    $ofertasArray = explode(',', $IdOferta);
    foreach ($ofertasArray as $IdOferta) {
      $result = fn_devolverDatosOferta($IdOferta ? $IdOferta : 0);
      if ($result) {
        $ofertaData = mysqli_fetch_assoc($result);
        if ($ofertaData) {
          $ofertas[] = $ofertaData;
        }
      }
    }
  }
?>
  <script src="../resources/js/jquery-3.2.1.min.js"></script>
  <script>
    window.nstPrintReceipt = async function() {
      const images = Array.from(document.querySelectorAll('.ticket img'));
      await Promise.all(images.map(image => image.complete ? Promise.resolve() : new Promise(resolve => { image.addEventListener('load',resolve,{once:true}); image.addEventListener('error',resolve,{once:true}); setTimeout(resolve,5000); })));
      if (document.fonts && document.fonts.ready) await document.fonts.ready;
      window.focus(); window.print();
    };
    <?php if (!isset($_GET['preview'])): ?>
    if(document.readyState === 'complete')window.nstPrintReceipt();else window.addEventListener('load',window.nstPrintReceipt,{once:true});
    <?php endif; ?>
  </script>
<?php } ?>








<?php
if (isset($_GET['hashMovimiento'])) {
  $hash = $_GET['hashMovimiento'];
  $result = fn_devolverMovimiento($hash);
  $movimiento = mysqli_fetch_assoc($result);

  $resultDet = fn_devolverMovimientoDet($hash);
  $movimientoDet = mysqli_fetch_all($resultDet, MYSQLI_ASSOC);
  //var_dump($movimiento);
  //var_dump($movimientoDet);
  include_once('remisionticket.php');

?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      //window.print()

      <?php
      if (!isset($_GET['preview'])) {
        if (isset($_GET['redirect'])) {
          //echo 'window.print();';
          //echo 'window.location.href="' . $_GET['redirect'] . '";';
        } else {
          //echo 'window.print();';
          //echo 'window.location.href="/views/V_VentaForm.php";';
        }
      }

      ?>
      //window.location.href="/views/V_VentaForm.php";

    })
  </script>
<?php
}
?>





<?php
if (isset($_GET['especificacion'])) {

  include_once('especificacion.php');

?>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      //window.print()

      <?php
      if (!isset($_GET['preview'])) {
        if (isset($_GET['redirect'])) {
          //echo 'window.print();';
          //echo 'window.location.href="' . $_GET['redirect'] . '";';
        } else {
          //echo 'window.print();';
          //echo 'window.location.href="/views/V_VentaForm.php";';
        }
      }

      ?>
      //window.location.href="/views/V_VentaForm.php";

    })
  </script>
<?php
}
?>
<div id="printer">
  <a class="print no-print" href='/api/index.php/imprimirpdf/<?php echo $_GET['IdDocVenta']; ?>'>¡IMPRIMIR PDF!</a>
</div>
<div id="printerred" style="text-align: center;" class="no-print">
  <a class="print" @click="openPrint" href='neuroprinter://factura.vip'> ¡IMPRIMIR POR RED!</a>
</div>
<script src="../resources/js/jquery-3.2.1.min.js"></script>
<script>
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
</script>

<!-- OPEN PRINTER -->
<?php if (count($ofertas) > 0) { ?>
  <div class="oferta" style="width: 65mm; page-break-after: always;"></div>
  <div class="print">
    <?php
    $total = 0;
    foreach ($productos as $producto) {
      $total += $producto['TOTAL'];
    }

    $text = '';
    foreach ($ofertas as $oferta) {
      $docVentaNro = $docVenta['Serie'] . ' - ' . str_pad($docVenta['Numero'], 8, "0", STR_PAD_LEFT);
      $fechaDoc = $docVenta['FechaDoc'];
      // print_r($oferta);
      $text .= '
   <div class="oferta" style="width: 60mm; page-break-after: always; padding: 0 5%;">
    <center><h1 style="font-size:2rem !important">-- PROMOCIÓN --</h1></center>
      <p style="font-size:2rem !important; text-align: center">' . htmlspecialchars($oferta['Descripcion'], ENT_QUOTES, 'UTF-8') . '</p>
      <p style="font-size:1.5rem !important; text-align: justify">Oferta Válida Hasta ' . htmlspecialchars($oferta['FechaHasta'], ENT_QUOTES, 'UTF-8') . '</p>
      ';

      // if ($oferta['StockMax'] > 0) {
      // $text .= '<p style="font-size:1.2rem !important">Stock Máximo: ' . htmlspecialchars($oferta['StockMax'], ENT_QUOTES, 'UTF-8') . '</p>';
      // }
      $text .= '<p style="font-size:1.2rem !important; text-align: justify">Doc. Ref.: ' . $docVentaNro . '</p>';
      $text .= '<p style="font-size:1.2rem !important; text-align: justify">F. Doc. :' . $fechaDoc . '</p>';
      $text .= '<p style="font-size:1.2rem !important; text-align: justify">Monto Total: S/. ' . $total . '</p>';
      $text .= '<p style="font-size:1.2rem !important; text-align: justify">Código Oferta: ' . htmlspecialchars($oferta['Hash'], ENT_QUOTES, 'UTF-8') . '</p>';
      $text .= '</div>';
    }
    echo $text;
    ?>
  </div>
<?php } ?>

<div class="print no-print">
  <br>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <button style="cursor: pointer; margin-left:calc(4vw); background-color:#25d366; border: 1px #25d366 solid; border-radius: 5px; padding: 3px; color:#FFF; font-size:30px; display: flex;
    justify-content: center; align-items: center;" onclick="enviarNumero()"><i class="fa fa-whatsapp whatsapp-icon"></i><b style="margin-left: 5px;"> Enviar Al Whatsapp</b></button>
</div>
<script>
  async function enviarNumero() {
    let numero = prompt("Ingrese un número de 9 digitos");
    if (('' + numero).trim().length != 9) {

      return alert("Numero no valido")
    }
    try {
      // let url = null
      let res = await $.ajax({
        url: '../api/index.php/imprimirpdf_wsp/' + <?php echo $_GET['IdDocVenta']; ?>,
        type: "GET",
        success: function(res) {
          return res
        }
      });
      // console.log(res);
      let numeroWsp = `51${numero}`;
      let mensaje = encodeURIComponent(`Puede revisar su comprobante en el siguiente enlace: ${location.host}/api/${res.document}`);

      let urlWsp = /Android|iPhone/i.test(navigator.userAgent) ?
        `https://wa.me/${numeroWsp}?text=${mensaje}` :
        `https://web.whatsapp.com/send?phone=${numeroWsp}&text=${mensaje}`;

      window.open(urlWsp, "_blank");

    } catch (error) {
      alert("Ha ocurrido un error")
    }
  }
</script>
<?php if ($docVenta['CodSunat'] != 12): ?>
<?php endif; ?>
<style>
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



  #printer {
    width: 15%;
    text-align: center;
  }

  #printer .print {
    font-size: 4mm;
  }

  #printerred {
    width: 15%;
    text-align: center;
  }

  #printerred .print {
    font-size: 4mm;
  }

  @media print {
    .no-print {
      display: none;
    }
  }
</style>



<style>
  .strike {
    display: block;
    text-align: center;
    overflow: hidden;
    white-space: nowrap;
  }

  .strike>span {
    position: relative;
    display: inline-block;
  }

  .strike>span:before,
  .strike>span:after {
    content: "";
    position: absolute;
    top: 50%;
    width: 9999px;
    height: 1px;
    background: #000;
  }

  .strike>span:before {
    right: 100%;
    margin-right: 15px;
  }

  .strike>span:after {
    left: 100%;
    margin-left: 15px;
  }
</style>