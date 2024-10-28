import { API } from "../config.js";
import axios from "axios";

const state = {
  lstConfiguracionProductoPresentacion: []
};

const mutations = {
  SET_LISTADO_CONFIGURACION(state, data) {
    state.lstConfiguracionProductoPresentacion = data;
  }
};

const actions = {
  async ObtenerListadoConfiguracion({ commit }) {
    await axios.get(API + "configuracion_producto_presentacion").then(res => {
      let data = res.data;
      commit("SET_LISTADO_CONFIGURACION", data);
    });
  },
  async GuardarConfiguracionProductoPresentacion({ dispatch }, data) {
    await axios
      .post(API + "insert_configuracion_producto_presentacion", data)
      .then(res => {
        let data = res.data;
      });
    dispatch("ObtenerListadoConfiguracion");
  }
};

export default {
  state,
  mutations,
  actions
};
