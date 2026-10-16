<template>
<div>
  <b-form @submit="guardarServicio" @keydown.enter="keyDownInput">
    <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigServicio" :title="title" size="lg" ref="servicioForm" v-model="show" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <b-row>
        <b-col md="6" class="nst-editor-section">
          <b-form-group>
            <label>Nombre del Servicio:</label>
            <b-form-input v-model="servicio.NombreServicio"
              ref="inputNombreServicio"
              required="required"
              type="text"
              autocomplete="off"
              style="text-transform: uppercase;"
              :value="servicio.NombreServicio"
              ></b-form-input>
          </b-form-group>
          <hr>
          <b-form-group>
            <b-form-checkbox
                    v-model="servicio.RequiereCodigo"
                    :value="1"
                    :unchecked-value="0"
                    >
              Requiere código para la operación
            </b-form-checkbox>
          </b-form-group>
          <b-form-group v-if="servicio.RequiereCodigo">
            <label>Nombre del código:</label>
            <b-form-input v-model="servicio.NombreCodigo"
              ref="inputNombreCodigo"
              required="required"
              type="text"
              style="text-transform: uppercase;"
              autocomplete="off"
              :value="servicio.NombreCodigo"
              ></b-form-input>
          </b-form-group>
        </b-col>

        <b-col md="6">
          <b-form-group>
            <b-form-checkbox
                    v-model="servicio.TieneComision"
                    :value="1"
                    :unchecked-value="0"
                    >
              Tiene comisión
            </b-form-checkbox>
          </b-form-group>

          <div v-if="servicio.TieneComision">
            <b-form-group>
              <b-form-checkbox
                      v-model="servicio.Calculo"
                      :value="1"
                      :unchecked-value="0"
                      >
                El sistema realizará el cálculo de comisión
              </b-form-checkbox>
            </b-form-group>

            <div v-if="servicio.Calculo">
              <b-form-group>
                <b-form-checkbox
                        v-model="servicio.ComisionFija"
                        :value="1"
                        :unchecked-value="0"
                        >
                  Comision Fija
                </b-form-checkbox>
              </b-form-group>
              <b-form-group>
                <label>Comisión</label>
                <input   
                  required="required"
                  v-model="servicio.MontoComision"
                  type="number"
                  class="form-control"
                  step="0.01"
                  />
              </b-form-group>
              <b-form-group v-if="!servicio.ComisionFija">
                <label>Comisión por cada</label>
                <input   
                  required="required"
                  v-model="servicio.MontoMax"
                  type="number"
                  class="form-control"
                  step="0.01"
                  />
              </b-form-group>
            </div>

            <hr v-if="servicio.Calculo">
            <b-form-group v-if="servicio.Calculo">
              <b-form-checkbox
                      v-model="servicio.Ganancia"
                      :value="1"
                      :unchecked-value="0"
                      >
                Tiene ganancia el servicio
              </b-form-checkbox>
            </b-form-group>
          </div>

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
      servicio: {},
      idEmpresaEntidad: null,
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['servicioSelect','cbGuardar','empresaSelect'],
  watch: {
    show (val) {
      if(val){
        this.idEmpresaEntidad = this.empresaSelect.IdEmpresaEntidad || null
        this.title = 'Editar Servicio'
        let newVal = this.servicioSelect  || {}
        if(newVal.IdServicio){
          this.servicio.IdServicio = newVal.IdServicio
          this.servicio.Ganancia = newVal.Ganancia
          this.servicio.Calculo = newVal.Calculo
          this.servicio.MontoMax = newVal.MontoMax || 0
          this.servicio.MontoComision = newVal.MontoComision || 0
          this.servicio.TieneComision = newVal.TieneComision
          this.servicio.RequiereCodigo = newVal.RequiereCodigo
          this.servicio.NombreCodigo = newVal.NombreCodigo || ''
          this.servicio.NombreServicio = newVal.Servicio || ''
          this.servicio.ComisionFija = newVal.ComisionFija
        }else{
          this.title = 'Nuevo Servicio'
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
      this.servicio = {
        NombreServicio: '',
        MontoMax: 0,
        MontoComision: 0,
        Calculo: 0,
        TieneComision: 0,
        Ganancia: 0,
        ComisionFija: 0,
        RequiereCodigo: 0,
        NombreCodigo: '',
      }
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    async guardarServicio(e) {
      e.preventDefault();

      if(this.idEmpresaEntidad == null){
        this.$toasted.show('Ha ocurrido un error, porfavor actualice la pagina')
        return
      }

      if(this.servicio.RequiereCodigo && (''+this.servicio.NombreCodigo).trim()==''){
        this.$toasted.show('Debe ingresar el nombre de código para la operación')
        return
      }

      if(this.servicio.Calculo){
        if(this.servicio.ComisionFija && !(this.servicio.MontoComision > 0)){
          this.$toasted.show('Ingrese el monto de la comisión fija')
          return
        }

        if(!this.servicio.ComisionFija && (!(this.servicio.MontoComision > 0) || !(this.servicio.MontoMax > 0))){
          this.$toasted.show('Debe ingresar un monto de comisión y un monto de cada cuanto cobrar comisión')
          return
        }

      }

      //reniciar valores
      if(!this.servicio.Calculo){
        this.servicio.MontoComision = 0
        this.servicio.ComisionFija = 0
        this.servicio.MontoMax = 0
      }


      this.servicio.NombreCodigo = (this.servicio.RequiereCodigo?(''+this.servicio.NombreCodigo).trim().toUpperCase():'')
      this.servicio.NombreServicio = (''+this.servicio.NombreServicio).trim().toUpperCase()


      this.servicio.IdEmpresaEntidad = this.idEmpresaEntidad
      try {
          let repuesta = await this.axios.post(`${this.API}agente/entidad/empresa/servicio`, this.servicio);

          if (repuesta.data.error) {
            this.$toasted.show(repuesta.data.error)
            return
          } else {
            this.$toasted.success('Servicio guardado correctamente')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.servicioForm.hide()
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
#modalConfigServicio .modal-dialog{
  /*max-width: 87% !important;*/
}
</style>
