(function () {
  'use strict';
  function isPage() { return location.hash.toLowerCase().indexOf('/ordencompra') !== -1; }
  function findComponent() {
    var nodes = document.querySelectorAll('*');
    for (var i = 0; i < nodes.length; i++) {
      var vm = nodes[i].__vue__;
      if (vm && vm.movimiento && typeof vm.add === 'function' && vm.$store) return vm;
    }
    return null;
  }
  function enhanceFields(vm) {
    if (!vm || !vm.carrito || !Array.isArray(vm.carrito.fields) || vm.carrito._stockReferenceEnhanced) return;
    var fields = vm.carrito.fields;
    for (var removeIndex = fields.length - 1; removeIndex >= 0; removeIndex--) {
      if ((fields[removeIndex].key || fields[removeIndex]) === 'ProductoFormaFarmaceutica') fields.splice(removeIndex, 1);
    }
    var requested = [
      { key:'ProductoModelo', label:'Modelo' },
      { key:'ProductoTalla', label:'Talla' },
      { key:'Color', label:'Color' }
    ];
    var brandIndex = fields.findIndex(function (field) { return (field.key || field) === 'ProductoMarca'; });
    requested.reverse().forEach(function (field) {
      if (!fields.some(function (current) { return (current.key || current) === field.key; })) fields.splice(brandIndex + 1, 0, field);
    });
    fields.forEach(function (field) { if (field && field.key === 'cantidadPedida') { field.label = 'Cantidad a reponer'; field.variant = 'warning'; field.class = 'text-center fontsize nm-replenish-reference'; } });
    vm.carrito._stockReferenceEnhanced = true;
    if (vm.$forceUpdate) vm.$forceUpdate();
  }
  function notify(vm, status, text, type) {
    status.className = 'nm-order-import-status is-visible ' + (type ? 'is-' + type : '');
    status.innerHTML = text;
    if (vm && vm.$toasted) type === 'error' ? vm.$toasted.error(text.replace(/<[^>]+>/g,'')) : vm.$toasted.show(text.replace(/<[^>]+>/g,''));
  }
  function simplifyOrder(vm) {
    document.querySelectorAll('.inventory-movement-summary .ocularMovil').forEach(function (row) { row.style.display = 'none'; });
    if (!document.querySelector('.nm-new-product')) {
      var help = document.querySelector('.inventory-search-help');
      if (help) {
        var newButton = document.createElement('button');
        newButton.type = 'button';
        newButton.className = 'nm-new-product';
        newButton.innerHTML = '<i class="fas fa-plus-circle"></i> Crear nuevo producto';
        help.parentNode.insertBefore(newButton, help.nextSibling);
        newButton.addEventListener('click', function () {
          var currentVm = findComponent();
          var cart = currentVm && currentVm.$refs && currentVm.$refs.carrito;
          var productForm = cart && cart.$refs && (cart.$refs.productoFormEdit || cart.$refs.productoFormEditAnt);
          if (Array.isArray(productForm)) productForm = productForm[0];
          if (!productForm) return notify(currentVm, document.querySelector('.nm-order-import-status'), 'No se pudo abrir el formulario de producto.', 'error');
          cart.productoSelect = {};
          productForm.producto = {};
          if (typeof productForm.initForm === 'function') productForm.initForm();
          productForm.show = true;
        });
      }
    }
    if (!document.querySelector('.nm-new-supplier')) {
      var providerLabel = Array.prototype.slice.call(document.querySelectorAll('.ops-card label')).find(function (label) { return (label.textContent || '').trim().toLowerCase().indexOf('proveedor') === 0; });
      var providerGroup = providerLabel && providerLabel.closest('.form-group');
      if (providerGroup) {
        var supplierButton = document.createElement('button');
        supplierButton.type = 'button';
        supplierButton.className = 'nm-new-supplier';
        supplierButton.innerHTML = '<i class="fas fa-user-plus"></i> Nuevo proveedor';
        providerGroup.appendChild(supplierButton);
        var modal = document.createElement('div');
        modal.className = 'nm-supplier-modal';
        modal.innerHTML = '<div class="nm-supplier-dialog"><header><span><i class="fas fa-truck"></i></span><div><small>ABASTECIMIENTO</small><h2>Nuevo proveedor</h2></div><button type="button" data-close><i class="fas fa-times"></i></button></header><div class="nm-supplier-body"><label>RUC *</label><div class="nm-supplier-ruc"><input data-field="ruc" inputmode="numeric" maxlength="11" placeholder="Ingrese los 11 dígitos"><button type="button" data-search><i class="fas fa-search"></i> Consultar RUC</button></div><small data-status>Consulte el RUC para completar los datos automáticamente.</small><label>Razón social *</label><input data-field="name" placeholder="Nombre o razón social"><label>Dirección</label><input data-field="address" placeholder="Dirección fiscal"><label>Teléfono</label><input data-field="phone" placeholder="Teléfono de contacto"></div><footer><button type="button" data-close>Cancelar</button><button type="button" data-save><i class="fas fa-save"></i> Guardar y seleccionar</button></footer></div>';
        document.body.appendChild(modal);
        var rucInput = modal.querySelector('[data-field="ruc"]');
        var nameInput = modal.querySelector('[data-field="name"]');
        var addressInput = modal.querySelector('[data-field="address"]');
        var phoneInput = modal.querySelector('[data-field="phone"]');
        var modalStatus = modal.querySelector('[data-status]');
        function closeSupplier() { modal.classList.remove('is-open'); }
        supplierButton.addEventListener('click', function () { modal.classList.add('is-open'); setTimeout(function () { rucInput.focus(); }, 100); });
        modal.querySelectorAll('[data-close]').forEach(function (button) { button.addEventListener('click', closeSupplier); });
        modal.addEventListener('click', function (event) { if (event.target === modal) closeSupplier(); });
        modal.querySelector('[data-search]').addEventListener('click', function () {
          var ruc = rucInput.value.replace(/\D/g, ''); rucInput.value = ruc;
          if (ruc.length !== 11) { modalStatus.className='is-error'; modalStatus.textContent='El RUC debe tener 11 dígitos.'; return; }
          var searchButton = this; searchButton.disabled=true; searchButton.innerHTML='<i class="fas fa-spinner fa-spin"></i> Consultando';
          fetch('/api/index.php/consultarRUC?type=RUC&numero=' + encodeURIComponent(ruc)).then(function (response) { return response.json(); }).then(function (data) {
            if (!data || !data.razonSocial) throw new Error('RUC no encontrado');
            nameInput.value=data.razonSocial; addressInput.value=data.direccion || '';
            modalStatus.className='is-success'; modalStatus.textContent=(data.estado || 'ACTIVO') + ' · ' + (data.condicion || 'Datos encontrados');
          }).catch(function () { modalStatus.className='is-error'; modalStatus.textContent='No se encontró el RUC. Puede completar los datos manualmente.'; }).then(function () { searchButton.disabled=false; searchButton.innerHTML='<i class="fas fa-search"></i> Consultar RUC'; });
        });
        modal.querySelector('[data-save]').addEventListener('click', function () {
          var ruc=rucInput.value.replace(/\D/g,''), name=nameInput.value.trim();
          if (ruc.length !== 11 || !name) { modalStatus.className='is-error'; modalStatus.textContent='Complete un RUC válido y la razón social.'; return; }
          var saveButton=this; saveButton.disabled=true; saveButton.innerHTML='<i class="fas fa-spinner fa-spin"></i> Guardando...';
          var payload={Proveedor:name.toUpperCase(),Ruc:ruc,Direccion:addressInput.value.trim().toUpperCase(),Telefono:phoneInput.value.trim(),Observacion:'',FechaReg:null};
          fetch('/api/index.php/proveedores',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)}).then(function(response){return response.json().then(function(data){if(!response.ok)throw new Error('No se pudo guardar');return data;});}).then(function(data){
            var currentVm=findComponent();
            var supplier={IdProveedor:data.IdProveedor || data.id || data.insertId || data,Proveedor:payload.Proveedor,Ruc:ruc,Direccion:payload.Direccion,ProveedorRuc:ruc+' - '+payload.Proveedor};
            if(currentVm && typeof currentVm.setProveedor==='function') currentVm.setProveedor(supplier);
            if(currentVm && currentVm.$refs && currentVm.$refs.inputProveedor && typeof currentVm.$refs.inputProveedor.setValue==='function') currentVm.$refs.inputProveedor.setValue(supplier.ProveedorRuc);
            closeSupplier();
            rucInput.value='';nameInput.value='';addressInput.value='';phoneInput.value='';modalStatus.className='';modalStatus.textContent='Consulte el RUC para completar los datos automáticamente.';
            if(currentVm && currentVm.$toasted) currentVm.$toasted.success('Proveedor guardado y seleccionado');
          }).catch(function(error){modalStatus.className='is-error';modalStatus.textContent=error.message;}).then(function(){saveButton.disabled=false;saveButton.innerHTML='<i class="fas fa-save"></i> Guardar y seleccionar';});
        });
      }
    }
    var form = document.querySelector('.ops-card form');
    if (form && form.dataset.orderConfirmation !== '1') {
      form.dataset.orderConfirmation = '1';
      var confirmed = false;
      form.addEventListener('submit', function (event) {
        if (confirmed) { confirmed = false; return; }
        event.preventDefault();
        event.stopImmediatePropagation();
        var currentVm = findComponent();
        var products = currentVm && currentVm.$store ? currentVm.$store.state.cart.productos || [] : [];
        var provider = currentVm && currentVm.movimiento && currentVm.movimiento.proveedor ? currentVm.movimiento.proveedor.Proveedor || 'Sin proveedor' : 'Sin proveedor';
        var total = products.reduce(function (sum, product) { return sum + (parseFloat(product.cantidad) || 0) * (parseFloat(product.precio) || 0); }, 0);
        var message = 'Proveedor: ' + provider + '\nProductos: ' + products.length + '\nTotal: S/ ' + total.toFixed(2) + '\n\n¿Desea guardar esta orden de compra?';
        function submitConfirmed() { confirmed = true; if (form.requestSubmit) form.requestSubmit(); else form.querySelector('[type="submit"]').click(); }
        if (currentVm && currentVm.$swal) {
          currentVm.$swal({ title:'Confirmar orden de compra', html:'<div class="nm-order-confirm"><span>Proveedor</span><b>' + provider + '</b><span>Productos</span><b>' + products.length + '</b><span>Total</span><b>S/ ' + total.toFixed(2) + '</b></div><p>Revise cantidades y precios antes de continuar.</p>', icon:'question', showCancelButton:true, confirmButtonText:'Sí, guardar orden', cancelButtonText:'Seguir revisando', confirmButtonColor:'#0aae70', cancelButtonColor:'#64788a' }).then(function (result) { if (result && (result.isConfirmed || result.value)) submitConfirmed(); });
        } else if (window.confirm(message)) submitConfirmed();
      }, true);
    }
  }
  function enhance() {
    if (!isPage() || document.querySelector('.nm-order-import')) return;
    var hero = document.querySelector('.ops-hero');
    if (!hero) return;
    var pageVm = findComponent();
    enhanceFields(pageVm);
    simplifyOrder(pageVm);
    var button = document.createElement('button');
    button.type = 'button';
    button.className = 'nm-order-import';
    button.innerHTML = '<i class="fas fa-file-upload"></i> Cargar Excel de stock mínimo';
    var input = document.createElement('input');
    input.type = 'file'; input.accept = '.xlsx,.xls'; input.hidden = true;
    var status = document.createElement('div');
    status.className = 'nm-order-import-status';
    hero.appendChild(button); hero.appendChild(input);
    hero.parentNode.insertBefore(status, hero.nextSibling);
    button.addEventListener('click', function () {
      var vm = findComponent();
      if (!vm) return notify(null, status, 'No se pudo acceder a la orden de compra.', 'error');
      if (!vm.movimiento.almacen || !vm.movimiento.almacen.IdAlmacen) return notify(vm, status, '<i class="fas fa-warehouse"></i> Seleccione primero el almacén.', 'error');
      if (!vm.movimiento.proveedor || !vm.movimiento.proveedor.IdProveedor) return notify(vm, status, '<i class="fas fa-truck"></i> Seleccione primero el proveedor que desea importar.', 'error');
      input.click();
    });
    input.addEventListener('change', function () {
      var file = input.files[0], vm = findComponent();
      if (!file || !vm) return;
      button.disabled = true;
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Leyendo Excel...';
      notify(vm, status, 'Validando el archivo y el proveedor seleccionado...');
      var formData = new FormData(); formData.append('archivo', file);
      fetch('/api/index.php/reporte/stock-minimo/importar', { method:'POST', body:formData })
        .then(function (response) { return response.json().then(function (data) { if (!response.ok) throw new Error(data.error || 'No se pudo importar'); return data; }); })
        .then(function (data) {
          var provider = vm.movimiento.proveedor;
          var providerName = String(provider.Proveedor || '').trim().toUpperCase();
          var providerRuc = String(provider.Ruc || '').replace(/\D/g,'');
          var rows = data.items.filter(function (item) {
            var itemRuc = String(item.rucProveedor || '').replace(/\D/g,'');
            return (providerRuc && itemRuc === providerRuc) || (!providerRuc && String(item.proveedor || '').trim().toUpperCase() === providerName);
          });
          if (!rows.length) throw new Error('El Excel no contiene productos para el proveedor seleccionado.');
          rows = rows.filter(function (item) { return parseInt(item.idProducto, 10) > 0; });
          if (!rows.length) throw new Error('No se encontraron en el inventario los códigos del proveedor seleccionado.');
          var params = new URLSearchParams();
          params.append('idAlmacen', vm.movimiento.almacen.IdAlmacen);
          params.append('noLimit', '1');
          rows.forEach(function (item) { params.append('idProductos[]', item.idProducto); });
          return fetch('/api/index.php/productos/stock?' + params.toString()).then(function (response) { return response.json(); }).then(function (products) { return { rows:rows, products:products }; });
        })
        .then(function (result) {
          var byCode = {};
          result.rows.forEach(function (row) { byCode[String(row.codigo).trim().toUpperCase()] = row; });
          var added = 0;
          result.products.forEach(function (product) {
            var row = byCode[String(product.CodigoBarra || product.Codigo || '').trim().toUpperCase()];
            if (!row) return;
            vm.add([product, function () {}, false, true]); added++;
          });
          setTimeout(function () {
            var cart = vm.$store.state.cart.productos || [];
            cart.forEach(function (product) {
              var row = byCode[String(product.CodigoBarra || product.Codigo || '').trim().toUpperCase()];
              if (row) {
                if (vm.$set) {
                  vm.$set(product, 'ProductoTalla', row.talla || product.ProductoTalla || '');
                  vm.$set(product, 'Color', row.color || product.Color || '');
                  vm.$set(product, 'ProductoModelo', row.modelo || product.ProductoModelo || '');
                  vm.$set(product, 'cantidadPedida', row.cantidad);
                  vm.$set(product, 'cantidad', row.cantidad);
                } else {
                  product.cantidadPedida = row.cantidad;
                  product.cantidad = row.cantidad;
                }
                if (!product.precio) product.precio = product.PrecioCosto || 0;
              }
            });
            notify(vm, status, '<i class="fas fa-check-circle"></i> ' + added + ' productos cargados. Revise cantidades y precios antes de generar la orden.', 'success');
          }, 350);
        })
        .catch(function (error) { notify(vm, status, '<i class="fas fa-exclamation-circle"></i> ' + error.message, 'error'); })
        .then(function () { button.disabled=false; button.innerHTML='<i class="fas fa-file-upload"></i> Cargar Excel de stock mínimo'; input.value=''; });
    });
  }
  var queued=false;
  new MutationObserver(function(){if(!queued){queued=true;requestAnimationFrame(function(){queued=false;enhance();});}}).observe(document.documentElement,{childList:true,subtree:true});
  window.addEventListener('hashchange',enhance); document.addEventListener('DOMContentLoaded',enhance); enhance();
})();
