import { API } from "./config.js";
import axios from "axios";

export default {
  buscarProveedores({ commit }, search) {
    axios.get(API + "proveedores?limit=10&q=" + search).then(response => {
      commit("LLENAR_PROVEEDORES_FILTRADOS", response.data);
    });
  },
  buscarAlmacenes({ commit }, search) {
    axios.get(API + "almacenes?q=" + search).then(response => {
      commit("LLENAR_ALMACENES_FILTRADOS", response.data);
    });
  },
  async GetUbigeo({ commit }, search) {
    await axios.get(API + "/ubigeo/listado").then(response => {
      let listUbigeo = response.data;
      let departamentos = listUbigeo.filter(v => v.nivel == 1);
      //provincias = listUbigeo.filter(v => v.nivel == 2);
      // distritos = listUbigeo.filter(v => v.nivel == 3);

      departamentos.forEach(departamento => {
        departamento.name = departamento.nombre;
        let lstProvincias = listUbigeo.filter(
          v => (v.nivel == 2) & (v.id_padre == departamento.id)
        );
        lstProvincias.forEach(provincia => {
          provincia.name = provincia.nombre;
          let lstDistritos = listUbigeo.filter(
            v => (v.nivel == 3) & (v.id_padre == provincia.id)
          );
          lstDistritos = lstDistritos.map(distrito => {
            distrito.name = distrito.nombre;
            return distrito;
          });
          provincia.children = lstDistritos;
        });
        departamento.children = lstProvincias;
      });

      commit("LISTAR_UBIGEO", departamentos);
    });
  }
};
