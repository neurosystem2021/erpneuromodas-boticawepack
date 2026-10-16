from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\views\header.php');s=p.read_text(encoding='utf-8');needle='            <li><a href="<?php echo ReporteStock; ?>"></a></li>'
assert s.count(needle)==1
s=s.replace(needle+'\n','',1);out=Path('integration/hide-empty-stock/views/header.php');out.parent.mkdir(parents=True,exist_ok=True);out.write_text(s,encoding='utf-8')
