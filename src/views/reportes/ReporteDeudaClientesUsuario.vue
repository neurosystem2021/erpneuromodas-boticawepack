<template>
  <div class="rdc-vista">
    <!-- ===================== CABECERA DEL MÓDULO ===================== -->
    <div class="rdc-cabecera">
      <div>
        <h4 class="rdc-titulo"><i class="fas fa-users mr-2"></i>Deuda de Clientes por Vendedor</h4>
        <p class="rdc-subtitulo mb-0">
          {{ esVistaIndividual ? 'Clientes con saldo pendiente de tu cartera' : 'Clientes con saldo pendiente, agrupados por el vendedor asignado' }}
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
    </div>

    <!-- ===================== CUERPO ===================== -->
    <div class="rdc-cuerpo" :class="{ 'rdc-cuerpo--single': esVistaIndividual }">
      <!-- PANEL IZQUIERDO: lista de vendedores (oculto si solo hay uno, ej. perfil 3) -->
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
            <div class="rdc-vendedor-nombre">{{ u.NombreUsuario }}</div>
            <div class="rdc-vendedor-meta">
              <span class="rdc-badge">{{ u.CantidadClientes }} cliente{{ u.CantidadClientes === 1 ? '' : 's' }}</span>
              <strong class="rdc-vendedor-total">{{ formatoMoneda(u.TotalSaldo) }}</strong>
            </div>
          </button>
        </div>
      </div>

      <!-- PANEL DERECHO: clientes del vendedor seleccionado -->
      <div class="rdc-panel rdc-panel-clientes">
        <div class="rdc-panel-header d-flex align-items-center justify-content-between flex-wrap">
          <span>
            <i class="fas fa-user-friends mr-2"></i>
            <template v-if="vendedorSeleccionado">
              {{ esVistaIndividual ? 'Mi cartera de clientes' : vendedorSeleccionado.NombreUsuario }}
            </template>
            <template v-else>Selecciona un vendedor</template>
          </span>
          <div class="rdc-buscador" v-if="vendedorSeleccionado">
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
                <th class="text-right">Saldo</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in clientesFiltrados" :key="c.IdCliente">
                <td>{{ c.Cliente }}</td>
                <td>{{ c.DniRuc }}</td>
                <td class="text-center">{{ c.CantidadDocumentos }}</td>
                <td class="text-right rdc-saldo">{{ formatoMoneda(c.Saldo) }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr class="rdc-fila-total">
                <td colspan="3" class="text-right">
                  Total {{ esVistaIndividual ? 'de mi cartera' : vendedorSeleccionado.NombreUsuario }}:
                </td>
                <td class="text-right">{{ formatoMoneda(totalVendedorSeleccionado) }}</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
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
      exportando: ''
    }
  },

  computed: {
    esVistaIndividual () {
    return this.idPerfil === 3
    },
    vendedorSeleccionado () {
      return this.usuarios.find(u => u.IdUsuarioAsignado === this.seleccionado) || null
    },
    clientesFiltrados () {
      if (!this.vendedorSeleccionado) return []
      const texto = this.filtroCliente.trim().toLowerCase()
      if (!texto) return this.vendedorSeleccionado.clientes
      return this.vendedorSeleccionado.clientes.filter(c =>
        (c.Cliente || '').toLowerCase().includes(texto) ||
        (c.DniRuc || '').toLowerCase().includes(texto)
      )
    },
    totalVendedorSeleccionado () {
      return this.clientesFiltrados.reduce((acc, c) => acc + (Number(c.Saldo) || 0), 0)
    },
    totalClientes () {
      return this.usuarios.reduce((acc, u) => acc + u.CantidadClientes, 0)
    },
    totalGeneral () {
      return this.usuarios.reduce((acc, u) => acc + (Number(u.TotalSaldo) || 0), 0)
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
        // El filtro por perfil se aplica en el backend (perfil 3 solo recibe su cartera)
        const { data } = await this.axios.get(this.endpoint('reporte/clientes-deuda-por-usuario'), {
            params: { usuarioReg: this.usuarioLogueado }
        })
        this.usuarios = Array.isArray(data) ? data : []
        // mantiene la selección si el vendedor sigue en la lista; si no, selecciona el primero
        if (!this.usuarios.some(u => u.IdUsuarioAsignado === this.seleccionado)) {
          this.seleccionado = this.usuarios.length ? this.usuarios[0].IdUsuarioAsignado : null
        }
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

    // ---------- CONEXIÓN CON EL MODAL "Cobrar deuda cliente" ----------
    // TODO: confirmar cómo se invoca el modal real (ref, ruta, componente global, etc.)
    abrirCobro (cliente) {
      this.$emit('cobrar-cliente', cliente.IdCliente)
      // Alternativa si el modal vive en este mismo módulo:
      // this.$refs.modalCobro.abrir(cliente.IdCliente)
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

/* ===================== CABECERA ===================== */
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

/* ===================== TARJETAS DE RESUMEN ===================== */
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

/* ===================== CUERPO ===================== */
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

/* ---- panel vendedores ---- */
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
  box-shadow: inset 3px 0 0 #21a5a3;
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

/* ---- panel clientes ---- */
.rdc-panel-clientes { min-height: 420px; }
.rdc-buscador { width: 240px; }
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
.rdc-tabla tbody td {
  padding: 0.65rem 0.9rem;
  font-size: 0.86rem;
  color: #14231f;
  border-bottom: 1px solid #edf1ef;
  vertical-align: middle;
}
.rdc-tabla tbody tr:hover { background-color: #f2f8f7; }
.rdc-saldo { color: #a3311f; font-weight: 600; font-variant-numeric: tabular-nums; }
.rdc-fila-total td {
  padding: 0.75rem 0.9rem;
  font-weight: 700;
  border-top: 2px solid #d5ddda;
  background: #f8faf9;
  color: #0c3a3b;
}
.text-right { text-align: right; }
.text-center { text-align: center; }

/* ===================== BOTONES ===================== */
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