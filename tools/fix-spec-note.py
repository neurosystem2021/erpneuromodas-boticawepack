from pathlib import Path
p=Path('integration/ali-characteristics.php');s=p.read_text(encoding='utf-8').replace("$p['Descripcion']=($manual", "$manual=trim($manual);$p['Descripcion']=($manual");p.write_text(s,encoding='utf-8')
