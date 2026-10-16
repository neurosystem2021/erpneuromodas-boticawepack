from pathlib import Path
p=Path('integration/ali-characteristics.php');s=p.read_text(encoding='utf-8').replace("'~/proformas$~'", "'~(?:^|/)proformas$~'").replace("'~/proformas/id/\\d+$~'", "'~(?:^|/)proformas/id/\\d+$~'").replace("$next($q,$r)->withHeader('X-Product-Specs','items-v2')->withHeader('X-Product-Specs-Path',$path)","$next($q,$r)");p.write_text(s,encoding='utf-8')
