<script>
import { Bar } from "vue-chartjs";
export default {
  extends: Bar,
  props: {},
  data() {
    return {
      chartData: {
        labels: [],
        datasets: []
      },
      options: {
        scales: {
          yAxes: [
            {
              ticks: {
                beginAtZero: true
              },
              gridLines: {
                display: true
              }
            }
          ],
          xAxes: [
            {
              gridLines: {
                display: false
              }
            }
          ]
        },
        legend: {
          display: true
        },
        responsive: true,
        maintainAspectRatio: false
      }
    };
  },
  methods: {
    GraficarDiasRango(dataApi) {
      this.chartData.labels = [];
      this.chartData.datasets = [];

      let etiquetas = [];
      let valores = [];
      let background = [];
      let border = [];

      let dataset = {
        label: "Monto Total",
        borderWidth: 2,
        backgroundColor: [],
        borderColor: [],
        pointBorderColor: "#2554FF",
        data: []
      };

      // ordenar por si acaso, usando año/mes/día
      dataApi.sort((a, b) => {
        const d1 = new Date(a.Anio, a.Mes - 1, a.Dia);
        const d2 = new Date(b.Anio, b.Mes - 1, b.Dia);
        return d1 - d2;
      });

      dataApi.forEach(item => {
        // Etiqueta como quieras: DíaNombre, solo Día, o "2025-11-01"
        etiquetas.push(`${item.DiaNombre} ${item.Dia}`);

        valores.push(Number(item.Total.toFixed(2)));
        background.push("rgba(54, 162, 235, 0.2)");
        border.push("rgba(54, 162, 235, 1)");
      });

      this.chartData.labels = etiquetas;

      dataset.data = valores;
      dataset.backgroundColor = background;
      dataset.borderColor = border;

      this.chartData.datasets.push(dataset);

      this.renderChart(this.chartData, this.options);
    },
    GraficarDias(mes, dias, dataApi) {
      this.chartData.labels = [];
      this.chartData.datasets = [];
      let lab = [];
      let dataVal = [];
      let dataColor = [];
      let dataColorBorder = [];
      let dataInfo = {
        label: "Monto de Venta",
        borderWidth: 2,
        backgroundColor: [],
        borderColor: [],
        pointBorderColor: "#2554FF",
        data: []
      };
      for (let index = 1; index <= dias; index++) {
        let temp = dataApi.find(d => d.Dia == index);
        if (temp != undefined) {
          dataVal.push((Math.round(temp.Total * 100) / 100).toFixed(2));
        } else {
          dataVal.push(0);
        }
        lab.push("" + index);
        dataColor.push("rgba(54, 162, 235, 0.2)");
        dataColorBorder.push("rgba(54, 162, 235, 1)");
      }
      this.chartData.labels = lab;
      dataInfo.data = dataVal;
      dataInfo.backgroundColor = dataColor;
      dataInfo.borderColor = dataColorBorder;
      this.chartData.datasets.push(dataInfo);
      this.renderChart(this.chartData, this.options);
    },
    GraficarMes(dataApi, dataMes) {
      this.chartData.labels = [];
      this.chartData.datasets = [];
      let lab = [];
      let dataVal = [];
      let dataColor = [];
      let dataColorBorder = [];
      let dataInfo = {
        label: "Monto de Venta",
        borderWidth: 2,
        backgroundColor: [],
        borderColor: [],
        pointBorderColor: "#2554FF",
        data: []
      };
      dataMes.map(dtm => {
        let temp = dataApi.find(d => d.Mes == dtm.value);
        if (temp != undefined) {
          dataVal.push((Math.round(temp.Total * 100) / 100).toFixed(2));
        } else {
          dataVal.push(0);
        }
        lab.push(dtm.text);
        dataColor.push("rgba(54, 162, 235, 0.2)");
        dataColorBorder.push("rgba(54, 162, 235, 1)");
      });
      this.chartData.labels = lab;
      dataInfo.data = dataVal;
      dataInfo.backgroundColor = dataColor;
      dataInfo.borderColor = dataColorBorder;
      this.chartData.datasets.push(dataInfo);
      this.renderChart(this.chartData, this.options);
    }
  },
  mounted() {
    //this.renderChart(this.chartData, this.options)
  }
};
</script>
