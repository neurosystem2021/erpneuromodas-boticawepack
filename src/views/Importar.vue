<template>
  <div class="importador">

    <!-- PASO 1: SUBIR ARCHIVO -->
    <div v-if="paso === 1" class="paso-upload">
      <div
        class="drop-zone"
        :class="{ 'drag-over': arrastrando }"
        @dragover.prevent="arrastrando = true"
        @dragleave="arrastrando = false"
        @drop.prevent="onDrop"
        @click="$refs.fileInput.click()"
      >
        <div class="drop-icon">📊</div>
        <p class="drop-titulo">Arrastra tu Excel aquí</p>
        <p class="drop-sub">o haz clic para seleccionar</p>
        <p class="drop-hint">.xlsx · .xls · cualquier proveedor</p>
        <input ref="fileInput" type="file" accept=".xlsx,.xls" hidden @change="onFileSelect" />
      </div>
    </div>

    <!-- PASO 2: MAPEAR COLUMNAS -->
    <div v-if="paso === 2" class="paso-mapeo">
      <div class="mapeo-header">
        <div>
          <h2 class="mapeo-titulo">¿Qué columna es cada campo?</h2>
          <p class="mapeo-sub">
            Archivo: <strong>{{ archivo.name }}</strong> · {{ totalFilas }} filas detectadas
          </p>
        </div>
        <button class="btn-ghost" @click="reiniciar">✕ Cambiar archivo</button>
      </div>

      <div class="mapeo-grid">
        <div
          v-for="campo in camposRequeridos"
          :key="campo.key"
          class="campo-card"
          :class="{
            'campo-mapeado': mapeo[campo.key],
            'campo-requerido-error': campo.requerido && !mapeo[campo.key] && intentoImportar
          }"
        >
          <div class="campo-info">
            <span class="campo-nombre">{{ campo.label }}</span>
            <span v-if="campo.requerido" class="badge-req">requerido</span>
            <span v-else class="badge-opc">opcional</span>
          </div>
          <select class="campo-select" v-model="mapeo[campo.key]">
            <option value="">— sin mapear —</option>
            <option v-for="col in columnasExcel" :key="col" :value="col">{{ col }}</option>
          </select>
          <div v-if="mapeo[campo.key]" class="campo-preview">
            <span
              v-for="(val, i) in previewDe(mapeo[campo.key])"
              :key="i"
              class="preview-chip"
            >{{ val }}</span>
          </div>
        </div>
      </div>

      <div class="mapeo-actions">
        <button class="btn-primary" @click="irAPaso3">Ver resumen →</button>
      </div>
    </div>

    <!-- PASO 3: RESUMEN + PREVISUALIZAR -->
    <div v-if="paso === 3" class="paso-preview">
      <div class="preview-header">
        <div>
          <h2 class="mapeo-titulo">Resumen de importación</h2>
          <p class="mapeo-sub">Revisa qué se va a crear antes de importar</p>
        </div>
        <div class="preview-header-actions">
          <button class="btn-ghost" @click="paso = 2">← Ajustar mapeo</button>
          <button class="btn-primary" :disabled="importando" @click="importar">
            <span v-if="!importando">🚀 Importar ahora</span>
            <span v-else>⏳ Importando...</span>
          </button>
        </div>
      </div>

      <!-- RESUMEN: Categorías y Marcas nuevas -->
      <div class="resumen-grid">

        <!-- Categorías -->
        <div class="resumen-card">
          <div class="resumen-card-header">
            <span class="resumen-icon">🗂️</span>
            <div>
              <p class="resumen-titulo">Categorías detectadas</p>
              <p class="resumen-sub">
                <strong class="txt-verde">{{ categoriasNuevas.length }} nuevas</strong>
                se crearán en Gen_ProductoCategoria
              </p>
            </div>
          </div>
          <div class="resumen-chips">
            <span
              v-for="(cat, i) in categoriasNuevas"
              :key="i"
              class="chip-nuevo"
            >+ {{ cat }}</span>
            <span
              v-for="(cat, i) in categoriasExistentes"
              :key="'e'+i"
              class="chip-existe"
            >✓ {{ cat }}</span>
          </div>
        </div>

        <!-- Marcas -->
        <div class="resumen-card">
          <div class="resumen-card-header">
            <span class="resumen-icon">🏷️</span>
            <div>
              <p class="resumen-titulo">Marcas detectadas</p>
              <p class="resumen-sub">
                <strong class="txt-verde">{{ marcasNuevas.length }} nuevas</strong>
                se crearán en Gen_ProductoMarca
              </p>
            </div>
          </div>
          <div class="resumen-chips">
            <span
              v-for="(mar, i) in marcasNuevas"
              :key="i"
              class="chip-nuevo"
            >+ {{ mar }}</span>
            <span
              v-for="(mar, i) in marcasExistentes"
              :key="'e'+i"
              class="chip-existe"
            >✓ {{ mar }}</span>
          </div>
        </div>

      </div>

      <!-- Contador productos -->
      <div class="contador-productos">
        <div class="contador-item">
          <span class="contador-num">{{ productosParaImportar.length }}</span>
          <span class="contador-label">productos a importar en Gen_Producto</span>
        </div>
      </div>

      <!-- Tabla preview -->
      <div class="tabla-wrap">
        <table class="tabla-preview">
          <thead>
            <tr>
              <th v-for="campo in camposMapeados" :key="campo.key">{{ campo.label }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(prod, i) in productosParaImportar.slice(0, 8)" :key="i">
              <td v-for="campo in camposMapeados" :key="campo.key">
                {{ prod[campo.key] || '—' }}
              </td>
            </tr>
          </tbody>
        </table>
        <p v-if="productosParaImportar.length > 8" class="tabla-mas">
          ... y {{ productosParaImportar.length - 8 }} filas más
        </p>
      </div>
    </div>

    <!-- PASO 4: RESULTADO -->
    <div v-if="paso === 4" class="paso-resultado">
      <div v-if="resultado.error" class="resultado-error">
        <div class="resultado-icon">❌</div>
        <h3>Error al importar</h3>
        <p>{{ resultado.message }}</p>
        <button class="btn-primary" @click="reiniciar">Intentar de nuevo</button>
      </div>
      <div v-else class="resultado-ok">
        <div class="resultado-icon">✅</div>
        <h3>¡Importación completada!</h3>
        <p>{{ resultado.message }}</p>

        <div v-if="resultado.omitidos && resultado.omitidos.length" style="margin-top:24px; text-align:left;">
          <p style="font-weight:700; color:#dc2626; margin-bottom:8px;">
            ⚠️ {{ resultado.omitidos.length }} productos no se importaron:
          </p>
          <div style="max-height:300px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;">
            <table style="width:100%; border-collapse:collapse; font-size:0.83rem;">
              <thead>
                <tr>
                  <th style="background:#1e293b; color:#fff; padding:8px 12px; text-align:left;">Producto</th>
                  <th style="background:#1e293b; color:#fff; padding:8px 12px; text-align:left;">Razón</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(om, i) in resultado.omitidos" :key="i">
                  <td style="padding:7px 12px; border-bottom:1px solid #f1f5f9;">{{ om.producto }}</td>
                  <td style="padding:7px 12px; border-bottom:1px solid #f1f5f9; color:#dc2626;">{{ om.razon }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <button class="btn-primary" style="margin-top:20px;" @click="reiniciar">Importar otro archivo</button>
      </div>
    </div>

    <!-- BARRA DE PASOS -->
    <div class="pasos-bar" v-if="paso < 4">
      <div
        v-for="n in 3"
        :key="n"
        class="paso-dot"
        :class="{ activo: paso === n, completado: paso > n }"
      >
        <span class="paso-num">{{ paso > n ? '✓' : n }}</span>
        <span class="paso-label">{{ ['Subir', 'Mapear', 'Importar'][n - 1] }}</span>
      </div>
    </div>

  </div>
</template>

<script>
import * as XLSX from 'xlsx'
import config from '@/store/config.js'

export default {
  name: 'ImportarExcel',

  data() {
    return {
      API: config.API,
      paso: 1,
      arrastrando: false,
      archivo: null,
      columnasExcel: [],
      filasExcel: [],
      totalFilas: 0,
      intentoImportar: false,
      importando: false,
      resultado: {},

      // Categorías y marcas que ya existen en BD (se cargan al ir al paso 3)
      categoriasEnBD: [],
      marcasEnBD: [],

      mapeo: {
        producto:      '',
        codigo:        '',
        categoria:     '',
        marca:         '',
        precioContado: '',
        precioCosto:   '',
        stock:         '',
        genero:        '',
        color:         '',
        botapie:       '',
        modelo:        '',
      },

      camposRequeridos: [
        { key: 'producto',      label: 'Nombre del producto', requerido: true  },
        { key: 'categoria',     label: 'Categoría',           requerido: true  },
        { key: 'marca',         label: 'Marca',               requerido: true  },
        { key: 'precioContado', label: 'Precio de venta',     requerido: false },
        { key: 'precioCosto',   label: 'Precio de costo',     requerido: false },
        { key: 'stock',         label: 'Stock inicial',       requerido: false },
        { key: 'codigo',        label: 'Código / Barra',      requerido: false },
        { key: 'genero',        label: 'Género',              requerido: false },
        { key: 'color',         label: 'Color',               requerido: false },
        { key: 'botapie',       label: 'Bota / Pie',          requerido: false },
        { key: 'modelo',        label: 'Modelo',              requerido: false },
      ],
    }
  },

  computed: {
    camposMapeados() {
      return this.camposRequeridos.filter(function(c) {
        return this.mapeo[c.key]
      }, this)
    },

    productosParaImportar() {
      var self = this
      return this.filasExcel.map(function(fila) {
        var obj = {}
        self.camposRequeridos.forEach(function(campo) {
          if (self.mapeo[campo.key]) {
            var val = fila[self.mapeo[campo.key]]
            obj[campo.key] = (val !== undefined && val !== null) ? String(val) : ''
          }
        })
        return obj
      }).filter(function(p) { return p.producto })
    },

    // Categorías únicas del Excel
    categoriasDelExcel() {
      if (!this.mapeo.categoria) return []
      var vistas = {}
      var result = []
      this.productosParaImportar.forEach(function(p) {
        var cat = (p.categoria || '').trim().toUpperCase()
        if (cat && !vistas[cat]) {
          vistas[cat] = true
          result.push(cat)
        }
      })
      return result
    },

    // Categorías del Excel que NO están en BD → se van a crear
    categoriasNuevas() {
      var enBD = this.categoriasEnBD.map(function(c) {
        return c.toUpperCase()
      })
      return this.categoriasDelExcel.filter(function(c) {
        return enBD.indexOf(c) === -1
      })
    },

    // Categorías del Excel que YA están en BD
    categoriasExistentes() {
      var enBD = this.categoriasEnBD.map(function(c) {
        return c.toUpperCase()
      })
      return this.categoriasDelExcel.filter(function(c) {
        return enBD.indexOf(c) !== -1
      })
    },

    // Marcas únicas del Excel
    marcasDelExcel() {
      if (!this.mapeo.marca) return []
      var vistas = {}
      var result = []
      this.productosParaImportar.forEach(function(p) {
        var mar = (p.marca || '').trim().toUpperCase()
        if (mar && !vistas[mar]) {
          vistas[mar] = true
          result.push(mar)
        }
      })
      return result
    },

    // Marcas del Excel que NO están en BD → se van a crear
    marcasNuevas() {
      var enBD = this.marcasEnBD.map(function(m) {
        return m.toUpperCase()
      })
      return this.marcasDelExcel.filter(function(m) {
        return enBD.indexOf(m) === -1
      })
    },

    // Marcas del Excel que YA están en BD
    marcasExistentes() {
      var enBD = this.marcasEnBD.map(function(m) {
        return m.toUpperCase()
      })
      return this.marcasDelExcel.filter(function(m) {
        return enBD.indexOf(m) !== -1
      })
    },
  },

  methods: {
    onDrop(e) {
      this.arrastrando = false
      var file = e.dataTransfer.files[0]
      if (file) this.procesarArchivo(file)
    },

    onFileSelect(e) {
      var file = e.target.files[0]
      if (file) this.procesarArchivo(file)
    },

    procesarArchivo(file) {
      this.archivo = file
      var self = this
      var reader = new FileReader()
      reader.onload = function(e) {
        var wb = XLSX.read(e.target.result, { type: 'binary' })
        var ws = wb.Sheets[wb.SheetNames[0]]
        var datos = XLSX.utils.sheet_to_json(ws, { defval: '' })

        if (!datos.length) {
          alert('El archivo está vacío o no tiene el formato correcto.')
          return
        }

        self.columnasExcel = Object.keys(datos[0])
        self.filasExcel = datos
        self.totalFilas = datos.length
        self.intentarMapeoAutomatico()
        self.paso = 2
      }
      reader.readAsBinaryString(file)
    },

    intentarMapeoAutomatico() {
      var claves = {
        producto:      ['producto', 'descripcion', 'articulo', 'item', 'nombre', 'product', 'name'],
        codigo:        ['codigo', 'cod', 'barra', 'sku', 'code', 'codigobarra'],
        categoria:     ['categoria', 'category', 'rubro', 'grupo', 'division'],
        marca:         ['marca', 'brand', 'fabricante', 'laboratorio'],
        precioContado: ['precioventa', 'pventa', 'contado', 'price', 'venta'],
        precioCosto:   ['costo', 'costoprecio', 'pcosto', 'cost'],
        stock:         ['stock', 'cantidad', 'qty', 'existencia', 'saldo', 'inventario'],
        genero:        ['genero', 'gender', 'sexo'],
        color:         ['color', 'colour'],
        botapie:       ['bota', 'botapie', 'pie'],
        modelo:        ['modelo', 'model'],
      }

      var self = this
      this.columnasExcel.forEach(function(col) {
        var colLower = col.toLowerCase().replace(/[\s_-]/g, '')
        Object.keys(claves).forEach(function(campo) {
          if (!self.mapeo[campo]) {
            var encontrado = claves[campo].some(function(p) {
              return colLower.indexOf(p) !== -1
            })
            if (encontrado) self.mapeo[campo] = col
          }
        })
      })
    },

    previewDe(columna) {
      return this.filasExcel
        .slice(0, 3)
        .map(function(f) { return f[columna] })
        .filter(function(v) { return v !== '' && v !== null && v !== undefined })
    },

    // Al pasar al paso 3, carga categorías y marcas existentes en BD
    irAPaso3() {
      this.intentoImportar = true
      var faltantes = this.camposRequeridos.filter(function(c) {
        return c.requerido && !this.mapeo[c.key]
      }, this)
      if (faltantes.length) return

      var self = this
      // Cargar categorías existentes
      this.axios.get(this.API + 'categorias', { params: { q: '', limit: 500 } })
        .then(function(res) {
          self.categoriasEnBD = res.data.map(function(c) {
            return c.ProductoCategoria
          })
        })
        .catch(function() { self.categoriasEnBD = [] })

      // Cargar marcas existentes
      this.axios.get(this.API + 'marcas', { params: { q: '', limit: 500 } })
        .then(function(res) {
          self.marcasEnBD = res.data.map(function(m) {
            return m.ProductoMarca
          })
        })
        .catch(function() { self.marcasEnBD = [] })

      this.paso = 3
    },

    importar() {
      if (!this.productosParaImportar.length) return
      this.importando = true

      var self = this
      var session = {}
      try { session = JSON.parse(localStorage.getItem('user')) || {} } catch(e) {}
      var usuario = session.Usuario || 'IMPORT_EXCEL'

      this.axios.post(
        this.API + 'productos/importar',
        {
          data: this.productosParaImportar,
          usuario: usuario,
        },
        {
          headers: { 'Content-Type': 'application/json' },
          maxContentLength: Infinity,
          maxBodyLength: Infinity,
        }
      )
      .then(function(response) {
        self.resultado = {
          error: false,
          message: response.data.message || 'Importacion completada',
          omitidos: response.data.omitidos || [],
        }
      })
      .catch(function() {
        self.resultado = {
          error: true,
          message: 'Error en el servidor al procesar la carga.'
        }
      })
      .finally(function() {
        self.importando = false
        self.paso = 4
      })
    },

    reiniciar() {
      this.paso = 1
      this.archivo = null
      this.columnasExcel = []
      this.filasExcel = []
      this.totalFilas = 0
      this.intentoImportar = false
      this.resultado = {}
      this.categoriasEnBD = []
      this.marcasEnBD = []
      this.mapeo = {
        producto: '', codigo: '', categoria: '', marca: '',
        precioContado: '', precioCosto: '', stock: '',
        genero: '', color: '', botapie: '', modelo: '',
      }
      if (this.$refs.fileInput) this.$refs.fileInput.value = ''
    },
  },
}
</script>

<style scoped>
.importador {
  max-width: 960px;
  margin: 0 auto;
  padding: 32px 20px 90px;
  font-family: system-ui, sans-serif;
}

/* ── DROP ZONE ── */
.drop-zone {
  border: 2px dashed #c8d0db;
  border-radius: 16px;
  padding: 72px 32px;
  text-align: center;
  cursor: pointer;
  transition: all 0.2s;
  background: #fafbfc;
}
.drop-zone:hover, .drag-over {
  border-color: #2563eb;
  background: #eff6ff;
}
.drop-icon   { font-size: 3rem; margin-bottom: 12px; }
.drop-titulo { font-size: 1.3rem; font-weight: 600; color: #1e293b; margin: 0 0 6px; }
.drop-sub    { color: #64748b; margin: 0 0 6px; }
.drop-hint   { font-size: 0.8rem; color: #94a3b8; margin: 0; }

/* ── MAPEO ── */
.mapeo-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 24px;
}
.mapeo-titulo { font-size: 1.25rem; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.mapeo-sub    { color: #64748b; margin: 0; font-size: 0.88rem; }

.mapeo-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(255px, 1fr));
  gap: 14px;
  margin-bottom: 28px;
}
.campo-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 14px;
  transition: border-color 0.15s;
}
.campo-mapeado         { border-color: #22c55e; background: #f0fdf4; }
.campo-requerido-error { border-color: #ef4444; background: #fef2f2; }

.campo-info {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 8px;
}
.campo-nombre { font-weight: 600; font-size: 0.88rem; color: #334155; }
.badge-req { font-size: 0.7rem; background: #fee2e2; color: #dc2626; padding: 2px 6px; border-radius: 999px; }
.badge-opc { font-size: 0.7rem; background: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 999px; }

.campo-select {
  width: 100%;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  padding: 7px 10px;
  font-size: 0.85rem;
  color: #1e293b;
  background: #fff;
  cursor: pointer;
}
.campo-select:focus { outline: none; border-color: #2563eb; }

.campo-preview { display: flex; gap: 4px; flex-wrap: wrap; margin-top: 8px; }
.preview-chip {
  font-size: 0.72rem;
  background: #e0f2fe;
  color: #0369a1;
  padding: 2px 7px;
  border-radius: 999px;
  max-width: 130px;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.mapeo-actions { display: flex; justify-content: flex-end; }

/* ── RESUMEN ── */
.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 20px;
  gap: 12px;
  flex-wrap: wrap;
}
.preview-header-actions { display: flex; gap: 10px; align-items: center; }

.resumen-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 20px;
}
@media (max-width: 600px) {
  .resumen-grid { grid-template-columns: 1fr; }
}

.resumen-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 18px;
}
.resumen-card-header {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 14px;
}
.resumen-icon  { font-size: 1.6rem; line-height: 1; }
.resumen-titulo { font-weight: 700; font-size: 0.95rem; color: #1e293b; margin: 0 0 3px; }
.resumen-sub    { font-size: 0.8rem; color: #64748b; margin: 0; }
.txt-verde      { color: #16a34a; }

.resumen-chips { display: flex; flex-wrap: wrap; gap: 6px; }
.chip-nuevo {
  font-size: 0.75rem;
  background: #dcfce7;
  color: #15803d;
  border: 1px solid #bbf7d0;
  padding: 3px 9px;
  border-radius: 999px;
  font-weight: 600;
}
.chip-existe {
  font-size: 0.75rem;
  background: #f1f5f9;
  color: #64748b;
  border: 1px solid #e2e8f0;
  padding: 3px 9px;
  border-radius: 999px;
}

.contador-productos {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  padding: 14px 20px;
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}
.contador-num   { font-size: 2rem; font-weight: 800; color: #2563eb; line-height: 1; }
.contador-label { font-size: 0.88rem; color: #1e40af; font-weight: 500; }

/* ── TABLA ── */
.tabla-wrap    { overflow-x: auto; border-radius: 10px; border: 1px solid #e2e8f0; }
.tabla-preview { width: 100%; border-collapse: collapse; font-size: 0.83rem; }
.tabla-preview th {
  background: #1e293b;
  color: #f8fafc;
  padding: 10px 14px;
  text-align: left;
  white-space: nowrap;
}
.tabla-preview td {
  padding: 9px 14px;
  border-bottom: 1px solid #f1f5f9;
  color: #334155;
  white-space: nowrap;
}
.tabla-preview tr:hover td { background: #f8fafc; }
.tabla-mas {
  text-align: center;
  color: #64748b;
  font-size: 0.82rem;
  padding: 10px;
  margin: 0;
  border-top: 1px solid #e2e8f0;
}

/* ── RESULTADO ── */
.paso-resultado { text-align: center; padding: 60px 20px; }
.resultado-icon { font-size: 3.5rem; margin-bottom: 16px; }
.resultado-ok h3, .resultado-error h3 { font-size: 1.4rem; margin: 0 0 8px; color: #1e293b; }
.resultado-ok p, .resultado-error p   { color: #64748b; margin: 0 0 24px; }

/* ── BOTONES ── */
.btn-primary {
  background: #2563eb;
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 10px 22px;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.15s;
}
.btn-primary:hover    { background: #1d4ed8; }
.btn-primary:disabled { background: #93c5fd; cursor: not-allowed; }

.btn-ghost {
  background: transparent;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 9px 18px;
  font-size: 0.88rem;
  color: #64748b;
  cursor: pointer;
}
.btn-ghost:hover { background: #f8fafc; color: #334155; }

/* ── BARRA DE PASOS ── */
.pasos-bar {
  position: fixed;
  bottom: 0; left: 0; right: 0;
  background: #fff;
  border-top: 1px solid #e2e8f0;
  display: flex;
  justify-content: center;
  gap: 64px;
  padding: 14px 20px;
  z-index: 10;
}
.paso-dot {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}
.paso-num {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #e2e8f0;
  color: #94a3b8;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.78rem;
  font-weight: 700;
  transition: all 0.2s;
}
.paso-dot.activo    .paso-num { background: #2563eb; color: #fff; }
.paso-dot.completado .paso-num { background: #22c55e; color: #fff; }
.paso-label { font-size: 0.7rem; color: #94a3b8; font-weight: 500; }
.paso-dot.activo    .paso-label { color: #2563eb; font-weight: 700; }
.paso-dot.completado .paso-label { color: #22c55e; }
</style>