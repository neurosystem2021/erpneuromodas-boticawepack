from pathlib import Path
import re
s=Path('deploy-src/store-order-fix/index.php').read_text(encoding='utf-8').split('<template data-disabled=')[0]
for i,script in enumerate(re.findall(r'<script(?:\s[^>]*)?>([\s\S]*?)</script>',s)):
 if '<?' not in script:Path(f'.tmp-whatsapp-{i}.js').write_text(script,encoding='utf-8')
assert 'navigator.share(' not in s
assert "encodeURIComponent(msg)" in s and 'whatsappWindow.location.replace' in s
print('WhatsApp destination and PDF message checks passed')
