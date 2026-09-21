<template>
  <span class="reporte-deuda">
    <!-- Botón que abre el reporte. Se puede reemplazar con el slot "boton" -->
    <slot name="boton" :abrir="abrir">
      <b-button class="btn-deuda" :class="'btn-deuda--' + variante" @click="abrir">
        <i class="fas fa-file-invoice-dollar mr-2"></i>{{ textoBoton }}
      </b-button>
    </slot>

    <!-- ===================== MODAL 1: FILTROS ===================== -->
    <b-modal
      v-model="modalFiltros"
      size="lg"
      hide-header-close
      body-class="rd-modal-body"
      content-class="rd-modal-content"
    >
      <!-- Se usa la sintaxis slot="..." (funciona en Vue 2.5 y 2.6+) -->
      <template slot="modal-header">
        <div class="rd-modal-header d-flex align-items-center justify-content-between w-100">
          <div class="d-flex align-items-center">
            <span class="rd-header-icon mr-3"><i class="fas fa-file-invoice-dollar"></i></span>
            <div>
              <h5 class="mb-0 rd-header-titulo">Reporte de Pedidos por Pagar</h5>
              <small class="rd-header-subtitulo">Consulta el reporte de cobranza y genera el PDF</small>
            </div>
          </div>
          <button type="button" class="rd-cerrar" aria-label="Cerrar" @click="modalFiltros = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
      </template>

      <div class="rd-seccion">
        <div class="rd-seccion-titulo">
          <i class="fas fa-store"></i> Sucursal y fecha
        </div>
        <b-row>
          <b-col md="4">
            <b-form-group class="mb-3">
              <label class="rd-filtro-label"><span class="rd-lbl-icon"><i class="fas fa-store"></i></span> Sucursal</label>
              <b-form-select v-model="filtros.idAlmacen" :options="almacenes" class="rd-control" />
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group class="mb-3">
              <label class="rd-filtro-label"><span class="rd-lbl-icon"><i class="fas fa-calendar-alt"></i></span> Fecha de Pedido (desde)</label>
              <b-form-input v-model="filtros.desde" type="date" :max="filtros.hasta" class="rd-control" />
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group class="mb-3">
              <label class="rd-filtro-label"><span class="rd-lbl-icon"><i class="fas fa-calendar-check"></i></span> Fecha de Pedido (hasta)</label>
              <b-form-input v-model="filtros.hasta" type="date" :min="filtros.desde" class="rd-control" />
            </b-form-group>
          </b-col>
        </b-row>
      </div>

      <div class="rd-seccion">
        <div class="rd-seccion-titulo">
          <i class="fas fa-users"></i> Cliente y vendedor
        </div>
        <b-row>
          <!-- CLIENTE: autocompletado contra GET /clientes?q=... -->
          <b-col md="6">
            <b-form-group class="mb-3">
              <label class="rd-filtro-label"><span class="rd-lbl-icon"><i class="fas fa-user"></i></span> Cliente</label>
              <div class="rd-autocomplete">
                <b-input-group>
                  <b-form-input
                    :value="clienteTexto"
                    placeholder="Todos"
                    autocomplete="off"
                    class="rd-control"
                    @input.native="onClienteInput($event.target.value)"
                    @blur.native="onClienteBlur"
                    @keydown.down.native.prevent="moverCliente(1)"
                    @keydown.up.native.prevent="moverCliente(-1)"
                    @keydown.esc.native="clienteAbierto = false"
                    @keydown.enter.native.prevent="onClienteEnter"
                  />
                  <b-input-group-append>
                    <b-button class="rd-btn-limpiar" :disabled="!clienteTexto" @click="limpiarCliente">✕</b-button>
                  </b-input-group-append>
                </b-input-group>

                <div v-if="clienteAbierto" class="rd-sugerencias" @mousedown.prevent>
                  <div v-if="clienteBuscando" class="rd-sug-estado">Buscando...</div>
                  <div v-else-if="!clientesOpciones.length" class="rd-sug-estado">Sin coincidencias</div>
                  <template v-else>
                    <div
                      v-for="(c, i) in clientesOpciones"
                      :key="c.IdCliente || c.DniRuc || i"
                      class="rd-sug-item"
                      :class="{ 'rd-sug-item--activo': i === clienteIndice }"
                      @click="elegirCliente(c)"
                      @mouseenter="clienteIndice = i"
                    >
                      <span class="rd-sug-nombre">{{ c.Cliente }}</span>
                      <small class="rd-sug-doc">{{ c.DniRuc }}</small>
                    </div>
                  </template>
                </div>
              </div>
            </b-form-group>
          </b-col>

          <!-- VENDEDOR: lista cargada desde GET /usuarios-activos -->
          <b-col md="6">
            <b-form-group class="mb-3">
              <label class="rd-filtro-label"><span class="rd-lbl-icon"><i class="fas fa-user-tie"></i></span> Vendedor</label>
              <b-input-group>
                <b-form-select v-model="filtros.vendedor" :options="opcionesVendedor" class="rd-control" />
                <b-input-group-append>
                  <b-button class="rd-btn-limpiar" :disabled="!filtros.vendedor" @click="filtros.vendedor = ''">✕</b-button>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>
        </b-row>
      </div>

      <div class="rd-seccion rd-seccion--ultima">
        <div class="rd-seccion-titulo">
          <i class="fas fa-layer-group"></i> Agrupar por
        </div>
        <div class="rd-modo-selector">
          <button
            v-for="op in opcionesModo"
            :key="op.value"
            type="button"
            class="rd-modo-btn"
            :class="{ 'rd-modo-btn--activo': filtros.modo === op.value }"
            @click="filtros.modo = op.value"
          >
            {{ op.text }}
          </button>
        </div>
      </div>

      <template slot="modal-footer">
        <div class="d-flex justify-content-end w-100">
          <b-button class="rd-btn rd-btn-outline mr-2" @click="modalFiltros = false">Cerrar</b-button>
          <!-- "Mostrar" solo consulta y muestra la vista previa en pantalla. El PDF se genera en el otro modal -->
          <b-button class="rd-btn rd-btn-primary" :disabled="cargando" @click="mostrar()">
            <span v-if="cargando" class="spinner-border spinner-border-sm mr-2" role="status" />
            <i v-else class="fas fa-search mr-2"></i>
            {{ cargando ? 'Cargando...' : 'Mostrar' }}
          </b-button>
        </div>
      </template>
    </b-modal>

    <!-- ===================== MODAL 2: REPORTE PARA COBRANZA (vista previa en pantalla) ===================== -->
    <b-modal
      v-model="modalResultado"
      title="Reporte para cobranza"
      size="xl"
      scrollable
      hide-footer
    >
      <div class="d-flex align-items-start justify-content-between flex-wrap mb-3">
        <div class="rd-cabecera">
          <div class="rd-titulo">Reporte para cobranza {{ cabecera.titulo }}</div>
          <div class="small">Fecha de Pedido: <strong>{{ cabecera.fecha }}</strong></div>
          <div class="small">Sucursal: <strong>{{ cabecera.sucursal }}</strong></div>
          <div class="small">Vendedor: <strong>{{ cabecera.vendedor }}</strong></div>
          <div v-if="cabecera.cliente" class="small">Cliente: <strong>{{ cabecera.cliente }}</strong></div>
        </div>

        <div class="d-flex align-items-center flex-wrap">
          <b-button size="sm" variant="outline-secondary" class="mr-2" @click="volverAFiltros">
            <i class="fas fa-sliders-h mr-1"></i>Cambiar filtros
          </b-button>

          <!-- Orientación de la hoja A4: se elige aquí, justo antes de generar el PDF -->
          <div class="rd-orientacion mr-2" role="group" aria-label="Orientación de la hoja A4">
            <button
              type="button"
              class="rd-orient-btn"
              :class="{ 'rd-orient-btn--activo': orientacion === 'portrait' }"
              title="Hoja A4 vertical"
              @click="orientacion = 'portrait'"
            >
              <span class="rd-hoja rd-hoja--v"></span>Vertical
            </button>
            <button
              type="button"
              class="rd-orient-btn"
              :class="{ 'rd-orient-btn--activo': orientacion === 'landscape' }"
              title="Hoja A4 horizontal"
              @click="orientacion = 'landscape'"
            >
              <span class="rd-hoja rd-hoja--h"></span>Horizontal
            </button>
          </div>

          <b-button size="sm" class="rd-btn rd-btn-primary" :disabled="generandoPdf || !datos.length" @click="generarPdf()">
            <span v-if="generandoPdf" class="spinner-border spinner-border-sm mr-1" role="status" />
            <i v-else class="fas fa-file-pdf mr-1"></i>
            {{ generandoPdf ? 'Generando PDF...' : 'Generar PDF' }}
          </b-button>
        </div>
      </div>

      <div v-if="datos.length" class="rd-totales mb-3">
        <span class="mr-4">Total venta: <strong>{{ formatoMoneda(totales.TotalVenta) }}</strong></span>
        <span class="mr-4">Total pagado: <strong>{{ formatoMoneda(totales.TotalPagado) }}</strong></span>
        <span>Saldo por pagar: <strong>{{ formatoMoneda(totales.Saldo) }}</strong></span>
      </div>

      <!-- cargando -->
      <div v-if="cargando" class="text-center py-5">
        <span class="spinner-border rd-spinner" role="status" />
      </div>

      <!-- resultados -->
      <template v-else-if="datos.length">
        <div v-if="cabecera.modo === 'resumido'" class="rd-tabla">
          <TablaReporte :items="datos" :fields="campos" :loading="false" />
        </div>
        <div v-else>
          <div v-for="grupo in grupos" :key="grupo.nombre" class="rd-grupo">
            <div class="rd-grupo-header d-flex justify-content-between flex-wrap">
              <strong>{{ grupo.nombre || '(Sin nombre)' }}</strong>
              <span class="small">
                Venta {{ formatoMoneda(grupo.TotalVenta) }} ·
                Pagado {{ formatoMoneda(grupo.TotalPagado) }} ·
                Saldo <strong>{{ formatoMoneda(grupo.Saldo) }}</strong>
              </span>
            </div>
            <div class="rd-tabla">
              <TablaReporte :items="grupo.items" :fields="campos" :loading="false" />
            </div>
          </div>
        </div>
      </template>

      <!-- vacío -->
      <div v-else class="text-center rd-vacio">
        <div class="rd-vacio-icono"><i class="fas fa-inbox"></i></div>
        <div class="rd-vacio-titulo">Sin resultados</div>
        <div class="small">No hay pedidos por pagar con los filtros seleccionados.</div>
      </div>
    </b-modal>
  </span>
</template>

<script>
import config from '@/store/config.js'
import TablaReporte from '@/components/TablaReporte'

const crearFiltros = (idAlmacen, moment) => ({
  idAlmacen: idAlmacen || 0,
  // Fecha de pedido: siempre por rango, por defecto el día actual
  desde: moment().format('YYYY-MM-DD'),
  hasta: moment().format('YYYY-MM-DD'),
  idTipoCliente: 0,
  cliente: '', // nombre del cliente SELECCIONADO en la lista ('' = todos)
  vendedor: '', // NombreUsuario del vendedor seleccionado ('' = todos)
  modo: 'resumido' // 'resumido' | 'vendedor' | 'cliente'
})

const TITULOS_MODO = { resumido: 'General', vendedor: 'por Vendedor', cliente: 'por Cliente' }

export default {
  name: 'ReporteDeuda',
  components: { TablaReporte },

  props: {
    // Lista de sucursales [{ value, text }], normalmente con "TODOS" (value 0) al inicio
    almacenes: { type: Array, default: () => [{ value: 0, text: 'TODOS' }] },
    // Sucursal que se preselecciona al abrir el reporte
    idAlmacen: { type: Number, default: 0 },
    textoBoton: { type: String, default: 'Pedidos por pagar' },
    // 'cabecera' = translúcido para fondos oscuros; 'primario' = sólido para fondos claros
    variante: { type: String, default: 'cabecera' }
  },

  data () {
    return {
      API: config.API,

      modalFiltros: false,
      modalResultado: false,
      cargando: false,
      generandoPdf: false,
      catalogosCargados: false,

      filtros: crearFiltros(this.idAlmacen, this.$moment),

      // Orientación de la hoja A4 del PDF: 'landscape' (horizontal) | 'portrait' (vertical)
      orientacion: 'landscape',

      // ----- Cliente (autocompletado) -----
      clienteTexto: '', // lo que se ve escrito en el input
      clientesOpciones: [], // resultados de GET /clientes
      clienteAbierto: false, // lista desplegada
      clienteBuscando: false,
      clienteIndice: -1, // opción resaltada con el teclado
      clienteTimer: null, // debounce
      clienteSeq: 0, // descarta respuestas viejas

      // ----- Vendedor (lista) -----
      vendedores: [], // resultado de GET /usuarios-activos
      vendedoresCargados: false,

      // Parámetros con los que se consultó el último reporte (para pedir el PDF sin reabrir el modal de filtros)
      ultimosParams: null,

      // Foto de los filtros con los que se generó el reporte (lo que se muestra e imprime)
      cabecera: { titulo: 'General', modo: 'resumido', fecha: '', sucursal: '', vendedor: 'TODOS', cliente: '' },

      datos: [],

      opcionesModo: [
        { value: 'resumido', text: 'Resumido' },
        { value: 'vendedor', text: 'Agrupado por Vendedor' },
        { value: 'cliente', text: 'Agrupado por Cliente' }
      ],

      tiposCliente: [{ value: 0, text: '(Todos)' }]
    }
  },

  computed: {
    campos () {
      return [
        { key: 'Documento', label: 'N° Doc.', sortable: true },
        { key: 'Fecha', label: 'Fecha', sortable: true, formatter: v => this.formatoFecha(v) },
        { key: 'Cliente', label: 'Cliente', sortable: true },
        { key: 'Direccion', label: 'Dirección' },
        { key: 'TotalVenta', label: 'Total Venta', class: 'text-right', formatter: v => this.formatoDecimal(v) },
        { key: 'TotalPagado', label: 'Total Pag.', class: 'text-right', formatter: v => this.formatoDecimal(v) },
        { key: 'Saldo', label: 'Saldo Pag.', class: 'text-right', formatter: v => this.formatoDecimal(v) }
      ]
    },
    // Opciones del select de vendedor: "Todos" + usuarios activos
    opcionesVendedor () {
      const lista = this.vendedores
        .filter(v => v.NombreUsuario)
        .map(v => ({ value: v.NombreUsuario, text: v.NombreUsuario }))
      return [{ value: '', text: 'Todos' }, ...lista]
    },
    totales () {
      const t = { TotalVenta: 0, TotalPagado: 0, Saldo: 0 }
      this.datos.forEach(f => {
        t.TotalVenta += Number(f.TotalVenta) || 0
        t.TotalPagado += Number(f.TotalPagado) || 0
        t.Saldo += Number(f.Saldo) || 0
      })
      return t
    },
    // Agrupa por Vendedor o Cliente (solo se usa en los modos agrupados)
    grupos () {
      const campo = this.cabecera.modo === 'vendedor' ? 'Vendedor' : 'Cliente'
      const mapa = {}
      this.datos.forEach(f => {
        const nombre = f[campo] || ''
        if (!mapa[nombre]) mapa[nombre] = { nombre, items: [], TotalVenta: 0, TotalPagado: 0, Saldo: 0 }
        const g = mapa[nombre]
        g.items.push(f)
        g.TotalVenta += Number(f.TotalVenta) || 0
        g.TotalPagado += Number(f.TotalPagado) || 0
        g.Saldo += Number(f.Saldo) || 0
      })
      return Object.values(mapa).sort((a, b) => a.nombre.localeCompare(b.nombre))
    }
  },

  beforeDestroy () {
    clearTimeout(this.clienteTimer)
  },

  methods: {
    // ---------- API pública (se puede llamar con this.$refs.reporteDeuda.abrir()) ----------
    abrir () {
      this.filtros.idAlmacen = this.idAlmacen // hereda la sucursal elegida en la pantalla
      // cada vez que se abre, el rango arranca en el día actual
      const hoy = this.$moment().format('YYYY-MM-DD')
      this.filtros.desde = hoy
      this.filtros.hasta = hoy
      this.cargarCatalogos()
      this.cargarVendedores()
      this.modalFiltros = true
    },

    // ---------- navegación entre modales ----------
    volverAFiltros () {
      this.modalResultado = false
      this.modalFiltros = true
    },

    // ---------- utilidades ----------
    endpoint (path) {
      const base = this.API.endsWith('/') ? this.API.slice(0, -1) : this.API
      const rel = path.startsWith('/') ? path : '/' + path
      return base + rel
    },
    notificar (mensaje, tipo = 'error') {
      if (this.$toasted && typeof this.$toasted.show === 'function') {
        this.$toasted.show(mensaje, { type: tipo })
      } else {
        console.warn('[ReporteDeuda]', mensaje)
      }
    },
    formatoMoneda (valor) {
      return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(Number(valor) || 0)
    },
    formatoDecimal (valor) {
      return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(Number(valor) || 0)
    },
    formatoFecha (v) {
      if (!v) return ''
      if (/^\d{2}\/\d{2}\/\d{4}/.test(v)) return v.slice(0, 10) // ya viene dd/mm/yyyy
      const m = this.$moment(v)
      return m.isValid() ? m.format('DD/MM/YYYY') : v
    },

    // ---------- catálogos (se cargan una sola vez, al abrir por primera vez) ----------
    cargarCatalogos () {
      if (this.catalogosCargados) return
      this.catalogosCargados = true

      this.axios.get(this.endpoint('clientes/tipos')).then(r => {
        if (r.data) {
          this.tiposCliente = [{ value: 0, text: '(Todos)' }]
          r.data.forEach(t => this.tiposCliente.push({ value: t.IdTipoCliente, text: t.TipoCliente }))
        }
      }).catch(() => {})
    },

    // ---------- VENDEDOR: GET /usuarios-activos ----------
    cargarVendedores () {
      if (this.vendedoresCargados) return
      this.vendedoresCargados = true

      this.axios.get(this.endpoint('usuarios-activos')).then(r => {
        this.vendedores = Array.isArray(r.data) ? r.data : []
      }).catch(() => {
        this.vendedoresCargados = false // permite reintentar la próxima vez que se abra
        this.notificar('No se pudo cargar la lista de vendedores')
      })
    },

    // ---------- CLIENTE: autocompletado con GET /clientes?q=... ----------
    // El usuario escribe: se invalida la selección anterior y se busca con un pequeño retraso
    onClienteInput (texto) {
      this.clienteTexto = texto
      this.filtros.cliente = ''
      this.clienteIndice = -1

      clearTimeout(this.clienteTimer)
      // Sin texto no hay búsqueda: se cierra la lista
      if (!texto.trim()) {
        this.clienteAbierto = false
        this.clientesOpciones = []
        return
      }
      this.clienteAbierto = true
      this.clienteTimer = setTimeout(() => this.consultarClientes(texto.trim()), 300)
    },
    // Al salir del campo (clic fuera, Tab, etc.) la lista se cierra
    onClienteBlur () {
      this.clienteAbierto = false
      clearTimeout(this.clienteTimer)
      // Si escribió algo pero no eligió de la lista, se descarta para no mostrar un filtro que no se aplica
      if (!this.filtros.cliente) this.clienteTexto = ''
    },
    async consultarClientes (q) {
      const seq = ++this.clienteSeq
      this.clienteBuscando = true
      try {
        const { data } = await this.axios.get(this.endpoint('clientes'), {
          params: { q, limit: 20, sortBy: 'Cliente' }
        })
        if (seq !== this.clienteSeq) return // llegó una respuesta más nueva
        this.clientesOpciones = Array.isArray(data) ? data : []
      } catch (e) {
        if (seq !== this.clienteSeq) return
        this.clientesOpciones = []
        this.notificar('No se pudo buscar clientes')
      } finally {
        if (seq === this.clienteSeq) this.clienteBuscando = false
      }
    },
    moverCliente (paso) {
      if (!this.clienteAbierto) {
        if (this.clientesOpciones.length && !this.filtros.cliente) this.clienteAbierto = true
        return
      }
      const n = this.clientesOpciones.length
      if (!n) return
      this.clienteIndice = (this.clienteIndice + paso + n) % n
    },
    // Enter: elige la opción resaltada (o la primera); si la lista no está abierta, ejecuta "Mostrar"
    onClienteEnter () {
      if (this.clienteAbierto && this.clientesOpciones.length && !this.filtros.cliente) {
        this.elegirCliente(this.clientesOpciones[this.clienteIndice >= 0 ? this.clienteIndice : 0])
      } else {
        this.clienteAbierto = false
        this.mostrar()
      }
    },
    elegirCliente (c) {
      clearTimeout(this.clienteTimer)
      this.filtros.cliente = c.Cliente
      this.clienteTexto = c.Cliente
      this.clienteAbierto = false
      this.clienteIndice = -1
    },
    limpiarCliente () {
      clearTimeout(this.clienteTimer)
      this.filtros.cliente = ''
      this.clienteTexto = ''
      this.clientesOpciones = []
      this.clienteIndice = -1
    },

    // ---------- rango de fechas (siempre por rango) ----------
    rango () {
      return { desde: this.filtros.desde, hasta: this.filtros.hasta }
    },

    // ---------- MOSTRAR: solo consulta los datos y los muestra en pantalla (NO genera el PDF) ----------
    async mostrar () {
      if (this.cargando) return

      const { desde, hasta } = this.rango()
      if (!desde || !hasta || desde > hasta) {
        this.notificar('Ingrese un rango de fechas válido')
        return
      }

      // Foto de los filtros usados: es lo que se muestra e imprime en la cabecera
      const suc = this.almacenes.find(a => a.value === this.filtros.idAlmacen)
      const f = d => this.$moment(d).format('DD/MM/YYYY')
      let fecha = ''
      if (desde && hasta) fecha = desde === hasta ? f(desde) : `${f(desde)} al ${f(hasta)}`
      this.cabecera = {
        titulo: TITULOS_MODO[this.filtros.modo],
        modo: this.filtros.modo,
        fecha,
        sucursal: suc ? suc.text : '',
        vendedor: this.filtros.vendedor || 'TODOS',
        cliente: this.filtros.cliente || ''
      }

      this.modalFiltros = false
      this.modalResultado = true
      this.cargando = true

      this.ultimosParams = {
        idAlmacen: this.filtros.idAlmacen,
        fechaDesde: desde,
        fechaHasta: hasta,
        idTipoCliente: this.filtros.idTipoCliente,
        cliente: this.filtros.cliente || null,
        vendedor: this.filtros.vendedor || null,
        modo: this.filtros.modo
      }

      try {
        const { data } = await this.axios.get(this.endpoint('reporte/pedidos/por-pagar'), { params: this.ultimosParams })
        this.datos = (Array.isArray(data) ? data : []).map(r => ({
          ...r,
          Saldo: r.Saldo != null ? r.Saldo : (Number(r.TotalVenta) || 0) - (Number(r.TotalPagado) || 0)
        }))
      } catch (e) {
        console.error(e)
        this.datos = []
        this.notificar('No se pudo generar el reporte para cobranza. Intente nuevamente.')
      } finally {
        this.cargando = false
      }
    },

    // ---------- PDF real, generado en el servidor con dompdf ----------
    // Solo se ejecuta al pulsar "Generar PDF" en el modal de resultados, con la orientación elegida allí.
    // La pestaña se abre aquí mismo porque este clic es un gesto directo del usuario
    // (así el navegador no la bloquea como pop-up).
    async generarPdf () {
      if (!this.ultimosParams || this.generandoPdf) return
      const ventana = window.open('', '_blank')
      this.generandoPdf = true
      try {
        const response = await this.axios.get(this.endpoint('reporte/pedidos/por-pagar/pdf'), {
          params: { ...this.ultimosParams, orientacion: this.orientacion },
          responseType: 'blob'
        })
        const blob = new Blob([response.data], { type: 'application/pdf' })
        const url = URL.createObjectURL(blob)
        if (ventana && !ventana.closed) {
          ventana.location.href = url
        } else {
          // pop-up bloqueado por el navegador: se descarga el archivo
          const a = document.createElement('a')
          a.href = url
          a.download = 'reporte-cobranza.pdf'
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
/* ===================== BOTÓN ===================== */
.btn-deuda {
  font-weight: 600;
  font-size: 0.88rem;
  border-radius: 999px;
  padding: 0.5rem 1rem;
  transition: background-color 0.15s ease, border-color 0.15s ease;
}
/* para usarlo sobre la cabecera oscura del reporte */
.btn-deuda--cabecera {
  background: rgba(255, 255, 255, 0.14);
  border: 1px solid rgba(255, 255, 255, 0.3);
  color: #f4faf9;
}
.btn-deuda--cabecera:hover {
  background: rgba(255, 255, 255, 0.26);
  border-color: rgba(255, 255, 255, 0.45);
  color: #fff;
}
/* para usarlo sobre fondo claro */
.btn-deuda--primario {
  background: #21a5a3;
  border: 1px solid #21a5a3;
  color: #fff;
}
.btn-deuda--primario:hover {
  background: #0c3a3b;
  border-color: #0c3a3b;
  color: #fff;
}

/* ===================== MODAL DE FILTROS — cabecera con gradiente, igual que ReporteVentas ===================== */
.reporte-deuda >>> .rd-modal-content {
  border: none;
  border-radius: 16px;
  overflow: hidden;
}
.reporte-deuda >>> .modal-header {
  padding: 0;
  border-bottom: none;
}
.reporte-deuda >>> .rd-modal-body {
  padding: 1.25rem 1.5rem;
  background: #f8faf9;
}
.reporte-deuda >>> .modal-footer {
  border-top: 1px solid #e1e7e5;
  background: #fff;
  padding: 0.9rem 1.5rem;
}

.rd-modal-header {
  padding: 1.1rem 1.5rem;
  color: #f4faf9;
  background:
    radial-gradient(90% 160% at 0% 0%, rgba(5, 252, 252, 0.55) 0%, rgba(38, 150, 145, 0) 60%),
    linear-gradient(120deg, #1c6c6d 0%, #189797 100%);
}
.rd-header-icon {
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
.rd-header-titulo {
  font-size: 1.1rem;
  font-weight: 600;
  letter-spacing: -0.01em;
  color: #f4faf9;
}
.rd-header-subtitulo {
  display: block;
  margin-top: 0.1rem;
  font-size: 0.8rem;
  color: rgba(244, 250, 249, 0.75);
}
.rd-cerrar {
  border: none;
  background: rgba(255, 255, 255, 0.12);
  color: #f4faf9;
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.15s ease;
}
.rd-cerrar:hover {
  background: rgba(255, 255, 255, 0.24);
}

/* ===================== SECCIONES DE FILTROS ===================== */
.rd-seccion {
  padding: 1rem 0;
  border-bottom: 1px solid #e1e7e5;
}
.rd-seccion--ultima {
  border-bottom: none;
  padding-bottom: 0.25rem;
}
.rd-seccion-titulo {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  font-weight: 600;
  color: #566762;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-bottom: 0.75rem;
}
.rd-seccion-titulo i {
  color: #21a5a3;
}

.rd-filtro-label {
  display: flex;
  align-items: center;
  font-size: 0.78rem;
  font-weight: 600;
  color: #566762;
  margin-bottom: 0.4rem;
}
.rd-lbl-icon {
  display: inline-block;
  width: 1.3em;
  color: #21a5a3;
}
.rd-control {
  height: 2.6rem;
  border: 1px solid #e1e7e5;
  border-radius: 10px;
  background-color: #fff;
  color: #14231f;
  box-shadow: none;
  transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
}
.rd-control:hover {
  border-color: #cdd6d3;
}
.rd-control:focus {
  background-color: #fff;
  border-color: #21a5a3;
  box-shadow: 0 0 0 3px rgba(20, 114, 112, 0.16);
}
.input-group > .rd-control:not(:last-child) {
  border-top-right-radius: 0;
  border-bottom-right-radius: 0;
}
.rd-btn-limpiar {
  height: 2.6rem;
  border: 1px solid #e1e7e5;
  border-left: none;
  background: #f8faf9;
  color: #566762;
  border-top-right-radius: 10px;
  border-bottom-right-radius: 10px;
}
.rd-btn-limpiar:hover:not(:disabled) {
  background: #eef1f0;
  color: #14231f;
}

/* ===================== AUTOCOMPLETADO DE CLIENTE ===================== */
.rd-autocomplete {
  position: relative;
}
.rd-sugerencias {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  z-index: 30;
  max-height: 208px;
  overflow-y: auto;
  background: #fff;
  border: 1px solid #e1e7e5;
  border-radius: 10px;
  box-shadow: 0 8px 24px rgba(12, 58, 59, 0.14);
}
.rd-sug-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
  padding: 0.5rem 0.9rem;
  font-size: 0.86rem;
  color: #14231f;
  cursor: pointer;
}
.rd-sug-item + .rd-sug-item {
  border-top: 1px solid #edf1ef;
}
.rd-sug-item--activo {
  background: #e2f0ee;
}
.rd-sug-nombre {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.rd-sug-doc {
  flex-shrink: 0;
  color: #566762;
  font-variant-numeric: tabular-nums;
}
.rd-sug-estado {
  padding: 0.7rem 0.9rem;
  font-size: 0.85rem;
  color: #566762;
}

/* ===================== SELECTOR "AGRUPAR POR" (píldoras) ===================== */
.rd-modo-selector {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}
.rd-modo-btn {
  border: 1px solid #e1e7e5;
  background: #fff;
  color: #566762;
  font-size: 0.85rem;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease;
}
.rd-modo-btn:hover {
  border-color: #cdd6d3;
  background: #f8faf9;
}
.rd-modo-btn--activo {
  background: #e2f0ee;
  border-color: #21a5a3;
  color: #0c3a3b;
  font-weight: 600;
}

/* ===================== SELECTOR DE ORIENTACIÓN (modal de resultados) ===================== */
.rd-orientacion {
  display: inline-flex;
  border: 1px solid #e1e7e5;
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
}
.rd-orient-btn {
  display: inline-flex;
  align-items: center;
  height: 2.6rem;
  padding: 0 0.9rem;
  border: none;
  background: #fff;
  color: #566762;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.15s ease, color 0.15s ease;
}
.rd-orient-btn + .rd-orient-btn {
  border-left: 1px solid #e1e7e5;
}
.rd-orient-btn:hover {
  background: #f8faf9;
}
.rd-orient-btn--activo,
.rd-orient-btn--activo:hover {
  background: #e2f0ee;
  color: #0c3a3b;
  font-weight: 600;
}
.rd-hoja {
  display: inline-block;
  border: 2px solid currentColor;
  border-radius: 2px;
  margin-right: 0.5rem;
}
.rd-hoja--v {
  width: 11px;
  height: 15px;
}
.rd-hoja--h {
  width: 15px;
  height: 11px;
}

/* ===================== BOTONES DE ACCIÓN ===================== */
.rd-btn {
  height: 2.6rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
}
.rd-btn-primary {
  background: #21a5a3;
  border: 1px solid #21a5a3;
  color: #fff;
  box-shadow: 0 1px 2px rgba(12, 58, 59, 0.25);
}
.rd-btn-primary:hover:not(:disabled) {
  background: #0c3a3b;
  border-color: #0c3a3b;
  color: #fff;
}
.rd-btn-primary:disabled {
  opacity: 0.65;
}
.rd-btn-outline {
  background: #fff;
  border: 1px solid #e1e7e5;
  color: #14231f;
}
.rd-btn-outline:hover {
  background: #f8faf9;
  border-color: #cdd6d3;
  color: #14231f;
}

/* ===================== MODAL DE RESULTADOS ===================== */
.rd-titulo {
  font-size: 1.05rem;
  font-weight: 700;
  color: #14231f;
  margin-bottom: 0.25rem;
}
.rd-cabecera .small {
  color: #566762;
}
.rd-totales {
  font-size: 0.92rem;
  color: #566762;
}
.rd-spinner {
  color: #21a5a3;
}

.rd-grupo {
  margin-bottom: 1.25rem;
}
.rd-grupo-header {
  padding: 0.55rem 0.9rem;
  background: #e2f0ee;
  color: #0c3a3b;
  border: 1px solid #c4dcd8;
  border-radius: 10px 10px 0 0;
}

/* tabla (TablaReporte se renderiza dentro, por eso se usa >>>) */
.rd-tabla >>> .table-responsive {
  margin-bottom: 0;
  border: 1px solid #e1e7e5;
  border-radius: 10px;
}
.rd-grupo .rd-tabla >>> .table-responsive {
  border-top: none;
  border-radius: 0 0 10px 10px;
}
.rd-tabla >>> table {
  margin-bottom: 0;
  color: #14231f;
}
.rd-tabla >>> table thead th {
  padding: 0.7rem 0.9rem;
  background-color: #f8faf9;
  color: #566762;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
  border-top: none;
  border-bottom: 1px solid #e1e7e5;
}
.rd-tabla >>> table td {
  padding: 0.6rem 0.9rem;
  font-size: 0.86rem;
  vertical-align: middle;
  border-top: 1px solid #edf1ef;
}
.rd-tabla >>> table tbody tr:first-child td {
  border-top: none;
}
.rd-tabla >>> td.text-right {
  font-variant-numeric: tabular-nums;
}
.rd-tabla >>> table tbody tr:hover {
  background-color: #f2f8f7;
}

/* ===================== ESTADO VACÍO ===================== */
.rd-vacio {
  padding: 3rem 1rem;
  color: #566762;
}
.rd-vacio-icono {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3.5rem;
  height: 3.5rem;
  margin-bottom: 0.9rem;
  font-size: 1.3rem;
  color: #21a5a3;
  background: #e2f0ee;
  border-radius: 50%;
}
.rd-vacio-titulo {
  font-weight: 600;
  color: #14231f;
}
</style>