from pathlib import Path
p=Path('tools/test-technical-expert-browser.mjs');s=p.read_text(encoding='utf-8-sig').replace("const item=['Observación dictada del técnico.'];", "const item=[{transcript:'Observación dictada del técnico.'}];");p.write_text(s,encoding='utf-8')
