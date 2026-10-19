from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'src/views/reportes/Proforma.vue';s=f.read_text(encoding='utf-8').replace('<b>Consultor</b>', '<b>Consultor de TI</b>').replace('<span>CONSULTOR</span>', '<span>CONSULTOR DE TI</span>');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'src/components/ProformaContrato.vue';s=f.read_text(encoding='utf-8').replace('>Consultor *<', '>Consultor de TI *<').replace('IZQUIERDA · CONSULTOR</span>', 'IZQUIERDA · CONSULTOR DE TI</span>');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'integration/proforma-edit/proforma-pdf-render.php';s=f.read_text(encoding='utf-8').replace('<br>Consultor: ', '<br>Consultor de TI: ').replace('<span>CONSULTOR</span>', '<span>CONSULTOR DE TI</span>');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'integration/proforma-edit/proforma-contract-template.php';s=f.read_text(encoding='utf-8').replace("$out=[];$defaults=", "$out=['cargoConsultor'=>'CONSULTOR DE TI'];$defaults=").replace('<br>Consultor: ', '<br>Consultor de TI: ');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'integration/proforma-edit/proforma-contract-core.php';s=f.read_text(encoding='utf-8').replace("[$form['consultor'],'CONSULTOR','Firma / rúbrica']", "[$form['consultor'],$form['cargoConsultor']??'CONSULTOR','Firma / rúbrica']");f.write_text(s,encoding='utf-8',newline='\n')
for name in ['tools/test-proforma-contract-ui.cjs','tools/.tmp-proforma-signatories-installed.cjs']:
 f=root/name;s=f.read_text(encoding='utf-8').replace("'Consultor *'", "'Consultor de TI *'").replace("Consultor Ing. Jhon", "Consultor de TI Ing. Jhon");f.write_text(s,encoding='utf-8',newline='\n')
print('Current proposals and new contract snapshots now identify CONSULTOR DE TI; old contract role snapshots remain unchanged.')
