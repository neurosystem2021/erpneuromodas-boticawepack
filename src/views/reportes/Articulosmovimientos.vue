<template>
    <div>
      <br>
        <b-container fluid style="width: calc(100% - 200px)" class="bv-example-row">
          <b-row>
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
            <b-col md="3" class="my-1">
              <b-button variant="success" size="sm" @click="verArticulos">Artículos con más movimientos</b-button>
              <hr>
              <b-button variant="success" size="sm" :href="hrefXls" target="_blank">Exportar</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th style="width: 5% !important">Código</th>
                  <th>Producto</th>
                  <th>Categoria</th>
                  <th class="table-success">Total Ventas</th>
                  <th class="table-success">Cantidad Ventas</th>
                  <th class="table-success">Monto Ventas</th>
                  <th class="table-info">Total Compras</th>
                  <th class="table-info">Cantidad Compras</th>
                  <th class="table-info">Monto Compras</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, i) in productos" :key="i">
                  <td style="width: 5% !important">{{ prod.CodigoFrabricante }}</td>
                  <td>{{ prod.Producto }}</td>
                  <td>{{ prod.ProductoCategoria }}</td>
                  <td class="table-success">{{ prod.TotalVentas ? prod.TotalVentas.toFixed(2):0 }}</td>
                  <td class="table-success">{{ prod.CantidadVentas ? prod.CantidadVentas.toFixed(2):0 }}</td>
                  <td class="table-success">{{ prod.MontoVentas ? prod.MontoVentas.toFixed(2):0 }}</td>
                  <td class="table-info">{{ prod.TotalCompras ? prod.TotalCompras.toFixed(2):0 }}</td>
                  <td class="table-info">{{ prod.CantidadCompras ? prod.CantidadCompras.toFixed(2):0 }}</td>
                  <td class="table-info">{{ prod.MontoCompras ? prod.MontoCompras.toFixed(2):0 }}</td>
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
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD")
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
      this.productos = []
      this.axios.get(this.API + 'productos/masrotacion?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta
          }
      }).then((response) => {
          this.productos = response.data
      })
    }
  }
}
</script>
<style>
    

</style>