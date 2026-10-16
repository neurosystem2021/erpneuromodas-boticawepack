from pathlib import Path
import re
s=Path('integration/central-config/views/Seg_ConfigurarEtiquetas.php').read_text(encoding='utf-8')
print(re.findall(r'class="([^"\n]*(?:hero|head)[^"\n]*)"',s))
