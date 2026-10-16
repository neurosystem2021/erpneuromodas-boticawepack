<template>
<div class="printer-category-module">
  <printer-delivery-settings />
  <section class="management-grid">
    <article class="management-card">
      <div class="management-toolbar">
        <div class="management-search">
          <i class="fas fa-search" aria-hidden="true"></i>
          <input
            v-model="filter"
            type="search"
            autocomplete="off"
            placeholder="Buscar categoría"
            aria-label="Buscar categoría"
            @input="currentPage = 1"
          >
          <button v-if="filter" type="button" title="Limpiar búsqueda" @click="filter = ''">
            <i class="fas fa-times" aria-hidden="true"></i>
          </button>
        </div>
        <span class="management-count"><i class="fas fa-tags" aria-hidden="true"></i> {{ totalRows }}</span>
        <select v-model.number="perPage" class="rows-select" title="Filas por página" @change="currentPage = 1">
          <option v-for="option in pageOptions" :key="'cat-' + option" :value="option">{{ option }}</option>
        </select>
        <button type="button" class="primary-action" @click="nuevaCategoria">
          <i class="fas fa-plus" aria-hidden="true"></i>
          <span>Nueva categoría</span>
        </button>
      </div>

      <div class="management-table-shell">
        <b-table
          id="category-printer-table"
          ref="tablaCategorias"
          :filter="filter"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          :busy.sync="isBusy"
          :items="myProvider"
          :fields="fields"
          :per-page="perPage"
          :current-page="currentPage"
          small
          hover
          responsive
          show-empty
          empty-text="No hay categorías para mostrar"
          empty-filtered-text="No se encontraron categorías"
          @row-clicked="rowClick"
        >
          <template slot="NombreImpresora" slot-scope="data">
            <span v-if="data.item.NombreImpresora" class="printer-name"><i class="fas fa-print" aria-hidden="true"></i> {{ data.item.NombreImpresora }}</span>
            <span v-else class="not-assigned"><i class="fas fa-unlink" aria-hidden="true"></i> Sin asignar</span>
          </template>
          <template slot="Delivery" slot-scope="data">
            <span :class="['status-chip', Number(data.item.MostrarDelivery) === 1 ? 'enabled' : 'disabled']">
              <i :class="Number(data.item.MostrarDelivery) === 1 ? 'fas fa-check' : 'fas fa-minus'" aria-hidden="true"></i>
            </span>
          </template>
          <template slot="PuntoVenta" slot-scope="data">
            <span :class="['status-chip', Number(data.item.MostrarPuntoVenta) === 1 ? 'enabled' : 'disabled']">
              <i :class="Number(data.item.MostrarPuntoVenta) === 1 ? 'fas fa-check' : 'fas fa-minus'" aria-hidden="true"></i>
            </span>
          </template>
          <template slot="OrdenPuntoVenta" slot-scope="data">
            <span v-if="Number(data.item.MostrarPuntoVenta) === 1" class="order-pill">{{ data.item.Orden }}</span>
            <span v-else class="empty-value">—</span>
          </template>
          <template slot="EsOferta" slot-scope="data">
            <span v-if="Number(data.item.EsOferta) === 1" class="offer-chip"><i class="fas fa-star" aria-hidden="true"></i></span>
            <span v-else class="empty-value">—</span>
          </template>
          <template slot="Acciones" slot-scope="data">
            <button type="button" class="edit-row-action" title="Editar categoría" aria-label="Editar categoría" @click.stop="rowClick(data.item)">
              <i class="fas fa-pencil-alt" aria-hidden="true"></i>
            </button>
          </template>
          <template slot="table-busy">
            <div class="table-loading"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Cargando categorías...</div>
          </template>
        </b-table>
      </div>

      <div class="management-footer">
        <span>Página {{ currentPage }} de {{ paginasCategorias }}</span>
        <b-pagination v-model="currentPage" :total-rows="totalRows" :per-page="perPage" :limit="5" class="mb-0" />
      </div>
    </article>

    <article class="management-card">
      <div class="management-toolbar">
        <div class="management-search">
          <i class="fas fa-search" aria-hidden="true"></i>
          <input
            v-model="filterImpresora"
            type="search"
            autocomplete="off"
            placeholder="Buscar impresora"
            aria-label="Buscar impresora"
            @input="currentPageImpresora = 1"
          >
          <button v-if="filterImpresora" type="button" title="Limpiar búsqueda" @click="filterImpresora = ''">
            <i class="fas fa-times" aria-hidden="true"></i>
          </button>
        </div>
        <span class="management-count"><i class="fas fa-print" aria-hidden="true"></i> {{ totalRowsImpresora }}</span>
        <select v-model.number="perPageImpresora" class="rows-select" title="Filas por página" @change="currentPageImpresora = 1">
          <option v-for="option in pageOptions" :key="'printer-' + option" :value="option">{{ option }}</option>
        </select>
        <button type="button" class="primary-action" @click="nuevaImpresora">
          <i class="fas fa-plus" aria-hidden="true"></i>
          <span>Nueva impresora</span>
        </button>
      </div>

      <div class="management-table-shell">
        <b-table
          id="printer-table"
          ref="tablaImpresoras"
          :filter="filterImpresora"
          :sort-by.sync="sortByImpresora"
          :sort-desc.sync="sortDescImpresora"
          :busy.sync="isBusyImpresora"
          :items="myProviderImpresora"
          :fields="fieldsImpresora"
          :per-page="perPageImpresora"
          :current-page="currentPageImpresora"
          small
          hover
          responsive
          show-empty
          empty-text="No hay impresoras para mostrar"
          empty-filtered-text="No se encontraron impresoras"
          @row-clicked="rowClickImpresora"
        >
          <template slot="Ip" slot-scope="data">
            <span class="ip-value"><i class="fas fa-network-wired" aria-hidden="true"></i> {{ data.item.Ip || 'Sin IP' }}</span>
          </template>
          <template slot="Cocina" slot-scope="data">
            <span :class="['kitchen-chip', Number(data.item.EsCocina) === 1 ? 'enabled' : 'disabled']">
              <i :class="Number(data.item.EsCocina) === 1 ? 'fas fa-utensils' : 'fas fa-minus'" aria-hidden="true"></i>
              {{ Number(data.item.EsCocina) === 1 ? 'Sí' : 'No' }}
            </span>
          </template>
          <template slot="Acciones" slot-scope="data">
            <button type="button" class="edit-row-action" title="Editar impresora" aria-label="Editar impresora" @click.stop="rowClickImpresora(data.item)">
              <i class="fas fa-pencil-alt" aria-hidden="true"></i>
            </button>
          </template>
          <template slot="table-busy">
            <div class="table-loading"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Cargando impresoras...</div>
          </template>
        </b-table>
      </div>

      <div class="management-footer">
        <span>Página {{ currentPageImpresora }} de {{ paginasImpresoras }}</span>
        <b-pagination v-model="currentPageImpresora" :total-rows="totalRowsImpresora" :per-page="perPageImpresora" :limit="5" class="mb-0" />
      </div>
    </article>
  </section>

  <categoria-impresora-form ref="categoriaForm" :categoria-update="categoriaSelect" :cbGuardar="actualizarTablaCategorias"></categoria-impresora-form>
  <impresora-form ref="impresoraForm" :impresora-update="impresoraSelect" :cbGuardar="actualizarTablaImpresoras"></impresora-form>
</div>
</template>

<script>
import PrinterDeliverySettings from '@/components/PrinterDeliverySettings.vue';
import config from '@/store/config.js'
import CategoriaImpresoraForm from '@/components/CategoriaImpresoraForm'
import ImpresoraForm from '@/components/ImpresoraForm'
export default {
  mounted() {
    this.axios.get(this.API + 'categorias/count').then((response) => {
      this.totalRows = response.data.total
    })
    this.axios.get(this.API + 'impresoras/count').then((response) => {
      this.totalRowsImpresora = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 10, 25, 50, 100 ],
      filter: null,
      sortBy: 'MostrarPuntoVenta',
      sortDesc: true,
      categoriaSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProductoCategoria', label: 'ID', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'NombreImpresora', label: 'Impresora', sortable: true },
            { key: 'Delivery', label: 'Delivery', sortable: true , class:'text-center'},
            { key: 'PuntoVenta', label: 'P. venta', sortable: true , class:'text-center'},
            { key: 'OrdenPuntoVenta', label: 'Orden', sortable: true , class:'text-center'},
            { key: 'EsOferta', label: 'Oferta', class:'text-center'},
            { key: 'Acciones', label: '', class:'text-center action-column'}
      ],
      totalRows: 50,
      perPage: 25,
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
            { key: 'Acciones', label: '', class:'text-center action-column'}
      ],
      totalRowsImpresora: 50,
      perPageImpresora: 25,
      currentPageImpresora: 1
    }
  },
  computed: {
    paginasCategorias() {
      return Math.max(1, Math.ceil(Number(this.totalRows || 0) / Number(this.perPage || 1)))
    },
    paginasImpresoras() {
      return Math.max(1, Math.ceil(Number(this.totalRowsImpresora || 0) / Number(this.perPageImpresora || 1)))
    }
  },
  methods: {
      rowClick (item, index) {
        this.categoriaSelect = item
        this.$refs.categoriaForm.show = true;
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
      nuevaImpresora () {
          this.impresoraSelect = {}
          this.$refs.impresoraForm.show = true
          this.$refs.impresoraForm.nuevo = true;
      },
      nuevaCategoria () {
          this.categoriaSelect = {}
          this.$refs.categoriaForm.show = true
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
      }, myProviderImpresora (ctx) {
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
      PrinterDeliverySettings,
      CategoriaImpresoraForm,
      ImpresoraForm
  }
}
</script>

<style scoped>
.printer-category-module {
  --module-petrol: #0a4c5d;
  --module-petrol-2: #0b7184;
  --module-cyan: #11a8c8;
  --module-blue: #4255e7;
  min-height: calc(100vh - 118px);
  padding: 16px 18px 24px;
  background:
    radial-gradient(circle at 8% 4%, rgba(17, 168, 200, 0.08), transparent 29%),
    linear-gradient(135deg, #f5fbfd 0%, #f7f8ff 62%, #fff9f5 100%);
}

.management-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(420px, 0.8fr);
  gap: 14px;
  width: 100%;
}

.management-card {
  min-width: 0;
  overflow: hidden;
  border: 1px solid #c9e0e9;
  border-radius: 17px;
  background: rgba(255, 255, 255, 0.97);
  box-shadow: 0 14px 34px rgba(15, 65, 86, 0.11);
}

.management-toolbar {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px;
  border-bottom: 1px solid #d9e7ed;
  background: linear-gradient(120deg, rgba(235, 250, 253, 0.96), rgba(244, 245, 255, 0.96));
}

.management-search {
  position: relative;
  display: flex;
  flex: 1 1 220px;
  align-items: center;
  min-width: 140px;
}

.management-search > i {
  position: absolute;
  left: 13px;
  z-index: 1;
  color: #0995b2;
}

.management-search input {
  width: 100%;
  height: 39px;
  padding: 0 39px;
  border: 1px solid #c8dce6;
  border-radius: 11px;
  outline: none;
  background: #fff;
  color: #203b51;
  font-size: 0.86rem;
  transition: border-color 0.16s ease, box-shadow 0.16s ease;
}

.management-search input:focus {
  border-color: var(--module-cyan);
  box-shadow: 0 0 0 3px rgba(17, 168, 200, 0.12);
}

.management-search button {
  position: absolute;
  right: 6px;
  width: 28px;
  height: 28px;
  border: 0;
  border-radius: 8px;
  background: #edf3f6;
  color: #647b8c;
  cursor: pointer;
}

.management-count {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  height: 34px;
  padding: 0 10px;
  border-radius: 9px;
  background: #dff5f2;
  color: #087763;
  font-size: 0.78rem;
  font-weight: 900;
  white-space: nowrap;
}

.rows-select {
  width: 62px;
  height: 34px;
  border: 1px solid #c8dce6;
  border-radius: 9px;
  background: #fff;
  color: #29465c;
  font-weight: 750;
}

.primary-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  height: 38px;
  padding: 0 13px;
  border: 0;
  border-radius: 10px;
  background: linear-gradient(135deg, var(--module-cyan), var(--module-blue));
  color: #fff;
  font-size: 0.8rem;
  font-weight: 900;
  white-space: nowrap;
  cursor: pointer;
  box-shadow: 0 7px 15px rgba(37, 89, 171, 0.2);
}

.management-table-shell {
  min-height: 365px;
  overflow: hidden;
}

.management-table-shell .table-responsive { margin: 0; }

#category-printer-table,
#printer-table {
  margin: 0;
  color: #29445a;
}

#category-printer-table { min-width: 760px; }
#printer-table { min-width: 610px; }

#category-printer-table /deep/ thead th,
#printer-table /deep/ thead th {
  height: 43px;
  padding: 9px 10px;
  border: 0;
  background: var(--module-petrol);
  color: #fff;
  font-size: 0.69rem;
  font-weight: 900;
  letter-spacing: 0.035em;
  text-transform: uppercase;
  vertical-align: middle;
  white-space: nowrap;
}

#category-printer-table /deep/ tbody tr,
#printer-table /deep/ tbody tr {
  cursor: pointer;
  transition: background 0.15s ease;
}

#category-printer-table /deep/ tbody tr:nth-child(even),
#printer-table /deep/ tbody tr:nth-child(even) { background: #f7fafc; }

#category-printer-table /deep/ tbody tr:hover,
#printer-table /deep/ tbody tr:hover { background: #eaf8fb; }

#category-printer-table /deep/ tbody td,
#printer-table /deep/ tbody td {
  height: 54px;
  padding: 8px 10px;
  border-top: 1px solid #e1ebf0;
  font-size: 0.78rem;
  vertical-align: middle;
}

.printer-name,
.not-assigned,
.ip-value {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-weight: 750;
}

.printer-name { color: #31566d; }
.not-assigned { color: #b44b53; }
.ip-value { color: #43647a; white-space: nowrap; }

.status-chip,
.order-pill,
.offer-chip,
.kitchen-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 29px;
  height: 27px;
  padding: 0 8px;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 900;
}

.status-chip.enabled,
.kitchen-chip.enabled { background: #d8f7e8; color: #08765b; }
.status-chip.disabled,
.kitchen-chip.disabled { background: #edf2f5; color: #8998a4; }
.order-pill { background: #def3f9; color: #09748d; }
.offer-chip { background: #fff0bc; color: #9b6700; }
.empty-value { color: #a1aeb8; }

.edit-row-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border: 0;
  border-radius: 10px;
  background: linear-gradient(135deg, #119fbe, #4257e7);
  color: #fff;
  cursor: pointer;
  box-shadow: 0 6px 13px rgba(32, 82, 153, 0.2);
}

.action-column { width: 54px; }
.table-loading { padding: 44px 12px; color: var(--module-petrol-2); font-weight: 800; text-align: center; }
.table-loading i { margin-right: 6px; }

.management-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  min-height: 49px;
  padding: 7px 10px;
  border-top: 1px solid #dce8ee;
  color: #688093;
  font-size: 0.75rem;
  font-weight: 750;
}

.management-footer /deep/ .page-link {
  min-width: 31px;
  border-color: #d3e2e9;
  color: #0c7287;
  text-align: center;
}

.management-footer /deep/ .page-item.active .page-link {
  border-color: var(--module-petrol-2);
  background: linear-gradient(135deg, var(--module-petrol-2), var(--module-blue));
}

@media (max-width: 1280px) {
  .management-grid { grid-template-columns: 1fr; }
  .management-table-shell { min-height: 260px; }
}

@media (max-width: 640px) {
  .printer-category-module { padding: 8px; }
  .management-grid { gap: 10px; }
  .management-card { border-radius: 14px; }
  .management-toolbar { flex-wrap: wrap; }
  .management-search { flex-basis: 100%; }
  .primary-action { flex: 1 1 auto; }
  .management-count { margin-right: auto; }
  .management-table-shell { min-height: 220px; }
  .management-footer { align-items: flex-start; flex-direction: column; }
}
</style>
