from pathlib import Path
p=Path('tools/test-central-modules.php');s=p.read_text(encoding='ascii');s=s.replace('<?php','<?php\nob_start();',1);s=s.replace("session_destroy();exit($failed?1:0);", "session_destroy();ob_end_flush();exit($failed?1:0);")
p.write_text(s,encoding='ascii')
