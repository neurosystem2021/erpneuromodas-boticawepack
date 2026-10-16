from pathlib import Path
p=Path('tools/test-cash-save-button.cjs');s=p.read_text(encoding='ascii').replace("readFileSync('src/views/CajaBancos.vue','utf8')", "readFileSync('src/views/CajaBancos.vue','utf8').replace(/\\r\\n/g,'\\n')");p.write_text(s,encoding='ascii')
