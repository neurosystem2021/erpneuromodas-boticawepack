<template>
  <div>
    <div>
      <b-card no-body>
       
            <b-container class="bv-example-row" id="conten">
              <b-row>
                
              </b-row>
              <b-row>
                <b-col>
                  <h3>Valor de venta por día</h3>
                  <bar-chart ref="chartGrafica"></bar-chart>
                </b-col>
              </b-row>
            </b-container>

      </b-card>
    </div>
  </div>
</template>
<script>
import BarChart from "@/components/Dashboard/BarChart";
import LineChart from "@/components/LineChart";
import config from "@/store/config.js";
export default {
  mounted() {
    this.getDataRankinVendedor();
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
          this.$refs.chartGrafica.GraficarDias(response.data);
        })
        .catch(error => {
          console.log(error);
        });
    }
  },
  components: {
    BarChart,
    LineChart
  }
};
</script>
