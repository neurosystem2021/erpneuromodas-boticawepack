<template>
  <div class="reporte-stock">
    <b-container fluid class="px-4 py-4">

      <!-- Encabezado -->
      <div class="page-header d-flex align-items-center justify-content-between flex-wrap mb-4">
        <div class="d-flex align-items-center">
          <div class="header-icon mr-3">📦</div>
          <div>
            <h4 class="mb-0 font-weight-bold text-dark">Reporte de Stock</h4>
            <small class="text-muted">Control de inventario por almacén</small>
          </div>
        </div>

        <b-badge :variant="tipoStock === 'maximo' ? 'warning' : 'danger'" class="tipo-badge px-3 py-2">
          {{ tipoStock === 'maximo' ? '▲ Stock Máximo' : '▼ Stock Mínimo' }}
        </b-badge>
      </div>

      <!-- Card de filtros -->
      <b-card class="filter-card mb-4" no-body>
        <b-card-body>
          <b-row>
            <b-col lg="3" md="6" class="mb-3 mb-lg-0">
              <label class="filter-label">Buscar</label>
              <b-input-group class="search-group">
                <b-form-input
                  v-model="filter"
                  placeholder="Nombre, código, marca..."
                  @input="currentPage = 1"
                  class="search-input" />
                <b-input-group-append>
                  <b-button
                    variant="light"
                    :disabled="!filter"
                    @click="filter = ''"
                    class="clear-btn">
                    ✕
                  </b-button>
                </b-input-group-append>
              </b-input-group>
            </b-col>

            <b-col lg="3" md="6" class="mb-3 mb-lg-0">
              <label class="filter-label">Almacén</label>
              <autocomplete
                  required="required"
                  ref="inputAlmacen"
                  :url="API + 'almacenes'"
                  anchor="Almacen"
                  initValue=""
                  label="writer"
                  placeholder="Seleccione un almacén"
                  :on-select="setAlmacen"
                  :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                  :classes="{input: 'form-control'}">
              </autocomplete>
            </b-col>

            <b-col lg="3" md="6" class="mb-3 mb-lg-0">
              <label class="filter-label">Tipo de reporte</label>
              <b-form-select
                :options="tipoStockOptions"
                v-model="tipoStock"
                @input="onTipoStockChange"
                class="modern-select" />
            </b-col>

            <b-col lg="3" md="6">
              <label class="filter-label">Mostrar</label>
              <b-form-select
                :options="pageOptions"
                v-model="perPage"
                @input="currentPage = 1"
                class="modern-select" />
            </b-col>
          </b-row>
        </b-card-body>
      </b-card>

      <!-- Resumen rápido -->
      <b-row class="mb-3">
        <b-col cols="12">
          <div class="summary-strip d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center">
              <span class="text-muted">
                Mostrando resultados con
                <strong>{{ tipoStock === 'maximo' ? 'exceso de stock' : 'stock por debajo del mínimo' }}</strong>
              </span>
            </div>
            <b-badge pill variant="light" class="total-pill">
              {{ totalRows }} {{ totalRows === 1 ? 'artículo' : 'artículos' }}
            </b-badge>
          </div>
        </b-col>
      </b-row>

      <!-- Tabla -->
      <b-card class="table-card" no-body>
        <b-table
          ref="tablaProductos"
          :filter="filter"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          hover
          responsive
          id="my-table"
          class="modern-table mb-0"
          thead-class="modern-thead"
          :busy.sync="isBusy"
          :items="myProvider"
          :fields="fields"
          :per-page="perPage"
          :current-page="currentPage">

          <div slot="table-busy" class="text-center my-4">
            <b-spinner variant="primary" class="mb-2" />
            <p class="text-muted mb-0">Cargando productos...</p>
          </div>

          <template slot="cell(Producto)" slot-scope="row">
            <span class="font-weight-medium">{{ row.value }}</span>
          </template>

          <template slot="cell(stock)" slot-scope="row">
            <b-badge
              pill
              :variant="stockVariant(row.item)"
              class="stock-badge">
              {{ row.value }}
            </b-badge>
          </template>

          <template slot="cell(StockMinimo)" slot-scope="row">
            <span class="text-muted">{{ row.value }}</span>
          </template>

          <template slot="cell(StockMaximo)" slot-scope="row">
            <span class="text-muted">{{ row.value }}</span>
          </template>

          <template slot="cell(ProveedorUltimo)" slot-scope="row">
            <span v-if="row.value">{{ row.value }}</span>
            <span v-else class="text-muted font-italic">Sin registro</span>
          </template>

          <div slot="empty" class="text-center py-5">
            <p class="text-muted mb-0">No se encontraron productos con este filtro</p>
          </div>
        </b-table>

        <div class="pagination-footer d-flex align-items-center justify-content-between flex-wrap px-3 py-3">
          <small class="text-muted mb-2 mb-md-0">
            Página {{ currentPage }} · {{ perPage }} por página
          </small>
          <b-pagination
            :total-rows="totalRows"
            :per-page="perPage"
            v-model="currentPage"
            class="my-0 modern-pagination" />
        </div>
      </b-card>

      <!-- Exportar -->
      <div class="text-center mt-4">
        <b-button
          variant="success"
          :href="urlExportar"
          target="_blank"
          size="lg"
          class="export-btn px-5">
          ⬇ Exportar a Excel
        </b-button>
      </div>

    </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.fetchTotalRows()
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'ProveedorUltimo',
      sortDesc: false,
      items: [],
      isBusy: false,
      tipoStock: 'minimo', // 'minimo' | 'maximo'
      tipoStockOptions: [
        { value: 'minimo', text: 'Stock Mínimo' },
        { value: 'maximo', text: 'Stock Máximo' }
      ],
      fields: [
            { key: 'IdProducto', label: 'ID', sortable: true },
            { key: 'CodigoBarra', label: 'Código', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoMedicion', label: 'Medición', sortable: true },
            { key: 'StockMinimo', label: 'Stock Mín.', sortable: true },
            { key: 'StockMaximo', label: 'Stock Máx.', sortable: true },
            { key: 'stock', label: 'Stock', sortable: true },
            { key: 'ProveedorUltimo', label: 'Proveedor Ult.' , sortable: true}
      ],
      totalRows: 50,
      perPage: 10,
			currentPage: 1,
			almacen: {
				IdAlmacen: 1
			},
      urlExportar:''
    }
  },
  methods: {
		focusAutocomplete (ref) {
      this.$refs[ref].handleDoubleClick()
		},
		setAlmacen (obj) {
			this.almacen = obj
      this.fetchTotalRows()
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    onTipoStockChange () {
      this.currentPage = 1
      this.fetchTotalRows()
      this.$refs.tablaProductos.refresh()
    },
    fetchTotalRows () {
      const filterKey = this.tipoStock === 'maximo' ? 'maximo' : 'minimo'
      this.axios.get(this.API + 'productos/stock?idAlmacen=' + this.almacen.IdAlmacen + '&filter[' + filterKey + ']=1&count=1')
        .then((response) => {
          this.totalRows = response.data.total
        })
    },
    stockVariant (item) {
      if (this.tipoStock === 'maximo') {
        return (item.StockMaximo > 0 && item.stock > item.StockMaximo) ? 'warning' : 'success'
      }
      return (item.stock < item.StockMinimo) ? 'danger' : 'success'
    },
		myProvider (ctx) {
      const stockParam = this.tipoStock === 'maximo'
        ? 'stockmaximo=1&condicionstockmaximo=1'
        : 'stockminimo=1&condicionstockminimo=1'

      let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+encodeURIComponent(ctx.filter||'')+'&'+stockParam+'&sortBy='+ctx.sortBy
      url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      this.urlExportar = this.API + 'reporte/stock/minimo?idAlmacen=' + this.almacen.IdAlmacen + '&tipo=' + this.tipoStock + '&filter=' + encodeURIComponent(ctx.filter || '') + '&sortBy=' + ctx.sortBy + '&sortDesc=' + (ctx.sortDesc ? 1 : 0)
      let promise = this.axios.get(url)

      return promise.then((result) => {
        this.items = result.data
        this.items.map(r=>{
          if (this.tipoStock === 'maximo') {
            if (r.StockMaximo > 0 && r.stock > r.StockMaximo) {
              r._rowVariant = 'warning'
            }
          } else {
            if (r.stock < r.StockMinimo) {
              r._rowVariant = 'danger'
            }
          }
        })
        return(this.items)
      }).catch(error => {
        return []
      })
    }
  },
  components: {
      ProductoForm
  }
}
</script>

<style scoped>
.reporte-stock {
  background: #f4f6f9;
  min-height: 100%;
}

.page-header .header-icon {
  width: 46px;
  height: 46px;
  border-radius: 12px;
  background: linear-gradient(135deg, #4e73df, #6f86f7);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
}

.tipo-badge {
  font-size: 0.9rem;
  font-weight: 600;
  border-radius: 20px;
}

.filter-card,
.table-card {
  border: none;
  border-radius: 14px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
}

.filter-label {
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  color: #6c757d;
  margin-bottom: 6px;
  display: block;
}

.modern-select,
.search-input {
  border-radius: 8px;
  border: 1px solid #e2e5ec;
  height: calc(1.5em + 0.75rem + 4px);
}

.search-group .clear-btn {
  border-radius: 0 8px 8px 0;
  border: 1px solid #e2e5ec;
  border-left: none;
}

.summary-strip {
  background: #fff;
  border-radius: 10px;
  padding: 10px 18px;
  box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
  font-size: 0.9rem;
}

.total-pill {
  font-size: 0.85rem;
  font-weight: 600;
  padding: 6px 14px;
  color: #4e73df;
  background: #eef1fd;
}

.modern-table {
  font-size: 0.9rem;
}

.modern-table >>> .modern-thead th {
  background: #f8f9fc;
  border-bottom: 2px solid #edf0f7;
  color: #5a6272;
  font-weight: 600;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  white-space: nowrap;
}

.modern-table >>> tbody tr {
  transition: background 0.15s ease;
}

.modern-table >>> tbody tr:hover {
  background: #f8f9fd !important;
}

.stock-badge {
  font-size: 0.8rem;
  font-weight: 700;
  padding: 5px 12px;
  min-width: 42px;
  display: inline-block;
}

.pagination-footer {
  border-top: 1px solid #eef0f5;
}

.modern-pagination >>> .page-link {
  border-radius: 6px;
  margin: 0 2px;
  border: none;
  color: #4e73df;
}

.modern-pagination >>> .page-item.active .page-link {
  background: #4e73df;
  color: #fff;
}

.export-btn {
  border-radius: 10px;
  font-weight: 600;
  box-shadow: 0 4px 14px rgba(40, 167, 69, 0.35);
}
</style>