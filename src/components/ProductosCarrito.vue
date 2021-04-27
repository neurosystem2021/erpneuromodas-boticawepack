<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            ref="autocompleteProducto"
            :url="API + 'productos'"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="abrirCalcularM2"
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
      <b-table small responsive  striped hover :items="productos" :fields="fields" ref="tablaCarrito">
        <template slot="cantidad" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].cantidad"
            type="number"
            style="width: 75px"
            min="0"
            @input="(val) => $emit('onInputCantidad', val)"
            placeholder="Cantidad"></b-form-input>
        </template>
        <template slot="precio" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].precio"
            type="number"
            style="width: 75px"
            min="0"
            step="0.00001"
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
          <div v-if="!data.item.EsPadre">{{ data.item.stock }}</div>
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
    
     <b-modal size="md" ref="modalCalcularM2" hide-footer title="Calcular Precio" no-close-on-esc no-close-on-backdrop>
      <div class="d-block ">
        <b>{{ productoSelect.Producto }}</b>
        <br/>
        <br/>
          <b-col md="12" style="text-align:center">
              <b-button @click="accionFactor(0)" :variant="indexFactor == 0 ? 'primary' : 'outline-primary'">Público</b-button>
              <b-button @click="accionFactor(1)" :variant="indexFactor == 1 ? 'primary' : 'outline-primary'">Vidriero</b-button>
          </b-col><br/>
        <div v-if="mostrarInputs">
          <b-cold md="12">
            <b-from-group label="Pulgadas:">
              <b-form-radio-group id="Pulgadas"
              button-variant="outline-primary" 
              v-model="esPulgadas"
              @input="() => esPulgadas ? accionFactor(2) : accionFactor(indexFactor) "
              buttons
              :options="[
              { text: 'Pulgada', value: 1 },
              { text: 'Centimetro', value: 0 }
              ]"/>
            </b-from-group>
          </b-cold>
          <hr />
          <label>Medida de corte en (
            <span v-if="esPulgadas">Pulgadas</span>
            <span v-else>Centímetros</span>
            ):</label>
          <br />
          <label>Ancho:</label>
          <b-form-input
            type="number"
            ref="inputAncho"
            v-model="ancho"
            placeholder="Ancho"
            @input="val => calcularm2()"></b-form-input>
          <br />
          <label>Largo:</label>
          <b-form-input
            type="number"
            ref="inputLargo"
            v-model="largo"
            placeholder="Largo"
            @input="val => calcularm2()">
          </b-form-input>
          <br>
          <label>Factor ({{factorNombre}}):</label>  
          <b-form-input
            type="number"
            ref="inputFactor"
            v-model="factor"
            placeholder="factor"
            @input="val => calcularm2()">
          </b-form-input>
          <br>
          <label>Pie<sup>2</sup> del corte ({{factorNombre}}): {{ pie2corte.toFixed(1) }}</label>
          <br>
        </div>
        <hr />
        <p v-html="mensajeM2"></p>
      </div>
      <b-btn class="mt-3" variant="outline-success" block @click="seleccionarCalcularM2">Agregar</b-btn>
    </b-modal>
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
      esPulgadas:'0',
      producto: {},
      items: [],
      productosDet: [],
      handleKeyDown: false,
      vuelto: 0,
      pagocon: 0,
      productoSelect: {},
      mensajeM2: '',
      M2: '',
      ancho: 0,
      largo: 0,
      factorNombre: '',
      factor: 0,
      pie2corte: 0,
      mostrarInputs:false
    }
  },
  watch: {
    sumatoriaTotal (sum) {
      this.asignarVuelto()
    }
  },
  props: {
    setPrecio: Boolean,

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
          { key: 'CodigoBarra', label: 'Código'},
          { key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo' }, 
          { key: 'ProductoMarca', label: 'Marca' },
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
      actualizarTotal: 'actualizarTotalCart'
    }),
    accionFactor(val){
    console.log(val)
    this.mostrarInputs=true
      switch (val) {
        case 0:
          this.indexFactor = val
          this.factorNombre="Público"
          this.esPulgadas=0
          this.factor = 850
          break;
        case 1:
          this.indexFactor = val
          this.factorNombre="Vidriero"
          this.esPulgadas=0
          this.factor = 900
          break;
        case 2:
          this.factorNombre="Pulgadas"
          this.factor = 144
          break;
        default:
          break;
      }

      
    },
    asignarVuelto () {
      this.vuelto = this.pagocon - this.sumatoriaTotal
    },

    setPulgadas(esPulgadas){
      if(!esPulgadas){
        this.$refs.inputFactor.setValue('');
        this.factor = {}
        this.$refs.inputFactor.$refs.input.disable = true
        this.$refs.inputFactor.$refs.input.required = false
      }else{
        this.$refs.inputFactor.$refs.input.disable = false
        this.$refs.inputFactor.$refs.input.required = true
      }
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
        }, { setPrecio: this.setPrecio,  setSuma: true }, true])
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
    abrirCalcularM2 (obj) {
      this.setProducto(obj)
      if (obj.Ancho && obj.Largo) {
        this.mostrarInputs = false
        this.largo = 0
        this.ancho = 0
        this.$refs.modalCalcularM2.show()
      }
      this.productoSelect = obj
      this.productoSelect.cantidad = 1
      this.mensajeM2 = ''
      // this.$refs.inputM2.$refs.input.value = ''
    },
    calcularm2 () {
      // rendimiento = (rendimiento == '0' ) ? false : rendimiento;
      //halla el pie2
      let pie2total = this.productoSelect.Ancho * this.productoSelect.Largo/900
      // let ancho = (this.ancho == '0' ) ? false : this.Ancho;
      // let largo = (largo == '0' ) ? false : prod.Largo;

      if (pie2total > 0 && this.ancho > 0 && this.largo > 0) {
        this.pie2corte = Math.round((this.ancho*this.largo/this.factor)*10)/10
        let precio = (this.pie2corte * this.productoSelect.PrecioContado) / pie2total;
        this.precioCorte = precio.toFixed(2);
        this.mensajeM2 = 'El precio del corte es ' + precio.toFixed(2);
      }
      
      // if (!ancho || !largo) {
      //   alert('El producto no cuenta con un ancho o largo establecido');
      //   return;
      // }

      // this.productoSelect.precio = 133

      // var cantidad = 0
      // var mensaje = 0
      // // console.log(rendimiento)
      // if(rendimiento && rendimiento != '0') {
      //   cantidad = (m2 / rendimiento).toFixed(1);
      //   cantidad = Math.ceil(cantidad)
      //   mensaje = "Total de cajas requeridas: " + cantidad
      // } else {
        // console.log(ancho)
        // console.log(largo)
      //   cantidad = (parseFloat(m2) / ((ancho/100) * (largo/100))).toFixed(1)
      //   cantidad = Math.ceil(cantidad)
      //   mensaje = "Total de unidades requeridas: " + cantidad
      // }

      // if (cantidad > 0) {
      //   var mensajeAdicional = ''
      //   if (productoDetCantidad) {
      //     if (cantidad > productoDetCantidad) {
      //       var cajas = Math.floor(cantidad / productoDetCantidad);
      //       var unidades = cantidad%productoDetCantidad;
      //       mensajeAdicional += '(Cajas: ' + cajas;
      //       if (unidades > 0) {
      //         mensajeAdicional += ', Unidades: ' + unidades + ')';
      //       }else {
      //         mensajeAdicional += ')';
      //       }
      //     }
      //   } 
      //   this.mensajeM2 = mensaje;
      //   this.mensajeM2 += " <br /> " + mensajeAdicional
      //   this.productoSelect.cantidad = cantidad
      // }
      
      // if(!m2) {
      //   this.mensajeM2 = ''
      //   this.productoSelect.cantidad = 1
      // }

    },
    seleccionarCalcularM2 () {
      this.$refs.modalCalcularM2.hide()

      if (this.precioCorte) {
        this.productoSelect.precio = this.precioCorte;
        this.productoSelect.Ancho = this.ancho
        this.productoSelect.Largo = this.largo
      }
      // this.setProducto(this.productoSelect)
    },
    setProducto (obj) {
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
      }])

      // Vue.set(this.productos, obj.IdProducto, obj)
    },
    renderChild(data) {
      return `
        <b>${data.Producto} - ${data.CodigoBarra}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoPresentacion} / ${data.ProductoMedicion} / <b>S/. ${data.PrecioContado}</b>
      `
    },
    setNuevoPrecioContado (index) {

      // Añadir IGV si check IGV
      let precio = parseFloat(this.productos[index].precio)
      if(this.productos[index].TieneIgv) {
        precio += (precio * 0.18)
      }

      //precio += (precio * (this.productos[index].PorcentajeUtilidad/100))
      precio = precio / (1 - (this.productos[index].PorcentajeUtilidad/100))
      // let precio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))


      let normalPrecio = this.$options.filters.redondear(precio)
      //let nuevoPrecio = this.$options.filters.redondeoMayor(precio)
      let nuevoPrecio = this.$options.filters.redondear(precio, 1)
      
      Vue.set(this.productos[index], 'normalPrecioContado', normalPrecio)
      Vue.set(this.productos[index], 'nuevoPrecioContado', nuevoPrecio)
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

<style scoped>
#btnradios1 {
  text-align: center;
  padding: 5px 0 0 130px;
  justify-items: center;
}
</style>
