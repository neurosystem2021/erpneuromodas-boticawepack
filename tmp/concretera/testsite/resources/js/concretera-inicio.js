(function () {
  'use strict';
  window.cxGuidedStart = function (x) {
    const {state, api, esc, dialog, refresh, render, admin, mayWrite, workForm, specForm, proposalForm, error} = x;
    const $ = id => document.getElementById(id);
    const catalog = {
      productos: {entity:'PRODUCTO', singular:'producto', create:'/views/Gen_ProductoForm.php', title:'Crear producto en el ERP', hint:'Registra lo que vendes: concreto premezclado y, si corresponde, bombeo o transporte. Usa tus nombres y unidades reales. Los materiales de fabricación se gestionarán en producción.'},
      almacenes: {entity:'ALMACEN', singular:'planta / almacén', create:'/nuevo/index.html#/gestion/almacenes', title:'Crear planta / almacén en el ERP', hint:'Registra el nombre y la dirección reales de la planta desde donde atenderás. No selecciones un almacén anterior solo para completar el formulario.'},
      clientes: {entity:'CLIENTE', singular:'cliente', create:'/views/V_VentaCliente.php', title:'Crear cliente en el ERP', hint:'Registra al cliente con su documento y razón social. La dirección de entrega y el receptor se completan después en la obra.'}
    };
    const button = (id, text, primary = false) => '<button type="button" id="'+id+'" class="cx-btn '+(primary?'cx-btn-primary':'')+'">'+text+'</button>';
    function steps() {
      return [
        {name:'Productos', done:state.products.length>0, title:'1. Registra qué vas a vender', text:catalog.productos.hint, kind:'productos'},
        {name:'Planta', done:state.warehouses.length>0, title:'2. Define desde dónde se atenderá', text:catalog.almacenes.hint, kind:'almacenes'},
        {name:'Cliente y obra', done:state.clients.length>0&&(state.data.obras||[]).length>0, title:'3. Indica quién compra y dónde se entrega', text:'Primero selecciona al cliente. Después crea su obra con dirección de entrega y contacto receptor.', kind:'clientes'},
        {name:'Características', done:(state.data.especificaciones||[]).length>0||state.products.some(p=>p.oferta&&p.oferta.clase==='CONCRETO'), title:'4. Define el concreto que ofrecerás', text:'Vincula tu producto con sus características comerciales: resistencia, asentamiento y descarga. Ingresa los datos reales aprobados para tu oferta; esta ficha no es una receta de fabricación.'},
        {name:'Proforma', done:(state.data.propuestas||[]).length>0, title:'5. Prepara tu primera proforma', text:'Elige la empresa emisora, la obra, el concreto, la cantidad y el precio. El cliente revisará esta propuesta antes de confirmar su pedido.'}
      ];
    }
    function ready() { return steps().slice(0,4).every(s=>s.done); }
    function openPending() {state.guideStep=null;state.tab='inicio';render();}
    function linkedList(kind) {
      const rows=kind==='productos'?state.products:kind==='almacenes'?state.warehouses:state.clients;
      if(!rows.length)return '<p class="cx-guide-missing">Todavía no hay '+(kind==='almacenes'?'plantas / almacenes':kind)+' vinculados a Wuanuko Mix.</p>';
      return '<ul class="cx-guide-linked">'+rows.slice(0,6).map(r=>'<li><strong>'+esc(r.nombre)+'</strong><span>'+esc(r.referencia||'')+'</span></li>').join('')+'</ul>';
    }
    function renderStart() {
      const all=steps(), first=all.findIndex(s=>!s.done), index=state.guideStep==null?(first<0?4:first):state.guideStep;
      const step=all[index], complete=all.slice(0,4).filter(s=>s.done).length;
      let html='<div class="cx-guide"><div class="cx-section-head"><div><span class="cx-kicker">EMPIEZA AQUÍ</span><h2>Tu proforma, paso a paso</h2><p>Completa estos datos una sola vez. Los dos emisores de Wuanuko Mix comparten esta preparación.</p></div>'+button('cxGuideRefresh','Actualizar datos')+'</div>';
      html+='<p class="cx-guide-progress" role="status">'+complete+' de 4 pasos de preparación completos</p><ol class="cx-guide-steps">'+all.map((s,i)=>'<li><button type="button" data-guide-step="'+i+'" '+(i===index?'aria-current="step"':'')+'><span>'+(s.done?'✓':i+1)+'</span><strong>'+s.name+'</strong><small>'+(s.done?'Listo':'Pendiente')+'</small></button></li>').join('')+'</ol>';
      html+='<section class="cx-guide-card" aria-labelledby="cxGuideTitle"><h3 id="cxGuideTitle">'+step.title+'</h3><p>'+step.text+'</p>';
      if(step.kind){
        html+=linkedList(step.kind);
        html+='<div class="cx-actions">'+(admin()?'<a class="cx-btn cx-btn-primary" target="_blank" rel="noopener" href="'+catalog[step.kind].create+'">'+catalog[step.kind].title+' ↗</a>'+button('cxGuidePick','Ya existe: seleccionarlo'):'<p class="cx-help">Administración debe vincular los datos del ERP a este grupo.</p>')+'</div>';
        if(admin())html+='<p class="cx-help">Si lo creas en el ERP, guarda el registro y vuelve a esta pestaña. Pulsa «Ya existe: seleccionarlo» para usarlo en Concretera.</p>';
      }
      if(index===2){
        const works=state.data.obras||[];
        if(works.length)html+='<p class="cx-guide-done">Obra registrada: '+esc(works[0].Nombre)+'</p>';
        if(mayWrite())html+=button('cxGuideWork',works.length?'Añadir otra obra':'Crear obra del cliente',true);
        if(!state.clients.length)html+='<p class="cx-help">Primero vincula al cliente con el botón de arriba.</p>';
      }
      if(index===3){
        const specs=state.data.especificaciones||[],offers=state.products.filter(p=>p.oferta);
        if(offers.length)html+='<p class="cx-guide-done">Catálogo comercial cargado. Puedes cotizar estos concretos por resistencia; slump, descarga y receta se definirán para el requerimiento de planta.</p><ul class="cx-guide-linked">'+offers.map(p=>'<li><strong>'+esc(p.nombre)+'</strong><span>'+esc(p.oferta.moneda)+' '+Number(p.oferta.precio).toFixed(2)+' / m³ sin IGV</span></li>').join('')+'</ul>';
        if(specs.length)html+='<p class="cx-guide-done">Características registradas: '+esc(specs[0].Nombre)+'</p>';
        if(!state.products.length)html+='<p class="cx-guide-missing">Completa primero el paso 1: Productos.</p>';
        if(mayWrite())html+=button('cxGuideSpec',specs.length?'Añadir otra especificación':'Registrar características del concreto',true);
      }
      if(index===4){
        const missing=all.slice(0,4).filter(s=>!s.done);
        html+=ready()?'<p class="cx-guide-done">Preparación lista. Puedes crear la proforma.</p>':'<p class="cx-guide-missing">Falta completar: '+missing.map(s=>s.name).join(', ')+'.</p>';
        html+='<div class="cx-guide-choice"><p><strong>Con cantidad definida:</strong> usa Presupuesto con metrado. Por ejemplo, los m³ que solicita el cliente.</p><p><strong>Solo consulta de precios:</strong> usa Tarifario por unidad. Las cantidades se confirman cuando el cliente acepta.</p></div>';
        if(mayWrite())html+=button('cxGuideProposal',ready()?'Crear proforma':'Ir al paso pendiente',true);
      }
      html+='<div class="cx-guide-nav">'+(index>0?button('cxGuideBack','← Paso anterior'):'')+(index<4?button('cxGuideNext','Siguiente paso →'):'')+'</div></section>';
      html+='<div class="cx-guide-flow"><strong>Después de la proforma</strong><p>Revisar y emitir la propuesta → aceptación del cliente → pedido de suministro.</p><small>Programación, producción, guía por carga y facturación del pedido se incorporan en las siguientes etapas.</small></div></div>';
      $('cxContent').innerHTML=html;
      document.querySelectorAll('[data-guide-step]').forEach(b=>b.onclick=()=>{state.guideStep=Number(b.dataset.guideStep);renderStart();});
      $('cxGuideRefresh').onclick=async()=>{try{state.guideStep=null;await refresh();}catch(e){error(e.message);}};
      if($('cxGuidePick'))$('cxGuidePick').onclick=()=>pick(step.kind);
      if($('cxGuideBack'))$('cxGuideBack').onclick=()=>{state.guideStep=index-1;renderStart();};
      if($('cxGuideNext'))$('cxGuideNext').onclick=()=>{state.guideStep=index+1;renderStart();};
      if($('cxGuideWork'))$('cxGuideWork').onclick=()=>{state.clients.length?workForm():pick('clientes');};
      if($('cxGuideSpec'))$('cxGuideSpec').onclick=()=>{state.products.length?specForm():(state.guideStep=0,renderStart());};
      if($('cxGuideProposal'))$('cxGuideProposal').onclick=()=>{ready()?proposalForm():openPending();};
    }
    function pick(kind) {
      if(!admin()){error('Administración debe vincular los datos de esta empresa.');return;}
      const c=catalog[kind];let request=0;
      dialog('Preparar '+c.singular, '<div class="cx-guide-picker"><p>'+c.hint+'</p><div class="cx-guide-create"><strong>¿Todavía no existe?</strong><p>Ábrelo en el ERP, pulsa Nuevo y guarda el registro. Luego vuelve aquí para buscarlo y vincularlo.</p><a class="cx-btn cx-btn-primary" target="_blank" rel="noopener" href="'+c.create+'">'+c.title+' ↗</a></div><form id="cxGuideSearch" class="cx-actions"><label class="cx-field">Buscar por nombre, documento o código<input id="cxGuideQuery" autocomplete="off" placeholder="'+(kind==='productos'?'Ej.: concreto':kind==='clientes'?'Nombre o RUC del cliente':'Nombre de tu planta')+'" '+(kind==='almacenes'?'':'minlength="2" required')+'></label><button class="cx-btn">Buscar / actualizar</button></form><p class="cx-help">Solo se vincula el registro que elijas. No se copia todo el catálogo anterior.</p><div id="cxGuideResults" role="status">'+(kind==='almacenes'?'Cargando plantas…':'Escribe al menos dos letras para buscar.')+'</div></div>');
      const search=async()=>{
        const token=++request, scope=state.company.id, results=$('cxGuideResults');
        results.textContent='Buscando…';
        try{
          const rows=await api('catalog-candidates',{kind,q:$('cxGuideQuery').value});
          if(token!==request||scope!==state.company.id||!results.isConnected)return;
          results.innerHTML=rows.length?'<ul class="cx-guide-results">'+rows.map(r=>'<li><div><strong>'+esc(r.nombre)+'</strong><small>'+esc(r.referencia||'Sin referencia adicional')+'</small></div><button type="button" class="cx-btn" data-guide-link="'+esc(r.id)+'" '+(Number(r.vinculado)?'disabled':'')+'>'+(Number(r.vinculado)?'Ya vinculado':'Usar este registro')+'</button></li>').join('')+'</ul>':'<p>No hay coincidencias disponibles. Revisa el nombre o crea el registro con el botón de arriba.</p>';
          results.querySelectorAll('[data-guide-link]').forEach(b=>b.onclick=async()=>{
            if(state.busy)return;state.busy=true;b.disabled=true;
            try{await api('reference',{entidad:c.entity,idERP:b.dataset.guideLink},true);state.guideStep=null;await refresh();$('cxDialog').close();}
            catch(e){b.disabled=false;error(e.message);}finally{state.busy=false;}
          });
        }catch(e){if(token===request&&results.isConnected){results.textContent=e.message;error(e.message);}}
      };
      $('cxGuideSearch').onsubmit=e=>{e.preventDefault();search();};
      if(kind==='almacenes')search();else $('cxGuideQuery').focus();
    }
    function proposalSteps(form, beforeNext=async()=>{}) {
      const panels=[form.querySelector(':scope > .cx-form-grid'),...form.querySelectorAll(':scope > .cx-form-section')];
      const names=['Cliente y obra','Productos y m³','Condiciones','Pago y ver proforma'];
      if(panels.length!==4)return;
      form.noValidate=true;let current=0;
      const nav=document.createElement('nav');nav.className='cx-proposal-steps';nav.setAttribute('aria-label','Pasos de la proforma');
      nav.innerHTML=names.map((name,i)=>'<button type="button" data-proposal-step="'+i+'">'+(i+1)+'. '+name+'</button>').join('');
      form.prepend(nav);
      const controls=document.createElement('div');controls.className='cx-guide-nav';
      controls.innerHTML=button('cxProposalBack','← Anterior')+button('cxProposalNext','Continuar →',true);
      const actions=form.querySelector('.cx-form-actions');actions.before(controls);
      const submit=actions.querySelector('[type=submit]');
      const validPanel=i=>[...panels[i].querySelectorAll('input,select,textarea')].find(el=>!el.disabled&&!el.checkValidity());
      const show=i=>{current=i;panels.forEach((p,n)=>{p.hidden=n!==i;});nav.querySelectorAll('button').forEach((b,n)=>b.setAttribute('aria-current',n===i?'step':'false'));$('cxProposalBack').hidden=i===0;$('cxProposalNext').hidden=i===3;submit.hidden=i!==3;$('cxDialog').scrollTop=0;};
      let moving=false;const forward=async i=>{if(moving||state.busy)return;if(i>current){for(let n=0;n<i;n++){const invalid=validPanel(n);if(invalid){show(n);invalid.reportValidity();return;}}moving=true;$('cxProposalNext').disabled=true;try{await beforeNext();}catch(e){error(e.message);return;}finally{moving=false;$('cxProposalNext').disabled=false;}}show(i);};
      nav.querySelectorAll('[data-proposal-step]').forEach(b=>b.onclick=()=>forward(Number(b.dataset.proposalStep)));
      $('cxProposalBack').onclick=()=>show(Math.max(0,current-1));$('cxProposalNext').onclick=()=>forward(Math.min(3,current+1));
      form.addEventListener('submit',e=>{
        // Enter advances the current step; it never saves an unfinished proposal.
        if(current<3){e.preventDefault();e.stopImmediatePropagation();forward(current+1);return;}
        for(let i=0;i<panels.length;i++){const invalid=validPanel(i);if(invalid){e.preventDefault();e.stopImmediatePropagation();show(i);invalid.reportValidity();return;}}
      },true);
      show(0);
    }
    return {render:renderStart, ready, openPending, pick, proposalSteps};
  };
}());
