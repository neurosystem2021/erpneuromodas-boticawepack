
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
