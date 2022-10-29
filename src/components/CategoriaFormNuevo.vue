<template>
  <div>
    <!-- Modal Component -->
    <b-form @submit="guardarCategoria" @keydown.enter="keyDownInput" autocomplete="off" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="md" ref="productoForm" v-model="show">
        <b-col md="12">
          <b-form-group>
            <label>Categoria:</label>
            <b-form-input
              v-model="categoria.ProductoCategoria"
              @input="(val) => categoria.ProductoCategoria = val && val.toUpperCase()"
              type="text"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="12" style="margin-top:10px">
          <b-form-group>
            <label>% Desc. MAX:</label>
            <b-form-input
              v-model="categoria.PorcentajeDescMax"
              type="number"
              value="0"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>
        <div slot="modal-footer">
        <b-btn variant="primary" type="submit">
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
// import ModeloForm from '@/components/ModeloForm'
export default {
  data () {
    return {
      title: 'Nueva Categoria',
      API: config.API,
      show: false, 
      categoria: {},
      nuevo: false,
    }
  },
  props: ['categoriaUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.categoriaUpdate  || {}
      if (newVal.IdProductoCategoria && !this.nuevo) {
        this.initForm()
        this.title='Editar Categoria << '+newVal.IdProductoCategoria+' >>'
        this.categoria.ProductoCategoria=newVal.ProductoCategoria
        this.categoria.IdProductoCategoria = newVal.IdProductoCategoria
      } else {
        this.title='Nueva Categoria'
        this.initForm();
      }
    }
  },
  methods: {
    initForm () {
      this.categoria = {
          ProductoCategoria: '',
          IdProductoCategoria: null,
          PorcentajeDescMax: 0
      }
    },
    guardarCategoria(e) {
      e.preventDefault();
      this.axios.post(this.API + 'categorias/nuevo', {
        ...this.categoria, 
      })
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId)
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.codigoBarra)
          return
        } else {
          this.$toasted.info('Categoria guardada correctamente')
        }
        this.$refs.productoForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error o la categoria ya existe, vuelva a intentarlo')
      })
    },
  },
  computed : {
  },
  components: {
  }
}
</script>
<style>
</style>