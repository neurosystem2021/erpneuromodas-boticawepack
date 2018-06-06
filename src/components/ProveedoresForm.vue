<template>
  <div>
    <b-btn v-b-modal.modal1 variant="primary">Nuevo</b-btn>
    <!-- Modal Component -->
    <b-modal id="modal1" title="Tabla" size="lg" ref="proveedorForm" v-model="show">
      <b-row>
        <b-col md="12">
          <b-form-group>
            <label>RUC:</label>
            <b-input-group>
              <b-form-input 
                v-model="proveedor.Ruc"
                placeholder="Número RUC"></b-form-input>
              <b-input-group-append>
                <b-btn variant="info" @click="consultarRUC">Buscar RUC</b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
          <b-form-group>
            <label>Nombre del Proveedor:</label>
            <b-form-input 
              v-model="proveedor.Proveedor"
              type="text"
              placeholder="Nombre del Proveedor"></b-form-input>
          </b-form-group>
          <b-form-group>
            <label>Dirección:</label>
            <b-form-input 
              v-model="proveedor.Direccion"
              type="text"
              placeholder="Dirección"></b-form-input>
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
export default {
  mounted() {
    

  },
  data() {
    return {
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
  methods: {
    consultarRUC() {
      this.axios.get('http://boticav1.local/api/index.php/consultarRUC', {
          params: {
            type: 'RUC',
            numero: this.proveedor.Ruc
          }
      }).then((response) => {
          var data = response.data.result
          this.proveedor.Proveedor = data.RazonSocial
          this.proveedor.Direccion = data.Direccion
      })
      .catch(function (error) {
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')        
          console.log(error)
      })
    },
    guardarProveedor() {
      this.axios.post('http://boticav1.local/api/index.php/proveedores', this.proveedor)
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
