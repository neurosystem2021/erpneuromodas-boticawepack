from pathlib import Path
import re
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8');a=s.index('<tr v-for="(prod, index)');b=s.index('</tr>',a);part=s[a:b];labels=iter(['Mano de obra','Código','Producto','Precio unitario','Cantidad','Total','Pagado','Saldo','Estado','Acciones']);part=re.sub(r'<td(\s[^>]*)?>',lambda m:'<td'+(m.group(1) or '')+' data-label="'+next(labels)+'">',part);s=s[:a]+part+s[b:];p.write_text(s,encoding='utf-8')
