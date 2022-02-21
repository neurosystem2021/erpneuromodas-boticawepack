<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{idAlmacen: idAlmacen}"
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
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form :showButton="showButton" :cbGuardar="(id) => add([id])"></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>

      <b-modal ref="modalCant" size="xl" id="modalCantidad" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">PRESENTACIONES</h3>
          <h5>{{objProd.Producto}}</h5>
        </div>
        <hr>
        <b-row>
          <b-col cols="6">
            <b-card
              align="center"
              class="mb-3 contentCard"
              @click="setProductoUni(objProd)"
            >
              <b-row>
                <b-col md="7" class="nomCart">
                  <h5 class="text-uppercase">{{medicionPro}}</h5>
                </b-col>
                <b-col md="5" class="text-left">
                  <b>Precio: </b><br>S/ {{ precioCont }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>

          <b-col cols="6" v-for="d in preciosProd" :key="d.id">
            <b-card
              v-if="parseFloat(d.cantidad) > 0 && parseFloat(d.precio) > 0"
              align="center"
              class="mb-3 contentCard"
              :class="{'disabledMouse': !parseFloat(d.cantidad)}"
              @click="() => {
                if ( parseFloat(d.cantidad) ) {
                  setProductoCant(d)
                }
              }"
            >
              <b-row>
                <b-col md="7" class="nomCart" >
                  <h5 class="text-uppercase">x{{ d.cantidad }}</h5>
                </b-col>
                <b-col md="5" class="text-left">
                  <b>Precio: </b>S/ {{ d.precio }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>
      <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="eventPres" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
          <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>
        </template>
        <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 90px"
            step="0.01"
            min="0"
            :disabled="bloquearInput?true:false"
            @input="(val) => changeCant(val.target.value, productos[data.index])"
            placeholder="Cantidad"
            class="form-control"/>
          <span>x{{productos[data.index].cantidadPres}}</span>
        </template>
        <template slot="precioMayor" slot-scope="data" >
          <small style="display: block; width:105px" v-show="productos[data.index].PrecioDocena">Por 12 -> S/{{productos[data.index].PrecioDocena}}</small>
          <small v-show="productos[data.index].PrecioCentena">Por 100 -> S/{{productos[data.index].PrecioCentena}}</small>
        </template>
        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            :disabled="isVentas? true : false"
            type="number"
            style="width: 85px"
            min="0"
            step="0.001"
            @input="(val) => {
              $emit('onInputCantidad', val.target.value)
              if (tipo != 'mov') {
                //productos[data.index].precio = (val >= 12 && val < 100) ? productos[data.index].PrecioDocena : ((val >= 100) ? productos[data.index].PrecioCentena : productos[data.index].PrecioContado)
              }
            }"
            placeholder="Cantidad"
            class="form-control"/>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            type="number"
            style="width: 110px"
            min="0"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"/>
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
          <input
            style="width:85px"
            v-model="productos[data.index].nuevoPrecioContado"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            placeholder="Precio Venta"/>
            <b-form-text>
              <strike> {{ productos[data.index].normalPrecioContado }} </strike>
            </b-form-text>
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            :disabled="bloquearInput?true:false"
            v-model="productos[data.index].descuento"
            style="width:100px"
            type="number"
            min="0"
            step="0.01"
            class="form-control"
            @input="(val)=>ActualizarDesc(data.index,val.target.value)" 
            value="0">
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
          <div v-if="!data.item.EsPadre">{{ data.item.stock | redondear(3) }}</div>
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
      cant: 0,
      precioCont: 0,
      medicionPro:'UNIDAD',
      productoSelect: {},
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    }
  },
  props: {
    setPrecio: Boolean,
    idAlmacen: Number,
    tipo: String,
    isVentas: Boolean,
    bloquearInput:Boolean,
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
          { key: 'CodigoBarra', label: 'Código'},
          { key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo' }, 
          { key: 'ProductoMarca', label: 'Marca' },
          { key: 'ProductoPresentacion', label: 'Present.' }, 
          { key: 'ProductoMedicion', label: 'Medición' },
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
        // sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
        sumatoria = this.sumatoriaTotal / 1.18
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
        this.add([idProducto, (obj) => {
          if (obj) {
            this.$emit('onAddProducto')
            this.$toasted.show('Producto añadido correctamente')
          } else {
            this.$toasted.show('No se encontró ningun producto')
          }
          this.loadCode = ''
        }, { setPrecio: this.setPrecio,  setSuma: false, setPrecioCosto: (this.tipo == 'mov' ? true : false) }, true])
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
        obtenerStockCaja(prod) {
     axios.get(this.API + 'productos/stock/caja', {
        params: {
          idProducto: prod.IdProducto,
          idAlmacen: this.idAlmacen
        }
      }).then((response) => {
        Vue.set(this.productosCaja, prod.IdProducto, {stock: response.data.stock})
        //console.log(response.data.stock)
          //return response.data.stock
      })
      .catch((error) => {
        console.log(error)
      })
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },  
    setProducto (obj) {
      this.precioCont = obj.PrecioContado
      this.medicionPro = obj.ProductoMedicion
      this.loadCode = ''
      this.preciosProd = []
      this.objProd = {}
      console.log(obj);
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || '[]')
      let fPP = this.preciosProd.filter(p => p.cantidad === 0 || p.cantidad === '0')
      if(!this.isVentas){
        this.setProductoUni(obj)
        return
      }
      if(fPP.length==4){
        obj.cantxPrecio=1
        this.setProductoUni(obj)
        return
      }
      this.$refs['modalCant'].show()
      this.objProd = obj
      // Vue.set(this.productos, obj.IdProducto, obj)
    },
    setProductoUni (obj) {
      this.objProd.cantxPrecio = 1
      this.$refs.autocompleteProducto.setValue('')
      //obj.FLETE = 0
      obj.precio = obj.PrecioCosto
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      },{ setPrecioCosto: (this.tipo == 'mov' ? true : false) }])
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs['modalCant'].hide()
      obj.cantidadPres = obj.cantidad
      //this.actualizarProd(obj)
      //this.$refs.barcodeInput.focus()
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
      obj = this.onSetProducto(obj, parseFloat(prod.cantidad),prod.precio)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      }, false, false])
      this.$refs['modalCant'].hide()
      obj.cantidadPres = this.cant
      //this.actualizarProd(obj)
      //this.$refs.barcodeInput.focus()
    },
    showPresentacion (obj) { 
      if (!obj.PreciosPorProducto !== '') {
        this.preciosProd = obj.PreciosPorProducto ? JSON.parse(obj.PreciosPorProducto) : []
        this.objProd = obj
        this.precioCont = obj.PrecioContado
        this.medicionPro = obj.ProductoMedicion
        this.$refs['modalCant'].show()
        return
      }
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = 'ss'
        let classStock = 'table-info'

        if (data.stock <= 0) {
          classStock = 'table-danger'
        }
       
        
        return `
        <style>
          #table-render {
            font-size: 13px;
            margin-bottom: 5px;
            margin-top: 5px;
          }
          #table-render td, #table-render th {
            border: 1px solid #eaeaea;
          }
          #table-render td, #table-render th {
            color: #000000 !important;
          }
        </style>
        <div class="${style}">
          <table id="table-render" style="width: 700px; text-align: center  ">
            <thead>
              <tr style>
                <th class="table-info" style="width: 60px">Stock</th>
                <th>Código.</th>
                <th>Producto.</th>
                <th>Marca.</th>
                <th>Medición.</th>
                <th class="table-success" style="width: 65px">Precio.</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="${classStock}"><b>${data.stock}</b></td>
                <td>${data.CodigoBarra}</td>
                <td>${data.Producto}</td>
                <td>${data.ProductoMarca}</td>
                <td>${data.ProductoMedicion}</td>
                <td class="table-success"><b>${data.PrecioContado}</b></td>
              </tr>
            </tbody>
          </table>   
        </div>
        `
      }
      return `
        <b>${data.CodigoBarra}</b> / <b>${data.Producto}</b> / ${data.ProductoFormaFarmaceutica} / ${data.ProductoMarca} 
      `

     
    },
    setNuevoPrecioContado (index) {

      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio)
      if(this.productos[index].TieneIgv) {
        precio += (precio * 0.18)
      }

      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // precio = precio / (1 - (this.productos[index].PorcentajeUtilidad/100))
      precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))


      let normalPrecio = this.$options.filters.redondear(precio)
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      let nuevoPrecio = this.$options.filters.redondear(precio, 1)
      
      Vue.set(this.productos[index], 'normalPrecioContado', normalPrecio)
      Vue.set(this.productos[index], 'nuevoPrecioContado', nuevoPrecio)
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
      this.actualizarProd(this.productos[index])
    },
    ActualizarDesc(index,val){
      Vue.delete(this.productos[index], 'descuento');
      Vue.set(this.productos[index],'descuento',parseFloat(val))
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

      if (total <= 0) {
        //alert('El total de un producto no puede ser menor o igual a 0')
        Vue.set(this.productos[index], 'descuento', 0)

        //return
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
    align-content: center;
    justify-content: center;
    border-right: 2px solid black;
    align-items: center;
  }
</style>
