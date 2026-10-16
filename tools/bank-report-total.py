from pathlib import Path
p=Path('integration/bank-report.php');s=p.read_text(encoding='utf-8');needle=" return '<html><head>";assert needle in s
s=s.replace(needle," $personalTotal=array_sum(array_column($data['Cuentas'],'Personal'));\n $summary.='<tr><td><b>TOTAL BANCOS</b></td><td class=\"num\"><b>'.$money($totals[0]).'</b></td><td class=\"num\"><b>'.$money($totals[1]).'</b></td><td class=\"num\"><b>'.$money($totals[2]).'</b></td><td class=\"num\"><b>'.$money($personalTotal).'</b></td><td class=\"num\"><b>'.$money($totals[3]).'</b></td></tr>';\n"+needle)
p.write_text(s,encoding='utf-8')
p=Path('integration/ReporteBancos.php');s=p.read_text(encoding='utf-8');needle="html+='</tbody></table></div></section>';";s=s.replace(needle,"html+='<tr><td><b>TOTAL BANCOS</b></td>'+['Inicial','Ingresos','Salidas','Personal','Saldo'].map(k=>'<td class=\"bank-num\"><b>'+money(d.Cuentas.reduce((sum,g)=>sum+Number(g[k]),0))+'</b></td>').join('')+'</tr>';"+needle,1);p.write_text(s,encoding='utf-8')
