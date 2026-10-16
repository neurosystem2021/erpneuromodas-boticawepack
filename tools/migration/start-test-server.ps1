$ErrorActionPreference='Stop'
$root='C:\xampp\htdocs\neuro-migration-test'
$private='C:\xampp\neuro-private\migration'
$socket=New-Object System.Net.Sockets.TcpClient
$occupied=$false
try{$socket.Connect('127.0.0.1',8099);$occupied=$true}catch{}finally{$socket.Dispose()}
if($occupied){throw 'Port 8099 already in use; inspect the existing service before starting'}
$process=Start-Process -FilePath 'C:\xampp\php\php.exe' -ArgumentList @('-d','memory_limit=512M','-S','127.0.0.1:8099','-t',$root) -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $private 'test-server-out.log') -RedirectStandardError (Join-Path $private 'test-server-error.log')
[IO.File]::WriteAllText((Join-Path $private 'test-server-pid.txt'),[string]$process.Id)
Write-Output 'Isolated test server: http://localhost:8099/'
