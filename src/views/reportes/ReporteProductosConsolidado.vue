<template>
  <div class="reporte-productos-consolidado">
    <b-card no-body class="reporte-card">

      <!-- ===================== CABECERA ===================== -->
      <div class="reporte-header no-print d-flex align-items-center justify-content-between flex-wrap">
        <div class="d-flex align-items-center">
          <span class="header-icon mr-3"><i class="fas fa-boxes"></i></span>
          <div>
            <h5 class="mb-0 titulo-reporte">Consolidado de Productos Vendidos</h5>
            <small class="subtitulo-reporte">Cantidad total por producto {{ tituloModo }}</small>
          </div>
        </div>
        <b-badge v-if="cargando" class="badge-generando">
          <span class="spinner-border spinner-border-sm mr-2" role="status" />Generando...
        </b-badge>
      </div>

      <!-- ===================== FILTROS ===================== -->
      <b-card-body class="no-print filtros-card">
        <b-row>
          <b-col md="4" sm="6">
            <b-form-group>
              <label class="filtro-label">
                <span class="lbl-icon"><i class="fas fa-store"></i></span> Sucursal / almacén
              </label>
              <b-form-select v-model="idAlmacen" :options="almacenes" size="md" class="control-moderno" />
            </b-form-group>
          </b-col>

          <b-col md="4" sm="6">
            <b-form-group>
              <label class="filtro-label">
                <span class="lbl-icon"><i class="fas fa-calendar-alt"></i></span> Día de venta
              </label>
              <b-input-group size="md">
                <b-form-input v-model="fechaDesde" type="date" :max="fechaHasta" class="control-moderno" />
                <b-form-input v-model="fechaHasta" type="date" :min="fechaDesde" class="control-moderno" />
              </b-input-group>
            </b-form-group>
          </b-col>

          <b-col md="4" sm="6">
            <b-form-group>
              <label class="filtro-label">
                <span class="lbl-icon"><i class="fas fa-user-tie"></i></span> Vendedor
                <i v-if="cargandoVendedores" class="fas fa-spinner fa-spin ml-2 spinner-inline" title="Cargando vendedores..."></i>
              </label>
              <b-input-group size="md">
                <b-form-select v-model="vendedor" :options="opcionesVendedor" :disabled="cargandoVendedores" class="control-moderno" />
                <b-input-group-append>
                  <b-button class="btn-limpiar" :disabled="!vendedor" @click="vendedor = ''">✕</b-button>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>
        </b-row>

        <div v-if="rangoInvalido" class="rd-error-fecha mb-2">
          <i class="fas fa-exclamation-circle mr-1"></i>
          La fecha "desde" no puede ser posterior a la fecha "hasta".
        </div>

        <b-row class="align-items-end">
          <b-col md="6" class="mb-2">
            <label class="filtro-label mb-2">
              <span class="lbl-icon"><i class="fas fa-layer-group"></i></span> Presentar
            </label>
            <div class="modo-selector">
              <button
                v-for="op in opcionesModo"
                :key="op.value"
                type="button"
                class="modo-btn"
                :class="{ 'modo-btn--activo': modo === op.value }"
                :aria-pressed="modo === op.value"
                @click="modo = op.value"
              >
                {{ op.text }}
              </button>
            </div>
          </b-col>

          <b-col md="6" class="d-flex align-items-end justify-content-md-end flex-wrap">
            <b-form-group class="mb-0 w-100">
              <div class="d-flex align-items-center justify-content-md-end flex-wrap grupo-acciones">
                <!-- Orientación de la hoja A4 del PDF -->
                <div class="orientacion mr-2 mb-2" role="group" aria-label="Orientación de la hoja A4">
                  <button
                    type="button"
                    class="orient-btn"
                    :class="{ 'orient-btn--activo': orientacion === 'portrait' }"
                    :aria-pressed="orientacion === 'portrait'"
                    title="Hoja A4 vertical"
                    @click="orientacion = 'portrait'"
                  >
                    <span class="hoja hoja--v"></span>
                  </button>
                  <button
                    type="button"
                    class="orient-btn"
                    :class="{ 'orient-btn--activo': orientacion === 'landscape' }"
                    :aria-pressed="orientacion === 'landscape'"
                    title="Hoja A4 horizontal"
                    @click="orientacion = 'landscape'"
                  >
                    <span class="hoja hoja--h"></span>
                  </button>
                </div>

                <b-button-group class="grupo-acciones-btns mb-2">
                  <b-button
                    class="btn-accion btn-mostrar"
                    :class="{ 'btn-mostrar--pendiente': hayCambiosPendientes && !cargando }"
                    :disabled="cargando || rangoInvalido"
                    @click="mostrarReporte"
                  >
                    <span v-if="cargando" class="spinner-border spinner-border-sm mr-2" role="status" />
                    <i v-else class="fas fa-search mr-2"></i>
                    Mostrar
                  </b-button>
                  <b-button
                    class="btn-accion btn-imprimir"
                    :disabled="generandoPdf || !datos.length"
                    @click="generarPdf"
                  >
                    <span v-if="generandoPdf" class="spinner-border spinner-border-sm mr-2" role="status" />
                    <i v-else class="fas fa-file-pdf mr-2"></i>
                    {{ generandoPdf ? 'Generando...' : 'Generar PDF' }}
                  </b-button>
                </b-button-group>
              </div>
            </b-form-group>
            <small v-if="hayCambiosPendientes && !cargando" class="d-block w-100 text-md-right mt-n2 mb-2 aviso-pendiente">
              Tienes cambios sin aplicar. Presiona <strong>Mostrar</strong> para actualizar el reporte.
            </small>
          </b-col>
        </b-row>
      </b-card-body>

      <!-- ===================== KPIs ===================== -->
      <div v-if="datos.length" class="no-print resumen-bar">
        <div class="kpi-grid">
          <div class="stat-box stat-box--primary">
            <div class="stat-label">Productos distintos</div>
            <div class="stat-value">{{ formatoNumero(totales.productos) }}</div>
          </div>
          <div class="stat-box stat-box--registros">
            <div class="stat-label">Unidades totales</div>
            <div class="stat-value">{{ formatoCantidad(totales.unidades) }}</div>
          </div>
        </div>
      </div>

      <!-- ===================== UNIFICAR PRESENTACIONES ===================== -->
      <div v-if="datos.length" class="no-print filtro-proveedores-reporte barra-presentaciones">
        <b-form-checkbox v-model="unificarPresentaciones" switch>
          Unificar presentaciones <small class="text-muted ml-1">suma todo en unidades</small>
        </b-form-checkbox>
      </div>

      <!-- ===================== TABLA ===================== -->
      <div class="cuerpo-reporte">
        <CargaOverlay :cargando="cargando">
          <template v-if="datos.length">
            <div v-if="modo === 'conjunto'" class="tabla-consolidado">
              <TablaReporte :items="datosVista" :fields="campos" :loading="false" />
            </div>

            <!--
              El backend siempre nos devuelve el detalle con Vendedor incluido
              (ver mostrarReporte: la petición fuerza modo:'vendedor'), así
              que el agrupamiento de abajo ya es correcto sin necesidad de
              volver a consultar el servidor al cambiar de "Presentar".
            -->
            <div v-else>
              <div v-for="grupo in grupos" :key="grupo.nombre" class="grupo-vendedor">
                <div class="grupo-vendedor-header d-flex justify-content-between flex-wrap">
                  <strong>{{ grupo.nombre || '(Sin vendedor)' }}</strong>
                  <span class="small">
                    {{ grupo.items.length }} producto{{ grupo.items.length === 1 ? '' : 's' }} ·
                    Unidades <strong>{{ formatoCantidad(grupo.Cantidad) }}</strong>
                  </span>
                </div>
                <div class="tabla-consolidado">
                  <TablaReporte :items="grupo.items" :fields="campos" :loading="false" />
                </div>
              </div>
            </div>
          </template>
          <EstadoVacio v-else :cargando="cargando" />
        </CargaOverlay>
      </div>
    </b-card>
  </div>
</template>

<script>
import config from '@/store/config.js'
import TablaReporte from '@/components/TablaReporte'

// Estado "sin resultados" (mismo patrón que ReporteVentas.vue)
const EstadoVacio = {
  props: { cargando: { type: Boolean, default: false } },
  template: `
    <div v-if="!cargando" class="text-center estado-vacio">
      <div class="empty-icon"><i class="fas fa-inbox"></i></div>
      <div class="empty-titulo">Sin resultados</div>
      <div class="empty-texto">No hay productos vendidos con los filtros seleccionados.</div>
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

const TITULOS_MODO = { conjunto: 'en conjunto', vendedor: 'por vendedor' }

const redondear3 = n => Math.round((Number(n) || 0) * 1000) / 1000

// Agrupa/consolida filas para su presentación en pantalla.
// - porVendedor=true agrupa además por vendedor (modo "Bloque por vendedor").
// - unificarUnidades=true convierte cada fila a unidades reales
//   (Cantidad * CantidadPresentacion) y colapsa todas las presentaciones de
//   un mismo producto en una sola fila.
// - unificarUnidades=false mantiene una fila por presentación, pero igual
//   combina filas que compartan producto+presentación (y vendedor, si
//   corresponde). Esto es necesario porque el backend ahora SIEMPRE nos
//   devuelve el detalle desglosado por vendedor (ver mostrarReporte), así
//   que para el modo "conjunto" hay que sumar entre vendedores acá, en el
//   cliente, sin volver a consultar el servidor.
function agruparFilas (filas, porVendedor, unificarUnidades) {
  const mapa = {}
  filas.forEach(f => {
    const partes = []
    if (porVendedor) partes.push(f.Vendedor || '')
    partes.push(f.IdProducto)
    if (!unificarUnidades) partes.push(f.Presentacion || '')
    const clave = partes.join('|')
    if (!mapa[clave]) {
      const g = { ...f }
      if (unificarUnidades) {
        delete g.Presentacion
        delete g.CantidadPresentacion
        delete g.CantidadTexto
      }
      g.Cantidad = 0
      mapa[clave] = g
    }
    const factor = unificarUnidades && Number(f.CantidadPresentacion) > 0 ? Number(f.CantidadPresentacion) : 1
    mapa[clave].Cantidad += (Number(f.Cantidad) || 0) * factor
  })
  return Object.values(mapa).map(g => ({ ...g, Cantidad: redondear3(g.Cantidad) }))
}

export default {
  name: 'ReporteProductosConsolidado',
  components: { TablaReporte, EstadoVacio, CargaOverlay },

  mounted () {
    this.getAlmacenes()
    this.cargarVendedores()
    this.mostrarReporte()
  },

  data () {
    return {
      API: config.API,

      cargando: false,
      cargandoVendedores: false,
      generandoPdf: false,

      idAlmacen: 0,
      fechaDesde: this.$moment().format('YYYY-MM-DD'),
      fechaHasta: this.$moment().format('YYYY-MM-DD'),
      vendedor: '',
      modo: 'conjunto',

      // Orientación de la hoja A4 del PDF: 'landscape' (horizontal) | 'portrait' (vertical)
      orientacion: 'landscape',

      // Interruptor "Unificar presentaciones": true = una fila por producto,
      // convertida a unidades reales; false = una fila por presentación tal
      // cual se vendió. Es solo de presentación en pantalla: no dispara una
      // nueva consulta al servidor (igual que en ReporteVentas.vue). Al
      // generar el PDF sí se manda al backend para que unifique server-side.
      unificarPresentaciones: true,

      almacenes: [{ value: 0, text: 'TODOS' }],
      vendedores: [], // resultado de GET /usuarios-activos
      vendedoresCargados: false,

      // Última combinación de filtros con la que efectivamente se generó el reporte
      filtrosAplicados: null,

      // Parámetros con los que se consultó el último reporte (para pedir el PDF sin volver a mostrar)
      ultimosParams: null,

      datos: [],

      opcionesModo: [
        { value: 'conjunto', text: 'Todos los vendedores en conjunto' },
        { value: 'vendedor', text: 'Bloque por vendedor' }
      ]
    }
  },

  computed: {
    campos () {
      const base = [
        { key: 'CodigoBarra', label: 'Código', sortable: true },
        { key: 'Producto', label: 'Producto', sortable: true },
        { key: 'Medicion', label: 'Medición' }
      ]
      // La columna "Presentación" solo tiene sentido cuando las filas siguen
      // separadas por presentación de venta; al unificar, todo queda en
      // unidades reales y esa columna deja de aplicar.
      if (!this.unificarPresentaciones) {
        base.push({ key: 'Presentacion', label: 'Presentación' })
      }
      base.push({
        key: 'Cantidad',
        label: this.unificarPresentaciones ? 'Cantidad (unidades)' : 'Cantidad',
        class: 'text-right',
        sortable: true,
        // Al unificar ya no existe CantidadTexto (se quita en agruparFilas),
        // así que cae al formato numérico normal.
        formatter: (v, key, item) => item.CantidadTexto || this.formatoCantidad(v)
      })
      return base
    },
    // Opciones del select de vendedor: "Todos" + usuarios activos
    opcionesVendedor () {
      const lista = this.vendedores
        .filter(v => v.NombreUsuario)
        .map(v => ({ value: v.NombreUsuario, text: v.NombreUsuario }))
      return [{ value: '', text: 'Todos' }, ...lista]
    },
    // Filas efectivamente mostradas en la tabla, según el modo ("conjunto" /
    // "vendedor") y el interruptor de unificación. Ambas cosas son solo de
    // presentación: this.datos ya trae el detalle completo por vendedor.
    datosVista () {
      return agruparFilas(this.datos, this.modo === 'vendedor', this.unificarPresentaciones)
    },
    totales () {
      const ids = {}
      let unidades = 0
      // Las "Unidades totales" del KPI siempre se calculan en unidades reales,
      // sin importar el estado del switch (igual que en ReporteVentas.vue).
      this.datos.forEach(f => {
        ids[f.IdProducto] = true
        const factor = Number(f.CantidadPresentacion) > 0 ? Number(f.CantidadPresentacion) : 1
        unidades += (Number(f.Cantidad) || 0) * factor
      })
      return { productos: Object.keys(ids).length, unidades }
    },
    // Agrupa por vendedor (solo se usa en el modo "bloque por vendedor")
    grupos () {
      const mapa = {}
      this.datosVista.forEach(f => {
        const nombre = f.Vendedor || ''
        if (!mapa[nombre]) mapa[nombre] = { nombre, items: [], Cantidad: 0 }
        mapa[nombre].items.push(f)
        mapa[nombre].Cantidad += Number(f.Cantidad) || 0
      })
      return Object.values(mapa).sort((a, b) => a.nombre.localeCompare(b.nombre))
    },
    tituloModo () {
      return TITULOS_MODO[this.modo]
    },
    rangoInvalido () {
      return !this.fechaDesde || !this.fechaHasta || this.fechaDesde > this.fechaHasta
    },
    // Snapshot de los filtros actuales, usado para detectar cambios sin aplicar.
    // "unificarPresentaciones" y "modo" NO entran aquí a propósito: son solo
    // cambios de vista sobre datos ya cargados (this.datos siempre trae el
    // detalle completo por vendedor, ver mostrarReporte), así que cambiarlos
    // no requiere volver a pedir datos al servidor ni muestra el aviso de
    // "cambios sin aplicar".
    filtrosActualesSnapshot () {
      return JSON.stringify({
        idAlmacen: this.idAlmacen,
        fechaDesde: this.fechaDesde,
        fechaHasta: this.fechaHasta,
        vendedor: this.vendedor
      })
    },
    hayCambiosPendientes () {
      if (this.filtrosAplicados === null) return false
      return this.filtrosActualesSnapshot !== this.filtrosAplicados
    }
  },

  methods: {
    endpoint (path) {
      const base = this.API.endsWith('/') ? this.API.slice(0, -1) : this.API
      const rel = path.startsWith('/') ? path : '/' + path
      return base + rel
    },
    notificar (mensaje, tipo) {
      tipo = tipo || 'error'
      if (this.$toasted && typeof this.$toasted.show === 'function') {
        this.$toasted.show(mensaje, { type: tipo })
      } else {
        console.warn('[ReporteProductosConsolidado]', mensaje)
      }
    },
    // Cantidad sin ceros sobrantes: 12 -> "12", 1.5 -> "1.5", 1250 -> "1,250"
    formatoCantidad (valor) {
      return new Intl.NumberFormat('en-US', { maximumFractionDigits: 3 }).format(Number(valor) || 0)
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

    // ---------- VENDEDOR: GET /usuarios-activos ----------
    cargarVendedores () {
      if (this.vendedoresCargados) return
      this.vendedoresCargados = true
      this.cargandoVendedores = true

      this.axios.get(this.endpoint('usuarios-activos')).then(r => {
        this.vendedores = Array.isArray(r.data) ? r.data : []
      }).catch(() => {
        this.vendedoresCargados = false // permite reintentar
        this.notificar('No se pudo cargar la lista de vendedores')
      }).finally(() => {
        this.cargandoVendedores = false
      })
    },

    // ---------- MOSTRAR: consulta el consolidado con los filtros actuales ----------
    async mostrarReporte () {
      if (this.rangoInvalido) {
        this.notificar('Ingrese un rango de fechas válido')
        return
      }
      if (this.cargando) return

      this.cargando = true
      this.datos = []

      // "modo" NO va aquí: los filtros que sí obligan a re-consultar el
      // servidor son estos. this.ultimosParams se reutiliza tal cual para
      // el PDF, agregándole ahí el modo vigente en pantalla en ese momento.
      this.ultimosParams = {
        idAlmacen: this.idAlmacen,
        fechaDesde: this.fechaDesde,
        fechaHasta: this.fechaHasta,
        vendedor: this.vendedor || null
      }

      try {
        // Pedimos siempre el detalle desglosado por vendedor (modo:'vendedor'),
        // sin importar qué pestaña de "Presentar" esté activa en pantalla.
        // Así this.datos sirve para armar tanto la vista "conjunto" como la
        // vista "por vendedor" en el cliente (ver agruparFilas / datosVista),
        // y cambiar de pestaña no necesita volver a pedir datos al servidor.
        const paramsFetch = Object.assign({}, this.ultimosParams, { modo: 'vendedor' })
        const resp = await this.axios.get(this.endpoint('reporte/productos/consolidado'), { params: paramsFetch })
        this.datos = Array.isArray(resp.data) ? resp.data : []
      } catch (e) {
        console.error(e)
        this.datos = []
        this.notificar('No se pudo generar el consolidado. Intente nuevamente.')
      } finally {
        this.cargando = false
        this.filtrosAplicados = this.filtrosActualesSnapshot
      }
    },

    // ---------- PDF real, generado en el servidor con dompdf ----------
    async generarPdf () {
      if (!this.ultimosParams || this.generandoPdf) return
      const ventana = window.open('', '_blank')
      this.generandoPdf = true
      try {
        // Acá sí mandamos el modo tal como está seleccionado en pantalla en
        // este momento (this.modo), no el que se usó para pedir los datos
        // (que siempre es 'vendedor'), para que el PDF salga en el mismo
        // formato ("conjunto" o "por vendedor") que el usuario está viendo.
        const params = Object.assign({}, this.ultimosParams, {
          modo: this.modo,
          orientacion: this.orientacion,
          unificar: this.unificarPresentaciones
        })
        const response = await this.axios.get(this.endpoint('reporte/productos/consolidado/pdf'), {
          params: params,
          responseType: 'blob'
        })
        const blob = new Blob([response.data], { type: 'application/pdf' })
        const url = URL.createObjectURL(blob)
        if (ventana && !ventana.closed) {
          ventana.location.href = url
        } else {
          const a = document.createElement('a')
          a.href = url
          a.download = 'consolidado-productos.pdf'
          document.body.appendChild(a)
          a.click()
          a.remove()
        }
        setTimeout(() => URL.revokeObjectURL(url), 60000)
      } catch (e) {
        console.error(e)
        if (ventana && !ventana.closed) ventana.close()
        this.notificar('No se pudo generar el PDF. Intente nuevamente.')
      } finally {
        this.generandoPdf = false
      }
    }
  }
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600&display=swap');

/* ===================== TOKENS DE DISEÑO ===================== */
.reporte-productos-consolidado {
  --rd-ink: #14231f;
  --rd-ink-soft: #566762;
  --rd-bg: #f2f5f4;
  --rd-surface: #ffffff;
  --rd-surface-2: #f8faf9;
  --rd-border: #e1e7e5;
  --rd-border-soft: #edf1ef;
  --rd-primary-1: #0c3a3b;
  --rd-primary-2: #21a5a3;
  --rd-primary-tint: #e2f0ee;
  --rd-accent: #e0a339;
  --rd-accent-ink: #7a5417;
  --rd-radius: 16px;
  --rd-radius-sm: 10px;
  font-family: 'IBM Plex Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
  color: var(--rd-ink);
  background: var(--rd-bg);
  padding: 0.25rem 0;
}

.reporte-card {
  border: 1px solid var(--rd-border) !important;
  border-radius: var(--rd-radius);
  overflow: hidden;
  background: var(--rd-surface);
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
  padding: 1.25rem 1.5rem 1rem;
  background-color: var(--rd-surface);
  border-bottom: 1px solid var(--rd-border);
}
.filtro-label {
  display: flex;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 600;
  color: var(--rd-ink-soft);
  margin-bottom: 0.4rem;
}
.lbl-icon {
  display: inline-block;
  width: 1.3em;
  color: var(--rd-primary-2);
}
.spinner-inline {
  margin-left: 0.5rem;
  color: var(--rd-primary-2);
  font-size: 0.72rem;
}
.rd-error-fecha {
  display: flex;
  align-items: center;
  color: #c0392b;
  font-size: 0.8rem;
}
.control-moderno {
  height: 2.6rem;
  border: 1px solid var(--rd-border);
  border-radius: var(--rd-radius-sm);
  background-color: var(--rd-surface-2);
  color: var(--rd-ink);
  box-shadow: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}
.control-moderno:hover {
  border-color: #cdd6d3;
}
.control-moderno:focus {
  background-color: var(--rd-surface);
  border-color: var(--rd-primary-2);
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
.btn-limpiar {
  height: 2.6rem;
  border: 1px solid var(--rd-border);
  border-left: none;
  background: var(--rd-surface-2);
  color: var(--rd-ink-soft);
}
.btn-limpiar:hover:not(:disabled) {
  background: #eef1f0;
  color: var(--rd-ink);
}

/* ===================== SELECTOR "PRESENTAR" (píldoras) ===================== */
.modo-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.modo-btn {
  border: 1px solid var(--rd-border);
  background: var(--rd-surface);
  color: var(--rd-ink-soft);
  font-size: 0.85rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.modo-btn:hover {
  border-color: #cdd6d3;
  background: var(--rd-surface-2);
}
.modo-btn--activo {
  background: var(--rd-primary-tint);
  border-color: var(--rd-primary-2);
  color: var(--rd-primary-1);
  font-weight: 600;
}

/* ===================== ORIENTACIÓN DEL PDF ===================== */
.orientacion {
  display: inline-flex;
  border: 1px solid var(--rd-border);
  border-radius: var(--rd-radius-sm);
  overflow: hidden;
  background: var(--rd-surface);
}
.orient-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  height: 2.6rem;
  width: 2.6rem;
  border: none;
  background: var(--rd-surface);
  color: var(--rd-ink-soft);
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
}
.orient-btn + .orient-btn {
  border-left: 1px solid var(--rd-border);
}
.orient-btn:hover {
  background: var(--rd-surface-2);
}
.orient-btn--activo,
.orient-btn--activo:hover {
  background: var(--rd-primary-tint);
  color: var(--rd-primary-1);
}
.hoja {
  display: inline-block;
  border: 2px solid currentColor;
  border-radius: 2px;
}
.hoja--v {
  width: 11px;
  height: 15px;
}
.hoja--h {
  width: 15px;
  height: 11px;
}

/* ===================== BOTONES DE ACCIÓN ===================== */
.grupo-acciones-btns .btn-accion {
  height: 2.6rem;
  border-radius: var(--rd-radius-sm) !important;
  font-weight: 600;
  font-size: 0.9rem;
  transition: background-color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}
.grupo-acciones-btns .btn {
  margin-right: 0.5rem;
}
.grupo-acciones-btns .btn:last-child {
  margin-right: 0;
}
.btn-mostrar {
  background: var(--rd-primary-2);
  border: 1px solid var(--rd-primary-2);
  color: #fff;
  box-shadow: 0 1px 2px rgba(12, 58, 59, 0.25);
}
.btn-mostrar:hover:not(:disabled) {
  background: var(--rd-primary-1);
  border-color: var(--rd-primary-1);
}
.btn-mostrar--pendiente {
  background: var(--rd-accent);
  border-color: var(--rd-accent);
  color: #3f2b08;
  box-shadow: 0 0 0 3px rgba(224, 163, 57, 0.3);
}
.btn-mostrar--pendiente:hover:not(:disabled) {
  background: #cf931f;
  border-color: #cf931f;
  color: #3f2b08;
}
.btn-imprimir {
  background: var(--rd-surface);
  border: 1px solid var(--rd-border);
  color: var(--rd-ink);
}
.btn-imprimir:hover:not(:disabled) {
  background: var(--rd-surface-2);
  border-color: #cdd6d3;
  color: var(--rd-ink);
}
.grupo-acciones-btns .btn:disabled {
  opacity: 0.65;
}
.aviso-pendiente {
  color: var(--rd-accent-ink);
  font-weight: 500;
}

/* ===================== KPIs ===================== */
.resumen-bar {
  padding: 1rem 1.5rem;
  background-color: var(--rd-surface-2);
  border-bottom: 1px solid var(--rd-border);
}
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 0.75rem;
}
.stat-box {
  --tone: #7fb1ad;
  padding: 0.8rem 1rem 0.85rem;
  background: var(--rd-surface);
  border: 1px solid var(--rd-border);
  border-radius: 12px;
}
.stat-box--primary { --tone: var(--rd-primary-2); }
.stat-box--registros { --tone: var(--rd-accent); }
.stat-label {
  display: flex;
  align-items: center;
  font-size: 0.76rem;
  font-weight: 500;
  color: var(--rd-ink-soft);
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
  color: var(--rd-ink);
  font-variant-numeric: tabular-nums;
}

/* ===================== INTERRUPTOR "UNIFICAR PRESENTACIONES" ===================== */
.filtro-proveedores-reporte {
  padding: 0.85rem 1.5rem;
  background-color: var(--rd-surface);
  border-bottom: 1px solid var(--rd-border);
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
  background-color: var(--rd-primary-2);
  border-color: var(--rd-primary-2);
}
.filtro-proveedores-reporte >>> .custom-control-input:focus ~ .custom-control-label::before {
  box-shadow: 0 0 0 3px rgba(20, 114, 112, 0.2);
}

/* ===================== TABLA ===================== */
.cuerpo-reporte {
  background: var(--rd-surface);
}
.cuerpo-reporte >>> .carga-overlay-wrapper {
  position: relative;
  min-height: 3rem;
  padding: 1rem 1.5rem 1.5rem;
}
.cuerpo-reporte >>> .carga-overlay-mascara {
  position: absolute;
  inset: 0;
  z-index: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(1.5px);
}
.cuerpo-reporte >>> .carga-overlay-mascara .spinner-border {
  color: var(--rd-primary-2);
}

.grupo-vendedor {
  margin-bottom: 1.25rem;
}
.grupo-vendedor-header {
  padding: 0.55rem 0.9rem;
  background: var(--rd-primary-tint);
  color: var(--rd-primary-1);
  border: 1px solid #c4dcd8;
  border-radius: 10px 10px 0 0;
}
.grupo-vendedor .tabla-consolidado >>> .table-responsive {
  border-top: none;
  border-radius: 0 0 10px 10px;
}

.tabla-consolidado >>> .table-responsive {
  margin-bottom: 0;
  border: 1px solid var(--rd-border);
  border-radius: 10px;
  background: var(--rd-surface);
}
.tabla-consolidado >>> table {
  margin-bottom: 0;
  color: var(--rd-ink);
}
.tabla-consolidado >>> table thead th {
  padding: 0.7rem 0.9rem;
  background-color: var(--rd-surface-2);
  color: var(--rd-ink-soft);
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
  border-top: none;
  border-bottom: 1px solid var(--rd-border);
}
.tabla-consolidado >>> table td {
  padding: 0.6rem 0.9rem;
  font-size: 0.86rem;
  vertical-align: middle;
  border-top: 1px solid var(--rd-border-soft);
}
.tabla-consolidado >>> table tbody tr:first-child td {
  border-top: none;
}
.tabla-consolidado >>> td.text-right {
  font-variant-numeric: tabular-nums;
  font-weight: 600;
}
.tabla-consolidado >>> table tbody tr:hover {
  background-color: #f2f8f7;
}

/* ===================== ESTADO VACÍO ===================== */
.cuerpo-reporte >>> .estado-vacio {
  padding: 3rem 1rem;
  color: var(--rd-ink-soft);
}
.cuerpo-reporte >>> .empty-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 3.5rem;
  margin-bottom: 0.9rem;
  font-size: 1.3rem;
  color: var(--rd-primary-2);
  background: var(--rd-primary-tint);
  border-radius: 50%;
}
.cuerpo-reporte >>> .empty-titulo {
  font-weight: 600;
  color: var(--rd-ink);
}
.cuerpo-reporte >>> .empty-texto {
  font-size: 0.88rem;
}

@media (max-width: 576px) {
  .reporte-header,
  .filtros-card,
  .resumen-bar,
  .filtro-proveedores-reporte {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  .grupo-acciones {
    justify-content: flex-start !important;
  }
  .grupo-acciones-btns {
    flex-wrap: wrap;
  }
  .grupo-acciones-btns .btn-accion {
    flex: 1 1 100%;
    margin-right: 0 !important;
    margin-bottom: 0.4rem;
  }
}
</style>