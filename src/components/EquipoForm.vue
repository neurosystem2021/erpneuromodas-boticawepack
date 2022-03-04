<template>
  <div>
    <!-- Modal Component -->
    <b-form @submit="guardarEquipo" ref="form">    
    <b-modal :id="'modal2'" no-close-on-backdrop :title="title" size="lg" ref="equipoForm" v-model="show" @shown="shownClienteForm">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>Nombre del equipo:</label>
            <b-form-input 
                required="required"
                style="text-transform:uppercase"
                v-model="equipo.Nombre"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Placa:</label>
            <b-form-input 
              style="text-transform:uppercase"
              v-model="equipo.Placa"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Tipo:</label>
            <b-form-input 
              style="text-transform:uppercase"
              v-model="equipo.Tipo"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección:</label>
            <b-form-input 
              style="text-transform:uppercase"
              v-model="equipo.Direccion"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <b-btn  variant="success" :disabled="inProcess" type="submit">
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
export default {
  data() {
    return {
      API: config.API,
      show: false,
      title: 'Nuevo Equipo',
      equipo: {
        Nombre: '',
        Placa: '',
        Tipo: '',
        Direccion: '',
      },
      inProcess: false,
    }
  },
  props: ['equipoSelect'],
  watch: {
    equipoSelect (cl) {
      this.equipo = Object.assign({}, cl)
      if(Object.keys(this.equipo).length==0){
        this.title = 'Nuevo Equipo'
      }else{
        this.title = 'Editar Equipo <'+this.equipo.IdEquipo+'>'
      }
      
    }
  },
  methods: {
    shownClienteForm () {

    },
    guardarEquipo(evt) {
      evt.preventDefault();
      this.inProcess = true
      if((this.equipo.Nombre+'').trim()=='' && (this.equipo.Placa+'').trim()==''){
        this.$toasted.show('Por favor ingrese nombre y placa del equipo')
        this.inProcess = false
        return;
      }
      this.equipo.Nombre = (""+this.equipo.Nombre).toUpperCase();
      this.equipo.Placa = (""+this.equipo.Placa).toUpperCase();
      this.equipo.Tipo = (""+this.equipo.Tipo).toUpperCase();
      this.equipo.Direccion = (""+this.equipo.Direccion).toUpperCase();
      
      this.axios.post(this.API + 'equipos', this.equipo)
      .then((response) => {
        // console.log(response);
        this.$emit('onAddEquipo', response.data)
        this.$toasted.success('Equipo guardado correctamente')
        //this.initForm()
        this.$refs.equipoForm.hide()
        this.inProcess = false
      })
      .catch((error) => {
        this.inProcess = false
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