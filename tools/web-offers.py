from pathlib import Path
p=Path('src/components/ProductoForm.vue');s=p.read_text(encoding='utf-8');s=s.replace('        <b-form-group label="Precio web:">','''        <b-form-group>
          <b-form-checkbox v-model="producto.OfertaWeb" :value="1" :unchecked-value="0">Oferta tienda virtual NEUROSYSTEM</b-form-checkbox>
          <small>Mostrar en ofertas debajo del flyer de la tienda.</small>
        </b-form-group>
        <b-form-group label="Precio web:">''',1);s=s.replace('this.producto.PrecioWeb = newVal.PrecioWeb;','this.producto.OfertaWeb = Number(newVal.OfertaWeb || 0);\n              this.producto.PrecioWeb = newVal.PrecioWeb;',1);s=s.replace('        PrecioWeb: null,','        OfertaWeb: 0,\n        PrecioWeb: null,',1);p.write_text(s,encoding='utf-8')
root=Path('C:/xampp/htdocs/boticav1');out=Path('deploy-src/web-offers')
for rel in ['api/index.php','api/tienda/index.php','tienda/index.php']:
 s=(root/rel).read_text(encoding='utf-8')
 if rel=='api/index.php':
  s=s.replace("    $precioWeb = $request->getParam('PrecioWeb')", "    $ofertaWeb = (int)$request->getParam('OfertaWeb') === 1 ? 1 : 0;\n    $precioWeb = $request->getParam('PrecioWeb')",1)
  s=s.replace('"PrecioWeb" => $precioWeb,','"OfertaWeb" => $ofertaWeb,\n            "PrecioWeb" => $precioWeb,',1)
  s=s.replace('                "PrecioWeb",','                "OfertaWeb",\n                "PrecioWeb",',1)
  s=s.replace('                $precioWeb,','                $ofertaWeb,\n                $precioWeb,',1)
 elif rel=='api/tienda/index.php':
  s=s.replace('p.PrecioAnterior precio_anterior,','p.PrecioAnterior precio_anterior,p.OfertaWeb oferta_web,',1)
  s=s.replace('    $params = array();',"    if ((int)($_GET['ofertas'] ?? 0) === 1) $sql .= ' AND p.OfertaWeb=1';\n    $params = array();",1)
 else:
  # Patch the active script, before the disabled legacy templates.
  a,b=s.split('  <template data-disabled=',1)
  a=a.replace('${money(p.precio)}</span><button class="add"','${storePriceHtml(p)}</span><button class="add"',1)
  a=a.replace("$('detailPrice').textContent=money(activeProduct.precio)","$('detailPrice').innerHTML=storePriceHtml(activeProduct)",1)
  marker='function cardTemplate(p)'
  a=a.replace(marker,"function storePriceHtml(p){const old=Number(p.precio_anterior||0);return (old>Number(p.precio)?'<del class=\"web-price-before\">'+money(old)+'</del>':'')+'<span>'+money(p.precio)+'</span>'}\n"+marker,1)
  a+='''<section id="webOffers" class="web-offers" hidden aria-labelledby="webOffersTitle"><header><span>★ NEUROSYSTEM</span><h2 id="webOffersTitle">Ofertas de la tienda</h2></header><div id="webOffersList" class="web-offers-list"></div></section>
<script>
(async function(){
 try {
  const response=await fetch(API+'?ofertas=1',{cache:'no-store'});
  if(!response.ok)throw new Error('offers');
  const offers=await response.json();
  if(!Array.isArray(offers)||!offers.length)return;
  offers.forEach(p=>catalog.set(String(p.id),p));
  const section=document.getElementById('webOffers');
  section.querySelector('#webOffersList').innerHTML=offers.map(cardTemplate).join('');
  document.querySelector('.tools').insertAdjacentElement('beforebegin',section);
  section.hidden=false;
 } catch(error){console.warn('No se pudieron cargar las ofertas de la tienda.');}
})();
</script>
<style>
.web-offers{max-width:1800px;margin:20px auto 55px;padding:20px;background:#fff;border:1px solid #f0d6db;border-radius:22px;box-shadow:0 8px 30px #8e122610}
.web-offers[hidden]{display:none}.web-offers header{margin-bottom:16px}.web-offers header span{color:#db1230;font-size:12px;font-weight:800;letter-spacing:1px}.web-offers h2{margin:4px 0;color:#a91029;font-size:25px;font-weight:900}
.web-offers-list{display:grid;grid-auto-flow:column;grid-auto-columns:280px;gap:16px;overflow-x:auto;padding:4px 2px 14px;scroll-snap-type:x proximity}.web-offers-list>.card{scroll-snap-align:start}.web-price-before{display:block;color:#89919b;font-size:14px;font-weight:500;text-decoration:line-through;text-decoration-thickness:2px}
@media(max-width:600px){.web-offers{margin:12px 12px 50px;padding:14px}.web-offers-list{grid-auto-columns:250px}.web-offers h2{font-size:21px}}
</style>
'''
  s=a+'  <template data-disabled='+b
 p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
