export default {
    LLENAR_PROVEEDORES_FILTRADOS (state, items) { 
        state.proveedoresFiltrados = items
    },
    LLENAR_ALMACENES_FILTRADOS (state, items) {
        state.almacenesFiltrados = items
    },
    AGREGAR_PRODUCTO(state,item){
      state.cart.productos.push(item);  
    },
    ELIMINAR_PRODUCTO(state,item){
      state.cart.productos.slice(item,1);  
    }
}