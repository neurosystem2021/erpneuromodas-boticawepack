<template>
  <div class="descarga-masiva">
    <div class="dm-header">
      <h2 class="dm-titulo">Descarga masiva de comprobantes PDF</h2>
      <p class="dm-sub">Busca por RUC/DNI del cliente, ingresa IDs manualmente, o combina ambos.</p>
    </div>

    <!-- BLOQUE 1: BUSCAR POR RUC -->
    <div class="dm-bloque">
      <h3 class="dm-bloque-titulo">1. Buscar por RUC / DNI</h3>

      <div class="dm-form-ruc">
        <div class="dm-campo">
          <label>RUC / DNI del cliente</label>
          <input
            type="text"
            v-model="ruc"
            class="dm-input"
            placeholder="Ej: 20601234567"
            @keyup.enter="buscarPorRuc"
          />
        </div>
        <div class="dm-campo">
          <label>Desde</label>
          <input type="date" v-model="fechaInicio" class="dm-input" />
        </div>
        <div class="dm-campo">
          <label>Hasta</label>
          <input type="date" v-model="fechaFin" class="dm-input" />
        </div>
        <div class="dm-campo dm-campo-check">
          <label class="dm-check-label">
            <input type="checkbox" v-model="soloValidos" />
            Excluir anulados
          </label>
        </div>
        <div class="dm-campo dm-campo-btn">
          <button class="btn-primary" :disabled="buscando" @click="buscarPorRuc">
            {{ buscando ? 'Buscando...' : 'Buscar' }}
          </button>
        </div>
      </div>

      <p v-if="errorBusqueda" class="dm-error">⚠️ {{ errorBusqueda }}</p>

      <div v-if="ventas.length" class="dm-resultados">
        <div class="dm-resultados-header">
          <span class="dm-resultados-count">{{ ventas.length }} comprobante(s) encontrado(s)</span>
          <div class="dm-resultados-acciones">
            <button class="btn-ghost btn-sm" @click="toggleTodos(true)">Marcar todos</button>
            <button class="btn-ghost btn-sm" @click="toggleTodos(false)">Desmarcar todos</button>
          </div>
        </div>

        <div class="dm-tabla-wrap">
          <table class="dm-tabla">
            <thead>
              <tr>
                <th class="th-check"></th>
                <th>Comprobante</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Total</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="v in ventas" :key="v.idDocVenta" :class="{ 'fila-anulada': v.Anulado == 1 }">
                <td class="th-check">
                  <input type="checkbox" v-model="seleccion[v.idDocVenta]" />
                </td>
                <td>{{ v.Serie }}-{{ v.Numero }}</td>
                <td>{{ formatearFecha(v.FechaDoc) }}</td>
                <td>{{ v.Cliente }}</td>
                <td>{{ v.Total }}</td>
                <td>
                  <span class="badge-estado" :class="v.Anulado == 1 ? 'badge-anulado' : 'badge-ok'">
                    {{ v.Anulado == 1 ? 'Anulado' : 'Válido' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <p v-else-if="buscoAlMenosUnaVez && !buscando" class="dm-vacio">
        No se encontraron comprobantes para ese RUC/DNI con los filtros indicados.
      </p>
    </div>

    <!-- BLOQUE 2: IDs MANUALES -->
    <div class="dm-bloque">
      <h3 class="dm-bloque-titulo">2. O ingresa IDs de venta manualmente</h3>
      <p class="dm-bloque-hint">
        Sepáralos por coma, espacio o salto de línea. Ej: <code>1023, 1024, 1030</code>
      </p>
      <textarea
        v-model="idsManualTexto"
        class="dm-textarea"
        rows="3"
        placeholder="1023, 1024, 1030..."
      ></textarea>
      <p v-if="idsManualArray.length" class="dm-hint-conteo">
        {{ idsManualArray.length }} ID(s) detectado(s) manualmente
      </p>
    </div>

    <!-- BLOQUE 3: RESUMEN Y DESCARGA -->
    <div class="dm-bloque dm-resumen-final">
      <div class="dm-resumen-info">
        <span class="dm-resumen-num">{{ totalSeleccionados }}</span>
        <span class="dm-resumen-label">comprobante(s) listos para descargar</span>
      </div>

      <button class="btn-primary btn-lg" :disabled="!totalSeleccionados || descargando" @click="descargar">
        {{ descargando ? 'Generando ZIP...' : '⬇️ Descargar ZIP' }}
      </button>
    </div>

    <p v-if="errorMsg" class="dm-error">⚠️ {{ errorMsg }}</p>
    <p v-if="advertenciaFallidos" class="dm-advertencia">
      ⚠️ No se pudieron generar {{ fallidosCount }} comprobante(s) (ID: {{ advertenciaFallidos }}). Revisa que tengan Hash de SUNAT válido.
    </p>
  </div>
</template>

<script>
export default {
  name: 'DescargaMasivaPdfs',

  data() {
    return {
      // Búsqueda por RUC
      ruc: '',
      fechaInicio: '',
      fechaFin: '',
      soloValidos: true,
      buscando: false,
      buscoAlMenosUnaVez: false,
      errorBusqueda: '',
      ventas: [],
      seleccion: {},

      // IDs manuales
      idsManualTexto: '',

      // Descarga
      descargando: false,
      errorMsg: '',
      advertenciaFallidos: '',
    }
  },

  computed: {
    idsManualArray() {
      return this.idsManualTexto
        .split(/[\s,;]+/)
        .map(function(s) { return s.trim() })
        .filter(function(s) { return s !== '' && !isNaN(s) })
        .map(function(s) { return parseInt(s, 10) })
    },

    idsSeleccionadosRuc() {
      var self = this
      return this.ventas
        .filter(function(v) { return self.seleccion[v.idDocVenta] })
        .map(function(v) { return v.idDocVenta })
    },

    idsFinal() {
      var combinados = this.idsSeleccionadosRuc.concat(this.idsManualArray)
      return combinados.filter(function(id, i) { return combinados.indexOf(id) === i })
    },

    totalSeleccionados() {
      return this.idsFinal.length
    },

    fallidosCount() {
      if (!this.advertenciaFallidos) return 0
      return this.advertenciaFallidos.split(',').length
    },
  },

  methods: {
    formatearFecha(f) {
      if (!f) return '—'
      var d = new Date(f)
      if (isNaN(d.getTime())) return f
      var y = d.getFullYear()
      var m = ('0' + (d.getMonth() + 1)).slice(-2)
      var day = ('0' + d.getDate()).slice(-2)
      return y + '-' + m + '-' + day
    },

    async buscarPorRuc() {
      if (!this.ruc.trim()) {
        this.errorBusqueda = 'Ingresa un RUC o DNI.'
        return
      }
      this.errorBusqueda = ''
      this.buscando = true
      this.buscoAlMenosUnaVez = true
      this.ventas = []

      try {
        var params = new URLSearchParams()
        if (this.fechaInicio) params.append('fechaInicio', this.fechaInicio)
        if (this.fechaFin) params.append('fechaFin', this.fechaFin)
        params.append('soloValidos', this.soloValidos ? '1' : '0')

        var resp = await fetch('/api/index.php/ventas/buscar-por-ruc/' + encodeURIComponent(this.ruc.trim()) + '?' + params.toString())
        var data = await resp.json()

        if (!data.success) throw new Error(data.mensaje || 'Error al buscar comprobantes.')

        this.ventas = data.ventas

        var sel = {}
        this.ventas.forEach(function(v) { sel[v.idDocVenta] = true })
        this.seleccion = sel
      } catch (err) {
        this.errorBusqueda = err.message || 'Ocurrió un error al buscar.'
      } finally {
        this.buscando = false
      }
    },

    toggleTodos(valor) {
      var self = this
      this.ventas.forEach(function(v) {
        self.$set ? self.$set(self.seleccion, v.idDocVenta, valor) : (self.seleccion[v.idDocVenta] = valor)
      })
    },

    async descargar() {
      this.errorMsg = ''
      this.advertenciaFallidos = ''

      if (!this.idsFinal.length) {
        this.errorMsg = 'Selecciona al menos un comprobante o ingresa un ID manualmente.'
        return
      }

      this.descargando = true

      try {
        var body = new URLSearchParams()
        this.idsFinal.forEach(function(id) { body.append('ids[]', id) })

        var resp = await fetch('/api/index.php/pdfs/descargar-masivo', {
            method: 'POST',
            body: body,
        })

        var contentType = resp.headers.get('Content-Type') || ''

        if (!resp.ok || contentType.indexOf('json') !== -1) {
          var dataErr = await resp.json()
          throw new Error(dataErr.mensaje || 'No se pudo generar la descarga.')
        }

        var fallidos = resp.headers.get('X-Comprobantes-Fallidos')
        if (fallidos) this.advertenciaFallidos = fallidos

        var disposition = resp.headers.get('Content-Disposition') || ''
        var nombreArchivo = 'Comprobantes.zip'
        var match = disposition.match(/filename="?([^"]+)"?/)
        if (match) nombreArchivo = match[1]

        var blob = await resp.blob()
        var url = window.URL.createObjectURL(blob)
        var a = document.createElement('a')
        a.href = url
        a.download = nombreArchivo
        document.body.appendChild(a)
        a.click()
        a.remove()
        window.URL.revokeObjectURL(url)
      } catch (err) {
        this.errorMsg = err.message || 'Ocurrió un error al descargar.'
      } finally {
        this.descargando = false
      }
    },
  },
}
</script>

<style scoped>
.descarga-masiva {
  max-width: 1100px;
  margin: 0 auto;
  padding: 32px 20px 60px;
  font-family: system-ui, sans-serif;
}

.dm-header { margin-bottom: 24px; }
.dm-titulo { font-size: 1.3rem; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.dm-sub { color: #64748b; margin: 0; font-size: 0.88rem; }

.dm-bloque {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 18px;
}
.dm-bloque-titulo { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 14px; }
.dm-bloque-hint { font-size: 0.8rem; color: #64748b; margin: 0 0 8px; }
.dm-bloque-hint code { background: #f1f5f9; padding: 2px 6px; border-radius: 4px; }

/* Form RUC */
.dm-form-ruc {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr auto auto;
  gap: 12px;
  align-items: end;
}
@media (max-width: 800px) {
  .dm-form-ruc { grid-template-columns: 1fr 1fr; }
}
.dm-campo label { display: block; font-size: 0.78rem; color: #64748b; font-weight: 600; margin-bottom: 5px; }
.dm-input {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 8px 10px;
  font-size: 0.88rem;
  box-sizing: border-box;
}
.dm-input:focus { outline: none; border-color: #2563eb; }
.dm-campo-check { display: flex; align-items: center; padding-bottom: 8px; }
.dm-check-label { display: flex; align-items: center; gap: 6px; font-size: 0.82rem; color: #334155; cursor: pointer; font-weight: 600; white-space: nowrap; }
.dm-campo-btn { padding-bottom: 0; }

.dm-error { color: #dc2626; font-size: 0.85rem; margin: 10px 0 0; }
.dm-advertencia { color: #b45309; font-size: 0.85rem; margin: 10px 0 0; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; }
.dm-vacio { color: #94a3b8; font-size: 0.85rem; margin: 14px 0 0; }

/* Resultados búsqueda */
.dm-resultados { margin-top: 18px; }
.dm-resultados-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 10px; flex-wrap: wrap; gap: 8px;
}
.dm-resultados-count { font-size: 0.85rem; color: #334155; font-weight: 600; }
.dm-resultados-acciones { display: flex; gap: 8px; }

.dm-tabla-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px; max-height: 360px; overflow-y: auto; }
.dm-tabla { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.dm-tabla th {
  background: #1e293b; color: #f8fafc; padding: 9px 12px; text-align: left;
  white-space: nowrap; position: sticky; top: 0;
}
.dm-tabla td { padding: 8px 12px; border-bottom: 1px solid #f1f5f9; color: #334155; white-space: nowrap; }
.dm-tabla tr:hover td { background: #f8fafc; }
.th-check { width: 36px; text-align: center; }
.th-check input { width: 16px; height: 16px; cursor: pointer; }
.fila-anulada td { color: #94a3b8; text-decoration: line-through; }

.badge-estado { font-size: 0.7rem; padding: 3px 9px; border-radius: 999px; font-weight: 700; }
.badge-ok { background: #dcfce7; color: #15803d; }
.badge-anulado { background: #fee2e2; color: #b91c1c; }

/* IDs manuales */
.dm-textarea {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  padding: 10px 12px;
  font-size: 0.88rem;
  font-family: system-ui, sans-serif;
  resize: vertical;
  box-sizing: border-box;
}
.dm-textarea:focus { outline: none; border-color: #2563eb; }
.dm-hint-conteo { font-size: 0.78rem; color: #2563eb; font-weight: 600; margin: 8px 0 0; }

/* Resumen final */
.dm-resumen-final {
  display: flex; justify-content: space-between; align-items: center;
  flex-wrap: wrap; gap: 14px;
  background: #eff6ff;
  border-color: #bfdbfe;
}
.dm-resumen-info { display: flex; align-items: baseline; gap: 8px; }
.dm-resumen-num { font-size: 1.8rem; font-weight: 800; color: #1e293b; }
.dm-resumen-label { font-size: 0.85rem; color: #64748b; font-weight: 600; }

/* Botones */
.btn-primary {
  background: #2563eb; color: #fff; border: none; border-radius: 8px;
  padding: 10px 22px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.btn-primary:hover:not(:disabled) { background: #1d4ed8; }
.btn-primary:disabled { background: #93c5fd; cursor: not-allowed; }
.btn-lg { padding: 12px 26px; font-size: 0.95rem; }

.btn-ghost {
  background: transparent; border: 1px solid #e2e8f0; border-radius: 8px;
  padding: 9px 18px; font-size: 0.88rem; color: #64748b; cursor: pointer;
}
.btn-ghost:hover { background: #f8fafc; color: #334155; }
.btn-sm { padding: 5px 12px; font-size: 0.78rem; }
</style>