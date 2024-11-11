<template>
  <div>
      <b-container fluid class="ancho bv-example-row">
        <b-row>
          <b-col md="3" class="my-1">
            <b-form-group class="mb-0">
              <b-input-group>
                  <b-form-input 
                    v-model="searchFilter" 
                    placeholder="Buscar Producto" 
                    @input="updateFilters"
                  />
                <b-input-group-append>
                  <b-btn 
                    :disabled="!searchFilter" 
                    @click="clearSearchFilter"
                  >
                    Limpiar
                  </b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
        </b-col>
          <b-col md="3" class="my-1">
            <b-form-group>
              <div class="d-flex align-items-center">
                <b-form-input
                  ref="inputCodigoBarra"
                  placeholder="Buscar Código de Barra"
                  class="form-control"
                  :debounce="2000"
                  v-model="codigoBarraFilter"
                  @input="updateFilters"
                />
                <b-btn
                  :disabled="!codigoBarraFilter"
                  @click="clearCodigoBarraFilter"
                  class="ml-2"
                >
                  Limpiar
                </b-btn>
              </div>
            </b-form-group>
          </b-col>
          <b-col md="6" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select 
                  :options="pageOptions" 
                  v-model="perPage" 
                  @input="resetPage"
                />
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">
                de <b class="text-success">{{ totalRows }}</b> Artículos
              </p>
            </b-col>
            </b-row>
          </b-col>

        <b-col>
          <b-row class="my-1"></b-row>
        </b-col>
          <b-table small
            ref="tablaProductos"
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
              <template slot="PrecioCosto" slot-scope="data" >
                {{ data.item.PrecioCosto | redondear(2) }}
              </template>
              <template slot="PrecioContado" slot-scope="data" >
                {{ data.item.PrecioContado | redondear(2) }}
              </template>
            </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                  <b-pagination 
                    :total-rows="totalRows" 
                    :per-page="perPage" 
                    v-model="currentPage" 
                    class="my-0" 
                  />
                </b-col>
            </b-row>
        </b-row>
        <producto-form 
          ref="productoForm" 
          :producto-update="productoSelect" 
          :cbGuardar="actualizarTabla"
        ></producto-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
            <b-button 
              variant="success" 
              size="lg" 
              @click="nuevoProducto"
            >
              Nuevo
            </b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.fetchTotalRows();
  },
  data() {
    return {
      API: config.API,
      APISTOCK: 'productos',
      tipoStock: '',
      showKardex: false,
      almacen: {
        IdAlmacen: 1,
        Almacen: ''
      },
      pageOptions: [5, 10, 15, 50, 100],
      searchFilter: '',
      codigoBarraFilter: '',
      filter: '',
      sortBy: 'IdProducto',
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'CodigoBarra', label: 'Codigo', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoMedicion', label: 'Medición', sortable: true },
            { key: 'PorcentajeUtilidad', label: 'Porcentaje Utilidad', sortable: true },
            { key: 'PrecioCosto', label: 'Precio Compra', sortable: true },
            { key: 'PrecioContado', label: 'Precio Venta', sortable: true },
            { key: 'ProductoPresentacion', label: 'Anaquel', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
    fetchTotalRows() {
      this.axios.get(`${this.API}productos/count`).then((response) => {
        this.totalRows = response.data.total;
      });
    },
      rowClick (item, index) {
        this.productoSelect = item
        this.$refs.productoForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaProductos.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = `${this.API}productos?limit=${ctx.perPage}&page=${ctx.currentPage}&sortBy=${ctx.sortBy}&sortDesc=${ctx.sortDesc ? 1 : 0}`;

        if (this.filter) {
          url += this.filter;
        }

        this.isBusy = true;

        return this.axios.get(url)
          .then(result => {
            this.items = result.data;
            this.isBusy = false;
            return this.items;
          })
          .catch(error => {
            this.isBusy = false;
            return [];
          });
      },
      updateFilters() {
        let filters = [];

        if (this.searchFilter) {
          filters.push(`filter[producto]=${encodeURIComponent(this.searchFilter)}`);
        }

      if (this.codigoBarraFilter) {
        filters.push(`filter[codigo]=${encodeURIComponent(this.codigoBarraFilter)}`);
      }

      this.filter = filters.length ? `&${filters.join('&')}` : '';
      this.currentPage = 1; // Reset page number when applying filters
      this.$refs.tablaProductos.refresh(); // Refresh table to apply new filters
    },
    clearSearchFilter() {
      this.searchFilter = '';
      this.updateFilters();
    },
    clearCodigoBarraFilter() {
      this.codigoBarraFilter = '';
      this.updateFilters();
    },
    resetPage() {
      this.currentPage = 1;
      }
  },
  components: {
      ProductoForm
  }
}
</script>

<style>
  .b-form-group {
    margin-bottom: 0;
  }
  @media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
    .ancho{
        width: calc(100% - 300px)
    }
  }
</style>
