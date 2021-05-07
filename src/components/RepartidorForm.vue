<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalProducto variant="primary">Nuevo Repartidor</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarRepartidor" @keydown.enter.prevent="()=>{}" >
    <b-modal :no-close-on-esc="true" id="modalRepartidor" :title="title" size="lg" ref="repartidorForm" v-model="show">
      <b-row>
        <b-col md="6">
          <b-form-group>
            <label>Repartidor:</label>
            <b-form-input v-model="repartidor.Repartidor"
              ref="inputRepartidor"
              required="required"
              type="text"
              @input="(val) => repartidor.Repartidor = val && val.toUpperCase()"
              :value="repartidor.Repartidor"
              ></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>

      <div slot="modal-footer" >
        <b-btn  variant="danger" @click="anularRepartidor(repartidor)" v-if="repartidor.IdRepartidor && repartidor.Anulado==0">
          Anular
        </b-btn>
        <b-btn  variant="success" @click="habilitarRepartidor(repartidor)" v-if="repartidor.IdRepartidor && repartidor.Anulado==1">
          Habilitar
        </b-btn>
        <b-btn  variant="warning" @click="clearForm" v-if="!repartidor.IdRepartidor">
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
// import ModeloForm from '@/components/ModeloForm'

export default {
  data () {
    return {
      title: 'Nuevo Repartidor',
      API: config.API,
      show: false, 
      // generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      repartidor: {},
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'repartidorUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.repartidorUpdate  || {}
      if (newVal.IdRepartidor) {
        this.title = 'Editar Repartidor << ' + newVal.IdRepartidor + ' >>'
        this.repartidor.IdRepartidor = newVal.IdRepartidor
        this.repartidor.Repartidor = newVal.Repartidor
        this.repartidor.Anulado = newVal.Anulado
      } else {
        this.title = 'Nuevo Repartidor'
        this.clearForm()
      }
    }
  },
  methods: {
    anularRepartidor (repartidor) {
    // NO eliminar porque no distinque movimientos ni ventas
    let r = confirm('¿Esta seguro de Anular este repartidor?')
      if (r) {
        this.axios.post(this.API + 'repartidores/delete', {
          id: repartidor.IdRepartidor 
        }).then(res => {
          if (res.data.updated) {
            this.$toasted.success('El repartidor ' + res.data.IdRepartidor + ' ha sido anulado')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.repartidorForm.hide()
          }
        })
      } 
    },
    habilitarRepartidor (repartidor) {
    // NO eliminar porque no distinque movimientos ni ventas
    let r = confirm('¿Esta seguro de Habilitar este repartidor?')
      if (r) {
        this.axios.post(this.API + 'repartidores/habilitar', {
          id: repartidor.IdRepartidor 
        }).then(res => {
          if (res.data.updated) {
            this.$toasted.success('El repartidor ' + res.data.IdRepartidor + ' ha sido habilitado')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.repartidorForm.hide()
          }
        })
      } 
    },
    clearForm () {
      this.initForm()
    },
    initForm () {
      this.repartidor = {
          Repartidor:'',
          Anulado:0
      }
    },
    guardarRepartidor(e) {
      e.preventDefault();

      this.axios.post(this.API + 'repartidores', this.repartidor)
      .then((response) => {
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.selectId)
          return
        } else {
            if(response.data.updateId){
                 this.$toasted.success('Repartidor actualizado correctamente')
            }else{
                 this.$toasted.success('Repartidor guardado correctamente')
            }
        }

        if (this.cbGuardar) {
          this.cbGuardar()
        }

        this.$refs.repartidorForm.hide()
      })
      .catch((error) => {
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
