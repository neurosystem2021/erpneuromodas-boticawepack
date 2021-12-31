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
                  :disabled="isNota"
                  @keydown.enter.prevent="loadBarcode"
                   ref="barcodeInput"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form :showButton="showButton" :cbGuardar="(id,pxp,obj) =>{agregarGuardar(id,pxp,obj)}" ref="productoFormEdit" :producto-update="productoSelect" ></producto-form>
          </b-input-group-append>
        </b-input-group>
        <b-badge v-if="isVentas" variant="danger">Productos sin stock</b-badge>
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
              v-if="parseFloat(d.cantidad) > 0"
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
                  <h5>x{{ d.cantidad }}</h5>
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
          <b-button :disabled="bloquearInput?true:false" size="sm" variant="info" @click="showPresentacion(productos[data.index],data.index)"><i class="fas fa-list"></i></b-button>
        </template>

        <template slot="cantxPrecio" slot-scope="data" v-if="productos[data.index].PreciosPorProducto">
          <b-form-input
            v-model="productos[data.index].cantxPrecio"
            type="number"
            style="width: 75px"
            step="0.001"
            min="0"
            :disabled="bloquearInput?true:false"
            @input="(val) => changeCant(val, productos[data.index], data.index)"
            placeholder="Cantidad">
          </b-form-input>
          <span>x{{productos[data.index].cantidadPres}}</span>
        </template>

        <template slot="cantidad" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].cantidad"
            :disabled="isVentas? true : false"
            type="number"
            style="width: 75px"
            min="0"
            @input="(val) => {
              $emit('onInputCantidad', val)
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
            placeholder="Cantidad"></b-form-input>
        </template>
        <template slot="precio" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].precio"
            :disabled="productos[data.index].disabled || bloquearInput?true:false"
            type="number"
            style="width: 100px"
            min="0"
            step="0.00001"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)">
          </b-form-input>
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

        <template slot="utilidad" slot-scope="data" v-if="false">
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
          <b-form-input
            :disabled="bloquearInput?true:false"
            v-model="productos[data.index].descuento"
            type="number"
            min="0"
            style="width: 100px"
            step="0.01"
            @input="(val)=>ActualizarDesc(data.index,val)"        
            :value="0"></b-form-input>
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

        <!--<template slot="Descripcion" slot-scope="data">
          <textarea 
            class="form-control"
            placeholder="Descripción"
            cols="9" rows="3"
            v-model="productos[data.index].Descripcion"></textarea>
          <b-form-input
            v-model="productos[data.index].Descripcion"
            type="text"
            placeholder="Descripción"></b-form-input>
        </template>--> 

        <template slot="movimiento" slot-scope="data" v-if="(movTipo!=0 && movTipo!=undefined ) || isVentas">
          <div v-if="isNota" style="width: 135px; text-align:center">
            <div>
              {{productos[data.index].hashMovimiento}}
            </div>
            <small>Doc: <span style="font-weight:bold;"> {{productos[data.index].Serie}}-{{productos[data.index].Numero}}</span></small><br />

            <div>
              {{productos[data.index].FechaVen}}
            </div>
          </div>
          <b-form-select v-if="!isNota"
            style="width: 135px;"
            :disabled="bloquearInput?true:false"
            @change="(val) => changeMovimiento(val, data.item.IdProducto, data.index)"
            v-model="productos[data.index].hashMovimiento" :options="movimientoLotes[data.item.IdProducto]"></b-form-select>
          <br>
          <div style="text-align:center" v-if="!isNota">
          <small>Stock: <span style="font-weight:bold;">{{ productos[data.index].stockMovimiento }}</span></small>
          <small>Doc: <span style="font-weight:bold;">{{ productos[data.index].SerieNumero }}</span></small>
          </div>

          <div v-if="!isNota" v-html="productos[data.index].fechaVenMovimiento"></div>
        </template>

        <template slot="fechavencimiento" slot-scope="data" >
          <b-form-input
            required="required"
            v-model="productos[data.index].FechaVen"
            type="date"
            ></b-form-input>
        </template>

        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
          <br>
          <h6 v-if="isVentas"><i>Prof.</i></h6>
          <input v-if="isVentas"
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
          <b-button :disabled="bloquearInput?true:false" variant="danger" size="sm" @click="quitarProducto(data.index)"><i class="fas fa-times fa-lg"></i></b-button>
          <b-button  style="margin-top:5px" :disabled="bloquearInput?true:false"  v-if="(idPerfil == 1) ? true : false" variant="primary" size="sm" @click="editarProducto(productos[data.index])"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
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
      movimientoLotes: {},
      productoSelect: {},
      indexProdSelect: null,
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
    isNota:Boolean,
    tipo: String,
    setPrecio: Boolean,
    tipoCambio: Number,
    igvTotal: Boolean,
    idAlmacen: Number,
    movTipo: Number,
    almacenDestino:Number,
    almacenOrigen:Number,
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
          { key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
          { key: 'movimiento', label: 'Lote', variant: 'info', class:'text-center'  },
          { key: 'CodigoBarra', label: 'Código'},
          //{ key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo' }, 
          //{ key: 'Descripcion', label: 'Descripción'},
          { key: 'ProductoMarca', label: 'Marca' },
          //{ key: 'ProductoModelo', label: 'Modelo/Color'}, 
          //{ key: 'ProductoMedicion', label: 'Medición' },
          { key: 'cantidad' },
          { key: 'precio', label: 'P/U' },
          { key: 'subtotal', label: 'SubTotal', variant: 'success' },
          { key: 'igv', label: 'IGV' },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: 'precioVenta', label: 'Precio venta (S/)' },
          { key: 'total', label: 'Total', variant: 'danger' },
          { key: 'fechavencimiento', label: 'Vencimiento' },
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
    changeMovimiento(hash, idProducto, index) {
      if  (hash) {
        // Stock
        let stock = this.movimientoLotes[idProducto].find(m => m.value === hash).Stock
        let serieNumero = this.movimientoLotes[idProducto].find(m => m.value === hash).SerieNumero
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
        let en5dias  = this.$moment().add(5, 'days').unix();
        let style = 'bg-success'
        if(fechaVenUnix && fechaVenUnix < hoy) {
          style = 'bg-danger'
        }
        if (fechaVenUnix >= hoy && fechaVenUnix <= en5dias) {
          style = 'bg-warning'
        }
        if (fechaVen) {
          this.$nextTick(() => {

            if(!this.isVentas){
              if(this.movTipo!=0 &&  this.movTipo!=undefined ){
                this.productos[index].FechaVen = this.$moment(fechaVen).format("YYYY-MM-DD")
                //console.log(this.$moment(fechaVen).format("YYYY-MM-DD"));
                //Vue.set(this.productos[index], 'fechaVen', this.$moment(fechaVen).format("YYYY-MM-DD"))
                this.actualizarProd({Producto:this.productos[index],Pos:index})
              }
            }

            Vue.set(this.productos[index], 'fechaVenMovimiento', `<small style="font-weight:bold" class='venci ${style}'>F. Venci: ${ this.$moment(fechaVen).format("DD-MM-YYYY") }</small>`)
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
      this.axios.get(this.API + '/movimientolotes/' + $idProducto+'/'+(this.isVentas?this.idAlmacen:(this.movTipo==0?this.almacenDestino:this.almacenOrigen)))
        .then((response) => {
          let indexProducto = this.productos.findIndex(p => p.IdProducto === $idProducto);
          // Cargar por defecto lote próximo a vencer.
          let primeroVencer = response.data.reduce((a,b) => {
            return (a.FechaVen && (new Date(a.FechaVen) < new Date(b.FechaVen))) ? a : b;
          })
          
          this.$nextTick(() => {
            Vue.set(this.productos[indexProducto], 'hashMovimiento', primeroVencer.hashMovimiento);
            this.changeMovimiento(primeroVencer.hashMovimiento, $idProducto, indexProducto)
          })
          //
          this.movimientoLotes[$idProducto] = response.data.map(l => ({
            value: l.hashMovimiento,
            text : l.hashMovimiento,
            Stock : l.Stock,
            FechaVen: l.FechaVen,
            SerieNumero: l.Serie + '-' + l.Numero
          }))
        })
        .catch((error) => {
          console.log(error)
        })
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
    agregarGuardar(id,pxp,obj){
     /* this.add([id,false,false,false,false,true,this.isVentas]);
      setTimeout(() => {
              this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) ;
      }, 200); 
      */
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
        if(this.movTipo==undefined){
          this.$toasted.show('Primero seleccione un tipo de movimiento')
          return
        }
        this.setProductoUni(obj)
        return
      }
      if(fPP.length==4){
        obj.cantxPrecio=1
        this.setProductoUni(obj)
        return
      }
      this.indexProdSelect = null
      this.$refs['modalCant'].show()
      this.objProd = obj
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
      obj.cantidadPres = obj.cantidad
      if(this.indexProdSelect == null){
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
        this.loadMovimientoLotes(obj.IdProducto)
      },{ setPrecioCosto: (this.tipo == 'mov' ? true : false) }, false, this.cliente.EsVIP])
      }else{
        this.actualizarProd({Producto:obj,Pos:this.indexProdSelect});
      }
      // Vue.set(this.productos, obj.IdProducto, obj)
      
      this.$refs['modalCant'].hide()
      
      //this.actualizarProd(obj)
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
      obj = this.onSetProducto(obj, prod.cantidad,prod.precio)
      obj.cantidadPres = this.cant
      if(this.indexProdSelect == null){
        this.add([obj, () => {
        this.$emit('onAddProducto', obj)
        this.$toasted.success('Producto añadido correctamente')
        this.loadMovimientoLotes(obj.IdProducto)
      }, false, false, this.cliente.EsVIP])

      }else{
        this.actualizarProd({Producto:obj,Pos:this.indexProdSelect});
      }

      this.$refs['modalCant'].hide()
      
      //this.actualizarProd(obj)
      this.$refs.barcodeInput.focus()
    },
    showPresentacion (obj, index=null) {
      if (!obj.PreciosPorProducto !== '') {
        this.preciosProd = obj.PreciosPorProducto ? JSON.parse(obj.PreciosPorProducto) : []
        this.objProd = obj
        this.indexProdSelect = index
        this.precioCont = obj.PrecioContado
        this.medicionPro = obj.ProductoMedicion
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
         <b class="bg-${style}" style="color: #fff; padding: 0 6px">${data.stock? Number.isInteger(data.stock)?data.stock:data.stock.toFixed(2):data.stock}</b> | <b>${data.Producto} - ${data.ProductoMarca}</b> | <b>S/ ${data.PrecioContado?data.PrecioContado.toFixed(2):data.PrecioContado}</b> 
      `
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca}
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
    justify-content: center;
    border-right: 2px solid black;
  }
  .form-check-input {
    margin-left: -0.2rem !important;
  }


</style>
