<template>
  <div class="deuda-page">
    <header class="deuda-head">
      <div>
        <h2>Deuda de proveedores</h2>
        <p>Documentos al crédito y su estado de pago.</p>
      </div>
      <div class="acciones">
        <b-button class="btn-export btn-pdf" :disabled="exportando" @click="exportarPdf">
          PDF
        </b-button>
        <b-button class="btn-export btn-excel" :disabled="exportando" @click="exportarExcel">
          {{ exportando ? 'Generando...' : 'Excel' }}
        </b-button>
      </div>
    </header>

    <b-container fluid class="deuda-card">
      <b-row class="toolbar" align-v="end">
        <b-col md="6" class="mb-2">
          <label class="lbl">Buscar</label>
          <b-input-group>
            <b-form-input v-model="filter" placeholder="RUC o proveedor" @input="currentPage = 1" />
            <b-input-group-append>
              <b-btn variant="outline-secondary" :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
            </b-input-group-append>
          </b-input-group>
        </b-col>
        <b-col md="3" offset-md="3" class="mb-2">
          <label class="lbl">Mostrar</label>
          <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1" />
        </b-col>
      </b-row>

      <div class="filtros">
        <button v-for="f in filtros" :key="f.key" type="button" class="chip"
          :class="[f.key ? 'chip-' + f.key : 'chip-todos', { activo: estadoFiltro === f.key }]"
          @click="setEstado(f.key)">
          {{ f.label }} <span class="n">{{ conteoDe(f.key) }}</span>
        </button>
      </div>

      <b-table
        hover
        responsive
        class="tabla"
        ref="tabla"
        id="my-table"
        :filter="filter"
        :sort-by.sync="sortBy"
        :sort-desc.sync="sortDesc"
        :busy.sync="isBusy"
        :items="myProvider"
        :fields="fields"
        :per-page="perPage"
        :current-page="currentPage"
        show-empty
        empty-text="No hay deudas que coincidan con la búsqueda."
        @row-clicked="myRowClickHandler">

        <template slot="Estado" slot-scope="data">
          <span class="estado" :class="'estado-' + estadoDe(data.item)">
            <i></i>{{ estados[estadoDe(data.item)].label }}
          </span>
        </template>

        <template slot="Total" slot-scope="data">
          <span class="num">{{ money(data.item.Total) }}</span>
        </template>

        <template slot="Aplicado" slot-scope="data">
          <span class="num muted">{{ money(data.item.Aplicado) }}</span>
        </template>

        <template slot="Saldo" slot-scope="data">
          <span class="num saldo" :class="{ 'saldo-vencido': estadoDe(data.item) === 'vencido' }">
            {{ money(data.item.Saldo) }}
          </span>
        </template>

        <template slot="FechaCredito" slot-scope="data">
          <div class="venc">
            <span>{{ data.item.FechaCredito ? data.item.FechaCredito.substr(0, 10) : '-' }}</span>
            <small :class="'hint-' + estadoDe(data.item)">{{ vencimientoTexto(data.item) }}</small>
          </div>
        </template>

        <template slot="Letras" slot-scope="data">
          <span v-if="data.item.Letras === 'Ninguna'" class="muted">Ninguna</span>
          <span v-else class="letras">{{ data.item.Letras }} · ver detalle</span>
        </template>
      </b-table>

      <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="pager" />
    </b-container>

    <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showLetras" ok-only ok-title="Cerrar" title="Detalle de deuda">
      <detalle-deuda-proveedor ref="detDeuPro"></detalle-deuda-proveedor>
    </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import DetalleDeudaProveedor from '@/components/DetalleDeudaProveedor.vue'

export default {
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: '',
      sortBy: 'FechaDoc',
      sortDesc: false,
      items: [],
      isBusy: false,
      exportando: false,
      estados: {
        pendiente: { label: 'Pendiente' },
        recibido: { label: 'Recibido' },
        pagado: { label: 'Pagado' },
        vencido: { label: 'Vencido' }
      },
      fields: [
        { key: 'Estado', label: 'Estado' },
        { key: 'Ruc', label: 'RUC', sortable: true },
        { key: 'Proveedor', label: 'Proveedor', sortable: true },
        { key: 'Correlativo', label: 'Documento', sortable: true },
        { key: 'Total', label: 'Total', sortable: true, class: 'text-right' },
        { key: 'Aplicado', label: 'Aplicado', sortable: true, class: 'text-right' },
        { key: 'Saldo', label: 'Saldo', sortable: true, class: 'text-right' },
        { key: 'FechaDoc', label: 'Emisión', sortable: true },
        { key: 'FechaCredito', label: 'Vencimiento', sortable: true },
        { key: 'Letras', label: 'Letras', sortable: true }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      showLetras: false,
      estadoFiltro: '',
      conteo: {},
      filtros: [
        { key: '', label: 'Todos' },
        { key: 'pendiente', label: 'Pendiente' },
        { key: 'recibido', label: 'Recibido' },
        { key: 'pagado', label: 'Pagado' },
        { key: 'vencido', label: 'Vencido' }
      ]
    }
  },
  methods: {
    money(v) {
      return 'S/ ' + Number(v || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    // Convierte 'YYYY-MM-DD' (o con hora) a fecha local a las 00:00
    parseFecha(s) {
      if (!s) return null
      const p = String(s).substr(0, 10).split('-')
      return new Date(+p[0], +p[1] - 1, +p[2])
    },
    diasParaVencer(item) {
      const f = this.parseFecha(item.FechaCredito)
      if (!f) return null
      const hoy = new Date()
      hoy.setHours(0, 0, 0, 0)
      return Math.round((f - hoy) / 86400000)
    },
    /*
     * Reglas de estado (ajústalas a tu negocio):
     *  - Pagado:    saldo <= 0
     *  - Vencido:   saldo > 0 y fecha de vencimiento pasada
     *  - Recibido:  saldo > 0, vigente y con abonos o letras registradas
     *  - Pendiente: saldo > 0, vigente y sin abonos ni letras
     */
    estadoDe(item) {
      if (item.Estado) return item.Estado // viene calculado desde la API
      const saldo = Number(item.Saldo || 0)
      if (saldo <= 0) return 'pagado'
      const dias = this.diasParaVencer(item)
      if (dias !== null && dias < 0) return 'vencido'
      if (Number(item.Aplicado || 0) > 0 || item.Letras !== 'Ninguna') return 'recibido'
      return 'pendiente'
    },
    vencimientoTexto(item) {
      const est = this.estadoDe(item)
      if (est === 'pagado') return 'Cancelado'
      const d = this.diasParaVencer(item)
      if (d === null) return ''
      if (d < 0) return 'Venció hace ' + Math.abs(d) + (Math.abs(d) === 1 ? ' día' : ' días')
      if (d === 0) return 'Vence hoy'
      return 'Vence en ' + d + (d === 1 ? ' día' : ' días')
    },
    conteoDe(key) {
      if (key === '') {
        return Object.keys(this.conteo).reduce((a, k) => a + this.conteo[k], 0)
      }
      return this.conteo[key] || 0
    },
    setEstado(key) {
      this.estadoFiltro = key
      this.currentPage = 1
      this.$nextTick(() => this.$refs.tabla.refresh())
    },
    paramsExport() {
      return '?q=' + encodeURIComponent(this.filter || '') + '&estado=' + this.estadoFiltro
    },
    exportarPdf() {
      // Se abre en una pestaña nueva; el PDF respeta la búsqueda y el estado activos
      window.open(this.API + 'reporte/deudaproveedores/pdf' + this.paramsExport(), '_blank')
    },
    exportarExcel() {
      this.exportando = true
      this.axios.get(this.API + 'reporte/deudaproveedores/excel' + this.paramsExport())
        .then((response) => {
          // El endpoint devuelve la URL del .xlsx generado
          window.location.href = response.data
        })
        .catch((e) => {
          const sinDatos = e.response && e.response.status === 404
          alert(sinDatos ? 'No hay deudas para exportar' : 'No se pudo generar el Excel')
        })
        .then(() => {
          this.exportando = false
        })
    },
    myProvider(ctx) {
      let promise = this.axios.get(this.API + 'proveedores/deudas?q=' + encodeURIComponent(ctx.filter || '') + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage + '&estado=' + this.estadoFiltro)
      return promise.then((response) => {
        // El endpoint devuelve { total, data }: el total respeta el filtro de búsqueda
        this.totalRows = response.data.total
        this.conteo = response.data.conteo || {}
        this.items = response.data.data
        return this.items
      }).catch(() => [])
    },
    myRowClickHandler(record) {
      if (record.Letras != 'Ninguna') {
        this.showLetras = true
        this.$refs.detDeuPro.cargarDeudasProveedor(record.Id, record.Total, record.Ruc, record.Proveedor, record.Correlativo)
      }
    }
  },
  components: {
    DetalleDeudaProveedor
  }
}
</script>

<style scoped>
.deuda-page {
  --ink: #1f2a37;
  --muted: #6b7788;
  --line: #e6e9ef;
  --bg: #f5f7fa;
  --brand: #0f766e;
  --pendiente: #b45309;
  --pendiente-bg: #fef3c7;
  --recibido: #1d4ed8;
  --recibido-bg: #dbeafe;
  --pagado: #15803d;
  --pagado-bg: #dcfce7;
  --vencido: #b91c1c;
  --vencido-bg: #fee2e2;
  background: var(--bg);
  min-height: 100%;
  padding: 24px 16px 40px;
  color: var(--ink);
}

.deuda-head {
  max-width: 1280px;
  margin: 0 auto 16px;
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  flex-wrap: wrap;
  gap: 12px;
}
.deuda-head h2 { font-size: 1.6rem; font-weight: 700; margin: 0; letter-spacing: -0.01em; }
.deuda-head p { margin: 2px 0 0; color: var(--muted); }

.btn-export {
  background: var(--brand);
  border-color: var(--brand);
  padding: 8px 20px;
  border-radius: 8px;
  font-weight: 600;
}
.btn-export:hover, .btn-export:focus { background: #0b5f58; border-color: #0b5f58; }

.deuda-card {
  max-width: 1280px;
  background: #fff;
  border: 1px solid var(--line);
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 1px 2px rgba(16, 24, 40, 0.04);
}

.lbl { display: block; font-size: 0.85rem; font-weight: 600; color: var(--muted); margin-bottom: 4px; }
.toolbar .form-control, .toolbar .custom-select { border-radius: 8px; }
.toolbar .form-control:focus, .toolbar .custom-select:focus {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
}

/* Filtros por estado */
.filtros { display: flex; flex-wrap: wrap; gap: 8px; margin: 4px 0 16px; }
.chip {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 5px 8px 5px 14px;
  border: 1px solid var(--line); border-radius: 999px;
  background: #fff; color: var(--muted);
  font-size: 0.85rem; font-weight: 600; cursor: pointer;
  transition: background-color .15s, border-color .15s, color .15s;
}
.chip .n { background: #eef1f5; color: var(--ink); border-radius: 999px; padding: 0 8px; font-size: 0.78rem; font-variant-numeric: tabular-nums; }
.chip:hover { border-color: #cbd2dc; }
.chip:focus-visible { outline: 2px solid var(--brand); outline-offset: 2px; }
.chip.activo .n { background: rgba(255, 255, 255, 0.75); }
.chip-todos.activo { background: var(--ink); border-color: var(--ink); color: #fff; }
.chip-todos.activo .n { background: rgba(255, 255, 255, 0.2); color: #fff; }
.chip-pendiente.activo { background: var(--pendiente-bg); border-color: var(--pendiente); color: var(--pendiente); }
.chip-recibido.activo { background: var(--recibido-bg); border-color: var(--recibido); color: var(--recibido); }
.chip-pagado.activo { background: var(--pagado-bg); border-color: var(--pagado); color: var(--pagado); }
.chip-vencido.activo { background: var(--vencido-bg); border-color: var(--vencido); color: var(--vencido); }

/* Estados */
.estado {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 3px 10px 3px 8px;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  white-space: nowrap;
}
.estado i { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.estado-pendiente { color: var(--pendiente); background: var(--pendiente-bg); }
.estado-recibido { color: var(--recibido); background: var(--recibido-bg); }
.estado-pagado { color: var(--pagado); background: var(--pagado-bg); }
.estado-vencido { color: var(--vencido); background: var(--vencido-bg); }

/* Tabla */
.tabla { margin-bottom: 8px; font-size: 0.92rem; }
.tabla >>> thead th {
  border-top: 0;
  border-bottom: 1px solid var(--line);
  background: #f9fafb;
  color: var(--muted);
  font-size: 0.8rem;
  font-weight: 600;
  white-space: nowrap;
  padding: 10px 12px;
}
.tabla >>> tbody td { vertical-align: middle; padding: 12px; border-top: 1px solid var(--line); }
.tabla >>> tbody tr { cursor: pointer; }
.tabla >>> tbody tr:hover { background: #f1f5f9; }

.num { font-variant-numeric: tabular-nums; white-space: nowrap; }
.muted { color: var(--muted); }
.saldo { font-weight: 700; white-space: nowrap; }
.saldo-vencido { color: var(--vencido); }
.letras { color: var(--brand); font-weight: 600; }

.venc { display: flex; flex-direction: column; line-height: 1.25; }
.venc small { font-size: 0.78rem; color: var(--muted); }
.venc small.hint-vencido { color: var(--vencido); font-weight: 600; }
.venc small.hint-pagado { color: var(--pagado); }

.pager { margin: 12px 0 0; justify-content: flex-end; }
.pager >>> .page-item.active .page-link { background: var(--brand); border-color: var(--brand); color: #fff; }
.pager >>> .page-link { color: var(--brand); border-radius: 6px; margin: 0 2px; }

.acciones { display: flex; gap: 8px; }

.btn-export {
  padding: 8px 20px;
  border-radius: 8px;
  font-weight: 600;
  color: #fff;
}
.btn-pdf { background: #b91c1c; border-color: #b91c1c; }
.btn-pdf:hover, .btn-pdf:focus { background: #991b1b; border-color: #991b1b; }
.btn-excel { background: var(--brand); border-color: var(--brand); }
.btn-excel:hover, .btn-excel:focus { background: #0b5f58; border-color: #0b5f58; }

@media (max-width: 576px) {
  .deuda-card { padding: 12px; }
}
</style>