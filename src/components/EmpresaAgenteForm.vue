<template>
    <div>
      <b-form @submit="guardarOperacion" @keydown.enter="keyDownInput">
        <b-modal :no-close-on-esc="true" no-close-on-backdrop id="modalConfigOpe" :title="title" size="lg" ref="empresaAgenteForm" v-model="show">
          <b-row>
            <b-col md="6">
              <b-form-group>
                <label>Empresa:</label>
                <b-input-group>
                  <autocomplete
                    required="required"
                    :url="API + 'agente/empresa'"
                    ref="inputEmpresa"
                    anchor="Empresa"
                    label="writer"
                    style="text-transform: uppercase;"
                    :placeholder.sync="obj.empresa.Empresa"
                    :on-select="setEmpresa"
                    :onFocus="focusAutocomplete.bind(null, 'inputEmpresa')"                
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                  <b-input-group-append>
                    <b-btn variant="primary" @click="crearEmpresa"><b>Guardar</b></b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>
  
            <b-col md="6">
                <b-form-group>
                    <label>Categoria:</label>
                    <b-input-group>
                    <autocomplete
                        required="required"
                        :url="API + 'agente/empresa/categoria'"
                        ref="inputCategoria"
                        anchor="CategoriaEmpresa"
                        label="writer"
                        style="text-transform: uppercase;"
                        :placeholder.sync="obj.categoria.Categoria"
                        :on-select="setCategoria"
                        :onFocus="focusAutocomplete.bind(null, 'inputCategoria')"                
                        :classes="{input: 'form-control upper'}">
                    </autocomplete>
                    <b-input-group-append>
                        <b-btn variant="primary" @click="crearCategoria"><b>Guardar</b></b-btn>
                    </b-input-group-append>
                    </b-input-group>
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
        obj: {},
        idEntidad: null
      }
    },
    beforeMount () {
      this.initForm()
    },
    props: ['entidadSelect','cbGuardar'],
    watch: {
      show (val) {
        if(val){
          this.idEntidad = this.entidadSelect.IdEntidad || null
            this.title = 'Nueva empresa categorizada'
            this.clearForm()
        }
      }
    },
    methods: {
      clearForm () {
        this.$refs.inputEmpresa.setValue('')
        this.$refs.inputEmpresa.$refs.input.disabled = false
        this.$refs.inputEmpresa.disabled = false
        this.$refs.inputCategoria.setValue('')
        this.$refs.inputCategoria.$refs.input.disabled = false
        this.$refs.inputCategoria.disabled = false
        this.initForm()
      },
      initForm () {
        this.obj = {
          categoria: {},
          empresa: {},
        }
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      setCategoria (obj) {
        this.obj.categoria = obj
        this.$refs.inputCategoria.setValue(obj.CategoriaEmpresa)
      },
      crearCategoria () {
        if (this.$refs.inputCategoria.type) {
          let categoria = {}
          categoria.CategoriaEmpresa = this.$refs.inputCategoria.type.toUpperCase()
          categoria.CategoriaEmpresa = (''+categoria.CategoriaEmpresa).trim()
    
          this.axios.post(this.API + 'agente/empresa/categoria', categoria)
          .then((response) => {
            this.$toasted.success('Categoria creada correctamente')
            this.setCategoria({
                IdCategoriaEmpresa: response.data.insertId,
                CategoriaEmpresa: response.data.CategoriaEmpresa
            })
          })
          .catch((error) => {
            console.log(error)
            this.$toasted.error('La categoria ingresada ya existe, seleccionela', { duration:3000 })
          })
        }
      },
      setEmpresa (obj) {
        this.obj.empresa = obj
        this.$refs.inputEmpresa.setValue(obj.Empresa)
      },
      crearEmpresa () {
        if (this.$refs.inputEmpresa.type) {
          let empresa = {}
          empresa.Empresa = this.$refs.inputEmpresa.type.toUpperCase()
          empresa.Empresa = (''+empresa.Empresa).trim()
    
          this.axios.post(this.API + 'agente/empresa', empresa)
          .then((response) => {
            this.$toasted.success('Empresa creada correctamente')
            this.setEmpresa({
                IdEmpresa: response.data.insertId,
                Empresa: response.data.Empresa
            })
          })
          .catch((error) => {
            console.log(error)
            this.$toasted.error('La empresa ingresada ya existe, seleccionela', { duration:3000 })
          })
        }
      },
      async guardarOperacion(e) {
        e.preventDefault();

        if(this.idEntidad == null){
            this.$toasted.show('Ha ocurrido un error, porfavor actualice la pagina')
            return
        }

        if(!(this.obj.categoria.IdCategoriaEmpresa>0)){
            this.$toasted.show('Debe seleccionar una categoria')
            return
        }

        if(!(this.obj.empresa.IdEmpresa>0)){
            this.$toasted.show('Debe seleccionar una empresa')
            return
        }

        this.obj.idEntidad = this.idEntidad

        try {
            let repuesta = await this.axios.post(`${this.API}agente/entidad/empresa`, this.obj);

            if (repuesta.data.error) {
                this.$toasted.show(repuesta.data.error,{duration:3000})
                return
            } else {
                this.$toasted.success('Empresa guardada correctamente')
                if (this.cbGuardar) {
                this.cbGuardar()
                }
                this.$refs.empresaAgenteForm.hide()
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
  #modalConfigOpe .modal-dialog{
    /*max-width: 87% !important;*/
  }
  </style>
  