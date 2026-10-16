from pathlib import Path
p=Path('integration/ali-index.php');s=p.read_text(encoding='utf-8-sig');marker="$registerReceiptDownloads=require __DIR__.'/receipt-downloads.php';$registerReceiptDownloads($app);";assert marker in s;s=s.replace(marker,marker+"\n$registerAli=require __DIR__.'/ali-api.php';$registerAli($app);",1);p.write_text(s,encoding='utf-8')
