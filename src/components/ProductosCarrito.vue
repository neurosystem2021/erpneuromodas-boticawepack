<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :customParams="{'sortBy':isVentas?'Producto':'Producto', 'sortDesc': 0 , 'limit':20}"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{input: 'form-control'}">
          </autocomplete>
          <input v-model="loadCode"
                  :disabled="bloquearInput?true:false || isNota"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  ref="barcodeInput"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form  v-if="false" :showButton="showButton" :cbGuardar="(id) => add([id])"></producto-form>
          </b-input-group-append>
        </b-input-group>
          <b-badge variant="danger">Productos Vencidos</b-badge>
          <b-badge variant="warning">proximos a vencer 4 meses</b-badge>
          <b-badge v-if="isVentas" variant="primary">Anteponer <b>*</b>  para buscar por compuesto</b-badge>
      </b-form-group>

      <b-modal size="lg" ref="modalCant" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration:underline">{{ objProd.Producto}}</h3>
          <h5>Presentaciones</h5>
        </div>
        <hr>
        <b-row>

          <b-col cols="6">
            <b-card
              align="center"
              class="mb-3 contentCard"
              @click="() => {setProductoUni(objProd)}"
            >
              <b-row>
                <b-col md="6" class="nomCart">
                  <div>
                    <b-row class="nomCart2">
                      <h6 class="text-uppercase">{{ objProd.ProductoMedicion }}</h6>
                    </b-row>
                    <b-row class="nomCart2" v-if="isVentas">
                       <h6 style="margin-right:5px">Stock:</h6><h5><b-badge v-if="objProd.stock<=0" variant="danger">{{ objProd.stock }}</b-badge>
                       <b-badge v-else variant="success">{{ objProd.stock }}</b-badge></h5>
                    </b-row>
                  </div>
                </b-col>
                <b-col md="6" class="precioCart text-left">
                  <h6><b>Precio: </b>S/ {{ objProd.PrecioContado }}</h6>
                </b-col>
              </b-row>
            </b-card>
          </b-col>
          <b-col cols="6" v-for="objPro in PresentacionesProd" :key="objPro.IdProducto">
            <b-card
              align="center"
              class="mb-3 contentCard"
              v-if="objPro.cantidad>0"
              @click="() => {setProductoUni(objProd,objPro)}"
            >
              <b-row>
                <b-col md="6" class="nomCart">
                  <div>
                    <b-row class="nomCart2">
                      <h6 class="text-uppercase">{{ objPro.nombre }}</h6>
                    </b-row>
                    <b-row class="nomCart2" v-if="isVentas">
                       <h6 style="margin-right:5px">Stock:</h6><h5><b-badge v-if="(objProd.stock/objPro.cantidad)<=0" variant="danger">{{ (objProd.stock/objPro.cantidad).toFixed(2)}}</b-badge>
                       <b-badge v-else variant="success">{{ (objProd.stock/objPro.cantidad).toFixed(2)}}</b-badge></h5>
                    </b-row>
                  </div>
                </b-col>
                <b-col md="6" class="precioCart text-left">
                  <h6><b>Precio: </b>S/ {{ objPro.precioventa }}</h6>
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
      </b-modal>

      <b-table small class="tabla-carrito" responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="stock" slot-scope="data">
          <div v-if="data.item.ControlaStock" style="font-size:17px; font-weight:bold">
            {{ (data.item.stock/(data.item.cantidadPress || 1)).toFixed(2) }}
          </div>
          <div v-else>
            ---
          </div>
          
        </template>
        <template slot="cantxPrecio" slot-scope="data">
          <input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 100px"
            step="0.001"
            min="0"
            @input="(val) => changeCant(val.target.value, productos[data.index], data.index)"
            placeholder="Cantidad"
            class="form-control"/>
          <span>x{{productos[data.index].cantidadPress}}</span>
        </template>
        <template slot="eventPres" slot-scope="data">
          <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click="showPresentacion(productos[data.index],data.index)"><i class="fas fa-list"></i></b-button>
        </template>
        
        <template slot="cantidad" slot-scope="data">
          <input
            v-model="productos[data.index].cantidad"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 90px"
            min="0"
            readonly
            @input="(val) => {
              $emit('onInputCantidad', val.target.value)
              if(isVentas){
                //productos[data.index].precio = (val.target.value >= productos[data.index].StockPorMayor && productos[data.index].StockPorMayor !== 0) ? productos[data.index].PrecioPorMayor : productos[data.index].PrecioContado
              }   
            }"
            placeholder="Cantidad"
            class="form-control"/>
        </template>
        <template slot="precio" slot-scope="data">
          <input
            v-model="productos[data.index].precio"
            :disabled="bloquearInput?true:false"
            type="number"
            style="width: 110px"
            min="0"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"
            class="form-control"/>
        </template>
        <template slot="subtotal" slot-scope="data">
          <div style="font-size:17px">{{ getSubtotal(data.index) }}</div>
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :disabled="bloquearInput?true:false"
            :value="1"
            @input="setNuevoPrecioContado(data.index)"
            :unchecked-value="0">
            <div style="font-size:15px">{{ getIgv(data.index) }}</div>
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

        <template slot="movimiento" slot-scope="data" v-if="(movTipo!=0 && movTipo!=undefined ) || isVentas">
          <div v-if="isNota" style="width: 135px; text-align:center">
            <div>
              {{productos[data.index].Lote}}
            </div>
            <small>Doc: <span style="font-weight:bold;"> {{productos[data.index].Serie}}-{{productos[data.index].Numero}}</span></small><br />

            <div>
              {{productos[data.index].FechaVen}}
            </div>
          </div>
          <b-form-select v-if="!isNota"
            style="width: 155px;"
            :disabled="bloquearInput?true:false"
            @change="(val) => changeMovimiento(val, data.item.IdProducto, data.index)"
            v-model="productos[data.index].hashMovimiento" :options="movimientoLotes[data.item.IdProducto]"></b-form-select>
          <br>
          <div style="text-align:center;" v-if="!isNota">
                <small style="font-size:14px;">S: <span style="font-weight:bold;">{{ ((productos[data.index].stockMovimiento || 0 )/(productos[data.index].cantidadPress || 1)).toFixed(2) }}</span> -  D: <span style="font-weight:bold; text-transform:uppercase">{{ productos[data.index].SerieNumero }}</span></small><br />
          </div>
          <div style="font-size:19px" v-if="!isNota" v-html="productos[data.index].fechaVenMovimiento"></div>
        </template>

        <template slot="Lote" slot-scope="data" >
          <b-form-input
            v-model="productos[data.index].Lote"
            type="text"
            required
            v-bind:class="{ ventasAncho: isVentas }"
            autocomplete="false"
            ></b-form-input>
        </template>

        <template slot="Presentacion" slot-scope="data" >
          <b-form-input
            v-model="productos[data.index].Presentacion"
            type="text"
            style="text-align:center; font-weight:bold"
            autocomplete="false"
            readonly
            ></b-form-input>
        </template>

        <template slot="fechavencimiento" slot-scope="data" >
          <b-form-input
            required="required"
            v-model="productos[data.index].FechaVen"
            type="date"
            ></b-form-input>
        </template>

        <template slot="precioVenta" slot-scope="data">
          <input
            style="width:95px"
            v-model="productos[data.index].nuevoPrecioContado"
            :disabled="bloquearInput?true:false"
            type="number"
            min="0"
            step="0.01"
            placeholder="Precio Venta"
            class="form-control"/>
            <b-form-text style="font-size:16px;">
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
            class="form-control"/>
            <b-form-text>
            </b-form-text>
        </template>

        <template slot="total" slot-scope="data">
          <div style="font-size:17px">{{ getTotal(data.index) }}</div>
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button variant="danger" size="sm" :disabled="bloquearInput?true:false" @click="quitarProducto(data.index)">X</b-button>
        </template>

        <template slot="show_details" slot-scope="row">
           <b-button size="sm" variant="info" :disabled="bloquearInput?true:false" @click.stop="row.toggleDetails">></b-button>
        </template>
        <template slot="row-details" slot-scope="row">

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
                <td class="table-danger">- {{ sumatoriaDescuento }}</td>
              </tr>
              <tr>
                <td><b>SubTotal</b></td>
                <td class="table-danger">{{ sumatoriaSubTotal }}</td>
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
import axios from 'axios'

export default {
  mounted() {
    // this.alterarBuscadorProductos()
    if(this.$route.query.idProducto) {
        this.add([this.$route.query.idProducto, (obj) => {}, { setPrecio: this.setPrecio,  setSuma: true }])
    }
  },
  data () {
    return {
      API: config.API,
      loadCode: '',
      pasteCode: false,
      producto: {},
      productosCaja: {},
      items: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      idPerfil: sessionStorage.getItem('IdPerfil'),
      movimientoLotes: {},
      ProductosCodigoBarra: [],
      codeBar:'',
      PresentacionesProd:[],
      objProd: {},
      objProdMedicion:'',
      indexProdSelect: null
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
    bloquearInput:Boolean,
    igvTotal: Boolean,
    isVentas: Boolean,
    isNota:Boolean,
    movTipo: Number,
    almacenDestino:Number,
    almacenOrigen:Number,
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
          { key: 'movimiento', label: 'Lote', variant: 'primary', class:'text-center stocksize'  },
          //{ key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo', class:'stocksize' }, 
          { key: 'ProductoMarca', label: 'Marca', class:'stocksize' },
          //{ key: 'ProductoModelo', label: 'Modelo' }, 
          //{ key: 'Color', label: 'Color' }, 
          //{ key: 'ProductoTalla', label: 'Talla' },
          //{ key: 'Genero' },
          //{ key: 'Botapie' },
          { key: 'Presentacion', label: 'Present', class:'text-center stocksize' },
          { key: 'eventPres', label: '', class:'text-center stocksize' },
          { key: 'cantxPrecio', variant: 'warning', class:'text-center stocksize' },
          { key: 'cantidad', class:'stocksize' },
          { key: 'precio', label: 'P/U', class:'stocksize' },
          { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'stocksize' },
          { key: 'igv', label: 'IGV', class:'stocksize' },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: 'precioVenta', label: 'Precio venta', class:'stocksize' },
          { key: 'total', label: 'Total', variant: 'danger', class:'stocksize' },
          { key: 'Lote', label: 'C. Lote', class:'stocksize' },
          { key: 'fechavencimiento', label: 'Vencimiento', class:'stocksize' },
          { key: 'acciones', label: '', class:'stocksize'}
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
    changeCant (val, prod, index) {
      if (val) {
        prod.cantidad = prod.cantidadPress
        prod.cantidad *= val
      }
      this.$nextTick(() => {
        this.actualizarProd({Producto:prod,Pos:index})
      })
    },
    changeMovimiento(hash, idProducto, index) {
      if  (hash) {
        // Stock
        let stock = this.movimientoLotes[idProducto].find(m => m.value === hash).Stock
        let serieNumero = this.movimientoLotes[idProducto].find(m => m.value === hash).SerieNumero
        let IdLote = this.movimientoLotes[idProducto].find(m => m.value === hash).IdLote
        let Lote = this.movimientoLotes[idProducto].find(m => m.value === hash).Lote
        if (stock) {
          // this.productos[index].stockMovimiento = stock
          this.$nextTick(() => {
            Vue.set(this.productos[index], 'stockMovimiento', stock)
            Vue.set(this.productos[index], 'SerieNumero', serieNumero)
          })
        } else {
          this.$nextTick(() => {
            Vue.set(this.productos[index], 'stockMovimiento', 0)
            Vue.set(this.productos[index], 'SerieNumero', '----')
          })
        }
        // Fecha de vencimiento
        var fechaVen = this.movimientoLotes[idProducto].find(m => m.value === hash).FechaVen
        let hoy      = this.$moment().unix();
        let fechaVenUnix = this.$moment(fechaVen).unix();
        let en4meses  = this.$moment().add(4,'months').unix();
        let style = 'bg-success'
        if(fechaVenUnix && fechaVenUnix < hoy) {
          style = 'bg-danger text-white'
        }
        if (fechaVenUnix >= hoy && fechaVenUnix <= en4meses) {
          style = 'bg-warning'
        }
        if (fechaVen) {
          this.$nextTick(() => {
            if(!this.isVentas){
              if(this.movTipo!=0 &&  this.movTipo!=undefined ){
               this.productos[index].FechaVen = this.$moment(fechaVen).format("YYYY-MM-DD")
               this.productos[index].IdLote = IdLote 
               this.productos[index].Lote = Lote 
                //console.log(this.$moment(fechaVen).format("YYYY-MM-DD"));
                //Vue.set(this.productos[index], 'fechaVen', this.$moment(fechaVen).format("YYYY-MM-DD"))
              }
            }else{
                 this.productos[index].Lote = Lote 
                 try {
                  this.productos[index].FechaVen = this.$moment(fechaVen).format("YYYY-MM-DD")
                 } catch (error) {
                  
                 }
              }
             Vue.set(this.productos[index], 'fechaVenMovimiento', `<small style="font-weight:bold" class='venci ${style}'>${ this.$moment(fechaVen).format("DD-MM-YYYY") }</small>`)
          })
        } else {
          this.$nextTick(() => {
            Vue.set(this.productos[index], 'fechaVenMovimiento', '')
          })
        }
          // return `<small class='venci ${style}'>Venci: ${fechaVen}</small>`
      }
    },
    loadMovimientoLotes ($idProducto) {
      this.movimientoLotes[$idProducto]=[]
      this.axios.get(this.API + 'movimientolotes/' + $idProducto+'/'+(this.isVentas?this.idAlmacen:(this.movTipo==0?this.almacenDestino:this.almacenOrigen)))
        .then((response) => {
          if(response.data.length > 0){
            let indexProducto = this.productos.findIndex(p => p.IdProducto === $idProducto);
            // Cargar por defecto lote próximo a vencer.
            let primeroVencer = response.data.reduce((a,b) => {
              return (a.FechaVen && (new Date(a.FechaVen) < new Date(b.FechaVen))) ? a : b;
            })
            
            this.$nextTick(() => {
              Vue.set(this.productos[indexProducto], 'hashMovimiento', primeroVencer.hashMovimiento);
              this.changeMovimiento(primeroVencer.hashMovimiento, $idProducto, indexProducto)
            })
            
            this.movimientoLotes[$idProducto] = response.data.map(l => ({
              value: l.hashMovimiento,
              text : l.Lote,
              Stock : l.Stock,
              IdLote: l.IdLote,
              Lote: l.Lote,
              FechaVen: l.FechaVen,
              SerieNumero: l.Serie + '-' + l.Numero
            }))
          }
        })
        .catch((error) => {
          console.log(error)
        })
    },
    asignarVuelto () {
      this.vuelto = this.pagocon - this.sumatoriaTotal
    },
    loadBarcode (e) {
      e.preventDefault()
      let idProducto = this.loadCode
      this.codeBar = this.loadCode

      if(idProducto){
        if(this.isVentas){
          let params = {codigoBarra: idProducto, idAlmacen: this.idAlmacen || 1}
          this.loadCode = ''
          axios.get(this.API + '/productos/codigobarra', {params: params})
          .then((response) => {
            if (response.data) {
              this.setProducto(response.data[0])
            }else{
              this.$toasted.show('No se encontró ningun producto')
              this.loadCode = ''
            }
          })
        }else{
          let params = {codigoBarra: idProducto}
          this.loadCode = ''
          axios.get(this.API + '/productos/id/'+idProducto, {params: params})
          .then((response) => {
            if (response.data) {
              this.setProducto(response.data)
            }else{
              this.$toasted.show('No se encontró ningun producto')
              this.loadCode = ''
            }
          })
        }

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
        if(prod.ControlaStock==0){
          let index = this.productos.findIndex((pro)=> pro.IdProducto === prod.IdProducto)
          if(index !==-1){
            this.productos[index].stock = response.data.stock || 0
          }
        }
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
    setProducto(obj){
      if(!this.isVentas){
        if(this.movTipo==undefined){
          this.$toasted.show('Primero seleccione un tipo de movimiento')
          return
        }
      }
      try {
        if(obj.Presentaciones !='' && obj.Presentaciones !=null){
          this.PresentacionesProd = JSON.parse(obj.Presentaciones)
          if(this.PresentacionesProd.length==0){
             this.setProductoUni(obj)
             return
          }
        }else{
          this.setProductoUni(obj)
          return
        }
      } catch (error) {
        
      }
      this.objProd = {}
      this.objProd = obj
      this.indexProdSelect = null
      this.$refs['modalCant'].show()
    },
    setProductoUni (objIni,presentacion = false) {
      let obj = {...objIni}
      obj.cantxPrecio = 1
      if(presentacion){
        obj.Presentacion = presentacion.nombre
        obj.cantidadPress = Number(presentacion.cantidad || 1)
        obj.EsPresentacion = 1
        obj.PrecioCostoF = presentacion.preciocosto || 0
      }else{
        obj.Presentacion = obj.ProductoMedicion
        obj.cantidadPress = 1
        obj.EsPresentacion = 0
        obj.PrecioCostoF = obj.PrecioCosto || 0
      }
      this.$refs.autocompleteProducto.setValue('')
      //obj.FLETE = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj,presentacion)
      if(this.indexProdSelect == null){
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.loadMovimientoLotes(obj.IdProducto)
      },{ setPrecioCosto: (this.isVentas ? false : true)}])
      }else{
        this.actualizarProd({Producto:obj,Pos:this.indexProdSelect});
        this.indexProdSelect = null
      }
      // Vue.set(this.productos, obj.IdProducto, obj)
      this.$refs['modalCant'].hide()
      this.loadCode = ''
      this.$refs.barcodeInput.focus() 
    },
    showPresentacion (obj, index=null) {
      if (!obj.Presentaciones !== '' && index!=null) {
        this.PresentacionesProd = obj.Presentaciones ? JSON.parse(obj.Presentaciones) : []
        this.objProd = obj
        this.indexProdSelect = index
        this.$refs['modalCant'].show()
        return
      }
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = 'ss'

        // cambiar color los vencidos
        var dt = new Date()
        var anio = dt.getFullYear()
        var mes =  dt.getMonth() + 1
        var dia = dt.getDate()
        var fechaVen = new Date(data.FechaVen).getTime()
        
        var hoy = anio + "-" + mes + "-" + dia
        hoy = new Date(hoy).getTime()
        var en4meses = anio + '-' + (mes + 4) + '-' + dia
        en4meses = new Date(en4meses).getTime()

        // if(fechaVen < hoy && data.stock > 0) {
        /*if(fechaVen && fechaVen < hoy) {
          style = 'danger'
        }

        // cambiar color los que faltan 4 meses por vencer
        // if (fechaVen >= hoy && fechaVen <= en4meses && data.stock > 0) {
        if (fechaVen >= hoy && fechaVen <= en4meses) {
          style = 'ambar'
        }*/

        let styleStock = ''

        if(data.stock>0){
          styleStock = 'table-success'
        }else{
          styleStock = 'table-danger'
        }

        // if (!data.ControlaStock) {
        //   data.stock = '---'
        // }

//<b class="bg-success" style="color: #fff; padding: 0 6px">${data.stock}</b> | 
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
          <table id="table-render" style="width: 1050px; table-layout: fixed; word-wrap:break-word;">
            <thead>
              <tr style>
                <th class="table-info text-center" style="width: 60px;">Stock</th>
                <th style="width: 250px; padding-left:10px !important">Producto</th>
                <th style="width: 150px; padding-left:10px !important">Forma</th>
                <th style="width: 100px; padding-left:10px !important">Medición</th>
                <th style="width: 200px; padding-left:10px !important">Compuestos</th>
                <th style="width: 100px; padding-left:10px !important">Bloque</th>
                <th style="width: 150px; padding-left:10px !important">Lab</th>
                <th class="table-success text-center" style="width: 65px">Precio.</th>
                <th class="text-center" style="width: 35px">V.E.</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="${styleStock} text-center"><b>${data.ControlaStock==1?data.stock:'<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAE8ElEQVRIS62VW2xUVRSGv31uc86cmelMW1pLG3uBKkGJIQpqvCfeUmOEB6LBF5AHNET0QYIkJvqgIIkxGhMSfcG7UaI1onh7gGgUDWlaWlpoS6UFp3c60850bueyzYxaHZNKEz1PJzt77W+vf61/bcESv/q94aq4TBl0keQQ2SWGIZayselck7lucs3obP24eXxv14HUAeeppcQV9ywJcOOFBmuruSszUx3n+Udf/Tr9ev7e/xXAQcxPHvo4u9yM0bZjw5GZA3P3/T+A5zA4hsIwfNH9cbYiHGTDpgc/mhZzmxlmGScYvxTo3yW6k9sf2fbM0Vuva6GxsYZ27QMibgX93iifbj58wWn3Lv9vACC2KVahta5I5m/5BVrnUBuiZPYnT+efdVdf6vBFizz07e4KQ/o1lbHq9426YOPcxHz1slf2CL9Fsq3lCval27oc4h+69ZWfBazwyGX3vDS/GGxBoukT+553kbf7XvYakLZXyIvZ2XkG+seITGvs6+jku55zvHj1DdTdVEf96mpW1FWjWULqKE7OdU/rgchXy9bu3iME8k9gCZD8fl/M05hRdAtUSSbnUMi6OJkZ5vMJMjnQPR1N0cnqKkpQJ6CDcHzskEUmkyeXyqAiSV+cCt+x41C6DHDu6HNRDT9R0kwRZFIJBD7R2hZCtolmaiiVJn6hwMWRSWZnk8xMzpLPpVGEgYqP0LzS/9T8lH3/9sOZMsDI50/HXEPMoIBXkFi2jm4KUsks0hFIIQmaUXr6uvFcH1MXqLqG0LWSFroARwoUoZBJjZttO7/MlwGKRS3k3GRxUfqS5tUrUTWFUx19WIEA4GGFK+g904uhKUhFB99FVRR0ReAWPKQorqsUun402147Ww7obH8yKqSXKGbgFnxC0Qi1LY30ff8TdiwMLtgVMXp6T2KqCqAQDGmMT2cxdJ2unimuX7cc1fMZOTlkbH+jwynLoPPglqhjBko1wINcIUO64FNlBxG6ipSSSLiKzr4OLKGhWTpzqQITM3niF6ZZv66J+lqbmYsew2c7zJ3/zODowS1RTRG/AwBVgOv7KJqB9NyibkRCFZzs7Ua1LGJWkM7+cUIhlfh4Cicnufn6OnxHJd13ytr65nCuLIMj7z4cMbLMlpml2MB/dHMxA9uupO9MB1bAZj6bJ530iNaYSM1lcrRAa0MVWc9lvL8rtOudiQXjlXzQ2b4lOjY2n0Au+KPcmD6YtkXPmT7Clo0S0BgcThC1A6QzHlWVOrGQjueq5DOz0e37OxYuWwLs2Liq6q7bWqelXGz2SQKWzeDAIKZloesqiWQW35cELZWArpUk1RWFd9r76471Ti1M2dKJlkXDey/cf8H1vFKQLBqn7C2SmFaEoaF+9IABUqAqothM+O7vWqqqgqYInth7vCEL8bIaACv3P35tf+NlYUXTDTxf4Pgeng9CFoFgGgYD538l+Ie5inxVCDRNQZOSgpNjJD7vv3xoeBUw+E+ABdy9crn52FXNFevXXlkVbVgeEaGggY9CruCjKQYj8QlMS8e2QHoF5lIO5+Mp2TeUTAzG88dHk94B4BhQ7CJ/sXEdAW6rjaibm+tDd9y8prqmtTkimppijJ6fYHIyw9mxOflz99zU+Wnnm2SOt4EfgIX58/cOudSjrwLrDY2NTTXmA9l8Xh1LyI9cn7eAgb8aefGn5zdfsxo3JM8ArgAAAABJRU5ErkJggg==" style="width: 24px" />'}</b></td>
                <td style="padding-left:10px !important"><b>${data.Producto}</b></td>
                <td style="padding-left:10px !important">${data.ProductoFormaFarmaceutica}</td>
                <td style="padding-left:10px !important">${data.ProductoMedicion}</td>
                <td style="font-size:10px; padding-left:10px !important">${data.CompuestosProducto}</td>
                <td style="font-size:10px; padding-left:10px !important"><b>${data.Bloque}</b></td>
                <td style="padding-left:10px !important">${data.ProductoMarca}</td>
                <td class="table-success text-center"><b>${data.PrecioContado}</b></td>
                <td class="text-center"><b>${data.VentaEstrategica==1?'<i class="fas fa-star text-warning"></i>':''}</b></td>
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

      precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))


      let normalPrecio = this.$options.filters.redondear(precio)
      let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      
      Vue.set(this.productos[index], 'normalPrecioContado', normalPrecio)
      Vue.set(this.productos[index], 'nuevoPrecioContado', nuevoPrecio)
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
    },
    getSubtotal(index) {
      
      let subtotal = this.$options.filters.redondear(this.productos[index].cantxPrecio * this.productos[index].precio)
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
        Vue.set(this.productos[index], 'descuento', 0)
        return
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

td.stocksize{
  text-align: center !important; 
  vertical-align: middle !important;
}
.contentCard {
  cursor: pointer;
}
.nomCart {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 2px solid black;
}
.nomCart2 {
  display: flex;
  align-items: center;
  justify-content: center;
}
.precioCart{
  display: flex;
  align-items: center;
  justify-content: center;
}
.ventasAncho{
  width: 180px ;
}
</style>

