from pathlib import Path
p=Path('integration/bank-report.php');s=p.read_text(encoding='utf-8').replace("$name.' ('.$username.')'","$username.' ('.$name.')'");p.write_text(s,encoding='utf-8')
s=Path('integration/ReporteBancos.php').read_text(encoding='utf-8');assert 'm.UsuarioReg' not in s;assert 'm.RegistradoPor' in s;assert 'SUMATORIA' in s;Path('.tmp-order-workbook/bank-ui-check.js').write_text(s.split('<script>')[-1].split('</script>')[0],encoding='utf-8')
