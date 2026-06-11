
import { API } from '../config.js'
import axios from 'axios'
import Vue from 'vue'

const state = {
  productos: [],
  productosFiltrados: [],
  totalCart: 0
}

const mutations = {
  AGREGAR_PRODUCTO ({ productos }, item) {
    if (item.setSuma) {
      let index = productos.findIndex(prod => prod.IdProducto == item.IdProducto)
      if (index < 0) {
        // Inicializar stock como propiedad reactiva
        Vue.set(item, 'stock', item.stock || 0)
        productos.push(item)
      } else {
        productos[index].cantidad += 1
      }
    } else {
      if(!productos.find( producto => producto.IdProducto === item.IdProducto )) {
        // Inicializar stock como propiedad reactiva
        Vue.set(item, 'stock', item.stock || 0)
        productos.push(item)
      }
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
  },
  APLICAR_DESCUENTO (state, descuento) {
    let cantidadTotal = 0
    state.productos.forEach((prod, index) => {
      cantidadTotal += parseFloat(prod.cantidad)
    })
    state.productos = state.productos.map(prod => {
      prod.descuento = ((descuento / cantidadTotal) * prod.cantidad).toFixed(2)
      return prod
    })
  },
  ACTUALIZAR_TOTAL (state, total) {
    state.totalCart = total
  },
  OBTENER_STOCK (state, items) {
    state.productos = state.productos.map((prod, idx) => {
      let index = items.findIndex((item => item.IdProducto == prod.IdProducto));
      if (index >= 0) {
        Vue.set(prod, 'stock', items[index].stock)
      } else {
        Vue.set(prod, 'stock', 0)
      }
      return prod
    })
  }
}

const actions = {
  agregarProducto ({ commit }, [producto, cb, obje]) {
    let setPrecio = false
    let cantidad = false
    let setSuma = false
    let setPrecioCosto = false
    if (obje) {
      setPrecio = obje.setPrecio
      cantidad = obje.cantidad
      setSuma = obje.setSuma
      setPrecioCosto = obje.setPrecioCosto
    }

    if(typeof producto === 'object') {

      if (setPrecioCosto) {
        producto.precio = producto.PrecioCosto
        producto.nuevoPrecioContado = producto.PrecioContado || 0
      }

      commit('AGREGAR_PRODUCTO', producto)
      if (cb) cb(producto)
    } else {
      axios.get(API + 'productos/id/' + producto).then((response) => {
        if (response.data) {
          let obj = response.data

          if(setPrecio) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
            obj.nuevoPrecioContado = 0
          } else {
            obj.cantidad = 0
            obj.precio = obj.PrecioCosto
            obj.nuevoPrecioContado = obj.PrecioContado || 0
          }

          if (cantidad) {
            obj.cantidad = cantidad
          }

          if (setSuma) {
            obj.setSuma = true
          }

          obj.descuento = 0
          
        
          commit('AGREGAR_PRODUCTO', obj)
          if (cb) cb(obj)   
        } else {
          if (cb) cb(false)
        }
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
  },
  aplicarDescuentoGeneral ({ commit }, descuento) {
    commit('APLICAR_DESCUENTO', descuento)
  },
  actualizarTotalCart ({ commit }, total) {
    commit('ACTUALIZAR_TOTAL', total)
  },
  obtenerStock({ commit, state }, obj) {
    const productos = obj.productos || state.productos
    
    if (!productos || productos.length === 0) {
      return
    }

    axios.get(API + 'productos/stock', {
      params: {
        idProductos: productos.map(prod => prod.IdProducto),
        idAlmacen: obj.IdAlmacen
      }
    }).then((response) => {
        // Actualizar stock en los productos pasados con reactividad
        productos.forEach(prod => {
          const stockItem = response.data.find(item => item.IdProducto == prod.IdProducto)
          if (stockItem) {
            Vue.set(prod, 'stock', stockItem.stock)
          }
        })
        
        // También actualizar en el store si los productos vinieron de allá
        if (!obj.productos) {
          commit('OBTENER_STOCK', response.data)
        }
    })
    .catch((error) => {
      console.log(error)
    })
  }
}

export default {
    state,
    mutations,
    actions
}