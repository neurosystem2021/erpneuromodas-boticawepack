import { API } from "./config.js";
import axios from "axios";

export default {
  buscarProveedores({ commit }, search) {
    axios.get(API + "proveedores?limit=10&q=" + search).then(response => {
      commit("LLENAR_PROVEEDORES_FILTRADOS", response.data);
    });
  },
  async buscarSeriesxProduto({ commit }, search) {
    let res = [];
    await axios
      .get(API + "productos/serie", { params: search })
      .then(response => {
       
        res = response.data;
      });
    return res;
  },
  buscarAlmacenes({ commit }, search) {
    axios.get(API + "almacenes?q=" + search).then(response => {
      commit("LLENAR_ALMACENES_FILTRADOS", response.data);
    });
  },
  //   GUARDAR_EMPRESAS
  obtenerEmpresa({ commit }) {
    axios.get(`${API}/empresas`).then(res => {
      commit("GUARDAR_EMPRESAS", res.data);
    });
  }
};
