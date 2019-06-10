<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
        <b-container class="bv-example-row">
          <b-row>
              <b-col md="8">

                <b-row>
                 <b-col md="3">
                    <b-form-group
                      v-if="movimiento.movimientoTipo.Tipo == 1 || movimiento.movimientoTipo.Tipo == 2">
                      <label>Almacen Origen:</label>
                      <autocomplete
                        ref="inputAlmacenOrigen"
                        required="required"
                        initValue=""
                        :url="API + 'almacenes'"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenOrigen"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenOrigen')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                </b-row>

              </b-col>
              <b-col md="4">
              </b-col>
          </b-row>
          <b-row>
            <b-col>
              <productos-carrito ref="carrito"
                @onAddProducto="onAddProducto"
                :showButton="false"
                :showPagoCon="false"
                :onSetProducto="onSetProducto"
                :setPrecio="true"
                :fields="fields"
              ></productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="6">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Trasladar a FZ</b-button>
              <!--<b-button v-if="ultimoMovimiento" target="_blank" variant="info" size="lg" :href="'/views/codigoBarra.php?hash=' + ultimoMovimiento">Código de Barra</b-button>-->
                <b-button v-if="ultimoMovimiento" target="_blank" variant="info" size="lg" :href="'/imprimir/index.php?hashMovimiento=' + ultimoMovimiento">Imprimir Último traslado</b-button>
            </b-col>
          </b-row>
                <br>
                <b-alert show variant="warning" v-show="smsAdvertencia" v-html="smsAdvertencia"></b-alert>
        </b-container>
        <br><br>
    </b-form>

  </div>
</template>

<script>
import Menu from '@/components/Menu'
import ProductosCarrito from '@/components/ProductosCarrito'
//import ProveedoresTabla from 'components/ProveedoresTabla'
import ProveedoresForm from '@/components/ProveedoresForm'
import { mapState, mapActions } from 'vuex'
import config from '@/store/config.js'

export default {
  mounted () {
  },
  data () {
    return {
      smsAdvertencia: '',
      inProgress: false,
      newRuc: '',
      API: config.API,
      ultimoMovimiento: '',
      movimiento: this.initMovimiento(),
      fields: [
          { key: 'ProductoCategoria', label: 'Categoria'},
          { key: 'Producto', label: 'Artículo' }, 
          { key: 'ProductoMarca', label: 'Marca' },
          { key: 'ProductoMedicion', label: 'Medicion' },
          { key: 'cantidad' },
          { key: 'precio', label: 'P/U', readOnly: true },
          { key: 'subtotal', label: 'SubTotal', variant: 'success' },
          // { key: 'flete', label: 'FLETE' },
          // { key: 'lote', label: 'Lote' },
          { key: 'precioVenta', label: 'Precio venta', readOnly: true },
          { key: 'total', label: 'Total', variant: 'danger' },
          { key: 'acciones', label: ''}
      ]

    }
  },
  computed: {
    ...mapState({
      proveedoresFiltrados: state => state.proveedoresFiltrados,
      almacenes: state => state.almacenesFiltrados,
      productos: state => state.cart.productos
    })
  },
  methods: {
    ...mapActions({
      buscarProveedores: 'buscarProveedores',
      buscarAlmacenes: 'buscarAlmacenes',
      limpiar: 'limpiarCarrito'
    }),
    loadFirstAlmacen (cb) {
      this.axios.get(this.API + 'almacenes/primero')
        .then(res => {
          cb(res.data)
        })
    },
    onAddProducto (obj) {
        this.axios.get(this.API + 'destino/producto/verificar', {
            params: {
                producto: obj.Producto
            }
        })
            .then(res => {
                console.log(res.data.producto)
                if (!res.data.producto) {
                    this.smsAdvertencia += "El producto <b>" + obj.Producto + "</b> no existe en el sistema de destino, se recomienda crearlo antes de trasladar, en caso contrario, el sistema intentara crearlo pero debe ser verificado luego. <br><br>";
                }
            })
    },
    onSetProducto (obj) {
        // obj.cantidad = 1
        obj.precio = obj.PrecioCosto || 0
        return obj
    },
    cargarNumero () {
      this.axios.get(this.API + 'movimientos/numero', {
        params: {
          serie: this.movimiento.Serie,
          idMovimientoTipo: this.movimiento.movimientoTipo.IdMovimientoTipo
        }
      })
      .then((response) => {
        Vue.set(this.movimiento, 'Numero', response.data.NuevoNumero || '0001')
      })
      .catch((error) => {
        console.log(error)
        // this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    initMovimiento () {
      let currentMonth = this.$moment().month() + 1
      currentMonth = currentMonth < 10 ? '0' + currentMonth : currentMonth
      return {
        EsCredito: 0,
        alterarProductos: 1,
        FechaVenCredito: '',
        Moneda: 'PEN',
        FechaPeriodoTributario: this.$moment().year() + '' + currentMonth,
        MovimientoFecha: this.$moment().format("YYYY-MM-DD"),
        // MovimientoFecha: (this.$browser.name == 'chrome') ? this.$moment().format("YYYY-MM-DDThh:mm:ss") : this.$moment().format("YYYY-MM-DD hh:mm:ss"),
        FechaStock: this.$moment().format("YYYY-MM-DD"),
        TipoCambio: 1,
        proveedor: { IdProveedor: 0 },
        movimientoTipo: {},
        almacenOrigen: { IdAlmacen: -1 },
        almacenDestino: { IdAlmacen: -1 }
      }
    },
    onReset (evt) {
      evt.preventDefault();
      /* Reset our form values */
      /*this.form.email = '';
      this.form.name = '';
      this.form.food = null;
      this.form.checked = [];
      /* Trick to reset/clear native browser form validation state */
      /*this.show = false;
      this.$nextTick(() => { this.show = true });*/
    },
    afe (e) {
      return false
    },
    setTipoMovimiento (obj) {
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 }
        // this.movimiento.almacenDestino = { IdAlmacen: 1 }
        this.loadFirstAlmacen(data => {
          this.$refs.inputAlmacenDestino.setValue(data.Almacen)
          this.movimiento.almacenDestino = data
        })
      }else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 }
        // this.movimiento.almacenOrigen = { IdAlmacen: 1 }
        this.loadFirstAlmacen(data => {
          this.$refs.inputAlmacenOrigen.setValue(data.Almacen)
          this.movimiento.almacenOrigen = data
        })
      }else {
        // this.movimiento.almacenDestino = { IdAlmacen: 1 }
        // this.movimiento.almacenOrigen = { IdAlmacen: 1 }
        this.loadFirstAlmacen(data => {
          this.$refs.inputAlmacenOrigen.setValue(data.Almacen)
          this.movimiento.almacenOrigen = data

          this.$refs.inputAlmacenDestino.setValue(data.Almacen)
          this.movimiento.almacenDestino = data
        })
      }
      this.movimiento.movimientoTipo = obj

      this.cargarNumero()
    },
    setProveedor (obj) {
      this.movimiento.proveedor = obj
    },
    setAlmacenOrigen (obj) {
      this.movimiento.almacenOrigen = obj
    },
    setAlmacenDestino (obj) {
      this.movimiento.almacenDestino = obj
    },
    setMoneda (obj) {
      console.log(obj)
      this.movimiento.Moneda = obj.Moneda
      this.movimiento.TipoCambio = obj.TipoCambio
    },
    getProveedores (value) {
      this.buscarProveedores(value)
    },
    getAlmacenes (value) {
      this.buscarAlmacenes(value)
    },
    guardarMovimiento (evt) {
      evt.preventDefault();
      this.inProgress = true

      if(!this.productos.length) {
          this.$toasted.show('No ha seleccionado ningun producto')
          this.inProgress = false            
          return          
      }

      let faltaLlenar = false
      this.productos.forEach((prod, index) => {
        if (prod.total <= 0) {
          faltaLlenar = true
        }
      })

      if (faltaLlenar && this.movimiento.alterarProductos) {
        this.$toasted.show('Falta llenar cantidad / precio')
        this.inProgress = false
        return;
      }

      this.axios.post(this.API + 'traslado', {
        movimiento: this.movimiento,
        productos: this.productos
      })
      .then((response) => {
        if (response.data.error) {
          this.$toasted.show(response.data.error)
        } else {
          this.ultimoMovimiento = response.data.hash
          this.smsAdvertencia = '';
          this.movimiento = this.initMovimiento()
          this.limpiar()
          this.$toasted.show('Movimiento guardado correctamente ')          
        }
        this.inProgress = false
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        this.inProgress = false
      })
    },
    focusAutocomplete (ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    }
  },
  components: {
    Menu,
    ProductosCarrito,
    ProveedoresForm
  }
}
</script>
<style>
  .input-group {
    /*margin: 0.25rem 0 !important;*/
  }
</style>
