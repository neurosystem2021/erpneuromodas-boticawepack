<template>
  <b-container fluid
    class="px-4 mb-4">
    <b-row>
      <b-col cols="12">
        <b-breadcrumb :items="items"></b-breadcrumb>
      </b-col>

      <b-col md="3"
        class="mb-2">
        <label>Fecha desde</label>
        <input type="date"
          class="form-control"
          v-model="fechaDesde" />
      </b-col>

      <b-col md="3"
        class="mb-2">
        <label>Fecha hasta</label>
        <input type="date"
          class="form-control"
          v-model="fechaHasta" />
      </b-col>

      <b-col md="3"
        class="mb-2">
        <label>Almacén</label>
        <b-form-select v-model="idAlmacen"
          :options="lstAlmacenes"
          value-field="IdAlmacen"
          text-field="Almacen"></b-form-select>
      </b-col>

      <b-col md="3"
        class="mb-2">
        <label>Buscar producto / lote</label>
        <b-form-input v-model="q"
          placeholder="Producto, código, lote..."
          @keyup.enter="cargarReporte"></b-form-input>
      </b-col>

      <b-col md="3"
        class="mb-3">
        <label>Estado</label>
        <b-form-checkbox v-model="mostrarVencidos">
          Ver vencidos (desmarcado: por vencer)
        </b-form-checkbox>
      </b-col>

      <b-col md="3"
        class="mb-3">
        <label>Mostrar</label>
        <b-form-select v-model="perPage"
          :options="pageOptions"></b-form-select>
      </b-col>

      <b-col md="6"
        class="mb-3 d-flex align-items-end">
        <b-button variant="info"
          class="mr-2"
          :disabled="loading || exporting"
          @click="cargarReporte">
          <i class="fa fa-search"
            aria-hidden="true"></i> Generar
        </b-button>
        <b-button variant="success"
          class="mr-2"
          :disabled="loading || exporting"
          @click="exportarReporte">
          <i class="fa fa-file-excel"
            aria-hidden="true"></i> Exportar
        </b-button>
        <b-button variant="outline-secondary"
          :disabled="loading || exporting"
          @click="limpiarFiltro">
          Limpiar
        </b-button>
      </b-col>

      <b-col md="3"
        class="mb-3">
        <div class="resumen-card resumen-total">
          <div class="resumen-titulo">Registros</div>
          <div class="resumen-valor">{{ resumen.total }}</div>
        </div>
      </b-col>
      <b-col md="3"
        class="mb-3">
        <div class="resumen-card resumen-danger">
          <div class="resumen-titulo">Vencidos</div>
          <div class="resumen-valor">{{ resumen.vencidos }}</div>
        </div>
      </b-col>
      <b-col md="3"
        class="mb-3">
        <div class="resumen-card resumen-warning">
          <div class="resumen-titulo">Por vencer (6m)</div>
          <div class="resumen-valor">{{ resumen.porVencer }}</div>
        </div>
      </b-col>
      <b-col md="3"
        class="mb-3">
        <div class="resumen-card resumen-success">
          <div class="resumen-titulo">Stock total lotes</div>
          <div class="resumen-valor">{{ formatNumber(resumen.stockTotal) }}</div>
        </div>
      </b-col>

      <b-col cols="12">
        <b-table small
          striped
          hover
          responsive
          :busy="loading"
          :items="productos"
          :fields="fields"
          :per-page="perPage"
          :current-page="currentPage"
          show-empty
          empty-text="No hay productos/lotes en el rango seleccionado">
        </b-table>
      </b-col>

      <b-col cols="12"
        class="mt-3">
        <b-pagination v-model="currentPage"
          :total-rows="productos.length"
          :per-page="perPage"
          align="center"></b-pagination>
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";

export default {
  data () {
    return {
      API: config.API,
      loading: false,
      exporting: false,
      fechaDesde: "",
      fechaHasta: "",
      idAlmacen: 0,
      q: "",
      mostrarVencidos: false,
      currentPage: 1,
      perPage: 20,
      pageOptions: [10, 20, 50, 100, 200],
      productos: [],
      lstAlmacenes: [],
      resumen: {
        total: 0,
        vencidos: 0,
        porVencer: 0,
        vigentes: 0,
        stockTotal: 0
      },
      items: [
        { text: "REPORTES", href: "#" },
        { text: "PRODUCTOS", href: "#" },
        { text: "VENCIMIENTOS", active: true }
      ],
      fields: [
        { key: "IdProducto", label: "ID", sortable: true },
        { key: "CodigoBarra", label: "Código", sortable: true },
        { key: "Producto", sortable: true },
        { key: "Almacen", label: "Almacén", sortable: true },
        { key: "Lote", sortable: true },
        { key: "FechaVenFmt", label: "Vencimiento", sortable: true },
        { key: "DiasParaVencerFmt", label: "Días", sortable: true },
        { key: "EstadoVencimiento", label: "Estado", sortable: true },
        { key: "StockLoteFmt", label: "Stock Lote", sortable: true },
        { key: "Movimiento", label: "Mov. Ingreso" }
      ]
    };
  },
  mounted () {
    // Ventana inicial de consulta: 30 dias hacia atras y 6 meses hacia adelante.
    this.fechaDesde = this.$moment()
      .subtract(30, "days")
      .format("YYYY-MM-DD");
    this.fechaHasta = this.$moment()
      .add(6, "months")
      .format("YYYY-MM-DD");
    this.obtenerAlmacenes();
    this.cargarReporte();
  },
  methods: {
    async obtenerAlmacenes () {
      try {
        const res = await this.axios(`${this.API}almacenes`);
        const data = Array.isArray(res.data) ? res.data : [];
        this.lstAlmacenes = [
          {
            IdAlmacen: 0,
            Almacen: "TODOS"
          }
        ].concat(data);
      } catch (e) {
        this.lstAlmacenes = [
          {
            IdAlmacen: 0,
            Almacen: "TODOS"
          }
        ];
      }
    },
    limpiarFiltro () {
      this.q = "";
      this.mostrarVencidos = false;
      this.currentPage = 1;
      this.cargarReporte();
    },
    getEstadoFiltro () {
      return this.mostrarVencidos ? "VENCIDO" : "POR VENCER";
    },
    async cargarReporte () {
      this.loading = true;
      this.currentPage = 1;
      try {
        const res = await this.axios(`${this.API}productos_por_vencer`, {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta,
            idAlmacen: this.idAlmacen,
            q: this.q,
            estadoFiltro: this.getEstadoFiltro()
          }
        });

        const rows = Array.isArray(res.data) ? res.data : [];
        this.productos = rows.map(this.decorateRow);
        this.actualizarResumen();
      } catch (e) {
        this.productos = [];
        this.actualizarResumen();
        if (this.$toasted) {
          this.$toasted.show("No se pudo cargar el reporte de vencimientos", {
            type: "error",
            duration: 4000
          });
        }
      } finally {
        this.loading = false;
      }
    },
    exportarReporte () {
      this.exporting = true;
      try {
        if (this.$swal) {
          this.$swal({
            title: "Procesando...",
            text: "Generando archivo Excel",
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
              this.$swal.showLoading();
            }
          });
        }
        const params = [
          "fechaDesde=" + encodeURIComponent(this.fechaDesde || ""),
          "fechaHasta=" + encodeURIComponent(this.fechaHasta || ""),
          "idAlmacen=" + encodeURIComponent(this.idAlmacen || 0),
          "q=" + encodeURIComponent(this.q || ""),
          "estadoFiltro=" + encodeURIComponent(this.getEstadoFiltro())
        ].join("&");
        const url = `${this.API}reporte/productos_por_vencer/excel?${params}`;
        window.open(url, "_blank");
      } catch (e) {
        if (this.$toasted) {
          this.$toasted.show("No se pudo exportar el reporte a Excel", {
            type: "error",
            duration: 4000
          });
        }
      } finally {
        if (this.$swal) {
          this.$swal.close();
        }
        this.exporting = false;
      }
    },
    decorateRow (item) {
      const row = Object.assign({}, item);
      row.StockLote = this.toNumber(row.StockLote != null ? row.StockLote : row.stock);
      row.StockLoteFmt = this.formatNumber(row.StockLote);
      row.DiasParaVencer = parseInt(row.DiasParaVencer, 10);
      if (isNaN(row.DiasParaVencer)) {
        row.DiasParaVencer = 0;
      }
      row.DiasParaVencerFmt = row.DiasParaVencer;
      row.FechaVenFmt = this.formatDate(row.FechaVen);
      row.Movimiento = (row.Serie || "") + "-" + (row.Numero || "");

      // BootstrapVue RC11 pinta la fila usando _rowVariant en cada item.
      if (row.EstadoVencimiento === "VENCIDO") {
        row._rowVariant = "danger";
      } else if (row.EstadoVencimiento === "POR VENCER") {
        row._rowVariant = "warning";
      } else {
        row._rowVariant = "success";
      }

      if (row.DiasParaVencer < 0) {
        row.DiasParaVencerFmt = "Vencido (" + row.DiasParaVencer + ")";
      } else if (row.DiasParaVencer <= 180) {
        row.DiasParaVencerFmt = row.DiasParaVencer + " (<=6m)";
      }

      return row;
    },
    actualizarResumen () {
      const resumen = {
        total: this.productos.length,
        vencidos: 0,
        porVencer: 0,
        vigentes: 0,
        stockTotal: 0
      };

      this.productos.forEach(item => {
        resumen.stockTotal += this.toNumber(item.StockLote);
        if (item.EstadoVencimiento === "VENCIDO") {
          resumen.vencidos += 1;
        } else if (item.EstadoVencimiento === "POR VENCER") {
          resumen.porVencer += 1;
        } else if (item.EstadoVencimiento === "VIGENTE") {
          resumen.vigentes += 1;
        }
      });

      this.resumen = resumen;
    },
    formatDate (value) {
      if (!value) return "";
      return this.$moment(value).format("YYYY-MM-DD");
    },
    toNumber (value) {
      const n = parseFloat(value);
      return isNaN(n) ? 0 : n;
    },
    formatNumber (value) {
      return this.toNumber(value).toFixed(2);
    }
  }
};
</script>

<style scoped>
.resumen-card {
  border-radius: 8px;
  padding: 12px 14px;
  border: 1px solid rgba(0, 0, 0, 0.08);
  min-height: 78px;
}

.resumen-titulo {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  opacity: 0.85;
}

.resumen-valor {
  font-size: 22px;
  font-weight: 700;
  line-height: 1.2;
  margin-top: 4px;
}

.resumen-total {
  background: #eef3f8;
}

.resumen-danger {
  background: #fdeaea;
}

.resumen-warning {
  background: #fff4db;
}

.resumen-success {
  background: #e7f6eb;
}
</style>
