(function () {
  document.addEventListener('input', function (event) {
    var field = event.target;
    if (!field || !field.closest || !field.closest('.va-sale-panel,.va-purchases-panel,.va-modal')) return;
    if (!field.matches('input:not([type="email"]):not([type="date"]):not([type="number"]),textarea')) return;
    var start = field.selectionStart;
    var end = field.selectionEnd;
    var upper = String(field.value || '').toUpperCase();
    if (field.value === upper) return;
    field.value = upper;
    if (start !== null && field.setSelectionRange) field.setSelectionRange(start, end);
  }, true);
})();
