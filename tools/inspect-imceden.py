import subprocess,pathlib,re,json
root=pathlib.Path.cwd(); repo=pathlib.Path('C:/xampp/htdocs/boticav1')
def git(*a):return subprocess.check_output(['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C',str(repo),*a])
files=git('ls-tree','-r','--name-only','imceden').decode().splitlines()
extra=[p for p in files if p.startswith('tienda/') or p in ['models/EmpresaConfig.php','resources/js/quagga.min.js','resources/images/sire.png']]
for p in extra:
 t=root/'integration/imceden/backend'/p;t.parent.mkdir(parents=True,exist_ok=True);t.write_bytes(git('show','imceden:'+p))
print('extra staged',len(extra))
old=(repo/'api/index.php').read_text(encoding='utf-8-sig');new=(root/'integration/imceden/backend/api/index.php').read_text(encoding='utf-8-sig')
routes=lambda s:set(re.findall(r"\$app->(?:get|post|put|delete|patch)\(['\"]([^'\"]+)",s))
print('NEW ROUTES',sorted(routes(new)-routes(old)))
va=(root/'integration/imceden/backend/views/VentasApp.php').read_text(encoding='utf-8-sig')
print('VA paths',sorted(set(re.findall(r"(?:api|fetch)\(\s*['\"`]([^'\"`]+)",va))))
print('EmpresaConfig deps', re.findall(r'(?:require|include)[^;]+;', (root/'integration/imceden/backend/models/EmpresaConfig.php').read_text(encoding='utf-8-sig'))[:12])
