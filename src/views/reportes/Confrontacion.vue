<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
        <b-container fluid class="bv-example-row">
          <b-row>
              <b-col md="4">
                    <b-form-group>
                      <label>Almacen a Confrontar:</label>
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
              <b-col md="4" v-if="almacen.IdAlmacen">
                <b-button variant="success">Confrontar Todo ({{almacen.Almacen}})</b-button>
                <hr>
                
              </b-col>
          </b-row>
          <hr>
          <b-row>  
            <b-col  md="12">
              <carrito-confrontacion 
                ref="carritoc"
                tipo="mov" :fields="carrito.fields"></carrito-confrontacion>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Confrontar Inventario</b-button>
            </b-col>
          </b-row>
        </b-container>
        <br><br>
    </b-form>

  </div>
</template>

<script>
import Menu from '@/components/Menu'
import CarritoConfrontacion from '@/components/CarritoConfrontacion'
import { mapState, mapActions } from 'vuex'
import config from '@/store/config.js'

export default {
  mounted () {
  },
  data () {
    return {
      inProgress: false,
      API: config.API,
      carrito: {
          fields: [
              //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
              //{ key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
              { key: 'CodigoBarra', label:'Codigo' },
              { key: 'Producto' },
              { key: 'cantidad' , label:'Cantidad Fisica' },
              { key: 'acciones', label: ''}
          ]
      },
      almacen:{}
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
      add:'agregarProducto'
    }),
    afe (e) {
return false
    },
    onReset (evt) {
      evt.preventDefault();
    },
    setAlmacenOrigen (obj) {
      this.almacen = obj
      /*this.obtenerStok({
        IdAlmacen: !this.isTraslado?(this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1): (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
      })*/
    },
    guardarMovimiento (evt) {
      evt.preventDefault();
      this.inProgress = true

      if(!this.productos.length) {
          this.$toasted.show('No ha seleccionado ningun producto')
          this.inProgress = false            
          return          
      }

      let faltaCantidad = false
      this.productos.forEach((prod, index) => {

        if(!(prod.cantidad > 0)){
          faltaCantidad = true
        }
      })

      if (faltaCantidad) {
        this.$toasted.show('Complete la cantidad de los productos ingresados')
        this.inProgress = false
        return;
      }

      this.axios.post(this.API + 'movimientos', {
        productos: this.productos
      })
      .then((response) => {
        if (response.data.error) {
          this.$toasted.show(response.data.error)
        } else {
          this.limpiar()
          if(this.$refs.inputAlmacenOrigen){
            this.$refs.inputAlmacenOrigen.setValue('')
          }
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
    CarritoConfrontacion
  }
}
</script>
<style>
  .input-group {
    /*margin: 0.25rem 0 !important;*/
  }
</style>