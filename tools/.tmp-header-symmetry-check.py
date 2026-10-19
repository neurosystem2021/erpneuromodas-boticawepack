from pathlib import Path
f=Path(__file__).resolve().parent/' .tmp-proforma-title-check.cjs'.strip()
s=f.read_text(encoding='utf-8')
old="await p.locator('.quote-brand').screenshot({path:'output/proforma-titulos-resaltados.png'});"
new="""const geometry=await p.locator('.quote-software-brand').evaluate(e=>{const label=e.querySelector('span').getBoundingClientRect(),title=e.querySelector('strong').getBoundingClientRect();return {labelCentre:label.x+label.width/2,titleCentre:title.x+title.width/2,labelWidth:label.width,titleWidth:title.width,gap:title.top-label.bottom};});assert(Math.abs(geometry.labelCentre-geometry.titleCentre)<1);assert(geometry.labelWidth<geometry.titleWidth*.85);assert(geometry.gap>=8&&geometry.gap<=12);await p.locator('.quote-brand').screenshot({path:'output/proforma-encabezado-simetrico.png'});"""
assert old in s
s=s.replace(old,new)
s=s.replace("await p.emulateMedia({media:'print'});", "await p.locator('.quote-brand').screenshot({path:'output/proforma-encabezado-simetrico-movil.png'});await p.emulateMedia({media:'print'});")
f.write_text(s,encoding='utf-8',newline='\n')
