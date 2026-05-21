<script>
import { Bar } from "vue-chartjs";

export default {
  extends: Bar, // Manteniendo tu estructura
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
          yAxes: [{
            ticks: {
              beginAtZero: true
            }
          }]
        }
      }
    };
  },
  methods: {
    GraficarCompras(data) {

      this.$nextTick(() => {
        let proveedores = [...new Set(data.map(e => e.Proveedor))]; // Extraer nombres únicos de proveedores
        let productos = [...new Set(data.map(e => e.Producto))]; // Extraer nombres únicos de productos

        let datasets = productos.map(producto => {
          return {
            label: producto,
            backgroundColor: this.getRandomColor(),
            data: proveedores.map(proveedor => {
              let item = data.find(
                e => e.Proveedor === proveedor && e.Producto === producto
              );
              return item ? item.Cantidad : 0;
            })
          };
        });

        this.chartData = {
          labels: proveedores,
          datasets: datasets
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