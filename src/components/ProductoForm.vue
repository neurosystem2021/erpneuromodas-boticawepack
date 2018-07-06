<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter.prevent="()=>{}" >
    <b-modal :no-close-on-esc="true" id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Categoría:</label>
            <b-input-group>
              <autocomplete
                required="required"
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
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>

          <b-form-group>
            <label>Marca:</label>
            <b-input-group>
              <autocomplete
                required="required"
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
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'productos/modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                label="writer"
                :placeholder.sync="producto.ProductoModelo"
                :on-select="val => producto.ProductoModelo = val.ProductoModelo && val.ProductoModelo.toUpperCase()"
                :onInput="val => producto.ProductoModelo = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            </b-input-group>
            <!--<b-form-input v-model="producto.ProductoModelo"
              required="required"
              class="upper"
              type="text"
              @input="(val) => producto.ProductoModelo = val && val.toUpperCase()"              
              ></b-form-input>-->
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
            <autocomplete
                required="required"
                :url="API + 'productos/colores'"
                ref="inputColor"
                anchor="Color"
                label="writer"
                :placeholder.sync="producto.Color"
                :on-select="val => producto.Color = val.Color && val.Color.toUpperCase()"
                :onInput="val => producto.Color = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputColor')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            <!--<b-form-input v-model="producto.Color"
              type="text"
              @input="(val) => producto.Color = val && val.toUpperCase()"              
              ></b-form-input>-->
          </b-form-group>
          
          
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Medición:</label>
            <autocomplete
              required="required"
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
                required="required"
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
            <b-form-select required="required" v-model="producto.Genero" :options="generos" />
          </b-form-group>
          <b-form-group>
            <label>Botapie/Manga:</label>
            <autocomplete
                required="required"
                :url="API + 'productos/botapies'"
                ref="inputBotapie"
                anchor="Botapie"
                label="writer"
                :placeholder.sync="producto.Botapie"
                :on-select="val => producto.Botapie = val.Botapie && val.Botapie.toUpperCase()"
                :onInput="val => producto.Botapie = val && val.toUpperCase()"
                :onFocus="focusAutocomplete.bind(null, 'inputBotapie')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            <!--<b-form-input v-model="producto.Botapie"
              type="text"
              @input="(val) => producto.Botapie = val && val.toUpperCase()"              
              ></b-form-input>-->
          </b-form-group>
          <b-form-group>
            <label>Porcentaje Utilidad:</label>
            <b-form-input 
              required="required"
              v-model="producto.PorcentajeUtilidad"
              @input.native="actualizarPrecioContado"
              type="number"
              ></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>

      <hr v-if="producto.IdProducto" />
      <b-row v-if="producto.IdProducto">
        <b-col md="6">
          <b-form-group>
            <label>Precio de Costo:</label>
            <b-form-input
              v-model="producto.PrecioCosto"
              readonly="readonly"
              type="number"
              ></b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio de Venta:</label>
            <b-form-input v-model="producto.PrecioContado"
              type="number"
              ></b-form-input>
              <b-form-text v-if="producto.precioContadoNormal">
                Original - {{ producto.precioContadoNormal | redondear }}
              </b-form-text>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <div style="left: 1rem;position: absolute;" v-if="producto.IdProducto">
          <b-input-group>
            <b-form-input
              v-model="cantidadCodigoBarra"
              type="number"
              style="width:80px;"
            ></b-form-input>
            <b-input-group-append>
              <b-btn variant="info" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + producto.IdProducto + '&cantidad=' + cantidadCodigoBarra ">Código de barra</b-btn>
            </b-input-group-append>
          </b-input-group>
        </div>
        <b-btn  variant="warning" @click="clearForm" v-if="!producto.IdProducto">
          Limpiar
        </b-btn>
        <b-btn  variant="primary" type="submit">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Close
         </b-btn>
       </div>
    </b-modal>
    </b-form>
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
      cantidadCodigoBarra: 1,
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
      console.log(newVal)
      if (newVal.IdProducto) {
        this.title = 'Editar Producto << ' + newVal.CodigoBarra + ' >>'
        this.producto.categoria = {
          IdProductoCategoria: newVal.IdProductoCategoria,
          ProductoCategoria: newVal.ProductoCategoria
        }
        this.producto.marca = {
          IdProductoMarca: newVal.IdProductoMarca,
          ProductoMarca: newVal.ProductoMarca
        }
        this.producto.medicion = {
          IdProductoMedicion: newVal.IdProductoMedicion,
          ProductoMedicion: newVal.ProductoMedicion
        }
       
       // this.$refs.inputCategoria.setValue('')
        this.$refs.inputCategoria.setValue(newVal.ProductoCategoria)
       
       // this.$refs.inputMarca.setValue('')
        this.$refs.inputMarca.setValue(newVal.ProductoMarca)

       // this.$refs.inputMedicion.setValue('')
        this.$refs.inputMedicion.setValue(newVal.ProductoMedicion)
        
        // this.$refs.inputModelo.setValue('')
        this.$refs.inputModelo.setValue(newVal.ProductoModelo)

        this.$refs.inputTalla.setValue(newVal.ProductoTalla)
        
        this.$refs.inputColor.setValue(newVal.Color)
        this.$refs.inputBotapie.setValue(newVal.Botapie)
        
        /* this.producto.modelo = {
          IdProductoModelo: newVal.IdProductoModelo,
          ProductoModelo: newVal.ProductoModelo
        } */
        this.producto.talla = {
          IdProductoTalla: newVal.IdProductoTalla,
          ProductoTalla: newVal.ProductoTalla
        }
        this.producto.Producto = newVal.Producto.split('-').slice(0, -1).join()
        this.producto.IdProducto = newVal.IdProducto
        this.producto.Genero = newVal.Genero
        this.producto.Botapie = newVal.Botapie
        this.producto.Color = newVal.Color
        this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad
        this.producto.CodigoBarra = newVal.CodigoBarra
        this.producto.ProductoModelo = newVal.ProductoModelo
        this.producto.PrecioCosto = newVal.PrecioCosto
        this.producto.PrecioContado = newVal.PrecioContado

      } else {

        this.title = 'Nuevo Producto'
        this.clearForm()
      }
    }
  },
  methods: {
    actualizarPrecioContado () {
      var precioCosto = this.producto.PrecioCosto
      var utilidad = this.producto.PorcentajeUtilidad
      var nuevoPrecioContado = precioCosto + (precioCosto * (utilidad / 100))

      this.producto.precioContadoNormal = nuevoPrecioContado
      this.producto.PrecioContado = this.$options.filters.redondeoMayor(nuevoPrecioContado)

    },
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
      this.$refs.inputMedicion.setValue('UNIDAD')
      this.$refs.inputTalla.setValue('')
      this.$refs.inputModelo.setValue('')
      this.$refs.inputColor.setValue('')
      this.$refs.inputBotapie.setValue('')

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
        PrecioContado: '',
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
    guardarProducto(e) {
      e.preventDefault();

      this.axios.post(this.API + 'productos', this.producto)
      .then((response) => {
        //console.log(response);exit();
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId)
        }
        if (response.data.error) {
          this.$toasted.show('Producto insertado correctamente - ' + response.data.codigoBarra)
        } else {
          this.$toasted.show('Producto guardado correctamente')
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
      // this.$refs[ref].type = ""
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
