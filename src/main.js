// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from "vue";
import App from "./App";
import router from "./router";
import store from "./store";

import BootstrapVue from "bootstrap-vue";
import Autocomplete from "vue2-autocomplete-js";
import axios from "axios";
import VueAxios from "vue-axios";
import Toasted from "vue-toasted";
import VueMoment from "vue-moment";
import { detect } from "detect-browser";
import VueQuagga from "vue-quaggajs";
import VueBarcodeScanner from "vue-barcode-scanner";

import VueSweetalert2 from 'vue-sweetalert2';

import 'sweetalert2/dist/sweetalert2.min.css';


const browser = detect();

import "bootstrap/dist/css/bootstrap.css";
import "bootstrap-vue/dist/bootstrap-vue.css";
import "../static/vue2-autocomplete.css";
import "../static/styles.css";
import "./assets/css/pos-premium.css";
import "./assets/css/pos-spacing-final.css";
import "./assets/css/operations-modern.css";
import "./assets/css/reports-suite-modern.css";
import "./legacy-migrations/busqueda-modelo.css";
import "./legacy-migrations/caja-bancos-modern.css";
import "./legacy-migrations/estadisticas-modern.css";
import "./legacy-migrations/facturacion-modern.css";
import "./legacy-migrations/inventory-order-loader.css";
import "./legacy-migrations/orden-compra-import.css";
import "./legacy-migrations/proforma-empresa.css";
import "./legacy-migrations/registro-ventas-uniforme.css";
import "./legacy-migrations/reimpresion-documentos.css";
import "./legacy-migrations/remision-modern.css";
import "./legacy-migrations/reporte-cierre-caja.css";
import "./legacy-migrations/reporte-ventas.css";
import "./legacy-migrations/seguridad-modern.css";
import "./legacy-migrations/stock-minimo-export.css";
import "./assets/css/flat-theme.css";
import "./assets/css/modern-listings.css";
import "./assets/css/global-submenus.css";
import "./assets/css/pos-search-compact.css";
import "./assets/css/pos-image-gallery-fix.css";
import "./assets/css/pos-tire-battery.css";
import "./assets/css/petrol-theme.css";

import "./legacy-migrations/busqueda-modelo-page.js";
import "./legacy-migrations/caja-bancos-modern.js";
import "./legacy-migrations/estadisticas-modern.js";
import "./legacy-migrations/facturacion-modern.js";
import "./legacy-migrations/inventory-order-loader.js";
import "./legacy-migrations/orden-compra-import.js";
import "./legacy-migrations/pos-image-preview-fix.js";
import "./legacy-migrations/producto-clonar-correlativo.js";
import "./legacy-migrations/proforma-empresa.js";
import "./legacy-migrations/remision-modern.js";
import "./legacy-migrations/reporte-ventas-page.js";
import "./legacy-migrations/seguridad-modern.js";
import "./legacy-migrations/stock-minimo-export.js";
import "./legacy-migrations/global-submenus.js";

Vue.prototype.$browser = detect();

Vue.config.productionTip = false;
// Vue.use();
Vue.use(VueAxios, axios, VueSweetalert2);
Vue.use(Toasted, {
  theme: "bubble",
  position: "top-center",
  duration: 3000
});

Vue.use(BootstrapVue);
Vue.use(VueMoment);
Vue.use(VueQuagga);
Vue.use(VueBarcodeScanner);
Vue.use(VueSweetalert2);
// Componentes
Vue.component("autocomplete", Autocomplete);
Vue.component("editable", {
  template: '<div contenteditable="true" @input="update"></div>',
  props: ["content"],
  mounted: function() {
    this.$el.innerText = this.content;
  },
  methods: {
    update: function(event) {
      this.$emit("update", event.target.innerText);
    }
  }
});

Vue.filter("redondear", value => {
  return parseFloat(value || 0).toFixed(2);
});
Vue.filter("redondeoMayor", value => {
  return Math.ceil(value || 0);
});
Vue.filter("redondeoMenor", value => {
  return Math.floor(value || 0);
});
Vue.filter("moneda", (value, simbolo) => {
  if (simbolo) {
    return (
      simbolo +
      parseFloat(value || 0)
        .toFixed(2)
        .replace(/\d(?=(\d{3})+\.)/g, "$&,")
    );
  }
  return parseFloat(value || 0)
    .toFixed(2)
    .replace(/\d(?=(\d{3})+\.)/g, "$&,");
});
Vue.filter("quitarcodigo", value => {
  return value.substring(0, value.lastIndexOf("-"));
});

window.Vue = Vue;

/* eslint-disable no-new */
new Vue({
  el: "#app",
  router,
  store,
  components: { App },
  template: "<App/>"
});
