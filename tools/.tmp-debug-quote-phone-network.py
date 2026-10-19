from pathlib import Path
p=Path('tools/.tmp-proforma-tabs-phone-ui.cjs');s=p.read_text(encoding='utf-8');s=s.replace("const errors=[];p.on('pageerror'", "const errors=[];p.on('response',r=>{if(r.status()>=500)console.log('HTTP500',r.url());});p.on('pageerror'");p.write_text(s,encoding='utf-8')
