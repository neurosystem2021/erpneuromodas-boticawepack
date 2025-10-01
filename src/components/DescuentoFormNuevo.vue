<template>
  <div>
    <!-- Modal Component -->
    <b-form @submit="guardarDescuento" @keydown.enter="keyDownInput" autocomplete="off" >
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalProducto" :title="title" size="lg" ref="productoForm" v-model="show">
        <b-col md="6">
          <b-form-group>
            <label>Color:</label>
            <b-form-input
              v-model="descuento.Color"
              @input="(val) => descuento.Color = val && val.toUpperCase()"
              type="text"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>

        <b-col md="12" style="margin-top:10px">
          <b-form-group>
            <label>% Descuento Corporativo:</label>
            <b-form-input
              v-model="descuento.UCorporativo"
              type="number"
              value="0"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>

        <b-col md="12" style="margin-top:10px">
          <b-form-group>
            <label>% Descuento Público:</label>
            <b-form-input
              v-model="descuento.UPublico"
              type="number"
              value="0"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>

        <b-col md="12" style="margin-top:10px">
          <b-form-group>
            <label>% Descuento Institucional:</label>
            <b-form-input
              v-model="descuento.UTaller"
              type="number"
              value="0"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col>

        <!-- <b-col md="12" style="margin-top:10px">
          <b-form-group>
            <label>Tipo Color:</label>
            <b-form-input
              v-model="descuento.TipoColor"
              type="number"
              value="0"
              required="required"
              ></b-form-input>
          </b-form-group>
        </b-col> -->

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
      title: 'Nuevo Descuento',
      API: config.API,
      show: false, 
      // generos: ['VARON', 'DAMA', 'NIÑO', 'NIÑA', 'UNISEX', 'JUNIOR'],
      descuento: {},
      nuevo: false,
      descuentoItems: [
      ],
    }
  },
  props: ['categoriaUpdate', 'cbGuardar'],
  watch: {
     show (val) {
      
      let newVal = this.categoriaUpdate  || {}
      if (newVal.IdDescuento && !this.nuevo) {
        this.initForm()
        this.title='Editar Descuento << '+newVal.IdDescuento+' >>'
        this.descuento.IdDescuento = newVal.IdDescuento
        this.descuento.Color=newVal.Color
        this.descuento.UCorporativo = newVal.UCorporativo
        this.descuento.UPublico = newVal.UPublico
        this.descuento.UTaller = newVal.UTaller
        this.descuento.TipoColor = newVal.TipoColor
      } else {
        this.title='Nuevo Decuento'
        this.initForm();
      }
    }
  },
  methods: {
    initForm () {
      this.descuento = {
          Color: '',
          IdDescuento: null,
          UCorporativo: 0,
          UPublico: 0,
          UTaller: 0,
          TipoColor: 0
      }
    },
    guardarDescuento(e) {
      e.preventDefault();
      this.axios.post(this.API + 'descuentos/color', {
        ...this.descuento 
      })
      .then((response) => {
        if (this.cbGuardar) {
          this.cbGuardar(response.data.insertId)
        }
        if (response.data.error) {
          this.$toasted.show(response.data.error + ' - ' + response.data.Color)
          return
        } else {
          this.$toasted.show('Descuento guardada correctamente')
        }
        this.$refs.productoForm.hide()
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error o el descuento  ya existe, vuelva a intentarlo')
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