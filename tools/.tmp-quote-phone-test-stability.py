from pathlib import Path
p=Path('tools/.tmp-proforma-tabs-phone-ui.cjs');s=p.read_text(encoding='utf-8')
s=s.replace("await p.goto('http://localhost/nuevo/index.html#/ventas');", "await p.goto('http://localhost/nuevo/index.html#/ventas');console.log('PHASE initial POS loaded');")
s=s.replace("await p.getByRole('button',{name:'Actualizar',exact:true}).first().click();", "console.log('PHASE quick phone');await p.getByRole('button',{name:'Actualizar',exact:true}).first().click();")
s=s.replace("await p.locator('#modalProformas .tab-pane.active button[title=\"Editar proforma\"]').click();", "await p.waitForFunction(()=>document.querySelectorAll('#modalProformas .tab-pane.active').length===1&&!document.querySelector('#modalProformas .tab-pane.active table[aria-busy=true]'));await p.waitForTimeout(250);console.log('PHASE edit quote');await p.locator('#modalProformas .tab-pane.active button[title=\"Editar proforma\"]').click();")
s=s.replace("await p.locator('#modalProformas').screenshot", "await p.waitForFunction(()=>document.querySelectorAll('#modalProformas .tab-pane.active').length===1&&!document.querySelector('#modalProformas .tab-pane.active table[aria-busy=true]'));await p.waitForTimeout(250);await p.locator('#modalProformas').screenshot")
s=s.replace("await p.evaluate(sample=>", "console.log('PHASE prepare quote');await p.evaluate(sample=>")
p.write_text(s,encoding='utf-8')
