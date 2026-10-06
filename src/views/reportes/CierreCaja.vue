<template>
  <b-container fluid class="px-4 py-4 cash-close-modern" :class="{ 'ticket-mode': modoImpresionTicket || modoImpresionProductos === 'ticket' || modoImpresionYape === 'ticket' || modoImpresionTransferencia === 'ticket' }">
    <b-row class="no-print">  
      <b-col cols="12" lg="9" class="no-print cash-detail-panel">
        
        <b-card no-body>
          <div class="cash-detail-toolbar">
            <b-button
              type="button"
              class="btn-toggle-all-details"
              :class="{ 'is-open': todosDetallesVisibles }"
              :disabled="!ventas.length"
              @click="alternarTodosDetalles"
            >
              <i :class="todosDetallesVisibles ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
              {{ todosDetallesVisibles ? 'Ocultar todos los detalles' : 'Mostrar todos los detalles' }}
            </b-button>
          </div>
          <b-tabs content-class="mt-3 mx-3" card fill pills>
            <b-tab title="Resumen" active class="clsVentas">
              <div class="clsVentas">
              <h5 class="payment-title payment-cash">Ventas Efectivo</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Recibo</th><th>Fecha</th>
                      <th>Cliente</th>
                      <th>Estado</th><th style="width:90px">Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  
                  <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>
                    
                        <table class="tabla-detalle inline-sale-detail" v-if="esDetalleVisible(item)">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Modelo</th>
                            <th>Marca</th>
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
                            <td>{{ prod.Descripcion }}</td>
                            <td>{{ prod.ProductoModelo }}</td>
                            <td>{{ prod.ProductoMarca }}</td>
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
                      <div class="sale-client-line">
                        <b-button size="sm" class="btn-sale-detail" :class="{ 'is-open': esDetalleVisible(item) }" :title="esDetalleVisible(item) ? 'Ocultar consumo' : 'Ver consumo'" @click="alternarDetalleVenta(item)">
                          <i :class="esDetalleVisible(item) ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                        </b-button>
                        <span><b>Cliente:</b> {{ item.Cliente }}</span>
                      </div>

                    </td>
                    
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

              <h5 class="payment-title payment-card">Ventas Tarjeta</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>Recibo</th><th>Fecha</th>
              <th>Cliente</th>
              <th>Detalle</th>
<th>Estado</th>
<th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>
              
                  <table class="tabla-detalle inline-sale-detail" v-if="esDetalleVisible(item)">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
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
                <div class="sale-client-line">
                  <b-button size="sm" class="btn-sale-detail" :class="{ 'is-open': esDetalleVisible(item) }" :title="esDetalleVisible(item) ? 'Ocultar consumo' : 'Ver consumo'" @click="alternarDetalleVenta(item)">
                    <i :class="esDetalleVisible(item) ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                  </b-button>
                  <span><b>Cliente:</b> {{ item.Cliente }}</span>
                </div>

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
        <h5 class="payment-title payment-wallet">Ventas Yape/Plin</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
                <th>Recibo</th><th>Fecha</th>
                <th>Cliente</th>
                <th>Detalle</th>
<th>Estado</th>
<th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle inline-sale-detail" v-if="esDetalleVisible(item)">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
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
                <div class="sale-client-line">
                  <b-button size="sm" class="btn-sale-detail" :class="{ 'is-open': esDetalleVisible(item) }" :title="esDetalleVisible(item) ? 'Ocultar consumo' : 'Ver consumo'" @click="alternarDetalleVenta(item)">
                    <i :class="esDetalleVisible(item) ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                  </b-button>
                  <span><b>Cliente:</b> {{ item.Cliente }}</span>
                </div>
                    
              </td>
              <td>{{ item.MastercardDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Mastercard | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Yape/Plin</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <h5 class="payment-title payment-transfer">Ventas Transferencia</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
                <th>Recibo</th><th>Fecha</th>
                <th>Cliente</th>
                <th>Detalle</th>
                <th>Estado</th>
                <th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle inline-sale-detail" v-if="esDetalleVisible(item)">
                  <thead>
                    <tr>
                     <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
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
                <div class="sale-client-line">
                  <b-button size="sm" class="btn-sale-detail" :class="{ 'is-open': esDetalleVisible(item) }" :title="esDetalleVisible(item) ? 'Ocultar consumo' : 'Ver consumo'" @click="alternarDetalleVenta(item)">
                    <i :class="esDetalleVisible(item) ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                  </b-button>
                  <span><b>Cliente:</b> {{ item.Cliente }}</span>
                </div>
                    
              </td>
              <td>{{ item.TransferenciaDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Transferencia | redondear }}</td>
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
        <h5 class="movement-title movement-credit">Ventas a Crédito</h5><br>
        <table class="table b-table table-sm movement-table credit-table">
          <thead>
            <tr class="thDetalle">
              <th>Recibo</th><th>Fecha</th>
              <th>Cliente</th>
              <th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>
                
                  <table class="tabla-detalle inline-sale-detail" v-if="esDetalleVisible(item)">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                       <td>{{ prod.Descripcion }}</td>
                         <td>{{ prod.ProductoModelo }}</td>
                        <td>{{ prod.ProductoMarca }}</td>
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
                <div class="sale-client-line">
                  <b-button size="sm" class="btn-sale-detail" :class="{ 'is-open': esDetalleVisible(item) }" :title="esDetalleVisible(item) ? 'Ocultar consumo' : 'Ver consumo'" @click="alternarDetalleVenta(item)">
                    <i :class="esDetalleVisible(item) ? 'fa fa-eye-slash' : 'fa fa-eye'"></i>
                  </b-button>
                  <span><b>Cliente:</b> {{ item.Cliente }}</span>
                </div>


              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Total | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td class="total"><b>Total Crédito</b></td>
              <td class="total"><b>S/. {{ totalVentasCredito | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <b-row>
        <b-col v-if="totalIngresos">
          <h5 class="movement-title movement-income">Ingresos</h5><br>
          <table class="table b-table table-sm movement-table income-table">
            <thead>
              <tr class="thDetalle">
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
          <h5 class="movement-title movement-outflow">Salidas</h5><br>
          <table class="table b-table table-sm movement-table outflow-table">
            <thead>
              <tr class="thDetalle">
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
              <tr class="thDetalle">
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
                <td class="total"><b>Total Ingresos Tarjetas</b></td>
                <td class="total"><b>S/. {{ totalIngresosTarjeta | redondear }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
      </b-row>
      <b-row>
        <b-col v-if="totalSalidasTarjeta">
          <h5>Salidas de Cuentas Bancarias </h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr class="thDetalle">
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
    </div> </b-tab>
            <b-tab title="Productos vendidos" >
              <div class="products-title-row">
                <h5>Resumen Por Producto</h5>
                <b-dropdown size="sm" variant="primary" right class="no-print" text="Imprimir">
                  <b-dropdown-item @click="imprimirProductos('pdf')"><i class="fa fa-file-pdf-o mr-1"></i> PDF</b-dropdown-item>
                  <b-dropdown-item @click="imprimirProductos('ticket')"><i class="fa fa-print mr-1"></i> Ticket</b-dropdown-item>
                </b-dropdown>
              </div><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>ID</th>
              <th>Producto</th>
              <th>Modelo</th>
              <th>Marca</th>
              <th>Cantidad Vendida</th>
              <th>Monto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(produtos,index) in reportePorProveedor" :key="index">
               <td> {{ produtos.IdProducto }} </td>
               <td> {{ produtos.Producto }} </td>
               <td>{{ produtos.ProductoModelo }}</td>
               <td>{{ produtos.ProductoMarca }}</td>
               <td> {{ produtos.total }} </td>
               <td> {{ produtos.Precio }} </td>
              
            </tr>
            
          </tbody>
          <tfoot>
            <tr>
              <td colspan="8"></td>
              <td class="total"><b>Total Efectivos</b></td>
              <td class="total"><b>S/. {{
                parseFloat( reportePorProveedor.reduce((total,prod)=>{
                      return total + parseFloat(prod.Precio)
                  },0)).toFixed(2)
              }}</b></td>
            </tr>
          </tfoot>
        </table>
            </b-tab>
            <b-tab title="Conciliar Yape/Plin">
              <div class="scrollVertical">
                <div class="products-title-row">
                  <h5>Detalle de Ventas Pagadas con Yape/Plin</h5>
                  <b-dropdown size="sm" variant="primary" right class="no-print" text="Imprimir">
                    <b-dropdown-item @click="imprimirYape('pdf')"><i class="fa fa-file-pdf-o mr-1"></i> PDF</b-dropdown-item>
                    <b-dropdown-item @click="imprimirYape('ticket')"><i class="fa fa-print mr-1"></i> Ticket</b-dropdown-item>
                  </b-dropdown>
                </div>
                <table class="table b-table table-sm">
                  <thead>
                    <tr class="thDetalle">
                      <th>Recibo</th>
                      <th>Operación</th>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Vendedor</th>
                      <th>Cliente</th>
                      <th>Estado</th>
                      <th>Monto Pagado (Yape/Plin)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{ anulado: item.Anulado }">
                      <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                      <td>{{ item.MastercardDesc || '-' }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ formatoHora(item.FechaDoc) }}</td>
                      <td>{{ item.UsuarioReg }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ estadoVenta(item) }}</td>
                      <td>S/. {{ item.Mastercard | redondear }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="6"></td>
                      <td class="total"><b>Total Yape/Plin</b></td>
                      <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </b-tab>

            <b-tab title="Transferencias">
              <div class="scrollVertical">
                <div class="products-title-row">
                  <h5>Detalle de Ventas Pagadas con Transferencia</h5>
                  <b-dropdown size="sm" variant="primary" right class="no-print" text="Imprimir">
                    <b-dropdown-item @click="imprimirTransferencia('pdf')"><i class="fa fa-file-pdf-o mr-1"></i> PDF</b-dropdown-item>
                    <b-dropdown-item @click="imprimirTransferencia('ticket')"><i class="fa fa-print mr-1"></i> Ticket</b-dropdown-item>
                  </b-dropdown>
                </div>
                <table class="table b-table table-sm">
                  <thead>
                    <tr class="thDetalle">
                      <th>Recibo</th>
                      <th>Operación</th>
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
                      <td>{{ item.TransferenciaDesc || '-' }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ formatoHora(item.FechaDoc) }}</td>
                      <td>{{ item.UsuarioReg }}</td>
                      <td>{{ item.Cliente }}</td>
                      <td>{{ estadoVenta(item) }}</td>
                      <td>S/. {{ item.Transferencia | redondear }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="6"></td>
                      <td class="total"><b>Total Transferencia</b></td>
                      <td class="total"><b>S/. {{ totalVentasTransferencia | redondear }}</b></td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </b-tab>
          </b-tabs>
        </b-card>
      </b-col>
      <b-col cols="12" lg="3" class="card elevation-2 cash-summary-panel">
        <div class="alinear">
          <center>
          </center>
          <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
          <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
          <div v-if="cierre.UsuarioReg" style="font-size: 1.5em; padding: 5%;">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
          <div v-if="cierre.Descripcion" class="cash-close-description-readonly">
            <strong>Descripción del cierre</strong>
            <span>{{ cierre.Descripcion }}</span>
          </div>
        </div>
        <h2 class="text-center">Mi Cierre de Caja</h2> 
        <b-row  align-h="end" class=" no-print">
        <b-col cols="12">
            <div v-if="esAdministrador && !this.$route.params.id" class="admin-cash-control">
              <b-dropdown
                id="admin-cash-user"
                class="admin-user-dropdown"
                block
                menu-class="admin-user-menu"
                variant="light"
                no-caret
              >
                <template slot="button-content">
                  <span class="admin-user-current">
                    <i class="fa fa-user-circle"></i>
                    <span>
                      <strong>{{ nombreCajaSeleccionada }}</strong>
                      <small>{{ usuarioCajaSeleccionado || 'Seleccione un vendedor' }}</small>
                    </span>
                    <i class="fa fa-chevron-down admin-user-arrow"></i>
                  </span>
                </template>
                <b-dropdown-item
                  v-for="usuario in usuariosCaja"
                  :key="usuario.Usuario"
                  :active="usuario.Usuario === usuarioCajaSeleccionado"
                  @click="usuarioCajaSeleccionado = usuario.Usuario; seleccionarCajaUsuario(usuario.Usuario)"
                >
                  <span class="admin-user-option">
                    <span class="admin-user-avatar">{{ (usuario.NombreUsuario || usuario.Usuario).charAt(0).toUpperCase() }}</span>
                    <span class="admin-user-option-text">
                      <strong>{{ usuario.NombreUsuario || usuario.Usuario }}</strong>
                      <small>@{{ usuario.Usuario }}</small>
                    </span>
                    <i v-if="usuario.Usuario === usuarioCajaSeleccionado" class="fa fa-check-circle"></i>
                  </span>
                </b-dropdown-item>
              </b-dropdown>
            </div>
            <b-form-group
              v-if="!this.$route.params.id"
              label="Descripción del cierre"
              label-for="descripcion-cierre"
              class="cash-close-description-field"
            >
              <b-form-textarea
                id="descripcion-cierre"
                v-model="descripcionCierre"
                rows="3"
                max-rows="5"
                maxlength="500"
                placeholder="Ejemplo: cierre sin novedades, diferencia verificada o detalle del turno"
              />
              <small>{{ descripcionCierre.length }}/500 caracteres</small>
            </b-form-group>
           
            <b-button-group>
              <b-button variant="danger" @click="cerrarCaja" v-if="!this.$route.params.id"><i class="fa fa-unlock mx-1" aria-hidden="true"></i>
                {{ esAdministrador ? 'Cerrar caja seleccionada' : 'Cerrar Caja' }}</b-button>
            </b-button-group>
            <b-button-group>
              <cierres-caja
                :es-administrador="esAdministrador"
                :usuario-actual="usuarioSesion"
              ></cierres-caja>
            </b-button-group>
            <b-button-group>
              <b-button variant="primary" @click="imprimir"><i class="fa fa-print mx-1" aria-hidden="true"></i> Imprimir</b-button>
            </b-button-group>
            <b-button-group>
              <b-button class="btn-print-ticket" @click="imprimirResumen"><i class="fa fa-file-text-o mx-1" aria-hidden="true"></i> Imprimir resumen</b-button>
            </b-button-group>
          </b-col>
        <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
          Turno
          
          <b-form-select v-model="turno" :options="turnos" />
          
        </b-col>
            <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
              <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
              <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
              <!-- <div v-if="cierre.UsuarioReg" style="font-size: 2em;">Empleado: <b>{{ cierre.UsuarioReg }}</b></div> -->
              
            </b-col>
            <b-col cols="12">
        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr class="summary-grand">
              <td>Gran Total S/.</td>
              <td>{{ granTotal | redondear }}</td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr class="summary-income">
              <td>Ingresos S/.</td>
              <td>{{ totalIngresos | redondear }} </td>
            </tr>
            <tr class="summary-outflow">
              <td>Salida S/.</td>
              <td>{{ totalSalidas | redondear }} </td>
            </tr>
            <tr class="summary-credit">
              <td>Crédito S/.</td>
              <td>{{ totalVentasCredito | redondear }} </td>
            </tr>
          
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr class="summary-cash">
              <td>Efectivo S/.</td>
              <td>{{ totalVentasEfectivo | redondear }} </td>
            </tr>
            <tr class="summary-wallet">
              <td>Yape/Plin S/.</td>
              <td>{{ totalVentasMastercard | redondear }} </td>
            </tr>
            <tr class="summary-card">
              <td>Tarjeta S/.</td>
              <td>{{ totalVentasVisa | redondear }} </td>
            </tr>
            <tr class="summary-transfer">
              <td>Transferencia S/.</td>
              <td>{{ totalVentasTransferencia | redondear }} </td>
            </tr>
            
            <tr>
              <td colspan="2"></td>
            </tr>

            <!-- <tr>
              <td>Vales de Crédito S/.</td>
              <td>{{ totalVentasValeCredito | redondear }} </td>
            </tr> -->
            <!-- <tr>
              <td>Canjes S/.</td>
              <td>{{ totalVentasCanje | redondear }} </td>
            </tr> -->
          </tbody>
        </table>
      </b-col>
      </b-row>
      </b-col>
      
      <b-col cols="12">

        <b-alert variant="success" class="text-center" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
      </b-col>
    
    </b-row>

    <section v-if="modoImpresionTicket" class="si-print ticket-print-summary">
      <header>
        <span v-if="logoEmpresaCierre" class="print-company-logo-frame print-company-logo-ticket">
          <img :src="logoEmpresaCierre" :alt="nombreEmpresaCierre">
        </span>
        <strong class="print-company-name">{{ nombreEmpresaCierre }}</strong>
        <h1>CIERRE DE CAJA</h1>
        <p>Fecha: {{ cierre.FechaCierre || fechaActualTicket }}</p>
        <p>Vendedor(a): {{ cierre.UsuarioReg || nombreCajaSeleccionada }}</p>
        <p v-if="cierre.Descripcion">Descripción: {{ cierre.Descripcion }}</p>
        <p>Ventas realizadas: <b>{{ ventasActivas.length }}</b></p>
      </header>
      <div class="ticket-grand"><span>GRAN TOTAL</span><strong>S/. {{ totalGeneralVentas | moneda }}</strong></div>
      <section v-for="resumen in resumenDocumentosTicket" :key="resumen.clave" class="ticket-doc-summary">
        <h2>RESUMEN DE {{ resumen.titulo }}</h2>
        <div class="ticket-line"><span>{{ resumen.etiqueta }} EMITIDAS:</span><b>{{ resumen.emitidas }}</b></div>
        <div class="ticket-line"><span>NRO INICIAL:</span><b>{{ resumen.inicial }}</b></div>
        <div class="ticket-line"><span>NRO FINAL:</span><b>{{ resumen.final }}</b></div>
        <div class="ticket-line"><span>VENTAS ANULADAS:</span><b>{{ resumen.anuladas }}</b></div>
        <div class="ticket-line"><span>MONTO ANULADO:</span><b>S/. {{ resumen.montoAnulado | redondear }}</b></div>
        <div class="ticket-line"><span>VALOR VENTA:</span><b>S/. {{ resumen.valorVenta | redondear }}</b></div>
        <div class="ticket-line ticket-doc-total"><span>TOTAL {{ resumen.abreviatura }}:</span><b>S/. {{ resumen.total | redondear }}</b></div>
      </section>
      <h2>RESUMEN POR MÉTODO</h2>
      <div class="ticket-line"><span>Efectivo</span><b>S/. {{ totalVentasEfectivo | redondear }}</b></div>
      <div class="ticket-line"><span>Yape / Plin</span><b>S/. {{ totalVentasMastercard | redondear }}</b></div>
      <div class="ticket-line"><span>Tarjeta</span><b>S/. {{ totalVentasVisa | redondear }}</b></div>
      <div class="ticket-line"><span>Transferencia</span><b>S/. {{ totalVentasTransferencia | redondear }}</b></div>
      <div class="ticket-line"><span>Crédito</span><b>S/. {{ totalVentasCredito | redondear }}</b></div>
      <h2>MOVIMIENTOS DE CAJA</h2>
      <div class="ticket-line"><span>Total ingresos</span><b>S/. {{ totalIngresos | redondear }}</b></div>
      <div v-for="item in ingresos" :key="'ti-'+item.IdCajaBanco" class="ticket-detail"><span>{{ item.Concepto }}</span><b>S/. {{ item.Importe | redondear }}</b></div>
      <div class="ticket-line ticket-out"><span>Total salidas</span><b>S/. {{ totalSalidas | redondear }}</b></div>
      <div v-for="item in salidas" :key="'ts-'+item.IdCajaBanco" class="ticket-detail"><span>{{ item.Concepto }}</span><b>S/. {{ item.Importe | redondear }}</b></div>
      <div class="ticket-final"><span>TOTAL FINAL DE CAJA</span><strong>S/. {{ granTotal | moneda }}</strong></div>
      <footer>Resumen generado por el sistema</footer>
    </section>

    <section v-if="modoImpresionProductos" class="si-print products-print" :class="'products-print-' + modoImpresionProductos">
      <header class="print-report-brand">
        <span v-if="logoEmpresaCierre" class="print-company-logo-frame">
          <img :src="logoEmpresaCierre" :alt="nombreEmpresaCierre">
        </span>
        <strong class="print-company-name">{{ nombreEmpresaCierre }}</strong>
        <h1>PRODUCTOS VENDIDOS</h1>
        <p>{{ cierre.FechaCierre || fechaActualTicket }}</p>
      </header>
      <table v-if="modoImpresionProductos === 'pdf'" class="products-pdf-table">
        <thead><tr><th>ID</th><th>Producto</th><th>Modelo</th><th>Marca</th><th>Cantidad vendida</th><th>Monto</th></tr></thead>
        <tbody>
          <tr v-for="(producto, index) in reportePorProveedor" :key="'pdf-producto-'+index">
            <td>{{ producto.IdProducto }}</td><td>{{ producto.Producto }}</td><td>{{ producto.ProductoModelo }}</td>
            <td>{{ producto.ProductoMarca }}</td><td>{{ producto.total }}</td>
            <td>S/. {{ producto.Precio | redondear }}</td>
          </tr>
          <tr v-if="!reportePorProveedor.length"><td colspan="10" class="ticket-empty">Sin productos vendidos</td></tr>
        </tbody>
        <tfoot><tr><td colspan="8"></td><td>TOTAL DE VENTA</td><td>S/. {{ parseFloat(reportePorProveedor.reduce((total, producto) => total + parseFloat(producto.Precio || 0), 0)).toFixed(2) }}</td></tr></tfoot>
      </table>
      <table v-else class="ticket-products">
        <thead><tr><th>Producto</th><th>Marca</th><th>C. vendida</th><th>Monto</th></tr></thead>
        <tbody>
          <tr v-for="(producto, index) in reportePorProveedor" :key="'ticket-producto-'+index">
            <td>{{ producto.Producto }}</td><td>{{ producto.ProductoMarca }}</td>
            <td>{{ producto.total }}</td><td>S/. {{ producto.Precio | redondear }}</td>
          </tr>
          <tr v-if="!reportePorProveedor.length"><td colspan="4" class="ticket-empty">Sin productos vendidos</td></tr>
        </tbody>
        <tfoot><tr><td colspan="3">TOTAL DE VENTA</td><td>S/. {{ parseFloat(reportePorProveedor.reduce((total, producto) => total + parseFloat(producto.Precio || 0), 0)).toFixed(2) }}</td></tr></tfoot>
      </table>
    </section>

    <section v-if="modoImpresionYape" class="si-print yape-print" :class="'yape-print-' + modoImpresionYape">
      <header class="print-report-brand yape-print-header">
        <span v-if="logoEmpresaCierre" class="print-company-logo-frame">
          <img :src="logoEmpresaCierre" :alt="nombreEmpresaCierre">
        </span>
        <strong class="print-company-name">{{ nombreEmpresaCierre }}</strong>
        <h1>CONCILIACIÓN YAPE / PLIN</h1>
        <p>{{ cierre.FechaCierre || fechaActualTicket }}</p>
      </header>
      <div v-if="modoImpresionYape === 'ticket'" class="yape-ticket-list">
        <table class="yape-ticket-table">
          <thead><tr><th>Recibo</th><th>Operación</th><th>Monto</th></tr></thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="'yape-ticket-'+index" :class="{ anulado: item.Anulado }">
              <td>{{ item.Serie + '-' + item.Numero }}</td><td>{{ item.MastercardDesc || '-' }}</td><td>S/. {{ item.Mastercard | redondear }}</td>
            </tr>
            <tr v-if="!ventasMastercard.length"><td colspan="3" class="ticket-empty">Sin operaciones registradas</td></tr>
          </tbody>
        </table>
      </div>
      <table v-else class="yape-pdf-table">
        <thead><tr><th>Recibo</th><th>Operación</th><th>Fecha</th><th>Hora</th><th>Vendedor</th><th>Cliente</th><th>Estado</th><th>Monto</th></tr></thead>
        <tbody><tr v-for="(item, index) in ventasMastercard" :key="'yape-pdf-'+index" :class="{ anulado: item.Anulado }"><td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td><td>{{ item.MastercardDesc || '-' }}</td><td>{{ item.FechaDoc }}</td><td>{{ formatoHora(item.FechaDoc) }}</td><td>{{ item.UsuarioReg }}</td><td>{{ item.Cliente }}</td><td>{{ estadoVenta(item) }}</td><td>S/. {{ item.Mastercard | redondear }}</td></tr></tbody>
      </table>
      <div class="yape-print-total"><span>{{ modoImpresionYape === 'ticket' ? 'TOTAL' : 'TOTAL YAPE / PLIN' }}</span><strong>S/. {{ totalVentasMastercard | redondear }}</strong></div>
    </section>

    <section v-if="modoImpresionTransferencia" class="si-print yape-print" :class="'yape-print-' + modoImpresionTransferencia">
      <header class="print-report-brand yape-print-header">
        <span v-if="logoEmpresaCierre" class="print-company-logo-frame">
          <img :src="logoEmpresaCierre" :alt="nombreEmpresaCierre">
        </span>
        <strong class="print-company-name">{{ nombreEmpresaCierre }}</strong>
        <h1>CONCILIACIÓN DE TRANSFERENCIAS</h1>
        <p>{{ cierre.FechaCierre || fechaActualTicket }}</p>
      </header>
      <div v-if="modoImpresionTransferencia === 'ticket'" class="yape-ticket-list">
        <table class="yape-ticket-table">
          <thead><tr><th>Recibo</th><th>Operación</th><th>Monto</th></tr></thead>
          <tbody>
            <tr v-for="(item, index) in ventasTransferencia" :key="'transfer-ticket-'+index" :class="{ anulado: item.Anulado }">
              <td>{{ item.Serie + '-' + item.Numero }}</td><td>{{ item.TransferenciaDesc || '-' }}</td><td>S/. {{ item.Transferencia | redondear }}</td>
            </tr>
            <tr v-if="!ventasTransferencia.length"><td colspan="3" class="ticket-empty">Sin operaciones registradas</td></tr>
          </tbody>
        </table>
      </div>
      <table v-else class="yape-pdf-table">
        <thead><tr><th>Recibo</th><th>Operación</th><th>Fecha</th><th>Hora</th><th>Vendedor</th><th>Cliente</th><th>Estado</th><th>Monto</th></tr></thead>
        <tbody><tr v-for="(item, index) in ventasTransferencia" :key="'transfer-pdf-'+index" :class="{ anulado: item.Anulado }"><td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td><td>{{ item.TransferenciaDesc || '-' }}</td><td>{{ item.FechaDoc }}</td><td>{{ formatoHora(item.FechaDoc) }}</td><td>{{ item.UsuarioReg || '-' }}</td><td>{{ item.Cliente || '-' }}</td><td>{{ estadoVenta(item) }}</td><td>S/. {{ item.Transferencia | redondear }}</td></tr></tbody>
      </table>
      <div class="yape-print-total"><span>{{ modoImpresionTransferencia === 'ticket' ? 'TOTAL' : 'TOTAL TRANSFERENCIAS' }}</span><strong>S/. {{ totalVentasTransferencia | redondear }}</strong></div>
    </section>

    <section v-if="!modoImpresionTicket && !modoImpresionProductos && !modoImpresionYape && !modoImpresionTransferencia" class="si-print cierre-print-compacto">
      <header class="print-cierre-header">
        <div class="print-cierre-brand">
          <span v-if="logoEmpresaCierre" class="print-company-logo-frame">
            <img :src="logoEmpresaCierre" :alt="nombreEmpresaCierre">
          </span>
          <strong class="print-company-name">{{ nombreEmpresaCierre }}</strong>
          <h1>Reporte de cierre de caja</h1>
          <p>{{ cierre.UsuarioReg || nombreCajaSeleccionada }} · {{ cierre.FechaCierre || 'Caja actual' }}</p>
          <p v-if="cierre.Descripcion">Descripción: {{ cierre.Descripcion }}</p>
        </div>
        <div class="print-cierre-total">
          <small>TOTAL DE CAJA</small>
          <strong>S/. {{ totalGeneralVentas | moneda }}</strong>
        </div>
      </header>

      <div class="print-resumen-grid">
        <span>Efectivo <b>S/. {{ totalVentasEfectivo | redondear }}</b></span>
        <span>Yape/Plin <b>S/. {{ totalVentasMastercard | redondear }}</b></span>
        <span>Tarjeta <b>S/. {{ totalVentasVisa | redondear }}</b></span>
        <span>Transferencia <b>S/. {{ totalVentasTransferencia | redondear }}</b></span>
        <span>Crédito <b>S/. {{ totalVentasCredito | redondear }}</b></span>
        <span>Ingresos / Salidas <b>S/. {{ totalIngresos | redondear }} / {{ totalSalidas | redondear }}</b></span>
      </div>
      <div class="print-total-general">GRAN TOTAL DE VENTAS <strong>S/. {{ totalGeneralVentas | moneda }}</strong></div>

      <h2>Detalle de ventas</h2>
      <table class="print-tabla-ventas">
        <thead>
          <tr>
            <th>Fecha</th><th>Comprobante</th><th>Producto</th><th>Modelo</th>
            <th>Marca</th><th>Cant.</th><th>P. unit.</th><th>Desc.</th><th>Total</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="(grupo, grupoIndex) in gruposVentasParaImprimir">
            <tr class="print-grupo-titulo" :key="'grupo-'+grupoIndex"><td colspan="10">VENTAS {{ grupo.nombre }}</td></tr>
            <tr v-for="(fila, index) in grupo.filas" :key="'print-'+grupoIndex+'-'+index" :class="{ anulado: fila.anulado }">
              <td>{{ fila.fecha }}</td><td>{{ fila.comprobante }}</td><td>{{ fila.producto }}</td><td>{{ fila.modelo }}</td>
              <td>{{ fila.marca }}</td><td>{{ fila.cantidad }}</td>
              <td>{{ fila.precio | redondear }}</td><td>{{ fila.descuento | redondear }}</td><td>{{ fila.total | redondear }}</td>
            </tr>
            <tr class="print-grupo-total" :key="'total-'+grupoIndex"><td colspan="9">TOTAL {{ grupo.nombre }}</td><td>S/. {{ grupo.total | redondear }}</td></tr>
          </template>
          <tr v-if="!ventasParaImprimir.length"><td colspan="10" class="print-vacio">Sin ventas registradas</td></tr>
        </tbody>
        <tfoot><tr class="print-gran-total-final"><td colspan="9">GRAN TOTAL DE VENTAS</td><td>S/. {{ totalGeneralVentas | moneda }}</td></tr></tfoot>
      </table>

      <div class="print-movimientos-grid" v-if="ingresos.length || salidas.length">
        <table>
          <thead><tr><th colspan="4">Ingresos</th></tr><tr><th>ID</th><th>Fecha</th><th>Detalle</th><th>Monto</th></tr></thead>
          <tbody><tr v-for="item in ingresos" :key="'ing-'+item.IdCajaBanco"><td>{{ item.IdCajaBanco }}</td><td>{{ item.FechaDoc }}</td><td>{{ item.Concepto }}</td><td>S/. {{ item.Importe | redondear }}</td></tr></tbody>
          <tfoot><tr><td colspan="3">Total ingresos</td><td>S/. {{ totalIngresos | redondear }}</td></tr></tfoot>
        </table>
        <table>
          <thead><tr><th colspan="4">Salidas</th></tr><tr><th>ID</th><th>Fecha</th><th>Detalle</th><th>Monto</th></tr></thead>
          <tbody><tr v-for="item in salidas" :key="'sal-'+item.IdCajaBanco"><td>{{ item.IdCajaBanco }}</td><td>{{ item.FechaDoc }}</td><td>{{ item.Concepto }}</td><td>S/. {{ item.Importe | redondear }}</td></tr></tbody>
          <tfoot><tr><td colspan="3">Total salidas</td><td>S/. {{ totalSalidas | redondear }}</td></tr></tfoot>
        </table>
      </div>
    </section>

    <b-row class="si-print cierre-print-anterior">
       <h1> <center>Cierre Caja</center></h1>
       <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
        <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>  
       <h6 v-if="cierre.UsuarioReg" style="font-size: 1.5em; padding: 5%;">Empleado: <b>{{ cierre.UsuarioReg }}</b></h6>
        
        <table class="table table-sm" width="100%">
          <thead>
            <tr class="thDetalle">
              <th width="5%">ID</th>
              <th width="65%">Producto</th>
              <th width="15%">Cantidad Vendida</th>
              <th width="15%">Monto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(produtos,index) in reportePorProveedor" :key="index">
               <td> {{ produtos.IdProducto }} </td>
               <td> {{ produtos.Producto }} </td>
               <td> {{ produtos.total }} </td>
               <td> {{ produtos.Precio }} </td>
              
            </tr>
            
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Efectivo</b></td>
              <td class="total"><b>S/. {{ parseFloat(reportePorProveedor.reduce((total,prod)=>{
                      return total + parseFloat(prod.Precio)
                  },0) ).toFixed(2)}}</b></td>
            </tr>
          </tfoot>
        </table>

              <h5>Ventas Efectivo</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Recibo</th><th>Fecha</th>
                      <th v-if="$route.query.noDetalle">Cliente</th>
                      <th v-else>Productos</th>
                      <!-- <th>Estado</th> -->
                      <th style="width:90px">Pagado</th>
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
                            <th>Descripcion</th>
                            <th>Modelo</th>
                            <th>Marca</th>
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
                            <td class="detalle-producto">{{ prod.Producto }}</td>
                            <td>{{ prod.Descripcion }}</td>
                            <td>{{ prod.ProductoModelo }}</td>
                            <td>{{ prod.ProductoMarca }}</td>
                            <td>{{ prod.Cantidad }}</td>
                            <td>{{ prod.Precio }}</td>
                            <td>{{ prod.Descuento }}</td>
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
                    
                    <!-- <td>{{ estadoVenta(item) }}</td> -->
                    <td>S/. {{ item.Efectivo | redondear }}</td>
                  </tr>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="total"><b>Total Efectivo</b></td>
                    <td class="total"><b>S/. {{ totalVentasEfectivo | redondear }}</b></td>
                  </tr>
                </tfoot>
              </table>

              <h5>Ventas Tarjeta</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>Recibo</th><th>Fecha</th>
              <th v-if="$route.query.noDetalle">Cliente</th>
              <th v-else>Productos</th>
              <!-- <th>Detalle</th> -->
              <!-- <th>Estado</th> -->
              <th>Pagado</th>
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
                     
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                     
                      
                      <td class="detalle-producto">{{ prod.Producto }}</td>
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
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
              <!-- <td>{{ item.VisaDesc }}</td> -->
              <!-- <td>{{ estadoVenta(item) }}</td> -->
              <td>S/. {{ item.Visa | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Tarjeta</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <h5>Ventas Yape/Plin</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
                <th>Recibo</th><th>Fecha</th>
                <th v-if="$route.query.noDetalle">Cliente</th>
                <th v-else>Productos</th>
                <!-- <th>Detalle</th> -->
                <!-- <th>Estado</th> -->
                <th>Pagado</th>
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
                     
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                     
                      <td class="detalle-producto">{{ prod.Producto }}</td>
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
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
              <!-- <td>{{ item.MastercardDesc }}</td> -->
              <!-- <td>{{ estadoVenta(item) }}</td> -->
              <td>S/. {{ item.Mastercard | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Yape/Plin</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <h5>Ventas Transferencia</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
                <th>Recibo</th><th>Fecha</th>
                <th v-if="$route.query.noDetalle">Cliente</th>
                <th v-else>Productos</th>
                <!-- <th>Detalle</th> -->
<!-- <th>Estado</th> -->
                <th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                   
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                     
                      <td class="detalle-producto">{{ prod.Producto }}</td>
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
                      <td>{{ prod.Cantidad }}</td>
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
              <!-- <td>{{ item.TransferenciaDesc }}</td> -->
              <!-- <td>{{ estadoVenta(item) }}</td> -->
              <td>S/. {{ item.Transferencia | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Transferencia</b></td>
              <td class="total"><b>S/. {{ totalVentasTransferencia | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <h5>Ventas a Credito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>Recibo</th><th>Fecha</th>
              <th v-if="$route.query.noDetalle">Cliente</th>
              <th v-else>Productos</th>
              <!-- <th>Estado</th> -->
              <th>Pagado</th>
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
                     
                      <th>Producto</th>
                      <th>Descripcion</th>
                      <th>Modelo</th>
                      <th>Marca</th>
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
                     
                      <td class="detalle-producto">{{ prod.Producto }}</td>
                      <td>{{ prod.Descripcion }}</td>
                      <td>{{ prod.ProductoModelo }}</td>
                      <td>{{ prod.ProductoMarca }}</td>
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
              <!-- <td>{{ estadoVenta(item) }}</td> -->
              <td>S/. {{ item.Total | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Crédito</b></td>
              <td class="total"><b>S/. {{ totalVentasCredito | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <b-row>
        <b-col v-if="totalIngresos">
          <h5>Ingresos</h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr class="thDetalle">
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
              <tr class="thDetalle">
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
              <tr class="thDetalle">
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
                <td class="total"><b>Total Ingresos Tarjetas</b></td>
                <td class="total"><b>S/. {{ totalIngresosTarjeta | redondear }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
      </b-row>
      <b-row>
        <b-col v-if="totalSalidasTarjeta">
          <h5>Salidas de Cuentas Bancarias </h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr class="thDetalle">
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
           
    </b-row>
  </b-container>

</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";

export default {
  mounted() {
    this.cargarEmpresaImpresion();
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id);
      this.loadData();
    } else {
      this.getVendedor();
    }
  },
  data() {
    return {
      API: config.API,
      ventas: [],
      ingresos: [],
      salidas: [],
      ingresosTarjeta: [],
      salidasTarjeta: [],
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
      esAdministrador: false,
      usuarioSesion: sessionStorage.getItem("User") || "",
      usuariosCaja: [],
      usuarioCajaSeleccionado: null,
      reportePorProveedor: [],
      detallesVentaAbiertos: [],
      descripcionCierre: "",
      modoImpresionTicket: false,
      modoImpresionProductos: null,
      modoImpresionYape: null,
      modoImpresionTransferencia: null,
      empresaImpresion: {
        RAZONSOCIAL: "IMPORT COMPANY EDEN E.I.R.L.",
        nombre_comercial: "EDEN COMPANY",
        logo_principal: "/resources/uploads/empresa/logo_principal-20261002043422-1c191a.jpg"
      }
    };
  },
  computed: {
    logoEmpresaCierre() {
      return String(this.empresaImpresion.logo_principal || "").trim();
    },
    nombreEmpresaCierre() {
      return String(
        this.empresaImpresion.RAZONSOCIAL ||
        this.empresaImpresion.nombre_comercial ||
        "IMPORT COMPANY EDEN E.I.R.L."
      ).trim();
    },
    todosDetallesVisibles() {
      const ids = this.ventas
        .map(venta => this.idDetalleVenta(venta))
        .filter(id => id !== null && id !== undefined && id !== "");
      return ids.length > 0 && ids.every(id => this.detallesVentaAbiertos.includes(id));
    },
    resumenDocumentosTicket() {
      const texto = venta => String(venta.TipoDoc || "").toUpperCase();
      const codigo = venta => String(venta.CodSunat || "").padStart(2, "0");
      const definiciones = [
        { clave: "factura", titulo: "FACTURAS", etiqueta: "FACTURAS", abreviatura: "FACT.", filtro: v => codigo(v) === "01" || texto(v).includes("FACTURA") },
        { clave: "boleta", titulo: "BOLETAS", etiqueta: "BOLETAS", abreviatura: "BOLE.", filtro: v => codigo(v) === "03" || texto(v).includes("BOLETA") },
        { clave: "nota", titulo: "NOTAS DE VENTA", etiqueta: "NOTAS", abreviatura: "NOTA", filtro: v => codigo(v) === "12" || (texto(v).includes("NOTA DE VENTA") && !texto(v).includes("CREDITO")) }
      ];
      const importe = venta => Number(venta.Total || venta.Efectivo || venta.Visa || venta.Mastercard || venta.Transferencia || 0);
      return definiciones.map(definicion => {
        const documentos = this.ventas.filter(definicion.filtro).slice().sort((a, b) => Number(a.Numero || 0) - Number(b.Numero || 0));
        const activos = documentos.filter(documento => Number(documento.Anulado || 0) === 0);
        const anulados = documentos.filter(documento => Number(documento.Anulado || 0) !== 0);
        const correlativo = documento => documento ? `${documento.Serie || ""}-${documento.Numero || ""}` : "-";
        const total = activos.reduce((suma, documento) => suma + importe(documento), 0);
        return {
          ...definicion,
          emitidas: documentos.length,
          inicial: correlativo(documentos[0]),
          final: correlativo(documentos[documentos.length - 1]),
          anuladas: anulados.length,
          montoAnulado: anulados.reduce((suma, documento) => suma + importe(documento), 0),
          valorVenta: total,
          total
        };
      });
    },
    fechaActualTicket() {
      return new Date().toLocaleString("es-PE");
    },
    ventasActivas() {
      return this.ventas.filter(venta => !venta.Anulado);
    },
    totalGeneralVentas() {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard +
        this.totalVentasTransferencia + this.totalVentasCredito;
    },
    gruposVentasParaImprimir() {
      const grupos = [
        { nombre: "EFECTIVO", ventas: this.ventasEfectivo, total: this.totalVentasEfectivo },
        { nombre: "YAPE/PLIN", ventas: this.ventasMastercard, total: this.totalVentasMastercard },
        { nombre: "TARJETA", ventas: this.ventasVisa, total: this.totalVentasVisa },
        { nombre: "TRANSFERENCIA", ventas: this.ventasTransferencia, total: this.totalVentasTransferencia },
        { nombre: "CRÉDITO", ventas: this.ventasCredito, total: this.totalVentasCredito }
      ];
      const crearFilas = ventas => {
        const filas = [];
        ventas.forEach(venta => {
          const productos = venta.productos && venta.productos.length ? venta.productos : [{}];
          productos.forEach((producto, indice) => filas.push({
            fecha: indice === 0 ? venta.FechaDoc : "",
            comprobante: indice === 0 ? `${venta.TipoDoc || ""}-${venta.Serie || ""}-${venta.Numero || ""}` : "",
            producto: producto.Producto || "Sin detalle",
            modelo: producto.ProductoModelo || producto.Descripcion || "",
            marca: producto.ProductoMarca || "",
            cantidad: Number(producto.Cantidad || 0),
            precio: Number(producto.Precio || 0),
            descuento: Number(producto.Descuento || 0),
            total: Number(producto.Subtotal || venta.Total || 0),
            anulado: Boolean(venta.Anulado)
          }));
        });
        return filas;
      };
      return grupos.map(grupo => ({ ...grupo, filas: crearFilas(grupo.ventas) }));
    },
    ventasParaImprimir() {
      const numero = valor => Number(valor || 0);
      const medio = venta => {
        if (venta.EsCredito) return "Crédito";
        const medios = [];
        if (numero(venta.Efectivo) > 0) medios.push("Efectivo");
        if (numero(venta.Mastercard) > 0) medios.push("Yape/Plin");
        if (numero(venta.Visa) > 0) medios.push("Tarjeta");
        if (numero(venta.Transferencia) > 0) medios.push("Transferencia");
        return medios.join(" + ") || "Venta";
      };
      const filas = [];
      this.ventas.forEach(venta => {
        const productos = venta.productos && venta.productos.length ? venta.productos : [{}];
        productos.forEach((producto, indice) => filas.push({
          fecha: indice === 0 ? venta.FechaDoc : "",
          comprobante: indice === 0 ? `${venta.TipoDoc || ""}-${venta.Serie || ""}-${venta.Numero || ""}` : "",
          cliente: indice === 0 ? (venta.Cliente || "VARIOS") : "",
          medio: indice === 0 ? medio(venta) : "",
          producto: producto.Producto || "Sin detalle",
          modelo: producto.ProductoModelo || producto.Descripcion || "",
          marca: producto.ProductoMarca || "",
          cantidad: numero(producto.Cantidad),
          precio: numero(producto.Precio),
          descuento: numero(producto.Descuento),
          total: numero(producto.Subtotal || venta.Total),
          anulado: Boolean(venta.Anulado)
        }));
      });
      return filas;
    },
    opcionesUsuariosCaja() {
      const opciones = [{ value: null, text: "Seleccione un vendedor" }];
      return opciones.concat(this.usuariosCaja.map(usuario => ({
        value: usuario.Usuario,
        text: `${usuario.NombreUsuario || usuario.Usuario} (${usuario.Usuario})`
      })));
    },
    nombreCajaSeleccionada() {
      const usuario = this.usuariosCaja.find(item => item.Usuario === this.usuarioCajaSeleccionado);
      return usuario ? (usuario.NombreUsuario || usuario.Usuario) : "Seleccione un vendedor";
    },
    getFechaCierre() {
      //return this.$route.params.id ?
    },
    granTotal() {
      return (
        this.totalVentasEfectivo +
        this.totalVentasVisa +
        this.totalVentasMastercard +
        this.totalVentasTransferencia +
        this.totalIngresos -
        this.totalSalidas -
        this.totalAdelantosActivos
      );
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
    granTotalSinPagoCliente() {
      return this.totalVentasEfectivo - this.totalSalidas;
    },
    totalVentas() {
      return this.totalVentasEfectivo;
    },
    totalSalidas() {
      let total = 0;
      this.salidas.forEach(salida => {
        if (!salida.Anulado) total += Number(salida.Importe || 0);
      });
      return total;
    },
    totalIngresos() {
      let total = 0;
      this.ingresos.filter(v => v.Anulado == 0).forEach(ingreso => {
        if (!ingreso.Anulado) total += Number(ingreso.Importe || 0);
      });
      return total;
    },
    totalIngresosTarjeta() {
      let total = 0;
      this.ingresosTarjeta.filter(v => v.Anulado == 0).forEach(ingresoTar => {
        if (!ingresoTar.Anulado) total += Number(ingresoTar.Importe || 0);
      });
      return total;
    },
    totalSalidasTarjeta() {
      let total = 0;
      this.salidasTarjeta.filter(v => v.Anulado == 0).forEach(salidaTar => {
        if (!salidaTar.Anulado) total += Number(salidaTar.Importe || 0);
      });
      return total;
    },
    totalAdelantos() {
      let total = 0;
      this.adelantos.filter(v => v.Anulado == 0).forEach(ade => {
        if (!ade.Anulado) total += Number(ade.Importe || 0);
      });
      return total;
    },
    totalAdelantosActivos() {
      let total = 0;
      this.adelantos.filter(v => v.Anulado == 0).forEach(ade => {
        if (!ade.Anulado && !ade.AnuladoVenta && !ade.EsCreditoVenta)
          total += Number(ade.Importe || 0);
      });
      return total;
    },
    totalVentasEfectivo() {
      let total = 0;
      this.ventasEfectivo.filter(v => v.Anulado == 0).forEach(venta => {
        if (!venta.Anulado) total += Number(venta.Efectivo || 0);
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
        if (!venta.Anulado) total += Number(venta.Visa || 0);
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
        if (!venta.Anulado) total += Number(venta.Mastercard || 0);
      });
      return total;
    },
    ventasMastercard() {
      return this.ventas.filter(venta => {
        return venta.Mastercard > 0 && !venta.EsCredito;
      });
    },
    totalVentasTransferencia() {
      let total = 0;
      this.ventasTransferencia.forEach(venta => {
        if (!venta.Anulado) total += Number(venta.Transferencia || 0);
      });
      return total;
    },
    ventasTransferencia() {
      return this.ventas.filter(venta => {
        return venta.Transferencia > 0 && !venta.EsCredito;
      });
    },
    totalVentasCredito() {
      let total = 0;
      this.ventasCredito.filter(v => v.Anulado == 0).forEach(venta => {
        if (!venta.Anulado) total += Number(venta.Total || 0);
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
    cargarEmpresaImpresion() {
      this.axios
        .get("/api/tienda/index.php", { params: { action: "empresa" } })
        .then(response => {
          if (response.data && typeof response.data === "object") {
            this.empresaImpresion = { ...this.empresaImpresion, ...response.data };
          }
        })
        .catch(() => {
          // Se conserva la identidad local de respaldo para no perder el logo al imprimir.
        });
    },
    idDetalleVenta(venta) {
      return venta.idDocVenta || venta.IdDocVenta || `${venta.Serie}-${venta.Numero}`;
    },
    esDetalleVisible(venta) {
      return this.detallesVentaAbiertos.includes(this.idDetalleVenta(venta));
    },
    alternarDetalleVenta(venta) {
      const id = this.idDetalleVenta(venta);
      const posicion = this.detallesVentaAbiertos.indexOf(id);
      if (posicion === -1) {
        this.detallesVentaAbiertos.push(id);
      } else {
        this.detallesVentaAbiertos.splice(posicion, 1);
      }
    },
    alternarTodosDetalles() {
      if (this.todosDetallesVisibles) {
        this.detallesVentaAbiertos = [];
        return;
      }
      this.detallesVentaAbiertos = Array.from(new Set(
        this.ventas
          .map(venta => this.idDetalleVenta(venta))
          .filter(id => id !== null && id !== undefined && id !== "")
      ));
    },
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
          this.esAdministrador = Number(response.data.IdUsuarioPerfil) === 1;
          this.usuarioCajaSeleccionado = response.data.Usuario;
          this.setVendedor(response.data);
          // this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
          // this.$refs.inputVendedor.$refs.input.disabled = true

          if (response.data.IdUsuarioPerfil !== 1) {
            // this.$refs.inputVendedor.$refs.input.disabled = true
          }
          if (this.esAdministrador) {
            this.cargarUsuariosCaja();
          }
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    cargarUsuariosCaja() {
      this.axios.get(this.API + "usuarios", { params: { q: "" } }).then(response => {
        this.usuariosCaja = (response.data || []).map(usuario => ({
          ...usuario,
          Usuario: usuario.Usuario || usuario.usuario
        }));
      });
    },
    seleccionarCajaUsuario(usuario) {
      if (!usuario) return;
      this.axios.get(this.API + "usuarios/" + encodeURIComponent(usuario)).then(response => {
        this.setVendedor(response.data);
        this.detallesVentaAbiertos = [];
      });
    },
    imprimir() {
      this.modoImpresionTicket = false;
      this.$nextTick(() => window.print());
    },
    imprimirResumen() {
      this.modoImpresionTicket = true;
      const restaurar = () => {
        this.modoImpresionTicket = false;
        window.removeEventListener("afterprint", restaurar);
      };
      window.addEventListener("afterprint", restaurar);
      this.$nextTick(() => window.print());
    },
    imprimirProductos(formato) {
      this.modoImpresionProductos = formato;
      const restaurar = () => {
        this.modoImpresionProductos = null;
        window.removeEventListener("afterprint", restaurar);
      };
      window.addEventListener("afterprint", restaurar);
      this.$nextTick(() => window.print());
    },
    imprimirYape(formato) {
      this.modoImpresionYape = formato;
      const restaurar = () => {
        this.modoImpresionYape = null;
        window.removeEventListener("afterprint", restaurar);
      };
      window.addEventListener("afterprint", restaurar);
      this.$nextTick(() => window.print());
    },
    imprimirTransferencia(formato) {
      this.modoImpresionTransferencia = formato;
      const restaurar = () => {
        this.modoImpresionTransferencia = null;
        window.removeEventListener("afterprint", restaurar);
      };
      window.addEventListener("afterprint", restaurar);
      this.$nextTick(() => window.print());
    },
    async loadData() {
      await this.getVentas();
      await this.getIngresos();
      await this.getSalidas();

      await this.generarReporte();
      // console.log(this.ventas);
    },
    generarReporte() {
      let productos = [];
      this.ventas.filter(ventas => !ventas.Anulado).forEach(venta => {
        console.log(venta);
        if (venta.productos) {
          venta.productos.forEach(producto => {
            productos.push(producto);
          });
        }
      });

      const productosUnicos = Array.from(
        new Set(productos.map(item => item.IdProducto))
      ).map(producto => {
        return { IdProducto: producto };
      });

      this.reportePorProveedor = [];
      productosUnicos.forEach(producto => {
        let lstProducto = productos.filter(
          v => v.IdProducto == producto.IdProducto
        );
        let total = lstProducto.reduce((total, producto) => {
          return total + parseFloat(producto.Cantidad);
        }, 0);
        let Precio = lstProducto.reduce((total, producto) => {
          return (
            total +
            parseFloat(producto.Precio * producto.Cantidad) -
            parseFloat(producto.Descuento)
          );
        }, 0);
        this.reportePorProveedor.push({
          IdProducto: producto.IdProducto,
          Producto: lstProducto[0].Producto,
          ProductoModelo: lstProducto[0].ProductoModelo,
          ProductoMarca: lstProducto[0].ProductoMarca,
          total: total,
          Precio: Precio
        });
      });
      // console.log(prod);
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
            user: this.vendedor.Usuario || sessionStorage.getItem("User"),
            actorUser: sessionStorage.getItem("User"),
            ingresos: parseFloat(this.granTotal + this.totalSalidas).toFixed(2),
            salidas: this.totalSalidas,
            total: this.granTotal,
            idAlmacen: sessionStorage.getItem("IdAlmacen"),
            totalVentas: this.totalVentas,
            descripcion: String(this.descripcionCierre || "").trim().slice(0, 500)
          })
          .then(res => {
            this.descripcionCierre = "";
            this.loadData();
            this.$toasted.show("¡Caja cerrada!");
            // console.log(res)
          });
      }
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

    formatoHora(fecha) {
      if (!fecha) return "";
      if (typeof fecha === "string" && fecha.includes(" ")) {
        return fecha.split(" ")[1].substring(0, 5);
      }
      const d = new Date(fecha);
      return isNaN(d) ? fecha : d.toLocaleTimeString("es-PE", { hour: "2-digit", minute: "2-digit" });
    },

    async loadDetalles() {
      await this.axios
        .post(this.API + "ventas/detalle", {
          idDocVentas: this.ventas.map(item => item.idDocVenta)
        })
        .then(response => {
          this.itemsDetalle = response.data;
          this.ventas = this.ventas.map(item => {
            item.productos = this.getDetalles(item.idDocVenta);
            return item;
          });
        })
        .catch(error => {
          console.log(error);
        });
    },
    async getVentas() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }
      // cierre de caja enviar el vendedor
      await this.axios
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
    async getIngresos() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }
      await this.axios
        .get(this.API + "cierrecaja/ingresos", {
          params: params
        })
        .then(res => {
          this.ingresos = res.data;
          // this.ingresos = res.data.filter(ing => ing.EsTarjeta == 0);
          // this.ingresosTarjeta = res.data.filter(ing => ing.EsTarjeta == 1);
        })
        .catch(err => {
          console.log(err);
        });
    },
    async getSalidas() {
      let params = {
        idCierre: this.$route.params.id
      };
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario;
      }
      await this.axios
        .get(this.API + "cierrecaja/salidas", {
          params: params
        })
        .then(res => {
          this.salidas = res.data;
          // this.salidas = res.data.filter(sal => sal.EsTarjeta == 0);
          // this.salidasTarjeta = res.data.filter(sal => sal.EsTarjeta == 1);
        })
        .catch(err => {
          console.log(err);
        });
    },
    getAdelantos() {
      this.axios
        .post(this.API + "cierrecaja/ingresos/adelantos", {
          idDocVentas: this.ventas.map(v => v.idDocVenta)
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
  background: #1a237e;
  /* color: #ffff; */
  border: 1px solid #ffffff;
}
.total {
  background-color: #e4ff95;
  color: #000000;
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
.tableResumen {
  width: 100%;
  font-weight: bold;
}

.thDetalle {
  color: white;
}
.si-print {
  display: none;
}
@media print {
  @page cierre-landscape { size: A4 landscape; margin: 7mm; }
  @page ticket { size: auto; margin: 0; }
  @page products-pdf { size: A4 portrait; margin: 10mm; }
  .cierre-print-anterior { display: none !important; }
  .cierre-print-compacto {
    page: cierre-landscape;
    display: block !important;
    width: 100%;
    color: #17324d;
    font-family: Arial, sans-serif;
    font-size: 8px;
  }
  .print-cierre-header { display:flex; justify-content:space-between; align-items:center; border-bottom:2px solid #0b5f89; padding:0 0 5px; margin-bottom:5px; }
  .print-cierre-header h1 { font-size:18px; margin:0; color:#084c75; }
  .print-cierre-header p { margin:2px 0 0; font-size:8px; }
  .print-cierre-total { text-align:right; }
  .print-cierre-total small { display:block; font-size:7px; font-weight:700; }
  .print-cierre-total strong { font-size:16px; color:#087f5b; }
  .print-resumen-grid { display:grid; grid-template-columns:repeat(6, 1fr); gap:3px; margin-bottom:6px; }
  .print-resumen-grid span { background:#eef5f9; border:1px solid #d2e2eb; padding:4px; border-radius:3px; }
  .print-resumen-grid b { display:block; margin-top:1px; font-size:9px; }
  .print-total-general { display:flex; justify-content:flex-end; align-items:center; gap:12px; margin:0 0 6px; padding:4px 7px; background:#e4f5ed!important; color:#075d42; font-size:9px; font-weight:700; }
  .print-total-general strong { font-size:12px; }
  .cierre-print-compacto h2 { margin:5px 0 3px; font-size:11px; color:#084c75; }
  .print-tabla-ventas, .print-movimientos-grid table { width:100%; border-collapse:collapse; table-layout:fixed; }
  .print-tabla-ventas th, .print-tabla-ventas td, .print-movimientos-grid th, .print-movimientos-grid td { border:1px solid #d5e0e7; padding:2px 3px; line-height:1.15; vertical-align:top; overflow-wrap:anywhere; }
  .print-tabla-ventas th, .print-movimientos-grid th { background:#0b628d !important; color:#fff !important; font-weight:700; }
  .print-tabla-ventas tr:nth-child(even) td { background:#f4f8fa !important; }
  .print-tabla-ventas .print-grupo-titulo td { background:#dcecf4!important; color:#084c75!important; font-weight:800; padding:4px 5px!important; }
  .print-tabla-ventas .print-grupo-total td { background:#edf7f2!important; color:#075d42!important; font-weight:800; text-align:right; }
  .print-tabla-ventas .print-gran-total-final td { background:#084c75!important; color:#fff!important; font-size:10px; font-weight:900; text-align:right; padding:5px!important; }
  .print-tabla-ventas th:nth-child(1) { width:8%; } .print-tabla-ventas th:nth-child(2) { width:11%; }
  .print-tabla-ventas th:nth-child(3) { width:12%; } .print-tabla-ventas th:nth-child(4) { width:8%; }
  .print-tabla-ventas th:nth-child(5) { width:17%; } .print-tabla-ventas th:nth-child(6) { width:10%; }
  .print-tabla-ventas th:nth-child(n+7) { width:6%; text-align:right; }
  .print-tabla-ventas td:nth-child(n+7) { text-align:right; }
  .print-tabla-ventas tr { break-inside:avoid; }
  .print-vacio { text-align:center !important; padding:8px !important; }
  .print-movimientos-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; margin-top:6px; }
  .print-movimientos-grid tfoot td { font-weight:700; background:#eef5f9 !important; }
  .print-movimientos-grid td:last-child { text-align:right; }
  .no-print {
    display: none;
  }
  .si-print {
    max-width: 350px;
    display: block;
  }
  .table {
    max-width: 350px;
    font-size: 0.5em;
  }
  th {
    background: #ffffff !important;
    color: #000000 !important;
    border: 1px solid #ffffff;
  }
  .cash-close-modern {
    width: 100% !important;
    max-width: none !important;
    padding: 0 !important;
    margin: 0 !important;
    background: #fff !important;
  }
  .si-print.cierre-print-compacto {
    display: block !important;
    width: 100% !important;
    max-width: none !important;
    margin: 0 !important;
  }
  .print-tabla-ventas th:nth-child(1) { width:9%; }
  .print-tabla-ventas th:nth-child(2) { width:12%; }
  .print-tabla-ventas th:nth-child(3) { width:20%; }
  .print-tabla-ventas th:nth-child(4) { width:12%; }
  .print-tabla-ventas th:nth-child(5) { width:11%; }
  .print-tabla-ventas th:nth-child(6) { width:7%; }
  .print-tabla-ventas th:nth-child(7) { width:6%; }
  .print-tabla-ventas th:nth-child(8) { width:8%; }
  .print-tabla-ventas th:nth-child(9) { width:7%; }
  .print-tabla-ventas th:nth-child(10) { width:8%; }
  .ticket-print-summary {
    page: ticket;
    display:block!important;
    width:78mm!important;
    min-width:78mm!important;
    max-width:78mm!important;
    box-sizing:border-box!important;
    margin:0!important;
    position:absolute!important;
    left:10mm!important;
    top:0!important;
    color:#111;
    font-family:Arial,sans-serif;
    font-size:14px;
    font-weight:600;
  }
  .ticket-print-summary header{text-align:center;border-bottom:2px solid #111;padding-bottom:6px;margin-bottom:7px}
  .ticket-print-summary h1{font-size:22px;margin:0 0 5px;font-weight:900}
  .ticket-print-summary header p{margin:3px 0;font-size:13px}
  .ticket-products{width:100%;margin:0 auto;border-collapse:collapse;table-layout:fixed;font-size:13px;font-weight:600}
  .ticket-products th,.ticket-products td{padding:7px 3px;border-bottom:1px dotted #666;overflow-wrap:anywhere;vertical-align:middle;line-height:1.25}
  .ticket-products th{padding-top:9px;padding-bottom:9px;font-size:11px;text-transform:uppercase;text-align:center;border-top:2px solid #111;border-bottom:2px solid #111}
  .ticket-products th:nth-child(1){width:38%;text-align:left}.ticket-products th:nth-child(2){width:22%;text-align:left}
  .ticket-products th:nth-child(3){width:17%;text-align:center}.ticket-products th:nth-child(4){width:23%;text-align:right}
  .ticket-products td:nth-child(3){text-align:center}.ticket-products td:nth-child(4){text-align:right;white-space:nowrap}
  .ticket-products tfoot td{padding:11px 3px 9px;border-top:3px solid #111;border-bottom:5px double #111;font-size:15px;font-weight:900}
  .ticket-products tfoot td:last-child{text-align:right}
  .ticket-products .ticket-empty{text-align:center;padding:10px 2px}
  .products-print h1{text-align:center;font:900 22px Arial,sans-serif;margin:0 0 15px;letter-spacing:.2px}
  .products-print-pdf{page:products-pdf;display:block!important;width:100%!important;max-width:none!important;color:#111}
  .products-pdf-table{width:100%;margin:0 auto;border-collapse:collapse;table-layout:fixed;font-size:7.5px}
  .products-pdf-table th,.products-pdf-table td{padding:5px 3px;border-bottom:1px solid #dbe5eb;overflow-wrap:anywhere;text-align:left;vertical-align:middle}
  .products-pdf-table th{background:#e7f0f5!important;color:#073b5c!important;font-size:7px;font-weight:800}
  .products-pdf-table tbody tr:nth-child(even) td{background:#f5f8fa!important}
  .products-pdf-table th:nth-child(1){width:5%}.products-pdf-table th:nth-child(2){width:14%}.products-pdf-table th:nth-child(3){width:13%}
  .products-pdf-table th:nth-child(4){width:10%}.products-pdf-table th:nth-child(5){width:6%}.products-pdf-table th:nth-child(6){width:8%}
  .products-pdf-table th:nth-child(7){width:8%}.products-pdf-table th:nth-child(8){width:13%}.products-pdf-table th:nth-child(9){width:11%}.products-pdf-table th:nth-child(10){width:12%}
  .products-pdf-table td:nth-child(9){text-align:center}.products-pdf-table td:nth-child(10){text-align:right;white-space:nowrap}
  .products-pdf-table tfoot td{padding:7px 5px;background:#e3f5eb!important;color:#075d42!important;font-size:11px;font-weight:900}
  .products-pdf-table tfoot td:last-child{text-align:right}
  .products-print-ticket{page:ticket;display:block!important;position:absolute!important;left:0!important;top:0!important;width:78mm!important;min-width:78mm!important;max-width:78mm!important;box-sizing:border-box!important;margin:0!important;padding:4mm 1.2mm 3mm!important;border:1px solid #ddd;border-radius:7mm;color:#111;font-family:Arial,sans-serif}
  .products-print-ticket h1,.products-print-ticket .ticket-products{width:100%!important;max-width:none!important;box-sizing:border-box!important}
  .yape-print{font-family:Arial,sans-serif;color:#17212b}
  .yape-print-header{display:flex;align-items:center;gap:8px;border-bottom:2px solid #111;padding-bottom:7px;margin-bottom:8px}
  .yape-print-header h1{font-size:15px;margin:0;font-weight:900}.yape-print-header p{font-size:9px;margin:2px 0 0}
  .yape-print-icon{display:flex;align-items:center;justify-content:center;width:29px;height:29px;border:2px solid #111;border-radius:50%;font-size:10px;font-weight:900}
  .yape-print-pdf{page:products-pdf;display:block!important;width:100%!important;max-width:none!important}
  .yape-pdf-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:9px}
  .yape-pdf-table th,.yape-pdf-table td{border:1px solid #ccd5dd;padding:4px;overflow-wrap:anywhere}
  .yape-pdf-table th{background:#174f78!important;color:#fff!important}.yape-pdf-table td:last-child{text-align:right;white-space:nowrap}
  .yape-print-ticket{page:ticket;display:block!important;position:absolute!important;left:0!important;top:0!important;width:78mm!important;min-width:78mm!important;max-width:78mm!important;box-sizing:border-box!important;margin:0!important;padding:4mm 1.2mm 3mm!important;border:1px solid #ddd;border-radius:7mm;color:#111;font-family:Arial,sans-serif;font-size:13px;font-weight:600}
  .yape-ticket-list h1{text-align:center;font-size:22px;font-weight:900;margin:0 0 15px}
  .yape-ticket-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:13px;font-weight:600}
  .yape-ticket-table th,.yape-ticket-table td{padding:7px 3px;border-bottom:1px dotted #666;overflow-wrap:anywhere;vertical-align:middle;line-height:1.25}
  .yape-ticket-table th{padding-top:9px;padding-bottom:9px;border-top:2px solid #111;border-bottom:2px solid #111;font-size:11px;text-align:left;text-transform:uppercase}
  .yape-ticket-table th:nth-child(1){width:38%}.yape-ticket-table th:nth-child(2){width:34%;text-align:center}.yape-ticket-table th:nth-child(3){width:28%;text-align:right}
  .yape-ticket-table td:nth-child(2){text-align:center}.yape-ticket-table td:nth-child(3){text-align:right;white-space:nowrap}
  .yape-print-total{display:flex;justify-content:space-between;align-items:center;margin-top:7px;padding:8px 4px;font-size:14px;font-weight:900}
  .yape-print-ticket .yape-print-total{margin-top:0;padding:11px 3px 9px;border-top:3px solid #111;border-bottom:5px double #111;font-size:15px}
  .yape-print-total strong{font-size:17px}.yape-print footer{text-align:center;margin-top:10px;font-size:10px;letter-spacing:.5px}
  .ticket-print-summary h2{text-align:center;font-size:15px;margin:12px 0 6px;padding:6px 0;border-top:1px dashed #333;border-bottom:1px dashed #333}
  .ticket-line,.ticket-detail,.ticket-grand,.ticket-final{display:flex;justify-content:space-between;gap:8px;padding:4px 0;line-height:1.25}
  .ticket-line{font-size:13.5px;border-bottom:1px dotted #aaa}
  .ticket-detail{font-size:12px;padding-left:5px;color:#222}
  .ticket-detail span{max-width:48mm}
  .ticket-grand,.ticket-final{margin:8px 0;padding:9px 4px;border-top:2px solid #111;border-bottom:2px solid #111;font-size:15px;font-weight:900}
  .ticket-out{margin-top:5px}
  .ticket-print-summary footer{text-align:center;margin-top:11px;padding-top:6px;border-top:1px dashed #333;font-size:10px}
  .ticket-doc-summary{width:100%;margin:8px 0 12px;break-inside:avoid}
  .ticket-doc-summary .ticket-line{border-bottom:0;padding:3px 1px}
  .ticket-doc-summary .ticket-doc-total{margin-top:2px;border-top:1px solid #111;border-bottom:3px double #111;font-weight:900}
  .cash-close-modern.ticket-mode{
    position:absolute!important;
    left:0!important;
    top:0!important;
    width:78mm!important;
    min-width:78mm!important;
    max-width:78mm!important;
    height:auto!important;
    padding:0!important;
    margin:0!important;
    overflow:visible!important;
  }

  /* Identidad corporativa uniforme para cierre, productos y conciliaciones. */
  .print-company-logo-frame{
    display:block;
    width:46mm;
    height:15mm;
    margin:0 auto 1.5mm;
    overflow:hidden;
    border-radius:2mm;
    background:#fff;
  }
  .print-company-logo-frame img{
    display:block;
    width:100%;
    height:100%;
    object-fit:cover;
    object-position:center 53%;
  }
  .print-company-name{
    display:block;
    color:#073f3a!important;
    font-size:10px;
    font-weight:900;
    letter-spacing:.55px;
    line-height:1.15;
    text-align:center;
    text-transform:uppercase;
  }
  .print-report-brand{
    display:flex!important;
    margin:0 auto 4mm!important;
    padding:0 0 2.5mm!important;
    align-items:center!important;
    flex-direction:column!important;
    gap:.6mm!important;
    border:0!important;
    border-bottom:2px solid #075247!important;
    text-align:center!important;
  }
  .print-report-brand h1{
    margin:1mm 0 0!important;
    color:#082f39!important;
    font:900 18px/1.12 Arial,sans-serif!important;
    letter-spacing:.2px!important;
    text-align:center!important;
  }
  .print-report-brand p{
    margin:0!important;
    color:#536b72!important;
    font-size:9px!important;
    font-weight:700!important;
  }
  .print-cierre-header{
    display:grid!important;
    padding:0 0 2mm!important;
    grid-template-columns:1fr auto 1fr!important;
    align-items:center!important;
    border-bottom:2px solid #075247!important;
  }
  .print-cierre-brand{
    grid-column:2;
    text-align:center;
  }
  .print-cierre-brand .print-company-logo-frame{
    width:40mm;
    height:13mm;
    margin-bottom:1mm;
  }
  .print-cierre-brand h1{margin:1mm 0 0!important;color:#073f3a!important;text-align:center}
  .print-cierre-brand p{text-align:center}
  .print-cierre-total{grid-column:3;justify-self:end}
  .ticket-print-summary{
    left:0!important;
    margin:0 auto!important;
    padding:3mm 2.2mm!important;
    box-sizing:border-box!important;
  }
  .ticket-print-summary header{
    padding-bottom:2.5mm!important;
    border-bottom:2px solid #075247!important;
  }
  .ticket-print-summary header h1{
    margin:1.5mm 0 1mm!important;
    color:#082f39!important;
    font-size:19px!important;
  }
  .ticket-print-summary header p{margin:.6mm 0!important;font-size:11px!important}
  .products-print-ticket,
  .yape-print-ticket{
    left:0!important;
    margin:0 auto!important;
    padding:3mm 2.2mm!important;
    border:0!important;
    border-radius:0!important;
  }
  .products-print-ticket .print-report-brand,
  .yape-print-ticket .print-report-brand{margin-bottom:2.5mm!important}
  .products-print-ticket .print-company-logo-frame,
  .yape-print-ticket .print-company-logo-frame,
  .ticket-print-summary .print-company-logo-frame{
    width:48mm;
    height:15.5mm;
  }
  .products-print-ticket .print-company-name,
  .yape-print-ticket .print-company-name,
  .ticket-print-summary .print-company-name{font-size:9px}
  .products-print-ticket .print-report-brand h1,
  .yape-print-ticket .print-report-brand h1{font-size:17px!important}
  .products-print-pdf,
  .yape-print-pdf{margin:0 auto!important}
  .products-pdf-table,
  .yape-pdf-table{margin-right:auto!important;margin-left:auto!important}
  .products-pdf-table thead th,
  .yape-pdf-table thead th,
  .print-resumen-grid span,
  .print-total-general{
    -webkit-print-color-adjust:exact!important;
    print-color-adjust:exact!important;
  }

  /* Formato termico adaptable: ocupa el ancho real configurado en la impresora. */
  .cash-close-modern.ticket-mode{
    position:absolute!important;
    left:0!important;
    top:0!important;
    right:auto!important;
    bottom:auto!important;
    display:block!important;
    float:none!important;
    width:100vw!important;
    min-width:0!important;
    max-width:none!important;
    height:auto!important;
    margin:0!important;
    padding:0!important;
    overflow:visible!important;
    box-sizing:border-box!important;
    transform:none!important;
  }
  .cash-close-modern.ticket-mode > .ticket-print-summary,
  .cash-close-modern.ticket-mode > .products-print-ticket,
  .cash-close-modern.ticket-mode > .yape-print-ticket{
    position:relative!important;
    left:0!important;
    top:0!important;
    right:auto!important;
    display:block!important;
    float:none!important;
    width:100%!important;
    min-width:0!important;
    max-width:none!important;
    margin:0!important;
    padding:3mm 3.5mm 4mm!important;
    border:0!important;
    border-radius:0!important;
    box-sizing:border-box!important;
    color:#111!important;
    font-size:15px!important;
    line-height:1.28!important;
    transform:none!important;
  }
  .cash-close-modern.ticket-mode .print-report-brand,
  .cash-close-modern.ticket-mode .ticket-print-summary > header{
    width:100%!important;
    max-width:none!important;
    margin:0 0 3mm!important;
    padding:0 0 2.5mm!important;
    box-sizing:border-box!important;
    text-align:center!important;
  }
  .cash-close-modern.ticket-mode .print-company-logo-frame{
    width:72%!important;
    max-width:76mm!important;
    height:21mm!important;
    margin:0 auto 1.5mm!important;
  }
  .cash-close-modern.ticket-mode .print-company-name{
    font-size:12px!important;
    line-height:1.2!important;
    letter-spacing:.35px!important;
  }
  .cash-close-modern.ticket-mode .print-report-brand h1,
  .cash-close-modern.ticket-mode .ticket-print-summary h1{
    margin:1.2mm 0 .7mm!important;
    font-size:22px!important;
    line-height:1.08!important;
  }
  .cash-close-modern.ticket-mode .print-report-brand p,
  .cash-close-modern.ticket-mode .ticket-print-summary header p{
    font-size:13px!important;
    line-height:1.25!important;
  }
  .cash-close-modern.ticket-mode .ticket-products,
  .cash-close-modern.ticket-mode .yape-ticket-table{
    width:100%!important;
    max-width:none!important;
    margin:0!important;
    table-layout:fixed!important;
    font-size:14.5px!important;
    line-height:1.25!important;
  }
  .cash-close-modern.ticket-mode .ticket-products th,
  .cash-close-modern.ticket-mode .yape-ticket-table th{
    padding:9px 3px!important;
    font-size:12px!important;
    line-height:1.15!important;
  }
  .cash-close-modern.ticket-mode .ticket-products td,
  .cash-close-modern.ticket-mode .yape-ticket-table td{
    padding:8px 3px!important;
    font-size:14.5px!important;
    line-height:1.25!important;
  }
  .cash-close-modern.ticket-mode .ticket-products tfoot td,
  .cash-close-modern.ticket-mode .yape-print-total{
    padding:11px 3px 9px!important;
    font-size:18px!important;
    line-height:1.2!important;
  }
  .cash-close-modern.ticket-mode .yape-print-total strong{font-size:19px!important}
  .products-print-pdf .print-report-brand,
  .yape-print-pdf .print-report-brand{width:100%!important;max-width:none!important}
  .products-print-pdf .print-company-logo-frame,
  .yape-print-pdf .print-company-logo-frame{width:62mm!important;height:21mm!important}
  .products-print-pdf .print-report-brand h1,
  .yape-print-pdf .print-report-brand h1{font-size:22px!important}
  .products-print-pdf .products-pdf-table{font-size:9px!important}
  .products-print-pdf .products-pdf-table th{font-size:8.5px!important;padding:7px 4px!important}
  .products-print-pdf .products-pdf-table td{padding:7px 4px!important}
  .yape-print-pdf .yape-pdf-table{font-size:10px!important}
  .yape-print-pdf .yape-pdf-table th,
  .yape-print-pdf .yape-pdf-table td{padding:7px 5px!important}
}

.clsVentas {
  overflow: visible !important;
  max-height: none !important;
}

.cash-close-modern {
  --cash-blue: #075985;
  --cash-blue-2: #0e7490;
  --cash-border: #d8e5ee;
  background: #eef5f9;
  color: #16324a;
  font-family: "Inter", "Segoe UI", sans-serif;
}
.cash-close-modern .card {
  border: 1px solid var(--cash-border);
  border-radius: 18px;
  background: #ffffff;
  box-shadow: 0 12px 30px rgba(22, 64, 91, .10);
  overflow: hidden;
}
.cash-detail-panel > .card { height: 100%; }
.cash-close-modern .card-header { padding: 14px; background: #f4f8fb; border-bottom: 1px solid var(--cash-border); }
.cash-close-modern .nav-pills { gap: 8px; }
.cash-close-modern .nav-pills .nav-link { padding: 12px 19px; border-radius: 10px; color: #31546d; font-weight: 800; transition: transform .18s ease, box-shadow .18s ease, background-color .18s ease; }
.cash-close-modern .nav-pills .nav-link:hover { transform: translateY(-2px); background:#e5f1f7; }
.cash-close-modern .nav-pills .nav-link.active { background: var(--cash-blue); color: #fff; box-shadow: 0 7px 16px rgba(7, 89, 133, .25); }
.payment-title { display:inline-flex;align-items:center;margin:12px 0 3px;padding:10px 16px;border-radius:9px;color:#fff;font-size:1rem;font-weight:850;letter-spacing:.2px; }
.payment-cash { background:#07834f; }
.payment-card { background:#3157b7; }
.payment-wallet { background:#7c3fc2; }
.payment-transfer { background:#087f9c; }
.cash-close-modern .table { border-collapse:separate;border-spacing:0;font-size:.83rem; }
.cash-close-modern .table > thead th,.cash-close-modern .thDetalle th { padding:12px 10px;background:var(--cash-blue)!important;color:#fff!important;border:0!important;font-weight:800;white-space:nowrap; }
.cash-close-modern .table > tbody > tr > td { padding:11px 10px;border-color:#e2ebf1; }
.cash-close-modern .table > tbody > tr:nth-child(even) { background:#f5f9fc; }
.cash-close-modern .tabla-detalle { margin:6px 0;border:1px solid #dbe7ee;border-radius:9px;overflow:hidden;background:#fff; }
.cash-close-modern .tabla-detalle th { background:#eaf2f7!important;color:#264b65!important;font-weight:750!important; }
.cash-summary-panel { padding:18px!important;border-top:5px solid var(--cash-blue)!important;overflow:visible!important; }
.cash-summary-panel h2 { margin:0 0 14px;color:#063d69;font-size:1.75rem;font-weight:900;letter-spacing:-.6px; }
.cash-summary-panel .btn-group { margin:0 6px 8px 0; }
.cash-summary-panel .btn { min-height:48px;padding:10px 17px;border:0;border-radius:11px!important;font-weight:800;transition:transform .18s ease,box-shadow .18s ease; }
.cash-summary-panel .btn:hover { transform:translateY(-3px) scale(1.02);box-shadow:0 9px 18px rgba(15,52,78,.22); }
.cash-summary-panel .btn:active { transform:translateY(0) scale(.98); }
.cash-summary-panel .btn-danger { background:#e83d5b; }
.cash-summary-panel .btn-primary { background:#0789b0; }
.cash-summary-panel .form-control,.cash-summary-panel .custom-select { min-height:52px;border:1px solid #c9dce8;border-radius:12px;background:#f9fcfe;font-weight:650; }
.tableResumen { margin-top:18px;border:0!important;border-spacing:0 7px!important;background:transparent!important; }
.tableResumen tr td { border:0!important;padding:13px 14px!important;background:#f0f5f8;font-size:.9rem; }
.tableResumen tr td:first-child { border-radius:9px 0 0 9px;font-weight:750; }
.tableResumen tr td:last-child { border-radius:0 9px 9px 0;text-align:right;font-weight:900; }
.tableResumen tr:has(td[colspan]) { display:none; }
.tableResumen .summary-grand td { background:#063d69!important;color:#fff;font-size:1rem; }
.tableResumen .summary-income td { background:#d9f5e7!important;color:#07643f; }
.tableResumen .summary-outflow td { background:#ffe3e8!important;color:#a61f3b; }
.tableResumen .summary-credit td { background:#fff0cf!important;color:#8a5900; }
.tableResumen .summary-cash td { background:#dff5e9!important;color:#087247; }
.tableResumen .summary-card td { background:#e4eafd!important;color:#294b9e; }
.tableResumen .summary-wallet td { background:#f0e5fb!important;color:#6b2da6; }
.tableResumen .summary-transfer td { background:#dff3f7!important;color:#07677d; }
.cash-close-modern > .row > .col-12 > .alert { margin-top:16px;padding:17px;border:0;border-radius:13px;background:#075985;color:#fff;font-size:1.15rem;box-shadow:0 8px 18px rgba(7,89,133,.2); }
.btn-sale-detail{margin:0 8px 5px 0!important;padding:6px 11px!important;min-height:34px!important;border:0!important;border-radius:8px!important;background:#075985!important;color:#fff!important;font-weight:800!important;box-shadow:0 4px 9px rgba(7,89,133,.2)!important}
.btn-sale-detail:hover{transform:translateY(-2px);background:#0e7490!important}
@media(max-width:991px){.cash-summary-panel{margin-top:18px}.cash-close-modern{padding:12px!important}.clsVentas{max-height:none!important}.cash-detail-panel{padding:0}}

/* Panel de referencia: colores enteros suaves y sin franjas azules */
.cash-close-modern .card-header{padding:13px 16px;background:#fff;border:0;border-bottom:1px solid #e5edf2}
.cash-close-modern .nav-pills{justify-content:flex-start!important}
.cash-close-modern .nav-pills .nav-link{display:flex;align-items:center;justify-content:center;min-height:50px;padding:11px 20px;color:#fff!important;border:1px solid rgba(255,255,255,.2);border-radius:12px;font-size:.86rem;font-weight:850;letter-spacing:.1px;box-shadow:0 5px 0 rgba(24,49,70,.18),0 9px 18px rgba(32,66,89,.13)}
.cash-close-modern .nav-pills li:nth-child(1) .nav-link{background:#13abc5!important}
.cash-close-modern .nav-pills li:nth-child(2) .nav-link{background:#3478e5!important}
.cash-close-modern .nav-pills li:nth-child(3) .nav-link{background:#8a3fe2!important}
.cash-close-modern .nav-pills li:nth-child(4) .nav-link{background:#56677d!important}
.cash-close-modern .nav-pills .nav-link:hover,.cash-close-modern .nav-pills .nav-link.active{transform:translateY(-2px);filter:brightness(1.04);box-shadow:0 7px 0 rgba(24,49,70,.16),0 12px 21px rgba(32,66,89,.18)}
.cash-close-modern .nav-pills .nav-link:active{transform:translateY(3px);box-shadow:0 2px 0 rgba(24,49,70,.2),0 5px 10px rgba(32,66,89,.12)}
.cash-close-modern .payment-title{display:block;margin:12px 0 14px;padding:10px 14px;border-radius:9px;font-size:.84rem}
.cash-close-modern .payment-cash{background:#ddf5e9;color:#08784c;border:1px solid #a9e5ca}
.cash-close-modern .payment-card{background:#e4eafd;color:#294b9e;border:1px solid #c5d0f5}
.cash-close-modern .payment-wallet{background:#f0e2f5;color:#74238f;border:1px solid #dbb7e7}
.cash-close-modern .payment-transfer{background:#dff3f5;color:#08707b;border:1px solid #aadfe4}
.cash-close-modern .payment-title + br{display:none}
.cash-close-modern .table{border:0!important;border-collapse:separate;border-spacing:0}
.cash-close-modern .table > thead th,.cash-close-modern .thDetalle th{padding:12px 10px;background:#e7f0f6!important;color:#173f5b!important;border:0!important;border-bottom:1px solid #c9dce7!important}
.cash-close-modern .payment-cash + br + .table > thead th{background:#dff5e9!important;color:#086b45!important;border-bottom-color:#a9dfc7!important}
.cash-close-modern .payment-card + br + .table > thead th{background:#e4eafd!important;color:#294b9e!important;border-bottom-color:#c5d0f5!important}
.cash-close-modern .payment-wallet + br + .table > thead th{background:#f0e2f5!important;color:#6d2684!important;border-bottom-color:#dab6e4!important}
.cash-close-modern .payment-transfer + br + .table > thead th{background:#dff3f5!important;color:#086b76!important;border-bottom-color:#aadce1!important}
.cash-close-modern .table > tbody > tr > td{border:0!important;border-bottom:1px solid #e7eef3!important}
.cash-close-modern .table > tbody > tr:nth-child(even){background:#f7fafc}
.cash-close-modern .table tfoot td{border:0!important;background:#e3f7ec!important;color:#075d3d!important}
.cash-detail-panel>.card{border:1px solid #dce8ef;box-shadow:0 9px 24px rgba(29,67,91,.08)}
.cash-summary-panel{border-top:0!important}
.cash-close-modern .btn-sale-detail{display:inline-flex!important;align-items:center;justify-content:center;width:36px!important;height:36px!important;min-height:36px!important;margin:0 9px 5px 0!important;padding:0!important;border:0!important;border-radius:9px!important;background:#f59e0b!important;color:#fff!important;box-shadow:0 5px 11px rgba(245,158,11,.25)!important}
.cash-close-modern .btn-sale-detail:hover{transform:translateY(-2px) rotate(-4deg);background:#e58a00!important;box-shadow:0 8px 15px rgba(245,158,11,.32)!important}
.cash-close-modern .btn-sale-detail i{font-size:1rem}
.cash-close-modern .sale-client-line{display:flex;align-items:center;gap:9px;min-height:40px;margin-top:7px;color:#24475f;font-size:.78rem;font-style:normal;line-height:1.25}
.cash-close-modern .sale-client-line .btn-sale-detail{flex:0 0 36px;margin:0!important;align-self:center}
.cash-close-modern .sale-client-line span{display:block;min-width:0;font-style:italic}
.cash-detail-toolbar{display:flex;justify-content:flex-end;padding:14px 14px 0}
.cash-close-modern .btn-toggle-all-details{display:inline-flex;min-height:42px!important;padding:9px 15px!important;align-items:center;gap:8px;border:0!important;border-radius:10px!important;color:#fff!important;background:#f59e0b!important;font-size:.82rem;font-weight:900!important;box-shadow:0 6px 14px rgba(245,158,11,.25)!important}
.cash-close-modern .btn-toggle-all-details:hover{transform:translateY(-2px);background:#e58a00!important}
.cash-close-modern .btn-toggle-all-details.is-open{background:#10b981!important;box-shadow:0 6px 14px rgba(16,185,129,.28)!important}
.cash-close-modern .btn-toggle-all-details.is-open:hover{background:#059669!important}
.cash-close-modern .btn-toggle-all-details:disabled{transform:none!important;opacity:.48;box-shadow:none!important}
.cash-close-description-field{margin:4px 0 12px;padding:12px;border:1px solid #c9dce8;border-radius:12px;background:#f7fbfd}
.cash-close-description-field label{margin-bottom:7px;color:#073e68;font-size:.82rem;font-weight:900;text-transform:uppercase;letter-spacing:.3px}
.cash-close-description-field textarea{min-height:84px!important;padding:10px 12px!important;resize:vertical}
.cash-close-description-field small{display:block;margin-top:5px;color:#607786;text-align:right;font-weight:700}
.cash-close-description-readonly{margin:8px 0 14px;padding:11px 13px;border-left:4px solid #075247;border-radius:8px;background:#e5f2ef;color:#173f3a;text-align:left}
.cash-close-description-readonly strong,.cash-close-description-readonly span{display:block}
.cash-close-description-readonly strong{margin-bottom:3px;font-size:.76rem;text-transform:uppercase;letter-spacing:.3px}
.cash-close-description-readonly span{white-space:pre-wrap;overflow-wrap:anywhere}
.cash-close-modern .btn-print-ticket{background:#159a87!important;color:#fff!important}
.products-title-row{display:flex;align-items:center;justify-content:space-between;gap:12px}
.products-title-row h5{margin:0}
.cash-close-modern .btn-sale-detail.is-open{
  background:#10b981!important;
  color:#fff!important;
  box-shadow:0 5px 13px rgba(16,185,129,.38)!important;
  transform:none!important;
}
.cash-close-modern .btn-sale-detail.is-open:hover{background:#059669!important;transform:translateY(-1px)!important}
.cash-close-modern .inline-sale-detail{
  width:100%!important;
  table-layout:auto!important;
}
.cash-close-modern .inline-sale-detail th,
.cash-close-modern .inline-sale-detail td{display:none!important}
.cash-close-modern .inline-sale-detail th:nth-child(1),
.cash-close-modern .inline-sale-detail td:nth-child(1),
.cash-close-modern .inline-sale-detail th:nth-child(2),
.cash-close-modern .inline-sale-detail td:nth-child(2),
.cash-close-modern .inline-sale-detail th:nth-child(3),
.cash-close-modern .inline-sale-detail td:nth-child(3),
.cash-close-modern .inline-sale-detail th:nth-child(5),
.cash-close-modern .inline-sale-detail td:nth-child(5),
.cash-close-modern .inline-sale-detail th:nth-child(10),
.cash-close-modern .inline-sale-detail td:nth-child(10),
.cash-close-modern .inline-sale-detail th:nth-child(11),
.cash-close-modern .inline-sale-detail td:nth-child(11),
.cash-close-modern .inline-sale-detail th:last-child,
.cash-close-modern .inline-sale-detail td:last-child{
  display:table-cell!important;
  width:auto!important;
  padding:10px 12px!important;
  white-space:normal!important;
}
.cash-close-modern .inline-sale-detail th:nth-child(2),
.cash-close-modern .inline-sale-detail td:nth-child(2){width:26%!important}
.cash-close-modern .inline-sale-detail th:nth-child(3),
.cash-close-modern .inline-sale-detail td:nth-child(3){width:30%!important;overflow-wrap:anywhere!important}
.cash-close-modern .inline-sale-detail{margin:10px 0 7px!important;border:1px solid #d8e6ee!important;border-radius:10px!important;background:#fff!important;box-shadow:0 5px 14px rgba(24,68,96,.07)}
.cash-close-modern .inline-sale-detail thead th{padding:8px!important;background:#fff3dc!important;color:#8b5800!important;border-bottom:1px solid #f2d49e!important;font-size:.72rem!important}
.cash-close-modern .inline-sale-detail tbody td{padding:9px!important;background:#fff!important;border-bottom:0!important;font-size:.76rem!important}
.cash-close-modern .clsVentas .table{margin-bottom:10px!important}
.cash-close-modern .clsVentas .payment-title{margin-top:8px!important;margin-bottom:8px!important}
.cash-close-modern .clsVentas .table>tbody>tr>td{padding-top:8px!important;padding-bottom:8px!important}
.cash-close-modern .clsVentas .table>thead>tr>th{padding-top:9px!important;padding-bottom:9px!important}
.cash-close-modern .clsVentas .payment-card+br+.table tfoot td{background:#e4eafd!important;color:#294b9e!important}
.cash-close-modern .tableResumen{border-spacing:0 3px!important}
.cash-close-modern .tableResumen tr td{padding-top:10px!important;padding-bottom:10px!important}
.cash-close-modern .tab-content,.cash-close-modern .tab-pane,.cash-close-modern .clsVentas{height:auto!important;max-height:none!important;overflow:visible!important}
.cash-close-modern .cash-detail-panel>.card{overflow:visible!important}
.cash-close-modern .cash-detail-panel .card-body{padding-bottom:16px!important;overflow:visible!important}
.cash-close-modern .movement-title{display:block;margin:8px 0;padding:10px 14px;border-radius:9px;font-size:.86rem;font-weight:850}
.cash-close-modern .movement-title+br{display:none}
.cash-close-modern .movement-credit{background:#fff0c9;color:#8b5a00;border:1px solid #f0d38c}
.cash-close-modern .movement-outflow{background:#ffe0e5;color:#a91f3c;border:1px solid #f3b9c4}
.cash-close-modern .movement-income{background:#ddf5e9;color:#08754a;border:1px solid #a9e2c7}
.cash-close-modern .credit-table>thead th,.cash-close-modern .credit-table>tfoot td{background:#fff0c9!important;color:#8b5a00!important;border-bottom-color:#f0d38c!important}
.cash-close-modern .outflow-table>thead th,.cash-close-modern .outflow-table>tfoot td{background:#ffe0e5!important;color:#a91f3c!important;border-bottom-color:#f3b9c4!important}
.cash-close-modern .income-table>thead th,.cash-close-modern .income-table>tfoot td{background:#ddf5e9!important;color:#08754a!important;border-bottom-color:#a9e2c7!important}

/* Diferenciación final: transferencia oscura e ingresos plomo */
.cash-close-modern .payment-transfer{background:#126c78!important;color:#fff!important;border-color:#126c78!important}
.cash-close-modern .payment-transfer+br+.table>thead th{background:#126c78!important;color:#fff!important;border-bottom-color:#0d5660!important}
.cash-close-modern .payment-transfer+br+.table tfoot td{background:#d6eef1!important;color:#0d5963!important}
.cash-close-modern .tableResumen .summary-transfer td{background:#126c78!important;color:#fff!important}
.cash-close-modern .movement-income{background:#64748b!important;color:#fff!important;border-color:#64748b!important}
.cash-close-modern .income-table>thead th{background:#64748b!important;color:#fff!important;border-bottom-color:#526174!important}
.cash-close-modern .income-table>tfoot td{background:#e2e8f0!important;color:#475569!important}
.cash-close-modern .tableResumen .summary-income td{background:#64748b!important;color:#fff!important}

/* El énfasis corresponde a los totales del listado, no al resumen lateral. */
.cash-close-modern .cash-detail-panel .table tfoot tr{height:48px!important}
.cash-close-modern .cash-detail-panel .table tfoot td{height:48px!important;padding-top:8px!important;padding-bottom:8px!important;vertical-align:middle!important;line-height:1!important}
.cash-close-modern .cash-detail-panel .table tfoot td.total{padding:8px 14px!important;font-size:1.02rem!important;font-weight:900!important;white-space:nowrap!important}
.cash-close-modern .cash-detail-panel .table tfoot td.total:last-child{width:145px!important;min-width:145px!important;font-size:1.08rem!important;text-align:center!important}
.cash-close-modern .cash-detail-panel .credit-table tfoot td.total{background:#f3c75f!important;color:#6f4700!important}
.cash-close-modern .cash-detail-panel .credit-table tfoot td{background:#f3c75f!important;color:#6f4700!important}
.cash-close-modern .cash-detail-panel .outflow-table tfoot td.total-salida{height:48px!important;padding:8px 14px!important;vertical-align:middle!important;line-height:1!important;white-space:nowrap!important;background:#f4a8b5!important;color:#941c34!important;font-size:1.02rem!important;font-weight:900!important}
.cash-close-modern .cash-detail-panel .outflow-table tfoot td.total-salida:last-child{width:145px!important;min-width:145px!important;font-size:1.08rem!important;text-align:center!important}
.cash-close-modern .admin-cash-control{margin:0 0 9px;padding:0;border:0;background:transparent}
.cash-close-modern .admin-cash-control label{display:block;margin-bottom:6px;color:#073e68;font-size:.85rem;font-weight:900;text-transform:uppercase;letter-spacing:.35px}
.cash-close-modern .admin-cash-control small{display:block;margin-top:6px;color:#52697a;font-weight:650}
.cash-close-modern .admin-user-dropdown{width:100%}
.cash-close-modern .admin-user-dropdown>.btn{width:100%;min-height:50px!important;padding:6px 11px!important;border:1px solid #9fc7dc!important;border-radius:11px!important;background:#fff!important;color:#123b59!important;text-align:left!important;box-shadow:0 3px 8px rgba(10,70,105,.07)}
.cash-close-modern .admin-user-current{display:flex;align-items:center;gap:11px;width:100%}
.cash-close-modern .admin-user-current>.fa-user-circle{font-size:1.45rem;color:#087da7}
.cash-close-modern .admin-user-current>span{display:flex;flex:1;min-width:0;flex-direction:column;line-height:1.15}
.cash-close-modern .admin-user-current strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.92rem}
.cash-close-modern .admin-user-current small{margin:4px 0 0;color:#71879a;font-size:.72rem;text-transform:lowercase}
.cash-close-modern .admin-user-arrow{color:#087da7;transition:transform .18s ease}
.cash-close-modern .admin-user-dropdown.show .admin-user-arrow{transform:rotate(180deg)}
.cash-close-modern .admin-user-menu{width:100%!important;margin-top:7px!important;padding:7px!important;border:1px solid #c8dce9!important;border-radius:13px!important;background:#fff!important;box-shadow:0 15px 32px rgba(10,50,78,.2)!important}
.cash-close-modern .admin-user-menu .dropdown-item{margin:3px 0;padding:8px 10px;border-radius:9px;color:#173d58;transition:background .16s ease,transform .16s ease}
.cash-close-modern .admin-user-menu .dropdown-item:hover{background:#e8f5fb;transform:translateX(2px)}
.cash-close-modern .admin-user-menu .dropdown-item.active{background:#0b729d;color:#fff}
.cash-close-modern .admin-user-option{display:flex;align-items:center;gap:10px}
.cash-close-modern .admin-user-avatar{display:grid;flex:0 0 35px;height:35px;place-items:center;border-radius:10px;background:#d9eff8;color:#08678d;font-weight:900}
.cash-close-modern .dropdown-item.active .admin-user-avatar{background:rgba(255,255,255,.2);color:#fff}
.cash-close-modern .admin-user-option-text{display:flex;flex:1;min-width:0;flex-direction:column}
.cash-close-modern .admin-user-option-text strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.88rem}
.cash-close-modern .admin-user-option-text small{margin:2px 0 0;color:#71879a;font-size:.7rem}
.cash-close-modern .dropdown-item.active .admin-user-option-text small{color:#d7eff8}

/* Transferencia y crédito: misma estructura visual de los otros medios de pago. */
.cash-close-modern .payment-transfer{background:#d7eef2!important;color:#075e6b!important;border-color:#8ccbd3!important}
.cash-close-modern .payment-transfer+br+.table>thead th{background:#d7eef2!important;color:#075e6b!important;border-bottom-color:#8ccbd3!important}
.cash-close-modern .payment-transfer+br+.table tfoot td{background:#cbe8ed!important;color:#075e6b!important}
.cash-close-modern .movement-credit{background:#fff0c9!important;color:#805300!important;border-color:#edcf7f!important}
.cash-close-modern .credit-table>thead th{background:#fff0c9!important;color:#805300!important;border-bottom-color:#edcf7f!important}
.cash-close-modern .cash-detail-panel .credit-table tfoot td,
.cash-close-modern .cash-detail-panel .credit-table tfoot td.total{background:#ffe8aa!important;color:#805300!important}

/* Tipografía más elegante y correspondencia de colores con el resumen. */
.cash-close-modern .cash-detail-panel .table{font-family:"Inter","Segoe UI",Arial,sans-serif!important;font-size:.86rem!important;letter-spacing:.05px}
.cash-close-modern .cash-detail-panel .table>tbody>tr>td{color:#173b56;font-weight:600!important}
.cash-close-modern .cash-detail-panel .table>thead th{font-weight:850!important;letter-spacing:.12px}
.cash-close-modern .cash-detail-panel .payment-title,
.cash-close-modern .cash-detail-panel .movement-title{font-weight:900!important;letter-spacing:.15px}
.cash-close-modern .cash-detail-panel .table tfoot td{font-weight:850!important}
.cash-close-modern .cash-detail-panel .table tfoot td.total,
.cash-close-modern .cash-detail-panel .table tfoot td.total-salida{font-weight:900!important}
.cash-close-modern .tableResumen .summary-transfer td{background:#cbe8ed!important;color:#075e6b!important}
.cash-close-modern .tableResumen .summary-outflow td{background:#f4a8b5!important;color:#941c34!important}

/* Orden uniforme de medios de pago: efectivo, Yape/Plin, tarjeta y transferencia. */
.cash-close-modern .tab-pane.clsVentas > .clsVentas{display:flex;flex-direction:column}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > *{order:100}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-cash{order:10}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-cash + br{order:11}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-cash + br + table{order:12}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-wallet{order:20}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-wallet + br{order:21}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-wallet + br + table{order:22}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-card{order:30}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-card + br{order:31}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-card + br + table{order:32}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-transfer{order:40}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-transfer + br{order:41}
.cash-close-modern .tab-pane.clsVentas > .clsVentas > .payment-transfer + br + table{order:42}

/* Acabado solicitado: salida rosada completa e ingresos en plomo suave. */
.cash-close-modern .cash-detail-panel .outflow-table tfoot td,
.cash-close-modern .cash-detail-panel .outflow-table tfoot td.total-salida{background:#f2a2b1!important;color:#981d38!important}
.cash-close-modern .movement-income{background:#94a3b8!important;color:#fff!important;border-color:#94a3b8!important}
.cash-close-modern .income-table>thead th{background:#94a3b8!important;color:#fff!important;border-bottom-color:#8493a8!important}
.cash-close-modern .income-table>tfoot td{background:#dfe5ec!important;color:#415166!important}
.cash-close-modern .tableResumen .summary-income td{background:#dfe5ec!important;color:#415166!important}
.cash-close-modern .tableResumen .summary-outflow td{background:#ffe0e5!important;color:#a91f3c!important}
</style>
