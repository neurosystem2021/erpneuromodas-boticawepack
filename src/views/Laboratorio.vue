<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
   
          <b-col md="6" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
            <b-col md="6" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
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
            id="tabla-ventas" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">

                <template slot="detalles" slot-scope="row">
                  <b-button size="sm" @click.stop="row.toggleDetails" class="mr-2">
                    {{ row.detailsShowing ? '-' : '+'}}
                  </b-button>
                </template>
                 <template slot="row-details" slot-scope="row">

                  <b-card>
                    <b-row class="mb-2">
                  <table class="table b-table table-sm prod-detalle">
                    <thead>
                      <tr>
                        <th>Id</th>
                        <th>Codigo</th>
                        <th width="45%">Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Descuento</th>
                        <th v-if="row.item.TieneIgv">Subtotal</th>
                        <th v-if="row.item.TieneIgv">IGV</th>
                        <th>Total</th>
                        <th></th>
                        <th></th>
                        <th></th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in getDetalles(row.item.idDocVenta)" :key="index">
                        <td>{{ item.IdDocVentaDet}}</td>
                        <td>{{ item.CodigoBarra }}</td>
                        <td>{{ item.Producto }}</td>
                        <td>{{ item.Cantidad }}</td>
                        <td>{{ item.Precio }}</td>
                        <td>{{ item.Descuento }}</td>
                        <td v-if="row.item.TieneIgv">
                          {{ item.Subtotal - (item.Subtotal * 0.18) | redondear }}
                        </td>
                        <td v-if="row.item.TieneIgv">
                          {{ item.Subtotal * 0.18 | redondear}}
                        </td>
                        <td>{{ item.Subtotal | redondear }}</td>
                        <td v-if="item.EsAnalisis" ><a class="btn btn-success" target="_blank" :href="'/api/index.php/resultado/ingreso/' + item.IdDocVentaDet + ''">INGRESAR RESULTADO</a></td>
                        <td v-if="item.EsAnalisis"><a class="btn btn-info" target="_blank" :href="'/api/index.php/resultado/descarga/' + item.IdDocVentaDet + ''"><i class="fas fa-file-excel"></i> DESCARGAR</a></td>
                        <td v-if="item.EsAnalisis"><a class="btn btn-primary" target="_blank" :href="'/api/index.php/resultado/ingreso/' + item.IdDocVentaDet + '?imprimir=1'"> IMPRIMIR</a></td>
                      </tr>
                    </tbody>
                  </table>
                    </b-row>
                  </b-card>
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
    this.axios.get(this.API + 'ventas/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter:'',
      filters: { },
      sortBy: 'idDocVenta',
      sortDesc: true,
      productoSelect: {},
      items: [],
      itemsDetalle: [],
      isBusy: false,
      fields: [
             { key: 'detalles', label: '' },
            { key: 'idDocVenta', label: 'ID', sortable: true },
            { key: 'Correlativo', sortable: true },
            { key: 'FechaDoc', sortable: true },
            { key: 'DniRuc', sortable: true },
            { key: 'TipoDoc', sortable: true },
            { key: 'Cliente', sortable: true },
            { key: 'Total', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
    watch: {
    items (items) {
      if (items.length){
        this.axios.get(this.API + 'ventas/detalle', {
           params: {
             idDocVentas: items.map((item) => item.idDocVenta),
           }
         }).then((response) => {
              this.itemsDetalle = response.data
              this.items.map((item) => {
                item.productos = this.getDetalles(item.idDocVenta)
                return item
              })
         })
         .catch((error) => {
           console.log(error)
         })
      }
    }
  },
  methods: {
   
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    //  getVentas() {
    //     this.axios.get(this.API + 'ventas', {
    //      params: {
    //        fechaInicio: this.fechaInicio,
    //        fechaFin: this.fechaFin
    //      }
    //    }).then((response) => {
    //      this.ventasAcumuladas = response.data.total
    //    })
    // },
    getDetalles (idDocVenta) {

      let detalles = this.itemsDetalle.filter(item => item.IdDocVenta == idDocVenta)
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles
    },
      rowClick (item, index) {
        this.productoSelect = item
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaVentas.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'ventas?noFecha=1&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
  }
}
</script>

<style>

</style>
