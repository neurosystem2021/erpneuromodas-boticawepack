(function(){
  'use strict';
  function findVm(){var nodes=document.querySelectorAll('*');for(var i=0;i<nodes.length;i++){var vm=nodes[i].__vue__;if(vm&&vm.movimiento&&vm.$refs&&vm.$refs.inputProveedor&&typeof vm.setProveedor==='function'&&vm.$store)return vm;}return null;}
  function money(value){return 'S/ '+(parseFloat(value)||0).toFixed(2);}
  function dateText(value){return String(value||'').replace('T',' ').slice(0,16);}
  function enhanceProductImageIcons(){
    document.querySelectorAll('.inventory-shell .autocomplete li').forEach(function(item){
      if(item.querySelector('.nm-inventory-image-button'))return;
      var image=item.querySelector('img');
      if(!image)return;
      image.classList.add('nm-inventory-source-image');
      image.style.display='none';
      var button=document.createElement('button');
      button.type='button';
      button.className='nm-inventory-image-button';
      button.title='Ver imagen del producto';
      button.setAttribute('aria-label','Ver imagen del producto');
      button.innerHTML='<i class="fas fa-camera"></i>';
      image.parentNode.insertBefore(button,image);
    });
  }
  function showInventoryImage(button){
    var item=button.closest('li');
    var source=item&&item.querySelector('.nm-inventory-source-image');
    if(!source)return;
    var preview=document.getElementById('nm-inventory-image-preview');
    if(!preview){preview=document.createElement('img');preview.id='nm-inventory-image-preview';preview.alt='Vista previa del producto';document.body.appendChild(preview);}
    preview.src=source.currentSrc||source.src||'/nuevo/static/no-photo.png';
    preview.onerror=function(){this.onerror=null;this.src='/nuevo/static/no-photo.png';};
    var rect=button.getBoundingClientRect();
    preview.style.left=Math.max(12,Math.min(window.innerWidth-292,rect.left-286))+'px';
    preview.style.top=Math.max(12,Math.min(window.innerHeight-292,rect.top-120))+'px';
    preview.classList.add('is-visible');
  }
  document.addEventListener('mouseover',function(event){var button=event.target.closest&&event.target.closest('.nm-inventory-image-button');if(button)showInventoryImage(button);},true);
  document.addEventListener('mouseout',function(event){var button=event.target.closest&&event.target.closest('.nm-inventory-image-button');if(button){var preview=document.getElementById('nm-inventory-image-preview');if(preview)preview.classList.remove('is-visible');}},true);
  document.addEventListener('click',function(event){var button=event.target.closest&&event.target.closest('.nm-inventory-image-button');if(!button)return;event.preventDefault();event.stopImmediatePropagation();showInventoryImage(button);},true);
  function enhance(){
    enhanceProductImageIcons();
    var heading=document.querySelector('.inventory-heading__actions');
    var form=document.querySelector('.inventory-movement-form');
    if(form&&!form.dataset.orderConfirmation){
      form.dataset.orderConfirmation='true';
      form.addEventListener('submit',function(event){
        if(form.dataset.confirmedSubmit==='true'){delete form.dataset.confirmedSubmit;return;}
        event.preventDefault();event.stopImmediatePropagation();
        var vm=findVm();if(!vm)return;
        var products=vm.$store.state.cart.productos||[];
        var provider=vm.movimiento.proveedor&&vm.movimiento.proveedor.Proveedor?vm.movimiento.proveedor.Proveedor:'Sin proveedor';
        var order=vm.movimiento.OrdenCompraCorrelativo?'\nOrden de compra: '+vm.movimiento.OrdenCompraCorrelativo:'';
        var message='Proveedor: '+provider+'\nProductos: '+products.length+order+'\n\n¿Confirma que desea guardar este movimiento?';
        function submitConfirmed(){form.dataset.confirmedSubmit='true';if(form.requestSubmit){form.requestSubmit();}else{form.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}));}}
        if(vm.$swal){vm.$swal({title:'Confirmar movimiento',text:message,icon:'question',type:'question',showCancelButton:true,confirmButtonText:'Sí, guardar',cancelButtonText:'Revisar datos',confirmButtonColor:'#10b981',cancelButtonColor:'#64748b'}).then(function(result){if(result&&(result.value||result.isConfirmed))submitConfirmed();});}
        else if(window.confirm(message)){submitConfirmed();}
      },true);
    }
    if(!heading||document.querySelector('.nm-load-order'))return;
    var button=document.createElement('button');button.type='button';button.className='nm-load-order';button.innerHTML='<i class="fas fa-clipboard-check"></i> Cargar orden de compra';heading.appendChild(button);
    var modal=document.createElement('div');modal.className='nm-order-picker';modal.innerHTML='<div class="nm-order-picker__dialog"><header><span><i class="fas fa-file-invoice"></i></span><div><small>ABASTECIMIENTO / INVENTARIO</small><h2>Seleccionar orden de compra</h2></div><button type="button" data-close><i class="fas fa-times"></i></button></header><div class="nm-order-picker__tools"><input type="search" placeholder="Buscar por correlativo, proveedor o RUC"><span data-count>0 órdenes</span></div><div class="nm-order-picker__list"><div class="nm-order-picker__empty"><i class="fas fa-spinner fa-spin"></i> Cargando órdenes...</div></div></div>';document.body.appendChild(modal);
    var list=modal.querySelector('.nm-order-picker__list'),search=modal.querySelector('input'),count=modal.querySelector('[data-count]'),orders=[];
    function close(){modal.classList.remove('is-open');}
    list.addEventListener('click',function(event){
      var select=event.target.closest('[data-id]');if(!select)return;
      var selected=orders.find(function(item){return String(item.IdOrdenCompra)===String(select.dataset.id);});if(!selected)return;
      setTimeout(function(){
        if(modal.classList.contains('is-open'))return;
        var vm=findVm();if(!vm)return;
        if(vm.$set){vm.$set(vm.movimiento,'IdOrdenCompra',parseInt(selected.IdOrdenCompra,10));vm.$set(vm.movimiento,'OrdenCompraCorrelativo',selected.Correlativo);}
        else{vm.movimiento.IdOrdenCompra=parseInt(selected.IdOrdenCompra,10);vm.movimiento.OrdenCompraCorrelativo=selected.Correlativo;}
      },400);
    });
    function render(){var q=(search.value||'').trim().toLowerCase();var filtered=orders.filter(function(order){return [order.Correlativo,order.Proveedor,order.Ruc,order.Almacen].join(' ').toLowerCase().indexOf(q)!==-1;});count.textContent=filtered.length+' órdenes';list.innerHTML=filtered.length?filtered.map(function(order,index){return '<article class="nm-order-card"><div><small>Orden</small><b>'+order.Correlativo+'</b></div><div><small>Proveedor</small><b>'+order.Ruc+' · '+order.Proveedor+'</b></div><div><small>Fecha</small><b>'+dateText(order.FechaOrdenCompra)+'</b></div><div><small>Almacén</small><b>'+(order.Almacen||'—')+'</b></div><div><small>Total</small><b>'+money(order.Total)+'</b></div><button type="button" data-id="'+order.IdOrdenCompra+'"><i class="fas fa-download"></i> Cargar orden</button></article>';}).join(''):'<div class="nm-order-picker__empty">No se encontraron órdenes activas.</div>';}
    function loadOrders(){list.innerHTML='<div class="nm-order-picker__empty"><i class="fas fa-spinner fa-spin"></i> Cargando órdenes...</div>';var today=new Date().toISOString().slice(0,10);fetch('/api/index.php/ordencompra?fechaDesde=2017-01-01&fechaHasta='+today+'&estado=0').then(function(r){return r.json();}).then(function(data){orders=Array.isArray(data)?data:[];render();}).catch(function(){list.innerHTML='<div class="nm-order-picker__empty">No se pudo cargar el listado.</div>';});}
    button.addEventListener('click',function(){modal.classList.add('is-open');search.value='';loadOrders();setTimeout(function(){search.focus();},120);});search.addEventListener('input',render);modal.querySelector('[data-close]').addEventListener('click',close);modal.addEventListener('click',function(e){if(e.target===modal)close();});
    list.addEventListener('click',function(event){var select=event.target.closest('[data-id]');if(!select)return;var order=orders.find(function(item){return String(item.IdOrdenCompra)===String(select.dataset.id);});var vm=findVm();if(!order||!vm)return;var current=vm.$store.state.cart.productos||[];if(current.length&&!window.confirm('La orden reemplazará los productos actualmente cargados. ¿Desea continuar?'))return;select.disabled=true;select.innerHTML='<i class="fas fa-spinner fa-spin"></i> Cargando';fetch('/api/index.php/ordencompra/detalle?IdOrdenCompra='+order.IdOrdenCompra).then(function(r){return r.json();}).then(function(details){if(!Array.isArray(details)||!details.length)throw new Error('La orden no tiene productos');vm.limpiar();vm.setProveedor(order);vm.$refs.inputProveedor.setValue((order.Ruc?order.Ruc+' - ':'')+order.Proveedor);var cart=vm.$refs.carrito;details.forEach(function(product){product.cantidad=parseFloat(product.Cantidad||product.cantidad)||0;product.precio=parseFloat(product.Precio||product.precio)||0;if(cart&&typeof cart.add==='function')cart.add([product,function(){},{setPrecio:true}]);});setTimeout(function(){var loaded=vm.$store.state.cart.productos||[];loaded.forEach(function(product){var source=details.find(function(detail){return String(detail.IdProducto)===String(product.IdProducto);});if(source){if(vm.$set){vm.$set(product,'cantidad',parseFloat(source.Cantidad||source.cantidad)||0);vm.$set(product,'precio',parseFloat(source.Precio||source.precio)||0);}else{product.cantidad=parseFloat(source.Cantidad||source.cantidad)||0;product.precio=parseFloat(source.Precio||source.precio)||0;}}});if(typeof vm.onAddProducto==='function')vm.onAddProducto();},250);close();var old=document.querySelector('.nm-loaded-order');if(old)old.remove();var banner=document.createElement('div');banner.className='nm-loaded-order';banner.innerHTML='<i class="fas fa-check-circle"></i> Orden '+order.Correlativo+' cargada: '+details.length+' productos de '+order.Proveedor+'.';var shell=document.querySelector('.inventory-shell');shell.insertBefore(banner,shell.children[1]||null);if(vm.$toasted)vm.$toasted.success('Orden de compra cargada correctamente');}).catch(function(error){select.disabled=false;select.innerHTML='<i class="fas fa-download"></i> Cargar orden';if(vm.$toasted)vm.$toasted.error(error.message);});});
  }
  var queued=false;new MutationObserver(function(){if(!queued){queued=true;requestAnimationFrame(function(){queued=false;enhance();});}}).observe(document.documentElement,{childList:true,subtree:true});document.addEventListener('DOMContentLoaded',enhance);enhance();
})();
