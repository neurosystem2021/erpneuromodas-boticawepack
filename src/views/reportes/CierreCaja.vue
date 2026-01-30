<template>
  <b-container fluid class="px-5 py-5">
    <b-row class="no-print">  
      <b-col cols="9" class="no-print">
        <b-card no-body>
          <b-tabs content-class="mt-3 mx-3" card fill pills>
            <b-tab title="Ventas" active >
              <h5>Ventas Efectivo</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
                      <th v-if="$route.query.noDetalle">Cliente</th>
                      <th v-else>Productos</th>
                      <th>Estado</th><th style="width:90px">Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  
                  <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
                    <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>
                    
                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Codigo</th>
                            <th>Producto</th>
                            <th>Descripcion</th>
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
                    <td colspan="4"></td>
                    <td class="total"><b>Total Efectivo</b></td>
                    <td class="total"><b>S/. {{ totalVentasEfectivo | redondear }}</b></td>
                  </tr>
                </tfoot>
              </table>

              <h5>Ventas Tarjeta</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
              <th v-if="$route.query.noDetalle">Cliente</th>
              <th v-else>Productos</th>
              <th>Detalle</th>
<th>Estado</th>
<th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>
              
                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
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
              <td colspan="5"></td>
              <td class="total"><b>Total Tarjeta</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <h5>Ventas Yape/Plin</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
                <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
                <th v-if="$route.query.noDetalle">Cliente</th>
                <th v-else>Productos</th>
                <th>Detalle</th>
<th>Estado</th>
<th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
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
              <td colspan="5"></td>
              <td class="total"><b>Total Yape/Plin</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
        <h5>Ventas Transferencia</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
                <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
                <th v-if="$route.query.noDetalle">Cliente</th>
                <th v-else>Productos</th>
                <th>Detalle</th>
<th>Estado</th>
<th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
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
              <td>S/. {{ item.Yape | redondear }}</td>
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
        <h5>Ventas a Credito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr class="thDetalle">
              <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
              <th v-if="$route.query.noDetalle">Cliente</th>
              <th v-else>Productos</th>
              <th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>
                
                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Descripcion</th>
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
            </b-tab>
            <b-tab title="Productos" >
              <h5>Resumen Por Producto</h5><br>
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
               <td> {{ produtos.Precio }} </td>
              
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
            </b-tab>
          </b-tabs>
        </b-card>
      </b-col>
      <b-col cols="3" class="card elevation-2">
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
           
            <b-button-group>
              <b-button variant="danger" @click="cerrarCaja" v-if="!this.$route.params.id"><i class="fa fa-unlock mx-1 mt-1" aria-hidden="true"></i>
                Cerrar Caja</b-button>
            </b-button-group>
            <b-button-group class="my-1">
              <cierres-caja></cierres-caja>
            </b-button-group>
            <b-button-group class="my-1">
              <b-button variant="primary" @click="imprimir(1)"><i class="fa fa-print mx-1 mt-1" aria-hidden="true"></i> Imprimir</b-button>
            </b-button-group>
            <b-button-group class="my-1">
              <b-button variant="primary" @click="imprimir(2)"><i class="fa fa-print mx-1 mt-1" aria-hidden="true"></i> Productos</b-button>
            </b-button-group>
          </b-col>
        <b-col cols="12" v-if="!this.$route.params.id" class="mt-2">
           <b-input-group prepend="Turno" class="mt-2">
              <b-form-select v-model="turno" :options="turnos" />
          </b-input-group>
           <b-input-group prepend="Vendedor" class="mt-2" v-if="idPerfil==1">
                <autocomplete
                    required="required"
                    ref="inputVendedor"
                    :url="API + 'usuarios'"
                    :customParams="{idPerfil: idPerfil}"
                    anchor="NombreUsuario"
                    label="writer"
                    :on-select="setVendedorManual" 
                    :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
          </b-input-group>
           <b-input-group prepend="Vendedor" class="mt-2" v-else>
                <input type="text" disabled v-model="vendedor.Usuario" class="form-control" style="text-transform: uppercase;">
                <!-- <autocomplete

                    required="required"
                    ref="inputVendedor"
                    :url="API + 'usuarios'"
                    :customParams="{idPerfil: idPerfil}"
                    anchor="NombreUsuario"
                    label="writer"
                    :on-select="setVendedorManual" 
                    :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                    :classes="{input: 'form-control'}">
                </autocomplete> -->
          </b-input-group>
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
            </tr>
            <tr>
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

    <b-row class="si-print">
       <h1> <center>Cierre Caja</center></h1>
       <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
        <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</h6>  
       <h6 v-if="cierre.UsuarioReg" style="font-size: 1.5em; padding: 5%;">Empleado: <b>{{ cierre.UsuarioReg }}</b></h6>
        
        <table class="table table-sm" width="100%" v-if="val==2">
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
          <div v-if="val==1">
              <h5>Ventas Efectivo</h5><br>
              <table class="table b-table table-sm">
                <thead>
                  <tr class="thDetalle">
                      <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
                      <th v-if="$route.query.noDetalle">Cliente</th>
                      <th v-else>Productos</th>
                      <!-- <th>Estado</th> -->
                      <th style="width:90px">Pagado</th>
                  </tr>
                </thead>
                <tbody>
                  
                  <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
                    <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
                    <td>{{ item.FechaDoc }}</td>
                    <td>
                    
                        <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                        <thead>
                          <tr>
                            <th>Producto</th>
                            <th>Descripcion</th>
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
              <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
              <th v-if="$route.query.noDetalle">Cliente</th>
              <th v-else>Productos</th>
              <!-- <th>Detalle</th> -->
              <!-- <th>Estado</th> -->
              <th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>
              
                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                     
                      <th>Producto</th>
                      <th>Descripcion</th>
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
                <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
                <th v-if="$route.query.noDetalle">Cliente</th>
                <th v-else>Productos</th>
                <!-- <th>Detalle</th> -->
                <!-- <th>Estado</th> -->
                <th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                     
                      <th>Producto</th>
                      <th>Descripcion</th>
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
                <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
                <th v-if="$route.query.noDetalle">Cliente</th>
                <th v-else>Productos</th>
                <!-- <th>Detalle</th> -->
<!-- <th>Estado</th> -->
                <th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasTransferencia" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>

                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                   
                      <th>Producto</th>
                      <th>Descripcion</th>
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
              <th>Vendedor</th><th>Recibo</th><th>Fecha</th>
              <th v-if="$route.query.noDetalle">Cliente</th>
              <th v-else>Productos</th>
              <!-- <th>Estado</th> -->
              <th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td style="text-transform: uppercase;"> {{item.UsuarioReg}}</td>
                 <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>
                
                  <table class="tabla-detalle" v-if="!$route.query.noDetalle">
                  <thead>
                    <tr>
                     
                      <th>Producto</th>
                      <th>Descripcion</th>
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
           </div>
    </b-row>
  </b-container>

</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";

export default {
  mounted() {
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
      reportePorProveedor: [],
      idPerfil: sessionStorage.getItem("IdPerfil") || 0,
      val: 1
    };
  },
  computed: {
    getFechaCierre() {
      //return this.$route.params.id ?
    },
    granTotal() {
      return (
        (this.totalVentasEfectivo ? this.totalVentasEfectivo : 0) +
        (this.totalVentasVisa ? this.totalVentasVisa : 0) +
        (this.totalVentasMastercard ? this.totalVentasMastercard : 0) +
        (this.totalVentasTransferencia ? this.totalVentasTransferencia : 0) +
        (this.totalIngresos ? this.totalIngresos : 0) -
        (this.totalSalidas ? this.totalSalidas : 0) -
        (this.totalAdelantosActivos ? this.totalAdelantosActivos : 0)
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
        if (!venta.Anulado) total += venta.Efectivo;
      });
      return total;
    },
    ventasEfectivo() {
      return this.ventas.filter(venta => {
        return venta.Efectivo != 0 && !venta.EsCredito;
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
        return venta.Visa != 0 && !venta.EsCredito;
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
        return venta.Mastercard != 0 && !venta.EsCredito;
      });
    },
    totalVentasTransferencia() {
      let total = 0;
      this.ventasTransferencia.forEach(venta => {
        if (!venta.Anulado) total += venta.Yape;
      });
      return total;
    },
    ventasTransferencia() {
      return this.ventas.filter(venta => {
        return venta.Yape != 0 && !venta.EsCredito;
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
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    setVendedorManual(obj) {
      this.vendedor = obj;
      this.loadData();
      // this.getVendedor(obj.Usuario);
    },
    getDetalles(idDocVenta) {
      let detalles = this.itemsDetalle.filter(
        item => item.IdDocVenta == idDocVenta
      );
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles;
    },
    async getVendedor() {
      let usuario = sessionStorage.getItem("User");
      await this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          // if (!this.idPerfil == 1) {
          //   this.$refs.inputVendedor.$refs.input.disabled = true;
          // }

          if (response.data.IdUsuarioPerfil !== 1) {
            this.$refs.inputVendedor.$refs.input.disabled = true;
          }
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    imprimir(val) {
      this.$nextTick(() => {
        this.val = val;
      });
      setTimeout(() => {
        window.print();
      }, 100);
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
            // user: sessionStorage.getItem("User"),
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
          this.salidas = res.data.filter(sal => sal.EsTarjeta == 0);
          this.salidasTarjeta = res.data.filter(sal => sal.EsTarjeta == 1);
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
  .no-print {
    display: none;
  }
  .si-print {
    /* max-width: 80px; */
    display: block;
  }
  .table {
    /* max-width: 80px; */
    font-size: 1em;
  }
  th {
    background: #ffffff !important;
    color: #000000 !important;
    border: 1px solid #ffffff;
  }
}
</style>
