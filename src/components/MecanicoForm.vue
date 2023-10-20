<template>
    <div>
      <!-- Modal Component -->
      <b-form @submit="guardarMecanico" ref="form">    
      <b-modal :id="'modal2'" no-close-on-backdrop :title="title" size="lg" ref="mecanicoForm" v-model="show" @shown="shownClienteForm">
        <b-row>
          <b-col md="12">
            <b-form-group>
              <label>DNI:</label>
              <b-form-input 
                  required="required"
                  style="text-transform:uppercase"
                  v-model="mecanico.Dni"
                  autocomplete="off"
                  type="text"></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Nombres:</label>
              <b-form-input 
                  required="required"
                  style="text-transform:uppercase"
                  v-model="mecanico.Mecanico"
                  autocomplete="off"
                  type="text"></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Domicilio:</label>
              <b-form-input 
                style="text-transform:uppercase"
                v-model="mecanico.Domicilio"
                autocomplete="off"
                type="text"></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Telefono:</label>
              <b-form-input 
                style="text-transform:uppercase"
                v-model="mecanico.Telefono"
                autocomplete="off"
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
        title: 'Nuevo mecanico',
        mecanico: {
          Dni: '',
          Mecanico: '',
          Domicilio: '',
          Telefono: '',
        },
        inProcess: false,
      }
    },
    props: ['mecanicoSelect'],
    watch: {
      mecanicoSelect (cl) {
        this.mecanico = Object.assign({}, cl)
        if(Object.keys(this.mecanico).length==0){
          this.title = 'Nuevo Mecanico'
        }else{
          this.title = 'Editar Mecanico <'+this.mecanico.IdMecanico+'>'
        }
        
      }
    },
    methods: {
      shownClienteForm () {
  
      },
      guardarMecanico(evt) {
        evt.preventDefault();
        this.inProcess = true
        if((this.mecanico.Dni+'').trim()=='' && (this.mecanico.Mecanico+'').trim()==''){
          this.$toasted.show('Por favor ingrese Dni y Nombres del mecanico')
          this.inProcess = false
          return;
        }
        this.mecanico.Dni = (""+this.mecanico.Dni).toUpperCase();
        this.mecanico.Mecanico = (""+this.mecanico.Mecanico).toUpperCase();
        this.mecanico.Domicilio = (""+this.mecanico.Domicilio).toUpperCase();
        this.mecanico.Telefono = (""+this.mecanico.Telefono).toUpperCase();
        
        this.axios.post(this.API + 'mecanicos', this.mecanico)
        .then((response) => {
          // console.log(response);
          this.$emit('onAddMecanico', response.data)
          this.$toasted.success('Mecanico guardado correctamente')
          //this.initForm()
          this.$refs.mecanicoForm.hide()
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