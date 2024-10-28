import Vue from "vue";
import Vuex from "vuex";
import cart from "./modules/cart.js";
import ConfProdPres from "./modules/ConfProdPres.js";

import state from "./state";
import actions from "./actions";
import mutations from "./mutations";

Vue.use(Vuex);

const store = new Vuex.Store({
  state,
  actions,
  mutations,
  modules: {
    cart: cart,
    ConfProdPres
  }
});

export default store;
