<template>
  <div>
    <b-btn v-b-modal.modal1 variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-form @submit="guardarCliente">    
    <b-modal id="modal1" title="Nuevo Cliente" size="lg" ref="clienteForm" v-model="show" @shown="shownClienteForm">
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
      </b-row>
      <div slot="modal-footer" >
        <b-btn  variant="primary" type="submit">
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
  data() {
    return {
      API: config.API,
      show: false,
      cliente: {
        Cliente: '',
        DniRuc: '',
        Direccion: '',
        Telefono: '',
        Email: ''
      }
    }
  },
  props: ['newDniRuc'],
  methods: {
    shownClienteForm () {
      this.cliente.DniRuc = this.$parent.$parent.newDniRuc
      this.cliente.Direccion = ''
      this.cliente.Cliente = ''
      this.cliente.Telefono = ''
      this.cliente.Email = ''
    },
    consultarDNIRUC () {
        let tipo = this.cliente.DniRuc.length > 8 ? 'RUC' : 'DNI'

        this.axios.get(this.API + 'consultarRUC', {
            params: {
                type: tipo,
                numero: this.cliente.DniRuc
            }
        }).then((response) => {
            if (response.data) {
                this.$toasted.show('Cliente encontrado, se llenarán sus datos')                  
                var data = response.data

                if (tipo == 'DNI') {
                  this.cliente.Cliente = data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno
                  this.cliente.Direccion = ''
                } else {
                  this.cliente.Cliente = data.razonSocial
                  this.cliente.Direccion = data.direccion
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

      this.axios.post(this.API + 'clientes', this.cliente)
      .then((response) => {
        this.$emit('onAddCliente', response.data)
        this.$toasted.show('Cliente guardado correctamente')
        this.$refs.clienteForm.hide()
      })
      .catch((error) => {
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style>

</style>
