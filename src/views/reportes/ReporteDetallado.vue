<template>
  <div class="reporte-ventas">
    <b-card no-body class="shadow-sm border-0">

      <!-- ===================== CABECERA DEL REPORTE ===================== -->
      <div class="reporte-header no-print d-flex align-items-center justify-content-between flex-wrap px-4 py-3">
        <div class="d-flex align-items-center">
          <span class="header-icon mr-3">📊</span>
          <div>
            <h5 class="mb-0 titulo-reporte">Reporte de Ventas</h5>
            <small class="subtitulo-reporte">{{ tituloTabActivo }}</small>
          </div>
        </div>
        <b-badge v-if="cargando" class="badge-generando px-3 py-2">
          <span class="spinner-border spinner-border-sm mr-1" role="status" /> Generando...
        </b-badge>
      </div>

      <!-- ===================== ENCABEZADO IMPRESIÓN ===================== -->
      <div class="d-none d-print-block px-4 pt-3">
        <h4 class="mb-0">Reporte de Ventas — {{ tituloTabActivo }}</h4>
        <small>
          Periodo: {{ fechaDesde }} al {{ fechaHasta }}
          <span v-if="filtros.cliente"> · Cliente: {{ filtros.cliente }}</span>
          <span v-if="filtros.vendedor"> · Vendedor: {{ filtros.vendedor }}</span>
          <span v-if="filtros.producto"> · Producto: {{ filtros.producto }}</span>
          <span v-if="nombreProveedorSeleccionado"> · Proveedor: {{ nombreProveedorSeleccionado }}</span>
        </small>
        <hr>
      </div>

      <!-- ===================== FILTROS COMUNES ===================== -->
    <b-card-body class="pb-2 no-print filtros-card">
    <b-row>
        <b-col md="3" sm="6">
        <b-form-group>
            <label class="filtro-label">
            <span class="lbl-icon"><i class="fas fa-store mr-1"></i></span> Sucursal
            </label>
            <b-form-select v-model="idAlmacen" :options="almacenes" size="md" class="control-moderno" />
        </b-form-group>
        </b-col>

        <b-col md="3" sm="6">
        <b-form-group>
            <label class="filtro-label">
            <span class="lbl-icon"><i class="fas fa-calendar-alt mr-1"></i></span> Fecha de Venta
            </label>
            <b-input-group size="md">
            <b-form-input v-model="fechaDesde" type="date" :max="fechaHasta" class="control-moderno" />
            <b-form-input v-model="fechaHasta" type="date" :min="fechaDesde" class="control-moderno" />
            </b-input-group>
        </b-form-group>
        </b-col>

        <b-col md="3" sm="6">
        <b-form-group>
            <label class="filtro-label">
            <span class="lbl-icon"><i class="fas fa-user-tie mr-1"></i></span> Tipo de Vendedor
            </label>
            <b-form-select v-model="idTipoVendedor" :options="tiposVendedor" size="md" class="control-moderno" />
        </b-form-group>
        </b-col>

          <b-col md="3" sm="6" class="d-flex align-items-end">
            <b-form-group class="w-100">
              <b-button-group class="w-100 grupo-acciones">
                <b-button
                  class="btn-accion btn-mostrar"
                  :class="{ 'btn-mostrar--pendiente': hayCambiosPendientes && !cargando }"
                  :disabled="cargando"
                  @click="mostrarReporte"
                >
                  <span v-if="cargando" class="spinner-border spinner-border-sm mr-1" role="status" />
                  <span v-else>🔍</span>
                  Mostrar
                </b-button>
                <b-button variant="outline-primary" class="btn-accion" :disabled="cargando || cantidadRegistrosActivo === 0" @click="imprimirReporte">
                  🖨️ Imprimir
                </b-button>
              </b-button-group>
              <small v-if="hayCambiosPendientes && !cargando" class="d-block mt-1 aviso-pendiente">
                Tienes cambios sin aplicar · presiona <strong>Mostrar</strong> para actualizar el reporte
              </small>
            </b-form-group>
          </b-col>
        </b-row>

        <!-- chips con filtros adicionales activos -->
        <transition name="fade">
          <b-row v-if="hayFiltrosAdicionales">
            <b-col class="pb-2">
              <span v-if="filtros.cliente" class="chip">
                Cliente: {{ filtros.cliente }}
                <span class="quitar-chip" @click="quitarFiltro('cliente')">✕</span>
              </span>
              <span v-if="filtros.vendedor" class="chip">
                Vendedor: {{ filtros.vendedor }}
                <span class="quitar-chip" @click="quitarFiltro('vendedor')">✕</span>
              </span>
              <span v-if="filtros.producto" class="chip">
                Producto: {{ filtros.producto }}
                <span class="quitar-chip" @click="quitarFiltro('producto')">✕</span>
              </span>
              <span v-if="filtros.idProveedor" class="chip">
                Proveedor: {{ nombreProveedorSeleccionado }}
                <span class="quitar-chip" @click="quitarFiltro('idProveedor')">✕</span>
              </span>
              <a href="#" class="ml-2 small enlace-limpiar" @click.prevent="limpiarFiltrosAdicionales">Limpiar todo</a>
            </b-col>
          </b-row>
        </transition>
      </b-card-body>

      <!-- ===================== MODAL "MÁS FILTROS" ===================== -->
      <b-modal
        v-model="modalFiltrosVisible"
        id="modalMasFiltros"
        title="Filtros adicionales"
        ok-title="Aplicar y mostrar"
        cancel-title="Cancelar"
        ok-variant="primary"
        body-class="modal-filtros-body"
        @ok="mostrarReporte"
      >
        <b-form-group label="Cliente (nombre o documento):">
          <b-form-input v-model="filtros.cliente" placeholder="Ej: 20123456789 / Juan Pérez" class="control-moderno" />
        </b-form-group>
        <b-form-group label="Vendedor:">
          <b-form-input v-model="filtros.vendedor" placeholder="Nombre del vendedor" class="control-moderno" />
        </b-form-group>
        <b-form-group label="Producto:">
          <b-form-input v-model="filtros.producto" placeholder="Nombre o código del producto" class="control-moderno" />
        </b-form-group>
        <b-form-group label="Proveedor:">
          <b-form-select v-model="filtros.idProveedor" :options="proveedores" class="control-moderno" />
        </b-form-group>
        <a href="#" class="small enlace-limpiar" @click.prevent="limpiarFiltrosAdicionales">Limpiar filtros adicionales</a>
      </b-modal>

      <!-- ===================== RESUMEN / KPIs DEL REPORTE ACTIVO ===================== -->
      <div v-if="resumenActivo.campos.length && cantidadRegistrosActivo" class="no-print resumen-bar px-4 py-3">
        <b-row>
          <b-col cols="6" md="3" v-for="campo in resumenActivo.campos" :key="campo.key" class="mb-3 mb-md-0">
            <div class="stat-box">
              <div class="stat-label">{{ campo.label }}</div>
              <div class="stat-value">{{ formatoValor(campo.key, resumenActivo.totales[campo.key]) }}</div>
            </div>
          </b-col>
          <b-col cols="6" md="3" class="mb-3 mb-md-0">
            <div class="stat-box stat-box--registros">
              <div class="stat-label">N° de registros</div>
              <div class="stat-value">{{ formatoNumero(cantidadRegistrosActivo) }}</div>
            </div>
          </b-col>
        </b-row>
      </div>

      <!-- ===================== PESTAÑAS / SUB-REPORTES ===================== -->
      <b-tabs v-model="tabActive" card class="reporte-tabs" nav-wrapper-class="reporte-tabs-nav-wrapper">

        <b-tab title="Ventas por cliente">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasCliente.length" :items="dataVentasCliente" :fields="camposVentasCliente" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ranking por cliente">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataRankingCliente.length" :items="dataRankingCliente" :fields="camposRankingCliente" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por vendedor">
          <CargaOverlay :cargando="cargando">
            <TablaVentasVendedor v-if="dataVentasVendedor.length" :grupos="dataVentasVendedor" :formato-moneda="formatoMoneda" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por vendedor y producto" active>
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasVendedorProducto.length" :items="dataVentasVendedorProducto" :fields="camposVentasVendedorProducto" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por producto">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasProducto.length" :items="dataVentasProducto" :fields="camposVentasProducto" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por proveedor">
          <!-- Filtro específico de esta pestaña: qué proveedores mostrar -->
          <div v-if="opcionesProveedoresFiltro.length" class="no-print filtro-proveedores-reporte px-3 pt-3 pb-2">
            <div class="d-flex align-items-center flex-wrap mb-2">
              <strong class="mr-2 small texto-etiqueta">Proveedores a mostrar</strong>
              <b-badge v-if="proveedoresVendedorFiltro.length" pill class="mr-2 badge-contador">
                {{ proveedoresVendedorFiltro.length }} seleccionado(s)
              </b-badge>
              <a href="#" class="small mr-2 enlace-limpiar" @click.prevent="proveedoresVendedorFiltro = opcionesProveedoresFiltro.map(o => o.value)">Marcar todos</a>
              <a href="#" class="small enlace-limpiar" @click.prevent="proveedoresVendedorFiltro = []">Quitar filtro</a>
            </div>
            <div class="proveedores-checklist">
              <b-form-checkbox-group
                v-model="proveedoresVendedorFiltro"
                :options="opcionesProveedoresFiltro"
                stacked
              />
            </div>
          </div>
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasProveedorFiltrada.length" :items="dataVentasProveedorFiltrada" :fields="camposVentasProveedor" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por proveedor y producto">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasProveedorProducto.length" :items="dataVentasProveedorProducto" :fields="camposVentasProveedorProducto" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ranking por producto">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataRankingProducto.length" :items="dataRankingProducto" :fields="camposRankingProducto" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

      </b-tabs>
    </b-card>
  </div>
</template>

<script>
import config from '@/store/config.js'
import TablaReporte from '@/components/TablaReporte'

// Estado "sin resultados"
const EstadoVacio = {
  props: { cargando: { type: Boolean, default: false } },
  template: `
    <div v-if="!cargando" class="text-center estado-vacio py-5">
      <div class="empty-icon mb-2">📭</div>
      No hay resultados para los filtros seleccionados.
    </div>
    <div v-else class="py-5"></div>
  `
}

const CargaOverlay = {
  props: { cargando: { type: Boolean, default: false } },
  template: `
    <div class="carga-overlay-wrapper">
      <div v-if="cargando" class="carga-overlay-mascara">
        <span class="spinner-border" role="status" />
      </div>
      <slot />
    </div>
  `
}

// Tabla especial de "Ventas por vendedor": agrupa Tipo Vendedor > Vendedor > Tipo de Precio
// con rowspan y filas de subtotal por vendedor.
const TablaVentasVendedor = {
  props: {
    grupos: { type: Array, default: () => [] },
    formatoMoneda: { type: Function, required: true }
  },
  computed: {
    gruposConMeta () {
      return this.grupos.map(g => {
        const totalFilas = g.vendedores.reduce((acc, v) => acc + v.precios.length + 1, 0)
        return { ...g, totalFilas }
      })
    }
  },
  template: `
    <div class="tabla-vendedor-wrapper table-responsive">
      <table class="table table-sm table-hover tabla-vendedor mb-0">
        <thead>
          <tr>
            <th>Tipo Vend.</th>
            <th>Vendedor</th>
            <th>Tipo de Precio</th>
            <th class="text-right">Monto de Ventas</th>
            <th class="text-right">Devolución</th>
            <th class="text-right">Venta Neta</th>
            <th class="text-right">Total Costo</th>
            <th class="text-right">Costo S/Igv</th>
            <th class="text-right">Ganancia</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="grupo in gruposConMeta">
            <template v-for="(vendedor, iVend) in grupo.vendedores">
              <tr v-for="(precio, iPrecio) in vendedor.precios"
                  :key="grupo.TipoVendedor + '|' + vendedor.Vendedor + '|' + precio.TipoPrecio">
                <td v-if="iVend === 0 && iPrecio === 0" :rowspan="grupo.totalFilas" class="align-middle font-weight-bold">
                  {{ grupo.TipoVendedor }}
                </td>
                <td v-if="iPrecio === 0" :rowspan="vendedor.precios.length" class="align-middle">
                  {{ vendedor.Vendedor }}
                </td>
                <td>{{ precio.TipoPrecio }}</td>
                <td class="text-right num">{{ formatoMoneda(precio.MontoVentas) }}</td>
                <td class="text-right num">{{ formatoMoneda(precio.Devolucion) }}</td>
                <td class="text-right num">{{ formatoMoneda(precio.VentaNeta) }}</td>
                <td class="text-right num">{{ formatoMoneda(precio.TotalCosto) }}</td>
                <td class="text-right num">{{ formatoMoneda(precio.CostoSIgv) }}</td>
                <td class="text-right num">{{ formatoMoneda(precio.Ganancia) }}</td>
              </tr>
              <tr class="fila-subtotal-vendedor"
                  :key="grupo.TipoVendedor + '|' + vendedor.Vendedor + '|subtotal'">
                <td colspan="2" class="font-weight-bold">{{ vendedor.Vendedor }}</td>
                <td class="text-right font-weight-bold num">{{ formatoMoneda(vendedor.MontoVentas) }}</td>
                <td class="text-right font-weight-bold num">{{ formatoMoneda(vendedor.Devolucion) }}</td>
                <td class="text-right font-weight-bold num">{{ formatoMoneda(vendedor.VentaNeta) }}</td>
                <td class="text-right font-weight-bold num">{{ formatoMoneda(vendedor.TotalCosto) }}</td>
                <td class="text-right font-weight-bold num">{{ formatoMoneda(vendedor.CostoSIgv) }}</td>
                <td class="text-right font-weight-bold num">{{ formatoMoneda(vendedor.Ganancia) }}</td>
              </tr>
            </template>
          </template>
        </tbody>
      </table>
    </div>
  `
}

// Configuración de cada sub-reporte: se generan TODOS de una sola vez al presionar "Mostrar".
const REPORTES = [
  { path: 'reporte/ventas/cliente', dataKey: 'dataVentasCliente', camposKey: 'camposVentasCliente', titulo: 'Ventas por Cliente' },
  { path: 'reporte/ranking/cliente', dataKey: 'dataRankingCliente', camposKey: 'camposRankingCliente', titulo: 'Ranking por Cliente' },
  { path: 'reporte/ventas/vendedor', dataKey: 'dataVentasVendedor', camposKey: null, titulo: 'Ventas por Vendedor' },
  { path: 'reporte/ventas/vendedor-producto', dataKey: 'dataVentasVendedorProducto', camposKey: 'camposVentasVendedorProducto', titulo: 'Ventas por Vendedor y Producto' },
  { path: 'reporte/ventas/producto', dataKey: 'dataVentasProducto', camposKey: 'camposVentasProducto', titulo: 'Ventas por Producto' },
  { path: 'reporte/ventas/proveedor', dataKey: 'dataVentasProveedor', camposKey: 'camposVentasProveedor', titulo: 'Ventas por Proveedor' },
  { path: 'reporte/ventas/proveedor-producto', dataKey: 'dataVentasProveedorProducto', camposKey: 'camposVentasProveedorProducto', titulo: 'Ventas por Proveedor y Producto' },
  { path: 'reporte/ranking/producto', dataKey: 'dataRankingProducto', camposKey: 'camposRankingProducto', titulo: 'Ranking por Producto' }
]

// Campos que se consideran "montos" (se formatean como moneda)
const CAMPOS_MONEDA = ['MontoVentas', 'Devolucion', 'VentaNeta', 'TotalCompras', 'TotalCosto', 'CostoSIgv', 'Ganancia']

const INDEX_VENTAS_VENDEDOR = REPORTES.findIndex(r => r.dataKey === 'dataVentasVendedor')
const INDEX_VENTAS_PROVEEDOR = REPORTES.findIndex(r => r.dataKey === 'dataVentasProveedor')

export default {
  components: { TablaReporte, EstadoVacio, CargaOverlay, TablaVentasVendedor },
  mounted () {
    this.getAlmacenes()
    this.getTiposVendedor()
    this.getProveedores()
    this.mostrarReporte()
  },
  data () {
    return {
      API: config.API,
      tabActive: 3, // "Ventas por vendedor y producto"
      cargando: false,
      modalFiltrosVisible: false,

      idAlmacen: 0,
      idTipoVendedor: 0,
      fechaDesde: this.$moment().format('YYYY-MM-01'),
      fechaHasta: this.$moment().format('YYYY-MM-DD'),

      almacenes: [{ value: 0, text: 'TODOS' }],
      tiposVendedor: [
        { value: 0, text: 'TODOS' },
        { value: 1, text: 'EMPLEADO' },
        { value: 2, text: 'INDEPENDIENTE' }
      ],
      proveedores: [{ value: null, text: 'TODOS' }],

      filtros: {
        cliente: '',
        vendedor: '',
        producto: '',
        idProveedor: null
      },

      // Última combinación de filtros con la que efectivamente se generó el reporte.
      // Se compara contra los filtros actuales para saber si hay cambios sin aplicar.
      filtrosAplicados: null,

      // ---- filtro propio de la pestaña "Ventas por proveedor" ----
      // vacío = mostrar todos los proveedores del reporte
      proveedoresVendedorFiltro: [],

      dataVentasCliente: [],
      dataRankingCliente: [],
      dataVentasVendedor: [],
      dataVentasVendedorProducto: [],
      dataVentasProducto: [],
      dataVentasProveedor: [],
      dataVentasProveedorProducto: [],
      dataRankingProducto: [],

      camposVentasCliente: [
        { key: 'Cliente', label: 'Cliente', sortable: true },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposRankingCliente: [
        { key: 'Puesto', label: '#' },
        { key: 'Cliente', label: 'Cliente', sortable: true },
        { key: 'TotalCompras', label: 'Total Compras', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasVendedorProducto: [
        { key: 'TipoVendedor', label: 'Tipo Vend.' },
        { key: 'Vendedor', label: 'Vendedor', sortable: true },
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'ProductoMedicion', label: 'ProductoMedicion' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: v => this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasProducto: [
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'ProductoMedicion', label: 'ProductoMedicion' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: v => this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasProveedor: [
        { key: 'Proveedor', label: 'Proveedor', sortable: true },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: v => this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasProveedorProducto: [
        { key: 'Proveedor', label: 'Proveedor', sortable: true },
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'ProductoMedicion', label: 'ProductoMedicion' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: v => this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposRankingProducto: [
        { key: 'Puesto', label: '#' },
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'CantidadVendida', label: 'Cantidad Vendida', class: 'text-right', formatter: v => this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ]
    }
  },
  computed: {
    hayFiltrosAdicionales () {
      return !!(this.filtros.cliente || this.filtros.vendedor || this.filtros.producto || this.filtros.idProveedor)
    },
    cantidadFiltrosAdicionales () {
      return Object.values(this.filtros).filter(v => v !== '' && v !== null).length
    },
    nombreProveedorSeleccionado () {
      const p = this.proveedores.find(p => p.value === this.filtros.idProveedor)
      return p ? p.text : ''
    },
    tituloTabActivo () {
      return (REPORTES[this.tabActive] || {}).titulo || ''
    },

    // Snapshot de los filtros actuales, usado para detectar cambios sin aplicar.
    filtrosActualesSnapshot () {
      return JSON.stringify({
        idAlmacen: this.idAlmacen,
        idTipoVendedor: this.idTipoVendedor,
        fechaDesde: this.fechaDesde,
        fechaHasta: this.fechaHasta,
        filtros: this.filtros
      })
    },
    // true mientras el usuario haya tocado algún filtro y aún no presione "Mostrar".
    hayCambiosPendientes () {
      if (this.filtrosAplicados === null) return false
      return this.filtrosActualesSnapshot !== this.filtrosAplicados
    },

    // ---- filtro propio de "Ventas por proveedor" ----
    // opciones = proveedores que realmente aparecen en el reporte ya cargado
    opcionesProveedoresFiltro () {
      const nombres = [...new Set(this.dataVentasProveedor.map(f => f.Proveedor))].filter(Boolean)
      nombres.sort()
      return nombres.map(nombre => ({ text: nombre, value: nombre }))
    },
    dataVentasProveedorFiltrada () {
      if (!this.proveedoresVendedorFiltro.length) return this.dataVentasProveedor
      return this.dataVentasProveedor.filter(fila => this.proveedoresVendedorFiltro.includes(fila.Proveedor))
    },

    datosActivos () {
      const cfg = REPORTES[this.tabActive]
      if (!cfg) return []
      if (this.tabActive === INDEX_VENTAS_PROVEEDOR) return this.dataVentasProveedorFiltrada
      return this[cfg.dataKey]
    },
    camposActivos () {
      const cfg = REPORTES[this.tabActive]
      return (cfg && cfg.camposKey) ? this[cfg.camposKey] : []
    },
    // Cantidad de "registros" del KPI; en Ventas por Vendedor cuenta vendedores (no filas por tipo de precio).
    cantidadRegistrosActivo () {
      if (this.tabActive === INDEX_VENTAS_VENDEDOR) {
        return this.dataVentasVendedor.reduce((acc, g) => acc + g.vendedores.length, 0)
      }
      return this.datosActivos.length
    },
    resumenActivo () {
      if (this.tabActive === INDEX_VENTAS_VENDEDOR) {
        const campos = [
          { key: 'MontoVentas', label: 'Monto de Ventas' },
          { key: 'Devolucion', label: 'Devolución' },
          { key: 'VentaNeta', label: 'Venta Neta' },
          { key: 'TotalCosto', label: 'Total Costo' },
          { key: 'Ganancia', label: 'Ganancia' }
        ]
        const totales = { MontoVentas: 0, Devolucion: 0, VentaNeta: 0, TotalCosto: 0, Ganancia: 0 }
        this.dataVentasVendedor.forEach(g => {
          campos.forEach(c => { totales[c.key] += Number(g[c.key]) || 0 })
        })
        return { campos, totales }
      }
      const campos = (this.camposActivos || []).filter(c => c.class === 'text-right')
      const totales = {}
      campos.forEach(c => { totales[c.key] = 0 })
      this.datosActivos.forEach(fila => {
        campos.forEach(c => {
          const valor = Number(fila[c.key])
          if (!isNaN(valor)) totales[c.key] += valor
        })
      })
      return { campos, totales }
    }
  },
  methods: {
    abrirMasFiltros () {
      this.modalFiltrosVisible = true
    },
    quitarFiltro (nombre) {
      this.filtros[nombre] = (nombre === 'idProveedor') ? null : ''
      this.mostrarReporte()
    },
    limpiarFiltrosAdicionales () {
      this.filtros = { cliente: '', vendedor: '', producto: '', idProveedor: null }
      this.mostrarReporte()
    },
    endpoint (path) {
      const base = this.API.endsWith('/') ? this.API.slice(0, -1) : this.API
      const rel = path.startsWith('/') ? path : '/' + path
      return base + rel
    },
    paramsComunes () {
      return {
        idAlmacen: this.idAlmacen,
        idTipoVendedor: this.idTipoVendedor,
        fechaDesde: this.fechaDesde,
        fechaHasta: this.fechaHasta,
        cliente: this.filtros.cliente || null,
        vendedor: this.filtros.vendedor || null,
        producto: this.filtros.producto || null,
        idProveedor: this.filtros.idProveedor
      }
    },
    notificar (mensaje, tipo = 'error') {
      if (this.$toasted && typeof this.$toasted.show === 'function') {
        this.$toasted.show(mensaje, { type: tipo })
      } else {
        console.warn('[ReporteVentas]', mensaje)
      }
    },

    // ---- genera TODOS los sub-reportes de una sola vez, en paralelo ----
    // Este es el ÚNICO punto de entrada que dispara la carga de datos: los filtros
    // (sucursal, fechas, tipo de vendedor, y los adicionales del modal) sólo se
    // aplican cuando el usuario presiona "Mostrar" (o una acción equivalente,
    // como quitar un chip). Cambiar un filtro por sí solo ya NO recarga el reporte.
    async mostrarReporte () {
      this.modalFiltrosVisible = false

      if (!this.fechaDesde || !this.fechaHasta) {
        this.notificar('Ingrese un rango de fechas válido')
        return
      }
      if (this.fechaDesde > this.fechaHasta) {
        this.notificar('La fecha "desde" no puede ser mayor que la fecha "hasta"')
        return
      }

      this.cargando = true
      this.proveedoresVendedorFiltro = [] // reinicia el filtro propio de "Ventas por proveedor"
      const params = this.paramsComunes()

      await Promise.all(REPORTES.map(async cfg => {
        try {
          const { data } = await this.axios.get(this.endpoint(cfg.path), { params })
          this[cfg.dataKey] = Array.isArray(data) ? data : []
        } catch (e) {
          console.error(e)
          this.notificar(`No se pudo generar "${cfg.titulo}". Intente nuevamente.`)
        }
      }))

      // Se registra la combinación de filtros con la que se acaba de generar el
      // reporte, para poder avisar al usuario si vuelve a tocar algún filtro.
      this.filtrosAplicados = this.filtrosActualesSnapshot
      this.cargando = false
    },

    imprimirReporte () {
      window.print()
    },
    formatoValor (key, valor) {
      return CAMPOS_MONEDA.includes(key) ? this.formatoMoneda(valor) : this.formatoNumero(valor)
    },
    formatoMoneda (valor) {
      return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(Number(valor) || 0)
    },
    formatoNumero (valor) {
      return new Intl.NumberFormat('es-PE').format(Number(valor) || 0)
    },
    getAlmacenes () {
      this.almacenes = [{ value: 0, text: 'TODOS' }]
      this.axios.get(this.endpoint('almacenes')).then(r => {
        if (r.data) {
          r.data.map(al => this.almacenes.push({ value: al.IdAlmacen, text: al.Almacen }))
        }
      }).catch(() => {})
    },
    getTiposVendedor () {
      this.axios.get(this.endpoint('vendedores/tipos')).then(r => {
        if (r.data) {
          this.tiposVendedor = [{ value: 0, text: 'TODOS' }]
          r.data.map(t => this.tiposVendedor.push({ value: t.IdTipoVendedor, text: t.TipoVendedor }))
        }
      }).catch(() => {})
    },
    getProveedores () {
      this.axios.get(this.endpoint('proveedores')).then(r => {
        if (r.data) {
          this.proveedores = [{ value: null, text: 'TODOS' }]
          r.data.map(p => this.proveedores.push({ value: p.IdProveedor, text: p.Proveedor }))
        }
      }).catch(() => {})
    }
  }
}
</script>

<style scoped>
/* ===================== TOKENS DE DISEÑO ===================== */
.reporte-ventas {
  --rv-ink: #16241f;
  --rv-ink-soft: #5c6b66;
  --rv-bg: #f5f6f4;
  --rv-surface: #ffffff;
  --rv-border: #e2e6e2;
  --rv-primary-1: #0f3d3e;
  --rv-primary-2: #1a6362;
  --rv-accent: #e0a339;
  --rv-accent-ink: #7a5417;
  --rv-danger: #b3462e;
  --rv-success: #1f7a5c;
  --rv-radius: 0.65rem;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: var(--rv-ink);
  background: var(--rv-bg);
}

/* ===================== CABECERA ===================== */
.reporte-header {
  background: linear-gradient(120deg, var(--rv-primary-1) 0%, var(--rv-primary-2) 100%);
  color: #fdfdfb;
  border-top-left-radius: var(--rv-radius);
  border-top-right-radius: var(--rv-radius);
}
.header-icon {
  font-size: 1.6rem;
  filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.15));
}
.titulo-reporte {
  font-weight: 650;
  letter-spacing: -0.01em;
}
.subtitulo-reporte {
  color: rgba(253, 253, 251, 0.72);
}
.badge-generando {
  background: rgba(255, 255, 255, 0.16);
  color: #fdfdfb;
  font-weight: 500;
  border-radius: 999px;
}

/* ===================== FILTROS ===================== */
.filtros-card {
  background-color: var(--rv-surface);
  border-bottom: 1px solid var(--rv-border);
}
.filtro-label {
  font-size: 0.82rem;
  font-weight: 600;
  color: var(--rv-ink-soft);
  margin-bottom: 0.35rem;
}
.lbl-icon {
  display: inline-block;
  width: 1.1em;
}
.control-moderno {
  border: 1px solid var(--rv-border);
  border-radius: 0.5rem;
  box-shadow: none;
}
.control-moderno:focus {
  border-color: var(--rv-primary-2);
  box-shadow: 0 0 0 3px rgba(26, 99, 98, 0.15);
}
.grupo-acciones .btn-accion {
  border-radius: 0.5rem !important;
  font-weight: 550;
  border-color: var(--rv-border);
}
.grupo-acciones .btn {
  margin-right: 0.4rem;
}
.grupo-acciones .btn:last-child {
  margin-right: 0;
}
.btn-mostrar {
  background: var(--rv-primary-2);
  border-color: var(--rv-primary-2);
  color: #fff;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
}
.btn-mostrar:hover:not(:disabled) {
  background: var(--rv-primary-1);
  border-color: var(--rv-primary-1);
}
.btn-mostrar--pendiente {
  box-shadow: 0 0 0 3px rgba(224, 163, 57, 0.35);
  background: var(--rv-accent);
  border-color: var(--rv-accent);
  color: var(--rv-accent-ink);
}
.btn-mostrar--pendiente:hover:not(:disabled) {
  background: #cf931f;
  border-color: #cf931f;
  color: var(--rv-accent-ink);
}
.aviso-pendiente {
  color: var(--rv-accent-ink);
  font-weight: 500;
}
.badge-contador {
  background: var(--rv-primary-2);
  color: #fff;
}

/* chips de filtros adicionales */
.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.82rem;
  font-weight: 500;
  padding: 0.35rem 0.6rem;
  margin-right: 0.4rem;
  border-radius: 999px;
  background: #eaf1f0;
  color: var(--rv-primary-1);
  border: 1px solid #d7e5e3;
}
.quitar-chip {
  cursor: pointer;
  font-weight: bold;
  opacity: 0.6;
  line-height: 1;
}
.quitar-chip:hover {
  opacity: 1;
}
.enlace-limpiar {
  color: var(--rv-primary-2);
  text-decoration: none;
}
.enlace-limpiar:hover {
  text-decoration: underline;
}
.texto-etiqueta {
  color: var(--rv-ink-soft);
}
.modal-filtros-body >>> label {
  font-weight: 600;
  font-size: 0.85rem;
  color: var(--rv-ink-soft);
}

/* ===================== KPIs ===================== */
.resumen-bar {
  background-color: var(--rv-surface);
  border-bottom: 1px solid var(--rv-border);
}
.stat-box {
  background: #fafbfa;
  border: 1px solid var(--rv-border);
  border-left: 3px solid var(--rv-primary-2);
  border-radius: 0.5rem;
  padding: 0.7rem 1rem;
  height: 100%;
}
.stat-box--registros {
  border-left-color: var(--rv-accent);
}
.stat-label {
  font-size: 0.74rem;
  color: var(--rv-ink-soft);
  margin-bottom: 0.15rem;
}
.stat-value {
  font-size: 1.15rem;
  font-weight: 700;
  color: var(--rv-ink);
  font-variant-numeric: tabular-nums;
}

/* ===================== TABS ===================== */
.reporte-tabs >>> .nav-tabs {
  border-bottom: 1px solid var(--rv-border);
  padding: 0 1rem;
  flex-wrap: nowrap;
  overflow-x: auto;
  overflow-y: hidden;
}
.reporte-tabs >>> .nav-tabs .nav-item {
  white-space: nowrap;
}
.reporte-tabs >>> .nav-tabs .nav-link {
  border: none;
  border-bottom: 2px solid transparent;
  border-radius: 0;
  color: var(--rv-ink-soft);
  font-weight: 550;
  padding: 0.85rem 1rem;
}
.reporte-tabs >>> .nav-tabs .nav-link:hover {
  color: var(--rv-primary-1);
  border-bottom-color: var(--rv-border);
}
.reporte-tabs >>> .nav-tabs .nav-link.active {
  color: var(--rv-primary-1);
  background: transparent;
  border-bottom-color: var(--rv-accent);
}
.reporte-tabs-nav-wrapper {
  background: var(--rv-surface);
}
.reporte-tabs >>> .tab-content {
  background: var(--rv-surface);
}

/* ===================== TABLAS ===================== */
.reporte-tabs >>> table thead th,
.tabla-vendedor thead th {
  background-color: #fafbfa;
  color: var(--rv-ink-soft);
  font-size: 0.76rem;
  font-weight: 650;
  text-transform: none;
  letter-spacing: 0.01em;
  border-top: none;
  border-bottom: 1px solid var(--rv-border);
}
.reporte-tabs >>> table td,
.tabla-vendedor td {
  border-color: var(--rv-border);
  font-size: 0.86rem;
  vertical-align: middle;
}
.reporte-tabs >>> table tbody tr:hover,
.tabla-vendedor tbody tr:hover {
  background-color: #f5f8f7;
}
.num,
.reporte-tabs >>> td.text-right {
  font-variant-numeric: tabular-nums;
}
.fila-subtotal-vendedor td {
  background-color: #f2f6f2 !important;
  border-top: 2px solid var(--rv-primary-2);
}

/* estado vacío */
.estado-vacio {
  color: var(--rv-ink-soft);
}
.empty-icon {
  font-size: 2rem;
  opacity: 0.7;
}

/* overlay de carga */
.carga-overlay-wrapper {
  position: relative;
  min-height: 3rem;
}
.carga-overlay-mascara {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2;
}
.carga-overlay-mascara .spinner-border {
  color: var(--rv-primary-2);
}

/* filtro de proveedores dentro de la pestaña */
.filtro-proveedores-reporte {
  background-color: #fafbfa;
  border-bottom: 1px solid var(--rv-border);
}
.proveedores-checklist {
  max-height: 160px;
  overflow-y: auto;
  columns: 2;
  column-gap: 1.5rem;
}
.proveedores-checklist >>> .custom-control {
  break-inside: avoid;
}

/* transición de chips */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

@media print {
  .no-print {
    display: none !important;
  }
  .card {
    border: none !important;
    box-shadow: none !important;
  }
}

@media (max-width: 576px) {
  .grupo-acciones {
    flex-wrap: wrap;
  }
  .grupo-acciones .btn-accion {
    flex: 1 1 100%;
    margin-right: 0 !important;
    margin-bottom: 0.4rem;
  }
  .proveedores-checklist {
    columns: 1;
  }
}
</style>