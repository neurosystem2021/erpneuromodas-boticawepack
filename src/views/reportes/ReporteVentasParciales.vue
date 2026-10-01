<template>
  <div class="rvp-vista">
    <!-- CABECERA -->
    <div class="rvp-cabecera">
      <div>
        <h4 class="rvp-titulo"><i class="fas fa-file-invoice-dollar mr-2"></i>Ventas Parciales: Estado de Pago</h4>
        <p class="rvp-fecha-corte mb-0" v-if="fechaGeneracion">
          <i class="fas fa-clock mr-1"></i>Datos actualizados al {{ fechaGeneracion }}
        </p>
      </div>
      <div class="d-flex flex-wrap" style="gap: .5rem">
        <b-button class="rvp-btn" :disabled="cargando || !ventas.length || exportando === 'excel'" @click="exportarExcel">
          <i class="fas mr-2" :class="exportando === 'excel' ? 'fa-spinner fa-spin' : 'fa-file-excel'" style="color:#1d6f42"></i>Excel
        </b-button>
        <b-button class="rvp-btn" :disabled="cargando || !ventas.length" @click="exportarPdf">
          <i class="fas fa-file-pdf mr-2" style="color:#c0392b"></i>PDF
        </b-button>
      </div>
    </div>

    <!-- FILTROS -->
    <div class="rvp-filtros">
      <div class="rvp-campo">
        <label>Desde</label>
        <b-form-input type="date" size="sm" v-model="fechaDesde" class="rvp-control" @keyup.enter="generar" />
      </div>
      <div class="rvp-campo">
        <label>Hasta</label>
        <b-form-input type="date" size="sm" v-model="fechaHasta" class="rvp-control" @keyup.enter="generar" />
      </div>
      <div class="rvp-campo">
        <label>Estado de pago</label>
        <b-form-select size="sm" v-model="estado" :options="opcionesEstado" class="rvp-control" />
      </div>
      <div class="rvp-campo">
        <label>Ordenar</label>
        <b-form-select size="sm" v-model="orden" :options="opcionesOrden" class="rvp-control" />
      </div>
      <div class="rvp-campo rvp-campo--buscar">
        <label>Cliente, DNI/RUC, comprobante, usuario o vendedor</label>
        <b-input-group size="sm">
          <b-form-input
            v-model="q"
            placeholder="Escribe para filtrar..."
            class="rvp-control rvp-control--buscar"
            @keyup.enter="generar"
          />
          <b-input-group-append>
            <b-button class="rvp-btn-limpiar" :disabled="!q" @click="q = ''">✕</b-button>
          </b-input-group-append>
        </b-input-group>
      </div>
      <div class="rvp-campo rvp-campo--accion">
        <b-button class="rvp-btn-generar" :disabled="cargando" @click="generar">
          <i class="fas mr-2" :class="cargando ? 'fa-spinner fa-spin' : 'fa-play'"></i>Generar
        </b-button>
      </div>
    </div>

    <!-- TARJETAS (se recalculan con lo que se ve en pantalla) -->
    <div class="rvp-resumen">
      <div class="rvp-tarjeta">
        <div class="rvp-icono rvp-icono--azul"><i class="fas fa-receipt"></i></div>
        <div>
          <div class="rvp-valor">{{ resumenVista.CantidadVentas }}</div>
          <div class="rvp-label">Ventas parciales · {{ formatoMoneda(resumenVista.TotalVentas) }}</div>
        </div>
      </div>
      <div class="rvp-tarjeta">
        <div class="rvp-icono rvp-icono--verde"><i class="fas fa-check-circle"></i></div>
        <div>
          <div class="rvp-valor">{{ resumenVista.CantidadPagadas }}</div>
          <div class="rvp-label">Pagadas por completo</div>
        </div>
      </div>
      <div class="rvp-tarjeta">
        <div class="rvp-icono rvp-icono--naranja"><i class="fas fa-hourglass-half"></i></div>
        <div>
          <div class="rvp-valor">{{ resumenVista.CantidadPendientes }}</div>
          <div class="rvp-label">Pendientes · {{ formatoMoneda(resumenVista.TotalSaldoPendiente) }}</div>
        </div>
      </div>
      <div class="rvp-tarjeta rvp-tarjeta--alerta" v-if="resumenVista.CantidadVencidas > 0">
        <div class="rvp-icono rvp-icono--rojo"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
          <div class="rvp-valor">{{ resumenVista.CantidadVencidas }}</div>
          <div class="rvp-label">Vencidas · {{ formatoMoneda(resumenVista.TotalVencido) }}</div>
        </div>
      </div>
    </div>

    <!-- TABLA -->
    <div class="rvp-panel">
      <div v-if="cargando" class="text-center py-5">
        <span class="spinner-border rvp-spinner" role="status" />
      </div>

      <div v-else-if="!ventas.length" class="rvp-vacio">
        <i class="fas fa-inbox mb-2"></i>
        <div>No hay ventas parciales con esos filtros. Ajusta los filtros y pulsa Generar.</div>
      </div>

      <div v-else-if="!ventasFiltradas.length" class="rvp-vacio">
        <i class="fas fa-search mb-2"></i>
        <div>Ninguna venta coincide con "{{ q }}"</div>
      </div>

      <div v-else class="rvp-tabla-wrap">
        <table class="rvp-tabla">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Comprobante</th>
              <th>Cliente</th>
              <th>DNI/RUC</th>
              <th>Registrado por</th>
              <th>Vendedor</th>
              <th>F. venc.</th>
              <th class="text-right">Total</th>
              <th class="text-right" title="Pago inicial + abonos posteriores">
                Pagado <i class="fas fa-info-circle rvp-info"></i>
              </th>
              <th class="text-right" title="Monto aplicado desde notas de crédito">
                N. crédito <i class="fas fa-info-circle rvp-info"></i>
              </th>
              <th class="text-right">Saldo</th>
              <th class="text-center">Estado</th>
              <th>Últ. pago</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="v in ventasPagina" :key="v.IdDocVenta" :class="{ 'rvp-fila-vencida': v.DiasVencido > 0 }">
              <td>{{ formatoFecha(v.FechaDoc) }}</td>
              <td class="rvp-nowrap">{{ v.Correlativo }}</td>
              <td>{{ v.Cliente }}</td>
              <td>{{ v.DniRuc }}</td>
              <td>{{ v.UsuarioRegNombre }}</td>
              <td>{{ v.Vendedor || '—' }}</td>
              <td>{{ formatoFecha(v.FechaCredito) }}</td>
              <td class="text-right">{{ formatoMoneda(v.Total) }}</td>
              <td class="text-right" :title="'Pago inicial: ' + formatoMoneda(v.PagoInicial) + ' | Abonos: ' + formatoMoneda(v.Abonos)">
                {{ formatoMoneda(v.Aplicado) }}
              </td>
              <td class="text-right">{{ v.NotaCredito > 0 ? formatoMoneda(v.NotaCredito) : '—' }}</td>
              <td class="text-right" :class="v.Estado === 'PENDIENTE' ? 'rvp-saldo' : 'rvp-saldo-ok'">
                {{ formatoMoneda(v.Saldo) }}
              </td>
              <td class="text-center">
                <span v-if="v.Estado === 'PAGADO'" class="rvp-chip rvp-chip--pagado">
                  <i class="fas fa-check mr-1"></i>Pagado
                </span>
                <span v-else-if="v.DiasVencido > 0" class="rvp-chip rvp-chip--vencido">
                  <i class="fas fa-exclamation-circle mr-1"></i>Vencido {{ v.DiasVencido }} d
                </span>
                <span v-else class="rvp-chip rvp-chip--pendiente">Pendiente</span>
              </td>
              <td>{{ formatoFecha(v.FechaUltimoPago) }}</td>
              <td class="text-center">
                <div class="rvp-acciones">
                  <button
                    v-if="v.Estado === 'PENDIENTE' && puedeCobrar"
                    type="button"
                    class="rvp-btn-cobrar"
                    title="Cobrar este comprobante"
                    @click="abrirCobro(v)"
                  >
                    <i class="fas fa-money-bill-wave mr-1"></i>Cobrar
                  </button>
                  <button
                    v-if="!estaFacturado(v)"
                    type="button"
                    class="rvp-btn-ver"
                    title="Ver comprobante (solo una vez)"
                    :disabled="viendoId === v.IdDocVenta"
                    @click="verComprobante(v)"
                  >
                    <i class="fas mr-1" :class="viendoId === v.IdDocVenta ? 'fa-spinner fa-spin' : 'fa-file-invoice'"></i>Ver
                  </button>
                  <span v-else class="rvp-chip rvp-chip--facturado" title="Este comprobante ya fue visto">
                    <i class="fas fa-check-double mr-1"></i>Facturado
                  </span>
                </div>
              </td>
            </tr>
          </tbody>
          <tfoot>
            <tr class="rvp-fila-total">
              <td colspan="7" class="text-right">Total ({{ ventasFiltradas.length }} ventas):</td>
              <td class="text-right">{{ formatoMoneda(resumenVista.TotalVentas) }}</td>
              <td class="text-right">{{ formatoMoneda(resumenVista.TotalCobrado) }}</td>
              <td></td>
              <td class="text-right">{{ formatoMoneda(resumenVista.TotalSaldoPendiente) }}</td>
              <td colspan="3"></td>
            </tr>
          </tfoot>
        </table>

        <div class="rvp-paginacion" v-if="ventasFiltradas.length > porPagina">
          <b-pagination v-model="pagina" :total-rows="ventasFiltradas.length" :per-page="porPagina" size="sm" class="mb-0" />
        </div>
      </div>
    </div>

    <!-- ===================== MODAL DE COBRO ===================== -->
    <b-modal
      v-model="modalCobro"
      id="modalCobroVenta"
      size="lg"
      hide-footer
      no-close-on-backdrop
      :no-close-on-esc="guardando"
      :hide-header-close="guardando"
    >
      <template slot="modal-title">
        <i class="fas fa-money-bill-wave mr-2"></i>Cobrar comprobante
      </template>

      <div v-if="cargandoCobro" class="text-center py-4">
        <span class="spinner-border rvp-spinner" role="status" />
      </div>

      <b-form v-else-if="docCobro && ventaCobro" @submit.prevent="guardarCobro">
        <p class="rvp-cobro-cliente">
          <strong>{{ ventaCobro.Cliente }}</strong>
          <span class="rvp-cobro-dniruc">{{ ventaCobro.DniRuc }}</span>
        </p>

        <div class="rvp-cobro-info">
          <div class="rvp-cobro-dato">
            <span>Comprobante</span>
            <strong>{{ docCobro.Correlativo }}</strong>
          </div>
          <div class="rvp-cobro-dato">
            <span>Fecha</span>
            <strong>{{ formatoFecha(docCobro.FechaDoc) }}</strong>
          </div>
          <div class="rvp-cobro-dato">
            <span>Vencimiento</span>
            <strong>{{ formatoFecha(docCobro.FechaCredito) }}</strong>
          </div>
          <div class="rvp-cobro-dato">
            <span>Total</span>
            <strong>{{ formatoMoneda(docCobro.Total) }}</strong>
          </div>
          <div class="rvp-cobro-dato">
            <span>Aplicado</span>
            <strong>{{ formatoMoneda(docCobro.Aplicado) }}</strong>
          </div>
          <div class="rvp-cobro-dato">
            <span>Nota de crédito</span>
            <strong>{{ formatoMoneda(docCobro.NotaCreApli) }}</strong>
          </div>
          <div class="rvp-cobro-dato rvp-cobro-dato--saldo">
            <span>Saldo pendiente</span>
            <strong>{{ formatoMoneda(docCobro.Saldo) }}</strong>
          </div>
        </div>

        <b-row class="mb-2">
          <b-col md="4">
            <label class="rvp-cobro-label">Importe a cobrar</label>
            <b-input-group>
              <b-form-input
                type="number"
                step="0.01"
                min="0.01"
                v-model="cobro.importe"
                required
              />
              <b-input-group-append>
                <b-button variant="outline-primary" title="Cobrar saldo completo" @click="cobrarSaldoCompleto">
                  <i class="fas fa-check"></i>
                </b-button>
              </b-input-group-append>
            </b-input-group>
          </b-col>
          <b-col md="4">
            <label class="rvp-cobro-label">Método</label>
            <b-form-select v-model="cobro.metodo" :options="opcionesMetodo" required />
          </b-col>
          <b-col md="4">
            <label class="rvp-cobro-label">Cuenta</label>
            <b-form-select v-model="cobro.cuenta" :options="opcionesCuenta" required />
          </b-col>
        </b-row>

        <b-row class="mb-2">
          <b-col md="8">
            <label class="rvp-cobro-label">Concepto (opcional)</label>
            <b-form-input type="text" v-model="cobro.concepto" placeholder="Ej: abono parcial, depósito..." />
            <small class="text-muted">El sistema agrega automáticamente cliente, método y comprobante.</small>
          </b-col>
          <b-col md="4">
            <label class="rvp-cobro-label">Vendedor</label>
            <b-form-input type="text" :value="vendedor ? vendedor.NombreUsuario : ''" readonly />
          </b-col>
        </b-row>

        <p class="rvp-cobro-nota mb-3">
          <i class="fas fa-info-circle mr-1"></i>El cobro se registra con la fecha y hora actuales.
        </p>

        <div class="d-flex justify-content-end" style="gap:.5rem">
          <b-button variant="secondary" size="sm" :disabled="guardando" @click="modalCobro = false">
            Cancelar
          </b-button>
          <b-button variant="success" size="sm" type="submit" :disabled="guardando">
            <i class="fas mr-1" :class="guardando ? 'fa-spinner fa-spin' : 'fa-save'"></i>Registrar cobro
          </b-button>
        </div>
      </b-form>
    </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'

function pad (n) { return String(n).padStart(2, '0') }
function primerDiaMes () {
  const d = new Date()
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-01`
}
function hoyISO () {
  const d = new Date()
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}
function normalizar (s) {
  return String(s === null || s === undefined ? '' : s)
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
}
function redondear2 (n) {
  return Math.round((Number(n) || 0) * 100) / 100
}

export default {
  name: 'ReporteVentasParciales',

  data () {
    return {
      API: config.API,
      usuarioLogueado: sessionStorage.getItem('User'),
      cargando: false,
      exportando: '',
      viendoId: null,     // IdDocVenta que se está marcando como facturado
      ventas: [],
      fechaGeneracion: '',

      fechaDesde: primerDiaMes(),
      fechaHasta: hoyISO(),
      estado: 'todos',
      q: '',
      orden: 'fecha_desc',

      pagina: 1,
      porPagina: 25,

      opcionesEstado: [
        { value: 'todos', text: 'Todos' },
        { value: 'pagado', text: 'Pagados por completo' },
        { value: 'pendiente', text: 'Pendientes de pago' }
      ],
      opcionesOrden: [
        { value: 'fecha_desc', text: 'Más recientes' },
        { value: 'saldo_desc', text: 'Mayor saldo' },
        { value: 'vencido_desc', text: 'Más vencidos' }
      ],

      // ----- cobro -----
      modalCobro: false,
      cargandoCobro: false,
      guardando: false,
      ventaCobro: null,   // fila del reporte
      docCobro: null,     // documento fresco desde cliente/deudas
      cobro: { concepto: '', importe: null, metodo: null, cuenta: null },
      cuentas: [],        // [{ value, text }]
      metodos: [],        // [{ value, text }]
      vendedor: null
    }
  },

  computed: {
    // Para restringir por perfil: p. ej. return Number(sessionStorage.getItem('IdPerfil')) !== 3
    puedeCobrar () {
      return true
    },

    opcionesCuenta () {
      return [{ value: null, text: '-- SELECCIONE CUENTA --' }, ...this.cuentas]
    },
    opcionesMetodo () {
      return [{ value: null, text: '-- SELECCIONE METODO --' }, ...this.metodos]
    },

    // Filtro instantáneo en pantalla: todas las palabras deben aparecer en alguno de los campos
    ventasFiltradas () {
      const terminos = normalizar(this.q).split(/\s+/).filter(Boolean)
      if (!terminos.length) return this.ventas
      return this.ventas.filter(v => {
        const texto = normalizar([
          v.Cliente, v.DniRuc, v.Correlativo, v.UsuarioRegNombre, v.UsuarioReg, v.Vendedor
        ].join(' '))
        return terminos.every(t => texto.includes(t))
      })
    },

    ventasOrdenadas () {
      const lista = [...this.ventasFiltradas]
      if (this.orden === 'saldo_desc') {
        lista.sort((a, b) => b.Saldo - a.Saldo)
      } else if (this.orden === 'vencido_desc') {
        lista.sort((a, b) => (b.DiasVencido - a.DiasVencido) || (b.Saldo - a.Saldo))
      }
      return lista
    },

    ventasPagina () {
      const ini = (this.pagina - 1) * this.porPagina
      return this.ventasOrdenadas.slice(ini, ini + this.porPagina)
    },

    // Resumen calculado sobre lo que se ve (así tarjetas y totales siempre coinciden con la tabla)
    resumenVista () {
      const r = {
        CantidadVentas: 0,
        CantidadPagadas: 0,
        CantidadPendientes: 0,
        CantidadVencidas: 0,
        TotalVentas: 0,
        TotalCobrado: 0,
        TotalSaldoPendiente: 0,
        TotalVencido: 0
      }
      this.ventasFiltradas.forEach(v => {
        r.CantidadVentas++
        r.TotalVentas += Number(v.Total) || 0
        r.TotalCobrado += Number(v.Aplicado) || 0
        if (v.Estado === 'PAGADO') {
          r.CantidadPagadas++
        } else {
          r.CantidadPendientes++
          r.TotalSaldoPendiente += Number(v.Saldo) || 0
          if (v.DiasVencido > 0) {
            r.CantidadVencidas++
            r.TotalVencido += Number(v.Saldo) || 0
          }
        }
      })
      return r
    }
  },

  watch: {
    q () { this.pagina = 1 },
    orden () { this.pagina = 1 }
  },

  created () {
    this.cargar()
  },

  methods: {
    endpoint (path) {
      const base = this.API.endsWith('/') ? this.API.slice(0, -1) : this.API
      const rel = path.startsWith('/') ? path : '/' + path
      return base + rel
    },

    toast (msg, tipo) {
      if (this.$toasted && typeof this.$toasted.show === 'function') {
        this.$toasted.show(msg, tipo ? { type: tipo } : undefined)
      }
    },

    // ===================== VER COMPROBANTE (UNA SOLA VEZ) =====================
    estaFacturado (v) {
      return Number(v.Facturado) === 1
    },

    async verComprobante (venta) {
      if (this.estaFacturado(venta) || this.viendoId !== null) return

      if (!window.confirm('El comprobante solo se puede ver UNA vez y luego quedará como "Facturado". ¿Continuar?')) return

      // Se abre la pestaña de inmediato (dentro del clic) para que el navegador no la bloquee
      const ventana = window.open('', '_blank')
      this.viendoId = venta.IdDocVenta

      try {
        await this.axios.post(
          this.endpoint('reporte/ventas-parciales/' + venta.IdDocVenta + '/facturar'),
          null,
          { params: { usuarioReg: this.usuarioLogueado } }
        )
        venta.Facturado = 1
        if (ventana) {
          ventana.location = '/imprimir/index.php?IdDocVenta=' + encodeURIComponent(venta.IdDocVenta) + '&preview=1'
        }
      } catch (e) {
        if (ventana) ventana.close()
        const detalle = e && e.response && e.response.data && e.response.data.error
        this.toast(detalle || 'No se pudo abrir el comprobante', 'error')
        if (e && e.response && e.response.status === 409) venta.Facturado = 1
      } finally {
        this.viendoId = null
      }
    },

    parametros () {
      const p = { usuarioReg: this.usuarioLogueado, estado: this.estado }
      if (this.fechaDesde && this.fechaHasta) {
        p.fechaDesde = this.fechaDesde
        p.fechaHasta = this.fechaHasta
      }
      if (this.q && this.q.trim()) p.q = this.q.trim()
      return p
    },

    generar () {
      this.pagina = 1
      this.cargar()
    },

    async cargar () {
      this.cargando = true
      try {
        const { data } = await this.axios.get(this.endpoint('reporte/ventas-parciales'), {
          params: this.parametros()
        })
        this.ventas = Array.isArray(data.ventas) ? data.ventas : []
        this.fechaGeneracion = new Date().toLocaleString('es-PE', {
          day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        })
      } catch (e) {
        console.error(e)
        this.ventas = []
        this.toast('No se pudo cargar el reporte de ventas parciales', 'error')
      } finally {
        this.cargando = false
      }
    },

    // ===================== COBRO =====================
    async abrirCobro (venta) {
      this.ventaCobro = venta
      this.docCobro = null
      this.cobro = { concepto: '', importe: null, metodo: null, cuenta: null }
      this.modalCobro = true
      this.cargandoCobro = true

      try {
        const [resDeudas, resCuentas, resMetodos, resVend] = await Promise.all([
          this.axios.get(this.endpoint('cliente/deudas'), { params: { idCliente: venta.IdCliente } }),
          this.cuentas.length ? Promise.resolve(null) : this.axios.get(this.endpoint('cuentas')),
          this.metodos.length ? Promise.resolve(null) : this.axios.get(this.endpoint('metodoparcial')),
          this.vendedor ? Promise.resolve(null) : this.axios.get(this.endpoint('usuarios/' + this.usuarioLogueado))
        ])

        if (resCuentas) {
          this.cuentas = (resCuentas.data || []).map(c => ({ value: c.IdCuenta, text: c.Cuenta }))
        }
        if (resMetodos) {
          this.metodos = (resMetodos.data || []).map(m => ({ value: m.IdTipoCajaBanco, text: m.TipoCajaBanco }))
        }
        if (resVend) {
          this.vendedor = resVend.data
        }

        // Saldo fresco del servidor, no el del reporte
        const lista = Array.isArray(resDeudas.data) ? resDeudas.data : []
        const doc = lista.find(d => Number(d.IdDocVenta) === Number(venta.IdDocVenta))

        if (!doc) {
          this.modalCobro = false
          this.toast('Este comprobante ya no tiene saldo pendiente. Se actualizará el reporte.')
          this.cargar()
          return
        }

        try {
          doc.NotasCredito = JSON.parse(doc.NotasCredito)
        } catch (e) {
          doc.NotasCredito = null
        }

        const cuentaDefault = this.cuentas.find(c => String(c.text).trim().toUpperCase() === 'CAJA PRINCIPAL')
        const metodoDefault = this.metodos.find(m => String(m.text).trim().toUpperCase() === 'EFECTIVO')

        this.cobro.importe = redondear2(doc.Saldo)
        this.cobro.cuenta = cuentaDefault ? cuentaDefault.value : null
        this.cobro.metodo = metodoDefault ? metodoDefault.value : null
        this.docCobro = doc
      } catch (e) {
        console.error(e)
        this.modalCobro = false
        this.toast('No se pudo preparar el cobro. Vuelva a intentarlo.', 'error')
      } finally {
        this.cargandoCobro = false
      }
    },

    cobrarSaldoCompleto () {
      if (this.docCobro) this.cobro.importe = redondear2(this.docCobro.Saldo)
    },

    async guardarCobro () {
      if (this.guardando || !this.docCobro) return

      const importe = redondear2(this.cobro.importe)
      const saldo = redondear2(this.docCobro.Saldo)

      if (!(importe > 0)) {
        this.toast('Error: ingrese un importe mayor a 0', 'error')
        return
      }
      if (importe > saldo) {
        this.toast('Error: no puede cobrar más que el saldo (' + this.formatoMoneda(saldo) + ')', 'error')
        return
      }
      if (this.cobro.cuenta == null) {
        this.toast('Error: seleccione la cuenta', 'error')
        return
      }
      if (this.cobro.metodo == null) {
        this.toast('Error: seleccione el método de pago', 'error')
        return
      }
      if (!this.vendedor) {
        this.toast('Error: no se pudo identificar al vendedor', 'error')
        return
      }

      // Mismo formato de documento que envía el módulo del punto de venta
      const documento = Object.assign({}, this.docCobro, {
        aplicar: importe,
        cuenta: this.cobro.cuenta,
        metodoparcial: this.cobro.metodo,
        seleccionado: importe === saldo
      })

      // El servidor usa IdCliente y toma el nombre de "Cliente" para armar el concepto
      const cliente = {
        IdCliente: this.ventaCobro.IdCliente,
        Cliente: this.ventaCobro.Cliente,
        DniRuc: this.ventaCobro.DniRuc
      }

      this.guardando = true
      try {
        const { data } = await this.axios.post(this.endpoint('cliente/deudas/pagar'), {
          cajaBanco: {
            Concepto: (this.cobro.concepto || '').trim(),
            cliente: cliente
          },
          documentos: [documento],
          vendedor: this.vendedor
        })

        this.toast('Se registró el cobro.')
        this.modalCobro = false
        this.cargar()

        if (data && data.idCajaBanco) {
          window.open('/imprimir/pagocredito.php?idCajaBanco=' + data.idCajaBanco)
        }
      } catch (e) {
        console.error(e)
        const detalle = e && e.response && e.response.data && e.response.data.error
        this.toast(detalle || 'Ha ocurrido un error, vuelva a intentarlo', 'error')
      } finally {
        this.guardando = false
      }
    },

    // ===================== EXPORTAR =====================
    async exportarExcel () {
      this.exportando = 'excel'
      try {
        const { data } = await this.axios.get(this.endpoint('reporte/ventas-parciales/excel'), {
          params: this.parametros()
        })
        let url = String(data).replace(/\\/g, '')
        if (!url.startsWith('http://') && !url.startsWith('https://')) {
          url = 'http://' + url
        }
        window.open(url, '_blank')
      } catch (e) {
        console.error(e)
        this.toast('No se pudo exportar el Excel', 'error')
      } finally {
        this.exportando = ''
      }
    },

    exportarPdf () {
      const qs = new URLSearchParams(this.parametros()).toString()
      window.open(`${this.endpoint('reporte/ventas-parciales/pdf')}?${qs}`, '_blank')
    },

    formatoFecha (f) {
      if (!f || String(f).startsWith('0000')) return '—'
      const [y, m, d] = String(f).slice(0, 10).split('-')
      return `${d}/${m}/${y}`
    },

    formatoMoneda (valor) {
      return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(Number(valor) || 0)
    }
  }
}
</script>

<style scoped>
.rvp-vista { padding: 1.5rem; background: #f8faf9; min-height: 100%; }

.rvp-cabecera {
  display: flex; align-items: center; justify-content: space-between;
  flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;
}
.rvp-titulo { font-weight: 700; color: #14231f; margin-bottom: 0.15rem; }
.rvp-subtitulo { color: #566762; font-size: 0.9rem; }
.rvp-fecha-corte { color: #8a9591; font-size: 0.78rem; margin-top: 0.3rem; }

.rvp-btn {
  height: 2.5rem; background: #fff; border: 1px solid #e1e7e5; color: #14231f;
  border-radius: 8px; font-weight: 600; font-size: 0.85rem;
  display: inline-flex; align-items: center;
}
.rvp-btn:hover { background: #f8faf9; border-color: #cdd6d3; color: #14231f; }

.rvp-filtros {
  display: flex; flex-wrap: wrap; gap: 0.9rem; align-items: flex-end;
  background: #fff; border: 1px solid #e1e7e5; border-radius: 14px;
  padding: 0.9rem 1.1rem; margin-bottom: 1.25rem;
}
.rvp-campo { display: flex; flex-direction: column; min-width: 150px; }
.rvp-campo--buscar { min-width: 260px; flex: 1; }
.rvp-campo--accion { min-width: auto; }
.rvp-campo label { font-size: 0.74rem; font-weight: 600; color: #566762; margin-bottom: 0.2rem; }
.rvp-control { height: 2.1rem; border: 1px solid #e1e7e5; border-radius: 8px; font-size: 0.85rem; }
.rvp-control--buscar { border-radius: 8px 0 0 8px; }
.rvp-btn-limpiar {
  height: 2.1rem; border: 1px solid #e1e7e5; border-left: none; background: #f8faf9;
  color: #566762; border-radius: 0 8px 8px 0;
}
.rvp-btn-generar {
  height: 2.1rem; padding: 0 1.3rem; background: #21a5a3; border: 1px solid #21a5a3;
  color: #fff; border-radius: 8px; font-weight: 700; font-size: 0.85rem;
  display: inline-flex; align-items: center;
}
.rvp-btn-generar:hover:not(:disabled) { background: #0c3a3b; border-color: #0c3a3b; color: #fff; }

.rvp-resumen {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
  gap: 1rem; margin-bottom: 1.25rem;
}
.rvp-tarjeta {
  display: flex; align-items: center; gap: 0.9rem; background: #fff;
  border: 1px solid #e1e7e5; border-radius: 14px; padding: 1rem 1.2rem;
}
.rvp-tarjeta--alerta { border-color: #f3c6bd; background: #fff8f6; }
.rvp-icono {
  display: flex; align-items: center; justify-content: center;
  width: 2.8rem; height: 2.8rem; border-radius: 12px; font-size: 1.15rem;
  color: #fff; flex-shrink: 0;
}
.rvp-icono--azul { background: #21a5a3; }
.rvp-icono--verde { background: #2f8f5b; }
.rvp-icono--naranja { background: #e08e3e; }
.rvp-icono--rojo { background: #a3311f; }
.rvp-valor { font-size: 1.35rem; font-weight: 700; color: #14231f; line-height: 1.1; }
.rvp-label { font-size: 0.8rem; color: #566762; }

.rvp-panel { background: #fff; border: 1px solid #e1e7e5; border-radius: 14px; overflow: hidden; min-height: 240px; }
.rvp-vacio {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 4rem 1rem; color: #8a9591; font-size: 0.9rem; text-align: center;
}
.rvp-vacio i { font-size: 1.6rem; color: #cdd6d3; }
.rvp-spinner { color: #21a5a3; }

.rvp-tabla-wrap { overflow-x: auto; }
.rvp-tabla { width: 100%; border-collapse: collapse; }
.rvp-tabla thead th {
  padding: 0.7rem 0.8rem; background: #e2f0ee; color: #0c3a3b; font-size: 0.76rem;
  font-weight: 600; text-align: left; border-bottom: 1px solid #c4dcd8; white-space: nowrap;
}
.rvp-tabla tbody td {
  padding: 0.6rem 0.8rem; font-size: 0.84rem; color: #14231f;
  border-bottom: 1px solid #edf1ef; vertical-align: middle;
}
.rvp-tabla tbody tr:hover { background: #f2f8f7; }
.rvp-fila-vencida { background: #fdf1ef; }
.rvp-nowrap { white-space: nowrap; }
.rvp-info { color: #b9c3bf; font-size: 0.72rem; cursor: help; }
.rvp-saldo { color: #a3311f; font-weight: 600; font-variant-numeric: tabular-nums; }
.rvp-saldo-ok { color: #2f8f5b; font-weight: 600; font-variant-numeric: tabular-nums; }
.rvp-fila-total td {
  padding: 0.75rem 0.8rem; font-weight: 700; border-top: 2px solid #d5ddda;
  background: #f8faf9; color: #0c3a3b;
}
.rvp-paginacion { display: flex; justify-content: center; padding: 0.9rem; }

.text-right { text-align: right; }
.text-center { text-align: center; }

.rvp-chip {
  display: inline-flex; align-items: center; padding: 0.15rem 0.6rem;
  border-radius: 999px; font-size: 0.72rem; font-weight: 700; white-space: nowrap;
}
.rvp-chip--pagado { background: #e3f4ea; color: #2f8f5b; }
.rvp-chip--pendiente { background: #fdf1d6; color: #8a6414; }
.rvp-chip--vencido { background: #fbe4df; color: #a3311f; }
.rvp-chip--facturado { background: #e2f0ee; color: #0c3a3b; border: 1px solid #c4dcd8; padding: 0.3rem 0.7rem; }

.rvp-acciones {
  display: inline-flex; align-items: center; justify-content: center;
  gap: 0.4rem; flex-wrap: nowrap;
}
.rvp-btn-cobrar,
.rvp-btn-ver {
  display: inline-flex; align-items: center; padding: 0.3rem 0.7rem;
  border-radius: 8px; font-size: 0.78rem; font-weight: 700; cursor: pointer;
  white-space: nowrap; transition: background-color 0.12s ease;
}
.rvp-btn-cobrar { border: 1px solid #cdeadd; background: #eafbf2; color: #2f8f5b; }
.rvp-btn-cobrar:hover { background: #d7f4e5; }
.rvp-btn-ver { border: 1px solid #c4dcd8; background: #e2f0ee; color: #0c3a3b; }
.rvp-btn-ver:hover { background: #d0e7e3; }
.rvp-btn-ver:disabled { opacity: 0.6; cursor: not-allowed; }

/* Modal de cobro */
.rvp-cobro-cliente { font-size: 0.95rem; color: #14231f; margin-bottom: 0.8rem; }
.rvp-cobro-dniruc { color: #566762; margin-left: 0.5rem; }
.rvp-cobro-info {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 0.6rem; margin-bottom: 1.1rem;
}
.rvp-cobro-dato {
  background: #f8faf9; border: 1px solid #e1e7e5; border-radius: 10px;
  padding: 0.5rem 0.7rem; display: flex; flex-direction: column;
}
.rvp-cobro-dato span { font-size: 0.7rem; color: #8a9591; }
.rvp-cobro-dato strong { font-size: 0.9rem; color: #14231f; }
.rvp-cobro-dato--saldo { background: #fdf1ef; border-color: #f3c6bd; }
.rvp-cobro-dato--saldo strong { color: #a3311f; }
.rvp-cobro-label { font-size: 0.78rem; font-weight: 600; color: #566762; margin-bottom: 0.2rem; }
.rvp-cobro-nota { font-size: 0.78rem; color: #8a9591; }
</style>

<style>
#modalCobroVenta .modal-dialog { max-width: 820px !important; }
</style>