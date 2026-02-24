<template>
  <div>
    <b-btn v-if="showButton" id="popoverModeloForm" variant="primary"><b>+</b></b-btn>
    <!-- Modal Component -->

    <b-popover ref="popover" target="popoverModeloForm" triggers="focus" title="Nuevo Modelo">
      <b-form-group>
        <label>Modelo:</label>
        <b-form-input v-model="modelo.ProductoModelo"
          @keydown.enter.native="guardarModelo"
          class="upper"
          type="text"
          value=""
          ></b-form-input>
      </b-form-group>
      <b-btn  size="sm" variant="success" @click="guardarModelo">Guardar</b-btn>
    </b-popover>
  </div>
</template>
<script>
import config from '@/store/config.js'

export default {
  data () {
    return {
      API: config.API,
      show: false,
      modelo: {},
      show: false
    }
  },
  props: ['showButton', 'cbGuardar'],
  methods: {
    guardarModelo () {
      if (!this.modelo.ProductoModelo) {
        return
      }

      this.modelo.ProductoModelo = this.modelo.ProductoModelo.toUpperCase()

      this.axios.post(this.API + 'modelos', this.modelo)
      .then((response) => {
        this.$toasted.show('Modelo guardado correctamente')
        if (this.cbGuardar) {
          this.cbGuardar({
            IdProductoModelo: response.data.insertId,
            ProductoModelo: response.data.ProductoModelo
          })
        }
        this.modelo.ProductoModelo = ''
      })
      .catch((error) => {
        console.log(error)
        this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    }
  }
}
</script>
<style>

</style>
