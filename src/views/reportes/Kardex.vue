<template>
    <div class="fondo">
      <br>
        <b-container class="bv-example-row">
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
            <b-col md="3" class="my-1">
              <b-button :disabled="!producto.IdProducto" variant="success" size="sm" @click="verKardex">Kardex</b-button>
              <b-button :disabled="!producto.IdProducto" variant="success" size="sm" @click="verKardexValorizado">Kardex Valorizado</b-button>
              <hr>
              <b-button :disabled="!producto.IdProducto" variant="success" size="sm" :href="hrefXls" target="_blank">Exportar</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
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
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / ${data.ProductoTalla}
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
    
.fondo {
  background:#EFF9FF;
}
</style>