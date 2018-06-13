
import { API } from '../config.js'
import axios from 'axios'

const state = {
  productos: [],
  productosFiltrados: []
}

const mutations = {
  AGREGAR_PRODUCTO ({ productos }, item) {
    if(!productos.find( producto => producto.IdProducto === item.IdProducto )) {
      productos.push(item)
    }
  },
  ELIMINAR_PRODUCTO ({ productos }, index) {
    //const itemIndex = productos.findIndex(item => item.IdProducto === producto.IdProducto)
    productos.splice(index, 1)
  },
  LLENAR_PRODUCTOS_FILTRADOS (state, items) {
    state.productosFiltrados = items
  },
  LIMPIAR_CART (state) {
    state.productos = []
  }
}

const actions = {
  agregarProducto ({ commit }, producto) {
    if(typeof producto === 'object') {
      commit('AGREGAR_PRODUCTO', producto)
    } else {
      axios.get(API + 'productos/id/' + producto).then((response) => {
        commit('AGREGAR_PRODUCTO', response.data)        
      })
    }
  },
  eliminarProducto ({ commit }, index) {
    commit('ELIMINAR_PRODUCTO', index)
  },
  buscarProductos ({ commit }, search) {
    axios.get(API + 'productos?limit=10&q=' + search).then((response) => {
      commit('LLENAR_PRODUCTOS_FILTRADOS', response.data)
    })
  },
  limpiarCarrito ({ commit }) {
    commit('LIMPIAR_CART')
  }
}

export default {
    state,
    mutations,
    actions
}