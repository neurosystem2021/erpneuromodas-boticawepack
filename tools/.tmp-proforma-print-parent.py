from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('height:auto!important;min-height:0!important;background:#fff!important','height:auto!important;min-height:0!important;page:proformaA4;background:#fff!important');p.write_text(s,encoding='utf8')
