from pathlib import Path
p=Path('C:/xampp/htdocs/boticav1/tienda/index.php');s=p.read_text(encoding='utf-8');a=s.index('<section id="webOffers"');b=s.index('  <template data-disabled=',a)
s=s[:a]+'''<section id="webOffers" class="web-offers" hidden aria-labelledby="webOffersTitle">
 <div class="web-offers-heading"><h2 id="webOffersTitle">OFERTAS</h2><a href="#catalogo">VER MÁS</a></div>
 <div class="web-offers-carousel"><button class="offers-arrow offers-prev" type="button" aria-label="Ofertas anteriores">&#8249;</button><div id="webOffersList" class="web-offers-list" tabindex="0" aria-label="Carrusel de ofertas"></div><button class="offers-arrow offers-next" type="button" aria-label="Siguientes ofertas">&#8250;</button></div>
</section>
<script>
function offerCard(p){
 const previous=Number(p.precio_anterior||0),price=Number(p.precio||0);
 const discount=previous>price?Math.round((previous-price)/previous*100):0;
 return `<article class="offer-item"><button class="offer-photo" onclick="openProduct(${Number(p.id)})" aria-label="Ver ${esc(p.nombre)}"><img loading="lazy" src="${esc(productImages(p)[0]||FALLBACK)}" alt="${esc(p.nombre)}" onerror="safeImage(this)">${discount>0?`<span class="offer-discount">−${discount}%<small>DESCUENTO</small></span>`:'<span class="offer-tag">OFERTA</span>'}</button><button class="offer-name" onclick="openProduct(${Number(p.id)})" title="${esc(p.nombre)}">${esc(p.nombre)}</button><div class="offer-price">${storePriceHtml(p)}</div><button class="offer-add" data-product-id="${Number(p.id)}" onclick="add(${Number(p.id)},1,this)">＋ Agregar <span class="add-count" hidden>0</span></button></article>`;
}
(async function(){
 try {
  const response=await fetch(API+'?ofertas=1',{cache:'no-store'});
  if(!response.ok)throw new Error('offers');
  const offers=await response.json();
  if(!Array.isArray(offers)||!offers.length)return;
  offers.forEach(p=>catalog.set(String(p.id),p));
  const section=document.getElementById('webOffers'),list=document.getElementById('webOffersList');
  list.innerHTML=offers.map(offerCard).join('');
  document.querySelector('.tools').insertAdjacentElement('beforebegin',section);
  section.hidden=false;
  let interacting=false;
  function overflow(){return list.scrollWidth>list.clientWidth+5;}
  function move(direction){const max=list.scrollWidth-list.clientWidth;if(max<=5)return;const step=list.firstElementChild.getBoundingClientRect().width+16;const end=direction>0&&list.scrollLeft>=max-5;list.scrollTo({left:end?0:Math.max(0,list.scrollLeft+direction*step),behavior:'smooth'});}
  function controls(){section.querySelectorAll('.offers-arrow').forEach(button=>button.hidden=!overflow());}
  section.querySelector('.offers-prev').onclick=()=>move(-1);
  section.querySelector('.offers-next').onclick=()=>move(1);
  section.addEventListener('mouseenter',()=>interacting=true);section.addEventListener('mouseleave',()=>interacting=false);
  section.addEventListener('focusin',()=>interacting=true);section.addEventListener('focusout',event=>{if(!section.contains(event.relatedTarget))interacting=false;});
  list.addEventListener('keydown',event=>{if(event.key==='ArrowRight'||event.key==='ArrowLeft'){event.preventDefault();move(event.key==='ArrowRight'?1:-1);}});
  window.addEventListener('resize',controls);controls();
  if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches)setInterval(()=>{if(!interacting&&!document.hidden&&!document.getElementById('productModal').classList.contains('open'))move(1);},4000);
 } catch(error){console.warn('No se pudieron cargar las ofertas de la tienda.');}
})();
</script>
<style>
#webOffers.web-offers{max-width:1650px;margin:24px auto 60px;padding:12px 20px 20px;background:#fff!important;border:0;border-radius:0;box-shadow:none;color:#24242a}
#webOffers[hidden]{display:none!important}.web-offers-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;border-bottom:1px solid #e6e6e6;margin-bottom:20px}
#webOffers .web-offers-heading h2{font-size:23px;font-weight:700;margin:0;padding:12px 0;border-bottom:3px solid #e4132f;color:#28282d;letter-spacing:.4px}
.web-offers-heading a{font-size:14px;font-weight:800;color:#e4132f;text-decoration:none}.web-offers-carousel{position:relative}
#webOffersList{display:flex;gap:16px;overflow-x:auto;padding:4px 2px 12px;scroll-snap-type:x mandatory;scrollbar-width:none;touch-action:pan-x pan-y}
#webOffersList::-webkit-scrollbar{display:none}.offer-item{flex:0 0 calc((100% - 80px)/6);min-width:0;scroll-snap-align:start;text-align:center;background:#fff;padding:6px 8px 12px;border:1px solid transparent;border-radius:12px}
.offer-item:hover{border-color:#f5d7dc}.offer-photo{position:relative;display:block;width:100%;height:205px;padding:22px 8px 8px;border:0;background:#fff;cursor:pointer}.offer-photo img{width:100%;height:100%;object-fit:contain}.offer-discount,.offer-tag{position:absolute;right:0;top:0;background:#e4132f;color:#fff;border-radius:8px;padding:7px;font-size:20px;font-weight:900}.offer-discount small{display:block;font-size:8px;letter-spacing:.4px}.offer-tag{font-size:11px}
.offer-name{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;width:100%;height:42px;padding:0;border:0;background:#fff;color:#29292f;font-size:13px;line-height:21px;font-weight:600;cursor:pointer;margin:12px 0 6px}
.offer-price{color:#e4132f;font-weight:800;font-size:21px;min-height:51px}.offer-price .web-price-before{display:block;color:#919197;font-size:13px;font-weight:400;line-height:18px;text-decoration:line-through}
.offer-add{background:#fff;color:#da1230;border:1px solid #f0ccd3;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:700;margin-top:5px}.offer-add:hover{background:#e4132f;color:#fff}
#webOffers .offers-arrow{position:absolute;top:95px;width:42px;height:42px;border:1px solid #eee;border-radius:50%;background:#fff;color:#d8132e;box-shadow:0 2px 8px #0002;font-size:29px;line-height:1;z-index:2}.offers-prev{left:-16px}.offers-next{right:-16px}.offers-arrow[hidden]{display:none}
@media(max-width:1100px){.offer-item{flex-basis:calc((100% - 48px)/4)}}
@media(max-width:700px){#webOffers.web-offers{margin:16px 8px 50px;padding:8px 12px}.offer-item{flex-basis:calc((100% - 16px)/2)}.offer-photo{height:160px}#webOffers .web-offers-heading h2{font-size:20px}.offer-price{font-size:19px}#webOffers .offers-arrow{top:75px;width:34px;height:34px}.offers-prev{left:-10px}.offers-next{right:-10px}}
</style>
'''+s[b:]
p=Path('deploy-src/offers-carousel/tienda/index.php');p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
import re
active=s.split('<template data-disabled=')[0]
for i,script in enumerate(re.findall(r'<script[^>]*>(.*?)</script>',active,re.S)):Path('.tmp-carousel-'+str(i)+'.js').write_text(script,encoding='utf-8')
