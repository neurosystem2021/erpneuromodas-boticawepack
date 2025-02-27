<template>
  
            <b-container class="bv-example-row" id="conten" fluid>
              <b-row>
                
              </b-row>
              <b-row>
                <b-col cols="12">
                  <b-row>
                   <b-col cols="12"> <h3>Ranking de Ventas por Vendedor </h3></b-col>

                    <b-col cols="2"> 
                        Desde Hasta
                        <input type="date" class="form-control">
                    </b-col>
                    <b-col cols="10">
                      <DoughnutVendedor ref="chartGrafica"></DoughnutVendedor>
                    </b-col>
                  </b-row>
                </b-col>
                <b-col cols="12">
                  <b-row>
                    <b-col cols="12">
                      <h3>Ranking de Productos Más Vendidos </h3>
                    </b-col>
                    <b-col cols="2"> 
                        Desde Hasta
                        <input type="date" class="form-control">
                    </b-col>
                    <b-col cols="10">

                      <BarChartProductos ref="barChartGrafica"></BarChartProductos>
                    </b-col>
                  </b-row>
                  </b-col>
              </b-row>
            </b-container>

     
</template>
<script>
import DoughnutVendedor from "@/components/Dashboard/DoughnutVendedor";
import BarChartProductos from "@/components/Dashboard/BarChartProductos";
import LineChart from "@/components/LineChart";
import config from "@/store/config.js";
export default {
  mounted() {
    this.getDataRankinVendedor();
    this.getProdcutosMasVendidos();
    // this.getAlmacenes();
  },
  data() {
    return {
      API: config.API
    };
  },
  methods: {
    getDataRankinVendedor(anio, mes, dias, idAlmacen) {
      this.axios
        .get(this.API + "reporte/rankingvendedor", {
          params: {
            anio: anio,
            mes: mes,
            idAlmacen: idAlmacen || this.idAlmacen
          }
        })
        .then(response => {
          // console.log(response.data);
          this.$refs.chartGrafica.GraficarVentas(response.data);
        })
        .catch(error => {
          console.log(error);
        });
    },
    getProdcutosMasVendidos(anio, mes, dias, idAlmacen) {
      this.axios
        .get(this.API + "reporte/productomasvendidos", {
          params: {
            anio: anio,
            mes: mes,
            idAlmacen: idAlmacen || this.idAlmacen
          }
        })
        .then(response => {
          // console.log(response.data);
          this.$refs.barChartGrafica.GraficarProductoVendidos(response.data);
        })
        .catch(error => {
          console.log(error);
        });
    }
  },
  components: {
    DoughnutVendedor,
    BarChartProductos
  }
};
</script>
