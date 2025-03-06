<template>
  <div>
    <br>
    <b-container class="bv-example-row" fluid>
      <b-breadcrumb :items="items"></b-breadcrumb>
      <div>
          
      </div>
      
      <b-row >
        <b-col cols="4" class="no-print px-5">
          <div class="no-print">
              <b-row align-h="end" class=" no-print">
                    
                  <b-col cols="12" v-if="!this.$route.params.id">
                      <b-form-group  >
                        <label>Vendedor:</label>
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
              </b-row>
              <b-row>
                  <b-col cols="4" v-if="!this.$route.params.id">
                        <b-button variant="outline-success" @click="cerrarCaja" >Cerrar Caja</b-button>
                  </b-col>
                  <b-col cols="8"  v-if="!this.$route.params.id">
                    <b-form-select v-model="turno" :options="turnos" /> 
                  </b-col>
                
                  <b-col cols="6" class="mt-3">
                        <cierres-caja></cierres-caja>
                  </b-col>
                  <b-col cols="6" class="mt-3">
                    <b-button variant="outline-primary" @click="imprimir">IMPRIMIR</b-button>
                </b-col>
              </b-row>
          </div>
          <br class="no-print" /><br class="no-print" />
          <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
          <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
          <center>
            <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
          </center>

          <div class="tblResumenProducto expandable-card mt-2">
            <div class="header" @click="isExpandedResumen = !isExpandedResumen">
              <h4>Resumen Producto</h4>
            </div>
            <div class="content" :class="{ expanded: isExpandedResumen }">
            <table class="table hover small" width="100%">
                <thead>
                  <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(res,index) in resumen" :key="index"> 
                    <td>{{ res.Producto }}</td>
                    <td>{{ res.Cantidad }}</td>
                    <td>{{ res.Total }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr variant="success">
                    <th colspan="2">Gran Total</th>
                    <th colspan="2">{{parseFloat(granTotal).toFixed(2)}}</th>
                  </tr>
                </tfoot>
            </table>
            </div>
        </div>
        </b-col>
        <b-col cols="8" class="px-5 reporte">
          <b-row>
            <h5>Ventas Efectivo</h5><br>
            <table class="table b-table table-sm">
              <thead>
                <tr>
                    <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Estado</th><th style="width:90px">Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>
                  
                      <table class="tabla-detalle">
                      <thead>
                        <tr>
                          <th>Codigo</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Precio</th>
                          <th>Descuento</th>
                          <th v-if="item.TieneIgv">Subtotal</th>
                          <th v-if="item.TieneIgv">IGV</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(prod, index) in item.productos" :key="index">
                          <td>{{ prod.CodigoBarra }}</td>
                          <td class="detalle-producto">{{ prod.Producto }}</td>
                          <td>{{ prod.Cantidad }}</td>
                          <td>{{ prod.Precio }}</td>
                          <td>{{ prod.Descuento }}</td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal - (prod.Subtotal * 0.18) | redondear }}
                          </td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal * 0.18 | redondear}}
                          </td>
                          <td>{{ prod.Subtotal | redondear }}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>

                  </td>
                  <!--<td>{{ item.EfectivoDesc }}</td>-->
                  <td>{{ estadoVenta(item) }}</td>
                  <td>S/. {{ item.Efectivo | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3"></td>
                  <td class="total"><b>Total Efectivo</b></td>
                  <td class="total"><b>S/. {{ totalVentasEfectivo | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </b-row>


          <b-row>
            <h5>Ventas Tarjeta</h5><br>
            <table class="table b-table table-sm">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>
                  
                      <table class="tabla-detalle">
                      <thead>
                        <tr>
                          <th>Codigo</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Precio</th>
                          <th>Descuento</th>
                          <th v-if="item.TieneIgv">Subtotal</th>
                          <th v-if="item.TieneIgv">IGV</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(prod, index) in item.productos" :key="index">
                          <td>{{ prod.CodigoBarra }}</td>
                          <td class="detalle-producto">{{ prod.Producto }}</td>
                          <td>{{ prod.Cantidad }}</td>
                          <td>{{ prod.Precio }}</td>
                          <td>{{ prod.Descuento }}</td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal - (prod.Subtotal * 0.18) | redondear }}
                          </td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal * 0.18 | redondear}}
                          </td>
                          <td>{{ prod.Subtotal | redondear }}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>

                  </td>
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

          

          <b-row>
            <h5>Ventas Transferencia</h5><br>
            <table class="table b-table table-sm">
              <thead>
                <tr>
                    <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>

                      <table class="tabla-detalle">
                      <thead>
                        <tr>
                          <th>Codigo</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Precio</th>
                          <th>Descuento</th>
                          <th v-if="item.TieneIgv">Subtotal</th>
                          <th v-if="item.TieneIgv">IGV</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(prod, index) in item.productos" :key="index">
                          <td>{{ prod.CodigoBarra }}</td>
                          <td class="detalle-producto">{{ prod.Producto }}</td>
                          <td>{{ prod.Cantidad }}</td>
                          <td>{{ prod.Precio }}</td>
                          <td>{{ prod.Descuento }}</td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal - (prod.Subtotal * 0.18) | redondear }}
                          </td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal * 0.18 | redondear}}
                          </td>
                          <td>{{ prod.Subtotal | redondear }}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>
                        
                  </td>
                  <td>{{ item.MastercardDesc }}</td>
                  <td>{{ estadoVenta(item) }}</td>
                  <td>S/. {{ item.Mastercard | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4"></td>
                  <td class="total"><b>Total Transferencia</b></td>
                  <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </b-row>

          <b-row>
            <h5>Ventas YAPE / PLIN</h5><br>
            <table class="table b-table table-sm">
              <thead>
                <tr>
                    <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasYape" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>

                      <table class="tabla-detalle">
                      <thead>
                        <tr>
                          <th>Codigo</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Precio</th>
                          <th>Descuento</th>
                          <th v-if="item.TieneIgv">Subtotal</th>
                          <th v-if="item.TieneIgv">IGV</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(prod, index) in item.productos" :key="index">
                          <td>{{ prod.CodigoBarra }}</td>
                          <td class="detalle-producto">{{ prod.Producto }}</td>
                          <td>{{ prod.Cantidad }}</td>
                          <td>{{ prod.Precio }}</td>
                          <td>{{ prod.Descuento }}</td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal - (prod.Subtotal * 0.18) | redondear }}
                          </td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal * 0.18 | redondear}}
                          </td>
                          <td>{{ prod.Subtotal | redondear }}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>

                  </td>
                  <td>{{ item.YapeDesc }}</td>
                  <td>{{ estadoVenta(item) }}</td>
                  <td>S/. {{ item.Yape | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4"></td>
                  <td class="total"><b>Total YAPE / PLIN </b></td>
                  <td class="total"><b>S/. {{ totalVentasYape | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </b-row>

          <b-row class="credito">
            <h5>Ventas a Credito</h5><br>
            <table class="table b-table table-sm">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>
                    
                      <table class="tabla-detalle">
                      <thead>
                        <tr>
                          <th>Codigo</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Precio</th>
                          <th>Descuento</th>
                          <th v-if="item.TieneIgv">Subtotal</th>
                          <th v-if="item.TieneIgv">IGV</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(prod, index) in item.productos" :key="index">
                          <td>{{ prod.CodigoBarra }}</td>
                          <td class="detalle-producto">{{ prod.Producto }}</td>
                          <td>{{ prod.Cantidad }}</td>
                          <td>{{ prod.Precio }}</td>
                          <td>{{ prod.Descuento }}</td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal - (prod.Subtotal * 0.18) | redondear }}
                          </td>
                          <td v-if="item.TieneIgv">
                            {{ prod.Subtotal * 0.18 | redondear}}
                          </td>
                          <td>{{ prod.Subtotal | redondear }}</td>
                        </tr>
                      </tbody>
                    </table>
                    <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>


                  </td>
                  <td>{{ estadoVenta(item) }}</td>
                  <td>S/. {{ item.Total | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3"></td>
                  <td><b>Total Credito</b></td>
                  <td><b>S/. {{ totalVentasCredito | moneda | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </b-row>

          <b-row>
            <b-col v-if="totalIngresos">
              <h5>Ingresos Caja Principal</h5><br>
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
                    <td>S/. {{ item.Importe | redondear }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="total"><b>Total Ingresos</b></td>
                    <td class="total"><b>S/. {{ totalIngresos | redondear }}</b></td>
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
                    <td>S/. {{ item.Importe | redondear }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="total-salida"><b>Total Salidas</b></td>
                    <td class="total-salida"><b>S/. {{ totalSalidas | redondear }}</b></td>
                  </tr>
                </tfoot>
              </table>
            </b-col>
          </b-row>
          

          <b-row>
            <b-col v-if="totalIngresosTarjeta">
              <h5>Ingresos de Cuentas Bancarias </h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr>
                    <th>ID</th><th>Cuenta</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in ingresosTarjeta" :key="index">
                    <td>{{ item.IdCajaBanco }}</td>
                    <td>{{ item.Cuenta }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ item.Concepto }}</td>
                    <td>S/. {{ item.Importe | redondear }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="total"><b>Total Ingresos tarjeta</b></td>
                    <td class="total"><b>S/. {{ totalIngresosTarjeta | redondear }}</b></td>
                  </tr>
                </tfoot>
              </table>
            </b-col>
          </b-row>

          <b-row>
              <b-col v-if="totalAdelantos">
                <h5>Descontar Adelantos</h5><br>
                <table class="table b-table table-sm adelantos">
                  <thead>
                    <tr>
                      <th>ID</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in adelantos" :key="index">
                      <td>{{ item.IdCajaBanco }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Concepto }} ({{ item.Serie }}-{{ item.Numero }})</td>
                      <td>S/. {{ item.Importe | redondear }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="2"></td>
                      <td class="total"><b>Total Adelantos</b></td>
                      <td class="total"><b>S/. {{ totalAdelantos | redondear }}</b></td>
                    </tr>
                  </tfoot>
                </table>
              </b-col>
          </b-row>
          <b-alert variant="success" class="text-center gran-total" show>
                <b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b>
            </b-alert>
       </b-col>
       
      </b-row>
    </b-container>
  </div>
</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";

export default {
  mounted() {
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id);
      this.items.push({
        text: "Ver Cierre Caja ",
        active: true
      });
      this.loadData();
    } else {
      this.getVendedor();
      this.items.push({
        text: "Cierre Caja",
        active: true
      });
    }
  },
  data() {
    return {
      API: config.API,
      isExpandedResumen: false,
      ventas: [],
      ingresos: [],
      ingresosTarjeta: [],
      salidas: [],
      adelantos: [],
      turno: null,
      turnos: [
        { value: null, text: "Seleccione un Turno" },
        { value: 1, text: "Mañana" },
        { value: 2, text: "Tarde" },
        { value: 3, text: "Noche" }
      ],
      cierre: {},
      vendedor: {},
      items: [
        {
          text: "Ventas",
          href: "#"
        }
      ],
      resumen: []
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
        this.totalVentasYape +
        this.totalIngresos -
        this.totalSalidas -
        this.totalAdelantosActivos
      );
    },
    granTotalSinPagoCliente() {
      return (
        this.totalVentasEfectivo +
        this.totalVentasMastercard +
        this.totalVentasVisa +
        this.totalVentasYape -
        this.totalSalidas
      );
    },
    totalVentas() {
      return (
        this.totalVentasEfectivo +
        this.totalVentasMastercard +
        this.totalVentasVisa +
        this.totalVentasYape
      );
    },
    totalSalidas() {
      let total = 0;
      this.salidas.forEach(salida => {
        if (!salida.Anulado) total += salida.Importe;
      });
      return total;
    },
    totalIngresosTarjeta() {
      let total = 0;
      this.ingresosTarjeta.forEach(ingresoTar => {
        if (!ingresoTar.Anulado) total += ingresoTar.Importe;
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
    totalAdelantos() {
      let total = 0;
      this.adelantos.forEach(ade => {
        if (!ade.Anulado) total += ade.Importe;
      });
      return total;
    },
    totalAdelantosActivos() {
      let total = 0;
      this.adelantos.forEach(ade => {
        if (!ade.Anulado && !ade.AnuladoVenta && !ade.EsCreditoVenta)
          total += ade.Importe;
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
        return venta.Efectivo > 0 && !venta.EsCredito;
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
        return venta.Visa > 0 && !venta.EsCredito;
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
        return venta.Mastercard > 0 && !venta.EsCredito;
      });
    },
    totalVentasYape() {
      let total = 0;
      this.ventasYape.forEach(venta => {
        if (!venta.Anulado) total += venta.Yape;
      });
      return total;
    },
    ventasYape() {
      return this.ventas.filter(venta => {
        return venta.Yape > 0 && !venta.EsCredito;
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
    getDetalles(idDocVenta) {
      let detalles = this.itemsDetalle.filter(
        item => item.IdDocVenta == idDocVenta
      );
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles;
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          // this.$refs.inputVendedor.$refs.input.disabled = true

          if (response.data.IdUsuarioPerfil !== 1) {
            this.$refs.inputVendedor.$refs.input.disabled = true;
          }
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    imprimir() {
      window.print();
    },
    loadData() {
      this.getVentas();
      this.getIngresos();
      this.getSalidas();
    },
    setVendedor(obj) {
      this.vendedor = obj;
      this.loadData();
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
            //user: sessionStorage.getItem("User")
            user: this.vendedor.Usuario,
            ingresos: parseFloat(this.granTotal + this.totalSalidas).toFixed(2),
            salidas: this.totalSalidas,
            total: this.granTotal,
            totalVentas: this.totalVentas
          })
          .then(res => {
            this.loadData();
            this.$toasted.show("¡Caja cerrada!");
            // console.log(res)
          });
      }
    },
    obtenerResumenProducto(detalles) {
      const agrupados = detalles.reduce((acc, item) => {
        if (!acc[item.IdProducto]) {
          acc[item.IdProducto] = {
            Producto: item.Producto,
            Cantidad: item.Cantidad,
            Total: 0
          };
        }
        acc[item.IdProducto].Total += item.Total;
        acc[item.IdProducto].Cantidad += item.Cantidad;
        return acc;
      }, {});
      const resultado = Object.values(agrupados);
      this.resumen = resultado;
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    estadoVenta(venta) {
      return venta.Anulado
        ? "ANULADO"
        : venta.EsCredito
          ? "CREDITO"
          : "APLICADO";
    },
    loadDetalles() {
      this.axios
        .get(this.API + "ventas/detalle", {
          params: {
            idDocVentas: this.ventas.map(item => item.idDocVenta)
          }
        })
        .then(response => {
          this.itemsDetalle = response.data;
          this.obtenerResumenProducto(this.itemsDetalle);
          this.ventas = this.ventas.map(item => {
            item.productos = this.getDetalles(item.idDocVenta);
            return item;
          });
        })
        .catch(error => {
          console.log(error);
        });
    },

    getVentas() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }
      // cierre de caja enviar el vendedor
      this.axios
        .get(this.API + "cierrecaja/ventas", {
          params: params
        })
        .then(res => {
          this.ventas = res.data;
          this.loadDetalles();
          this.getAdelantos();
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
          this.ingresos = res.data.filter(ing => ing.EsTarjeta == 0);
          this.ingresosTarjeta = res.data.filter(ing => ing.EsTarjeta == 1);
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
    getAdelantos() {
      let params = {
        idDocVentas: this.ventas.map(v => v.idDocVenta)
      };
      this.axios
        .get(this.API + "cierrecaja/ingresos/adelantos", {
          params: params
        })
        .then(res => {
          this.adelantos = res.data;
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
  },
  components: {
    CierresCaja
  }
};
</script>
<style scoped>
/*th {
  background-color: #f1f1f1;
}
.total {
  background-color: #e4ff95;
}
.total-salida {
  background-color: #ffd4d3;  
}
.anulado td{
  text-decoration: line-through;
}
.credito {
  background: #f9f9f9;
  padding: 10px;
}
.si-print {
  display: none;
}*/
th,
td {
  font-size: 0.9em;
  vertical-align: middle;
}
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
.tabla-detalle {
}
.tabla-detalle th {
  background-color: #ffffff;
  font-weight: normal;
}
.tabla-detalle th,
.tabla-detalle td {
  font-size: 0.9em;
}
.detalle-producto {
  width: 270px;
}
.adelantos {
  background-color: #ffe8c5;
}
.tblResumenProducto {
  margin: 0px 0;
  padding: 0px 20px;
  border: 0 1px 0 0 solid #000 !important;
}
.reporte {
  /* border: 1px solid #000 !important; */
  margin: 15px 0;
  padding: 20px 50px;
  /* border-radius: 25px; */

  max-height: 80vh;
  overflow-y: auto;
}
.expandable-card {
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
}

.header {
  padding: 10px;
  cursor: pointer;
  user-select: none;
}

.content {
  max-height: 0;
  overflow: hidden;
  transition: max-height 0.5s ease-in-out, padding 0.5s ease-in-out;
  padding: 0 15px;
}

.expanded {
  max-height: 100%; /* Ajusta según el contenido */
  padding: 10px 15px;
}
@media print {
  .no-print {
    display: none;
  }
  .si-print {
    display: block;
    min-height: 800px;
  }
  /* .reporte {
    overflow: visible !important;
  } */
  .reporte {
    display: flex;
    flex-direction: column;
    min-height: 100vh !important;
    overflow: visible !important;
  }

  .gran-total {
    margin-top: auto; /* Empuja el total al final sin sobreponerse */
  }
}
</style>
