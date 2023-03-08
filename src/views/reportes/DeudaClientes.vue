<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Deuda de Clientes</h2>
      <b-container class="bv-example-row">
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
          <b-col md="4">
            <b-form-group>
                  <label>Vendedor:</label>
                  <autocomplete
                      required="required"
                      ref="inputVendedor"
                      :url="API + 'usuarios'"
                      anchor="Usuario"
                      initValue="TODOS"
                      label="writer"
                      placeholder=""
                      :on-select="setVendedor" 
                      :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
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
          <b-col md="4">
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
          <b-col md="4">
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
            id="my-tableDeuda" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
            @row-clicked="myRowClickHandler"></b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/deudaclientes2?idalmacen=' + almacen.IdAlmacen+'&limit='+perPage+'&usuarioReg='+vendedor.Usuario+'&fechaDesde='+rango.fechaDesde+'&fechaHasta='+rango.fechaHasta" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
      <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showLetras" ok-only ok-title="Cerrar" title="Detalle de Deuda">
           <detalle-deuda-cliente ref="detDeuCli"></detalle-deuda-cliente>
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import DetalleDeudaCliente from '@/components/DetalleDeudaCliente.vue'
// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'clientes/deuda/count?idalmacen=' + this.almacen.IdAlmacen ).then((response) => {
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
            { key: 'DniRuc', label: 'DNI/RUC', sortable: true },
            { key: 'Cliente', label: 'Cliente', sortable: true },
            { key: 'UsuarioReg', label: 'Vendedor', sortable: true },
            { key: 'Correlativo', label: 'Serie', sortable: true },
            { key: 'Total', label: 'Total', sortable: true},
            { key: 'Aplicado', label: 'Aplicado', sortable: true},
            { key: 'NotaCreApli', label: 'Apli. Nota Cred.', sortable: true},
            { key: 'Saldo', label: 'Saldo', sortable: true },
            { key: 'FechaDoc', label: 'Emision', sortable: true },
            { key: 'FechaCredito', label: 'Vencimiento', sortable: true },
            { key: 'Letras', label: 'Letras', sortable: true },
      ],
      totalRows: 50,
      perPage: 50,
      currentPage: 1,
      almacen: {
        IdAlmacen: 1
      },
      vendedor: {
        Usuario: undefined
      },
      rango: {
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      showLetras: false,
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
    setVendedor (obj) {
      this.vendedor = obj
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    limpiarFiltros () {
      this.filter = ''
      this.vendedor.Usuario = undefined;
      this.almacen.IdAlmacen = 1;
      this.rango.fechaDesde = this.$moment().format("YYYY-01-01")
      this.rango.fechaHasta = this.$moment().format("YYYY-MM-DD")
      this.$refs.inputVendedor.setValue('TODOS')
      this.$refs.inputAlmacen.setValue('')
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      // console.log(ctx.filter)
      // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
      // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      let promise = this.axios.get( this.API + 'cliente/deudas?idalmacen=' + this.almacen.IdAlmacen + '&usuarioReg=' + this.vendedor.Usuario + '&q=' + ctx.filter + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage+'&fechaDesde='+this.rango.fechaDesde+'&fechaHasta='+this.rango.fechaHasta)

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
      if(record.Letras!='Ninguna' && record.Letras!=''){
        this.showLetras = true
        this.$refs.detDeuCli.cargarDeudasCliente(record.IdDocVenta,record.Total,record.DniRuc,record.Cliente,record.Correlativo);
      }
    }
  },
  components: {
      // ProductoForm
    DetalleDeudaCliente
  }
}
</script>

<style>
#my-tableDeuda tbody tr:last-child{
    color: black;
    background: transparent;
    font-weight: bold;
}
</style>