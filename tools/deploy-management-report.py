from pathlib import Path
import datetime, shutil, re, subprocess
root=Path(__file__).resolve().parent.parent
live=Path('C:/xampp/htdocs/boticav1')
backup=live/('.management-report-backup-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'))
api=live/'api/index.php'
text=api.read_text(encoding='utf-8-sig')
marker='        if ($yapeMonto > 0) {'
if "require_once __DIR__ . '/yape-collection.php';" not in text:
    if text.count(marker)!=1: raise RuntimeError('Yape checkout boundary changed')
    start=text.index(marker);end=text.index('        if ($visaMonto > 0)',start)
    block=text[start:end]
    if block.count('VALUES (7, 3,')!=1: raise RuntimeError('Yape insertion changed')
    block=block.replace(marker,marker+"\n            require_once __DIR__ . '/yape-collection.php';\n            $idCobroYape = yapeCollectionType($this->db);")
    block=block.replace('VALUES (7, 3,','VALUES ($idCobroYape, 3,')
    text=text[:start]+block+text[end:]
header=live/'views/header.php';header_text=header.read_text(encoding='utf-8-sig')
if '/views/ReporteGerencial.php' not in header_text:
    marker='<li><a href="<?php echo ReporteUtilidadBrutaPorUsuario; ?>">'
    if marker not in header_text:raise RuntimeError('Report menu boundary missing')
    header_text=header_text.replace(marker,'<li><a href="/views/ReporteGerencial.php">CONSOLIDADO GERENCIAL / EXCEL</a></li>\n            '+marker,1)
backup.mkdir()
for file in (api,header):shutil.copy2(file,backup/file.name)
for source in (root/'integration/management-report').rglob('*.php'):
    relative=source.relative_to(root/'integration/management-report');target=live/relative
    if target.exists():
        saved=backup/relative;saved.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(target,saved)
    target.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(source,target)
subprocess.run(['C:/xampp/php/php.exe',str(root/'tools/install-management-yape.php')],check=True)
api.write_text(text,encoding='utf-8');header.write_text(header_text,encoding='utf-8')
print('Report and Yape checkout installed. Backup:',backup)
