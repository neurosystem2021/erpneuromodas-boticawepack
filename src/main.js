// The Vue build version to load with the `import` command
// (runtime-only or standalone) has been set in webpack.base.conf with an alias.
import Vue from 'vue'
import App from './App'
import router from './router'
import store from './store'

import BootstrapVue from 'bootstrap-vue'
import Autocomplete from 'vue2-autocomplete-js'
import axios from 'axios';
import VueAxios from 'vue-axios'
import Toasted from 'vue-toasted';
import VueMoment from 'vue-moment'
import { detect } from 'detect-browser';
import VueQuagga from "vue-quaggajs";
import VueBarcodeScanner from "vue-barcode-scanner";

const browser = detect();

import 'bootstrap/dist/css/bootstrap.css'
import 'bootstrap-vue/dist/bootstrap-vue.css'
import '../static/vue2-autocomplete.css'
import '../static/styles.css'

Vue.prototype.$browser = detect()

Vue.config.productionTip = false

Vue.use(VueAxios, axios)
Vue.use(Toasted, {
  theme: "bubble",
  position: "top-center",
  duration : 3000
})

Vue.use(BootstrapVue);
Vue.use(VueMoment);
Vue.use(VueQuagga);
Vue.use(VueBarcodeScanner);

// Componentes 
Vue.component('autocomplete', Autocomplete)
Vue.component('editable',{
  template:'<div contenteditable="true" @input="update"></div>',
  props:['content'],
  mounted:function(){
    this.$el.innerText = this.content;
  },
  methods:{
    update:function(event){
      this.$emit('update',event.target.innerText);
    }
  }
})


Vue.filter('redondear', (value) => {
  return parseFloat(value || 0).toFixed(2)
})
Vue.filter('redondeoMayor', (value) => {
  return Math.ceil(value || 0)
})
Vue.filter('redondeoMenor', (value) => {
  return Math.floor(value || 0)
})
Vue.filter('moneda', (value, simbolo) => {
  if (simbolo) {
    return simbolo + parseFloat(value || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
  }
  return parseFloat(value || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,')
})
Vue.filter('quitarcodigo', (value) => {
  return value.substring(0, value.lastIndexOf('-'));
})

window.Vue = Vue


/* eslint-disable no-new */
new Vue({
  el: '#app',
  router,
  store,
  components: { App },
  template: '<App/>'
})
