<template>
  <div>
    <b-card no-body>
      <b-tabs v-model="tabActive" pills card>

        <!-- ═══════════════ TAB: VENTAS POR DÍA ═══════════════ -->
        <b-tab title="Ventas Por Dia" @click="cambioOpcion(0)" active>
          <b-container fluid>

            <!-- Filtros arriba -->
            <b-row class="mb-3">
              <b-col md="3">
                <b-form-group label="Almacén:">
                  <b-form-select v-model="idAlmacen" :options="almacenes" @change="setAlmacen($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group label="Seleccione Año:">
                  <b-form-select v-model="anioSelect" :options="anios" @change="setAnio($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group label="Seleccione Mes:">
                  <b-form-select v-model="mesSelect" :options="meses" @change="setMes($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group label="Tipo Comprobante:">
                  <b-form-select v-model="idTipoDoc" :options="opcionesTipoDoc" size="md" />
                </b-form-group>
              </b-col>
            </b-row>

            <!-- Layout principal: KPIs izquierda | Gráfica+Tabla derecha -->
            <b-row>

              <!-- Columna KPI (izquierda) -->
              <b-col md="2" class="d-flex flex-column" style="gap: 12px;">
                <b-card class="kpi-card text-center border-primary">
                  <div class="kpi-label">💰 Total del Mes</div>
                  <div class="kpi-value text-primary">S/ {{ kpiDia.total }}</div>
                </b-card>
                <b-card class="kpi-card text-center border-success">
                  <div class="kpi-label">📈 Día Mayor Venta</div>
                  <div class="kpi-value text-success">Día {{ kpiDia.mejorDia }}</div>
                  <small class="text-muted kpi-small">S/ {{ kpiDia.mejorValor }}</small>
                </b-card>
                <b-card class="kpi-card text-center border-warning">
                  <div class="kpi-label">📊 Promedio Diario</div>
                  <div class="kpi-value text-warning">S/ {{ kpiDia.promedio }}</div>
                </b-card>
                <b-card class="kpi-card text-center border-info">
                  <div class="kpi-label">📅 Días con Venta</div>
                  <div class="kpi-value text-info">{{ kpiDia.diasConVenta }}</div>
                </b-card>
              </b-col>

              <!-- Columna Gráfica + Tabla (derecha) -->
              <b-col md="10">
                <h5 class="font-weight-bold">Valor de venta por día</h5>
                <bar-chart ref="chartGrafica" id="grafico" :height="160" :altura="550"></bar-chart>

                <b-table class="mt-3" :items="tablaDias" :fields="camposDia" small striped hover bordered responsive>
                  <template slot="Total" slot-scope="data">
                    <span :class="parseFloat(data.item.Total) < kpiDia.promedioRaw * 0.5 ? 'text-danger font-weight-bold' : ''">
                      S/ {{ data.item.Total }}
                    </span>
                  </template>
                  <template slot="estado" slot-scope="data">
                    <b-badge :variant="parseFloat(data.item.Total) >= kpiDia.promedioRaw ? 'success' : 'warning'">
                      {{ parseFloat(data.item.Total) >= kpiDia.promedioRaw ? '▲ Sobre promedio' : '▼ Bajo promedio' }}
                    </b-badge>
                  </template>
                </b-table>
              </b-col>

            </b-row>
          </b-container>
        </b-tab>

        <!-- ═══════════════ TAB: VENTAS POR MES ═══════════════ -->
        <b-tab title="Ventas Por Mes" @click="cambioOpcion(1)">
          <b-container fluid>

            <b-row class="mb-3">
              <b-col md="4">
                <b-form-group label="Almacén:">
                  <b-form-select v-model="idAlmacen" :options="almacenes" @change="setAlmacen($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="4">
                <b-form-group label="Seleccione Año:">
                  <b-form-select v-model="anioSelect" :options="anios" @change="setAnioMes($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="4">
                <b-form-group label="Tipo Comprobante:">
                  <b-form-select v-model="idTipoDoc" :options="opcionesTipoDoc" size="md" />
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>

              <!-- Columna KPI (izquierda) -->
              <b-col md="2" class="d-flex flex-column" style="gap: 12px;">
                <b-card class="kpi-card text-center border-primary">
                  <div class="kpi-label">💰 Total del Año</div>
                  <div class="kpi-value text-primary">S/ {{ kpiMes.total }}</div>
                </b-card>
                <b-card class="kpi-card text-center border-success">
                  <div class="kpi-label">🏆 Mejor Mes</div>
                  <div class="kpi-value text-success">{{ kpiMes.mejorMes }}</div>
                  <small class="text-muted kpi-small">S/ {{ kpiMes.mejorValor }}</small>
                </b-card>
                <b-card class="kpi-card text-center border-warning">
                  <div class="kpi-label">📊 Promedio Mensual</div>
                  <div class="kpi-value text-warning">S/ {{ kpiMes.promedio }}</div>
                </b-card>
                <b-card class="kpi-card text-center border-danger">
                  <div class="kpi-label">📉 Mes más Bajo</div>
                  <div class="kpi-value text-danger">{{ kpiMes.peorMes }}</div>
                  <small class="text-muted kpi-small">S/ {{ kpiMes.peorValor }}</small>
                </b-card>
              </b-col>

              <!-- Columna Gráfica + Tabla (derecha) -->
              <b-col md="10">
                <h5 class="font-weight-bold">Valor de venta por mes</h5>
                <bar-chart ref="chartGraficaMes" id="graficoMes" :height="160" :altura="450"></bar-chart>

                <b-table class="mt-3" :items="tablaMeses" :fields="camposMes" small striped hover bordered responsive>
                  <template slot="Total" slot-scope="data">
                    S/ {{ data.item.Total }}
                  </template>
                  <template slot="Participacion" slot-scope="data">
                    <b-progress :value="data.item.PorcentajeRaw" max="100" show-value variant="info" height="20px" />
                  </template>
                </b-table>
              </b-col>

            </b-row>
          </b-container>
        </b-tab>

        <!-- ═══════════════ TAB: VENTAS POR RANGO ═══════════════ -->
        <b-tab title="Ventas Por Rango" @click="cambioOpcion(2)">
          <b-container fluid>

            <b-row class="mb-3">
              <b-col md="3">
                <b-form-group label="Almacén:">
                  <b-form-select v-model="idAlmacen" :options="almacenes" @change="setAlmacen($event)" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Inicio:">
                  <b-form-input v-model="fechaDesde" type="date" />
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Fecha Fin:">
                  <b-form-input v-model="fechaHasta" type="date" />
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group label="Tipo Comprobante:">
                <b-form-select v-model="idTipoDoc" :options="opcionesTipoDoc" size="md" />
                </b-form-group>
              </b-col>
              <b-col md="2" class="text-center">
                <b-form-group><br>
                  <b-button @click="fechaRango" variant="success" size="lg">Generar</b-button>
                </b-form-group>
              </b-col>
            </b-row>

            <b-row>

              <!-- Columna KPI (izquierda) -->
              <b-col md="2" class="d-flex flex-column" style="gap: 12px;">
                <template v-if="kpiRango.total > 0">
                  <b-card class="kpi-card text-center border-primary">
                    <div class="kpi-label">💰 Total del Rango</div>
                    <div class="kpi-value text-primary">S/ {{ kpiRango.total }}</div>
                  </b-card>
                  <b-card class="kpi-card text-center border-success">
                    <div class="kpi-label">📈 Mejor Día</div>
                    <div class="kpi-value text-success">{{ kpiRango.mejorDia }}</div>
                    <small class="text-muted kpi-small">S/ {{ kpiRango.mejorValor }}</small>
                  </b-card>
                  <b-card class="kpi-card text-center border-warning">
                    <div class="kpi-label">📊 Promedio Diario</div>
                    <div class="kpi-value text-warning">S/ {{ kpiRango.promedio }}</div>
                  </b-card>
                  <b-card class="kpi-card text-center border-info">
                    <div class="kpi-label">📅 Días Analizados</div>
                    <div class="kpi-value text-info">{{ kpiRango.dias }}</div>
                  </b-card>
                </template>
                <div v-else class="text-muted text-center mt-3" style="font-size:0.85rem;">
                  Genere un rango para ver los indicadores
                </div>
              </b-col>

              <!-- Columna Gráfica + Tabla (derecha) -->
              <b-col md="10">
                <h5 class="font-weight-bold">Valor de venta por rango</h5>
                <line-chart ref="chartGraficaRango" id="graficoRango" :height="160"></line-chart>

                <b-table v-if="tablaRango.length > 0" class="mt-3" :items="tablaRango" :fields="camposRango" small striped hover bordered responsive>
                  <template slot="Total" slot-scope="data">
                    S/ {{ data.item.Total }}
                  </template>
                </b-table>
              </b-col>

            </b-row>
          </b-container>
        </b-tab>

      </b-tabs>
    </b-card>
  </div>
</template>

<script>
import BarChart from '@/components/BarChart'
import LineChart from '@/components/LineChart'
import config from '@/store/config.js'

export default {
  mounted() {
    this.initDia(0)
    this.obtenerTiposDocumento()
    this.getAlmacenes()
  },
  watch: {
    idTipoDoc(nuevoValor) {
      if (this.tabActive === 0) {
        const mes = this.meses.find(ms => ms.value === this.mesSelect)
        if (mes) this.getDataDias(this.anioSelect, this.mesSelect, mes.dias, this.idAlmacen)
      } else if (this.tabActive === 1) {
        this.getDataMes(this.anioSelect, this.idAlmacen)
      }
    }
  },
  data() {
    return {
      API: config.API,
      diasSemana: ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
      meses: [
        { value: 1,  text: 'Enero',      dias: 31 }, { value: 2,  text: 'Febrero',    dias: 29 },
        { value: 3,  text: 'Marzo',      dias: 31 }, { value: 4,  text: 'Abril',       dias: 30 },
        { value: 5,  text: 'Mayo',       dias: 31 }, { value: 6,  text: 'Junio',       dias: 30 },
        { value: 7,  text: 'Julio',      dias: 31 }, { value: 8,  text: 'Agosto',      dias: 31 },
        { value: 9,  text: 'Septiembre', dias: 30 }, { value: 10, text: 'Octubre',     dias: 31 },
        { value: 11, text: 'Noviembre',  dias: 30 }, { value: 12, text: 'Diciembre',   dias: 31 }
      ],
      anios: [],
      mesSelect:  1,
      tabActive:  0,
      anioSelect: Number(this.$moment().format('YYYY')),
      fechaDesde: this.$moment().format('YYYY-MM-01'),
      fechaHasta: this.$moment().format('YYYY-MM-DD'),
      idAlmacen:  0,
      almacenes: [{ value: 0, text: 'TODOS' }],
      
      idTipoDoc:       "TODOS",
      opcionesTipoDoc: [{ value: "TODOS", text: "TODOS" }],

      tablaDias:  [],
      tablaMeses: [],
      tablaRango: [],

      camposDia: [
        { key: 'Dia',    label: 'Día',    sortable: true  },
        { key: 'Total',  label: 'Monto',  sortable: true  },
        { key: 'estado', label: 'Estado', sortable: false }
      ],
      camposMes: [
        { key: 'Mes',           label: 'Mes',             sortable: true  },
        { key: 'Total',         label: 'Monto',           sortable: true  },
        { key: 'Participacion', label: '% Participación', sortable: false }
      ],
      camposRango: [
        { key: 'Fecha', label: 'Fecha', sortable: true },
        { key: 'Total', label: 'Monto', sortable: true }
      ],

      kpiDia: {
        total: '0.00', mejorDia: '-', mejorValor: '0.00',
        promedio: '0.00', promedioRaw: 0, diasConVenta: 0
      },
      kpiMes: {
        total: '0.00', mejorMes: '-', mejorValor: '0.00',
        promedio: '0.00', peorMes: '-', peorValor: '0.00'
      },
      kpiRango: {
        total: '0.00', mejorDia: '-', mejorValor: '0.00',
        promedio: '0.00', dias: 0
      }
    }
  },

  methods: {
    initDia(idAlmacen) {
      const anioActual = Number(this.$moment().format('YYYY'))
      const mesActual  = Number(this.$moment().format('MM'))
      this.anios = [{ value: anioActual, text: '' + anioActual }]
      for (let i = 1; i <= 5; i++)
        this.anios.push({ value: anioActual - i, text: '' + (anioActual - i) })
      const mesd = this.meses.find(ms => ms.value === mesActual)
      if (mesd) {
        this.mesSelect  = mesActual
        this.anioSelect = anioActual
        this.getDataDias(anioActual, mesActual, mesd.dias, idAlmacen)
      }
    },
    initMes(idAlmacen) {
      const anioActual = Number(this.$moment().format('YYYY'))
      this.anios = [{ value: anioActual, text: '' + anioActual }]
      for (let i = 1; i <= 5; i++)
        this.anios.push({ value: anioActual - i, text: '' + (anioActual - i) })
      this.anioSelect = anioActual
      this.getDataMes(anioActual, idAlmacen)
    },
    setMes(e) {
      const mes = this.meses.find(ms => ms.value === e)
      if (mes) this.getDataDias(this.anioSelect, e, mes.dias, this.idAlmacen)
    },
    setAnio(e) {
      const mes = this.meses.find(ms => ms.value === this.mesSelect)
      if (mes) this.getDataDias(e, mes.value, mes.dias, this.idAlmacen)
    },
    setAnioMes(e) { this.getDataMes(e, this.idAlmacen) },
    setAlmacen(id) {
      if (this.tabActive === 0) this.initDia(id)
      else if (this.tabActive === 1) this.initMes(id)
    },
    cambioOpcion(e) {
      this.idAlmacen = 0
      this.idTipoDoc = 'TODOS'   // ← AÑADIR
      if (e === 0) this.initDia(0)
      else if (e === 1) this.initMes(0)
      else {
        this.fechaDesde = this.$moment().format('YYYY-MM-01')
        this.fechaHasta = this.$moment().format('YYYY-MM-DD')
      }
    },
    getAlmacenes() {
      this.almacenes = [{ value: 0, text: 'TODOS' }]
      this.axios.get(this.API + '/almacenes').then(r => {
        if (r.data)
          r.data.forEach(al => this.almacenes.push({ value: al.IdAlmacen, text: al.Almacen }))
      })
    },
    calcularKpiDia(data) {
      if (!data || !data.length) return
      const totales  = data.map(d => parseFloat(d.Total) || 0)
      const total    = totales.reduce((a, b) => a + b, 0)
      const promedio = total / totales.length
      const maxVal   = Math.max(...totales)
      const maxItem  = data[totales.indexOf(maxVal)]
      this.kpiDia = {
        total: total.toFixed(2), mejorDia: maxItem.Dia,
        mejorValor: maxVal.toFixed(2), promedio: promedio.toFixed(2),
        promedioRaw: promedio, diasConVenta: totales.filter(v => v > 0).length
      }
    },
    calcularKpiMes(data, meses) {
      if (!data || !data.length) return
      const totales   = data.map(d => parseFloat(d.Total) || 0)
      const total     = totales.reduce((a, b) => a + b, 0)
      const promedio  = total / totales.length
      const maxVal    = Math.max(...totales)
      const positivos = totales.filter(v => v > 0)
      const minVal    = positivos.length ? Math.min(...positivos) : 0
      const maxItem   = data[totales.indexOf(maxVal)]
      const minItem   = data[totales.indexOf(minVal)]
      const getNombre = v => { const m = meses.find(ms => ms.value === v); return m ? m.text : v }
      this.kpiMes = {
        total: total.toFixed(2), mejorMes: getNombre(maxItem.Mes),
        mejorValor: maxVal.toFixed(2), promedio: promedio.toFixed(2),
        peorMes: minItem ? getNombre(minItem.Mes) : '-', peorValor: minVal.toFixed(2)
      }
    },
    calcularKpiRango(data) {
      if (!data || !data.length) return
      const totales  = data.map(d => parseFloat(d.Total) || 0)
      const total    = totales.reduce((a, b) => a + b, 0)
      const promedio = total / totales.length
      const maxVal   = Math.max(...totales)
      const maxItem  = data[totales.indexOf(maxVal)]
      this.kpiRango = {
        total: total.toFixed(2), mejorDia: `${maxItem.Dia}/${maxItem.Mes}`,
        mejorValor: maxVal.toFixed(2), promedio: promedio.toFixed(2), dias: data.length
      }
    },
    construirTablaDias(data) {
      if (!data) return
      this.tablaDias = data.map(d => {
        const total = (Math.round((parseFloat(d.Total) || 0) * 100) / 100).toFixed(2)
        const v = parseFloat(total)
        const fecha = new Date(this.anioSelect, this.mesSelect - 1, d.Dia)
        const nombreDia = this.diasSemana[fecha.getDay()]
        return {
          Dia: `${d.Dia} - ${nombreDia}`,
          Total: total,
          _rowVariant: v === 0 ? 'secondary' : v < this.kpiDia.promedioRaw * 0.5 ? 'danger' : ''
        }
      })
    },
    construirTablaMeses(data, meses) {
      if (!data) return
      const total = data.reduce((a, d) => a + (parseFloat(d.Total) || 0), 0)
      this.tablaMeses = data.map(d => {
        const mes   = meses.find(ms => ms.value === d.Mes)
        const monto = parseFloat(d.Total) || 0
        const pct   = total > 0 ? (monto / total * 100) : 0
        return {
          Mes: mes ? mes.text : d.Mes, Total: monto.toFixed(2),
          Participacion: pct.toFixed(1) + '%', PorcentajeRaw: parseFloat(pct.toFixed(1))
        }
      })
    },
    construirTablaRango(data) {
      if (!data) return
      this.tablaRango = data.map(d => {
        const fecha     = new Date(d.Anio || this.anioSelect, d.Mes - 1, d.Dia)
        const nombreDia = this.diasSemana[fecha.getDay()]
        return {
          Fecha: `${d.Dia}/${d.Mes} - ${nombreDia}`,
          Total: (Math.round((parseFloat(d.Total) || 0) * 100) / 100).toFixed(2)
        }
      })
    },
    exportarCSV(data, nombre) {
      if (!data || !data.length) return
      const headers = Object.keys(data[0]).filter(k => k !== 'PorcentajeRaw').join(',')
      const rows    = data.map(r =>
        Object.entries(r).filter(([k]) => k !== 'PorcentajeRaw').map(([, v]) => v).join(',')
      ).join('\n')
      const blob = new Blob([`${headers}\n${rows}`], { type: 'text/csv;charset=utf-8;' })
      const url  = URL.createObjectURL(blob)
      const a    = document.createElement('a')
      a.href = url
      a.download = `${nombre}_${this.$moment().format('YYYYMMDD')}.csv`
      a.click()
      URL.revokeObjectURL(url)
    },
    obtenerTiposDocumento() {
      this.axios.get(this.API + '/ventas/tipos').then(res => {
        this.opcionesTipoDoc = [
          { value: 'TODOS', text: 'TODOS' },
          ...res.data.map(d => ({ value: d.IdTipoDoc, text: d.TipoDoc }))
        ]
      }).catch(e => console.error(e))
    },
    getDataDias(anio, mes, dias, idAlmacen) {
      const tipoDoc = this.idTipoDoc !== 'TODOS' ? this.idTipoDoc : undefined
      this.axios.get(this.API + 'ventas/estadistica/dia', {
        params: {
          anio, mes,
          idAlmacen: (idAlmacen !== null && idAlmacen !== undefined) ? idAlmacen : this.idAlmacen,
          tipoDoc: tipoDoc
        }
      }).then(r => {
        this.calcularKpiDia(r.data)
        this.construirTablaDias(r.data)
        this.$nextTick(() => {
          if (this.$refs.chartGrafica)
            this.$refs.chartGrafica.GraficarDias(mes, dias, r.data)
        })
      }).catch(e => console.log(e))
    },

    getDataMes(anio, idAlmacen) {
      const tipoDoc = this.idTipoDoc !== 'TODOS' ? this.idTipoDoc : undefined
      this.axios.get(this.API + 'ventas/estadistica/mes', {
        params: {
          anio,
          idAlmacen: (idAlmacen !== null && idAlmacen !== undefined) ? idAlmacen : this.idAlmacen,
          tipoDoc: tipoDoc
        }
      }).then(r => {
        this.calcularKpiMes(r.data, this.meses)
        this.construirTablaMeses(r.data, this.meses)
        this.$nextTick(() => {
          if (this.$refs.chartGraficaMes)
            this.$refs.chartGraficaMes.GraficarMes(r.data, this.meses)
        })
      }).catch(e => console.log(e))
    },

    fechaRango() {
      if (!this.fechaDesde) return this.$toasted.show('Ingrese una fecha válida en fecha Inicio')
      if (!this.fechaHasta) return this.$toasted.show('Ingrese una fecha válida en fecha Fin')
      this.axios.get(this.API + 'ventas/estadistica/rango', {
        params: {
          fechaDesde: this.fechaDesde, fechaHasta: this.fechaHasta,
          idAlmacen: this.idAlmacen,
          tipoDoc: this.idTipoDoc !== 'TODOS' ? this.idTipoDoc : undefined  // ← AÑADIR
        }
      }).then(r => {
        this.calcularKpiRango(r.data)
        this.construirTablaRango(r.data)
        this.$nextTick(() => {
          if (this.$refs.chartGraficaRango)
            this.$refs.chartGraficaRango.GraficarRango(r.data)
        })
      }).catch(e => console.log(e))
    },
  },

  components: { BarChart, LineChart }
}
</script>

<style scoped>
#grafico canvas,
#graficoMes canvas,
#graficoRango canvas {
  height: 250px !important;
}

.kpi-card {
  border-radius: 12px;
  margin-bottom: 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.10);
  min-height: 140px;        /* ← más alto */
}

.kpi-card .card-body {
  padding: 16px 10px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  width: 100%;
}

.kpi-label {
  font-size: 1.3rem;          /* ← más grande */
  color: #343a40;
  font-weight: bold;
  margin-bottom: 8px;
  text-align: center;
  line-height: 1.4;
}

.kpi-value {
  font-size: 1.9rem;        /* ← más grande */
  font-weight: bold;
  line-height: 1.2;
}

.kpi-small {
  font-size: 1rem;          /* ← más grande */
  font-weight: bold;
  color: #343a40 !important;
  margin-top: 4px;
}
</style>