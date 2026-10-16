from pathlib import Path
p=Path('integration/payroll/api/payroll-export.php');s=p.read_text(encoding='utf-8').replace("date('d/m/Y H:i')","(new DateTime('now',new DateTimeZone('America/Lima')))->format('d/m/Y H:i')");p.write_text(s,encoding='utf-8')
