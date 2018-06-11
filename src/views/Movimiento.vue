<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="onSubmit" @reset="onReset" validated>
        <b-container class="bv-example-row">
          <b-row>
              <b-col md="8">

                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Tipo de Movimiento:</label>
                      <autocomplete
                        ref="inputMovimiento"
                        :url="API + 'movimientos/tipos'"
                        anchor="TipoMovimiento"
                        label="writer"
                        placeholder=""
                        :on-select="setTipoMovimiento"
                        :onFocus="focusAutocomplete.bind(null, 'inputMovimiento')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="8">
                    <b-form-group>
                      <label>Proveedor:</label>
                      <b-input-group>
                        <autocomplete
                          ref="inputProveedor"
                          :options="proveedoresFiltrados"
                          url="#"
                          :onShouldGetData="getProveedores"
                          anchor="ProveedorRuc"
                          label="writer"
                          placeholder=""
                          :on-select="setProveedor"
                          :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                          :classes="{input: 'form-control'}">
                        </autocomplete>

                        <b-input-group-append>
                          <!--<proveedores-tabla></proveedores-tabla>-->
                          <proveedores-form></proveedores-form>
                        </b-input-group-append>
                      </b-input-group>
                    </b-form-group>
                  </b-col>
                </b-row>

                <b-row>
                  <b-col md="3">
                    <b-form-group>
                      <label>Periodo Tributario:</label>
                      <b-form-input v-model="movimiento.FechaPeriodoTributario"
                        type="text"
                        readonly="readonly"
                        placeholder="Periodo Tributario"></b-form-input>
                    </b-form-group>
                  </b-col>
                  <b-col md="3">
                    <b-form-group
                      v-if="movimiento.movimientoTipo.Tipo == 1 || movimiento.movimientoTipo.Tipo == 2">
                      <label>Almacen Origen:</label>
                      <autocomplete
                        :options="almacenes"
                        initValue="VENTA"
                        url="#"
                        :onShouldGetData="getAlmacenes"
                        anchor="Almacen"
                        label="writer"
                        placeholder="Almacen Origen"
                        :on-select="setAlmacenOrigen"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="3">
                    <b-form-group
                      v-if="movimiento.movimientoTipo.Tipo == 0 || movimiento.movimientoTipo.Tipo == 2">
                      <label>Almacen Destino:</label>
                      <autocomplete
                        :options="almacenes"
                        initValue="VENTA"
                        url="#"
                        :onShouldGetData="getAlmacenes"
                        anchor="Almacen"
                        label="writer"
                        placeholder="Almacen Destino"
                        :on-select="setAlmacenDestino"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="3">
                    <b-form-group label="Credito: ">
                      <b-form-checkbox id="checkbox1"
                        v-model="movimiento.EsCredito"
                        :value="1"
                        :unchecked-value="0">
                        Pago pendiente
                      </b-form-checkbox>
                      <b-form-input v-model="movimiento.FechaVenCredito"
                        v-if="movimiento.EsCredito"
                        type="date"
                        placeholder="Fecha Vencimiento"></b-form-input>
                    </b-form-group>
                  </b-col>
                </b-row>

                
            
              </b-col>
              <b-col md="4">
                <b-row>
                  <b-col>
                    <b-form-group>
                      <label>Serie:</label>                    
                      <b-form-input v-model="movimiento.Serie"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Número:</label>                    
                      <b-form-input v-model="movimiento.Numero"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Moneda:</label>
                      <autocomplete
                        :url="API + 'monedas'"
                        initValue="PEN"
                        anchor="Moneda"
                        label="writer"
                        placeholder="Moneda"
                        :on-select="setMoneda"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col>
                    <b-form-group>
                      <label>Fecha Contable:</label>
                      <b-form-input
                        v-model="movimiento.MovimientoFecha"
                        type="date"
                        placeholder="Fecha Contable"></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Fecha Stock:</label>
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Tipo de cambio:</label>
                      <b-form-input v-model="movimiento.TipoCambio"
                        type="number"
                        placeholder="Tipo de cambio"></b-form-input>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-col>
          </b-row>
          <b-row>
            <b-col>
              <productos-carrito></productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="guardarMovimiento">Guardar</b-button>
              <b-button v-if="ultimoMovimiento" target="_blank" variant="info" size="lg" :href="'/views/codigoBarra.php?hash=' + ultimoMovimiento">Código de Barra</b-button>
            </b-col>
          </b-row>
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
  data () {
    return {
      API: config.API,
      ultimoMovimiento: '',
      movimiento: this.initMovimiento()
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
    initMovimiento () {
      let currentMonth = this.$moment().month() + 1
      currentMonth = currentMonth < 10 ? '0' + currentMonth : currentMonth
      return {
        EsCredito: 0,
        FechaVenCredito: '',
        Moneda: 'PEN',
        FechaPeriodoTributario: this.$moment().year() + '' + currentMonth,
        // MovimientoFecha: this.$moment().format("YYYY-MM-DDTH:mm"),
        MovimientoFecha: this.$moment().format("YYYY-MM-DD"),
        FechaStock: this.$moment().format("YYYY-MM-DD"),
        TipoCambio: 1,
        proveedor: { IdProveedor: 0 },
        movimientoTipo: {}
      }
    },
    onSubmit (evt) {
      evt.preventDefault();
      alert(JSON.stringify(this.form));
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
    setTipoMovimiento (obj) {
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 }
        this.movimiento.almacenDestino = { IdAlmacen: 1 }
      }else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 }
        this.movimiento.almacenOrigen = { IdAlmacen: 1 }
      }else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 }
        this.movimiento.almacenOrigen = { IdAlmacen: 1 }
      }
      this.movimiento.movimientoTipo = obj
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
      this.movimiento.Moneda = obj.Moneda
    },
    getProveedores (value) {
      this.buscarProveedores(value)
    },
    getAlmacenes (value) {
      this.buscarAlmacenes(value)
    },
    guardarMovimiento () {
      this.axios.post(this.API + 'movimientos', {
        movimiento: this.movimiento,
        productos: this.productos
      })
      .then((response) => {
        console.log(response)
        if (response.data.error) {
          this.$toasted.show(response.data.error)
        } else {
          this.ultimoMovimiento = response.data.hash
          this.movimiento = this.initMovimiento()
          this.limpiar()
          this.$toasted.show('Movimiento guardado correctamente ')          
        }
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
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