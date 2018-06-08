<template>
  <div>
    <b-btn v-if="showButton" id="popoverCategoriaForm" variant="primary"><b>+</b></b-btn>
    <!-- Modal Component -->

    <b-popover ref="popover" target="popoverCategoriaForm" triggers="focus" title="Nueva Categoría">
      <b-form-group>
        <label>Categoria:</label>
        <b-form-input v-model="categoria.ProductoCategoria"
          @keydown.enter.native="guardarCategoria"
          class="upper"
          type="text"
          value=""
          ></b-form-input>
      </b-form-group>
      <b-btn  size="sm" variant="success" @click="guardarCategoria">Guardar</b-btn>
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
      categoria: {},
      show: false
    }
  },
  props: ['showButton', 'cbGuardar'],
  methods: {
    guardarCategoria () {
      if (!this.categoria.ProductoCategoria) {
        return
      }

      this.categoria.ProductoCategoria = this.categoria.ProductoCategoria.toUpperCase()

      this.axios.post(this.API + 'categorias', this.categoria)
      .then((response) => {
        this.$toasted.show('Categoría guardada correctamente')
        if (this.cbGuardar) {
          this.cbGuardar({
            IdProductoCategoria: response.data.insertId,
            ProductoCategoria: response.data.ProductoCategoria
          })
        }
        this.categoria.ProductoCategoria = ''
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
