<template>
    <b-col md="12">
      <b-row>
        <b-col md="12" v-if="(type === 'mov') ? true : false">
          <b-form-group>
            <b-input-group>
              <autocomplete
                ref="autocompleteProducto"
                style="width:45%"
                :url="APIProductos"
                :onShouldRenderChild="renderChild"
                :customParams="{'sinMovidos': 1}"
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
                <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
                <producto-form :showButton="showButton" :cbGuardar="(id) => add([id])"></producto-form>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row style="height:32vh; overflow-y: scroll; background: #FFFFFF;">
      <b-col md="12">
      <b-table small class="tabla-carrito" responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="selec" slot-scope="data">
          <div v-if="productoSelectIndex==data.index">
            <i class="fas fa-arrow-right fa-2x" style="color:  green; font-weight:bold"></i>
          </div>
          <div v-else>
             <b-button variant="info" @click="cambiarSeleccion(data.index)"><i class="fas fa-hand-pointer"></i></b-button>
          </div>
        </template>
        <template slot="stock" slot-scope="data">
          {{ $options.filters.redondear(data.item.stock, 4) }}
        </template>
        <template slot="cantidad" slot-scope="data">
          <input
            v-bind:class="{ selectionProducto: productoSelectIndex==data.index && accionBoton==2 }"
            v-model="productos[data.index].cantidad"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 110px"
            step="0.000001"
            min="0"
            class="form-control"
            @input="(val) => {
              $emit('onInputCantidad', val.target.value)
              changeCant(val.target.value,productos[data.index])
              //productos[data.index].precio = (productos[data.index].StockPorMayor && val >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : productos[data.index].PrecioContado
            }"
            placeholder="Cantidad"/>
        </template>
        // :readonly="(type === 'mov') ? false : true && idPerfil != 1 || bloquearInput?true:false"
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            type="number"
            min="0"
            style="width: 110px"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"/>
        </template>
        <template slot="subtotal" slot-scope="data">
          <div class="text-center">
            <input
              v-bind:class="{ selectionProducto: productoSelectIndex==data.index && accionBoton==1 }"
              v-model="productos[data.index].sutotal"
              :disabled="bloquearInput?true:false"
              type="number"
              min="0"
              step="0.00001"
              style="width: 110px"
              placeholder="Subtotal"
              @input="val => setSubtotal(data.index, val.target.value)"
              class="form-control"/>
            <b>{{ getSubtotal(data.index) }}</b>
          </div>

        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :disabled="bloquearInput?true:false || idPerfil != 1"
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
            style="width:110px"
            v-model="productos[data.index].nuevoPrecioContado"
            :disabled="bloquearInput?true:false || idPerfil != 1"
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
            v-bind:class="{ selectionProducto: productoSelectIndex==data.index && accionBoton==3 }"
            v-model="productos[data.index].descuento"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 110px"
            min="0"
            step="0.01"
            class="form-control"
            value="0">
            <b-form-text>
            </b-form-text>
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button variant="danger" size="sm" :disabled="bloquearInput?true:false" @click="quitarProducto(data.index)">X</b-button>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button size="sm" variant="info" :disabled="bloquearInput?true:false" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

        </template>
        <template slot="eventPres" slot-scope="data">
          <b-button :disabled="bloquearInput?true:false" size="sm" variant="success" class="mt-1" @click="showPresentacion(productos[data.index])"><i class="fas fa-list-alt"></i></b-button>
        </template>

      </b-table>
      </b-col>
    </b-row>
    <b-row style="border-top: 3px solid #C0C0C0;">
          <b-col md="4" style="border-right: 3px solid rgb(192, 192, 192);">
            <slot name="infoSurtidor"></slot>
            <b-row style="justify-content: center;" v-if="false">
              <table class="calc__numbers">
                <tbody>
                    <tr>
                        <td class="botones" @click="addNumber('7');">7</td>
                        <td class="botones" @click="addNumber('8');">8</td>
                        <td class="botones" @click="addNumber('9');">9</td>
                        <td class="botones" @click="accionBoton=1" v-bind:class="{ 'accionActiva': accionBoton==1 }">Precio</td>
                    </tr>
                    <tr>
                        <td class="botones" @click="addNumber('4');">4</td>
                        <td class="botones" @click="addNumber('5');">5</td>
                        <td class="botones" @click="addNumber('6');">6</td>
                        <td class="botones" @click="accionBoton=2" v-bind:class="{ accionActiva: accionBoton==2 }"><span>Cant.</span></td>
                    </tr>
                    <tr>
                        <td class="botones" @click="addNumber('1');">1</td>
                        <td class="botones" @click="addNumber('2');">2</td>
                        <td class="botones" @click="addNumber('3');">3</td>
                        <td class="botones" @click="accionBoton=3" v-bind:class="{ 'accionActiva': accionBoton==3 }">Desc.</td>
                    </tr>
                    <tr>
                        <td class="botones" @click="addNumber(0);">0</td>
                        <td class="botones" @click="addPoint('.');">.</td>
                        <td colspan="2"  class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                    </tr>
                </tbody>
              </table>
            </b-row>
          </b-col>
          <b-col md="3" class="text-center mt-1">
              <slot name="infoLlevar"></slot>
          </b-col>
          <b-col md="5" class="text-center mt-1">
            <table class="table b-table border table-footer" style="font-size:0.85rem">
              <tbody>
                <tr>
                  <td>
                    <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success" :disabled="EsVale==1 || EsVale==2"><i class="fas fa-credit-card" ></i></b-button>
                    <b-popover target="popover-metodopago" title="Metodo de pago" triggers="focus">
                    <slot name="metodopago"></slot>
                    </b-popover>
                  </td>
                  <td class="table-success" style="vertical-align: middle !important;">SUBTOTAL</td>
                  <td class="table-success font-weight-bold" style="vertical-align: middle !important;">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
                </tr>
                <tr>
                  <td>
                    <b-button class="p-1 ml-1 mt-1" id="popover-opciones" variant="warning"><i class="fas fa-tags" ></i></b-button>
                    <b-popover target="popover-opciones" title="Opciones" triggers="focus" >
                      <slot name="operaciones"></slot>
                    </b-popover>  
                  </td>
                  <td class="table-danger" style="vertical-align: middle !important;">DESCUENTO</td>
                  <td class="table-danger  font-weight-bold" style="vertical-align: middle !important;">{{ sumatoriaDescuento > 0?'-'+sumatoriaDescuento:sumatoriaDescuento  }}</td>
                </tr>
                <tr v-if="sumatoriaIgv > 0">
                  <td></td>
                  <td style="vertical-align: middle !important;"><b>IGV</b></td>
                  <td class="table-danger" style="vertical-align: middle !important;">{{ sumatoriaIgv }}</td>
                </tr>
                <tr>
                  <td></td>
                  <td style="vertical-align: middle !important;"><b>TOTAL</b></td>
                  <td class="table-danger  font-weight-bold" style="vertical-align: middle !important;">{{ sumatoriaTotal }}</td>
                </tr>
                <tr>
                  <td  colspan="3">
                    <b-row>
                      <b-col md="8">
                        <b-input-group prepend="Pagó con" class="pagocon mt-2 mb-2" >
                          <b-form-input
                            type="text"
                            autocomplete="off"
                            v-model="pagocon"
                            @input="asignarVuelto">
                          </b-form-input>
                        </b-input-group>
                      </b-col>
                      <b-col  md="4">
                          <label>Vuelto:</label>
                          <br>
                          <b>S/  {{ vuelto | redondear }}</b>
                      </b-col>
                    </b-row>
                  </td>
                </tr>
              </tbody>
            </table>
          </b-col>
      </b-row>

      <b-modal ref="modalCantC" size="xl" id="modalCantidad" hide-footer hide-header>
          <div class="d-block text-center">
              <h3 style="text-decoration: underline;">{{productoSelect.Producto}}</h3>
              <h5>Precios Disponibles</h5>
          </div>
          <hr>
          <b-row>
              <b-col cols="6">
                  <b-card
                      align="center"
                      class="mb-3 contentCard"
                      style="cursor: pointer;"
                      @click="() => {
                          setProductoUni(productoSelect, true, productoSelect.PrecioContado)
                      }"
                  >
                      <b-row>
                      <b-col md="6" class="nomCartP">
                          <h5 class="text-uppercase">VENTA</h5>
                      </b-col>
                      <b-col md="6" class="nomCartP2">
                          <b class="mr-1">Precio: </b> S/ {{ productoSelect.PrecioContado | moneda }}
                      </b-col>
                      </b-row>
                  </b-card>
              </b-col>
              <b-col cols="6" v-for="d in preciosProd" :key="d.id">
                  <b-card
                      align="center"
                      class="mb-3 contentCard"
                      style="cursor: pointer;"
                      @click="() => {
                      if ( parseFloat(d.precio) > 0 ) {
                          setProductoUni(productoSelect, true, d.precio)
                      }
                      }"
                  >
                      <b-row>
                      <b-col md="6" class="nomCartP">
                          <h5 class="text-uppercase">{{ d.nombre }}</h5>
                      </b-col>
                      <b-col md="6" class="nomCartP2">
                          <b class="mr-1">Precio: </b> S/ {{ d.precio | moneda }}
                      </b-col>
                      </b-row>
                  </b-card>
              </b-col>
          </b-row>
      </b-modal>
  </b-col>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ProductoForm from './ProductoForm'
import config from '@/store/config.js'
import Vue from 'vue'

export default {
  mounted() {
    // this.alterarBuscadorProductos()
  },
  beforeMount(){
    this.cargarProductos()
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
      productosData:[],
      accionBoton: 1,
      productoSelectIndex:null,
      idPerfil: sessionStorage.getItem('IdPerfil'),
      productoSelect: {},
      preciosProd: [],
      actualizarPrecio: false
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    }
  },
  props: {
    type: {
        default: '',
        type: String
    },
    setPrecio: Boolean,
    idAlmacen: Number,
    bloquearInput:Boolean,
    igvTotal: Boolean,
    EsVale: Number,
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
          { key: 'ProductoCategoria', label: 'Categoria' },
          { key: 'Producto', label: 'Artículo'}, 
          { key: 'ProductoMarca', label: 'Marca' },
          { key: 'ProductoMedicion', label: 'Medicion' },
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
        return this.API + 'productos/puntoventa/stock/' + this.idAlmacen
      }
      return this.API + 'productos'
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
        if(val.slice(-1)!='0'){
          prod.cantidad = Number(val)
          this.$nextTick(() => {
            this.actualizarProd(prod)
          })
        }
      }
    },
    addNumber(number){
      if(this.productos.length==0){
          this.productoSelectIndex=null
          return;
      }
      if(this.productoSelectIndex!=null){
        if(this.accionBoton==1){
          this.productos[this.productoSelectIndex].sutotal = Number((this.productos[this.productoSelectIndex].sutotal==undefined?'':(this.productos[this.productoSelectIndex].sutotal.toString()=='0'?'':this.productos[this.productoSelectIndex].sutotal.toString())) + number)
          this.setSubtotal(this.productoSelectIndex, this.productos[this.productoSelectIndex].sutotal )   
        }else if (this.accionBoton==2){
           this.productos[this.productoSelectIndex].cantidad = Number(this.productos[this.productoSelectIndex].cantidad.toString() + number)
          
        }else if(this.accionBoton==3){
          this.productos[this.productoSelectIndex].descuento = Number(this.productos[this.productoSelectIndex].descuento.toString() + number)
        }
        
      }
    },
    cambiarSeleccion(index){
      this.productoSelectIndex=index;
    },
    addPoint(){
        if(this.productos.length==0){
          this.productoSelectIndex=null
          return;
         }
        if(this.accionBoton==1 && !(''+this.productos[this.productoSelectIndex].sutotal).includes('.')){
          this.$nextTick(()=>{
            this.productos[this.productoSelectIndex].sutotal = this.productos[this.productoSelectIndex].sutotal + '.'
          }) 
        }else if (this.accionBoton==2 && !(''+this.productos[this.productoSelectIndex].sutotal).includes('.')){
          this.$nextTick(()=>{
            this.productos[this.productoSelectIndex].cantidad = this.productos[this.productoSelectIndex].cantidad + '.'
          })
          
        }else if(this.accionBoton==3 && !(''+this.productos[this.productoSelectIndex].sutotal).includes('.')){
          this.$nextTick(()=>{
            this.productos[this.productoSelectIndex].descuento = this.productos[this.productoSelectIndex].descuento + '.'
          })
        }
    },
    setAltImg(event) { 
        event.target.src = "static/no-photo.png" 
    },
    deleteNumber(){
      if(this.productos.length==0){
          this.productoSelectIndex=null
          return;
      }
      if(this.productoSelectIndex!=null){
          if(this.accionBoton==1){
          this.productos[this.productoSelectIndex].sutotal =(this.productos[this.productoSelectIndex].sutotal==undefined?'':(this.productos[this.productoSelectIndex].sutotal.toString()=='0'?'':this.productos[this.productoSelectIndex].sutotal.toString().slice(0,-1)))
          this.actualizarProd(this.productos[this.productoSelectIndex])         
        }else if (this.accionBoton==2){
          this.productos[this.productoSelectIndex].cantidad =(this.productos[this.productoSelectIndex].cantidad==undefined?'':(this.productos[this.productoSelectIndex].cantidad.toString()=='0'?'':this.productos[this.productoSelectIndex].cantidad.toString().slice(0,-1)))
          this.actualizarProd(this.productos[this.productoSelectIndex])
        }else if(this.accionBoton==3){
          this.productos[this.productoSelectIndex].descuento =(this.productos[this.productoSelectIndex].descuento==undefined?'':(this.productos[this.productoSelectIndex].descuento.toString()=='0'?'':this.productos[this.productoSelectIndex].descuento.toString().slice(0,-1)))
          this.actualizarProd(this.productos[this.productoSelectIndex])
        }
      }
    },
    cargarProductos(){
      this.axios.get(this.API+'productos/lineal')
      .then((response) => {
        this.productosData = response.data
      })
      .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
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
        }, { setPrecio: this.setPrecio,  setSuma: true, setPrecioCosto: (this.tipo == 'mov' ? true : false) }, true])
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
      this.actualizarPrecio = false
      this.$refs.autocompleteProducto.setValue('')
      obj = Object.assign({}, obj);
      this.productoSelect = obj
      //obj.FLETE = 0
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || '[]')
      this.preciosProd = this.preciosProd.filter(p => p.precio > 0)
      if (this.preciosProd.length==0) {
          this.setProductoUni(obj)
          return
      }
      this.$refs['modalCantC'].show()

/*
      obj = Object.assign({}, obj);
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
      }])

      // Vue.set(this.productos, obj.IdProducto, obj)*/
    },
    setProductoUni(obj, seleccion = false, precio = false){
        obj.precio = 0
        obj.cantidad = 0
        //obj.IdLote = 0
        //obj.TieneIgv = 0
        obj.descuento = 0
        obj.sutotal = ''
        obj.nuevoPrecioContado = 0
        obj = this.onSetProducto(obj, precio)
        let existe = this.productos.find(p=>p.IdProducto == obj.IdProducto)
        if(existe==undefined){
            this.$toasted.info('Producto agregado')
            this.add([obj, () => {
              this.$emit('onAddProducto')
              this.actualizarProd(obj)
            }])
        }else{
          if(this.actualizarPrecio){
            this.$toasted.info('El precio cambiado')
            this.actualizarProd(obj)
            this.actualizarPrecio = false
          }else{
            this.$toasted.error('El producto ya esta agregado')
          }
            
        }
        this.$refs['modalCantC'].hide()
    },
    showPresentacion(obj){
        obj = Object.assign({}, obj);
        this.productoSelect = obj
        this.preciosProd = JSON.parse(obj.PreciosPorProducto || '[]')
        this.preciosProd = this.preciosProd.filter(p => p.precio > 0)
        this.actualizarPrecio = true
        this.$refs['modalCantC'].show()
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = 'ss'

        return `
        <div>
          <b class="bg-success" style="color: #fff; padding: 0 6px">${data.stock>0?data.stock.toFixed(2):0}</b> | <b>${data.CodigoBarra}</b> / 
          <b>${data.Producto}</b> / ${data.ProductoCategoria} 
        </div>
        `
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoMedicion}
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
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
    },
    setSubtotal(index, val) {

      if(val.toString().slice(-1)=='.'){
        return
      }

      let cantidad = this.$options.filters.redondear(val / this.productos[index].precio, 5)
      //let cantidad=2
      // Vue.set(this.productos[index], 'subtotal', val)
      Vue.set(this.productos[index], 'cantidad', cantidad)
      //this.getSubtotal(index)
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
<style scoped>
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
 
    .calc__numbers {
        width:  15.5vw;
        height: 15.5vw;
    }
    .calc__numbers td {
    min-width: 65px;
    min-height: 65px;
    border: 0.7px solid #A3A3A3;
    cursor: pointer;
    text-align: center;
    vertical-align: middle;
    font-size: 0.75rem;
    font-weight: bold;
    font-family: Arial;
    color: black;
    background-color: #fff;
    }
    .calc__numbers td:last-child {
    background-color: #EAEAEA;
    }
    .calc__numbers .botones:hover {
    color: #fff;
    background: #65BA9B;
    }
    .calc__numbers .accionActiva{
        color: #fff !important;
        background: #65BA9B !important;
    }
    .pagocon .input-group-text{
      font-size: 0.8rem;
      font-weight: bold;
    }
    .fontsize{
      font-size: 50px !important;
    }
    .popover {
    max-width: 420px !important;
    }
    .new {
      animation: blink 0.3s ease;
    }
    @keyframes blink {
      0% {
        background-color: yellow;
      }
      100% {
        background-color: white;
      }
    }
</style>
<style>
.selectionProducto{
  background-color: #f9f964 !important;
  font-weight: bold;
}
.danger {
  background-color:#ff7675;
  color: #ffffff;
}
.ambar {
  background-color: #feca57;
  color: #ffffff;
}
.tabla-carrito .table-sm td  {
  font-size: .8rem!important;
}
/* The heart of the matter */
.contenedor {
  display: flex !important;
  flex-wrap: nowrap !important;
  overflow-x: auto !important;
}
.columna {
  flex: 0 0 auto !important;
}
.popover-body{
  background-color: #C1C1C1;
}
.popover {
max-width: 420px !important;
}
</style>

