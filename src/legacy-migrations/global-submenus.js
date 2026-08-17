(function () {
  "use strict";

  var iconos = [
    [/caja|banco|cuenta/, "fa-wallet"],
    [/gasto|egreso/, "fa-arrow-down"],
    [/ingreso/, "fa-arrow-up"],
    [/salida/, "fa-sign-out-alt"],
    [/dep[oó]sito|transferencia/, "fa-university"],
    [/m[oó]dulo/, "fa-th-large"],
    [/perfil/, "fa-id-badge"],
    [/usuario/, "fa-users"],
    [/resumen/, "fa-chart-pie"],
    [/producto/, "fa-box-open"],
    [/yape|plin|conciliar/, "fa-mobile-alt"],
    [/d[ií]a/, "fa-calendar-day"],
    [/mes/, "fa-calendar-alt"],
    [/rango/, "fa-calendar-check"],
    [/proforma/, "fa-file-invoice"],
    [/favorit/, "fa-star"],
    [/venta/, "fa-shopping-cart"],
    [/reporte/, "fa-chart-bar"],
    [/configur/, "fa-cog"]
  ];

  function claseIcono(texto) {
    var valor = String(texto || "").trim().toLowerCase();
    for (var i = 0; i < iconos.length; i += 1) {
      if (iconos[i][0].test(valor)) return iconos[i][1];
    }
    return "fa-layer-group";
  }

  function modernizar() {
    var app = document.getElementById("app");
    if (!app) return;
    app.querySelectorAll(".nav-tabs, .nav-pills").forEach(function (nav) {
      if (nav.closest(".pos-modern")) return;
      nav.classList.add("app-modern-submenu");
      nav.querySelectorAll(":scope > .nav-item > .nav-link").forEach(function (link) {
        link.classList.add("app-modern-submenu__link");
        if (link.querySelector(":scope > .app-modern-submenu__icon")) return;
        var icono = document.createElement("i");
        icono.className = "fas " + claseIcono(link.textContent) + " app-modern-submenu__icon";
        icono.setAttribute("aria-hidden", "true");
        link.insertBefore(icono, link.firstChild);
      });
    });
  }

  var programado = false;
  function programar() {
    if (programado) return;
    programado = true;
    window.requestAnimationFrame(function () {
      programado = false;
      modernizar();
    });
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", programar);
  else programar();
  new MutationObserver(programar).observe(document.documentElement, { childList: true, subtree: true });
})();
