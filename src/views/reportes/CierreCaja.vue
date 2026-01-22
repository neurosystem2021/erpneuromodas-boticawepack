<template>
  <b-container fluid class="px-5 py-5">
    <b-row class="no-print">  
      <b-col cols="12"  lg="9" xl="9" class="no-print" order="2" order-lg="1">
        
        <b-card no-body>
          <center><h4>DETALLE CIERRE DE CAJA</h4></center>
          <b-tabs content-class="mt-3 mx-3" card fill pills>
            <b-tab title="Ventas" active >
              <div class="">

              
              <h5>Ventas Efectivo</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Recibo</th>
                      <th>Fecha</th>
                      <th>U. Proforma</th>
                      <th>U. Venta</th>
                      <th v-if="$route.query.noDetalle">Cliente</th>
                      <th v-else>Productos</th>
                      <th>Estado</th><th style="width:90px">Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  
                  <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ item.UsuarioProforma}}</td>
                    <td>{{ item.UsuarioVenta}}</td>
                    
                    <td>
                    
                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <!-- <th>Descripcion</th> -->
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
                            <!-- <td>{{ prod.Descripcion }}</td> -->
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

              <h5>Ventas Tarjeta</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                    <th>Recibo</th><th>Fecha</th>
                    <th>U. Proforma</th>
                    <th>U. Venta</th>
                    <th v-if="$route.query.noDetalle">Cliente</th>
                    <th v-else>Productos</th>
                    <th>Detalle</th>
                    <th>Estado</th>
                    <th>Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ item.UsuarioProforma}}</td>
                    <td>{{ item.UsuarioVenta}}</td>
                    
                    <td>
                    
                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <!-- <th>Descripcion</th> -->
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
                            <!-- <td>{{ prod.Descripcion }}</td> -->
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
                    <td class="total"><b>Total Tarjeta</b></td>
                    <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
                  </tr>
                </tfoot>
              </table>

              <h5>Ventas Yape/Plin</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Recibo</th>
                      <th>Fecha</th>
                      <th>U. Proforma</th>
                      <th>U. Venta</th>
                      <th v-if="$route.query.noDetalle">Cliente</th>
                      <th v-else>Productos</th>
                      <th>Detalle</th>
                      <th>Estado</th>
                      <th>Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ item.UsuarioProforma}}</td>
                    <td>{{ item.UsuarioVenta}}</td>
                    <td>

                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <!-- <th>Descripcion</th> -->
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
                            <!-- <td>{{ prod.Descripcion }}</td> -->
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
                    <td>S/. {{ item.Yape | redondear }}</td>
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

              <h5>Ventas Transferencia</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Recibo</th>
                      <th>Fecha</th>
                      <th>U. Proforma</th>
                      <th>U. Venta</th>
                      <th v-if="$route.query.noDetalle">Cliente</th>
                      <th v-else>Productos</th>
                      <th>Detalle</th>
                      <th>Estado</th>
                      <th>Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ item.UsuarioProforma}}</td>
                    <td>{{ item.UsuarioVenta}}</td>
                    <td>

                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <!-- <th>Descripcion</th> -->
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
                            <!-- <td>{{ prod.Descripcion }}</td> -->
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
                    <td>{{ item.TransferenciaDesc }}</td>
                    <td>{{ estadoVenta(item) }}</td>
                    <td>S/. {{ item.Mastercard | redondear }}</td>
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

              <h5>Ventas a Credito</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                    <th>Recibo</th>
                    <th>Fecha</th>
                    <th>U. Proforma</th>
                    <th>U. Venta</th>
                    <th v-if="$route.query.noDetalle">Cliente</th>
                    <th v-else>Productos</th>
                    <th>Estado</th><th>Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
                    <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>{{ item.UsuarioProforma}}</td>
                    <td>{{ item.UsuarioVenta}}</td>
                    <td>
                      
                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <!-- <th>Descripcion</th> -->
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
                            <!-- <td>{{ prod.Descripcion }}</td> -->
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

              <b-row>
              <b-col v-if="totalIngresos">
                <h5>Ingresos</h5><br>
                <table class="table b-table table-sm">
                  <thead>
                    <tr class="thDetalle">
                      <th>Usuario</th><th>ID</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in ingresos" :key="index">
                      <td>{{ item.UsuarioReg }}</td>
                      <td>{{ item.IdCajaBanco }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Concepto }}</td>
                      <td>S/. {{ item.Importe | redondear }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
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
                      <th>Usuario</th><th>ID</th><th>Fecha</th><th>Detalle</th><th>Salida</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(item, index) in salidas" :key="index">
                      <td>{{ item.UsuarioReg }}</td>
                      <td>{{ item.IdCajaBanco }}</td>
                      <td>{{ item.FechaDoc }}</td>
                      <td>{{ item.Concepto }}</td>
                      <td>S/. {{ item.Importe | redondear }}</td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3"></td>
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
            </div>
            </b-tab>
            <b-tab title="Productos" >
              <div class="scrollVertical">
              <h5>Resumen Por Producto
              </h5>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Cantidad Vendida</th>
                    <th>Monto</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(produtos,index) in reportePorProveedor" :key="index">
                    <td> {{ produtos.IdProducto }} </td>
                    <td> {{ produtos.Producto }} </td>
                    <td> {{ produtos.total }} </td>
                    <td> {{ parseFloat(produtos.Precio).toFixed(2) }} </td>
                    
                  </tr>
                  
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2"></td>
                    <td class="total"><b>Total Efectivos</b></td>
                    <td class="total"><b>S/. {{
                      parseFloat( reportePorProveedor.reduce((total,prod)=>{
                            return total + parseFloat(prod.Precio)
                        },0)).toFixed(2)
                    }}</b></td>
                  </tr>
                </tfoot>
              </table>
            </div>
            </b-tab>
          </b-tabs>
        </b-card>
      </b-col>
      <b-col 
          cols="12" 
          lg="3" 
          xl="3" 
          class="card elevation-2 sticky-sidebar" 
          order="1" 
          order-lg="2"
        >
        <div class="alinear">
          <center>
          </center>
          <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
          <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
          <div v-if="cierre.UsuarioReg" style="font-size: 1.5em; padding: 5%;">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
        </div>
        <h2 class="text-center">Mi Cierre de Caja</h2> 
        <b-row  align-h="end" class=" no-print">
        
        <b-col cols="12">
           <b-row>
              <b-col cols="12" md="6" class="my-1">
             <b-button-group>
               <b-button variant="danger" @click="cerrarCaja" v-if="!this.$route.params.id"><i class="fa fa-unlock mx-1" aria-hidden="true"></i>
                Cerrar Caja</b-button>
              </b-button-group>
               </b-col>
              <b-col cols="12" md="6" class="my-1">
              <b-button-group>
                <cierres-caja></cierres-caja>
              </b-button-group>
              </b-col>
              <b-col cols="12" md="6" class="my-1">
              <b-button-group>
                <b-button variant="primary" @click="imprimir()"><i class="fa fa-print mx-1" aria-hidden="true"></i> Imprimir Detallado</b-button>
                
              </b-button-group>
               </b-col>
              <b-col cols="12" md="6" class="my-1">
              <b-button-group>
                <b-button variant="success" @click="imprimirResumen()">Imprimir</b-button>
              </b-button-group>
              </b-col>
            </b-row>
          </b-col>
        <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
          Turno
          
          <b-form-select v-model="turno" :options="turnos" />
          
        </b-col>
        <b-col cols="12" v-if="!this.$route.params.id">
          Vendedor:
          <b-select 
              :options="lstVendedor"
              value-field="Usuario"
              text-field="NombreUsuario"
              @input="recargarCierre"
              v-model="IdVendedor"
              :disabled="bloquearVendedor"
          ></b-select>
        </b-col>
            <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
              <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
              <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
              <!-- <div v-if="cierre.UsuarioReg" style="font-size: 2em;">Empleado: <b>{{ cierre.UsuarioReg }}</b></div> -->
              
            </b-col>
            <b-col cols="12">
        <table class="table b-table table-sm table-striped table-bordered tableResumen">
          <tbody>
            <tr>
              <td>Gran Total S/.</td>
              <td>{{ granTotal | redondear }}</td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
            <tr>
              <td>Total  S/.</td>
              <td>{{ totalV | redondear }}</td>
            </tr>
            <tr>
              <td colspan="2"></td>
            </tr>
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
          
            <tr>
              <td colspan="2"></td>
            </tr>
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
      
      <b-col cols="12" order="3">

        <b-alert variant="success" class="text-center" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
      </b-col>
    
    </b-row>

    <b-row class="si-print" >
        <h2>Cierre Caja</h2>
      <table class="table table-striped table-bordered">
              <tbody>
                <tr>
                  <td>Gran Total S/.</td>
                  <td>{{ granTotal | redondear }}</td>
                </tr>
                <tr>
                  <td colspan="2"></td>
                </tr>
                <tr>
                  <td>Total  S/.</td>
                  <td>{{ totalV | redondear }}</td>
                </tr>
                <tr>
                  <td colspan="2"></td>
                </tr>
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
              
                <tr>
                  <td colspan="2"></td>
                </tr>
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
    </b-row>

    <b-row class="si-print" v-if="imprimirCierre">
       <h1> <center>Cierre Caja</center></h1>
       <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
        <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>  
       <h6 v-if="cierre.UsuarioReg" style="font-size: 1.5em; padding: 5%;">Empleado: <b>{{ cierre.UsuarioReg }}</b></h6>
        
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
                            <th>Producto</th>
                            <!-- <th>Descripcion</th> -->
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
                            <!-- <td>{{ prod.Descripcion }}</td> -->
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
                      <!-- <th>Descripcion</th> -->
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
                      <!-- <td>{{ prod.Descripcion }}</td> -->
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
                      <!-- <th>Descripcion</th> -->
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
                      <!-- <td>{{ prod.Descripcion }}</td> -->
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
                      <!-- <th>Descripcion</th> -->
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
                      <!-- <td>{{ prod.Descripcion }}</td> -->
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
              <td>S/. {{ item.Mastercard | redondear }}</td>
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
                      <!-- <th>Descripcion</th> -->
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
                      <!-- <td>{{ prod.Descripcion }}</td> -->
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
              <td><b>Total Credito</b></td>
              <td><b>S/. {{ totalVentasCredito | moneda | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <b-row>
        <b-col cols="12" v-if="totalIngresos">
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
        <b-col cols="12" v-if="totalSalidas">
          <h5>Salidas</h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr class="thDetalle">
                <th>UsuarioReg</th><th>ID</th><th>Fecha</th><th>Detalle</th><th>Salida</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in salidas" :key="index">
                <td>{{ item.UsuarioReg }}</td>
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
          <b-col cols="12">

            <b-alert variant="success" class="text-center" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
          </b-col>
      </b-row>
           
    </b-row>

    <b-row class="printResumen" v-if="imprimirResumenProducto">
      RESUMEN DE PEDIDOS:
      <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>ID</th>
              <th>Producto</th>
              <th>Cantidad Vendida</th>
              <th>Monto</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(produtos,index) in reportePorProveedor" :key="index">
               <td> {{ produtos.IdProducto }} </td>
               <td> {{ produtos.Producto }} </td>
               <td> {{ produtos.total }} </td>
               <td> {{ parseFloat(produtos.Precio).toFixed(2) }} </td>
              
            </tr>
            
          </tbody>
          <tfoot>
            <tr>
              <td colspan="2"></td>
              <td class="total"><b>Total Efectivos</b></td>
              <td class="total"><b>S/. {{
                parseFloat( reportePorProveedor.reduce((total,prod)=>{
                      return total + parseFloat(prod.Precio)
                  },0)).toFixed(2)
              }}</b></td>
            </tr>
          </tfoot>
        </table>
    </b-row>
  </b-container>

</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";
import Proformas from "@/components/ProformaLista.vue";

export default {
  async mounted() {
    await this.getListadoVendedor();
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id);
      this.loadData();
    } else {
      this.IdVendedor = sessionStorage.getItem("User").toLocaleLowerCase();
      let usuario = sessionStorage.getItem("User");
      let IdPerfil = sessionStorage.getItem("IdPerfil");
      let Perfil = ["1", "16", 1, 16];
      this.bloquearVendedor = !Perfil.includes(IdPerfil);
      await this.getVendedor();
    }
  },
  data() {
    return {
      IdVendedor: "",
      API: config.API,
      lstVendedor: [],
      ventas: [],
      ingresos: [],
      salidas: [],
      ingresosTarjeta: [],
      salidasTarjeta: [],
      adelantos: [],
      turno: null,
      imprimirResumenProducto: false,
      bloquearVendedor: false,
      imprimirCierre: false,
      turnos: [
        { value: null, text: "Seleccione un Turno" },
        { value: 1, text: "Mañana" },
        { value: 2, text: "Tarde" },
        { value: 3, text: "Noche" }
      ],
      cierre: {},
      vendedor: {},
      reportePorProveedor: []
    };
  },
  computed: {
    getFechaCierre() {
      //return this.$route.params.id ?
    },
    totalV() {
      return (
        (this.totalIngresos ? this.totalIngresos : 0) +
        (this.totalVentasEfectivo ? this.totalVentasEfectivo : 0) +
        (this.totalVentasVisa ? this.totalVentasVisa : 0) +
        (this.totalVentasMastercard ? this.totalVentasMastercard : 0) +
        (this.totalVentasTransferencia ? this.totalVentasTransferencia : 0)
      );
    },
    granTotal() {
      console.log(this.totalAdelantos);
      return (
        (this.totalIngresos ? this.totalIngresos : 0) +
        (this.totalVentasEfectivo ? this.totalVentasEfectivo : 0) +
        (this.totalVentasVisa ? this.totalVentasVisa : 0) +
        (this.totalVentasMastercard ? this.totalVentasMastercard : 0) +
        (this.totalVentasTransferencia ? this.totalVentasTransferencia : 0) -
        ((this.totalSalidas ? this.totalSalidas : 0) +
          (this.totalAdelantos ? this.totalAdelantos : 0))
      );
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
    totalIngresosTarjeta() {
      let total = 0;
      this.ingresosTarjeta.forEach(ingresoTar => {
        if (!ingresoTar.Anulado) total += ingresoTar.Importe;
      });
      return total;
    },
    totalSalidasTarjeta() {
      let total = 0;
      this.salidasTarjeta.forEach(salidaTar => {
        if (!salidaTar.Anulado) total += salidaTar.Importe;
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
        if (!venta.Anulado) total += venta.Yape;
      });
      return total;
    },
    ventasMastercard() {
      return this.ventas.filter(venta => {
        return venta.Yape > 0 && !venta.EsCredito;
      });
    },
    totalVentasTransferencia() {
      let total = 0;
      this.ventasTransferencia.forEach(venta => {
        if (!venta.Anulado) total += venta.Mastercard;
      });
      return total;
    },
    ventasTransferencia() {
      return this.ventas.filter(venta => {
        return venta.Mastercard > 0 && !venta.EsCredito;
      });
    },
    totalVentasCredito() {
      let total = 0;
      this.ventasCredito.forEach(venta => {
        if (!venta.Anulado) total += venta.Total;
      });
      return parseFloat(total).toFixed(2);
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
    recargarCierre(vendedor) {
      let v = this.lstVendedor.filter(v => v.Usuario == vendedor)[0];
      if (v) {
        this.setVendedor(v);
        this.loadData();
      }
    },
    async getListadoVendedor() {
      await this.axios(`${this.API}lstusuarios/listado`).then(res => {
        if (res.data) {
          this.lstVendedor = res.data;
        }
      });
    },
    async getVendedor() {
      let usuario = sessionStorage.getItem("User").toLocaleLowerCase();
      await this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          // this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
          // this.$refs.inputVendedor.$refs.input.disabled = true

          if (response.data.IdUsuarioPerfil !== 1) {
            // this.$refs.inputVendedor.$refs.input.disabled = true
          }
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    imprimirResumen() {
      this.imprimirCierre = false;
      this.imprimirResumenProducto = true;
      this.$nextTick(() => {
        setTimeout(() => {
          window.print();
        }, 100);
      });
    },
    imprimir() {
      this.imprimirCierre = true;
      this.imprimirResumenProducto = false;
      this.$nextTick(() => {
        setTimeout(() => {
          window.print();
        }, 100);
      });
    },
    async loadData() {
      await Promise.all([
        this.getVentas(),
        this.getIngresos(),
        this.getSalidas(),
        this.generarReporte()
      ]);
      // console.log(this.ventas);
    },
    generarReporte() {
      let productos = [];
      this.ventas.filter(ventas => !ventas.Anulado).forEach(venta => {
        venta.productos.forEach(producto => {
          productos.push(producto);
        });
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
          total: total,
          Precio: Precio
        });
      });
      // console.log(prod);
    },
    setVendedor(obj) {
      obj.Usuario = obj.Usuario.toLowerCase();

      this.vendedor = obj;
      this.loadData();
    },
    async cerrarCaja() {
      let IdPerfil = sessionStorage.getItem("IdPerfil");

      if (IdPerfil != 16) {
        this.$toasted.show("Solo Caja puede cerrar.");
        return;
      }

      if (!this.turno) {
        this.$toasted.show("Seleccione un Turno");
        return;
      }
      let r = confirm("¿Está seguro de cerrar la caja?");
      if (r) {
        await this.axios
          .post(this.API + "cierrecaja", {
            idTurno: this.turno,
            user: sessionStorage.getItem("User"),
            // user:         this.vendedor.Usuario,
            ingresos: parseFloat(this.granTotal + this.totalSalidas).toFixed(2),
            salidas: this.totalSalidas,
            total: this.granTotal,
            totalVentas: this.totalVentas,
            granTotal: this.granTotal
          })
          .then(res => {
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
    async loadDetalles() {
      if (this.ventas.length == 0) {
        return;
      }
      await this.axios
        .get(this.API + "ventas/detalle", {
          params: {
            idDocVentas: this.ventas.map(item => item.idDocVenta)
          }
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
          this.ingresos = res.data.filter(ing => ing.EsTarjeta == 0);
          this.ingresosTarjeta = res.data.filter(ing => ing.EsTarjeta == 1);
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
          this.salidas = res.data.filter(sal => sal.Tipo == 1);
          this.salidasTarjeta = res.data.filter(sal => sal.Tipo == 0);
        })
        .catch(err => {
          console.log(err);
        });
    },
    async getAdelantos() {
      let params = {
        idDocVentas: this.ventas.map(v => v.idDocVenta)
      };
      await this.axios
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
    async getCierre(id) {
      await this.axios
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
.printResumen {
  display: none;
}
.scrollVertical {
  max-height: 800px;
  overflow-y: auto;
  overflow-x: auto;
}
@media print {
  .printResumen {
    max-width: 890px;
    /* font-size: 2em; */
    display: block;
  }
  .no-print {
    display: none;
  }
  .si-print {
    max-width: 890px;
    display: block;
    font-size: 1.2em;
  }
  .table {
    max-width: 890px;
    /* font-size: 0.8em; */
  }
  th {
    background: #ffffff !important;
    color: #000000 !important;
    border: 1px solid #ffffff;
  }
}

.sticky-sidebar {
  position: sticky;
  top: 20px; /* separación desde el top mientras haces scroll */
  z-index: 99;
}
</style>
