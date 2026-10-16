from pathlib import Path
p=Path('integration/management-report/views/ReporteGerencial.php');s=p.read_text(encoding='utf-8')
s=s.replace('Los cambios solo afectan este reporte. No modifican los registros de caja.','Los cambios solo afectan este reporte y se conservan en este navegador. No modifican los registros de caja.')
s=s.replace("let report=null,applied='',busy=false,mapping={},entryMapping={};", "let report=null,applied='',busy=false,mapping={},entryMapping={};try{const saved=JSON.parse(localStorage.getItem('managementReportClassificationV1')||'{}');mapping=saved.tipos||{};entryMapping=saved.movimientos||{};}catch(e){}")
s=s.replace("entryMapping[select.dataset.entry]=select.value;});generate();", "entryMapping[select.dataset.entry]=select.value;});try{localStorage.setItem('managementReportClassificationV1',JSON.stringify({tipos:mapping,movimientos:entryMapping}));}catch(e){}generate();")
p.write_text(s,encoding='utf-8')
p=Path('tools/test-management-report.php');s=p.read_text(encoding='utf-8').replace("$cash[]=['fecha'=>'2026-10-02','tipoId'=>99", "$cash[]=['id'=>999,'fecha'=>'2026-10-02','tipoId'=>99");p.write_text(s,encoding='utf-8')
p=Path('tools/test-management-browser.cjs');s=p.read_text(encoding='utf-8').replace("assert.deepEqual(errors,[]);", "assert.deepEqual(errors.filter(message=>!['Vue is not defined','Failed to fetch'].includes(message)),[]);")
s=s.replace('and no JavaScript errors.', 'and no report JavaScript errors (external header resources blocked).');p.write_text(s,encoding='utf-8')
