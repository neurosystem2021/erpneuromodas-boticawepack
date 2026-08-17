(function () {
  'use strict';

  var allowOriginalExport = false;
  var profitTimer = 0;

  function reportDateInputs() {
    return Array.prototype.slice.call(document.querySelectorAll('input[type="date"]')).slice(0, 2);
  }

  function isGrossProfitReport() {
    return window.location.hash.indexOf('reporte_utilidad_bruta') !== -1 ||
      window.location.hash.indexOf('reporteutilidadbruta') !== -1;
  }

  function isoToday() {
    var now = new Date();
    var local = new Date(now.getTime() - now.getTimezoneOffset() * 60000);
    return local.toISOString().slice(0, 10);
  }

  function groupByLabel(text) {
    var labels = Array.prototype.slice.call(document.querySelectorAll('label'));
    var label = labels.find(function (item) {
      return (item.textContent || '').replace(/\s+/g, ' ').trim().toUpperCase().indexOf(text) === 0;
    });
    return label ? label.closest('.form-group') || label.parentElement : null;
  }

  function vendorInput() {
    var group = groupByLabel('VENDEDOR') || groupByLabel('USUARIO');
    if (group) return group.querySelector('input, select');
    if (window.location.hash.indexOf('reporte_utilidad_bruta') !== -1) {
      return document.querySelector('select');
    }
    return null;
  }

  function reportFilters() {
    var dates = reportDateInputs();
    var input = vendorInput();
    return {
      start: dates[0] && dates[0].value ? dates[0].value : isoToday(),
      end: dates[1] && dates[1].value ? dates[1].value : isoToday(),
      vendor: input ? input.value.trim() : ''
    };
  }

  function ensureProfitCard() {
    var card = document.querySelector('.nm-gross-profit-card');
    if (!isGrossProfitReport()) {
      if (card) card.remove();
      return null;
    }
    if (card) return card;
    var table = document.querySelector('#tabla-ventas') || document.querySelector('table');
    if (!table) return null;
    card = document.createElement('section');
    card.className = 'nm-gross-profit-card';
    card.innerHTML = '<div class="nm-gross-profit-card__icon"><i class="fas fa-chart-line"></i></div><div class="nm-gross-profit-card__content"><small>Utilidad bruta</small><strong>0.00</strong><span>Todos los vendedores</span></div>';
    var container = table.closest('.col-md-12') || table.parentElement;
    container.parentNode.insertBefore(card, container);
    return card;
  }

  function ensureVendorTools() {
    if (!isGrossProfitReport()) return;
    var group = groupByLabel('VENDEDOR') || groupByLabel('USUARIO');
    if (!group || group.querySelector('.nm-sales-vendor-tools')) return;
    var tools = document.createElement('div');
    tools.className = 'nm-sales-vendor-tools';
    tools.innerHTML = '<button type="button" class="nm-sales-vendor-all">Todos los vendedores</button><span class="nm-sales-vendor-mode">Reporte general</span>';
    group.appendChild(tools);
    tools.querySelector('button').addEventListener('click', function () {
      var input = vendorInput();
      if (!input) return;
      input.value = '';
      input.dispatchEvent(new Event('input', { bubbles: true }));
      input.dispatchEvent(new Event('change', { bubbles: true }));
      scheduleProfit();
    });
  }

  function updateVendorMode(vendor) {
    var mode = document.querySelector('.nm-sales-vendor-mode');
    if (mode) mode.textContent = vendor ? 'Vendedor: ' + vendor : 'Reporte general';
  }

  function loadGrossProfit() {
    if (!isGrossProfitReport()) return;
    var card = ensureProfitCard();
    if (!card) return;
    var filters = reportFilters();
    updateVendorMode(filters.vendor);
    card.classList.add('is-loading');
    card.querySelector('span').textContent = filters.vendor ? 'Vendedor: ' + filters.vendor : 'Todos los vendedores';
    var path = filters.vendor ? 'reporte/utlidadbruta/usuario' : 'reporte/utlidadbruta';
    var query = new URLSearchParams({ fechaInicio: filters.start, fechaFin: filters.end });
    if (filters.vendor) query.set('usuarioReg', filters.vendor);
    fetch('/api/index.php/' + path + '?' + query.toString(), { credentials: 'same-origin' })
      .then(function (response) { if (!response.ok) throw new Error('No se pudo consultar'); return response.json(); })
      .then(function (rows) {
        var profit = Array.isArray(rows) ? rows.reduce(function (sum, row) { return sum + (parseFloat(row.UtilidadBruta) || 0); }, 0) : 0;
        card.querySelector('strong').textContent = profit.toFixed(2);
      })
      .catch(function () { card.querySelector('strong').textContent = 'No disponible'; })
      .finally(function () { card.classList.remove('is-loading'); });
  }

  function scheduleProfit() {
    clearTimeout(profitTimer);
    profitTimer = setTimeout(loadGrossProfit, 300);
  }

  function enhanceSalesReport() {
    ensureVendorTools();
    ensureProfitCard();
    ensureExportButton();
    ensureReportMetrics();
    scheduleProfit();
  }

  function ensureReportMetrics() {
    if (!isGrossProfitReport()) return;
    var tables = Array.prototype.slice.call(document.querySelectorAll('table'));
    var table = tables.find(function (item) {
      return (item.textContent || '').toUpperCase().indexOf('UTILIDAD BRUTA') !== -1;
    });
    if (!table) return;
    table.classList.add('nm-gross-profit-table');
    var headerRow = table.querySelector('thead tr') || table.querySelector('thead');
    if (!headerRow) return;
    var headers = Array.prototype.slice.call(headerRow.querySelectorAll('th'));
    if (!headers.some(function (th) { return (th.textContent || '').indexOf('% Utilidad') !== -1; })) {
      var percentageHeader = document.createElement('th');
      percentageHeader.textContent = '% Utilidad';
      headerRow.appendChild(percentageHeader);
    }
    var rows = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
    var percentageSum = 0;
    var percentageCount = 0;
    rows.forEach(function (row) {
      var cells = row.querySelectorAll('td');
      if (cells.length < 14) return;
      var cost = parseFloat((cells[12].textContent || '').replace(/,/g, '')) || 0;
      var profit = parseFloat((cells[13].textContent || '').replace(/,/g, '')) || 0;
      var percentage = cost !== 0 ? (profit / cost) * 100 : 0;
      var cell = row.querySelector('.nm-profit-percentage');
      if (!cell) {
        cell = document.createElement('td');
        cell.className = 'nm-profit-percentage';
        row.appendChild(cell);
      }
      cell.textContent = percentage.toFixed(2) + '%';
      if (cost !== 0) { percentageSum += percentage; percentageCount++; }
    });
    var footer = table.querySelector('tfoot');
    var totalRow = footer && footer.querySelector('tr:not(.nm-average-row)');
    if (!footer || !totalRow) return;
    var totalCells = totalRow.querySelectorAll('td');
    if (totalCells.length >= 4) {
      var totalCost = parseFloat((totalCells[2].textContent || '').replace(/,/g, '')) || 0;
      var totalProfit = parseFloat((totalCells[3].textContent || '').replace(/,/g, '')) || 0;
      var totalPercentageCell = totalRow.querySelector('.nm-profit-percentage');
      if (!totalPercentageCell) {
        totalPercentageCell = document.createElement('td');
        totalPercentageCell.className = 'nm-profit-percentage';
        totalRow.appendChild(totalPercentageCell);
      }
      var totalPercentage = totalCost !== 0 ? (totalProfit / totalCost) * 100 : 0;
      totalPercentageCell.innerHTML = '<strong>' + totalPercentage.toFixed(2) + '%</strong>';
    }
    var averageRow = footer.querySelector('.nm-average-row');
    if (!averageRow) {
      averageRow = document.createElement('tr');
      averageRow.className = 'nm-average-row';
      averageRow.innerHTML = '<td colspan="11"><strong>Promedio</strong></td><td></td><td></td><td></td><td></td>';
      footer.appendChild(averageRow);
    }
    var averageCells = averageRow.querySelectorAll('td');
    var count = rows.length || 1;
    var sumVenta = parseFloat((totalCells[1] && totalCells[1].textContent || '').replace(/,/g, '')) || 0;
    var sumCosto = parseFloat((totalCells[2] && totalCells[2].textContent || '').replace(/,/g, '')) || 0;
    var sumUtilidad = parseFloat((totalCells[3] && totalCells[3].textContent || '').replace(/,/g, '')) || 0;
    averageCells[1].innerHTML = '<strong>' + (sumVenta / count).toFixed(2) + '</strong>';
    averageCells[2].innerHTML = '<strong>' + (sumCosto / count).toFixed(2) + '</strong>';
    averageCells[3].innerHTML = '<strong>' + (sumUtilidad / count).toFixed(2) + '</strong>';
    var averagePercentage = percentageCount ? percentageSum / percentageCount : 0;
    averageCells[4].innerHTML = '<strong>' + averagePercentage.toFixed(2) + '%</strong>';
  }

  function ensureExportButton() {
    if (!isGrossProfitReport()) return null;
    if (document.querySelector('.nm-gross-profit-export')) return document.querySelector('.nm-gross-profit-export');
    var buttons = Array.prototype.slice.call(document.querySelectorAll('button'));
    var generate = buttons.find(function (button) {
      return (button.textContent || '').replace(/\s+/g, ' ').trim().toUpperCase() === 'GENERAR';
    });
    if (!generate || !generate.parentElement) return null;
    var exportButton = document.createElement('button');
    exportButton.type = 'button';
    exportButton.className = generate.className + ' nm-gross-profit-export';
    exportButton.innerHTML = '<i class="fas fa-file-excel"></i> Exportar';
    exportButton.style.marginLeft = '12px';
    exportButton.style.background = 'linear-gradient(135deg,#078b63,#17c981)';
    generate.parentElement.appendChild(exportButton);
    return exportButton;
  }

  function createModal() {
    var modal = document.querySelector('.nm-sales-export-modal');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.className = 'nm-sales-export-modal';
    modal.hidden = true;
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML = '<section class="nm-sales-export-card">' +
      '<header class="nm-sales-export-head"><span class="nm-sales-export-head__icon"><i class="fas fa-file-excel"></i></span>' +
      '<div><h2>Opciones de exportación</h2><p>Genere el registro de ventas en Excel con todos sus campos.</p></div>' +
      '<button type="button" class="nm-sales-export-close" aria-label="Cerrar">&times;</button></header>' +
      '<div class="nm-sales-export-body"><div class="nm-sales-export-fields">' +
      '<div class="nm-sales-export-field"><label for="nmExportStart">Fecha inicial</label><input id="nmExportStart" type="date"></div>' +
      '<div class="nm-sales-export-field"><label for="nmExportEnd">Fecha final</label><input id="nmExportEnd" type="date"></div></div>' +
      '<p class="nm-sales-export-columns"><strong>Campos incluidos:</strong> ID, vendedor, fecha, código SUNAT, tipo de comprobante, cliente, estado, serie, número, subtotal, IGV y total. Los anulados se identifican y no se suman.</p>' +
      '<div class="nm-sales-export-actions"><button type="button" class="nm-sales-export-action nm-sales-export-action--current"><i class="fas fa-bolt"></i> Exportar actual</button>' +
      '<button type="button" class="nm-sales-export-action nm-sales-export-action--range"><i class="fas fa-calendar-alt"></i> Exportar por fechas</button></div></div></section>';
    document.body.appendChild(modal);

    function close() {
      modal.hidden = true;
      document.body.classList.remove('nm-sales-export-open');
    }

    function runOriginalExport() {
      var button = modal._exportButton;
      close();
      if (!button) return;
      allowOriginalExport = true;
      button.click();
      setTimeout(function () { allowOriginalExport = false; }, 0);
    }

    function exportGrossProfit(start, end) {
      var vendor = reportFilters().vendor;
      var query = new URLSearchParams({ fechaInicio: start, fechaFin: end });
      if (vendor) query.set('usuarioReg', vendor);
      var button = modal.querySelector('.nm-sales-export-action--range');
      button.disabled = true;
      button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando Excel...';
      fetch('/api/index.php/reporte/utlidadbruta/excel?' + query.toString(), { credentials: 'same-origin' })
        .then(function (response) { if (!response.ok) throw new Error('No se pudo generar'); return response.json(); })
        .then(function (url) { window.location.href = url; close(); })
        .catch(function () { alert('No se pudo generar el Excel de utilidad bruta.'); })
        .finally(function () {
          button.disabled = false;
          button.innerHTML = '<i class="fas fa-calendar-alt"></i> Exportar por fechas';
        });
    }

    modal.addEventListener('click', function (event) {
      if (event.target === modal || event.target.closest('.nm-sales-export-close')) close();
      if (event.target.closest('.nm-sales-export-action--current')) {
        var current = reportFilters();
        exportGrossProfit(current.start, current.end);
      }
      if (event.target.closest('.nm-sales-export-action--range')) {
        var start = modal.querySelector('#nmExportStart').value;
        var end = modal.querySelector('#nmExportEnd').value;
        if (!start || !end || start > end) {
          alert('Seleccione un rango de fechas válido.');
          return;
        }
        exportGrossProfit(start, end);
      }
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && !modal.hidden) close();
    });
    return modal;
  }

  document.addEventListener('click', function (event) {
    var button = event.target.closest('button, a');
    if (!button || (button.textContent || '').trim().toUpperCase() !== 'EXPORTAR' || allowOriginalExport) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    var modal = createModal();
    var dates = reportDateInputs();
    modal.querySelector('#nmExportStart').value = dates[0] && dates[0].value ? dates[0].value : isoToday();
    modal.querySelector('#nmExportEnd').value = dates[1] && dates[1].value ? dates[1].value : isoToday();
    modal._exportButton = button;
    modal.hidden = false;
    document.body.classList.add('nm-sales-export-open');
  }, true);

  document.addEventListener('input', function (event) {
    if (event.target.matches('input[type="date"]') || event.target === vendorInput()) scheduleProfit();
  });
  document.addEventListener('change', function (event) {
    if (event.target.matches('input[type="date"]') || event.target === vendorInput()) scheduleProfit();
  });

  var enhanceQueued = false;
  new MutationObserver(function () {
    if (enhanceQueued) return;
    enhanceQueued = true;
    requestAnimationFrame(function () {
      enhanceQueued = false;
      ensureVendorTools();
      ensureProfitCard();
      ensureExportButton();
      ensureReportMetrics();
    });
  }).observe(document.documentElement, { childList: true, subtree: true });

  document.addEventListener('DOMContentLoaded', enhanceSalesReport);
  setTimeout(enhanceSalesReport, 500);
  setTimeout(enhanceSalesReport, 1200);
  setTimeout(enhanceSalesReport, 2500);
  window.addEventListener('hashchange', function () {
    setTimeout(enhanceSalesReport, 250);
    setTimeout(enhanceSalesReport, 900);
  });
})();
