import pathlib,shutil,json,secrets,datetime,subprocess
root=pathlib.Path.cwd();target=pathlib.Path('C:/xampp/htdocs/boticav1');stage=root/'integration/imceden/backend';backup=target/('.whatsapp-activation-backup-'+datetime.datetime.now().strftime('%Y%m%d-%H%M%S'));backup.mkdir()
for f in ['config/crm.local.php','whatsapp-gateway/server.cjs','whatsapp-gateway/start.ps1']:
 p=target/f
 if p.exists():b=backup/f;b.parent.mkdir(parents=True,exist_ok=True);shutil.copy2(p,b)
for f in ['whatsapp-gateway/server.cjs','whatsapp-gateway/start.ps1']:shutil.copy2(stage/f,target/f)
cfg=target/'whatsapp-gateway/config.local.json'
if not cfg.exists():cfg.write_text(json.dumps({'port':8788,'apiKey':secrets.token_hex(32)}),encoding='utf-8')
local=json.loads(cfg.read_text(encoding='utf-8-sig'))
(target/'whatsapp-gateway/.htaccess').write_text('Require all denied\nDeny from all\n',encoding='utf-8')
text="<?php\nreturn array('enabled'=>true,'demo_mode'=>false,'messaging_enabled'=>true,'messaging_provider'=>'wppconnect','messaging_base_url'=>'http://127.0.0.1:"+str(local['port'])+"','messaging_instance'=>'neurosoft-crm','messaging_api_key'=>'"+local['apiKey']+"','ai_enabled'=>false,'ai_auto_reply_enabled'=>false,'write_profiles'=>array(1));\n"
(target/'config/crm.local.php').write_text(text,encoding='utf-8')
ign=target/'.gitignore';text=ign.read_text(encoding='utf-8-sig')
for line in ['whatsapp-gateway/config.local.json','whatsapp-gateway/.sessions/']:
 if line not in text:text+='\n'+line
ign.write_text(text,encoding='utf-8')
subprocess.run(['node','--check',str(target/'whatsapp-gateway/server.cjs')],check=True)
subprocess.run(['C:/xampp/php/php.exe','-l',str(target/'config/crm.local.php')],check=True)
print('WhatsApp local configured; automatic replies remain disabled. Backup:',backup.name)
