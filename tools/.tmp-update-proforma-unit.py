from pathlib import Path
p=Path('tools/test-proforma-editor.cjs');s=p.read_text(encoding='utf8').replace("new Function('config',", "new Function('config','ProformaPresentacion',").replace("replace(/import config[^;]+;/,'')", "replace(/import[^;]+;/g,'')").replace("({API:'/api/index.php/'});", "({API:'/api/index.php/'},{});");p.write_text(s,encoding='utf8')
