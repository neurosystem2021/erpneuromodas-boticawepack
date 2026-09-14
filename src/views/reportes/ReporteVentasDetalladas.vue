<template>
  <b-container fluid class="px-5 mb-5 rvd-page">
    <b-row>
      <!-- <b-col cols="12">
        <b-breadcrumb :items="items" class="rvd-breadcrumb"></b-breadcrumb>
      </b-col> -->



      <!-- Panel de filtros -->
      <b-col cols="12">
        <div class="rvd-filters-card">
          <div class="rvd-filters-grid">
            <div class="rvd-field">
              <label>Fecha Inicio</label>
              <input type="date" class="form-control" v-model="fechaInicio" />
            </div>
            <div class="rvd-field">
              <label>Fecha Fin</label>
              <input type="date" class="form-control" v-model="fechaFin" />
            </div>
            <div class="rvd-field">
              <label>Local</label>
              <b-form-select
                v-model="almacen"
                value-field="IdAlmacen"
                text-field="Almacen"
                :options="lstAlmacenes"
              ></b-form-select>
            </div>
            <div class="rvd-field">
              <label>Turno</label>
              <b-form-select
                v-model="turno"
                value-field="value"
                text-field="text"
                :options="lstTurnos"
              ></b-form-select>
            </div>
            <div class="rvd-field">
              <label>Cajero</label>
              <b-form-select
                v-model="cajero"
                value-field="UsuarioReg"
                text-field="UsuarioReg"
                :options="lstCajeros"
              ></b-form-select>
            </div>
            <div class="rvd-field">
              <label>Categoría</label>
              <b-form-select
                v-model="categoria"
                value-field="IdProductoCategoria"
                text-field="ProductoCategoria"
                :options="lstCategorias"
              ></b-form-select>
            </div>
            <div class="rvd-field">
              <label>Medio de Pago</label>
              <b-form-select
                v-model="medioPago"
                value-field="IdMetodoPago"
                text-field="MetodoPago"
                :options="lstMediosPago"
              ></b-form-select>
            </div>
            <div class="rvd-field">
              <label>Estado</label>
              <b-form-select
                v-model="estado"
                value-field="value"
                text-field="text"
                :options="lstEstados"
              ></b-form-select>
            </div>
          </div>

          <div class="rvd-actions">
            <b-button variant="info" class="rvd-btn" @click="generarReporte()" :disabled="cargando">
              <i class="fa fa-search" aria-hidden="true"></i>
              {{ cargando ? "Generando..." : "Generar" }}
            </b-button>
            <b-button variant="success" class="rvd-btn" @click="exportarReporte()" :disabled="cargando || lstReporte.length === 0">
              <i class="fa fa-file-excel" aria-hidden="true"></i> Exportar
            </b-button>
          </div>
        </div>
      </b-col>
      <!-- Resumen rápido -->
      <b-col cols="12" v-if="lstReporte.length > 0">
        <div class="rvd-summary">
          <div class="rvd-summary-item">
            <span class="rvd-summary-label">Cantidad de Ventas</span>
            <span class="rvd-summary-value">{{ lstReporte.length }}</span>
          </div>
          <div class="rvd-summary-item">
            <span class="rvd-summary-label">Unidades</span>
            <span class="rvd-summary-value">{{ footer.Cantidad }}</span>
          </div>
          <div class="rvd-summary-item">
            <span class="rvd-summary-label">Descuentos</span>
            <span class="rvd-summary-value">S/ {{ footer.Descuento }}</span>
          </div>
          <div class="rvd-summary-item rvd-summary-highlight">
            <span class="rvd-summary-label">Total vendido</span>
            <span class="rvd-summary-value">S/ {{ footer.ImporteVendido }}</span>
          </div>
          <div class="rvd-summary-item">
            <span class="rvd-summary-label">Costo total</span>
            <span class="rvd-summary-value">S/ {{ footer.CostoTotal }}</span>
          </div>
        </div>
      </b-col>
      <!-- Tabla -->
      <b-col cols="12" class="mt-3 rvd-table-container">
        <div class="rvd-table-scroll">
          <table class="rvd-table">
            <thead>
              <tr>
                <th>Fecha y Hora</th>
                <th>Local</th>
                <th>Turno</th>
                <th>Cajero</th>
                <th>Ticket</th>
                <th>SKU/Código</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="text-center">Cantidad</th>
                <th class="text-right">Precio</th>
                <th class="text-right">Descuento</th>
                <th class="text-right">Importe Vendido</th>
                <th class="text-right">Costo Unitario</th>
                <th>Medio de Pago</th>
                <th class="text-center">Estado</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="cargando">
                <td colspan="15" class="rvd-empty">Cargando resultados…</td>
              </tr>
              <tr v-else-if="lstReporte.length === 0">
                <td colspan="15" class="rvd-empty">No hay ventas para los filtros seleccionados.</td>
              </tr>
              <tr
                v-for="(reg, index) in filasConGrupo"
                :key="index"
                :class="{ 'rvd-row-anulado': reg.Anulado == 1, 'rvd-venta-inicio': reg._esPrimeraDeVenta && index !== 0 }"
              >
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="text-center rvd-nowrap rvd-celda-venta">{{ reg.FechaDoc }}</td>
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="rvd-celda-venta">{{ reg.Almacen }}</td>
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="text-center rvd-celda-venta">
                  <span class="rvd-badge" :class="claseTurno(reg.Turno)">{{ reg.Turno }}</span>
                </td>
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="rvd-celda-venta">{{ reg.Cajero }}</td>
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="text-center rvd-nowrap rvd-celda-venta">{{ reg.Ticket }}</td>
                <td class="text-center rvd-mono">{{ reg.Sku }}</td>
                <td>{{ reg.Producto }}</td>
                <td>{{ reg.Categoria }}</td>
                <td class="text-center">{{ reg.Cantidad }}</td>
                <td class="text-right">{{ formatoMoneda(reg.Precio) }}</td>
                <td class="text-right">{{ formatoMoneda(reg.Descuento) }}</td>
                <td class="text-right rvd-importe">{{ formatoMoneda(reg.ImporteVendido) }}</td>
                <td class="text-right">{{ formatoMoneda(reg.CostoUnitario) }}</td>
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="rvd-celda-venta">
                  <span class="rvd-badge" :class="claseMedioPago(reg.MedioPago)">
                    {{ etiquetaMedioPago(reg.MedioPago) }}
                  </span>
                </td>
                <td v-if="reg._esPrimeraDeVenta" :rowspan="reg._rowspanVenta" class="text-center rvd-celda-venta">
                  <span class="rvd-badge" :class="reg.Anulado == 1 ? 'rvd-badge-anulado' : 'rvd-badge-activo'">
                    {{ reg.Anulado == 1 ? "Anulado" : "Activo" }}
                  </span>
                </td>
              </tr>
            </tbody>
            <tfoot v-if="lstReporte.length > 0">
              <tr>
                <td colspan="8">Total</td>
                <td class="text-center">{{ footer.Cantidad }}</td>
                <td></td>
                <td class="text-right">{{ footer.Descuento }}</td>
                <td class="text-right">{{ footer.ImporteVendido }}</td>
                <td class="text-right">{{ footer.CostoTotal }}</td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      fechaInicio: "",
      fechaFin: "",
      almacen: 0,
      turno: 0,
      cajero: "TODOS",
      categoria: 0,
      medioPago: 0,
      estado: -1,
      cargando: false,
      API: config.API,
      lstReporte: [],
      title: "Ventas Detalladas",
      items: [
        { text: "VENTAS", href: "#" },
        { text: "REPORTE", href: "#" },
        { text: "VENTAS DETALLADAS", active: true }
      ],
      lstAlmacenes: [],
      lstCajeros: [],
      lstCategorias: [],
      lstMediosPago: [],
      lstTurnos: [
        { value: 0, text: "TODOS" },
        { value: 1, text: "Mañana" },
        { value: 2, text: "Tarde" },
        { value: 3, text: "Noche" }
      ],
      lstEstados: [
        { value: -1, text: "TODOS" },
        { value: 0, text: "Activos" },
        { value: 1, text: "Anulados" }
      ],
      footer: {
        Cantidad: 0,
        Descuento: 0,
        ImporteVendido: 0,
        CostoTotal: 0
      }
    };
  },
  mounted() {
    this.fechaInicio = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.fechaFin = this.$moment().endOf("month").format("YYYY-MM-DD");
    this.obtenerAlmacenes();
    this.obtenerCategorias();
    this.obtenerMediosPago();
    this.obtenerCajeros();
    this.generarReporte();
  },
  computed: {
    // Agrupa las líneas de lstReporte por idDocVenta (deben venir contiguas,
    // el backend ya ordena por FechaDoc + idDocVenta para garantizarlo) y
    // calcula cuántas filas debe abarcar el rowspan de cada venta.
    filasConGrupo() {
      const filas = [];
      let i = 0;
      while (i < this.lstReporte.length) {
        const idVenta = this.lstReporte[i].idDocVenta;
        let j = i;
        while (j < this.lstReporte.length && this.lstReporte[j].idDocVenta === idVenta) {
          j++;
        }
        const rowspan = j - i;
        for (let k = i; k < j; k++) {
          filas.push({
            ...this.lstReporte[k],
            _esPrimeraDeVenta: k === i,
            _rowspanVenta: rowspan
          });
        }
        i = j;
      }
      return filas;
    }
  },
  methods: {
    async obtenerAlmacenes() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmacenes = res.data;
          this.lstAlmacenes.unshift({ Almacen: "TODOS", IdAlmacen: 0 });
        }
      });
    },
    async obtenerCategorias() {
      await this.axios(`${this.API}reporte/VentasDetalladas/categorias`).then(res => {
        if (res.data) {
          this.lstCategorias = res.data;
          this.lstCategorias.unshift({
            ProductoCategoria: "TODAS",
            IdProductoCategoria: 0
          });
        }
      });
    },
    async obtenerMediosPago() {
      await this.axios(`${this.API}reporte/VentasDetalladas/mediospago`).then(res => {
        if (res.data) {
          this.lstMediosPago = res.data;
          this.lstMediosPago.unshift({ MetodoPago: "TODOS", IdMetodoPago: 0 });
        }
      });
    },
    async obtenerCajeros() {
      await this.axios(`${this.API}reporte/VentasDetalladas/cajeros`).then(res => {
        if (res.data) {
          this.lstCajeros = res.data;
          this.lstCajeros.unshift({ UsuarioReg: "TODOS" });
        }
      });
    },
    armarParams() {
      return {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        almacen: this.almacen,
        turno: this.turno,
        cajero: this.cajero === "TODOS" ? "" : this.cajero,
        categoria: this.categoria,
        medioPago: this.medioPago,
        estado: this.estado
      };
    },
    async generarReporte() {
      this.cargando = true;
      this.lstReporte = [];
      try {
        const res = await this.axios(`${this.API}reporte/VentasDetalladas`, {
          params: this.armarParams()
        });
        if (res.data) {
          // Object.freeze evita que Vue 2 recorra y convierta en reactiva
          // cada fila/campo del array — con reportes de miles de filas esto
          // es lo que realmente causa la demora "invisible" tras la petición.
          this.lstReporte = Object.freeze(res.data);
          this.calcularFooter();
        }
      } catch (e) {
        console.error(e);
      } finally {
        this.cargando = false;
      }
    },
    calcularFooter() {
      let Cantidad = 0;
      let Descuento = 0;
      let ImporteVendido = 0;
      let CostoTotal = 0;
      this.lstReporte.forEach(r => {
        Cantidad += Number(r.Cantidad) || 0;
        Descuento += Number(r.Descuento) || 0;
        ImporteVendido += Number(r.ImporteVendido) || 0;
        CostoTotal += (Number(r.CostoUnitario) || 0) * (Number(r.Cantidad) || 0);
      });
      this.footer = {
        Cantidad: parseFloat(Cantidad).toFixed(2),
        Descuento: parseFloat(Descuento).toFixed(2),
        ImporteVendido: parseFloat(ImporteVendido).toFixed(2),
        CostoTotal: parseFloat(CostoTotal).toFixed(2)
      };
    },
    async exportarReporte() {
      await this.axios(`${this.API}reporte/VentasDetalladas/excel`, {
        params: this.armarParams()
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url;
        }
        window.open(url, "_blank");
      });
    },
    formatoMoneda(valor) {
      const num = Number(valor) || 0;
      return num.toFixed(2);
    },
    // Un MedioPago vacío significa que la venta fue a crédito (sin pago registrado aún)
    etiquetaMedioPago(medioPago) {
      return medioPago && medioPago.trim() !== "" ? medioPago : "A crédito";
    },
    claseMedioPago(medioPago) {
      if (!medioPago || medioPago.trim() === "") {
        return "rvd-badge-credito";
      }
      const texto = medioPago.toLowerCase();
      if (texto.includes("efectivo")) return "rvd-badge-efectivo";
      if (texto.includes("yape") || texto.includes("plin")) return "rvd-badge-digital";
      return "rvd-badge-tarjeta";
    },
    claseTurno(turno) {
      if (turno === "Mañana") return "rvd-badge-manana";
      if (turno === "Tarde") return "rvd-badge-tarde";
      if (turno === "Noche") return "rvd-badge-noche";
      return "rvd-badge-neutro";
    }
  }
};
</script>

<style scoped>
.rvd-page {
  --rvd-primary: #006064;
  --rvd-primary-dark: #004d51;
  --rvd-accent: #00acc1;
  --rvd-bg: #f4f7f8;
  --rvd-ink: #22313a;
  --rvd-muted: #6b7c86;
  --rvd-border: #e1e7ea;
  color: var(--rvd-ink);
}

.rvd-breadcrumb {
  background: transparent;
  padding-left: 0;
  font-size: 0.85rem;
}

/* ---------- Filtros ---------- */
.rvd-filters-card {
  background: #ffffff;
  border: 1px solid var(--rvd-border);
  border-radius: 10px;
  padding: 20px 24px;
  box-shadow: 0 1px 3px rgba(15, 40, 45, 0.06);
  margin-bottom: 18px;
}

.rvd-filters-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(160px, 1fr));
  gap: 16px 20px;
}

.rvd-field label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--rvd-muted);
  margin-bottom: 4px;
  letter-spacing: 0.01em;
}

.rvd-field .form-control,
.rvd-field select {
  border: 1px solid var(--rvd-border);
  border-radius: 6px;
}

.rvd-field .form-control:focus,
.rvd-field select:focus {
  border-color: var(--rvd-accent);
  box-shadow: 0 0 0 3px rgba(0, 172, 193, 0.15);
}

.rvd-actions {
  display: flex;
  gap: 10px;
  margin-top: 18px;
}

.rvd-btn {
  border-radius: 6px;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* ---------- Resumen ---------- */
.rvd-summary {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 12px;
  margin-bottom: 18px;
}

.rvd-summary-item {
  background: #ffffff;
  border: 1px solid var(--rvd-border);
  border-radius: 8px;
  padding: 14px 18px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.rvd-summary-highlight {
  border-color: var(--rvd-primary);
  background: #e8f5f6;
}

.rvd-summary-label {
  font-size: 0.72rem;
  color: var(--rvd-muted);
  font-weight: 600;
}

.rvd-summary-value {
  font-size: 1.3rem;
  font-weight: 700;
  color: var(--rvd-primary-dark);
}

/* ---------- Tabla ---------- */
.rvd-table-container {
  background: #ffffff;
  border: 1px solid var(--rvd-border);
  border-radius: 10px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(15, 40, 45, 0.06);
}

.rvd-table-scroll {
  max-height: 640px;
  overflow: auto;
}

.rvd-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.86rem;
}

.rvd-table thead th {
  position: sticky;
  top: 0;
  background: #f1f5f6;
  color: var(--rvd-ink);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  padding: 10px 12px;
  border-bottom: 2px solid var(--rvd-border);
  white-space: nowrap;
  z-index: 2;
}

.rvd-table tbody td {
  padding: 9px 12px;
  border-bottom: 1px solid var(--rvd-border);
  vertical-align: middle;
}

.rvd-table tbody tr:hover {
  background: #f5fbfb;
}

.rvd-table tbody tr:nth-child(even) {
  background: #fafcfc;
}

.rvd-table tbody tr:nth-child(even):hover {
  background: #f5fbfb;
}

.rvd-row-anulado {
  background: #fdecea !important;
}

.rvd-row-anulado:hover {
  background: #fbe0dd !important;
}

/* Celdas a nivel de venta (con rowspan): se centran verticalmente y llevan
   un separador a la derecha para distinguirlas del detalle de productos */
.rvd-celda-venta {
  vertical-align: middle !important;
  border-right: 1px solid var(--rvd-border);
  background: #fcfdfd;
}

/* Línea divisoria más marcada al iniciar una nueva venta */
.rvd-venta-inicio td {
  border-top: 2px solid #c7d1d4;
}

.rvd-nowrap {
  white-space: nowrap;
}

.rvd-mono {
  font-variant-numeric: tabular-nums;
}

.rvd-importe {
  font-weight: 600;
  color: var(--rvd-primary-dark);
}

.rvd-empty {
  text-align: center;
  padding: 32px 12px;
  color: var(--rvd-muted);
}

.rvd-table tfoot td {
  position: sticky;
  bottom: 0;
  background: var(--rvd-primary);
  color: #ffffff;
  font-weight: 700;
  padding: 10px 12px;
  z-index: 2;
}

/* ---------- Badges ---------- */
.rvd-badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 20px;
  font-size: 0.74rem;
  font-weight: 600;
  white-space: nowrap;
}

.rvd-badge-activo {
  background: #e3f4e6;
  color: #2e7d32;
}

.rvd-badge-anulado {
  background: #fbe3e1;
  color: #c62828;
}

.rvd-badge-manana {
  background: #fff3e0;
  color: #ef6c00;
}

.rvd-badge-tarde {
  background: #e3f0fd;
  color: #1565c0;
}

.rvd-badge-noche {
  background: #eae6fb;
  color: #4527a0;
}

.rvd-badge-neutro {
  background: #eceff1;
  color: #546e7a;
}

.rvd-badge-efectivo {
  background: #e3f4e6;
  color: #2e7d32;
}

.rvd-badge-tarjeta {
  background: #e3f0fd;
  color: #1565c0;
}

.rvd-badge-digital {
  background: #f3e5f5;
  color: #7b1fa2;
}

.rvd-badge-credito {
  background: #fff3e0;
  color: #e65100;
  border: 1px dashed #e65100;
}

@media (max-width: 992px) {
  .rvd-filters-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .rvd-summary {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 576px) {
  .rvd-filters-grid {
    grid-template-columns: 1fr;
  }
  .rvd-summary {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>