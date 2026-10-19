from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('@page{size:A4;margin:2mm!important}','@page proformaA4{size:210mm 297mm;margin:2mm}.quote-page{page:proformaA4}');p.write_text(s,encoding='utf8')
