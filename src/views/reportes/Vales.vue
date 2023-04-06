<template>
    <div>
      <h2 align-h="center" class="text-center" >Reporte de Ventas por Consumo</h2>
        <b-container fluid style="width: calc(100% - 200px)" class="bv-example-row">
          <b-row>
            <b-col md="4">
              <b-form-group>
                    <label>Buscar:</label>
                <b-input-group>
                  <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                </b-input-group>
              </b-form-group>
              <br />
            </b-col>
            <b-col md="2">
              <b-form-group>
                <label>Fecha Emisión Inicio:</label>
                <b-form-input
                  v-model="rango.fechaDesde"
                  type="date"
                  step="1"
                  @input="val => $refs.tablaProductos.refresh()"
                  placeholder="Fecha Inicio">
                </b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="2">
              <b-form-group>
                <label>Fecha Emisión Fin:</label>
                <b-form-input
                  v-model="rango.fechaHasta"
                  type="date"
                  step="1"
                  @input="val =>$refs.tablaProductos.refresh() "
                  placeholder="Fecha Inicio">
                </b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="1">
              <b-form-group label="Limpiar ">
                <b-button @click="limpiarFiltros">Limpiar</b-button>
              </b-form-group>
              <br />
            </b-col>
            <b-col md="3" style="padding-left: 60px;">
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
              id="my-tableVales" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage"
              @row-clicked="myRowClickHandler">
                <template slot="Vales" slot-scope="data">
                    <b-badge v-if="data.item.Vales>0" variant="primary" style="font-size: 1.1rem;">{{ data.item.Vales }}</b-badge>
                    <b-badge v-else variant="danger" style="font-size: 1.1rem;">0</b-badge>
                </template>
                <template slot="Estado" slot-scope="data">
                    <b-badge v-if="data.item.AnuladoConsumo==0" variant="primary" style="font-size: 0.9rem;">Activo</b-badge>
                    <b-badge v-else variant="danger" style="font-size: 0.9rem;">Anulado N.C.</b-badge>
                </template>
                <template slot="Ver" slot-scope="data">
                  <b-button :href="'/imprimir/index.php?IdDocVenta=' + data.item.IdDocVenta" variant="success" target="_blank" size="sm">Documento</b-button>
                </template>
            </b-table>
              <b-row>
                  <b-col md="6" class="my-1">
                      <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                  </b-col>
              </b-row>
          </b-row>
          <!--<b-row align-h="center" class="text-center">
              <b-col cols="4">
                <b-button variant="success" size="lg" :href="'/api/index.php/reporte/deudaclientes2?idalmacen=' + almacen.IdAlmacen+'&limit='+perPage+'&usuarioReg='+vendedor.Usuario+'&q='+filter+'&fechaDesde='+rango.fechaDesde+'&fechaHasta='+rango.fechaHasta" target="_blank">Exportar</b-button>
              </b-col>
          </b-row>-->
          <br /><br />
        </b-container>
        <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showVales" ok-only ok-title="Cerrar" title="Detalle Venta">
             <vales-detalle ref="detVales"></vales-detalle>
        </b-modal>
    </div>
  </template>
  
  <script>
  import config from '@/store/config.js'
  import ValesDetalle from '@/components/ValesDetalle.vue'
  // import ProductoForm from '@/components/ProductoForm'
  
  export default {
    mounted() {
      this.axios.get(this.API + 'ventas/consumo/count').then((response) => {
        this.totalRows = response.data.total
      })
    },
    data() {
      return {
        API: config.API,
        pageOptions: [ 50, 100, 150, 200, 300 ],
        filter: '',
        sortBy: 'IdDocVenta',
        sortDesc: false,
        items: [],
        isBusy: false,
        fields: [
              { key: 'IdDocVenta', label: 'ID', sortable: true },
              { key: 'FechaDoc', label: 'Fecha', sortable: true },
              { key: 'DniRuc', label: 'DNI/RUC', sortable: true },
              { key: 'Cliente', label: 'Cliente', sortable: true },
              { key: 'UsuarioReg', label: 'Vendedor', sortable: true },
              { key: 'TipoDoc', label: 'Tipo', sortable: true },
              { key: 'Serie', label: 'Serie', sortable: true },
              { key: 'Numero', label: 'Numero', sortable: true},
              { key: 'SubtotalIni', label: 'Total', sortable: true},
              { key: 'SubTotalConsumo', label: 'Monto Consumido', sortable: true},
              { key: 'MontoDisponible', label: 'Monto Disponible', sortable: true },
              { key: 'Vales', label: 'Vales Consumidos', sortable: true, class:'text-center'},
              { key: 'Ver', label: 'Ver', sortable: true, class:'text-center'},
              { key: 'Estado', label: 'Estado', sortable: true, class:'text-center'},
        ],
        totalRows: 50,
        perPage: 50,
        currentPage: 1,
        rango: {
          fechaDesde: this.$moment().format("YYYY-01-01"),
          fechaHasta: this.$moment().format("YYYY-MM-DD")
        },
        showVales: false,
      }
    },
    methods: {
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },

      limpiarFiltros () {
        this.filter = ''
        this.rango.fechaDesde = this.$moment().format("YYYY-01-01")
        this.rango.fechaHasta = this.$moment().format("YYYY-MM-DD")
        Vue.nextTick(() => this.$refs.tablaProductos.refresh())
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        // console.log(ctx.filter)
        // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
        // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( this.API + 'ventas/consumo?q=' + ctx.filter + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage+'&fechaDesde='+this.rango.fechaDesde+'&fechaHasta='+this.rango.fechaHasta)
  
        return promise.then((response) => {
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
      },
      myRowClickHandler(record, index) {
        if(record.Vales>0){
          this.showVales = true
          this.$refs.detVales.cargarVales(record.IdDocVenta,record.SubtotalIni,record.DniRuc,record.Cliente,record.Serie,record.Numero,record.SubTotalConsumo,record.MontoDisponible,record.FechaDoc,record.AnuladoConsumo);
        }
      }
    },
    components: {
        // ProductoForm
      ValesDetalle
    }
  }
  </script>
  