(function () {
  'use strict';

  /* El componente antiguo no cancela consultas de modelos. Si una respuesta
     lenta llega al final, reemplaza resultados nuevos. Conservamos solo la
     solicitud más reciente para que lo visible siempre corresponda al texto. */
  if (!window.nmModelRequestGuardInstalled && window.XMLHttpRequest) {
    window.nmModelRequestGuardInstalled = true;
    var NativeXhrOpen = XMLHttpRequest.prototype.open;
    var NativeXhrSend = XMLHttpRequest.prototype.send;
    var activeModelRequest = null;
    XMLHttpRequest.prototype.open = function (method, url) {
      this.nmIsModelSearch = String(url || '').indexOf('/productos/modelos') !== -1;
      return NativeXhrOpen.apply(this, arguments);
    };
    XMLHttpRequest.prototype.send = function () {
      if (this.nmIsModelSearch) {
        if (activeModelRequest && activeModelRequest !== this && activeModelRequest.readyState !== 4) {
          try { activeModelRequest.abort(); } catch (ignore) {}
        }
        activeModelRequest = this;
        this.addEventListener('loadend', function () {
          if (activeModelRequest === this) activeModelRequest = null;
        });
      }
      return NativeXhrSend.apply(this, arguments);
    };
  }

  var previewId = 'pos-image-hover-preview-live';

  function hidePreview() {
    var preview = document.getElementById(previewId);
    if (preview) preview.remove();
  }

  function removeDuplicatePreview() {
    var duplicate = document.getElementById('pos-image-hover-preview');
    if (duplicate) duplicate.remove();
  }

  function showPreview(image) {
    hidePreview();
    removeDuplicatePreview();
    if (!image || !(image.currentSrc || image.src)) return;

    var preview = document.createElement('img');
    preview.id = previewId;
    preview.src = image.currentSrc || image.src;
    preview.alt = 'Vista ampliada del producto';
    preview.style.cssText = [
      'position:fixed',
      'width:300px',
      'height:300px',
      'object-fit:contain',
      'box-sizing:border-box',
      'padding:8px',
      'background:#fff',
      'border:2px solid #1597c1',
      'border-radius:14px',
      'box-shadow:0 18px 45px rgba(3,40,70,.35)',
      'pointer-events:none',
      'z-index:2147483647'
    ].join(';');

    document.body.appendChild(preview);

    var rect = image.getBoundingClientRect();
    var left = rect.left - 316;
    if (left < 12) left = Math.min(window.innerWidth - 312, rect.right + 12);
    var top = Math.max(12, Math.min(window.innerHeight - 312, rect.top - 120));

    preview.style.left = left + 'px';
    preview.style.top = top + 'px';
  }

  window.showPosProductImagePreview = showPreview;
  window.hidePosProductImagePreview = hidePreview;

  new MutationObserver(function () {
    removeDuplicatePreview();
  }).observe(document.documentElement, { childList: true, subtree: true });

  document.addEventListener('mouseover', function (event) {
    var image = event.target.closest && event.target.closest('img.imagen, img.pos-product-hover-image');
    if (image) showPreview(image);
  }, true);

  document.addEventListener('mouseout', function (event) {
    var image = event.target.closest && event.target.closest('img.imagen, img.pos-product-hover-image');
    if (image && (!event.relatedTarget || !image.contains(event.relatedTarget))) hidePreview();
  }, true);

  /* El componente Vue antiguo abre al hacer clic un modal Bootstrap de ancho
     completo, pero solo inserta la miniatura de 40 px. El resultado es una
     ventana casi vacía que tapa el punto de venta. Interceptamos exclusivamente
     las imágenes de producto y usamos el visor ligero de arriba. */
  document.addEventListener('click', function (event) {
    var image = event.target.closest && event.target.closest('img.imagen, img.pos-product-hover-image');
    if (!image) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    hidePreview();
    var productRow = image.closest('tr');
    if (productRow) {
      productRow.dispatchEvent(new MouseEvent('click', {
        bubbles: true,
        cancelable: true,
        view: window
      }));
    }
  }, true);

  document.addEventListener('dblclick', function (event) {
    var image = event.target.closest && event.target.closest('img.imagen, img.pos-product-hover-image');
    if (!image) return;
    event.preventDefault();
    event.stopImmediatePropagation();
  }, true);
})();

(function compactProductRowActions() {
  'use strict';
  if (window.__compactProductRowActions) return;
  window.__compactProductRowActions = true;

  var style = document.createElement('style');
  style.id = 'nm-compact-product-row-actions';
  style.textContent =
    '.pos-modern.pos-modern .pos-row-actions{min-width:68px!important;gap:5px!important}' +
    '.pos-modern.pos-modern .pos-row-actions .btn{width:31px!important;height:31px!important;min-width:31px!important;min-height:31px!important;padding:0!important;border-radius:9px!important;box-shadow:0 4px 9px rgba(15,35,65,.16),inset 0 1px 0 rgba(255,255,255,.26)!important}' +
    '.pos-modern.pos-modern .pos-row-actions .btn i{margin:0!important;font-size:12px!important;line-height:1!important}';
  document.head.appendChild(style);
})();

(function keepLastTicketButtonVisible() {
  'use strict';
  if (window.__lastTicketButtonVisible) return;
  window.__lastTicketButtonVisible = true;
  var style = document.createElement('style');
  style.id = 'nm-last-ticket-button-visible';
  style.textContent =
    '.pos-floating-documents .pos-last-ticket{' +
      'display:inline-flex!important;width:44px!important;height:44px!important;' +
      'min-width:44px!important;min-height:44px!important;padding:0!important;' +
      'align-items:center!important;justify-content:center!important;' +
      'border:2px solid #fff!important;border-radius:50%!important;' +
      'background:linear-gradient(135deg,#1688f0,#075dcc)!important;' +
      'box-shadow:0 8px 20px rgba(7,93,204,.42),0 0 0 3px rgba(22,136,240,.16)!important;' +
      'visibility:visible!important;opacity:1!important}' +
    '.pos-floating-documents .pos-last-ticket i{display:inline-flex!important;margin:0!important;color:#fff!important;font-size:15px!important;visibility:visible!important;opacity:1!important}';
  document.head.appendChild(style);
})();

(function compactDocumentsModalHeader() {
  'use strict';
  if (window.__compactDocumentsModalHeader) return;
  window.__compactDocumentsModalHeader = true;
  var style = document.createElement('style');
  style.id = 'nm-compact-documents-modal-header';
  style.textContent =
    '.pos-documents-modal .pos-documents-heading{display:none!important}' +
    '.pos-documents-modal .modal-body{padding:0!important}' +
    '.pos-documents-modal .pos-documents-frame{height:calc(100vh - 125px)!important;min-height:620px!important}';
  document.head.appendChild(style);
})();

(function enableLastReceiptViewer() {
  'use strict';
  if (window.__lastReceiptViewerEnabled) return;
  window.__lastReceiptViewerEnabled = true;

  var style = document.createElement('style');
  style.id = 'nm-last-receipt-viewer-style';
  style.textContent =
    '.nm-ticket-viewer{position:fixed;z-index:2147483600;inset:0;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(3,18,35,.72);backdrop-filter:blur(5px)}' +
    '.nm-ticket-viewer.is-open{display:flex}' +
    '.nm-ticket-viewer__panel{display:flex;width:min(520px,96vw);height:min(880px,94vh);overflow:hidden;flex-direction:column;border:1px solid rgba(255,255,255,.3);border-radius:18px;background:#eef3f7;box-shadow:0 30px 85px rgba(0,0,0,.42)}' +
    '.nm-ticket-viewer__head{display:flex;min-height:58px;padding:0 12px 0 18px;align-items:center;color:#fff;background:linear-gradient(135deg,#075dcc,#1688f0)}' +
    '.nm-ticket-viewer__head i{margin-right:9px}.nm-ticket-viewer__head strong{font-size:15px}' +
    '.nm-ticket-viewer__actions{display:flex;margin-left:auto;align-items:center;gap:8px}' +
    '.nm-ticket-viewer__print{display:inline-flex;height:36px;padding:0 13px;align-items:center;gap:7px;border:1px solid rgba(255,255,255,.4);border-radius:10px;color:#075dcc;background:#fff;font-size:12px;font-weight:850;cursor:pointer;box-shadow:0 5px 12px rgba(0,34,90,.18)}' +
    '.nm-ticket-viewer__print:hover{background:#eaf5ff;transform:translateY(-1px)}' +
    '.nm-ticket-viewer__print i{margin:0}' +
    '.nm-ticket-viewer__close{display:grid;width:36px;height:36px;padding:0;place-items:center;border:1px solid rgba(255,255,255,.35);border-radius:50%;color:#fff;background:rgba(255,255,255,.12);font-size:17px;cursor:pointer}' +
    '.nm-ticket-viewer iframe{width:100%;height:100%;border:0;background:#fff}';
  document.head.appendChild(style);

  function getViewer() {
    var viewer = document.getElementById('nm-ticket-viewer');
    if (viewer) return viewer;
    viewer = document.createElement('div');
    viewer.id = 'nm-ticket-viewer';
    viewer.className = 'nm-ticket-viewer';
    viewer.innerHTML = '<section class="nm-ticket-viewer__panel" role="dialog" aria-modal="true" aria-label="Vista previa de la ultima boleta">' +
      '<header class="nm-ticket-viewer__head"><i class="fas fa-receipt"></i><strong>Vista previa de la ultima boleta</strong><div class="nm-ticket-viewer__actions"><button type="button" class="nm-ticket-viewer__print"><i class="fas fa-print"></i> Imprimir</button><button type="button" class="nm-ticket-viewer__close" aria-label="Cerrar"><i class="fas fa-times"></i></button></div></header>' +
      '<iframe title="Ultima boleta"></iframe></section>';
    viewer.addEventListener('click', function(event) {
      if (event.target.closest('.nm-ticket-viewer__print')) {
        var frame = viewer.querySelector('iframe');
        if (frame && frame.contentWindow) {
          frame.contentWindow.focus();
          frame.contentWindow.print();
        }
        return;
      }
      if (event.target === viewer || event.target.closest('.nm-ticket-viewer__close')) {
        viewer.classList.remove('is-open');
        viewer.querySelector('iframe').src = 'about:blank';
      }
    });
    document.body.appendChild(viewer);
    return viewer;
  }

  document.addEventListener('click', function(event) {
    var button = event.target && event.target.closest ? event.target.closest('.pos-last-ticket') : null;
    if (!button) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    var saleId = parseInt(window.localStorage.getItem('ultimaVentaId') || '0', 10) || 0;
    if (!saleId) {
      window.alert('No existe una venta reciente para mostrar.');
      return;
    }
    var viewer = getViewer();
    viewer.querySelector('iframe').src = '/imprimir/index.php?IdDocVenta=' + encodeURIComponent(saleId) + '&preview=1&popup=1&manualViewer=1';
    viewer.classList.add('is-open');
  }, true);
})();

(function enableInAppReceiptPreview() {
  'use strict';
  if (window.__inAppReceiptPreviewEnabled) return;
  window.__inAppReceiptPreviewEnabled = true;

  function saleIdFromLink(link) {
    try { return new URL(link.href, location.origin).searchParams.get('IdDocVenta') || ''; }
    catch (error) { return ''; }
  }

  function openPreview(link) {
    var id = saleIdFromLink(link);
    if (!id) return;
    var old = document.getElementById('nm-in-app-receipt');
    if (old) old.remove();
    var layer = document.createElement('div');
    layer.id = 'nm-in-app-receipt';
    layer.style.cssText = 'position:fixed;z-index:2147483647;inset:0;display:flex;flex-direction:column;background:#efe9ec';
    if (!document.getElementById('nm-receipt-responsive-style')) {
      var responsiveStyle = document.createElement('style');
      responsiveStyle.id = 'nm-receipt-responsive-style';
      responsiveStyle.textContent = '#nm-in-app-receipt [data-desktop-tools]{display:none}' +
        '@media(min-width:769px){#nm-in-app-receipt [data-receipt-head]{display:none!important}' +
        '#nm-in-app-receipt [data-desktop-tools]{position:absolute;z-index:3;top:18px;right:22px;display:flex!important;gap:9px}' +
        '#nm-in-app-receipt [data-desktop-tools] button{height:46px;padding:0 18px;border:1px solid #d8e2eb;border-radius:13px;color:#fff;background:linear-gradient(135deg,#123a62,#078fbd);box-shadow:0 10px 25px #123a6238;font-size:13px;font-weight:900;cursor:pointer}' +
        '#nm-in-app-receipt iframe{height:100vh!important}}';
      document.head.appendChild(responsiveStyle);
    }
    var previewUrl = new URL(link.href, location.origin);
    previewUrl.searchParams.set('preview', '1');
    layer.innerHTML = '<div data-receipt-head style="display:flex;align-items:center;gap:9px;padding:10px 12px;color:#fff;background:linear-gradient(110deg,#28181f,#a71938 65%,#ef5b48)">' +
      '<button data-close style="width:40px;height:40px;border:1px solid #ffffff44;border-radius:12px;color:#fff;background:#ffffff18;font-size:18px">&#8592;</button>' +
      '<div style="flex:1"><b style="display:block">Comprobante #' + id + '</b><small>Vista previa dentro de Neuro App</small></div>' +
      '<button data-print style="height:40px;padding:0 14px;border:1px solid #ffffff44;border-radius:12px;color:#fff;background:#ffffff18;font-weight:800">Imprimir</button></div>' +
      '<div data-desktop-tools><button data-local-print type="button">&#128424; Imprimir en este equipo</button><button data-desktop-close type="button">&#10005; Cerrar</button></div>' +
      '<iframe title="Vista previa del comprobante" src="' + previewUrl.href + '" style="width:100%;height:100%;flex:1;border:0;background:#fff"></iframe>';
    document.body.appendChild(layer);
    layer.querySelector('[data-close]').onclick = function () { layer.remove(); };
    layer.querySelector('[data-desktop-close]').onclick = function () { layer.remove(); };
    layer.querySelector('[data-local-print]').onclick = function () {
      var frame = layer.querySelector('iframe');
      try { frame.contentWindow.focus(); frame.contentWindow.print(); }
      catch (error) { alert('No se pudo abrir la impresora local.'); }
    };
    layer.querySelector('[data-print]').onclick = function () {
      fetch('/api/index.php/empresa/id/1', { credentials:'same-origin' })
        .then(function (response) { if (!response.ok) throw new Error('No se pudo obtener la configuración de impresión.'); return response.json(); })
        .then(function (empresa) { var token=empresa.APPTOKEN||empresa.AppToken||''; if(!token)throw new Error('Falta configurar el token de impresión.'); window.location.href='neuroprinterred://factura.vip?iddocventa='+encodeURIComponent(id)+'&token='+encodeURIComponent(token); })
        .catch(function (error) { alert(error.message); });
    };
  }

  document.addEventListener('click', function (event) {
    var link = event.target.closest && event.target.closest('a[href*="/imprimir/index.php?IdDocVenta="]');
    if (!link) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    openPreview(link);
  }, true);
})();

(function notifyImmediateSunatDelivery() {
  'use strict';
  function install() {
    if (window.location.hash.indexOf('ventas') === -1) return;
    var nodes = document.querySelectorAll('*');
    for (var i = 0; i < nodes.length; i += 1) {
      var vm = nodes[i].__vue__;
      if (!vm || typeof vm.guardarVenta !== 'function' || !vm.axios || vm.axios.__sunatImmediateNotice) continue;
      var originalPost = vm.axios.post.bind(vm.axios);
      vm.axios.post = function (url, data, config) {
        return originalPost(url, data, config).then(function (response) {
          var endpoint = String(url || '').replace(/[?#].*$/, '').replace(/\/$/, '');
          if (/\/ventas$/.test(endpoint) && response && response.data && response.data.insertId && response.data.envioSunatMensaje) {
            if (response.data.envioSunatAutomatico) vm.$toasted.success(response.data.envioSunatMensaje);
            else vm.$toasted.show(response.data.envioSunatMensaje);
          }
          return response;
        });
      };
      vm.axios.__sunatImmediateNotice = true;
      break;
    }
  }
  var queued = false;
  new MutationObserver(function () {
    if (!queued) {
      queued = true;
      requestAnimationFrame(function () { queued = false; install(); });
    }
  }).observe(document.documentElement, { childList: true, subtree: true });
  document.addEventListener('DOMContentLoaded', install);
  install();
})();

(function salesExportSelector() {
  'use strict';

  function isSalesRegister() {
    return window.location.hash.indexOf('ReporteRegistroVentas') !== -1;
  }

  function findReportVm(vm) {
    if (!vm) return null;
    if (typeof vm.exportarReporte === 'function' && Array.isArray(vm.tiposDocumento)) return vm;
    var children = vm.$children || [];
    for (var i = 0; i < children.length; i += 1) {
      var found = findReportVm(children[i]);
      if (found) return found;
    }
    return null;
  }

  function closeModal() {
    var modal = document.getElementById('nm-export-modal');
    if (modal) modal.classList.remove('is-open', 'is-loading');
    document.body.classList.remove('nm-modal-open');
  }

  function updateCount(modal) {
    var count = modal.querySelectorAll('input[name="nm-export-type"]:checked').length;
    modal.querySelector('[data-export-count]').textContent = count + (count === 1 ? ' comprobante seleccionado' : ' comprobantes seleccionados');
    modal.querySelector('[data-export-confirm]').disabled = count === 0;
  }

  function ensureModal() {
    var modal = document.getElementById('nm-export-modal');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.id = 'nm-export-modal';
    modal.className = 'nm-export-modal';
    modal.innerHTML = '<div class="nm-export-backdrop" data-export-close></div>' +
      '<section class="nm-export-dialog" role="dialog" aria-modal="true" aria-labelledby="nm-export-title">' +
        '<header><span class="nm-export-icon"><i class="fas fa-file-excel"></i></span><div><small>EXPORTACI\u00d3N PERSONALIZADA</small><h2 id="nm-export-title">Seleccione los comprobantes</h2><p>Puede combinar uno, dos o los tres tipos en un solo reporte.</p></div><button type="button" class="nm-export-close" data-export-close aria-label="Cerrar">&times;</button></header>' +
        '<div class="nm-export-options">' +
          '<label class="nm-export-option is-receipt"><input type="checkbox" name="nm-export-type" value="03"><span class="nm-check"><i class="fas fa-check"></i></span><span class="nm-doc-icon"><i class="fas fa-receipt"></i></span><span><b>Boletas</b><small>Boletas electr\u00f3nicas</small></span></label>' +
          '<label class="nm-export-option is-invoice"><input type="checkbox" name="nm-export-type" value="01"><span class="nm-check"><i class="fas fa-check"></i></span><span class="nm-doc-icon"><i class="fas fa-file-invoice-dollar"></i></span><span><b>Facturas</b><small>Facturas electr\u00f3nicas</small></span></label>' +
          '<label class="nm-export-option is-note"><input type="checkbox" name="nm-export-type" value="12"><span class="nm-check"><i class="fas fa-check"></i></span><span class="nm-doc-icon"><i class="fas fa-sticky-note"></i></span><span><b>Notas de venta</b><small>Documentos internos</small></span></label>' +
        '</div>' +
        '<div class="nm-export-selection"><button type="button" data-export-all><i class="fas fa-check-double"></i> Seleccionar todos</button><span data-export-count>3 comprobantes seleccionados</span></div>' +
        '<footer><button type="button" class="nm-export-cancel" data-export-close>Cancelar</button><button type="button" class="nm-export-confirm" data-export-confirm><i class="fas fa-download"></i><span>Generar Excel</span></button></footer>' +
      '</section>';
    document.body.appendChild(modal);
    modal.querySelectorAll('[data-export-close]').forEach(function (el) { el.addEventListener('click', closeModal); });
    modal.querySelectorAll('input[name="nm-export-type"]').forEach(function (el) { el.addEventListener('change', function () { updateCount(modal); }); });
    modal.querySelector('[data-export-all]').addEventListener('click', function () {
      var boxes = modal.querySelectorAll('input[name="nm-export-type"]');
      var shouldCheck = Array.prototype.some.call(boxes, function (box) { return !box.checked; });
      boxes.forEach(function (box) { box.checked = shouldCheck; });
      updateCount(modal);
    });
    modal.querySelector('[data-export-confirm]').addEventListener('click', function () {
      var vm = findReportVm(document.getElementById('app').__vue__);
      var codes = Array.prototype.map.call(modal.querySelectorAll('input[name="nm-export-type"]:checked'), function (box) { return box.value; });
      if (!vm || !codes.length) return;
      var downloadWindow = window.open('', '_blank');
      modal.classList.add('is-loading');
      var params = { fechaIni: vm.fechaInicio, fechaFin: vm.fechaFin, declarado: vm.declarado, almacen: vm.almacen, tipoDocumento: codes.join(',') };
      vm.axios(vm.API + 'reporte/ListarRegVenta/excel', { params: params }).then(function (res) {
        var url = String(res.data || '').replace(/\\/g, '');
        if (!url || url === '[object Object]') throw new Error('El servidor no devolvi\u00f3 el archivo Excel.');
        if (!/^https?:\/\//i.test(url)) {
          url = url.charAt(0) === '/' ? window.location.origin + url : window.location.protocol + '//' + url;
        }
        if (downloadWindow && !downloadWindow.closed) downloadWindow.location.replace(url);
        else window.location.assign(url);
        closeModal();
        if (vm.$toasted) vm.$toasted.success('Reporte generado con los comprobantes seleccionados');
      }).catch(function (error) {
        if (downloadWindow && !downloadWindow.closed) downloadWindow.close();
        modal.classList.remove('is-loading');
        var data = error.response && error.response.data;
        var message = (data && (data.errorr || data.message)) || error.message || 'No se pudo generar el reporte. Intente nuevamente.';
        if (vm.$toasted) vm.$toasted.error(message);
      });
    });
    return modal;
  }

  function openModal() {
    var modal = ensureModal();
    var vm = findReportVm(document.getElementById('app').__vue__);
    var selected = vm && vm.tipoDocumento ? [String(vm.tipoDocumento)] : ['03', '01', '12'];
    modal.querySelectorAll('input[name="nm-export-type"]').forEach(function (box) { box.checked = selected.indexOf(box.value) !== -1; });
    updateCount(modal);
    modal.classList.add('is-open');
    document.body.classList.add('nm-modal-open');
  }

  document.addEventListener('click', function (event) {
    if (!isSalesRegister()) return;
    var button = event.target.closest('button, a.btn');
    if (!button || (button.textContent || '').trim().toUpperCase() !== 'EXPORTAR') return;
    event.preventDefault();
    event.stopImmediatePropagation();
    openModal();
  }, true);
  document.addEventListener('keydown', function (event) { if (event.key === 'Escape') closeModal(); });
})();
(function preventVentaHeaderFromScrollingParent() {
  var nativeScrollIntoView = Element.prototype.scrollIntoView;

  Element.prototype.scrollIntoView = function (options) {
    /* Ventas.vue llama a scrollIntoView al crear/cerrar una ventana. Dentro
       del iframe esa llamada también desplaza el documento padre y saca de
       pantalla su barra de navegación. El encabezado ya está visible, por lo
       que no necesita ese desplazamiento. */
    if (this && this.id === 'header') {
      return;
    }

    return nativeScrollIntoView.call(this, options);
  };
})();

(function addElectronicDocumentDownloads() {
  'use strict';

  function getFilename(response, fallback) {
    var disposition = response.headers.get('Content-Disposition') || '';
    var match = disposition.match(/filename\*?=(?:UTF-8''|["']?)([^"';]+)/i);
    return match ? decodeURIComponent(match[1].replace(/["']/g, '')) : fallback;
  }

  function downloadDocument(event) {
    event.preventDefault();
    event.stopPropagation();

    var button = event.currentTarget;
    if (button.classList.contains('is-downloading')) return;

    button.classList.add('is-downloading');
    var originalHtml = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>DESCARGANDO</span>';

    fetch(button.dataset.url, { credentials: 'same-origin' })
      .then(function (response) {
        if (!response.ok) {
          return response.json().catch(function () { return {}; }).then(function (body) {
            throw new Error(body.error || 'No se pudo descargar el comprobante');
          });
        }
        var fallback = 'comprobante-' + button.dataset.id + '.' + button.dataset.type.toLowerCase();
        var filename = getFilename(response, fallback);
        return response.blob().then(function (blob) {
          return { blob: blob, filename: filename };
        });
      })
      .then(function (file) {
        var objectUrl = URL.createObjectURL(file.blob);
        var link = document.createElement('a');
        link.href = objectUrl;
        link.download = file.filename;
        document.body.appendChild(link);
        link.click();
        link.remove();
        setTimeout(function () { URL.revokeObjectURL(objectUrl); }, 1500);
      })
      .catch(function (error) {
        window.alert(error.message);
      })
      .finally(function () {
        button.classList.remove('is-downloading');
        button.innerHTML = originalHtml;
      });
  }

  function showReprintMessage(message, error) {
    var toast = document.createElement('div');
    toast.className = 'nm-reprint-toast' + (error ? ' error' : '');
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(function () { toast.remove(); }, 3800);
  }

  function openEmailPanel(id) {
    var old = document.getElementById('nm-reprint-email-modal');
    if (old) old.remove();
    var modal = document.createElement('div');
    modal.id = 'nm-reprint-email-modal';
    modal.className = 'nm-reprint-email-modal';
    modal.innerHTML = '<div class="nm-reprint-email-card"><button class="nm-email-close" type="button">&times;</button><span class="nm-email-icon"><i class="fas fa-envelope"></i></span><h3>Enviar comprobante</h3><p>El PDF del comprobante #' + id + ' será adjuntado automáticamente.</p><label>Correo electrónico del cliente</label><input type="email" inputmode="email" autocomplete="email" placeholder="cliente@correo.com"><button class="nm-email-send" type="button"><i class="fas fa-paper-plane"></i> Enviar PDF</button></div>';
    document.body.appendChild(modal);
    var input = modal.querySelector('input'), send = modal.querySelector('.nm-email-send');
    modal.querySelector('.nm-email-close').onclick = function () { modal.remove(); };
    modal.addEventListener('click', function (event) { if (event.target === modal) modal.remove(); });
    send.onclick = function () {
      var email = input.value.trim();
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showReprintMessage('Ingrese un correo electrónico válido.', true); input.focus(); return; }
      send.disabled = true; send.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
      fetch('/api/index.php/correo/comprobante', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body:JSON.stringify({idDocVenta:id,email:email}) })
        .then(function (response) { return response.json().catch(function(){return {};}).then(function(body){if(!response.ok||body.error)throw new Error(body.error||'No se pudo enviar el correo.');return body;}); })
        .then(function () { modal.remove(); showReprintMessage('Comprobante enviado correctamente.'); })
        .catch(function (error) { showReprintMessage(error.message, true); send.disabled=false; send.innerHTML='<i class="fas fa-paper-plane"></i> Enviar PDF'; });
    };
    setTimeout(function () { input.focus(); }, 80);
  }

  function shareDocument(id) {
    var url = '/api/index.php/imprimirpdf/' + id;
    fetch(url, {credentials:'same-origin'}).then(function(response){if(!response.ok)throw new Error('No se pudo generar el PDF.');return response.blob();}).then(function(blob){
      var file = new File([blob], 'comprobante-' + id + '.pdf', {type:'application/pdf'});
      if (navigator.share && (!navigator.canShare || navigator.canShare({files:[file]}))) return navigator.share({title:'Comprobante #' + id,text:'Compartimos su comprobante electrónico.',files:[file]});
      window.open('https://wa.me/?text=' + encodeURIComponent('Comprobante #' + id + ': ' + new URL(url, location.origin).href), '_blank', 'noopener');
    }).catch(function(error){if(error && error.name!=='AbortError')showReprintMessage(error.message,true);});
  }

  function openWhatsAppPdfPanel(id) {
    var previous = document.querySelector('.nm-whatsapp-pdf-modal');
    if (previous) previous.remove();

    var modal = document.createElement('div');
    modal.className = 'nm-whatsapp-pdf-modal';
    modal.innerHTML = '<div class="nm-whatsapp-pdf-card">' +
      '<button class="nm-whatsapp-pdf-close" type="button" aria-label="Cerrar">&times;</button>' +
      '<span class="nm-whatsapp-pdf-icon"><i class="fab fa-whatsapp"></i></span>' +
      '<div class="nm-whatsapp-pdf-copy"><span class="nm-whatsapp-pdf-label">ENVÍO DE COMPROBANTE</span><h3>Enviar PDF por WhatsApp</h3><p>Ingresa el celular. Descargaremos el PDF y abriremos directamente el chat del cliente.</p></div>' +
      '<label for="nm-whatsapp-phone">Número de celular</label>' +
      '<div class="nm-whatsapp-phone"><span>+51</span><input id="nm-whatsapp-phone" type="tel" inputmode="numeric" autocomplete="tel" maxlength="12" placeholder="987 654 321"></div>' +
      '<small class="nm-whatsapp-hint"><i class="fas fa-file-pdf"></i> Se preparará el comprobante en formato PDF, no un enlace.</small>' +
      '<div class="nm-whatsapp-error" role="alert"></div>' +
      '<div class="nm-whatsapp-pdf-actions"><button class="nm-whatsapp-cancel" type="button">Cancelar</button><button class="nm-whatsapp-send" type="button"><i class="fab fa-whatsapp"></i> Preparar PDF y abrir</button></div>' +
      '</div>';
    document.body.appendChild(modal);

    var input = modal.querySelector('input');
    var error = modal.querySelector('.nm-whatsapp-error');
    var send = modal.querySelector('.nm-whatsapp-send');
    function close() { modal.remove(); }
    modal.querySelector('.nm-whatsapp-pdf-close').addEventListener('click', close);
    modal.querySelector('.nm-whatsapp-cancel').addEventListener('click', close);
    modal.addEventListener('click', function(event) { if (event.target === modal) close(); });
    setTimeout(function(){ input.focus(); }, 50);

    send.addEventListener('click', function () {
      var phone = String(input.value || '').replace(/\D/g, '').replace(/^51(?=9\d{8}$)/, '');
      if (!/^9\d{8}$/.test(phone)) {
        error.textContent = 'Ingresa un celular peruano válido de 9 dígitos.';
        input.focus();
        return;
      }

      error.textContent = '';
      send.disabled = true;
      send.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Preparando PDF...';
      var pdfUrl = '/api/index.php/imprimirpdf/' + id;
      fetch(pdfUrl, { credentials: 'same-origin' })
        .then(function(response) { if (!response.ok) throw new Error('No se pudo generar el PDF.'); return response.blob(); })
        .then(function(blob) {
          var filename = 'comprobante-' + id + '.pdf';
          var blobUrl = URL.createObjectURL(blob);
          var download = document.createElement('a');
          download.href = blobUrl;
          download.download = filename;
          document.body.appendChild(download);
          download.click();
          download.remove();
          setTimeout(function(){ URL.revokeObjectURL(blobUrl); }, 30000);

          var message = 'Hola, le envío su comprobante en PDF.';
          window.open('https://wa.me/51' + phone + '?text=' + encodeURIComponent(message), '_blank', 'noopener');
          close();
        })
        .catch(function(fetchError) {
          error.textContent = fetchError.message || 'No se pudo preparar el PDF.';
          send.disabled = false;
          send.innerHTML = '<i class="fab fa-whatsapp"></i> Preparar PDF y abrir';
        });
    });
  }

  document.addEventListener('click', function(event) {
    var whatsappButton = event.target.closest && event.target.closest('.document-actions .whatsapp-button');
    if (!whatsappButton) return;
    var cell = whatsappButton.closest('td');
    var printButton = cell && cell.querySelector('a[href*="/imprimir/index.php?IdDocVenta="]');
    var idMatch = printButton && printButton.getAttribute('href').match(/IdDocVenta=(\d+)/i);
    if (!idMatch) return;
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
    openWhatsAppPdfPanel(idMatch[1]);
  }, true);

  document.addEventListener('click', function(event) {
    var xmlButton = event.target.closest && event.target.closest('.document-actions .xml-button');
    if (!xmlButton) return;
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
    var url = xmlButton.getAttribute('href');
    xmlButton.classList.add('is-loading');
    fetch(url, { credentials: 'same-origin' })
      .then(function(response) {
        if (!response.ok) {
          return response.json().catch(function(){ return {}; }).then(function(body) {
            throw new Error(body.error || 'No se pudo descargar el XML.');
          });
        }
        return response.blob();
      })
      .then(function(blob) {
        var objectUrl = URL.createObjectURL(blob);
        var download = document.createElement('a');
        download.href = objectUrl;
        download.download = 'comprobante.xml';
        document.body.appendChild(download);
        download.click();
        download.remove();
        setTimeout(function(){ URL.revokeObjectURL(objectUrl); }, 30000);
        showReprintMessage('XML descargado correctamente.');
      })
      .catch(function(error) { showReprintMessage(error.message, true); })
      .finally(function() { xmlButton.classList.remove('is-loading'); });
  }, true);

  function addButtons() {
    if (window.location.hash.indexOf('reimpresion') === -1) return;

    document.querySelectorAll('a[href*="/imprimir/index.php?IdDocVenta="]').forEach(function (printButton) {
      var cell = printButton.closest('td');
      if (!cell || cell.querySelector('.nm-document-downloads')) return;
      /* La vista moderna ya trae PDF, XML y WhatsApp: agregar solamente Correo. */
      var nativeActions = cell.querySelector('.document-actions');
      if (nativeActions && cell.querySelector('.pdf-button, .xml-button, .whatsapp-button')) {
        if (nativeActions.querySelector('.email-button')) return;
        var nativeEmail = document.createElement('button');
        nativeEmail.type = 'button';
        nativeEmail.className = 'btn action-button email-button';
        nativeEmail.innerHTML = '<i class="fas fa-envelope"></i><span>CORREO</span>';
        nativeEmail.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          var nativeIdMatch = printButton.getAttribute('href').match(/IdDocVenta=(\d+)/i);
          if (nativeIdMatch) openEmailPanel(nativeIdMatch[1]);
        });
        nativeActions.appendChild(nativeEmail);
        return;
      }

      var idMatch = printButton.getAttribute('href').match(/IdDocVenta=(\d+)/i);
      if (!idMatch) return;
      var id = idMatch[1];
      var group = document.createElement('div');
      group.className = 'nm-document-downloads';

      var pdf = document.createElement('button');
      pdf.type = 'button';
      pdf.className = 'nm-download-button nm-download-pdf';
      pdf.dataset.id = id;
      pdf.dataset.type = 'PDF';
      pdf.dataset.url = '/api/index.php/imprimirpdf/' + id + '?descargar=1';
      pdf.innerHTML = '<i class="fas fa-file-pdf"></i><span>PDF</span>';
      pdf.addEventListener('click', downloadDocument);

      var xml = document.createElement('button');
      xml.type = 'button';
      xml.className = 'nm-download-button nm-download-xml';
      xml.dataset.id = id;
      xml.dataset.type = 'XML';
      xml.dataset.url = '/api/index.php/descargarxml/' + id;
      xml.innerHTML = '<i class="fas fa-code"></i><span>XML</span>';
      xml.addEventListener('click', downloadDocument);

      var whatsapp = document.createElement('button');
      whatsapp.type = 'button';
      whatsapp.className = 'nm-download-button nm-share-whatsapp';
      whatsapp.innerHTML = '<i class="fab fa-whatsapp"></i><span>WHATSAPP</span>';
      whatsapp.addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); shareDocument(id); });

      var email = document.createElement('button');
      email.type = 'button';
      email.className = 'nm-download-button nm-share-email';
      email.innerHTML = '<i class="fas fa-envelope"></i><span>CORREO</span>';
      email.addEventListener('click', function (event) { event.preventDefault(); event.stopPropagation(); openEmailPanel(id); });

      group.appendChild(pdf);
      group.appendChild(xml);
      group.appendChild(whatsapp);
      group.appendChild(email);
      cell.appendChild(group);
    });
  }

  var scheduled = false;
  new MutationObserver(function () {
    if (scheduled) return;
    scheduled = true;
    window.requestAnimationFrame(function () {
      scheduled = false;
      addButtons();
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  document.addEventListener('DOMContentLoaded', addButtons);
  addButtons();
})();

(function preloadFirstModelsOnOpen() {
  var queued = false;
  function update() {
    document.querySelectorAll('.modal').forEach(function (modal) {
      var visible = modal.classList.contains('show') || modal.style.display === 'block';
      var titleNode = modal.querySelector('.modal-title');
      var title = (titleNode ? titleNode.textContent : '').toUpperCase();
      var isModelSearch = title.indexOf('BÚSQUEDA POR MODELO') !== -1 || title.indexOf('BUSQUEDA POR MODELO') !== -1;
      if (!visible || !isModelSearch) {
        if (!visible) modal.removeAttribute('data-first-models-loaded');
        return;
      }
      if (modal.getAttribute('data-first-models-loaded') === '1') return;
      var inputs = modal.querySelectorAll('.autocomplete input');
      var modelInput = Array.prototype.find.call(inputs, function (input, index) {
        var placeholder = (input.getAttribute('placeholder') || '').toUpperCase();
        return index > 0 && (placeholder.indexOf('MODELO') !== -1 || index === 1);
      });
      if (!modelInput) return;
      modal.setAttribute('data-first-models-loaded', '1');
      modelInput.dispatchEvent(new MouseEvent('dblclick', { bubbles: true, cancelable: true }));
      modelInput.focus();
    });
  }
  new MutationObserver(function () {
    if (queued) return;
    queued = true;
    requestAnimationFrame(function () { queued = false; update(); });
  }).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'style'] });
  document.addEventListener('DOMContentLoaded', update);
  update();
})();

(function forceVisibleModelSuggestions() {
  function load() {
    document.querySelectorAll('.autocomplete-wrapper input').forEach(function (input) {
      var placeholder = (input.getAttribute('placeholder') || '').toUpperCase();
      if (placeholder.indexOf('BUSCAR MODELO') === -1 && placeholder !== 'MODELO') return;
      var wrapper = input.closest('.autocomplete-wrapper');
      var visible = !!(input.offsetWidth || input.offsetHeight || input.getClientRects().length);
      if (!visible) {
        wrapper.removeAttribute('data-model-list-forced');
        return;
      }
      if (wrapper.getAttribute('data-model-list-forced') === '1') return;
      wrapper.setAttribute('data-model-list-forced', '1');
      setTimeout(function () {
        input.value = '';
        input.dispatchEvent(new Event('input', { bubbles: true, cancelable: true }));
        input.dispatchEvent(new MouseEvent('dblclick', { bubbles: true, cancelable: true }));
        input.focus();
      }, 150);
    });
  }
  new MutationObserver(load).observe(document.documentElement, { childList: true, subtree: true, attributes: true, attributeFilter: ['class', 'style'] });
  document.addEventListener('DOMContentLoaded', load);
  setInterval(load, 700);
  load();
})();

(function markModernFacturacionView() {
  function updatePageClass() {
    document.body.classList.toggle('nm-facturacion-page', window.location.hash.indexOf('facturacion') !== -1);
    document.body.classList.toggle('nm-sales-register-page', window.location.hash.indexOf('ReporteRegistroVentas') !== -1);
    document.body.classList.toggle('nm-gross-profit-user-page', window.location.hash.indexOf('reporte_utilidad_bruta') !== -1);
    document.body.classList.toggle('nm-inventory-movement-page', window.location.hash === '' || window.location.hash === '#/' || window.location.hash === '#');
    document.body.classList.toggle('nm-model-search-page', window.location.hash.indexOf('BusquedaPorModelo') !== -1);
  }

  window.addEventListener('hashchange', updatePageClass);
  document.addEventListener('DOMContentLoaded', updatePageClass);
  updatePageClass();
})();

(function enhanceClientAndModelLists() {
  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (character) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[character];
    });
  }

  function decorate() {
    if (window.location.hash.indexOf('BusquedaPorModelo') === -1) return;
    var autocompletes = document.querySelectorAll('.autocomplete-wrapper');
    autocompletes.forEach(function (wrapper, index) {
      var input = wrapper.querySelector('input');
      var placeholder = ((input && input.getAttribute('placeholder')) || '').toUpperCase();
      var isClient = index === 0 || placeholder.indexOf('CLIENT') !== -1;
      var isModel = !isClient && (index === 1 || placeholder.indexOf('MODELO') !== -1);
      if (!isClient && !isModel) return;
      wrapper.classList.add(isClient ? 'nm-client-autocomplete' : 'nm-model-autocomplete');
      /* No se modifica el HTML interno de las opciones: Vue necesita conservar
         esos nodos para actualizar correctamente cada respuesta de búsqueda. */
      wrapper.querySelectorAll('li a').forEach(function (anchor) {
        anchor.classList.add('nm-native-suggestion');
      });
    });
  }
  var queued = false;
  new MutationObserver(function () {
    if (queued) return;
    queued = true;
    requestAnimationFrame(function () { queued = false; decorate(); });
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('hashchange', decorate);
  document.addEventListener('DOMContentLoaded', decorate);
  decorate();
})();

(function enhanceModelSearchPresentation() {
  function update() {
    if (window.location.hash.indexOf('BusquedaPorModelo') === -1) return;
    document.querySelectorAll('.model-search-totals').forEach(function (table) {
      table.classList.add('nm-model-summary');
      if (!table.previousElementSibling || !table.previousElementSibling.classList.contains('nm-model-summary-head')) {
        var heading = document.createElement('div');
        heading.className = 'nm-model-summary-head';
        heading.innerHTML = '<span><i class="fas fa-calculator"></i></span><div><small>RESUMEN COMERCIAL</small><strong>Totales de la proforma</strong></div>';
        table.parentNode.insertBefore(heading, table);
      }
    });
    document.querySelectorAll('.model-add-cart').forEach(function (button) {
      button.classList.add('nm-model-primary-action');
    });
    document.querySelectorAll('.model-cart-table img').forEach(function (image) {
      image.classList.add('nm-model-product-image');
      image.title = 'Pase el cursor para ampliar';
    });
  }
  var pending = false;
  new MutationObserver(function () {
    if (pending) return;
    pending = true;
    requestAnimationFrame(function () { pending = false; update(); });
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('hashchange', update);
  document.addEventListener('DOMContentLoaded', update);
  update();
})();

(function enhanceInventoryMovementButtons() {
  function update() {
    if (!document.body.classList.contains('nm-inventory-movement-page')) return;
    document.querySelectorAll('button, a.btn').forEach(function (button) {
      var label = (button.textContent || '').replace(/\s+/g, ' ').trim().toUpperCase();
      button.classList.add('nm-inventory-button');
      if (label.indexOf('GUARDAR MOVIMIENTO') !== -1) button.classList.add('nm-action-save');
      else if (label.indexOf('NUEVO PROVEEDOR') !== -1) button.classList.add('nm-action-supplier');
      else if (label === 'NUEVO') button.classList.add('nm-action-new');
      else if (label.indexOf('OCULTAR DATOS') !== -1 || label.indexOf('MOSTRAR DATOS') !== -1) button.classList.add('nm-action-toggle');
      else if (label.indexOf('PROBAR IMPRESORA') !== -1) button.classList.add('nm-action-print');
      else if (label.indexOf('BUSCAR') !== -1) button.classList.add('nm-action-search');

      if (button.querySelector('.fa-barcode, .fas.fa-barcode, [class*="barcode"]')) {
        button.classList.add('nm-action-barcode');
      }
      if (button.querySelector('.fa-search, .fas.fa-search')) {
        button.classList.add('nm-action-magnifier');
      }
    });
  }
  var scheduled = false;
  new MutationObserver(function () {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(function () { scheduled = false; update(); });
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('hashchange', update);
  document.addEventListener('DOMContentLoaded', update);
  update();
})();

(function enhanceSalesRegisterActions() {
  function update() {
    if (window.location.hash.indexOf('ReporteRegistroVentas') === -1) return;
    var table = document.querySelector('.table-container table');
    if (!table) return;
    var actionHeader = table.querySelector('thead th:last-child');
    if (actionHeader && !actionHeader.textContent.trim()) actionHeader.textContent = 'ACCI\u00d3N';
    table.querySelectorAll('tbody td:last-child .fa-trash').forEach(function (icon) {
      icon.classList.add('nm-sales-delete-icon');
      icon.classList.remove('fa', 'fa-trash');
      if (!icon.querySelector('svg')) {
        icon.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M9 3h6l1 2h4v2H4V5h4l1-2Zm-3 6h12l-1 12H7L6 9Zm3 2v7h2v-7H9Zm4 0v7h2v-7h-2Z"/></svg>';
      }
      icon.setAttribute('title', 'Anular registro de venta');
      icon.setAttribute('aria-label', 'Anular registro de venta');
      var cell = icon.parentElement;
      if (cell) cell.classList.add('nm-sales-action-cell');
    });
  }
  var pending = false;
  new MutationObserver(function () {
    if (pending) return;
    pending = true;
    requestAnimationFrame(function () { pending = false; update(); });
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('hashchange', update);
  document.addEventListener('DOMContentLoaded', update);
  update();
})();

(function enhanceExistingSunatTestCenter() {
  'use strict';

  function getEnvironment() {
    return new URLSearchParams(window.location.search).get('sunat') === 'produccion' ? 'produccion' : 'beta';
  }

  function refreshSummary(panel) {
    var rows = document.querySelectorAll('#tabla-ventas tbody tr');
    var accepted = 0, pending = 0, observed = 0;
    rows.forEach(function (row) {
      var text = (row.textContent || '').trim().toUpperCase();
      if (!text) return;
      if (/ACEPTAD|APROBAD|ENVIADO/.test(text)) accepted += 1;
      else if (/RECHAZ|ERROR|OBSERV/.test(text)) observed += 1;
      else pending += 1;
    });
    panel.querySelector('[data-sunat-count="visible"]').textContent = rows.length;
    panel.querySelector('[data-sunat-count="accepted"]').textContent = accepted;
    panel.querySelector('[data-sunat-count="pending"]').textContent = pending;
    panel.querySelector('[data-sunat-count="observed"]').textContent = observed;
    var checked = panel.querySelector('[data-sunat-last-check]');
    if (checked) checked.textContent = new Date().toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  }

  function refreshExistingTable() {
    if (document.hidden || window.location.hash.indexOf('facturacion') === -1) return;
    var table = document.getElementById('tabla-ventas');
    if (!table) return;
    var vue = table.__vue__;
    if (vue && typeof vue.refresh === 'function') {
      vue.refresh();
    } else if (vue && vue.$root) {
      vue.$root.$emit('bv::refresh::table', 'tabla-ventas');
    }
    var panel = document.getElementById('nm-sunat-test-center');
    if (panel) {
      panel.classList.add('is-checking');
      setTimeout(function () {
        panel.classList.remove('is-checking');
        refreshSummary(panel);
      }, 1200);
    }
  }

  function findBillingViewModel() {
    var table = document.getElementById('tabla-ventas');
    var vue = table && table.__vue__;
    while (vue) {
      if (typeof vue.emitirElectronico === 'function') return vue;
      vue = vue.$parent;
    }
    return null;
  }

  function sendPendingDocuments() {
    if (document.hidden || window.location.hash.indexOf('facturacion') === -1) return;
    var vue = findBillingViewModel();
    if (!vue || vue.loading || Number(vue.estadoVenta) !== 1 || !Array.isArray(vue.items) || !vue.items.length) {
      refreshExistingTable();
      return;
    }
    vue.emitirElectronico();
    window.setTimeout(refreshExistingTable, 2500);
  }

  function decorateExistingActions() {
    document.querySelectorAll('#tabla-ventas button, #tabla-ventas a').forEach(function (button) {
      var label = (button.textContent || '').trim().toUpperCase();
      if (label.indexOf('CONSULTA SUNAT') !== -1) {
        button.classList.add('nm-sunat-query');
        if (!button.querySelector('.fa-sync-alt')) button.insertAdjacentHTML('afterbegin', '<i class="fas fa-sync-alt"></i>');
        button.title = 'Consultar el estado mediante la funci\u00f3n SUNAT existente';
      } else if (label === 'XML') {
        button.classList.add('nm-sunat-xml');
      } else if (label === 'PDF') {
        button.classList.add('nm-sunat-pdf');
      } else if (label === 'ANULAR') {
        button.classList.add('nm-sunat-cancel');
      }
    });
  }

  function mount() {
    if (window.location.hash.indexOf('facturacion') === -1) return;
    var container = document.querySelector('#app > div > .container.bv-example-row');
    var filterRow = container && container.querySelector(':scope > .row:first-child');
    if (!container || !filterRow) return;
    var panel = document.getElementById('nm-sunat-test-center');
    if (!panel) {
      var environment = getEnvironment();
      panel = document.createElement('section');
      panel.id = 'nm-sunat-test-center';
      panel.className = 'nm-sunat-test-center is-' + environment;
      panel.innerHTML = '<div class="nm-sunat-intro"><span class="nm-sunat-icon"><i class="fas fa-satellite-dish"></i></span><div><div class="nm-sunat-title">Env\u00edo SUNAT autom\u00e1tico <em>ACTIVO</em></div><div class="nm-sunat-copy">Env\u00eda los comprobantes pendientes cada 30 minutos y actualiza sus respuestas. \u00daltima revisi\u00f3n: <b data-sunat-last-check>ahora</b></div></div></div>' +
        '<div class="nm-sunat-environment"><span class="nm-pulse"></span><div><small>ENTORNO ACTIVO</small><strong>' + (environment === 'beta' ? 'BETA / PRUEBAS' : 'PRODUCCI\u00d3N') + '</strong><span>' + (environment === 'beta' ? 'Sin validez fiscal' : 'Documentos con validez fiscal') + '</span></div></div>' +
        '<div class="nm-sunat-metrics"><span><b data-sunat-count="visible">0</b> visibles</span><span><b data-sunat-count="accepted">0</b> conformes</span><span><b data-sunat-count="pending">0</b> pendientes</span><span><b data-sunat-count="observed">0</b> observados</span></div>';
      container.insertBefore(panel, filterRow);
    }
    decorateExistingActions();
    refreshSummary(panel);
  }

  var queued = false;
  new MutationObserver(function () {
    if (queued) return;
    queued = true;
    window.requestAnimationFrame(function () { queued = false; mount(); });
  }).observe(document.documentElement, { childList: true, subtree: true });
  window.addEventListener('hashchange', mount);
  document.addEventListener('DOMContentLoaded', mount);
  mount();
  if (!window.nmSunatAutoValidationTimer) {
    window.nmSunatAutoValidationTimer = window.setInterval(sendPendingDocuments, 30 * 60 * 1000);
  }
})();

(function enableDirectUsbReceiptPrinting() {
  'use strict';

  if (window.location.hash.indexOf('/ventas') === -1) return;
  if (window.__directUsbReceiptPrintingEnabled) return;
  window.__directUsbReceiptPrintingEnabled = true;

  // La impresión directa ocultaba el comprobante y solo mostraba "OK".
  // Se conserva la función más abajo para compatibilidad, pero el botón
  // principal vuelve a abrir la vista previa normal del documento.
  var originalOpen = window.open.bind(window);
  var printRequestInProgress = false;

  function extractSaleId(url) {
    try {
      var parsed = new URL(String(url || ''), window.location.origin);
      if (!/\/imprimir\/index\.php$/i.test(parsed.pathname)) return 0;
      return parseInt(parsed.searchParams.get('IdDocVenta') || '0', 10) || 0;
    } catch (error) {
      return 0;
    }
  }

  window.imprimirTicketUsb = function (saleId) {
    saleId = parseInt(saleId, 10) || 0;
    if (!saleId || printRequestInProgress) return Promise.resolve(false);
    printRequestInProgress = true;

    var printRequest = fetch('/imprimir/usb.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      credentials: 'same-origin',
      cache: 'no-store',
      body: JSON.stringify({ IdDocVenta: saleId })
    });

    // Confirma que la solicitud fue despachada sin esperar a la impresora USB.
    return printRequest.then(function (response) {
      return response.json().catch(function () {
        throw new Error('Respuesta invalida del servidor.');
      }).then(function (payload) {
        if (!response.ok || !payload.ok) {
          throw new Error(payload.mensaje || 'No se pudo enviar el ticket.');
        }
        window.alert('OK');
        return true;
      });
    }).catch(function (error) {
      if (window.console && typeof window.console.warn === 'function') {
        window.console.warn('La solicitud de impresion fue enviada, pero la impresora no respondio.', error);
      }
      window.alert(error.message || 'No se pudo enviar el ticket a la impresora.');
      return false;
    }).then(function (result) {
      printRequestInProgress = false;
      return result;
    }, function (error) {
      printRequestInProgress = false;
      throw error;
    });
  };

  window.open = function (url, target, features) {
    var saleId = extractSaleId(url);
    if (saleId) {
      window.imprimirTicketUsb(saleId);
      return {
        closed: false,
        close: function () {},
        focus: function () {},
        location: { href: String(url) }
      };
    }
    return originalOpen(url, target, features);
  };

  document.addEventListener('click', function (event) {
    var source = event.target;
    var link = source && source.closest
      ? source.closest('a[href*="/imprimir/index.php?IdDocVenta="]')
      : null;
    if (!link) return;

    var saleId = extractSaleId(link.href);
    if (!saleId) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    window.imprimirTicketUsb(saleId);
  }, true);
})();

(function fixCreditNoteReferenceLayer() {
  'use strict';
  if (window.__creditNoteReferenceLayerFixed) return;
  window.__creditNoteReferenceLayerFixed = true;

  var style = document.createElement('style');
  style.id = 'nm-credit-note-reference-layer';
  style.textContent =
    '.pos-modern .pos-credit-note-reference{position:relative!important;z-index:2147482000!important;overflow:visible!important}' +
    '.pos-modern .pos-credit-note-reference .input-group,.pos-modern .pos-credit-note-reference .autocomplete{position:relative!important;z-index:inherit!important;overflow:visible!important}' +
    '.pos-modern .pos-credit-note-reference .autocomplete ul{position:absolute!important;z-index:2147483000!important;top:calc(100% + 7px)!important;left:0!important;width:100%!important;max-height:260px!important;overflow-y:auto!important;border:1px solid #c9ddeb!important;border-radius:12px!important;background:#fff!important;box-shadow:0 18px 38px rgba(4,39,70,.28)!important}' +
    '.pos-modern .pos-credit-note-reference .autocomplete ul li,.pos-modern .pos-credit-note-reference .autocomplete ul li a{position:relative!important;z-index:2147483001!important;background:#fff!important}' +
    '.pos-modern .pos-sale-header,.pos-modern .pos-command-header,.pos-modern .tab-content,.pos-modern .tab-pane.active{overflow:visible!important}' +
    '.pos-modern .pos-credit-note-reference.is-reference-open{z-index:2147483000!important}';
  document.head.appendChild(style);

  function mountReferenceLayer() {
    document.querySelectorAll('[id^="inputDocRef"]').forEach(function(input) {
      var column = input.closest('.docInfo') || input.parentElement;
      if (!column) return;
      column.classList.add('pos-credit-note-reference');
      if (input.dataset.referenceLayerReady === '1') return;
      input.dataset.referenceLayerReady = '1';
      input.addEventListener('focus', function(){ column.classList.add('is-reference-open'); });
      input.addEventListener('blur', function(){ setTimeout(function(){ column.classList.remove('is-reference-open'); }, 220); });
    });
  }

  var queued = false;
  new MutationObserver(function(){
    if (queued) return;
    queued = true;
    window.requestAnimationFrame(function(){ queued = false; mountReferenceLayer(); });
  }).observe(document.documentElement, { childList:true, subtree:true });
  document.addEventListener('DOMContentLoaded', mountReferenceLayer);
  mountReferenceLayer();
})();
