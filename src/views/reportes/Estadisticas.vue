<template>
    <div>
      <div>
        <b-card no-body>
          <b-tabs v-model="tabActive"  pills card>
            <b-tab title="Ventas Por Dia" @click="cambioOpcion(0)" active>
              <b-container class="bv-example-row" id="conten">
                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Almacén:</label>
                      <b-form-select v-model="idAlmacen" :options="almacenes" v-on:change="setAlmacen($event)" class="mb-3" size="md"/>
                    </b-form-group>
                  </b-col>
                  <b-col md="4">      
                    <b-form-group>
                      <label>Seleccione Año:</label>
                      <b-form-select v-model="anioSelect" :options="anios" v-on:change="setAnio($event)" class="mb-3" size="md" required/>
                    </b-form-group>
                  </b-col>
                  <b-col md="4">      
                    <b-form-group>
                      <label>Seleccione Mes:</label>
                      <b-form-select v-model="mesSelect" :options="meses" v-on:change="setMes($event)" class="mb-3" size="md" required/>
                    </b-form-group>
                  </b-col>
                </b-row>
                <b-row>
                  <b-col cols="9">
                    <h3>Valor de venta por día</h3>
                    <bar-chart ref="chartGrafica"></bar-chart>
                  </b-col>
                  <b-col cols="3">
                     <b-input-group prepend="Total:" >
                        <input type="number" disabled v-model="totalDia" class="form-control">
                     </b-input-group>
                    
                  </b-col>
                </b-row>
              </b-container>
            </b-tab>
            <b-tab title="Ventas Por Mes" @click="cambioOpcion(1)">
              <b-container class="bv-example-row" id="conten">
                <b-row>
                  <b-col md="6">
                    <b-form-group>
                      <label>Almacén:</label>
                      <b-form-select v-model="idAlmacen" :options="almacenes" v-on:change="setAlmacen($event)" class="mb-3" size="md"/>
                    </b-form-group>
                  </b-col>
                  <b-col md="6">      
                    <b-form-group>
                      <label>Seleccione Año:</label>
                      <b-form-select v-model="anioSelect" :options="anios" v-on:change="setAnioMes($event)" class="mb-3" size="md" required/>
                    </b-form-group>
                  </b-col>
                </b-row>
                <b-row>
                  <b-col cols="9">
                    <h3>Valor de venta por mes</h3>
                    <bar-chart ref="chartGraficaMes"></bar-chart>
                  </b-col>
                    <b-col cols="3">
                      <b-input-group prepend="Total:" >
                        <input type="number" disabled v-model="totalMes" class="form-control">
                     </b-input-group>
                    </b-col>
                </b-row>
              </b-container>
            </b-tab>
            <b-tab title="Ventas Por Rango" @click="cambioOpcion(3)">
                <b-container class="bv-example-row" id="conten">
                  <b-row>
                    <b-col md="3">
                      <b-form-group>
                        <label>Almacén:</label>
                        <b-form-select v-model="idAlmacen" :options="almacenes" v-on:change="setAlmacen($event)" class="mb-3" size="md"/>
                      </b-form-group>
                    </b-col>
                    <b-col md="3">
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
                    <b-col md="3">
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
                    <b-col md="3" class="text-center">
                      <b-form-group>
                        <br>
                        <b-button @click="fechaRango" variant="success" size="lg">Generar</b-button>
                      </b-form-group>
                    </b-col>
                  </b-row>
                  <b-row>
                  <b-col cols="9">
                    <h3>Valor de venta por rango</h3>
                    <line-chart ref="chartGraficaRango"></line-chart>
                  </b-col>
                  <b-col cols="3">
                      <b-input-group prepend="Total:" >
                        <input type="number" disabled v-model="totalRango" class="form-control">
                     </b-input-group>
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
import BarChart from "@/components/BarChart";
import LineChart from "@/components/LineChart";
import config from "@/store/config.js";
export default {
  mounted() {
    this.initDia(0);
    this.getAlmacenes();
  },
  data() {
    return {
      API: config.API,
      meses: [
        { value: 1, text: "Enero", dias: 31 },
        { value: 2, text: "Febrero", dias: 29 },
        { value: 3, text: "Marzo", dias: 31 },
        { value: 4, text: "Abril", dias: 30 },
        { value: 5, text: "Mayo", dias: 31 },
        { value: 6, text: "Junio", dias: 30 },
        { value: 7, text: "Julio", dias: 31 },
        { value: 8, text: "Agosto", dias: 31 },
        { value: 9, text: "Septiembre", dias: 30 },
        { value: 10, text: "Octubre", dias: 31 },
        { value: 11, text: "Noviembre", dias: 30 },
        { value: 12, text: "Diciembre", dias: 31 }
      ],
      anios: [],
      mesSelect: 1,
      tabActive: 0,
      anioSelect: Number(this.$moment().format("YYYY")),
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      idAlmacen: 0,
      almacenes: [{ value: 0, text: "TODOS" }],
      lstDia: [],
      totalDia: 0,
      lstMes: [],
      totalMes: 0,
      lstRango: [],
      totalRango: 0
    };
  },
  methods: {
    initDia(idAlmacen) {
      let anioActual = Number(this.$moment().format("YYYY"));
      let mesActual = Number(this.$moment().format("MM"));
      this.anios = [];
      this.anios.push({ value: anioActual, text: "" + anioActual });
      for (let index = 1; index <= 5; index++) {
        this.anios.push({
          value: anioActual - index,
          text: "" + (anioActual - index)
        });
      }
      let mesd = this.meses.find(ms => ms.value == mesActual);
      if (mesd != undefined) {
        this.mesSelect = mesActual;
        this.anioSelect = anioActual;
        this.getDataDias(anioActual, mesActual, mesd.dias, idAlmacen);
      }
    },
    initMes(idAlmacen) {
      let anioActual = Number(this.$moment().format("YYYY"));
      this.anios = [];
      this.anios.push({ value: anioActual, text: "" + anioActual });
      for (let index = 1; index <= 5; index++) {
        this.anios.push({
          value: anioActual - index,
          text: "" + (anioActual - index)
        });
      }
      this.anioSelect = anioActual;
      this.getDataMes(anioActual, idAlmacen);
    },
    setMes(e) {
      let mes = this.meses.find(ms => ms.value == e);
      if (mes != undefined) {
        this.getDataDias(this.anioSelect, e, mes.dias, this.idAlmacen);
      }
    },
    setAnio(e) {
      let mes = this.meses.find(ms => ms.value == this.mesSelect);
      if (mes != undefined) {
        this.getDataDias(e, mes.value, mes.dias, this.idAlmacen);
      }
    },
    setAnioMes(e) {
      this.getDataMes(e, this.idAlmacen);
    },
    setAlmacen(id) {
      switch (this.tabActive) {
        case 0:
          this.initDia(id);
          break;
        case 1:
          this.initMes(id);
          break;
        case 2:
          break;

        default:
          break;
      }
    },
    getAlmacenes() {
      this.almacenes = [];
      this.almacenes.push({ value: 0, text: "TODOS" });
      this.axios.get(this.API + "/almacenes").then(r => {
        if (r.data) {
          r.data.map(al => {
            this.almacenes.push({ value: al.IdAlmacen, text: al.Almacen });
          });
        }
      });
    },
    getDataDias(anio, mes, dias, idAlmacen) {
      this.lstDia = [];
      this.axios
        .get(this.API + "ventas/estadistica/dia", {
          params: {
            anio: anio,
            mes: mes,
            idAlmacen: idAlmacen || this.idAlmacen
          }
        })
        .then(response => {
          this.lstDia = response.data;
          this.$refs.chartGrafica.GraficarDias(mes.value, dias, response.data);
          this.calcularTotalDia();
        })
        .catch(error => {
          console.log(error);
        });
    },
    calcularTotalDia() {
      let totalDia = this.lstDia.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalDia = totalDia.toFixed(2);
    },
    getDataMes(anio, idAlmacen) {
      this.lstMes = [];
      this.axios
        .get(this.API + "ventas/estadistica/mes", {
          params: {
            anio: anio,
            idAlmacen: idAlmacen || this.idAlmacen
          }
        })
        .then(response => {
          this.lstMes = response.data;
          this.$refs.chartGraficaMes.GraficarMes(response.data, this.meses);
          this.calcularTotalMes();
        })
        .catch(error => {
          console.log(error);
        });
    },
    calcularTotalMes() {
      let totalMes = this.lstMes.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalMes = totalMes.toFixed(2);
    },
    cambioOpcion(e) {
      switch (e) {
        case 0:
          this.initDia(0);
          this.idAlmacen = 0;
          break;
        case 1:
          this.initMes(0);
          this.idAlmacen = 0;
          break;
        case 2:
          this.idAlmacen = 0;
          this.fechaDesde = this.$moment().format("YYYY-MM-01");
          this.fechaHasta = this.$moment().format("YYYY-MM-DD");
          break;

        default:
          break;
      }
    },
    fechaRango() {
      this.lstRango = [];
      if (this.fechaDesde == "") {
        this.$toasted.show("Ingrese una fecha validad en fecha Inicio");
        return;
      }
      if (this.fechaHasta == "") {
        this.$toasted.show("Ingrese una fecha validad en fecha fin");
        return;
      }
      this.axios
        .get(this.API + "ventas/estadistica/rango", {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta,
            idAlmacen: this.idAlmacen
          }
        })
        .then(response => {
          this.lstRango = response.data;
          this.$refs.chartGraficaRango.GraficarRango(response.data);
          this.calcularTotalRango();
        })
        .catch(error => {
          console.log(error);
        });
    },
    calcularTotalRango() {
      let totalRango = this.lstRango.reduce((suma, listado) => {
        return suma + parseFloat(listado.Total);
      }, 0);

      this.totalRango = totalRango.toFixed(2);
    }
  },
  components: {
    BarChart,
    LineChart
  }
};
</script>
