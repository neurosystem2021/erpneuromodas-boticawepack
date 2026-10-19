from pathlib import Path
f=Path(__file__).resolve().parent/'.tmp-proforma-rubric-installed.cjs';s=f.read_text(encoding='utf-8')
old="await p.locator('.quote-footer').screenshot({path:'output/proforma-rubrica-limpia-instalada.png'});"
new="const centred=await p.locator('.quote-signature').evaluate(e=>{const i=e.querySelector('img').getBoundingClientRect(),n=e.querySelector('strong').getBoundingClientRect(),line=e.querySelector('.quote-rubric-space').getBoundingClientRect();return {difference:Math.abs(i.x+i.width*927/1810-(n.x+n.width/2)),lineDifference:Math.abs(line.x+line.width/2-(n.x+n.width/2))};});assert(centred.difference<1);assert(centred.lineDifference<1);await p.locator('.quote-footer').screenshot({path:'output/proforma-rubrica-centrada.png'});"
assert old in s;s=s.replace(old,new);f.write_text(s,encoding='utf-8',newline='\n')
