from pathlib import Path
p=Path('deploy-src/neurosystem-store/tienda/index.php')
s=p.read_text(encoding='utf-8')
s=s.replace("const productImages=p=>", "const placeholder=FALLBACK;\nconst productImages=p=>",1)
s=s.replace('onerror="this.src=', 'onerror="this.onerror=null;this.src=')
p.write_text(s,encoding='utf-8')
