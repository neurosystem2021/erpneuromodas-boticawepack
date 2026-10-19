from pathlib import Path
p=Path('tools/.tmp-proforma-tabs-phone-ui.cjs');s=p.read_text(encoding='utf-8');s=s.replace("p.getByRole('button',{name:'Editar proforma',exact:true})", '''p.locator('#modalProformas .tab-pane.active button[title="Editar proforma"]')''');p.write_text(s,encoding='utf-8')
