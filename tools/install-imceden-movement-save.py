from pathlib import Path
import re,sys,subprocess,datetime,shutil,hashlib,difflib
root=Path(__file__).resolve().parent.parent;live=Path('C:/xampp/htdocs/boticav1');target=live/'api/index.php';original=target.read_text(encoding='utf-8-sig');candidate=original
for route,flag in [('/movimientos','false'),('/movimientos/traslado','true')]:
 pattern=r"\$app->post\('"+re.escape(route)+r"',.*?\n\}\);"
 replacement="$app->post('"+route+"', function (Request $request, Response $response) {\n    require_once __DIR__ . '/guardar-movimiento.php';\n    return imcSaveMovement($this->db, $request, $response, "+flag+");\n});"
 candidate,count=re.subn(pattern,lambda m:replacement,candidate,count=1,flags=re.S)
 if count!=1:raise RuntimeError('Unexpected route '+route)
stage=root/'tmp/imceden-movement-save';stage.mkdir(parents=True,exist_ok=True);(stage/'index.php').write_text(candidate,encoding='utf-8')
module=root/'server/inventario/guardar-movimiento.php'
for file in [stage/'index.php',module]:subprocess.run(['C:/xampp/php/php.exe','-l',str(file)],check=True)
patch=''.join(difflib.unified_diff(original.splitlines(True),candidate.splitlines(True),fromfile='a/api/index.php',tofile='b/api/index.php'))
patchFile=root/'server/patches/imceden-movement-save.patch';patchFile.parent.mkdir(parents=True,exist_ok=True);patchFile.write_text(patch,encoding='utf-8')
if '--prepare-only' in sys.argv:print('Movement save prepared. No installation or database changes.');sys.exit(0)
for repo in [root,live]:
 branch=subprocess.check_output(['git','-c','safe.directory='+repo.as_posix(),'-C',str(repo),'branch','--show-current'],text=True).strip()
 if branch!='imceden':raise RuntimeError('Both projects must remain on IMCEDEN.')
if target.read_text(encoding='utf-8-sig')!=original:raise RuntimeError('Concurrent API edit; installation stopped.')
backup=Path('C:/xampp/accounting-private')/('imceden-movement-save-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir(parents=True)
for source,destination in [(module,live/'api/guardar-movimiento.php'),(stage/'index.php',target)]:
 if destination.exists():shutil.copy2(destination,backup/destination.name)
 shutil.copyfile(source,destination)
 if hashlib.sha256(source.read_bytes()).digest()!=hashlib.sha256(destination.read_bytes()).digest():raise RuntimeError('Installed checksum mismatch.')
print('IMCEDEN movement save installed. Backup: '+str(backup)+'. No real purchase, transfer, stock change or schema migration executed.')
