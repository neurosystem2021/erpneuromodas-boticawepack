
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
        productos.unshift(item)
      } else {
        productos[index].cantidad += 1
      }
    } else {
      if(!productos.find( producto => producto.IdProducto === item.IdProducto )) {
        productos.unshift(item)
      }else{
        let index2 = productos.findIndex(prod => prod.IdProducto == item.IdProducto)
        productos.splice(index2, 1)
        productos.splice(index2,0,item)
      }
    }
  },
  ACTUALIZAR_PRECIOCONVENIO ({ productos }, cliente) {
    productos = productos.map(p => {
      if (cliente.EsVIP) {
        p.precio = p.precioConvenio || p.precio
      } else {
        p.precio = p.PrecioContado
      }
      return p;
    })
  },
  ACTUALIZAR_PRODUCTO ({ productos }, prod) {
    let index = productos.findIndex( p => p.IdProducto === prod.IdProducto )
    productos.splice(index, 1, prod)
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
  agregarProducto ({ commit }, [producto, cb, obje, isBarCode, isClienteVip, actualizar, isVentas]) {
    let setPrecio = false
    let cantidad = false
    let precio = false
    let setSuma = false
    let setPrecioCosto = false
    let proforma = false
    if (obje) {
      setPrecio = obje.setPrecio
      cantidad = obje.cantidad
      precio = obje.precio
      setSuma = obje.setSuma
      setPrecioCosto = obje.setPrecioCosto
      proforma = obje.proforma
    }

    if(typeof producto === 'object') {
      if (setPrecioCosto) {
        producto.precio = producto.PrecioCosto
        producto.nuevoPrecioContado = producto.PrecioContado || 0
      }
      if (isClienteVip) {
        producto.precio = producto.precioConvenio || producto.precio;
      }

      let arr = [
        {"desc": producto.ProductoDesc},
        {"desc": producto.ProductoDesc2},
        {"desc": producto.ProductoDesc3}
      ]
      for (let i = 0; i < arr.length; i++) {
        if (arr[i].desc == null) {
          arr[i].desc = ''
        }
        producto.Descripcion = arr[0].desc + '\n' + arr[1].desc + '\n' + arr[2].desc
      }

      // console.log(producto);
      producto.disabledProf = 0
      
      commit('AGREGAR_PRODUCTO', producto)
      if (cb) cb(producto)
    } else {
      let params = {}

      if (isBarCode) {
        params = {codigoBarra: producto}
      } 

      axios.get(API + 'productos/id/' + producto, {params: params}).then((response) => {
        if (response.data) {
          let obj = response.data

          if(setPrecio) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
          } else {
            obj.cantidad = 0
            obj.precio = 0
          }

          if (isClienteVip) {
            producto.precio = producto.precioConvenio || producto.precio;
          }

          if (cantidad) {
            obj.cantidad = cantidad
          }

          if (precio) {
            obj.precio = precio
          }

          if (proforma) {
            obj.estadoPxP = false
            obj.cantxPrecio = obj.cantidad
            obj.cantidadPres = 1
          }

          if (setSuma) {
            obj.setSuma = true
          }

          if(actualizar){
            obj.cantidad = 1
            obj.estadoPxP = false
            obj.precio = obj.PrecioContado
            obj.cantidadPres = 1
            obj.cantxPrecio = 1
            if(!isVentas){
              obj.estadoPxP = true
              obj.cantidad = 0
              obj.precio = obj.PrecioCosto
              obj.nuevoPrecioContado = obj.PrecioContado
            }else{
              obj.nuevoPrecioContado = 0
            }
          }else{
            obj.nuevoPrecioContado = 0
          }

          obj.descuento = 0
          //obj.nuevoPrecioContado = 0
          
          let arr2 = [
            {"desc": obj.ProductoDesc},
            {"desc": obj.ProductoDesc2},
            {"desc": obj.ProductoDesc3}
          ]
          for (let i = 0; i < arr2.length; i++) {
            if (arr2[i].desc == null) {
              arr2[i].desc = ''
            }
            obj.Descripcion = arr2[0].desc + '\n' + arr2[1].desc + '\n' + arr2[2].desc
          }

          commit('AGREGAR_PRODUCTO', obj)
          if (cb) cb(obj)   
        } else {
          if (cb) cb(false)
        }
      })
    }
  },
  actualizarPrecioConvenio ({ commit }, cliente) {
    commit('ACTUALIZAR_PRECIOCONVENIO', cliente);
  },
  actualizarProducto ({ commit }, prod) {
    commit('ACTUALIZAR_PRODUCTO', prod)
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
    axios.get(API + 'productos/stock', {
      params: {
        idProductos: state.productos.map(prod => prod.IdProducto),
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