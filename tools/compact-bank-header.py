from pathlib import Path
p=Path('integration/ReporteBancos.php');s=Path('C:/xampp/htdocs/boticav1/views/ReporteBancos.php').read_text(encoding='utf-8')
old='.bank-hero{background:linear-gradient(110deg,#12384d,#0b656c);color:white;padding:26px;border-radius:20px;margin-bottom:20px}'
new='.bank-hero{background:linear-gradient(110deg,#12384d,#0b656c);color:white;padding:10px 22px;border-radius:16px;margin-bottom:16px}.bank-hero small{display:block;font-size:10px;line-height:12px;margin:0}.bank-hero h2{font-size:24px;line-height:29px;margin:3px 0!important}.bank-hero p{font-size:13px;line-height:17px;margin:0!important}'
assert old in s;s=s.replace(old,new);p.write_text(s,encoding='utf-8')
