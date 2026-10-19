from pathlib import Path
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('height:130px;object-fit:cover','height:130px;object-fit:contain;object-position:right center').replace('        console.log(response);','        this.missingImages = {};');p.write_text(s,encoding='utf8')
