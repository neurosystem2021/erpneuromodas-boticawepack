import { API } from '../config.js'

const state = {
  productos: []
}
  
const mutations = {
  AGREGAR_PRODUCTO ({ productos }, producto) {
    productos.push(producto)
  },
  ELIMINAR_PRODUCTO ({ productos }, ) {
    const itemIndex = productos.findIndex(item => item.IdProducto === producto.IdProducto)
    productos.splice(itemIndex, 1)
  }
}

const actions = {
  /*cargarProductos ({ commit }) {
    axios.get(API + 'productos?limit'
  },*/
  agregarProducto22 ({ commit }, producto) {
    commit('AGREGAR_PRODUCTO', producto)
  },
  eliminarProducto ({ commit }, producto) {
    commit('ELIMINAR_PRODUCTO', producto)
  }
}

export default {
    state,
    mutations,
    actions
}