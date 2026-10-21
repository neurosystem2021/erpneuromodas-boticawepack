(function () {
  'use strict';
  // Loaded by the authorized POS wrapper. No Vue internals or sale data changed.
  window.cxAttachProformaLauncher = function (iframe) {
    const doc = iframe.contentDocument;
    if (!doc || doc.documentElement.dataset.cxProformaLauncher) return;
    doc.documentElement.dataset.cxProformaLauncher = '1';
    const link = doc.createElement('link');
    link.rel = 'stylesheet'; link.href = '/resources/css/concretera-pos.css?v=1'; doc.head.appendChild(link);
    let bypass = null, trigger = null, generation = 0;
    const modal = doc.createElement('dialog');
    modal.className = 'cx-pos-launcher'; modal.setAttribute('aria-labelledby', 'cxPosTitle');
    doc.body.appendChild(modal);
    const element = (tag, text, className) => {
      const node = doc.createElement(tag); node.textContent = text;
      if (className) node.className = className; return node;
    };
    function shell() {
      modal.textContent = '';
      const header = element('header', '');
      const title = element('h2', 'Crear proforma'); title.id = 'cxPosTitle'; header.appendChild(title);
      const close = element('button', '×', 'cx-pos-close'); close.type = 'button'; close.setAttribute('aria-label', 'Cerrar');
      close.onclick = () => modal.close(); header.appendChild(close); modal.appendChild(header);
      const content = element('section', ''); modal.appendChild(content);
      const footer = element('footer', '');
      const commercial = element('button', 'Proforma de productos comerciales', 'cx-pos-commercial'); commercial.type = 'button';
      commercial.onclick = () => { const button = trigger; modal.close(); if (button && button.isConnected) { bypass = button; button.click(); bypass = null; } };
      footer.appendChild(commercial); modal.appendChild(footer); return content;
    }
    modal.addEventListener('close', () => { generation++; if (trigger && trigger.isConnected) trigger.focus(); });
    async function choose() {
      const ticket = ++generation, content = shell();
      content.appendChild(element('p', 'Consultando las empresas autorizadas…', 'cx-pos-status'));
      if (!modal.open) modal.showModal();
      try {
        const response = await fetch('/api/concretera.php?action=context', {credentials: 'same-origin', cache: 'no-store'});
        if (!response.ok) throw Error('No se pudieron consultar las empresas. Revise su sesión y permisos.');
        const context = await response.json();
        if (ticket !== generation || !modal.open) return;
        const companies = (context.empresas || []).filter(c => c.activo && ['ADMIN', 'COMERCIAL', 'VENTAS'].includes(c.rol));
        const issuers = companies.flatMap(c => (c.emisores || []).map(e => ({company: c, issuer: e})));
        content.textContent = '';
        if (!issuers.length) { content.appendChild(element('p', 'No tiene empresas habilitadas para crear proformas de concretera.', 'cx-pos-status')); return; }
        content.appendChild(element('p', 'PROFORMA DE CONCRETERA', 'cx-pos-kicker'));
        content.appendChild(element('h3', 'Elige la empresa que proforma y facturará'));
        content.appendChild(element('p', 'Se abrirá el formulario por obra con la empresa seleccionada. La venta actual permanece en esta pestaña.', 'cx-pos-help'));
        const storageKey = 'cxPreferredIssuer:' + context.usuario;
        let preferred = null; try { preferred = localStorage.getItem(storageKey); } catch (_) {}
        const choices = element('div', '', 'cx-pos-choices'); content.appendChild(choices);
        const form = element('form', '');
        for (const [index, item] of issuers.entries()) {
          const label = element('label', '', 'cx-pos-choice'), input = doc.createElement('input');
          input.type = 'radio'; input.name = 'emisor'; input.value = String(item.issuer.IDEMPRESA);
          input.checked = preferred ? input.value === preferred : index === 0;
          const detail = element('span', '');
          detail.appendChild(element('strong', item.issuer.RAZONSOCIAL));
          detail.appendChild(element('small', 'RUC ' + item.issuer.RUC));
          label.appendChild(input); label.appendChild(detail); choices.appendChild(label);
        }
        if (!choices.querySelector(':checked')) choices.querySelector('input').checked = true;
        form.appendChild(choices);
        const submit = element('button', 'Continuar con esta empresa', 'cx-pos-continue'); submit.type = 'submit'; form.appendChild(submit);
        form.onsubmit = event => {
          event.preventDefault();
          const id = form.querySelector(':checked').value;
          try { localStorage.setItem(storageKey, id); } catch (_) {}
          const url = '/views/Concretera.php?' + new URLSearchParams({empresaEmisora: id, nueva: '1'});
          const opened = window.open(url, '_blank');
          if (opened) { opened.opener = null; modal.close(); }
          else { const warning = element('p', 'El navegador bloqueó la nueva pestaña. Permita ventanas emergentes y vuelva a continuar.', 'cx-pos-error'); form.appendChild(warning); }
        };
        content.appendChild(form);
      } catch (err) {
        if (ticket !== generation || !modal.open) return;
        content.textContent = ''; content.appendChild(element('p', err.message, 'cx-pos-error'));
        const retry = element('button', 'Volver a consultar', 'cx-pos-commercial'); retry.type = 'button'; retry.onclick = choose; content.appendChild(retry);
      }
    }
    doc.addEventListener('click', event => {
      const button = event.target.closest('button.pos-action-proforma,button.pos-mobile-action-proforma');
      if (!button || button === bypass || button.disabled) return;
      event.preventDefault(); event.stopImmediatePropagation(); trigger = button;
      if (!modal.open) choose();
    }, true);
  };
}());
