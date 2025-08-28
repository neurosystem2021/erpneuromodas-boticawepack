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
              <b-button variant="outline-primary" @click="imprimir(1)">IMPRIMIR</b-button>
            </b-button-group>
            <b-button-group size="sm">
              <b-button variant="outline-danger" @click="imprimir(2)">IMPRIMIR DETALLADO</b-button>
            </b-button-group>
            <b-button-group size="sm">
              <b-button variant="outline-danger" @click="imprimir(3)">IMPRIMIR PRODUCTOS</b-button>
            </b-button-group>
          </b-col>
      </b-row>
      <br class="no-print" /> 

      <h3 class="text-center">Cierre de Caja</h3> 
      <h4 class="si-print">Cierre de Caja </h4>
      <hr>
      <div v-if="cierre.FechaCierre">Fecha Fin:  <b>{{ cierre.FechaCierre }}</b></div>
      <div v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : (cierre.IdTurno == 2? 'TARDE':'NOCHE') }}</div>
      <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
      <br>
      <b-row>
        <b-col cols="4">
        <table class="table b-table table-sm table-striped table-bordered">
          <tbody>
            <tr>
              <td>GRAN TOTAL S/.</td>
              <td>{{ granTotal | moneda }}</td>
            </tr>
          </tbody>
        </table>          
        <table class="table b-table table-sm table-striped table-bordered">
          <tbody>
            <tr v-if="false">
              <td>Salida S/.</td>
              <td>{{ (granTotalSinPagoCliente + totalVentasCredito) | moneda }} </td>
            </tr>
            <tr>
              <td>Credito S/.</td>
              <td>{{ totalVentasCredito | moneda }} </td>
            </tr>
            <tr>
              <td>Contado S/.</td>
              <td>{{ granTotalSinPagoCliente | moneda }} </td>
            </tr>
          </tbody>
        </table>
        <table class="table b-table table-sm table-striped table-bordered">
          <tbody>
            <tr>
              <td>Efectivo S/.</td>
              <td>{{ totalVentasEfectivo | moneda }} </td>
            </tr>
            <tr>
              <td>Tarjeta Débito o Crédito S/.</td>
              <td>{{ totalVentasVisa | moneda }} </td>
            </tr>
            <tr>
              <td>Yape, Plin o Transf. S/.</td>
              <td>{{ totalVentasMastercard | moneda }} </td>
            </tr>
            <tr>
              <td>Ingresos S/.</td>
              <td>{{ totalIngresos | moneda }} </td>
            </tr>
            <tr>
              <td>Salidas S/.</td>
              <td>{{ totalSalidas | moneda }} </td>
            </tr>
          </tbody>
        </table>
        </b-col>
      </b-row>

      <div class="si-print" v-if="tipo ==3">          
          <h2>Resumen Por Producto</h2>
          <table class="table">
              <thead>
                <tr>
                  <td>#</td>	
                  <td>Producto</td>	
                  <td>Cantidad</td> 
                  <td>Monto</td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod,index) in reportePorProveedor" :key="index">
                    <td>{{ index+1 }}</td>
                    <td>{{ prod.Producto }}</td>
                    <td>{{ prod.Cantidad }}</td>
                    <td>{{ prod.Monto }}</td>
                </tr>
              </tbody>

          </table>
      </div>
      <div class="si-print px-5" v-if="tipo==2">          
      <b-row>
        <h5>Ventas Efectivo</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>
                <table class="table table-sm" style="font-size: 0.75rem;">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Efectivo | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Efectivo</b></td>
              <td class="total"><b>S/. {{ totalVentasEfectivo | redondear  }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row>
        <h5>Ventas Tarjeta Débito o Crédito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
               <td>
                <table class="table table-sm" style="font-size: 0.75rem;">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Visa | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Tarjeta Débito o Crédito</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>
 
      <b-row class="">
        <h5>Ventas Yape, Plin o Transf.</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
             
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
           
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>
                <table class="table table-sm" style="font-size: 0.75rem;">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Mastercard | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Yape, Plin o Transf.</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="credito" >
        <h5>Ventas a Credito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
             
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
           
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
               <td>
                <table class="table table-sm" style="font-size: 0.75rem;">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>S/. {{ item.Total | redondear }}</td>
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


      <b-row class="">
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
      </div>

      
      <b-tabs content-class="mt-3" class="no-print" pills card fill justified align="center">
        <b-tab title="Productos" active >
          <div class="">         
          <h2>Resumen Por Producto</h2>
          <table class="table table-sm">
              <thead>
                <tr>
                  <td>#</td>	
                  <td>Producto</td>	
                  <td>Cantidad</td> 
                  <td>Monto</td>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod,index) in reportePorProveedor" :key="index">
                    <td>{{ index+1 }}</td>
                    <td>{{ prod.Producto }}</td>
                    <td>{{ prod.total }}</td>
                    <td>{{ prod.Precio }}</td>
                </tr>
              </tbody>

          </table>
          </div>
        </b-tab>
        <b-tab title="Detalle" active><p>
          <b-row class="no-print">
        <h5>Ventas Efectivo</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>
                <b-button variant="info" v-if="!item.mostrar" @click="item.mostrar=!item.mostrar" ><i class="fa fa-eye" aria-hidden="true"></i></b-button>
                <b-button variant="warning" v-else @click="item.mostrar=!item.mostrar"><i class="fa fa-eye-slash" aria-hidden="true"></i></b-button>
                <table class="table table-sm" style="font-size: 0.75rem;" v-if="item.mostrar">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Efectivo | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Efectivo</b></td>
              <td class="total"><b>S/. {{ totalVentasEfectivo | redondear  }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="no-print">
        <h5>Ventas Tarjeta Débito o Crédito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
               <td>
                <b-button variant="info" v-if="!item.mostrar" @click="item.mostrar=!item.mostrar" ><i class="fa fa-eye" aria-hidden="true"></i></b-button>
                <b-button variant="warning" v-else @click="item.mostrar=!item.mostrar"><i class="fa fa-eye-slash" aria-hidden="true"></i></b-button>
                <table class="table table-sm" style="font-size: 0.75rem;" v-if="item.mostrar">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Visa | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Tarjeta Débito o Crédito</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>
 
      <b-row class="no-print">
        <h5>Ventas Yape, Plin o Transf.</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
             
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
           
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>
                <b-button variant="info" v-if="!item.mostrar" @click="item.mostrar=!item.mostrar" ><i class="fa fa-eye" aria-hidden="true"></i></b-button>
                <b-button variant="warning" v-else @click="item.mostrar=!item.mostrar"><i class="fa fa-eye-slash" aria-hidden="true"></i></b-button>
                <table class="table table-sm" style="font-size: 0.75rem;" v-if="item.mostrar">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Mastercard | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Yape, Plin o Transf.</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="credito no-print" >
        <h5>Ventas a Credito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
             
              <th width="10%">Recibo</th>
              <th width="10%">Fecha</th>
              <th width="20%">Cliente</th>
              <th width="30%">Detalle</th>
              <th width="5%">Estado</th>
              <th width="5%">Pagado</th>
           
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
               <td>
                <b-button variant="info" v-if="!item.mostrar" @click="item.mostrar=!item.mostrar" ><i class="fa fa-eye" aria-hidden="true"></i></b-button>
                <b-button variant="warning" v-else @click="item.mostrar=!item.mostrar"><i class="fa fa-eye-slash" aria-hidden="true"></i></b-button>
                <table class="table table-sm" style="font-size: 0.75rem;" v-if="item.mostrar">
                  <thead>
                    <tr>
                      <td>Producto</td>
                      <td>Cantidad</td>
                      <td>Precio</td>
                      <td>Descuento</td>
                      <td>Total</td>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod,index) in item.productos" :key="index">
                      <td>{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td>
                          {{ 
                              parseFloat(
                                parseFloat(prod.Cantidad) * parseFloat(prod.Precio) - parseFloat(prod.Descuento)
                              ).toFixed(2)
                          }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
              <td>S/. {{ item.Total | redondear }}</td>
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
        </p></b-tab>
        <!-- <b-tab title="Disabled" disabled><p>I'm a disabled tab!</p></b-tab> -->
      </b-tabs>
     
      

      <br>

      <b-alert variant="success" class="text-center no-print" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
    </b-container>
  </div>
</template>
<script>
import config from "@/store/config.js";
import CierresCaja from "@/components/CierresCaja.vue";
import Proformas from "@/components/ProformaLista.vue";

export default {
  async mounted() {
    this.$swal({
      title: "Procesando...",
      text: "Por favor espera",
      allowOutsideClick: false,
      showConfirmButton: false,
      didOpen: () => {
        this.$swal.showLoading();
      }
    });

    await this.getListadoVendedor();
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id);
      await this.loadData();
      this.$swal.close();
    } else {
      this.IdVendedor = sessionStorage.getItem("User").toLocaleLowerCase();
      let usuario = sessionStorage.getItem("User");
      this.bloquearVendedor = usuario !== "admin";
      await this.getVendedor();
      this.$swal.close();
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
      reportePorProveedor: [],
      tipo: 1,
      lstProductos: []
    };
  },
  computed: {
    getFechaCierre() {
      //return this.$route.params.id ?
    },
    granTotal() {
      return (
        (this.totalIngresos ? this.totalIngresos : 0) +
        (this.totalVentasEfectivo ? this.totalVentasEfectivo : 0) +
        (this.totalVentasVisa ? this.totalVentasVisa : 0) +
        (this.totalVentasMastercard ? this.totalVentasMastercard : 0) +
        (this.totalVentasTransferencia ? this.totalVentasTransferencia : 0) -
        (this.totalSalidas ? this.totalSalidas : 0) -
        (this.totalAdelantos ? this.totalAdelantos : 0)
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
      await this.axios(`${this.API}usuarios`).then(res => {
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
        window.print();
      });
    },
    imprimir(type) {
      this.tipo = type;
      this.$nextTick(() => {
        window.print();
      });
    },
    async loadData() {
      await this.getVentas();
      await this.getIngresos();
      await this.getSalidas();
      await this.generarReporte();
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
      obj.Usuario = obj.Usuario.toLowerCase();
      this.vendedor = obj;
      this.loadData();
    },
    async cerrarCaja() {
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
          let data = res.data.map(v => {
            return { ...v, mostrar: false };
          });
          this.ventas = data;
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
    }
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
.si-print {
  display: none;
}
@media print {
  .no-print {
    display: none;
  }
  .si-print {
    display: block;
  }
}
</style>
