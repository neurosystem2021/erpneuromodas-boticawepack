<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
        <b-container fluid class="ancho bv-example-row">
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
                        step="0.0001"
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
                :tipoCambio="movimiento.TipoCambio"
                tipo="mov" :tipoMov="movimiento.movimientoTipo.Tipo" :onSetProducto="onSetProducto"
                :almacenDestino = "movimiento.almacenDestino.IdAlmacen"
                :almacenOrigen = "movimiento.almacenOrigen.IdAlmacen"></productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Guardar</b-button>
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
  mounted () {
  },
  data () {
    return {
      inProgress: false,
      newRuc: '',
      API: config.API,
      ultimoMovimiento: '',
      movimiento: this.initMovimiento(),
      isTraslado:true,
      isSalida:false,
      almacenPrincipal: 1,
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
      obtenerStok: 'obtenerStock'
    }),
    onSetProducto (obj, cant, precio) {
      if (cant) {
          obj.cantidad = cant
          obj.precio = obj.PrecioCosto || 0
          obj.nuevoPrecioContado = obj.PrecioContado || 0
          return obj
      }
      obj.cantidad = 1
      obj.precio = obj.PrecioCosto || 0
      obj.nuevoPrecioContado = obj.PrecioContado || 0
      return obj
    },
    onAddProducto (obj) {
      if(this.isSalida){
        this.obtenerStok({
          IdAlmacen: (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
        })
        console.log(this.movimiento.almacenOrigen.IdAlmacen)
      }else{
        this.obtenerStok({
          IdAlmacen: !this.isTraslado?(this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1): (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
        })
      }
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
        almacenDestino: { IdAlmacen: -1 },
        user: sessionStorage.getItem("User")
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
    setTipoMovimiento (obj) {
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 }
        this.movimiento.almacenDestino = { IdAlmacen: 1 }
        this.isTraslado = false
        this.isSalida = false
        if(this.$refs.inputAlmacenDestino){
          this.$refs.inputAlmacenDestino.setValue('')
        }
      }else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 }
        this.movimiento.almacenOrigen = { IdAlmacen: 1 }
        if(this.$refs.inputAlmacenOrigen){
          this.$refs.inputAlmacenOrigen.setValue('')
        }
        this.isTraslado = false
        this.isSalida = true
      }else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 }
        this.movimiento.almacenOrigen = { IdAlmacen: 1 }
        this.isTraslado = true
        this.isSalida = false
        if(this.$refs.inputAlmacenDestino){
          this.$refs.inputAlmacenDestino.setValue('')
        }
        if(this.$refs.inputAlmacenOrigen){
          this.$refs.inputAlmacenOrigen.setValue('')
        }
      }
      this.productos.map((r)=>{
          r.Chasis = ''
          r.Motor = ''
          r.IdColor = 1
          r.IdMovimientoDet = null
        })
      this.movimiento.movimientoTipo = obj
      console.log(obj);

      this.cargarNumero()
    },
    setProveedor (obj) {
      this.movimiento.proveedor = obj
    },
    setAlmacenOrigen (obj) {
      this.movimiento.almacenOrigen = obj
      if(this.isSalida){
        this.obtenerStok({
          IdAlmacen: (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
        })
      }else{
        this.obtenerStok({
          IdAlmacen: !this.isTraslado?(this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1): (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
        })
      }

      this.productos.map((r)=>{
        r.Chasis = ''
        r.Motor = ''
        r.IdColor = 1
        r.IdMovimientoDet = null
      })
    },
    setAlmacenDestino (obj) {
      this.movimiento.almacenDestino = obj
      this.obtenerStok({
        IdAlmacen: !this.isTraslado?(this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1): (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
      })
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
      let sinStock = 0
      let faltaCantidad = false
      let vehiculoCantidad = false
      let noColor = false
      let sinSeleccionar = false
      let movDetDuplicado = false
      let sinStockVehiculo = false
      let mismoChasis = false
      this.productos.forEach((prod, index) => {
        if((parseFloat(prod.stock) <= 0 || parseFloat(prod.stock).toFixed(2)  < parseFloat(prod.cantidad) || prod.stock == undefined)) {
            sinStock += 1
        }

        if (prod.total <= 0 && prod.EsObsequio != 1) {
          faltaLlenar = true
        }

        if(!(prod.cantidad > 0)){
          faltaCantidad = true
        }

        if(prod.cantidad != 1 && prod.EsVehiculo == 1){
          vehiculoCantidad = true
        }

        if(prod.IdColor == 1 && prod.EsVehiculo == 1){
          noColor = true
        }

        if(!(prod.IdMovimientoDet > 0) && prod.EsVehiculo == 1){
          sinSeleccionar = true
        }

        let count = this.productos.filter((p)=>p.IdMovimientoDet == prod.IdMovimientoDet && prod.EsVehiculo==1)
        if(count.length > 1){
            movDetDuplicado = true
        }

        let cantidadTotal = 0
        this.productos.map((p)=>{
            if(p.IdProducto == prod.IdProducto){
                cantidadTotal += (p.cantidad || 0)
            }
            
        })

        if(prod.EsVehiculo){
          let count = this.productos.filter((f)=>((""+(f.Chasis || '')).toUpperCase().trim() == (""+(prod.Chasis || '')).toUpperCase().trim() || (""+(f.Motor || '')).toUpperCase().trim() == (""+(prod.Motor || '')).toUpperCase().trim()) && f.EsVehiculo==1).length
          
          if(count > 1 ){
            this.$toasted.show('Se repite chasis en el item: ' + (index+1))
            mismoChasis = true
          }
        }

        if(((prod.stock <= 0) || (parseFloat(cantidadTotal) > prod.stock) || prod.stock==undefined) && prod.EsVehiculo == 1){
            sinStockVehiculo = true
        }

        prod.Chasis = (""+(prod.Chasis || '')).toUpperCase().trim()
        prod.Motor = (""+(prod.Motor || '')).toUpperCase().trim()
        prod.Descripcion = (""+(prod.Descripcion || '')).toUpperCase().trim()
      })

      if (mismoChasis > 0) {
        this.inProgress = false
        return;
      }

      if (noColor && (!this.isTraslado && !this.isSalida)) {
        this.$toasted.show('Todo VEHICULO debe seleccionarse el color')
        this.inProgress = false
        return;
      }

      if (vehiculoCantidad) {
        this.$toasted.show('Un producto es VEHICULO debe ingresar solo cantidad 1')
        this.inProgress = false
        return;
      }

      if (faltaCantidad) {
        this.$toasted.show('Complete la cantidad de los productos ingresados')
        this.inProgress = false
        return;
      }

      if (faltaLlenar && this.movimiento.alterarProductos) {
        this.$toasted.show('Falta llenar cantidad / precio')
        this.inProgress = false
        return;
      }

      if (sinStock>=1 && (this.isTraslado==true || this.isSalida==true)) {
        this.$toasted.show('No tiene stock para realizar el movimiento')
        this.inProgress = false
        return;
      }

      if (sinSeleccionar && (this.isTraslado==true || this.isSalida==true)) {
        this.$toasted.show('Tiene que seleccionar un chasis con el boton azul para la operacion')
        this.inProgress = false
        return;
      }

      if (sinStockVehiculo && (this.isTraslado==true || this.isSalida==true)) {
        this.$toasted.error('No tiene stock de vehículos para las cantidades agregadas', {duration: 4000});
        this.inProgress = false            
        return;
      }
      

      if (movDetDuplicado && (this.isTraslado==true || this.isSalida==true)) {
        this.$toasted.error('Solo debe seleccionar un chasis por vehículo', {duration: 4000});
        this.inProgress = false            
        return;
      }

      if(this.isTraslado==true || this.isSalida==true){
        this.movimiento.alterarProductos = 0
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
          this.$toasted.success('Movimiento guardado correctamente ')          
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
  @media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
    .ancho{
        width: calc(100%)
    }
  }
</style>