from pathlib import Path
r=Path(__file__).resolve().parents[1]
p=r/'tools/deploy-ali-pago.ps1';s=(r/'tools/deploy-ali-phase2.ps1').read_text(encoding='utf-8').replace('.ali-phase2-backup-','.ali-pago-backup-').replace("@('ali-pos-security.php', 'ali-api.php')","@('ali-checkout.php', 'ali-pos-security.php', 'ali-api.php')");p.write_text(s,encoding='utf-8')
for name in ['tools/test-ali-phase2-security.php','tools/test-ali-phase2-live.php']:
 p=r/name;s=p.read_text(encoding='utf-8').replace("['pay',2,$admin,409]","['pay',2,$admin,200]").replace("['confirm',2,$admin,409]","['confirm',2,$admin,200]").replace("['pay',409]","['pay',200]").replace("['confirm',409]","['confirm',200]").replace('checkout blocked','checkout action permission').replace('automated checkout denied','checkout action permissions');p.write_text(s,encoding='utf-8')
print('Deployment script and permission regression expectations updated.')
