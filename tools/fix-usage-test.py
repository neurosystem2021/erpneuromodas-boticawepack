from pathlib import Path
p=Path('tools/test-product-guide-browser.mjs');s=p.read_text(encoding='utf-8').replace("page.getByLabel('Tipo de consulta',{exact:true})", "page.locator('.product-technical-options select')");p.write_text(s,encoding='utf-8')
