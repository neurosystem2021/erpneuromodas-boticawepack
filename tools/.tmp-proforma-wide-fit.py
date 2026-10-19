from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('height:130px;object-fit:contain','height:auto;object-fit:contain').replace('.quote-art{height:30mm}', '.quote-art{height:auto}');p.write_text(s,encoding='utf8')
