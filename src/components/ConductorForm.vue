<template>
    <div>
      <b-form @submit="guardarConductor" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigConductor" :title="title" size="md" ref="conductorForm" v-model="show">
          <b-row>
            <b-col md="6">
                <b-form-group>
                    <label>DNI:</label>
                    <b-form-input v-model="conductor.Dni"
                    ref="inputDniConductor"
                    required="required"
                    type="text"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="conductor.Dni"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col md="12">
              <b-form-group>
                <label>Nombres:</label>
                <b-form-input v-model="conductor.Nombres"
                  ref="inputNombresConductor"
                  required="required"
                  type="text"
                  autocomplete="off"
                  style="text-transform: uppercase;"
                  :value="conductor.Nombres"
                  ></b-form-input>
              </b-form-group>
              <b-form-group>
                <label>Apellidos</label>
                <b-form-input v-model="conductor.Apellidos"
                  ref="inputApellidosConductor"
                  required="required"
                  type="text"
                  autocomplete="off"
                  style="text-transform: uppercase;"
                  :value="conductor.Apellidos"
                  ></b-form-input>
              </b-form-group>
              <hr>
            </b-col>
  
            <b-col md="6">
                <b-form-group>
                    <label>Licencia:</label>
                    <b-form-input v-model="conductor.Licencia"
                    ref="inputLicenciaConductor"
                    required="required"
                    type="text"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="conductor.Licencia"
                    ></b-form-input>
                </b-form-group>
                <b-form-group>
                    <label>Fecha Nacimiento:</label>
                    <b-form-input v-model="conductor.FechaNacimiento"
                    ref="inputFechaNacimientoConductor"
                    required="required"
                    type="date"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="conductor.FechaNacimiento"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col md="6">
                <b-form-group>
                    <label>Télefono - Celular:</label>
                    <b-form-input v-model="conductor.Telefono"
                    ref="inputTelefonoConductor"
                    required="required"
                    type="text"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="conductor.Telefono"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col md="12">
                <b-form-group>
                    <label>Domicilio - Dirección:</label>
                    <b-form-textarea 
                    ref="inputDireccionConductor"
                    rows="2"
                    v-model="conductor.Direccion"
                    :value="conductor.Direccion"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    />
                </b-form-group>
            </b-col>
          </b-row>
          <div slot="modal-footer" >
            <b-btn  variant="primary" type="submit">
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
    data () {
      return {
        title: '',
        API: config.API,
        show: false,
        conductor: {},
      }
    },
    beforeMount () {
      this.initForm()
    },
    props: ['conductorSelect','cbGuardar'],
    watch: {
      show (val) {
        if(val){
          this.title = 'Editar de Conductor'
          let newVal = this.conductorSelect  || {}
          if(newVal.IdConductor){
            this.conductor.IdConductor = newVal.IdConductor
            this.conductor.Nombres = newVal.Nombres
            this.conductor.Apellidos = newVal.Apellidos
            this.conductor.Dni = newVal.Dni
            this.conductor.Telefono = newVal.Telefono
            this.conductor.Direccion = newVal.Direccion
            this.conductor.Licencia = newVal.Licencia
            this.conductor.FechaNacimiento = newVal.FechaNacimiento
            this.conductor.Anulado = newVal.Anulado
            this.conductor.Imagen = newVal.Imagen
          }else{
            this.title = 'Nuevo Conductor'
            this.clearForm()
          }
        }
      }
    },
    methods: {
      setTipo(obj){
        console.log(obj);
      },
      clearForm () {
        this.initForm()
      },
      initForm () {
        this.conductor = {
          Nombres: '',
          Apellidos: '',
          Dni: '',
          Telefono: '',
          Direccion: '',
          Licencia: '',
          FechaNacimiento: '',
          Anulado: 0,
          Imagen: null,
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      async guardarConductor(e) {
        e.preventDefault();
  
        /*if(this.idEntidad == null){
          this.$toasted.show('Ha ocurrido un error, porfavor actualice la pagina')
          return
        }
  
        if(this.conductor.Tipo == null){
          this.$toasted.show('Debe seleccionar un tipo de operación referente a efectivo de caja')
          return
        }
  
        if(this.conductor.RequiereCodigo && (''+this.conductor.NombreCodigo).trim()==''){
          this.$toasted.show('Debe ingresar el nombre de código para la operación')
          return
        }
  
        if(this.conductor.Calculo){
          if(this.conductor.ComisionFija && !(this.conductor.MontoComision > 0)){
            this.$toasted.show('Ingrese el monto de la comisión fija')
            return
          }
  
          if(!this.conductor.ComisionFija && (!(this.conductor.MontoComision > 0) || !(this.conductor.MontoMax > 0))){
            this.$toasted.show('Debe ingresar un monto de comisión y un monto de cada cuanto cobrar comisión')
            return
          }
  
        }
  
        //reniciar valores
        if(!this.conductor.Calculo){
          this.conductor.MontoComision = 0
          this.conductor.ComisionFija = 0
          this.conductor.MontoMax = 0
        }
  
        if(this.conductor.TieneEnlaceExterno){
          if((''+this.conductor.EnlaceExterno1).trim().toUpperCase()==''){
            this.$toasted.show('Complete el primer enlace externo')
            this.conductor.EnlaceExterno1 = (''+this.conductor.EnlaceExterno1).trim()
            this.conductor.EnlaceExterno2 = (''+this.conductor.EnlaceExterno2).trim()
            return
          }
        }else{
          this.conductor.EnlaceExterno1 = ''
          this.conductor.EnlaceExterno2 = ''
        }
  
        this.conductor.EnlaceExterno1 = (''+this.conductor.EnlaceExterno1).trim()
        this.conductor.EnlaceExterno2 = (''+this.conductor.EnlaceExterno2).trim()
        this.conductor.NombreCodigo = (this.conductor.RequiereCodigo?(''+this.conductor.NombreCodigo).trim().toUpperCase():'')
        this.conductor.Conductor = (''+this.conductor.Conductor).trim().toUpperCase()
        this.conductor.IdEntidad = this.idEntidad*/
  
        try {
            let repuesta = await this.axios.post(`${this.API}transporte/conductor`, this.conductor);
  
            if (repuesta.data.error) {
              this.$toasted.show(repuesta.data.error)
              return
            } else {
              this.$toasted.success('Conductor guardado correctamente')
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.conductorForm.hide()
            }  
        } catch (error) {
          this.$toasted.error('Ha ocurrido un error')
        }
      }
    },
    computed : {
    },
    components: {
  
    }
  }
  </script>
  <style>
  #modalConfigConductor .modal-dialog{
    /*max-width: 87% !important;*/
  }
  </style>