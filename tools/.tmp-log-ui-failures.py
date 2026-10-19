from pathlib import Path
p=Path('tools/.tmp-systems-name-ui.cjs');s=p.read_text(encoding='utf8').replace("const errors=[];p.on", "const errors=[];p.on('response',r=>{if(r.status()>=500)console.log('HTTP '+r.status()+' '+new URL(r.url()).pathname)});p.on");p.write_text(s,encoding='utf8')
