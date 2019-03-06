<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter="enterSubmit" >
    <b-modal :no-close-on-esc="true" id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Código barra:</label>
            <b-form-input 
              ref="inputCodigoBarra"
              required="required"
              v-model="producto.CodigoBarra"
              @input="(val) => producto.CodigoBarra = val && val.toUpperCase()"
              type="text"
              class="form-control upper"
              ></b-form-input>
          </b-form-group>
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
            <label>Artículo:</label>
            <b-form-input v-model="producto.Producto"
              ref="inputProducto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Descripción:</label>
            <b-form-textarea 
              ref="inputProductoDesc"
              rows="6"
              v-model="producto._ProductoDesc"
              @input="(val) => producto.ProductoDesc = val"
              />
          </b-form-group>

          
          
        </b-col>
        <b-col md="6">

          <b-form-group>
            <label>N° Chasis:</label>
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                label="writer"
                :placeholder.sync="producto.modelo.ProductoModelo"
                :on-select="setModelo"
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"                
                :classes="{input: 'form-control upper'}">
              </autocomplete>
              <b-input-group-append>
                <b-btn variant="primary" @click="crearModelo"><b>Guardar</b></b-btn>                
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          
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
              :onFocus="focusAutocomplete.bind(null, 'inputMedicion')" 
              :classes="{input: 'form-control'}">
            </autocomplete>
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
          <b-form-group v-if="!producto.EsPadre">
            <label>Stock Minimo:</label>
            <b-form-input   
              required="required"
              v-model="producto.StockMinimo"
              type="number"
              ></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Activar SubProductos:</label>
            <b-form-checkbox
                     v-model="producto.EsPadre"
                     :value="1"
                     :unchecked-value="0"
                     @input="checkIsHijo">
              Seleccione esta opción si el producto contiene mas productos
            </b-form-checkbox>
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

      <hr />
      <b-row>
        <b-col>
          <producto-form-det :id-producto="producto.IdProducto" v-if="showProductoDet && show" ref="productoDet"></producto-form-det>
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
        <b-btn  variant="danger" @click="eliminarProducto(producto)" v-if="producto.IdProducto">
          Eliminar
        </b-btn>
        <b-btn  variant="warning" @click="clearForm" v-if="!producto.IdProducto">
          Limpiar
        </b-btn>
        <b-btn  variant="primary" type="submit">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Cerrar
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
import ProductoFormDet from '@/components/ProductoFormDet'
// import ModeloForm from '@/components/ModeloForm'

export default {
  data () {
    return {
      showProductoDet: true,
      cantidadCodigoBarra: 1,
      title: 'Nuevo Producto',
      API: config.API,
      show: false, 
      // generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      producto: {},
      productosDet: []
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'productoUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.productoUpdate  || {}
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

        this.$refs.inputProductoDesc.$el.value = newVal.ProductoDesc

        // this.$refs.inputTalla.setValue(newVal.ProductoTalla)
        // this.$refs.inputProducto.$el.disabled = true
        this.$refs.inputCodigoBarra.$el.disabled = true

        // this.$refs.inputColor.setValue(newVal.Color)
        // this.$refs.inputBotapie.setValue(newVal.Botapie)
        
        this.producto.modelo = {
          IdProductoModelo: newVal.IdProductoModelo,
          ProductoModelo: newVal.ProductoModelo
        }
        /*this.producto.talla = {
          IdProductoTalla: newVal.IdProductoTalla,
          ProductoTalla: newVal.ProductoTalla
        }*/
        //this.producto.Producto = newVal.Producto.split('-').slice(0, -1).join()
        this.producto.Producto = newVal.Producto
        this.producto.IdProducto = newVal.IdProducto


        this.producto.EsPadre = newVal.EsPadre
        Vue.nextTick(() => {
           if (this.$refs.productoDet) this.$refs.productoDet.show = parseInt(newVal.EsPadre)
        })
        // this.producto.Genero = newVal.Genero
        // this.producto.Botapie = newVal.Botapie
        // this.producto.Color = newVal.Color
        this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad
        this.producto.StockMinimo = newVal.StockMinimo
        this.producto.CodigoBarra = newVal.CodigoBarra
        this.producto.ProductoModelo = newVal.ProductoModelo
        this.producto.PrecioCosto = newVal.PrecioCosto
        this.producto.PrecioContado = newVal.PrecioContado

      } else {

        this.title = 'Nuevo Producto'
        this.clearForm()

        this.$refs.inputCodigoBarra.$el.disabled = false
      }
    }
  },
  methods: {
    enterSubmit(e) {
      if(e.target.tagName.toLowerCase() !== 'textarea') {
        e.preventDefault()
      }
    },
    eliminarProducto (producto) {
      // NO eliminar porque no distinque movimientos ni ventas
      let r = confirm('¿Esta seguro de eliminar este producto?')
      if (r) {
        this.axios.post(this.API + 'productos/delete', {
          id: producto.IdProducto
        }).then(res => {
          if (res.data.updated) {
            this.$toasted.success('El producto con código ' + res.data.IdProducto + ' ha sido anulado')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.productoForm.hide()
          }
        })
      }
    },
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
    setModelo (obj) {
      this.producto.modelo = obj
      this.$refs.inputModelo.setValue(obj.ProductoModelo)
    },
    /*setTalla (obj) {
      this.producto.talla = obj
      this.$refs.inputTalla.setValue(obj.ProductoTalla)
    },*/
    clearForm () {
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputMarca.setValue('')
      this.$refs.inputMedicion.setValue('UNIDAD')
      // this.$refs.inputTalla.setValue('')
      //this.$refs.inputModelo.setValue('')
      // this.$refs.inputColor.setValue('')
      // this.$refs.inputBotapie.setValue('')

      this.initForm()
    },
    initForm () {
      this.producto = {
        EsPadre: 0,
        Anulado: 0,
        IdProductoMarca: null,
        IdProductoMedicion: null,
        IdProductoCategoria: null,
        IdProductoModelo: null,
        // IdProductoBotapie: null,
        // IdProductoTalla: '',
        Producto: '',
        CodigoBarra: '',
        // Color: '',
        PorcentajeUtilidad: '',
        StockMinimo: 0,
        PrecioContado: '',
        // Botapie: '',
        // Genero: '',
        categoria: {},
        marca: {},
        modelo: {},
        //talla: {},
        medicion: {
          IdProductoMedicion:6,
          ProductoMedicion:"UNIDAD"
        }
      }
    },
    guardarProducto(e) {
      e.preventDefault();

      // Agregar producto DET
      if (this.showProductoDet) {
        this.producto.productosDet = this.$refs.productoDet.productos
      }
        // console.log(JSON.stringify(this.producto))

      this.axios.post(this.API + 'productos', this.producto)
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId)
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.codigoBarra)
          return
        } else {
          this.$toasted.show('Producto guardado correctamente')
        }

        // this.initForm()
        // this.producto.talla = {}
        // this.$refs.inputTalla.setValue('')

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
    crearModelo () {
      if (this.$refs.inputModelo.type) {
        let modelo = {}
        modelo.ProductoModelo = this.$refs.inputModelo.type.toUpperCase()
  
        this.axios.post(this.API + 'modelos', modelo)
        .then((response) => {
          this.$toasted.show('Modelo guardado correctamente')
          this.setModelo({
            IdProductoModelo: response.data.insertId,
            ProductoModelo: response.data.ProductoModelo
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
    },
    checkIsHijo (val) {
      if (val && this.producto.IdProducto) {
        this.axios.get(this.API + 'productos/eshijo', {params: {
          idProducto: this.producto.IdProducto
        }})
        .then(res => {
          let esHijo = res.data.esHijo
          if(esHijo) {
            this.$toasted.show('Este producto no puede contener productos ya que es un producto hijo')
            this.producto.EsPadre = false      
          }else {
            this.$refs.productoDet.show = parseInt(this.producto.EsPadre)
          }
        })
      } else {
        if (this.$refs.productoDet) this.$refs.productoDet.show = parseInt(this.producto.EsPadre)
      }
    }
  },
  computed : {
  },
  components: {
    CategoriaForm, MarcaForm, ProductoFormDet
  }
}
</script>
<style>

</style>