from pathlib import Path
root=Path(__file__).resolve().parent.parent
for name in ['src/views/reportes/Proforma.vue','src/components/ProformaContrato.vue','integration/proforma-edit/proforma-pdf-render.php']:
 f=root/name;s=f.read_text(encoding='utf-8').replace('consultor-jhon-rubrica-original.png','consultor-jhon-rubrica-limpia.png')
 if name.endswith('proforma-pdf-render.php'):
  s=s.replace('filesize($path)<=200000','filesize($path)<=1000000').replace("strlen($rubric['base64'])>270000","strlen($rubric['base64'])>1340000")
 f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/install-proforma-contract.php';s=f.read_text(encoding='utf-8')
old="$rubricSource=$root.'/static/images/consultor-jhon-rubrica-original.png';$rubricTarget=$live.'/nuevo/static/images/consultor-jhon-rubrica-original.png';\nif(is_file($rubricSource)){if(is_file($rubricTarget))copy($rubricTarget,$backup.'/consultor-jhon-rubrica-original.png');if(!copy($rubricSource,$rubricTarget)||hash_file('sha256',$rubricSource)!==hash_file('sha256',$rubricTarget))throw new RuntimeException('Rubric verification failed');}"
new="foreach(['consultor-jhon-rubrica-original.png','consultor-jhon-rubrica-limpia.png'] as $asset){$rubricSource=$root.'/static/images/'.$asset;$rubricTarget=$live.'/nuevo/static/images/'.$asset;if(is_file($rubricSource)){if(is_file($rubricTarget))copy($rubricTarget,$backup.'/'.$asset);if(!copy($rubricSource,$rubricTarget)||hash_file('sha256',$rubricSource)!==hash_file('sha256',$rubricTarget))throw new RuntimeException('Rubric verification failed');}}"
assert old in s;s=s.replace(old,new);f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/test-proforma-contract.php';s=f.read_text(encoding='utf-8').replace('static/images/consultor-jhon-rubrica-original.png','static/images/consultor-jhon-rubrica-limpia.png').replace('Original image bytes preserved in contract snapshot','Cleaned asset bytes preserved in contract snapshot').replace('pdf/contrato-sistemas-con-rubrica.pdf','pdf/contrato-sistemas-rubrica-limpia.pdf');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/test-proforma-context.php';s=f.read_text(encoding='utf-8').replace('output/pdf/proforma-18-con-rubrica.pdf','output/pdf/proforma-18-rubrica-limpia.pdf');f.write_text(s,encoding='utf-8',newline='\n')
f=root/'tools/.tmp-proforma-rubric-installed.cjs';s=f.read_text(encoding='utf-8').replace('),223)', '),1810)').replace('),107)', '),869)').replace('original 223x107 signature','clean transparent 1810x869 signature').replace('output/proforma-rubrica-instalada.png','output/proforma-rubrica-limpia-instalada.png');f.write_text(s,encoding='utf-8',newline='\n')
print('Clean transparent signature connected; original source asset and previous contract snapshots preserved.')
