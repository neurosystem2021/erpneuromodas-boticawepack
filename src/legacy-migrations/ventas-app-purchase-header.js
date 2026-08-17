// Fuente del comportamiento aplicado en views/VentasApp.php.
window.setNativePurchaseHeaderHidden = function (hidden) {
  var header = document.getElementById('purchaseInvoiceHeader');
  var button = document.getElementById('purchaseProductListToggle');
  if (!header || !button) return;
  header.hidden = Boolean(hidden);
  button.setAttribute('aria-expanded', hidden ? 'false' : 'true');
  button.innerHTML = hidden
    ? '<i class="fa fa-eye"></i> Mostrar cabecera'
    : '<i class="fa fa-eye-slash"></i> Ocultar cabecera';
};
