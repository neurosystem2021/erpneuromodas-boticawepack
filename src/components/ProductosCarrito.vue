<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{ limit: '25' }"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{input: 'form-control'}">
          </autocomplete>
          <input v-model="loadCode"
                  @keydown.enter.prevent="loadBarcode"
                  ref="barcodeInput"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <producto-form :showButton="showButton" :cbGuardar="(id) => add([id])"></producto-form>
          </b-input-group-append>
        </b-input-group>
        <b-badge v-if="isVentas" variant="danger">Productos sin stock</b-badge>
      </b-form-group>

      <b-modal size="lg" ref="modalCant" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{objProd.Producto}}</h3>
          <h5>Presentaciones</h5>
        </div>

        <b-row>
          <b-col cols="6">
            <b-card
              align="center"
              class="mb-3 contentCard"
              @click="setProductoUni(objProd)"
            >
              <b-row>
                <b-col md="4" class="nomCart">
                  <h5 class="text-uppercase">{{medicionPro}}</h5>
                </b-col>
                <b-col md="8" class="text-left">
                  <b>Precio Unitario: </b><br>S/ {{ precioCont }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>

          <b-col cols="6" v-for="d in preciosProd" :key="d.id">
            <b-card
              align="center"
              class="mb-3 contentCard"
              :class="{'disabledMouse': d.cantidad <= 0}"
              @click="() => {
                if (!(d.cantidad == 0)) {
                  setProductoCant(d)
                }
              }"
            >
              <b-row>
                <b-col md="4" class="nomCart" >
                  <h5>x{{ d.cantidad }}</h5>
                </b-col>
                <b-col md="8" class="text-left">
                  <b>Precio Unitario: </b>S/ {{ d.precio }}
                  <br>
                  <b>Total: </b>S/ {{ $options.filters.redondear(d.precio *  d.cantidad)  }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>

      <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">

        <template slot="eventPres" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
          <b-button size="sm" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-pencil-alt"></i></b-button>
        </template>

        <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
          <b-form-input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 80px"
            step="0.01"
            min="0"
            @input="(val) => changeCant(val, productos[data.index])"
            placeholder="Cantidad">
          </b-form-input>
          <span>x{{productos[data.index].cantidadPres}}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].cantidad"
            :disabled="productos[data.index].PreciosPorProducto !== ''"
            type="number"
            style="width: 80px"
            step="0.01"
            min="0"
            @input="(val) => {
              $emit('onInputCantidad', val)
              /*if(!productos[data.index].PreciosPorProducto) {
                productos[data.index].precio = (productos[data.index].StockPorMayor && val >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : productos[data.index].PrecioContado
                return
              }

              let reg = JSON.parse(productos[data.index].PreciosPorProducto).find( e => e.cantidad === productos[data.index].cantidadPres )
              if(reg && reg.cantidad){
                productos[data.index].precio = reg.precio
                return
              }
              productos[data.index].precio = productos[data.index].PrecioContado */
            }"
            placeholder="Cantidad"></b-form-input>
        </template>

        <template slot="precio" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].precio"
            type="number"
            style="width: 120px"
            min="0"
            step="0.0001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"></b-form-input>
        </template>
        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0">
            <div>{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>
        <!--<template slot="flete" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FLETE"
            type="number"
            min="0"
            placeholder="Flete"></b-form-input>
        </template>-->
        
        <!--<template slot="lote" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].IdLote"
            type="text"
            min="0"
            placeholder="Lote"></b-form-input>
        </template>-->
        <template slot="precioVenta" slot-scope="data">
          <b-form-input
            style="width:80px"
            v-model="productos[data.index].nuevoPrecioContado"
            type="number"
            min="0"
            step="0.01"
            placeholder="Precio Venta"></b-form-input>
            <b-form-text>
              <strike> {{ productos[data.index].normalPrecioContado }} </strike>
            </b-form-text>
        </template>

        <template slot="descuento" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].descuento"
            type="number"
            min="0"
            step="0.01"
            :value="0"></b-form-input>
            <b-form-text>
            </b-form-text>
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button variant="danger" size="sm" @click="quitarProducto(data.index)">X</b-button>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

        </template>
        <template slot="stock" slot-scope="data">
          <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
          <div v-if="!data.item.EsPadre">{{ data.item.stock | redondear(2) }}</div>
        </template>

      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="3" offset-md="2">
          <table class="table b-table border table-footer">
            <tbody>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-success">SubTotal</td>
                <td class="table-success">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
              </tr>
              <tr v-if="sumatoriaDescuento > 0">
                <td class="table-danger">Descuento</td>
                <td class="table-danger">- {{ sumatoriaDescuento  }}</td>
              </tr>
              <tr>
                <td><b>SubTotal</b></td>
                <td class="table-danger">{{ sumatoriaSubTotal  }}</td>
              </tr>
              <tr v-if="sumatoriaIgv > 0">
                <td><b>IGV</b></td>
                <td class="table-danger">{{ sumatoriaIgv }}</td>
              </tr>
              <tr>
                <td><b>TOTAL</b></td>
                <td class="table-danger">{{ sumatoriaTotal }}</td>
              </tr>
              <tr>
                <td  colspan="2">
                  <label>pagó con:</label>
                  <b-form-input
                    type="text"
                    placeholder="Pagó con"
                    v-model="pagocon"
                    @input="asignarVuelto"></b-form-input><br>
                  <label>Vuelto:</label>
                  <b>S/  {{ vuelto | redondear }}</b>
                </td>
              </tr>
            </tbody>
          </table>
    </b-col>
    <br><br>
  </b-row>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ProductoForm from './ProductoForm'
import config from '@/store/config.js'

export default {
  mounted() {
    // this.alterarBuscadorProductos()
  },
  data () {
    return {
      API: config.API,
      loadCode: '',
      pasteCode: false,
      producto: {},
      items: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      preciosProd: [],
      objProd: {},
      precioCont: 0,
      medicionPro:'UNIDAD',
      cant: 0,
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    }
  },
  props: {
    isVentas: Boolean,
    setPrecio: Boolean,
    idAlmacen: Number,
    igvTotal: Boolean,
    onSetProducto: {
      type: Function,
      default (obj) {
        return obj
      }
    },
    showButton: {
      default: true,
      type: Boolean
    },
    fields: {
      default () {
        return [
          { key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
          { key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo' }, 
          { key: 'ProductoMarca', label: 'Marca' },
          { key: 'ProductoModelo', label: 'Modelo/Color' }, 
          // { key: 'Color', label: 'Color' }, 
          // { key: 'ProductoTalla', label: 'Talla' },
          // { key: 'Genero' },
          // { key: 'Botapie' },
          { key: 'cantidad' },
          { key: 'precio', label: 'P/U' },
          { key: 'subtotal', label: 'SubTotal', variant: 'success' },
          { key: 'igv', label: 'IGV' },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: 'precioVenta', label: 'Precio venta' },
          { key: 'total', label: 'Total', variant: 'danger' },
          { key: 'acciones', label: ''}
        ]
      },
      type: Array
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
    APIProductos () {
      if (this.idAlmacen) {
        return this.API + 'productos/stock/' + this.idAlmacen
      } else {
        return this.API + 'productos'
      }
    },
    sumatoriaSubTotal () {
      let sumatoria = 0
      if (this.igvTotal) {
        sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
      } else {
        this.productos.map((value) => {
          sumatoria += parseFloat(value.subtotal)
          if (value.descuento) {
            sumatoria -= value.descuento
          }
        })
      }
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaTotal () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.total)
      })
      
      this.actualizarTotal(sumatoria)
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaIgv () {
      let sumatoria = 0
      if (this.igvTotal) {
        sumatoria = this.sumatoriaTotal
        // sumatoria = sumatoria * 0.18
        sumatoria = sumatoria - (sumatoria/1.18)
      } else {
        this.productos.map((value) => {
          sumatoria += parseFloat(value.igv)
        })
      }
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaDescuento () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.descuento || 0)
      })
      return this.$options.filters.redondear(sumatoria)
    }
  },
  methods: {
    ...mapActions({
      add: 'agregarProducto',
      buscar: 'buscarProductos',
      eliminar: 'eliminarProducto',
      actualizarTotal: 'actualizarTotalCart',
      actualizarProd : 'actualizarProducto',
    }),
    changeCant (val, prod) {
      if (val) {
        prod.cantidad = prod.cantidadPres
        prod.cantidad *= val
      }
      this.$nextTick(() => {
        this.actualizarProd(prod)
      })
    },
    asignarVuelto () {
      this.vuelto = this.pagocon - this.sumatoriaTotal
    },
    loadBarcode (e) {
      e.preventDefault()
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = {codigoBarra: idProducto}
        this.axios.get(this.API + 'productos/id/' + idProducto, {params: params})
        .then((response) => {
          if (response.data.IdProducto) {
            this.setProducto(response.data)
          } else {
            this.loadCode = ''
            this.$toasted.show('No se encontró ningun producto')    
          }
        })
      }
    },
    /*alterarBuscadorProductos() {
      this.handleKeyDown = this.$refs.autocompleteProducto.handleKeyDown

      this.$refs.autocompleteProducto.$refs.input.onpaste = (e) => {
        console.log('paste')
        this.pasteCode = (e.originalEvent || e).clipboardData.getData('text/plain')
      }

      this.$refs.autocompleteProducto.handleKeyDown = (e) =>{
        let key = e.keyCode;

        if (key == 13) {
          if(this.pasteCode) {
            e.preventDefault()
            let idProducto = this.pasteCode.substring(2, this.pasteCode.length -2);
            this.add([idProducto, () => {
              this.$emit('onAddProducto')
            }])
          } else {
            e.preventDefault()
            this.handleKeyDown(e) 
          }   
        } else {
          this.handleKeyDown(e)
        }
        this.pasteCode = false
      }
    },*/
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },  
    setProducto (obj) {
      this.loadCode = ''
      this.preciosProd = []
      this.objProd = {}
      this.precioCont = obj.PrecioContado
      this.medicionPro = obj.ProductoMedicion

      if (!this.isVentas) {
        obj.PreciosPorProducto = '';
      }

      if (!obj.PreciosPorProducto) {
        this.setProductoUni(obj)
        return
      }

      
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || '[]')
      this.preciosProd = this.preciosProd.filter(p => p.cantidad !== 0  && p.cantidad !=='0' && p.cantidad !=='')
      
      if(this.preciosProd.length==0){
        obj.cantxPrecio = 1
        this.setProductoUni(obj)
        return
      }
      this.$refs['modalCant'].show()
      this.objProd = obj
    },
    setProductoUni (obj) {
      this.objProd.cantxPrecio = 1
      this.$refs.autocompleteProducto.setValue('')
      //obj.FLETE = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      }])
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs['modalCant'].hide()
      obj.cantidadPres = obj.cantidad
      this.actualizarProd(obj)
      this.$refs.barcodeInput.focus()
    },
    setProductoCant (prod) {
      this.objProd.cantxPrecio = 1
      this.cant = prod.cantidad
      let obj = this.objProd
      this.$refs.autocompleteProducto.setValue('')
      //obj.FLETE = 0
      // this.objProd.precio = 0
      // this.objProd.cantidad = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj, prod.cantidad)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      }])
      this.$refs['modalCant'].hide()
      obj.cantidadPres = this.cant
      this.actualizarProd(obj)
      this.$refs.barcodeInput.focus()
    },
    showPresentacion (obj) {
      if (!obj.PreciosPorProducto !== '') {
        this.preciosProd = obj.PreciosPorProducto ? JSON.parse(obj.PreciosPorProducto) : []
        this.preciosProd = this.preciosProd.filter(p => p.cantidad !== 0  && p.cantidad !=='0' && p.cantidad !=='')
        this.precioCont = obj.PrecioContado
        this.medicionPro = obj.ProductoMedicion
        this.objProd = obj
        this.$refs['modalCant'].show()
        return
      }
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = '';
        let styleBag = 'ss'
        if (parseFloat(data.stock) > 0) {
          styleBag  = 'success'
        }
        if (parseFloat(data.stock)<= 0) {
          styleBag  = 'danger'
        }

        return `
        <div style="${style}">
          <b class="bg-${styleBag}" style="color: #fff; padding: 0 6px">${data.stock? Number.isInteger(data.stock)?data.stock:data.stock.toFixed(2):data.stock}</b> /
          <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / <b>${data.ProductoMedicion}</b> / <b> S/ ${data.PrecioContado || 0} </b>
        </div>  
        `;
      } else {

        return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / <b>${data.ProductoMedicion}</b> / <b> S/ ${data.PrecioContado || 0} </b>
        `
      }


    },
    setNuevoPrecioContado (index) {

      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio)
      if(this.productos[index].TieneIgv) {
        precio += (precio * 0.18)
      }

      precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))


      let normalPrecio = this.$options.filters.redondear(precio)
      let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      
      Vue.set(this.productos[index], 'normalPrecioContado', normalPrecio)
      Vue.set(this.productos[index], 'nuevoPrecioContado', nuevoPrecio)
      this.getSubtotal(index)
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
    },
    getSubtotal(index) {
      
      let subtotal = this.$options.filters.redondear(this.productos[index].cantidad * this.productos[index].precio)
      Vue.set(this.productos[index], 'subtotal', subtotal)
      // this.productos[index].subtotal = subtotal
      return subtotal
    },
    getIgv (index) {
      let igv = 0
      if (this.productos[index].TieneIgv) {
        igv = this.$options.filters.redondear(this.productos[index].subtotal * 0.18)
      }

      Vue.set(this.productos[index], 'igv', igv)
      // this.productos[index].igv = igv
      return igv
    },
    getTotal (index) {
      let total = parseFloat(this.productos[index].subtotal)

      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE)
      }
      if (this.productos[index].TieneIgv) {
        total += parseFloat(this.productos[index].igv)
      }
      if (this.productos[index].descuento) {
        total -= parseFloat(this.productos[index].descuento)
      }

      total =  this.$options.filters.redondear(total)
      Vue.set(this.productos[index], 'total', total)
      // this.productos[index].total = total
      return total
    },
    getData (value) {
      this.buscar(value)
    },
    quitarProducto (index) {
      this.eliminar(index)
      this.$emit('onRemoveProducto', index)
      this.$toasted.show('Producto quitado del listado')
    }
  },
  components: {
    ProductoForm
  }
}
</script>

<style>
  .contentCard {
    cursor: pointer;
  }
  .disabledMouse {
    background: #CECECE;
    cursor: inherit;
  }
  .nomCart {
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 2px solid black;
  }
</style>
