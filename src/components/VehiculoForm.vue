<template>
    <div>
      <b-form @submit="guardarVehiculo" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigVehiculo" :title="title" size="md" ref="vehiculoForm" v-model="show">
          <b-row>
            <b-col md="6">
                <b-form-group>
                    <label>Placa:</label>
                    <b-form-input v-model="vehiculo.Placa"
                    ref="inputPlacaVehiculo"
                    required="required"
                    type="text"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="vehiculo.Placa"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col md="12">
              <b-form-group>
                <label>Descripción (Modelo, Marca):</label>
                <b-form-input v-model="vehiculo.Vehiculo"
                  ref="inputVehiculoVehiculo"
                  required="required"
                  type="text"
                  autocomplete="off"
                  style="text-transform: uppercase;"
                  :value="vehiculo.Vehiculo"
                  ></b-form-input>
              </b-form-group>
            </b-col>
  
            <b-col md="6">
                <b-form-group>
                    <label>Asientos:</label>
                    <b-form-input v-model="vehiculo.Asientos"
                    ref="inputAsientosVehiculo"
                    required="required"
                    type="number"
                    step="1"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="vehiculo.Asientos"
                    ></b-form-input>
                </b-form-group>
            </b-col>

            <b-col md="6">
                <b-form-group>
                    <label>Ejes:</label>
                    <b-form-input v-model="vehiculo.Ejes"
                    ref="inputEjesVehiculo"
                    required="required"
                    type="number"
                    step="1"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    :value="vehiculo.Ejes"
                    ></b-form-input>
                </b-form-group>
            </b-col>
            <b-col md="12">
                <b-form-group>
                    <label>Propietario:</label>
                    <b-form-textarea 
                    ref="inputPropietarioVehiculo"
                    rows="2"
                    v-model="vehiculo.Propietario"
                    :value="vehiculo.Propietario"
                    autocomplete="off"
                    style="text-transform: uppercase;"
                    />
                </b-form-group>
            </b-col>
          </b-row>
          <div slot="modal-footer" >
            <div style="left: 1rem;position: absolute;" v-if="vehiculo.IdVehiculo">
              <b-btn  :variant="vehiculo.Anulado ? 'success':'danger'" @click="cambiarEstado(vehiculo)">
                {{  vehiculo.Anulado ? 'Habilitar':'Anular' }}
              </b-btn>
            </div>
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
        vehiculo: {},
      }
    },
    beforeMount () {
      this.initForm()
    },
    props: ['vehiculoSelect','cbGuardar'],
    watch: {
      show (val) {
        if(val){
          this.title = 'Editar Vehiculo'
          let newVal = this.vehiculoSelect  || {}
          if(newVal.IdVehiculo){
            this.vehiculo.IdVehiculo = newVal.IdVehiculo
            this.vehiculo.Vehiculo = newVal.Vehiculo
            this.vehiculo.Placa = newVal.Placa
            this.vehiculo.Ejes = newVal.Ejes
            this.vehiculo.Propietario = newVal.Propietario
            this.vehiculo.Asientos = newVal.Asientos
            this.vehiculo.Anulado = newVal.Anulado
          }else{
            this.title = 'Nuevo Vehiculo'
            this.clearForm()
          }
        }
      }
    },
    methods: {
      clearForm () {
        this.initForm()
      },
      initForm () {
        this.vehiculo = {
          Vehiculo: '',
          Placa: '',
          Ejes: 0,
          Propietario: '',
          Asientos: 0,
          Anulado: 0,
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      async cambiarEstado(vehiculo){
        let r = confirm('¿Esta seguro de '+(vehiculo.Anulado ? 'Habilitar':'Anular')+' este vehiculo?')
        if (r) {
          try {
            let respuesta = await this.axios.post(this.API + 'transporte/vehiculo/estado', {
              IdVehiculo: vehiculo.IdVehiculo,
              Estado: vehiculo.Anulado? 0 : 1
            })
            if (respuesta.data.updated) {
              this.$toasted.info('El vehiculo con Placa ' + vehiculo.Placa + ' ha sido '+(vehiculo.Anulado ? 'Habilitado' : 'Anulado'))
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.vehiculoForm.hide()
            }
          } catch (error) {
            this.$toasted.error('Ha ocurrido un error')
          }
        }
      },
      async guardarVehiculo(e) {
        e.preventDefault();
  
        for (const [key, value] of Object.entries(this.vehiculo)) {
          if((''+value).trim().toUpperCase()=='' && key!='Asientos' && key!='Ejes'){
            this.$toasted.show(`Complete el dato ${key} es requerido`)
            this.vehiculo[key] = (''+value).trim()
            return
          }

          if((key=='Asientos' || key=='Ejes') && !(value > 0)){
            this.$toasted.show(`Complete el dato ${key} es requerido y númerico`)
            return
          }
        }

        for (const [key, value] of Object.entries(this.vehiculo)) {
          if(key!='Asientos' && key!='Ejes'){
            this.vehiculo[key] = (''+value).trim().toUpperCase()
          }
        }

        this.vehiculo.usuario = sessionStorage.getItem('User')
        try {
            let repuesta = await this.axios.post(`${this.API}transporte/vehiculo`, this.vehiculo);
  
            if (repuesta.data.error) {
              this.$toasted.show(repuesta.data.msg)
              return
            } else {
              this.$toasted.success('Vehiculo guardado correctamente')
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.vehiculoForm.hide()
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
  #modalConfigVehiculo .modal-dialog{
    /*max-width: 87% !important;*/
  }
  </style>