(function () {
  'use strict';

  function cleanText(node) {
    return (node.textContent || '').replace(/\s+/g, ' ').trim().toLowerCase();
  }

  function enhance() {
    if (location.hash.indexOf('/reportes/estadisticas') === -1) return;
    document.body.classList.add('nm-statistics-page');

    var candidates = Array.prototype.slice.call(document.querySelectorAll('a, button'));
    var tabs = candidates.filter(function (node) {
      var text = cleanText(node);
      return text === 'ventas por dia' || text === 'ventas por mes' || text === 'ventas por rango';
    });
    if (tabs.length < 3) return;

    var tabsContainer = tabs[0].parentElement;
    while (tabsContainer && !tabs.every(function (tab) { return tabsContainer.contains(tab); })) {
      tabsContainer = tabsContainer.parentElement;
    }
    if (!tabsContainer) return;
    tabsContainer.classList.add('nm-statistics-tabs');

    var shell = tabsContainer.parentElement;
    if (shell) shell.classList.add('nm-statistics-shell');

    if (!document.querySelector('.nm-statistics-hero')) {
      var hero = document.createElement('header');
      hero.className = 'nm-statistics-hero';
      hero.innerHTML = '<div><span class="nm-statistics-hero__eyebrow">REPORTES / ESTADÍSTICAS</span>' +
        '<h1><i class="fas fa-chart-line"></i> Análisis de ventas</h1>' +
        '<p>Consulta el rendimiento diario, mensual y por rangos de fechas.</p></div>' +
        '<span class="nm-statistics-hero__badge"><i class="fas fa-circle" style="font-size:8px;color:#5ff0b2"></i>&nbsp; Información actualizada</span>';
      tabsContainer.parentElement.insertBefore(hero, tabsContainer);
    }

    var icons = ['fa-calendar-day', 'fa-calendar-alt', 'fa-chart-area'];
    tabs.forEach(function (tab, index) {
      if (!tab.querySelector('.nm-tab-icon')) {
        var icon = document.createElement('i');
        icon.className = 'fas ' + icons[index] + ' nm-tab-icon';
        tab.insertBefore(icon, tab.firstChild);
      }
    });
  }

  var queued = false;
  function queueEnhance() {
    if (queued) return;
    queued = true;
    setTimeout(function () { queued = false; enhance(); }, 80);
  }
  document.addEventListener('DOMContentLoaded', enhance);
  window.addEventListener('hashchange', queueEnhance);
  new MutationObserver(queueEnhance).observe(document.documentElement, { childList: true, subtree: true });
  queueEnhance();
})();
