from pathlib import Path
p=Path('src/utils/productGuide.js');s=p.read_text(encoding='utf-8').replace(".split(/\\r?\\n|(?<!\\d)[.;]\\s+(?=[A-ZÁÉÍÓÚÑ])/)",".replace(/([^0-9])[.;]\\s+(?=[A-ZÁÉÍÓÚÑ])/g,'$1\\n').split(/\\r?\\n/)");p.write_text(s,encoding='utf-8')
