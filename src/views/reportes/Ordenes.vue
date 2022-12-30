<template>
    <div>
      <br>
        <b-container fluid style="width: calc(100% - 200px)" class="bv-example-row">
          <b-row class="text-center">
            <b-col>
              <h3>Registro de Ordenes de Compra</h3>
            </b-col>
          </b-row>
          <hr>
          <b-row>
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
            <b-col md="4" class="my-1">
              <b-button variant="success" size="md" @click="verArticulos">Generar</b-button>
              <hr>
              <b-button variant="success" size="md" :href="hrefXls" target="_blank"><i class="fas fa-file-excel"></i>  Exportar Ordenes</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th style="width: 5% !important">Id</th>
                  <th>Correlativo</th>
                  <th>Fecha</th>
                  <th>Ruc</th>
                  <th>Proveedor</th>
                  <th>Total</th>
                  <th>Ver</th>
                  <th>Reporte</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, i) in productos" :key="i">
                  <td style="width: 5% !important">{{ prod.IdOrdenCompra }}</td>
                  <td>{{ prod.Correlativo }}</td>
                  <td>{{ prod.FechaOrdenCompra }}</td>
                  <td>{{ prod.Ruc }}</td>
                  <td>{{ prod.Proveedor }}</td>
                  <td>S/ {{ prod.Total | moneda }}</td>
                  <td><b-button variant="primary" @click="mostrarDetalles(prod)"><i class="fas fa-eye"></i> Ver</b-button></td>
                  <td><a class="btn btn-success" target="_blank" :href="'/api/index.php/reporte/ordencompra?IdOrdenCompra=' + prod.IdOrdenCompra"><i class="fas fa-file-excel"></i> Excel</a></td>
                </tr>
              </tbody>
            </table>
          </b-row>
          <b-modal size="lg" id="modalDetalle" ref="modalDetalle" :title="'Orden de Compra: '+infoOrden.Correlativo" ok-only ok-title="Cerrar">
            <b-row>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">RUC</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Ruc }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Proveedor</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Proveedor }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Dirección</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Direccion }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Forma de Pago</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.EsCredito==1?'CRÉDITO':'CONTADO'}}</td>
                    </tr>
                    <tr v-if="infoOrden.EsCredito==1">
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha de Crédito</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaCredito }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Metodo de Pago</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.MovimientoMetodoPago}}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>

              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaOrdenCompra }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha a Recoger</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaRecojo }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Registrado Por</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.UsuarioRegOrden }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            <br>
            <b-row>
              <b-col md="12">
                <table class="table b-table table-sm table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prod, i) in detalle" :key="i">
                            <td class="text-center">{{ prod.CodigoBarra }}</td>
                            <td class="text-center">{{ prod.Producto }}</td>
                            <td class="text-center">{{ prod.Cantidad }}</td>
                            <td class="text-center">{{ prod.Precio | moneda }}</td>
                            <td class="text-center">{{ (prod.Precio *  prod.Cantidad) | moneda }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                      <td colspan="3"></td>
                      <td class="text-center"><b>TOTAL</b></td>
                      <td class="table-success text-center" style="vertical-align: middle !important;">S/ {{infoOrden.Total | moneda}} </td>
                    </tr>
                  </tfoot>
                </table>
              </b-col>
            </b-row>
          </b-modal>
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
      productos: [],
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      detalle: {},
      infoOrden:{
        Anio: null,
        Correlativo: null,
        Direccion: null,
        EsCredito: null,
        FechaCredito: null,
        FechaOrdenCompra: null,
        FechaRecojo: null,
        IdOrdenCompra: null,
        IdProveedor: null,
        NumCuenta: null,
        Numero: null,
        Proveedor: null,
        Ruc: null,
        Telefono: null,
        UsuarioReg: null,
      }
    }
  },
  computed: {
    hrefXls () {
      return '/api/index.php/reporte/ordenescompra?fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta;
    }
  },
  methods: {
    focusAutocomplete (ref) {
      this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    mostrarDetalles(data){
      this.axios.get(this.API + 'ordencompra/detalle', {
        params: {
          IdOrdenCompra: data.IdOrdenCompra
        }
      }).then((response) => {
            this.detalle = response.data
            this.infoOrden = data
            this.$refs['modalDetalle'].show()
      })
      .catch((error) => {
        console.log(error)
      })
    },
    verArticulos () {
      this.productos = []
      this.axios.get(this.API + 'ordencompra', {
          params: {
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