<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Ranking de Clientes</h2>
      <b-container class="bv-example-row">
        <b-row class="ranking-clientes-filtros">
          <b-col md="3" class="my-1">
            <b-form-group label="Desde:">
              <b-form-input type="date" v-model="fechaInicio" @change="aplicarFechas" />
            </b-form-group>
          </b-col>
          <b-col md="3" class="my-1">
            <b-form-group label="Hasta:">
              <b-form-input type="date" v-model="fechaFin" @change="aplicarFechas" />
            </b-form-group>
          </b-col>
          <b-col md="4" class="my-1">
            <b-form-group>
                  <label>Buscar:</label>
              
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="4">
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
          <b-col md="2" class="my-1">
            <b-row>
            <b-col md="12">

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
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" :href="exportUrl" target="_blank"><i class="fa fa-file-excel-o"></i> Exportar</b-button>
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
  mounted() { this.loadCount('') },
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
      fechaInicio: this.firstDayOfMonth(),
      fechaFin: this.today(),
      almacen: {
        IdAlmacen: 1
      }
    }
  },
  methods: {
    today () {
      const date = new Date(); date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); return date.toISOString().slice(0, 10)
    },
    firstDayOfMonth () {
      const date = new Date(); date.setDate(1); date.setMinutes(date.getMinutes() - date.getTimezoneOffset()); return date.toISOString().slice(0, 10)
    },
    loadCount (query) {
      return this.axios.get(this.API + 'ranking/clientes/count', { params: { idalmacen: this.almacen.IdAlmacen, fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, q: query || '' } }).then(response => { this.totalRows = Number(response.data.total || 0) })
    },
    aplicarFechas () {
      if (this.fechaInicio > this.fechaFin) { const value = this.fechaInicio; this.fechaInicio = this.fechaFin; this.fechaFin = value }
      this.currentPage = 1; this.loadCount(this.filter); this.$nextTick(() => this.$refs.tablaProductos.refresh())
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setAlmacen (obj) {
      this.almacen = obj
      this.currentPage = 1
      this.loadCount(this.filter)
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      // console.log(ctx.filter)
      // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
      // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      this.loadCount(ctx.filter)
      let promise = this.axios.get(this.API + 'ranking/clientes', { params: { idalmacen: this.almacen.IdAlmacen, q: ctx.filter || '', limit: ctx.perPage, page: ctx.currentPage, fechaInicio: this.fechaInicio, fechaFin: this.fechaFin } })

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
  computed: {
    exportUrl () { return '/api/index.php/reporte/rankingclientes?idalmacen=' + encodeURIComponent(this.almacen.IdAlmacen) + '&fechaInicio=' + encodeURIComponent(this.fechaInicio) + '&fechaFin=' + encodeURIComponent(this.fechaFin) }
  },
  components: {
      // ProductoForm
  }
}
</script>

<style>

</style>
