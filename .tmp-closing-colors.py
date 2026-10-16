from pathlib import Path
import re
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8')
for label,key in [('Efectivo','cash'),('Yape/Plin','yape'),('Tarjeta','card'),('Transferencia','transfer'),('Gran Total','grand'),('Ingresos','income'),('Salida','expense'),('Crédito','credit')]:
 s=re.sub(r'<tr>(\s*<td>'+re.escape(label)+r' S/\.</td>)',r'<tr class="closing-summary-'+key+r'">\1',s)
p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
@media screen{
 tr.closing-summary-cash{--closing-color:#05966d;--closing-bg:#e5f8ef}
 tr.closing-summary-yape{--closing-color:#7b43d2;--closing-bg:#f2eaff}
 tr.closing-summary-card{--closing-color:#205de0;--closing-bg:#eaf1ff}
 tr.closing-summary-transfer{--closing-color:#b67505;--closing-bg:#fff3dc}
 tr.closing-summary-grand{--closing-color:#fff;--closing-bg:#125976}
 tr.closing-summary-income{--closing-color:#08856d;--closing-bg:#edf9f5}
 tr.closing-summary-expense{--closing-color:#bc2445;--closing-bg:#fff0f3}
 tr.closing-summary-credit{--closing-color:#627083;--closing-bg:#f0f3f7}
 tr[class*="closing-summary-"]>td{background:var(--closing-bg)!important;color:var(--closing-color)!important;font-weight:700!important;border-color:#fff!important;padding:12px 10px!important}
 tr[class*="closing-summary-"]>td:first-child{border-left:4px solid var(--closing-color)!important}
 tr[class*="closing-summary-"]>td:last-child{text-align:right;font-variant-numeric:tabular-nums;font-weight:800!important;white-space:nowrap}
 tr.closing-summary-grand>td{font-size:17px!important}
}
''';p.write_text(s,encoding='utf-8')
