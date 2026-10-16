from pathlib import Path
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8')+'''
/* Larger subtotal and total in the POS summary. */
.pos-page .pos-summary-panel>.table-footer>tbody>tr:has(>td>b)>td{font-size:20px!important;font-weight:800!important;padding-top:15px!important;padding-bottom:15px!important;vertical-align:middle!important}
.pos-page .pos-summary-panel>.table-footer>tbody>tr:has(>td>b)>td:last-child{font-size:27px!important;font-weight:800!important;font-variant-numeric:tabular-nums!important;white-space:nowrap!important}
@media(max-width:600px){.pos-page .pos-summary-panel>.table-footer>tbody>tr:has(>td>b)>td{font-size:18px!important}.pos-page .pos-summary-panel>.table-footer>tbody>tr:has(>td>b)>td:last-child{font-size:24px!important}}
''';p.write_text(s,encoding='utf-8')
