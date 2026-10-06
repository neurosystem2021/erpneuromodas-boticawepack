<template>
    <div class="simple-kardex-page">
        <b-container class="simple-kardex-shell" fluid>
          <header class="simple-kardex-hero"><div class="simple-kardex-hero__icon"><i class="fas fa-clipboard-list"></i></div><div><small>CONTROL DE INVENTARIO</small><h1>Reporte Kardex</h1><p>Consulta entradas, salidas y saldos por producto y almacén.</p></div><span><i class="fas fa-sync-alt"></i> Movimientos actualizados</span></header>
          <b-row class="simple-kardex-filters">
            <b-col cols="12" class="simple-kardex-filter-title"><div><small>FILTROS DEL REPORTE</small><h2>Configura tu consulta</h2></div><i class="fas fa-filter"></i></b-col>
            <b-col md="2" class="my-1">
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
                <label>Seleccionar Producto:</label>
                <b-input-group>
                  <autocomplete
                    ref="autocompleteProducto"
                    :url="API + 'productos'"
                    :onShouldRenderChild="renderChild"
                    anchor="Producto"
                    label="writer"
                    placeholder="Seleccionar Producto"
                    :on-select="setProducto"
                    :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                    :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-input-group>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="kardex.fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="kardex.fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1 simple-kardex-actions">
              <b-button class="btn-kardex" :disabled="!producto.IdProducto" @click="verKardex"><i class="fas fa-list"></i> Kardex</b-button>
              <b-button class="btn-valued" :disabled="!producto.IdProducto" @click="verKardexValorizado"><i class="fas fa-coins"></i> Valorizado</b-button>
              <b-button class="btn-export" :disabled="!producto.IdProducto" :href="hrefXls" target="_blank"><i class="fas fa-file-excel"></i> Exportar</b-button>
            </b-col>
          </b-row>

          <b-row v-if="producto.IdProducto" class="simple-kardex-report">
            <b-col cols="12" class="simple-kardex-report-head"><div><small>RESULTADOS</small><h2>{{ esValorizado ? 'Kardex valorizado' : 'Kardex de movimientos' }}</h2></div><span><i class="fas fa-box"></i> {{ producto.Producto }}</span></b-col>
            <div class="simple-kardex-table-scroll"><table class="table b-table table-striped table-hover simple-kardex-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Serie</th>
                  <th>Número</th>
                  <th>Tipo de <br>Operación</th>
                  <th class="column-entry">Entradas</th>
                  <th v-if="esValorizado">Costo<br> Unitario</th>
                  <th v-if="esValorizado">Costo<br> Total</th>
                  <th class="column-exit">Salidas</th>
                  <th v-if="esValorizado">Costo<br> Unitario</th>
                  <th v-if="esValorizado">Costo<br> Total</th>
                  <th class="column-balance">Saldo Final</th>
                  <th v-if="esValorizado">Costo<br> Unitario</th>
                  <th v-if="esValorizado">Costo<br> Total</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, i) in kardexSaldoFecha" :key="i">
                  <td>{{ prod.Fecha }}</td>
                  <td>{{ prod.CodSunat }}</td>
                  <td>{{ prod.Serie }}</td>
                  <td>{{ prod.Numero }}</td>
                  <td>{{ prod.TipoDocSunat }}</td>
                  <td class="value-entry">{{ prod.IngresoCantidad }}</td>
                  <td v-if="esValorizado">{{ prod.IngresoPrecio | redondear }}</td>
                  <td v-if="esValorizado">{{ prod.IngresoCantidad * prod.IngresoPrecio | redondear }}</td>
                  <td class="value-exit">{{ prod.SalidaCantidad }}</td>
                  <td v-if="esValorizado">{{ prod.SalidaPrecio | redondear }}</td>
                  <td v-if="esValorizado">{{ prod.SalidaCantidad * prod.SalidaPrecio | redondear}}</td>
                  <td class="value-balance">{{ prod.saldo }}</td>
                  <td v-if="esValorizado">{{ prod.saldoCostoUnitario | redondear }}</td>
                  <td v-if="esValorizado">{{ prod.saldoCostoTotal | redondear }}</td>
                </tr>
              </tbody>
            </table></div>
          </b-row>
          <b-row v-else class="simple-kardex-empty"><div><i class="fas fa-box-open"></i></div><h3>Selecciona un producto</h3><p>Elige el almacén, producto y periodo para generar un reporte detallado.</p></b-row>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  mounted () {
    this.loadFirstAlmacen()
  },
  data () {
    return {
      API: config.API,
      producto: {},
      almacen: {},
      esValorizado: false,
      kardex: {
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      kardexSelect: []
    }
  },
  computed: {
    hrefXls () {
      let tipoKardex = 'kardexsimple'

      if (this.esValorizado) {
        tipoKardex = 'kardexvalorizado'
      }

      return '/api/index.php/reporte/' + tipoKardex + '?idAlmacen=' + this.almacen.IdAlmacen + '&idProducto='+this.producto.IdProducto+'&producto='+this.producto.Producto+'&codigoBarra='+this.producto.CodigoBarra+'&fechaDesde='+this.kardex.fechaDesde+'&fechaHasta='+this.kardex.fechaHasta;
    },
    kardexSaldo () {
      let saldo = 0
      let saldoCostoTotal = 0
      let saldoCostoUnitario = 0
      return this.kardexSelect.map(item => {
          saldo += (item.IngresoCantidad - item.SalidaCantidad)
          item.saldo = saldo
          if (item.IngresoCantidad > 0) {
            saldoCostoTotal = saldoCostoTotal + (item.IngresoCantidad * item.IngresoPrecio)
            saldoCostoUnitario = saldoCostoTotal / saldo
          } else {
            // la salida Precio es el ultimo costo unitario de saldo
            item.SalidaPrecio = saldoCostoUnitario
            
            saldoCostoTotal = saldo * item.SalidaPrecio
            saldoCostoUnitario = item.SalidaPrecio
          }
          item.saldoCostoUnitario = saldoCostoUnitario
          item.saldoCostoTotal = saldoCostoTotal
          return item
      })
    },
    kardexSaldoFecha () {
      let inicialEntradas = 0 
      let inicialSalidas = 0
      let inicialSaldoCostoTotal = 0
      let inicialSaldoCostoUnitario = 0

      let arr = this.kardexSaldo.filter(item => {
        if (item.Fecha >= this.kardex.fechaDesde) {
          return item.Fecha >= this.kardex.fechaDesde
        } else {
          inicialEntradas += parseFloat(item.IngresoCantidad)
          inicialSalidas += parseFloat(item.SalidaCantidad)
          inicialSaldoCostoTotal = item.saldoCostoTotal
          inicialSaldoCostoUnitario = item.saldoCostoUnitario
          return false
        }
      })

      arr.unshift({
        Fecha: this.kardex.fechaDesde,
        TipoDocSunat: 16,
        IngresoCantidad: inicialEntradas,
        SalidaCantidad: inicialSalidas,
        saldo: inicialEntradas - inicialSalidas,
        saldoCostoTotal: inicialSaldoCostoTotal,
        saldoCostoUnitario: inicialSaldoCostoUnitario 
      })

      return arr

    }
  },
  methods: {
    loadFirstAlmacen () {
      this.axios.get(this.API + 'almacenes/primero')
        .then(res => {
          this.$refs.inputAlmacen.setValue(res.data.Almacen)
          this.almacen = res.data
        })
    },
    setAlmacen (obj) {
      this.almacen = obj
    },
    setProducto (obj) {
      this.producto = obj
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },  
    renderChild(data) {
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo}
      `
    },
    verKardex () {
      this.esValorizado = false
      this.axios.get(this.API + 'productos/kardex/' + this.producto.IdProducto + '?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaHasta: this.kardex.fechaHasta
          }
      }).then((response) => {
          this.kardexSelect = response.data
      })
    },
    verKardexValorizado () {
      this.verKardex()
      this.esValorizado = true
    },
    exportar () {

    }
  }
}
</script>
<style>
.simple-kardex-page{min-height:100vh;padding:12px 20px 28px;color:#112b49;background:linear-gradient(145deg,#f4f9fd,#eaf3fa 55%,#f9fcfe)}
.simple-kardex-shell{max-width:1500px;padding:0!important}
.simple-kardex-hero{display:flex;align-items:center;gap:13px;min-height:76px;padding:11px 20px;margin-bottom:12px;color:#fff;border-radius:20px;background:linear-gradient(118deg,#082b58,#0b568e 63%,#08a0c4);box-shadow:0 15px 34px rgba(9,52,94,.18)}
.simple-kardex-hero__icon{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;color:#79edff;font-size:21px;border:1px solid rgba(255,255,255,.25);border-radius:14px;background:rgba(255,255,255,.1)}
.simple-kardex-hero small{color:#84ecff;font-size:10px;font-weight:900;letter-spacing:1.5px}.simple-kardex-hero h1{margin:0;font-size:25px;font-weight:900}.simple-kardex-hero p{margin:1px 0 0;color:rgba(239,249,255,.83);font-size:12px}.simple-kardex-hero>span{margin-left:auto;padding:8px 13px;white-space:nowrap;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(2,31,64,.25);font-size:11px;font-weight:800}.simple-kardex-hero>span i{margin-right:6px;color:#43e5a3}
.simple-kardex-filters{margin:0 0 12px;padding:15px 16px 17px;border:1px solid #d9e7f2;border-radius:20px;background:rgba(255,255,255,.97);box-shadow:0 12px 30px rgba(31,69,108,.08)}
.simple-kardex-filter-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;padding-bottom:10px;border-bottom:1px solid #e5edf5}.simple-kardex-filter-title small,.simple-kardex-report-head small{color:#1388c8;font-size:9px;font-weight:900;letter-spacing:1.3px}.simple-kardex-filter-title h2,.simple-kardex-report-head h2{margin:1px 0 0;font-size:18px;font-weight:900}.simple-kardex-filter-title>i{color:#168fd1;font-size:18px}
.simple-kardex-page label{margin-bottom:5px;color:#29445f;font-size:12px;font-weight:850}.simple-kardex-page .form-control{min-height:46px;color:#18304e;font-weight:700;border:1px solid #cfdeea;border-radius:11px;background:#fbfdff;box-shadow:none}.simple-kardex-page .form-control:focus{border-color:#1599df;box-shadow:0 0 0 4px rgba(21,153,223,.13)}
.simple-kardex-actions{display:flex;align-items:flex-end;flex-wrap:wrap;gap:7px}.simple-kardex-actions .btn{min-height:43px;padding:0 12px;border:0;border-radius:10px;font-size:11px;font-weight:900;box-shadow:0 7px 15px rgba(24,69,111,.13)}.btn-kardex{background:linear-gradient(135deg,#0874b6,#09a3c5)!important}.btn-valued{background:linear-gradient(135deg,#6551c7,#8b65df)!important}.btn-export{background:linear-gradient(135deg,#09955b,#21c178)!important}.simple-kardex-actions .btn:disabled{filter:grayscale(.4);opacity:.45;box-shadow:none}
.simple-kardex-report{margin:0;overflow:hidden;border:1px solid #d8e6f1;border-radius:20px;background:#fff;box-shadow:0 13px 32px rgba(31,69,108,.09)}.simple-kardex-report-head{display:flex;align-items:center;justify-content:space-between;padding:14px 17px;border-bottom:1px solid #e3edf5}.simple-kardex-report-head>span{max-width:55%;overflow:hidden;padding:7px 11px;color:#315571;text-overflow:ellipsis;white-space:nowrap;border-radius:999px;background:#edf7fd;font-size:11px;font-weight:800}.simple-kardex-table-scroll{width:100%;overflow-x:auto}.simple-kardex-table{min-width:1100px;margin:0}.simple-kardex-table thead{color:#fff;background:linear-gradient(105deg,#0a376d,#087fae)}.simple-kardex-table th{padding:12px 10px;vertical-align:middle;border:0;font-size:11px;letter-spacing:.2px}.simple-kardex-table td{padding:10px;vertical-align:middle;border-color:#e5edf4;font-size:12px}.simple-kardex-table tbody tr:hover{background:#eef8ff!important}.column-entry{background:rgba(24,190,112,.22)}.column-exit{background:rgba(248,70,92,.22)}.column-balance{background:rgba(50,153,235,.22)}.value-entry{color:#00894d;font-weight:900}.value-exit{color:#d21e3d;font-weight:900}.value-balance{color:#0875b7;font-weight:900}
.simple-kardex-empty{min-height:330px;display:flex;align-items:center;justify-content:center;flex-direction:column;margin:0;color:#71869a;text-align:center;border:1px dashed #c7d9e8;border-radius:20px;background:rgba(255,255,255,.62)}.simple-kardex-empty>div{display:grid;place-items:center;width:64px;height:64px;margin-bottom:12px;color:#168fca;font-size:25px;border-radius:20px;background:#e1f3fc}.simple-kardex-empty h3{margin:0;color:#244560;font-size:18px;font-weight:900}.simple-kardex-empty p{margin:5px 0 0;font-size:12px}
@media(max-width:991px){.simple-kardex-hero>span{display:none}.simple-kardex-actions{max-width:100%;flex:0 0 100%}.simple-kardex-empty{min-height:250px}}
@media(max-width:575px){.simple-kardex-page{padding:8px}.simple-kardex-hero{padding:10px 13px;border-radius:16px}.simple-kardex-hero p{display:none}.simple-kardex-hero h1{font-size:20px}.simple-kardex-actions .btn{flex:1 1 auto}}
</style>
