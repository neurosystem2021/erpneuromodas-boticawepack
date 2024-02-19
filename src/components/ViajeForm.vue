<template>
    <div>
      <b-form @submit="guardarViaje" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalViaje" :title="title" size="md" ref="viajeForm" v-model="show">
          <b-row>
            <b-col md="6">
              <b-form-group>
                <label>Origen:</label>
                <b-input-group>
                  <autocomplete
                    required="required"
                    :url="API + 'transporte/ciudad'"
                    ref="inputOrigen"
                    anchor="Nombre"
                    label="writer"
                    :placeholder="viaje.origen.Nombre"
                    :on-select="setOrigen"
                    :onFocus="focusAutocompleteOrigen.bind(null, 'inputOrigen')"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-input-group>
              </b-form-group>
            </b-col>

            <b-col md="6">
              <b-form-group>
                <label>Destino:</label>
                <b-input-group>
                  <autocomplete
                    required="required"
                    :url="API + 'transporte/ciudad'"
                    ref="inputDestino"
                    anchor="Nombre"
                    label="writer"
                    :placeholder="viaje.destino.Nombre"
                    :on-select="setDestino"
                    :onFocus="focusAutocompleteDestino.bind(null, 'inputDestino')"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-input-group>
              </b-form-group>
            </b-col>

            <b-col md="6">
              <b-form-group>
                <label>Fecha de salida:</label>
                <b-form-input
                  required="required"
                  v-model="viaje.fecha"
                  type="date"
                  :min="minViaje"
                ></b-form-input>
              </b-form-group>
            </b-col>

            <b-col md="6">
              <b-form-group>
                <label>Hora de salida:</label>
                <b-form-input
                  v-model="viaje.hora"
                  type="time"
                  required="required"
                  v-on:input="(val) => convertirTiempo(val)"
                ></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="12" class="alignCenter">
                <h3 class="m-2"><b>{{ horaViaje }}</b></h3>
            </b-col>

            <b-col md="12">
              <b-form-group>
                <label>Conductor:</label>
                <b-input-group>
                  <autocomplete
                    required="required"
                    :url="API + 'transporte/conductor/seleccion'"
                    ref="inputConductor"
                    anchor="NombreCompleto"
                    label="writer"
                    :placeholder="viaje.conductor.NombreCompleto"
                    :on-select="setConductor"
                    :onFocus="focusAutocompleteConductor.bind(null, 'inputConductor')"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-input-group>
              </b-form-group>
            </b-col>

            <b-col md="12">
              <b-form-group>
                <label>Vehículo:</label>
                <b-input-group>
                  <autocomplete
                    required="required"
                    :url="API + 'transporte/vehiculo/seleccion'"
                    ref="inputVehiculo"
                    anchor="VehiculoCompleto"
                    label="writer"
                    :placeholder="viaje.vehiculo.Vehiculo"
                    :on-select="setVehiculo"
                    :onFocus="focusAutocompleteVehiculo.bind(null, 'inputVehiculo')"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-input-group>
              </b-form-group>
            </b-col>

          </b-row>
          <div slot="modal-footer">
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
        viaje: {},
        minViaje: new Date(Date.now() - 86400000).toISOString().split('T')[0],
        horaViaje: ''
      }
    },
    beforeMount () {
      this.initForm()
    },
    props: ['viajeSelect','cbGuardar'],
    watch: {
      show (val) {
        if(val){
          this.title = 'Editar Viaje'
          let newVal = this.viajeSelect  || {}
          if(newVal.IdViaje){

          }else{
            this.title = 'Registrar Nuevo Viaje'
            this.clearForm()
          }
        }
      }
    },
    methods: {
      clearForm () {
        this.initForm()
        this.$refs.inputDestino.setValue('')
        this.$refs.inputOrigen.setValue('')
        this.$refs.inputConductor.setValue('')
        this.$refs.inputVehiculo.setValue('')
        this.horaViaje = ''
        this.minViaje = new Date(Date.now() - 86400000).toISOString().split('T')[0]
      },
      initForm () {
        this.viaje = {
          origen: {
            IdCiudad: null,
            Nombre: ''
          },
          destino: {
            IdCiudad: null,
            Nombre: ''
          },
          conductor: {
            IdConductor: null,
            NombreCompleto: ''
          },
          vehiculo: {
            IdVehiculo: null,
            VehiculoCompleto: ''
          },
          fecha: '',
          hora: '',
        }
      },
      setOrigen (obj) {
        this.viaje.origen = obj
        this.$refs.inputOrigen.setValue(obj.Nombre)
      },
      setDestino (obj) {
        this.viaje.destino = obj
        this.$refs.inputDestino.setValue(obj.Nombre)
      },
      setConductor(obj) {
        console.log(obj)
        this.viaje.conductor = obj
        this.$refs.inputConductor.setValue(obj.NombreCompleto)
      },
      setVehiculo(obj) {
        this.viaje.vehiculo = obj
        this.$refs.inputVehiculo.setValue(obj.VehiculoCompleto)
      },
      focusAutocompleteDestino (ref) {
        this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
        this.viaje.destino = {
          IdCiudad: null,
          Nombre: ''
        }
      },
      focusAutocompleteOrigen (ref) {
        this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
        this.viaje.origen = {
          IdCiudad: null,
          Nombre: ''
        }
      },
      focusAutocompleteConductor (ref) {
        this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
        this.viaje.conductor = {
          IdConductor: null,
          NombreCompleto: ''
        }
      },
      focusAutocompleteVehiculo (ref) {
        this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
        this.viaje.vehiculo = {
          IdVehiculo: null,
          VehiculoCompleto: ''
        }
      },
      convertirTiempo() {
          if(this.viaje.hora!=''){
            let oldFormatTimeArray = this.viaje.hora.split(":");
            let HH = parseInt(oldFormatTimeArray[0]);
            let min = oldFormatTimeArray[1];

            let AMPM = HH >= 12 ? "PM" : "AM";
            let hours;
            if(HH == 0){
              hours = HH + 12;
            } else if (HH > 12) {
              hours = HH - 12;
            } else {
              hours = HH;
            }
            this.horaViaje = hours + ":" + min + " " + AMPM;
          }
      },
      async guardarViaje(e) {
        e.preventDefault();
  
        if(this.viaje.vehiculo.IdVehiculo == null){
          this.$toasted.show('Debe seleccionar un vehículo')
          return
        }

        if(this.viaje.origen.IdCiudad == null){
          this.$toasted.show('Debe seleccionar una ciudad de origen')
          return
        }

        if(this.viaje.destino.IdCiudad == null){
          this.$toasted.show('Debe seleccionar una ciudad de destino')
          return
        }

        if(this.viaje.conductor.IdConductor == null){
          this.$toasted.show('Debe seleccionar un conductor')
          return
        }

        if(this.viaje.fecha == ''){
          this.$toasted.show('Debe seleccionar una fecha de viaje')
          return
        }

        if(this.viaje.hora == ''){
          this.$toasted.show('Debe seleccionar una hora de viaje')
          return
        }

        if(this.viaje.origen.IdCiudad == this.viaje.destino.IdCiudad){
          this.$toasted.show('El destino y el origen no pueden ser el mismo')
          return
        }

        this.viaje.usuario = sessionStorage.getItem('User')
        try {
            let repuesta = await this.axios.post(`${this.API}transporte/viajes`, this.viaje);
  
            if (repuesta.data.error) {
              this.$toasted.show(repuesta.data.msg)
              return
            } else {
              this.$toasted.success('Viaje creado correctamente')
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.viajeForm.hide()
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
  #modalViaje .modal-dialog{
    /*max-width: 87% !important;*/
  }
  .alignCenter{
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
  }
  </style>