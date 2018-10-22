<template>
    <div>
      <br>
        <b-container class="bv-example-row">
          <b-row>
            <b-col md="4" class="my-1">
              <b-form-group>
                <label>Seleccionar Producto:</label>
                <b-input-group>
                  <autocomplete
                    ref="autocompleteProducto"
                    :url="API + 'productos'"
                    :onShouldRenderChild="renderChild"
                    anchor="Producto"
                    label="writer"
                    placeholder="Seleccionar Producto"
                    :on-select="setProducto"
                    :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                    :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-input-group>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="kardex.fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="kardex.fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
          </b-row>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  data () {
    return {
      API: config.API,
      kardex: {}
    }
  },
  methods: {
    setProducto (obj) {

    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },  
    renderChild(data) {
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / ${data.ProductoTalla}
      `
    },
  }
}
</script>
<style>
    

</style>