from pathlib import Path
p=Path('deploy-src/store-order-fix/index.php');s=Path('C:/xampp/htdocs/boticav1/tienda/index.php').read_text(encoding='utf-8')
s=s.replace("const btn=$('checkoutBtn'),label=$('checkoutLabel');checkoutBusy=true;", "const salesPhone=String(company.whatsapp_ventas||company.CELULAR||company.TELEFONO||company.whatsapp||'').replace(/\\D/g,'');\n if(!salesPhone)return alert('Configure el WhatsApp de ventas en los datos de la empresa.');\n const whatsappWindow=window.open('about:blank','_blank');if(whatsappWindow)whatsappWindow.opener=null;\n const btn=$('checkoutBtn'),label=$('checkoutLabel');checkoutBusy=true;",1)
a=s.index("  const phone=String(company.CELULAR",s.index('async function checkout'))
b=s.index(" }catch(error)",a)
s=s[:a]+'''  const phone=salesPhone.startsWith('51')?salesPhone:'51'+salesPhone;
  label.textContent='Abriendo WhatsApp de ventas...';
  const whatsappUrl='https://wa.me/'+phone+'?text='+encodeURIComponent(msg);
  if(whatsappWindow&&!whatsappWindow.closed)whatsappWindow.location.replace(whatsappUrl);
  else window.location.assign(whatsappUrl);
'''+s[b:]
s=s.replace("}catch(error){alert(error.message||'No se pudo preparar la proforma.');}","}catch(error){if(whatsappWindow&&!whatsappWindow.closed)whatsappWindow.close();alert(error.message||'No se pudo preparar la proforma.');}",1)
p.write_text(s,encoding='utf-8')
import re
for i,script in enumerate(re.findall(r'<script(?:\\s[^>]*)?>([\\s\\S]*?)</script>',s.split('<template data-disabled=')[0])):
 if '<?' not in script:Path(f'.tmp-whatsapp-{i}.js').write_text(script,encoding='utf-8')
