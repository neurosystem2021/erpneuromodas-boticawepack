<template>
  <div>
    <b-btn v-if="showButton" id="popoverMarcaForm" variant="primary"><b>+</b></b-btn>
    <!-- Modal Component -->

    <b-popover ref="popover" target="popoverMarcaForm" triggers="focus" title="Nueva Marca">
      <b-form-group>
        <label>Marca:</label>
        <b-form-input v-model="marca.ProductoMarca"
          @keydown.enter.native="guardarMarca"
          class="upper"
          type="text"
          value=""
          ></b-form-input>
      </b-form-group>
      <b-btn  size="sm" variant="success" @click="guardarMarca">Guardar</b-btn>
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
      marca: {},
      show: false
    }
  },
  props: ['showButton', 'cbGuardar'],
  methods: {
    guardarMarca () {
      if (!this.marca.ProductoMarca) {
        return
      }

      this.marca.ProductoMarca = this.marca.ProductoMarca.toUpperCase()

      this.axios.post(this.API + 'marcas', this.marca)
      .then((response) => {
        this.$toasted.show('Marca guardada correctamente')
        if (this.cbGuardar) {
          this.cbGuardar({
            IdProductoMarca: response.data.insertId,
            ProductoMarca: response.data.ProductoMarca
          })
        }
        this.marca.ProductoMarca = ''
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
