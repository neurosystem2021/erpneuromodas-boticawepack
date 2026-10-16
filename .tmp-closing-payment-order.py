from pathlib import Path
import re
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8')
starts=[m.start() for m in re.finditer('<h5>Ventas Tarjeta</h5>',s)]
for a in reversed(starts):
 b=s.index('<h5>Ventas Yape/Plin</h5>',a);c=s.index('<h5>Ventas Transferencia</h5>',b)
 s=s[:a]+s[b:c]+s[a:b]+s[c:]
pattern=r'(<tr>\s*<td>Tarjeta S/\.</td>[\s\S]*?</tr>)(\s*)(<tr>\s*<td>Yape/Plin S/\.</td>[\s\S]*?</tr>)'
s,count=re.subn(pattern,lambda m:m[3]+m[2]+m[1],s);assert count==2,count
p.write_text(s,encoding='utf-8')
