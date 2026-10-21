(function () {
  'use strict';
  window.cxQuoteCustomer = function ({state, api, esc, admin, error}) {
    return function attach(form, copy) {
      if (copy) return async () => {};
      const panel=form.querySelector(':scope > .cx-form-grid'), work=form.elements.idObra;
      const customer=document.createElement('div');customer.className='cx-quote-customer wide';
      customer.innerHTML='<h3>¿Para quién es la cotización?</h3><label class="cx-field">Cliente<select name="quoteClient" required></select></label><div class="cx-actions">'+(admin()?'<button type="button" id="cxQuoteFind" class="cx-btn">Buscar cliente por nombre o RUC</button>':'')+'<a class="cx-btn" href="/views/V_VentaCliente.php" target="_blank" rel="noopener">Registrar cliente nuevo ↗</a></div><div id="cxQuoteSearch" hidden><label class="cx-field">Nombre o documento<input id="cxQuoteQuery" placeholder="Escribe al menos 2 caracteres" autocomplete="off"></label><button type="button" id="cxQuoteSearchButton" class="cx-btn">Buscar</button><div id="cxQuoteResults" role="status"></div></div>';
      panel.prepend(customer);
      const clients=form.elements.quoteClient, $=id=>form.querySelector('#'+id);
      const clientOptions=selected=>{clients.innerHTML='<option value="">Selecciona al cliente</option>'+state.clients.map(c=>'<option value="'+esc(c.id)+'">'+esc(c.nombre+' · '+c.referencia)+'</option>').join('');if(selected)clients.value=String(selected);else if(state.clients.length===1)clients.value=String(state.clients[0].id);};
      clientOptions();
      const details=document.createElement('div');details.className='cx-quote-work wide';
      details.innerHTML='<h3>Datos de la obra o lugar de entrega</h3><div class="cx-form-grid"><label class="cx-field">Nombre de la obra<input name="quoteWorkName" required placeholder="Ej.: Veredas de la obra"></label><label class="cx-field">Dirección de entrega<input name="quoteWorkAddress" required placeholder="Calle, número y referencia"></label><label class="cx-field">Persona de contacto<input name="quoteWorkContact" required></label><label class="cx-field">Teléfono de contacto<input name="quoteWorkPhone" type="tel"></label></div><p class="cx-help">La obra se registrará al continuar. Puedes usarla en las siguientes cotizaciones.</p>';
      work.closest('label').after(details);
      let draftCode='';
      function showWorkDetails(){const isNew=work.value==='__new__';details.hidden=!isNew;details.querySelectorAll('input').forEach(i=>i.disabled=!isNew);}
      function worksForClient(selected){
        const rows=(state.data.obras||[]).filter(w=>String(w.IdCliente)===clients.value);
        work.innerHTML='<option value="">Selecciona una obra</option>'+rows.map(w=>'<option value="'+esc(w.IdObra)+'">'+esc(w.Nombre+' · '+w.Direccion)+'</option>').join('')+(clients.value?'<option value="__new__">Registrar una obra nueva aquí</option>':'');
        work.value=selected?String(selected):rows.length===1?String(rows[0].IdObra):clients.value?'__new__':'';
        showWorkDetails();
      }
      clients.onchange=()=>{draftCode='';worksForClient();};work.addEventListener('change',showWorkDetails);worksForClient();
      if(admin()){
        $('cxQuoteFind').onclick=()=>{$('cxQuoteSearch').hidden=false;$('cxQuoteQuery').focus();};
        let request=0;
        const search=async()=>{
          const query=$('cxQuoteQuery').value.trim(), results=$('cxQuoteResults'), ticket=++request;
          if(query.length<2){results.textContent='Escribe al menos 2 caracteres del nombre o documento.';return;}
          results.textContent='Buscando clientes…';
          try{
            const candidates=await api('catalog-candidates',{kind:'clientes',q:query});
            if(ticket!==request||!results.isConnected)return;
            results.innerHTML=candidates.length?'<ul class="cx-guide-results">'+candidates.map(c=>'<li><div><strong>'+esc(c.nombre)+'</strong><small>'+esc(c.referencia)+'</small></div><button type="button" class="cx-btn" data-quote-client="'+esc(c.id)+'">Seleccionar</button></li>').join('')+'</ul>':'No se encontró al cliente. Puedes registrarlo con «Registrar cliente nuevo» y volver a buscarlo aquí.';
            results.querySelectorAll('[data-quote-client]').forEach(button=>button.onclick=async()=>{
              if(state.busy)return;state.busy=true;button.disabled=true;
              try{
                const id=button.dataset.quoteClient;
                if(!state.clients.some(c=>String(c.id)===id))await api('reference',{entidad:'CLIENTE',idERP:id},true);
                const chosen=candidates.find(c=>String(c.id)===id);if(!state.clients.some(c=>String(c.id)===id))state.clients.push({id:chosen.id,nombre:chosen.nombre,referencia:chosen.referencia});clientOptions(id);draftCode='';worksForClient();$('cxQuoteSearch').hidden=true;
              }catch(e){button.disabled=false;error(e.message);}finally{state.busy=false;}
            });
          }catch(e){results.textContent=e.message;}
        };
        $('cxQuoteSearchButton').onclick=search;$('cxQuoteQuery').onkeydown=e=>{if(e.key==='Enter'){e.preventDefault();search();}};
      }
      return async function ensureWork(){
        if(work.value!=='__new__')return;
        const payload={idCliente:clients.value,codigo:draftCode||(draftCode='OB-'+Date.now().toString(36)+'-'+Math.random().toString(36).slice(2,8)),nombre:form.elements.quoteWorkName.value,direccion:form.elements.quoteWorkAddress.value,contacto:form.elements.quoteWorkContact.value,telefono:form.elements.quoteWorkPhone.value};
        state.busy=true;
        try{
          let saved;
          try{saved=await api('work',payload,true);}catch(e){
            const rows=await api('list',{kind:'obras'});
            saved=rows.find(w=>w.Codigo===payload.codigo&&String(w.IdCliente)===String(payload.idCliente)&&w.Nombre===payload.nombre&&w.Direccion===payload.direccion&&w.Contacto===payload.contacto&&String(w.Telefono||'')===payload.telefono);
            if(!saved)throw e;
          }
          state.data.obras=(state.data.obras||[]).filter(w=>String(w.IdObra)!==String(saved.IdObra));state.data.obras.push(saved);worksForClient(saved.IdObra);
        }finally{state.busy=false;}
      };
    };
  };
}());
