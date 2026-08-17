(function(){
  'use strict';
  var empresa=null, reloj=null;
  function esc(v){var d=document.createElement('div');d.textContent=v||'';return d.innerHTML;}
  function textoPlano(v){return String(v||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toUpperCase();}
  function buscarCabecera(){
    var nodos=document.querySelectorAll('#app *');
    for(var i=0;i<nodos.length;i++){
      var texto=textoPlano(nodos[i].textContent).trim();
      if(texto.indexOf('COTIZACION')===0){
        var p=nodos[i];
        while(p&&p.id!=='app'){
          var r=p.getBoundingClientRect();
          if(r.width>800&&r.height>140&&r.height<300)return p;
          p=p.parentElement;
        }
      }
    }
    return null;
  }
  function aplicar(){
    if(!empresa||location.hash.toLowerCase().indexOf('/reportes/proforma/')<0)return;
    var cabecera=buscarCabecera();if(!cabecera)return;
    var numero=textoPlano(cabecera.textContent).match(/COTIZACION\s*([0-9-]+)/);
    if(getComputedStyle(cabecera).position==='static')cabecera.style.position='relative';
    cabecera.classList.add('proforma-cabecera-configurada');
    var bloque=document.getElementById('proformaEmpresaDatos');
    if(!bloque){bloque=document.createElement('section');bloque.id='proformaEmpresaDatos';bloque.className='proforma-empresa-datos';cabecera.appendChild(bloque);}
    var firma=[empresa.logo_proforma,empresa.nombre_comercial,empresa.razon_social,empresa.ruc,empresa.telefono,empresa.direccion,numero&&numero[1]].join('|');
    if(bloque.getAttribute('data-firma')===firma)return;
    bloque.setAttribute('data-firma',firma);
    bloque.innerHTML='<div class="proforma-logo-config"><img src="'+esc(empresa.logo_proforma)+'?empresa=3" alt="Logo empresarial"></div><div class="proforma-identidad"><strong>'+esc(empresa.nombre_comercial||empresa.razon_social)+'</strong><span>RUC: '+esc(empresa.ruc)+'</span>'+(empresa.telefono?'<span>Telefono: '+esc(empresa.telefono)+'</span>':'')+(empresa.direccion?'<span>'+esc(empresa.direccion)+'</span>':'')+'</div>'+(numero?'<div class="proforma-numero-config"><small>COTIZACION</small><b>'+esc(numero[1])+'</b></div>':'');
  }
  function activar(){clearInterval(reloj);var intentos=0;reloj=setInterval(function(){aplicar();if(++intentos>=80)clearInterval(reloj);},200);}
  fetch('/controllers/EmpresaConfigPublica.php?empresa=3',{credentials:'same-origin',cache:'no-store'}).then(function(r){if(!r.ok)throw new Error('config');return r.json();}).then(function(d){empresa=d;activar();}).catch(function(e){if(window.console)console.error('No se pudo cargar la empresa',e);});
  window.addEventListener('load',activar);window.addEventListener('hashchange',activar);
}());
