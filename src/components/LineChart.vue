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
      }
    },
    mounted() {
      //this.renderChart(this.chartData, this.options)
    }
  }
</script> 