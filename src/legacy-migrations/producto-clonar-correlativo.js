(function () {
  'use strict';

  function textoBoton(elemento) {
    return (elemento && elemento.textContent ? elemento.textContent : '')
      .replace(/\s+/g, ' ')
      .trim()
      .toLowerCase();
  }

  function buscarVue(elemento) {
    var actual = elemento;
    while (actual) {
      var componente = actual.__vue__;
      while (componente) {
        if (componente.producto && componente.$refs) return componente;
        componente = componente.$parent;
      }
      actual = actual.parentElement;
    }
    return null;
  }

  function avisar(vm, mensaje, tipo) {
    if (vm && vm.$toasted && typeof vm.$toasted[tipo] === 'function') {
      vm.$toasted[tipo](mensaje);
      return;
    }
    if (vm && vm.$toasted && typeof vm.$toasted.show === 'function') {
      vm.$toasted.show(mensaje);
    }
  }

  document.addEventListener('click', function (evento) {
    var boton = evento.target && evento.target.closest
      ? evento.target.closest('button, .btn, a')
      : null;

    if (!boton || textoBoton(boton).indexOf('nuevo producto') === -1) return;

    var vm = buscarVue(boton);
    if (!vm || !vm.producto || !vm.producto.IdProducto) return;

    var codigoBase = String(vm.producto.CodigoBarra || '').trim().toUpperCase();
    if (!codigoBase) return;

    // El manejador original conserva la ficha y cambia el formulario a modo crear.
    // Después de ejecutarse, solamente sustituimos el código por el correlativo libre.
    window.setTimeout(function () {
      fetch('/api/index.php/productos/codigo-siguiente/' + encodeURIComponent(codigoBase), {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      })
        .then(function (respuesta) {
          return respuesta.json().then(function (datos) {
            if (!respuesta.ok || !datos || datos.error) {
              throw new Error((datos && datos.error) || 'No se pudo calcular el nuevo código.');
            }
            return datos;
          });
        })
        .then(function (datos) {
          var nuevoCodigo = String(datos.codigo || '').trim().toUpperCase();
          if (!nuevoCodigo) throw new Error('El servicio no devolvió un código válido.');

          if (typeof vm.$set === 'function') vm.$set(vm.producto, 'CodigoBarra', nuevoCodigo);
          else vm.producto.CodigoBarra = nuevoCodigo;

          if (vm.$refs.inputCodigoBarra && typeof vm.$refs.inputCodigoBarra.setValue === 'function') {
            vm.$refs.inputCodigoBarra.setValue(nuevoCodigo);
          }
          vm.title = 'Nuevo Producto << ' + nuevoCodigo + ' >>';
          if (typeof vm.$forceUpdate === 'function') vm.$forceUpdate();
          avisar(vm, 'Nuevo código asignado automáticamente: ' + nuevoCodigo, 'success');
        })
        .catch(function (error) {
          // Evita que se guarde accidentalmente con el código del producto original.
          if (typeof vm.$set === 'function') vm.$set(vm.producto, 'CodigoBarra', '');
          else vm.producto.CodigoBarra = '';
          avisar(vm, error.message || 'No se pudo generar el código correlativo.', 'error');
        });
    }, 0);
  }, true);
})();
