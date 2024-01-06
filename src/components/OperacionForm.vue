<template>
  <div>
    <b-form @submit="guardarOperacion" @keydown.enter="keyDownInput">
      <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigOperacion" :title="title" size="lg" ref="operacionForm" v-model="show">
        <b-row>
          <b-col md="6">
            <b-form-group>
              <label>Nombre de la Operación:</label>
              <b-form-input v-model="operacion.Operacion"
                ref="inputNombreOperacion"
                required="required"
                type="text"
                autocomplete="off"
                style="text-transform: uppercase;"
                :value="operacion.Operacion"
                ></b-form-input>
            </b-form-group>
            <hr>
            <b-form-group>
              <b-form-checkbox
                      v-model="operacion.RequiereCodigo"
                      :value="1"
                      :unchecked-value="0"
                      >
                Requiere código para la operación
              </b-form-checkbox>
            </b-form-group>
            <b-form-group v-if="operacion.RequiereCodigo">
              <label>Nombre del código:</label>
              <b-form-input v-model="operacion.NombreCodigo"
                ref="inputNombreCodigo"
                required="required"
                type="text"
                style="text-transform: uppercase;"
                autocomplete="off"
                :value="operacion.NombreCodigo"
                ></b-form-input>
            </b-form-group>
            <b-form-group>
              <label>Tipo de Operación</label>
              <b-form-select v-model="operacion.Tipo" :options="itemsTipos" v-on:change="setTipo($event)" size="md"/>
            </b-form-group>
          </b-col>

          <b-col md="6">
            <b-form-group>
              <b-form-checkbox
                      v-model="operacion.TieneComision"
                      :value="1"
                      :unchecked-value="0"
                      >
                Tiene comisión
              </b-form-checkbox>
            </b-form-group>

            <div v-if="operacion.TieneComision">
              <b-form-group>
                <b-form-checkbox
                        v-model="operacion.Calculo"
                        :value="1"
                        :unchecked-value="0"
                        >
                  El sistema realizará el cálculo de comisión
                </b-form-checkbox>
              </b-form-group>

              <div v-if="operacion.Calculo">
                <b-form-group>
                  <b-form-checkbox
                          v-model="operacion.ComisionFija"
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
                    v-model="operacion.MontoComision"
                    type="number"
                    class="form-control"
                    step="0.01"
                    />
                </b-form-group>
                <b-form-group v-if="!operacion.ComisionFija">
                  <label>Comisión por cada</label>
                  <input   
                    required="required"
                    v-model="operacion.MontoMax"
                    type="number"
                    class="form-control"
                    step="0.01"
                    />
                </b-form-group>
              </div>

              <hr v-if="operacion.Calculo">
              <b-form-group v-if="operacion.Calculo">
                <b-form-checkbox
                        v-model="operacion.Ganancia"
                        :value="1"
                        :unchecked-value="0"
                        >
                  Tiene ganancia la operación
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
      operacion: {},
      idEntidad: null,
      itemsTipos: [
        { value: null, text: '== SELECCIONE TIPO ==' },
        { value: 0, text: 'INGRESO DE EFECTIVO' },
        { value: 1, text: 'EGRESO DE EFECTIVO' },
      ],
    }
  },
  beforeMount () {
    this.initForm()
  },
  props: ['operacionSelect','cbGuardar','entidadSelect'],
  watch: {
    show (val) {
      if(val){
        this.idEntidad = this.entidadSelect.IdEntidad || null
        this.title = 'Editar de Operación'
        let newVal = this.operacionSelect  || {}
        if(newVal.IdOperacion){
          this.operacion.IdOperacion = newVal.IdOperacion
          this.operacion.Ganancia = newVal.Ganancia
          this.operacion.Calculo = newVal.Calculo
          this.operacion.MontoMax = newVal.MontoMax || 0
          this.operacion.MontoComision = newVal.MontoComision || 0
          this.operacion.TieneComision = newVal.TieneComision
          this.operacion.RequiereCodigo = newVal.RequiereCodigo
          this.operacion.NombreCodigo = newVal.NombreCodigo || ''
          this.operacion.Operacion = newVal.Operacion || ''
          this.operacion.ComisionFija = newVal.ComisionFija
          this.operacion.Tipo = newVal.Tipo
        }else{
          this.title = 'Nueva Operación'
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
      this.operacion = {
        Operacion: '',
        MontoMax: 0,
        MontoComision: 0,
        Calculo: 0,
        TieneComision: 0,
        Ganancia: 0,
        ComisionFija: 0,
        RequiereCodigo: 0,
        NombreCodigo: '',
        Tipo: null
      }
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    async guardarOperacion(e) {
      e.preventDefault();

      if(this.idEntidad == null){
        this.$toasted.show('Ha ocurrido un error, porfavor actualice la pagina')
        return
      }

      if(this.operacion.Tipo == null){
        this.$toasted.show('Debe seleccionar un tipo de operación referente a efectivo de caja')
        return
      }

      if(this.operacion.RequiereCodigo && (''+this.operacion.NombreCodigo).trim()==''){
        this.$toasted.show('Debe ingresar el nombre de código para la operación')
        return
      }

      if(this.operacion.Calculo){
        if(this.operacion.ComisionFija && !(this.operacion.MontoComision > 0)){
          this.$toasted.show('Ingrese el monto de la comisión fija')
          return
        }

        if(!this.operacion.ComisionFija && (!(this.operacion.MontoComision > 0) || !(this.operacion.MontoMax > 0))){
          this.$toasted.show('Debe ingresar un monto de comisión y un monto de cada cuanto cobrar comisión')
          return
        }

      }

      //reniciar valores
      if(!this.operacion.Calculo){
        this.operacion.MontoComision = 0
        this.operacion.ComisionFija = 0
        this.operacion.MontoMax = 0
      }


      this.operacion.NombreCodigo = (this.operacion.RequiereCodigo?(''+this.operacion.NombreCodigo).trim().toUpperCase():'')
      this.operacion.Operacion = (''+this.operacion.Operacion).trim().toUpperCase()
      this.operacion.IdEntidad = this.idEntidad

      try {
          let repuesta = await this.axios.post(`${this.API}agente/entidad/operacion`, this.operacion);

          if (repuesta.data.error) {
            this.$toasted.show(repuesta.data.error)
            return
          } else {
            this.$toasted.success('Operacion guardada correctamente')
            if (this.cbGuardar) {
              this.cbGuardar()
            }
            this.$refs.operacionForm.hide()
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
#modalConfigOperacion .modal-dialog{
  /*max-width: 87% !important;*/
}
</style>
