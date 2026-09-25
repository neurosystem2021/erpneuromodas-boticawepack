<template>
  <div class="rdc-vista">
    <!-- ===================== CABECERA DEL MÓDULO ===================== -->
    <div class="rdc-cabecera">
      <div>
        <h4 class="rdc-titulo"><i class="fas fa-users mr-2"></i>Deuda de Clientes por Vendedor</h4>
        <p class="rdc-subtitulo mb-0">
          {{ esVistaIndividual ? 'Clientes con saldo pendiente de tu cartera' : 'Clientes con saldo pendiente, agrupados por el vendedor asignado' }}
        </p>
        <p class="rdc-fecha-corte mb-0" v-if="fechaGeneracion">
          <i class="fas fa-clock mr-1"></i>Datos actualizados al {{ fechaGeneracion }}
        </p>
      </div>
      <div class="d-flex flex-wrap" style="gap: .5rem">
        <b-button
            class="rdc-btn rdc-btn-outline"
            :disabled="cargando || !usuarios.length || exportando === 'excel'"
            @click="exportarExcel"
        >
            <i class="fas mr-2" :class="exportando === 'excel' ? 'fa-spinner fa-spin' : 'fa-file-excel'" style="color:#1d6f42"></i>Excel
        </b-button>
        <b-button
            class="rdc-btn rdc-btn-outline"
            :disabled="cargando || !usuarios.length"
            @click="exportarPdf"
        >
            <i class="fas fa-file-pdf mr-2" style="color:#c0392b"></i>PDF
        </b-button>
        <b-button class="rdc-btn rdc-btn-outline" :disabled="cargando" @click="cargar">
            <i class="fas fa-sync-alt mr-2" :class="{ 'fa-spin': cargando }"></i>Actualizar
        </b-button>
      </div>
    </div>

    <!-- ===================== TARJETAS DE RESUMEN ===================== -->
    <div class="rdc-resumen">
      <div class="rdc-tarjeta" v-if="!esVistaIndividual">
        <div class="rdc-tarjeta-icono rdc-tarjeta-icono--azul"><i class="fas fa-user-tie"></i></div>
        <div>
          <div class="rdc-tarjeta-valor">{{ usuarios.length }}</div>
          <div class="rdc-tarjeta-label">Vendedores con clientes deudores</div>
        </div>
      </div>
      <div class="rdc-tarjeta">
        <div class="rdc-tarjeta-icono rdc-tarjeta-icono--naranja"><i class="fas fa-user-friends"></i></div>
        <div>
          <div class="rdc-tarjeta-valor">{{ totalClientes }}</div>
          <div class="rdc-tarjeta-label">Clientes con deuda</div>
        </div>
      </div>
      <div class="rdc-tarjeta">
        <div class="rdc-tarjeta-icono rdc-tarjeta-icono--rojo"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
          <div class="rdc-tarjeta-valor">{{ formatoMoneda(totalGeneral) }}</div>
          <div class="rdc-tarjeta-label">Saldo total pendiente</div>
        </div>
      </div>
      <div class="rdc-tarjeta">
        <div class="rdc-tarjeta-icono rdc-tarjeta-icono--verde"><i class="fas fa-hand-holding-usd"></i></div>
        <div>
          <div class="rdc-tarjeta-valor">{{ formatoMoneda(totalAdelantoGeneral) }}</div>
          <div class="rdc-tarjeta-label">Adelanto total disponible <span class="rdc-label-hint">(a favor del cliente)</span></div>
        </div>
      </div>
      <!-- Tarjeta de vencidos: aparece sola cuando el backend manda SaldoVencido por cliente -->
      <div class="rdc-tarjeta rdc-tarjeta--alerta" v-if="hayInfoVencidoGlobal">
        <div class="rdc-tarjeta-icono rdc-tarjeta-icono--vencido"><i class="fas fa-exclamation-triangle"></i></div>
        <div>
          <div class="rdc-tarjeta-valor">{{ formatoMoneda(totalVencidoGeneral) }}</div>
          <div class="rdc-tarjeta-label">Saldo vencido (requiere atención)</div>
        </div>
      </div>
    </div>

    <!-- ===================== CUERPO ===================== -->
    <div class="rdc-cuerpo" :class="{ 'rdc-cuerpo--single': esVistaIndividual }">
      <!-- PANEL IZQUIERDO: lista de vendedores -->
      <div v-if="!esVistaIndividual" class="rdc-panel rdc-panel-vendedores">
        <div class="rdc-panel-header">
          <i class="fas fa-user-tie mr-2"></i>Vendedores
        </div>

        <div v-if="cargando" class="text-center py-5">
          <span class="spinner-border rdc-spinner" role="status" />
        </div>

        <div v-else-if="!usuarios.length" class="rdc-vacio-panel">
          <i class="fas fa-inbox mb-2"></i>
          <div>Sin resultados</div>
        </div>

        <div v-else class="rdc-lista-vendedores">
          <button
            v-for="u in usuarios"
            :key="u.IdUsuarioAsignado"
            type="button"
            class="rdc-vendedor-fila"
            :class="{ 'rdc-vendedor-fila--activa': seleccionado === u.IdUsuarioAsignado }"
            @click="seleccionar(u.IdUsuarioAsignado)"
          >
            <div class="rdc-vendedor-nombre">
              <i v-if="seleccionado === u.IdUsuarioAsignado" class="fas fa-check-circle rdc-check-activo"></i>
              {{ u.NombreUsuario }}
            </div>
            <div class="rdc-vendedor-meta">
              <span class="rdc-badge">{{ u.CantidadClientes }} cliente{{ u.CantidadClientes === 1 ? '' : 's' }}</span>
              <strong class="rdc-vendedor-total">{{ formatoMoneda(u.TotalSaldo) }}</strong>
            </div>
            <div class="rdc-mini-barra-fondo">
              <div
                class="rdc-mini-barra-relleno"
                :style="{ width: porcentajeBarraVendedor(u.TotalSaldo) + '%' }"
              ></div>
            </div>
          </button>
        </div>
      </div>

      <!-- PANEL DERECHO: clientes del vendedor seleccionado -->
      <div class="rdc-panel rdc-panel-clientes">
        <div class="rdc-panel-header d-flex align-items-center justify-content-between flex-wrap" style="gap:.6rem">
          <span>
            <i class="fas fa-user-friends mr-2"></i>
            <template v-if="vendedorSeleccionado">
              {{ esVistaIndividual ? 'Mi cartera de clientes' : vendedorSeleccionado.NombreUsuario }}
            </template>
            <template v-else>Selecciona un vendedor</template>
          </span>

          <div class="d-flex align-items-center flex-wrap" style="gap:.6rem" v-if="vendedorSeleccionado">
            <b-form-select
              v-model="ordenCliente"
              size="sm"
              class="rdc-control rdc-select-orden"
              :options="opcionesOrden"
            />
            <label class="rdc-check-vencidos" v-if="hayInfoVencidoGlobal">
              <input type="checkbox" v-model="soloVencidos" />
              Solo vencidos
            </label>
            <div class="rdc-buscador">
              <b-input-group size="sm">
                <b-form-input
                  v-model="filtroCliente"
                  placeholder="Buscar cliente o DNI/RUC..."
                  class="rdc-control"
                />
                <b-input-group-append>
                  <b-button class="rdc-btn-limpiar" :disabled="!filtroCliente" @click="filtroCliente = ''">✕</b-button>
                </b-input-group-append>
              </b-input-group>
            </div>
          </div>
        </div>

        <!-- resumen de totales del vendedor seleccionado (vencido / por vencer / vigente) -->
        <div class="rdc-resumen-panel" v-if="vendedorSeleccionado && clientesFiltrados.length">
          <div class="rdc-chip rdc-chip--grande rdc-chip--total" v-if="hayInfoTotalGlobal">
            <i class="fas fa-file-invoice mr-1"></i>
            Total documentos: {{ formatoMoneda(totalTotalVendedorSeleccionado) }}
          </div>
          <div class="rdc-chip rdc-chip--grande rdc-chip--total">
            <i class="fas fa-file-invoice-dollar mr-1"></i>
            Saldo mostrado: {{ formatoMoneda(totalVendedorSeleccionado) }}
          </div>
          <template v-if="hayInfoVencidoGlobal">
            <div class="rdc-chip rdc-chip--grande rdc-chip--vencido" v-if="totalVencidoVendedorSeleccionado > 0">
              <i class="fas fa-exclamation-circle mr-1"></i>
              Vencido: {{ formatoMoneda(totalVencidoVendedorSeleccionado) }}
            </div>
            <div class="rdc-chip rdc-chip--grande rdc-chip--porvencer" v-if="totalPorVencerVendedorSeleccionado > 0">
              <i class="fas fa-clock mr-1"></i>
              Por vencer: {{ formatoMoneda(totalPorVencerVendedorSeleccionado) }}
            </div>
            <div class="rdc-chip rdc-chip--grande rdc-chip--vigente" v-if="totalVigenteVendedorSeleccionado > 0">
              Vigente: {{ formatoMoneda(totalVigenteVendedorSeleccionado) }}
            </div>
          </template>
          <div class="rdc-chip rdc-chip--grande rdc-chip--adelanto" v-if="totalAdelantoVendedorSeleccionado > 0">
            <i class="fas fa-hand-holding-usd mr-1"></i>
            Adelanto: {{ formatoMoneda(totalAdelantoVendedorSeleccionado) }}
          </div>
        </div>

        <div v-if="cargando && esVistaIndividual" class="text-center py-5">
          <span class="spinner-border rdc-spinner" role="status" />
        </div>

        <div v-else-if="!vendedorSeleccionado" class="rdc-vacio-panel rdc-vacio-panel--grande">
          <i class="fas" :class="esVistaIndividual ? 'fa-inbox' : 'fa-hand-point-left'"></i>
          <div class="mt-2">
            {{ usuarios.length || cargando ? 'Elige un vendedor de la lista para ver sus clientes con deuda' : 'No tienes clientes con deuda pendiente' }}
          </div>
        </div>

        <div v-else-if="!clientesFiltrados.length" class="rdc-vacio-panel rdc-vacio-panel--grande">
          <i class="fas fa-inbox mb-2"></i>
          <div>Sin clientes que coincidan con la búsqueda</div>
        </div>

        <div v-else class="rdc-tabla-clientes">
          <table class="rdc-tabla">
            <thead>
              <tr>
                <th>Cliente</th>
                <th>DNI/RUC</th>
                <th class="text-center">Docs. pendientes</th>
                <th class="text-right" v-if="hayInfoTotalGlobal" title="Monto original de los documentos, antes de adelantos y notas aplicadas">
                  Total <i class="fas fa-info-circle rdc-info-icono"></i>
                </th>
                <th class="text-right" title="Saldo a favor del cliente, disponible para aplicar a futuras compras">
                  Adelanto <i class="fas fa-info-circle rdc-info-icono"></i>
                </th>
                <th class="text-right">Saldo</th>
                <th class="text-center" v-if="hayInfoVencidoGlobal">Estado</th>
                <th class="text-center">Ver</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in clientesFiltrados" :key="c.IdCliente">
                <td>{{ c.Cliente }}</td>
                <td>{{ c.DniRuc }}</td>
                <td class="text-center">{{ c.CantidadDocumentos }}</td>
                <td class="text-right" v-if="hayInfoTotalGlobal">{{ formatoMoneda(c.Total) }}</td>
                <td class="text-right" :class="{ 'rdc-adelanto': c.Adelanto > 0 }">
                  {{ c.Adelanto > 0 ? formatoMoneda(c.Adelanto) : '—' }}
                </td>
                <td class="text-right rdc-saldo">{{ formatoMoneda(c.Saldo) }}</td>
                <td class="text-center" v-if="hayInfoVencidoGlobal">
                  <span v-if="Number(c.SaldoVencido) > 0" class="rdc-chip rdc-chip--vencido" :title="'Vencido: ' + formatoMoneda(c.SaldoVencido)">
                    <i class="fas fa-exclamation-circle mr-1"></i>Vencido
                  </span>
                  <span v-else class="rdc-chip rdc-chip--vigente">Al día</span>
                </td>
                <td class="text-center">
                  <button
                    v-if="c.CantidadDocumentos > 0"
                    type="button"
                    class="rdc-btn-ojo"
                    title="Ver documentos de esta deuda"
                    @click="verDetalleDeuda(c)"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                </td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="rdc-fila-total">
                <td colspan="3" class="text-right">
                  Total {{ esVistaIndividual ? 'de mi cartera' : vendedorSeleccionado.NombreUsuario }}:
                </td>
                <td class="text-right" v-if="hayInfoTotalGlobal">{{ formatoMoneda(totalTotalVendedorSeleccionado) }}</td>
                <td class="text-right rdc-adelanto">{{ formatoMoneda(totalAdelantoVendedorSeleccionado) }}</td>
                <td class="text-right">{{ formatoMoneda(totalVendedorSeleccionado) }}</td>
                <td v-if="hayInfoVencidoGlobal"></td>
                <td></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <b-modal
      v-model="modalDetalleVisible"
      id="modalDetalleDeuda"
      size="lg"
      scrollable
      hide-footer
      body-class="rdc-modal-body"
    >
      <template slot="modal-title">
        <i class="fas fa-file-invoice-dollar mr-2"></i>Documentos de la deuda
      </template>

      <p class="rdc-modal-cliente" v-if="clienteDetalleActual">
        <strong>{{ clienteDetalleActual.Cliente }}</strong>
        <span class="rdc-modal-dniruc">{{ clienteDetalleActual.DniRuc }}</span>
      </p>

      <div v-if="cargandoDetalle" class="text-center py-4">
        <span class="spinner-border rdc-spinner" role="status" />
      </div>

      <div v-else-if="!documentosDetalle.length" class="rdc-vacio-panel">
        <i class="fas fa-inbox mb-2"></i>
        <div>Este cliente no tiene documentos con saldo pendiente</div>
      </div>

      <template v-else>
        <div class="rdc-resumen-modal">
          <div class="rdc-chip rdc-chip--vencido rdc-chip--grande" v-if="totalSaldoVencidoModal > 0">
            <i class="fas fa-exclamation-circle mr-1"></i>
            Vencido: {{ formatoMoneda(totalSaldoVencidoModal) }}
          </div>
          <div class="rdc-chip rdc-chip--porvencer rdc-chip--grande" v-if="totalSaldoPorVencerModal > 0">
            <i class="fas fa-clock mr-1"></i>
            Por vencer (≤7 días): {{ formatoMoneda(totalSaldoPorVencerModal) }}
          </div>
          <div class="rdc-chip rdc-chip--vigente rdc-chip--grande" v-if="totalSaldoVigenteModal > 0">
            Vigente: {{ formatoMoneda(totalSaldoVigenteModal) }}
          </div>
        </div>

        <div class="rdc-tabla-clientes">
          <table class="rdc-tabla">
            <thead>
              <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>FechaVen</th>
                <th class="text-center">Estado</th>
                <th>Tipo</th>
                <th>Correlativo</th>
                <th class="text-right">Total</th>
                <th class="text-right">Adelanto</th>
                <th class="text-right" title="Monto aplicado a este documento desde una nota de crédito">
                  Nota Aplicada <i class="fas fa-info-circle rdc-info-icono"></i>
                </th>
                <th class="text-right">Saldo</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="d in documentosDetalle"
                :key="d.IdDocVenta"
                :class="filaClaseEstado(d.FechaCredito)"
              >
                <td class="text-center">{{ d.IdDocVenta }}</td>
                <td>{{ d.FechaDoc }}</td>
                <td>{{ d.FechaCredito }}</td>
                <td class="text-center">
                  <span class="rdc-chip" :class="'rdc-chip--' + estadoDocumento(d.FechaCredito).tipo">
                    {{ estadoDocumento(d.FechaCredito).texto }}
                  </span>
                </td>
                <td>{{ d.TipoTexto }}</td>
                <td>{{ d.Correlativo }}</td>
                <td class="text-right">{{ formatoMoneda(d.Total) }}</td>
                <td class="text-right">{{ formatoMoneda(d.Aplicado) }}</td>
                <td class="text-right">{{ formatoMoneda(d.NotaCreApli) }}</td>
                <td class="text-right rdc-saldo">{{ formatoMoneda(d.Saldo) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="rdc-fila-total">
                <td colspan="9" class="text-right">Total pendiente:</td>
                <td class="text-right rdc-saldo">{{ formatoMoneda(totalSaldoDetalleModal) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </template>
    </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  name: 'ReporteDeudaClientesUsuario',

  data () {
    return {
      API: config.API,
      cargando: false,
      usuarios: [],
      seleccionado: null,
      filtroCliente: '',
      idPerfil: Number(sessionStorage.getItem('IdPerfil')),
      usuarioLogueado: sessionStorage.getItem('User'),
      exportando: '',
      modalDetalleVisible: false,
      cargandoDetalle: false,
      clienteDetalleActual: null,
      documentosDetalle: [],

      fechaGeneracion: '',

      ordenCliente: 'saldo_desc',
      opcionesOrden: [
        { value: 'saldo_desc', text: 'Ordenar: mayor saldo' },
        { value: 'saldo_asc', text: 'Ordenar: menor saldo' },
        { value: 'nombre_asc', text: 'Ordenar: nombre A-Z' }
      ],
      soloVencidos: false
    }
  },

  computed: {
    esVistaIndividual () {
    return this.idPerfil === 3
    },
    vendedorSeleccionado () {
      return this.usuarios.find(u => u.IdUsuarioAsignado === this.seleccionado) || null
    },

    hayInfoVencidoGlobal () {
      return this.usuarios.some(u =>
        Array.isArray(u.clientes) && u.clientes.some(c => c.SaldoVencido !== undefined)
      )
    },

    clientesFiltrados () {
      if (!this.vendedorSeleccionado) return []
      const texto = this.filtroCliente.trim().toLowerCase()
      let lista = this.vendedorSeleccionado.clientes || []

      if (texto) {
        lista = lista.filter(c =>
          (c.Cliente || '').toLowerCase().includes(texto) ||
          (c.DniRuc || '').toLowerCase().includes(texto)
        )
      }

      if (this.soloVencidos) {
        lista = lista.filter(c => Number(c.SaldoVencido) > 0)
      }

      lista = [...lista].sort((a, b) => {
        if (this.ordenCliente === 'saldo_asc') return (Number(a.Saldo) || 0) - (Number(b.Saldo) || 0)
        if (this.ordenCliente === 'nombre_asc') return (a.Cliente || '').localeCompare(b.Cliente || '')
        return (Number(b.Saldo) || 0) - (Number(a.Saldo) || 0)
      })

      return lista
    },

    totalVendedorSeleccionado () {
      return this.clientesFiltrados.reduce((acc, c) => acc + (Number(c.Saldo) || 0), 0)
    },
    totalAdelantoVendedorSeleccionado () {
      return this.clientesFiltrados.reduce((acc, c) => acc + (Number(c.Adelanto) || 0), 0)
    },

    hayInfoTotalGlobal () {
      return this.usuarios.some(u =>
        Array.isArray(u.clientes) && u.clientes.some(c => c.Total !== undefined)
      )
    },
    totalTotalVendedorSeleccionado () {
      return this.clientesFiltrados.reduce((acc, c) => acc + (Number(c.Total) || 0), 0)
    },

    totalVencidoVendedorSeleccionado () {
      return this.clientesFiltrados.reduce((acc, c) => acc + (Number(c.SaldoVencido) || 0), 0)
    },
    totalPorVencerVendedorSeleccionado () {
      return this.clientesFiltrados.reduce((acc, c) => acc + (Number(c.SaldoPorVencer) || 0), 0)
    },
    totalVigenteVendedorSeleccionado () {
      const total = this.totalVendedorSeleccionado
      const vencido = this.totalVencidoVendedorSeleccionado
      const porVencer = this.totalPorVencerVendedorSeleccionado
      return Math.max(0, total - vencido - porVencer)
    },

    totalClientes () {
      return this.usuarios.reduce((acc, u) => acc + u.CantidadClientes, 0)
    },
    totalGeneral () {
      return this.usuarios.reduce((acc, u) => acc + (Number(u.TotalSaldo) || 0), 0)
    },
    totalAdelantoGeneral () {
      return this.usuarios.reduce((acc, u) => acc + (Number(u.TotalAdelanto) || 0), 0)
    },
    totalVencidoGeneral () {
      return this.usuarios.reduce((acc, u) => {
        const clientes = Array.isArray(u.clientes) ? u.clientes : []
        return acc + clientes.reduce((a, c) => a + (Number(c.SaldoVencido) || 0), 0)
      }, 0)
    },

    totalSaldoDetalleModal () {
      return this.documentosDetalle.reduce((acc, d) => acc + (Number(d.Saldo) || 0), 0)
    },
    totalSaldoVencidoModal () {
      return this.documentosDetalle
        .filter(d => this.estadoDocumento(d.FechaCredito).tipo === 'vencido')
        .reduce((acc, d) => acc + (Number(d.Saldo) || 0), 0)
    },
    totalSaldoPorVencerModal () {
      return this.documentosDetalle
        .filter(d => this.estadoDocumento(d.FechaCredito).tipo === 'porvencer')
        .reduce((acc, d) => acc + (Number(d.Saldo) || 0), 0)
    },
    totalSaldoVigenteModal () {
      return this.documentosDetalle
        .filter(d => this.estadoDocumento(d.FechaCredito).tipo === 'vigente')
        .reduce((acc, d) => acc + (Number(d.Saldo) || 0), 0)
    }
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

    async exportarExcel () {
        this.exportando = 'excel'
        try {
            const { data } = await this.axios.get(this.endpoint('reporte/clientes-deuda-por-usuario/excel'), {
            params: { usuarioReg: this.usuarioLogueado }
            })
            let url = String(data).replace(/\\/g, '')
            if (!url.startsWith('http://') && !url.startsWith('https://')) {
            url = 'http://' + url
            }
            window.open(url, '_blank')
        } catch (e) {
            console.error(e)
            if (this.$toasted && typeof this.$toasted.show === 'function') {
            this.$toasted.show('No se pudo exportar el Excel', { type: 'error' })
            }
        } finally {
            this.exportando = ''
        }
    },

    exportarPdf () {
        const qs = new URLSearchParams({ usuarioReg: this.usuarioLogueado }).toString()
        window.open(`${this.endpoint('reporte/clientes-deuda-por-usuario/pdf')}?${qs}`, '_blank')
    },
    async cargar () {
      this.cargando = true
      try {
        const { data } = await this.axios.get(this.endpoint('reporte/clientes-deuda-por-usuario'), {
            params: { usuarioReg: this.usuarioLogueado }
        })
        this.usuarios = Array.isArray(data) ? data : []
        if (!this.usuarios.some(u => u.IdUsuarioAsignado === this.seleccionado)) {
          this.seleccionado = this.usuarios.length ? this.usuarios[0].IdUsuarioAsignado : null
        }
        this.fechaGeneracion = new Date().toLocaleString('es-PE', {
          day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
        })
      } catch (e) {
        console.error(e)
        this.usuarios = []
        this.seleccionado = null
        if (this.$toasted && typeof this.$toasted.show === 'function') {
          this.$toasted.show('No se pudo cargar la deuda de clientes', { type: 'error' })
        }
      } finally {
        this.cargando = false
      }
    },

    seleccionar (idUsuarioAsignado) {
      this.seleccionado = idUsuarioAsignado
      this.filtroCliente = ''
    },

    async verDetalleDeuda (cliente) {
      this.clienteDetalleActual = cliente
      this.modalDetalleVisible = true
      this.cargandoDetalle = true
      this.documentosDetalle = []
      try {
        const { data } = await this.axios.get(this.endpoint('cliente/deudas'), {
          params: { idCliente: cliente.IdCliente }
        })
        this.documentosDetalle = (Array.isArray(data) ? data : []).map(d => ({
          ...d,
          TipoTexto: this.textoTipoDoc(d.IdTipoDoc)
        }))
      } catch (e) {
        console.error(e)
        if (this.$toasted && typeof this.$toasted.show === 'function') {
          this.$toasted.show('No se pudo cargar el detalle de la deuda', { type: 'error' })
        }
      } finally {
        this.cargandoDetalle = false
      }
    },

    textoTipoDoc (idTipoDoc) {
      if (Number(idTipoDoc) === 6) return 'FACTURA ELEC.'
      if (Number(idTipoDoc) === 7) return 'BOLETA ELEC.'
      return 'OTRO'
    },

    estadoDocumento (fechaVen) {
      if (!fechaVen) return { tipo: 'vigente', texto: 'Vigente' }
      const hoy = new Date()
      hoy.setHours(0, 0, 0, 0)
      const fv = new Date(fechaVen)
      fv.setHours(0, 0, 0, 0)
      const diffDias = Math.round((fv - hoy) / 86400000)

      if (diffDias < 0) return { tipo: 'vencido', texto: 'Vencido', dias: Math.abs(diffDias) }
      if (diffDias <= 7) return { tipo: 'porvencer', texto: 'Por vencer', dias: diffDias }
      return { tipo: 'vigente', texto: 'Vigente', dias: diffDias }
    },

    filaClaseEstado (fechaVen) {
      const tipo = this.estadoDocumento(fechaVen).tipo
      if (tipo === 'vencido') return 'rdc-fila-vencida'
      if (tipo === 'porvencer') return 'rdc-fila-porvencer'
      return ''
    },

    porcentajeBarraVendedor (totalSaldo) {
      const maximo = Math.max(...this.usuarios.map(u => Number(u.TotalSaldo) || 0), 1)
      return Math.max(4, Math.round(((Number(totalSaldo) || 0) / maximo) * 100))
    },

    abrirCobro (cliente) {
      this.$emit('cobrar-cliente', cliente.IdCliente)
    },

    formatoMoneda (valor) {
      return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(Number(valor) || 0)
    }
  }
}
</script>

<style scoped>
.rdc-vista {
  padding: 1.5rem;
  background: #f8faf9;
  min-height: 100%;
}

.rdc-cabecera {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 1rem;
  margin-bottom: 1.25rem;
}
.rdc-titulo {
  font-weight: 700;
  color: #14231f;
  margin-bottom: 0.15rem;
}
.rdc-subtitulo {
  color: #566762;
  font-size: 0.9rem;
}
.rdc-fecha-corte {
  color: #8a9591;
  font-size: 0.78rem;
  margin-top: 0.3rem;
}

.rdc-resumen {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.rdc-tarjeta {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  background: #fff;
  border: 1px solid #e1e7e5;
  border-radius: 14px;
  padding: 1rem 1.2rem;
}
.rdc-tarjeta--alerta {
  border-color: #f3c6bd;
  background: #fff8f6;
}
.rdc-tarjeta-icono {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.8rem;
  height: 2.8rem;
  border-radius: 12px;
  font-size: 1.15rem;
  color: #fff;
  flex-shrink: 0;
}
.rdc-tarjeta-icono--azul { background: #21a5a3; }
.rdc-tarjeta-icono--naranja { background: #e08e3e; }
.rdc-tarjeta-icono--rojo { background: #c0392b; }
.rdc-tarjeta-icono--verde { background: #2f8f5b; }
.rdc-tarjeta-icono--vencido { background: #a3311f; }
.rdc-tarjeta-valor {
  font-size: 1.35rem;
  font-weight: 700;
  color: #14231f;
  line-height: 1.1;
}
.rdc-tarjeta-label {
  font-size: 0.8rem;
  color: #566762;
}
.rdc-label-hint {
  color: #8a9591;
  font-size: 0.72rem;
}

.rdc-cuerpo {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 1.25rem;
  align-items: start;
}
.rdc-cuerpo--single { grid-template-columns: 1fr; }
@media (max-width: 900px) {
  .rdc-cuerpo { grid-template-columns: 1fr; }
}

.rdc-panel {
  background: #fff;
  border: 1px solid #e1e7e5;
  border-radius: 14px;
  overflow: hidden;
}
.rdc-panel-header {
  padding: 0.9rem 1.1rem;
  font-weight: 600;
  color: #0c3a3b;
  background: #e2f0ee;
  border-bottom: 1px solid #c4dcd8;
  font-size: 0.92rem;
}

.rdc-panel-vendedores { min-height: 420px; }
.rdc-lista-vendedores {
  max-height: 640px;
  overflow-y: auto;
}
.rdc-vendedor-fila {
  width: 100%;
  display: block;
  text-align: left;
  padding: 0.8rem 1.1rem;
  border: none;
  border-bottom: 1px solid #edf1ef;
  background: #fff;
  cursor: pointer;
  transition: background-color 0.12s ease;
}
.rdc-vendedor-fila:hover { background: #f2f8f7; }
.rdc-vendedor-fila--activa {
  background: #e2f0ee;
  box-shadow: inset 4px 0 0 #21a5a3;
}
.rdc-vendedor-fila--activa .rdc-vendedor-nombre,
.rdc-vendedor-fila--activa .rdc-vendedor-total {
  font-weight: 700;
  color: #0c3a3b;
}
.rdc-check-activo {
  color: #21a5a3;
  margin-right: 0.35rem;
  font-size: 0.85rem;
}
.rdc-vendedor-nombre {
  font-weight: 600;
  color: #14231f;
  font-size: 0.9rem;
  margin-bottom: 0.3rem;
}
.rdc-vendedor-meta {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.rdc-badge {
  font-size: 0.72rem;
  color: #566762;
  background: #f0f3f2;
  padding: 0.1rem 0.5rem;
  border-radius: 999px;
}
.rdc-vendedor-total {
  color: #0c3a3b;
  font-size: 0.88rem;
  font-variant-numeric: tabular-nums;
}

.rdc-mini-barra-fondo {
  margin-top: 0.5rem;
  height: 5px;
  border-radius: 999px;
  background: #edf1ef;
  overflow: hidden;
}
.rdc-mini-barra-relleno {
  height: 100%;
  border-radius: 999px;
  background: #21a5a3;
  transition: width 0.2s ease;
}

.rdc-panel-clientes { min-height: 420px; }
.rdc-buscador { width: 220px; }
.rdc-select-orden {
  width: 190px;
  height: 2.1rem;
  font-size: 0.82rem;
  border: 1px solid #e1e7e5;
  border-radius: 8px;
}
.rdc-check-vencidos {
  display: flex;
  align-items: center;
  gap: 0.35rem;
  font-size: 0.82rem;
  color: #a3311f;
  font-weight: 600;
  margin: 0;
  white-space: nowrap;
}
.rdc-control {
  height: 2.1rem;
  border: 1px solid #e1e7e5;
  border-radius: 8px 0 0 8px;
  font-size: 0.85rem;
}
.rdc-btn-limpiar {
  height: 2.1rem;
  border: 1px solid #e1e7e5;
  border-left: none;
  background: #f8faf9;
  color: #566762;
  border-radius: 0 8px 8px 0;
}

.rdc-resumen-panel {
  display: flex;
  flex-wrap: wrap;
  gap: 0.55rem;
  padding: 0.9rem 1.1rem;
  background: #fafcfb;
  border-bottom: 1px solid #edf1ef;
}
.rdc-chip--total { background: #eef2f4; color: #14231f; }
.rdc-chip--adelanto { background: #e3f4ea; color: #2f8f5b; }

.rdc-vacio-panel {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2.5rem 1rem;
  color: #8a9591;
  font-size: 0.9rem;
}
.rdc-vacio-panel i { font-size: 1.6rem; color: #cdd6d3; }
.rdc-vacio-panel--grande { padding: 5rem 1rem; }

.rdc-tabla-clientes { overflow-x: auto; }
.rdc-tabla {
  width: 100%;
  border-collapse: collapse;
}
.rdc-tabla thead th {
  padding: 0.7rem 0.9rem;
  background-color: #f8faf9;
  color: #566762;
  font-size: 0.78rem;
  font-weight: 600;
  text-align: left;
  border-bottom: 1px solid #e1e7e5;
  white-space: nowrap;
}
.rdc-info-icono {
  color: #b9c3bf;
  font-size: 0.72rem;
  cursor: help;
}
.rdc-tabla tbody td {
  padding: 0.65rem 0.9rem;
  font-size: 0.86rem;
  color: #14231f;
  border-bottom: 1px solid #edf1ef;
  vertical-align: middle;
}
.rdc-tabla tbody tr:hover { background-color: #f2f8f7; }
.rdc-saldo { color: #a3311f; font-weight: 600; font-variant-numeric: tabular-nums; }
.rdc-adelanto { color: #2f8f5b; font-weight: 600; font-variant-numeric: tabular-nums; }
.rdc-fila-total td {
  padding: 0.75rem 0.9rem;
  font-weight: 700;
  border-top: 2px solid #d5ddda;
  background: #f8faf9;
  color: #0c3a3b;
}
.text-right { text-align: right; }
.text-center { text-align: center; }

.rdc-fila-vencida { background: #fdf1ef; }
.rdc-fila-vencida:hover { background: #fbe4df !important; }
.rdc-fila-porvencer { background: #fef8ec; }
.rdc-fila-porvencer:hover { background: #fdf1d6 !important; }

.rdc-chip {
  display: inline-flex;
  align-items: center;
  padding: 0.15rem 0.55rem;
  border-radius: 999px;
  font-size: 0.72rem;
  font-weight: 700;
  white-space: nowrap;
}
.rdc-chip--vencido { background: #fbe4df; color: #a3311f; }
.rdc-chip--porvencer { background: #fdf1d6; color: #8a6414; }
.rdc-chip--vigente { background: #e3f4ea; color: #2f8f5b; }
.rdc-chip--grande { padding: 0.4rem 0.85rem; font-size: 0.82rem; }
.rdc-resumen-modal {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
  margin-bottom: 1rem;
}

.rdc-btn-ojo {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 2rem;
  height: 2rem;
  border-radius: 8px;
  border: 1px solid #cdeadd;
  background: #eafbf2;
  color: #2f8f5b;
  font-size: 0.85rem;
  cursor: pointer;
  transition: background-color 0.12s ease;
}
.rdc-btn-ojo:hover { background: #d7f4e5; }

.rdc-modal-cliente {
  font-size: 0.9rem;
  color: #14231f;
  margin-bottom: 0.9rem;
}
.rdc-modal-dniruc {
  color: #566762;
  margin-left: 0.5rem;
}
.rdc-modal-hint {
  font-size: 0.78rem;
  color: #8a9591;
  margin-top: 0.2rem;
}

.rdc-btn {
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.85rem;
  display: inline-flex;
  align-items: center;
}
.rdc-btn-primary {
  background: #21a5a3;
  border: 1px solid #21a5a3;
  color: #fff;
}
.rdc-btn-primary:hover { background: #0c3a3b; border-color: #0c3a3b; color: #fff; }
.rdc-btn-outline {
  height: 2.5rem;
  background: #fff;
  border: 1px solid #e1e7e5;
  color: #14231f;
}
.rdc-btn-outline:hover { background: #f8faf9; border-color: #cdd6d3; }

.rdc-spinner { color: #21a5a3; }
</style>

<!--
  Estilo SIN scope, a propósito: el <b-modal> de BootstrapVue no se renderiza dentro
  del árbol con el atributo de scope de este componente, así que un selector scoped
  (o ">>>"/"::v-deep" en un bloque scoped) no lo alcanzaría de forma confiable en
  esta versión de vue-loader. Se apunta directo por el "id" que le dimos al modal.
-->
<style>
#modalDetalleDeuda .modal-dialog {
  max-width: 1140px !important;
  width: 92vw !important;
}
@media (min-width: 1300px) {
  #modalDetalleDeuda .modal-dialog {
    max-width: 1240px !important;
  }
}
#modalDetalleDeuda .modal-content {
  max-height: 90vh;
}
#modalDetalleDeuda .modal-body.rdc-modal-body {
  max-height: calc(90vh - 130px);
  overflow-y: auto;
}
</style>