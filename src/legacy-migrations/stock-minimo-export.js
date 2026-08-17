(function () {
  'use strict';
  function isPage() { return location.hash.indexOf('/reportes/stockminimo') !== -1; }
  function warehouseId() {
    var nodes = Array.prototype.slice.call(document.querySelectorAll('*'));
    for (var i = 0; i < nodes.length; i++) {
      var vm = nodes[i].__vue__;
      if (!vm) continue;
      var candidates = [vm.idAlmacen, vm.IdAlmacen, vm.almacen && vm.almacen.IdAlmacen, vm.almacenSeleccionado && vm.almacenSeleccionado.IdAlmacen];
      for (var c = 0; c < candidates.length; c++) if (parseInt(candidates[c], 10) > 0) return parseInt(candidates[c], 10);
    }
    return 1;
  }
  function enhanceColumns() {
    var nodes = document.querySelectorAll('*');
    for (var i = 0; i < nodes.length; i++) {
      var vm = nodes[i].__vue__;
      if (!vm || !Array.isArray(vm.fields) || vm._stockColumnsEnhanced) continue;
      var keys = vm.fields.map(function (field) { return field.key || field; });
      if (keys.indexOf('ProductoMarca') === -1 || keys.indexOf('stock') === -1) continue;
      var brandIndex = keys.indexOf('ProductoMarca');
      [{key:'Color',label:'Color'},{key:'ProductoTalla',label:'Talla'},{key:'ProductoModelo',label:'Modelo'}].forEach(function (field) {
        if (!vm.fields.some(function (current) { return (current.key || current) === field.key; })) vm.fields.splice(brandIndex + 1, 0, field);
      });
      vm._stockColumnsEnhanced = true;
      if (vm.$forceUpdate) vm.$forceUpdate();
    }
  }
  function enhance() {
    if (!isPage()) return;
    document.body.classList.add('nm-stock-min-page');
    enhanceColumns();
    if (document.querySelector('.nm-stock-min-hero')) return;
    var app = document.querySelector('#app > div') || document.getElementById('app');
    if (!app || !app.firstElementChild) return;
    var hero = document.createElement('header');
    hero.className = 'nm-stock-min-hero';
    hero.innerHTML = '<div class="nm-stock-min-hero__title"><span class="nm-stock-min-hero__icon"><i class="fas fa-boxes"></i></span><div><small>INVENTARIO / REPOSICIÓN</small><h1>Productos con stock mínimo</h1><p>Genera una lista de compra agrupada por el último proveedor registrado.</p></div></div><button type="button" class="nm-stock-min-export"><i class="fas fa-file-excel"></i> Exportar Excel por proveedor</button>';
    app.insertBefore(hero, app.firstElementChild);
    var button = hero.querySelector('button');
    button.addEventListener('click', function () {
      if (button.classList.contains('is-loading')) return;
      button.classList.add('is-loading');
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando Excel...';
      window.open('/api/index.php/reporte/stock-minimo-proveedor?idAlmacen=' + warehouseId(), '_blank');
      setTimeout(function () { button.classList.remove('is-loading'); button.innerHTML = '<i class="fas fa-file-excel"></i> Exportar Excel por proveedor'; }, 2200);
    });
  }
  var queued = false;
  new MutationObserver(function () { if (!queued) { queued = true; requestAnimationFrame(function () { queued = false; enhance(); }); } }).observe(document.documentElement,{childList:true,subtree:true});
  window.addEventListener('hashchange', enhance);
  document.addEventListener('DOMContentLoaded', enhance);
  enhance();
})();
