from pathlib import Path
p=Path('src/utils/cashAccountMovements.js');p.write_text('''export function isMainCashMovement(item) {
  if (item.IdCuenta !== undefined && item.IdCuenta !== null) return Number(item.IdCuenta) === 1;
  return Number(item.EsTarjeta) === 0;
}
export function partitionCashMovements(items) {
  const rows = Array.isArray(items) ? items : [];
  return {cash:rows.filter(isMainCashMovement),bank:rows.filter(item => !isMainCashMovement(item))};
}
export function sumMovementAmounts(items) {
  return (items || []).reduce((sum,item) => sum + (Number(item.Anulado) ? 0 : Number(item.Importe) || 0),0);
}
''',encoding='utf-8')
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8');s=s.replace('import config from','import { isMainCashMovement, partitionCashMovements, sumMovementAmounts } from "@/utils/cashAccountMovements";\nimport config from',1)
s=s.replace('this.ingresos = res.data.filter(ing => ing.EsTarjeta == 0);\n          this.ingresosTarjeta = res.data.filter(ing => ing.EsTarjeta == 1);','const partition = partitionCashMovements(res.data);\n          this.ingresos = partition.cash;\n          this.ingresosTarjeta = partition.bank;',1)
s=s.replace('this.salidas = res.data.filter(sal => sal.Tipo == 1);\n          this.salidasTarjeta = res.data.filter(sal => sal.Tipo == 0);','const partition = partitionCashMovements(res.data);\n          this.salidas = partition.cash;\n          this.salidasTarjeta = partition.bank;',1)
s=s.replace('this.adelantos = res.data;', 'this.adelantos = res.data.filter(isMainCashMovement);',1)
for getter,items in [('totalSalidas','salidas'),('totalIngresos','ingresos'),('totalIngresosTarjeta','ingresosTarjeta'),('totalSalidasTarjeta','salidasTarjeta'),('totalAdelantos','adelantos')]:
 a=s.index('    '+getter+'() {');b=s.index('\n    },',a)+len('\n    },')
 s=s[:a]+'    '+getter+'() {\n      return sumMovementAmounts(this.'+items+');\n    },'+s[b:]
s=s.replace('<h5>Ingresos</h5>','<h5>Ingresos Caja Principal</h5>').replace('<h5>Salidas</h5>','<h5>Salidas Caja Principal</h5>')
s=s.replace('<h5>Ingresos de Cuentas Bancarias </h5><br>', '<h5>Ingresos bancarios de referencia</h5><p class="closing-bank-note">Informativo: estos movimientos no suman al saldo de Caja Principal.</p>')
s=s.replace('<h5>Salidas de Cuentas Bancarias </h5><br>', '<h5>Salidas bancarias de referencia</h5><p class="closing-bank-note">Informativo: estos movimientos no restan al saldo de Caja Principal.</p>')
s=s.replace('Total Ingresos Tarjetas','Total ingresos bancarios (referencial)').replace('Total Salidas Tarjetas','Total salidas bancarias (referencial)')
# Correct labels in bank outflow tables only.
search=0
while True:
 a=s.find('<h5>Salidas bancarias de referencia</h5>',search)
 if a<0:break
 b=s.index('</table>',a);chunk=s[a:b].replace('<th>Ingreso</th>','<th>Salida</th>');s=s[:a]+chunk+s[b:];search=a+len(chunk)
s=s.replace('<td>Ingresos S/.</td>','<td>Ingresos caja S/.</td>').replace('<td>Salida S/.</td>','<td>Salidas caja S/.</td>')
s=s.replace('cashClosingTicket({productDetail:productDetail === true,', "cashClosingTicket({bankMovements:this.ingresosTarjeta.map(item => Object.assign({},item,{direction:'Ingreso'})).concat(this.salidasTarjeta.map(item => Object.assign({},item,{direction:'Salida'}))),productDetail:productDetail === true,",1)
p.write_text(s,encoding='utf-8')
p=Path('src/utils/cashClosingTicket.js');s=p.read_text(encoding='utf-8');needle='  return `<!doctype html>'
s=s.replace(needle,"  const banks=(data.bankMovements || []).filter(item => !Number(item.Anulado));\n  const bankReference=banks.length ? section('MOVIMIENTOS BANCARIOS (REFERENCIA)')+'<p class=\"meta center\">Informativo: no suman ni restan a Caja Principal.</p>'+banks.map(item => row((item.Cuenta || 'Cuenta bancaria')+' · '+item.direction,money(item.Importe))+'<div class=\"meta\">'+escape(item.Concepto || '')+'</div>').join('') : '';\n"+needle,1)
s=s.replace('<div class="row grand"><span>TOTAL FINAL DE CAJA</span>', '${bankReference}<div class="row grand"><span>TOTAL FINAL DE CAJA</span>',1)
p.write_text(s,encoding='utf-8')
root=Path('integration/bank-closing/api');root.mkdir(parents=True,exist_ok=True)
s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8')
for path in ['/cierrecaja/ingresos','/cierrecaja/ingresos/adelantos','/cierrecaja/salidas']:
 a=s.index("$app->get('"+path+"'");b=s.index('\n});',a)+len('\n});');chunk=s[a:b]
 chunk=chunk.replace('Cb_CajaBanco.Importe,','Cb_CajaBanco.Importe, COALESCE(Cb_CajaBanco.Anulado+0,0) AS Anulado,',1)
 if 'Cb_Cuenta.EsTarjeta' not in chunk:chunk=chunk.replace('Cb_CajaBanco.Importe,','Cb_CajaBanco.Importe, COALESCE(Cb_Cuenta.EsTarjeta+0,0) AS EsTarjeta,',1)
 else:chunk=chunk.replace('Cb_Cuenta.EsTarjeta,','COALESCE(Cb_Cuenta.EsTarjeta+0,0) AS EsTarjeta,',1)
 s=s[:a]+chunk+s[b:]
(root/'index.php').write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='\n.closing-bank-note{padding:10px 13px;margin:0 0 10px;border-radius:9px;background:#eaf6ff;color:#19678b;font-size:12px;font-weight:600}\n';p.write_text(s,encoding='utf-8')
