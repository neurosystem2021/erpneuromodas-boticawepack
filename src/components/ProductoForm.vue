<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter.prevent="()=>{}" >
    <b-modal :no-close-on-esc="true" :no-close-on-backdrop="true" id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Código barra:</label>
            <b-form-input 
              ref="inputCodigoBarra"
              v-model="producto.CodigoBarra"
              @input="(val) => producto.CodigoBarra = val && val.toUpperCase()"
              type="text"
              class="form-control upper"
              ></b-form-input>
              <b-form-text>Dejar en blanco para generar el codigo de barra</b-form-text>
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
            <label>Código Producto Sunat:</label>
            <b-input-group>
              <autocomplete
                :url="API + 'codigoproductosunat'"
                ref="inputCodigoSunat"
                anchor="busqueda_label"
                label="writer"
                :placeholder.sync="producto.codigoSunat.busqueda_label"
                :on-select="setCodigoSunat"
                :onFocus="focusAutocomplete.bind(null, 'inputCodigoSunat')"
                :classes="{input: 'form-control upper'}">
              </autocomplete>
            </b-input-group>
          </b-form-group>
          
          <b-form-group>
            <label>Producto:</label>
            <b-form-input v-model="producto.Producto"
              ref="inputProducto"
              required="required"
              type="text"
              @input="(val) => producto.Producto = val && val.toUpperCase()"
              :value="producto.Producto"
              ></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Imagen:</label>
            <b-img :src="'/resources/images/productos/' + producto.Imagen" fluid alt=""></b-img>
            <input type="file" id="file" ref="file" @change="handleFileUpload()"/>
            <small>Nota: La imagen debe ser en formato .png y de 300x300</small>
          </b-form-group>

          
          
        </b-col>
        <b-col md="6">

          <!--<b-form-group>
            <label>Modelo:</label>
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
                <b-btn variant="primary" @click="crearModelo"><b>Guardar</b></b-btn>         -->       
                <!--<marca-form :showButton="true" :cbGuardar="setMarca"></marca-form>-->
              <!--</b-input-group-append>
            </b-input-group>
          </b-form-group>-->
          
          <b-form-group>
            <label>Medición:</label>
            <autocomplete
              required="required"
              :url="API + 'mediciones'"
              ref="inputMedicion"
              initValue="GALONES"
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
            <input 
              required="required"
              v-model="producto.PorcentajeUtilidad"
              @input="actualizarPrecioContado"
              type="number"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
          <b-form-group v-if="!producto.EsPadre">
            <label>Stock Minimo:</label>
            <input   
              required="required"
              class="form-control"
              v-model="producto.StockMinimo"
              type="number"
              step="0.0001"
              />
          </b-form-group>
          <b-form-group>
            <b-form-checkbox
                   v-model="producto.MasMovido"
                   :value="1"
                   :unchecked-value="0">
              Producto con más movimiento
            </b-form-checkbox>
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

      <hr/>
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Precio de Costo:</label>
            <input
              v-model="producto.PrecioCosto"
              readonly="readonly"
              type="number"
              step="0.0001"
              class="form-control"
              />
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Precio de Venta:</label>
            <input v-model="producto.PrecioContado"
              type="number"
              class="form-control"
              step="0.001"
              />
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
        // this.$refs.inputModelo.setValue(newVal.ProductoModelo)

        // this.$refs.inputTalla.setValue(newVal.ProductoTalla)
        // this.$refs.inputProducto.$el.disabled = true
        this.$refs.inputCodigoBarra.$el.disabled = true

        // this.$refs.inputColor.setValue(newVal.Color)
        // this.$refs.inputBotapie.setValue(newVal.Botapie)
        
        /* this.producto.modelo = {
          IdProductoModelo: newVal.IdProductoModelo,
          ProductoModelo: newVal.ProductoModelo
        }*/
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
        this.producto.CodigoProductoSunat = newVal.CodigoSunat || '00000000'
        this.producto.codigoSunat = {
          codigo: newVal.CodigoSunat || '00000000',
          busqueda_label: newVal.CodigoSunatDescripcion
            ? (newVal.CodigoSunat + ' - ' + newVal.CodigoSunatDescripcion)
            : '00000000 - NO REGISTRADO / COMODÍN GENÉRICO'
        }
        
        if (this.$refs.inputCodigoSunat) {
          this.$refs.inputCodigoSunat.setValue(this.producto.codigoSunat.busqueda_label)
        }
        this.producto.PorcentajeUtilidad = newVal.PorcentajeUtilidad
        this.producto.StockMinimo = newVal.StockMinimo
        this.producto.CodigoBarra = newVal.CodigoBarra
        this.producto.Imagen = newVal.Imagen
        // this.producto.ProductoModelo = newVal.ProductoModelo
        this.producto.PrecioCosto = newVal.PrecioCosto
        this.producto.PrecioContado = newVal.PrecioContado
        this.producto.MasMovido = newVal.MasMovido
        this.$refs.file.value=null
      } else {

        this.title = 'Nuevo Producto'
        this.clearForm()
        this.$refs.file.value=null
        this.$refs.inputCodigoBarra.$el.disabled = false
      }
    }
  },
  methods: {
    handleFileUpload() {
      this.file = this.$refs.file.files[0]
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
      this.$refs.inputMedicion.setValue('GALONES')
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
        PorcentajeUtilidad: 0,
        StockMinimo: 0,
        PrecioContado: '',
        MasMovido: 0,
        PrecioContado: 0,
        // Botapie: '',
        // Genero: '',
        categoria: {},
        Imagen: null,
        marca: {},
        modelo: {},
        CodigoProductoSunat: '00000000',
        codigoSunat: {
          codigo: '00000000',
          busqueda_label: '00000000 - NO REGISTRADO / COMODÍN GENÉRICO'
        },
        //talla: {},
        medicion: {
          IdProductoMedicion:6,
          ProductoMedicion:"GALONES"
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
      this.producto.CodigoBarra=(''+this.producto.CodigoBarra).trim();
      this.axios.post(this.API + 'productos', this.producto)
      .then((response) => {
        console.log(response)
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
                //INSERTANDO IMAGEN
        let formData = new FormData();
        formData.append('imagen', this.file);
        this.axios({
            method    : 'post',
            url       : this.API + 'producto/imagen/' + response.data.insertId,
            data      : formData,
            headers   : { 'Content-Type': 'multipart/form-data' }
        })
        .then((res) => {
          // console.log(res);
        })

        this.$refs.productoForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    focusAutocomplete(refName) {
      this.$nextTick(() => {
        const component = this.$refs[refName]
        if (!component) return

        // 1. Seleccionar todo el texto del input
        const inputEl = component.$el ? component.$el.querySelector('input') : null
        if (inputEl) {
          inputEl.select()
        }

        // 2. Restaurar el comportamiento original: forzar apertura de la lista
        if (typeof component.handleDoubleClick === 'function') {
          component.handleDoubleClick()
        }
      })
    },

    setCodigoSunat(obj) {
      this.producto.codigoSunat = obj;
      this.producto.CodigoProductoSunat = obj.CodigoSunat;
      if (this.$refs.inputCodigoSunat) {
        this.$refs.inputCodigoSunat.setValue(obj.busqueda_label);
      }
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