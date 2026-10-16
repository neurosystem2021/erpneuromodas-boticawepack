from pathlib import Path
p=Path('src/utils/cashClosingTicket.js');s=p.read_text(encoding='utf-8');a=s.index('export function cashClosingTicket')
s=s[:a]+'''export function soldProductSummary(sales) {
  const groups = new Map();
  sales.filter(sale => !number(sale.Anulado) && String(sale.CodSunat).padStart(2,'0') !== '07').forEach(sale => {
    (sale.productos || []).forEach(product => {
      const price = number(product.Precio);
      const key = JSON.stringify([product.IdProducto, product.Producto, price]);
      if (!groups.has(key)) groups.set(key, {name:product.Producto || product.Descripcion || '', quantity:0, price, discount:0, total:0});
      const item=groups.get(key);item.quantity+=number(product.Cantidad);item.discount+=number(product.Descuento);item.total+=number(product.Cantidad)*price-number(product.Descuento);
    });
  });
  return Array.from(groups.values()).sort((a,b)=>a.name.localeCompare(b.name,'es') || a.price-b.price);
}
'''+s[a:]
needle="  return `<!doctype html>"
insert='''  const products=soldProductSummary(data.sales);
  const productTotal=products.reduce((sum,item)=>sum+item.total,0);
  const detail=section('PRODUCTOS VENDIDOS')+products.map(item=>`<article class="product-line"><strong>${escape(item.name)}</strong><div class="product-numbers"><span>${escape(Number(item.quantity.toFixed(3)))} × ${money(item.price)}</span><b>${money(item.total)}</b></div>${item.discount?`<small>Descuento: ${money(item.discount)}</small>`:''}</article>`).join('')+`<div class="row grand"><span>GRAN TOTAL VENDIDO</span><b>${money(productTotal)}</b></div>`;
'''
s=s.replace(needle,insert+needle,1)
s=s.replace('.toolbar{padding:', '.product-line{padding:6px 0;border-bottom:1px dashed #888;break-inside:avoid}.product-line>strong{display:block;overflow-wrap:anywhere;font-size:10px}.product-numbers{display:flex;justify-content:space-between;gap:6px;margin-top:4px;font-variant-numeric:tabular-nums}.product-numbers>b{white-space:nowrap}.product-line small{display:block;margin-top:3px;font-size:8px}.toolbar{padding:',1)
s=s.replace('<h1>CIERRE DE CAJA</h1>', "<h1>${data.productDetail?'PRODUCTOS VENDIDOS':'CIERRE DE CAJA'}</h1>")
s=s.replace('<div class="row grand"><span>GRAN TOTAL</span><b>${money(data.grandTotal)}</b></div>${summaries}', '${data.productDetail?detail:`<div class="row grand"><span>GRAN TOTAL</span><b>${money(data.grandTotal)}</b></div>${summaries}`}')
p.write_text(s,encoding='utf-8')
p=Path('src/views/reportes/CierreCaja.vue');s=p.read_text(encoding='utf-8');s=s.replace('<h5>Resumen Por Producto\n              </h5>', '''<h5>Resumen Por Producto</h5>
              <b-button class="mb-3" variant="info" @click="imprimirTicket(true)"><i class="fa fa-print mr-1" aria-hidden="true"></i> Ticket de productos vendidos</b-button>''',1)
s=s.replace('async imprimirTicket() {', 'async imprimirTicket(productDetail = false) {',1)
s=s.replace("      try {\n        const response = await this.axios.get(this.API + 'empresa/id/'", "      try {\n        if (productDetail === true) {\n          await this.loadDetalles();\n          if (this.ventas.some(sale => !Number(sale.Anulado) && !Array.isArray(sale.productos))) throw new Error('No se cargaron los productos.');\n        }\n        const response = await this.axios.get(this.API + 'empresa/id/'",1)
s=s.replace('cashClosingTicket({company:response.data', 'cashClosingTicket({productDetail:productDetail === true,company:response.data',1)
p.write_text(s,encoding='utf-8')
