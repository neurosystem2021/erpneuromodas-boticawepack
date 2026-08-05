<template>
    <div class="rotation-report-page">
        <b-container class="rotation-report-shell" fluid>
          <header class="rotation-report-hero"><div class="rotation-report-hero__icon"><i class="fas fa-exchange-alt"></i></div><div><small>ANÁLISIS DE INVENTARIO</small><h1>Artículos con más movimientos</h1><p>Identifica los productos con mayor actividad de compras y ventas.</p></div><span><i class="fas fa-chart-line"></i> Reporte de rotación</span></header>
          <b-row class="rotation-filter-card">
            <b-col cols="12" class="rotation-filter-title"><div><small>FILTROS DEL REPORTE</small><h2>Configura tu consulta</h2></div><i class="fas fa-filter"></i></b-col>
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

            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Marca:</label>
                <select class="form-control" v-model="idMarca">
                  <option value="">Todas las marcas</option>
                  <option v-for="item in marcas" :key="item.IdProductoMarca" :value="item.IdProductoMarca">{{ item.ProductoMarca }}</option>
                </select>
              </b-form-group>
            </b-col>

            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1 rotation-actions">
              <b-button variant="success" @click="verArticulos"><i class="fas fa-search"></i> Generar reporte</b-button>
              
              <b-button variant="success" :href="hrefXls" target="_blank"><i class="fas fa-file-excel"></i> Exportar Excel</b-button>
            </b-col>
          </b-row>

          <b-row v-if="productos.length" class="rotation-summary">
            <b-col md="4"><div><i class="fas fa-boxes"></i><span>Productos encontrados<strong>{{ productos.length }}</strong></span></div></b-col>
            <b-col md="4"><div class="summary-sales"><i class="fas fa-arrow-up"></i><span>Total ventas<strong>{{ totalVentas }}</strong></span></div></b-col>
            <b-col md="4"><div class="summary-purchases"><i class="fas fa-arrow-down"></i><span>Total compras<strong>{{ totalCompras }}</strong></span></div></b-col>
          </b-row>
          <b-row v-if="productos.length" class="rotation-report-card">
            <b-col cols="12" class="rotation-report-head"><div><small>RESULTADOS</small><h2>Ranking de movimientos</h2></div><span><i class="fas fa-calendar-alt"></i> {{ fechaDesde }} — {{ fechaHasta }}</span></b-col>
            <div class="rotation-table-scroll"><table class="table b-table table-striped table-hover rotation-table">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Descripcion</th>
                  <th>Marca</th>
                  <th>U. Medida</th>
                  <th class="sales-column">Total<br>Ventas</th>
                  <th class="sales-column">Cantidad<br>Ventas</th>
                  <th class="sales-column">Monto<br>Ventas</th>
                  <th class="purchases-column">Total<br>Compras</th>
                  <th class="purchases-column">Cantidad<br>Compras</th>
                  <th class="purchases-column">Monto<br>Compras</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, i) in productos" :key="i">
                  <td>{{ prod.CodigoBarra }}</td>
                  <td>{{ prod.Producto | quitarcodigo }} - {{prod.ProductoCategoria + ' - ' + prod.ProductoMarca }}</td>
                  <td>{{ prod.ProductoMarca }}</td>
                  <td>{{ prod.ProductoMedicion }}</td>
                  <td class="sales-value">{{ prod.TotalVentas }}</td>
                  <td class="sales-value">{{ formatoNumero(prod.CantidadVentas) }}</td>
                  <td class="sales-value">S/ {{ formatoNumero(prod.MontoVentas) }}</td>
                  <td class="purchases-value">{{ prod.TotalCompras }}</td>
                  <td class="purchases-value">{{ formatoNumero(prod.CantidadCompras) }}</td>
                  <td class="purchases-value">S/ {{ formatoNumero(prod.MontoCompras) }}</td>
                </tr>
              </tbody>
            </table></div>
          </b-row>
          <b-row v-else class="rotation-empty"><div><i class="fas fa-chart-bar"></i></div><h3>{{ consultado ? 'No se encontraron movimientos' : 'Tu reporte aparecerá aquí' }}</h3><p>{{ consultado ? 'Prueba cambiando el almacén, la marca o el periodo.' : 'Configura los filtros y pulsa “Generar reporte”.' }}</p></b-row>
        </b-container>
    </div>
</template>
<script>
import config from '@/store/config.js'
export default {
  mounted () {
    this.loadFirstAlmacen()
    this.loadMarcas()
  },
  data () {
    return {
      API: config.API,
      almacen: {},
      marca: {},
      marcas: [],
      idMarca: "",
      productos: [],
      consultado: false,
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD")
    }
  },
  computed: {
    totalVentas () {
      return this.productos.reduce((total, producto) => total + Number(producto.TotalVentas || 0), 0)
    },
    totalCompras () {
      return this.productos.reduce((total, producto) => total + Number(producto.TotalCompras || 0), 0)
    },
    hrefXls () {
      return '/api/index.php/reporte/articulosmovimientos?idAlmacen=' + this.almacen.IdAlmacen +'&fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta+'&idMarca='+(this.idMarca || '');
    }
  },
  methods: {
    formatoNumero (valor) {
      return Number(valor || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
    },
    loadMarcas () {
      this.axios.get(this.API + 'marcas?limit=1000&q=').then(res => {
        this.marcas = Array.isArray(res.data) ? res.data : []
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
    setMarca (obj) {
      this.marca = obj
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    verArticulos () {
      this.axios.get(this.API + 'productos/masrotacion?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta,
              idMarca: this.idMarca || ''
          }
      }).then((response) => {
          this.productos = response.data
          this.consultado = true
      })
    }
  }
}
</script>
<style>
.rotation-report-page{min-height:100vh;padding:12px 20px 28px;color:#112b49;background:linear-gradient(145deg,#f4f9fd,#eaf3fa 55%,#f9fcfe)}.rotation-report-shell{max-width:1500px;padding:0!important}.rotation-report-hero{display:flex;align-items:center;gap:13px;min-height:76px;padding:11px 20px;margin-bottom:12px;color:#fff;border-radius:20px;background:linear-gradient(118deg,#082b58,#0b568e 63%,#08a0c4);box-shadow:0 15px 34px rgba(9,52,94,.18)}.rotation-report-hero__icon{display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;color:#79edff;font-size:21px;border:1px solid rgba(255,255,255,.25);border-radius:14px;background:rgba(255,255,255,.1)}.rotation-report-hero small{color:#84ecff;font-size:10px;font-weight:900;letter-spacing:1.5px}.rotation-report-hero h1{margin:0;font-size:25px;font-weight:900}.rotation-report-hero p{margin:1px 0 0;color:rgba(239,249,255,.83);font-size:12px}.rotation-report-hero>span{margin-left:auto;padding:8px 13px;white-space:nowrap;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(2,31,64,.25);font-size:11px;font-weight:800}.rotation-report-hero>span i{margin-right:6px;color:#43e5a3}
.rotation-filter-card{margin:0 0 12px;padding:15px 16px 17px;border:1px solid #d9e7f2;border-radius:20px;background:rgba(255,255,255,.97);box-shadow:0 12px 30px rgba(31,69,108,.08)}.rotation-filter-title{display:flex;align-items:center;justify-content:space-between;margin-bottom:9px;padding-bottom:10px;border-bottom:1px solid #e5edf5}.rotation-filter-title small,.rotation-report-head small{color:#1388c8;font-size:9px;font-weight:900;letter-spacing:1.3px}.rotation-filter-title h2,.rotation-report-head h2{margin:1px 0 0;font-size:18px;font-weight:900}.rotation-filter-title>i{color:#168fd1;font-size:18px}.rotation-report-page label{margin-bottom:5px;color:#29445f;font-size:12px;font-weight:850}.rotation-report-page .form-control{min-height:46px;color:#18304e;font-weight:700;border:1px solid #cfdeea;border-radius:11px;background:#fbfdff;box-shadow:none}.rotation-report-page .form-control:focus{border-color:#1599df;box-shadow:0 0 0 4px rgba(21,153,223,.13)}
.rotation-actions{display:flex;align-items:flex-end;flex-wrap:wrap;gap:8px}.rotation-actions .btn{min-height:44px;padding:0 14px;border:0;border-radius:11px;font-size:11px;font-weight:900;box-shadow:0 7px 15px rgba(24,69,111,.13)}.rotation-actions .btn:first-child{background:linear-gradient(135deg,#0874b6,#09a3c5)!important}.rotation-actions .btn:last-child{background:linear-gradient(135deg,#09955b,#21c178)!important}
.rotation-summary{margin:0 -6px 12px}.rotation-summary>[class*=col]{padding:0 6px}.rotation-summary [class*=col]>div{display:flex;align-items:center;min-height:76px;padding:13px 16px;border:1px solid #dce9f3;border-radius:16px;background:#fff;box-shadow:0 9px 22px rgba(31,69,108,.07)}.rotation-summary i{display:grid;place-items:center;width:42px;height:42px;margin-right:12px;color:#087ab8;border-radius:13px;background:#def2fc}.rotation-summary span{display:flex;flex-direction:column;color:#60778c;font-size:11px;font-weight:800}.rotation-summary strong{color:#173b59;font-size:20px;font-weight:900}.rotation-summary .summary-sales i{color:#008a50;background:#d9f7e8}.rotation-summary .summary-sales strong{color:#008a50}.rotation-summary .summary-purchases i{color:#be2a45;background:#ffe1e6}.rotation-summary .summary-purchases strong{color:#be2a45}
.rotation-report-card{margin:0;overflow:hidden;border:1px solid #d8e6f1;border-radius:20px;background:#fff;box-shadow:0 13px 32px rgba(31,69,108,.09)}.rotation-report-head{display:flex;align-items:center;justify-content:space-between;padding:14px 17px;border-bottom:1px solid #e3edf5}.rotation-report-head>span{padding:7px 11px;color:#315571;border-radius:999px;background:#edf7fd;font-size:11px;font-weight:800}.rotation-table-scroll{width:100%;overflow-x:auto}.rotation-table{min-width:1280px;margin:0}.rotation-table thead{color:#fff;background:linear-gradient(105deg,#0a376d,#087fae)}.rotation-table th{padding:12px;border:0;font-size:11px}.rotation-table th.sales-column{color:#075b36;background:#bfe8cd}.rotation-table th.purchases-column{color:#07536c;background:#bce5ed}.rotation-table td{padding:11px 12px;vertical-align:middle;border-color:#e5edf4;font-size:12px}.rotation-table tbody tr:hover{background:#eef8ff!important}.sales-value{color:#008a50;font-weight:900;background:rgba(190,235,205,.32)}.purchases-value{color:#08738e;font-weight:900;background:rgba(188,229,237,.4)}
.rotation-empty{min-height:330px;display:flex;align-items:center;justify-content:center;flex-direction:column;margin:0;color:#71869a;text-align:center;border:1px dashed #c7d9e8;border-radius:20px;background:rgba(255,255,255,.62)}.rotation-empty>div{display:grid;place-items:center;width:64px;height:64px;margin-bottom:12px;color:#168fca;font-size:25px;border-radius:20px;background:#e1f3fc}.rotation-empty h3{margin:0;color:#244560;font-size:18px;font-weight:900}.rotation-empty p{margin:5px 0 0;font-size:12px}
@media(max-width:991px){.rotation-report-hero>span{display:none}.rotation-actions{max-width:100%;flex:0 0 100%}.rotation-summary>[class*=col]{margin-bottom:8px}.rotation-empty{min-height:250px}}@media(max-width:575px){.rotation-report-page{padding:8px}.rotation-report-hero{padding:10px 13px;border-radius:16px}.rotation-report-hero p{display:none}.rotation-report-hero h1{font-size:20px}.rotation-actions .btn{flex:1 1 auto}.rotation-report-head>span{display:none}}
</style>
