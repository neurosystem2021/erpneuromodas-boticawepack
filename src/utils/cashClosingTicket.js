const number = value => Number(value) || 0;
const escape = value => String(value == null ? '' : value).replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
const money = value => 'S/ ' + number(value).toFixed(2);
export function closingSummary(sales, code) {
  const rows = sales.filter(sale => String(sale.CodSunat).padStart(2, '0') === code).slice().sort((a,b) => String(a.Serie).localeCompare(String(b.Serie)) || number(a.Numero)-number(b.Numero));
  const cancelled = rows.filter(sale => number(sale.Anulado) !== 0);
  return {count: rows.length, first: rows.length ? rows[0].Serie+'-'+rows[0].Numero : '—', last: rows.length ? rows[rows.length-1].Serie+'-'+rows[rows.length-1].Numero : '—', cancelled:cancelled.length, cancelledAmount:cancelled.reduce((sum,sale)=>sum+number(sale.Total),0), total:rows.filter(sale=>!number(sale.Anulado)).reduce((sum,sale)=>sum+number(sale.Total),0)};
}
export function soldProductSummary(sales) {
  const groups = new Map();
  sales.filter(sale => !number(sale.Anulado) && String(sale.CodSunat).padStart(2,'0') !== '07').forEach(sale => {
    (sale.productos || []).forEach(product => {
      const price = number(product.Precio);
      const key = product.IdProducto != null ? String(product.IdProducto) : String(product.Producto || product.Descripcion || '');
      if (!groups.has(key)) groups.set(key, {name:product.Producto || product.Descripcion || '', quantity:0, price, gross:0, mixedPrices:false, discount:0, total:0});
      const item=groups.get(key);if(item.price!==price)item.mixedPrices=true;item.gross+=number(product.Cantidad)*price;item.quantity+=number(product.Cantidad);item.discount+=number(product.Descuento);item.total+=number(product.Cantidad)*price-number(product.Descuento);
    });
  });
  return Array.from(groups.values()).map(item => Object.assign({},item,{price:item.quantity ? item.gross/item.quantity : item.price})).sort((a,b)=>a.name.localeCompare(b.name,'es'));
}
export function cashClosingTicket(data) {
  const row = (label,value,bold=false) => `<div class="row ${bold?'bold':''}"><span>${escape(label)}</span><b>${escape(value)}</b></div>`;
  const section = title => `<h2>${escape(title)}</h2>`;
  const summaries = [['01','FACTURAS'],['03','BOLETAS'],['12','NOTAS DE VENTA']].map(([code,title])=>{
    const summary=closingSummary(data.sales,code);
    return section('RESUMEN DE '+title)+row('COMPROBANTES EMITIDOS:',summary.count)+row('NRO INICIAL:',summary.first)+row('NRO FINAL:',summary.last)+row('VENTAS ANULADAS:',summary.cancelled)+row('MONTO ANULADO:',money(summary.cancelledAmount))+row('VALOR VENTA:',money(summary.total))+row('TOTAL '+title+':',money(summary.total),true);
  }).join('');
  const products=soldProductSummary(data.sales);
  const productTotal=products.reduce((sum,item)=>sum+item.total,0);
  const detail=section('PRODUCTOS VENDIDOS')+products.map(item=>`<article class="product-line"><strong>${escape(item.name)}</strong><div class="product-numbers"><span>${escape(Number(item.quantity.toFixed(3)))} × ${item.mixedPrices ? 'S/ '+item.price.toFixed(4).replace(/0+$/,'').replace(/\.$/,'')+' (prom.)' : money(item.price)}</span><b>${money(item.total)}</b></div>${item.discount?`<small>Descuento: ${money(item.discount)}</small>`:''}</article>`).join('')+`<div class="row grand"><span>GRAN TOTAL VENDIDO</span><b>${money(productTotal)}</b></div>`;
  const banks=(data.bankMovements || []).filter(item => !Number(item.Anulado));
  const bankReference=banks.length ? section('MOVIMIENTOS BANCARIOS (REFERENCIA)')+'<p class="meta center">Informativo: no suman ni restan a Caja Principal.</p>'+banks.map(item => row((item.Cuenta || 'Cuenta bancaria')+' · '+item.direction,money(item.Importe))+'<div class="meta">'+escape(item.Concepto || '')+'</div>').join('') : '';
  return `<!doctype html><html lang="es"><head><meta charset="utf-8"><title>Cierre de caja</title><style>
  @page{size:80mm 297mm;margin:4mm}*{box-sizing:border-box}html{margin:0;padding:0}body{width:72mm;max-width:100%;margin:0 auto;padding:0;color:#000;background:#fff;font:10px Arial,sans-serif}.center{text-align:center}h1{font-size:17px;margin:9px 0 5px}h2{font-size:11px;text-align:center;margin:9px 0 6px;border-top:1px dashed #555;border-bottom:1px dashed #555;padding:4px 0}.company{font-size:12px;font-weight:bold;overflow-wrap:anywhere}.meta{font-size:9px;line-height:1.45;overflow-wrap:anywhere}.row{display:flex;justify-content:space-between;gap:8px;padding:3px 0}.row span{flex:1;min-width:0;overflow-wrap:anywhere}.row b{flex:0 0 auto;max-width:45%;overflow-wrap:anywhere;text-align:right;font-variant-numeric:tabular-nums}.bold{border-top:2px solid #000;border-bottom:2px solid #000;font-weight:bold;margin:4px 0}.grand{font-size:14px;font-weight:900;border-top:4px solid #000;border-bottom:2px solid #000;margin:10px 0;padding:10px 0}.footer{font-size:8px;text-align:center;border-top:1px dashed #777;padding-top:8px;margin-top:10px}.product-line{padding:6px 0;border-bottom:1px dashed #888;break-inside:avoid}.product-line>strong{display:block;overflow-wrap:anywhere;font-size:10px}.product-numbers{display:flex;justify-content:space-between;gap:6px;margin-top:4px;font-variant-numeric:tabular-nums}.product-numbers>b{white-space:nowrap}.product-line small{display:block;margin-top:3px;font-size:8px}.toolbar{padding:12px;text-align:center}.toolbar button{padding:10px;cursor:pointer}@media print{.toolbar{display:none}body{width:72mm;max-width:100%;margin:0 auto;padding:0}h2,.row{break-inside:avoid}}
  </style></head><body><div class="toolbar"><button onclick="window.print()">Imprimir ticket</button></div><div class="center"><div class="company">${escape(data.company.RAZONSOCIAL||data.company.nombre_comercial||'')}</div><div class="meta">${data.company.RUC?'RUC: '+escape(data.company.RUC):''}<br>${escape(data.company.DIRECCION||'')}</div><h1>${data.productDetail?'PRODUCTOS VENDIDOS':'CIERRE DE CAJA'}</h1><div class="meta">${data.warehouse?'Almacén: '+escape(data.warehouse)+'<br>':''}Fecha: ${escape(data.date)}<br>Vendedor(a): ${escape(data.seller)}<br>Ventas realizadas: ${data.sales.filter(sale=>!number(sale.Anulado)&&String(sale.CodSunat)!=='07').length}</div></div>
  ${data.productDetail?detail:`<div class="row grand"><span>GRAN TOTAL</span><b>${money(data.grandTotal)}</b></div>${summaries}`}
  ${section('RESUMEN POR MÉTODO')}${row('Efectivo',money(data.cash))}${row('Yape / Plin',money(data.yape))}${row('Tarjeta',money(data.card))}${row('Transferencia',money(data.transfer))}${row('Crédito',money(data.credit))}
  ${section('MOVIMIENTOS DE CAJA')}${row('Total ingresos',money(data.income))}${row('Total salidas',money(data.expenses))}${row('Adelantos',money(data.advances))}
  ${bankReference}<div class="row grand"><span>TOTAL FINAL DE CAJA</span><b>${money(data.grandTotal)}</b></div><div class="footer">Resumen generado por el sistema</div></body></html>`;
}

