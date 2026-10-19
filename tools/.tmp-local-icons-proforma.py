from pathlib import Path
import re
p=Path('static/vendor/fontawesome/css/all.min.css');s=p.read_text(encoding='utf8')
def fix(m):
 block=m.group(0); match=re.search(r'url\(([^)]*\.woff2)\)',block)
 if match:block=re.sub(r'src:[^;]+;', '',block).replace('@font-face{','@font-face{src:url('+match.group(1)+') format("woff2");')
 return block
s=re.sub(r'@font-face\{[^}]+\}',fix,s);p.write_text(s,encoding='utf8')
p=Path('index.html');s=p.read_text(encoding='utf8');s=re.sub(r'<link rel="stylesheet" href="https://use.fontawesome.com[^>]+>','<link rel="stylesheet" href="./static/vendor/fontawesome/css/all.min.css">',s);p.write_text(s,encoding='utf8')
p=Path('src/views/reportes/Proforma.vue');s=p.read_text(encoding='utf8').replace('height:auto;object-fit:contain;object-position:center','height:auto;max-height:190px;object-fit:contain;object-position:center').replace('.quote-art{height:auto}', '.quote-art{height:auto;max-height:42mm}');p.write_text(s,encoding='utf8')
