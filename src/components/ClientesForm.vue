<template>
  <div>
    <b-btn @click="show = true" variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarCliente" ref="form">    
    <b-modal class="modal-cliente" no-close-on-backdrop title="Nuevo Cliente" size="lg" ref="clienteForm" v-if="show" v-model="show" @shown="shownClienteForm">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>DNI / RUC:</label>
            <b-input-group>
              <b-form-input 
                required="required"
                v-model="cliente.DniRuc"></b-form-input>
              <b-input-group-append>
                <b-btn variant="info" @click="consultarDNIRUC">Buscar </b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Nombre del Cliente:</label>
            <b-form-input 
                required="required"
                v-model="cliente.Cliente"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección:</label>
            <b-form-input 
              v-model="cliente.Direccion"
              type="text"></b-form-input>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
          <b-col>
            <b-form-group>
                <label>Teléfono:</label>
                <b-form-input 
                v-model="cliente.Telefono"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
                <label>Correo Electrónico:</label>
                <b-form-input 
                v-model="cliente.Email"
                type="email"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
                <label>Nacionalidad:</label>
                <b-form-input 
                v-model="cliente.Nacionalidad"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
      </b-row>
      <b-row>
          <b-col>
            <b-form-group>
                <label>Sexo:</label>
                <b-form-select v-model="cliente.Sexo" :options="['M', 'F']" class="mb-3" />
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
                <label>Ocupación:</label>
                <b-form-input 
                v-model="cliente.Ocupacion"
                type="text"></b-form-input>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group>
                <label>Fecha Nacimiento:</label>
                <b-form-input 
                v-model="cliente.FechaNacimiento"
                type="date"></b-form-input>
            </b-form-group>
          </b-col>
      </b-row>
      
      <div slot="modal-footer" >
        <b-btn  variant="primary" type="submit" :disabled="isSaving">
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
  mounted () {
    document.body.appendChild(this.$refs.form)
  },
  data() {
    return {
      API: config.API,
      show: false,
      cliente: {
        Cliente: '',
        DniRuc: '',
        Direccion: '',
        Telefono: '',
        Email: '',
        Sexo: '',
        Ocupacion:'',
        FechaNacimiento: '',
        Nacionalidad: ''
      },
      isSaving: false
    }
  },
  props: ['newDniRuc', 'index'],
  computed: {
  },
  methods: {
    shownClienteForm () {
      console.log('show')
      this.cliente.DniRuc = this.$parent.$parent.newDniRuc
      this.cliente.Direccion = ''
      this.cliente.Cliente = ''
      this.cliente.Telefono = ''
      this.cliente.Email = ''
      this.cliente.Sexo = ''
      this.cliente.Ocupacion = ''
      this.cliente.FechaNacimiento = ''
      this.cliente.Nacionalidad = ''
    },
    consultarDNIRUC () {
        let tipo = this.cliente.DniRuc.length > 8 ? 'RUC' : 'DNI'

        // this.axios.get(this.API + 'consultarRUC', {
        //   params: {
        //         type: tipo,
        //         numero: this.cliente.DniRuc
        //     }
        // })
        this.axios.get('https://www.facturacionelectronica.us/facturacion/controller/ws_consulta_rucdni_v2.php?usuario=20573027125&password=5i573m45&documento=' + tipo + "&nro_documento=" + this.cliente.DniRuc).then((response) => {
            if (response.data) {
                this.$toasted.show('Cliente encontrado, se llenarán sus datos')                  
                var data = response.data.result
                if (tipo == 'DNI') {
                    this.cliente.Cliente = data.Nombre + ' ' + data.Paterno + ' ' + data.Materno
                } else {
                    this.cliente.Cliente = data.RazonSocial
                    this.cliente.Direccion = data.Direccion
                    // this.cliente.Telefono = data.Telefono
                }
            } else {
                this.$toasted.show('No se encontró el Usuario')                
            }
            
        })
        .catch((error) => {
            this.$toasted.show('No se encontró el Usuario')        
            console.log(error)
        })
    },
    guardarCliente(evt) {
        evt.preventDefault();
        this.isSaving = true;

      this.axios.post(this.API + 'clientes', this.cliente)
      .then((response) => {
        this.$emit('onAddCliente', response.data)
        this.$toasted.show('Cliente guardado correctamente')
        this.$refs.clienteForm.hide()
        this.isSaving = false;
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        this.isSaving = false
      })
    }
  }
}
</script>
<style scoped>


</style>
