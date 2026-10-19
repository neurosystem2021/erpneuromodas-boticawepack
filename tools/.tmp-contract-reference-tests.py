from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'tools/test-proforma-contract-ui.cjs'
s=f.read_text(encoding='utf-8')
old=".replace('export default','return')"
new=".replace(/import clauseCatalog from '[^']+';/,'const clauseCatalog='+fs.readFileSync('static/data/proforma-contract-clauses.json','utf8')+';').replace('export default','return')"
assert old in s
s=s.replace(old,new)
old="assert.equal(await p.getByLabel('Adelanto (%) *').inputValue(),'50');"
new=old+"assert.equal(await p.getByLabel('Referencia del contrato *').inputValue(),'017-'+new Date().getFullYear());assert.equal(await p.locator('.contract-clause-options input:checked').count(),0);await p.getByRole('button',{name:'Seleccionar recomendaciones del modelo'}).click();assert.equal(await p.locator('.contract-clause-options input:checked').count(),5);assert.equal(await p.getByLabel('Asistencia presencial coordinada',{exact:false}).isChecked(),false);await p.getByLabel('Documento del representante del cliente',{exact:true}).fill('12345678');await p.getByLabel('Cuentas y datos para el pago').fill('Cuenta de muestra revisada por el proveedor');"
assert old in s
s=s.replace(old,new)
old="assert.equal(await p.evaluate(()=>window.contractWrites[0].adelanto),800.25);"
s=s.replace(old,old+"assert.equal(await p.evaluate(()=>window.contractWrites[0].compromisos.length),5);assert.equal(await p.evaluate(()=>window.contractWrites[0].documentoRepresentanteCliente),'12345678');assert.equal(await p.evaluate(()=>window.contractWrites[0].documentoRepresentanteProveedor),'22641497');assert.equal(await p.evaluate(()=>window.contractWrites[0].cuentasPago),'Cuenta de muestra revisada por el proveedor');")
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/test-proforma-contract.php'
s=f.read_text(encoding='utf-8').replace("$legacy=$saved;unset($legacy['form']['consultor']);", "$legacy=$saved;unset($legacy['templateVersion'],$legacy['form']['consultor']);")
old="$html=qcHtml($saved);"
new="""$html=qcHtml($saved);
qcCheck($saved['templateVersion']===2&&$saved['form']['numeroContrato']==='099-2026','New template and derived reference saved');
foreach(['CONTRATO DE VENTA, LICENCIA E INSTALACIÓN','ANEXO A','ANEXO B','PRECIO, ADELANTO Y SALDO','TRES MIL CON 00/100 SOLES','MIL QUINIENTOS CON 00/100 SOLES'] as $phrase)qcCheck(strpos($html,$phrase)!==false,'Missing reference structure: '.$phrase);
foreach(['MELISSA','70746770','003- 2026','365-28875212','lector de código de barra es de'] as $phrase)qcCheck(strpos($html,$phrase)===false,'Historical customer or conditions copied');
qcCheck(strpos($html,'CAPACITACIÓN, CONFIGURACIÓN Y ACOMPAÑAMIENTO')===false,'Unselected commitments not promised');
qcCheck(qcMoneyWords(0)==='CERO CON 00/100 SOLES'&&qcMoneyWords(80025)==='OCHOCIENTOS CON 25/100 SOLES'&&qcMoneyWords(100000000)==='UN MILLÓN CON 00/100 SOLES','Money words accurate');
$chosen=qcInput(array_replace($form,['compromisos'=>['training','formats','peripherals'],'documentoRepresentanteProveedor'=>'22641497','documentoRepresentanteCliente'=>'12345678','soporteVigencia'=>'12 meses desde la activación.','cuentasPago'=>'Cuenta de muestra, datos confirmados para esta venta.','numeroContrato'=>'SIS-099-2026']),$source);
qcCheck(count($chosen['compromisosDetalle'])===3&&$chosen['compromisosDetalle'][0]['id']==='training','Trusted clause snapshot');
$example=$saved;$example['form']=$chosen;$example['form']['representanteCliente']='REPRESENTANTE LEGAL DE MUESTRA';$exampleHtml=qcHtml($example);qcCheck(strpos($exampleHtml,'Configuración de equipos periféricos')!==false&&strpos($exampleHtml,'Asistencia presencial coordinada')===false,'Only selected model clauses');
qcCheck(strpos($exampleHtml,'Cuenta de muestra')!==false&&strpos($exampleHtml,'Documento: 12345678')!==false,'Payment and signatory documents rendered');
foreach([['compromisos'=>['unknown']],['compromisos'=>'training'],['compromisos'=>[['id'=>'training']]],['documentoConsultor'=>'00000000'],['numeroContrato'=>'<script>'],['cuentasPago'=>str_repeat('x',3001)]] as $bad){try{qcInput(array_replace($form,$bad),$source);throw new LogicException('Invalid model fields accepted');}catch(InvalidArgumentException $expected){}}
$saved=$example;$html=$exampleHtml;
"""
assert old in s
s=s.replace(old,new)
s=s.replace("$saved['form']['fechaSaldo']==='2026-10-30'", "$saved['form']['fechaSaldo']==='2026-10-30'")
s=s.replace("contrato-sistemas-tres-firmas.pdf", "pdf/contrato-sistemas-modelo-mejorado.pdf")
s=s.replace("$lib='C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf';", "if(!is_dir(__DIR__.'/../output/pdf'))mkdir(__DIR__.'/../output/pdf',0755,true);$lib='C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf';")
f.write_text(s,encoding='utf-8',newline='\n')
print('Reference-model coverage added to PHP and Vue checks.')
