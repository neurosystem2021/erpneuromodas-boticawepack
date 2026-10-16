from pathlib import Path
p=Path('deploy-src/store-order-fix/index.php');s=Path('C:/xampp/htdocs/boticav1/tienda/index.php').read_text(encoding='utf-8')
s=s.replace(" if(!salesPhone)return alert('Configure el WhatsApp de ventas en los datos de la empresa.');\n",'')
s=s.replace("const whatsappUrl='https://wa.me/'+phone+'?text='+encodeURIComponent(msg);", "const whatsappUrl=phone?'https://wa.me/'+phone+'?text='+encodeURIComponent(msg):'https://api.whatsapp.com/send?text='+encodeURIComponent(msg);")
s=s.replace("const phone=salesPhone.startsWith('51')?salesPhone:'51'+salesPhone;", "const phone=salesPhone?(salesPhone.startsWith('51')?salesPhone:'51'+salesPhone):'';")
p.write_text(s,encoding='utf-8')
import re
for i,script in enumerate(re.findall(r'<script(?:\s[^>]*)?>([\s\S]*?)</script>',s.split('<template data-disabled=')[0])):
 if '<?' not in script:Path(f'.tmp-whatsapp-{i}.js').write_text(script,encoding='utf-8')
assert 'Configure el WhatsApp de ventas' not in s
