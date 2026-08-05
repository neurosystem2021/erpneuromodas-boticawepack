<template>

  <b-container class="dashboard-modern" id="conten" fluid>
    <header class="dashboard-hero"><div class="dashboard-hero__icon"><i class="fas fa-chart-pie"></i></div><div><small>CENTRO DE ANÁLISIS</small><h1>Panel de reportes</h1><p>Visualiza ventas, productos, rentabilidad y compras en un solo lugar.</p></div><span><i class="fas fa-sync-alt"></i> Datos actualizados</span></header>

    <b-row class="dashboard-filters">
      <b-col cols="4">
        <label><i class="fas fa-warehouse"></i> Almacén</label>
        <select class="form-control" v-model="idAlmacen" @change="recargarReportes()">
          <option v-for="alm in lstAlmacenes" :key="alm.value" :value="alm.value">
            {{ alm.text }}
          </option>
        </select>
      </b-col>
      <b-col cols="4">
        <label><i class="far fa-calendar-alt"></i> Desde</label>
        <input type="date" class="form-control" v-model="fechaInicio" @input="recargarReportes()">
      </b-col>
      <b-col cols="4">
        <label><i class="far fa-calendar-check"></i> Hasta</label>
        <input type="date" class="form-control" v-model="fechaFin" @input="recargarReportes()">
      </b-col>
    </b-row>
    <b-row class="dashboard-grid d-flex align-items-stretch">
      <b-col cols="4" class="my-3 ">
        <div class="cards dashboard-card h-100">
          <div class="dashboard-card-title"><div><small>VENTAS DEL PERIODO</small><h3>Movimiento diario</h3></div><i class="fas fa-chart-bar"></i></div>
          <div class="dashboard-total"><small>Monto total</small><strong>S/ {{ Number(totalDia || 0).toFixed(2) }}</strong></div>
          <bar-chart ref="chartGraficaDias"></bar-chart>
        </div>
      </b-col>
      <b-col cols="4" class="my-3 ">
        <div class="cards dashboard-card h-100">
          <b-row>
            <b-col cols="12">
              <div class="dashboard-card-title"><div><small>DESEMPEÑO COMERCIAL</small><h3>Ventas por vendedor</h3></div><i class="fas fa-users"></i></div>
            </b-col>
            <b-col cols="12">
              <DoughnutVendedor ref="chartGrafica"></DoughnutVendedor>
            </b-col>
          </b-row>
        </div>
      </b-col>
      <b-col cols="4" class="my-3 ">
        <div class="cards dashboard-card h-100">
          <b-row>
            <b-col cols="12">
              <div class="dashboard-card-title"><div><small>PRODUCTOS DESTACADOS</small><h3>Productos más vendidos</h3></div><i class="fas fa-box-open"></i></div>
            </b-col>

            <b-col cols="12">

              <BarChartProductos ref="barChartGrafica"></BarChartProductos>
            </b-col>
          </b-row>
        </div>
      </b-col>

      <b-col cols="5" class="my-3 ">
        <div class="cards dashboard-card h-100">
          <b-row>
            <b-col cols="12">
              <div class="dashboard-card-title"><div><small>RENTABILIDAD</small><h3>Utilidad bruta</h3></div><i class="fas fa-percentage"></i></div>
            </b-col>

            <b-col cols="12">

              <BarChartUtilidadBruta ref="barChartUtilidadBruta"></BarChartUtilidadBruta>
            </b-col>
          </b-row>
        </div>
      </b-col>

      <b-col cols="7" class="my-3 ">
        <div class="cards dashboard-card h-100">
          <b-row>
            <b-col cols="12">
              <div class="dashboard-card-title"><div><small>ABASTECIMIENTO</small><h3>Compras</h3></div><i class="fas fa-shopping-cart"></i></div>
            </b-col>
            <b-col cols="12">
              <BarChartCompras ref="barChartCompras"></BarChartCompras>
            </b-col>
          </b-row>
        </div>
      </b-col>
    </b-row>
  </b-container>


</template>
<script>
import DoughnutVendedor from "@/components/Dashboard/DoughnutVendedor";
import BarChartProductos from "@/components/Dashboard/BarChartProductos";
import BarChartCompras from "@/components/Dashboard/BarChartCompras";
import BarChartUtilidadBruta from "@/components/Dashboard/BarChartUtilidadBruta";
import BarChart from "@/components/Dashboard/BarChart";
import LineChart from "@/components/LineChart";
import config from "@/store/config.js";
export default {
  mounted() {
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment().format("YYYY-MM-DD");
    // this.fechaInicio = "2024-03-01";
    // this.fechaFin = "2025-03-03";
    this.recargarReportes();
    this.getAlmacenes();
    // this.getAlmacenes();
  },
  data() {
    return {
      API: config.API,
      fechaInicio: "",
      fechaFin: "",
      idAlmacen: 0,
      totalDia: 0,
      lstAlmacenes: [],
      lstDia: []
    };
  },
  methods: {
    recargarReportes() {
      Promise.all([
        this.getDataRankinVendedor(),
        this.getProdcutosMasVendidos(),
        this.getUtilidadBruta(),
        this.getMovimientos(),
        this.getDataDias()
      ]);
    },

    getAlmacenes() {
      this.lstAlmacenes = [];
      this.lstAlmacenes.push({ value: 0, text: "TODOS" });

      // Llamada a tu ruta de Slim PHP
      this.axios.get(this.API + "almacenes").then(r => {
        if (r.data) {
          r.data.forEach(al => {
            // Usamos IdAlmacen y Almacen que vienen de tu SELECT * en PHP
            this.lstAlmacenes.push({
              value: al.IdAlmacen,
              text: al.Almacen
            });
          });
        }
      }).catch(err => console.error("Error cargando almacenes:", err));
    },

    getDataDias() {
      this.lstDia = [];
      this.axios.get(this.API + "ventas/estadistica/dashbora/dia", {
        params: {
          fechaInicio: this.fechaInicio + " 00:00:00",
          fechaFin: this.fechaFin + " 23:59:59",
          idAlmacen: this.idAlmacen // Dinámico
        }
      }).then(response => {
        this.lstDia = response.data;
        this.$refs.chartGraficaDias.GraficarDiasRango(response.data);
        this.calcularTotalDia();
      });
    },
    calcularTotalDia() {
      let totalDia = this.lstDia.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalDia = totalDia.toFixed(2);
    },
    getDataRankinVendedor() {
      this.axios.get(this.API + "reporte/rankingvendedor", {
        params: {
          fechaInicio: this.fechaInicio + " 00:00:00",
          fechaFin: this.fechaFin + " 23:59:59",
          almacen: this.idAlmacen // Antes estaba en 0
        }
      }).then(response => {
        this.$refs.chartGrafica.GraficarVentas(response.data);
      });
    },
    getProdcutosMasVendidos() {
      this.axios.get(this.API + "reporte/productomasvendidos", {
        params: {
          fechaInicio: this.fechaInicio + " 00:00:00",
          fechaFin: this.fechaFin + " 23:59:59",
          almacen: this.idAlmacen // Antes estaba en 0
        }
      }).then(response => {
        this.$refs.barChartGrafica.GraficarProductoVendidos(response.data);
      });
    },
    async getUtilidadBruta() {
      await this.axios.get(this.API + "reporte/utlidadbruta", {
        params: {
          fechaInicio: this.fechaInicio + " 00:00:00",
          fechaFin: this.fechaFin + " 23:59:59",
          almacen: this.idAlmacen // Antes estaba en 0
        }
      }).then(response => {
        this.$refs.barChartUtilidadBruta.GraficarUtilidadBruta(response.data);
      });
    },
    getMovimientos() {
      this.axios.get(this.API + "reporte/movimientos/dashboard", {
        params: {
          fechaInicio: this.fechaInicio + " 00:00:00",
          fechaFin: this.fechaFin + " 23:59:59",
          almacen: this.idAlmacen // Antes estaba en 1 fijo
        }
      }).then(response => {
        this.$refs.barChartCompras.GraficarCompras(response.data);
      });
    },
  },
  components: {
    DoughnutVendedor,
    BarChartCompras,
    BarChartUtilidadBruta,
    BarChartProductos,
    BarChart
  }
};
</script>

<style>
.dashboard-modern{min-height:100vh;padding:14px 24px 34px!important;color:#173651;background:linear-gradient(145deg,#f3f8fc,#eaf3f9 60%,#f9fcfe)}.dashboard-hero{display:flex;align-items:center;gap:14px;min-height:90px;margin-bottom:14px;padding:15px 22px;color:#fff;border-radius:20px;background:linear-gradient(112deg,#083562,#0b5787 65%,#078dac);box-shadow:0 14px 32px rgba(11,68,111,.18)}.dashboard-hero__icon{display:grid;width:52px;height:52px;place-items:center;flex:0 0 52px;color:#a6ecff;border:1px solid rgba(255,255,255,.22);border-radius:15px;background:rgba(255,255,255,.1);font-size:21px}.dashboard-hero small{color:#a9eaff;font-size:10px;font-weight:900;letter-spacing:1.4px}.dashboard-hero h1{margin:0;font-size:27px;font-weight:900}.dashboard-hero p{margin:2px 0 0;color:#d8edf8;font-size:12px}.dashboard-hero>span{margin-left:auto;padding:9px 14px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(0,35,67,.2);font-size:11px;font-weight:800}.dashboard-hero>span i{margin-right:6px;color:#75e5ae}
.dashboard-filters{margin:0 0 14px;padding:17px 14px;border:1px solid #d7e5ef;border-radius:19px;background:#fff;box-shadow:0 10px 28px rgba(28,70,105,.08)}.dashboard-filters label{display:block;margin-bottom:6px;color:#294961;font-size:12px;font-weight:900}.dashboard-filters label i{width:18px;color:#0b78a7}.dashboard-filters .form-control{min-height:46px;color:#173651;border:1px solid #cedeea;border-radius:11px;background:#fbfdff;font-weight:700;box-shadow:none}.dashboard-filters .form-control:focus{border-color:#168fc7;box-shadow:0 0 0 4px rgba(22,143,199,.12)}
.dashboard-grid{margin-top:0!important}.dashboard-grid>[class*=col]{margin-top:7px!important;margin-bottom:7px!important}.cards.dashboard-card{overflow:hidden;padding:18px;border:1px solid #d8e6ef;border-radius:19px;background:#fff;box-shadow:0 12px 30px rgba(26,68,102,.1);transition:transform .2s ease,box-shadow .2s ease}.cards.dashboard-card:hover{transform:translateY(-2px);box-shadow:0 16px 36px rgba(26,68,102,.14)}.dashboard-card-title{display:flex;align-items:center;margin-bottom:12px;padding-bottom:11px;border-bottom:1px solid #e2ebf1}.dashboard-card-title small{color:#1185b3;font-size:9px;font-weight:900;letter-spacing:1.2px}.dashboard-card-title h3{margin:1px 0 0;color:#153b5b;font-size:18px;font-weight:900}.dashboard-card-title>i{display:grid;width:40px;height:40px;margin-left:auto;place-items:center;color:#0b6998;border-radius:12px;background:#e5f4fa;font-size:16px}.dashboard-total{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;padding:10px 13px;color:#fff;border-radius:12px;background:linear-gradient(115deg,#0a5688,#078ba9)}.dashboard-total small{font-size:10px;font-weight:900;letter-spacing:.8px;text-transform:uppercase}.dashboard-total strong{font-size:20px;font-weight:900}.dashboard-card canvas{max-width:100%!important}.dashboard-grid>.col-5 .dashboard-card-title>i{color:#078457;background:#ddf5e8}.dashboard-grid>.col-7 .dashboard-card-title>i{color:#9b6a0b;background:#fff1ce}
@media(max-width:991px){.dashboard-modern{padding:10px!important}.dashboard-hero>span{display:none}.dashboard-grid>.col-4,.dashboard-grid>.col-5,.dashboard-grid>.col-7{max-width:100%;flex:0 0 100%}}
@media(max-width:575px){.dashboard-hero p{display:none}.dashboard-hero h1{font-size:21px}.dashboard-filters>.col-4{max-width:100%;flex:0 0 100%;margin-bottom:9px}.cards.dashboard-card{padding:13px}}
</style>
