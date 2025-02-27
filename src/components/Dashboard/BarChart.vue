<script>
import { Doughnut } from "vue-chartjs";

export default {
  extends: Doughnut, // Extiende el componente
  data() {
    return {
      //   chartData: {
      //     labels: [],
      //     datasets: [
      //       {
      //         label: "Ventas",
      //         backgroundColor: ["#ff6384", "#36a2eb", "#ffce56"],
      //         data: [30, 50, 20]
      //       }
      //     ]
      //   },
      chartData: {},
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: true }
      }
    };
  },
  mounted() {
    // this.renderChart(this.chartData, this.options); // Renderiza el gráfico
  },
  methods: {
    GraficarDias(data) {
      this.$nextTick(() => {
        let labels = [];
        let datos = [];
        let colores = [];
        data.forEach(element => {
          labels.push(element.NombreUsuario);
          datos.push(element.totalventa);
          colores.push(this.getRandomColor());
        });
        this.chartData = {
          labels: labels,
          datasets: [
            {
              label: "Ventas",
              backgroundColor: colores,
              data: datos
            }
          ]
        };
        console.log(labels);
        console.log(datos);
        console.log(this.chartData);
        this.renderChart(this.chartData, this.options);
      });
    },
    getRandomColor() {
      return `hsl(${Math.floor(Math.random() * 360)}, 70%, 60%)`;
    }
  }
};
</script>
