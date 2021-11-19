<template>
  <div>
      <b-container class="bv-example-row">

        <b-row>
          <b-col md="12" class="my-1">
            <b-row>
              <b-col md="4">
                <b-form-group >
                  <label>Fecha Referencia:</label>
                  <b-form-input
                    v-model="fechaReferencia"
                    type="date"
                    placeholder=""
                    @input="setFechaReferencia"></b-form-input>
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Estado">
                  <b-form-select v-model="estadoVenta" :options="optionsEstado" @input="$refs.tablaVentas.refresh()" />
                </b-form-group>
                <br />
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <b-row>
          <b-col md="12">
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

                <template slot="FechaTraslado" slot-scope="row">
                  {{ row.item.FechaTraslado !== '0000-00-00 00:00:00' ? $moment(row.item.FechaTraslado, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD') : '' }}
                </template>

                <template slot="row-details" slot-scope="row">

                  <b-card>

                    <b-row class="mb-2">
                  <table class="table b-table table-sm prod-detalle">
                    <thead>
                      <tr>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in getDetalles(row.item.IdDocVentaRemision)" :key="index">
                        <td>{{ item.CodigoBarra }}</td>
                        <td>{{ item.Producto }}</td>
                        <td>{{ item.Cantidad }}</td>
                        <td>{{ item.CodigoMedicion }}</td>
                      </tr>
                    </tbody>
                  </table>
                      <b-button v-if="row.item.Estado != '2'" style="margin-left: 30px;" variant="primary" size="sm" @click="moverAEmitidos(row.item)">Mover a emitidos</b-button>
                      </b-row>
                  </b-card>
                </template>

                <template slot="estado" slot-scope="data">
                  <div style="display:flex;">
                      <img v-b-tooltip.hover v-if="data.item.Estado == 2" ref="imgSuccess" :title="data.item.Msj_sunat" src="static/success.png" width="32" height="32"/>
                      &nbsp;&nbsp;
                      <img v-b-tooltip.hover v-if="data.item.Msj_sunat && data.item.Estado != 2" :title="data.item.Msj_sunat"  src="static/error.png" height="32"/>
                      &nbsp;&nbsp;
                      <b-button v-if="estadoVenta != 2" variant="success" @click="emitirElectronico(data.item)">EMITIR</b-button>
                      &nbsp;&nbsp;
                      <b-button variant="info" @click="regenerarPDF(data.item)">PDF</b-button>
                      &nbsp;&nbsp;
                  </div>
                </template>


              </b-table>
          </b-col>
        </b-row>
        <br />
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoKardex from '@/components/ProductoKardex'
export default {
  beforeMount() {
    this.generateFilter('fechaReferencia', this.fechaReferencia)
  },
  data() {
    return {
      API: config.API,
      loading: false,
      estadoVenta: 1,
      optionsEstado: [
        { value: '1', text: 'PENDIENTE'},
        { value: '2', text: 'EMITIDOS' }
      ],
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      fechaReferencia: this.$moment().format("YYYY-MM-DD"),
      filters: { },
      filter: '',
      sortBy: 'FechaReg',
      sortDesc: false,
      items: [],
      itemsDetalle: [],
      isBusy: false,
      fields: [
            { key: 'detalles', label: '' },
            { key: 'IdDocVentaRemision', label: 'ID', sortable: true },
            { key: 'Serie', label: 'Serie', sortable: true },
            { key: 'Numero', sortable: true },
            { key: 'FechaTraslado', label: 'Fecha Traslado', sortable: true },
            { key: 'PesoBruto', label: 'Peso Total', sortable: true},
            { key: 'RazonSocialCliente', label: 'Razón Social Cliente', sortable: true },
            { key: 'DireccionOrigen', label: 'Dirección Origen' },
            { key: 'DireccionDestino', label: 'Dirección Destino' },
            { key: 'estado', sortable: false, class: 'text-center' },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      itemsStatus: {},
      isBoleta: false,
    }
  },
  watch: {
    items (items) {
      if (items.length){
        this.axios.get(this.API + 'remision/detalle', {
           params: {
             idRemisiones: items.map((item) => item.IdDocVentaRemision),
           }
         }).then((response) => {
            this.itemsDetalle = response.data
            this.items.map((item) => {
              item.productos = this.getDetalles(item.IdDocVentaRemision)
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
    regenerarPDF (doc) {
      this.$refs.tablaVentas.refresh()
      this.loading = true
      this.axios.post(this.API + 'generarpdfguiaelectronica/' + doc.IdDocVentaRemision ).then(res => {
        console.log(res)
        this.$toasted.success('El PDF fue generado nuevamente', { duration: 7000 })
        this.loading = false
      })
    },
    setFechaReferencia () {
      this.generateFilter('fechaReferencia', this.fechaReferencia)
      
      this.$nextTick(() => {
        this.$refs.tablaVentas.refresh()
      })
    },
    getStatus (idDocVenta) {
      let obj = this.itemsStatus[idDocVenta] || {}
      if (obj.Estado) {
        obj.image = obj.Estado == 2 ? 'success.png' : 'error.png'
      }
      
      return obj
    },
    async emitirElectronico (doc) {
      this.loading = true
      this.axios.post(this.API + 'emitirguiaelectronica/' + doc.IdDocVentaRemision ).then((response) => {
        if(response.data.Estado == 1) {
          this.$toasted.error(response.data.msj_sunat)                  
          this.loading = false
        } else {
          this.$toasted.success(response.data.msj_sunat)
          this.loading = false
        }
      });
    },
    getDetalles (idDocVentaRemision) {
      let detalles = this.itemsDetalle.filter(item => item.IdDocVentaRemision == idDocVentaRemision)
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setAlmacen (obj) {
      this.almacen = obj
      this.limpiarFiltros()
      Vue.nextTick(() => this.$refs.tablaVentas.refresh())
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh()
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      
      let url = this.API + 'remision?estado=' + this.estadoVenta + '&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
      url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      let promise = this.axios.get( url )
      return promise.then((result) => {
        //this.items = result.data
        this.items = result.data.map(el => {
          if (el.Anulado === 1) {
            el._rowVariant = 'warning'
          }
          return el
        })
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
    limpiarFiltros () {
      this.filters = {}
      this.filter = ''
      this.fechaInicio = this.$moment().format("YYYY-MM-DD")
      this.fechaFin = this.$moment().format("YYYY-MM-DD")
      //this.$refs.inputVendedor.setValue('')
    },
    generateFilter (key, value) {
      this.filters[key] = value
      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key]
      }
      this.filter = filter
    },
    exportarProductos () {
    },
    moverAEmitidos (doc) {
      this.loading = true
      this.axios.post(this.API + 'moveraemitidosguia', {
        idDocVentaRemision: doc.IdDocVentaRemision
      }).then( response => {
        this.$refs.tablaVentas.refresh()
        this.loading = false
      })
    },
    cambiarAAnonimo (doc) {
      let r = confirm('¿Desea Cambiar a Cliente Anónimo?')
      if (r == true) {
        this.loading = true
        this.axios.post(this.API + 'cambiaraanonimo', {
          idDocVenta: doc.idDocVenta
        }).then( response => {
          this.$refs.tablaVentas.refresh()
          this.loading = false
        })
      }
    },
   
  },
  components: {
    ProductoKardex
  }
}
</script>

<style scope>
.total-ventas {
  font-weight: bold;
  text-align: center;
  color: #ffffff;
  background-color: #ff7675;
  padding: 10px;
  font-size: 40px;
}
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d; 
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
@media (min-width: 992px) {
    .modal-xl .modal-dialog {
        max-width: 1200px;
    }
}
.prod-detalle {
  margin: 0 30px;
}
.prod-detalle th, .prod-detalle td{
  background-color: #ffffff;
}
</style>