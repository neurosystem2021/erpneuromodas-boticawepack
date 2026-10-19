from pathlib import Path
p=Path('tools/install-systems-proforma.php');s=p.read_text(encoding='utf8').replace('str_replace(" $db->beginTransaction();",',"str_replace(' $db->beginTransaction();',").replace("$editor=str_replace(' $db->commit();',", "if(strpos($editor,'quotePresentationSave')===false)$editor=str_replace(' $db->commit();',")
p.write_text(s.lstrip('\ufeff'),encoding='utf8')
