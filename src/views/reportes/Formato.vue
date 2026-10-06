<template>
    <div class="format37-page">
        <b-container class="format37-shell" fluid>
          <header class="format37-hero"><div class="format37-hero__icon"><i class="fas fa-file-invoice-dollar"></i></div><div><small>LIBRO DE INVENTARIOS</small><h1>Formato 3.7</h1><p>Genera el detalle valorizado de saldos por almacén y periodo anual.</p></div><span><i class="fas fa-check-circle"></i> Formato tributario</span></header>
          <b-row class="format37-filter-card">
            <b-col cols="12" class="format37-filter-title"><div><small>PARÁMETROS DEL REPORTE</small><h2>Configura la generación</h2></div><i class="fas fa-sliders-h"></i></b-col>
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
            <b-col md="3" class="my-1 format37-actions">
              <b-form-group>
                <label>Año:</label>
                <b-form-input
                  :disabled="desactivado"
                  v-model="kardex.anio"
                  type="text"
                  step="1"
                  placeholder="Año"></b-form-input>
              </b-form-group>
            </b-col>
            <!--<b-col md="2" class="my-1">
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
            </b-col>-->
            <b-col md="3" class="my-1">
              <!--<b-button :disabled="!producto.IdProducto" variant="success" size="sm" @click="verKardex">Kardex</b-button>
              <b-button :disabled="!producto.IdProducto" variant="success" size="sm" @click="verKardexValorizado">Kardex Valorizado</b-button>
              -->
              <b-button class="btn-format-generate" :disabled="!kardex.anio || desactivado" @click="generarExcel"><i class="fas fa-cogs"></i> Generar reporte</b-button>
              <b-button class="btn-format-export" :disabled="!kardex.anio || desactivado" :href="hrefXls" target="_blank"><i class="fas fa-file-excel"></i> Exportar Excel</b-button>
            </b-col>
          </b-row>

          <b-row class="format37-status-card">
            <b-col md="12">
              <div class="format37-status-head"><div><small>ESTADO DE GENERACIÓN</small><h2>{{ desactivado ? 'Procesando productos' : (progress ? 'Reporte preparado' : 'Listo para generar') }}</h2></div><strong>{{ progress }} / {{ cantidadProductos }}</strong></div>
              <b-progress class="format37-progress" :value="progress" :max="cantidadProductos || 1" variant="success" :animated="desactivado"></b-progress>
              <div class="format37-status-info"><span><i class="fas fa-warehouse"></i> {{ almacen.Almacen || 'Seleccionando almacén' }}</span><span><i class="fas fa-calendar-alt"></i> Periodo {{ kardex.anio }}</span><span><i class="fas fa-boxes"></i> {{ cantidadProductos }} productos</span></div>
            </b-col>

            <!--<table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Tipo</th>
                  <th>Serie</th>
                  <th>Número</th>
                  <th>Tipo de <br>Operación</th>
                  <th>Entradas</th>
                  <th v-if="esValorizado">Costo<br> Unitario</th>
                  <th v-if="esValorizado">Costo<br> Total</th>
                  <th>Salidas</th>
                  <th v-if="esValorizado">Costo<br> Unitario</th>
                  <th v-if="esValorizado">Costo<br> Total</th>
                  <th>Saldo Final</th>
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
                  <td>{{ prod.IngresoCantidad }}</td>
                  <td v-if="esValorizado">{{ prod.IngresoPrecio | redondear }}</td>
                  <td v-if="esValorizado">{{ prod.IngresoCantidad * prod.IngresoPrecio | redondear }}</td>
                  <td>{{ prod.SalidaCantidad }}</td>
                  <td v-if="esValorizado">{{ prod.SalidaPrecio | redondear }}</td>
                  <td v-if="esValorizado">{{ prod.SalidaCantidad * prod.SalidaPrecio | redondear}}</td>
                  <td>{{ prod.saldo }}</td>
                  <td v-if="esValorizado">{{ prod.saldoCostoUnitario | redondear }}</td>
                  <td v-if="esValorizado">{{ prod.saldoCostoTotal | redondear }}</td>
                </tr>
              </tbody>
            </table>-->
          </b-row>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  mounted () {
    this.loadFirstAlmacen()
    this.loadProductos()
  },
  data () {
    return {
      API: config.API,
      producto: {},
      productos: [],
      page: 1,
      limit: 100,
      desactivado: false,
      cantidadProductos: 0,
      progress: 0,
      prodGenerados: [],
      almacen: {},
      esValorizado: false,
      kardex: {
        anio: this.$moment().format("YYYY"),
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      kardexSelect: []
    }
  },
  computed: {
    hrefXls () {
      return '/api/index.php/reporte/formato37/exportar';
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
    generarExcel () {
      // console.log(this.page);return;
      this.desactivado = true
      //this.productos.forEach(async (el) => {
        this.axios.post(this.API + 'reporte/formato37', {
          row: (this.page-2) * this.limit,
          idAlmacen: this.almacen.IdAlmacen,
          anio: this.kardex.anio,
          // fechaDesde: this.kardex.fechaDesde,
          // fechaHasta: this.kardex.fechaHasta,
          idProductos: this.productos.map((el) => el.IdProducto)
        })
          .then(res => {
            this.progress = this.limit*(this.page-1)
            this.progress = this.progress > this.cantidadProductos ? this.cantidadProductos : this.progress
            setTimeout(() => {
                this.loadProductos(() => {
                  this.generarExcel()
                })
            }, 2000);
            //this.prodGenerados.push(el)
          })
      //})
    },
    loadProductos (cb) {
      this.axios.get(this.API + 'productos/count')
        .then(res => {
          this.cantidadProductos = res.data.total
        })

      this.axios.get(this.API + 'productos?controlaStock=1&limit=' + this.limit + '&page='+ this.page)
        .then(res => {
          if(res.data.length) {
            this.productos = res.data
            this.page += 1
            if (cb) cb()
          } else {
            this.$toasted.success('Reporte generado correctamente, ahora puede exportarlo')
            this.progress = this.cantidadProductos
            this.desactivado = false
          }
        })
    },
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
.format37-page{min-height:100vh;padding:12px 20px 28px;color:#112b49;background:linear-gradient(145deg,#f4f9fd,#eaf3fa 55%,#f9fcfe)}
.format37-shell{max-width:1450px;padding:0!important}.format37-hero{display:flex;align-items:center;gap:13px;min-height:76px;padding:11px 20px;margin-bottom:12px;color:#fff;border-radius:20px;background:linear-gradient(118deg,#082b58,#0b568e 63%,#08a0c4);box-shadow:0 15px 34px rgba(9,52,94,.18)}
.format37-hero__icon{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;color:#79edff;font-size:21px;border:1px solid rgba(255,255,255,.25);border-radius:14px;background:rgba(255,255,255,.1)}.format37-hero small{color:#84ecff;font-size:10px;font-weight:900;letter-spacing:1.5px}.format37-hero h1{margin:0;font-size:25px;font-weight:900}.format37-hero p{margin:1px 0 0;color:rgba(239,249,255,.83);font-size:12px}.format37-hero>span{margin-left:auto;padding:8px 13px;white-space:nowrap;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(2,31,64,.25);font-size:11px;font-weight:800}.format37-hero>span i{margin-right:6px;color:#43e5a3}
.format37-filter-card{margin:0 0 12px;padding:15px 16px 17px;border:1px solid #d9e7f2;border-radius:20px;background:rgba(255,255,255,.97);box-shadow:0 12px 30px rgba(31,69,108,.08)}.format37-filter-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;padding-bottom:10px;border-bottom:1px solid #e5edf5}.format37-filter-title small,.format37-status-head small{color:#1388c8;font-size:9px;font-weight:900;letter-spacing:1.3px}.format37-filter-title h2,.format37-status-head h2{margin:1px 0 0;font-size:18px;font-weight:900}.format37-filter-title>i{color:#168fd1;font-size:18px}.format37-page label{margin-bottom:5px;color:#29445f;font-size:12px;font-weight:850}.format37-page .form-control{min-height:46px;color:#18304e;font-weight:700;border:1px solid #cfdeea;border-radius:11px;background:#fbfdff;box-shadow:none}.format37-page .form-control:focus{border-color:#1599df;box-shadow:0 0 0 4px rgba(21,153,223,.13)}
.format37-actions{display:flex;align-items:flex-end;gap:8px}.format37-actions .btn{min-height:46px;padding:0 15px;border:0;border-radius:11px;font-size:12px;font-weight:900;box-shadow:0 7px 15px rgba(24,69,111,.13)}.btn-format-generate{background:linear-gradient(135deg,#0874b6,#09a3c5)!important}.btn-format-export{background:linear-gradient(135deg,#09955b,#21c178)!important}.format37-actions .btn:disabled{filter:grayscale(.4);opacity:.45;box-shadow:none}
.format37-status-card{margin:0;min-height:245px;padding:20px;border:1px solid #d8e6f1;border-radius:20px;background:#fff;box-shadow:0 13px 32px rgba(31,69,108,.09)}.format37-status-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}.format37-status-head strong{padding:8px 12px;color:#0874ad;border-radius:999px;background:#e5f5fc;font-size:13px}.format37-progress{height:15px!important;overflow:hidden;border-radius:999px;background:#e5edf4;box-shadow:inset 0 2px 4px rgba(25,60,94,.08)}.format37-progress .progress-bar{border-radius:999px;background:linear-gradient(90deg,#0aa96c,#29d889);box-shadow:0 0 14px rgba(20,196,120,.35)}.format37-status-info{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:25px}.format37-status-info span{display:flex;align-items:center;min-height:58px;padding:12px 14px;color:#35546e;border:1px solid #deebf4;border-radius:14px;background:#f7fbfe;font-size:12px;font-weight:800}.format37-status-info i{display:grid;place-items:center;width:32px;height:32px;margin-right:10px;color:#1289c8;border-radius:10px;background:#def2fc}
@media(max-width:991px){.format37-hero>span{display:none}.format37-actions{max-width:100%;flex:0 0 100%;margin-top:6px}.format37-status-info{grid-template-columns:1fr}}
@media(max-width:575px){.format37-page{padding:8px}.format37-hero{padding:10px 13px;border-radius:16px}.format37-hero p{display:none}.format37-hero h1{font-size:20px}.format37-actions{flex-wrap:wrap}.format37-actions .btn{flex:1 1 auto}.format37-status-card{padding:15px}.format37-status-head{align-items:flex-start}.format37-status-head strong{font-size:11px}}
</style>
