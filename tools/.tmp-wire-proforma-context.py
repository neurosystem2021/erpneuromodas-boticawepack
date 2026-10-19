from pathlib import Path
p=Path('tools/install-proforma-files.php');s=p.read_text(encoding='utf-8');s=s.replace("'proforma-services.php','proforma-attachment.php'", "'proforma-services.php','proforma-scope.php','proforma-attachment.php'");p.write_text(s,encoding='utf-8')
