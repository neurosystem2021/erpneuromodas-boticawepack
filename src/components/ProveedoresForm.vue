<template>
<div>
  <b-btn v-b-modal.modal1 variant="primary">Nuevo</b-btn>
  <!-- Modal Component -->
  <b-modal id="modal1" title="Nuevo Proveedor" size="lg" ref="proveedorForm" v-model="show" @shown="shownProveedorForm" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
    <b-row>
      <b-col md="12" class="nst-editor-section nst-editor-section--identity">
        <h3><i class="fas fa-building" aria-hidden="true"></i> Identificación y contacto del proveedor</h3>
        <b-form-group>
          <label>RUC:</label>
          <b-input-group>
            <b-form-input 
              v-model="proveedor.Ruc"></b-form-input>
            <b-input-group-append>
              <b-btn variant="info" @click="consultarRUC">Buscar RUC</b-btn>
            </b-input-group-append>
          </b-input-group>
        </b-form-group>
        <b-form-group class="nst-field-wide">
          <label>Nombre del Proveedor:</label>
          <b-form-input 
            v-model="proveedor.Proveedor"
            type="text"></b-form-input>
        </b-form-group>
        <b-form-group>
          <label>Dirección:</label>
          <b-form-input 
            v-model="proveedor.Direccion"
            type="text"></b-form-input>
        </b-form-group>
        <b-form-group>
          <label>Teléfono:</label>
          <b-form-input 
            v-model="proveedor.Telefono"
            type="text"></b-form-input>
        </b-form-group>
      </b-col>
    </b-row>
    <div slot="modal-footer" >
      <b-btn  variant="primary" @click="guardarProveedor">
         Guardar
       </b-btn>
       <b-btn  variant="secondary" @click="show=false">
         Cerrar
       </b-btn>
     </div>
  </b-modal>
</div>
</template>
<script>
import config from '@/store/config.js'
export default {
  data() {
    return {
      API: config.API,
      show: false,
      proveedor: {
        Proveedor: '',
        Ruc: '',
        Direccion: '',
        Telefono: '',
        Observacion: '',
        FechaReg: null
      }
    }
  },
  props: ['newRuc'],
  methods: {
    shownProveedorForm () {
      this.proveedor.Ruc = this.$parent.$parent.newRuc
      this.proveedor.Direccion = ''
      this.proveedor.Proveedor = ''
      this.proveedor.Telefono = ''
    },
    consultarRUC() {
      this.axios.get(this.API + 'consultarRUC', {
          params: {
            type: 'RUC',
            numero: this.proveedor.Ruc
          }
      }).then((response) => {
          this.$toasted.show('Proveedor encontrado, se llenarán sus datos')                  
          var data = response.data
          this.proveedor.Proveedor = data.razonSocial
          this.proveedor.Direccion = data.direccion
      })
      .catch((error) => {
          this.$toasted.show('No se encontró el RUC')        
          console.log(error)
      })
    },
    guardarProveedor() {
      this.axios.post(this.API + 'proveedores', this.proveedor)
      .then((response) => {
        this.$toasted.success('Proveedor guardado correctamente')
        this.$refs.proveedorForm.hide()
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
