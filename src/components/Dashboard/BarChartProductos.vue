<script>
import { Bar, Doughnut } from "vue-chartjs";

export default {
  extends: Bar, // Extiende el componente
  data() {
    return {
      chartData: {},
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: true
          // position: "left"
        }
      }
    };
  },
  mounted() {},
  methods: {
    GraficarProductoVendidos(data) {
      this.$nextTick(() => {
        let labels = [];
        let datos = [];
        let colores = [];
        data.forEach(element => {
          labels.push(element.Producto);
          datos.push(element.TotalVentas);
          colores.push(this.getRandomColor());
        });
        this.chartData = {
          labels: labels,
          datasets: [
            {
              label: "Productos Más Vendidos",
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
