<template>
  <div class="cash-report-blue">
    <header class="cash-report-hero">
      <div class="cash-report-hero-icon"><i class="fas fa-cash-register"></i></div>
      <div><small>CAJA Y BANCO</small><h1>Reporte de Cierre de Caja</h1><p>Consulta y revisa los cierres registrados por almacén y periodo.</p></div>
      <span class="cash-report-count"><b>{{ cierres.length }}</b> cierres</span>
    </header>

    <b-container fluid class="cash-report-content">
      <section class="cash-report-filters no-print">
        <div class="cash-report-section-title"><i class="fas fa-filter"></i><div><strong>Filtros del reporte</strong><small>Selecciona el almacén y rango de fechas</small></div></div>
        <b-row align-v="end">
          <b-col lg="4" md="6">
            <b-form-group label="Almacén">
              <autocomplete required="required" ref="inputAlmacen" :url="API + 'almacenes'" anchor="Almacen" label="writer"
                placeholder="Seleccione un almacén" :on-select="setAlmacen" :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                :classes="{input: 'form-control'}"></autocomplete>
            </b-form-group>
          </b-col>
          <b-col lg="3" md="6"><b-form-group label="Fecha de inicio"><b-form-input v-model="fechaDesde" type="date" step="1"></b-form-input></b-form-group></b-col>
          <b-col lg="3" md="6"><b-form-group label="Fecha final"><b-form-input v-model="fechaHasta" type="date" step="1"></b-form-input></b-form-group></b-col>
          <b-col lg="2" md="6"><b-button block :disabled="!almacen.IdAlmacen" variant="primary" @click="getCierres"><i class="fas fa-search mr-2"></i>Buscar</b-button></b-col>
        </b-row>
      </section>

      <section class="cash-report-metrics">
        <article><span><i class="fas fa-arrow-down"></i></span><small>Ingresos</small><strong>S/ {{ totalIngresos | moneda }}</strong></article>
        <article><span><i class="fas fa-shopping-cart"></i></span><small>Ventas</small><strong>S/ {{ totalVentas | moneda }}</strong></article>
        <article><span><i class="fas fa-arrow-up"></i></span><small>Salidas</small><strong>S/ {{ totalSalidas | moneda }}</strong></article>
        <article class="metric-main"><span><i class="fas fa-wallet"></i></span><small>Total general</small><strong>S/ {{ totalGeneral | moneda }}</strong></article>
      </section>

      <section class="cash-report-table-card">
        <div class="cash-report-table-heading"><div><strong>Historial de cierres</strong><small>Detalle consolidado del periodo seleccionado</small></div><i class="fas fa-list-alt"></i></div>
        <div class="table-responsive">
          <table class="table cash-report-table">
            <thead><tr><th>ID</th><th>Fecha de cierre</th><th>Turno</th><th>Usuario</th><th>Ingresos</th><th>Ventas</th><th>Salidas</th><th>Saldo</th><th class="text-center">Detalle</th></tr></thead>
            <tbody>
              <tr v-for="(cierre, i) in cierres" :key="i">
                <td><span class="cash-report-id">#{{ cierre.IdCierreCaja }}</span></td><td>{{ cierre.FechaCierre }}</td>
                <td><span class="cash-report-shift">{{ cierre.IdTurno === 1 ? 'Mañana' : cierre.IdTurno === 2 ? 'Tarde' : 'Noche' }}</span></td>
                <td><i class="fas fa-user-circle mr-2 text-primary"></i>{{ cierre.UsuarioReg }}</td>
                <td>S/ {{ cierre.Ingresos | moneda }}</td><td>S/ {{ cierre.Ventas | moneda }}</td><td>S/ {{ cierre.Salidas | moneda }}</td>
                <td class="cash-report-balance">S/ {{ cierre.Total | moneda }}</td>
                <td class="text-center"><b-button :href="'/views/ve_cierreform.php?idCierre=' + cierre.IdCierreCaja" variant="primary" size="sm" target="_blank"><i class="fas fa-eye mr-1"></i> Ver</b-button></td>
              </tr>
              <tr v-if="!cierres.length"><td colspan="9" class="cash-report-empty"><i class="fas fa-inbox"></i><strong>Sin cierres para mostrar</strong><small>Selecciona los filtros y presiona Buscar.</small></td></tr>
            </tbody>
            <tfoot><tr><td colspan="4">TOTALES DEL PERIODO</td><td>S/ {{ totalIngresos | moneda }}</td><td>S/ {{ totalVentas | moneda }}</td><td>S/ {{ totalSalidas | moneda }}</td><td>S/ {{ totalSaldo | moneda }}</td><td></td></tr></tfoot>
          </table>
        </div>
      </section>
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
.cash-report-blue{min-height:100vh;padding:20px;background:linear-gradient(180deg,#f7fbfe,#edf5fa);color:#10324f;font-family:Arial,sans-serif}
.cash-report-hero{display:flex;align-items:center;gap:16px;max-width:1500px;margin:0 auto;padding:21px 25px;border-radius:20px;color:#fff;background:linear-gradient(115deg,#062f5b 0%,#0878bd 70%,#12a5d2 100%);box-shadow:0 16px 34px rgba(6,47,91,.22)}
.cash-report-hero-icon{display:grid;width:54px;height:54px;flex:0 0 54px;place-items:center;border:1px solid rgba(255,255,255,.3);border-radius:16px;font-size:24px;background:rgba(255,255,255,.14)}
.cash-report-hero small,.cash-report-hero h1,.cash-report-hero p{margin:0}.cash-report-hero small{font-size:10px;font-weight:800;letter-spacing:.16em;opacity:.75}.cash-report-hero h1{margin:2px 0 3px;font-size:25px;font-weight:900}.cash-report-hero p{font-size:12px;opacity:.8}
.cash-report-count{margin-left:auto;padding:8px 14px;border:1px solid rgba(255,255,255,.25);border-radius:12px;background:rgba(255,255,255,.12);font-size:11px}.cash-report-count b{display:block;font-size:18px;text-align:center}
.cash-report-content{max-width:1500px!important;padding:16px 0!important}
.cash-report-filters,.cash-report-table-card{border:1px solid #cfe1ed;border-radius:18px;background:#fff;box-shadow:0 10px 25px rgba(7,55,99,.08)}
.cash-report-filters{padding:17px 20px}.cash-report-section-title,.cash-report-table-heading{display:flex;align-items:center;gap:11px;margin-bottom:14px}.cash-report-section-title>i{display:grid;width:38px;height:38px;place-items:center;border-radius:11px;color:#fff;background:linear-gradient(135deg,#07558b,#12a5d2)}
.cash-report-section-title strong,.cash-report-section-title small,.cash-report-table-heading strong,.cash-report-table-heading small{display:block}.cash-report-section-title strong,.cash-report-table-heading strong{font-size:14px;font-weight:900}.cash-report-section-title small,.cash-report-table-heading small{margin-top:2px;color:#6d8395;font-size:10px}
.cash-report-filters label{margin-bottom:5px;color:#496a82;font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.06em}.cash-report-filters .form-group{margin-bottom:0}.cash-report-filters .form-control{height:43px;border:1px solid #c7dce9;border-radius:11px;background:#fbfdff;font-size:13px;font-weight:700}.cash-report-filters .form-control:focus{border-color:#0878bd;box-shadow:0 0 0 3px rgba(8,120,189,.13)}.cash-report-filters .btn{height:43px;border:0;border-radius:11px;font-weight:800;background:linear-gradient(135deg,#07558b,#0784c7);box-shadow:0 7px 15px rgba(7,85,139,.2)}
.cash-report-metrics{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin:14px 0}.cash-report-metrics article{display:grid;grid-template-columns:42px 1fr;align-items:center;padding:14px 16px;border:1px solid #d4e5ef;border-radius:15px;background:#fff;box-shadow:0 8px 19px rgba(7,55,99,.06)}.cash-report-metrics article>span{display:grid;width:38px;height:38px;grid-row:1/3;place-items:center;border-radius:11px;color:#0878bd;background:#eaf5fc}.cash-report-metrics small{color:#688197;font-size:10px;font-weight:800;text-transform:uppercase}.cash-report-metrics strong{font-size:18px;font-weight:900;color:#073763}.cash-report-metrics .metric-main{border-color:#0878bd;color:#fff;background:linear-gradient(135deg,#063764,#0878bd)}.cash-report-metrics .metric-main span{color:#fff;background:rgba(255,255,255,.15)}.cash-report-metrics .metric-main small,.cash-report-metrics .metric-main strong{color:#fff}
.cash-report-table-card{overflow:hidden}.cash-report-table-heading{justify-content:space-between;margin:0;padding:15px 18px;border-bottom:1px solid #dbe8f0}.cash-report-table-heading>i{color:#0878bd;font-size:22px}
.cash-report-table{margin:0;font-size:12px}.cash-report-table thead th{padding:12px 10px;border:0;color:#fff;white-space:nowrap;background:#07558b;font-size:10px;text-transform:uppercase;letter-spacing:.035em}.cash-report-table tbody td{padding:11px 10px;border-color:#e1ebf1;vertical-align:middle}.cash-report-table tbody tr:nth-child(even) td{background:#f6faff}.cash-report-table tbody tr:hover td{background:#eaf5fc}.cash-report-id,.cash-report-shift{display:inline-block;padding:4px 8px;border-radius:8px;color:#07558b;font-weight:900;background:#e9f5fc}.cash-report-shift{color:#475f73;background:#eef3f6}.cash-report-balance{color:#07558b;font-weight:900}.cash-report-table .btn{border:0;border-radius:8px;background:linear-gradient(135deg,#07558b,#0784c7)}
.cash-report-table tfoot td{padding:12px 10px;border:0;color:#fff;background:#063764;font-weight:900}.cash-report-empty{padding:36px!important;text-align:center;color:#7890a2}.cash-report-empty i,.cash-report-empty strong,.cash-report-empty small{display:block}.cash-report-empty i{margin-bottom:8px;font-size:28px;color:#91bfd9}.cash-report-empty strong{color:#365a74;font-size:14px}.cash-report-empty small{margin-top:3px;font-size:10px}
@media(max-width:991px){.cash-report-blue{padding:12px}.cash-report-metrics{grid-template-columns:repeat(2,1fr)}.cash-report-filters .form-group{margin-bottom:12px}.cash-report-hero p{display:none}}
@media(max-width:575px){.cash-report-hero{padding:15px}.cash-report-hero h1{font-size:18px}.cash-report-count{display:none}.cash-report-metrics{grid-template-columns:1fr}.cash-report-metrics article{padding:11px 14px}}
</style>
