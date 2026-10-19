from pathlib import Path
p=Path(__file__).resolve().parent.parent/'integration/proforma-edit/proforma-contract-core.php'
s=p.read_text(encoding='utf-8')
s=s.replace('.signatures-three{table-layout:fixed}', '.contract-three .parties{margin:10px 0}.contract-three .parties td{padding:8px}.contract-three h2{margin-top:10px}.contract-three .annual{padding:8px;margin:8px 0}.contract-three .annual p{margin:7px 0}.contract-three .services td{padding:2px 3px;line-height:1.35}.contract-close{page-break-inside:avoid}.signatures-three{table-layout:fixed;margin-top:12px}')
s=s.replace('.signature-space{height:50px}', '.signature-space{height:40px}')
s=s.replace('</style></head><body><table class="brand">', '</style></head><body\'.(isset($f[\'consultor\'])?\' class="contract-three"\':\'\').\'><table class="brand">')
old="$html.=$section('06 · Acuerdos particulares',$f['condicionesEspeciales']).$section('07 · Revisión y aceptación',"
new="$html.=$section('06 · Acuerdos particulares',$f['condicionesEspeciales']).'<div class=\"contract-close\">'.$section('07 · Revisión y aceptación',"
assert old in s
s=s.replace(old,new).replace("$html.='<div class=\"footer\">Contrato basado", "$html.='</div><div class=\"footer\">Contrato basado")
p.write_text(s,encoding='utf-8',newline='\n')
