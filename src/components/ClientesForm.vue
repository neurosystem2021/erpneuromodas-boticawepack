<template>
  <div>
   <b-btn v-b-modal="idModal || 'modal1'" variant="primary">{{ txtButton || 'Nuevo' }}</b-btn>
    <!-- Modal Component -->
  <b-form @submit="guardarCliente" ref="form">
    <b-modal :id="idModal || 'modal1'" title="Nuevo Cliente" size="lg" ref="clienteForm" v-model="show" @shown="shownClienteForm">
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
            <label>Nombre Comercial:</label>
            <b-form-input 
                required="required"
                v-model="cliente.NombreComercial"
                type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección 1:</label>
            <b-form-input 
              v-model="cliente.Direccion"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección 2:</label>
            <b-form-input 
              v-model="cliente.Direccion2"
              type="text"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección 3:</label>
            <b-form-input 
              v-model="cliente.Direccion3"
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
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>Usuario Responsable: <span class="text-danger">*</span></label>
            <b-form-select
              required
              v-model="cliente.IdUsuarioAsignado">
              <option value="" disabled>-- Seleccione un usuario --</option>
              <option
                v-for="u in usuariosActivos"
                :key="u.IdUsuarioAsignado"
                :value="u.IdUsuarioAsignado">
                {{ u.NombreUsuario || u.Usuario }}
              </option>
            </b-form-select>
          </b-form-group>
        </b-col>
      </b-row>
      <b-row>
        <b-col>
          <b-form-checkbox id="checkbox1"
                     v-model="cliente.EsVIP"
                     :value="1"
                     :unchecked-value="0">
            Marcar si es un cliente VIP
          </b-form-checkbox>
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
      usuariosActivos: [],
      cliente: {
        Cliente: '',
        DniRuc: '',
        Direccion: '',
         Direccion2: '',
        Direccion3: '',
        Telefono: '',
        Email: '',
        IdUsuarioAsignado: ''
      }
    }
  },
  props: ['idModal', 'newDniRuc', 'editCliente', 'txtButton'],
  methods: {
    shownClienteForm () {
      this.cargarUsuariosActivos()
      // this.cliente.DniRuc = this.$parent.$parent.newDniRuc
      // this.cliente.Direccion = ''
      // this.cliente.Cliente = ''
      // this.cliente.NombreComercial = ''
      // this.cliente.Telefono = ''
      // this.cliente.Email = ''
      if (this.editCliente && this.editCliente.IdCliente) {
        this.cliente = this.editCliente
      } else {
        this.cliente.DniRuc = this.$parent.$parent.newDniRuc
      }
    },
    cargarUsuariosActivos () {
      this.axios.get(this.API + 'usuarios-activos')
        .then((response) => { this.usuariosActivos = response.data })
        .catch((error) => { console.log(error) })
    },
    consultarDNIRUC () {
      var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc))
      if (!esNumerico) {
          this.$toasted.show('El DNI/RUC debe ser numérico, editar cliente.')
          return
      }

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
                    this.cliente.NombreComercial =  data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno
                } else {
                    this.cliente.Cliente = data.razonSocial
                    this.cliente.NombreComercial = data.razonSocial
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

     var esNumerico = Number.isInteger(parseInt(this.cliente.DniRuc))
      if (!esNumerico) {
          this.$toasted.show('El DNI/RUC debe ser numérico, editar cliente.')
          return
      }
      var esDocumento = this.cliente.DniRuc.length > 7 && this.cliente.DniRuc.length <= 11? true : false
      if (!esDocumento) {
          this.$toasted.show('El Número de Documento es de 8 ó 11 digitos')
          return
      }
      if (!this.cliente.IdUsuarioAsignado) {
          this.$toasted.show('Debe seleccionar el Usuario responsable de este cliente')
          return
      }

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
<style scoped>
  input[type=number]::-webkit-inner-spin-button, 
  input[type=number]::-webkit-outer-spin-button { 
    -webkit-appearance: none; 
    margin: 0; 
  }
</style>
