<template>
  <div>
    <b-btn v-if="showButton" @click="()=>nuevo=true" v-b-modal.modalProducto variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarProducto" @keydown.enter="keyDownInput" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="ProductoForm2" v-model="show">
      <b-row>
        <b-col md="6">
         <b-form-group>
            <b-form-checkbox
                   v-model="EsAnalisis"
                   :value="1"
                   :unchecked-value="0">
              Es Analisis
            </b-form-checkbox>
          </b-form-group>
         <b-form-group>
            <label>Formato:</label>
            <input type="file" id="file" ref="file" @change="handleFileUpload()"/>
          </b-form-group>
        </b-col>
      </b-row>
 
      <div slot="modal-footer" >
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
import ProductoFormDet from '@/components/ProductoFormDet'
// import ModeloForm from '@/components/ModeloForm'
export default {
  data () {
    return {
      showProductoDet: true,
      cantidadCodigoBarra: 1,
      title: 'Producto',
      API: config.API,
      show: false, 
      producto: {},
      EsAnalisis: 0,
      file: '',
      idPerfil: sessionStorage.getItem('IdPerfil')
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'productoUpdate', 'cbGuardar'],
  watch: {
    show (val) {
        let newVal = this.productoUpdate  || {}
        this.axios.get(this.API + 'productos/id/'+newVal.IdProducto
        ).then((response) => {
        newVal = response.data 
        this.initForm()
        this.title = 'Editar Producto << ' + newVal.CodigoBarra + ' >>'
        this.EsAnalisis = newVal.EsAnalisis
        }).catch((error) => {
            console.log(error)
            this.initForm()
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          })
    }
  },
  methods: {
    handleFileUpload() {
      this.file = this.$refs.file.files[0]
    },
    clearForm () {
      this.initForm()
    },
    initForm () {
      this.producto = {
        Formato: null,
      }
    },
    keyDownInput(e) {
      if (e.target.tagName != 'TEXTAREA') {  
        e.preventDefault();
      }
    },
    guardarProducto(e) {
      e.preventDefault();

      let formData = new FormData();
      formData.append('formato', this.file);
      formData.append('esanalisis', this.EsAnalisis);
      this.axios({
          method    : 'post',
          url       : this.API + 'producto/formato/' + this.productoUpdate.IdProducto,
          data      : formData,
          headers   : { 'Content-Type': 'multipart/form-data' }
        })
        .then((res) => {  
          // console.log(res);
          this.$toasted.success('Producto guardado correctamente')
          this.$refs.ProductoForm2.hide()
          this.cbGuardar()
          this.$refs.file.value=null
        })
     
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },

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