from pathlib import Path
p=Path('integration/ali-proposal-assistant.php');p.write_text(p.read_text(encoding='utf-8-sig'),encoding='utf8')
