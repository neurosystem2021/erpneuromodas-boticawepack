<template>
  <div>
      <b-container fluid style="width: calc(100% - 200px)" class="bv-example-row">
        <b-row>
          <b-col md="6">
            <!-- <b-col md="12" class="my-1">
              <h3>Categorias</h3>
              <br>
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
            <b-table small
              ref="tablaCategorias"
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
              <template slot="NombreImpresora" slot-scope="data">
                <label v-if="data.item.NombreImpresora!=null">{{data.item.NombreImpresora}}</label>
                <label v-else style="color:red; font-weight:bold">No asignado</label>
              </template>
              <template slot="Delivery" slot-scope="data">
                <b-badge v-if="data.item.MostrarDelivery==1" variant="success">SI</b-badge>
                <b-badge v-else variant="danger">NO</b-badge>
              </template>
              <template slot="PuntoVenta" slot-scope="data">
                <b-badge v-if="data.item.MostrarPuntoVenta==1" variant="success">SI</b-badge>
                <b-badge v-else variant="danger">NO</b-badge>
              </template>
              <template slot="OrdenPuntoVenta" slot-scope="data">
                <b-badge v-if="data.item.MostrarPuntoVenta==1" variant="info">{{data.item.Orden}}</b-badge>
                <label v-else>---</label>
              </template>
              <template slot="EsOferta" slot-scope="data">
                <b-badge v-if="data.item.EsOferta==1" variant="success">SI</b-badge>
                <label v-else>---</label>
              </template>
            </b-table>
            <br>

            <b-row>
               <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
               </b-col>
            </b-row>
            <br>
            <b-row align-h="center" class="text-center">
              <b-col cols="4">
                <b-button variant="success" size="xl" @click="nuevaCategoria">Nueva Categoria</b-button>
              </b-col>
            </b-row> -->

             <b-col md="12" class="my-1">
              <h3>Porcentaje de Descuento Máximo (S/.)</h3>
              <br>
              <b-form-group horizontal label="Buscar" class="mb-0">
                <b-input-group>
                  <b-form-input v-model="filterDescuento" placeholder="Buscar" @input="currentPageDescuento = 1" />
                  <b-input-group-append>
                    <b-btn :disabled="!filterDescuento" @click="filterDescuento = ''">Limpiar</b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <br />
            </b-col>

            <b-table small
              ref="tablaDescuentos"
              :filter="filterDescuento"
              :sort-by.sync="sortByDescuento"
              :sort-desc.sync="sortDescDescuento"
              striped
              hover
              @row-clicked="rowClickDescuento"
              id="my-table"
              :busy.sync="isBusy"
              :items="myProviderDescuento"
              :fields="fieldsDescuento"
              :per-page="perPageDescuento"
              :current-page="currentPageDescuento">
            </b-table>
             <br>
            <b-row>
               <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRowsDescuento" :per-page="perPageDescuento" v-model="currentPageDescuento" class="my-0" />
               </b-col>
            </b-row>
            <br>
            <b-row align-h="center" class="text-center">
              <b-col cols="4">
                <b-button variant="success" size="xl" @click="nuevoDescuento">Nuevo Descuento</b-button>
              </b-col>
            </b-row>
          </b-col>

          <b-col md="6">
            <b-col md="12" class="my-1">
            <h3>Impresoras</h3>
            <br>
              <b-form-group horizontal label="Buscar" class="mb-0">
                <b-input-group>
                  <b-form-input v-model="filterImpresora" placeholder="Buscar" @input="currentPageImpresora = 1" />
                  <b-input-group-append>
                    <b-btn :disabled="!filterImpresora" @click="filterImpresora = ''">Limpiar</b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
              <br />
            </b-col>
            <b-table small
              ref="tablaImpresoras"
              :filter="filterImpresora"
              :sort-by.sync="sortByImpresora"
              :sort-desc.sync="sortDescImpresora"
              striped
              hover
              @row-clicked="rowClickImpresora"
              id="my-table" 
              :busy.sync="isBusy" 
              :items="myProviderImpresora" 
              :fields="fieldsImpresora"
              :per-page="perPageImpresora"
              :current-page="currentPageImpresora">
                <template slot="Cocina" slot-scope="data" class="text-center">
                  <div class="text-center">
                    <b-badge v-if="data.item.EsCocina==1" variant="success">SI</b-badge>
                    <b-badge v-else variant="danger">NO</b-badge>
                  </div>
                </template>
            </b-table>
            <b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRowsImpresora" :per-page="perPageImpresora" v-model="currentPageImpresora" class="my-0" />
              </b-col>
            </b-row>
            <br>
            <b-row align-h="center" class="text-center">
              <b-col cols="4">
                <b-button variant="success" size="xl" @click="nuevaImpresora">Nueva Impresora</b-button>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <categoria-form-nuevo ref="categoriaForm" :categoria-update="categoriaSelect" :cbGuardar="actualizarTablaCategorias"></categoria-form-nuevo>
        <impresora-form ref="impresoraForm" :impresora-update="impresoraSelect" :cbGuardar="actualizarTablaImpresoras"></impresora-form>
        <descuentoFormNuevo ref="descuentoForm" :categoria-update="descuentoSelect" :cbGuardar="actualizarTablaDescuentos"></descuentoFormNuevo>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import CategoriaFormNuevo from '@/components/CategoriaFormNuevo'
import ImpresoraForm from '@/components/ImpresoraForm'
import DescuentoFormNuevo from '@/components/DescuentoFormNuevo.vue'
export default {
  mounted() {
    this.axios.get(this.API + 'categorias/count').then((response) => {
      this.totalRows = response.data.total
    })
     this.axios.get(this.API + 'descuentos/count').then((response) => {
      this.totalRowsDescuento = response.data.total
    })
    this.axios.get(this.API + 'impresoras/count').then((response) => {
      this.totalRowsImpresora = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 10, 15, 20, 50, 100 ],
      filter: null,
      sortBy: 'PorcentajeDescMax',
      sortDesc: true,
      categoriaSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProductoCategoria', label: 'Id', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'PorcentajeDescMax', label: '% Desc. Max', sortable: true},
            // { key: 'NombreImpresora', label: 'Impresora Asignada', sortable: true },
            // { key: 'Delivery', label: 'Mostrar en Delivery', sortable: true , class:'text-center'},
            //{ key: 'PuntoVenta', label: 'Mostrar en P. de Venta', sortable: true , class:'text-center'},
            //{ key: 'OrdenPuntoVenta', label: 'Orden P. de Venta', sortable: true , class:'text-center'},
            //{ key: 'EsOferta', label: 'Oferta APP', class:'text-center'},
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      //
      filterImpresora: null,
      sortByImpresora: 'IdImpresora',
      sortDescImpresora: false,
      impresoraSelect: {},
      itemsImpresora: [],
      isBusyImpresora: false,
      fieldsImpresora: [
            { key: 'IdImpresora', label: 'Codigo', sortable: true },
            { key: 'NombreImpresora', label: 'Nombre', sortable: true},
            { key: 'Ip', label: 'Direccion IP', sortable: true },
            { key: 'Puerto', label: 'Puerto', sortable: true },
            { key: 'Cocina', label: 'Es Cocina', sortable: false },
      ],
      totalRowsImpresora: 50,
      perPageImpresora: 10,
      currentPageImpresora: 1,
      
    // ============================
    // COLORES - DESCUENTO
    // ============================
    filterDescuento: null,
    sortByDescuento: 'IdDescuento',
    sortDescDescuento: false,
    descuentoSelect: {},
    itemsDescuento: [],
    isBusyDescuento: false,
    fieldsDescuento: [
      { key: 'IdDescuento', label: 'ID', sortable: true },
      { key: 'Color', label: 'Color', sortable: true },
      { key: 'UCorporativo', label: '%. Desc. Corporativo', sortable: true },
      { key: 'UPublico', label: '%. Desc. Público', sortable: true },
      { key: 'UTaller', label: '%. Desc. Institucional', sortable: true }
      // { key: 'TipoColor', label: 'Tipo Color', sortable: true }
    ],
    totalRowsDescuento: 50,
    perPageDescuento: 10,
    currentPageDescuento: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.categoriaSelect = item
        this.$refs.categoriaForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      rowClickDescuento (item, index) {
        this.descuentoSelect = item
        this.$refs.descuentoForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      rowClickImpresora (item, index) {
        this.impresoraSelect = item
        this.$refs.impresoraForm.show = true;
        this.$refs.impresoraForm.nuevo = false;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTablaCategorias() {
        this.obtenerTotalCategorias()
        this.$refs.tablaCategorias.refresh()
      },
      actualizarTablaDescuentos() {
        this.obtenerTotalCategorias()
        this.$refs.tablaDescuentos.refresh()
      },
      actualizarTablaImpresoras() {
        this.obtenerTotalImpresoras()
        this.$refs.tablaImpresoras.refresh()
      },
      obtenerTotalImpresoras(){
        this.axios.get(this.API + 'impresoras/count').then((response) => {
          this.totalRowsImpresora = response.data.total     
        })
      },
      obtenerTotalCategorias(){
        this.axios.get(this.API + 'categorias/count').then((response) => {
          this.totalRows = response.data.total
        })
      },
      obtenerTotalDescuentos(){
        this.axios.get(this.API + 'descuentos/count').then((response) => {
          this.totalRowsDescuento = response.data.total
        })
      },
      nuevaImpresora () {
          this.impresoraSelect = {}
          this.$refs.impresoraForm.show = true
          this.$refs.impresoraForm.nuevo = true;
      },
      nuevaCategoria () {
          this.categoriaSelect = {}
          this.$refs.categoriaForm.show = true
      },
      nuevoDescuento () {
          this.descuentoSelect = {}
          this.$refs.descuentoForm.show = true
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'categorias/impresora?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      },
      myProviderDescuento (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'descuentos/color?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )
        return promise.then((result) => {
          this.itemsDescuento = result.data
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return(this.itemsDescuento)
        }).catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return []
        })
      },

      myProviderImpresora (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'impresoras?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )
        return promise.then((result) => {
          this.itemsImpresora = result.data
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return(this.itemsImpresora)
        }).catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return []
        })
      }
  },
  components: {
      CategoriaFormNuevo,
      ImpresoraForm,
      DescuentoFormNuevo
  }
}
</script>

<style>
</style>