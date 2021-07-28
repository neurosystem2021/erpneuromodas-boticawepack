<template>
  <div>
    <!-- Modal Component -->
    <b-form @submit="guardarImpresora" @keydown.enter="keyDownInput" autocomplete="off">
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
        <b-col md="6">
          <b-form-group>
            <label>Nombre:</label>
            <b-form-input
              v-model="impresora.NombreImpresora"
              @input="(val) => impresora.NombreImpresora = val && val.toUpperCase()"
              type="text"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Direccion IP:</label>
            <b-form-input
              v-model="impresora.Ip"
              type="text"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="6">
          <b-form-group>
            <label>Puerto:</label>
            <b-form-input
              v-model="impresora.Puerto"
              type="number"
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
      title: 'Nueva Impresora',
      API: config.API,
      show: false, 
      impresora: {},
      nuevo: false,
    }
  },
  props: ['impresoraUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.impresoraUpdate  || {}
      if (newVal.IdImpresora && !this.nuevo) {
        this.initForm()
        this.title='Editar Impresora << '+newVal.IdImpresora+' >>'
        this.impresora.IdImpresora = newVal.IdImpresora
        this.impresora.NombreImpresora=newVal.NombreImpresora
        this.impresora.Ip = newVal.Ip
        this.impresora.Puerto = newVal.Puerto
      } else {
        this.title='Nueva Impresora'
        this.initForm();
      }
    }
  },
  methods: {
    initForm () {
      this.impresora = {
          NombreImpresora:'',
          Ip:'',
          Puerto:0
      }
    },
    guardarImpresora(e) {
      e.preventDefault();

      this.axios.post(this.API + 'impresoras', {
        ...this.impresora, 
      })
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId)
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.codigoBarra)
          return
        } else {
          this.$toasted.show('Impresora guardada correctamente')
        }

        this.$refs.productoForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
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