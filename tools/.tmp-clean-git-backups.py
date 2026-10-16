import subprocess,pathlib,json,hashlib
root=pathlib.Path('C:/xampp/htdocs/boticav1');git=['git','-c','safe.directory=C:/xampp/htdocs/boticav1','-C',str(root)]
if subprocess.check_output(git+['status','--porcelain']).strip():raise SystemExit('Working tree changed; stopping before cleanup')
files=json.loads(pathlib.Path('tmp/git-local-backups.json').read_text());hashes={p:((root/p).stat().st_size,(root/p).stat().st_mtime_ns) for p in files}
ignore=root/'.gitignore';text=ignore.read_text();text+='\n# Local deployment backups (keep on disk, exclude from Git)\n*.bak\n*.bak-*\n*.bak.*\n*backup-20*/\n*backup-20*\n';ignore.write_text(text)
for offset in range(0,len(files),100):subprocess.run(git+['rm','--cached','--quiet','--']+files[offset:offset+100],check=True)
for p,digest in hashes.items():
 if not (root/p).is_file() or ((root/p).stat().st_size,(root/p).stat().st_mtime_ns)!=digest:raise SystemExit('Backup verification failed')
subprocess.run(git+['add','--','.gitignore'],check=True)
subprocess.run(git+['commit','-m','Keep local deployment backups out of the repository'],check=True,stdout=subprocess.PIPE)
print('Committed backup cleanup; all '+str(len(files))+' local backups verified unchanged.')
