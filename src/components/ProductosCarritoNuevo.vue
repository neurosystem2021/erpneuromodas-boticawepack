<template>
    <b-col md="12">
        <b-modal ref="modalIndi" size="xl" id="modalIndicacion" hide-footer hide-header>
          <div class="d-block text-center">
            <h5 class="font-weight-normal">Indicaciones para:</h5>
            <h4>{{productoSelect.Producto}}</h4>
          </div>
          <hr>
          <b-row>
             <b-col md="12">
                <textarea 
                class="form-control"
                style="text-transform: uppercase;"
                cols="9" rows="3"
                v-model="productoSelect.indicacion">
                </textarea>
            </b-col>
          </b-row>
          <br>
          <b-row>
            <b-col md="12">
              <b-form-group>
              <label>Indicaciones Comunes:</label>
                <autocomplete
                  ref="autocompleteIndicacion"
                  :url="apiInidicaciones"
                  :customParams="{ limit: '25' }"
                  anchor="Indicaciones"
                  :onShouldRenderChild="renderChild"
                  label="writer"
                  placeholder="Agregar indicación"
                  :on-select="setIndicacion"
                  :onFocus="focusAutocomplete.bind(null, 'autocompleteIndicacion')"
                  :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>
            <b-col class="text-center">
              <b-button variant="info" size="md" @click="()=>{$refs['modalIndi'].hide()}">Cerrar</b-button>
            </b-col>
          </b-row>
        </b-modal>
        <b-row style="height:34vh; overflow-y: scroll; background: #FFFFFF;">
            <b-col md="12">
                <b-table id="carrito" small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
                <template slot="Indicaciones" slot-scope="data">
                  <textarea 
                    :disabled="bloquearInput?true:false || esDelivery"
                    class="form-control"
                    style="width: 120px; font-size:0.8rem; text-transform: uppercase;"
                    cols="9" rows="3"
                    v-model="productos[data.index].indicacion"></textarea>
                </template>

                <template slot="eventPres" slot-scope="data">
                  <b-button :disabled="bloquearInput?true:false || esDelivery" size="sm" variant="info" @click="showIndicaciones(productos[data.index])"><i class="fas fa-pencil-alt"></i></b-button>
                </template>

                <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
                  <div style="display: flex; justify-content: space-evenly; align-items: center;">
                    <input
                    v-model="productos[data.index].cantxPrecio"
                    type="number"
                    style="width: 100px"
                    step="0.001"
                    min="0"
                    :disabled="bloquearInput?true:false"
                    @input="(val) => changeCant(val.target.value, productos[data.index], data.index)"
                    placeholder="Cantidad"
                    class="form-control"/>
                  <span>x{{productos[data.index].cantidadPres}}</span>
                  </div>
                </template>

                <template slot="cantidad" slot-scope="data">
                <input
                    v-model="productos[data.index].cantidad"
                    type="number"
                    style="width: 90px; font-weight:bold"
                    :disabled="bloquearInput?true:false || esDelivery || true"
                    min="0"
                    step="1"
                    autocomplete="off"
                    v-bind:class="{ new: data.item.IdProducto==productoChange }"
                    @input="(val) => {
                    changeCant(val.target.value,productos[data.index])
                    $emit('onInputCantidad', val.target.value)
                      if(!productos[data.index].PreciosPorProducto) {
                        return
                      }
                      if(!precioHabilitado){
                        let arr = JSON.parse(productos[data.index].PreciosPorProducto || '[]');
                        arr.map((r)=>{
                          r.cantidad = Number(r.cantidad || 0)
                          r.precio = Number(r.precio || 0)
                        })
                        let arreglado = arr.sort((a,b) => Number(a.cantidad) - Number(b.cantidad))
                        let cambio = false
                        arreglado.map(r=>{
                          if(r.cantidad>0 && r.precio>0 && val.target.value >=r.cantidad){
                            productos[data.index].precio = r.precio
                            cambio = true
                          }
                        })
                        if(!cambio){
                          productos[data.index].precio = productos[data.index].PrecioContado
                        }
                      } 
                    }"
                    class="form-control"
                    placeholder="Cantidad"/>
                </template>
                <template slot="precio" slot-scope="data">
                <input
                    v-model="productos[data.index].precio"
                    :disabled="(bloquearInput?true:false) || esDelivery || (precioHabilitado?false:true)"
                    type="number"
                    style="width: 90px"
                    min="0"
                    step="0.001"
                    placeholder="Precio"
                    autocomplete="off"
                    @input="setNuevoPrecioContado(data.index)"
                    :v-on="setNuevoPrecioContado(data.index)"
                    class="form-control"
                    />
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

                <template slot="descuento" slot-scope="data">
                <input
                    :disabled="bloquearInput?true:false || esDelivery || (precioHabilitado==false?false:true)"
                    v-model="productos[data.index].descuento"
                    type="number"
                    min="0"
                    step="0.01"
                    style="width: 80px"
                    @input="(val)=>ActualizarDesc(data.index,val.target.value)"    
                    value="0"
                    class="form-control"/>
                    <b-form-text>
                    </b-form-text>
                </template>

                <template slot="total" slot-scope="data">
                {{ getTotal(data.index) }}
                <br>
                <!--<h6 v-if="isVentas"><i>Prof.</i></h6>
                <input v-if="isVentas"
                    class="form-check-input" 
                    type="checkbox" 
                    :disabled="bloquearInput?true:false"
                    @change="(e) => {
                    $emit('onCheckProf', e)
                    asignarDisabledProf(data.index, e)
                    }"
                />-->
                </template>
                <template slot="acciones" slot-scope="data">
                <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" @click="quitarProducto(data.index)"><i class="fas fa-times fa-lg"></i></b-button>
                </template>

                <template slot="show_details" slot-scope="row">
                <b-button size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
                </template>
                <template slot="row-details" slot-scope="row">

                </template>

                <template slot="stock" slot-scope="data">
                <div v-if="data.item.CodigoBarra=='COSTODELIVERY'">
                    <img src="static/moto-delivery.png" style="width: 24px" />
                </div>
                <div v-else>
                    <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
                    <div v-if="!data.item.EsPadre">{{data.item.stock? Number.isInteger(data.item.stock)?data.item.stock:data.item.stock.toFixed(2):data.item.stock }}</div>
                </div>
                </template>
            </b-table>
            </b-col>
        </b-row>
        <b-row style="border-top: 3px solid #C0C0C0">
            <b-col md="3">
              <b-row>
                <slot name="botones"></slot>
              </b-row>
            </b-col>
            <b-col cols="4" class="text-center mt-1">
               <slot name="infoLlevar"></slot>
            </b-col>
            <b-col md="5" class="text-center mt-1">
              <table class="table b-table border table-footer" style="font-size:0.85rem">
                <tbody>
                  <tr>
                    <td>
                      <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                      <b-popover target="popover-metodopago" title="Metodo de pago" placement="bottomleft" triggers="focus">
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
                    <td class="table-danger">{{ sumatoriaIgv }}</td>
                  </tr>
                  <tr>
                    <td>
                      <b-button class="p-1 ml-1 mt-1" id="popover-num" variant="primary"><i class="fas fa-calculator" ></i></b-button>
                      <b-popover target="popover-num" triggers="focus" placement='bottom'>
                        <table class="calc__numbers">
                          <tbody>
                              <tr>
                                  <td class="botones" @click="foldNumbers('7');">7</td>
                                  <td class="botones" @click="foldNumbers('8');">8</td>
                                  <td class="botones" @click="foldNumbers('9');">9</td>
                                  <td class="botones" @click="accionBoton=1" v-bind:class="{ accionActiva: accionBoton==1 }"><span>Cant.</span></td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('4');">4</td>
                                  <td class="botones" @click="foldNumbers('5');">5</td>
                                  <td class="botones" @click="foldNumbers('6');">6</td>
                                  <td class="botones" @click="accionBoton=2" v-bind:class="{ 'accionActiva': accionBoton==2 }">Desc.</td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('1');">1</td>
                                  <td class="botones" @click="foldNumbers('2');">2</td>
                                  <td class="botones" @click="foldNumbers('3');">3</td>
                                  <td class="botones" @click="accionBoton=3" v-bind:class="{ 'accionActiva': accionBoton==3 }">Precio</td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('0');">0</td>
                                  <td class="botones" @click="pushNumbers('-');">-</td>
                                  <td class="botones" @click="pushNumbers('+');">+</td>
                                  <td class="botones" @click="result();"><i class="fas fa-backspace"></i></td>
                              </tr>
                          </tbody>
                        </table>
                      </b-popover>
                    </td>
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
    </b-col>
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
      pagocon: '',
      cliente: {},
      preciosProd: [],
      objProd: {},
      cant: 0,
      precioCont: 0,
      productoSelect: {},
      idPerfil: sessionStorage.getItem('IdPerfil'),
      accionBoton: 1,
      productoChange:null
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    },
    productos (prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === 'MANODEOBRA');
      if (pi >= 0 && !this.productos[pi].precioBruta) {
        this.productos[pi].precioBruta = this.productos[pi].precio
      }
    }
  },
  props: {
    descuentoActivo: Number,
    precioHabilitado: Boolean,
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    bloquearInput:Boolean,
    esDelivery:Boolean,
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
          
            { key: 'stock', label: 'Stock', variant: 'info', class:'text-center stocksize' },
            { key: 'Producto', class:'fontsize thsize' },
            //{ key: 'Indicaciones', label: 'Indicaciones' , class:'thsize'},
            //{ key: 'eventPres', label: '', class:'thsize' },
            // { key: 'cantxPrecio', variant: 'warning' },
            // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
            // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
            /*{ key: 'ProductoMedicion', label: 'Medición' },*/
            { key: 'cantxPrecio', variant: 'warning', class:'text-center thsize' },
            { key: 'cantidad', class:'thsize' },
            { key: 'precio', label: 'Precio' , class:'thsize' },
            //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },
            { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'text-center thsize' }, 
            { key: 'descuento', label: 'Descuento', class:'thsize'},
            { key: 'total', label: 'Total', variant: 'danger', class:'text-center thsize' },
            { key: 'acciones', label: '', class:'thsize'}
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
    apiInidicaciones () {
        return this.API + 'indicaciones'
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
        //sumatoria = sumatoria * 0.18
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
      obtenerStok: 'obtenerStock',
      limpiarCart: 'limpiarCarrito',
    }),
    changeCant (val, prod, index) {
      if (val) {
        prod.cantidad = prod.cantidadPres
        prod.cantidad *= val
        //prod.cantidad = Number(val)
      }
      this.$nextTick(() => {
        this.actualizarProd({Producto:prod,Pos:index})
      })
    },
    setIndicacion(obj){
      this.$nextTick(()=>{
        if(this.productoSelect.indicacion==null || this.productoSelect.indicacion == undefined){
          this.productoSelect.indicacion = ''
        }
        this.productoSelect.indicacion = this.productoSelect.indicacion +" -"+ obj.Indicacion
        this.actualizarProd(this.productoSelect)
      })
      
    },
    limpiar(){
        this.limpiarCart()
    },
    cambiarPrecio (index, prod) {
      var utilidad =   (1-(this.productos[index].PorcentajeUtilidad / 100))
      this.productos[index].precio = Math.round((this.productos[index].PrecioCosto / utilidad)*10)/10
    },
    
    addManoDeObra (index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === 'MANODEOBRA');
      if ( pi < 0 ) {
        this.$toasted.error('Primero seleccione el producto MANODEOBRA')
        
        this.productos[index].EsManoDeObra = 0
        return ;
      }
      if (this.productos[index].EsManoDeObra) {
        e.target.parentNode.parentNode.style.backgroundColor = "#e7c4ff"
        this.productos[pi].precio = parseFloat(this.productos[pi].precio) - (parseFloat(this.productos[index].precio) * parseFloat(this.productos[index].cantidad))
        this.productos[index].disabled = true
      } else {
        e.target.parentNode.parentNode.removeAttribute("style")
        this.productos[pi].precio = 0
        this.productos[pi].precio = parseFloat(this.productos[pi].precio) + (parseFloat(this.productos[index].precio) * parseFloat(this.productos[index].cantidad))
        this.productos[index].disabled = false
      }
    },
    updateManoDeObraNeta (index) {
      let pManoObra = this.productos.find(p => p.CodigoBarra === 'MANODEOBRA');
      let precio = pManoObra ? pManoObra.precioBruta : 0;
      this.productos.filter(p => p.EsManoDeObra).map(p => {
          precio -= (parseFloat(p.precio) * parseFloat(p.cantidad))
      })
      
      let pi = this.productos.findIndex(p => p.CodigoBarra === 'MANODEOBRA');
      if (pi >= 0 && index !== pi) {
        this.productos[pi].precio = precio
      }
    },
    asignarDisabledProf (index, e) {
      if (e.target.checked === true) {
        this.productos[index].disabledProf = 1
        return
      }
      this.productos[index].disabledProf = 0
    },
    asignarVuelto () {
      this.vuelto = this.pagocon - this.sumatoriaTotal
    },
    // loadBarcode (e) {
    //   e.preventDefault()
    //   let idProducto = this.loadCode;
    //   if (idProducto) {
    //     this.add([idProducto, (obj) => {
    //       if (obj) {
    //         this.$emit('onAddProducto')
    //         this.$toasted.show('Producto añadido correctamente')
    //       } else {
    //         this.$toasted.show('No se encontró ningun producto')
    //       }
    //       this.loadCode = ''
    //     }, { setPrecio: this.setPrecio,  setSuma: true }, true])
    //   }
    // },
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
    agregarGuardar(id,obj){
      this.add([id,false,false,false,false,true,this.isVentas]);
      setTimeout(() => {
              //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) ;
      }, 200); 
      
      if(this.isVentas){
          obj.estadoPxP = false
          obj.ProductoMedicion = obj.medicion.ProductoMedicion
          obj.IdProductoMedicion = obj.medicion.IdProductoMedicion
          obj.IdProductoMarca= obj.marca.IdProductoMarca
          obj.ProductoMarca= obj.marca.ProductoMarca
          obj.IdProductoCategoria = obj.categoria.IdProductoCategoria
          obj.ProductoCategoria = obj.categoria.ProductoCategoria
          this.productoSelect = obj
          this.showPresentacion(this.productoSelect)
      }
    },
    setProducto (obj) {
      let precios = 1;
      this.precioCont = obj.PrecioContado
      obj.estadoPxP = false
      this.loadCode = ''
      this.preciosProd = []
      if (!this.isVentas) {
        obj.estadoPxP = true
        obj.PreciosPorProducto = ''
        this.setProductoUni(obj)
        return
      }
      this.preciosProd.push({nombre:"P. Pub.", cantidad: 1, precio:obj.PrecioContado})
      if(obj.precioConvenio){
        precios++;
        this.preciosProd.push({nombre:"P. Dis.", cantidad: 1, precio:obj.precioConvenio})
      }
      if(obj.PrecioEspecial){
        precios++;
       this.preciosProd.push({nombre:"P. Esp", cantidad: 1,  precio:obj.PrecioEspecial})
      }
      if(precios==1){
        this.setProductoUni(obj,obj.PrecioContado)
        return
      }else{
        this.objProd = {}
        this.$refs['modalCant'].show()
        this.objProd = obj
      }
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
      let existe= this.productos.some((producto)=>producto.IdProducto===obj.IdProducto)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        if(existe){
          if(this.isVentas){
            this.$toasted.success('Precio Modificado')
          }else{
            this.$toasted.success('El producto ya está en la lista')
          }
          
        }else{
          this.$toasted.success('Producto añadido correctamente')
        }
        
      }, { setPrecioCosto: (this.tipo == 'mov' ? true : false) }, false, this.cliente.EsVIP])
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs['modalCant'].hide()
      obj.cantidadPres = obj.cantidad
      this.actualizarProd({Producto:obj,Pos:this.indexProdSelect})
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
      obj.cantidad = 1
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj, prod.cantidad, prod.precio)
      obj.cantidadPres = this.cant
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      }, false, false, this.cliente.EsVIP])
      this.$refs['modalCant'].hide()
      this.actualizarProd(obj)
      this.$refs.barcodeInput.focus()
    },
    showIndicaciones(obj) {
      this.productoSelect = obj
      this.$refs['modalIndi'].show()
    },
    renderChild(data) {
        return `
          <div>
            <b>${data.Indicacion}</b>
          </div>  
          `
    },
    // setCantidad (index, val) {
    //   this.$emit('onInputCantidad', val)
    // },
    setNuevoPrecioContado (index) {
      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio)
      if(this.productos[index].TieneIgv) {
        precio += (precio * 0.18)
      }
      if(this.isVentas){
        precio += (precio * (this.productos[index].PorcentajeUtilidad/100)) 
      }else{
        precio = (precio / (1-this.productos[index].PorcentajeUtilidad/100))
      }
      let normalPrecio = this.$options.filters.redondear(precio)
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      let nuevoPrecio = this.$options.filters.redondear(precio, 1)
      
      if (this.tipoCambio > 0) {
        normalPrecio *= this.tipoCambio
        nuevoPrecio *= this.tipoCambio
      }
      Vue.set(this.productos[index], 'normalPrecioContado', parseFloat(normalPrecio).toFixed(2))
      Vue.set(this.productos[index], 'nuevoPrecioContado', Math.ceil(nuevoPrecio))
      this.getSubtotal(index)
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
      this.updateManoDeObraNeta(index)
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
    },
    formatFecha(fecha) {
      if (fecha) {
			  return this.$moment(fecha, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD')
      } else {
        return ''
      }
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
        width:  15vw;
        height: 15vw;
    }
    .calc__numbers td {
    min-width: 40px;
    min-height: 40px;
    border: 0.5px solid #A3A3A3;
    cursor: pointer;
    text-align: center;
    vertical-align: middle;
    padding:1.8% 1.8%;
    font-size: 0.8rem;
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
    td.fontsize{
      font-size: 0.8rem !important;
      font-weight: bold;
      text-align: center; 
      vertical-align: middle !important;
    }
    td.stocksize{
      text-align: center !important; 
      vertical-align: middle !important;
    }
    th.stocksize{
      font-size: 0.8rem !important;
      text-align: center;
    }
    th.thsize{
      font-size: 0.85rem !important;
      text-align: center;
    }
    td.thsize{
      vertical-align: middle !important;
    }
    .popover-body{
      background-color: #C1C1C1;
    }
    .bs-popover-bottom{
      max-width: 370px !important;
    }
</style>