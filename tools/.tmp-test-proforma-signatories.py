from pathlib import Path
root = Path(__file__).resolve().parent.parent
f = root / 'tools/test-proforma-contract-ui.cjs'
s = f.read_text(encoding='utf-8')
s = s.replace(".replace('export default','return')", ".replace(/import \\{DEFAULT_CONSULTANT,DEFAULT_MANAGER\\} from '[^']+';/,fs.readFileSync('src/utils/proformaSignatories.js','utf8').replace(/export /g,'' )).replace('export default','return')")
s = s.replace("assert.equal(await p.getByLabel('Adelanto (%) *').inputValue(),'50');", "assert.equal(await p.getByLabel('Adelanto (%) *').inputValue(),'50');assert.equal(await p.getByLabel('Consultor *',{exact:true}).inputValue(),'Ing. Jhon H. Medrano Martinez');assert.equal(await p.getByLabel('Gerente general *',{exact:true}).inputValue(),'Blanca Martinez Bravo');await p.getByLabel('Representante legal del cliente',{exact:true}).fill('Representante de prueba');assert.deepEqual(await p.locator('.contract-signatories strong').allTextContents(),['Ing. Jhon H. Medrano Martinez','Representante de prueba','Blanca Martinez Bravo']);")
s = s.replace("assert.equal(await p.evaluate(()=>window.contractWrites[0].adelanto),800.25);", "assert.equal(await p.evaluate(()=>window.contractWrites[0].adelanto),800.25);assert.equal(await p.evaluate(()=>window.contractWrites[0].consultor),'Ing. Jhon H. Medrano Martinez');assert.equal(await p.evaluate(()=>window.contractWrites[0].representanteProveedor),'Blanca Martinez Bravo');assert.equal(await p.evaluate(()=>window.contractWrites[0].representanteCliente),'Representante de prueba');")
f.write_text(s,encoding='utf-8',newline='\n')
f = root / 'tools/test-proforma-contract.php'
s = f.read_text(encoding='utf-8')
s = s.replace("$html=qcHtml($saved);", """$html=qcHtml($saved);
qcCheck($input['consultor']==='Ing. Jhon H. Medrano Martinez'&&$input['representanteProveedor']==='Blanca Martinez Bravo','Default consultant and general manager');
$signatures=qcSignaturesHtml($saved['form']);qcCheck(strpos($signatures,'Jhon')<strpos($signatures,'MARKET DE MUESTRA')&&strpos($signatures,'MARKET DE MUESTRA')<strpos($signatures,'Blanca'),'Consultant left, client centre, manager right');
$represented=$saved;$represented['form']['representanteCliente']='Representante legal de muestra';qcCheck(strpos(qcSignaturesHtml($represented['form']),'REPRESENTANTE LEGAL DEL CLIENTE')!==false,'Legal representative signs for client');
$legacy=$saved;unset($legacy['form']['consultor']);$legacy['form']['representanteProveedor']='Representante original';qcCheck(strpos(qcHtml($legacy),'signatures signatures-three')===false&&strpos(qcHtml($legacy),'Representante original')!==false,'Previous version retains original signatories');
$custom=qcInput(array_replace($form,['consultor'=>'Consultor especial','representanteProveedor'=>'Gerente especial']),$source);qcCheck($custom['consultor']==='Consultor especial'&&$custom['representanteProveedor']==='Gerente especial','Editable signatories saved');
foreach(['consultor','representanteProveedor'] as $name){try{qcInput(array_replace($form,[$name=>'']),$source);throw new LogicException('Missing signatory accepted');}catch(InvalidArgumentException $expected){}}
""")
f.write_text(s,encoding='utf-8',newline='\n')
print('Added signatory defaults, submission, legal representative, placement and immutable-version checks.')
