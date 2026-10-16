from pathlib import Path
p=Path('integration/bank-report.php');s=Path('C:/xampp/htdocs/boticav1/api/bank-report.php').read_text(encoding='utf-8')
s=s.replace(" $q=$db->prepare('SELECT IdCuenta", " $users=[];foreach($db->query('SELECT Usuario,NombreUsuario FROM Seg_Usuario')->fetchAll(PDO::FETCH_ASSOC) as $u)$users[strtolower(trim($u['Usuario']))]=trim((string)$u['NombreUsuario']);\n $q=$db->prepare('SELECT IdCuenta",1)
s=s.replace("'Movimientos'=>[]]","'Movimientos'=>[],'Usuarios'=>[]]")
s=s.replace("$m['Anulado']=$void?1:0;", """$username=trim((string)$m['UsuarioReg']);$name=$users[strtolower($username)]??'';
 $m['UsuarioNombre']=$name;$m['RegistradoPor']=$username!==''?($name!==''&&strcasecmp($name,$username)!==0?$name.' ('.$username.')':$username):'Sin usuario registrado';
 if(!in_array($m['RegistradoPor'],$g['Usuarios'],true))$g['Usuarios'][]=$m['RegistradoPor'];
 $m['Anulado']=$void?1:0;""")
s=s.replace("$e($m['UsuarioReg'])","$e($m['RegistradoPor'])")
s=s.replace('<th width="12%">Usuario</th>','<th width="12%">Registrado por</th>')
s=s.replace("$detail.='<section class=\"account\"><h2>'.$e($g['Cuenta']).'</h2><p>","$detail.='<section class=\"account\"><h2>'.$e($g['Cuenta']).'</h2><div class=\"balance\">Saldo registrado al '.$e($data['Hasta']).': <b>S/ '.$money($g['Saldo']).'</b></div><p>Registrado por: '.$e(implode(', ',$g['Usuarios']) ?: 'Sin movimientos en el rango').'</p><p>")
needle="'</tbody></table></section>';";assert needle in s
s=s.replace(needle,"'</tbody><tfoot><tr><td colspan=\"4\"><b>SUMATORIA '.$e($g['Cuenta']).'</b></td><td class=\"num\"><b>'.$money($g['Ingresos']).'</b></td><td class=\"num\"><b>'.$money($g['Salidas']).'</b></td><td class=\"num\"><b>'.$money($g['Saldo']).'</b></td></tr></tfoot></table></section>';")
s=s.replace('.account{page-break-before:always}', '.account{page-break-before:always}.balance{background:#e6f5f1;padding:13px;font-size:15px;color:#08776e}tfoot td{background:#eaf3f7;border-top:2px solid #153f54}')
s=s.replace('<th>Final S/</th>','<th>Saldo al cierre S/</th>')
p.write_text(s,encoding='utf-8')
p=Path('integration/ReporteBancos.php');s=Path('C:/xampp/htdocs/boticav1/views/ReporteBancos.php').read_text(encoding='utf-8')
s=s.replace('</style>', '.bank-balances{display:flex;gap:16px;flex-wrap:wrap;margin:18px 0}.bank-balance{flex:1;min-width:260px;padding:22px;border-radius:16px;background:white;border:1px solid #cfe2ec;border-top:5px solid #0b8c8d}.bank-balance strong{display:block;font-size:30px;margin:10px 0;color:#08776e}.bank-balance.negative strong{color:#b22941}.bank-balance small{display:block;color:#547184;margin:8px 0}.bank-report tfoot td{background:#e8f3f7;font-weight:bold;border-top:2px solid #153f54}.bank-user{font-weight:bold;color:#086d80}</style>')
needle=" let html='<section class=\"bank-panel\"><h3>Consolidado por cuenta</h3>";assert needle in s
s=s.replace(needle,""" let html='<div class="bank-balances">'+d.Cuentas.map(g=>'<article class="bank-balance '+(Number(g.Saldo)<0?'negative':'')+'"><h3>'+esc(g.Cuenta)+'</h3><small>Saldo registrado al '+esc(d.Hasta)+'</small><strong>S/ '+money(g.Saldo)+'</strong><div>Inicial '+money(g.Inicial)+' + ingresos '+money(g.Ingresos)+' − salidas '+money(g.Salidas)+'</div><small>Registrado por: '+esc(g.Usuarios.join(', ')||'Sin movimientos en el rango')+'</small>'+(Number(g.Saldo)<0?'<small>Saldo negativo según el historial registrado. Verifica el saldo inicial y los depósitos.</small>':'')+'</article>').join('')+'</div>';
 html+='<section class="bank-panel"><h3>Consolidado por cuenta</h3>""")
s=s.replace('<th>Final S/</th>','<th>Saldo al cierre S/</th>')
s=s.replace('<th>Usuario</th>','<th>Registrado por</th>')
s=s.replace("'<td>'+esc(m.UsuarioReg)+'</td>'", "'<td class=\"bank-user\">'+esc(m.RegistradoPor)+'</td>'")
# Existing concatenation includes the preceding cell boundary in the same literal.
s=s.replace("</td><td>'+esc(m.UsuarioReg)+'</td>","</td><td class=\"bank-user\">'+esc(m.RegistradoPor)+'</td>")
needle="html+='</tbody></table></div></section>';});";assert needle in s
s=s.replace(needle,"html+='</tbody><tfoot><tr><td colspan=\"5\">SUMATORIA '+esc(g.Cuenta)+'</td><td class=\"bank-num\">'+money(g.Ingresos)+'</td><td class=\"bank-num\">'+money(g.Salidas)+'</td><td class=\"bank-num\">'+money(g.Saldo)+'</td></tr></tfoot></table></div></section>';});")
p.write_text(s,encoding='utf-8')
