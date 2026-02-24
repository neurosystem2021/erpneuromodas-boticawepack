<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Ranking de Clientes</h2>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="4" class="my-1">
            <b-form-group>
                  <label>Buscar:</label>
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="4" class="my-1">
            <b-form-group>
              <label>Fecha Inicio:</label>
              <b-form-input
                v-model="fechaInicio"
                type="date"
                step="1"
                @input="val => $refs.tablaProductos.refresh()"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="4" class="my-1">
            <b-form-group>
              <label>Fecha Fin:</label>
              <b-form-input
                v-model="fechaFin"
                type="date"
                step="1"
                @input="val =>$refs.tablaProductos.refresh() "
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="3">
            <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue=""
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
          </b-col>
          <b-col md="2">
            <label>Mostrar:</label>
            <b-form-group>
              <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
            </b-form-group>
          </b-col>
          <b-col md="4">
            <label>Limpiar Filtros:</label>
            <b-form-group>
              <b-button @click="limpiarFiltros">Limpiar</b-button>
            </b-form-group>   
            <br>
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
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/rankingclientes?idalmacen=' + almacen.IdAlmacen +'&fechaDesde='+fechaInicio+'&fechaHasta='+fechaFin" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'ranking/clientes/count?idalmacen=' + this.almacen.IdAlmacen ).then((response) => {
      console.log(response)
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: '',
      sortBy: 'Total',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
            { key: 'DniRuc', label: 'DNI / RUC', sortable: true },
            { key: 'Cliente', label: 'Cliente', sortable: true },
            { key: 'FechaNacimiento', label: 'Fecha Nacimiento', sortable: true },
            { key: 'Puntos', label: 'Puntos', sortable: true },
            { key: 'Total', label: 'Total', sortable: true},
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      almacen: {
        IdAlmacen: ''
      },
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD")
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
    limpiarFiltros () {
      this.filter = ''
      this.$refs.inputAlmacen.setValue('')
      this.almacen.IdAlmacen = ''
      this.fechaInicio = this.$moment().format("YYYY-MM-DD")
      this.fechaFin = this.$moment().format("YYYY-MM-DD")
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
     //  this.getVentasAcumuladas()
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      // console.log(ctx.filter)
      // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
      // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      let promise = this.axios.get( this.API + 'ranking/clientes?idalmacen=' + this.almacen.IdAlmacen + '&fechaDesde='+this.fechaInicio+'&fechaHasta='+this.fechaFin + '&q=' + ctx.filter + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage )

      return promise.then((response) => {
        console.log(response)
        this.items = response.data
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
      // ProductoForm
  }
}
</script>

<style>

</style>