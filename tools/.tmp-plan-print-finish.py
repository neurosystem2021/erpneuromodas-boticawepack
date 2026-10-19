from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf-8').replace('@media print{.quote-annual-plan{border-radius:4px}', '@media print{.quote-annual-plan{border-radius:4px;break-inside:avoid;page-break-inside:avoid}');p.write_text(s,encoding='utf-8')
p=Path('integration/proforma-edit/proforma-pdf-render.php');s=p.read_text(encoding='utf-8').replace('.annual-plan{border:1px solid #e5c4cd;', '.annual-plan{page-break-inside:avoid;border:1px solid #e5c4cd;');p.write_text(s,encoding='utf-8')
