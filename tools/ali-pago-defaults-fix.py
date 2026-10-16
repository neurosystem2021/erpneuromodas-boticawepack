from pathlib import Path
import re
r=Path(__file__).resolve().parents[1]
p=r/'src/views/Ventas.vue';s=p.read_text(encoding='utf-8');start=s.index('    async guardarVenta(evt,');end=s.index("      const printWindow = window.open",start);region=s[start:end]
region=re.sub(r'this\.\$toasted\.(?:show|error)\(\s*("[^"\n]*")\s*\);',lambda m:m.group(0)+'\n          if (aliOptions && aliOptions.prepare) throw new Error('+m[1]+');',region)
s=s[:start]+region+s[end:];pos=s.rfind('</style>');s=s[:pos]+'.ali-checkout-error{margin:10px 16px;padding:12px 16px;background:#fff2d9;border:1px solid #e4b866;border-radius:10px;color:#734600;font-size:14px}.ali-checkout-error button{margin-left:10px;border:0;border-radius:6px;background:#795c28;color:white;padding:5px 10px}\n'+s[pos:];p.write_text(s,encoding='utf-8')
p=r/'tools/test-ali-pago-db.php';s=p.read_text(encoding='utf-8').replace('DniRuc VARCHAR(11),Anulado INT DEFAULT 0','DniRuc VARCHAR(11),Anulado INT DEFAULT 0,Direccion VARCHAR(200) DEFAULT \'\',Direccion2 VARCHAR(200) DEFAULT \'\',Direccion3 VARCHAR(200) DEFAULT \'\',esComisionista INT DEFAULT 0')
# These DDL literals use single-quoted PHP strings; escape the empty SQL defaults.
s=s.replace("DEFAULT '',", "DEFAULT \\\'\\\',")
s=s.replace("INSERT INTO Ve_DocVentaCliente VALUES (1,'CLIENTE FIXTURE','00000000',0)","INSERT INTO Ve_DocVentaCliente (IdCliente,Cliente,DniRuc,Anulado) VALUES (1,'CLIENTE FIXTURE','00000000',0)")
p.write_text(s,encoding='utf-8')
print('Specific POS validation messages now reach ALI and the inline error. Fixture schema includes real address/commission fields.')
