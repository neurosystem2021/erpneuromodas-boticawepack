from pathlib import Path
p=Path('integration/ali-technical-expert.php');s=p.read_text(encoding='utf-8').replace('$image=getimagesizefromstring($bytes);','$image=@getimagesizefromstring($bytes);');p.write_text(s,encoding='utf-8')
