from pathlib import Path
p=Path('src/utils/cashClosingTicket.js');p.parent.mkdir(exist_ok=True)
p.write_text('''const number = value => Number(value) || 0;
const escape = value => String(value == null ? '' : value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
const money = value => 'S/ ' + number(value).toFixed(2);
export function closingSummary(sales, code) {
  const rows = sales.filter(sale => String(sale.CodSunat).padStart(2, '0') === code).slice().sort((a,b) => String(a.Serie).localeCompare(String(b.Serie)) || number(a.Numero)-number(b.Numero));
  const cancelled = rows.filter(sale => number(sale.Anulado) !== 0);
  return {count: rows.length, first: rows.length ? rows[0].Serie+'-'+rows[0].Numero : '—', last: rows.length ? rows[rows.length-1].Serie+'-'+rows[rows.length-1].Numero : '—', cancelled:cancelled.length, cancelledAmount:cancelled.reduce((sum,sale)=>sum+number(sale.Total),0), total:rows.filter(sale=>!number(sale.Anulado)).reduce((sum,sale)=>sum+number(sale.Total),0)};
}
export function cashClosingTicket(data) {
  const row = (label,value,bold=false) => `<div class="row ${bold?'bold':''}"><span>${escape(label)}</span><b>${escape(value)}</b></div>`;
  const section = title => `<h2>${escape(title)}</h2>`;
  const summaries = [['01','FACTURAS'],['03','BOLETAS'],['99','NOTAS DE VENTA']].map(([code,title])=>{
    const summary=closingSummary(data.sales,code);
    return section('RESUMEN DE '+title)+row('COMPROBANTES EMITIDOS:',summary.count)+row('NRO INICIAL:',summary.first)+row('NRO FINAL:',summary.last)+row('VENTAS ANULADAS:',summary.cancelled)+row('MONTO ANULADO:',money(summary.cancelledAmount))+row('VALOR VENTA:',money(summary.total))+row('TOTAL '+title+':',money(summary.total),true);
  }).join('');
  return `<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Cierre de caja</title><style>
  @page{size:80mm auto;margin:2mm}*{box-sizing:border-box}body{width:76mm;margin:0 auto;color:#000;background:#fff;font:10px Arial,sans-serif}.center{text-align:center}h1{font-size:17px;margin:9px 0 5px}h2{font-size:11px;text-align:center;margin:9px 0 6px;border-top:1px dashed #555;border-bottom:1px dashed #555;padding:4px 0}.company{font-size:12px;font-weight:bold}.meta{font-size:9px;line-height:1.45}.row{display:flex;justify-content:space-between;gap:8px;padding:3px 0}.row span{flex:1}.row b{text-align:right;font-variant-numeric:tabular-nums}.bold{border-top:2px solid #000;border-bottom:2px solid #000;font-weight:bold;margin:4px 0}.grand{font-size:14px;font-weight:900;border-top:4px solid #000;border-bottom:2px solid #000;margin:10px 0;padding:10px 0}.footer{font-size:8px;text-align:center;border-top:1px dashed #777;padding-top:8px;margin-top:10px}.toolbar{padding:12px;text-align:center}.toolbar button{padding:10px;cursor:pointer}@media print{.toolbar{display:none}body{width:76mm;margin:0}h2,.row{break-inside:avoid}}
  </style></head><body><div class="toolbar"><button onclick="window.print()">Imprimir ticket</button></div><div class="center"><div class="company">${escape(data.company.RAZONSOCIAL||data.company.nombre_comercial||'')}</div><div class="meta">${data.company.RUC?'RUC: '+escape(data.company.RUC):''}<br>${escape(data.company.DIRECCION||'')}</div><h1>CIERRE DE CAJA</h1><div class="meta">${data.warehouse?'Almacén: '+escape(data.warehouse)+'<br>':''}Fecha: ${escape(data.date)}<br>Vendedor(a): ${escape(data.seller)}<br>Ventas realizadas: ${data.sales.filter(sale=>!number(sale.Anulado)&&String(sale.CodSunat)!=='07').length}</div></div>
  <div class="row grand"><span>GRAN TOTAL</span><b>${money(data.grandTotal)}</b></div>${summaries}
  ${section('RESUMEN POR MÉTODO')}${row('Efectivo',money(data.cash))}${row('Yape / Plin',money(data.yape))}${row('Tarjeta',money(data.card))}${row('Transferencia',money(data.transfer))}${row('Crédito',money(data.credit))}
  ${section('MOVIMIENTOS DE CAJA')}${row('Total ingresos',money(data.income))}${row('Total salidas',money(data.expenses))}${row('Adelantos',money(data.advances))}
  <div class="row grand"><span>TOTAL FINAL DE CAJA</span><b>${money(data.grandTotal)}</b></div><div class="footer">Resumen generado por el sistema</div></body></html>`;
}
''',encoding='utf-8')
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8');s=s.replace('import config from', 'import { cashClosingTicket } from "@/utils/cashClosingTicket";\nimport config from',1)
needle='<b-button variant="success" @click="imprimirResumen()">Imprimir</b-button>'
s=s.replace(needle,needle+'''\n              </b-button-group></b-col><b-col cols="12" class="my-1"><b-button-group>
                <b-button variant="info" @click="imprimirTicket"><i class="fa fa-print mr-1" aria-hidden="true"></i> Imprimir ticket 80 mm</b-button>''',1)
s=s.replace('    imprimirResumen() {','''    async imprimirTicket() {
      const popup = window.open('', '_blank', 'width=440,height=720');
      if (!popup) { this.$toasted.error('Permita las ventanas emergentes para imprimir el ticket.'); return; }
      popup.document.write('<p>Preparando cierre de caja...</p>');
      try {
        const response = await this.axios.get(this.API + 'empresa/id/' + (sessionStorage.getItem('empresa') || 1));
        const html = cashClosingTicket({company:response.data || {},sales:this.ventas,seller:this.vendedor.NombreUsuario || this.vendedor.Usuario || this.IdVendedor,date:this.cierre.FechaReg || this.cierre.Fecha || new Date().toLocaleString('es-PE'),warehouse:this.vendedor.Almacen || '',grandTotal:this.granTotal,cash:this.totalVentasEfectivo,yape:this.totalVentasMastercard,card:this.totalVentasVisa,transfer:this.totalVentasTransferencia,credit:this.totalVentasCredito,income:this.totalIngresos,expenses:this.totalSalidas,advances:this.totalAdelantos});
        popup.document.open(); popup.document.write(html); popup.document.close();
        popup.focus(); setTimeout(() => { if (!popup.closed) popup.print(); }, 250);
      } catch (error) { popup.close(); this.$toasted.error('No se pudo preparar el ticket del cierre. Intente nuevamente.'); }
    },
    imprimirResumen() {''',1)
p.write_text(s,encoding='utf-8')
