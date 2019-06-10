<template>
  <div>
    <b-btn v-if="showButton" v-b-modal.modalAlmacen variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarAlmacenTipoDocSeries" @keydown.enter.prevent="()=>{}" >
    <b-modal :no-close-on-esc="true" id="modalAlmacen" :title="title" size="md" ref="almacenForm" v-model="show">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>Nombre Almacen:</label>
            <b-form-input v-model="almacen.Almacen"
              ref="inputProducto"
              required="required"
              type="text"
              @input="(val) => almacen.Almacen = val && val.toUpperCase()"
              :value="almacen.Almacen"
              ></b-form-input>
          </b-form-group>         
          
        </b-col>
      </b-row>

      <b-row>
        <b-col>
          <almacen-form-det 
            :id-almacen="almacen.IdAlmacen" 
            v-if="showAlmacenDet && show"
            ref="almacenDet"
            ></almacen-form-det>
        </b-col>
      </b-row>

      <div slot="modal-footer" >
       <b-btn  variant="danger" @click="eliminarAlmacen(almacen)" v-if="almacen.Almacen">
          Anular
        </b-btn>
        <b-btn  variant="warning" @click="clearForm" v-if="!almacen.IdAlmacen">
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
import AlmacenFormDet from '@/components/AlmacenFormDet'

export default {
  data () {
    return {
      showAlmacenDet: true,
      title: 'Nuevo Almacen',
      API: config.API,
      show: false, 
      almacen: {},
      almacenesDet: [],
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['showButton', 'almacenUpdate', 'cbGuardar'],
  watch: {
    show (val) {
      let newVal = this.almacenUpdate  || {}

      if (newVal.IdAlmacen) {
        this.title = 'Editar Almacen'
        this.almacen.Almacen = newVal.Almacen
        this.almacen.IdAlmacen = newVal.IdAlmacen

      } else {
        this.title = 'Nuevo Almacen'
        this.clearForm()
      }

    }
  },
  methods: {

    eliminarAlmacen (almacen) {
      let r = confirm('¿Esta seguro de Anular este almacen?')

      if (r) {
        this.axios.post(this.API + 'almacenes/delete', {
          id: almacen.IdAlmacen
        }).then(res => {
          if (res.data) {
            this.$toasted.success('El almacen con código ' + res.data.IdAlmacen + ' ha sido anulado')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.almacenForm.hide()
          }
        })
      }
    },
    clearForm () {
      this.initForm()
    },
    initForm () {
      this.almacen = {
        Almacen: '',
        Anulado: 0
      }
      mostrarSeries: 0
    },
    guardarAlmacenTipoDocSeries(e) {
      e.preventDefault();

      if (this.showAlmacenDet) {
        this.almacen.almacenesDet = this.$refs.almacenDet.tipodocumentos
      }

      this.axios.post(this.API + 'almacenes', this.almacen)
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(response.data)
        }
        this.$toasted.show('Almacen guardado correctamente')
        this.$refs.almacenForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    focusAutocomplete (ref) {
      this.$refs[ref].handleDoubleClick()
    }
  },
  computed : {
  },
  components: {
    AlmacenFormDet
  }
}
</script>
<style>

</style>
