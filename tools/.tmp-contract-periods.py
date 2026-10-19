from pathlib import Path
p=Path('integration/proforma-edit/proforma-contract-core.php');s=p.read_text(encoding='utf-8')
needle=".$section('Vigencia de la licencia',$f['vigenciaLicencia']).$section('Plazo de implementación',$f['plazoImplementacion']);"
replacement=".'<table class=\"periods\"><tr><td><b>Vigencia de la licencia</b><br>'.nl2br(qpEscape($f['vigenciaLicencia'])).'</td><td><b>Plazo de implementación</b><br>'.nl2br(qpEscape($f['plazoImplementacion'])).'</td></tr></table>';"
assert needle in s;s=s.replace(needle,replacement,1)
s=s.replace('.payment{margin:10px', '.periods{margin:10px 0;page-break-inside:avoid}.periods td{width:50%;padding:8px;border:1px solid #eee0e5;font-size:8.5px}.periods b{color:#a3233c}.payment{margin:10px')
s=s.replace('Las partes revisarán el alcance, los pagos, la vigencia y las condiciones descritas antes de firmar. Cualquier modificación posterior del alcance o sus importes se acordará por escrito. La generación de este borrador no incorpora firmas ni confirma por sí sola la aceptación del cliente.', 'Las partes revisarán y suscribirán los términos de este documento. Los cambios de alcance o importes se acordarán por escrito. Este borrador está pendiente de aceptación y firma.')
p.write_text(s,encoding='utf-8')
