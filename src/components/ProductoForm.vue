<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-modal :no-close-on-esc="true" id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Categoría:</label>
            <b-input-group>
              <autocomplete
                :url="API + 'categorias'"
                ref="inputCategoria"
                anchor="ProductoCategoria"
                label="writer"
                :placeholder="producto.categoria.ProductoCategoria"
                :on-select="setCategoria"
                :onFocus="focusAutocomplete.bind(null, 'inputCategoria')"
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearCategoria"><b>Guardar</b></b-btn>
                <!--<categoria-form :showButton="true" :cbGuardar="setCategoria"></categoria-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Artículo:</label>
            <b-form-input v-model="producto.Producto"
              type="text"
              @input="(val) => producto.Producto = val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>

          <b-form-group>
            <label>Marca:</label>
            <b-input-group>
              <autocomplete
                :url="API + 'marcas'"
                ref="inputMarca"
                anchor="ProductoMarca"
                label="writer"
                :placeholder.sync="producto.marca.ProductoMarca"
                :on-select="setMarca"
                :onFocus="focusAutocomplete.bind(null, 'inputMarca')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearMarca"><b>Guardar</b></b-btn>                
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Modelo:</label>
            <b-form-input v-model="producto.ProductoModelo"
              type="text"
              @input="(val) => producto.ProductoModelo = val.toUpperCase()"              
              ></b-form-input>
            <!-- <b-input-group>
              <autocomplete
                :url="API + 'modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                label="writer"
                :placeholder.sync="producto.modelo.ProductoModelo"
                :on-select="setModelo"
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"                
                :classes="{input: 'form-control'}">
              </autocomplete>
              <b-input-group-append>
                <modelo-form :showButton="true" :cbGuardar="setModelo"></modelo-form>
              </b-input-group-append>
            </b-input-group> -->
          </b-form-group>
          <b-form-group>
            <label>Colores:</label>
            <b-form-input v-model="producto.Color"
              type="text"
              @input="(val) => producto.Color = val.toUpperCase()"              
              ></b-form-input>
          </b-form-group>
          
          
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Medición:</label>
            <autocomplete
              :url="API + 'mediciones'"
              ref="inputMedicion"
              initValue="UNIDAD"
              anchor="ProductoMedicion"
              label="writer"
              :placeholder.sync="producto.medicion.ProductoMedicion"
              :on-select="setMedicion"              
              :classes="{input: 'form-control'}">
            </autocomplete>
          </b-form-group>
          
          <b-form-group>
            <label>Talla:</label>
            <b-input-group>
              <autocomplete
                :url="API + 'tallas'"
                ref="inputTalla"
                anchor="ProductoTalla"
                label="writer"
                :placeholder.sync="producto.talla.ProductoTalla"
                :on-select="setTalla"
                :onFocus="focusAutocomplete.bind(null, 'inputTalla')"                    
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearTalla"><b>Guardar</b></b-btn>                
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Genero:</label>            
            <b-form-select v-model="producto.Genero" :options="generos" />
          </b-form-group>
          <b-form-group>
            <label>Botapie:</label>
            <b-form-input v-model="producto.Botapie"
              type="text"
              @input="(val) => producto.Botapie = val.toUpperCase()"              
              ></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Porcentaje Utilidad:</label>
            <b-form-input v-model="producto.PorcentajeUtilidad"
              type="number"
              ></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <b-btn  variant="warning" @click="clearForm" v-if="!producto.IdProducto">
          Limpiar
        </b-btn>
        <b-btn  variant="primary" @click="guardarProducto">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Close
         </b-btn>
       </div>
    </b-modal>
  </div>
</template>
<script>
import config from '@/store/config.js'
import CategoriaForm from '@/components/CategoriaForm'
import MarcaForm from '@/components/MarcaForm'
// import ModeloForm from '@/components/ModeloForm'

export default {
  data () {
    return {
      title: 'Nuevo Producto',
      API: config.API,
      show: false, 
      generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      producto: {}
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'productoUpdate', 'cbGuardar'],
  watch: {
    productoUpdate (newVal) {
      this.title = 'Editar Producto'
      this.producto.categoria = {
        IdProductoCategoria: newVal.IdProductoCategoria,
        ProductoCategoria: newVal.ProductoCategoria
      }
      this.producto.marca = {
        IdProductoMarca: newVal.IdProductoMarca,
        ProductoMarca: newVal.ProductoMarca
      }
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputMarca.setValue('')
      this.$refs.inputMedicion.setValue('')
      // this.$refs.inputModelo.setValue('')
      this.$refs.inputTalla.setValue('')
      this.producto.medicion = {
        IdProductoMedicion: newVal.IdProductoMedicion,
        ProductoMedicion: newVal.ProductoMedicion
      }
      /* this.producto.modelo = {
        IdProductoModelo: newVal.IdProductoModelo,
        ProductoModelo: newVal.ProductoModelo
      } */
      this.producto.talla = {
        IdProductoTalla: newVal.IdProductoTalla,
        ProductoTalla: newVal.ProductoTalla
      }
      this.producto.Producto = newVal.Producto
      this.producto.IdProducto = newVal.IdProducto
      this.producto.Genero = newVal.Genero
      this.producto.Botapie = newVal.Botapie
      this.producto.Color = newVal.Color
      this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad
      this.producto.CodigoBarra = newVal.CodigoBarra
      this.producto.ProductoModelo = newVal.ProductoModelo
    }
  },
  methods: {
    setCategoria (obj) {
      this.producto.categoria = obj
      this.$refs.inputCategoria.setValue(obj.ProductoCategoria)
    },
    setMedicion (obj) {
      this.producto.medicion = obj
    },
    setMarca (obj) {
      this.producto.marca = obj
      this.$refs.inputMarca.setValue(obj.ProductoMarca)
    },
    /* setModelo (obj) {
      this.producto.modelo = obj
      this.$refs.inputModelo.setValue(obj.ProductoModelo)
    }, */
    setTalla (obj) {
      this.producto.talla = obj
      this.$refs.inputTalla.setValue(obj.ProductoTalla)
    },
    clearForm () {
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputMarca.setValue('')
      this.$refs.inputMedicion.setValue('')
      this.$refs.inputTalla.setValue('')

      this.initForm()
    },
    initForm () {
      this.producto = {
        Anulado: 0,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        // IdProductoModelo: null,
        IdProductoBotapie: null,
        IdProductoTalla: '',
        Producto: '',
        CodigoBarra: '',
        Color: '',
        PorcentajeUtilidad: '',
        Botapie: '',
        Genero: '',
        categoria: {},
        marca: {},
        // modelo: {},
        talla: {},
        medicion: {
          IdProductoMedicion:6,
          ProductoMedicion:"UNIDAD"
        }
      }
    },
    guardarProducto() {
      this.axios.post(this.API + 'productos', this.producto)
      .then((response) => {
        this.$toasted.show('Producto guardado correctamente')
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId)
        }

        // this.initForm()
        this.producto.talla = {}
        this.$refs.inputTalla.setValue('')

        this.$refs.productoForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    crearCategoria () {
      if (this.$refs.inputCategoria.type) {
        let categoria = {}
        categoria.ProductoCategoria = this.$refs.inputCategoria.type.toUpperCase()

        this.axios.post(this.API + 'categorias', categoria)
        .then((response) => {
          this.$toasted.show('Categoría guardada correctamente')
          this.setCategoria({
            IdProductoCategoria: response.data.insertId,
            ProductoCategoria: response.data.ProductoCategoria
          })
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
      }
      
    },
    crearMarca () {
      if (this.$refs.inputMarca.type) {
        let marca = {}
        marca.ProductoMarca = this.$refs.inputMarca.type.toUpperCase()
  
        this.axios.post(this.API + 'marcas', marca)
        .then((response) => {
          this.$toasted.show('Marca guardada correctamente')
          this.setMarca({
            IdProductoMarca: response.data.insertId,
            ProductoMarca: response.data.ProductoMarca
          })
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
      }
    },
    crearTalla () {
      if (this.$refs.inputTalla.type) {
        let talla = {}
        talla.ProductoTalla = this.$refs.inputTalla.type.toUpperCase()
  
        this.axios.post(this.API + 'tallas', talla)
        .then((response) => {
          this.$toasted.show('Talla guardada correctamente')
          this.setTalla({
            IdProductoTalla: response.data.insertId,
            ProductoTalla: response.data.ProductoTalla
          })
        })
        .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
      }
    }
  },
  computed : {
  },
  components: {
    CategoriaForm, MarcaForm
  }
}
</script>
<style>

</style>
