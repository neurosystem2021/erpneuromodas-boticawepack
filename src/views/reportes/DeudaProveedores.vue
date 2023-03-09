<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Deuda de Proveedores</h2>
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
                  <label>Proveedor:</label>
                  <autocomplete
                      required="required"
                      ref="inputProveedor"
                      :url="API + 'proveedores'"
                      anchor="Proveedor"
                      initValue="TODOS"
                      label="writer"
                      placeholder=""
                      :on-select="setProveedor" 
                      :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
          </b-col>
          <!-- <b-col md="4">
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
          </b-col>-->
          <b-col md="1" class="my-1" >
            <b-form-group label="Limpiar ">
              <b-button @click="limpiarFiltros">Limpiar</b-button>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="3" class="my-1" style="padding-left: 60px;">
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
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/deudaproveedores?proveedor=' + proveedor.Proveedor + '&q='+filter" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
      <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showLetras" ok-only ok-title="Cerrar" title="Detalle de Deuda">
        <detalle-deuda-proveedor ref="detDeuPro"></detalle-deuda-proveedor>
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import DetalleDeudaProveedor from '@/components/DetalleDeudaProveedor.vue'
// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'proveedores/deuda/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: '',
      sortBy: 'IdDocVenta',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
            { key: 'Ruc', label: 'RUC', sortable: true },
            { key: 'Proveedor', label: 'Cliente', sortable: true },
            { key: 'Correlativo', label: 'Serie', sortable: true },
            { key: 'Total', label: 'Total', sortable: true},
            { key: 'Aplicado', label: 'Aplicado', sortable: true},
            { key: 'Saldo', label: 'Saldo', sortable: true },
            { key: 'FechaDoc', label: 'Emision', sortable: true },
            { key: 'FechaCredito', label: 'Vencimiento', sortable: true },
            { key: 'Letras', label: 'Letras', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      almacen: {
        IdAlmacen: 1
      },
      proveedor: {
        Proveedor: undefined
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
      // this.almacen = obj
      // Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    setProveedor (obj) {
      this.proveedor = obj
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    limpiarFiltros () {
      this.filter = ''
      this.proveedor.Proveedor = undefined;
      this.$refs.inputProveedor.setValue('TODOS')
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      // console.log(ctx.filter)
      // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
      // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      let promise = this.axios.get( this.API + 'proveedores/deudas?q=' + ctx.filter + '&proveedor=' + this.proveedor.Proveedor + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage )

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
    },
    myRowClickHandler(record, index) {
      if(record.Letras!='Ninguna' && record.Letras!=''){
        this.showLetras = true
        this.$refs.detDeuPro.cargarDeudasProveedor(record.Id,record.Total,record.Ruc,record.Proveedor,record.Correlativo);
      }
    }
  },
  components: {
      // ProductoForm
    DetalleDeudaProveedor
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