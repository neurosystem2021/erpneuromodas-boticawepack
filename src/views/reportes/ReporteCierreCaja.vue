<template>
    <div>
    <h2 align-h="center" class="text-center" >Reporte de Cierre de Caja</h2>
      <br>
        <b-container class="bv-example-row">
          <b-row>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Almacen:</label>
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
                <br>
              <b-button :disabled="!almacen.IdAlmacen" variant="success" size="sm" @click="getCierres">Buscar</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Fecha Cierre</th>
                  <th>Turno</th>
                  <th>Usuario</th>
                  <th>Ingresos</th>
                  <th>Ventas</th>
                  <th>Salidas</th>
                  <th>Saldo</th>
                  <th>Ver</th>
                </tr> 
              </thead>
              <tbody>
                <tr v-for="(cierre, i) in cierres" :key="i">
                  <td>{{ cierre.IdCierreCaja }}</td>
                  <td>{{ cierre.FechaCierre }}</td>
                  <td v-if="cierre.IdTurno === 1">Mañana</td>
                  <td v-if="cierre.IdTurno === 2">Tarde</td>
                  <td v-if="cierre.IdTurno === 3">Noche</td>
                  <td>{{ cierre.UsuarioReg }}</td>
                  <td>{{ cierre.Ingresos }}</td>
                  <td>{{ cierre.Ventas }}</td>
                  <td>{{ cierre.Salidas }}</td>
                  <td><b>{{ cierre.Total }}</b></td>
                  <td>
                    <b-button :href="'/views/ve_cierreform.php?idCierre=' + cierre.IdCierreCaja" variant="primary" target="_blank">Ver</b-button>
                  </td>
                </tr>
              </tbody>
              <tfoot>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>S/ {{ totalIngresos | moneda }}</th>
                <th>S/ {{ totalVentas | moneda }}</th>
                <th>S/ {{ totalSalidas | moneda }}</th>
                <!--<th>S/ {{ totalSaldo | moneda }}</th>-->
                <th></th>
              </tfoot>
            </table>
          </b-row>
          <b-alert variant="success" class="text-center no-print" show>
            <b>TOTAL GENERAL: S/ {{ totalGeneral | moneda }}</b>
          </b-alert>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  mounted() {
    this.getVendedor()
  },
  data () {
    return {
      API: config.API,
      producto: {},
      almacen: {},
      esValorizado: false,
      fechaDesde: this.$moment().format("YYYY-MM-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      kardexSelect: [],
      cierres: []
    }
  },
  computed: {
    // totalGeneral() {
    //   let total = 0;
    //   this.cierres.map(cierre => {
    //     total += cierre.Total
    //   })
    //   return total
    // }
    totalIngresos() {
      let total = 0;
      for (var i = 0; i < this.cierres.length; i++) {
        total += this.cierres[i].Ingresos
      }
      return total
    },
    totalVentas() {
      let total = 0;
      for (var i = 0; i < this.cierres.length; i++) {
        total += this.cierres[i].Ventas
      }
      return total
    },
    totalSalidas() {
      let total = 0;
      for (var i = 0; i < this.cierres.length; i++) {
        total += this.cierres[i].Salidas
      }
      return total
    },
    totalSaldo() {
      let total = 0;
      for (var i = 0; i < this.cierres.length; i++) {
        total += this.cierres[i].Total
      }
      return total
    },
    totalGeneral() {
      let total = 0;
      
      for (var i = 0; i < this.cierres.length; i++) {
        if (!this.cierres[i].Ingresos && !this.cierres[i].Salidas) {
          continue;
        } else {
          total = this.cierres[i].Total
          break;
        }
      }
      return total
    }
  },
  methods: {
    getCierres() {
      this.axios.get(this.API + 'cierrecaja/reporte', {
        params: {
            idAlmacen: this.almacen.IdAlmacen,
            fechaDesde: this.fechaDesde + ' 00:00:00',
            fechaHasta: this.fechaHasta + ' 23:59:59'
        }
      }).then((response) => {
        this.cierres = response.data
      })
    },
    getVendedor () {
      let usuario = sessionStorage.getItem('User')
      this.axios.get(this.API + 'usuarios/' + usuario)
        .then((response) => {
            // this.setVendedor(response.data)
            // this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
            // this.$refs.inputVendedor.$refs.input.disabled = true
            this.loadAlmacen(response.data.IdAlmacen)
            // this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta)
            // bloquear ambos inputs
            if (response.data.IdUsuarioPerfil !== 1) {
              this.$refs.inputAlmacen.$refs.input.disabled = true
            }
            // this.$refs.inputPuntoVenta.$refs.input.disabled = true
            //this.getNumeroVenta()
        })
        .catch((error) => {
            console.log(error)
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },
    loadAlmacen (id) {
        this.axios.get(this.API + 'almacenes/id/' + id)
        .then(res => {
            this.setAlmacen(res.data)
        })
    },
    setAlmacen (obj) {
      this.almacen = obj
      this.$refs.inputAlmacen.setValue(obj.Almacen)
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