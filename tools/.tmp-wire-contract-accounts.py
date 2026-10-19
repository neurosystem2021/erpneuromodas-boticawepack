from pathlib import Path
root = Path(__file__).resolve().parent.parent
def update(relative, old, new):
    path=root/relative
    raw=path.read_bytes()
    old=old.encode('utf-8'); new=new.encode('utf-8')
    assert raw.count(old)==1, relative
    path.write_bytes(raw.replace(old,new))
update('integration/proforma-edit/proforma-contract-core.php',
       "'templateVersion'=>2,'consultantSignature'",
       "'templateVersion'=>2,'paymentAccountsImage'=>qcPaymentMode($input)!=='texto'?qcPaymentImage():null,'consultantSignature'")
update('src/components/ProformaContrato.vue',
       "compromisos:[],cuentasPago:'',cliente:",
       "compromisos:[],formatoCuentas:'imagen',cuentasPago:'',cliente:")
update('src/components/ProformaContrato.vue',
       'this.form=Object.assign({},this.form,data.contract.form);this.form.consultor=',
       "this.form=Object.assign({},this.form,data.contract.form);this.form.formatoCuentas=data.contract.form.formatoCuentas||(this.form.cuentasPago.trim()?'texto':'imagen');this.form.consultor=")
update('src/components/ProformaContrato.vue',
       '</style>',
       '.contract-bank-preview{padding:12px;border:1px solid #ead9e1;border-radius:6px;margin:0 0 14px;background:#fff}.contract-bank-preview span{display:block;font-size:9px;letter-spacing:.7px;color:#a5233c;font-weight:700}.contract-bank-preview img{display:block;width:100%;height:auto;margin:12px auto;max-width:760px}.contract-bank-preview small{font-size:11px;line-height:1.5;color:#8d6e7a}\n</style>')
update('tools/test-proforma-contract-ui.cjs',
       "await p.getByLabel('Cuentas y datos para el pago').fill('Cuenta de muestra revisada por el proveedor');",
       "assert.equal(await p.getByLabel('Presentación de las cuentas bancarias').inputValue(),'imagen');assert.equal(await p.locator('.contract-bank-preview img').count(),1);await p.getByLabel('Presentación de las cuentas bancarias').selectOption('texto');assert.equal(await p.locator('.contract-bank-preview img').count(),0);await p.getByLabel('Cuentas y datos para el pago *').fill('Cuenta de muestra revisada por el proveedor');await p.getByLabel('Presentación de las cuentas bancarias').selectOption('ambos');assert.equal(await p.locator('.contract-bank-preview img').count(),1);")
update('tools/test-proforma-contract-ui.cjs',
       "assert.equal(await p.evaluate(()=>window.contractWrites[0].cuentasPago),'Cuenta de muestra revisada por el proveedor');",
       "assert.equal(await p.evaluate(()=>window.contractWrites[0].cuentasPago),'Cuenta de muestra revisada por el proveedor');assert.equal(await p.evaluate(()=>window.contractWrites[0].formatoCuentas),'ambos');")
print('Wired image/text/both choices, immutable payment image snapshots, legacy form loading and UI checks.')
