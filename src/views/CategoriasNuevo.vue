<template>
    <div>
        <b-container fluid style="width: calc(100% - 200px)" class="bv-example-row">
          <b-row>
            <b-col md="12">
              <b-col md="12" class="my-1">
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
              </b-table>
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
              </b-row>
            </b-col>
            <!--<b-col md="6">
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
                id="my-table" 
                :busy.sync="isBusy" 
                :items="myProviderImpresora" 
                :fields="fieldsImpresora"
                :per-page="perPageImpresora"
                :current-page="currentPageImpresora">
                  <template slot="Tipo" slot-scope="data" class="text-center">
                    <div class="text-center">
                      <b-badge v-if="data.item.Tipo==1" variant="info">USB</b-badge>
                      <b-badge v-if="data.item.Tipo==2" variant="primary">RED</b-badge>
                      <b-badge v-if="data.item.Tipo==null" variant="danger">----</b-badge>
                    </div>
                  </template>
  
                  <template slot="Probar" slot-scope="data" class="text-center">
                    <div class="text-center">
                      <b-button variant="success" @click="prueba(data.item)"><i class="fas fa-print fa-sm"></i></b-button>
                    </div>
                  </template>
                  <template slot="Editar" slot-scope="data" class="text-center">
                    <div class="text-center">
                      <b-button variant="primary" @click="rowClickImpresora(data.item)"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
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
            </b-col>-->
          </b-row>
          <categoria-nuevo-form ref="categoriaForm" :categoria-update="categoriaSelect" :cbGuardar="actualizarTablaCategorias"></categoria-nuevo-form>
          <!--<impresora-form ref="impresoraForm" :impresora-update="impresoraSelect" :cbGuardar="actualizarTablaImpresoras"></impresora-form>-->
        </b-container>
    </div>
  </template>
  
  <script>
  import config from '@/store/config.js'
  import CategoriaNuevoForm from '@/components/CategoriaNuevoForm'
  export default {
    mounted() {
      this.axios.get(this.API + 'categorias/count').then((response) => {
        this.totalRows = response.data.total
      })
      /*this.axios.get(this.API + 'impresoras/count').then((response) => {
        this.totalRowsImpresora = response.data.total
      })*/
    },
    data() {
      return {
        API: config.API,
        pageOptions: [ 10, 15, 20, 50, 100 ],
        filter: null,
        sortBy: 'MostrarPuntoVenta',
        sortDesc: true,
        categoriaSelect: {},
        items: [],
        isBusy: false,
        fields: [
              { key: 'IdProductoCategoria', label: 'Id', sortable: true },
              { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
              { key: 'PuntoVenta', label: 'Mostrar en P. de Venta', sortable: true , class:'text-center'},
              { key: 'OrdenPuntoVenta', label: 'Orden P. de Venta', sortable: true , class:'text-center'},
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
              { key: 'IdImpresora', label: 'Id', sortable: true },
              { key: 'NombreImpresora', label: 'Nombre', class:'text-center' , sortable: true},
              { key: 'Tipo', label: 'Tipo'},
              { key: 'NombreRed', label: 'Nombre en RED', class:'text-center'},
              { key: 'Ip', label: 'Direccion IP' },
              { key: 'Puerto', label: 'Puerto', class:'text-center' },
              'Probar','Editar',
              
        ],
        totalRowsImpresora: 50,
        perPageImpresora: 10,
        currentPageImpresora: 1
      }
    },
    methods: {
        rowClick (item, index) {
          this.categoriaSelect = item
          this.$refs.categoriaForm.show = true;
          //this.$refs.productoForm.producto.IdProducto = 'a'
          //console.log(this.$refs.productoForm.$refs.productoForm)
        },
        actualizarTablaCategorias() {
          this.obtenerTotalCategorias()
          this.$refs.tablaCategorias.refresh()
        },
        obtenerTotalCategorias(){
          this.axios.get(this.API + 'categorias/count').then((response) => {
            this.totalRows = response.data.total
          })
        },
        nuevaCategoria () {
            this.categoriaSelect = {}
            this.$refs.categoriaForm.show = true
        },
        myProvider (ctx) {
          // Here we don't set isBusy prop, so busy state will be handled by table itself
          // this.isBusy = true
          let url = this.API + 'categorias/nuevo?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
        CategoriaNuevoForm
    }
  }
  </script>
  
  <style>
  </style>