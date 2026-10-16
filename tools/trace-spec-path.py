from pathlib import Path
p=Path('integration/ali-characteristics.php');s=p.read_text(encoding='utf-8').replace("withHeader('X-Product-Specs','items-v2')", "withHeader('X-Product-Specs','items-v2')->withHeader('X-Product-Specs-Path',$path)");p.write_text(s,encoding='utf-8')
