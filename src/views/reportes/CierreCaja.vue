<template>
  
  <div>
    <br>
    <b-container class="bv-example-row">
      <b-row  align-h="end" class=" no-print">
              <label v-if="!this.$route.params.id">Vendedor:</label>
          <b-col cols="3" v-if="!this.$route.params.id">
            <b-form-group size="sm">
              <autocomplete
                  required="required"
                  ref="inputVendedor"
                  :url="API + 'usuarios'"
                  anchor="NombreUsuario"
                  label="writer"
                  :on-select="setVendedor" 
                  :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                  :classes="{input: 'form-control'}">
              </autocomplete>
            </b-form-group>
          </b-col>
          <b-col cols="7">
            <b-button-group size="sm" v-if="!this.$route.params.id">
              <b-form-select v-model="turno" :options="turnos" />
            </b-button-group>
            <b-button-group size="sm">
              <b-button variant="outline-success" @click="cerrarCaja" v-if="!this.$route.params.id">Cerrar Caja</b-button>
            </b-button-group>
            <b-button-group size="sm">
              <cierres-caja></cierres-caja>
            </b-button-group>
            <b-button-group size="sm">
              <b-button variant="outline-primary" @click="imprimir">IMPRIMIR TICKET</b-button>
            </b-button-group>
          </b-col>
      </b-row>
      <br class="no-print" /><br class="no-print" />
      <div class="alinear">
        <h2 class="text-center">Cierre de Caja</h2> 
        <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
        <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
        <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
      </div>
      <br>
      <b-row>
        <b-col cols="5">
        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Gran Total S/.</td>
              <td>{{ granTotal | redondear }}</td>
            </tr>
          </tbody>
        </table>          
        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Ingresos S/.</td>
              <td>{{ totalIngresos | redondear }} </td>
            </tr>
            <tr>
              <td>Salida S/.</td>
              <td>{{ totalSalidas | redondear }} </td>
            </tr>
            <tr>
              <td>Crédito S/.</td>
              <td>{{ totalVentasCredito | redondear }} </td>
            </tr>
          </tbody>
        </table>
        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Efectivo S/.</td>
              <td>{{ totalVentasEfectivo | redondear }} </td>
            </tr>
            <tr>
              <td>Tarjeta S/.</td>
              <td>{{ totalVentasVisa | redondear }} </td>
            </tr>
            <tr>
              <td>Yape/Plin S/.</td>
              <td>{{ totalVentasMastercard | redondear }} </td>
            </tr>
            <tr>
              <td>Transferencia S/.</td>
              <td>{{ totalVentasTransferencia | redondear }} </td>
            </tr>
          </tbody>
        </table>
        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Vales de Crédito S/.</td>
              <td>{{ totalVentasValeCredito | redondear }} </td>
            </tr>
            <tr>
              <td>Canjes S/.</td>
              <td>{{ totalVentasCanje | redondear }} </td>
            </tr>
          </tbody>
        </table>
        </b-col>
      </b-row>

      <br>
      <b-row class="no-print">
        <h2 class="text-center">Resumen de Productos</h2>
        
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>ID</th>
              <th>Producto</th>
              
              <th>Cantidad Vendida</th>
              <th>Monto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in productosVendidos" :key="index">
              <td>{{ item.IdProducto }}</td>
              <td>{{ item.Producto }}</td>
              <td>{{ item.CantidadVendida }}</td>
              <td>S/. {{ item.MontoVendido | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Monto</b></td>
              <td class="total"><b>S/. {{ productosVendidosTotal | redondear}}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>
      <b-row class="print">
        
          <h2>Resumen: </h2> 

      
        
        <b-col cols="12">
        
        <table class="table tbl-historico" size="sm" >
            <thead>
              <tr>
                <th width="5px">Producto</th>
                <th width="1px">Surtidor</th>
                <th width="1px">Cara</th>
                <th width="5px">V. I.</th>
                <th width="5px">V.</th>
                <th width="5px">Dif</th>              
              </tr>
            </thead>
            <tbody>
                <tr v-for="(resumen,index) in resumenHistorial" :key="index">
                  <td width="5px">{{resumen.Producto}}</td>
                  <td width="1px">{{resumen.IdSurtidor}}</td>
                  <td width="1px">{{resumen.Cara}}</td>
                  <td width="5px">{{resumen.ValorInicial}}</td>
                  <td width="5px">{{resumen.VentaTotal}}</td>
                  <td width="5px">{{resumen.Restante}}</td>
                </tr>
            </tbody>
        </table>
      </b-col>
      </b-row>
      <!--<h2 class="text-center">Cierre de Caja</h2> 
      <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
      <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>-->
      <hr class="no-print">
      <b-row class="no-print">
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
              <td>S/. {{ item.Efectivo | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Efectivo</b></td>
              <td class="total"><b>S/. {{ totalVentasEfectivo | redondear}}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row class="no-print">
        <h5>Ventas Tarjeta</h5><br>
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
              <td>S/. {{ item.Visa | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Tarjeta</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      

      <b-row class="no-print">
        <h5>Ventas Yape / Plin</h5><br>
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
              <td>S/. {{ item.Mastercard | redondear}}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Yape / Plin</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="no-print">
        <h5>Ventas Transferencia</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.TransferenciaDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Transferencia | redondear}}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Transferencia</b></td>
              <td class="total"><b>S/. {{ totalVentasTransferencia | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="no-print">
        <h5>Ventas Vale Crédito (No Suma)</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasValeCredito" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.Placa }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ (item.Transferencia + item.Efectivo + item.Visa + item.Mastercard) | redondear}}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Vale</b></td>
              <td class="total"><b>S/. {{ totalVentasValeCredito | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="no-print">
        <h5>Ventas Canje</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasValeCanje" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.Placa }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ (item.Transferencia + item.Efectivo + item.Visa + item.Mastercard) | redondear}}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Canje</b></td>
              <td class="total"><b>S/. {{ totalVentasCanje | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="credito no-print">
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
              <td>S/. {{ item.Total | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td><b>Total Credito</b></td>
              <td><b>S/. {{ totalVentasCredito | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row class="no-print">
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
                <th>ID</th>
                <th>Fecha</th>
                <th>Detalle</th>
                <th>Salida</th>
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

      <b-alert variant="success" class="text-center no-print" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
    </b-container>
  </div>
</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";

export default {
  mounted() {
    // this.loadData()
    // if (this.$route.params.id) {
    //   this.getCierre(this.$route.params.id)
    // }
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id);
      this.loadData();
    } else {
      this.getVendedor();
    }
    if (!this.$route.params.id) {
      this.obtenerHistorioGalones();
    } else {
      this.obtenerHistorioGalonesCierre();
    }
  },
  data() {
    return {
      API: config.API,
      ventas: [],
      ingresos: [],
      salidas: [],
      turno: null,
      turnos: [
        { value: null, text: "Seleccione un Turno" },
        { value: 1, text: "Mañana" },
        { value: 2, text: "Tarde" },
        { value: 3, text: "Noche" }
      ],
      cierre: {},
      vendedor: {},
      productosVendidos: [],
      historial: [],
      ventas: [],
      productosVendidosTotal: 0,
      resumenHistorial: [],
      fieldsHistorial: [
        { key: "Producto" },
        { key: "IdSurtidor", label: "Surtidor" },
        { key: "Cara" },
        { key: "ValorInicial", label: "L. I." },
        { key: "VentaTotal", label: "V." },
        { key: "Restante", label: "Dif." }
      ]
    };
  },
  computed: {
    getFechaCierre() {
      //return this.$route.params.id ?
    },
    granTotal() {
      return (
        this.totalVentasEfectivo +
        this.totalVentasVisa +
        this.totalVentasMastercard +
        this.totalVentasTransferencia +
        this.totalVentasCanje +
        this.totalIngresos -
        this.totalSalidas
      );
    },
    totalSalidas() {
      let total = 0;
      this.salidas.forEach(salida => {
        if (!salida.Anulado) total += salida.Importe;
      });
      return total;
    },
    totalIngresos() {
      let total = 0;
      this.ingresos.forEach(ingreso => {
        if (!ingreso.Anulado) total += ingreso.Importe;
      });
      return total;
    },
    totalVentasEfectivo() {
      let total = 0;
      this.ventasEfectivo.forEach(venta => {
        if (!venta.Anulado && venta.CodSunat != "07") total += venta.Efectivo;
      });
      return total;
    },
    ventasEfectivo() {
      return this.ventas.filter(venta => {
        return venta.Efectivo > 0 && !venta.EsCredito && venta.EsVale == 0;
      });
    },
    totalVentasVisa() {
      let total = 0;
      this.ventasVisa.forEach(venta => {
        if (!venta.Anulado) total += venta.Visa;
      });
      return total;
    },
    ventasVisa() {
      return this.ventas.filter(venta => {
        return venta.Visa > 0 && !venta.EsCredito && venta.EsVale == 0;
      });
    },
    totalVentasTransferencia() {
      let total = 0;
      this.ventasTransferencia.forEach(venta => {
        if (!venta.Anulado) total += venta.Transferencia;
      });
      return total;
    },
    ventasTransferencia() {
      return this.ventas.filter(venta => {
        return venta.Transferencia > 0 && !venta.EsCredito && venta.EsVale == 0;
      });
    },
    totalVentasVale() {
      let total = 0;
      this.ventasVale.forEach(venta => {
        if (!venta.Anulado)
          total +=
            venta.Transferencia +
            venta.Mastercard +
            venta.Efectivo +
            venta.Visa;
      });
      return total;
    },
    ventasVale() {
      return this.ventas.filter(venta => {
        return venta.EsVale == 1;
      });
    },
    totalVentasValeCredito() {
      let total = 0;
      this.ventasValeCredito.forEach(venta => {
        if (!venta.Anulado)
          total +=
            venta.Transferencia +
            venta.Mastercard +
            venta.Efectivo +
            venta.Visa;
      });
      return total;
    },

    ventasValeCredito() {
      return this.ventas.filter(venta => {
        return venta.EsVale == 1 && venta.CodSunat == "13";
      });
    },
    totalVentasCanje() {
      let total = 0;
      this.ventasValeCanje.forEach(venta => {
        if (!venta.Anulado)
          total +=
            venta.Transferencia +
            venta.Mastercard +
            venta.Efectivo +
            venta.Visa;
      });
      return total;
    },
    ventasValeCanje() {
      return this.ventas.filter(venta => {
        return venta.EsVale == 2 && venta.CodSunat == "14";
      });
    },
    totalVentasMastercard() {
      let total = 0;
      this.ventasMastercard.forEach(venta => {
        if (!venta.Anulado) total += venta.Mastercard;
      });
      return total;
    },
    ventasMastercard() {
      return this.ventas.filter(venta => {
        return venta.Mastercard > 0 && !venta.EsCredito && venta.EsVale == 0;
      });
    },
    totalVentasCredito() {
      let total = 0;
      this.ventasCredito.forEach(venta => {
        if (!venta.Anulado) total += venta.Total;
      });
      return total;
    },
    ventasCredito() {
      return this.ventas.filter(venta => {
        return venta.EsCredito;
      });
    }
  },
  methods: {
    async obtenerHistorioGalones() {
      await this.axios(`${this.API}historialgalones/resumen`).then(res => {
        this.historial = res.data.historial;
        this.ventas = res.data.ventas;
      });
      this.recalcularHistorial();
    },
    async obtenerHistorioGalonesCierre() {
      await this.axios(
        `${this.API}historialgalones/resumen/cierre?idCierre=${
          this.$route.params.id
        }`
      ).then(res => {
        this.historial = res.data.historial;
        this.ventas = res.data.ventas;
      });
      this.recalcularHistorialVer();
    },
    recalcularHistorial() {
      let acumulados = {}; // Guardará los valores iniciales corregidos

      // Agrupar el historial por Surtidor - Producto - Cara
      this.historial.forEach(element => {
        let clave = `${element.IdSurtidor}-${element.IdProducto}-${
          element.Cara
        }`;

        // Si la clave no existe en acumulados, registrar el primer valor inicial
        if (!acumulados.hasOwnProperty(clave)) {
          acumulados[clave] = element.Galones;
        }

        // Restar ventas de turnos anteriores (IdCierre !== null)
        let ventasAnteriores = this.ventas
          .filter(
            v =>
              v.IdSurtidor == element.IdSurtidor &&
              v.IdProducto == element.IdProducto &&
              v.Cara == element.Cara &&
              v.IdCierre !== null
          ) // Solo ventas de turnos cerrados
          .reduce((sum, v) => sum + v.Total, 0);

        acumulados[clave] -= ventasAnteriores;
      });

      // Generar el resultado final agrupado
      this.resumenHistorial = Object.keys(acumulados).map(clave => {
        let [IdSurtidor, IdProducto, Cara] = clave.split("-").map(Number);

        // Obtener el producto y datos generales del historial
        let ref = this.historial.find(
          h =>
            h.IdSurtidor == IdSurtidor &&
            h.IdProducto == IdProducto &&
            h.Cara == Cara
        );

        // Obtener total vendido en el turno actual (IdCierre === null)
        let ventasActuales = this.ventas
          .filter(
            v =>
              v.IdSurtidor == IdSurtidor &&
              v.IdProducto == IdProducto &&
              v.Cara == Cara &&
              v.IdCierre === null
          ) // Solo ventas del turno actual
          .reduce((sum, v) => sum + v.Total, 0);

        let saldoFinal = acumulados[clave] - ventasActuales;

        return {
          IdSurtidor,
          IdProducto,
          Cara,
          Producto: ref.Producto,
          ValorInicial: acumulados[clave], // VI después de ventas anteriores
          VentaTotal: ventasActuales, // Lo vendido en el turno actual
          Restante: saldoFinal // VI - VentaTotal del turno actual
        };
      });
    },
    recalcularHistorialVer() {
      let idCierreActual = parseInt(this.$route.params.id); // ID del cierre actual
      let acumulados = {}; // Objeto para agrupar valores iniciales

      console.log("ID Cierre Actual:", idCierreActual);
      console.log("Historial:", this.historial);
      console.log("Ventas:", this.ventas);

      // Paso 1: Inicializar valores desde el historial
      this.historial.forEach(element => {
        let clave = `${element.IdSurtidor}-${element.IdProducto}-${
          element.Cara
        }`;

        if (!acumulados[clave]) {
          acumulados[clave] = {
            IdSurtidor: element.IdSurtidor,
            IdProducto: element.IdProducto,
            Cara: element.Cara,
            Producto: element.Producto,
            ValorInicial: element.Galones, // Se ajustará después
            VentaTotal: 0, // Se calculará después
            Restante: 0 // Se calculará después
          };
        }
      });

      console.log("Inicialización:", acumulados);

      // Paso 2: Restar ventas de cierres anteriores (IdCierre < idCierreActual)
      this.ventas.forEach(v => {
        let clave = `${v.IdSurtidor}-${v.IdProducto}-${v.Cara}`;
        if (acumulados[clave]) {
          if (v.IdCierre < idCierreActual) {
            acumulados[clave].ValorInicial -= v.Total;
          }
        }
      });

      console.log(
        "Después de restar ventas de cierres anteriores:",
        acumulados
      );

      // Paso 3: Calcular el total vendido en el cierre actual (IdCierre = idCierreActual)
      this.ventas.forEach(v => {
        let clave = `${v.IdSurtidor}-${v.IdProducto}-${v.Cara}`;
        if (acumulados[clave]) {
          if (v.IdCierre === idCierreActual) {
            acumulados[clave].VentaTotal += v.Total;
          }
        }
      });

      console.log("Después de calcular VentaTotal:", acumulados);

      // Paso 4: Calcular el saldo final (Restante)
      Object.keys(acumulados).forEach(clave => {
        acumulados[clave].Restante =
          acumulados[clave].ValorInicial - acumulados[clave].VentaTotal;
      });

      console.log("Después de calcular Restante:", acumulados);

      // Paso 5: Pasar los valores finales a this.resumenHistorial
      this.resumenHistorial = Object.values(acumulados);

      console.log("Resumen Final:", this.resumenHistorial);
    },

    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          if (response.data.IdUsuarioPerfil !== 1) {
            this.$refs.inputVendedor.$refs.input.disabled = true;
          }
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      this.vendedor = obj;
      this.loadData();
    },
    imprimir() {
      window.print();
    },
    loadData() {
      this.productosVendidos = [];
      this.productosVendidosTotal = 0;
      this.getVentas();
      this.getIngresos();
      this.getSalidas();
    },
    cerrarCaja() {
      if (!this.turno) {
        this.$toasted.show("Seleccione un Turno");
        return;
      }
      let r = confirm("¿Está seguro de cerrar la caja?");
      if (r) {
        this.axios
          .post(this.API + "cierrecaja", {
            idTurno: this.turno,
            user: this.vendedor.Usuario
          })
          .then(res => {
            this.loadData();
            this.$toasted.show("¡Caja cerrada!");
            console.log(res);
          });
      }
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    estadoVenta(venta) {
      return venta.Anulado ? "ANULADO" : "APLICADO";
    },
    getProductos(ventas) {
      this.productosVendidos = [];
      this.productosVendidosTotal = 0;
      this.axios
        .post(this.API + "cierrecaja/productos", {
          idDocVentas: ventas.map(prod => prod.idDocVenta)
        })
        .then(res => {
          this.productosVendidos = res.data;
          let total = 0;
          this.productosVendidos.map(pv => {
            total += pv.MontoVendido;
          });
          this.productosVendidosTotal = total;
        })
        .catch(err => {
          console.log(err);
        });
    },
    getVentas() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }

      this.axios
        .get(this.API + "cierrecaja/ventas", {
          params: params
        })
        .then(res => {
          this.ventas = res.data;
          if (this.ventas.length > 0) {
            this.getProductos(this.ventas);
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    getIngresos() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }

      this.axios
        .get(this.API + "cierrecaja/ingresos", {
          params: params
        })
        .then(res => {
          this.ingresos = res.data;
        })
        .catch(err => {
          console.log(err);
        });
    },
    getSalidas() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }

      this.axios
        .get(this.API + "cierrecaja/salidas", {
          params: params
        })
        .then(res => {
          this.salidas = res.data;
        })
        .catch(err => {
          console.log(err);
        });
    },
    getCierre(id) {
      this.axios
        .get(this.API + "cierrecaja", {
          params: {
            idCierre: id
          }
        })
        .then(res => {
          this.cierre = res.data[0];
        });
    }
    // getHistorial(id) {
    //   this.axios
    //     .get(this.API + "historialoferta/cierre", {
    //       params: {
    //         idCierre: id
    //       }
    //     })
    //     .then(res => {
    //       this.cierre = res.data[0];
    //     });
    // }
  },
  components: {
    CierresCaja
  }
};
</script>
<style scoped>
th {
  background-color: #f1f1f1;
}
.total {
  background-color: #e4ff95;
}
.total-salida {
  background-color: #ffd4d3;
}
.anulado td {
  /*font-style: italic;*/
  text-decoration: line-through;
}
.credito {
  background: #f9f9f9;
  padding: 10px;
}
@media print {
  .no-print {
    display: none;
  }
  .tbl-historico {
    max-width: 30px;
    font-size: 0.55rem;
  }
}
.alinear * {
  text-align: left !important;
}
.tableResumen {
  width: 100%;
  font-weight: bold;
}
</style>
