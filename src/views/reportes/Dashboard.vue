<template>

  <b-container class="bv-example-row px-5" id="conten" fluid>

    <h1> REPORTES </h1>

    <b-row>
      <b-col cols="4">
        Almacén:
        <select class="form-control" v-model="idAlmacen" @change="recargarReportes()">
          <option v-for="alm in lstAlmacenes" :key="alm.value" :value="alm.value">
            {{ alm.text }}
          </option>
        </select>
      </b-col>
      <b-col cols="4">
        Desde:
        <input type="date" class="form-control" v-model="fechaInicio" @input="recargarReportes()">
      </b-col>
      <b-col cols="4">
        Hasta:
        <input type="date" class="form-control" v-model="fechaFin" @input="recargarReportes()">
      </b-col>
    </b-row>
    <b-row class="d-flex align-items-stretch mt-5">
      <b-col cols="4" class="my-3 ">
        <div class="cards h-100">
          <b-input-group prepend="Monto Total:">
            <input type="number" disabled v-model="totalDia" class="form-control">
          </b-input-group>
          <bar-chart ref="chartGraficaDias"></bar-chart>
        </div>
      </b-col>
      <b-col cols="4" class="my-3 ">
        <div class="cards h-100">
          <b-row>
            <b-col cols="12">
              <h3>Ranking de Ventas por Vendedor </h3>
            </b-col>
            <b-col cols="12">
              <DoughnutVendedor ref="chartGrafica"></DoughnutVendedor>
            </b-col>
          </b-row>
        </div>
      </b-col>
      <b-col cols="4" class="my-3 ">
        <div class="cards h-100">
          <b-row>
            <b-col cols="12">
              <h3>Ranking de Productos Más Vendidos </h3>
            </b-col>

            <b-col cols="12">

              <BarChartProductos ref="barChartGrafica"></BarChartProductos>
            </b-col>
          </b-row>
        </div>
      </b-col>

      <b-col cols="5" class="my-3 ">
        <div class="cards h-100">
          <b-row>
            <b-col cols="12">
              <h3>Reporte de Utilidad Bruta (10 > Rentabilidad) </h3>
            </b-col>

            <b-col cols="12">

              <BarChartUtilidadBruta ref="barChartUtilidadBruta"></BarChartUtilidadBruta>
            </b-col>
          </b-row>
        </div>
      </b-col>

      <b-col cols="7" class="my-3 ">
        <div class="cards h-100">
          <b-row>
            <b-col cols="12">
              <h3>Compras </h3>
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
import BarChart from "@/components/BarChart";
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
      this.axios.get(this.API + "reporte/movimientos", {
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
.cards {
  /* border: 1px solid red; */
  padding: 5%;
  -webkit-box-shadow: 0px 0px 19px 0px rgba(0, 0, 0, 0.78);
  box-shadow: 0px 0px 19px 0px rgba(0, 0, 0, 0.78);
  /* margin: 1%; */
}
</style>
