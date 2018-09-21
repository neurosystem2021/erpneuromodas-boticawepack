<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="4" class="my-1">
            <b-form-group>
                  <label>Fecha:</label>
							
              <b-input-group>
                <b-form-input type="date" @input="currentPage = 1" />
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>

        </b-row>
          <h1 class="text-center" >Registro de Huéspedes</h1>
        <b-row>
          <table>
            <thead>
              <tr><th>asd</th></tr>
            </thead>
            <tbody>
              <tr><td>asd</td></tr>
            </tbody>
          </table>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&filter[minimo]=1&count=1').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'Gen_Producto.IdProducto',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProducto', label: 'ID', sortable: true },
            { key: 'CodigoBarra', label: 'Codigo', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoMedicion', label: 'Medición', sortable: true },
            { key: 'stock', label: 'Stock', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
			currentPage: 1,
			almacen: {
				IdAlmacen: 1
			}
    }
  },
  methods: {
		focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
		},
		setAlmacen (obj) {
			this.almacen = obj
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
		myProvider (ctx) {
			// Here we don't set isBusy prop, so busy state will be handled by table itself
			// this.isBusy = true
			let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
			url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
			let promise = this.axios.get( url )

			return promise.then((result) => {
				this.items = result.data
				//this.totalRows = this.items.length
				// Here we could override the busy state, setting isBusy to false
				// this.isBusy = false
				return(this.items)
			}).catch(error => {
				// Here we could override the busy state, setting isBusy to false
				// this.isBusy = false
				// Returning an empty array, allows table to correctly handle busy state in case of error
				return []
			})
		}
  },
  components: {
      ProductoForm
  }
}
</script>

<style>

</style>