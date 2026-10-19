from pathlib import Path
root=Path(__file__).resolve().parent.parent
f=root/'integration/proforma-edit/proforma-contract-template.php'
s=f.read_text(encoding='utf-8')
s=s.replace('Los acuerdos particulares de la cláusula siguiente precisan las condiciones específicas de esta contratación.', 'Las condiciones particulares de esta contratación se recogen en el apartado de acuerdos particulares, cuando corresponda.')
s=s.replace("qpEscape(qpSystemTitle($q['AlcanceSistema']??'',$source['products']))", "qpEscape(preg_replace('/^PROPUESTA DE /u','',qpSystemTitle($q['AlcanceSistema']??'',$source['products'])))")
s=s.replace('.footer{position:fixed;', '.footer{padding-right:65px;position:fixed;')
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'integration/proforma-edit/proforma-contract.php'
s=f.read_text(encoding='utf-8')
assert '$pdf->render();$bytes=' in s
s=s.replace('$pdf->render();$bytes=',"$pdf->render();if(($data['templateVersion']??1)>=2)qcPdfNumberPages($pdf);$bytes=")
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/test-proforma-contract.php'
s=f.read_text(encoding='utf-8').replace('$pdf->render();file_put_contents', '$pdf->render();qcPdfNumberPages($pdf);file_put_contents')
f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/.tmp-proforma-signatories-installed.cjs'
s=f.read_text(encoding='utf-8')
s=s.replace("await p.getByLabel('Representante legal del cliente',{exact:true}).fill('Representante de prueba');", "await p.getByLabel('Representante legal del cliente',{exact:true}).fill('Representante de prueba');assert.equal(await p.getByLabel('Referencia del contrato *').inputValue(),'018-2026');await p.getByLabel('Documento del representante del cliente',{exact:true}).fill('12345678');await p.getByRole('button',{name:'Seleccionar recomendaciones del modelo'}).click();assert.equal(await p.locator('.contract-clause-options input:checked').count(),5);")
s=s.replace("assert.equal(sent.consultor,'Ing. Jhon H. Medrano Martinez');", "assert.equal(sent.numeroContrato,'018-2026');assert.equal(sent.compromisos.length,5);assert.equal(sent.documentoRepresentanteCliente,'12345678');assert.equal(sent.consultor,'Ing. Jhon H. Medrano Martinez');")
f.write_text(s,encoding='utf-8',newline='\n')
print('Page numbering, reference language and live UI verification updated.')
