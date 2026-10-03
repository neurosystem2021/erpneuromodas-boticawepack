<template>
  <b-row>
    <b-col md="12">
      <div class="carrito-add-panel">
        <b-form-group class="mb-0">
          <label class="carrito-add-label"><i class="fas fa-plus-circle"></i> Agregar Productos</label>
          <b-row no-gutters>
            <b-col cols="12" md="6" class="mx-0 pdr">
                <b-input-group class="carrito-input-group">
                <autocomplete
                  id="autocompleteProducto"
                  ref="autocompleteProducto"
                  :url="APIProductos"
                  :customParams="{ limit: '25' }"
                  :onShouldRenderChild="renderChild"
                  anchor="Producto"
                  label="writer"
                  placeholder="Agregar Producto"
                  :on-select="setProducto"
                  :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                  :classes="{input: 'form-control txtAutoComplete',}">
                </autocomplete>
                    <b-button variant="primary" class="btn-mic" @click="startRecognition" title="Buscar por voz">
                      <i class="fas fa-microphone"></i>
                    </b-button>
                </b-input-group>
            </b-col>
            <b-col cols="12" md="6" class="mx-0 pdl">
                <b-input-group class="carrito-input-group">
                  <input v-model="loadCode"
                      @keydown.enter.prevent="loadBarcode"
                      type="text"
                      class="form-control carrito-barcode-input"
                      ref="barcodeInput"
                      placeholder="C. Barra" />
                  <b-input-group-append>
                    <!--<proveedores-tabla></proveedores-tabla>-->
                    <b-button variant="primary" class="btn-buscar-barra" @click="loadBarcode" title="Buscar"><i class="fas fa-search"></i></b-button>
                    <producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}" ref="productoFormEdit" :producto-update="productoSelect" ></producto-form>
                  </b-input-group-append>
                </b-input-group>
            </b-col>
          </b-row>  
          <b-badge v-if="isVentas && false" variant="danger" class="carrito-badge-stock">Productos sin stock</b-badge>
        </b-form-group>
      </div>

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
                  <b>Precio: </b><br>S/ {{ precioCont ? precioCont : 0 }}
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
      <div class="clsTable carrito-table-card">
        <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
          <template slot="eventPres" slot-scope="data" v-if="productos[data.index].TienePresentacionFlag">
            <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click="showPresentacion(productos[data.index])"><i class="fas fa-list"></i></b-button>
          </template>
      <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
            <input
              v-model="productos[data.index].cantxPrecio"
              type="number"
              style="width: 85px"
              step="0.001"
              min="0"
              :disabled="bloquearInput?true:false"
              @input="(val) => changeCant(val.target.value, productos[data.index], data.index)"
              placeholder="Cantidad"
              class="form-control" />
            <span>x{{productos[data.index].cantidadPres}}</span>
          </template>

          <template slot="cantidad" slot-scope="data">
            <input
              v-model="productos[data.index].cantidad"
              :disabled="isVentas? true : false"
              type="number"
              style="width: 85px"
              min="0"
              @input="(val) => {
                $emit('onInputCantidad', val.target.value)
              /* if(!productos[data.index].PreciosPorProducto) {
                  productos[data.index].precio = (productos[data.index].StockPorMayor && val >= productos[data.index].StockPorMayor) ? productos[data.index].PrecioPorMayor : (tipo == 'mov' ? productos[data.index].PrecioCosto : productos[data.index].PrecioContado)
                  return
                }
let reg = JSON.parse(productos[data.index].PreciosPorProducto).find( e => e.cantidad === productos[data.index].cantidadPres )
                if(reg && reg.cantidad){
                  productos[data.index].precio = reg.precio
                  return
                }
                productos[data.index].precio = productos[data.index].PrecioContado */
              }"
              placeholder="Cantidad"
              class="form-control" />
          </template>
          <template slot="precio" slot-scope="data">
            <input
              v-model="productos[data.index].precio"
              :disabled="productos[data.index].disabled || bloquearInput?true:false"
              type="number"
              style="width: 90px"
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
        
<template slot="manoobra" slot-scope="data" v-if="false">
            <input  :disabled="bloquearInput?true:false"
              @change="(e) => addManoDeObra(data.index, e)"
              v-if="productos[data.index].CodigoBarra != 'MANODEOBRA' " 
              v-model="productos[data.index].EsManoDeObra"
              class="form-check-input" type="checkbox" />
          </template>

          <template slot="precioVenta" slot-scope="data">
            <input
              style="width:85px"
              v-model="productos[data.index].nuevoPrecioContado"
              type="number"
              min="0"
              step="0.01"
              placeholder="Precio Venta"
              class="form-control"/>
              <b-form-text>
                <strike> {{ productos[data.index].normalPrecioContado }} </strike>
              </b-form-text>
          </template>

          <template slot="utilidad" slot-scope="data">
            <input 
              class="form-control"
              v-model="productos[data.index].PorcentajeUtilidad"
              style="width: 80px"
              type="number" 
              step="0.01"
              :disabled="bloquearInput?true:false"
              @input="cambiarPrecio(data.index, productos[data.index])" 
              @mouseup="cambiarPrecio(data.index, productos[data.index])" 
              />
              <small>ganancia: {{ (productos[data.index].precio - productos[data.index].PrecioCosto) | redondear }}</small>
          </template>

          <template slot="descuento" slot-scope="data">
            <input
              :disabled="bloquearInput?true:false"
              v-model="productos[data.index].descuento"
              type="number"
              style="width: 90px"
              min="0"
              step="0.01"
              class="form-control"
              @input="(val)=>ActualizarDesc(data.index,val.target.value)" 
              value="0">
              <b-form-text>
              </b-form-text>
          </template>

          <template slot="FechaAlquiler" slot-scope="data">
            <b-form-input
              v-model="productos[data.index].FechaAlquiler"
              type="date"
              placeholder="Fecha Fin"
              :value="formatFecha(data.item.FechaAlquiler)"></b-form-input>
          </template>

          <template slot="FechaAlquilerFin" slot-scope="data">
            <b-form-input
              v-model="productos[data.index].FechaAlquilerFin"
              type="date"
              placeholder="Fecha Fin"
              :value="formatFecha(data.item.FechaAlquilerFin)"></b-form-input>
          </template>

          <template slot="Descripcion" slot-scope="data">
            <textarea 
              :disabled="bloquearInput?true:false"
              class="form-control txtArea"
              placeholder="Descripción"
              v-model="productos[data.index].Descripcion"></textarea>
            <!--<b-form-input
              v-model="productos[data.index].Descripcion"
              type="text"
              placeholder="Descripción"></b-form-input>-->
          </template>  

          <template slot="total" slot-scope="data">
            {{ getTotal(data.index) }}
            
            <h6 v-if="false"><i>Prof.</i></h6>
            <input v-if="false"
              class="form-check-input" 
              type="checkbox" 
              :disabled="bloquearInput?true:false"
              @change="(e) => {
                $emit('onCheckProf', e)
                asignarDisabledProf(data.index, e)
              }"
            />
          </template>
          <template slot="acciones" slot-scope="data">
            <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" class="carrito-btn-accion" @click="quitarProducto(data.index)"><i class="fas fa-times"></i></b-button>
            <b-button :disabled="bloquearInput?true:false"  v-if="(idPerfil == 1) ? true : false" variant="primary" size="sm" class="carrito-btn-accion" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt"></i></b-button>
          </template>

          <template slot="show_details" slot-scope="row">
            <b-button size="sm" variant="info" @click.stop="row.toggleDetails">></b-button>
          </template>
          <template slot="row-details" slot-scope="row">

          </template>

          <template slot="stock" slot-scope="data">
            <img v-if="data.item.EsPadre" src="static/box.png" style="width: 24px" />
            <div v-if="!data.item.EsPadre">{{data.item.stock? Number.isInteger(data.item.stock)?data.item.stock:data.item.stock.toFixed(2):data.item.stock }}</div>
          </template>
        </b-table>
        <div v-if="!productos.length" class="carrito-empty">
          <i class="fas fa-shopping-basket"></i>
          <span>Aún no agregaste productos. Búscalos arriba o escanea el código de barra.</span>
        </div>
      </div>
    </b-col>

    <!-- ===== Acciones principales: Efectuar Venta / Crear Proforma =====
         Se pinta a todo el ancho, justo debajo de la tabla del carrito
         y arriba de Opciones / Método de pago. -->
    <b-col md="12" class="col-acciones-superior">
      <slot name="acciones-superior"></slot>
    </b-col>

    <b-col md="3">
      <slot name="operaciones"></slot>
    </b-col>
    <b-col md="4">
      <slot name="metodopago"></slot>
    </b-col>
    <b-col md="2">
      <slot name="adicional"></slot>
    </b-col>
    <b-col md="3">
          <table class="table b-table border table-footer table-resumen-jobi">
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
              <tr class="ocularMovil">
                <td  colspan="2">
                  <label>pagó con:</label>
                  <b-form-input
                    :disabled="bloquearInput?true:false"
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
      cliente: {},
      preciosProd: [],
      objProd: {},
      cant: 0,
      precioCont: 0,
      medicionPro:'UNIDAD',
      productoSelect: {},
      idPerfil: sessionStorage.getItem('IdPerfil')
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
    isVentas: Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    bloquearInput:Boolean,
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
          // { key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
          { key: 'CodigoBarra', label: 'Código'},
          // { key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo' }, 
          //{ key: 'Descripcion', label: 'Descripción'},
          { key: 'ProductoMarca', label: 'Marca' },
          { key: 'ProductoPresentacion', label: 'Present.' }, 
          { key: 'ProductoMedicion', label: 'Medición' },
          { key: 'cantidad' },
          { key: 'precio', label: 'P/U' },
          { key: 'subtotal', label: 'SubTotal', variant: 'success' },
          { key: 'igv', label: 'IGV' },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: 'precioVenta', label: 'Precio venta (S/)' },
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
    }),

    startRecognition() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.query = event.results[0][0].transcript;
          this.search();
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        if (!this.query) {
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    search() {
      const inputEvent = new Event("input", {
        bubbles: true,
        cancelable: true
      });
      let inputElement = `autocompleteProducto`;
      let autocompleteInput = document.getElementById(inputElement);
      setTimeout(() => {
        document.getElementById(inputElement).value = this.query;
        autocompleteInput.dispatchEvent(inputEvent);
      }, 200);
    },

    changeCant (val, prod, index) {
      if (val) {
        prod.cantidad = prod.cantidadPres
        prod.cantidad *= val
      }
      this.$nextTick(() => {
        this.actualizarProd({Producto:prod,Pos:index})
      })
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
    agregarGuardar(id,pxp,obj){
      this.add([id,false,false,false,false,true,this.isVentas]);
      setTimeout(() => {
              this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) ;
      }, 200); 
      
      if(this.isVentas){
        let fPP = pxp.filter(p => p.cantidad === 0 || p.cantidad === '0')
        if(fPP.length!=4){
          obj.ProductoMedicion = obj.medicion.ProductoMedicion
          obj.IdProductoMedicion = obj.medicion.IdProductoMedicion
          obj.IdProductoMarca= obj.marca.IdProductoMarca
          obj.ProductoMarca= obj.marca.ProductoMarca
          obj.IdProductoCategoria = obj.categoria.IdProductoCategoria
          obj.ProductoCategoria = obj.categoria.ProductoCategoria
          this.productoSelect = obj
          this.productoSelect.PreciosPorProducto = JSON.stringify(pxp)
          this.showPresentacion(this.productoSelect)
        } 

      }
//miley 1
    },
    setProducto (obj) {
      this.precioCont = obj.PrecioContado
      this.medicionPro = obj.ProductoMedicion
      this.loadCode = ''
      this.preciosProd = []
      this.objProd = {}

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
    },  
    setProductoUni (obj) {
      obj = Object.assign({}, obj)

      this.$refs.autocompleteProducto.setValue('')
      obj.cantxPrecio = 1
      obj.Descripcion = ''  
      obj.precio = obj.PrecioCosto
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj)
      //let existe= this.productos.some((producto)=>producto.IdProducto===obj.IdProducto)
      this.add([obj, () => {
        /*if(existe){
            this.$toasted.info('El producto ya está en la lista')
        }else{
          this.$toasted.success('Producto añadido correctamente')
        }*/
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      },{ setPrecioCosto: (this.tipo == 'mov' ? true : false) }, false, this.cliente.EsVIP])
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs['modalCant'].hide()
      obj.cantidadPres = obj.cantidad
      //this.actualizarProd(obj)
      this.$refs.barcodeInput.focus()
    },
    setProductoCant (prod) {
      this.cant = prod.cantidad
      let obj = Object.assign({}, this.objProd)
      this.$refs.autocompleteProducto.setValue('')
      obj.cantxPrecio = 1
      obj.Descripcion = ''
      //obj.FLETE = 0
      // this.objProd.precio = 0
      // this.objProd.cantidad = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj, prod.cantidad, prod.precio)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
      }, false, false, this.cliente.EsVIP])
      this.$refs['modalCant'].hide()
      obj.cantidadPres = this.cant
      //this.actualizarProd(obj)
      this.$refs.barcodeInput.focus()
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
        let sinStock = data.stock <= 0;

        return `
        <style>
          #table-render {
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            margin: 0;
          }
          #table-render td {
            border: none;
            padding: 0;
            vertical-align: middle;
          }
          /* Tarjeta blanca opaca propia por fila: independientemente del
             color que la librería de autocomplete le ponga a la fila
             (hover/selección suele venir en azul fuerte, controlado por
             ella y fuera de nuestro alcance), el texto SIEMPRE se lee
             sobre este fondo blanco propio con buen contraste. */
          #table-render .jr-card {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            border: 1px solid #e7edf5;
            border-radius: 10px;
            padding: 9px 12px;
            margin: 3px 2px;
            box-shadow: 0 2px 6px rgba(11,42,74,.08);
          }
          #table-render .jr-stock-dot {
            flex: 0 0 auto;
            width: 9px;
            height: 9px;
            border-radius: 50%;
            background: #17A554;
          }
          #table-render .jr-stock-dot.sin-stock {
            background: #E5484D;
          }
          #table-render .jr-info {
            flex: 1 1 auto;
            min-width: 0;
          }
          #table-render .jr-nombre {
            font-weight: 800;
            font-size: 13.5px;
            line-height: 1.25;
            color: #0B2A4A !important;
            white-space: normal;
          }
          #table-render .jr-badges {
            display: flex;
            gap: 6px;
            margin-top: 4px;
            flex-wrap: wrap;
          }
          #table-render .jr-badge {
            display: inline-flex;
            align-items: center;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .02em;
            padding: 2px 8px;
            border-radius: 999px;
            background: #EEF3F8;
            color: #33465F !important;
            white-space: nowrap;
          }
          #table-render .jr-badge.marca {
            background: #DCEBFD;
            color: #1D4ED8 !important;
          }
          #table-render .jr-precio-cell {
            flex: 0 0 auto;
          }
          #table-render .jr-precio {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 13px;
            color: #ffffff !important;
            background: #0F9B8E;
            border-radius: 999px;
            padding: 5px 12px;
            white-space: nowrap;
          }
        </style>
        <div>
          <table id="table-render">
            <tbody>
              <tr>
                <td>
                  <div class="jr-card">
                    <span class="jr-stock-dot${sinStock ? ' sin-stock' : ''}" title="${sinStock ? 'Sin stock' : 'Con stock'}"></span>
                    <div class="jr-info">
                      <div class="jr-nombre">${data.Producto}</div>
                      <div class="jr-badges ocultarMovil">
                        <span class="jr-badge">${data.ProductoCategoria}</span>
                        <span class="jr-badge marca">${data.ProductoMarca}</span>
                      </div>
                    </div>
                    <div class="jr-precio-cell"><span class="jr-precio">S/ ${data.PrecioContado}</span></div>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>   
        </div>
        `;
      }
      return `
        <style>
          #table-render-simple {
            font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #ffffff;
            border: 1px solid #e7edf5;
            border-radius: 10px;
            padding: 9px 12px;
            margin: 3px 2px;
            box-shadow: 0 2px 6px rgba(11,42,74,.08);
          }
          #table-render-simple .jr-nombre {
            font-weight: 800;
            font-size: 13.5px;
            color: #0B2A4A;
          }
          #table-render-simple .jr-sub {
            font-size: 11.5px;
            color: #5b6b82;
            font-weight: 700;
            margin-top: 2px;
          }
        </style>
        <div id="table-render-simple">
          <div class="jr-nombre">${data.Producto}</div>
          <div class="jr-sub">${data.ProductoCategoria} · ${data.ProductoMarca} · ${data.ProductoModelo}</div>
        </div>
      `;
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
      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))

      let normalPrecio = this.$options.filters.redondear(precio)
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      let nuevoPrecio = this.$options.filters.redondear(precio, 1)
      
      if (this.tipoCambio > 0) {
        normalPrecio *= this.tipoCambio
        nuevoPrecio *= this.tipoCambio
      }

      //Vue.set(this.productos[index], 'normalPrecioContado', parseFloat(normalPrecio).toFixed(2))
      //Vue.set(this.productos[index], 'nuevoPrecioContado', Math.ceil(nuevoPrecio))
      this.getSubtotal(index)

      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
      //this.updateManoDeObraNeta(index)
    },
    ActualizarDesc(index,val){
      Vue.delete(this.productos[index], 'descuento');
      Vue.set(this.productos[index],'descuento',parseFloat(val))
      this.actualizarProd({Producto:this.productos[index],Pos:index})
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
  /* Reutiliza la misma paleta JoBi; si el padre (.pos-jobi) ya define estas
     variables, se heredan por cascada normal de CSS (los scoped styles de
     Vue no bloquean la herencia de variables). Se redefinen aquí también
     para que este componente luzca bien aunque se use fuera de ese padre. */
  :root, .clsTable, .carrito-add-panel {
    --jobi-navy: #00666A;
    --jobi-navy-2: #00666A;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
  }

  .table-resumen-jobi {
    background: #fff;
    border-radius: 14px !important;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
    border: 1px solid #e7edf5 !important;
  }

  .table-resumen-jobi tbody tr td {
    border-color: #eef2f7 !important;
    padding: 18px 20px !important;
    vertical-align: middle;
    font-size: 1.35rem;
  }

  .table-resumen-jobi td.table-success {
    background-color: #e6f7ef !important;
    color: #0f6b3f !important;
    font-weight: 700;
  }

  .table-resumen-jobi td.table-danger {
    background-color: #fdeceb !important;
    color: #a3231f !important;
    font-weight: 700;
  }

  .table-resumen-jobi tr:last-of-type td.table-danger {
    background-color: var(--jobi-green) !important;
    color: #fff !important;
    font-size: 1.5rem;
    font-weight: 900;
    padding: 22px 20px !important;
  }

  .table-resumen-jobi >>> input.form-control {
    border-radius: 999px !important;
    border: 1.5px solid #e3ebf4 !important;
    height: 52px;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--jobi-navy);
  }

  .table-resumen-jobi label {
    font-size: .92rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6b85a3;
  }

  .table-resumen-jobi tr:last-child td b {
    font-size: 1.3rem;
  }

  /* -------- Panel "Agregar Productos" -------- */
  .carrito-add-panel {
    background: #fff;
    border: 1px solid #e7edf5;
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 14px;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
  }

  .carrito-add-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .78rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: var(--jobi-navy);
    margin-bottom: 8px;
  }
  .carrito-add-label i {
    color: var(--jobi-teal);
  }

  .pdr { padding-right: 6px; }
  .pdl { padding-left: 6px; }
  @media (max-width: 767px) {
    .pdr, .pdl { padding-right: 0; padding-left: 0; }
    .pdl { margin-top: 8px; }
  }

  /* Input-group en forma de píldora, coherente con el resto del POS.
     IMPORTANTE: este contenedor NUNCA debe llevar "overflow: hidden".
     El listado de sugerencias del autocomplete se renderiza como un
     elemento posicionado justo debajo del <input>, dentro de este mismo
     contenedor — "overflow: hidden" lo recorta por completo y la lista
     desaparece aunque los datos sí lleguen. Por eso el look de "píldora"
     se logra redondeando cada elemento (input / botones) por separado,
     en vez de recortar el contenedor que los envuelve. */
  .carrito-input-group {
    box-shadow: 0 2px 8px rgba(11,42,74,.08);
    border-radius: 10px;
  }
  .carrito-input-group >>> input,
  .carrito-input-group >>> .form-control {
    border: none !important;
    background: #f6f9fc !important;
    height: 44px;
    font-weight: 600;
    color: var(--jobi-navy);
    border-radius: 10px 0 0 10px !important;
  }
  .carrito-input-group >>> input:focus,
  .carrito-input-group >>> .form-control:focus {
    box-shadow: none;
    background: #fff !important;
  }
  .carrito-input-group .btn-mic,
  .carrito-input-group .btn-buscar-barra {
    border: none !important;
    background: var(--jobi-teal) !important;
    width: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 0 !important;
  }
  .carrito-input-group .btn-mic:hover,
  .carrito-input-group .btn-buscar-barra:hover {
    background: #0c8578 !important;
  }
  /* Redondea la esquina derecha del ÚLTIMO control visible del grupo
     (el micrófono en la fila de arriba; en la fila del código de barra
     puede ser el botón de búsqueda o el botón que agrega producto-form,
     según lo que se muestre al final). */
  .carrito-input-group .btn-mic:last-child,
  .carrito-input-group .btn-buscar-barra:last-child,
  .carrito-input-group >>> .input-group-append:last-child > *:last-child,
  .carrito-input-group >>> .input-group-append:last-child .btn:last-child {
    border-radius: 0 10px 10px 0 !important;
  }

  /* -------- Dropdown de sugerencias del autocomplete de productos --------
     El componente de autocomplete inyecta su propio contenedor de
     sugerencias justo debajo del input; le damos aquí la misma tarjeta
     redondeada/con sombra que el resto del POS, ya que el HTML interno
     de cada fila (renderChild) no puede controlar el contenedor externo. */
  .carrito-input-group >>> .autocomplete-list,
  .carrito-input-group >>> .autocomplete-results,
  .carrito-input-group >>> ul[class*="autocomplete"] {
    background: #fff !important;
    border-radius: 12px !important;
    box-shadow: 0 12px 30px rgba(11,42,74,.16) !important;
    border: 1px solid #e7edf5 !important;
    margin-top: 6px !important;
    overflow: hidden;
    padding: 4px !important;
  }

  .carrito-badge-stock {
    margin-top: 8px;
    border-radius: 999px;
    font-weight: 700;
    padding: 5px 12px;
  }

  /* -------- Tarjeta que envuelve la tabla del carrito -------- */
  .carrito-table-card {
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
    border: 1px solid #e7edf5;
    overflow-x: auto;
  }

  .carrito-table-card >>> table {
    margin-bottom: 0;
  }

  .carrito-table-card >>> thead th {
    background: var(--jobi-navy);
    color: #fff !important;
    text-transform: uppercase;
    font-size: .70rem;
    letter-spacing: .03em;
    font-weight: 800;
    border: none !important;
    white-space: nowrap;
    vertical-align: middle;
    padding: 10px 8px;
  }

  .carrito-table-card >>> thead th:first-child { border-top-left-radius: 10px; }
  .carrito-table-card >>> thead th:last-child { border-top-right-radius: 10px; }

  .carrito-table-card >>> tbody td {
    vertical-align: middle;
    font-size: .84rem;
    padding: 8px;
    border-color: #eef2f7;
  }

  .carrito-table-card >>> tbody tr:hover {
    background-color: #f5f9ff !important;
  }

  /* Celdas del cuerpo (subtotal / total) más suaves, estilo pill */
  .carrito-table-card >>> td.table-success {
    background-color: #e6f7ef !important;
    color: #0f6b3f;
    font-weight: 700;
  }
  .carrito-table-card >>> td.table-danger {
    background-color: #fdeceb !important;
    color: #a3231f;
    font-weight: 700;
  }
  .carrito-table-card >>> td.table-info {
    background-color: #e7f1fd !important;
    color: var(--jobi-blue);
  }

  /* Encabezados (th) de esas mismas columnas: se mantienen oscuros
     igual que el resto de la cabecera, en vez de heredar el color suave */
  .carrito-table-card >>> th.table-success,
  .carrito-table-card >>> th.table-danger,
  .carrito-table-card >>> th.table-info {
    background-color: var(--jobi-navy) !important;
    color: #fff !important;
    font-weight: 800;
  }

  .carrito-table-card >>> input.form-control,
  .carrito-table-card >>> textarea.form-control {
    border-radius: 8px !important;
    border: 1.5px solid #dbe6f1 !important;
    font-weight: 600;
    color: var(--jobi-navy);
    padding: 4px 8px;
  }
  .carrito-table-card >>> input.form-control:focus,
  .carrito-table-card >>> textarea.form-control:focus {
    border-color: var(--jobi-blue) !important;
    box-shadow: 0 0 0 3px rgba(47,111,237,.12) !important;
  }

  .carrito-table-card >>> .txtArea {
    min-height: 38px;
    resize: vertical;
  }

  .carrito-btn-accion {
    border-radius: 8px !important;
    margin: 0 2px;
  }

  /* Mensaje cuando el carrito está vacío */
  .carrito-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 34px 16px;
    color: #8296b0;
    text-align: center;
  }
  .carrito-empty i {
    font-size: 1.9rem;
    color: #c6d4e3;
  }
  .carrito-empty span {
    font-size: .85rem;
    font-weight: 600;
    max-width: 320px;
  }

  /* -------- Reglas originales, sin cambios funcionales -------- */
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
  .form-check-input {
    margin-left: -0.2rem !important;
  }
  .col-acciones-superior {
    display: flex;
    justify-content: center;
  }
  @media only screen and (max-width: 700px) {
  /* For desktop: */
    .ocularMovil{
        display: none;
    }
  }
</style>