<script>
  import { Line } from 'vue-chartjs'
  export default {
    extends: Line,
    props: {
    },
    data() {
      return {
        chartData: {
          labels: [],
          datasets:[]
        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true
              },
              gridLines: {
                display: true
              }
            }],
            xAxes: [{
              gridLines: {
                display: false
              }
            }]
          },
          legend: {
            display: true
          },
          responsive: true,
          maintainAspectRatio: false
        }
      }
    },
    methods:{
      GraficarRango(dataApi){
        this.chartData.labels = []
        this.chartData.datasets = []
        let lab = []
        let dataVal = []
        let dataColor = []
        let dataColorBorder = []
        let dataInfo = {
          label:'Monto de Venta',
          borderWidth: 2,
          backgroundColor:[],
          borderColor:[],
          pointBorderColor: '#2554FF',
          data:[]
        }
        dataApi.map(dtm=>{
          dataVal.push((Math.round((dtm.Total)*100)/100).toFixed(2))
          lab.push(dtm.Dia+"/"+dtm.Mes)
          dataColor.push('rgba(54, 162, 235, 0.2)')
          dataColorBorder.push('rgba(54, 162, 235, 1)')
        })
        this.chartData.labels= lab
        dataInfo.data = dataVal
        dataInfo.backgroundColor = dataColor
        dataInfo.borderColor = dataColorBorder
        this.chartData.datasets.push(dataInfo)
        this.renderChart(this.chartData, this.options)
      },
      GraficarRangoMetodo(dataApi) {
        this.chartData.labels = [];
        this.chartData.datasets = [];

        let labelsSet = new Set();
        let agrupado = {}; // { IdMetodoPago: { label: 'Efectivo', color: '#...', data: { '1/6': total } } }

        // Mapeo de nombres y colores de métodos de pago
        const metodosPago = {
          1: { nombre: 'Efectivo', color: 'rgba(54, 162, 235, 1)' },
          2: { nombre: 'Tarjeta', color: 'rgba(255, 99, 132, 1)' },
          3: { nombre: 'Yape', color: 'rgba(255, 206, 86, 1)' },
          4: { nombre: 'Transferencia', color: 'rgba(75, 192, 192, 1)' }
        };

        // Agrupar los totales por día y método de pago
        dataApi.forEach(dtm => {
          let dia = dtm.Dia + "/" + dtm.Mes;
          labelsSet.add(dia);

          let metodo = dtm.IdMetodoPago;
          if (!agrupado[metodo]) {
        agrupado[metodo] = {
          label: (metodosPago[metodo] && metodosPago[metodo].nombre) || `Método ${metodo}`,
          color: (metodosPago[metodo] && metodosPago[metodo].color) || 'rgba(153, 102, 255, 1)',
          data: {}
        };
      }

          // Acumular total por día
          agrupado[metodo].data[dia] = (agrupado[metodo].data[dia] || 0) + parseFloat(dtm.Total || 0);
        });

        // Crear un array ordenado de fechas
        let labels = Array.from(labelsSet).sort((a, b) => {
          let [da, ma] = a.split('/').map(Number);
          let [db, mb] = b.split('/').map(Number);
          return new Date(2020, ma - 1, da) - new Date(2020, mb - 1, db);
        });

        this.chartData.labels = labels;

        // Crear datasets
        for (let metodoId in agrupado) {
          let metodo = agrupado[metodoId];
          let dataset = {
            label: metodo.label,
            data: labels.map(label => (metodo.data[label] || 0).toFixed(2)),
            fill: false,
            borderColor: metodo.color,
            tension: 0.1
          };
          this.chartData.datasets.push(dataset);
        }

        // Renderizar gráfico
        this.renderChart(this.chartData, this.options);
      }
    },
    mounted() {
      //this.renderChart(this.chartData, this.options)
    }
  }
</script> 