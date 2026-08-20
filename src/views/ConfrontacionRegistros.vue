<template>
  <div class="confrontador">

    <!-- PASO 1: SUBIR AMBOS ARCHIVOS -->
    <div v-if="paso === 1" class="paso-upload">
      <div class="upload-grid">
        <div class="upload-col">
          <label class="nombre-archivo-label">
            Nombre de este origen
            <input type="text" v-model="nombreA" class="nombre-input" placeholder="Ej: SUNAT SIRE" />
          </label>
          <div
            class="drop-zone"
            :class="{ 'drag-over': arrastrandoA, 'drop-listo': archivoA }"
            @dragover.prevent="arrastrandoA = true"
            @dragleave="arrastrandoA = false"
            @drop.prevent="onDrop($event, 'A')"
            @click="$refs.fileInputA.click()"
          >
            <div class="drop-icon">{{ archivoA ? '✅' : '📄' }}</div>
            <p class="drop-titulo">{{ archivoA ? archivoA.name : 'Arrastra el primer archivo' }}</p>
            <p class="drop-sub" v-if="!archivoA">o haz clic para seleccionar</p>
            <p class="drop-hint" v-if="filasA.length">{{ filasA.length }} filas detectadas</p>
            <input ref="fileInputA" type="file" accept=".xlsx,.xls" hidden @change="onFileSelect($event, 'A')" />
          </div>
        </div>

        <div class="upload-versus">VS</div>

        <div class="upload-col">
          <label class="nombre-archivo-label">
            Nombre de este origen
            <input type="text" v-model="nombreB" class="nombre-input" placeholder="Ej: Mi Sistema" />
          </label>
          <div
            class="drop-zone"
            :class="{ 'drag-over': arrastrandoB, 'drop-listo': archivoB }"
            @dragover.prevent="arrastrandoB = true"
            @dragleave="arrastrandoB = false"
            @drop.prevent="onDrop($event, 'B')"
            @click="$refs.fileInputB.click()"
          >
            <div class="drop-icon">{{ archivoB ? '✅' : '📄' }}</div>
            <p class="drop-titulo">{{ archivoB ? archivoB.name : 'Arrastra el segundo archivo' }}</p>
            <p class="drop-sub" v-if="!archivoB">o haz clic para seleccionar</p>
            <p class="drop-hint" v-if="filasB.length">{{ filasB.length }} filas detectadas</p>
            <input ref="fileInputB" type="file" accept=".xlsx,.xls" hidden @change="onFileSelect($event, 'B')" />
          </div>
        </div>
      </div>

      <div class="upload-actions" v-if="archivoA && archivoB">
        <button class="btn-primary" @click="irAPaso2">Continuar al mapeo →</button>
      </div>
    </div>

    <!-- PASO 2: MAPEAR CAMPOS DE AMBOS ARCHIVOS -->
    <div v-if="paso === 2" class="paso-mapeo">
      <div class="mapeo-header">
        <div>
          <h2 class="mapeo-titulo">Empareja las columnas de cada archivo</h2>
          <p class="mapeo-sub">
            Marca ✔ los campos que forman la <strong>clave</strong> para saber que dos filas son "la misma venta".
          </p>
        </div>
        <button class="btn-ghost" @click="reiniciar">✕ Cambiar archivos</button>
      </div>

      <div class="mapeo-tabla-wrap">
        <table class="mapeo-tabla">
          <thead>
            <tr>
              <th>Campo</th>
              <th>Clave</th>
              <th>{{ nombreA || 'Archivo A' }}</th>
              <th>{{ nombreB || 'Archivo B' }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="campo in campos" :key="campo.key" :class="{ 'fila-clave': clave[campo.key] }">
              <td class="celda-campo">
                {{ campo.label }}
                <span v-if="campo.opcional" class="tag-opcional">opcional</span>
              </td>
              <td class="celda-clave">
                <input
                  type="checkbox"
                  v-model="clave[campo.key]"
                  :disabled="!campoMapeadoA(campo.key) || !campoMapeadoB(campo.key)"
                  :title="(!campoMapeadoA(campo.key) || !campoMapeadoB(campo.key)) ? 'Mapea el campo en ambos archivos para poder usarlo como clave' : ''"
                />
              </td>

              <!-- Columna A -->
              <td v-if="campo.key !== 'comprobante'">
                <select class="campo-select" v-model="mapeoA[campo.key]">
                  <option value="">— sin mapear —</option>
                  <option v-for="col in columnasA" :key="col" :value="col">{{ col }}</option>
                </select>
              </td>
              <td v-else class="celda-comprobante">
                <label class="check-combinar">
                  <input type="checkbox" v-model="combinarA" />
                  Combinar 2 columnas (Serie + Número)
                </label>
                <select v-if="!combinarA" class="campo-select" v-model="mapeoA.comprobante">
                  <option value="">— sin mapear —</option>
                  <option v-for="col in columnasA" :key="col" :value="col">{{ col }}</option>
                </select>
                <div v-else class="combinar-selects">
                  <select class="campo-select" v-model="serieColA">
                    <option value="">Serie...</option>
                    <option v-for="col in columnasA" :key="'sa'+col" :value="col">{{ col }}</option>
                  </select>
                  <select class="campo-select" v-model="numeroColA">
                    <option value="">Número...</option>
                    <option v-for="col in columnasA" :key="'na'+col" :value="col">{{ col }}</option>
                  </select>
                </div>
              </td>

              <!-- Columna B -->
              <td v-if="campo.key !== 'comprobante'">
                <select class="campo-select" v-model="mapeoB[campo.key]">
                  <option value="">— sin mapear —</option>
                  <option v-for="col in columnasB" :key="col" :value="col">{{ col }}</option>
                </select>
              </td>
              <td v-else class="celda-comprobante">
                <label class="check-combinar">
                  <input type="checkbox" v-model="combinarB" />
                  Combinar 2 columnas (Serie + Número)
                </label>
                <select v-if="!combinarB" class="campo-select" v-model="mapeoB.comprobante">
                  <option value="">— sin mapear —</option>
                  <option v-for="col in columnasB" :key="col" :value="col">{{ col }}</option>
                </select>
                <div v-else class="combinar-selects">
                  <select class="campo-select" v-model="serieColB">
                    <option value="">Serie...</option>
                    <option v-for="col in columnasB" :key="'sb'+col" :value="col">{{ col }}</option>
                  </select>
                  <select class="campo-select" v-model="numeroColB">
                    <option value="">Número...</option>
                    <option v-for="col in columnasB" :key="'nb'+col" :value="col">{{ col }}</option>
                  </select>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p class="mapeo-tip">
        💡 Usa "Combinar 2 columnas" cuando el número de comprobante venga separado en Serie + Número
        (típico en el reporte SIRE de SUNAT). El comprobante se compara ignorando ceros a la izquierda,
        así "B155-0004046" y "B155-4046" se tratan como el mismo documento.
      </p>

      <div class="tolerancia-box">
        <label>
          Tolerancia para diferencias de monto:
          <input type="number" step="0.01" min="0" v-model.number="tolerancia" class="tolerancia-input" />
        </label>
        <span class="tolerancia-hint">(diferencias menores a este valor no se marcan como error, útil para redondeos)</span>
      </div>

      <p v-if="intentoContinuar && camposClaveSeleccionados.length === 0" class="mapeo-error">
        ⚠️ Marca al menos un campo como clave (y mapéalo en ambos archivos) para poder comparar.
      </p>

      <div class="mapeo-actions">
        <button class="btn-primary" @click="irAPaso3">Comparar →</button>
      </div>
    </div>

    <!-- PASO 3: RESULTADOS -->
    <div v-if="paso === 3" class="paso-resultado-comp">
      <div class="preview-header">
        <div>
          <h2 class="mapeo-titulo">Resultado de la confrontación</h2>
          <p class="mapeo-sub">{{ nombreA || 'Archivo A' }} vs {{ nombreB || 'Archivo B' }}</p>
        </div>
        <div class="preview-header-actions">
          <button class="btn-ghost" @click="paso = 2">← Ajustar mapeo</button>
          <button class="btn-primary" @click="exportarExcel">⬇️ Descargar reporte</button>
        </div>
      </div>

      <div class="resumen-grid-comp">
        <div class="resumen-mini" :class="{ activo: filtro === 'todos' }" @click="filtro = 'todos'">
          <span class="resumen-mini-num">{{ filasResultado.length }}</span>
          <span class="resumen-mini-label">Total claves</span>
        </div>
        <div class="resumen-mini" :class="{ activo: filtro === 'coincide_exacto' }" @click="filtro = 'coincide_exacto'">
          <span class="resumen-mini-num txt-verde">{{ coincidenciasExactas.length }}</span>
          <span class="resumen-mini-label">Coinciden exacto</span>
        </div>
        <div class="resumen-mini" :class="{ activo: filtro === 'diferencias' }" @click="filtro = 'diferencias'">
          <span class="resumen-mini-num txt-naranja">{{ coincidenciasConDiferencia.length }}</span>
          <span class="resumen-mini-label">Con diferencias</span>
        </div>
        <div class="resumen-mini" :class="{ activo: filtro === 'solo_a' }" @click="filtro = 'solo_a'">
          <span class="resumen-mini-num txt-azul">{{ soloA.length }}</span>
          <span class="resumen-mini-label">Solo en {{ nombreA || 'A' }}</span>
        </div>
        <div class="resumen-mini" :class="{ activo: filtro === 'solo_b' }" @click="filtro = 'solo_b'">
          <span class="resumen-mini-num txt-azul">{{ soloB.length }}</span>
          <span class="resumen-mini-label">Solo en {{ nombreB || 'B' }}</span>
        </div>
        <div class="resumen-mini" :class="{ activo: filtro === 'duplicados_a' }" @click="filtro = 'duplicados_a'">
          <span class="resumen-mini-num txt-rojo">{{ duplicadosA.length }}</span>
          <span class="resumen-mini-label">Duplicados en {{ nombreA || 'A' }}</span>
        </div>
        <div class="resumen-mini" :class="{ activo: filtro === 'duplicados_b' }" @click="filtro = 'duplicados_b'">
          <span class="resumen-mini-num txt-rojo">{{ duplicadosB.length }}</span>
          <span class="resumen-mini-label">Duplicados en {{ nombreB || 'B' }}</span>
        </div>
      </div>

      <div class="tabla-wrap">
        <table class="tabla-preview tabla-resultado">
          <thead>
            <tr>
              <th>Estado</th>
              <th>Clave</th>
              <th v-for="campo in campos" :key="'a' + campo.key">{{ campo.label }} ({{ nombreA || 'A' }})</th>
              <th v-for="campo in campos" :key="'b' + campo.key">{{ campo.label }} ({{ nombreB || 'B' }})</th>
              <th>Detalle</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(fila, i) in filasFiltradas.slice(0, 300)" :key="i">
              <td><span class="badge-estado" :class="'badge-' + fila.tipo">{{ fila.etiqueta }}</span></td>
              <td class="celda-clave-val">{{ fila.clave }}</td>
              <td
                v-for="campo in campos"
                :key="'va' + campo.key"
                :class="{ 'celda-diff': fila.diffs.indexOf(campo.key) !== -1 }"
              >{{ fila.a ? fila.a[campo.key] : '—' }}</td>
              <td
                v-for="campo in campos"
                :key="'vb' + campo.key"
                :class="{ 'celda-diff': fila.diffs.indexOf(campo.key) !== -1 }"
              >{{ fila.b ? fila.b[campo.key] : '—' }}</td>
              <td class="celda-detalle">{{ fila.detalle }}</td>
            </tr>
          </tbody>
        </table>
        <p v-if="!filasFiltradas.length" class="tabla-mas">No hay filas en esta categoría.</p>
        <p v-else-if="filasFiltradas.length > 300" class="tabla-mas">
          Mostrando 300 de {{ filasFiltradas.length }} filas. Descarga el reporte para verlas todas.
        </p>
      </div>
    </div>

    <!-- BARRA DE PASOS -->
    <div class="pasos-bar" v-if="paso < 4">
      <div v-for="n in 3" :key="n" class="paso-dot" :class="{ activo: paso === n, completado: paso > n }">
        <span class="paso-num">{{ paso > n ? '✓' : n }}</span>
        <span class="paso-label">{{ ['Subir', 'Mapear', 'Comparar'][n - 1] }}</span>
      </div>
    </div>

  </div>
</template>

<script>
import * as XLSX from 'xlsx'

export default {
  name: 'ConfrontacionRegistros',

  data() {
    return {
      paso: 1,

      arrastrandoA: false,
      arrastrandoB: false,
      archivoA: null,
      archivoB: null,
      nombreA: '',
      nombreB: '',

      columnasA: [],
      filasA: [],
      columnasB: [],
      filasB: [],

      intentoContinuar: false,
      filtro: 'todos',
      tolerancia: 0.01,

      // Comprobante compuesto (Serie + Número), típico del reporte SIRE de SUNAT
      combinarA: false,
      combinarB: false,
      serieColA: '',
      numeroColA: '',
      serieColB: '',
      numeroColB: '',

      mapeoA: { comprobante: '', fecha: '', cliente: '', monto: '', formaPago: '', ruc: '', tipoDoc: '' },
      mapeoB: { comprobante: '', fecha: '', cliente: '', monto: '', formaPago: '', ruc: '', tipoDoc: '' },
      clave:  { comprobante: true, fecha: false, cliente: false, monto: false, formaPago: false, ruc: false, tipoDoc: false },

      campos: [
        { key: 'comprobante', label: 'N° Comprobante', tipo: 'comprobante' },
        { key: 'fecha',       label: 'Fecha',           tipo: 'fecha'  },
        { key: 'cliente',     label: 'Cliente',         tipo: 'texto'  },
        { key: 'monto',       label: 'Monto',           tipo: 'numero' },
        { key: 'formaPago',   label: 'Forma de pago',   tipo: 'texto'  },
        { key: 'ruc',         label: 'RUC / DNI',       tipo: 'texto', opcional: true },
        { key: 'tipoDoc',     label: 'Tipo Doc.',       tipo: 'texto', opcional: true },
      ],
    }
  },

  computed: {
    camposClaveSeleccionados() {
      var self = this
      return this.campos.filter(function(c) {
        return self.clave[c.key] && self.campoMapeadoA(c.key) && self.campoMapeadoB(c.key)
      })
    },

    filasA_norm() {
      if (!this.camposClaveSeleccionados.length) return []
      return this.construirFilas(this.filasA, this.mapeoA, 'A')
    },

    filasB_norm() {
      if (!this.camposClaveSeleccionados.length) return []
      return this.construirFilas(this.filasB, this.mapeoB, 'B')
    },

    mapaA() {
      var m = {}
      this.filasA_norm.forEach(function(f) {
        if (!f._clave) return
        if (!m[f._clave]) m[f._clave] = []
        m[f._clave].push(f)
      })
      return m
    },

    mapaB() {
      var m = {}
      this.filasB_norm.forEach(function(f) {
        if (!f._clave) return
        if (!m[f._clave]) m[f._clave] = []
        m[f._clave].push(f)
      })
      return m
    },

    // Una fila por cada clave única encontrada en A y/o B
    filasResultado() {
      var self = this
      var mapaA = this.mapaA
      var mapaB = this.mapaB
      var claves = {}
      Object.keys(mapaA).forEach(function(k) { claves[k] = true })
      Object.keys(mapaB).forEach(function(k) { claves[k] = true })

      var resultado = []

      Object.keys(claves).forEach(function(key) {
        var grupoA = mapaA[key] || []
        var grupoB = mapaB[key] || []
        var enA = grupoA.length > 0
        var enB = grupoB.length > 0
        var dupA = grupoA.length > 1
        var dupB = grupoB.length > 1

        var fila = {
          clave: key,
          a: enA ? grupoA[0]._campos : null,
          b: enB ? grupoB[0]._campos : null,
          diffs: [],
          dupA: dupA,
          dupB: dupB,
          tipo: '',
          etiqueta: '',
          detalle: '',
        }

        if (enA && enB) {
        self.campos.forEach(function(campo) {
            if (!self.clave[campo.key]) return
            if (!self.campoMapeadoA(campo.key) || !self.campoMapeadoB(campo.key)) return
            var na = grupoA[0]._norm[campo.key]
            var nb = grupoB[0]._norm[campo.key]
            var distinto = campo.tipo === 'numero'
              ? Math.abs((na || 0) - (nb || 0)) > self.tolerancia
              : na !== nb
            if (distinto) fila.diffs.push(campo.key)
          })

          if (fila.diffs.length) {
            fila.tipo = 'diferencia'
            fila.etiqueta = 'Diferencia'
            fila.detalle = 'Distinto en: ' + fila.diffs.map(function(k) {
              var c = self.campos.filter(function(cc) { return cc.key === k })[0]
              return c ? c.label : k
            }).join(', ')
          } else {
            fila.tipo = 'exacto'
            fila.etiqueta = 'Coincide'
            fila.detalle = 'Coincide en todos los campos mapeados'
          }
        } else if (enA && !enB) {
          fila.tipo = 'solo_a'
          fila.etiqueta = 'Solo en ' + (self.nombreA || 'A')
          fila.detalle = 'No se encontró en ' + (self.nombreB || 'B')
        } else {
          fila.tipo = 'solo_b'
          fila.etiqueta = 'Solo en ' + (self.nombreB || 'B')
          fila.detalle = 'No se encontró en ' + (self.nombreA || 'A')
        }

        if (dupA) fila.detalle += ' | ' + grupoA.length + ' registros repetidos en ' + (self.nombreA || 'A')
        if (dupB) fila.detalle += ' | ' + grupoB.length + ' registros repetidos en ' + (self.nombreB || 'B')

        resultado.push(fila)
      })

      return resultado
    },

    coincidenciasExactas() {
      return this.filasResultado.filter(function(f) { return f.tipo === 'exacto' })
    },
    coincidenciasConDiferencia() {
      return this.filasResultado.filter(function(f) { return f.tipo === 'diferencia' })
    },
    soloA() {
      return this.filasResultado.filter(function(f) { return f.tipo === 'solo_a' })
    },
    soloB() {
      return this.filasResultado.filter(function(f) { return f.tipo === 'solo_b' })
    },
    duplicadosA() {
      return this.filasResultado.filter(function(f) { return f.dupA })
    },
    duplicadosB() {
      return this.filasResultado.filter(function(f) { return f.dupB })
    },

    filasFiltradas() {
      switch (this.filtro) {
        case 'coincide_exacto': return this.coincidenciasExactas
        case 'diferencias':     return this.coincidenciasConDiferencia
        case 'solo_a':          return this.soloA
        case 'solo_b':          return this.soloB
        case 'duplicados_a':    return this.duplicadosA
        case 'duplicados_b':    return this.duplicadosB
        default:                return this.filasResultado
      }
    },
  },

  methods: {
    // Indica si el campo está listo para usarse (soporta el modo combinado del comprobante)
    campoMapeadoA(key) {
      if (key === 'comprobante' && this.combinarA) return !!(this.serieColA && this.numeroColA)
      return !!this.mapeoA[key]
    },
    campoMapeadoB(key) {
      if (key === 'comprobante' && this.combinarB) return !!(this.serieColB && this.numeroColB)
      return !!this.mapeoB[key]
    },

    onDrop(e, lado) {
      if (lado === 'A') this.arrastrandoA = false
      else this.arrastrandoB = false
      var file = e.dataTransfer.files[0]
      if (file) this.procesarArchivo(file, lado)
    },

    onFileSelect(e, lado) {
      var file = e.target.files[0]
      if (file) this.procesarArchivo(file, lado)
    },

    procesarArchivo(file, lado) {
      var self = this
      if (lado === 'A') this.archivoA = file
      else this.archivoB = file

      var reader = new FileReader()
      reader.onload = function(e) {
        var wb = XLSX.read(e.target.result, { type: 'binary' })
        var ws = wb.Sheets[wb.SheetNames[0]]
        var datos = XLSX.utils.sheet_to_json(ws, { defval: '' })

        if (!datos.length) {
          alert('El archivo está vacío o no tiene el formato correcto.')
          return
        }

        if (lado === 'A') {
          self.columnasA = Object.keys(datos[0])
          self.filasA = datos
          if (!self.nombreA) self.nombreA = 'Archivo A'
        } else {
          self.columnasB = Object.keys(datos[0])
          self.filasB = datos
          if (!self.nombreB) self.nombreB = 'Archivo B'
        }
        self.intentarMapeoAutomatico(lado)
      }
      reader.readAsBinaryString(file)
    },

    intentarMapeoAutomatico(lado) {
      var claves = {
        comprobante: ['comprobante', 'numerodocumento', 'nrodocumento', 'nrodoc', 'nrocp', 'numero', 'nro', 'factura', 'boleta', 'referencia', 'folio', 'operacion', 'voucher', 'carsunat'],
        fecha:       ['fecha', 'date', 'fechaemision', 'fechaoperacion', 'fechaemis'],
        cliente:     ['cliente', 'client', 'razonsocial', 'nombre', 'apellidos'],
        monto:       ['monto', 'total', 'importe', 'amount', 'venta', 'pventa'],
        formaPago:   ['formapago', 'pago', 'metodopago', 'medio', 'condpago', 'formapa'],
        ruc:         ['rucdni', 'ruc', 'dni', 'nrodocidentidad', 'docidentidad'],
        tipoDoc:     ['tipodoc', 'tipocp', 'tipocomprobante', 'tipodocumento', 'tipo'],
      }

      var columnas = lado === 'A' ? this.columnasA : this.columnasB
      var mapeo = lado === 'A' ? this.mapeoA : this.mapeoB

      function normCol(c) {
        return c.toLowerCase().replace(/[\s_\-.]/g, '')
      }

      columnas.forEach(function(col) {
        var colLower = normCol(col)
        Object.keys(claves).forEach(function(campo) {
          if (!mapeo[campo]) {
            var encontrado = claves[campo].some(function(p) { return colLower.indexOf(p) !== -1 })
            if (encontrado) mapeo[campo] = col
          }
        })
      })

      // Detecta reportes tipo SUNAT SIRE donde el comprobante viene partido en Serie + Número
      var colSerie = columnas.filter(function(c) { return normCol(c).indexOf('serie') !== -1 })[0]
      var colNumero = columnas.filter(function(c) {
        var n = normCol(c)
        return ['nrocp', 'numerocp', 'nrocorrelativo', 'correlativo'].some(function(p) { return n.indexOf(p) !== -1 })
      })[0]

      if (colSerie && colNumero) {
        if (lado === 'A') {
          this.serieColA = colSerie
          this.numeroColA = colNumero
          this.combinarA = true
        } else {
          this.serieColB = colSerie
          this.numeroColB = colNumero
          this.combinarB = true
        }
      }
    },

    irAPaso2() {
      this.paso = 2
    },

    irAPaso3() {
      this.intentoContinuar = true
      if (this.camposClaveSeleccionados.length === 0) return
      this.filtro = 'todos'
      this.paso = 3
    },

    construirFilas(filas, mapeo, lado) {
      var self = this
      var combinar = lado === 'A' ? this.combinarA : this.combinarB
      var serieCol = lado === 'A' ? this.serieColA : this.serieColB
      var numeroCol = lado === 'A' ? this.numeroColA : this.numeroColB

      return filas.map(function(fila) {
        var obj = { _campos: {}, _norm: {} }
        self.campos.forEach(function(campo) {
          var val
          if (campo.key === 'comprobante' && combinar) {
            var vs = serieCol ? fila[serieCol] : ''
            var vn = numeroCol ? fila[numeroCol] : ''
            var partes = [vs, vn].filter(function(x) { return x !== undefined && x !== null && x !== '' })
            val = partes.join('-')
          } else {
            var col = mapeo[campo.key]
            val = col ? fila[col] : ''
          }
          obj._campos[campo.key] = (val !== undefined && val !== null && val !== '') ? String(val) : ''
          obj._norm[campo.key] = self.normalizarValor(val, campo.tipo)
        })
        obj._clave = self.camposClaveSeleccionados.map(function(c) { return obj._norm[c.key] }).join('|')
        return obj
      })
    },

    normalizarValor(valor, tipo) {
      if (tipo === 'numero') return this.normalizarMonto(valor)
      if (tipo === 'fecha') return this.normalizarFecha(valor)
      if (tipo === 'comprobante') return this.normalizarComprobante(valor)
      return this.normalizarTexto(valor)
    },

    normalizarTexto(v) {
      return String(v === undefined || v === null ? '' : v).trim().toUpperCase().replace(/\s+/g, ' ')
    },

    normalizarMonto(v) {
      var n = parseFloat(String(v === undefined || v === null ? '' : v).replace(/[^0-9.\-]/g, ''))
      if (isNaN(n)) n = 0
      return Math.round(n * 100) / 100
    },

    // Ignora ceros a la izquierda en la parte numérica final, para que
    // "B155-0004046" y "B155-4046" (o "B150" + "1506") se traten como el mismo comprobante.
    normalizarComprobante(v) {
      var s = String(v === undefined || v === null ? '' : v).trim().toUpperCase().replace(/\s+/g, '')
      if (!s) return ''
      var m = s.match(/^(.*?)(\d+)$/)
      if (m) {
        var prefijo = m[1].replace(/[-_]+$/, '')
        var numero = m[2].replace(/^0+(?=\d)/, '')
        return prefijo ? (prefijo + '-' + numero) : numero
      }
      return s
    },

    normalizarFecha(v) {
      if (v === undefined || v === null || v === '') return ''
      if (typeof v === 'number') {
        var d = new Date(Math.round((v - 25569) * 86400 * 1000))
        return this.formatoFecha(d)
      }
      var d2 = new Date(v)
      if (!isNaN(d2.getTime())) return this.formatoFecha(d2)
      return String(v).trim().toUpperCase()
    },

    formatoFecha(d) {
      var y = d.getFullYear()
      var m = ('0' + (d.getMonth() + 1)).slice(-2)
      var day = ('0' + d.getDate()).slice(-2)
      return y + '-' + m + '-' + day
    },

    exportarExcel() {
      var self = this
      var wb = XLSX.utils.book_new()

      var nomA = this.nombreA || 'A'
      var nomB = this.nombreB || 'B'

      var resumenData = [
        { Metrica: 'Total filas ' + nomA, Valor: this.filasA.length },
        { Metrica: 'Total filas ' + nomB, Valor: this.filasB.length },
        { Metrica: 'Coinciden exacto', Valor: this.coincidenciasExactas.length },
        { Metrica: 'Con diferencias', Valor: this.coincidenciasConDiferencia.length },
        { Metrica: 'Solo en ' + nomA, Valor: this.soloA.length },
        { Metrica: 'Solo en ' + nomB, Valor: this.soloB.length },
        { Metrica: 'Duplicados en ' + nomA, Valor: this.duplicadosA.length },
        { Metrica: 'Duplicados en ' + nomB, Valor: this.duplicadosB.length },
      ]
      XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(resumenData), 'Resumen')

      function filaAObjetoPlano(fila) {
        var obj = { Clave: fila.clave, Estado: fila.etiqueta, Detalle: fila.detalle }
        self.campos.forEach(function(c) {
          obj[nomA + ' - ' + c.label] = fila.a ? fila.a[c.key] : ''
          obj[nomB + ' - ' + c.label] = fila.b ? fila.b[c.key] : ''
        })
        return obj
      }

      var hojas = [
        ['Diferencias', this.coincidenciasConDiferencia],
        ['CoincidenciasExactas', this.coincidenciasExactas],
        ['SoloEn' + nomA, this.soloA],
        ['SoloEn' + nomB, this.soloB],
        ['DuplicadosEn' + nomA, this.duplicadosA],
        ['DuplicadosEn' + nomB, this.duplicadosB],
      ]

      hojas.forEach(function(h) {
        var nombreHoja = h[0].substring(0, 31)
        var datos = h[1].map(filaAObjetoPlano)
        if (!datos.length) datos = [{ Info: 'Sin registros' }]
        XLSX.utils.book_append_sheet(wb, XLSX.utils.json_to_sheet(datos), nombreHoja)
      })

      XLSX.writeFile(wb, 'Confrontacion_' + nomA + '_vs_' + nomB + '.xlsx')
    },

    reiniciar() {
      this.paso = 1
      this.arrastrandoA = false
      this.arrastrandoB = false
      this.archivoA = null
      this.archivoB = null
      this.nombreA = ''
      this.nombreB = ''
      this.columnasA = []
      this.filasA = []
      this.columnasB = []
      this.filasB = []
      this.intentoContinuar = false
      this.filtro = 'todos'
      this.tolerancia = 0.01
      this.combinarA = false
      this.combinarB = false
      this.serieColA = ''
      this.numeroColA = ''
      this.serieColB = ''
      this.numeroColB = ''
      this.mapeoA = { comprobante: '', fecha: '', cliente: '', monto: '', formaPago: '', ruc: '', tipoDoc: '' }
      this.mapeoB = { comprobante: '', fecha: '', cliente: '', monto: '', formaPago: '', ruc: '', tipoDoc: '' }
      this.clave = { comprobante: true, fecha: false, cliente: false, monto: false, formaPago: false, ruc: false, tipoDoc: false }
      if (this.$refs.fileInputA) this.$refs.fileInputA.value = ''
      if (this.$refs.fileInputB) this.$refs.fileInputB.value = ''
    },
  },
}
</script>

<style scoped>
.confrontador {
  width: 100%;
  max-width: 1600px; /* o el ancho que prefieras, más grande que 1100px */
  margin: 0 auto;
  padding: 32px 40px 90px;
  box-sizing: border-box;
}

/* ── UPLOAD (2 columnas) ── */
.upload-grid {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
  gap: 20px;
  align-items: start;
}
@media (max-width: 700px) {
  .upload-grid { grid-template-columns: 1fr; }
  .upload-versus { padding: 4px 0 !important; }
}
.upload-versus {
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  color: #94a3b8;
  font-size: 1.1rem;
  padding-top: 62px;
}
.nombre-archivo-label {
  display: block;
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 600;
  margin-bottom: 6px;
}
.nombre-input {
  display: block;
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 7px 10px;
  font-size: 0.85rem;
  margin-top: 4px;
  box-sizing: border-box;
}
.upload-actions { display: flex; justify-content: center; margin-top: 28px; }

.drop-zone {
  border: 2px dashed #c8d0db;
  border-radius: 16px;
  padding: 52px 20px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #fafbfc;
}
.drop-zone:hover, .drag-over { border-color: #2563eb; background: #eff6ff; }
.drop-listo { border-color: #22c55e; background: #f0fdf4; }
.drop-icon   { font-size: 2.6rem; margin-bottom: 10px; }
.drop-titulo { font-size: 1.05rem; font-weight: 600; color: #1e293b; margin: 0 0 6px; word-break: break-word; }
.drop-sub    { color: #64748b; margin: 0 0 6px; font-size: 0.85rem; }
.drop-hint   { font-size: 0.8rem; color: #94a3b8; margin: 0; }

/* ── MAPEO ── */
.mapeo-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}
.mapeo-titulo { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.mapeo-sub    { color: #64748b; margin: 0; font-size: 0.88rem; }

.mapeo-tabla-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px; margin-bottom: 10px; }
.mapeo-tabla { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
.mapeo-tabla th {
  background: #1e293b; color: #f8fafc; padding: 10px 14px; text-align: left; white-space: nowrap;
}
.mapeo-tabla td { padding: 9px 14px; border-bottom: 1px solid #f1f5f9; }
.fila-clave { background: #eff6ff; }
.celda-campo { font-weight: 600; color: #334155; white-space: nowrap; }
.tag-opcional { font-size: 0.68rem; font-weight: 600; color: #94a3b8; margin-left: 4px; }
.celda-clave { text-align: center; }
.celda-clave input { width: 18px; height: 18px; cursor: pointer; }

.campo-select {
  width: 100%; min-width: 160px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 7px 10px;
  font-size: 0.85rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}
.campo-select:focus { outline: none; border-color: #2563eb; }

.celda-comprobante { min-width: 200px; }
.check-combinar {
  display: flex; align-items: center; gap: 6px;
  font-size: 0.74rem; color: #64748b; font-weight: 600;
  margin-bottom: 6px; cursor: pointer; white-space: normal;
}
.check-combinar input { width: 14px; height: 14px; cursor: pointer; }
.combinar-selects { display: flex; flex-direction: column; gap: 6px; }

.mapeo-tip {
  font-size: 0.78rem;
  color: #64748b;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 8px 12px;
  margin-bottom: 14px;
}

.tolerancia-box { margin-bottom: 14px; font-size: 0.85rem; color: #334155; }
.tolerancia-input { width: 90px; border: 1px solid #cbd5e1; border-radius: 6px; padding: 5px 8px; margin-left: 8px; }
.tolerancia-hint { color: #94a3b8; font-size: 0.78rem; margin-left: 8px; }

.mapeo-error { color: #dc2626; font-size: 0.85rem; margin-bottom: 12px; }
.mapeo-actions { display: flex; justify-content: flex-end; }

/* ── RESUMEN / RESULTADOS ── */
.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  gap: 12px;
  flex-wrap: wrap;
}
.preview-header-actions { display: flex; gap: 10px; align-items: center; }

.resumen-grid-comp {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 10px;
  margin-bottom: 20px;
}
.resumen-mini {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 14px;
  text-align: center;
  cursor: pointer;
  transition: all 0.15s;
}
.resumen-mini:hover { border-color: #94a3b8; }
.resumen-mini.activo { border-color: #2563eb; background: #eff6ff; }
.resumen-mini-num { display: block; font-size: 1.6rem; font-weight: 800; color: #1e293b; }
.resumen-mini-label { font-size: 0.72rem; color: #64748b; font-weight: 600; }
.txt-verde   { color: #16a34a; }
.txt-naranja { color: #ea580c; }
.txt-azul    { color: #2563eb; }
.txt-rojo    { color: #dc2626; }

/* ── TABLA ── */
.tabla-wrap    { overflow-x: auto; border-radius: 10px; border: 1px solid #e2e8f0; }
.tabla-preview { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.tabla-preview th {
  background: #1e293b; color: #f8fafc; padding: 10px 14px; text-align: left; white-space: nowrap;
}
.tabla-preview td {
  padding: 9px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; white-space: nowrap;
}
.tabla-preview tr:hover td { background: #f8fafc; }
.tabla-mas { text-align: center; color: #64748b; font-size: 0.82rem; padding: 10px; margin: 0; border-top: 1px solid #e2e8f0; }

.badge-estado { font-size: 0.72rem; padding: 3px 9px; border-radius: 999px; font-weight: 700; white-space: nowrap; }
.badge-exacto     { background: #dcfce7; color: #15803d; }
.badge-diferencia { background: #ffedd5; color: #c2410c; }
.badge-solo_a, .badge-solo_b { background: #dbeafe; color: #1d4ed8; }
.celda-diff { background: #fef3c7 !important; font-weight: 700; color: #92400e; }
.celda-clave-val { font-family: monospace; font-size: 0.78rem; color: #64748b; }
.celda-detalle { font-size: 0.78rem; color: #64748b; white-space: normal; max-width: 240px; }

/* ── BOTONES ── */
.btn-primary {
  background: #2563eb; color: #fff; border: none; border-radius: 8px;
  padding: 10px 22px; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: background 0.15s;
}
.btn-primary:hover    { background: #1d4ed8; }
.btn-primary:disabled { background: #93c5fd; cursor: not-allowed; }

.btn-ghost {
  background: transparent; border: 1px solid #e2e8f0; border-radius: 8px;
  padding: 9px 18px; font-size: 0.88rem; color: #64748b; cursor: pointer;
}
.btn-ghost:hover { background: #f8fafc; color: #334155; }

/* ── BARRA DE PASOS ── */
.pasos-bar {
  position: fixed; bottom: 0; left: 0; right: 0;
  background: #fff; border-top: 1px solid #e2e8f0;
  display: flex; justify-content: center; gap: 64px;
  padding: 14px 20px; z-index: 10;
}
.paso-dot { display: flex; flex-direction: column; align-items: center; gap: 4px; }
.paso-num {
  width: 28px; height: 28px; border-radius: 50%;
  background: #e2e8f0; color: #94a3b8;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.78rem; font-weight: 700; transition: all 0.2s;
}
.paso-dot.activo     .paso-num { background: #2563eb; color: #fff; }
.paso-dot.completado .paso-num { background: #22c55e; color: #fff; }
.paso-label { font-size: 0.7rem; color: #94a3b8; font-weight: 500; }
.paso-dot.activo     .paso-label { color: #2563eb; font-weight: 700; }
.paso-dot.completado .paso-label { color: #22c55e; }
</style>