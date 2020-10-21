<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="12" class="my-1">
            <b-row>
               <b-col md="4">
                <!-- <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue="VENTA"
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group> -->

                <b-form-group>
                  <label>Fecha Inicio:</label>
                  <b-form-input v-model="fechaInicio"
                    type="date"
                    placeholder=""
                    @input="val => generateFilter('fechaInicio', val) "></b-form-input>
                </b-form-group>
                <b-form-group>
                    <label>Fecha Fin:</label>
                    <b-form-input v-model="fechaFin"
                    type="date"
                    placeholder=""
                    @input="val => generateFilter('fechaFin', val) "></b-form-input>
                </b-form-group>
              </b-col>
              <b-col md="4">
                <b-row>
                  <b-col md="12">
                    <b-form-group>
                        <label>Cliente:</label>
                        <autocomplete
                            required="required"
                            ref="inputCliente"
                            :url="API + 'clientes'"
                            anchor="ClienteDniRuc"
                            label="writer"
                            placeholder=""
                            :onInput="(val) => !val ? generateFilter('cliente', val) : false"
                            :on-select="setCliente" 
                            :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                    </b-form-group>
                  </b-col>
                </b-row>
                
                
              </b-col>
              
              <b-col md="4">
                <b-row>
                    <b-col md="6">
                        <b-form-group label="Limpiar Filtros">
                            <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                        </b-form-group>
                    </b-col>
                    <b-col md="6">
                        <b-form-group label="Mostrar">
                            <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                        </b-form-group>
                    </b-col>
                </b-row>
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
              @row-clicked="rowClick"
              id="tabla-ventas" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">

                <template slot="stock" slot-scope="data">
                  <div :class="getStock(data.item.IdProducto) > 0 ? 'table-success' : 'table-danger'" @click.stop="() => false">
                    {{ getStock(data.item.IdProducto) }}
                  </div>
                </template>

                <template slot="Reimprimir" slot-scope="data">
                  <a class="btn btn-success" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'">{{data.item.TipoDoc}}</a>
                </template>

                <template slot="Entregar" slot-scope="data">
                  <button class="btn btn-success" target="_blank" @click="() => entregarVenta(data.item)">ENTREGAR</button>
                </template>

              </b-table>
          </b-col>
          <b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
              </b-col>
          </b-row>
        </b-row>
        <br />

        <br /><br />
      </b-container>

      <b-modal id="kardex" size="lg" modal-class="modal-xl" v-model="showVenta" title="Kardex de producto">
        <b-alert show variant="warning">titulo</b-alert>
        <!--<producto-kardex :producto="productoSelect" :kardex="kardexSelect"></producto-kardex>-->
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoKardex from '@/components/ProductoKardex'

export default {
  mounted() {
    this.axios.get(this.API + 'ventas/count', {
      params: { idAlmacen: this.almacen.IdAlmacen }
    }).then((response) => {
      this.totalRows = response.data.total
    })

    this.getVentasAcumuladas()
  },
  data() {
    return {
      API: config.API,
      cliente: '',
      // fechaInicio: this.$moment().format("YYYY") + '-01-01',
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      declarado: 1,
      filters: { },
      filter: '',
      sortBy: 'FechaDoc',
      sortDesc: false,
      ventasAcumuladas: 0,
      almacen: {
        IdAlmacen: 1,
        Almacen: 'VENTA'
      },
      tipoDoc: {
      },
      pageOptions: [ 5, 10, 15, 50, 100 ],
      ventaSelect: {},
      ventaDetalleSelect: [],
      items: [],
      showVenta: false,
      isBusy: false,
      fields: [
            { key: 'idDocVenta', label: 'ID', sortable: true },
            { key: 'FechaDoc', label: 'Fecha', sortable: true },
            { key: 'TipoDoc', label: 'Documento', sortable: true},
            { key: 'Serie', label: 'Serie', sortable: true },
            { key: 'Numero', sortable: true },
            { key: 'Cliente', sortable: true },
            { key: 'Anulado', label: 'Anulado', sortable: true },
            { key: 'UsuarioReg', label: 'Vendedor', sortable: true },
            { key: 'Total', sortable: false, variant: 'success', class:'text-center'},
            { key: 'Reimprimir', label: 'Ver' },
            { key: 'Entregar' }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
  },
  methods: {
    entregarVenta (venta) {
        let r = confirm('¿Esta seguro de entregar esta venta?')

        if (r) {
            this.axios.post(this.API + 'ventas/entregar', {
                idDocVenta: venta.idDocVenta
            }).then((response) => {
                this.$toasted.success('Venta entregada correctamente')
                this.$refs.tablaVentas.refresh()
            })
        }
    },
    setCliente (obj) {
      this.currentPage = 1
      this.cliente = obj
      this.generateFilter('cliente', obj.Cliente)
  
    },
    getVentasAcumuladas() {
      if(this.filters.vendedor) {
        this.axios.get(this.API + 'ventas/usuario', {
         params: {
           usuario: this.vendedor.Usuario,
           fechaInicio: this.fechaInicio,
           fechaFin: this.fechaFin,
            idTipoDoc: this.tipoDoc.IdTipoDoc || ''
         }
       }).then((response) => {
         this.ventasAcumuladas = response.data.total
       })
      } else {
        this.axios.get(this.API + 'ventas/total', {
          params: {
            fechaInicio: this.fechaInicio,
            fechaFin: this.fechaFin,
            idTipoDoc: this.tipoDoc.IdTipoDoc || ''
          }
        }).then((response) => {
          this.ventasAcumuladas = response.data.total
        })
      }
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    // setAlmacen (obj) {
    //   this.almacen = obj
    //   this.limpiarFiltros()
    //   Vue.nextTick(() => this.$refs.tablaVentas.refresh())
    // },
    rowClick (item) {
      this.ventaSelect = item
      this.showKardex = true

      this.axios.get(this.API + 'productos/kardex/' + 10 + '?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaHasta: this.$moment().format("YYYY-MM-DDTH:mm:ss")
          }
      }).then((response) => {
          this.ventaDetalleSelect = response.data
      })
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh()
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let tipoDocFilter = ''
      if (this.tipoDoc.IdTipoDoc) {
        tipoDocFilter = 'idTipoDoc[]=' + this.tipoDoc.IdTipoDoc + '&'
      }
      let url = this.API + 'ventas?' + tipoDocFilter + 'porEntregar=1&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
      url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
console.log(url)
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
    limpiarFiltros () {
      this.filters = {}
      this.filter = ''
      this.$refs.inputCliente.setValue('')
      document.location.reload()
     //  this.getVentasAcumuladas()
    },
    generateFilter (key, value) {
      this.filters[key] = value

      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key].replace('&', '%26')
      }
      this.filter = filter
      this.getVentasAcumuladas()
    },
    exportarProductos () {

    }
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
</style>
