from pathlib import Path
p=Path('tools/test-central-security.php');s=p.read_text(encoding='ascii').replace('CURLOPT_POSTFIELDS=>array()','CURLOPT_POSTFIELDS=>\'csrf=invalid\'');p.write_text(s,encoding='ascii')
