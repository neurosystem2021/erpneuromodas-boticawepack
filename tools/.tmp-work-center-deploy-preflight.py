from pathlib import Path
p=Path('tools/deploy-diario.php');s=p.read_text(encoding='utf-8-sig').replace("$db->exec(file_get_contents($root.'/integration/diario/upgrade-operaciones.sql'));", "$db->exec(file_get_contents($root.'/integration/diario/upgrade-operaciones.sql'));$db->exec(file_get_contents($root.'/integration/diario/upgrade-archivado.sql'));")
s=s.replace("foreach(['api/diario-operaciones.php'", "foreach(['api/diario-avances.php'=>$root.'/integration/diario/diario-avances.php','api/diario-operaciones.php'",1);p.write_text(s,encoding='utf-8')
p=Path('tools/deploy-support.php');s=p.read_text(encoding='utf-8-sig').replace("foreach(['support-api.php','support-service.php'] as $file)", "foreach(['support-activity.php','support-api.php','support-service.php'] as $file)",1);p.write_text(s,encoding='utf-8')
p=Path('tools/deploy-work-center.php');s=p.read_text(encoding='utf-8')
import re,hashlib
block=s[s.index('$expected=array('):s.index('foreach($expected')]
for name,expected in re.findall(r"'([^']+\.php)'=>'([^']+)'",block):
 actual=hashlib.sha256((Path('C:/xampp/htdocs/boticav1/api')/name).read_bytes()).hexdigest()
 if actual!=expected.lower():raise RuntimeError('Live baseline changed: '+name)
print('Installed backend baselines verified before deployment.')
