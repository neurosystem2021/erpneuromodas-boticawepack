from pathlib import Path
p=Path('tools/test-central-security.php');s=p.read_text(encoding='ascii');s=s.replace('if(!$ok)$failed=true;', 'if(!$ok){$failed=true;echo substr(strip_tags($body),0,300)."\\n";}',1);p.write_text(s,encoding='ascii')
