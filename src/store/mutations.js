export default {
  LLENAR_PROVEEDORES_FILTRADOS(state, items) {
    state.proveedoresFiltrados = items;
  },
  LLENAR_ALMACENES_FILTRADOS(state, items) {
    state.almacenesFiltrados = items;
  },
  GUARDAR_EMPRESAS(state, data) {
    state.empresas = data;
  }
};
