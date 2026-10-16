import pathlib,shutil,subprocess
root=pathlib.Path.cwd();stage=root/'integration/imceden/backend';target=pathlib.Path('C:/xampp/htdocs/boticav1')
for f in ['resources/css/imceden-nst-theme.css','api/crm/src/CrmSyncService.php']:
 shutil.copy2(stage/f,target/f)
subprocess.run(['C:/xampp/php/php.exe',str(root/'tools/sync-imceden-customers.php')],check=True)
