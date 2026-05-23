<template>
  <b-container fluid id="conten">

    <!-- ═══ HEADER ═══ -->
    <div class="dashboard-header">
      <div>
        <h2 class="dashboard-title">📊 Dashboard de Reportes</h2><br>

        <div class="filtro-grupo">
          <label>Desde:</label>
          <input type="date" class="form-control form-control-sm" v-model="fechaInicio" @input="recargarReportes" />
          <label>Hasta:</label>
          <input type="date" class="form-control form-control-sm" v-model="fechaFin" @input="recargarReportes" />
        </div>
      </div>
    </div>

    <!-- ═══ KPI CARDS ═══ -->
    <b-row class="mb-4">
      <b-col md="3">
        <div class="kpi-dash kpi-azul">
          <div class="kpi-dash-icon">💰</div>
          <div class="kpi-dash-info">
            <div class="kpi-dash-label">Total Ventas</div>
            <div class="kpi-dash-value">S/ {{ totalDia }}</div>
          </div>
        </div>
      </b-col>
      <b-col md="3">
        <div class="kpi-dash kpi-verde">
          <div class="kpi-dash-icon">🧾</div>
          <div class="kpi-dash-info">
            <div class="kpi-dash-label">Comprobantes</div>
            <div class="kpi-dash-value">{{ totalComprobantes }}</div>
          </div>
        </div>
      </b-col>
      <b-col md="3">
        <div class="kpi-dash kpi-naranja">
          <div class="kpi-dash-icon">📦</div>
          <div class="kpi-dash-info">
            <div class="kpi-dash-label">Ticket Promedio</div>
            <div class="kpi-dash-value">S/ {{ ticketPromedio }}</div>
          </div>
        </div>
      </b-col>
      <b-col md="3">
        <div class="kpi-dash kpi-morado">
          <div class="kpi-dash-icon">📅</div>
          <div class="kpi-dash-info">
            <div class="kpi-dash-label">Días con Venta</div>
            <div class="kpi-dash-value">{{ diasConVenta }}</div>
          </div>
        </div>
      </b-col>
    </b-row>

    <!-- ═══ LOADING ═══ -->
    <div v-if="cargando" class="loading-overlay">
      <div class="spinner-border text-primary" role="status"></div>
      <span class="ml-2">Cargando reportes...</span>
    </div>

    <!-- ═══ GRÁFICAS FILA 1 ═══ -->
    <b-row class="d-flex align-items-stretch">
      <b-col cols="4" class="my-3">
        <div class="card-dash h-100">
          <div class="card-dash-title">📈 Ventas por Día</div>
          <bar-chart ref="chartGraficaDias"></bar-chart>
        </div>
      </b-col>
      <b-col cols="4" class="my-3">
        <div class="card-dash h-100">
          <div class="card-dash-title">👥 Ranking de Vendedores</div>
          <DoughnutVendedor ref="chartGrafica"></DoughnutVendedor>
        </div>
      </b-col>
      <b-col cols="4" class="my-3">
        <div class="card-dash h-100">
          <div class="card-dash-title">🏆 Productos Más Vendidos</div>
          <BarChartProductos ref="barChartGrafica"></BarChartProductos>
        </div>
      </b-col>
    </b-row>

    <!-- ═══ GRÁFICAS FILA 2 ═══ -->
    <b-row class="d-flex align-items-stretch">
      <b-col cols="4" class="my-3">
        <div class="card-dash h-100">
          <div class="card-dash-title">💹 Utilidad Bruta <small class="text-muted">(rentab. &lt; 10% en rojo)</small></div>
          <BarChartUtilidadBruta ref="barChartUtilidadBruta"></BarChartUtilidadBruta>
        </div>
      </b-col>
      <b-col cols="8" class="my-3">
        <div class="card-dash h-100">
          <div class="card-dash-title">🛒 Compras del Período</div>
          <BarChartCompras ref="barChartCompras"></BarChartCompras>
        </div>
      </b-col>
    </b-row>

  </b-container>
</template>

<script>
import DoughnutVendedor      from "@/components/Dashboard/DoughnutVendedor";
import BarChartProductos     from "@/components/Dashboard/BarChartProductos";
import BarChartCompras       from "@/components/Dashboard/BarChartCompras";
import BarChartUtilidadBruta from "@/components/Dashboard/BarChartUtilidadBruta";
import BarChart              from "@/components/BarChart";
import config                from "@/store/config.js";

export default {
  mounted() {
    this.fechaInicio = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.fechaFin    = this.$moment().format("YYYY-MM-DD");
    this.recargarReportes();
  },
  data() {
    return {
      API:      config.API,
      fechaInicio: "",
      fechaFin:    "",
      lstDia:      [],
      totalDia:    "0.00",
      totalComprobantes: 0,
      cargando:    false,
    };
  },

  computed: {
    periodoLabel() {
      if (!this.fechaInicio || !this.fechaFin) return "";
      const ini = this.$moment(this.fechaInicio).format("DD/MM/YYYY");
      const fin = this.$moment(this.fechaFin).format("DD/MM/YYYY");
      return `Período: ${ini} — ${fin}`;
    },
    ticketPromedio() {
      if (!this.totalComprobantes) return "0.00";
      return (parseFloat(this.totalDia) / this.totalComprobantes).toFixed(2);
    },
    diasConVenta() {
      return this.lstDia.filter(d => parseFloat(d.Total) > 0).length;
    }
  },

  methods: {
    recargarReportes() {
      this.cargando = true;
      Promise.all([
        this.getDataDias(),
        this.getDataRankinVendedor(),
        this.getProdcutosMasVendidos(),
        this.getUtilidadBruta(),
        this.getMovimientos(),
      ]).finally(() => {
        this.cargando = false;
      });
    },

    getDataDias() {
      return this.axios.get(this.API + "ventas/estadistica/dashbora/dia", {
        params: { fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, idAlmacen: 0 }
      }).then(r => {
        this.lstDia = r.data;
        this.totalComprobantes = r.data.length;
        this.$nextTick(() => {
          if (this.$refs.chartGraficaDias)
            this.$refs.chartGraficaDias.GraficarDiasRango(r.data);
        });
        this.calcularTotalDia();
      }).catch(e => console.log(e));
    },

    calcularTotalDia() {
      const total = this.lstDia.reduce((s, d) => s + parseFloat(d.Total), 0);
      this.totalDia = total.toFixed(2);
    },

    getDataRankinVendedor() {
      return this.axios.get(this.API + "reporte/rankingvendedor", {
        params: { fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, almacen: 0 }
      }).then(r => {
        this.$nextTick(() => {
          if (this.$refs.chartGrafica)
            this.$refs.chartGrafica.GraficarVentas(r.data);
        });
      }).catch(e => console.log(e));
    },

    getProdcutosMasVendidos() {
      return this.axios.get(this.API + "reporte/productomasvendidos", {
        params: { fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, almacen: 0 }
      }).then(r => {
        this.$nextTick(() => {
          if (this.$refs.barChartGrafica)
            this.$refs.barChartGrafica.GraficarProductoVendidos(r.data);
        });
      }).catch(e => console.log(e));
    },

    getUtilidadBruta() {
      return this.axios.get(this.API + "reporte/utlidadbruta", {
        params: { fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, almacen: 0 }
      }).then(r => {
        this.$nextTick(() => {
          if (this.$refs.barChartUtilidadBruta)
            this.$refs.barChartUtilidadBruta.GraficarUtilidadBruta(r.data);
        });
      }).catch(e => console.log(e));
    },

    getMovimientos() {
      return this.axios.get(this.API + "reporte/movimientos", {
        params: { fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, almacen: 1 }
      }).then(r => {
        this.$nextTick(() => {
          if (this.$refs.barChartCompras)
            this.$refs.barChartCompras.GraficarCompras(r.data);
        });
      }).catch(e => console.log(e));
    },
  },

  components: {
    DoughnutVendedor, BarChartCompras,
    BarChartUtilidadBruta, BarChartProductos, BarChart
  }
};
</script>

<style scoped>
#conten { padding: 0 3% !important; }

/* ── Header ── */
.dashboard-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 0 16px;
  border-bottom: 2px solid #e8edf4;
  margin-bottom: 20px;
}
.dashboard-title {
  font-size: 1.6rem;
  font-weight: 700;
  color: #1a2b4a;
  margin: 0;
}
.dashboard-sub {
  font-size: 13px;
  color: #7a8fa8;
  margin-top: 3px;
}
.dashboard-filtros {
  display: flex;
  gap: 16px;
  align-items: flex-end;
}
.filtro-grupo {
  display: flex;
  flex-direction: row;      /* ← en fila en vez de columna */
  align-items: center;
  gap: 10px;
}
.filtro-grupo label {
  font-size: 22px;
  color: #6b7a99;
  margin: 0;
  font-weight: 600;
  white-space: nowrap;      /* ← evita que el label se parta */
}
.filtro-grupo input { width: 160px; font-size: 20px;}

/* ── KPI Cards ── */
.kpi-dash {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 18px 20px;
  border-radius: 14px;
  color: #fff;
  box-shadow: 0 3px 12px rgba(0,0,0,0.12);
  margin-bottom: 8px;
}
.kpi-azul   { background: linear-gradient(135deg, #185FA5, #2980d9); }
.kpi-verde  { background: linear-gradient(135deg, #1a9e5c, #27ae60); }
.kpi-naranja{ background: linear-gradient(135deg, #d4920a, #f39c12); }
.kpi-morado { background: linear-gradient(135deg, #7b2fa8, #9b59b6); }
.kpi-dash-icon  { font-size: 32px; }
.kpi-dash-label { font-size: 11px; opacity: 0.85; text-transform: uppercase; letter-spacing: 0.05em; }
.kpi-dash-value { font-size: 1.6rem; font-weight: 700; line-height: 1.2; }

/* ── Cards gráficas ── */
.card-dash {
  background: #fff;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 2px 14px rgba(0,0,0,0.08);
  border: 1px solid #edf0f7;
}
.card-dash-title {
  font-size: 15px;
  font-weight: bold;
  color: #1a2b4a;
  margin-bottom: 14px;
  padding-bottom: 10px;
  border-bottom: 1px solid #edf0f7;
}

/* ── Loading ── */
.loading-overlay {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 16px;
  background: #f0f4fb;
  border-radius: 10px;
  margin-bottom: 16px;
  color: #185FA5;
  font-weight: 500;
}
</style>