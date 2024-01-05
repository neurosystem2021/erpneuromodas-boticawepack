<template>
    <div>
      <b-form @submit="guardarMovimiento" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalMovimiento" :title="title" size="lg" ref="movimientoForm" v-model="show">
          <b-row>
            <b-col md="6">
                <b-form-group>
                    <label>Tipo de Movimiento</label>
                    <b-form-select v-model="movimiento.Tipo" :options="itemsTipos" v-on:change="setTipo($event)" size="md"  required="required"/>
                </b-form-group>
              <hr>
            </b-col>
  
            <b-col md="6">
                <b-form-group>
                <label>Importe</label>
                <input   
                    required="required"
                    v-model="movimiento.Importe"
                    type="number"
                    class="form-control"
                    step="0.1"
                    />
                </b-form-group>
  
            </b-col>
            <b-col md="12">
                <b-form-group>
                    <label>Concepto:</label>
                    <b-form-textarea 
                    rows="4"
                    v-model="movimiento.Concepto"
                    required="required"
                    style="text-transform: uppercase;"
                    />
                </b-form-group>
            </b-col>
          </b-row>
          <div slot="modal-footer" >
            <b-btn  variant="success" type="submit">
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
        movimiento: {},
        usuario: null,
        itemsTipos: [
          { value: null, text: '== SELECCIONE ==' },
          { value: 0, text: 'INGRESO' },
          { value: 1, text: 'EGRESO' },
        ],
      }
    },
    beforeMount () {
      this.initForm()
    },
    props: ['usuarioReg','cbGuardar'],
    watch: {
      show (val) {
        if(val){
          this.usuario = this.usuarioReg || null
            this.title = 'Nuevo Movimiento de Caja'
            this.clearForm()
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
        this.movimiento = {
          Concepto: '',
          Importe: null,
          Tipo: null
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      async guardarMovimiento(e) {
        e.preventDefault();
  
        if(this.usuario == null){
          this.$toasted.show('Ha ocurrido un error, porfavor actualice la pagina')
          return
        }
  
        if(this.movimiento.Tipo == null){
          this.$toasted.show('Debe seleccionar un tipo de operación referente a efectivo de caja')
          return
        }
  
        if(!(this.movimiento.Importe > 0)){
          this.$toasted.show('Debe ingresar un importe mayor a cero')
          return
        }

        if((''+this.movimiento.Concepto).trim()==''){
            this.$toasted.show('Ingrese un concepto del movimiento')
            return
        }
  
        this.movimiento.Usuario = this.usuario
        this.movimiento.Concepto = (''+this.movimiento.Concepto).trim().toUpperCase()
        try {
            let repuesta = await this.axios.post(`${this.API}agente/caja`, this.movimiento);
  
            if (repuesta.data.error) {
              this.$toasted.error(repuesta.data.error)
              return
            } else {
              this.$toasted.success('Movimiento guardado correctamente')
              if (this.cbGuardar) {
                this.cbGuardar()
              }
              this.$refs.movimientoForm.hide()
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
  #modalMovimiento .modal-dialog{
    /*max-width: 87% !important;*/
  }
  </style>
  