<script>
import { Bar } from "vue-chartjs";

export default {
  extends: Bar,
  data() {
    return {
      chartData: {},
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: {
          display: true,
          position: "bottom"
        },
        scales: {
          yAxes: [
            {
              ticks: {
                beginAtZero: true
              }
            }
          ]
        }
      }
    };
  },
  methods: {
    GraficarUtilidadBruta(data) {
      this.$nextTick(() => {
        // Ordenar por Utilidad Bruta y tomar los 10 mejores
        let topProductos = [...data]
          .sort((a, b) => b.UtilidadBruta - a.UtilidadBruta)
          .slice(0, 10);

        let labels = topProductos.map(item => item.DescProducto);
        let totalVenta = topProductos.map(item => item.TotalVenta);
        let totalCosto = topProductos.map(item => item.TotalCosto);
        let utilidadBruta = topProductos.map(item => item.UtilidadBruta);

        this.chartData = {
          labels: labels,
          datasets: [
            {
              label: "Total Venta",
              backgroundColor: "rgba(54, 162, 235, 0.6)",
              data: totalVenta
            },
            {
              label: "Total Costo",
              backgroundColor: "rgba(255, 99, 132, 0.6)",
              data: totalCosto
            },
            {
              label: "Utilidad Bruta",
              backgroundColor: "rgba(75, 192, 192, 0.6)",
              data: utilidadBruta
            }
          ]
        };

        this.renderChart(this.chartData, this.options);
      });
    }
  }
};
</script>
