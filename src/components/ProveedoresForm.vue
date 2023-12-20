<template>
  <div>
    <b-btn v-b-modal.modal1 variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-modal id="modal1" title="Nuevo Proveedor" size="lg" ref="proveedorForm" v-model="show" @shown="shownProveedorForm">
      <b-row>
        <b-col md="12">
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
          <b-form-group>
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
        </b-col>
      </b-row>
      <div slot="modal-footer" >
        <b-btn  variant="primary" @click="guardarProveedor">
           Guardar
         </b-btn>
         <b-btn  variant="secondary" @click="show=false">
           Close
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
    },
    consultarRUC() {
      this.axios.get(this.API + 'consultarRUC', {
          params: {
            type: 'RUC',
            numero: this.proveedor.Ruc
          }
        }).then((response) => {              
          var data = response.data
          if(data.success==false){
            this.$toasted.show('No se encontró el RUC')   
          }else{
            this.$toasted.show('Proveedor encontrado, se llenarán sus datos') 
            this.proveedor.Proveedor = data.razonSocial
            this.proveedor.Direccion = data.direccion
          }
      })
      .catch((error) => {
          this.$toasted.show('No se encontró el RUC')        
          console.log(error)
      })
    },
    guardarProveedor() {
      this.axios.post(this.API + 'proveedores', this.proveedor)
      .then((response) => {
        this.$toasted.show('Proveedor guardado correctamente')
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
