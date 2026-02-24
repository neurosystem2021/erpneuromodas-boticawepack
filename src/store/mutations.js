export default {
  LLENAR_PROVEEDORES_FILTRADOS(state, items) {
    state.proveedoresFiltrados = items;
  },
  LLENAR_ALMACENES_FILTRADOS(state, items) {
    state.almacenesFiltrados = items;
  },
  LISTAR_UBIGEO(state, item) {
    state.listUbigeo = item;
  }
};
