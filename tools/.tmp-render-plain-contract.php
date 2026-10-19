<?php
require __DIR__.'/../integration/proforma-edit/proforma-contract-core.php';require 'C:/xampp/htdocs/boticav1/config/database.php';$db=appPdoConnection();
$s=$db->prepare('SELECT Datos FROM Ve_ProformaContrato WHERE IdContrato=?');$s->execute([2]);$raw=$s->fetchColumn();if(!$raw)throw new RuntimeException('Contract unavailable');$hash=hash('sha256',$raw);$count=$db->query('SELECT COUNT(*) FROM Ve_ProformaContrato')->fetchColumn();$data=json_decode($raw,true);
$original=hash_file('sha256',__DIR__.'/../static/images/consultor-jhon-rubrica-original.png');$clean=hash_file('sha256',__DIR__.'/../static/images/consultor-jhon-rubrica-limpia.png');
if(($data['consultantSignature']['sha256']??'')!==$original||qcDisplaySignature($data)['sha256']!==$clean)throw new RuntimeException('Known original image not cleaned for presentation');
$empty=$data;$empty['consultantSignature']=null;if(qcDisplaySignature($empty)!==null)throw new RuntimeException('Signature unexpectedly inserted');
$custom=$data;$custom['consultantSignature']['sha256']=str_repeat('1',64);if(qcDisplaySignature($custom)!==$custom['consultantSignature'])throw new RuntimeException('Unrelated image changed');
$html=qcHtml($data);if(strpos($html,'class="contract-plain"')===false)throw new RuntimeException('Plain contract style missing');
$lib='C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf';require $lib.'/lib/php-font-lib/src/FontLib/Autoloader.php';require $lib.'/lib/php-svg-lib/src/autoload.php';require $lib.'/src/Autoloader.php';Dompdf\Autoloader::register();$options=new Dompdf\Options();$options->set('isRemoteEnabled',false);$options->set('isPhpEnabled',false);$pdf=new Dompdf\Dompdf($options);$pdf->setPaper('A4');$pdf->loadHtml($html,'UTF-8');$pdf->render();qcPdfNumberPages($pdf);
$target=__DIR__.'/../output/pdf/contrato-200-2026-sin-fondos.pdf';if(file_put_contents($target,$pdf->output())===false)throw new RuntimeException('Cannot save preview');
$s->execute([2]);if(hash('sha256',$s->fetchColumn())!==$hash||$count!==$db->query('SELECT COUNT(*) FROM Ve_ProformaContrato')->fetchColumn())throw new RuntimeException('Stored agreement changed');
echo 'PASS actual contract 200-2026 version 2: plain presentation, cleaned supplied rubric, opt-out and unrelated image preserved. Original database snapshot and version count unchanged. Updated PDF saved.';
