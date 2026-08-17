(function(){
  'use strict';
  function txt(el){return (el&&el.textContent||'').replace(/\s+/g,' ').trim();}
  function compact(node,required,limit){var el=node;while(el&&el!==document.body){var t=txt(el);if(required.every(function(x){return t.indexOf(x)!==-1;})&&t.length<=limit)return el;el=el.parentElement;}return null;}
  function findExact(nodes,value){return nodes.find(function(el){return txt(el)===value;});}
  function plain(value){return String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();}
  function enhance(){
    if(location.hash.indexOf('facturacion')===-1)return;
    document.body.classList.add('billing-modern');
    var root=document.querySelector('#app');if(!root)return;
    var nodes=Array.prototype.slice.call(root.querySelectorAll('*'));
    var validation=nodes.find(function(el){var value=plain(txt(el));return value.indexOf('envio sunat automatico')===0||value.indexOf('validacion sunat automatica')===0;});
    var status=document.getElementById('nm-sunat-test-center')||(validation&&compact(validation,[],900));
    if(status&&!status.classList.contains('billing-status')){
      status.classList.add('billing-status');
      var hero=document.createElement('section');hero.className='billing-hero';
      hero.innerHTML='<span class="billing-hero__icon"><i class="fas fa-file-invoice-dollar"></i></span><div><small>FACTURACIÓN ELECTRÓNICA</small><h1>Comprobantes SUNAT</h1><p>Supervisa, valida y emite tus documentos electrónicos con mayor claridad.</p></div><span class="billing-hero__secure"><i class="fas fa-shield-alt"></i> Monitoreo automático</span>';
      if(!status.previousElementSibling||!status.previousElementSibling.classList.contains('billing-hero'))status.parentNode.insertBefore(hero,status);
      var metrics=document.createElement('div');metrics.className='billing-metrics';
      metrics.innerHTML='<article><i class="fas fa-eye"></i><div><b data-visible>0</b><span>Visibles</span></div></article><article><i class="fas fa-check-circle"></i><div><b data-ok>0</b><span>Conformes</span></div></article><article><i class="fas fa-clock"></i><div><b data-pending>0</b><span>Pendientes</span></div></article><article><i class="fas fa-exclamation-circle"></i><div><b data-observed>0</b><span>Observados</span></div></article>';
      status.appendChild(metrics);
    }
    if(status){
      var source=txt(status),match=source.match(/(\d+)\s*visibles\s*(\d+)\s*conformes\s*(\d+)\s*pendientes\s*(\d+)\s*observados/i);
      if(match){[['[data-visible]',1],['[data-ok]',2],['[data-pending]',3],['[data-observed]',4]].forEach(function(item){var el=status.querySelector(item[0]);if(el&&el.textContent!==match[item[1]])el.textContent=match[item[1]];});}
    }
    var almacen=nodes.find(function(el){return el.tagName==='LABEL'&&plain(txt(el).replace(':',''))==='almacen';});
    if(almacen){var filters=almacen.parentElement;while(filters&&filters!==root){var filterText=plain(txt(filters));if(filterText.indexOf('fecha inicio')!==-1&&filterText.indexOf('fecha fin')!==-1&&filterText.indexOf('tipo comprobante')!==-1&&filterText.indexOf('estado')!==-1)break;filters=filters.parentElement;}if(filters&&filters!==root)filters.classList.add('billing-filters');}
    var table=Array.prototype.slice.call(root.querySelectorAll('table')).find(function(el){var t=txt(el);return t.indexOf('Documento')!==-1&&t.indexOf('Cliente')!==-1&&t.indexOf('Consulta')!==-1;});
    if(table){table.classList.add('billing-table');var wrap=table.parentElement;if(wrap)wrap.classList.add('billing-table-card');if(table.tBodies.length&&table.tBodies[0].rows.length===0&&!wrap.querySelector('.billing-empty')){var empty=document.createElement('div');empty.className='billing-empty';empty.innerHTML='<i class="fas fa-file-signature"></i><strong>No hay comprobantes para mostrar</strong><span>Ajusta los filtros o selecciona otro estado.</span>';wrap.appendChild(empty);}}
    var emit=nodes.find(function(el){return /^(BUTTON|A)$/.test(el.tagName)&&plain(txt(el)).indexOf('emitir documentos electronicos')!==-1;});
    if(emit&&!emit.classList.contains('billing-emit')){emit.classList.add('billing-emit');emit.innerHTML='<i class="fas fa-paper-plane"></i><span>Emitir documentos electrónicos</span>';}
  }
  var queued=false;new MutationObserver(function(){if(!queued){queued=true;requestAnimationFrame(function(){queued=false;enhance();});}}).observe(document.documentElement,{childList:true,subtree:true});document.addEventListener('DOMContentLoaded',enhance);enhance();
}());

// Carga aislada del diseño de Seguridad sin modificar el paquete compilado de Vue.
