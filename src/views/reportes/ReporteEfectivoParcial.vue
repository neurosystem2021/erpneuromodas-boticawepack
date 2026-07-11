<template>
  <div>
    <h2 class="text-center">Dashboard de Ventas con Efectivo Parcial</h2>
    <b-container fluid style="width: calc(100% - 50px)">

      <!-- FILTROS -->
      <b-row class="align-items-end mb-2">
        <b-col md="3">
          <b-form-group label="Fecha Inicio:">
            <b-form-input v-model="filtros.fechaDesde" type="date" @change="cargarTodo" />
          </b-form-group>
        </b-col>
        <b-col md="3">
          <b-form-group label="Fecha Fin:">
            <b-form-input v-model="filtros.fechaHasta" type="date" @change="cargarTodo" />
          </b-form-group>
        </b-col>
        <b-col md="3">
          <b-form-group label="Almacén:">
            <autocomplete
              ref="inputAlmacen"
              :url="API + 'almacenes'"
              anchor="Almacen"
              initValue=""
              label="writer"
              :on-select="setAlmacen"
              :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
              :classes="{ input: 'form-control' }">
            </autocomplete>
          </b-form-group>
        </b-col>
        <b-col md="3">
          <b-form-group label="Buscar cliente:">
            <b-form-input v-model="filtros.q" placeholder="Nombre o DNI/RUC" @input="debounceCargarDetalle" />
          </b-form-group>
        </b-col>

        <b-col md="4">
        <b-form-group label="Departamento">
            <b-form-select v-model="ubigeoSeleccionado.departamento">
            <option :value="null">-- Todos --</option>
            <option v-for="dep in $store.state.listUbigeo" :key="dep.id" :value="dep">{{ dep.name }}</option>
            </b-form-select>
        </b-form-group>
        </b-col>
        <b-col md="4">
        <b-form-group label="Provincia">
            <b-form-select v-model="ubigeoSeleccionado.provincia" :disabled="!ubigeoSeleccionado.departamento">
            <option :value="null">-- Todas --</option>
            <option v-for="prov in provinciasDisponibles" :key="prov.id" :value="prov">{{ prov.name }}</option>
            </b-form-select>
        </b-form-group>
        </b-col>
        <b-col md="4">
        <b-form-group label="Distrito">
            <b-form-select v-model="ubigeoSeleccionado.distrito" :disabled="!ubigeoSeleccionado.provincia">
            <option :value="null">-- Todos --</option>
            <option v-for="dist in distritosDisponibles" :key="dist.id" :value="dist">{{ dist.name }}</option>
            </b-form-select>
        </b-form-group>
        </b-col>

        <b-col md="12" class="mb-3">
          <b-button variant="primary" @click="cargarTodo" :disabled="cargando">
            <i class="fa fa-search"></i> Generar
          </b-button>
        </b-col>
      </b-row>

      <!-- KPIs -->
      <b-row class="mb-3">
        <b-col md="3" sm="6">
          <b-card class="text-center kpi-card">
            <div class="kpi-label">Vendido</div>
            <div class="kpi-valor">S/ {{ totalVendido | redondear }}</div>
          </b-card>
        </b-col>
        <b-col md="3" sm="6">
          <b-card class="text-center kpi-card">
            <div class="kpi-label">Cobrado</div>
            <div class="kpi-valor text-success">S/ {{ totalCobrado | redondear }}</div>
          </b-card>
        </b-col>
        <b-col md="3" sm="6">
          <b-card class="text-center kpi-card">
            <div class="kpi-label">Saldo pendiente</div>
            <div class="kpi-valor text-danger">S/ {{ totalSaldo | redondear }}</div>
          </b-card>
        </b-col>
        <b-col md="3" sm="6">
          <b-card class="text-center kpi-card">
            <div class="kpi-label">% de cobranza</div>
            <div class="kpi-valor">{{ porcentajeCobranza }}%</div>
          </b-card>
        </b-col>
      </b-row>

      <!-- GRAFICOS -->
      <b-row class="mb-3">
        <b-col md="12">
          <b-card>
            <h6>Vendido, Cobrado y Saldo por Departamento</h6>
            <div style="position: relative; height: 280px;">
              <canvas ref="canvasDepto"></canvas>
            </div>
          </b-card>
        </b-col>
      </b-row>

      <b-row class="mb-3">
        <b-col md="6">
          <b-card>
            <h6>% de Cobranza por Departamento</h6>
            <div style="position: relative; height: 260px;">
              <canvas ref="canvasCobranza"></canvas>
            </div>
          </b-card>
        </b-col>
        <b-col md="6">
          <b-card>
            <h6>Saldo Pendiente por Antigüedad</h6>
            <div style="position: relative; height: 260px;">
              <canvas ref="canvasAging"></canvas>
            </div>
          </b-card>
        </b-col>
      </b-row>

      <b-row class="mb-3">
        <b-col md="12">
          <b-card>
            <h6>Top Clientes con Mayor Saldo Pendiente</h6>
            <div style="position: relative; height: 300px;">
              <canvas ref="canvasTop"></canvas>
            </div>
          </b-card>
        </b-col>
      </b-row>

      <!-- TABLA RESUMEN GEOGRÁFICO -->
      <h5>Resumen por Departamento</h5>
      <b-table small striped hover :items="resumenGeografico" :fields="fieldsResumen" :busy="isBusyResumen">
        <template slot="TotalVendido" slot-scope="data">S/ {{ data.item.TotalVendido | redondear }}</template>
        <template slot="TotalCobrado" slot-scope="data">S/ {{ data.item.TotalCobrado | redondear }}</template>
        <template slot="TotalSaldo" slot-scope="data">
          <b :class="data.item.TotalSaldo > 0 ? 'text-danger' : ''">S/ {{ data.item.TotalSaldo | redondear }}</b>
        </template>
        <template slot="PorcentajeCobranza" slot-scope="data">
          <b-badge :variant="data.item.PorcentajeCobranza >= 70 ? 'success' : (data.item.PorcentajeCobranza >= 40 ? 'warning' : 'danger')">
            {{ data.item.PorcentajeCobranza }}%
          </b-badge>
        </template>
      </b-table>

      <br />

      <!-- TABLA DETALLE -->
      <h5>Detalle de ventas ({{ detalle.length }})</h5>
      <b-table small striped hover :items="detalle" :fields="fieldsDetalle" :busy="isBusyDetalle">
        <template slot="Saldo" slot-scope="data">
          <b :class="data.item.Saldo > 0 ? 'text-danger' : 'text-success'">S/ {{ data.item.Saldo | redondear }}</b>
        </template>
        <template slot="Total" slot-scope="data">S/ {{ data.item.Total | redondear }}</template>
        <template slot="Aplicado" slot-scope="data">S/ {{ data.item.Aplicado | redondear }}</template>
        <template slot="DiasAntiguedad" slot-scope="data">
          <b-badge :variant="data.item.DiasAntiguedad > 60 ? 'danger' : (data.item.DiasAntiguedad > 30 ? 'warning' : 'success')">
            {{ data.item.DiasAntiguedad }} días
          </b-badge>
        </template>
      </b-table>

    </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import Chart from "chart.js";

export default {
  mounted() {
    if (this.$store.state.listUbigeo.length === 0) {
      this.$store.dispatch("GetUbigeo");
    }
    this.cargarTodo();
  },
  beforeDestroy() {
    this.destruirCharts();
  },
  data() {
    return {
      API: config.API,
      cargando: false,
      filtros: {
        fechaDesde: this.$moment().subtract(3, "months").format("YYYY-MM-DD"),
        fechaHasta: this.$moment().format("YYYY-MM-DD"),
        idalmacen: null,
        q: ""
      },
      detalle: [],
      resumenGeografico: [],
      aging: [],
      topClientes: [],
      isBusyDetalle: false,
      isBusyResumen: false,
      debounceTimer: null,
      ubigeoSeleccionado: {
        departamento: null,
        provincia: null,
        distrito: null
      },
      provinciasDisponibles: [],
      distritosDisponibles: [],
      charts: {
        depto: null,
        cobranza: null,
        aging: null,
        top: null
      },
      fieldsResumen: [
        { key: "Zona", label: "Departamento", sortable: true },
        { key: "NumVentas", label: "N° Ventas", sortable: true },
        { key: "NumClientes", label: "N° Clientes", sortable: true },
        { key: "TotalVendido", label: "Vendido", sortable: true },
        { key: "TotalCobrado", label: "Cobrado", sortable: true },
        { key: "TotalSaldo", label: "Saldo", sortable: true },
        { key: "PorcentajeCobranza", label: "% Cobranza", sortable: true }
      ],
      fieldsDetalle: [
        { key: "FechaDoc", label: "Fecha", sortable: true },
        { key: "Correlativo", label: "Comprobante" },
        { key: "Cliente", sortable: true },
        { key: "DniRuc", label: "DNI/RUC" },
        { key: "Departamento", sortable: true },
        { key: "Provincia", sortable: true },
        { key: "Distrito", sortable: true },
        { key: "Almacen" },
        { key: "Total", sortable: true },
        { key: "Aplicado", sortable: true },
        { key: "Saldo", sortable: true },
        { key: "DiasAntiguedad", label: "Antigüedad", sortable: true }
      ]
    };
  },
    computed: {
        totalVendido() {
        return this.detalle.reduce((sum, v) => sum + parseFloat(v.Total), 0);
        },
        totalCobrado() {
        return this.detalle.reduce((sum, v) => sum + parseFloat(v.Aplicado), 0);
        },
        totalSaldo() {
        return this.detalle.reduce((sum, v) => sum + parseFloat(v.Saldo), 0);
        },
        porcentajeCobranza() {
        if (this.totalVendido === 0) return 0;
        return ((this.totalCobrado / this.totalVendido) * 100).toFixed(1);
        }
    },
    watch: {
        'ubigeoSeleccionado.departamento'(dep) {
        this.ubigeoSeleccionado.provincia = null;
        this.ubigeoSeleccionado.distrito = null;
        this.distritosDisponibles = [];
        this.provinciasDisponibles = dep ? dep.children : [];
        this.cargarTodo();
        },
        'ubigeoSeleccionado.provincia'(prov) {
        this.ubigeoSeleccionado.distrito = null;
        this.distritosDisponibles = prov ? prov.children : [];
        this.cargarTodo();
        },
        'ubigeoSeleccionado.distrito'() {
        this.cargarTodo();
        }
    },
  methods: {
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    setAlmacen(obj) {
      this.filtros.idalmacen = obj.IdAlmacen;
      this.cargarTodo();
    },
    debounceCargarDetalle() {
      clearTimeout(this.debounceTimer);
      this.debounceTimer = setTimeout(() => this.cargarTodo(), 500);
    },
    obtenerCodUbigeo() {
      if (this.ubigeoSeleccionado.distrito) return this.ubigeoSeleccionado.distrito.id;
      if (this.ubigeoSeleccionado.provincia) return this.ubigeoSeleccionado.provincia.id;
      if (this.ubigeoSeleccionado.departamento) return this.ubigeoSeleccionado.departamento.id;
      return "";
    },

    paramsBase() {
      return {
        fechaDesde: this.filtros.fechaDesde,
        fechaHasta: this.filtros.fechaHasta,
        idalmacen: this.filtros.idalmacen,
        ubigeo: this.obtenerCodUbigeo()
      };
    },
    cambioDepartamento(dep) {
      this.ubigeoSeleccionado.provincia = null;
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = [];
      this.provinciasDisponibles = dep ? dep.children : [];
      this.cargarTodo();
    },
    cambioProvincia(prov) {
      this.ubigeoSeleccionado.distrito = null;
      this.distritosDisponibles = prov ? prov.children : [];
      this.cargarTodo();
    },

    paramsBase() {
      return {
        fechaDesde: this.filtros.fechaDesde,
        fechaHasta: this.filtros.fechaHasta,
        idalmacen: this.filtros.idalmacen,
        ubigeo: this.obtenerCodUbigeo()
      };
    },

    async cargarTodo() {
      this.cargando = true;
      await Promise.all([
        this.cargarDetalle(),
        this.cargarResumenGeografico(),
        this.cargarAging(),
        this.cargarTopClientes()
      ]);
      this.cargando = false;
      this.$nextTick(() => this.renderCharts());
    },

    cargarDetalle() {
      this.isBusyDetalle = true;
      return this.axios.get(this.API + "reporte/efectivoparcial", {
        params: { ...this.paramsBase(), q: this.filtros.q }
      })
        .then(response => {
          this.detalle = response.data;
        })
        .catch(() => {
          this.$toasted.show("Error al cargar el detalle de Efectivo Parcial");
        })
        .finally(() => {
          this.isBusyDetalle = false;
        });
    },

    cargarResumenGeografico() {
      this.isBusyResumen = true;
      return this.axios.get(this.API + "reporte/efectivoparcial/geografico", {
        params: { ...this.paramsBase(), nivel: "departamento" }
      })
        .then(response => {
          this.resumenGeografico = response.data;
        })
        .catch(() => {
          this.$toasted.show("Error al cargar el resumen geográfico");
        })
        .finally(() => {
          this.isBusyResumen = false;
        });
    },

    cargarAging() {
      return this.axios.get(this.API + "reporte/efectivoparcial/aging", {
        params: this.paramsBase()
      })
        .then(response => {
          this.aging = response.data;
        })
        .catch(() => {
          this.$toasted.show("Error al cargar la antigüedad de saldos");
        });
    },

    cargarTopClientes() {
      return this.axios.get(this.API + "reporte/efectivoparcial/topclientes", {
        params: { ...this.paramsBase(), limit: 8 }
      })
        .then(response => {
          this.topClientes = response.data;
        })
        .catch(() => {
          this.$toasted.show("Error al cargar el top de clientes");
        });
    },

    destruirCharts() {
      Object.keys(this.charts).forEach(key => {
        if (this.charts[key]) {
          this.charts[key].destroy();
          this.charts[key] = null;
        }
      });
    },

    renderCharts() {
      this.destruirCharts();
      const mutedColor = "#6c757d";
      const gridColor = "rgba(108,117,125,0.15)";
      const baseOpt = { responsive: true, maintainAspectRatio: false };

      // 1) Vendido / Cobrado / Saldo por departamento
      if (this.$refs.canvasDepto) {
        this.charts.depto = new Chart(this.$refs.canvasDepto, {
          type: "bar",
          data: {
            labels: this.resumenGeografico.map(r => r.Zona),
            datasets: [
              { label: "Vendido", data: this.resumenGeografico.map(r => r.TotalVendido), backgroundColor: "#2a78d6" },
              { label: "Cobrado", data: this.resumenGeografico.map(r => r.TotalCobrado), backgroundColor: "#1baf7a" },
              { label: "Saldo", data: this.resumenGeografico.map(r => r.TotalSaldo), backgroundColor: "#e34948" }
            ]
          },
          options: {
            ...baseOpt,
            legend: { position: "top", labels: { fontColor: mutedColor } },
            scales: {
              xAxes: [{ gridLines: { display: false }, ticks: { fontColor: mutedColor } }],
              yAxes: [{
                gridLines: { color: gridColor },
                ticks: { fontColor: mutedColor, callback: v => "S/ " + v }
              }]
            }
          }
        });
      }

      // 2) % de cobranza por departamento
      if (this.$refs.canvasCobranza) {
        const ordenado = [...this.resumenGeografico].sort((a, b) => b.PorcentajeCobranza - a.PorcentajeCobranza);
        this.charts.cobranza = new Chart(this.$refs.canvasCobranza, {
          type: "horizontalBar",
          data: {
            labels: ordenado.map(r => r.Zona),
            datasets: [{
              data: ordenado.map(r => r.PorcentajeCobranza),
              backgroundColor: ordenado.map(r => r.PorcentajeCobranza >= 70 ? "#0ca30c" : (r.PorcentajeCobranza >= 40 ? "#fab219" : "#e34948"))
            }]
          },
          options: {
            ...baseOpt,
            legend: { display: false },
            scales: {
              xAxes: [{
                ticks: { beginAtZero: true, max: 100, fontColor: mutedColor, callback: v => v + "%" },
                gridLines: { color: gridColor }
              }],
              yAxes: [{ gridLines: { display: false }, ticks: { fontColor: mutedColor } }]
            }
          }
        });
      }

      // 3) Aging (antigüedad de saldo)
      if (this.$refs.canvasAging) {
        this.charts.aging = new Chart(this.$refs.canvasAging, {
          type: "doughnut",
          data: {
            labels: this.aging.map(a => a.Rango),
            datasets: [{
              data: this.aging.map(a => a.Saldo),
              backgroundColor: ["#0ca30c", "#fab219", "#ec835a", "#d03b3b"],
              borderWidth: 2,
              borderColor: "#ffffff"
            }]
          },
          options: {
            ...baseOpt,
            legend: { position: "bottom", labels: { fontColor: mutedColor, boxWidth: 12 } },
            cutoutPercentage: 60
          }
        });
      }

      // 4) Top clientes con mayor saldo
      if (this.$refs.canvasTop) {
        this.charts.top = new Chart(this.$refs.canvasTop, {
          type: "horizontalBar",
          data: {
            labels: this.topClientes.map(c => c.Cliente),
            datasets: [{
              data: this.topClientes.map(c => c.Saldo),
              backgroundColor: "#2a78d6"
            }]
          },
          options: {
            ...baseOpt,
            legend: { display: false },
            scales: {
              xAxes: [{
                gridLines: { color: gridColor },
                ticks: { beginAtZero: true, fontColor: mutedColor, callback: v => "S/ " + v }
              }],
              yAxes: [{ gridLines: { display: false }, ticks: { fontColor: mutedColor } }]
            }
          }
        });
      }
    }
  }
};
</script>

<style scoped>
.kpi-card {
  min-height: 90px;
}
.kpi-label {
  font-size: 0.85rem;
  color: #888;
}
.kpi-valor {
  font-size: 1.5rem;
  font-weight: bold;
}
</style>