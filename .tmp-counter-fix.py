from pathlib import Path
p=Path('deploy-src/store-order-fix/index.php');s=p.read_text(encoding='utf-8');s=s.replace('</head>','<style>.floating-cart #floatingCount{color:#d51c30!important;background:#fff!important;font-size:14px!important;font-weight:900;min-width:30px;height:30px;display:grid;place-items:center}</style>\n</head>',1);p.write_text(s,encoding='utf-8')
# Compile active JavaScript to catch syntax errors.
import re
scripts=re.findall(r'<script(?:\s[^>]*)?>([\s\S]*?)</script>',s.split('<template data-disabled=')[0])
for i,script in enumerate(scripts):
 if '<?' not in script:Path(f'.tmp-store-script-{i}.js').write_text(script,encoding='utf-8')
