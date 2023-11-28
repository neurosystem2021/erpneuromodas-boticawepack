
import { API } from '../config.js'
import axios from 'axios'

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
        productos.push(item)
      } else {
        productos[index].cantidad += 1
      }
    } else {
      if(!productos.find( producto => producto.IdProducto === item.IdProducto )) {
        productos.push(item)
      }else{
        let index2 = productos.findIndex(prod => prod.IdProducto == item.IdProducto)
        productos.splice(index2, 1)
        productos.splice(index2,0,item)
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
      prod.descuento = ((descuento / cantidadTotal) * prod.cantidad).toFixed(3)
      return prod
    })
  },
  ACTUALIZAR_TOTAL (state, total) {
    state.totalCart = total
  },
  OBTENER_STOCK (state, items) {
    state.productos = state.productos.map((prod) => {
      let index = items.findIndex((item => item.IdProducto == prod.IdProducto));
      prod.stock = items[index] && items[index].stock
      return prod
    })
    //state.productos[index].stock = item.stock
    // Vue.set(state.productos[index], 'stock', item.stock)
  }
}

const actions = {
  agregarProducto ({ commit }, [producto, cb, obje]) {
    let setPrecio = false
    let setPrecioCosto = false
    let cantidad = false
    let setSuma = false
    if (obje) {
      setPrecio = obje.setPrecio
      setPrecioCosto = obje.setPrecioCosto
      cantidad = obje.cantidad
      setSuma = obje.setSuma
    }

    if(typeof producto === 'object') {
      producto.nuevoPrecioContado =  producto.PrecioContado || 0
      commit('AGREGAR_PRODUCTO', producto)
      if (cb) cb(producto)
    } else {
      axios.get(API + 'productos/id/' + producto).then((response) => {
        if (response.data) {
          let obj = response.data

          if(setPrecio) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
          } else {
            obj.cantidad = 0
            obj.precio = 0
          }

          if (cantidad) {
            obj.cantidad = cantidad
          }

          if (setSuma) {
            obj.setSuma = true
          }

          if (setPrecioCosto) {
            obj.cantidad = 1
            obj.precio = obj.PrecioCosto || 0
            obj.nuevoPrecioContado = obj.PrecioContado || 0
          }else{
            obj.nuevoPrecioContado = 0
          }

          obj.descuento = 0
          //obj.nuevoPrecioContado = 0
        
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
    var idProductos = state.productos.map(prod => prod.IdProducto);
    axios.get(API + 'productos/stock', {
      params: {
        idProductos: idProductos.length ? idProductos : [1],
        idAlmacen: obj.IdAlmacen
      }
    }).then((response) => {
        commit('OBTENER_STOCK', response.data)
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