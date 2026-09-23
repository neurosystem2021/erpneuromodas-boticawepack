<template>
  <div class="reporte-ventas">
    <b-card no-body class="reporte-card">

      <!-- ===================== CABECERA DEL REPORTE ===================== -->
      <div class="reporte-header no-print d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
          <span class="header-icon mr-3"><i class="fas fa-chart-bar"></i></span>
          <div>
            <h5 class="mb-0 titulo-reporte">Reporte de Ventas</h5>
            <small class="subtitulo-reporte">{{ tituloTabActivo }}</small>
          </div>
        </div>
        <ReporteDeuda class="mr-2" :almacenes="almacenes" :id-almacen="idAlmacen" />
        <b-badge v-if="cargando" class="badge-generando">
          <span class="spinner-border spinner-border-sm mr-2" role="status" />Generando...
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
          <span v-if="tabAdmitePresentaciones && unificarPresentaciones"> · Presentaciones unificadas en unidades</span>
        </small>
        <hr>
      </div>

      <!-- ===================== FILTROS COMUNES ===================== -->
      <b-card-body class="no-print filtros-card">
        <b-row>
          <b-col md="3" sm="6">
            <b-form-group>
              <label class="filtro-label">
                <span class="lbl-icon"><i class="fas fa-store"></i></span> Sucursal
              </label>
              <b-form-select v-model="idAlmacen" :options="almacenes" size="md" class="control-moderno" />
            </b-form-group>
          </b-col>

          <b-col md="3" sm="6">
            <b-form-group>
              <label class="filtro-label">
                <span class="lbl-icon"><i class="fas fa-calendar-alt"></i></span> Fecha de venta
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
                <span class="lbl-icon"><i class="fas fa-user-tie"></i></span> Tipo de vendedor
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
                  <span v-if="cargando" class="spinner-border spinner-border-sm mr-2" role="status" />
                  <i v-else class="fas fa-search mr-2"></i>
                  Mostrar
                </b-button>
                <b-button
                  class="btn-accion btn-excel"
                  :disabled="cargando || cantidadRegistrosActivo === 0 || generandoExcel"
                  @click="exportarExcel"
                >
                  <span v-if="generandoExcel" class="spinner-border spinner-border-sm mr-2" role="status" />
                  <i v-else class="fas fa-file-excel mr-2"></i>
                  Excel
                </b-button>
                <b-button class="btn-accion btn-imprimir" :disabled="cargando || cantidadRegistrosActivo === 0" @click="imprimirReporte">
                  <i class="fas fa-print mr-2"></i>Imprimir
                </b-button>
              </b-button-group>
              <small v-if="hayCambiosPendientes && !cargando" class="d-block mt-2 aviso-pendiente">
                Tienes cambios sin aplicar. Presiona <strong>Mostrar</strong> para actualizar el reporte.
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
      <div v-if="resumenActivo.campos.length && cantidadRegistrosActivo" class="no-print resumen-bar">
        <div class="kpi-grid">
          <div v-for="campo in resumenActivo.campos" :key="campo.key" class="stat-box" :class="tonoKpi(campo.key)">
            <div class="stat-label">{{ campo.label }}</div>
            <div class="stat-value">{{ formatoValor(campo.key, resumenActivo.totales[campo.key]) }}</div>
          </div>
          <div class="stat-box stat-box--registros">
            <div class="stat-label">N° de registros</div>
            <div class="stat-value">{{ formatoNumero(cantidadRegistrosActivo) }}</div>
          </div>
        </div>
      </div>

      <!-- ===================== INTERRUPTOR ÚNICO: UNIFICAR PRESENTACIONES ===================== -->
      <!-- Se muestra solo en: Ventas por vendedor y producto, por producto, por proveedor, por proveedor y producto, Ranking por producto -->
      <div v-if="tabAdmitePresentaciones" class="no-print filtro-proveedores-reporte barra-presentaciones">
        <b-form-checkbox v-model="unificarPresentaciones" switch>
          Unificar presentaciones <small class="text-muted ml-1">suma todo en unidades</small>
        </b-form-checkbox>
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
            <TablaReporte v-if="dataVentasVendedorProductoVista.length" :items="dataVentasVendedorProductoVista" :fields="camposVentasVendedorProductoVista" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por producto">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasProductoVista.length" :items="dataVentasProductoVista" :fields="camposVentasProductoVista" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por proveedor">
          <!-- Filtro específico de esta pestaña: qué proveedores mostrar -->
          <div v-if="opcionesProveedoresFiltro.length" class="no-print filtro-proveedores-reporte">
            <div class="d-flex align-items-center flex-wrap mb-2">
              <strong class="mr-2 small texto-etiqueta">Proveedores a mostrar</strong>
              <b-badge v-if="proveedoresVendedorFiltro.length" pill class="mr-2 badge-contador">
                {{ proveedoresVendedorFiltro.length }} seleccionado(s)
              </b-badge>
              <a href="#" class="small mr-3 enlace-limpiar" @click.prevent="proveedoresVendedorFiltro = opcionesProveedoresFiltro.map(o => o.value)">Marcar todos</a>
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
            <TablaReporte v-if="dataVentasProveedorVista.length" :items="dataVentasProveedorVista" :fields="camposVentasProveedorVista" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ventas por proveedor y producto">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataVentasProveedorProductoVista.length" :items="dataVentasProveedorProductoVista" :fields="camposVentasProveedorProductoVista" :loading="cargando" />
            <EstadoVacio v-else :cargando="cargando" />
          </CargaOverlay>
        </b-tab>

        <b-tab title="Ranking por producto">
          <CargaOverlay :cargando="cargando">
            <TablaReporte v-if="dataRankingProductoVista.length" :items="dataRankingProductoVista" :fields="camposRankingProductoVista" :loading="cargando" />
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
import ReporteDeuda from '@/components/ReporteDeuda'

// Estado "sin resultados"
const EstadoVacio = {
  props: { cargando: { type: Boolean, default: false } },
  template: `
    <div v-if="!cargando" class="text-center estado-vacio">
      <div class="empty-icon"><i class="fas fa-inbox"></i></div>
      <div class="empty-titulo">Sin resultados</div>
      <div class="empty-texto">No hay datos para los filtros seleccionados.</div>
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
// excelPath: endpoint backend que genera el .xlsx de ese sub-reporte (ver reporte_ventas_excel.php)
const REPORTES = [
  { path: 'reporte/ventas/cliente', excelPath: 'reporte/ventas/cliente/excel', dataKey: 'dataVentasCliente', camposKey: 'camposVentasCliente', titulo: 'Ventas por Cliente' },
  { path: 'reporte/ranking/cliente', excelPath: 'reporte/ranking/cliente/excel', dataKey: 'dataRankingCliente', camposKey: 'camposRankingCliente', titulo: 'Ranking por Cliente' },
  { path: 'reporte/ventas/vendedor', excelPath: 'reporte/ventas/vendedor/excel', dataKey: 'dataVentasVendedor', camposKey: null, titulo: 'Ventas por Vendedor' },
  { path: 'reporte/ventas/vendedor-producto', excelPath: 'reporte/ventas/vendedor-producto/excel', dataKey: 'dataVentasVendedorProducto', camposKey: 'camposVentasVendedorProducto', titulo: 'Ventas por Vendedor y Producto' },
  { path: 'reporte/ventas/producto', excelPath: 'reporte/ventas/producto/excel', dataKey: 'dataVentasProducto', camposKey: 'camposVentasProducto', titulo: 'Ventas por Producto' },
  { path: 'reporte/ventas/proveedor', excelPath: 'reporte/ventas/proveedor/excel', dataKey: 'dataVentasProveedor', camposKey: 'camposVentasProveedor', titulo: 'Ventas por Proveedor' },
  { path: 'reporte/ventas/proveedor-producto', excelPath: 'reporte/ventas/proveedor-producto/excel', dataKey: 'dataVentasProveedorProducto', camposKey: 'camposVentasProveedorProducto', titulo: 'Ventas por Proveedor y Producto' },
  { path: 'reporte/ranking/producto', excelPath: 'reporte/ranking/producto/excel', dataKey: 'dataRankingProducto', camposKey: 'camposRankingProducto', titulo: 'Ranking por Producto' }
]

// Campos que se consideran "montos" (se formatean como moneda)
const CAMPOS_MONEDA = ['MontoVentas', 'Devolucion', 'VentaNeta', 'TotalCompras', 'TotalCosto', 'CostoSIgv', 'Ganancia']

const INDEX_VENTAS_VENDEDOR = REPORTES.findIndex(r => r.dataKey === 'dataVentasVendedor')
const INDEX_VENTAS_VENDEDOR_PRODUCTO = REPORTES.findIndex(r => r.dataKey === 'dataVentasVendedorProducto')
const INDEX_VENTAS_PRODUCTO = REPORTES.findIndex(r => r.dataKey === 'dataVentasProducto')
const INDEX_VENTAS_PROVEEDOR = REPORTES.findIndex(r => r.dataKey === 'dataVentasProveedor')
const INDEX_VENTAS_PROVEEDOR_PRODUCTO = REPORTES.findIndex(r => r.dataKey === 'dataVentasProveedorProducto')
const INDEX_RANKING_PRODUCTO = REPORTES.findIndex(r => r.dataKey === 'dataRankingProducto')

// Pestañas que admiten el interruptor "Unificar presentaciones"
const TABS_CON_PRESENTACIONES = [
  INDEX_VENTAS_VENDEDOR_PRODUCTO,
  INDEX_VENTAS_PRODUCTO,
  INDEX_VENTAS_PROVEEDOR,
  INDEX_VENTAS_PROVEEDOR_PRODUCTO,
  INDEX_RANKING_PRODUCTO
]

const CAMPOS_CANTIDAD = ['Cantidad', 'CantidadVendida']
const CAMPOS_SUMABLES = ['MontoVentas', 'Devolucion', 'VentaNeta']
const redondear2 = n => Math.round((Number(n) || 0) * 100) / 100

// Color de acento de cada tarjeta de resumen (solo visual)
const TONOS_KPI = {
  Devolucion: 'danger',
  VentaNeta: 'primary',
  Ganancia: 'success',
  TotalCosto: 'muted'
}

// Junta las filas de un mismo grupo convirtiendo todo a unidades reales
// (cantidad * CantidadPresentacion) y sumando los montos.
function unificarFilas (filas, camposClave, campoCantidad) {
  const mapa = {}
  filas.forEach(f => {
    const clave = camposClave.map(k => f[k]).join('|')
    if (!mapa[clave]) {
      const g = { ...f }
      delete g.Presentacion
      delete g.CantidadPresentacion
      delete g.CantidadTexto
      g[campoCantidad] = 0
      CAMPOS_SUMABLES.forEach(k => { if (k in f) g[k] = 0 })
      mapa[clave] = g
    }
    const g = mapa[clave]
    g[campoCantidad] += (Number(f[campoCantidad]) || 0) * (Number(f.CantidadPresentacion) || 1)
    CAMPOS_SUMABLES.forEach(k => { if (k in f) g[k] += Number(f[k]) || 0 })
  })
  return Object.values(mapa).map(g => {
    CAMPOS_SUMABLES.forEach(k => { if (k in g) g[k] = redondear2(g[k]) })
    return g
  })
}

export default {
  components: {
    TablaReporte,
    EstadoVacio,
    CargaOverlay,
    TablaVentasVendedor,
    ReporteDeuda 
  },
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
      generandoExcel: false,
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

      // ---- interruptor compartido por las pestañas con presentaciones ----
      // false = filas separadas por presentación; true = todo unificado en unidades
      unificarPresentaciones: true,

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
        { key: 'Presentacion', label: 'Presentación' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: (v, key, item) => item.CantidadTexto || this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasProducto: [
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'Presentacion', label: 'Presentación' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: (v, key, item) => item.CantidadTexto || this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasProveedor: [
        { key: 'Proveedor', label: 'Proveedor', sortable: true },
        { key: 'Presentacion', label: 'Presentación' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: (v, key, item) => item.CantidadTexto || this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposVentasProveedorProducto: [
        { key: 'Proveedor', label: 'Proveedor', sortable: true },
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'Presentacion', label: 'Presentación' },
        { key: 'Cantidad', label: 'Cantidad', class: 'text-right', formatter: (v, key, item) => item.CantidadTexto || this.formatoNumero(v) },
        { key: 'MontoVentas', label: 'Monto de Ventas', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'Devolucion', label: 'Devolución', class: 'text-right', formatter: v => this.formatoMoneda(v) },
        { key: 'VentaNeta', label: 'Venta Neta', class: 'text-right', formatter: v => this.formatoMoneda(v) }
      ],
      camposRankingProducto: [
        { key: 'Puesto', label: '#' },
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'Presentacion', label: 'Presentación' },
        { key: 'CantidadVendida', label: 'Cantidad Vendida', class: 'text-right', formatter: (v, key, item) => item.CantidadTexto || this.formatoNumero(v) },
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

    // ---- interruptor "Unificar presentaciones" ----
    tabAdmitePresentaciones () {
      return TABS_CON_PRESENTACIONES.includes(this.tabActive)
    },

    // Ventas por vendedor y producto
    dataVentasVendedorProductoVista () {
      return this.vistaSegunModo(this.dataVentasVendedorProducto, ['Vendedor', 'Producto'], 'Cantidad')
    },
    camposVentasVendedorProductoVista () {
      return this.camposSegunModo(this.camposVentasVendedorProducto, true)
    },

    // Ventas por producto
    dataVentasProductoVista () {
      return this.vistaSegunModo(this.dataVentasProducto, ['Producto'], 'Cantidad')
    },
    camposVentasProductoVista () {
      return this.camposSegunModo(this.camposVentasProducto, true)
    },

    // Ventas por proveedor (respeta el filtro de proveedores)
    dataVentasProveedorVista () {
      return this.vistaSegunModo(this.dataVentasProveedorFiltrada, ['Proveedor'], 'Cantidad')
    },
    camposVentasProveedorVista () {
      return this.camposSegunModo(this.camposVentasProveedor, false)
    },

    // Ventas por proveedor y producto
    dataVentasProveedorProductoVista () {
      return this.vistaSegunModo(this.dataVentasProveedorProducto, ['Proveedor', 'Producto'], 'Cantidad')
    },
    camposVentasProveedorProductoVista () {
      return this.camposSegunModo(this.camposVentasProveedorProducto, true)
    },

    // Ranking por producto: ordena por unidades reales y calcula el puesto
    dataRankingProductoVista () {
      const base = this.vistaSegunModo(this.dataRankingProducto, ['Producto'], 'CantidadVendida')
      const unidades = f => (Number(f.CantidadVendida) || 0) * (Number(f.CantidadPresentacion) || 1)
      return [...base]
        .sort((a, b) => unidades(b) - unidades(a))
        .map((f, i) => ({ ...f, Puesto: i + 1 }))
    },
    camposRankingProductoVista () {
      // El ranking no trae "ProductoMedicion", por eso al unificar solo se quita la columna Presentación
      return this.camposSegunModo(this.camposRankingProducto, false)
    },

    datosActivos () {
      const cfg = REPORTES[this.tabActive]
      if (!cfg) return []
      switch (this.tabActive) {
        case INDEX_VENTAS_VENDEDOR_PRODUCTO: return this.dataVentasVendedorProductoVista
        case INDEX_VENTAS_PRODUCTO: return this.dataVentasProductoVista
        case INDEX_VENTAS_PROVEEDOR: return this.dataVentasProveedorVista
        case INDEX_VENTAS_PROVEEDOR_PRODUCTO: return this.dataVentasProveedorProductoVista
        case INDEX_RANKING_PRODUCTO: return this.dataRankingProductoVista
        default: return this[cfg.dataKey]
      }
    },
    camposActivos () {
      const cfg = REPORTES[this.tabActive]
      switch (this.tabActive) {
        case INDEX_VENTAS_VENDEDOR_PRODUCTO: return this.camposVentasVendedorProductoVista
        case INDEX_VENTAS_PRODUCTO: return this.camposVentasProductoVista
        case INDEX_VENTAS_PROVEEDOR: return this.camposVentasProveedorVista
        case INDEX_VENTAS_PROVEEDOR_PRODUCTO: return this.camposVentasProveedorProductoVista
        case INDEX_RANKING_PRODUCTO: return this.camposRankingProductoVista
        default: return (cfg && cfg.camposKey) ? this[cfg.camposKey] : []
      }
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
          let valor = Number(fila[c.key])
          if (isNaN(valor)) return
          // La tarjeta de cantidad siempre suma unidades reales (sobres * unidades por sobre)
          if (CAMPOS_CANTIDAD.includes(c.key)) valor *= Number(fila.CantidadPresentacion) || 1
          totales[c.key] += valor
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

    // clase de color de cada tarjeta de resumen (solo presentación)
    tonoKpi (key) {
      return 'stat-box--' + (TONOS_KPI[key] || 'default')
    },

    // ---- vistas con/sin presentaciones unificadas ----
    vistaSegunModo (filas, camposClave, campoCantidad) {
      if (!this.unificarPresentaciones) return filas
      return unificarFilas(filas, camposClave, campoCantidad)
    },
    // Al unificar: la columna Presentación pasa a "Medición" (o se quita) y la cantidad se rotula en unidades
    camposSegunModo (campos, conMedicion) {
      if (!this.unificarPresentaciones) return campos
      return campos
        .map(c => {
          if (c.key === 'Presentacion') return conMedicion ? { key: 'ProductoMedicion', label: 'Medición' } : null
          if (CAMPOS_CANTIDAD.includes(c.key)) {
            return { ...c, label: c.label + ' (unidades)', formatter: v => this.formatoNumero(v) }
          }
          return c
        })
        .filter(Boolean)
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

    // ---- exporta a Excel el sub-reporte de la pestaña activa ----
    // Llama al endpoint .../excel correspondiente (backend, PhpSpreadsheet) con
    // los mismos filtros que están aplicados en el reporte, y abre el archivo
    // .xlsx que devuelve.
    async exportarExcel () {
      const cfg = REPORTES[this.tabActive]
      if (!cfg) return

      this.generandoExcel = true
      try {
        const { data: url } = await this.axios.get(this.endpoint(cfg.excelPath), { params: this.paramsComunes() })
        if (url) {
          window.open('//' + url, '_blank')
        } else {
          this.notificar('No se pudo generar el archivo Excel. Intente nuevamente.')
        }
      } catch (e) {
        console.error(e)
        this.notificar(`No se pudo exportar "${cfg.titulo}" a Excel. Intente nuevamente.`)
      } finally {
        this.generandoExcel = false
      }
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
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap');

/* ===================== TOKENS DE DISEÑO ===================== */
.reporte-ventas {
  --rv-ink: #14231f;
  --rv-ink-soft: #566762;
  --rv-ink-faint: #8a9893;
  --rv-bg: #f2f5f4;
  --rv-surface: #ffffff;
  --rv-surface-2: #f8faf9;
  --rv-border: #e1e7e5;
  --rv-border-soft: #edf1ef;
  --rv-primary-1: #0c3a3b;
  --rv-primary-2: #21a5a3;
  --rv-primary-tint: #e2f0ee;
  --rv-accent: #e0a339;
  --rv-accent-ink: #7a5417;
  --rv-danger: #c4523a;
  --rv-success: #1f8a63;
  --rv-excel: #1d6f42;
  --rv-radius: 16px;
  --rv-radius-sm: 10px;
  font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: var(--rv-ink);
  background: var(--rv-bg);
  padding: 0.25rem 0;
}

.reporte-card {
  border: 1px solid var(--rv-border) !important;
  border-radius: var(--rv-radius);
  overflow: hidden;
  background: var(--rv-surface);
  box-shadow: 0 1px 2px rgba(20, 35, 31, 0.04), 0 8px 24px rgba(20, 35, 31, 0.05);
}

/* ===================== CABECERA ===================== */
.reporte-header {
  padding: 1.1rem 1.5rem;
  color: #f4faf9;
  background:
    radial-gradient(90% 160% at 0% 0%, rgba(5, 252, 252, 0.55) 0%, rgba(38, 150, 145, 0) 60%),
    linear-gradient(120deg, #1c6c6d 0%, #189797 100%);
}
.header-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2.75rem;
  height: 2.75rem;
  font-size: 1.1rem;
  border-radius: 0.85rem;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
}
.titulo-reporte {
  font-size: 1.15rem;
  font-weight: 600;
  letter-spacing: -0.01em;
}
.subtitulo-reporte {
  display: block;
  margin-top: 0.1rem;
  font-size: 0.82rem;
  color: rgba(244, 250, 249, 0.75);
}
.badge-generando {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 0.9rem;
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: #f4faf9;
  font-weight: 500;
  border-radius: 999px;
}

/* ===================== FILTROS ===================== */
.filtros-card {
  padding: 1.25rem 1.5rem 0.5rem;
  background-color: var(--rv-surface);
  border-bottom: 1px solid var(--rv-border);
}
.filtro-label {
  display: flex;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--rv-ink-soft);
  margin-bottom: 0.4rem;
}
.lbl-icon {
  display: inline-block;
  width: 1.3em;
  color: var(--rv-primary-2);
}
.control-moderno {
  height: 2.6rem;
  border: 1px solid var(--rv-border);
  border-radius: var(--rv-radius-sm);
  background-color: var(--rv-surface-2);
  color: var(--rv-ink);
  box-shadow: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}
.control-moderno:hover {
  border-color: #cdd6d3;
}
.control-moderno:focus {
  background-color: var(--rv-surface);
  border-color: var(--rv-primary-2);
  box-shadow: 0 0 0 3px rgba(20, 114, 112, 0.16);
}
.input-group > .control-moderno:not(:last-child) {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
.input-group > .control-moderno:not(:first-child) {
  border-top-left-radius: 0;
  border-bottom-left-radius: 0;
  margin-left: -1px;
}

.grupo-acciones .btn-accion {
  height: 2.6rem;
  border-radius: var(--rv-radius-sm) !important;
  font-weight: 600;
  font-size: 0.9rem;
  transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
.grupo-acciones .btn {
  margin-right: 0.5rem;
}
.grupo-acciones .btn:last-child {
  margin-right: 0;
}
.btn-mostrar {
  background: var(--rv-primary-2);
  border: 1px solid var(--rv-primary-2);
  color: #fff;
  box-shadow: 0 1px 2px rgba(12, 58, 59, 0.25);
}
.btn-mostrar:hover:not(:disabled) {
  background: var(--rv-primary-1);
  border-color: var(--rv-primary-1);
}
.btn-mostrar--pendiente {
  background: var(--rv-accent);
  border-color: var(--rv-accent);
  color: #3f2b08;
  box-shadow: 0 0 0 3px rgba(224, 163, 57, 0.3);
}
.btn-mostrar--pendiente:hover:not(:disabled) {
  background: #cf931f;
  border-color: #cf931f;
  color: #3f2b08;
}
.btn-excel {
  background: var(--rv-surface);
  border: 1px solid var(--rv-excel);
  color: var(--rv-excel);
}
.btn-excel:hover:not(:disabled) {
  background: #e9f5ee;
  border-color: var(--rv-excel);
  color: var(--rv-excel);
}
.btn-imprimir {
  background: var(--rv-surface);
  border: 1px solid var(--rv-border);
  color: var(--rv-ink);
}
.btn-imprimir:hover:not(:disabled) {
  background: var(--rv-surface-2);
  border-color: #cdd6d3;
  color: var(--rv-ink);
}
.grupo-acciones .btn:focus,
.grupo-acciones .btn:focus-visible {
  box-shadow: 0 0 0 3px rgba(20, 114, 112, 0.25);
}
.aviso-pendiente {
  color: var(--rv-accent-ink);
  font-weight: 500;
}
.badge-contador {
  background: var(--rv-primary-2);
  color: #fff;
  font-weight: 500;
}

/* chips de filtros adicionales */
.chip {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.82rem;
  font-weight: 500;
  padding: 0.3rem 0.4rem 0.3rem 0.8rem;
  margin: 0 0.4rem 0.4rem 0;
  border-radius: 999px;
  background: var(--rv-primary-tint);
  color: var(--rv-primary-1);
}
.quitar-chip {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 1.3rem;
  height: 1.3rem;
  font-size: 0.65rem;
  border-radius: 50%;
  cursor: pointer;
  background: rgba(12, 58, 59, 0.1);
  transition: background-color 0.15s ease;
}
.quitar-chip:hover {
  background: rgba(12, 58, 59, 0.22);
}
.enlace-limpiar {
  color: var(--rv-primary-2);
  font-weight: 500;
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
  padding: 1rem 1.5rem;
  background-color: var(--rv-surface-2);
  border-bottom: 1px solid var(--rv-border);
}
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 0.75rem;
}
.stat-box {
  --tone: #7fb1ad;
  padding: 0.8rem 1rem 0.85rem;
  background: var(--rv-surface);
  border: 1px solid var(--rv-border);
  border-radius: 12px;
}
.stat-box--primary { --tone: var(--rv-primary-2); }
.stat-box--danger { --tone: var(--rv-danger); }
.stat-box--success { --tone: var(--rv-success); }
.stat-box--muted { --tone: #a4b0ac; }
.stat-box--registros { --tone: var(--rv-accent); }
.stat-label {
  display: flex;
  align-items: center;
  font-size: 0.76rem;
  font-weight: 500;
  color: var(--rv-ink-soft);
  margin-bottom: 0.3rem;
}
.stat-label::before {
  content: '';
  flex: none;
  width: 0.5rem;
  height: 0.5rem;
  margin-right: 0.45rem;
  border-radius: 50%;
  background: var(--tone);
}
.stat-value {
  font-size: 1.4rem;
  font-weight: 600;
  letter-spacing: -0.015em;
  color: var(--rv-ink);
  font-variant-numeric: tabular-nums;
}

/* ===================== BARRAS DE OPCIONES (interruptor y filtro de proveedores) ===================== */
.filtro-proveedores-reporte {
  padding: 0.85rem 1.5rem;
  background-color: var(--rv-surface);
  border-bottom: 1px solid var(--rv-border);
}
.barra-presentaciones {
  padding-top: 0.7rem;
  padding-bottom: 0.7rem;
}
.filtro-proveedores-reporte >>> .custom-switch .custom-control-label {
  font-weight: 500;
  font-size: 0.9rem;
  cursor: pointer;
}
.filtro-proveedores-reporte >>> .custom-control-input:checked ~ .custom-control-label::before {
  background-color: var(--rv-primary-2);
  border-color: var(--rv-primary-2);
}
.filtro-proveedores-reporte >>> .custom-control-input:focus ~ .custom-control-label::before {
  box-shadow: 0 0 0 3px rgba(20, 114, 112, 0.2);
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
.proveedores-checklist >>> .custom-control-input:checked ~ .custom-control-label::before {
  background-color: var(--rv-primary-2);
  border-color: var(--rv-primary-2);
}

/* ===================== PESTAÑAS ===================== */
.reporte-tabs {
  border: none;
}
.reporte-tabs >>> .reporte-tabs-nav-wrapper {
  padding: 0.7rem 1.1rem;
  background: var(--rv-surface) !important;
  border-bottom: 1px solid var(--rv-border);
}
.reporte-tabs >>> .nav-tabs {
  border-bottom: none;
  margin: 0;
  flex-wrap: nowrap;
  overflow-x: auto;
  overflow-y: hidden;
  padding: 0.15rem;
  scrollbar-width: thin;
}
.reporte-tabs >>> .nav-tabs .nav-item {
  white-space: nowrap;
  margin-right: 0.3rem;
}
.reporte-tabs >>> .nav-tabs .nav-link {
  border: 1px solid transparent;
  border-radius: 999px;
  padding: 0.5rem 1rem;
  font-size: 0.86rem;
  font-weight: 500;
  color: var(--rv-ink-soft);
  background: transparent;
  transition: background-color 0.15s ease, color 0.15s ease;
}
.reporte-tabs >>> .nav-tabs .nav-link:hover {
  color: var(--rv-primary-1);
  background: var(--rv-surface-2);
  border-color: transparent;
}
.reporte-tabs >>> .nav-tabs .nav-link.active {
  color: var(--rv-primary-1);
  background: var(--rv-primary-tint);
  border-color: transparent;
  font-weight: 600;
}
.reporte-tabs >>> .nav-tabs .nav-link:focus-visible {
  outline: 2px solid var(--rv-primary-2);
  outline-offset: 2px;
}
.reporte-tabs >>> .tab-content {
  background: var(--rv-surface);
}
.reporte-tabs >>> .tab-pane.card-body {
  padding: 0;
}

/* ===================== TABLAS ===================== */
.reporte-tabs >>> .table-responsive {
  margin-bottom: 0;
  border: 1px solid var(--rv-border);
  border-radius: 12px;
  background: var(--rv-surface);
}
.reporte-tabs >>> table {
  margin-bottom: 0;
  color: var(--rv-ink);
}
.reporte-tabs >>> table thead th {
  padding: 0.75rem 0.9rem;
  background-color: var(--rv-surface-2);
  color: var(--rv-ink-soft);
  font-size: 0.78rem;
  font-weight: 600;
  letter-spacing: 0.005em;
  white-space: nowrap;
  border-top: none;
  border-bottom: 1px solid var(--rv-border);
}
.reporte-tabs >>> table td {
  padding: 0.7rem 0.9rem;
  font-size: 0.88rem;
  vertical-align: middle;
  border-top: 1px solid var(--rv-border-soft);
}
.reporte-tabs >>> table tbody tr:first-child td {
  border-top: none;
}
.reporte-tabs >>> .table-striped tbody tr:nth-of-type(odd) {
  background-color: transparent;
}
.reporte-tabs >>> .table-hover tbody tr:hover,
.reporte-tabs >>> table tbody tr:hover {
  background-color: #f2f8f7;
}
.reporte-tabs >>> .num,
.reporte-tabs >>> td.text-right {
  font-variant-numeric: tabular-nums;
}
.reporte-tabs >>> .b-table-bottom-row td,
.reporte-tabs >>> .b-table-bottom-row:hover td {
  background-color: var(--rv-primary-tint);
  color: var(--rv-primary-1);
  font-weight: 600;
  border-top: 1px solid #c4dcd8;
}
.reporte-tabs >>> .fila-subtotal-vendedor td {
  background-color: #eef5f3 !important;
  border-top: 1px solid #c4dcd8;
  color: var(--rv-primary-1);
}

/* ===================== ESTADO VACÍO ===================== */
.reporte-tabs >>> .estado-vacio {
  padding: 3rem 1rem;
  color: var(--rv-ink-soft);
}
.reporte-tabs >>> .empty-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 3.5rem;
  margin-bottom: 0.9rem;
  font-size: 1.3rem;
  color: var(--rv-primary-2);
  background: var(--rv-primary-tint);
  border-radius: 50%;
}
.reporte-tabs >>> .empty-titulo {
  font-weight: 600;
  color: var(--rv-ink);
}
.reporte-tabs >>> .empty-texto {
  font-size: 0.88rem;
}

/* ===================== OVERLAY DE CARGA ===================== */
.reporte-tabs >>> .carga-overlay-wrapper {
  position: relative;
  min-height: 3rem;
  padding: 1rem 1.5rem 1.5rem;
}
.reporte-tabs >>> .carga-overlay-mascara {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(1.5px);
}
.reporte-tabs >>> .carga-overlay-mascara .spinner-border {
  color: var(--rv-primary-2);
}

/* transición de chips */
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.2s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}

@media (prefers-reduced-motion: reduce) {
  .reporte-ventas,
  .reporte-ventas * {
    transition: none !important;
  }
}

@media print {
  .no-print {
    display: none !important;
  }
  .reporte-card {
    border: none !important;
    box-shadow: none !important;
    border-radius: 0;
  }
  .reporte-tabs >>> .carga-overlay-wrapper {
    padding: 0;
  }
  .reporte-tabs >>> .table-responsive {
    border: none;
  }
}

@media (max-width: 576px) {
  .reporte-header,
  .filtros-card,
  .resumen-bar,
  .filtro-proveedores-reporte {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .reporte-tabs >>> .carga-overlay-wrapper {
    padding: 0.75rem 1rem 1rem;
  }
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
  .stat-value {
    font-size: 1.2rem;
  }
}
</style>