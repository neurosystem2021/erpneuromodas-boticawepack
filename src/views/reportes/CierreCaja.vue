<template>
  <div class="pos-jobi" :class="{ 'modo-ticket': imprimiendoTicket }">
    <b-container class="bv-example-row pos-container" fluid>
      <div class="pos-card">

        <!-- ================= BARRA DE ACCIONES (no se imprime) ================= -->
        <div class="pos-panel pos-panel-header no-print">
          <b-row class="align-items-end pos-header-row">
            <b-col md="4" v-if="!this.$route.params.id">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-user-tie pos-label-icon"></i> Vendedor</label>
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

            <b-col md="3" v-if="!this.$route.params.id">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-clock pos-label-icon"></i> Turno</label>
                <b-form-select v-model="turno" :options="turnos" />
              </b-form-group>
            </b-col>

            <b-col :md="!this.$route.params.id ? 5 : 12" class="d-flex align-items-center pos-header-actions">
              <b-button v-if="!this.$route.params.id" class="pos-btn-cerrar" @click="cerrarCaja">
                <i class="fas fa-lock"></i> Cerrar Caja
              </b-button>
              <div class="pos-btn-cierres">
                <cierres-caja></cierres-caja>
              </div>
              <b-button class="pos-btn-imprimir" @click="imprimir">
                <i class="fas fa-print"></i> Imprimir
              </b-button>
              <b-button class="pos-btn-imprimir-ticket" @click="imprimirTicket" title="Imprimir resumen en ticketera">
                <i class="fas fa-receipt"></i> Ticket
              </b-button>
            </b-col>
          </b-row>
        </div>

        <!-- ================= TÍTULO ================= -->
        <div class="pos-title-block">
          <h2 class="pos-title">Cierre de Caja</h2>
          <div class="pos-title-meta">
            <span v-if="cierre.FechaCierre" class="pos-meta-badge"><i class="fas fa-calendar-day"></i> {{ cierre.FechaCierre }}</span>
            <span v-if="cierre.IdTurno" class="pos-meta-badge"><i class="fas fa-clock"></i> {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</span>
            <span v-if="cierre.UsuarioReg" class="pos-meta-badge"><i class="fas fa-user"></i> {{ cierre.UsuarioReg }}</span>
          </div>
        </div>

        <!-- ================= TARJETAS RESUMEN ================= -->
        <div class="pos-summary-grid">
          <div class="pos-stat-card pos-stat-navy pos-stat-full">
            <div class="pos-stat-icon"><i class="fas fa-coins"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Gran Total</div>
              <div class="pos-stat-value">S/. {{ granTotal | redondear }}</div>
            </div>
          </div>

          <div class="pos-stat-card pos-stat-teal">
            <div class="pos-stat-icon"><i class="fas fa-arrow-down"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Ingresos</div>
              <div class="pos-stat-value">S/. {{ totalIngresos | redondear }}</div>
            </div>
          </div>
          <div class="pos-stat-card pos-stat-red">
            <div class="pos-stat-icon"><i class="fas fa-arrow-up"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Salida</div>
              <div class="pos-stat-value">S/. {{ totalSalidas | redondear }}</div>
            </div>
          </div>
          <div class="pos-stat-card pos-stat-orange">
            <div class="pos-stat-icon"><i class="fas fa-calendar-alt"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Crédito</div>
              <div class="pos-stat-value">S/. {{ totalVentasCredito | redondear }}</div>
            </div>
          </div>

          <div class="pos-stat-card pos-stat-green">
            <div class="pos-stat-icon"><i class="fas fa-money-bill-wave"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Efectivo</div>
              <div class="pos-stat-value">S/. {{ totalVentasEfectivo | redondear }}</div>
            </div>
          </div>
          <div class="pos-stat-card pos-stat-purple">
            <div class="pos-stat-icon"><i class="fas fa-mobile-alt"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Tarjeta</div>
              <div class="pos-stat-value">S/. {{ totalVentasVisa | redondear }}</div>
            </div>
          </div>
          <div class="pos-stat-card pos-stat-blue">
            <div class="pos-stat-icon"><i class="fas fa-university"></i></div>
            <div class="pos-stat-body">
              <div class="pos-stat-label">Transferencia</div>
              <div class="pos-stat-value">S/. {{ totalVentasMastercard | redondear }}</div>
            </div>
          </div>
        </div>

        <!-- ================= VENTAS EFECTIVO ================= -->
        <div class="pos-section">
          <div class="pos-section-title pos-section-title-green"><i class="fas fa-money-bill-wave"></i> Ventas Efectivo</div>
          <div class="pos-table-card">
            <table class="table b-table table-sm">
              <thead>
                <tr>
                    <th>Recibo</th><th>Fecha</th>
                    <th v-if="$route.query.noDetalle">Cliente</th>
                    <th v-else>Productos</th>
                    <th>Estado</th><th style="width:90px">Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>

                      <table class="tabla-detalle" v-if="!$route.query.noDetalle">
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
                    <div><i><small>
                    <b v-if="$route.query.noDetalle"></b>
                    <b v-else>Cliente:</b>

                     {{ item.Cliente }}</small></i></div>

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
          </div>
        </div>

        <!-- ================= VENTAS YAPE/PLIN ================= -->
        <div class="pos-section">
          <div class="pos-section-title pos-section-title-purple"><i class="fas fa-mobile-alt"></i> Ventas Yape/Plin</div>
          <div class="pos-table-card">
            <table class="table b-table table-sm">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th>
                  <th v-if="$route.query.noDetalle">Cliente</th>
                  <th v-else>Productos</th>
                  <th>Detalle</th><th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>

                      <table class="tabla-detalle" v-if="!$route.query.noDetalle">
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
                    <div><i><small>
                    <b v-if="$route.query.noDetalle"></b>
                    <b v-else>Cliente:</b>
                    {{ item.Cliente }}</small></i></div>

                  </td>
                  <td>{{ item.VisaDesc }}</td>
                  <td>{{ estadoVenta(item) }}</td>
                  <td>S/. {{ item.Visa | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="4"></td>
                  <td class="total"><b>Total Yape/Plin</b></td>
                  <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- ================= VENTAS TRANSFERENCIA ================= -->
        <div class="pos-section">
          <div class="pos-section-title pos-section-title-blue"><i class="fas fa-university"></i> Ventas Transferencia</div>
          <div class="pos-table-card">
            <table class="table b-table table-sm">
              <thead>
                <tr>
                    <th>Recibo</th><th>Fecha</th>
                    <th v-if="$route.query.noDetalle">Cliente</th>
                    <th v-else>Productos</th>
                    <th>Detalle</th><th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>

                      <table class="tabla-detalle" v-if="!$route.query.noDetalle">
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
                    <div><i><small>
                    <b v-if="$route.query.noDetalle"></b>
                    <b v-else>Cliente:</b>
                    {{ item.Cliente }}</small></i></div>

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
          </div>
        </div>

        <!-- ================= VENTAS A CREDITO ================= -->
        <div class="pos-section credito">
          <div class="pos-section-title pos-section-title-orange"><i class="fas fa-calendar-alt"></i> Ventas a Credito</div>
          <div class="pos-table-card">
            <table class="table b-table table-sm">
              <thead>
                <tr>
                  <th>Recibo</th><th>Fecha</th>
                  <th v-if="$route.query.noDetalle">Cliente</th>
                  <th v-else>Productos</th>
                  <th>Estado</th><th>Pagado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
                  <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>

                      <table class="tabla-detalle" v-if="!$route.query.noDetalle">
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
                    <div><i><small>
                    <b v-if="$route.query.noDetalle"></b>
                    <b v-else>Cliente:</b>
                    {{ item.Cliente }}</small></i></div>


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
          </div>
        </div>

        <!-- ================= INGRESOS / SALIDAS ================= -->
        <b-row>
          <b-col md="6" v-if="totalIngresos">
            <div class="pos-section">
              <div class="pos-section-title pos-section-title-teal"><i class="fas fa-arrow-down"></i> Ingresos</div>
              <div class="pos-table-card">
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
              </div>
            </div>
          </b-col>
          <b-col md="6" v-if="totalSalidas">
            <div class="pos-section">
              <div class="pos-section-title pos-section-title-red"><i class="fas fa-arrow-up"></i> Salidas</div>
              <div class="pos-table-card">
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
              </div>
            </div>
          </b-col>
        </b-row>

        <!-- ================= INGRESOS / SALIDAS CUENTA BANCARIA ================= -->
        <b-row v-if="totalIngresosTarjeta">
          <b-col>
            <div class="pos-section">
              <div class="pos-section-title pos-section-title-teal"><i class="fas fa-university"></i> Ingresos Cuenta Bancaria</div>
              <div class="pos-table-card">
                <table class="table b-table table-sm">
                  <thead>
                    <tr>
                      <th>ID</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in ingresosTarjeta" :key="index">
                      <td>{{ item.IdCajaBanco }}</td>
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
              </div>
            </div>
          </b-col>
        </b-row>
        <b-row v-if="totalSalidasTarjeta">
          <b-col>
            <div class="pos-section">
              <div class="pos-section-title pos-section-title-red"><i class="fas fa-university"></i> Salidas de Cuentas Bancarias</div>
              <div class="pos-table-card">
                <table class="table b-table table-sm">
                  <thead>
                    <tr>
                      <th>ID</th><th>Cuenta</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in salidasTarjeta" :key="index">
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
                      <td class="total-salida"><b>Total Salidas Tarjetas</b></td>
                      <td class="total-salida"><b>S/. {{ totalSalidasTarjeta | redondear }}</b></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </b-col>
        </b-row>

        <!-- ================= ADELANTOS ================= -->
        <b-row v-if="totalAdelantos">
          <b-col>
            <div class="pos-section">
              <div class="pos-section-title pos-section-title-orange"><i class="fas fa-hand-holding-usd"></i> Descontar Adelantos</div>
              <div class="pos-table-card">
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
              </div>
            </div>
          </b-col>
        </b-row>

        <!-- ================= GRAN TOTAL ================= -->
        <div class="pos-final-total">
          <i class="fas fa-trophy"></i>
          <span>GRAN TOTAL: S/. {{ granTotal | moneda }}</span>
        </div>

        <!-- ================= TICKET RESUMEN (solo visible al imprimir en modo ticket) ================= -->
        <div class="pos-ticket">
          <div class="ticket-empresa">CIERRE DE CAJA</div>
          <div class="ticket-linea"></div>

          <div class="ticket-dato"><span>Fecha:</span><span>{{ cierre.FechaCierre || fechaImpresion }}</span></div>
          <div class="ticket-dato" v-if="cierre.IdTurno || turno">
            <span>Turno:</span>
            <span>{{ textoTurno(cierre.IdTurno || turno) }}</span>
          </div>
          <div class="ticket-dato" v-if="cierre.UsuarioReg || (vendedor && vendedor.NombreUsuario)">
            <span>Vendedor:</span>
            <span>{{ cierre.UsuarioReg || vendedor.NombreUsuario }}</span>
          </div>

          <div class="ticket-linea"></div>
          <div class="ticket-seccion-titulo">VENTAS POR FORMA DE PAGO</div>

          <div class="ticket-dato"><span>Efectivo ({{ cantidadEfectivo }})</span><span>S/. {{ totalVentasEfectivo | redondear }}</span></div>
          <div class="ticket-dato"><span>Yape/Plin ({{ cantidadVisa }})</span><span>S/. {{ totalVentasVisa | redondear }}</span></div>
          <div class="ticket-dato"><span>Transferencia ({{ cantidadMastercard }})</span><span>S/. {{ totalVentasMastercard | redondear }}</span></div>
          <div class="ticket-dato"><span>Credito ({{ cantidadCredito }})</span><span>S/. {{ totalVentasCredito | redondear }}</span></div>

          <div class="ticket-linea"></div>
          <div class="ticket-dato ticket-bold"><span>Total Ventas</span><span>S/. {{ totalVentas | redondear }}</span></div>

          <template v-if="totalIngresos || totalSalidas || totalAdelantosActivos">
            <div class="ticket-linea"></div>
            <div class="ticket-seccion-titulo">MOVIMIENTOS DE CAJA</div>
            <div class="ticket-dato" v-if="totalIngresos"><span>Ingresos</span><span>S/. {{ totalIngresos | redondear }}</span></div>
            <div class="ticket-dato" v-if="totalSalidas"><span>Salidas</span><span>- S/. {{ totalSalidas | redondear }}</span></div>
            <div class="ticket-dato" v-if="totalAdelantosActivos"><span>Adelantos</span><span>- S/. {{ totalAdelantosActivos | redondear }}</span></div>
          </template>

          <div class="ticket-linea"></div>
          <div class="ticket-total">
            <span>GRAN TOTAL</span>
            <span>S/. {{ granTotal | redondear }}</span>
          </div>
          <div class="ticket-linea"></div>

          <div class="ticket-footer">
            <div>Impreso: {{ fechaImpresion }}</div>
            <div class="ticket-firma">_______________________</div>
            <div>Firma Responsable</div>
          </div>
        </div>

      </div>
    </b-container>
  </div>
</template>
<script>
import config from '@/store/config.js'
import CierresCaja from '@/components/CierresCaja.vue'

export default {
  mounted () {
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id)
      this.loadData()
    } else {
        this.getVendedor()
    }
    // Cuando el navegador termina de imprimir (o el usuario cancela),
    // volvemos a la vista normal del reporte completo.
    window.addEventListener('afterprint', this.finalizarImpresion)
  },
  beforeDestroy () {
    window.removeEventListener('afterprint', this.finalizarImpresion)
  },
  data () {
    return {
      API: config.API,
      ventas: [],
      ingresos: [],
      salidas: [],
	    adelantos: [],
      ingresosTarjeta: [],
      salidasTarjeta: [],
      turno: null,
      turnos: [
        { value: null, text: 'Seleccione un Turno' },
        { value: 1, text: 'Mañana' },
        { value: 2, text: 'Tarde' },
        { value: 3, text: 'Noche' }
      ],
      cierre: {},
      vendedor: {},
      correcto:false,
      // true mientras se imprime el resumen tipo ticket (ticketera)
      imprimiendoTicket: false
    }
  },
  computed: {
    getFechaCierre () {
      //return this.$route.params.id ? 
    },
    granTotal () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard + this.totalIngresos - this.totalSalidas - this.totalAdelantosActivos
    },
    granTotalSinPagoCliente () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard  - this.totalSalidas
    },
    totalVentas () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard
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
    totalIngresosTarjeta () {
      let total = 0
      this.ingresosTarjeta.forEach(ingresoTar => {
        if (!ingresoTar.Anulado) total += ingresoTar.Importe
      });
      return total 
    },
    totalSalidasTarjeta () {
      let total = 0
      this.salidasTarjeta.forEach(salidaTar => {
        if (!salidaTar.Anulado) total += salidaTar.Importe
      });
      return total 
    },
  	totalAdelantos () {
  		let total = 0
  		this.adelantos.forEach(ade => {
  			if (!ade.Anulado) total += ade.Importe
  		})
  		return total
  	},
    totalAdelantosActivos () {
  		let total = 0
  		this.adelantos.forEach(ade => {
  			if (!ade.Anulado  &&  !ade.AnuladoVenta && !ade.EsCreditoVenta) total += ade.Importe
  		})
  		return total
  	},
    totalVentasEfectivo () {
      let total = 0
      this.ventasEfectivo.forEach(venta => {
        if (!venta.Anulado && venta.CodSunat!='07') total += venta.Efectivo
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
    },
    // ---- Conteos usados en el ticket ----
    cantidadEfectivo () {
      return this.ventasEfectivo.filter(v => !v.Anulado).length
    },
    cantidadVisa () {
      return this.ventasVisa.filter(v => !v.Anulado).length
    },
    cantidadMastercard () {
      return this.ventasMastercard.filter(v => !v.Anulado).length
    },
    cantidadCredito () {
      return this.ventasCredito.filter(v => !v.Anulado).length
    },
    fechaImpresion () {
      return new Date().toLocaleString('es-PE')
    }
  },
  methods: {
    getDetalles (idDocVenta) {
      let detalles = this.itemsDetalle.filter(item => item.IdDocVenta == idDocVenta)
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles
    },
    getVendedor () {
        let usuario = sessionStorage.getItem('User')
        this.axios.get(this.API + 'usuarios/' + usuario)
        .then((response) => {
          this.correcto = true
            this.setVendedor(response.data)
            this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
            this.$refs.inputVendedor.$refs.input.disabled = true

            if(response.data.IdUsuarioPerfil !== 1) {
             // this.$refs.inputVendedor.$refs.input.disabled = true
            }
        })
        .catch((error) => {
            this.correcto = false
            console.log(error)
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },
    // Imprime el reporte completo (comportamiento original, tal como estaba)
    imprimir () {
      window.print();
    },
    // Imprime solo el resumen tipo ticket, pensado para ticketeras (80mm/58mm)
    imprimirTicket () {
      this.imprimiendoTicket = true
      this.$nextTick(() => {
        window.print()
      })
    },
    finalizarImpresion () {
      this.imprimiendoTicket = false
    },
    textoTurno (idTurno) {
      const turnos = { 1: 'MAÑANA', 2: 'TARDE', 3: 'NOCHE' }
      return turnos[idTurno] || ''
    },
    loadData () {
      this.getVentas()
      this.getIngresos()
      this.getSalidas()
    },
    setVendedor (obj) {
        this.vendedor = obj
        this.loadData()
    },
    cerrarCaja () {
      if(this.correcto==false){
        this.$toasted.show('No se cargaron algunos datos porfavor actualice la pagina.')
        return
      }
      if (!this.turno) {
        this.$toasted.show('Seleccione un Turno')
        return
      }
      let r = confirm('¿Está seguro de cerrar la caja?')
      if (r) {
        this.axios.post(this.API + 'cierrecaja', {
          idTurno:      this.turno,
          //user: sessionStorage.getItem("User"),
          user:         this.vendedor.Usuario,
          ingresos:     parseFloat(this.granTotal + this.totalSalidas).toFixed(2),
          salidas:      this.totalSalidas,
          total:        this.granTotal,
          totalVentas:  this.totalVentas
        }).then(res => {
          this.loadData()
          this.$toasted.show('¡Caja cerrada!')
          // console.log(res)
        })
      }
    },
    focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
    },
    estadoVenta (venta) {
        return venta.Anulado ? 'ANULADO' : (venta.EsCredito ? 'CREDITO' : 'APLICADO')
    },
    loadDetalles () {
      this.axios.get(this.API + 'ventas/detalle', {
          params: {
            idDocVentas: this.ventas.map((item) => item.idDocVenta),
          }
        }).then((response) => {
            this.itemsDetalle = response.data
            this.ventas = this.ventas.map((item) => {
              item.productos = this.getDetalles(item.idDocVenta)
              return item
            })
        })
        .catch((error) => {
          console.log(error)
        })
    },
    getVentas () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario
      }
      // cierre de caja enviar el vendedor
      this.axios.get(this.API + 'cierrecaja/ventas', {
        params: params
      })
      .then(res => {
        this.ventas = res.data
        this.loadDetalles()
		this.getAdelantos()
      }).catch(err => {
        console.log(err)
      })
    },
    getIngresos () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario
      }
      this.axios.get(this.API + 'cierrecaja/ingresos',{
        params: params
      })
      .then(res => {
        this.ingresos = res.data.filter((ing)=> ing.EsTarjeta==0)
        this.ingresosTarjeta = res.data.filter((ing)=> ing.EsTarjeta==1)
      }).catch(err => {
        console.log(err)
      }) 
    },
    getSalidas () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario
      }
      this.axios.get(this.API + 'cierrecaja/salidas', {
        params: params
      })
      .then(res => {
        this.salidas = res.data.filter((sal)=> sal.EsTarjeta==0)
        this.salidasTarjeta = res.data.filter((sal)=> sal.EsTarjeta==1)
      }).catch(err => {
        console.log(err)
      }) 
    },
    getAdelantos() {
        let params = {
          idDocVentas: this.ventas.map(v => v.idDocVenta)
        }
        this.axios.get(this.API + 'cierrecaja/ingresos/adelantos', {
          params: params
        })
        .then(res => {
          this.adelantos = res.data
        }).catch(err => {
          console.log(err)
        })
    },
    getCierre(id) {
      this.axios.get(this.API + 'cierrecaja', {
        params: {
          idCierre: id
        }
      }).then(res =>{
        this.cierre = res.data[0]
      })
    }
  },
  components: {
    CierresCaja
  }
}
</script>
<style scoped>
/* =========================================================
   CierreCaja.vue - mismo lenguaje visual JoBi que el resto
   de los módulos. Se conservan íntegras las reglas @media
   print originales (con leves ajustes) para que el reporte
   se siga imprimiendo limpio y en blanco y negro.
   ========================================================= */
.pos-jobi {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
    --jobi-bg: #EEF3F8;
    background: var(--jobi-bg);
    min-height: 100vh;
    padding-bottom: 10px !important;
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    width: 100%;
}

.pos-container {
    padding-top: 10px !important;
    max-width: 100% !important;
}

.pos-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(11,42,74,.10);
    padding: 14px 18px 20px !important;
}

/* -------- Barra de acciones -------- */
.pos-panel-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    border-radius: 14px;
    padding: 14px 16px;
    margin-bottom: 18px;
    color: #fff;
}
.pos-panel-header label {
    color: rgba(255,255,255,.72);
    font-size: .70rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pos-label-icon { font-size: .82em; opacity: .85; }

.pos-field >>> input,
.pos-field >>> select,
.pos-field >>> .form-control {
    border-radius: 10px !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 700;
    height: 42px;
    border: none !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}

.pos-header-actions {
    gap: 8px;
    flex-wrap: wrap;
}
.pos-btn-cerrar,
.pos-btn-imprimir,
.pos-btn-imprimir-ticket {
    border-radius: 999px !important;
    border: none !important;
    padding: 0 18px !important;
    height: 42px;
    font-weight: 800 !important;
    text-transform: uppercase;
    font-size: .76rem;
    letter-spacing: .03em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 6px 14px rgba(0,0,0,.18);
    color: #fff !important;
}
.pos-btn-cerrar { background: var(--jobi-green) !important; }
.pos-btn-imprimir { background: var(--jobi-blue) !important; }
.pos-btn-imprimir-ticket { background: var(--jobi-purple) !important; }
.pos-btn-cerrar:hover,
.pos-btn-imprimir:hover,
.pos-btn-imprimir-ticket:hover { color: #fff !important; transform: translateY(-1px); }

.pos-btn-cierres {
    display: inline-flex;
}
.pos-btn-cierres >>> .btn {
    border-radius: 999px !important;
    border: none !important;
    height: 42px;
    padding: 0 18px !important;
    font-weight: 800 !important;
    background: var(--jobi-orange) !important;
    color: #fff !important;
    box-shadow: 0 6px 14px rgba(0,0,0,.18);
}

/* -------- Título -------- */
.pos-title-block {
    text-align: center;
    margin-bottom: 18px;
}
.pos-title {
    color: var(--jobi-navy);
    font-weight: 900;
    margin-bottom: 8px;
}
.pos-title-meta {
    display: flex;
    justify-content: center;
    gap: 10px;
    flex-wrap: wrap;
}
.pos-meta-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eef3f8;
    color: var(--jobi-navy);
    font-weight: 700;
    font-size: .78rem;
    padding: 5px 12px;
    border-radius: 999px;
    border: 1px solid #dbe6f1;
}

/* -------- Tarjetas resumen -------- */
.pos-summary-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 10px;
    margin-bottom: 22px;
}
.pos-stat-full {
    grid-column: 1 / -1;
}
.pos-stat-card {
    display: flex;
    align-items: center;
    gap: 10px;
    border-radius: 12px;
    padding: 12px 14px;
    color: #fff;
    box-shadow: 0 6px 14px rgba(11,42,74,.12);
}
.pos-stat-icon {
    flex: 0 0 auto;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: rgba(255,255,255,.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
}
.pos-stat-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    font-weight: 800;
    opacity: .85;
}
.pos-stat-value {
    font-size: 1.05rem;
    font-weight: 900;
}
.pos-stat-navy .pos-stat-value { font-size: 1.6rem; }
.pos-stat-navy { background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%); }
.pos-stat-teal   { background: var(--jobi-teal); }
.pos-stat-red    { background: var(--jobi-red); }
.pos-stat-orange { background: var(--jobi-orange); }
.pos-stat-green  { background: var(--jobi-green); }
.pos-stat-purple { background: var(--jobi-purple); }
.pos-stat-blue   { background: var(--jobi-blue); }

@media (max-width: 991px) {
    .pos-summary-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (max-width: 576px) {
    .pos-summary-grid { grid-template-columns: repeat(2, 1fr); }
}

/* -------- Secciones de tablas -------- */
.pos-section {
    margin-bottom: 22px;
}
.pos-section-title {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    font-weight: 800;
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 7px 14px;
    border-radius: 999px;
    margin-bottom: 10px;
}
.pos-section-title-green  { background: var(--jobi-green); }
.pos-section-title-purple { background: var(--jobi-purple); }
.pos-section-title-blue   { background: var(--jobi-blue); }
.pos-section-title-orange { background: var(--jobi-orange); }
.pos-section-title-teal   { background: var(--jobi-teal); }
.pos-section-title-red    { background: var(--jobi-red); }

.pos-table-card {
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
    border: 1px solid #e7edf5;
    overflow-x: auto;
}
.pos-table-card >>> table { margin-bottom: 0; }
.pos-table-card >>> thead th {
    background: var(--jobi-navy) !important;
    color: #fff !important;
    text-transform: uppercase;
    font-size: .68rem;
    letter-spacing: .03em;
    font-weight: 800;
    border: none !important;
    padding: 8px;
}
.pos-table-card >>> thead th:first-child { border-top-left-radius: 10px; }
.pos-table-card >>> thead th:last-child { border-top-right-radius: 10px; }
.pos-table-card >>> tbody td {
    vertical-align: middle;
    padding: 7px 8px;
    border-color: #eef2f7;
}

.credito {
    background: #fbf7f0;
    border-radius: 14px;
    padding: 12px;
}

/* -------- GRAN TOTAL -------- */
.pos-final-total {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, var(--jobi-green) 0%, #0f8a48 100%);
    color: #fff;
    font-weight: 900;
    font-size: 1.15rem;
    text-align: center;
    padding: 16px;
    border-radius: 14px;
    box-shadow: 0 10px 22px rgba(23,165,84,.30);
    margin-top: 6px;
}
.pos-final-total i { font-size: 1.3rem; }

/* -------- Reglas originales de tabla, sin cambios de fondo -------- */
th, td {
  font-size: .9em;
  vertical-align: middle;
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
.tabla-detalle th {
  background-color: #ffffff;
  font-weight: normal;
}
.tabla-detalle th, .tabla-detalle td {
  font-size: .9em;
}
.detalle-producto {
  width: 270px;
}
.adelantos {
  background-color: #ffe8c5;
}

/* -------- Ticket resumen: oculto en pantalla, se muestra solo
   al imprimir en modo ticket (ver reglas @media print abajo) -------- */
.pos-ticket {
  display: none;
}

/* -------- Impresión: se aplana todo el diseño para ahorrar
   tinta y mantener el reporte legible en blanco y negro -------- */
@media print {
  .no-print { display: none; }
  .si-print { display: block; }

  .pos-jobi { background: #fff !important; }
  .pos-card { box-shadow: none !important; border-radius: 0 !important; padding: 0 !important; }

  .pos-stat-card,
  .pos-stat-navy,
  .pos-final-total,
  .pos-section-title,
  .pos-table-card >>> thead th {
    background: #fff !important;
    color: #000 !important;
    box-shadow: none !important;
    border: 1px solid #000 !important;
  }
  .pos-stat-icon { background: transparent !important; }
  .pos-table-card { box-shadow: none !important; border: 1px solid #ccc !important; }
  .credito { background: #fff !important; }

  /* ---- Modo ticket (ticketera 80mm/58mm) ----
     Oculta todo el reporte detallado y muestra solo el resumen. */
  .modo-ticket .pos-title-block,
  .modo-ticket .pos-summary-grid,
  .modo-ticket .pos-section,
  .modo-ticket .row,
  .modo-ticket .pos-final-total {
    display: none !important;
  }

  .modo-ticket .pos-ticket {
    display: block !important;
    width: 76mm;
    max-width: 76mm;
    margin: 0 auto;
    font-family: 'Courier New', Courier, monospace;
    font-size: 15px;
    color: #000;
    line-height: 1.35;
    margin-left: 50px;
  }

  .pos-ticket .ticket-empresa {
    text-align: center;
    font-weight: 700;
    font-size: 15px;
    text-transform: uppercase;
    margin-bottom: 4px;
  }

  .pos-ticket .ticket-linea {
    border-top: 1px dashed #000;
    margin: 5px 0;
  }

  .pos-ticket .ticket-seccion-titulo {
    font-weight: 700;
    text-transform: uppercase;
    font-size: 14px;
    margin: 4px 0 2px;
  }

  .pos-ticket .ticket-dato {
    display: flex;
    justify-content: space-between;
    gap: 6px;
  }

  .pos-ticket .ticket-bold {
    font-weight: 700;
  }

  .pos-ticket .ticket-total {
    display: flex;
    justify-content: space-between;
    font-weight: 900;
    font-size: 13px;
    margin: 4px 0;
  }

  .pos-ticket .ticket-footer {
    text-align: center;
    font-size: 13px;
    margin-top: 10px;
  }

  .pos-ticket .ticket-firma {
    margin-top: 18px;
  }

  /* Ajusta el ancho de la hoja/rollo para ticketeras térmicas de 80mm.
     Si tu ticketera es de 58mm, cambia 80mm por 58mm aquí y arriba
     en ".modo-ticket .pos-ticket { width: ... }" (usa ~48mm). */
  @page {
    size: 80mm auto;
    margin: 2mm;
  }
}
</style>