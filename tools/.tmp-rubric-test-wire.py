from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'tools/test-proforma-contract.php';s=f.read_text(encoding='utf-8');old='$html=qcHtml($saved);';new='''$html=qcHtml($saved);
$original=file_get_contents(__DIR__.'/../static/images/consultor-jhon-rubrica-original.png');
qcCheck($saved['consultantSignature']['sha256']===hash('sha256',$original)&&base64_decode($saved['consultantSignature']['base64'],true)===$original,'Original image bytes preserved in contract snapshot');
qcCheck(substr_count($html,'<img class="consultant-rubric-image"')===1,'Only consultant receives signature');
qcCheck(qpConsultantRubric('Otro consultor')===null,'Never sign another consultant with Jhon image');
$noSignature=$saved;$noSignature['consultantSignature']=null;qcCheck(strpos(qcHtml($noSignature),'<img class="consultant-rubric-image"')===false,'Signature opt out and previous snapshot supported');
$damaged=$saved['consultantSignature'];$damaged['sha256']=str_repeat('0',64);qcCheck(qpRubricImage($damaged)==='','Corrupt snapshot rejected');
$optOut=qcInput(array_replace($form,['incluirRubricaConsultor'=>false]),$source);qcCheck(!$optOut['incluirRubricaConsultor'],'Explicit no signature choice');
''';assert old in s;s=s.replace(old,new).replace('pdf/contrato-sistemas-modelo-mejorado.pdf','pdf/contrato-sistemas-con-rubrica.pdf');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/test-proforma-contract-ui.cjs';s=f.read_text(encoding='utf-8');old="assert.equal(await p.getByLabel('Adelanto (%) *').inputValue(),'50');";new=old+"assert.equal(await p.getByLabel('Incluir la rúbrica de Ing. Jhon H. Medrano Martinez en este contrato',{exact:true}).isChecked(),true);assert.equal(await p.locator('.contract-rubric-preview img').count(),1);";assert old in s;s=s.replace(old,new);old="assert.equal(await p.evaluate(()=>window.contractWrites[0].adelanto),800.25);";new=old+"assert.equal(await p.evaluate(()=>window.contractWrites[0].incluirRubricaConsultor),true);";s=s.replace(old,new);f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/test-proforma-context.php';s=f.read_text(encoding='utf-8').replace('output/proforma-18-contexto.pdf','output/pdf/proforma-18-con-rubrica.pdf');f.write_text(s,encoding='utf-8',newline='\n')
print('Signature fidelity, identity match, snapshot, opt-out and form controls covered.')
