<template>
  <div class="pos-jobi">
    <b-container class="bv-example-row pos-container" fluid>
      <div class="pos-card">

        <!-- ================= CABECERA: Buscar / Mostrar ================= -->
        <div class="pos-panel pos-panel-header">
          <b-row class="align-items-end pos-header-row">
            <b-col md="6" class="pos-header-col-fields">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-search pos-label-icon"></i> Buscar</label>
                <b-input-group class="pos-search-group">
                  <span class="pos-cliente-pin"><i class="fas fa-box-open"></i></span>
                  <b-form-input v-model="filter" placeholder="Buscar producto..." @input="currentPage = 1" />
                  <b-input-group-append>
                    <b-btn class="pos-btn-limpiar" :disabled="!filter" @click="filter = ''"><i class="fas fa-times"></i> Limpiar</b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>

            <b-col md="3" class="pos-header-col-fields">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-list-ol pos-label-icon"></i> Mostrar</label>
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>

            <b-col md="3" class="d-flex align-items-center justify-content-end">
              <div class="pos-stat-mini">
                <div class="pos-stat-mini-icon"><i class="fas fa-cubes"></i></div>
                <div>
                  <div class="pos-stat-mini-num">{{ totalRows }}</div>
                  <div class="pos-stat-mini-label">Artículos</div>
                </div>
              </div>
            </b-col>
          </b-row>
        </div>

        <!-- ================= TABLA ================= -->
        <div class="pos-table-card">
          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            responsive
            @row-clicked="rowClick"
            id="my-table"
            :busy.sync="isBusy"
            :items="myProvider"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">

            <template slot="table-busy">
              <div class="pos-table-busy">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Cargando productos...</span>
              </div>
            </template>

            <template slot="empty">
              <div class="pos-table-empty">
                <i class="fas fa-box-open"></i>
                <span>No se encontraron productos.</span>
              </div>
            </template>
          </b-table>

          <b-row class="pos-pagination-row">
            <b-col md="6" class="my-1">
              <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
            </b-col>
          </b-row>
        </div>

        <producto-form ref="productoForm" :producto-update="productoSelect" :cbGuardar="actualizarTabla"></producto-form>

        <!-- ================= ACCIONES ================= -->
        <div class="pos-main-actions">
          <b-button class="pos-btn-pagar" size="lg" @click="nuevoProducto">
            <i class="fas fa-plus-circle pos-btn-icon"></i>
            <span>Nuevo Producto</span>
          </b-button>
        </div>

      </div>
    </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
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
            { key: 'ProductoPresentacion', label: 'Presentación', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
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
        let url = this.API + 'productos?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      ProductoForm
  }
}
</script>

<style scoped>
/* =========================================================
   ProductosLista.vue - mismo lenguaje visual JoBi que
   Venta.vue / Movimiento.vue / ProductosCarrito.vue
   ========================================================= */
.pos-jobi {
    --jobi-navy: #00666A;
    --jobi-navy-2: #00666A;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
    --jobi-bg: #EEF3F8;
    background: var(--jobi-bg);
    min-height: 100vh;
    padding-bottom: 10px !important;
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    width: 100%;
}

.pos-container {
    padding-top: 10px !important;
    max-width: 100% !important;
}

.pos-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(11,42,74,.10);
    padding: 12px 16px 16px !important;
}

/* -------- Panel de cabecera navy: Buscar / Mostrar / Total -------- */
.pos-panel-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 16px;
    color: #fff;
}

.pos-panel-header label {
    color: rgba(255,255,255,.72);
    font-size: .70rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.pos-label-icon {
    font-size: .82em;
    opacity: .85;
}

.pos-field >>> input,
.pos-field >>> select,
.pos-field >>> .form-control {
    border-radius: 10px !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 700;
    height: 42px;
    border: none !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}
.pos-field >>> input:focus,
.pos-field >>> .form-control:focus {
    box-shadow: 0 0 0 3px rgba(47,111,237,.30);
}

/* Buscador con pin/icono a la izquierda, estilo pill */
.pos-search-group {
    align-items: center;
}
.pos-cliente-pin {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255,255,255,.12);
    color: #fff;
    margin-right: 6px;
    font-size: 1.05rem;
    flex: 0 0 auto;
}
.pos-btn-limpiar {
    border-radius: 10px !important;
    background: var(--jobi-red) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700;
    height: 42px;
    margin-left: 6px;
    box-shadow: 0 4px 10px rgba(229,72,77,.30);
}
.pos-btn-limpiar:disabled {
    opacity: .45;
}

/* Mini tarjeta con el total de artículos */
.pos-stat-mini {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    padding: 8px 14px;
}
.pos-stat-mini-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: #fff;
    color: var(--jobi-navy);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
    flex: 0 0 auto;
}
.pos-stat-mini-num {
    font-size: 1.15rem;
    font-weight: 900;
    line-height: 1.1;
    color: #fff;
}
.pos-stat-mini-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(255,255,255,.65);
    font-weight: 800;
}

/* -------- Tarjeta que envuelve la tabla -------- */
.pos-table-card {
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
    border: 1px solid #e7edf5;
    overflow-x: auto;
}

.pos-table-card >>> table {
    margin-bottom: 0;
}

.pos-table-card >>> thead th {
    background: var(--jobi-navy);
    color: #fff !important;
    text-transform: uppercase;
    font-size: .70rem;
    letter-spacing: .03em;
    font-weight: 800;
    border: none !important;
    white-space: nowrap;
    vertical-align: middle;
    padding: 10px 8px;
    cursor: pointer;
}
.pos-table-card >>> thead th:first-child { border-top-left-radius: 10px; }
.pos-table-card >>> thead th:last-child { border-top-right-radius: 10px; }

.pos-table-card >>> tbody td {
    vertical-align: middle;
    font-size: .84rem;
    padding: 9px 8px;
    border-color: #eef2f7;
    cursor: pointer;
}
.pos-table-card >>> tbody tr:hover {
    background-color: #f5f9ff !important;
}

.pos-table-busy,
.pos-table-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 34px 16px;
    color: #8296b0;
    text-align: center;
}
.pos-table-busy i,
.pos-table-empty i {
    font-size: 1.9rem;
    color: #c6d4e3;
}
.pos-table-busy span,
.pos-table-empty span {
    font-size: .85rem;
    font-weight: 600;
    max-width: 320px;
}

.pos-pagination-row {
    padding: 10px 8px 4px;
}
.pos-pagination-row >>> .page-link {
    border-radius: 8px !important;
    margin: 0 2px;
    border: 1.5px solid #dbe6f1 !important;
    color: var(--jobi-navy);
    font-weight: 700;
}
.pos-pagination-row >>> .page-item.active .page-link {
    background: var(--jobi-blue) !important;
    border-color: var(--jobi-blue) !important;
    color: #fff !important;
}

/* -------- Botón "Nuevo Producto" -------- */
.pos-main-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 18px 8px 6px;
}
.pos-btn-pagar {
    min-height: 52px;
    border-radius: 999px !important;
    background: var(--jobi-green) !important;
    border: none !important;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 0 30px !important;
    font-size: 1rem;
    box-shadow: 0 10px 22px rgba(23,165,84,.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all .18s ease;
    color: #fff !important;
}
.pos-btn-pagar:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(23,165,84,.40);
    color: #fff !important;
}
.pos-btn-icon {
    font-size: 1.1rem;
}

@media (max-width: 767px) {
    .pos-header-col-fields { margin-bottom: 10px; }
}
</style>