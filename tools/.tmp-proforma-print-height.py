from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('min-width:0!important;width:100%!important;background:#fff!important','min-width:0!important;width:100%!important;height:auto!important;min-height:0!important;background:#fff!important');p.write_text(s,encoding='utf8')
