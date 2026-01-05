<template>
    <div>
      <br>
        <b-container fluid style="width:calc(100% - 5%)" class="bv-example-row">
          <b-row>
            <b-col class="text-center">
                <h2>RESUMEN POR ARTICULO</h2>
            </b-col>
          </b-row>
          <b-row class="no-print">
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
              <b-button variant="success" size="md" @click="verArticulos">Generar</b-button>
              <b-button variant="success" size="md" :href="hrefXls" target="_blank">Exportar</b-button>
              <b-button variant="primary" size="md" @click="imprimir">Imprimir</b-button>
            </b-col>
          </b-row>

          <b-row class="si-print" style="display:none">
            <b-col>
              <h4>Almacén: {{almacen.Almacen}}</h4>
              <h4>Fecha desde: {{fechaDesde}}</h4>
              <h4>Fecha hasta: {{fechaHasta}}</h4>
              <br>
            </b-col>
          </b-row>
          <b-row>
            <table class="table b-table table-hover table-sm">
              <thead>
                <tr>
                  <th class="text-center">COD.</th>
                  <th class="text-center">NOMBRE O DESCRIPCION</th>
                  <th>MEDIDA</th>
                  <th>CANTIDAD</th>
                  <th>TOTAL</th>
                  <th>PROMEDIO</th>
                  <th>%</th>
                </tr>
              </thead>
              <tbody v-for="(cat, i) in categorias" :key="i">
                <tr>
                  <td class="text-right"><b>{{cat.IdProductoCategoria}}</b></td>
                  <td colspan="6"><b>{{cat.ProductoCategoria}}</b></td>
                </tr>
                <tr v-for="(prod, k) in cat.Productos" :key="k">
                  <td class="text-center">{{ prod.CodigoFrabricante }}</td>
                  <td>{{ prod.Producto }}</td>
                  <td>{{ prod.ProductoMedicion }}</td>
                  <td>{{ prod.CantidadVentas }}</td>
                  <td>S/ {{ prod.MontoVentas | moneda}}</td>
                  <td>S/ {{ (prod.MontoVentas/prod.CantidadVentas) | moneda}}</td>
                  <td>{{ prod.MontoVentas*100/TotalGeneralMonto | redondear }}</td>
                </tr>
                <tr>
                  <td class="text-right" colspan="2"><b>*** TOTAL CATEGORIA</b></td>
                  <td></td>
                  <td><b>{{cat.TotalCantidad}}</b></td>
                  <td>S/ <b>{{cat.TotalMonto | moneda}}</b></td>
                  <td>S/ <b>{{cat.TotalPromedio | moneda}}</b></td>
                  <td><b>{{cat.TotalMonto*100/TotalGeneralMonto | redondear}}</b></td>
                </tr>
              </tbody>
              <tbody>
                <tr>
                  <td class="text-right" colspan="2"><b>*** TOTAL GENERAL</b></td>
                  <td></td>
                  <td><b>{{TotalGeneralCantidad}}</b></td>
                  <td>S/ <b>{{TotalGeneralMonto | moneda}}</b></td>
                  <td>S/ <b>{{TotalGeneralPromedio | moneda}}</b></td>
                  <td><b>{{TotalGeneralMonto*100/TotalGeneralMonto | redondear}}</b></td>
                </tr>
              </tbody>
            </table>
          </b-row>
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
      almacen: {},
      productos: [],
      fechaDesde: this.$moment().format("YYYY-MM-DD"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      categorias:[],
      TotalGeneralCantidad:0,
      TotalGeneralMonto:0,
      TotalGeneralPromedio:0,
    }
  },
  computed: {
    hrefXls () {
      return '/api/index.php/reporte/articulosmovimientos?idAlmacen=' + this.almacen.IdAlmacen +'&fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta;
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
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    verArticulos () {
      this.axios.get(this.API + 'productos/masrotacion/categoria?noLimit=1&sortBy=IdProducto', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta
          }
      }).then((response) => {
          if(response.data.data){
            this.TotalGeneralCantidad = 0
            this.TotalGeneralMonto = 0
            this.TotalGeneralPromedio = 0
            let categorias = response.data.categorias
            categorias.map(cat=>{
              cat.Productos = []
              cat.TotalCantidad = 0
              cat.TotalMonto = 0
              cat.TotalPromedio = 0
              response.data.data.map(pro=>{
                if(cat.IdProductoCategoria == pro.IdProductoCategoria){
                  cat.Productos.push(pro)
                  cat.TotalCantidad += parseFloat(pro.CantidadVentas)
                  cat.TotalMonto += parseFloat(pro.MontoVentas)
                  cat.TotalPromedio += parseFloat(pro.MontoVentas/pro.CantidadVentas)
                }
              })
              this.TotalGeneralCantidad +=  cat.TotalCantidad
              this.TotalGeneralMonto += cat.TotalMonto
              this.TotalGeneralPromedio += cat.TotalPromedio
            })
            this.categorias = categorias.filter(ct=>ct.Productos.length>0)
          }
          //this.productos = response.data
          console.log(this.categorias);
      })
    },
    imprimir () {
      window.print();
    },
  }
}
</script>
<style>
@media print {
  .no-print {
    display: none !important;
  }
  .si-print {
    display: block !important;
  }
}
</style>