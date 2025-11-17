<script>
import { Doughnut } from "vue-chartjs";

export default {
  extends: Doughnut,
  data() {
    return {
      chartData: {},
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: true, position: "left" },

        tooltips: {
          enabled: true,
          callbacks: {
            label: function(tooltipItem, data) {
              let label = data.labels[tooltipItem.index];
              let value = data.datasets[0].data[tooltipItem.index];
              return `${label}: ${value}`;
            }
          }
        }
      }
    };
  },

  methods: {
    GraficarVentas(data) {
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
          labels,
          datasets: [
            {
              label: "Ranking de Ventas por Usuario",
              backgroundColor: colores,
              data: datos
            }
          ]
        };

        this.renderChart(this.chartData, this.options);
      });
    },

    getRandomColor() {
      return `hsl(${Math.floor(Math.random() * 360)}, 70%, 60%)`;
    }
  }
};
</script>
