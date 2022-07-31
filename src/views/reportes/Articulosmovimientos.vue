<template>
    <div>
      <br>
        <b-container fluid style="width: calc(100% - 50px)" class="bv-example-row">
          <b-row>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Almacen:</label>
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3">
              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Productos</p>
            </b-col>
            <b-col md="3" class="my-1">
              <b-button variant="success" size="sm" @click="actualizarTabla">Actualizar Tabla</b-button>
              <hr>
              <b-button variant="success" size="sm" :href="hrefXls" target="_blank">Exportar Excel</b-button>
            </b-col>
          </b-row>
          <b-row>
            <b-table small
            ref="tablaVentas"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">

              <template slot="MontoVentas" slot-scope="data">
                    {{ data.item.MontoVentas ?  data.item.MontoVentas.toFixed(2):0 }}
              </template>
              <template slot="MontoCompras" slot-scope="data">
                    {{ data.item.MontoCompras ?  data.item.MontoCompras.toFixed(2):0 }}
              </template>
            </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
          </b-row>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  mounted () {
    this.loadFirstAlmacen()
    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data () {
    return {
      API: config.API,
      almacen: {},
      productos: [],
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      pageOptions: [ 100, 200, 300, 400, 500 ],
      filter: null,
      sortBy: 'MontoVentas',
      sortDesc: true,
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProducto', label: 'ID', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca',label:'Lab.', sortable: true },
            { key: 'ProductoCategoria',label:'Categoria', sortable: true },
            { key: 'ProductoMedicion',label:'Medicion', sortable: true },
            { key: 'TotalVentas', sortable: true,class:'table-success' },
            { key: 'CantidadVentas', sortable: true,class:'table-success' },
            { key: 'MontoVentas', sortable: true,class:'table-success' },
            { key: 'TotalCompras', sortable: true,class:'table-info' },
            { key: 'CantidadCompras', sortable: true,class:'table-info' },
            { key: 'MontoCompras', sortable: true,class:'table-info' }
      ],
      totalRows: 50,
      perPage: 100,
      currentPage: 1
    }
  },
  computed: {
    hrefXls () {
      return '/api/index.php/reporte/articulosmovimientos?idAlmacen=' + this.almacen.IdAlmacen +'&fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta;
    }
  },
  methods: {
      rowClick (item, index) {
        //this.productoSelect = item
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaVentas.refresh()
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'productos/masrotacion?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )+'&fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta+'&idAlmacen='+(this.almacen.IdAlmacen || 1)
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
    },
    loadFirstAlmacen () {
      this.axios.get(this.API + 'almacenes/primero')
        .then(res => {
          this.$refs.inputAlmacen.setValue(res.data.Almacen)
          this.almacen = res.data
        })
    },
    setAlmacen (obj) {
      this.almacen = obj
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
  }
}
</script>
<style>
    
</style>