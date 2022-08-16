<script>
  import { Bar } from 'vue-chartjs'
  export default {
    extends: Bar,
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
      GraficarDias(mes, dias, dataApi){
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
        for (let index = 1; index <= dias; index++) {
          let temp = dataApi.find(d=>d.Dia==index)
          if(temp!=undefined){
            dataVal.push((Math.round((temp.Total)*100)/100).toFixed(2))
          }else{
            dataVal.push(0)
          }
          lab.push(""+index)
          dataColor.push('rgba(54, 162, 235, 0.2)')
          dataColorBorder.push('rgba(54, 162, 235, 1)')
        }
        this.chartData.labels= lab
        dataInfo.data = dataVal
        dataInfo.backgroundColor = dataColor
        dataInfo.borderColor = dataColorBorder
        this.chartData.datasets.push(dataInfo)
        this.renderChart(this.chartData, this.options)
      },
      GraficarMes(dataApi,dataMes){
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

        dataMes.map(dtm=>{
          let temp = dataApi.find(d=>d.Mes==dtm.value)
          if(temp!=undefined){
            dataVal.push((Math.round((temp.Total)*100)/100).toFixed(2))
          }else{
            dataVal.push(0)
          }
          lab.push(dtm.text)
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