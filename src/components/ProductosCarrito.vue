<template>
  <b-row>
    <b-col md="12">
      <b-form-group>
        <label>Agregar Productos:</label>
        <b-input-group>
          <autocomplete
            :options="filtrados"
            url="#"
            :onShouldRenderChild="renderChild"
            :onShouldGetData="getData"
            anchor="Producto"
            label="writer"
            placeholder="Agregar Producto"
            :on-select="setProducto"
            :classes="{input: 'form-control'}">
          </autocomplete>
          <b-input-group-append>
            <!--<proveedores-tabla></proveedores-tabla>-->
            <producto-form></producto-form>
          </b-input-group-append>
        </b-input-group>
      </b-form-group>
      <b-table small responsive  striped hover :items="productos" :fields="fields">
        <template slot="cantidad" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].cantidad"
            type="number"
            min="0"
            placeholder="Cantidad"></b-form-input>
        </template>
        <template slot="precio" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].precio"
            type="number"
            min="0"
            placeholder="Precio"
            @input="setNuevoPrecioContado(data.index)"></b-form-input>
        </template>
        <template slot="subtotal" slot-scope="data">
          {{ getSubtotal(data.index) }}
        </template>
        <template slot="igv" slot-scope="data">
          <b-form-checkbox
            v-model="productos[data.index].TieneIgv"
            :value="1"
            :unchecked-value="0">
            <div>{{ getIgv(data.index) }}</div>
          </b-form-checkbox>
        </template>
        <!--<template slot="flete" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].FLETE"
            type="number"
            min="0"
            placeholder="Flete"></b-form-input>
        </template>-->
        
        <template slot="lote" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].IdLote"
            type="text"
            min="0"
            placeholder="Lote"></b-form-input>
        </template>
        <template slot="precioVenta" slot-scope="data">
          <b-form-input
            v-model="productos[data.index].nuevoPrecioContado"
            type="text"
            placeholder="Precio Venta"></b-form-input>
        </template>
        <template slot="total" slot-scope="data">
          {{ getTotal(data.index) }}
        </template>
        <template slot="acciones" slot-scope="data">
          <b-button variant="danger" size="sm" @click="quitarProducto(data.index)">X</b-button>
        </template>
      </b-table>
    </b-col>
    <b-col md="12">
      <b-row align-h="end">
        <b-col md="2">
          <b>SubTotal</b>
          {{ sumatoriaSubTotal  }}
        </b-col>
        <b-col md="2">
          <b>IGV</b>
          {{ sumatoriaIgv }}
        </b-col>
        <!--<b-col md="2">
          <b>FLETE</b>
          
        </b-col>-->
        <b-col md="2">
          <b>TOTAL</b>
          {{ sumatoriaTotal }}
        </b-col>
      </b-row>
    </b-col>
    <br><br>
  </b-row>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import ProductoForm from './ProductoForm'

export default {
  mounted() {
  },
  data () {
    return {
      producto: {},
      fields: [
        { key: 'Producto' }, 
        { key: 'ProductoMarca', label: 'Marca' },
        { key: 'ProductoTalla', label: 'Talla' },
        { key: 'Genero' },
        { key: 'cantidad' },
        { key: 'precio', label: 'P/U' },
        { key: 'subtotal', label: 'SubTotal', variant: 'success' },
        { key: 'igv', label: 'IGV' },
        // { key: 'flete', label: 'FLETE' },
        { key: 'lote', label: 'Lote' },
        { key: 'precioVenta', label: 'Precio venta' },
        { key: 'total', label: 'Total', variant: 'danger' },
        { key: 'acciones', label: ''}
      ],
      items: []
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      filtrados: state => state.cart.productosFiltrados
    }),
    sumatoriaSubTotal () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.subtotal)
      })
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaTotal () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.total)
      })
      return this.$options.filters.redondear(sumatoria)
    },
    sumatoriaIgv () {
      let sumatoria = 0
      this.productos.map((value) => {
        sumatoria += parseFloat(value.igv)
      })
      return this.$options.filters.redondear(sumatoria)
    }
  },
  methods: {
    ...mapActions({
      add: 'agregarProducto',
      buscar: 'buscarProductos',
      eliminar: 'eliminarProducto'
    }),
    setProducto (obj) {
      //this.producto = obj
      obj.FLETE = 0
      obj.precio = 0
      obj.cantidad = 0
      obj.IdLote = 0
      obj.TieneIgv = 0
      obj.nuevoPrecioContado = 0
      this.add(obj)
      // Vue.set(this.productos, obj.IdProducto, obj)
    },
    renderChild(data) {
      return `
        <b>${data.Producto} - ${data.ProductoCategoria} - ${data.ProductoMarca} - ${data.ProductoTalla}</b>
      `
    },
    setNuevoPrecioContado (index) {
      let nuevoPrecio = parseFloat(this.productos[index].precio) + (this.productos[index].precio * (this.productos[index].PorcentajeUtilidad/100))
      nuevoPrecio = this.$options.filters.redondear(nuevoPrecio)
      Vue.set(this.productos[index], 'nuevoPrecioContado', nuevoPrecio)
      // this.productos[index].nuevoPrecioContado = nuevoPrecio
      // return nuevoPrecio 
    },
    getSubtotal(index) {
      let subtotal = this.$options.filters.redondear(this.productos[index].cantidad * this.productos[index].precio)
      Vue.set(this.productos[index], 'subtotal', subtotal)
      // this.productos[index].subtotal = subtotal
      return subtotal
    },
    getIgv (index) {
      let igv = 0
      if (this.productos[index].TieneIgv) {
        igv = this.$options.filters.redondear(this.productos[index].subtotal * 0.18)
      }

      Vue.set(this.productos[index], 'igv', igv)
      // this.productos[index].igv = igv
      return igv
    },
    getTotal (index) {
      let total = parseFloat(this.productos[index].subtotal)

      if (this.productos[index].FLETE) {
        total += parseFloat(this.productos[index].FLETE)
      }
      if (this.productos[index].TieneIgv) {
        total += parseFloat(this.productos[index].igv)
      }

      total =  this.$options.filters.redondear(total)
      Vue.set(this.productos[index], 'total', total)
      // this.productos[index].total = total
      return total
    },
    getData (value) {
      this.buscar(value)
    },
    quitarProducto (index) {
      this.eliminar(index)
    }
  },
  components: {
    ProductoForm
  }
}
</script>

<style>
</style>
