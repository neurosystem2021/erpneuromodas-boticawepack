<template>
    <div>
      <br>
        <b-container fluid style="width:calc(100% - 5%)" class="bv-example-row">
          <b-row>
            <b-col class="text-center no-print">
                <h2>REPORTE DE VENTAS POR METODO DE PAGO</h2>
            </b-col>
          </b-row>
          <b-row class="no-print">
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
                  placeholder="Fecha Fin"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label>Metodo de Pago:</label>
                <b-form-select v-model="idMetodoPago" :options="itemsMetodoPago" v-on:change="setMetodoPago($event)" class="mb-3" size="md" required/>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <br>
              <b-button variant="success" size="md" @click="verArticulos">Generar</b-button>
              <!--<b-button variant="success" size="md" :href="hrefXls" target="_blank">Exportar</b-button>-->
              <b-button variant="primary" size="md" @click="imprimir">Imprimir</b-button>
            </b-col>
          </b-row>
          <b-row class="si-print" style="display:none">
            <b-col>
              <h3>REPORTE DE VENTAS POR METODO DE PAGO</h3>
              <h4>Fecha desde: {{fechaDesde}}</h4>
              <h4>Fecha hasta: {{fechaHasta}}</h4>
              <br>
            </b-col>
          </b-row>
          <hr>
          <b-row v-if="idMetodoPago == 0 || idMetodoPago == 2">
            <h5>Ventas Tarjeta</h5><br>
            <table class="table table-hover b-table table-sm tbmetodopago">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th><th width="40%">Cliente</th><th>Placa</th><th>Detalle</th><th>Vendedor</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>{{ item.Cliente }}</td>
                  <td>{{ item.Placa }}</td>
                  <td class="table-success">{{ item.VisaDesc }}</td>
                  <td style="text-transform: uppercase ;">{{ item.UsuarioReg }}</td>
                  <td>S/. {{ item.Visa | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="5"></td>
                  <td class="total"><b>Total Tarjeta</b></td>
                  <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </b-row>

          <b-row v-if="idMetodoPago == 0 || idMetodoPago == 3">
            <h5>Ventas Yape / Plin</h5><br>
            <table class="table table-hover b-table table-sm tbmetodopago">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th><th width="40%">Cliente</th><th>Placa</th><th>Detalle</th><th>Vendedor</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>{{ item.Cliente }}</td>
                  <td>{{ item.Placa }}</td>
                  <td class="table-success">{{ item.MastercardDesc }}</td>
                  <td style="text-transform: uppercase ;">{{ item.UsuarioReg }}</td>
                  <td>S/. {{ item.Mastercard | redondear}}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="5"></td>
                  <td class="total"><b>Total Yape / Plin</b></td>
                  <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </b-row>

          <b-row v-if="idMetodoPago == 0 || idMetodoPago == 4">
            <h5>Ventas Transferencia</h5><br>
            <table class="table table-hover b-table table-sm tbmetodopago">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th><th width="40%">Cliente</th><th>Placa</th><th>Detalle</th><th>Vendedor</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>{{ item.Cliente }}</td>
                  <td>{{ item.Placa }}</td>
                  <td class="table-success">{{ item.TransferenciaDesc }}</td>
                  <td style="text-transform: uppercase ;">{{ item.UsuarioReg }}</td>
                  <td>S/. {{ item.Transferencia | redondear}}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="5"></td>
                  <td class="total"><b>Total Transferencia</b></td>
                  <td class="total"><b>S/. {{ totalVentasTransferencia | redondear }}</b></td>
                </tr>
              </tfoot>
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
      fechaDesde: this.$moment().format("YYYY-MM-DD"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      ventas: [],
      idMetodoPago: 0,
      itemsMetodoPago: [
          { value: 0, text: 'TODOS' },
          { value: 2, text: 'TARJETA' },
          { value: 3, text: 'YAPE/PLIN' },
          { value: 4, text: 'TRANSFERENCIA' },
      ],
    }
  },
  computed: {
    hrefXls () {
      return '/api/index.php/reporte/articulosresumen?fechaDesde='+this.fechaDesde+'&fechaHasta='+this.fechaHasta;
    },
    setMetodoPago(ev){

    },
    totalVentasVisa () {
      let total = 0
      this.ventasVisa.forEach(venta => {
        if (!venta.Anulado) total += venta.Visa
      });
      return total
    },
    ventasVisa () {
      return this.ventas.filter(venta => {
        return venta.Visa > 0  && !venta.EsCredito && venta.EsVale == 0
      })
    },
    totalVentasTransferencia() {
      let total = 0
      this.ventasTransferencia.forEach(venta => {
        if (!venta.Anulado) total += venta.Transferencia
      });
      return total
    },
    ventasTransferencia () {
      return this.ventas.filter(venta => {
        return venta.Transferencia > 0  && !venta.EsCredito && venta.EsVale == 0
      })
    },
    totalVentasMastercard () {
      let total = 0
      this.ventasMastercard.forEach(venta => {
        if (!venta.Anulado) total += venta.Mastercard
      });
      return total
    },
    ventasMastercard () {
      return this.ventas.filter(venta => {
        return venta.Mastercard > 0  && !venta.EsCredito && venta.EsVale == 0
      })
    },
  },
  methods: {
    VendedorVenta (venta) {
      return venta.Anulado ? 'ANULADO' : 'APLICADO'
    },
    verArticulos () {
      if(this.fechaDesde == '' || this.fechaHasta == ''){
        this.$toasted.show('Seleccione una fecha valida de INICIO y FIN')
        return
      }

      this.axios.get(this.API + 'metodopago/ventas', {
          params: {
              fechaDesde: this.fechaDesde,
              fechaHasta: this.fechaHasta,
          }
      }).then((response) => {
          if(response.data){
            this.ventas = response.data
          }
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
.tbmetodopago tbody td{
  vertical-align: middle;
  text-align: center;
}
.tbmetodopago thead th{
  vertical-align: middle;
  text-align: center;
}
</style>