<template>
    <div>
      <br>
        <b-container fluid style="width:calc(100% - 5%)" class="bv-example-row">
          <b-row>
            <b-col class="text-center">
                <h2>REPORTE DE CANTIDADES DE VENDIDAS</h2>
            </b-col>
          </b-row>
          <b-row class="no-print">
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
              <br>
              <b-button variant="success" size="md" @click="verArticulos" :disabled="!producto.IdProducto">Generar</b-button>
              <b-button variant="success" size="md" :href="hrefXls" :disabled="!producto.IdProducto" target="_blank">Exportar</b-button>
              <b-button variant="primary" size="md" @click="imprimir">Imprimir</b-button>
            </b-col>
          </b-row>

          <b-row class="si-print" style="display:none">
            <b-col>
              <h5>Producto: {{nombre}}</h5>
              <h5>Fecha desde: {{fechaDesde}}</h5>
              <h5>Fecha hasta: {{fechaHasta}}</h5>
              <br>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-hover table-sm">
              <thead>
                <tr>
                  <th class="text-center">ID</th>
                  <th class="text-center">FECHA</th>
                  <th class="text-center">COMPROBANTE</th>
                  <th class="text-center">PRODUCTO</th>
                  <th class="text-center">MEDIDA</th>
                  <th class="text-center">PRECIO</th>
                  <th class="text-center">CANTIDAD</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, k) in items" :key="k">
                  <td class="text-center">{{ item.idDocVenta }}</td>
                  <td class="text-center">{{ item.FechaDoc }}</td>
                  <td class="text-center">{{ item.Comprobante }}</td>
                  <td class="text-center">{{ item.Producto }}</td>
                  <td class="text-center">{{ item.ProductoMedicion }}</td>
                  <td class="text-center">{{ item.Precio }}</td>
                  <td class="text-center">{{ item.CodSunat=='07'?'-':'' }} {{ item.Cantidad }}</td>
                </tr>
              </tbody>
              <tbody>
                <tr>
                  <td class="text-right" colspan="6"><b>*** TOTAL CANTIDAD</b></td>
                  <td class="text-center"><b>{{ TotalGeneralCantidad }}</b></td>
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
  },
  data () {
    return {
      API: config.API,
      almacen: {},
      producto: {},
      fechaDesde: this.$moment().format("YYYY-MM-DD"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      TotalGeneralCantidad:0,
      items: [],
      nombre: '',
    }
  },
  computed: {
    hrefXls () {
      return '/api/index.php/reporte/ventas/cantidad/export?fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta+'&idProducto='+this.producto.IdProducto+'&producto='+this.nombre;
    }
  },
  methods: {
    focusAutocomplete (ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setProducto (obj) {
      this.producto = obj
      this.nombre = obj.Producto
    },
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    renderChild(data) {
      return `
        <b>${data.CodigoBarra} - ${data.Producto}</b>
      `
    },
    verArticulos () {
        this.TotalGeneralCantidad = 0 
        this.axios.get(this.API + 'reporte/ventas/cantidad', {
            params: {
                fechaDesde: this.fechaDesde,
                fechaHasta: this.fechaHasta,
                idProducto: this.producto.IdProducto
            }
        }).then((response) => {
                this.items =  response.data

                this.items.map(d=>{
                    if(d.CodSunat=='07'){
                        this.TotalGeneralCantidad -= d.Cantidad
                    }else{
                        this.TotalGeneralCantidad += d.Cantidad
                    }
                    
                })
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