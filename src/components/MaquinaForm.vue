<template>
  <div>
    <b-btn v-b-modal="idModal || 'modal1'" variant="primary">{{ txtButton || 'Nuevo' }}</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarMaquina" ref="form">    
    <b-modal :id="idModal || 'modal1'" title="Nueva maquina" size="lg" ref="maquinaForm" v-model="show" @shown="shownClienteForm">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>Nombre del equipo:</label>
            <b-form-input 
                required="required"
                v-model="equipo.Nombre"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Placa:</label>
            <b-form-input 
              v-model="equipo.Placa"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Tipo:</label>
            <b-form-input 
              v-model="equipo.Tipo"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección:</label>
            <b-form-input 
              v-model="equipo.Direccion"
              type="text"></b-form-input>
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
export default {
  data() {
    return {
      API: config.API,
      show: false,
      equipo: {
        Nombre: '',
        Placa: '',
        Tipo: '',
        Direccion: '',
      }
    }
  },
  props: ['idModal', 'newDniRuc', 'editCliente', 'txtButton'],
  /*watch: {
    show (val) {
      console.log(this.editCliente)
      if (this.editCliente.IdCliente) {
        this.cliente = this.editCliente
      }
    }
  },*/
  methods: {
    shownClienteForm () {

    },
    guardarMaquina(evt) {
      evt.preventDefault();

      if((this.equipo.Nombre+'').trim()=='' && (this.equipo.Placa+'').trim()==''){
        this.$toasted.show('Por favor ingrese nombre y placa del equipo')
        return;
      }

      this.axios.post(this.API + 'clientes', this.cliente)
      .then((response) => {
        // console.log(response);
        this.$emit('onAddCliente', response.data)
        this.$toasted.show('Cliente guardado correctamente')
        this.$refs.clienteForm.hide()
        this.cliente = {}
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style scoped>
  input[type=number]::-webkit-inner-spin-button, 
  input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
  }
</style>