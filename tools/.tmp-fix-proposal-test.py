from pathlib import Path
p=Path('tools/test-proposal-assistant.php');s=p.read_text(encoding='utf-8-sig').replace("strpos($p['instructions'],'No inventes')", "stripos($p['instructions'],'no inventes')");p.write_text(s,encoding='utf8')
