<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="4" class="my-1">
            <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue="PRINCIPAL"
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
                <b-form-group>
                  <label>Fecha Inicio:</label>
                  <b-form-input v-model="fechaInicio"
                    type="date"
                    placeholder=""
                    @input="val => generateFilter('fechaInicio', val) "></b-form-input>
                </b-form-group>
          </b-col>
          <b-col md="4" class="my-1">
           <label>Buscar:</label>
              <b-form-group>
                  <b-input-group>
                    <b-form-input v-model="txtBuscar" placeholder="Buscar" @input="val => buscar(val)"/>
                    <b-input-group-append>
                      <b-btn  @click="limpiarFiltros">Limpiar</b-btn>
                    </b-input-group-append>
                  </b-input-group>
              </b-form-group>
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input v-model="fechaFin"
                  type="date"
                  placeholder=""
                  @input="val => generateFilter('fechaFin', val) "></b-form-input>
              </b-form-group>
            <br />
          </b-col>
          <b-col md="4" class="my-1">
            <b-row>
            <b-col md="8">
              <label>Mostrar:</label>
              <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Documentos</p>
            </b-col>
            </b-row>
          </b-col>

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
          
              <template slot="Reimprimir" slot-scope="data">
                  <a class="btn btn-success" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'">{{data.item.TipoDoc}}</a>
              </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br />
        <br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.cantidadFilas();
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'idDocVenta',
      filters: { },
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      sortDesc: true,
      productoSelect: {},
      items: [],
      txtBuscar: '',
      isBusy: false,
      fields: [
            { key: 'idDocVenta', label: 'ID', sortable: true },
            { key: 'Correlativo', sortable: true },
            { key: 'FechaDoc', sortable: true },
            { key: 'DniRuc', sortable: true },
            { key: 'TipoDoc', sortable: true },
            { key: 'Cliente', sortable: true },
            { key: 'Total', sortable: true },
            { key: 'Reimprimir' }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      almacen: {}
    }
  },
  methods: {
      rowClick (item, index) {
        this.productoSelect = item
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      cantidadFilas() {
        this.axios.get(this.API + 'ventas/count' + '?filter='+ (this.filter||'')).then((response) => {
          this.totalRows = response.data.total
        })
      },
      buscar (val) {
        this.generateFilter('buscar', this.txtBuscar)
      },
      limpiarFiltros() {
        this.filters = {}
        this.filter = ''
        this.txtBuscar = ''
        this.fechaInicio = this.$moment().format("YYYY-MM-DD");
        this.fechaFin = this.$moment().format("YYYY-MM-DD")

      },
      actualizarTabla() {
        this.$refs.tablaVentas.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true

      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      setAlmacen (obj) {
        this.almacen = obj
        Vue.nextTick(() => this.$refs.tablaVentas.refresh())
      },
      generateFilter (key, value) {
        this.filters[key] = value

        let filter = ''
        for (var key in this.filters) {
          filter += '&filter[' + key + ']=' + this.filters[key]
        }
        this.filter = filter
        // this.actualizarTabla();
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        
        if (!ctx.filter) {
          ctx.filter = '&filter[fechaInicio]=' + this.fechaInicio
          ctx.filter += '&filter[fechaFin]=' + this.fechaFin
        }
        let url = this.API + 'ventas?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        
        url += '&idAlmacen=' + (this.almacen.IdAlmacen || '1')
        

        let promise = this.axios.get( url )

        return promise.then((result) => {
          this.items = result.data
          this.cantidadFilas()
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
  }
}
</script>

<style>

</style>
