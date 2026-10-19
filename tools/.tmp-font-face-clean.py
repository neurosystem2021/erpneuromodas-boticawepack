import re
from pathlib import Path
p=Path('static/vendor/fontawesome/css/all.min.css');s=p.read_text()
def fix(m):
 b=m.group();f=re.search(r'url\(([^)]*\.woff2)\)',b).group(1);b=re.sub(r'src:[^;}]+;?', '',b);return b.replace('@font-face{','@font-face{src:url('+f+') format("woff2");')
p.write_text(re.sub(r'@font-face\{[^}]+\}',fix,s))
