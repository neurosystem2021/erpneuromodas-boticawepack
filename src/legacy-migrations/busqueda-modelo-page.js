(function () {
  'use strict';

  function enhance() {
    if (window.location.hash.indexOf('BusquedaPorModelo') === -1) {
      document.querySelectorAll('.pos-modern .nm-summary-title').forEach(function (title) {
        title.remove();
      });
      return;
    }
    document.querySelectorAll('table').forEach(function (table) {
      var tableText = (table.textContent || '').replace(/\s+/g, ' ').toUpperCase();
      if (tableText.indexOf('SUBTOTAL') === -1 || tableText.indexOf('DESCUENTO') === -1 || tableText.indexOf('TOTAL') === -1) return;
      table.classList.add('nm-summary-table');
      if (!table.previousElementSibling || !table.previousElementSibling.classList.contains('nm-summary-title')) {
        var title = document.createElement('div');
        title.className = 'nm-summary-title';
        title.innerHTML = '<span><i class="fas fa-calculator"></i></span><div><small>RESUMEN COMERCIAL</small><strong>Totales de la proforma</strong></div>';
        table.parentNode.insertBefore(title, table);
      }
    });

    document.querySelectorAll('button, a.btn').forEach(function (button) {
      var label = (button.textContent || '').replace(/\s+/g, ' ').trim().toUpperCase();
      if (label.indexOf('GENERAR PROFORMA') !== -1 || label.indexOf('AGREGAR AL CARRITO') !== -1) {
        button.classList.add('nm-proforma-button');
      }
    });

    document.querySelectorAll('table img').forEach(function (image) {
      image.classList.add('nm-product-photo');
      image.title = 'Clic para ampliar imagen';
    });
  }

  function getViewer() {
    var viewer = document.querySelector('.nm-image-viewer');
    if (viewer) return viewer;

    viewer = document.createElement('div');
    viewer.className = 'nm-image-viewer';
    viewer.hidden = true;
    viewer.setAttribute('role', 'dialog');
    viewer.setAttribute('aria-modal', 'true');
    viewer.setAttribute('aria-label', 'Vista ampliada del producto');
    viewer.innerHTML = '<div class="nm-image-viewer__panel"><img class="nm-image-viewer__image" alt="Imagen ampliada del producto"><button type="button" class="nm-image-viewer__close" aria-label="Cerrar imagen">&times;</button></div>';
    document.body.appendChild(viewer);

    function closeViewer() {
      viewer.hidden = true;
      document.body.classList.remove('nm-image-viewer-open');
    }

    viewer.addEventListener('click', function (event) {
      if (event.target === viewer || event.target.closest('.nm-image-viewer__close')) closeViewer();
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !viewer.hidden) closeViewer();
    });
    return viewer;
  }

  document.addEventListener('click', function (event) {
    var image = event.target.closest('img.nm-product-photo');
    if (!image) return;
    var viewer = getViewer();
    var preview = viewer.querySelector('.nm-image-viewer__image');
    preview.src = image.currentSrc || image.src;
    preview.alt = image.alt || 'Imagen ampliada del producto';
    viewer.hidden = false;
    document.body.classList.add('nm-image-viewer-open');
  });

  var queued = false;
  new MutationObserver(function () {
    if (queued) return;
    queued = true;
    requestAnimationFrame(function () {
      queued = false;
      enhance();
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  document.addEventListener('DOMContentLoaded', enhance);
  enhance();
})();
