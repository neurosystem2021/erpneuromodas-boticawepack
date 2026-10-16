import subprocess,pathlib,re,json,hashlib
root=pathlib.Path('C:/xampp/htdocs/boticav1')
git=['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C',str(root)]
files=subprocess.check_output(git+['ls-files','-z']).decode().split('\0')
selected=[p for p in files if p and (re.search(r'\.bak(?:[-.]|$)',p,re.I) or re.search(r'backup-20\d{6}',p,re.I))]
print('Backups tracked:',len(selected))
print('Size MiB:',round(sum((root/p).stat().st_size for p in selected)/1048576,2))
pathlib.Path('tmp/git-local-backups.json').write_text(json.dumps(selected))
