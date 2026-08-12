<template>
  <div class="fondo">
    <!-- ================= HEADER ================= -->
    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">
          <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
            <path d="M9 15l2 2 4-4"></path>
          </svg>
        </div>
        <div>
          <h2 class="page-title">Reporte de Compras por Cliente</h2>
          <p class="page-subtitle">Consulta el resumen de productos adquiridos por un cliente durante un período determinado.</p>
        </div>
      </div>

      <div class="breadcrumb">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
          <polyline points="9 22 9 12 15 12 15 22"></polyline>
        </svg>
        <span class="crumb-sep">›</span>
        <span>Reportes</span>
        <span class="crumb-sep">›</span>
        <span class="crumb-active">Compras por Cliente</span>
      </div>
    </div>

    <!-- ================= FILTROS ================= -->
    <b-card class="shadow-card filtros-card" no-body>
      <div class="card-section-header" @click="mostrarFiltros = !mostrarFiltros">
        <div class="card-section-title">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
          </svg>
          <span>Filtros de Búsqueda</span>
        </div>
        <button type="button" class="toggle-filtros">
          {{ mostrarFiltros ? 'Ocultar filtros' : 'Mostrar filtros' }}
          <svg :class="['chevron', { 'chevron-up': mostrarFiltros }]" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"></polyline>
          </svg>
        </button>
      </div>

      <b-collapse v-model="mostrarFiltros">
        <div class="card-section-body">
          <b-row>
            <b-col md="4">
              <b-form-group label="Buscar producto">
                <div class="input-icon-wrap">
                  <svg class="input-icon" viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  </svg>
                  <b-form-input
                    v-model="filter"
                    class="pretty-input with-icon"
                    placeholder="Buscar por nombre de producto..."
                    @input="currentPage = 1; $refs.tablaProductos.refresh();"
                  />
                </div>
              </b-form-group>
            </b-col>

            <b-col md="4">
              <b-form-group label="Cliente">
                <autocomplete
                  ref="inputCliente"
                  :url="API + 'clientes'"
                  anchor="Cliente"
                  initValue=""
                  label="writer"
                  placeholder=""
                  :on-select="setCliente"
                  :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                  :classes="{ input: 'form-control pretty-input' }"
                ></autocomplete>
              </b-form-group>
            </b-col>

            <b-col md="4">
              <b-form-group label="Almacén">
                <autocomplete
                  required="required"
                  ref="inputAlmacen"
                  :url="API + 'almacenes'"
                  anchor="Almacen"
                  initValue="TODOS"
                  label="writer"
                  placeholder=""
                  :on-select="setAlmacen"
                  :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                  :classes="{ input: 'form-control pretty-input' }"
                ></autocomplete>
              </b-form-group>
            </b-col>
          </b-row>

          <b-row class="align-items-end">
            <b-col md="3">
              <b-form-group label="Fecha Inicio">
                <b-form-input
                  v-model="rango.fechaDesde"
                  type="date"
                  step="1"
                  class="pretty-input"
                  @input="val => $refs.tablaProductos.refresh()"
                  placeholder="Fecha Inicio"
                ></b-form-input>
              </b-form-group>
            </b-col>

            <b-col md="3">
              <b-form-group label="Fecha Fin">
                <b-form-input
                  v-model="rango.fechaHasta"
                  type="date"
                  step="1"
                  class="pretty-input"
                  @input="val => $refs.tablaProductos.refresh()"
                  placeholder="Fecha Fin"
                ></b-form-input>
              </b-form-group>
            </b-col>

            <b-col md="3">
              <b-form-group label="Limpiar filtros">
                <b-button class="btn-limpiar" block @click="limpiarFiltros">
                  <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="1 4 1 10 7 10"></polyline>
                    <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                  </svg>
                  Limpiar
                </b-button>
              </b-form-group>
            </b-col>

            <b-col md="3">
              <b-form-group label="Mostrar">
                <div class="mostrar-select-wrap">
                  <b-form-select
                    :options="pageOptions"
                    v-model="perPage"
                    class="pretty-input"
                    @input="currentPage = 1"
                  />
                  <span class="mostrar-label">registros por página</span>
                </div>
              </b-form-group>
            </b-col>
          </b-row>
        </div>
      </b-collapse>
    </b-card>

    <!-- ================= RESULTADOS ================= -->
    <b-card class="shadow-card resultados-card" no-body>
      <div class="card-section-header">
        <div class="card-section-title">
          <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="8" y1="6" x2="21" y2="6"></line>
            <line x1="8" y1="12" x2="21" y2="12"></line>
            <line x1="8" y1="18" x2="21" y2="18"></line>
            <line x1="3" y1="6" x2="3.01" y2="6"></line>
            <line x1="3" y1="12" x2="3.01" y2="12"></line>
            <line x1="3" y1="18" x2="3.01" y2="18"></line>
          </svg>
          <div>
            <div>Resultados</div>
            <div class="resultados-count">{{ totalRows }} resultado{{ totalRows === 1 ? '' : 's' }} encontrado{{ totalRows === 1 ? '' : 's' }}</div>
          </div>
        </div>

        <div class="total-general-badge">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
            <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
          </svg>
          Total Comprado: <b>S/. {{ Number(totalGeneral).toFixed(2) }}</b>
        </div>
      </div>

      <div class="card-section-body no-top-pad">
        <b-table
          small
          ref="tablaProductos"
          :filter="filter"
          :sort-by.sync="sortBy"
          :sort-desc.sync="sortDesc"
          hover
          id="my-tableDeuda"
          class="tabla-bonita"
          :busy.sync="isBusy"
          :items="myProvider"
          :fields="fields"
          :per-page="perPage"
          :current-page="currentPage"
        >
          <template #table-busy>
            <div class="text-center my-3">
              <b-spinner small class="align-middle mr-2"></b-spinner>
              <strong>Cargando...</strong>
            </div>
          </template>
        </b-table>

        <div class="footer-row">
          <div class="mostrando-text">
            Mostrando {{ totalRows === 0 ? 0 : (currentPage - 1) * perPage + 1 }}
            a {{ Math.min(currentPage * perPage, totalRows) }} de {{ totalRows }} registros
          </div>
          <b-pagination
            :total-rows="totalRows"
            :per-page="perPage"
            v-model="currentPage"
            class="my-0 pretty-pagination"
            first-number
            last-number
          />
        </div>
      </div>
    </b-card>

    <!-- ================= EXPORTAR ================= -->
    <div class="exportar-wrap">
      <b-button
        class="btn-exportar"
        :href="
        '/api/index.php/reporte/comprasporcliente'
        + '?idCliente=' + cliente.IdCliente
        + '&cliente=' + encodeURIComponent(cliente.Cliente)
        + '&idalmacen=' + almacen.IdAlmacen
        + '&q=' + encodeURIComponent(filter)
        + '&fechaDesde=' + rango.fechaDesde
        + '&fechaHasta=' + rango.fechaHasta
        "
        target="_blank"
      >
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="9" y1="14" x2="15" y2="18"></line>
          <line x1="15" y1="14" x2="9" y2="18"></line>
        </svg>
        Exportar a Excel
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="ml-2">
          <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
      </b-button>
    </div>
  </div>
</template>

<script>
import config from '@/store/config.js'

// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted () {
    // this.axios.get(this.API + 'clientes/deuda/count?idalmacen=' + this.almacen.IdAlmacen ).then((response) => {
    //   this.totalRows = response.data.total
    // })
  },
  data () {
    return {
      API: config.API,
      pageOptions: [50, 100, 150, 200, 300],
      filter: '',
      sortBy: 'Producto',
      sortDesc: false,
      items: [],
      isBusy: false,
      mostrarFiltros: true,
        fields: [
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'Cantidad', label: 'Cantidad Comprada', sortable: true },
        { key: 'Total', label: 'Total', sortable: true }
        ],
      totalRows: 50,
      perPage: 50,
      currentPage: 1,
      almacen: {
        IdAlmacen: null,
        Almacen: 'TODOS'
      },
      cliente: {
        IdCliente: null,
        Cliente: ''
      },
      rango: {
        fechaDesde: this.$moment().format('YYYY-MM-01'),
        fechaHasta: this.$moment().format('YYYY-MM-DD')
      },
      totalGeneral: 0
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
    setCliente (obj) {
      this.cliente = obj
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    limpiarFiltros () {
      this.filter = ''
      // this.vendedor.Usuario = undefined;
      this.cliente = {
        IdCliente: null,
        Cliente: ''
      }
      this.almacen = {
        IdAlmacen: null,
        Almacen: 'TODOS'
      }
      this.$refs.inputAlmacen.setValue('TODOS')
      this.rango.fechaDesde = this.$moment().format('YYYY-01-01')
      this.rango.fechaHasta = this.$moment().format('YYYY-MM-DD')
      // this.$refs.inputVendedor.setValue('TODOS')
      this.$refs.inputCliente.setValue('')
      this.$refs.inputAlmacen.setValue('')
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    async cargarTotal () {
      const response = await this.axios.get(
        this.API +
          'cliente/comprasproducto/total?idCliente=' + this.cliente.IdCliente +
          (this.almacen.IdAlmacen ? '&idalmacen=' + this.almacen.IdAlmacen : '') +
          '&q=' + this.filter +
          '&fechaDesde=' + this.rango.fechaDesde +
          '&fechaHasta=' + this.rango.fechaHasta
      )

      this.totalGeneral = response.data.Total || 0
    },
    async cargarCount () {
      const response = await this.axios.get(
        this.API +
          'cliente/comprasproducto/count?idCliente=' + this.cliente.IdCliente +
          (this.almacen.IdAlmacen ? '&idalmacen=' + this.almacen.IdAlmacen : '') +
          '&q=' + this.filter +
          '&fechaDesde=' + this.rango.fechaDesde +
          '&fechaHasta=' + this.rango.fechaHasta
      )

      this.totalRows = response.data.total || 0
    },
    myProvider (ctx) {
      const promise = this.axios.get(
        this.API +
          'cliente/comprasproducto?idCliente=' + this.cliente.IdCliente +
          (this.almacen.IdAlmacen ? '&idalmacen=' + this.almacen.IdAlmacen : '') +
          '&q=' + this.filter +
          '&limit=' + ctx.perPage +
          '&page=' + ctx.currentPage +
          '&fechaDesde=' + this.rango.fechaDesde +
          '&fechaHasta=' + this.rango.fechaHasta
      )

      return promise.then(async (response) => {
        // Registros de la tabla
        this.items = response.data

        // Cargar la suma total
        await Promise.all([
          this.cargarTotal(),
          this.cargarCount()
        ])

        // Retornar los registros al b-table
        return this.items
      }).catch(error => {
        console.error(error)

        return []
      })
    }
  },
  components: {
    // ProductoForm
  }
}
</script>

<style scoped>
.fondo {
  background: #F1F5F9;
  padding: 24px 28px 40px;
  min-height: 100%;
}

/* ---------- HEADER ---------- */
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 22px;
  flex-wrap: wrap;
  gap: 12px;
}
.page-header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}
.page-icon {
  width: 48px;
  height: 48px;
  min-width: 48px;
  border-radius: 50%;
  background: #2563EB;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
}
.page-title {
  margin: 0;
  font-weight: 700;
  font-size: 1.6rem;
  color: #0F172A;
}
.page-subtitle {
  margin: 2px 0 0;
  color: #64748B;
  font-size: 0.92rem;
}
.breadcrumb {
  display: flex;
  align-items: center;
  gap: 6px;
  color: #64748B;
  font-size: 0.9rem;
  margin-top: 8px;
}
.crumb-sep { color: #94A3B8; }
.crumb-active { color: #2563EB; font-weight: 600; }

/* ---------- CARDS ---------- */
.shadow-card {
  border: 1px solid #E2E8F0;
  border-radius: 14px;
  box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
  margin-bottom: 20px;
  overflow: hidden;
}
.filtros-card {
  overflow: visible;
}

.card-section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 18px 22px;
  border-bottom: 1px solid #EEF2F6;
  cursor: default;
}
.filtros-card .card-section-header {
  cursor: pointer;
}
.card-section-title {
  display: flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: 1.05rem;
  color: #0F172A;
}
.card-section-title svg { color: #2563EB; flex-shrink: 0; }
.resultados-count {
  font-weight: 400;
  font-size: 0.85rem;
  color: #64748B;
  margin-top: 2px;
}
.toggle-filtros {
  background: none;
  border: none;
  color: #2563EB;
  font-weight: 600;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 4px;
}
.toggle-filtros:hover { text-decoration: underline; }
.chevron { transition: transform 0.2s ease; }
.chevron-up { transform: rotate(180deg); }

.card-section-body {
  padding: 22px;
}
.card-section-body.no-top-pad {
  padding-top: 14px;
}

/* ---------- FORM CONTROLS ---------- */
.filtros-card >>> label {
  font-weight: 600;
  font-size: 0.85rem;
  color: #334155;
  margin-bottom: 6px;
}
.pretty-input,
.filtros-card >>> .form-control,
.filtros-card >>> select.form-control {
  border-radius: 9px;
  border: 1px solid #D9E1EC;
  padding: 10px 12px;
  font-size: 0.9rem;
  height: auto;
}
.pretty-input:focus,
.filtros-card >>> .form-control:focus {
  border-color: #2563EB;
  box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.input-icon-wrap { position: relative; }
.input-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94A3B8;
  pointer-events: none;
}
.with-icon { padding-right: 34px; }

.btn-limpiar {
  background: #fff;
  border: 1px solid #D9E1EC;
  color: #334155;
  border-radius: 9px;
  font-weight: 600;
  font-size: 0.88rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  padding: 10px 12px;
}
.btn-limpiar:hover {
  background: #F8FAFC;
  color: #0F172A;
}
.mostrar-select-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.mostrar-select-wrap select { max-width: 100px; }
.mostrar-label {
  font-size: 0.82rem;
  color: #64748B;
  white-space: nowrap;
}

/* ---------- TOTAL BADGE ---------- */
.total-general-badge {
  background: #DBEAFE;
  color: #1E40AF;
  border-radius: 10px;
  padding: 10px 16px;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
}
.total-general-badge b { font-weight: 700; }

/* ---------- TABLE ---------- */
.tabla-bonita >>> thead th {
  background: #1E3A8A;
  color: #fff;
  border: none;
  font-weight: 600;
  font-size: 0.85rem;
  padding: 12px 16px;
  vertical-align: middle;
}
.tabla-bonita >>> tbody td {
  padding: 12px 16px;
  font-size: 0.9rem;
  color: #1E293B;
  border-color: #EEF2F6;
  vertical-align: middle;
}
.tabla-bonita >>> tbody tr:hover {
  background: #F5F9FF;
}
.tabla-bonita >>> tbody tr:last-child {
  background: #EFF6FF;
  font-weight: 700;
  color: #0F172A;
}

/* ---------- FOOTER / PAGINATION ---------- */
.footer-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 14px;
}
.mostrando-text {
  color: #64748B;
  font-size: 0.85rem;
}
.pretty-pagination >>> .page-link {
  border-radius: 8px;
  margin: 0 2px;
  border: 1px solid #E2E8F0;
  color: #334155;
}
.pretty-pagination >>> .page-item.active .page-link {
  background-color: #1E3A8A;
  border-color: #1E3A8A;
  color: #fff;
}

/* ---------- EXPORT BUTTON ---------- */
.exportar-wrap {
  display: flex;
  justify-content: center;
  margin-top: 8px;
}
.btn-exportar {
  background: #16A34A;
  border: none;
  color: #fff;
  font-weight: 700;
  font-size: 1rem;
  padding: 14px 32px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 10px;
  box-shadow: 0 4px 10px rgba(22, 163, 74, 0.25);
}
.btn-exportar:hover {
  background: #15803D;
  color: #fff;
}
</style>
