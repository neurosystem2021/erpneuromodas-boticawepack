<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="6" class="my-1">  
            <b-row>
              <b-form-group v-if="false">
                <label>Código de barra:</label>
                <input ref="inputCodigoBarra" class="form-control"/>
              </b-form-group>  
                 
              <b-form-group class="ml-2">
                <label>Buscar por nombre:</label>
                <b-input-group>
                  <b-form-input v-model="filter" placeholder="Ingrese nombre" @input="currentPage = 1" />
                  <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-row>
          </b-col>
					<b-col md="4">
						<b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue="HUANUCO"
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
					</b-col>
          <b-col md="2" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group>
                  <label>Mostrar:</label>
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
             <!-- <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>-->
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"></b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br /><br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" :href="urlExportar" target="_blank" size="lg" >Exportar (Con Condición)</b-button>
            </b-col>
        </b-row>
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
      sortBy: 'ProveedorUltimo',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProducto', label: 'ID', sortable: true },
            { key: 'CodigoBarra', label: 'Codigo', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'StockMinimo', label: 'Stock Minimo', sortable: true },
            { key: 'stock', label: 'Stock', sortable: true },
            { key: 'ProveedorUltimo', label: 'Proveedor Ult.' , sortable: true},
            { key: 'TelefonoUltimo', label: 'Telf. Proveedor Ult.' },
            { key: 'ProveedorSec', label: 'Proveedor Sec.' , sortable: true},
            { key: 'ProveedorSecTelf', label: 'Telf. Proveedor Sec.' },
      ],
      totalRows: 50,
      perPage: 10,
			currentPage: 1,
			almacen: {
				IdAlmacen: 1
			},
      urlExportar:''
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
			let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&stockminimo=1&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
			url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      this.urlExportar  = this.API + 'reporte/stock/minimo?idAlmacen='+this.almacen.IdAlmacen+'&sortBy='+ctx.sortBy+'&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      let promise = this.axios.get( url )

			return promise.then((result) => {
				this.items = result.data
				//this.totalRows = this.items.length
				// Here we could override the busy state, setting isBusy to false
				// this.isBusy = false
        this.items.map(r=>{
          if(r.stock < r.StockMinimo){
            r._rowVariant='danger'
          }
        })
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