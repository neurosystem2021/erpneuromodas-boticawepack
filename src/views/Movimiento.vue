<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
        <b-container fluid class="bv-example-row">
          <b-row>
              <b-col md="8">

                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Tipo de Movimiento:</label>
                      <autocomplete
                        required="required"
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
                          required="required"
                          ref="inputProveedor"
                          :url="API + 'proveedores'"
                          anchor="ProveedorRuc"
                          label="writer"
                          placeholder=""
                          :onInput="(val) => newRuc = val"                          
                          :on-select="setProveedor"
                          :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                          :classes="{input: 'form-control'}">
                        </autocomplete>

                        <b-input-group-append>
                          <!--<proveedores-tabla></proveedores-tabla>-->
                          <proveedores-form :ruc="newRuc"></proveedores-form>
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
                  <b-col md="3">
                    <b-form-group
                      v-if="movimiento.movimientoTipo.Tipo == 0 || movimiento.movimientoTipo.Tipo == 2">
                      <label>Almacen Destino:</label>
                      <autocomplete
                        ref="inputAlmacenDestino"
                        required="required"
                        initValue=""
                        :url="API + 'almacenes'"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenDestino"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenDestino')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="3">
                    <b-form-group label="Credito: ">
                      <b-form-checkbox 
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

                <b-row>
                  <b-col md="6">
                    <b-form-group>
                      <b-form-checkbox id="alterar-productos"
                        v-model="movimiento.alterarProductos"
                        :value="1"
                        :unchecked-value="0">
                        Alterar precio productos
                      </b-form-checkbox>
                    </b-form-group>
                  </b-col>
                </b-row>
            
              </b-col>
              <b-col md="4">
                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Serie:</label>                    
                      <b-form-input v-model="movimiento.Serie"
                        required="required"
                        @input="cargarNumero"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Número:</label>                    
                      <b-form-input v-model="movimiento.Numero"
                        required="required"
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
                  <b-col md="8">
                    <b-form-group>
                      <label>Fecha Contable:</label>
                      <b-form-input
                        v-model="movimiento.MovimientoFecha"
                        type="datetime-local"
                        step="1"
                        placeholder="Fecha Contable"
                        ></b-form-input>

                    </b-form-group>
                    <b-form-group>
                      <label>Fecha Stock:</label>
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"
                        ></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Tipo de cambio:</label>
                      <input v-model.number="movimiento.TipoCambio"
                        type="number"
                        class="form-control"
                        style="0.00001"
                        placeholder="Tipo de cambio"/>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-col>
          </b-row>
          <b-row>
            <b-col>
              <productos-carrito 
                ref="carrito"
                @onAddProducto="onAddProducto" 
                :onSetProducto="onSetProducto"
                :tipoCambio="movimiento.TipoCambio"
                :movTipo = "movimiento.movimientoTipo.Tipo"
                :almacenDestino = "movimiento.almacenDestino.IdAlmacen"
                :almacenOrigen = "movimiento.almacenOrigen.IdAlmacen"
                tipo="mov" padre="compra"></productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Guardar</b-button>
              <b-button v-if="ultimoMovimiento && false" target="_blank" variant="info" size="lg" :href="'/views/codigoBarra.php?hash=' + ultimoMovimiento">Código de Barra</b-button>
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
  mounted () {
  },
  data () {
    return {
      inProgress: false,
      newRuc: '',
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
      limpiar: 'limpiarCarrito',
      obtenerStok: 'obtenerStock',
      limpiarCart: 'limpiarCarrito',
    }),
    onAddProducto (obj) {
        this.obtenerStok({
          IdAlmacen: this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1
        })
    },
    afe (e) {
return false
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
        // MovimientoFecha: this.$moment().format("YYYY-MM-DDTH:mm"),
        MovimientoFecha: (this.$browser.name == 'chrome') ? this.$moment().format("YYYY-MM-DDThh:mm:ss") : this.$moment().format("YYYY-MM-DD hh:mm:ss"),
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
    onSetProducto (obj,presentacion) {
        if(presentacion){
            obj.cantidad = presentacion.cantidad || 1
            obj.nuevoPrecioContado = presentacion.precioventa || 0
            obj.precio = presentacion.preciocosto || 0
            return obj
        }
        obj.cantidad = 1
        obj.precio = obj.PrecioCosto || 0
        obj.nuevoPrecioContado = obj.PrecioContado || 0
        return obj
    },
    setTipoMovimiento (obj) {
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 }
        this.movimiento.almacenDestino = { IdAlmacen: 1 }
      }else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 }
        this.movimiento.almacenOrigen = { IdAlmacen: 1 }
         this.limpiarCart()
      }else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 }
        this.movimiento.almacenOrigen = { IdAlmacen: 1 }
         this.limpiarCart()
      }
      this.movimiento.movimientoTipo = obj
      this.cargarNumero()
    },
    setProveedor (obj) {
      this.movimiento.proveedor = obj
    },
    setAlmacenOrigen (obj) {
      this.limpiarCart()
      this.movimiento.almacenOrigen = obj
    },
    setAlmacenDestino (obj) {
      console.log(obj);
      this.movimiento.almacenDestino = obj
      this.obtenerStok({ IdAlmacen: this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1 })
    },
    setMoneda (obj) {
      this.movimiento.Moneda = obj.Moneda
      if (obj.Moneda === 'USD') {
        this.movimiento.TipoCambio = 3.7
      } else {
        this.movimiento.TipoCambio = 1
      }
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
      if (!this.movimiento.proveedor.IdProveedor) {
        this.$toasted.show('Por favor seleccione un proveedor')        
        this.inProgress = false            
        return
      }
      if(!this.productos.length) {
          this.$toasted.show('No ha seleccionado ningun producto')
          this.inProgress = false            
          return          
      }
      let faltaLlenar = false
      let faltaFechaVen = false
      let igualProducto = false
      let faltaStock = false
      let faltaLote = false
      this.productos.forEach((prod, index) => {
        if ((((prod.cantidad || 0)  * (prod.precio || 0)) <= 0 ) || ((prod.nuevoPrecioContado || 0) <= 0)) {
          this.$toasted.show('Se requiere los precios del producto '+prod.Producto)
          faltaLlenar = true
        }
        if (prod.FechaVen == undefined || prod.FechaVen=='' || prod.FechaVen.length>10 ) {
          if(prod.FechaVen.length>10){
             this.$toasted.show('La fecha de vencimiento del producto '+prod.Producto+' no tiene el formato correcto')
          }
          faltaFechaVen = true
        }
        let pro = this.productos.filter(p => p.IdProducto == prod.IdProducto).length
          if(pro > 1){
          igualProducto = true
        }

        if(this.movimiento.movimientoTipo.Tipo!=0){
          if((parseFloat(prod.stock) <= 0 || parseFloat(prod.stockMovimiento).toFixed(2) < parseFloat(prod.cantidad) || prod.stock == undefined) ){
           faltaStock=true
          }
        }

        if((''+prod.Lote).trim()=='' || prod.Lote== undefined){
          faltaLote = true
          this.$toasted.error('Por favor especifique un LOTE del producto: '+prod.Producto);
        }
        
      })
      if (faltaStock) {
          this.$toasted.error('No hay stock del producto para el movimiento seleccionado');
          this.inProgress = false            
          return;
      }

      if (faltaLote) {
          this.inProgress = false            
          return;
      }

      if (igualProducto) {
          this.$toasted.error('No puede realizar movimiento de 2 productos iguales, si son lotes diferentes relizalo en otro movimiento');
          this.inProgress = false            
          return;
      }

      if (faltaFechaVen) {
        this.$toasted.show('Ingrese la fecha correcta de vencimiento del producto')
        this.inProgress = false
        return;
      }
      if (faltaLlenar && this.movimiento.alterarProductos) {
        //this.$toasted.show('Falta llenar cantidad / precio')
        this.inProgress = false
        return;
      }

      this.axios.post(this.API + 'movimientos', {
        movimiento: this.movimiento,
        productos: this.productos
      })
      .then((response) => {
        if (response.data.error) {
          this.$toasted.show(response.data.error)
        } else {
          this.ultimoMovimiento = response.data.hash
          this.movimiento = this.initMovimiento()
          this.limpiar()
          this.$refs.inputMovimiento.setValue('')
          this.$refs.inputProveedor.setValue('')
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