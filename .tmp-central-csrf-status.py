from pathlib import Path
for p in Path('integration/central-config/controllers').glob('*.php'):
 s=p.read_text(encoding='utf-8').replace('array(), 419','array(), 403').replace(', 419)',', 403)').replace(',419)',',403)');p.write_text(s,encoding='utf-8')
p=Path('tools/test-central-security.php');s=p.read_text(encoding='ascii').replace('$status===419','$status===403');p.write_text(s,encoding='ascii')
