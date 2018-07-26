<template>
  <div>
    <b-form-group>
      <label>Agregar Productos:</label>
      <b-input-group>
        <autocomplete
          :url="API + 'productos'"
          ref="inputProducto"
          anchor="Producto"
          :onShouldRenderChild="renderChild"
          label="writer"
          placeholder="Seleccionar Producto"
          :on-select="addProducto"
          :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
          :classes="{input: 'form-control upper'}">
        </autocomplete>
      </b-input-group>
    </b-form-group>
    <table class="table b-table table-sm">
      <thead>
        <tr>
          <th>Id</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th class="text-center">Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(prod, index) in productos" :key="index">
          <td>{{ prod.IdProductoDet }}</td>
          <td>{{ prod.Producto }}</td>
          <td><input type="number" class="form-control" v-model="prod.Cantidad" required /></td>
          <td class="text-center"><b-button variant="danger" size="sm" @click="quitarProducto(prod)">X</b-button></td>
        </tr>
      </tbody>
    </table>
  </div>    
</template>
<script>
import config from '@/store/config.js'

export default {
  mounted () {
    this.getProductosDet()
  },
  data () {
    return {
      API: config.API,
      productos: []
    }
  },
  props: ['idProducto'],
  methods: {
    getProductosDet () {
      if (this.idProducto) {
        this.axios.get(this.API + 'productosdet', {
          params: {idProducto: this.idProducto}
        }).then(res => {
          this.productos = res.data
        }).catch(err => {
          console.log(err)
        })
      }
    },
    renderChild(data) {
      return `
        <b>${data.IdProducto} - ${data.Producto}</b>
      `
    },
    addProducto (data) {
      let index = this.productos.findIndex(el => el.IdProductoDet == data.IdProducto)
      if (index < 0 && this.idProducto !== data.IdProducto ) {
        data.IdProductoDet = data.IdProducto
        this.productos.push(data)
      }
    },
    quitarProducto (prod) {
      let index = this.productos.findIndex(el => el.IdProductoDet == prod.IdProductoDet)
      this.productos.splice(index, 1)
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
  }
}
</script>
<style>

</style>
