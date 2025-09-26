<template>
  
  <b-container fluid class="px-5 py-5">
    <b-row class="no-print">
      <b-col cols="9" class="no-print">
        
        <b-card no-body>
          <b-tabs card>
            <b-tab title="Ventas" active >
              <b-card-text>
                <div class="details px-5" >

                  <hr class="no-print">
                  <b-row class="no-print">
                  <h5>Resumen Productos</h5><br>
                  <table class="table b-table table-sm">
                      <thead>
                        <tr>
                          <th>Producto</th>
                          <th>Isla</th>
                          <th>Cara</th>
                          <th>Cantidad</th>
                          <th>Monto</th>
                        </tr>
                      </thead>
                      <tbody>
                          <tr v-for="(productos,index) in productosVendidos" :key="index">
                            <td>{{productos.Producto}}</td>
                            <td>{{productos.Isla}}</td>
                            <td>{{
                              productos.Isla ? 
                            productos.Cara==1 ? 'Cara A' : 'Cara B'
                            : ''
                            }}</td>
                            <td>{{productos.CantidadVendida}}</td>
                            <td>{{productos.MontoVendido}}</td>
                          </tr>
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="2"></td>
                          <td class="total"><b>Total Efectivo</b></td>
                          <td class="total"><b>{{ productosVendidos.reduce((sum,productos)=>{
                            return sum + parseFloat(productos.CantidadVendida);
                          },0) | redondear}}</b></td>
                          <td class="total"><b>S/. {{ productosVendidos.reduce((sum,productos)=>{
                            return sum + parseFloat(productos.MontoVendido);
                          },0) | redondear}}</b></td>
                        </tr>
                      </tfoot>
                  </table>
                  </b-row>
                
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
              </div>

                <b-alert variant="success" class="text-center no-print" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
            
                          </b-card-text>
                        </b-tab>
                        <b-tab title="Productos" class="px-5">
                          <b-card-text>
                            <b-row class="print">
                              <b-col cols="12">
                            
                                      <table class="table tbl-historico" size="sm" >
                                          <thead>
                                            <tr>
                                              <th width="5px">PRODUCTO</th>
                                              <th width="1px">ISLA</th>
                                              <th width="1px">CARA</th>
                                              <th width="5px">V. I.</th>
                                              <th width="5px">V. F</th>              
                                              <th width="5px">VENTA</th>
                                            </tr>
                                          </thead>
                                          <tbody>
                                              <tr v-for="(resumen,index) in resumenHistorial" :key="index">
                                                <td width="5px">{{resumen.Producto}}</td>
                                                <td width="1px"> {{resumen.Isla}}</td>
                                                <td width="1px">Cara {{resumen.Cara}}</td>
                                                <td width="5px">{{parseFloat(resumen.ValorInicial).toFixed(4)}}</td>
                                                <td width="5px">{{parseFloat(resumen.Restante).toFixed(4)}}</td>
                                                <td width="5px">{{parseFloat(resumen.VentaTotal).toFixed(4)}}</td>
                                              </tr>
                                          </tbody>
                                      </table>
                              </b-col>
                              <b-col cols="12">

                                <b-alert variant="success" class="text-center no-print" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
                              </b-col>
                          </b-row>
              </b-card-text>
            </b-tab>
            <!-- <b-tab title="Operaciones" class="px-5">
              <b-card-text>Tab contents 2</b-card-text>
            </b-tab> -->
          </b-tabs>
        </b-card>
      


    </b-col>
      <b-col cols="3" class="card elevation-2">
        <div class="alinear">
          <center>
          </center>
          <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
          <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
          <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
        </div>
        <h2 class="text-center">Mi Cierre de Caja</h2> 
      <br>
      <b-row  align-h="end" class="no-print">
        <b-col cols="12">
           
            <b-button-group>
              <b-button variant="danger" @click="cerrarCaja" v-if="!this.$route.params.id"><i class="fa fa-unlock mx-1" aria-hidden="true"></i>
                Cerrar Caja</b-button>
            </b-button-group>
            <b-button-group>
              <cierres-caja></cierres-caja>
            </b-button-group>
            <b-button-group>
              <b-button variant="primary" @click="imprimir"><i class="fa fa-print mx-1" aria-hidden="true"></i> Imprimir</b-button>
            </b-button-group>
          </b-col>
        <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
            Turno
            
              <b-form-select v-model="turno" :options="turnos" />
           
        </b-col>
        <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
          <label v-if="!this.$route.params.id">Vendedor:</label>
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
          
      </b-row>
      <b-row class="px-3">
       
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
        
        <b-row class="print ocultarNoPrint">
           <b-col cols="12">
            <table class="table tbl-historico" size="sm" >
              <thead>
                <tr>
                  <th width="5px">PRODUCTO</th>
                  <th width="1px">ISLA</th>
                  <th width="1px">CARA</th>
                  <th width="5px">V. I.</th>
                  <th width="5px">V. F</th>              
                  <th width="5px">VENTA</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(resumen,index) in resumenHistorial" :key="index">
                  <td width="5px">{{resumen.Producto}}</td>
                  <td width="1px"> {{resumen.Isla}}</td>
                  <td width="1px">Cara {{resumen.Cara}}</td>
                  <td width="5px">{{parseFloat(resumen.ValorInicial).toFixed(4)}}</td>
                  <td width="5px">{{parseFloat(resumen.Restante).toFixed(4)}}</td>
                  <td width="5px">{{parseFloat(resumen.VentaTotal).toFixed(4)}}</td>
                </tr>
              </tbody>
            </table>
            </b-col>
            </b-row>
      </b-row>

      
      </b-col>
    
    </b-row>
    
    <!-- si-print -->
    <b-row class="si-print">
      <b-col class="text-center" cols="12">
        <h5>INVERSIONES KATIMILA E.I.R.L</h5>
        <h5>20605427147</h5>
        <h6>Fecha Del Sistema: {{ FechaActual }}</h6>
      </b-col>
      <hr>
      <b-col cols="12" class="text-center">
        <h5>Cierre de Caja</h5>
        <h6 class="text-center" v-if="cierre.FechaCierre">Fecha Cierre: {{ cierre.FechaCierre }}</h6>
        <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>
        <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
        
      </b-col>
      <hr>  

       <b-col cols="12">
                
                    <center>
                      <h3>CONTROMETROS</h3>
                    </center>
                    <div class="" v-for="(resumen,index) in resumenHistorial" :key="index">
                        <hr>
                          <h5>Isla: {{resumen.Isla}} - Cara: {{resumen.Cara}}</h5>
                        <hr>
                        <h5>{{resumen.Producto}}</h5>
                        <p>L.F. {{parseFloat(resumen.Restante).toFixed(4)}} </p>
                        <p>L.I. {{parseFloat(resumen.ValorInicial).toFixed(4)}} </p>
                        <p>Dif. {{parseFloat(resumen.VentaTotal).toFixed(4)}} </p>
                    </div>
                      <hr>
                  </b-col>

                <b-row>
                      <div class="page-break"></div>
                  <b-col cols="12">

                  
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
                </b-col>


                <b-col cols="12">

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
                </b-col>

                

                <b-col cols="12">

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
                </b-col>

                <b-col cols="12">

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
                </b-col>

                <b-col cols="12">

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
                </b-col>

                <b-col cols="12">

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
                </b-col>

                <b-col cols="12">

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
                </b-col>


                
                  <b-col v-if="totalIngresos" cols="12">
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
                  <b-col v-if="totalSalidas" cols="12">
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
    </b-row>

   
  </b-container>
</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";

export default {
  async mounted() {
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
      await this.obtenerHistorioGalones();
    } else {
      await this.obtenerHistorioGalonesCierre();
    }
    // console.log(this.historial);
    // console.log(this.ventas);
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
        { key: "Isla", label: "Isla" },
        { key: "Cara" },
        { key: "ValorInicial", label: "L. I." },
        { key: "VentaTotal", label: "V." },
        { key: "Restante", label: "Dif." }
      ]
    };
  },
  computed: {
    FechaActual() {
      return this.$moment().format("YYYY-MM-DD H:mm:ss");
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
      console.log("historial", this.historial);
      console.log("ventas", this.ventas);
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
    // recalcularHistorial() {
    //   let acumulados = {}; // Guardará los valores iniciales corregidos

    //   // Agrupar el historial por Surtidor - Producto - Cara
    //   this.historial.forEach(element => {
    //     let clave = `${element.Isla}-${element.IdProducto}-${
    //       element.Cara
    //     }`;

    //     // Si la clave no existe en acumulados, registrar el primer valor inicial
    //     if (!acumulados.hasOwnProperty(clave)) {
    //       acumulados[clave] = element.Galones;
    //     }

    //     // Restar ventas de turnos anteriores (IdCierre !== null)
    //     let ventasAnteriores = this.ventas
    //       .filter(
    //         v =>
    //           v.Isla == element.Isla &&
    //           v.IdProducto == element.IdProducto &&
    //           v.Cara == element.Cara &&
    //           v.IdCierre !== null
    //       ) // Solo ventas de turnos cerrados
    //       .reduce((sum, v) => sum + v.Total, 0);

    //     acumulados[clave] -= ventasAnteriores;
    //   });

    //   // Generar el resultado final agrupado
    //   this.resumenHistorial = Object.keys(acumulados).map(clave => {
    //     let [Isla, IdProducto, Cara] = clave.split("-").map(Number);

    //     // Obtener el producto y datos generales del historial
    //     let ref = this.historial.find(
    //       h =>
    //         h.Isla == Isla &&
    //         h.IdProducto == IdProducto &&
    //         h.Cara == Cara
    //     );

    //     // Obtener total vendido en el turno actual (IdCierre === null)
    //     let ventasActuales = this.ventas
    //       .filter(
    //         v =>
    //           v.Isla == Isla &&
    //           v.IdProducto == IdProducto &&
    //           v.Cara == Cara &&
    //           v.IdCierre === null
    //       ) // Solo ventas del turno actual
    //       .reduce((sum, v) => sum + v.Total, 0);

    //     let saldoFinal = acumulados[clave] - ventasActuales;

    //     return {
    //       Isla,
    //       IdProducto,
    //       Cara,
    //       Producto: ref.Producto,
    //       ValorInicial: acumulados[clave], // VI después de ventas anteriores
    //       VentaTotal: ventasActuales, // Lo vendido en el turno actual
    //       Restante: saldoFinal // VI - VentaTotal del turno actual
    //     };
    //   });
    // },
    recalcularHistorial() {
      let resumen = {};

      // Procesamos el historial (almacenar el valor inicial)
      this.historial.forEach(item => {
        const key = `${item.IdIsla}-${item.IdProducto}-${item.Cara}`;

        if (!resumen[key]) {
          resumen[key] = {
            Producto: item.Producto,
            Isla: item.Isla,
            Cara: item.Cara,
            ValorInicial: parseFloat(item.Galones) || 0,
            VentaTotal: 0, // Solo sumaremos ventas sin IdCierre
            Restante: 0
          };
        }
      });

      // Primero restamos las ventas con IdCierre del ValorInicial
      this.ventas.forEach(venta => {
        const key = `${venta.IdIsla}-${venta.IdProducto}-${venta.Cara}`;

        if (venta.IdCierre && resumen[key]) {
          resumen[key].ValorInicial -= parseFloat(venta.Total) || 0;
        }
      });

      // Luego sumamos las ventas SIN IdCierre a VentaTotal
      this.ventas.forEach(venta => {
        const key = `${venta.IdIsla}-${venta.IdProducto}-${venta.Cara}`;

        if (!venta.IdCierre && resumen[key]) {
          resumen[key].VentaTotal += parseFloat(venta.Total) || 0;
        }
      });

      // Calculamos la diferencia
      Object.values(resumen).forEach(item => {
        item.Restante = item.ValorInicial - item.VentaTotal;
      });

      // Asignamos los datos al array que se usa en la vista
      this.resumenHistorial = Object.values(resumen);
    },
    recalcularHistorialVer() {
      let resumen = {};
      let idCierreActual = parseInt(this.$route.params.id); // ID de cierre actual

      // Inicializar el resumen con los datos del historial
      this.historial.forEach(item => {
        const key = `${item.IdIsla}-${item.IdProducto}-${item.Cara}`;

        if (!resumen[key]) {
          resumen[key] = {
            Producto: item.Producto,
            Cara: item.Cara,
            ValorInicial: parseFloat(item.Galones) || 0,
            VentaTotal: 0, // Se llenará con la venta del cierre actual
            Restante: 0
          };
        }
      });

      // Procesamos las ventas
      this.ventas.forEach(venta => {
        const key = `${venta.IdSurtidor}-${venta.IdProducto}-${venta.Cara}`;

        if (resumen[key]) {
          if (venta.IdCierre === idCierreActual) {
            // Solo si es del IdCierre actual, se asigna a VentaTotal
            resumen[key].VentaTotal = parseFloat(venta.Total) || 0;
          } else {
            // Si no es del cierre actual, se resta del ValorInicial
            resumen[key].ValorInicial -= parseFloat(venta.Total) || 0;
          }
        }
      });

      // Calcular el restante
      Object.keys(resumen).forEach(key => {
        resumen[key].Restante =
          resumen[key].ValorInicial - resumen[key].VentaTotal;
      });

      // Convertimos el objeto en un array para usar en v-for
      this.resumenHistorial = Object.values(resumen);
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

th {
  background: #1a237e;
  color: #ffff;
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
.details {
  max-height: 79vh;
  overflow-y: auto;
}

.ocultarNoPrint {
  display: none;
}
.alinear * {
  text-align: left !important;
}
.tableResumen {
  width: 100%;
  font-weight: bold;
}
.si-print {
  display: none;
}
@media print {
  .no-print {
    display: none;
  }
  .si-print {
    max-width: 350px;
    display: block;
  }
  .tableResumen {
    width: 100%;
    border-collapse: collapse;
  }
  .tbl-historico {
    width: 100%;
    font-size: 0.5rem;
    border-collapse: collapse;
  }

  .tbl-historico th,
  .tbl-historico td {
    padding: 2px 4px;
    text-align: left;
    white-space: nowrap;
  }

  .ocultarNoPrint {
    display: block;
    font-size: 0.6rem;
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
  .page-break {
    page-break-before: always;
  }
}
</style>
