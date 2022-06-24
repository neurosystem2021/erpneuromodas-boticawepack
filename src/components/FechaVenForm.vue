<template>
  <div>
    <!-- Modal Component -->
    <b-form @submit="guardarFechaVen" @keydown.enter="keyDownInput" autocomplete="off">
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
        <b-col md="6">
          <b-form-group>
            <label>Fecha Vencimiento:</label>
            <b-form-input
              v-model="producto.FechaVen"
              type="date"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group>
            <label>Lote:</label>
            <b-form-input
              v-model="producto.IdLote"
              type="text"
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
      producto: {},
      nuevo: false,
    }
  },
  props: ['fechavenUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.fechavenUpdate  || {}
      if (newVal.IdProducto && !this.nuevo) {
        this.initForm()
        this.title='Editar Fecha Vencimiento << '+newVal.Producto+' >>'
        this.producto.IdProducto = newVal.IdProducto
        this.producto.FechaVen = newVal.FechaVen
        this.producto.IdLote = newVal.IdLote
        this.producto.IdAlmacen = newVal.IdAlmacen
        console.log(newVal.IdAlmacen)
      } else {
        this.title='Nueva Impresora'
        this.initForm();
      }
    }
  },
  methods: {
    initForm () {
      this.producto = {
          IdProducto:'',
          FechaVen:'',
          IdAlmacen:0,
          IdLote:''
      }
    },
    guardarFechaVen(e) {
        e.preventDefault();
        if (this.producto.FechaVen == undefined || this.producto.FechaVen=='' || this.producto.FechaVen.length>10 ) {
          if(this.producto.FechaVen.length>10){
            this.$toasted.show('La fecha de vencimiento no tiene formato correcto')
            return
          }
          this.$toasted.show('Ingrese fecha de vencimiento')
          return
        }
        this.producto.IdLote = (''+this.producto.IdLote).trim();
        this.axios.post(this.API + 'actualizar/fechaven', {
            ...this.producto, 
        })
        .then((response) => {
            if (response.data.error) {
            this.$toasted.show(response.data.error)
            return
            } else {
                this.$toasted.success('Fecha actualizada correctamente')
                if (this.cbGuardar) {
                    this.cbGuardar(response.data.insertId)
                }
                this.$refs.productoForm.hide()
            }
            
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