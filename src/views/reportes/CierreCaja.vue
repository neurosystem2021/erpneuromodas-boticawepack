<template>
  <div>
    <b-container class="bv-example-row">
      <h2 class="text-center">Cierre de Caja</h2> 
      <h6 class="text-center">#4664336</h6>
      <h6 class="text-center">Fecha de Cierre: 4664336</h6>
      
      <b-row>
        <h5>Ventas Efectivo</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.EfectivoDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Efectivo }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Efectivo</b></td>
              <td class="total"><b>S/. {{ totalVentasEfectivo }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row>
        <h5>Ventas Visa</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.VisaDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Visa }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Visa</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      

      <b-row>
        <h5>Ventas Mastercard</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.MastercardDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Mastercard }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Mastercard</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="credito">
        <h5>Ventas a Credito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Total }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td><b>Total Credito</b></td>
              <td><b>S/. {{ totalVentasCredito | moneda }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row>
        <b-col v-if="totalIngresos">
          <h5>Ingresos</h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr>
                <th>ID</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in ingresos" :key="index">
                <td>{{ item.IdCajaBanco }}</td>
                <td>{{ item.FechaDoc }}</td>
                <td>{{ item.Concepto }}</td>
                <td>S/. {{ item.Importe }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="total"><b>Total Ingresos</b></td>
                <td class="total"><b>S/. {{ totalIngresos }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
        <b-col v-if="totalSalidas">
          <h5>Salidas</h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr>
                <th>ID</th><th>Fecha</th><th>Detalle</th><th>Salida</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in salidas" :key="index">
                <td>{{ item.IdCajaBanco }}</td>
                <td>{{ item.FechaDoc }}</td>
                <td>{{ item.Concepto }}</td>
                <td>S/. {{ item.Importe }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="total-salida"><b>Total Salidas</b></td>
                <td class="total-salida"><b>S/. {{ totalSalidas }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
      </b-row>
      

      <br>

      <b-alert variant="success" class="text-center" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
    </b-container>
  </div>
</template>
<script>
import config from '@/store/config.js'

export default {
  mounted () {
    this.getVentas()
    this.getIngresos()
    this.getSalidas()
  },
  data () {
    return {
      API: config.API,
      ventas: [],
      ingresos: [],
      salidas: []
    }
  },
  computed: {
    granTotal () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard + this.totalIngresos - this.totalSalidas
    },
    totalSalidas () {
      let total = 0
      this.salidas.forEach(salida => {
        if (!salida.Anulado) total += salida.Importe
      });
      return total 
    },
    totalIngresos () {
      let total = 0
      this.ingresos.forEach(ingreso => {
        if (!ingreso.Anulado) total += ingreso.Importe
      });
      return total 
    },
    totalVentasEfectivo () {
      let total = 0
      this.ventasEfectivo.forEach(venta => {
        if (!venta.Anulado) total += venta.Efectivo
      });
      return total
    },
    ventasEfectivo () {
      return this.ventas.filter(venta => {
        return venta.Efectivo > 0 && !venta.EsCredito
      })
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
        return venta.Visa > 0  && !venta.EsCredito
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
        return venta.Mastercard > 0  && !venta.EsCredito
      })
    },
    totalVentasCredito () {
      let total = 0
      this.ventasCredito.forEach(venta => {
        if (!venta.Anulado) total += venta.Total
      });
      return total
    },
    ventasCredito () {
      return this.ventas.filter(venta => {
        return venta.EsCredito
      })
    }
  },
  methods: {
    estadoVenta (venta) {
      return venta.Anulado ? 'ANULADO' : 'APLICADO'
    },
    getVentas () {
      this.axios.get(this.API + 'cierrecaja')
        .then(res => {
          this.ventas = res.data
        }).catch(err => {
          console.log(err)
        })
    },
    getIngresos () {
      this.axios.get(this.API + 'cierrecaja/ingresos')
        .then(res => {
          this.ingresos = res.data
        }).catch(err => {
          console.log(err)
        })
    },
    getSalidas () {
      this.axios.get(this.API + 'cierrecaja/salidas')
        .then(res => {
          this.salidas = res.data
        }).catch(err => {
          console.log(err)
        })
    }
  }
}
</script>
<style>
th {
  background-color: #f1f1f1;
}
.total {
  background-color: #e4ff95;
}
.total-salida {
  background-color: #ffd4d3;  
}
.anulado td{
  font-style: italic;
  text-decoration: line-through;
}
.credito {
  background: #f9f9f9;
  padding: 10px;
}
</style>
