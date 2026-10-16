from pathlib import Path
p=Path('src/components/ProductoForm.vue');s=p.read_text(encoding='utf-8');needle='''        <b-form-group v-if="false">''';pos=s.index(needle,s.index('<label>Precio APP:'))
s=s[:pos]+'''        <b-form-group label="Precio web:">
          <input type="number" min="0" step="0.01" v-model.number="producto.PrecioWeb" class="form-control">
          <small>Si se deja vacío, se usa el precio de venta.</small>
        </b-form-group>
        <b-form-group label="Precio anterior (tachado en la web):">
          <input type="number" min="0" step="0.01" v-model.number="producto.PrecioAnterior" class="form-control">
          <small>Se muestra tachado cuando es mayor que el precio web.</small>
        </b-form-group>

'''+s[pos:]
s=s.replace('this.producto.PrecioApp = newVal.PrecioApp;','this.producto.PrecioApp = newVal.PrecioApp;\n              this.producto.PrecioWeb = newVal.PrecioWeb;\n              this.producto.PrecioAnterior = newVal.PrecioAnterior;')
s=s.replace('        PrecioApp: 0,','        PrecioApp: 0,\n        PrecioWeb: null,\n        PrecioAnterior: null,');p.write_text(s,encoding='utf-8')
root=Path('C:/xampp/htdocs/boticav1');out=Path('deploy-src/web-prices')
for rel in ['api/index.php','api/tienda/index.php','tienda/index.php']:
 s=(root/rel).read_text(encoding='utf-8')
 if rel=='api/index.php':
  needle="    $PrecioApp = $request->getParam('PrecioApp') ? $request->getParam('PrecioApp') : null;"
  assert needle in s;s=s.replace(needle,needle+"\n    $precioWeb = $request->getParam('PrecioWeb') !== null && $request->getParam('PrecioWeb') !== '' ? max(0, (float)$request->getParam('PrecioWeb')) : null;\n    $precioAnterior = $request->getParam('PrecioAnterior') !== null && $request->getParam('PrecioAnterior') !== '' ? max(0, (float)$request->getParam('PrecioAnterior')) : null;",1)
  needle='"PrecioApp" =>                      $PrecioApp,';assert needle in s;s=s.replace(needle,needle+'\n            "PrecioWeb" => $precioWeb,\n            "PrecioAnterior" => $precioAnterior,',1)
  s=s.replace('                "PrecioApp",','                "PrecioApp",\n                "PrecioWeb",\n                "PrecioAnterior",',1)
  s=s.replace('                $PrecioApp,','                $PrecioApp,\n                $precioWeb,\n                $precioAnterior,',1)
 elif rel=='api/tienda/index.php':
  assert 'p.PrecioContado precio,' in s;s=s.replace('p.PrecioContado precio,','COALESCE(p.PrecioWeb,p.PrecioContado) precio,p.PrecioAnterior precio_anterior,',1)
 else:
  needle='    function priceOf(product){';assert needle in s
  s=s.replace(needle,'''    function webPriceHtml(product) {
      const current = priceOf(product), previous = Number(product.precio_anterior || 0);
      return (previous > current ? '<del class="web-price-before">' + money(previous) + '</del>' : '') + '<span>' + money(current) + '</span>';
    }
'''+needle,1)
  s=s.replace('${money(priceOf(product))}</strong><button class="add"','${webPriceHtml(product)}</strong><button class="add"',1)
  s=s.replace("$('detailPrice').textContent=money(priceOf(product));","$('detailPrice').innerHTML=webPriceHtml(product);",1)
  s+='\n<style>.web-price-before{display:block;font-size:14px;line-height:1.4;color:#89919b;font-weight:500;text-decoration:line-through;text-decoration-thickness:2px}.price>span{white-space:nowrap}</style>\n'
 p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
