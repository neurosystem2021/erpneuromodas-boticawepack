<template>
  <div>
    <div>
      <b-card no-body>
        <b-tabs v-model="tabActive"  pills card>
          <b-tab title="Ventas Por Dia" @click="cambioOpcion(0)" active>
            <b-container class="bv-example-row" id="conten">
              <b-row>
                <b-col md="6">      
                  <b-form-group>
                    <label>Seleccione Año:</label>
                    <b-form-select v-model="anioSelect" :options="anios" v-on:change="setAnio($event)" class="mb-3" size="md" required/>
                  </b-form-group>
                </b-col>
                <b-col md="6">      
                  <b-form-group>
                    <label>Seleccione Mes:</label>
                    <b-form-select v-model="mesSelect" :options="meses" v-on:change="setMes($event)" class="mb-3" size="md" required/>
                  </b-form-group>
                </b-col>
              </b-row>
              <b-row>
                <b-col>
                  <h3>Valor de venta por día</h3>
                  <bar-chart ref="chartGrafica"></bar-chart>
                </b-col>
              </b-row>
            </b-container>
          </b-tab>
          <b-tab title="Ventas Por Mes" @click="cambioOpcion(1)">
            <b-container class="bv-example-row" id="conten">
              <b-row>
                <b-col md="6">      
                  <b-form-group>
                    <label>Seleccione Año:</label>
                    <b-form-select v-model="anioSelect" :options="anios" v-on:change="setAnioMes($event)" class="mb-3" size="md" required/>
                  </b-form-group>
                </b-col>
              </b-row>
              <b-row>
                <b-col>
                  <h3>Valor de venta por mes</h3>
                  <bar-chart ref="chartGraficaMes"></bar-chart>
                </b-col>
              </b-row>
            </b-container>
          </b-tab>
          <b-tab title="Ventas Por Rango" @click="cambioOpcion(3)">
              <b-container class="bv-example-row" id="conten">
                <b-row>
                  <b-col md="5">
                    <b-form-group>
                      <label>Fecha Inicio:</label>
                      <b-form-input
                        v-model="fechaDesde"
                        type="date"
                        step="1"
                        placeholder="Fecha Inicio">
                      </b-form-input>
                    </b-form-group>
                  </b-col>
                  <b-col md="5">
                    <b-form-group>
                      <label>Fecha Fin:</label>
                      <b-form-input
                        v-model="fechaHasta"
                        type="date"
                        step="1"
                        placeholder="Fecha Inicio">
                      </b-form-input>
                    </b-form-group>
                  </b-col>
                  <b-col md="2" class="text-center">
                    <b-form-group>
                      <br>
                      <b-button @click="fechaRango" variant="success" size="lg">Generar</b-button>
                    </b-form-group>
                  </b-col>
                </b-row>
                <b-row>
                <b-col>
                  <h3>Valor de venta por rango</h3>
                  <line-chart ref="chartGraficaRango"></line-chart>
                </b-col>
                </b-row>
              </b-container>
          </b-tab>
        </b-tabs>
      </b-card>
    </div>
  </div>
</template>
<script>
import BarChart from '@/components/BarChart'
import LineChart from '@/components/LineChart'
import config from '@/store/config.js'
export default {
  mounted(){
    this.initDia()
  },
  data () {
    return {
      API: config.API,
      meses:[
        {value:1,text:"Enero",dias:31},{value:2,text:"Febrero",dias:29},{value:3,text:"Marzo",dias:31},
        {value:4,text:"Abril",dias:30},{value:5,text:"Mayo",dias:31},{value:6,text:"Junio",dias:30},
        {value:7,text:"Julio",dias:31},{value:8,text:"Agosto",dias:31},{value:9,text:"Septiembre",dias:30},
        {value:10,text:"Octubre",dias:31},{value:11,text:"Noviembre",dias:30},{value:12,text:"Diciembre",dias:31}
      ],
      anios:[],
      mesSelect:1,
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      tabActive:0,
      anioSelect: Number(this.$moment().format("YYYY")),
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD")
      
    }
  },
  methods:{
    initDia(){
      let anioActual = Number(this.$moment().format("YYYY"))
      let mesActual = Number(this.$moment().format("MM"))
      this.anios=[]
      this.anios.push({value:anioActual,text:''+anioActual})
      for (let index = 1; index <= 5; index++) {
        this.anios.push({value:(anioActual-index),text:''+(anioActual-index)})
      }

      let mesd = this.meses.find(ms=>ms.value==mesActual)
      if(mesd !=undefined){
        this.mesSelect = mesActual
        this.anioSelect = anioActual
        this.getDataDias(anioActual,mesActual,mesd.dias)
      }  
    },
    initMes(){
      let anioActual = Number(this.$moment().format("YYYY"))
      this.anios=[]
      this.anios.push({value:anioActual,text:''+anioActual})
      for (let index = 1; index <= 5; index++) {
        this.anios.push({value:(anioActual-index),text:''+(anioActual-index)})
      }
      this.anioSelect = anioActual
      this.getDataMes(anioActual)
    },
    setMes(e){
      let mes = this.meses.find(ms=>ms.value==e)
      if(mes !=undefined){
        this.getDataDias(this.anioSelect,e,mes.dias)
      }
    },
    setAnio(e){
      let mes = this.meses.find(ms=>ms.value==this.mesSelect)
      if(mes !=undefined){
        this.getDataDias(e,mes.value,mes.dias)
      }
    },
    setAnioMes(e){
      this.getDataMes(e)
    },
    getDataDias(anio, mes, dias){
      this.axios.get(this.API + 'ventas/estadistica/dia', {
          params: {
              anio: anio,
              mes: mes
          }
      })
      .then((response) => {
          this.$refs.chartGrafica.GraficarDias(mes.value,dias,response.data)
      })
      .catch((error) => {
          console.log(error)
      })
    },
    getDataMes(anio){
      this.axios.get(this.API + 'ventas/estadistica/mes', {
          params: {
              anio: anio,
          }
      })
      .then((response) => {
          this.$refs.chartGraficaMes.GraficarMes(response.data,this.meses)
      })
      .catch((error) => {
          console.log(error)
      })
    },
    cambioOpcion(e){
      switch (e) {
        case 0:
          this.initDia()
          break;
        case 1:
          this.initMes()
        break;
          
        default:
          break;
      }
    },
    fechaRango(){
      if(this.fechaDesde==''){
        this.$toasted.show("Ingrese una fecha validad en fecha Inicio")
        return
      }
      if(this.fechaHasta==''){
        this.$toasted.show("Ingrese una fecha validad en fecha fin")
        return
      }
      this.axios.get(this.API + 'ventas/estadistica/rango', {
          params: {
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta
          }
      })
      .then((response) => {
          this.$refs.chartGraficaRango.GraficarRango(response.data)
      })
      .catch((error) => {
          console.log(error)
      })
    }
  },
  components: {
    BarChart,
    LineChart
  }
}
</script>