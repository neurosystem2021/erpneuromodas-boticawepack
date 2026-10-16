import pathlib,shutil,json,secrets,re,subprocess
root=pathlib.Path.cwd();target=pathlib.Path('C:/xampp/htdocs/boticav1');stage=root/'integration/imceden/backend'
shutil.copy2(stage/'api/imceden-modules.php',target/'api/imceden-modules.php')
# Preserve previous exports when preparing a new SIRE file.
p=target/'api/index.php';text=p.read_text(encoding='utf-8-sig')
text=re.sub(r'\s*foreach \(glob\("\{\$basePath\}LE\{\$rucEmpresa\}\*\.\{txt,zip\}", GLOB_BRACE\) as \$archivo\) \{\s*@unlink\(\$archivo\);\s*\}', '', text)
text=text.replace("$declarado  = $request->getParam('declarado') ?: 0;", "$declarado  = filter_var($request->getParam('declarado'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0;")
p.write_text(text,encoding='utf-8')
subprocess.run(['C:/xampp/php/php.exe','-l',str(p)],check=True)
print('KPI Excel dependency corrected. Messaging remains disabled.')

h=target/'views/header.php';text=h.read_text(encoding='utf-8-sig');item='<li><a href="/views/CRM.php"><i class="fa fa-comments"></i> CRM</a></li>'
if item in text:
 text=text.replace(item,'',1)
 needle='<a href="<?php echo Logout; ?>">'
 at=text.index(needle);pos=text.rfind('<li class="dropdown">',0,at)
 text=text[:pos]+item+'\n        '+text[pos:]
 h.write_text(text,encoding='utf-8')
