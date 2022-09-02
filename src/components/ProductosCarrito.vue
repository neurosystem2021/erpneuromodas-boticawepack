<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos()"
            :customParams="{idAlmacen: idAlmacen, 'sortBy':isVentas?'stock':'IdProducto', 'sortDesc': 1, 'limit':25}"
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
            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
            <producto-form ref="productoFormEdit" :producto-update="productoSelect" :showButton="showButton" :cbGuardar="(id) =>{agregarGuardar(id)}"></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>

      <b-modal ref="modalCant" size="xl" id="modalCantidad" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration: underline;">{{objProd.Producto}}</h3>
          <h5>Precios Disponibles</h5>
        </div>
        <hr>
        <b-row>
          <b-col cols="6" v-if="false">
            <b-card
              align="center"
              class="mb-3 contentCard"
              @click="setProductoUni(objProd)"
            >
              <b-row>
                <b-col md="4" class="nomCart">
                  <h5 class="text-uppercase">UNI</h5>
                </b-col>
                <b-col md="8" class="text-left">
                  <b>Cant: </b>1
                  <br>
                  <b>Pre: </b>S/ {{ precioCont }}
                </b-col>
              </b-row>
            </b-card>
          </b-col>

          <b-col cols="6" v-for="d in preciosProd" :key="d.id">
            <b-card
              v-if="d.cantidad != 0"
              align="center"
              class="mb-3 contentCard"
              :class="{'disabledMouse': !parseFloat(d.cantidad)}"
              @click="() => {
                if ( parseFloat(d.cantidad) ) {
                  setProductoUni(objProd, d.precio)
                }
              }"
            >
              <b-row>
                <b-col md="4" class="nomCart">
                  <h5 class="text-uppercase">{{ d.nombre }}</h5>
                </b-col>
                <b-col md="8" class="text-left">
                  <b>Cant: </b>{{ d.cantidad }}
                  <br>
                  <b>Precio: </b>S/ {{ d.precio }}
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

        <template slot="cantxPrecio" slot-scope="data" v-if="false">
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 75px"
            step="0.1"
            min="0"
            class="form-control"
            @input="(val) => changeCant(val.target.value, productos[data.index])"
            placeholder="Cantidad"/>
          <span>x{{(productos[data.index].cantidadPres)}}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            type="number"
            :disabled="bloquearInput?true:false"
            style="width: 95px"
            step="0.1"
            min="0"
            class="form-control"
            @input="(val) => {
              $emit('onInputCantidad', val.target.value)
              changeCant(val.target.value, productos[data.index])
              if(false){
                if(!productos[data.index].PreciosPorProducto) {
                productos[data.index].precio = (productos[data.index].StockPorMayor && val.target.value >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : productos[data.index].PrecioContado
                return
                }

                let reg = JSON.parse(productos[data.index].PreciosPorProducto).find( e => e.cantidad === productos[data.index].cantidadPres )
                if(reg && reg.cantidad){
                  productos[data.index].precio = reg.precio
                  return
                }
                productos[data.index].precio = productos[data.index].PrecioContado
              }
            }"
            placeholder="Cantidad"/>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="bloquearInput?true:false"
            type="number"
            class="form-control"
            style="width: 95px"
            min="0"
            step="0.01"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"/>
        </template>
        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            @input="setNuevoPrecioContado(data.index)"
            :disabled="bloquearInput?true:false"
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
            style="width:95px"
            v-model="productos[data.index].nuevoPrecioContado"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.1"
            placeholder="Precio Venta"
            class="form-control"/>
            <b-form-text>
              <strike> {{ productos[data.index].normalPrecioContado }} </strike>
            </b-form-text>
        </template>

        <template slot="descuento" slot-scope="data">
          <input
            v-model="productos[data.index].descuento"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.01"
            value="0"
            @input="(val)=>ActualizarDesc(data.index,val.target.value)"   
            class="form-control"/>
            <b-form-text>
            </b-form-text>
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
        </template>
        <template slot="acciones" slot-scope="data">
          <div style="display:inline-flex">
            <b-button style="margin-right:5px" :disabled="bloquearInput?true:false" variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
            <b-button variant="danger" size="sm" :disabled="bloquearInput?true:false" @click="quitarProducto(data.index)">X</b-button>
          </div>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button size="sm" variant="info" :disabled="bloquearInput?true:false" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

        </template>
        <template slot="stock" slot-scope="data">
          <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
          <div v-if="!data.item.EsPadre">{{ (data.item.stock?data.item.stock.toFixed(0):0) }}</div>
        </template>

      </b-table>
    </b-col>
    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="3" offset-md="2" v-if="!noSubtotal">
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
                    :disabled="bloquearInput?true:false"
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
// import Vue from 'vue'

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
      productoSelect: {},
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    }
  },
  props: {
    idAlmacen:Number,
    noSubtotal: Boolean,
    isVentas: Boolean,
    setPrecio: Boolean,
    tipo: String,
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
          { key: 'itemNum', label: '#', class:'text-center' },
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
    },
    
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
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
      add                 : 'agregarProducto',
      buscar              : 'buscarProductos',
      actualizarProd      : 'actualizarProducto',
      eliminar            : 'eliminarProducto',
      actualizarTotal     : 'actualizarTotalCart'
    }),
    APIProductos () {
      if (this.idAlmacen) {
        return this.API + 'productos/puntoventa/stock/' + this.idAlmacen
      } else {
        return this.API + 'productos'
      }
    },
    changeCant (val, prod) {
      /*if (val) {
        prod.cantidad = prod.cantidadPres
        prod.cantidad *= val
      }*/
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
    editarProducto(Pro){
      this.productoSelect = Pro
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarGuardar(id){
      this.productoSelect = {}
        this.add([id, (obj) => {
          if (obj) {
            this.$emit('onAddProducto',obj)
          }
        }, { setPrecioCosto:(this.isVentas?false:true) }])
    },
    setProducto (obj) {
      this.precioCont = obj.PrecioContado
      this.loadCode = ''
      this.preciosProd = []
      if (!this.isVentas) {
        obj.PreciosPorProducto = ''
        this.setProductoUni(obj)
        return
      }
      this.preciosProd.push({nombre:"P. Pub.", cantidad: 1, precio:obj.PrecioContado})
      if(obj.PrecioConvenio){
        this.preciosProd.push({nombre:"P. Dis.", cantidad: 1, precio:obj.PrecioConvenio})
      }
      if(obj.PrecioEspecial){
       this.preciosProd.push({nombre:"P. Esp.", cantidad: 1,  precio:obj.PrecioEspecial})
      }
      this.objProd = {}
      this.objProd = obj
      if(this.preciosProd.length == 1){
        this.setProductoUni(obj,obj.PrecioContado)
      }else{
        this.$refs['modalCant'].show()
      }
    },
    setProductoUni (obj, precio) {
      console.log(precio);
      this.objProd.cantxPrecio = 1
      this.$refs.autocompleteProducto.setValue('')
      //obj.FLETE = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      // //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj,false,precio)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      },{setPrecioCosto:(this.isVentas?false:true)}])
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs['modalCant'].hide()
      obj.cantidadPres = obj.cantidad
      //this.actualizarProd(obj)
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
      // obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj, prod.cantidad)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      }])
      this.$refs['modalCant'].hide()
      obj.cantidadPres = this.cant
      this.actualizarProd(obj)
    },
    ActualizarDesc(index,val){
      Vue.delete(this.productos[index], 'descuento');
      Vue.set(this.productos[index],'descuento',parseFloat(val))
    },
    showPresentacion (obj) {
      if(this.isVentas){
        this.preciosProd = []
        this.preciosProd.push({nombre:"P. Pub.", cantidad: 1, precio:obj.PrecioContado})
        if(obj.PrecioConvenio){
        this.preciosProd.push({nombre:"P. Dis.", cantidad: 1, precio:obj.PrecioConvenio})
        }
        if(obj.PrecioEspecial){
        this.preciosProd.push({nombre:"P. Esp.", cantidad: 1,  precio:obj.PrecioEspecial})
        }
        this.objProd = obj
        this.$refs['modalCant'].show()
        return
      }
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = 'ss'
        if(data.stock <= 0) {
          style = 'danger'
        }
        // cambiar color los que faltan 4 meses por vencer
        if (data.stock > 0) {
          style = 'success'
        }
        return `
        <div>
          <b class="bg-${style}" style="color: #fff; padding: 0 6px">${data.stock>0?data.stock.toFixed(2):data.stock}</b> / 
          <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / <b>${data.ProductoMedicion}</b> -  <b> S/ ${data.PrecioContado || 0} </b> 
        </div>
        `
      }

      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / <b>${data.ProductoMedicion}</b> -  <b> S/ ${data.PrecioContado || 0} </b>
      `
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
    border-right: 2px solid black;
  }
  .form-check-input {
    margin-left: -0.2rem !important;
  }
  #modalCantidad .modal-dialog{
    max-width: 45% !important;
  }
</style>
