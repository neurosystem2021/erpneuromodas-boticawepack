from pathlib import Path
p=Path('src/utils/cashClosingTicket.js');s=p.read_text(encoding='utf-8');s=s.replace('const key = JSON.stringify([product.IdProducto, product.Producto, price]);', "const key = product.IdProducto != null ? String(product.IdProducto) : String(product.Producto || product.Descripcion || '');")
s=s.replace("quantity:0, price, discount:0, total:0", "quantity:0, price, gross:0, mixedPrices:false, discount:0, total:0")
s=s.replace('const item=groups.get(key);item.quantity+=', 'const item=groups.get(key);if(item.price!==price)item.mixedPrices=true;item.gross+=number(product.Cantidad)*price;item.quantity+=')
s=s.replace("return Array.from(groups.values()).sort((a,b)=>a.name.localeCompare(b.name,'es') || a.price-b.price);", "return Array.from(groups.values()).map(item => ({...item, price:item.quantity ? item.gross/item.quantity : item.price})).sort((a,b)=>a.name.localeCompare(b.name,'es'));")
s=s.replace('${money(item.price)}</span><b>', "${item.mixedPrices ? 'S/ '+item.price.toFixed(4).replace(/0+$/,'').replace(/\\.$/,'')+' (prom.)' : money(item.price)}</span><b>")
p.write_text(s,encoding='utf-8')
