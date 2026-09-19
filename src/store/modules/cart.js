
import { API } from '../config.js'
import axios from 'axios'

const state = {
  productos: [],
  productosFiltrados: [],
  totalCart: 0
}

const mutations = {
  AGREGAR_PRODUCTO ({ productos }, item) {
    if (!item.uid) {
      item.uid = 'p_' + Date.now() + '_' + Math.floor(Math.random() * 100000)
    }
    if (item.setSuma) {
      let index = productos.findIndex(prod => prod.IdProducto == item.IdProducto)
      if (index < 0) {
        productos.push(item)
      } else {
        productos[index].cantidad += 1
      }
    } else {
      //if(!productos.find( producto => producto.IdProducto === item.IdProducto )) {
      productos.unshift(item)
      //}
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
      if (prod.EsRegalo) return prod // no se descuenta sobre líneas de regalo
      prod.descuento = ((descuento / 100) * (prod.precio * prod.cantidad)).toFixed(2)
      return prod
    })
  },
  ACTUALIZAR_TOTAL (state, total) {
    state.totalCart = total
  },
  ACTUALIZAR_PRODUCTO ({ productos }, obj) {
    //let index = productos.findIndex( p => p.IdProducto === prod.IdProducto )
    productos.splice(obj.Pos, 1, obj.Producto)
  },
  OBTENER_STOCK (state, items) {
    state.productos = state.productos.map((prod) => {
      let index = items.findIndex((item => item.IdProducto == prod.IdProducto))
      prod.stock = items[index] && items[index].stock
      return prod
    })
  },

  // ================================================================
  // PROMOCIONES: recalcula todas las líneas de regalo desde cero,
  // en base a las cantidades actuales de las líneas "pagadas".
  // ================================================================
  RECALCULAR_PROMOCIONES (state) {
    const lineasPagadas = state.productos.filter(p => !p.EsRegalo)
    const nuevasLineas = [...lineasPagadas]

    lineasPagadas.forEach(p => {
      if (!p.PromoId || !p.PromoCantidadCompra) return

      // La promoción SOLO aplica si la línea se vendió exactamente con la
      // presentación configurada en la promo (ej: promo "por CAJA" no debe
      // activarse si el cliente compró la misma cantidad en UNIDAD suelta)
      if ((p.Presentacion || '') !== (p.PromoUnidad || '')) return

      // El umbral (CantidadCompra) está expresado en unidades base
      // (ej: CAJA x100 -> CantidadCompra=100), así que convertimos
      // cantxPrecio (N° de paquetes comprados) a unidades base antes de comparar
      const unidadesBaseCompradas = parseFloat(p.cantxPrecio || 0) * parseFloat(p.cantidadPress || 1)
      const vecesCumplidas = Math.floor(unidadesBaseCompradas / p.PromoCantidadCompra)
      const cantidadGratis = vecesCumplidas * (p.PromoCantidadRegalo || 1)

      if (cantidadGratis > 0) {
        const uidRegalo = 'r_' + p.uid
        const regaloExistente = state.productos.find(r => r.uid === uidRegalo)
        const mismoProducto = p.IdProducto == p.PromoIdProductoRegalo

        let heredado = {}

        if (regaloExistente && regaloExistente.LoteManual) {
          // El usuario ya eligió el lote a mano: NO lo tocamos
          heredado = {
            hashMovimiento: regaloExistente.hashMovimiento,
            IdLote: regaloExistente.IdLote,
            Lote: regaloExistente.Lote,
            Registro: regaloExistente.Registro,
            FechaVen: regaloExistente.FechaVen,
            stockMovimiento: regaloExistente.stockMovimiento,
            SerieNumero: regaloExistente.SerieNumero,
            fechaVenMovimiento: regaloExistente.fechaVenMovimiento,
            LoteManual: true
          }
        } else if (mismoProducto) {
          // Sigue sincronizado con el principal (comportamiento por defecto)
          heredado = {
            hashMovimiento: p.hashMovimiento,
            IdLote: p.IdLote,
            Lote: p.Lote,
            Registro: p.Registro,
            FechaVen: p.FechaVen,
            stockMovimiento: p.stockMovimiento,
            SerieNumero: p.SerieNumero,
            fechaVenMovimiento: p.fechaVenMovimiento
          }
        }

        nuevasLineas.push(Object.assign({
          uid: 'r_' + p.uid,
          IdLineaOrigen: p.uid,
          IdProducto: p.PromoIdProductoRegalo,
          Producto: (p.PromoProductoRegalo || 'Producto') + ' (REGALO)',
          Presentacion: 'UNIDAD',
          ProductoMarca: p.ProductoMarca || '',
          ProductoFormaFarmaceutica: p.ProductoFormaFarmaceutica || '',
          stock: null,
          ControlaStock: false,
          cantxPrecio: cantidadGratis,
          cantidad: cantidadGratis,
          cantidadPress: 1,
          precio: 0,
          descuento: 0,
          subtotal: 0,
          total: 0,
          TieneIgv: 0,
          EsRegalo: 1,
          NotaVenta: 0
        }, heredado))
      }
    })

    state.productos = nuevasLineas
  }
}

const actions = {
  agregarProducto ({ commit, dispatch }, [producto, cb, obje, isBarCode]) {
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

    if (typeof producto === 'object') {
      commit('AGREGAR_PRODUCTO', producto)
      dispatch('recalcularPromociones')
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
            obj.cantidad = cantidad || 1

            if (obj.cantidad >= obj.StockPorMayor && obj.StockPorMayor !== 0) {
              obj.precio = obj.PrecioPorMayor
            } else {
              obj.precio = obj.PrecioContado || 0
            }

          } else {
            obj.cantidad = 0
            obj.precio = 0
          }

          if (setPrecioCosto) {
            obj.precio = obj.PrecioCosto || 0
            obj.cantidad = 0
          }else{
            obj.precio = obj.PrecioContado || 0
            obj.cantidad = 1
          }

          if (cantidad) {
            obj.cantidad = cantidad
          }

          if (setSuma) {
            obj.setSuma = true
          }

          obj.descuento = 0
          obj.nuevoPrecioContado = 0
          obj.cantxPrecio = obj.cantxPrecio || obj.cantidad || 1
          commit('AGREGAR_PRODUCTO', obj)
          dispatch('recalcularPromociones')
          if (cb) cb(obj)
        } else {
          if (cb) cb(false)
        }
      })
    }
  },
  eliminarProducto ({ commit, dispatch, state }, index) {
    const item = state.productos[index]

    // No permitir borrar una línea de regalo manualmente: se gestiona sola
    if (item && item.EsRegalo) return

    commit('ELIMINAR_PRODUCTO', index)
    dispatch('recalcularPromociones')
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
  actualizarProducto ({ commit, dispatch }, obj) {
    commit('ACTUALIZAR_PRODUCTO', obj)
    dispatch('recalcularPromociones')
  },
  obtenerStock ({ commit, state }, obj) {
    axios.get(API + 'productos/puntoventa/stock', {
      params: {
        idProductos: state.productos.filter(p => !p.EsRegalo).map(prod => prod.IdProducto),
        idAlmacen: obj.IdAlmacen,
        noLimit: 1
      }
    }).then((response) => {
      if (response.data) {
        commit('OBTENER_STOCK', response.data)
      }
    })
      .catch((error) => {
        console.log(error)
      })
  },
  recalcularPromociones ({ commit }) {
    commit('RECALCULAR_PROMOCIONES')
  }
}

export default {
  state,
  mutations,
  actions
}