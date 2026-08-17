(function(){
  'use strict';
  function textOf(node){return (node&&node.textContent||'').replace(/\s+/g,' ').trim();}
  function closestCompact(node,words,maxLength){
    var current=node;
    while(current&&current!==document.body){
      var text=textOf(current);
      if(words.every(function(word){return text.indexOf(word)!==-1;})&&text.length<=maxLength)return current;
      current=current.parentElement;
    }
    return null;
  }
  function commonParent(nodes){
    nodes=nodes.filter(Boolean);if(!nodes.length)return null;
    var current=nodes[0];
    while(current&&current!==document.body){
      if(nodes.every(function(node){return current.contains(node);}))return current;
      current=current.parentElement;
    }
    return null;
  }
  function directBranch(root,node){
    var current=node;
    while(current&&current.parentElement!==root)current=current.parentElement;
    return current&&current.parentElement===root?current:null;
  }
  function wireMainLayout(filter,tableCard){
    if(!filter||!tableCard)return;
    var layout=commonParent([filter,tableCard]);
    if(!layout||layout===document.body)return;
    var filterBranch=directBranch(layout,filter);
    var tableBranch=directBranch(layout,tableCard);
    if(!filterBranch||!tableBranch||filterBranch===tableBranch)return;
    layout.classList.add('cb-main-layout');
    filterBranch.classList.add('cb-main-layout__filter');
    tableBranch.classList.add('cb-main-layout__table');
  }
  function enhance(){
    var route=(location.hash||'').toLowerCase();
    if(route.indexOf('cajabanco')===-1)return;
    var isReport=route.indexOf('buscador')!==-1;
    document.body.classList.add('caja-bancos-modern');
    var root=document.querySelector('#app');if(!root)return;
    var all=Array.prototype.slice.call(root.querySelectorAll('*'));
    var breadcrumb=all.find(function(el){var t=textOf(el);return t==='CAJA BANCOS / REPORTE'||t==='CAJA BANCOS/REPORTE';});
    if(breadcrumb&&!breadcrumb.classList.contains('cb-breadcrumb')){
      breadcrumb.classList.add('cb-breadcrumb');
      var hero=document.createElement('section');hero.className='cb-hero';
      hero.innerHTML='<span class="cb-hero__icon"><i class="fas fa-wallet"></i></span><div><small>GESTIÓN FINANCIERA</small><h1>Caja y bancos</h1><p>Controla ingresos, salidas y movimientos bancarios desde un solo lugar.</p></div><span class="cb-hero__status"><i class="fas fa-shield-alt"></i> Control diario</span>';
      breadcrumb.parentNode.insertBefore(hero,breadcrumb);
    }
    if(!root.querySelector('.cb-hero')){
      var routeHero=document.createElement('section');routeHero.className='cb-hero';
      routeHero.innerHTML='<span class="cb-hero__icon"><i class="fas fa-wallet"></i></span><div><small>GESTIÓN FINANCIERA</small><h1>'+(isReport?'Reporte de caja y bancos':'Caja y bancos')+'</h1><p>'+(isReport?'Consulta, filtra y exporta todos los movimientos de caja.':'Controla ingresos, salidas y movimientos bancarios desde un solo lugar.')+'</p></div><span class="cb-hero__status"><i class="fas fa-shield-alt"></i> Control diario</span>';
      root.insertBefore(routeHero,root.firstChild);
    }
    var tabTexts=['Caja Bancos','Gasto','Ingreso','Salida','Dep. Cuenta Bancaria'];
    var tabLinks=all.filter(function(el){return (el.tagName==='A'||el.tagName==='BUTTON')&&tabTexts.indexOf(textOf(el))!==-1;});
    if(tabLinks.length){var tabBox=closestCompact(tabLinks[0],['Caja Bancos','Gasto','Ingreso','Salida'],180);if(tabBox)tabBox.classList.add('cb-tabs');}
    var cuentaLabel=all.find(function(el){return el.tagName==='LABEL'&&textOf(el).replace(':','')==='Cuenta';});
    var fechaLabel=all.find(function(el){return el.tagName==='LABEL'&&textOf(el).replace(':','')==='Fecha';});
    var filter=null;if(cuentaLabel){filter=closestCompact(cuentaLabel,['Cuenta','Fecha','EXPORTAR','NUEVO'],520)||commonParent([cuentaLabel,fechaLabel]);if(filter&&filter!==root)filter.classList.add('cb-filter-card');}
    Array.prototype.slice.call(root.querySelectorAll('.clsCuenta')).forEach(function(card){
      var controls=card.querySelectorAll('select,.autocomplete');
      Array.prototype.slice.call(controls).forEach(function(control){
        var holder=control.closest('[class*=col-]')||control.parentElement;
        if(holder)holder.classList.add('cb-account-field');
      });
    });
    var table=Array.prototype.slice.call(root.querySelectorAll('table')).find(function(el){var t=textOf(el);return t.indexOf('Concepto')!==-1&&t.indexOf('Ingresos')!==-1&&t.indexOf('Salida')!==-1;});
    if(table){
      table.classList.add('cb-table');
      var wrapper=table.parentElement;if(wrapper)wrapper.classList.add('cb-table-card');
      var existingEmpty=wrapper&&wrapper.querySelector('.cb-empty');
      if(table.tBodies.length&&table.tBodies[0].rows.length>0&&existingEmpty)existingEmpty.remove();
      if(table.tBodies.length&&table.tBodies[0].rows.length===0&&!existingEmpty){
        var empty=document.createElement('div');empty.className='cb-empty';empty.innerHTML='<i class="fas fa-receipt"></i><strong>Sin movimientos registrados</strong><span>Selecciona una cuenta y fecha, o crea un nuevo movimiento.</span>';wrapper.appendChild(empty);
      }
    }
    wireMainLayout(filter,table?table.parentElement:null);
    var exportButton=all.find(function(el){return /^(BUTTON|A)$/.test(el.tagName)&&textOf(el)==='EXPORTAR';});if(exportButton)exportButton.classList.add('cb-btn-export');
    var newButton=all.find(function(el){return /^(BUTTON|A)$/.test(el.tagName)&&textOf(el)==='NUEVO';});if(newButton)newButton.classList.add('cb-btn-new');
    if(isReport){
      var generateButton=all.find(function(el){return /^(BUTTON|A)$/.test(el.tagName)&&textOf(el).toLowerCase()==='generar';});
      if(generateButton)generateButton.classList.add('cb-btn-generate');
      var reportTable=Array.prototype.slice.call(root.querySelectorAll('table')).find(function(el){var t=textOf(el).toLowerCase();return t.indexOf('id caja banco')!==-1&&t.indexOf('fecha doc')!==-1&&t.indexOf('importe')!==-1;});
      var controls=[].slice.call(root.querySelectorAll('select,input[type="date"]'));if(generateButton)controls.push(generateButton);if(exportButton)controls.push(exportButton);
      var reportFilter=commonParent(controls);if(reportFilter&&reportFilter!==root&&textOf(reportFilter).length<700)reportFilter.classList.add('cb-report-filter');
      if(reportTable){
        reportTable.classList.add('cb-report-table');var reportWrap=reportTable.parentElement;reportWrap.classList.add('cb-report-table-card');
        if(reportTable.tBodies.length&&reportTable.tBodies[0].rows.length===0&&!reportWrap.querySelector('.cb-empty')){var reportEmpty=document.createElement('div');reportEmpty.className='cb-empty';reportEmpty.innerHTML='<i class="fas fa-search-dollar"></i><strong>No hay movimientos para mostrar</strong><span>Selecciona los filtros y pulsa Generar para consultar el reporte.</span>';reportWrap.appendChild(reportEmpty);}
      }
    }
    Array.prototype.slice.call(document.querySelectorAll('.modal')).forEach(function(modal){
      var title=textOf(modal.querySelector('.modal-header'));
      if(title.indexOf('Agregar Tipo Caja')!==-1||textOf(modal).indexOf('Documentos a aplicar')!==-1){
        modal.classList.add('cb-operation-modal');
        var header=modal.querySelector('.modal-header');
        if(header&&!header.querySelector('.cb-modal-heading')){
          var original=title||'Nuevo movimiento';
          header.innerHTML='<div class="cb-modal-heading"><span><i class="fas fa-wallet"></i></span><div><small>CAJA Y BANCOS</small><strong>'+original+'</strong><p>Registra la operacion y aplica los documentos correspondientes.</p></div></div><button type="button" class="cb-modal-close" data-dismiss="modal" aria-label="Cerrar"><i class="fas fa-times"></i></button>';
        }
        Array.prototype.slice.call(modal.querySelectorAll('.panel')).forEach(function(panel){panel.classList.add('cb-modal-panel');});
        Array.prototype.slice.call(modal.querySelectorAll('select')).forEach(function(select){select.classList.add('cb-modern-select');});
      }
    });
  }
  var queued=false;new MutationObserver(function(){if(!queued){queued=true;requestAnimationFrame(function(){queued=false;enhance();});}}).observe(document.documentElement,{childList:true,subtree:true});
  document.addEventListener('DOMContentLoaded',enhance);enhance();
}());
