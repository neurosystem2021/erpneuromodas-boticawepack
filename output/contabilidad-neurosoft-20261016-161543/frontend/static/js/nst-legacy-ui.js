/* Progressive enhancement for NST PHP screens; keeps IDs, handlers and forms. */
(function () {
  'use strict';
  var script = document.currentScript;
  var assetURL = script ? new URL(script.src, location.href) : null;
  var appURL = assetURL ? new URL('../../index.html', assetURL) : null;
  var titles = {
    gen_productomarcaform: ['Marcas', 'Producto'],
    gen_productocategoriaform: ['Categorías de productos', 'Producto'],
    gen_productomedicionform: ['Unidades de medida', 'Producto'],
    gen_productoformafarmaceuticaform: ['Formas farmacéuticas', 'Producto'],
    gen_productocompuestoform: ['Compuestos de productos', 'Producto'],
    gen_productomodelo: ['Modelos de productos', 'Producto'],
    seg_usuarioperfil: ['Perfiles y permisos', 'Seguridad'],
    seg_usuarioform: ['Usuarios del sistema', 'Seguridad'],
    v_preorden: ['Preórdenes de venta', 'Ventas'],
    v_expertoform: ['Asistente de ventas', 'Ventas'],
    lo_buscarvencimiento: ['Productos por vencer', 'Logística'],
    lo_generarcodigobarra: ['Códigos de barras', 'Logística'],
    ve_cambiopreciobloque: ['Actualizar precios en bloque', 'Ventas'],
    ag_configuracion: ['Configuración de agentes', 'Multi agente'],
    lo_regcompracontable: ['Registro de compras contables', 'Logística'],
    ve_regventaform: ['Registro de ventas', 'Ventas'],
    lo_regmovimiento: ['Registro de movimientos', 'Logística']
  };
  function run() {
    var menuItems = document.querySelectorAll('.erp-topbar .navbar-nav > li.dropdown');
    function setMenu(item, open) {
      item.classList.toggle('open', open);
      var toggle = item.querySelector('.dropdown-toggle');
      if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }
    Array.prototype.forEach.call(menuItems, function (item) {
      var closeTimer;
      item.addEventListener('mouseenter', function () {
        if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
        clearTimeout(closeTimer);
        Array.prototype.forEach.call(menuItems, function (other) {
          setMenu(other, other === item);
        });
      });
      item.addEventListener('mouseleave', function () {
        if (!window.matchMedia('(hover: hover) and (pointer: fine)').matches) return;
        closeTimer = setTimeout(function () { setMenu(item, false); }, 180);
      });
    });
    var frames = Array.prototype.filter.call(document.querySelectorAll('iframe'), function (frame) {
      try { return /\/nuevo\/index\.html$/.test(new URL(frame.src, location.href).pathname); } catch (_) { return false; }
    });
    if (frames.length) {
      frames.forEach(function (frame) {
        var current = new URL(frame.src, location.href);
        if (appURL) {
          current.pathname = appURL.pathname;
          current.searchParams.set('nst', assetURL.searchParams.get('v') || 'premium');
          if (frame.src !== current.href) frame.src = current.href;
        }
        function resize() {
          var top = frame.getBoundingClientRect().top;
          frame.style.height = Math.max(420, window.innerHeight - top - 4) + 'px';
        }
        frame.style.display = 'block'; frame.style.width = '100%'; frame.style.border = '0';
        frame.addEventListener('load', resize); window.addEventListener('resize', resize); resize();
      });
      return;
    }
    var content = Array.prototype.filter.call(document.querySelectorAll('body > .container, body > .container-fluid, body > #app, body > #conten, body > .row'), function (element) {
      return !element.querySelector('nav, .navbar') && !element.closest('.modal') && (element.querySelector('table, form, input, select') || element.id === 'app');
    });
    if (!content.length) return;
    document.body.classList.add('nst-legacy-page');
    content.forEach(function (element) { element.classList.add('nst-legacy-content'); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', run);
  else run();
})();
