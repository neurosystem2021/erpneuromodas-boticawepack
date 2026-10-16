
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
