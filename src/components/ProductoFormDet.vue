<template>
  <div v-if="show">
    <b-form-group>
      <label>Agrega Subproductos:</label>
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
          <td><input type="number" class="form-control" v-model="prod.Cantidad" requiredv @input="emitUpdate" /></td>
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
    console.log("hola entra");
  },
  data () {
    return {
      show: false,
      API: config.API,
      productos: []
    }
  },
  props: ['idProducto'],
  methods: {
    emitUpdate () {
      this.$emit('onUpdateProductos', this.productos)
    },
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
        <b>${data.IdProducto} - ${data.Producto} - ${data.CodigoBarra}</b>
      `
    },
    addProducto (data) {
      let index = this.productos.findIndex(el => el.IdProductoDet == data.IdProducto)

      if (index >= 0) {
        this.$toasted.show('El producto ya está agregado')
        return
      }

      if (this.idProducto == data.IdProducto) {
        this.$toasted.show('No puede agregar el mismo producto que esta editando')
        return
      }

      this.axios.get(this.API + 'productos/espadre', {params: {
        idProducto: data.IdProducto
      }}).then(res => {
        let esPadre = res.data.esPadre
        if  (esPadre) {
          this.$toasted.show('No puede agregar un producto padre')
        } else {
          data.IdProductoDet = data.IdProducto
          this.productos.push(data)
          this.emitUpdate()
        }
      })

      /*if (index < 0 && this.idProducto !== data.IdProducto && !data.EsPadre ) {
        data.IdProductoDet = data.IdProducto
        this.productos.push(data)
      } else {
        this.$toasted.show('no puede agregar este producto')
      }*/
    },
    quitarProducto (prod) {
      let index = this.productos.findIndex(el => el.IdProductoDet == prod.IdProductoDet)
      this.productos.splice(index, 1)
      this.emitUpdate()
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
