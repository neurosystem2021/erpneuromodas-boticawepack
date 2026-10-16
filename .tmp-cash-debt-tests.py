from pathlib import Path
p=Path('tools/test-cash-save.php');s=p.read_text(encoding='utf-8-sig');s=s.replace('<?php','<?php\nob_start();\n$sessionDir=__DIR__."/../.tmp-test-sessions";if(!is_dir($sessionDir))mkdir($sessionDir);ini_set("session.save_path",$sessionDir);session_start();',1)
a=s.index('$r=$save(new CashRequest($data)');s=s[:a]+'''$r=$save(new CashRequest($data),new CashResponse());if($r['status']!==422||(int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco')->fetchColumn()!==0)throw new Exception('Payment without debt accepted');
$data['AplicadoDocVenta']=array(array('Id'=>1,'IdLetraCaja'=>1,'Aplicado'=>1000));
$r=$save(new CashRequest($data),new CashResponse());if($r['status']!==200)throw new Exception('Applied supplier payment failed');$row=$db->query('SELECT * FROM Cb_CajaBanco')->fetch(PDO::FETCH_ASSOC);if((float)$row['Importe']!==1000.0||$row['Concepto']!==$data['Concepto']||$row['UsuarioReg']!=='superadmin')throw new Exception('Incorrect movement');
$invalid=$data;$invalid['Importe']=0;$r=$save(new CashRequest($invalid),new CashResponse());if($r['status']!==422)throw new Exception('Zero amount accepted');
$invalid=$data;$invalid['Importe']=2000;$r=$save(new CashRequest($invalid),new CashResponse());if($r['status']!==422)throw new Exception('Mismatched amount accepted');
$generic=$data;$generic['IdTipoCajaBanco']=3;$generic['AplicadoDocVenta']=array();$r=$save(new CashRequest($generic),new CashResponse());if($r['status']!==200)throw new Exception('General cash movement blocked');
$before=(int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco')->fetchColumn();$data['AplicadoDocVenta']=array(array('Id'=>2,'Aplicado'=>500),array('Id'=>2,'Aplicado'=>500));$r=$save(new CashRequest($data),new CashResponse());if($r['status']!==500||(int)$db->query('SELECT COUNT(*) FROM Cb_CajaBanco')->fetchColumn()!==$before)throw new Exception('Rollback failed');
$_SESSION=array();session_destroy();echo "PASS payments require debt; valid applied payments and general movements saved; session user, totals and rollback verified. Temporary tables only.\\n";ob_end_flush();
''';p.write_text(s,encoding='utf-8')
p=Path('tools/test-cash-save-button.cjs');s=p.read_text(encoding='ascii');s=s.replace("assert.equal(fn.call(ctx),false);ctx.Proveedor={};", "assert.equal(fn.call(ctx),true);ctx.lstAplicado=[{Id:1,Aplicado:1000}];assert.equal(fn.call(ctx),false);ctx.Proveedor={};")
s=s.replace("console.log('Save button: standalone supplier/client payment enabled; missing data blocked')", "console.log('Save button: no applied debt blocked; valid applied payment enabled')")
p.write_text(s,encoding='ascii')
