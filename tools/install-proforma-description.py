"""Preserve per-item descriptions across proforma save/read and PDF display."""
from pathlib import Path
import datetime,subprocess,shutil,hashlib,sys,re,difflib

root=Path(__file__).resolve().parent.parent
live=Path('C:/xampp/htdocs/boticav1')
def replace_once(text,old,new):
    if text.count(old)!=1:raise RuntimeError('Unexpected proforma code: '+old[:70])
    return text.replace(old,new,1)
def route(text,method,path):
    found=list(re.finditer(r"\$app->"+method+r"\('"+re.escape(path)+r"',.*?\n\}\);",text,re.S))
    if len(found)!=1:raise RuntimeError('Unexpected route: '+path)
    return found[0]

apiFile=live/'api/index.php';original=apiFile.read_text(encoding='utf-8-sig');api=original
m=route(api,'post','/proformas');block=m.group()
if "'Precio', 'Descuento', 'Descripcion'" not in block:
    block=replace_once(block,"        $descuento = $producto['descuento'];","        $descuento = $producto['descuento'];\n        $descripcion = isset($producto['Descripcion']) ? (string) $producto['Descripcion'] : '';")
    block=replace_once(block,"array('IdProforma', 'IdProducto', 'Cantidad', 'Precio', 'Descuento')","array('IdProforma', 'IdProducto', 'Cantidad', 'Precio', 'Descuento', 'Descripcion')")
    block=replace_once(block,"array($idProforma, $idProducto, $cantidad, $precio, $descuento)","array($idProforma, $idProducto, $cantidad, $precio, $descuento, $descripcion)")
api=api[:m.start()]+block+api[m.end():]
for path in ['/proformas/id/{id}','/proformas/productos/{id}']:
    m=route(api,'get',path);block=m.group()
    if 'AS Descripcion' not in block:
        block=replace_once(block,'Gen_Producto.*',"Gen_Producto.*, COALESCE(Ve_ProformaDet.Descripcion,'') AS Descripcion")
    api=api[:m.start()]+block+api[m.end():]
pdfFile=live/'api/sunat/plugins/dompdf/proforma.php';pdfOriginal=pdfFile.read_text(encoding='utf-8-sig');pdf=pdfOriginal
if "$det['Descripcion']" not in pdf:
    pdf=replace_once(pdf,"    $extras = '';","    $extras = '';\n    if (isset($det['Descripcion']) && trim((string) $det['Descripcion']) !== '') {\n        $extras .= '<div style=\"font-size:0.8rem;color:#333;margin-top:3px;white-space:pre-wrap;\">' . htmlspecialchars((string) $det['Descripcion'], ENT_QUOTES, 'UTF-8') . '</div>';\n    }")
stage=root/'tmp/proforma-description';stage.mkdir(parents=True,exist_ok=True)
files=[(apiFile,api,'api-index.php',original),(pdfFile,pdf,'proforma-pdf.php',pdfOriginal)]
patch=''
for target,content,name,before in files:
    candidate=stage/name;candidate.write_text(content,encoding='utf-8');subprocess.run(['C:/xampp/php/php.exe','-l',str(candidate)],check=True)
    relative=target.relative_to(live).as_posix()
    patch+=''.join(difflib.unified_diff(before.splitlines(True),content.splitlines(True),fromfile='a/'+relative,tofile='b/'+relative))
if patch:
    patchFile=root/'server/patches/imceden-proforma-description.patch';patchFile.parent.mkdir(parents=True,exist_ok=True);patchFile.write_text(patch,encoding='utf-8')
if '--prepare-only' in sys.argv:
    print('Prepared only the description save/read/PDF changes.');sys.exit(0)
for repo in [root,live]:
    branch=subprocess.check_output(['git','-c','safe.directory='+repo.as_posix(),'-C',str(repo),'branch','--show-current'],text=True).strip()
    if branch!='imceden':raise RuntimeError('Installation requires IMCEDEN in both repositories.')
backup=Path('C:/xampp/accounting-private')/('imceden-proforma-description-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir(parents=True)
for target,content,name,before in files:
    if target.read_text(encoding='utf-8-sig')!=before:raise RuntimeError('Concurrent backend edit; installation stopped.')
    shutil.copy2(target,backup/name)
for target,content,name,before in files:
    shutil.copyfile(stage/name,target)
    if hashlib.sha256(target.read_bytes()).digest()!=hashlib.sha256((stage/name).read_bytes()).digest():raise RuntimeError('Installed file mismatch.')
print('Proforma descriptions installed in boticav1. Backup: '+str(backup)+'. No sales/proformas created or changed.')
