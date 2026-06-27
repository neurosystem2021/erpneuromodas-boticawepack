<template>
  <b-container fluid class="px-4 py-4 cierre-wrapper">

    <!-- ============ HEADER ============ -->


    <b-row class="no-print">
      <!-- ============ COLUMNA PRINCIPAL: TABS ============ -->
      <b-col cols="12" lg="9">
        <b-card no-body class="shadow-sm">
          <b-tabs card class="cierre-tabs">

            <!-- ===================== TAB VENTAS ===================== -->
            <b-tab title="Ventas" active>
              <div class="details px-2 px-md-3">

                <!-- Resumen de productos -->
                <section-block icon="fa-box" title="Resumen de Productos" subtitle="Referencial">
                  <data-table :headers="['Producto', 'Isla', 'Cara', 'Cantidad', 'Monto']"
                    :align="['left', 'left', 'left', 'right', 'right']">
                    <tr v-for="(producto, index) in productosVendidos" :key="index">
                      <td>{{ producto.Producto }}</td>
                      <td>{{ producto.Isla }}</td>
                      <td>
                        <span v-if="producto.Isla" class="badge-cara"
                          :class="producto.Cara == 1 ? 'badge-cara--a' : 'badge-cara--b'">
                          {{ producto.Cara == 1 ? 'Cara A' : 'Cara B' }}
                        </span>
                      </td>
                      <td class="text-right">{{ producto.CantidadVendida }}</td>
                      <td class="text-right num">{{ producto.MontoVendido | redondear }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="3"></td>
                      <td class="text-right"><b>{{ sumaCampo(productosVendidos, 'CantidadVendida') | redondear }}</b>
                      </td>
                      <td class="text-right num"><b>S/. {{ sumaCampo(productosVendidos, 'MontoVendido') | redondear
                      }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas Efectivo -->
                <section-block icon="fa-money-bill-wave" title="Ventas Efectivo" accent="efectivo">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Placa', 'Detalle', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasEfectivo" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ item.Placa }}</td>
                      <td>{{ item.EfectivoDesc }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ item.Efectivo | redondear }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="6"><b>Total Efectivo</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasEfectivo | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas Tarjeta -->
                <section-block icon="fa-credit-card" title="Ventas Tarjeta" accent="tarjeta">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Placa', 'Detalle', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasVisa" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ item.Placa }}</td>
                      <td>{{ item.VisaDesc }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ item.Visa | redondear }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="6"><b>Total Tarjeta</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas Yape / Plin -->
                <section-block icon="fa-mobile-alt" title="Ventas Yape / Plin" accent="yape">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Placa', 'Detalle', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasMastercard" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ item.Placa }}</td>
                      <td>{{ item.MastercardDesc }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ item.Mastercard | redondear }}</td>
                    </tr>
                    <tr v-for="(item, index) in salidasYape" :key="'salida-' + index"
                      class="fila-salida-yape" :class="{ anulado: item.Anulado }">
                      <td>—</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td colspan="2">{{ item.Concepto }}</td>
                      <td><span class="badge-tipo badge-tipo--salida">Salida Yape</span></td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num text-danger">- S/. {{ item.Importe | redondear }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="6"><b>Total Yape / Plin</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasMastercardNeto | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas Transferencia -->
                <section-block icon="fa-exchange-alt" title="Ventas Transferencia" accent="transferencia">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Placa', 'Detalle', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasTransferencia" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ item.Placa }}</td>
                      <td>{{ item.TransferenciaDesc }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ item.Transferencia | redondear }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="6"><b>Total Transferencia</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasTransferencia | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas Vale Crédito -->
                <section-block icon="fa-ticket-alt" title="Ventas Vale Crédito" subtitle="No Suma" accent="vale">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Placa', 'Ref.', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasValeCredito" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ item.Placa }}</td>
                      <td>{{ item.RefVale }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ (item.Transferencia + item.Efectivo + item.Visa + item.Mastercard) |
                        redondear }}
                      </td>
                    </tr>
                    <template slot="footer">
                      <td colspan="6"><b>Total Vale</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasValeCredito | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas Canje -->
                <section-block icon="fa-handshake" title="Ventas Canje" accent="canje">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Placa', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasValeCanje" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ item.Placa }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ (item.Transferencia + item.Efectivo + item.Visa + item.Mastercard) |
                        redondear }}
                      </td>
                    </tr>
                    <template slot="footer">
                      <td colspan="5"><b>Total Canje</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasCanje | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ventas a Crédito -->
                <section-block icon="fa-hand-holding-usd" title="Ventas a Crédito" accent="credito">
                  <data-table :headers="['Recibo', 'Fecha', 'Cliente', 'Estado', 'Pagado']"
                    :align="['left', 'left', 'left', 'center', 'right']">
                    <tr v-for="(item, index) in ventasCredito" :key="index"
                      :class="{ anulado: item.Anulado, marcado: item.check }" @click="toggleMarcado(item)">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td class="text-center"><estado-badge :anulado="item.Anulado" /></td>
                      <td class="text-right num">{{ item.Total | redondear }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="4"><b>Total Crédito</b></td>
                      <td class="text-right num"><b>S/. {{ totalVentasCredito | redondear }}</b></td>
                    </template>
                  </data-table>
                </section-block>

                <!-- Ingresos / Salidas -->
                <b-row class="mt-2">
                  <b-col md="6" v-if="totalIngresos">
                    <section-block icon="fa-arrow-circle-up" title="Ingresos" accent="ingreso" compact>
                      <data-table :headers="['ID', 'Fecha', 'Detalle', 'Ingreso']"
                        :align="['left', 'left', 'left', 'right']" small>
                        <tr v-for="(item, index) in ingresos" :key="index">
                          <td>{{ item.IdCajaBanco }}</td>
                          <td>{{ item.FechaDoc }}</td>
                          <td>{{ item.Concepto }}</td>
                          <td class="text-right num">{{ item.Importe | redondear }}</td>
                        </tr>
                        <template slot="footer">
                          <td colspan="3"><b>Total Ingresos</b></td>
                          <td class="text-right num"><b>S/. {{ totalIngresos | redondear }}</b></td>
                        </template>
                      </data-table>
                    </section-block>
                  </b-col>
                  <b-col md="6" v-if="totalSalidas">
                    <section-block icon="fa-arrow-circle-down" title="Salidas" accent="salida" compact>
                      <data-table :headers="['ID', 'Fecha', 'Detalle', 'Salida']"
                        :align="['left', 'left', 'left', 'right']" small>
                        <tr v-for="(item, index) in salidas" :key="index">
                          <td>{{ item.IdCajaBanco }}</td>
                          <td>{{ item.FechaDoc }}</td>
                          <td>{{ item.Concepto }}</td>
                          <td class="text-right num">{{ item.Importe | redondear }}</td>
                        </tr>
                        <template slot="footer">
                          <td colspan="3"><b>Total Salidas</b></td>
                          <td class="text-right num"><b>S/. {{ totalSalidas | redondear }}</b></td>
                        </template>
                      </data-table>
                    </section-block>
                  </b-col>
                </b-row>
              </div>

              <b-alert variant="success" class="text-center no-print gran-total-alert" show>
                <i class="fas fa-coins"></i> GRAN TOTAL: <b>S/. {{ granTotal | moneda }}</b>
              </b-alert>
            </b-tab>

            <!-- ===================== TAB YAPE/PLIN ===================== -->
            <!-- <b-tab title="Yape/Plin">
              <div class="scrollVertical">
                <h5>Detalle de Ventas Pagadas con Yape/Plin y Tarjeta</h5>
                <table class="table b-table table-sm">
                  <thead>
                    <tr class="thDetalle">
                      <th>Tipo</th>
                      <th>Recibo</th>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Vendedor</th>
                      <th>Cliente</th>
                      <th>Estado</th>
                      <th>Monto Pagado</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in ventasMastercard" :key="'yape-' + index"
                      :class="{ anulado: item.Anulado }">
                      <td>
                        <span class="badge-tipo badge-tipo--yape">Yape/Plin</span>
                      </td>
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ formatoHora(item.FechaDoc) }}</td>
                      <td>{{ item.UsuarioVenta }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ estadoVenta(item) }}</td>
                      <td>S/. {{ item.Mastercard | redondear }}</td>
                    </tr>
                    <tr v-for="(item, index) in ventasVisa" :key="'visa-' + index" :class="{ anulado: item.Anulado }">
                      <td>
                        <span class="badge-tipo badge-tipo--tarjeta">Tarjeta</span>
                      </td>
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ formatoHora(item.FechaDoc) }}</td>
                      <td>{{ item.UsuarioVenta }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ estadoVenta(item) }}</td>
                      <td>S/. {{ item.Visa | redondear }}</td>
                    </tr>
                  </tbody>
                 <tr v-for="(item, index) in salidasYape" :key="'salida-yape-' + index" class="fila-salida-yape">
                    <td><span class="badge-tipo badge-tipo--salida">Salida</span></td>
                    <td>—</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ formatoHora(item.FechaDoc) }}</td>
                    <td>{{ item.UsuarioReg }}</td>
                    <td>{{ item.Concepto }}</td>
                    <td>SALIDA</td>
                    <td class="text-danger font-weight-bold">- S/. {{ item.Importe | redondear }}</td>
                  </tr>
                  <tfoot>
                    <tr>
                      <td colspan="6"></td>
                      <td class="total"><b>Total Yape/Plin</b></td>
                      <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
                    </tr>
                    <tr class="fila-salida-resumen">
                      <td colspan="6"></td>
                      <td class="total text-danger"><b>Salidas Yape</b></td>
                      <td class="total text-danger"><b>- S/. {{ totalSalidasYape | redondear }}</b></td>
                    </tr>
                    <tr class="fila-gran-total">
                      <td colspan="6"></td>
                      <td class="total"><b>TOTAL NETO YAPE</b></td>
                      <td class="total"><b>S/. {{ totalVentasMastercardNeto | redondear }}</b></td>
                    </tr>
                    <tr>
                      <td colspan="6"></td>
                      <td class="total"><b>Total Tarjeta</b></td>
                      <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
                    </tr>
                    <tr class="fila-gran-total">
                      <td colspan="6"></td>
                      <td class="total"><b>TOTAL COMBINADO</b></td>
                      <td class="total"><b>S/. {{ totalVentasVisaTransferencia | redondear }}</b></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </b-tab> -->

            <!-- <b-tab title="Transferencias">
              <div class="scrollVertical">
                <h5>Detalle de Ventas Pagadas con Transferencia</h5>
                <table class="table b-table table-sm">
                  <thead>
                    <tr class="thDetalle">
                      <th>Recibo</th>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Vendedor</th>
                      <th>Cliente</th>
                      <th>Estado</th>
                      <th>Monto Pagado (Transferencia)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{ anulado: item.Anulado }">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ formatoHora(item.FechaDoc) }}</td>
                      <td>{{ item.UsuarioVenta }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ estadoVenta(item) }}</td>
                      <td>S/. {{ item.Transferencia | redondear }}</td>
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
              </div>
            </b-tab> -->
            <!-- ===================== TAB GALONAJE ===================== -->
           <b-tab title="Galonaje">
              <div class="px-2 px-md-3">

                <!-- NUEVO: Detalle de ventas por producto -->
                <section-block icon="fa-list-alt" title="Detalle de Ventas por Producto"
                  subtitle="Ventas del turno actual">
                  <data-table :headers="['Producto', 'Isla', 'Cara', 'Cantidad', 'Precio', 'Monto']"
                    :align="['left', 'left', 'left', 'right', 'right', 'right']">
                    <tr v-for="(producto, index) in productosVendidos" :key="index">
                      <td>{{ producto.Producto }}</td>
                      <td>{{ producto.Isla }}</td>
                      <td>
                        <span v-if="producto.Isla" class="badge-cara"
                          :class="producto.Cara == 1 ? 'badge-cara--a' : 'badge-cara--b'">
                          {{ producto.Cara == 1 ? 'Cara A' : 'Cara B' }}
                        </span>
                      </td>
                      <td class="text-right num">{{ parseFloat(producto.CantidadVendida).toFixed(4) }}</td>
                      <td class="text-right num">
                        {{ producto.CantidadVendida > 0
                          ? (producto.MontoVendido / producto.CantidadVendida).toFixed(4)
                        : '0.0000' }}
                      </td>
                      <td class="text-right num">{{ parseFloat(producto.MontoVendido).toFixed(2) }}</td>
                    </tr>
                    <template slot="footer">
                      <td colspan="3"></td>
                      <td class="text-right">
                        <b>{{ sumaCampo(productosVendidos, 'CantidadVendida') | redondear }}</b>
                      </td>
                      <td></td>
                      <td class="text-right num">
                        <b>S/. {{ sumaCampo(productosVendidos, 'MontoVendido') | redondear }}</b>
                      </td>
                    </template>
                  </data-table>
                </section-block>

              </div>

              <b-alert variant="success" class="text-center no-print gran-total-alert" show>
                <i class="fas fa-coins"></i> GRAN TOTAL: <b>S/. {{ granTotal | moneda }}</b>
              </b-alert>
            </b-tab>
          </b-tabs>
        </b-card>
      </b-col>

      <!-- ============ COLUMNA LATERAL: RESUMEN ============ -->
      <b-col cols="12" lg="3" class="mt-3 mt-lg-0"> <b-row class="no-print mb-3">
          <b-row align-h="end" class="no-print">


            <b-col cols="12" v-if="!this.$route.params.id">
              <label v-if="!this.$route.params.id">Vendedor:</label>
              <b-form-group size="sm">
                <autocomplete required="required" ref="inputVendedor" :url="API + 'usuarios'" anchor="NombreUsuario"
                  label="writer" :on-select="setVendedor" :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                  :classes="{ input: 'form-control' }">
                </autocomplete>
              </b-form-group>
            </b-col>
            <b-col cols="12" v-if="!this.$route.params.id" class="mb-3">
              Turno
              <b-form-select v-model="turno" :options="turnos" />
            </b-col>
            <b-col cols="12">
              <b-button-group>
                <b-button variant="danger" @click="cerrarCaja" v-if="!this.$route.params.id"><i
                    class="fa fa-unlock mx-1" aria-hidden="true"></i>
                  Cerrar Caja</b-button>
              </b-button-group>
              <b-button-group>
                <cierres-caja></cierres-caja>
              </b-button-group>
              <b-button-group>
                <movimiento @actualizar="loadData"></movimiento>
              </b-button-group>
              <b-button-group>
                <b-button class="mt-2" variant="primary" @click="imprimir"><i class="fa fa-print mx-1" aria-hidden="true"></i>
                  Imprimir</b-button>
              </b-button-group>
              <b-button-group>
                <b-button class="mt-2" variant="secondary" @click="imprimirTicket"><i class="fa fa-receipt mx-1"
                    aria-hidden="true"></i>
                  Imprimir Ticket</b-button>
              </b-button-group>
            </b-col>
          </b-row>
        </b-row>
        <div class="summary-panel">

          <div class="summary-card summary-card--main">
            <span class="summary-card__label"><i class="fas fa-coins"></i> Gran Total Combustible</span>
            <span class="summary-card__value">S/. {{ granTotalVenta | redondear }}</span>
          </div>

          <div class="summary-card summary-card--accent">
            <span class="summary-card__label"><i class="fas fa-cash-register"></i> Gran Total Efectivo</span>
            <span class="summary-card__value">S/. {{ granTotal | redondear }}</span>
          </div>

          <div class="summary-group">
            <div class="summary-group__title">Movimientos</div>
            <div class="summary-row"><span>Ingresos</span><b class="text-success">S/. {{ totalIngresos | redondear
            }}</b>
            </div>
            <div class="summary-row"><span>Salidas</span><b class="text-danger">S/. {{ totalSalidas | redondear }}</b>
            </div>
            <div class="summary-row"><span>Crédito</span><b>S/. {{ totalVentasCredito | redondear }}</b></div>
          </div>

          <div class="summary-group">
            <div class="summary-group__title">Métodos de pago</div>
            <div class="summary-row"><span>Efectivo</span><b>S/. {{ totalVentasEfectivo | redondear }}</b></div>
            <div class="summary-row"><span>Tarjeta y Yape/Plin</span><b>S/. {{ totalVentasVisaTransferencia | redondear
            }}</b>
            </div>
            <div class="summary-row"><span>Transferencia</span><b>S/. {{ totalVentasTransferencia | redondear }}</b>
            </div>
            <div class="summary-row"><span>Canjes</span><b>S/. {{ totalVentasCanje | redondear }}</b></div>
          </div>

          <div class="summary-group">
            <div class="summary-group__title">Vales</div>
            <div class="summary-row"><span>Vales de Crédito</span><b>S/. {{ totalVentasValeCredito | redondear }}</b>
            </div>
          </div>

          <!-- Contrómetros también visibles en panel lateral para impresión -->
          <b-row class="print ocultarNoPrint mt-2">
            <b-col cols="12">
              <table class="table tbl-historico" size="sm">
                <thead>
                  <tr>
                    <th>PRODUCTO</th>
                    <th>ISLA</th>
                    <th>CARA</th>
                    <th>V. I.</th>
                    <th>V. F</th>
                    <th>VENTA</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(resumen, index) in resumenHistorial" :key="index">
                    <td>{{ resumen.Producto }}</td>
                    <td>{{ resumen.Isla }}</td>
                    <td>Cara {{ resumen.Cara == 1 ? 'A' : (resumen.Cara == 2 ? 'B' : '') }}</td>
                    <td>{{ parseFloat(resumen.ValorInicial).toFixed(4) }}</td>
                    <td>{{ parseFloat(resumen.Restante).toFixed(4) }}</td>
                    <td>{{ parseFloat(resumen.VentaTotal).toFixed(4) }}</td>
                  </tr>
                </tbody>
              </table>
            </b-col>
          </b-row>
        </div>
      </b-col>
    </b-row>

    <!-- ============ VERSIÓN IMPRESIÓN (TICKET) ============ -->
    <b-row class="si-print" v-if="modoImpresion === 'detallado'">
      <!-- <b-col cols="12" class="text-center">
        <h3>Cierre de Caja</h3>
        <h6 v-if="cierre.FechaCierre">Fecha Cierre: {{ cierre.FechaCierre }}</h6>
        <h6 v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
        <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
      </b-col> -->
      <h2>Cierre de Caja</h2>
      <hr>
      <b-col cols="5">

        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Ingresos S/.</td>
              <td>{{ totalIngresos | redondear }}</td>
            </tr>
            <tr>
              <td>Salida S/.</td>
              <td>{{ totalSalidas | redondear }}</td>
            </tr>
            <tr>
              <td>Crédito S/.</td>
              <td>{{ totalVentasCredito | redondear }}</td>
            </tr>
          </tbody>
        </table>

        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Efectivo S/.</td>
              <td>{{ totalVentasEfectivo | redondear }}</td>
            </tr>
            <tr>
              <td>Tarjeta y Yape/Plin S/.</td>
              <td>{{ totalVentasVisaTransferencia | redondear }}</td>
            </tr>
            <tr>
              <td>Transferencia S/.</td>
              <td>{{ totalVentasTransferencia | redondear }}</td>
            </tr>
            <tr>
              <td>Canjes S/.</td>
              <td>{{ totalVentasCanje | redondear }}</td>
            </tr>
          </tbody>
        </table>

        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Vales de Crédito S/.</td>
              <td>{{ totalVentasValeCredito | redondear }}</td>
            </tr>
          </tbody>
        </table>

        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Gran Total Combustible S/.</td>
              <td>{{ granTotalVenta | redondear }}</td>
            </tr>
            <tr>
              <td>Gran Total Efectivo S/.</td>
              <td>{{ granTotal | redondear }}</td>
            </tr>
          </tbody>
        </table>

      </b-col>
    </b-row>

    <!-- ============ VERSIÓN IMPRESIÓN (TICKET SIMPLE / NUEVO FORMATO) ============ -->
    <b-row class="si-print ticket-simple" v-if="modoImpresion === 'simple'">
      <b-col cols="5">
        <table class="table table-sm tableResumenSimple">
          <tbody>
            <tr>
              <td>GRAN TOTAL COMBUSTIBLE</td>
              <td>{{ granTotalVenta | redondear }}</td>
            </tr>
            <tr>
              <td>INGRESOS</td>
              <td>{{ totalIngresos | redondear }}</td>
            </tr>
            <tr class="fila-espaciadora"><td colspan="2"></td></tr>
            <tr>
              <td>SALIDA</td>
              <td>{{ totalSalidas | redondear }}</td>
            </tr>
            <tr>
              <td>TARJETAS Y YAPE</td>
              <td>{{ totalVentasVisaTransferencia | redondear }}</td>
            </tr>
            <tr>
              <td>VALES DE CREDITO</td>
              <td>{{ totalVentasValeCredito | redondear }}</td>
            </tr>
            <tr>
              <td>CANJES</td>
              <td>{{ totalVentasCanje | redondear }}</td>
            </tr>
            <tr class="fila-espaciadora"><td colspan="2"></td></tr>
            <tr class="fila-gran-total-simple">
              <td>GRAN TOTAL EFECTIVO</td>
              <td>{{ granTotal | redondear }}</td>
            </tr>
          </tbody>
        </table>
      </b-col>
    </b-row>

  </b-container>
</template>

<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";
import Movimiento from '@/components/Movimiento'

// ---------- Subcomponentes pequeños de presentación ----------
const SectionBlock = {
  name: "section-block",
  props: {
    icon: { type: String, default: "" },
    title: { type: String, required: true },
    subtitle: { type: String, default: "" },
    accent: { type: String, default: "default" },
    compact: { type: Boolean, default: false }
  },
  template: `
    <div class="section-block" :class="['section-block--' + accent, { 'section-block--compact': compact }]">
      <div class="section-block__head">
        <i v-if="icon" class="fas" :class="icon"></i>
        <span class="section-block__title">{{ title }}</span>
        <span v-if="subtitle" class="section-block__subtitle">{{ subtitle }}</span>
      </div>
      <div class="section-block__body">
        <slot></slot>
      </div>
    </div>
  `
};

const DataTable = {
  name: "data-table",
  props: {
    headers: { type: Array, required: true },
    align: { type: Array, default: () => [] },
    small: { type: Boolean, default: false }
  },
  template: `
    <div class="table-responsive">
      <table class="table table-sm data-table" :class="{ 'data-table--small': small }">
        <thead>
          <tr>
            <th v-for="(h, i) in headers" :key="i" :class="'text-' + (align[i] || 'left')">{{ h }}</th>
          </tr>
        </thead>
        <tbody>
          <slot></slot>
        </tbody>
        <tfoot>
          <tr class="data-table__footer">
            <slot name="footer"></slot>
          </tr>
        </tfoot>
      </table>
    </div>
  `
};

const EstadoBadge = {
  name: "estado-badge",
  props: { anulado: { type: [Boolean, Number], default: false } },
  template: `
    <span class="estado-badge" :class="anulado ? 'estado-badge--anulado' : 'estado-badge--aplicado'">
      {{ anulado ? 'ANULADO' : 'APLICADO' }}
    </span>
  `
};

export default {
  components: { CierresCaja, Movimiento, "section-block": SectionBlock, "data-table": DataTable, "estado-badge": EstadoBadge },
  async mounted() {
    this.Usuario = sessionStorage.getItem("User");
    this.IdSurtidor = sessionStorage.getItem("IdSurtidor");

    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id);
      this.loadData();
    } else {
      this.getVendedor();
    }

    if (!this.$route.params.id) {
      await this.obtenerHistorioGalones();
    } else {
      await this.obtenerHistorioGalonesCierre();
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
      salidasYape: [],
      productosVendidos: [],
      historial: [],
      productosVendidosTotal: 0,
      resumenHistorial: [],
      Usuario: "",
      IdSurtidor: "",
      modoImpresion: "detallado"
    };
  },
  computed: {
    totalSalidasYape() {
      return this.salidasYape
        .filter(s => !s.Anulado)
        .reduce((t, s) => t + parseFloat(s.Importe || 0), 0);
    },
    totalVentasMastercardNeto() {
      return this.totalVentasMastercard - this.totalSalidasYape;
    },
    FechaActual() {
      return this.$moment().format("YYYY-MM-DD H:mm:ss");
    },
    // ---- Totales generales (migrados del cierre antiguo) ----
    granTotalVenta() {
      return (
        this.totalVentasEfectivo +
        this.totalVentasMastercard +
        this.totalVentasVisa +
        this.totalVentasTransferencia +
        this.totalVentasValeCredito +
        this.totalVentasCanje
      );
    },
    granTotalGeneral() {
      return this.granTotalVenta + this.totalIngresos - this.totalSalidas;
    },
    granTotal() {
      return (
        this.totalVentasEfectivo +
        this.totalVentasMastercardNeto +
        this.totalVentasCanje +
        this.totalIngresos -
        this.totalSalidas
      );
    },
    totalSalidas() {
      return this.salidas
        .filter(s => !s.Anulado)
        .reduce((t, s) => t + s.Importe, 0);
    },
    totalIngresos() {
      return this.ingresos
        .filter(i => !i.Anulado)
        .reduce((t, i) => t + i.Importe, 0);
    },
    totalVentasEfectivo() {
      return this.ventasEfectivo
        .filter(v => !v.Anulado && v.CodSunat != "07")
        .reduce((t, v) => t + v.Efectivo, 0);
    },
    ventasEfectivo() {
      return this.ventas.filter(v => v.Efectivo > 0 && !v.EsCredito && v.EsVale == 0);
    },
    totalVentasVisa() {
      return this.ventasVisa
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Visa, 0);
    },
    ventasVisa() {
      return this.ventas.filter(v => v.Visa > 0 && !v.EsCredito && v.EsVale == 0);
    },

    // ✅ CORREGIDO: ahora filtra Anulado en ambas listas
    totalVentasVisaTransferencia() {
      const visa = this.ventasVisa
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Visa, 0);
      return visa + this.totalVentasMastercardNeto;
    },

    totalVentasTransferencia() {
      return this.ventasTransferencia
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Transferencia, 0);
    },
    ventasTransferencia() {
      return this.ventas.filter(v => v.Transferencia > 0 && !v.EsCredito && v.EsVale == 0);
    },
    totalVentasVale() {
      return this.ventasVale
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Transferencia + v.Mastercard + v.Efectivo + v.Visa, 0);
    },
    ventasVale() {
      return this.ventas.filter(v => v.EsVale == 1);
    },
    totalVentasValeCredito() {
      return this.ventasValeCredito
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Transferencia + v.Mastercard + v.Efectivo + v.Visa, 0);
    },
    ventasValeCredito() {
      return this.ventas.filter(v => v.EsVale == 1 && v.CodSunat == "13");
    },
    totalVentasCanje() {
      return this.ventasValeCanje
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Transferencia + v.Mastercard + v.Efectivo + v.Visa, 0);
    },
    ventasValeCanje() {
      return this.ventas.filter(v => v.EsVale == 2 && v.CodSunat == "14");
    },
    totalVentasMastercard() {
      return this.ventasMastercard
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Mastercard, 0);
    },
    ventasMastercard() {
      return this.ventas.filter(v => v.Mastercard > 0 && !v.EsCredito && v.EsVale == 0);
    },
    totalVentasCredito() {
      return this.ventasCredito
        .filter(v => !v.Anulado)
        .reduce((t, v) => t + v.Total, 0);
    },
    ventasCredito() {
      return this.ventas.filter(v => v.EsCredito);
    }
  },
  methods: {
    sumaCampo(lista, campo) {
      return lista.reduce((sum, item) => sum + parseFloat(item[campo] || 0), 0);
    },
    // ---- Migrado del cierre antiguo: marcar/resaltar fila al hacer click ----
    toggleMarcado(item) {
      item.check = !item.check;
    },
    async obtenerHistorioGalones() {
      await this.axios(`${this.API}historialgalones/resumen`, {
        params: {
          IdTipoProducto: this.IdTipoProducto,
          Usuario: this.Usuario,
          IdSurtidor: this.IdSurtidor
        }
      }).then(res => {
        this.historial = res.data.historial;
        this.ventas = res.data.ventas;
      });
      this.recalcularHistorial();
    },
    async obtenerHistorioGalonesCierre() {
      await this.axios(
        `${this.API}historialgalones/resumen/cierre?idCierre=${this.$route.params.id}`
      ).then(res => {
        this.historial = res.data.historial;
        this.ventas = res.data.ventas;
      });
      this.recalcularHistorialVer();
    },
    recalcularHistorial() {
      let resumen = {};

      this.historial.forEach(item => {
        const key = `${item.IdIsla}-${item.IdProducto}-${item.Cara}`;
        if (!resumen[key]) {
          resumen[key] = {
            Producto: item.Producto,
            Isla: item.Isla,
            Cara: item.Cara,
            ValorInicial: parseFloat(item.Galones) || 0,
            VentaTotal: 0,
            Restante: 0
          };
        }
      });

      this.ventas.forEach(venta => {
        const key = `${venta.IdIsla}-${venta.IdProducto}-${venta.Cara}`;
        if (venta.IdCierre && resumen[key]) {
          resumen[key].ValorInicial -= parseFloat(venta.Total) || 0;
        }
      });

      this.ventas.forEach(venta => {
        const key = `${venta.IdIsla}-${venta.IdProducto}-${venta.Cara}`;
        if (!venta.IdCierre && resumen[key]) {
          resumen[key].VentaTotal += parseFloat(venta.Total) || 0;
        }
      });

      Object.values(resumen).forEach(item => {
        item.Restante = item.ValorInicial - item.VentaTotal;
      });

      this.resumenHistorial = Object.values(resumen);
    },
    recalcularHistorialVer() {
      let resumen = {};
      let idCierreActual = parseInt(this.$route.params.id);

      this.historial.forEach(item => {
        const key = `${item.IdIsla}-${item.IdProducto}-${item.Cara}`;
        if (!resumen[key]) {
          resumen[key] = {
            Producto: item.Producto,
            Isla: item.Isla,
            Cara: item.Cara,
            ValorInicial: parseFloat(item.Galones) || 0,
            VentaTotal: 0,
            Restante: 0
          };
        }
      });

      this.ventas.forEach(venta => {
        const key = `${venta.IdSurtidor}-${venta.IdProducto}-${venta.Cara}`;
        if (resumen[key]) {
          if (venta.IdCierre === idCierreActual) {
            resumen[key].VentaTotal = parseFloat(venta.Total) || 0;
          } else {
            resumen[key].ValorInicial -= parseFloat(venta.Total) || 0;
          }
        }
      });

      Object.keys(resumen).forEach(key => {
        resumen[key].Restante = resumen[key].ValorInicial - resumen[key].VentaTotal;
      });

      this.resumenHistorial = Object.values(resumen);
    },
    reportePorYape() {
      let productos = [];
      this.ventasMastercard.filter(v => !v.Anulado).forEach(venta => {
        if (!venta.productos) return;
        venta.productos.forEach(producto => {
          productos.push({ ...producto, montoVenta: venta.Yape });
        });
      });

      const productosUnicos = Array.from(new Set(productos.map(i => i.IdProducto)))
        .map(id => ({ IdProducto: id }));

      let resultado = [];
      productosUnicos.forEach(p => {
        let lst = productos.filter(v => v.IdProducto == p.IdProducto);
        resultado.push({
          IdProducto: p.IdProducto,
          Producto: lst[0].Producto,
          total: lst.reduce((t, x) => t + parseFloat(x.Cantidad), 0),
          Precio: lst.reduce((t, x) => t + parseFloat(x.Precio * x.Cantidad) - parseFloat(x.Descuento), 0)
        });
      });
      return resultado;
    },

    reportePorTransferencia() {
      let productos = [];
      this.ventasTransferencia.filter(v => !v.Anulado).forEach(venta => {
        if (!venta.productos) return;  // ← AGREGAR ESTA GUARDA
        venta.productos.forEach(producto => {
          productos.push({ ...producto, montoVenta: venta.Mastercard });
        });
      });

      const productosUnicos = Array.from(new Set(productos.map(i => i.IdProducto)))
        .map(id => ({ IdProducto: id }));

      let resultado = [];
      productosUnicos.forEach(p => {
        let lst = productos.filter(v => v.IdProducto == p.IdProducto);
        resultado.push({
          IdProducto: p.IdProducto,
          Producto: lst[0].Producto,
          total: lst.reduce((t, x) => t + parseFloat(x.Cantidad), 0),
          Precio: lst.reduce((t, x) => t + parseFloat(x.Precio * x.Cantidad) - parseFloat(x.Descuento), 0)
        });
      });
      return resultado;
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
      this.modoImpresion = "detallado";
      this.$nextTick(() => {
        window.print();
      });
    },
    imprimirTicket() {
      this.modoImpresion = "simple";
      this.$nextTick(() => {
        window.print();
      });
    },
    getSalidasYape() {
      let params = { idCierre: this.$route.params.id };
      if (this.vendedor.Usuario) params.usuario = this.vendedor.Usuario;

      this.axios
        .get(this.API + "cajaybanco/salidasyape", { params })
        .then(res => { this.salidasYape = res.data; })
        .catch(err => console.log(err));
    },
    loadData() {
      this.productosVendidos = [];
      this.productosVendidosTotal = 0;
      this.getVentas();
      this.getIngresos();
      this.getSalidas();
      this.getSalidasYape();
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
          });
      }
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    formatoHora(fecha) {
      if (!fecha) return "";
      if (typeof fecha === "string" && fecha.includes(" ")) {
        return fecha.split(" ")[1].substring(0, 5);
      }
      const d = new Date(fecha);
      return isNaN(d) ? fecha : d.toLocaleTimeString("es-PE", { hour: "2-digit", minute: "2-digit" });
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
          this.productosVendidosTotal = res.data.reduce((sum, pv) => sum + pv.MontoVendido, 0);
        })
        .catch(err => console.log(err));
    },
    getVentas() {
      let params = { idCierre: this.$route.params.id };
      if (this.vendedor.Usuario) params.usuario = this.vendedor.Usuario;

      this.axios
        .get(this.API + "cierrecaja/ventas", { params })
        .then(res => {
          this.ventas = res.data;
          this.ventas.forEach(vt => { vt.check = false; });
          if (this.ventas.length > 0) this.getProductos(this.ventas);
        })
        .catch(err => console.log(err));
    },
    getIngresos() {
      let params = { idCierre: this.$route.params.id };
      if (this.vendedor.Usuario) params.usuario = this.vendedor.Usuario;

      this.axios
        .get(this.API + "cierrecaja/ingresos", { params })
        .then(res => { this.ingresos = res.data; })
        .catch(err => console.log(err));
    },
    getSalidas() {
      let params = { idCierre: this.$route.params.id };
      if (this.vendedor.Usuario) params.usuario = this.vendedor.Usuario;

      this.axios
        .get(this.API + "cierrecaja/salidas", { params })
        .then(res => { this.salidas = res.data; })
        .catch(err => console.log(err));
    },
    getCierre(id) {
      this.axios
        .get(this.API + "cierrecaja", { params: { idCierre: id } })
        .then(res => { this.cierre = res.data[0]; });
    }
  }
};
</script>

<style scoped>
/* ============ BASE ============ */
.cierre-wrapper {
  --c-bg: #f4f6f9;
  --c-card: #ffffff;
  --c-border: #e3e7ee;
  --c-text: #1f2733;
  --c-text-soft: #6b7785;
  --c-primary: #1a237e;
  --c-success: #1d8a4e;
  --c-danger: #c62828;
  --c-warning: #b8860b;
  background: var(--c-bg);
  color: var(--c-text);
  font-family: "Segoe UI", Roboto, Arial, sans-serif;
}

/* ============ HEADER ============ */
.header-bar {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 10px;
  padding: 14px 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 12px;
  box-shadow: 0 1px 3px rgba(20, 30, 60, 0.05);
}

.header-bar__title {
  margin: 0;
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--c-primary);
}

.header-bar__meta {
  display: flex;
  gap: 16px;
  font-size: 0.85rem;
  color: var(--c-text-soft);
  margin-top: 2px;
}

.header-bar__meta i {
  margin-right: 4px;
  color: var(--c-primary);
}

.header-bar__actions {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 4px;
}

.turno-select {
  width: 150px;
}

.vendedor-input {
  width: 200px;
}

/* ============ TABS ============ */
.cierre-tabs>>>.nav-tabs .nav-link {
  font-weight: 600;
  color: var(--c-text-soft);
  border: none;
  border-bottom: 3px solid transparent;
}

.cierre-tabs>>>.nav-tabs .nav-link.active {
  color: var(--c-primary);
  background: transparent;
  border-bottom: 3px solid var(--c-primary);
}

.details {
  max-height: 78vh;
  overflow-y: auto;
  padding-top: 8px;
}

/* ============ SECTION BLOCK ============ */
.section-block {
  margin-bottom: 18px;
  border: 1px solid var(--c-border);
  border-radius: 10px;
  overflow: hidden;
  background: var(--c-card);
}

.section-block--compact {
  margin-bottom: 0;
}

.section-block__head {
  display: flex;
  align-items: baseline;
  gap: 8px;
  padding: 10px 14px;
  background: #f8f9fc;
  border-bottom: 1px solid var(--c-border);
  font-weight: 700;
  font-size: 0.95rem;
}

.section-block__head i {
  color: var(--c-text-soft);
  font-size: 0.9rem;
}

.section-block__subtitle {
  font-size: 0.75rem;
  font-weight: 500;
  color: var(--c-text-soft);
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.section-block__body {
  padding: 4px 0;
}

/* Acentos de color por tipo de medio de pago (barra izquierda) */
.section-block--efectivo .section-block__head {
  border-left: 4px solid #1d8a4e;
}

.section-block--tarjeta .section-block__head {
  border-left: 4px solid #1565c0;
}

.section-block--yape .section-block__head {
  border-left: 4px solid #6a1b9a;
}

.section-block--transferencia .section-block__head {
  border-left: 4px solid #00838f;
}

.section-block--vale .section-block__head {
  border-left: 4px solid #b8860b;
}

.section-block--canje .section-block__head {
  border-left: 4px solid #8d6e63;
}

.section-block--credito .section-block__head {
  border-left: 4px solid #455a64;
}

.section-block--ingreso .section-block__head {
  border-left: 4px solid #1d8a4e;
}

.section-block--salida .section-block__head {
  border-left: 4px solid #c62828;
}

.section-block--default .section-block__head {
  border-left: 4px solid var(--c-primary);
}

/* ============ DATA TABLE ============ */
.data-table {
  margin-bottom: 0;
  font-size: 0.85rem;
}

.data-table thead th {
  background: #fff;
  color: var(--c-text-soft);
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  border-top: none;
  border-bottom: 2px solid var(--c-border);
  white-space: nowrap;
}

.data-table tbody tr {
  cursor: pointer;
  transition: background 0.12s ease;
}

.data-table tbody tr:hover {
  background: #f5f7fb;
}

.data-table tbody td {
  vertical-align: middle;
}

.data-table--small {
  font-size: 0.78rem;
}

.data-table .num {
  font-variant-numeric: tabular-nums;
  font-weight: 600;
}

.data-table__footer td {
  border-top: 2px solid var(--c-border);
  background: #f8f9fc;
  font-size: 0.85rem;
}

/* fila anulada */
.anulado td {
  text-decoration: line-through;
  color: #9aa3af;
}

/* fila marcada por el usuario (click) */
.marcado td {
  background-color: #d1e7dd !important;
}

/* Badge de cara A/B (combustible) */
.badge-cara {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 12px;
  font-size: 0.72rem;
  font-weight: 700;
}

.badge-cara--a {
  background: #e3f2fd;
  color: #1565c0;
}

.badge-cara--b {
  background: #e8f5e9;
  color: #1d8a4e;
}

/* Badge de estado */
.estado-badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 12px;
  font-size: 0.7rem;
  font-weight: 700;
  letter-spacing: 0.02em;
}

.estado-badge--aplicado {
  background: #e3f2fd;
  color: #1565c0;
}

.estado-badge--anulado {
  background: #fdecea;
  color: var(--c-danger);
}

/* Alerta gran total */
.gran-total-alert {
  margin: 0 16px 16px;
  font-size: 1.05rem;
  border-radius: 10px;
}

/* ============ PANEL LATERAL RESUMEN ============ */
.summary-panel {
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 10px;
  padding: 16px;
  box-shadow: 0 1px 3px rgba(20, 30, 60, 0.05);
  position: sticky;
  top: 12px;
}

.summary-card {
  border-radius: 8px;
  padding: 12px 14px;
  margin-bottom: 10px;
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.summary-card--main {
  background: linear-gradient(135deg, #1a237e, #283593);
  color: #fff;
}

.summary-card--accent {
  background: linear-gradient(135deg, #1d8a4e, #2e7d32);
  color: #fff;
}

.summary-card__label {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  opacity: 0.85;
}

.summary-card__value {
  font-size: 1.3rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

.summary-group {
  border-top: 1px solid var(--c-border);
  padding-top: 10px;
  margin-top: 10px;
}

.summary-group__title {
  font-size: 0.72rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: var(--c-text-soft);
  font-weight: 700;
  margin-bottom: 6px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  padding: 4px 0;
}

.summary-row b {
  font-variant-numeric: tabular-nums;
}

/* ============ IMPRESIÓN ============ */
.tbl-historico {
  width: 100%;
  border-collapse: collapse;
}

.tbl-historico th,
.tbl-historico td {
  padding: 2px 4px;
  text-align: left;
  white-space: nowrap;
}

.ocultarNoPrint {
  display: none;
}

.si-print {
  display: none;
}

/* ✅ CLAVE: fuera del @media print para que funcione en preview */
.tableResumen {
  width: 100%;
  font-weight: bold;
  table-layout: fixed;
}

.tableResumen td:first-child {
  width: 65%;
}

.tableResumen td:last-child {
  width: 35%;
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.tableResumenSimple {
  width: 100%;
  font-weight: bold;
  table-layout: fixed;
  border: none;
}

.tableResumenSimple td {
  border: none;
  padding: 6px 4px;
  font-size: 0.95rem;
  text-transform: uppercase;
}

.tableResumenSimple td:first-child {
  width: 65%;
  text-align: left;
}

.tableResumenSimple td:last-child {
  width: 35%;
  text-align: right;
  font-variant-numeric: tabular-nums;
}

.tableResumenSimple .fila-espaciadora td {
  padding: 10px 0 0 0;
}

.tableResumenSimple .fila-gran-total-simple td {
  border-top: 1px solid #000;
  padding-top: 10px;
}

@media print {
  .no-print {
    display: none !important;
  }

  .si-print {
    display: block !important;
  }

  .ocultarNoPrint {
    display: block;
    font-size: 0.6rem;
  }

  th {
    background: #fff !important;
    color: #000 !important;
    border: 1px solid #ccc;
  }

  .page-break {
    page-break-before: always;
  }


  /* Ticket de impresión */
  .tableResumen {
    width: 100%;
    font-weight: bold;
  }

  .tableResumen td:last-child {
    text-align: right;
    font-variant-numeric: tabular-nums;
  }

  .fila-salida-yape td {
    background-color: #fff3f3;
    color: #c62828;
  }

  .badge-tipo {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 12px;
    font-size: 0.72rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .badge-tipo--yape {
    background: #f3e5f5;
    color: #6a1b9a;
  }

  .badge-tipo--tarjeta {
    background: #e3f2fd;
    color: #1565c0;
  }

  .badge-tipo--salida {
    background: #fdecea;
    color: #c62828;
  }

  .fila-salida-resumen td {
    color: #c62828;
  }

  /* Fila de gran total en tfoot */
  .fila-gran-total td {
    border-top: 2px solid var(--c-border);
    background: #f0f4ff;
    font-size: 0.9rem;
  }

  .ticket-simple .tableResumenSimple td {
    border: none;
  }
}
</style>
