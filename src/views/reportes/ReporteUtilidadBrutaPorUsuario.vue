<template>
  <div class="ub-page">
    <!-- Encabezado -->
    <div class="ub-hero">
      <p class="ub-eyebrow">Reportes · Ventas</p>
      <h1 class="ub-title">Utilidad Bruta de Ventas</h1>
    </div>

    <!-- Filtros -->
    <div class="ub-filters">
      <div class="ub-filter-group">
        <label class="ub-label">Desde</label>
        <input type="date" v-model="fechaInicio" class="ub-input" />
      </div>
      <div class="ub-filter-group">
        <label class="ub-label">Hasta</label>
        <input type="date" v-model="fechaFin" class="ub-input" />
      </div>
      <div class="ub-filter-group">
        <label class="ub-label">Usuario</label>
        <b-select
          :options="lstUsuarioConTodos"
          value-field="Usuario"
          text-field="NombreUsuario"
          v-model="usuarioReg"
          class="ub-input"
        />
      </div>
      <div class="ub-filter-group">
        <label class="ub-label">Marca</label>
        <b-select
          :options="lstMarcaConTodos"
          value-field="IdProductoMarca"
          text-field="ProductoMarca"
          v-model="marcaReg"
          class="ub-input"
        />
      </div>
      <div class="ub-filter-group">
        <label class="ub-label">Categoría</label>
        <b-select
          :options="lstCategoriaConTodos"
          value-field="IdProductoCategoria"
          text-field="ProductoCategoria"
          v-model="categoriaReg"
          class="ub-input"
        />
      </div>
      <b-col cols="2" class="pt-4">
        <b-form-checkbox v-model="agrupado" switch>
            Agrupar por producto
        </b-form-checkbox>
      </b-col>
      <div class="ub-filter-group ub-filter-action">
        <label class="ub-label ub-label-hidden">&nbsp;</label>
        <button class="ub-btn-generate" @click="getReporteUtilidadPorUsuario" :disabled="isLoading">
          <span v-if="!isLoading">Generar reporte</span>
          <span v-else class="ub-spinner-inline" aria-label="Cargando"></span>
        </button>
      </div>
      <div class="ub-filter-group ub-filter-action">
        <label class="ub-label ub-label-hidden">&nbsp;</label>
        <button class="ub-btn-export ub-btn-excel" @click="exportarExcel" :disabled="!lstReporte.length || isExporting">
          <span v-if="!isExporting">Exportar Excel</span>
          <span v-else class="ub-spinner-inline" aria-label="Exportando"></span>
        </button>
      </div>
      <div class="ub-filter-group ub-filter-action">
        <label class="ub-label ub-label-hidden">&nbsp;</label>
        <button class="ub-btn-export ub-btn-pdf" @click="exportarPDF" :disabled="!lstReporte.length || isExporting">
          <span v-if="!isExporting">Exportar PDF</span>
          <span v-else class="ub-spinner-inline" aria-label="Exportando"></span>
        </button>
      </div>
    </div>

    <!-- KPIs -->
    <div class="ub-kpis" v-if="lstReporte.length">
      <div class="ub-kpi">
        <p class="ub-kpi-label">Total venta</p>
        <p class="ub-kpi-value">S/ {{ formatNum(totalVenta) }}</p>
      </div>
      <div class="ub-kpi">
        <p class="ub-kpi-label">Total costo</p>
        <p class="ub-kpi-value ub-kpi-cost">S/ {{ formatNum(totalCosto) }}</p>
      </div>
      <div class="ub-kpi ub-kpi-highlight">
        <p class="ub-kpi-label">Utilidad bruta</p>
        <p class="ub-kpi-value">S/ {{ formatNum(utilidadBruta) }}</p>
      </div>
      <div class="ub-kpi">
        <p class="ub-kpi-label">Margen promedio</p>
        <p class="ub-kpi-value">{{ margenPromedio }}%</p>
      </div>
    </div>

    <!-- Tabla -->
    <div class="ub-table-card">
      <div v-if="isLoading" class="ub-state">
        <div class="ub-spinner"></div>
        <p>Calculando utilidad…</p>
      </div>

      <div v-else-if="!lstReporte.length" class="ub-state ub-state-empty">
        <p class="ub-state-title">Sin datos todavía</p>
        <p class="ub-state-text">
          Elige un rango de fechas y presiona “Generar reporte” para ver el detalle por producto.
        </p>
      </div>

      <table v-else class="ub-table">
        <thead>
          <tr>
            <th>Id</th>
            <th>Producto</th>
            <th class="ub-num">Cantidad</th>
            <th class="ub-num">Costo prom.</th>
            <th class="ub-num">Venta prom.</th>
            <th class="ub-num">Total venta</th>
            <th class="ub-num">Total costo</th>
            <th class="ub-num">Utilidad</th>
            <th class="ub-margin-col">Margen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(reporte, index) in lstReporte" :key="index">
            <td class="ub-id">{{ reporte.IdProducto }}</td>
            <td class="ub-product">{{ reporte.Producto }}</td>
            <td class="ub-num">
                <span v-if="agrupadoAplicado || reporte.CantidadPresentacionGrupo <= 1">
                  {{ reporte.Cantidad }}
                </span>
                <span v-else>
                  {{ reporte.CantidadVenta }} ({{ reporte.Cantidad }} unid.)
                </span>
              </td>
            <td class="ub-num">S/ {{ formatNum(reporte.PrecioCosto) }}</td>
            <td class="ub-num">S/ {{ formatNum(reporte.PrecioVenta) }}</td>
            <td class="ub-num">S/ {{ formatNum(reporte.TotalVenta) }}</td>
            <td class="ub-num">S/ {{ formatNum(reporte.TotalCosto) }}</td>
            <td class="ub-num ub-utility">S/ {{ formatNum(reporte.UtilidadBruta) }}</td>
            <td class="ub-margin-col">
              <div class="ub-margin-track">
                <div
                  class="ub-margin-fill"
                  :style="{ width: margenPct(reporte) + '%', background: margenColor(reporte) }"
                ></div>
              </div>
              <span class="ub-margin-pct">{{ margenPct(reporte) }}%</span>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5" class="ub-total-label">Sumatoria</td>
            <td class="ub-num ub-total-value sumTotalVenta">S/ {{ formatNum(totalVenta) }}</td>
            <td class="ub-num ub-total-value sumTotalCosto">S/ {{ formatNum(totalCosto) }}</td>
            <td class="ub-num ub-total-value sumUtilidadBruta">S/ {{ formatNum(utilidadBruta) }}</td>
            <td class="ub-total-value">{{ margenPromedio }}%</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</template>

<script>
import config from "@/store/config.js";

export default {
  data() {
    return {
      lstReporte: [],
      API: config.API,
      fechaInicio: "",
      fechaFin: "",
      usuarioReg: "",
      marcaReg: "",
      categoriaReg: "",
      lstUsuario: [],
      lstMarca: [],
      lstCategoria: [],
      agrupado: false,
      agrupadoAplicado: false,
      isLoading: false,
      isExporting: false
    };
  },
  computed: {
    totalVenta() {
      return this.lstReporte.reduce((sum, r) => sum + parseFloat(r.TotalVenta || 0), 0);
    },
    totalCosto() {
      return this.lstReporte.reduce((sum, r) => sum + parseFloat(r.TotalCosto || 0), 0);
    },
    utilidadBruta() {
      return this.lstReporte.reduce((sum, r) => sum + parseFloat(r.UtilidadBruta || 0), 0);
    },
    margenPromedio() {
      if (!this.totalVenta) return "0.0";
      return ((this.utilidadBruta / this.totalVenta) * 100).toFixed(1);
    },
    lstUsuarioConTodos() {
      return [
        { Usuario: '', NombreUsuario: '— Todos —' },
        ...this.lstUsuario
      ];
    },
    lstMarcaConTodos() {
      return [
        { IdProductoMarca: '', ProductoMarca: '— Todas —' },
        ...this.lstMarca
      ];
    },
    lstCategoriaConTodos() {
      return [
        { IdProductoCategoria: '', ProductoCategoria: '— Todas —' },
        ...this.lstCategoria
      ];
    }
  },
  mounted() {
    this.cargarUsuarios();
    this.cargarMarcas();
    this.cargarCategorias();
  },
  methods: {
    formatNum(n) {
      return parseFloat(n || 0).toLocaleString("es-PE", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    margenPct(reporte) {
      const venta = parseFloat(reporte.TotalVenta || 0);
      const utilidad = parseFloat(reporte.UtilidadBruta || 0);
      if (!venta) return "0.0";
      return ((utilidad / venta) * 100).toFixed(1);
    },
    margenColor(reporte) {
      const pct = parseFloat(this.margenPct(reporte));
      if (pct < 10) return "#C2410C"; // margen bajo
      if (pct < 25) return "#C88A1F"; // margen medio
      return "#1F7A6C"; // margen saludable
    },
    async cargarUsuarios() {
      await this.axios.get(`${this.API}usuarios`).then(response => {
        if (response.data) {
          this.lstUsuario = response.data;
        }
      });
    },
    async cargarMarcas() {
      await this.axios.get(`${this.API}marcas`).then(response => {
        if (response.data) {
          this.lstMarca = response.data;
        }
      });
    },
    async cargarCategorias() {
      // El endpoint /categorias filtra por 'q' y pagina con 'limit',
      // así que pedimos un límite alto para traer el listado completo
      await this.axios.get(`${this.API}categorias`, { params: { limit: 1000 } }).then(response => {
        if (response.data) {
          this.lstCategoria = response.data;
        }
      });
    },
    async exportarExcel() {
      this.isExporting = true;
      try {
        const filtro = {
          params: {
            fechaInicio: this.fechaInicio,
            fechaFin: this.fechaFin,
            usuarioReg: this.usuarioReg,
            marcaReg: this.marcaReg,
            categoriaReg: this.categoriaReg,
            agrupado: this.agrupado ? 1 : 0
          }
        };
        const response = await this.axios.get(`${this.API}reporte/utlidadbruta/usuario/excel`, filtro);
        window.open(response.data, '_blank');
      } catch (e) {
        console.error(e);
      } finally {
        this.isExporting = false;
      }
    },
    async exportarPDF() {
      this.isExporting = true;
      try {
        const filtro = {
          params: {
            fechaInicio: this.fechaInicio,
            fechaFin: this.fechaFin,
            usuarioReg: this.usuarioReg,
            marcaReg: this.marcaReg,
            categoriaReg: this.categoriaReg,
            agrupado: this.agrupado ? 1 : 0
          }
        };
        const response = await this.axios.get(`${this.API}reporte/utlidadbruta/usuario/pdf`, filtro);
        window.open(response.data.url, '_blank');
      } catch (e) {
        console.error(e);
      } finally {
        this.isExporting = false;
      }
    },
    async getReporteUtilidadPorUsuario() {
      let filtro = {
        params: {
          fechaInicio: this.fechaInicio,
          fechaFin: this.fechaFin,
          usuarioReg: this.usuarioReg,
          marcaReg: this.marcaReg,
          categoriaReg: this.categoriaReg,
          agrupado: this.agrupado ? 1 : 0
        }
      };
      this.isLoading = true;
      await this.axios
        .get(`${this.API}reporte/utlidadbruta/usuario`, filtro)
        .then(response => {
          if (response.data) {
            this.lstReporte = response.data;
            this.agrupadoAplicado = this.agrupado; // recién aquí se refleja el cambio visual
          }
        })
        .finally(() => {
          this.isLoading = false;
        });
    }
  }
};
</script>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@500&display=swap");

.ub-page {
  --ub-navy: #16233f;
  --ub-navy-soft: #2c3a5c;
  --ub-teal: #1f7a6c;
  --ub-teal-soft: #e6f2f0;
  --ub-amber: #c2760c;
  --ub-bg: #f5f6f8;
  --ub-card: #ffffff;
  --ub-border: #e2e5eb;
  --ub-text: #1c2333;
  --ub-text-soft: #6b7280;

  font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
  color: var(--ub-text);
  background: var(--ub-bg);
  padding: 2.25rem 2rem 3rem;
  min-height: 100%;
}

/* ---------- Hero ---------- */
.ub-hero {
  margin-bottom: 1.75rem;
}
.ub-eyebrow {
  font-size: 0.72rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ub-teal);
  font-weight: 600;
  margin: 0 0 0.35rem;
}
.ub-title {
  font-family: "Fraunces", Georgia, serif;
  font-weight: 600;
  font-size: 2.1rem;
  color: var(--ub-navy);
  margin: 0 0 0.4rem;
  letter-spacing: -0.01em;
}
.ub-subtitle {
  color: var(--ub-text-soft);
  font-size: 0.95rem;
  margin: 0;
  max-width: 46rem;
}

/* ---------- Filtros ---------- */
.ub-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 1rem;
  background: var(--ub-card);
  border: 1px solid var(--ub-border);
  border-radius: 12px;
  padding: 1.1rem 1.25rem;
  margin-bottom: 1.5rem;
  position: sticky;
  top: 0.5rem;
  z-index: 5;
  box-shadow: 0 1px 2px rgba(22, 35, 63, 0.04);
}
.ub-filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex: 1 1 150px;
  min-width: 140px;
}
.ub-filter-action {
  flex: 0 0 auto;
  min-width: 160px;
}
.ub-label {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--ub-text-soft);
}
.ub-label-hidden {
  visibility: hidden;
}
.ub-input,
.ub-input select,
.ub-page select.ub-input {
  border: 1px solid var(--ub-border);
  border-radius: 8px;
  padding: 0.5rem 0.65rem;
  font-size: 0.88rem;
  font-family: inherit;
  color: var(--ub-text);
  background: #fbfbfc;
  width: 100%;
  transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.ub-input:focus,
.ub-input:focus-visible {
  outline: none;
  border-color: var(--ub-teal);
  box-shadow: 0 0 0 3px var(--ub-teal-soft);
}

.ub-btn-generate {
  background: var(--ub-navy);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 0.62rem 1.25rem;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2.5rem;
  transition: background 0.15s ease, transform 0.1s ease;
}
.ub-btn-generate:hover:not(:disabled) {
  background: var(--ub-teal);
}
.ub-btn-generate:active:not(:disabled) {
  transform: translateY(1px);
}
.ub-btn-generate:disabled {
  opacity: 0.7;
  cursor: default;
}
.ub-btn-generate:focus-visible {
  outline: 2px solid var(--ub-teal);
  outline-offset: 2px;
}
.ub-spinner-inline {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-top-color: #fff;
  border-radius: 50%;
  animation: ub-spin 0.7s linear infinite;
}

/* ---------- KPIs ---------- */
.ub-kpis {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.ub-kpi {
  background: var(--ub-card);
  border: 1px solid var(--ub-border);
  border-radius: 12px;
  padding: 1rem 1.15rem;
}
.ub-kpi-highlight {
  background: var(--ub-navy);
  border-color: var(--ub-navy);
}
.ub-kpi-highlight .ub-kpi-label {
  color: rgba(255, 255, 255, 0.65);
}
.ub-kpi-highlight .ub-kpi-value {
  color: #fff;
}
.ub-kpi-label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--ub-text-soft);
  margin: 0 0 0.3rem;
}
.ub-kpi-value {
  font-family: "IBM Plex Mono", monospace;
  font-size: 1.4rem;
  font-weight: 500;
  color: var(--ub-navy);
  margin: 0;
  font-variant-numeric: tabular-nums;
}
.ub-kpi-cost {
  color: var(--ub-amber);
}

/* ---------- Tabla ---------- */
.ub-table-card {
  background: var(--ub-card);
  border: 1px solid var(--ub-border);
  border-radius: 12px;
  overflow: hidden;
}
.ub-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.87rem;
}
.ub-table thead th {
  position: sticky;
  top: 0;
  background: var(--ub-navy);
  color: rgba(255, 255, 255, 0.92);
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-weight: 600;
  text-align: left;
  padding: 0.75rem 0.9rem;
  z-index: 1;
}
.ub-table tbody td {
  padding: 0.65rem 0.9rem;
  border-bottom: 1px solid var(--ub-border);
  color: var(--ub-text);
}
.ub-table tbody tr:nth-child(even) {
  background: #fafbfc;
}
.ub-table tbody tr:hover {
  background: var(--ub-teal-soft);
}
.ub-id {
  color: var(--ub-text-soft);
  font-family: "IBM Plex Mono", monospace;
  font-size: 0.8rem;
}
.ub-product {
  font-weight: 500;
}
.ub-num {
  text-align: right;
  font-family: "IBM Plex Mono", monospace;
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
}
.ub-utility {
  color: var(--ub-teal);
  font-weight: 600;
}
.ub-margin-col {
  min-width: 130px;
}
.ub-margin-track {
  background: #edeff2;
  border-radius: 999px;
  height: 6px;
  width: 100%;
  overflow: hidden;
  margin-bottom: 0.25rem;
}
.ub-margin-fill {
  height: 100%;
  border-radius: 999px;
  transition: width 0.3s ease;
}
.ub-margin-pct {
  font-family: "IBM Plex Mono", monospace;
  font-size: 0.75rem;
  color: var(--ub-text-soft);
}

.ub-table tfoot td {
  padding: 0.8rem 0.9rem;
  border-top: 2px solid var(--ub-navy);
  background: #f2f4f7;
}
.ub-total-label {
  font-weight: 600;
  color: var(--ub-navy);
  text-transform: uppercase;
  font-size: 0.72rem;
  letter-spacing: 0.06em;
}
.ub-total-value {
  font-family: "IBM Plex Mono", monospace;
  font-weight: 600;
  text-align: right;
  color: var(--ub-navy);
  font-variant-numeric: tabular-nums;
}

/* ---------- Estados ---------- */
.ub-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
  padding: 3.5rem 1rem;
  color: var(--ub-text-soft);
}
.ub-state-title {
  font-family: "Fraunces", serif;
  font-size: 1.1rem;
  color: var(--ub-navy);
  margin: 0;
}
.ub-state-text {
  margin: 0;
  font-size: 0.9rem;
  max-width: 26rem;
  text-align: center;
}
.ub-spinner {
  width: 28px;
  height: 28px;
  border: 3px solid var(--ub-border);
  border-top-color: var(--ub-teal);
  border-radius: 50%;
  animation: ub-spin 0.8s linear infinite;
}
@keyframes ub-spin {
  to {
    transform: rotate(360deg);
  }
}

/* ---------- Exportacion ---------- */

.ub-btn-export {
  border: none;
  border-radius: 8px;
  padding: 0.62rem 1.25rem;
  font-size: 0.88rem;
  font-weight: 600;
  cursor: pointer;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 2.5rem;
  color: #fff;
  transition: background 0.15s ease, transform 0.1s ease, opacity 0.15s ease;
}
.ub-btn-export:active:not(:disabled) {
  transform: translateY(1px);
}
.ub-btn-export:disabled {
  opacity: 0.5;
  cursor: default;
}
.ub-btn-export:focus-visible {
  outline: 2px solid var(--ub-teal);
  outline-offset: 2px;
}
.ub-btn-excel {
  background: var(--ub-teal);
}
.ub-btn-excel:hover:not(:disabled) {
  background: #175f54;
}
.ub-btn-pdf {
  background: var(--ub-amber);
}
.ub-btn-pdf:hover:not(:disabled) {
  background: #a3630a;
}

/* ---------- Responsive ---------- */
@media (max-width: 900px) {
  .ub-kpis {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }
  .ub-filters {
    position: static;
  }
}
@media (max-width: 560px) {
  .ub-page {
    padding: 1.5rem 1rem 2rem;
  }
  .ub-kpis {
    grid-template-columns: 1fr;
  }
  .ub-table {
    font-size: 0.8rem;
  }
}

@media (prefers-reduced-motion: reduce) {
  .ub-spinner,
  .ub-spinner-inline,
  .ub-margin-fill {
    animation: none;
    transition: none;
  }
}
</style>