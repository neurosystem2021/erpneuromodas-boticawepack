<script>
  import { Line } from 'vue-chartjs'
  export default {
    extends: Line,
    props: {
      altura: {
        type: Number,
        default: 300
      }
    },
    data() {
      return {
        chartData: {
          labels: [],
          datasets: []
        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero: true,
                fontSize: 12,
                fontColor: '#6b7a99',
                callback: function(value) {
                  return 'S/ ' + Number(value).toLocaleString('es-PE')
                }
              },
              gridLines: {
                display: true,
                color: 'rgba(0,0,0,0.05)',
                borderDash: [4, 4]
              }
            }],
            xAxes: [{
              ticks: {
                fontSize: 11,
                fontColor: '#6b7a99'
              },
              gridLines: {
                display: false
              }
            }]
          },
          legend: {
            display: true,
            position: 'top',
            labels: {
              fontSize: 12,
              fontColor: '#444',
              usePointStyle: true,
              padding: 16
            }
          },
          tooltips: {
            mode: 'index',
            intersect: false,
            backgroundColor: '#185FA5',
            titleFontSize: 12,
            bodyFontSize: 12,
            callbacks: {
              label: function(item, data) {
                return ' ' + data.datasets[item.datasetIndex].label + ': S/ ' + Number(item.yLabel).toLocaleString('es-PE', { minimumFractionDigits: 2 })
              }
            }
          },
          hover: {
            mode: 'nearest',
            intersect: true
          },
          responsive: true,
          maintainAspectRatio: false,
          layout: {
            padding: { left: 8, right: 16, top: 8, bottom: 8 }
          }
        }
      }
    },
    mounted() {
      // Aplicar altura al canvas si se pasa como prop
      this.$el.style.height = this.altura + 'px'
    },
    methods: {
      // ── Ajustar altura en caliente ──────────────────────────
      setAltura(px) {
        this.$el.style.height = px + 'px'
      },

      GraficarRango(dataApi) {
        this.chartData.labels = []
        this.chartData.datasets = []
        let lab = []
        let dataVal = []
        let dataColor = []
        let dataColorBorder = []
        let dataInfo = {
          label: 'Monto de Venta',
          borderWidth: 2.5,
          backgroundColor: [],
          borderColor: [],
          pointBorderColor: '#185FA5',
          pointBackgroundColor: '#fff',
          pointRadius: 4,
          pointHoverRadius: 6,
          data: []
        }
        dataApi.map(dtm => {
          dataVal.push((Math.round((dtm.Total) * 100) / 100).toFixed(2))
          lab.push(dtm.Dia + "/" + dtm.Mes)
          dataColor.push('rgba(24, 95, 165, 0.1)')
          dataColorBorder.push('rgba(24, 95, 165, 1)')
        })
        this.chartData.labels = lab
        dataInfo.data = dataVal
        dataInfo.backgroundColor = dataColor
        dataInfo.borderColor = dataColorBorder
        dataInfo.fill = true
        this.chartData.datasets.push(dataInfo)
        this.renderChart(this.chartData, this.options)
      },

      GraficarRangoMetodo(dataApi) {
        this.chartData.labels = []
        this.chartData.datasets = []

        let labelsSet = new Set()
        let agrupado = {}

        const metodosPago = {
          1: { nombre: 'Efectivo',      color: 'rgba(24,  95, 165, 1)',  fondo: 'rgba(24,  95, 165, 0.08)' },
          2: { nombre: 'Tarjeta',       color: 'rgba(220, 53,  69, 1)',  fondo: 'rgba(220, 53,  69, 0.08)' },
          3: { nombre: 'Yape',          color: 'rgba(255, 193,  7, 1)',  fondo: 'rgba(255, 193,   7, 0.08)' },
          4: { nombre: 'Transferencia', color: 'rgba(32,  201, 151, 1)', fondo: 'rgba(32,  201, 151, 0.08)' }
        }

        dataApi.forEach(dtm => {
          let dia = dtm.Dia + "/" + dtm.Mes
          labelsSet.add(dia)
          let metodo = dtm.IdMetodoPago
          if (!agrupado[metodo]) {
            agrupado[metodo] = {
              label: (metodosPago[metodo] && metodosPago[metodo].nombre) || `Método ${metodo}`,
              color: (metodosPago[metodo] && metodosPago[metodo].color) || 'rgba(153, 102, 255, 1)',
              fondo: (metodosPago[metodo] && metodosPago[metodo].fondo) || 'rgba(153, 102, 255, 0.08)',
              data: {}
            }
          }
          agrupado[metodo].data[dia] = (agrupado[metodo].data[dia] || 0) + parseFloat(dtm.Total || 0)
        })

        let labels = Array.from(labelsSet).sort((a, b) => {
          let [da, ma] = a.split('/').map(Number)
          let [db, mb] = b.split('/').map(Number)
          return new Date(2020, ma - 1, da) - new Date(2020, mb - 1, db)
        })

        this.chartData.labels = labels

        for (let metodoId in agrupado) {
          let metodo = agrupado[metodoId]
          this.chartData.datasets.push({
            label:            metodo.label,
            data:             labels.map(label => (metodo.data[label] || 0).toFixed(2)),
            fill:             true,
            borderColor:      metodo.color,
            backgroundColor:  metodo.fondo,
            borderWidth:      2.5,
            tension:          0.3,
            pointRadius:      4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#fff',
            pointBorderColor: metodo.color,
            pointBorderWidth: 2
          })
        }

        this.renderChart(this.chartData, this.options)
      }
    }
  }
</script>