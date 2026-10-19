from pathlib import Path
root=Path(__file__).resolve().parent.parent
p=root/'src/views/reportes/Proforma.vue';raw=p.read_bytes()
old=b'v-if="proforma.Presentacion!==\'sistemas\' &amp;&amp; !isFeatured" class="quote-equipment"';assert raw.count(old)==1;raw=raw.replace(old,b'v-if="proforma.Presentacion!==\'sistemas\'" class="quote-equipment"')
old='<div v-if="isFeatured" class="quote-featured-brand"><span>SELECCIÓN COMERCIAL</span><strong>Tecnología para tu negocio</strong></div>'.encode('utf-8');assert raw.count(old)==1;raw=raw.replace(old,b'');p.write_bytes(raw)
p=root/'integration/proforma-edit/proforma-pdf-render.php';raw=p.read_bytes()
start=raw.index(b'<table class="brand"><tr><td width="35%">');end=raw.index(b'</td></tr></table>',start)+len(b'</td></tr></table>');brand=raw[start:end]
raw=raw[:start]+b"'.$brand.'"+raw[end:]
needle=b" $paymentFile=";assert raw.count(needle)==1
raw=raw.replace(needle,b" $brand='"+brand+b"';if($featured)$brand=qpFeaturedHeader($logoHtml,$company,$contact);\n"+needle)
p.write_bytes(raw)
p=root/'tools/.tmp-featured-installed.cjs';raw=p.read_bytes();old=b"assert.equal(await page.locator('.quote-equipment').count(),0);";assert raw.count(old)==1;raw=raw.replace(old,b"assert.equal(await page.locator('.quote-equipment').count(),1);assert.equal(await page.locator('.quote-equipment img').count(),7);await page.waitForFunction(()=>[...document.querySelectorAll('.quote-equipment img')].every(img=>img.complete&&img.naturalWidth>0));assert.equal(await page.locator('.quote-featured-brand').count(),0);");p.write_bytes(raw)
p=root/'tools/.tmp-featured-reference-pdf.php';raw=p.read_bytes().replace(b'execute([20])',b'execute([23])').replace(b'proforma-20-producto-destacado.pdf',b'proforma-23-destacado-con-encabezado.pdf').replace(b'actual quote 20',b'actual quote 23');(root/'tools/.tmp-featured-header-pdf.php').write_bytes(raw)
print('Featured report now shares the commercial equipment header; featured PDF header added without changing systems or catalog branches.')
