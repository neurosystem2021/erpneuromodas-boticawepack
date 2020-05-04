<template>
    <div>
    <h2 align-h="center" class="text-center" >Reporte de Cierre de Caja General</h2>
      <br>
        <b-container class="bv-example-row">
          <b-row>
            <b-col md="4" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
                <br>
              <b-button variant="success" size="sm" @click="getCierres">Buscar</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th>Almacen</th>
                  <th>Ingresos</th>
                  <th>Salidas</th>
                  <th>Saldo</th>
                </tr> 
              </thead>
              <tbody>
                <tr v-for="(cierre, i) in cierresAlmacen" :key="i">
                  <td>{{ cierre.Almacen }}</td>
                  <td>{{ cierre.Ingresos }}</td>
                  <td>{{ cierre.Salidas }}</td>
                  <td><b>{{ cierre.Total }}</b></td>
                </tr>
              </tbody>
            </table>
          </b-row>
          <b-alert variant="success" class="text-center no-print" show><b>TOTAL GENERAL: S/. {{ totalGeneral | moneda }}</b></b-alert>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  mounted() {
  },
  data () {
    return {
      API: config.API,
      producto: {},
      esValorizado: false,
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      kardexSelect: [],
      cierres: []
    }
  },
  computed: {
    totalGeneral() {
      let total = 0;
      this.cierres.map(cierre => {
        total += cierre.Total
      })
      return total
    },
    cierresAlmacen() {
        let cierres = []
        this.cierres.map(c => {
            if (!cierres[c.IdAlmacen]) {
                let ca = this.cierres.filter(ci => ci.IdAlmacen === c.IdAlmacen)
                cierres[c.IdAlmacen] = {
                    Almacen: c.Almacen,
                    Ingresos: ca.reduce( (a, b) => a + b.Ingresos , 0),
                    Salidas: ca.reduce( (a, b) => a + b.Salidas , 0),
                    Total: ca.reduce( (a, b) => a + b.Ingresos , 0) - ca.reduce( (a, b) => a + b.Salidas , 0)
                }
            }
        })
        
        return cierres.filter(c => c);
    }
  },
  methods: {
    getCierres() {
      this.axios.get(this.API + 'cierrecaja/reporte', {
        params: {
            fechaDesde: this.fechaDesde + ' 00:00:00',
            fechaHasta: this.fechaHasta + ' 23:59:59'
        }
      }).then((response) => {
        this.cierres = response.data
      })
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    }, 
  }
}
</script>
<style>
    

</style>