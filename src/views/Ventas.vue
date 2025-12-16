<template>
    <b-container class="body" fluid>
       <b-card no-body v-if="ventas.length>0">
        <b-tabs card pills  v-model="index" justified>
          <b-tab :title="`Venta ${i+1}`" v-for="(tab,i) in ventas" :key="i" style="background: #e3f2fd;">
              <b-row>
                 <b-col md="7">
                    <b-row>
                     <b-col md="4">
                        <b-form-group  class="my-0">
                          <span class="ocultarMovil">Tipo de Venta</span>
                            <label class="lblSerie"><b style="display:none" class="mostrarSerie">{{ tab.Serie }} - {{ tab.Numero }}</b></label>
                            <b-input-group>
                              <autocomplete
                                  required="required"
                                  :ref="`inputTipoVenta${i}`"
                                  :url="API + 'ventas/tipos'"
                                  anchor="TipoDoc"
                                  initValue="BOLETA ELEC."
                                  label="writer"
                                  placeholder=""
                                  :on-select="setTipoVenta" 
                                  :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${i}`)"
                                  :classes="{input: 'form-control'}">
                              </autocomplete>
                              
                            </b-input-group>
                          </b-form-group>
                          <b-form-group v-if="tab.isNota" > 
                              <label>Doc. Referencia:</label>
                              <autocomplete
                                  required="required"
                                  :ref="`inputDocRef${i}`"
                                  :url="API + 'ventas/lista/'+venta.puntoVenta.IdDocVentaPuntoVenta"
                                  anchor="NroComprobante"
                                  initValue=""
                                  label="writer"
                                  placeholder=""
                                  :on-select="setDocRef"
                                  :onFocus="focusAutocomplete.bind(null, `inputDocRef${i}`)"
                                  :classes="{input: 'form-control'}">
                              </autocomplete>
                          </b-form-group>
                     </b-col>
                      <b-col md="4" class="ocularMovil2 my-0">
                        <b-form-group class="my-0">
                            <label class="my-0">Almacen:</label>
                            <input type="text" disabled class="form-control" v-model="tab.almacen.Almacen">
                          
                        </b-form-group>
                        <b-form-group v-if="tab.isNota" class="my-0">
                          <label >Motivo :</label>
                          <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                        
                        </b-form-group>
                      </b-col>
                      <b-col md="4" class="ocularMovil2">
                         <b-form-group>
                             <label class="my-0">Punto de Venta:</label>
                             <input type="text" disabled class="form-control" v-model="tab.puntoVenta.PuntoVenta">
                        
                         </b-form-group>
                      </b-col>
                      <b-col md="12" class="my-0">
                          <b-form-group  class="my-0">
                            <label class="ocultarMovil">Cliente</label>
                              <b-input-group>
                               <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                                  <autocomplete
                                      required="required"
                                      :ref="`inputCliente${i}`"
                                      :url="API + 'clientes'"
                                      anchor="ClienteDniRuc"
                                      label="writer"
                                      placeholder=""
                                      :onInput="(val) => newDniRuc = val"
                                      :on-select="setCliente" 
                                      :onFocus="focusAutocomplete.bind(null, `inputCliente${i}`)"
                                      :classes="{input: 'form-control'}">
                                  </autocomplete>
                                  <input v-model="dni"
                                      @keydown.enter.prevent="loadClienteBarcode"
                                      type="text"
                                      class="form-control ocularMovil2"
                                      placeholder="Codigo de Barra" />
                                  <b-input-group-append>
                                      <!--<proveedores-tabla></proveedores-tabla>-->
                                      <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                                      
                                  </b-input-group-append>
                              </b-input-group>
                               <b-form-select v-if="tab.cliente.IdCliente" v-model="tab.CampoDireccion" class="mb-0">
                                  <option :value="tab.cliente.Direccion">{{ tab.cliente.Direccion }}</option>
                                  <option :value="tab.cliente.Direccion2">{{ tab.cliente.Direccion2 }}</option>
                                  <option :value="tab.cliente.Direccion3">{{ tab.cliente.Direccion3 }}</option>
                              </b-form-select>

                              <b-row v-if="$route.query.idPedido">
                                  <b-col>
                                      <b-form-group>
                                          <label style="font-weight:bold">Cliente (Delivery):</label>
                                          <b-form-input 
                                            required style="text-transform:uppercase; font-size:0.9rem"
                                            placeholder="Ingrese cliente" autocomplete="off" 
                                            v-model="tab.nombreLlevar" ref="inputNombreCliente" type="text" name="NombreLlevar" id="NombreLlevar" readonly></b-form-input>
                                      </b-form-group>
                                  </b-col>
                                  <b-col>
                                      <b-form-group>
                                          <label style="font-weight:bold">Direccion Entrega (Delivery):</label>
                                          <b-form-textarea 
                                            required style="text-transform:uppercase; font-size:0.9rem"
                                            placeholder="Ingrese direccion" rows="3" 
                                            autocomplete="off" 
                                            v-model="tab.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega" readonly></b-form-textarea>
                                      </b-form-group>
                                  </b-col>
                              </b-row>
                          </b-form-group>
                      </b-col>
                      <b-col cols="6" v-if="mostrarSeccionOferta() && IdOferta">
                        <b-form-group >
                              <label>Doc. Referencias Oferta:</label>
                             
                            </b-form-group>
                      </b-col>
                      <b-col cols="12" md="3" lg="3">
                          <b-form-checkbox
                          v-model="tab.EsOrganizacion"
                          :value="true"
                          :unchecked-value="false"
                          >
                          Es Organización
                          </b-form-checkbox>  
                      </b-col>
                      <b-col cols="12" md="8" lg="8"  v-if="tab.EsOrganizacion" >
                          <b-form-input 
                          v-model="tab.nombreOrganizacion" 
                          v-if="tab.EsOrganizacion"
                          placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                      </b-col>
                     </b-row>
                </b-col>
                <b-col md="3" class="ocultarMovil" >
                    <b-form-group class="text-center" v-if="tab.cliente.Cliente">
                        <div class="puntos">
                            {{ (tab.cliente.Puntos || 0) | redondeoMenor }}
                        </div>
                        Puntos del cliente
                    </b-form-group>
                    <b-input-group prepend="Puntos" :append="descuentoPuntos.descuento | redondear" v-if="venta.cliente.Puntos && descuentoGeneral == 0">
                        <b-form-input
                            v-model="puntosAplicados"
                            min="0"
                            :max="tab.cliente.Puntos"
                            @input="aplicarPuntos"
                            type="number"
                        ></b-form-input>
                    </b-input-group>
                    <b-form-text v-if="descuentoPuntos.ValorPunto">
                       1 Punto equivale a: S/ {{ descuentoPuntos.ValorPunto | redondear }}
                    </b-form-text>
                    <b-form-text v-if="tab.cliente.EsVIP">
                       Cliente VIP con Precio Convenio
                    </b-form-text>
                    <b-form-group class="ocultarMovil mt-4" >
                        <label>Comisionista</label>
                        <autocomplete
                            :ref="`inputComisionista${i}`"
                            required="required"
                            :url="API + 'comisionistas'"
                            anchor="ClienteDniRuc"
                            label="writer"
                            placeholder="Comisionista"
                            :onInput="(val) => newDniRuc = val"
                            :on-select="setComisionista" 
                            :onFocus="focusAutocomplete.bind(null, `inputComisionista${i}`)"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                    </b-form-group>
                </b-col>
                 <b-col md="2" class="ocularMovil2">
                    <b-form-group class="text-center">
                        <div class="big-text">
                            {{ tab.Serie }} - <b>{{ tab.Numero }}</b>
                        </div>
                        {{ tab.FechaDoc }}
                    </b-form-group>
                    <b-form-group class="ocultarMovil">
                      <center>
                        <span style="font-size: 12px; font-weight: bold; padding: 10px 15px; background: #FFD600; border-radius: 25px;"> Valor Referencial</span>
                      </center>
                    </b-form-group>
                    <b-form-group class="ocultarMovil">
                        <label>Vendedor:</label>
                        <input type="text" disabled class="form-control" v-model="tab.vendedor.NombreUsuario">
                    </b-form-group>
                    
                    <b-badge variant="info" v-if="ventas[index].adelanto">Adelanto de: S/ {{ ventas[index].adelanto }} </b-badge>
                    <b-badge variant="success" v-if="ventas[index].adelanto">Total a pagar: S/ {{ $options.filters.redondear(ventas[index].totalCart -  ventas[index].adelanto) }} </b-badge>
                </b-col>
                <b-col cols="12"> 
                        <productos-carrito 
                            @onInputCantidad="actualizarDescuento"
                            @onAddProducto="onAddProducto"
                            @onRemoveProducto="actualizarDescuento"
                            @setProducto="setProducto"
                            :igvTotal="tieneIgv"
                            :setPrecio="true"
                            :ref="`carrito${i}`" 
                            :showButton="false" 
                            :bloquearInput="bloquearInput"
                            :fields="carrito.fields"
                            :isVentas="true"
                            :idAlmacen="tab.almacen.IdAlmacen"
                            :onSetProducto="onSetProducto"
                            :esDelivery="tab.EsDelivery"
                            :productos="tab.productos"
                            :id="`prodiuctos${i}`"
                            @eliminarProducto="eliminarProducto"
                            :index="i"
                            >
                        </productos-carrito>
                    </b-col>

              </b-row>
          </b-tab>
          
        </b-tabs>
      </b-card>
        <Menu v-if="false"></Menu>
        <b-row v-if="pagosPendientes>0">
            <b-col class="holder">
                <h4 class="news" height="100" width="200" bgcolor="white">Tiene <b>{{pagosPendientes}} pago(s) pendientes</b> del servicio de facturación, porfavor regularizar para mantener la validez de los comprobantes emitidos.  <b>{{fechaUltPago!=''?' Fecha de Vencimiento: '+fechaUltPago:''}}</b></h4>
            </b-col>
        </b-row>
        <br />
        <b-container class="bv-example-row"  id="conten" fluid>
            <b-row>
                <b-col md="12" v-if="this.$route.query.idPedido">
                    <div class="form-inline">
                        <h4>Pago de delivery código: <b-badge variant="info">{{this.$route.query.idPedido}}</b-badge></h4>
                    </div>
                </b-col>
            </b-row>
            <b-form @submit="guardarVenta" @keydown.enter="prevent">
                
                <b-row v-if="mostrarSeccionOferta()">
                  <b-col cols="4">

                    <b-table :fields="fieldsDocRef" :items="DocRefOfertas">
                      <template slot="action" slot-scope="data">
                        <b-button variant="danger" @click="eliminarDocRef(data.index)">
                          <i class="fa fa-trash" aria-hidden="true"></i>
                        </b-button>
                      </template>
                      </b-table>
                  </b-col>
                </b-row>
                <b-row>
                  <b-col cols="12">
                    <b-row>
                      <b-col cols="12" lg="2" offset-lg="5">
                        <b-button class="btnAction my-2" style="font-size: 1.5rem !important;" size="lg" @click="guardarVenta" :disabled="inProgress" variant="success" ><i class="fa fa-shopping-cart" aria-hidden="true"></i>  Pagar</b-button>
                      </b-col>
                    </b-row>
                  </b-col>
                   
                    <b-col cols="12"  md="4" lg="3" class="text-center" align-h="center">
                        <b-button class="btnAction" type="button" @click="anularPagoDelivery" variant="danger" v-if="this.$route.query.idPedido">Anular Pago Delivery</b-button>
                        <b-button class="btnAction" type="button" v-if="!this.$route.query.idPedido"  :disabled="inProgress" style="background-color: #FF3D00; color:#ffffff" @click="guardarProforma">Crear Proforma</b-button>
                        <!-- <b-button class="btnAction" @click="guardarVenta" :disabled="inProgress" variant="success" >Efectuar Venta</b-button> -->
                        <b-button class="btnAction" @click="anadirVenta" style="background-color: #757575; color:#ffffff" >Añadir Ventana</b-button>
                        <b-button class="btnAction" @click="eliminarVentana" v-if="index!=0" style="background-color: #FFD600; color:#ffffff" >Eliminar Ventana</b-button>
                        <b-button class="btnAction" v-if="ultimaVenta" target="_blank" variant="info"  :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                        <proformas :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectProforma="setProforma"  @guardarDatosProforma="guardarDatosProforma"></proformas>
                        <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                         
                        <!--  -->
                      
                    </b-col>

                    <b-col cols="12" md="3" lg="3" v-if="!venta.EsCredito" class="ocultarMovil">
                          <table class="table b-table border table-footer table-sm text-center "  >
                              <tbody>
                                  <tr>
                                    <td>Descripción:</td>
                                    <td>
                                      <b-form-textarea
                                        placeholder="Descripción"
                                        rows="3"
                                        max-rows="6"
                                        v-model="venta.descripcion"
                                        style="field-sizing: content !important;"
                                      ></b-form-textarea>
                                    </td>
                                  </tr>
                                  <tr class="ocultarMovil"><td colspan="2">Mano de obra bruta</td></tr>
                                  <tr class="ocultarMovil"><td colspan="2"><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                  <tr class="ocultarMovil"><td colspan="2">Mano de obra neta</td></tr>
                                  <tr class="ocultarMovil"><td colspan="2"><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                                  <tr><td colspan="2" v-if="ventas[index].productos.length>0" style="background: #ffffff !important; ">
                                    <b-form-checkbox
                                      id="checkbox-1"
                                      v-model="ventas[index].asignarDisabledProf"
                                      name="checkbox-1"
                                      :value="1"
                                      :unchecked-value="0"
                                    >
                                       Mostrar Proforma 
                                    </b-form-checkbox>
                                   
                                  </td></tr>
                              </tbody>
                          </table>
                        </b-col>
                              <b-col cols="12" md="3" lg="3" v-if="!venta.EsCredito" class="ocultarWeb">
                                <table class="table b-table border table-footer table-sm text-center "  >
                                    <tbody>
                                        <tr style="background: #ffffff !important; ">
                                          <td width="50%">
                                            <b-form-checkbox
                                              v-model="mostrarDescripcion"
                                              :value="1"
                                              :unchecked-value="0"
                                            >
                                              Descripción
                                            </b-form-checkbox>
                                          </td>
                                          <td width="50%">
                                            <b-form-textarea
                                              placeholder="Descripción"
                                              v-model="venta.descripcion"
                                              style="field-sizing: content;"
                                              v-if="mostrarDescripcion==1"
                                            ></b-form-textarea>
                                          </td>
                                        </tr>
                                        <tr class="ocultarMovil"><td colspan="2">Mano de obra bruta</td></tr>
                                        <tr class="ocultarMovil"><td colspan="2"><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                        <tr class="ocultarMovil"><td colspan="2">Mano de obra neta</td></tr>
                                        <tr class="ocultarMovil"><td colspan="2"><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                                        <tr>
                                          <td colspan="2" v-if="ventas[index].productos.length>0" style="background: #ffffff !important; ">
                                            <span>
                                            <b-form-checkbox
                                                id="checkbox-1"
                                                v-model="ventas[index].asignarDisabledProf"
                                                name="checkbox-1"
                                                :value="1"
                                                :unchecked-value="0"
                                             >
                                                Mostrar Proforma 
                                             </b-form-checkbox>
                                            </span>
                                          </td>
                                        </tr>
                                    </tbody>
                                </table>   
                                
                                
                              </b-col>
                              <b-col cols="12" md="4" lg="3" >
                                  <table class="table b-table border table-footer">
                                    <tbody>
                                      <tr v-if="sumatoriaDescuento > 0">
                                        <td class="table-success">SubTotal</td>
                                        <td class="table-success">{{ parseFloat(sumatoriaSubTotal) + parseFloat(sumatoriaDescuento) | redondear }}</td>
                                      </tr>
                                      <tr v-if="sumatoriaDescuento > 0">
                                        <td class="table-danger">Descuento</td>
                                        <td class="table-danger">- {{ sumatoriaDescuento  }}</td>
                                      </tr>
                                      <tr>
                                        <td>
                                              <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                                              <br>
                                              <b-badge v-if="idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                                              <b-badge v-if="idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                                              <b-badge v-if="idMetodoPago==3" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                                              <b-badge v-if="idMetodoPago==4" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                                              <b-popover 
                                                  target="popover-metodopago" 
                                                  placement="bottom" 
                                                  triggers="click" 
                                                  custom-class="custom-popover"
                                                  auto-hide
                                                >
                                                  <div class="table-responsive p-2">
                                                    <table class="table table-bordered table-sm">
                                                      <tbody>
                                                        <tr>
                                                          <td colspan="3" class="text-center font-weight-bold">Método de Pago</td>
                                                        </tr>
                                                        <tr>
                                                          <td>Efectivo</td>
                                                          <td>
                                                            <b-button @click="cargarMonto('efectivo')" size="sm" variant="primary">
                                                              <i class="fa fa-check" aria-hidden="true"></i>
                                                            </b-button>
                                                          </td>
                                                          <td>
                                                            <b-form-input
                                                              v-model="ventas[index].pago.efectivo.descripcion"
                                                              type="text"
                                                              :disabled="bloquearInput"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              v-model="ventas[index].pago.efectivo.monto"
                                                              min="0"
                                                              :disabled="bloquearInput"
                                                              step="0.01"
                                                              type="number"
                                                              class="form-control"
                                                              placeholder="S/."
                                                            />
                                                          </td>
                                                        </tr>
                                                        <tr>
                                                          <td>Tarjeta</td>
                                                          <td>
                                                            <b-button size="sm" @click="cargarMonto('visa')" variant="primary">
                                                              <i class="fa fa-check" aria-hidden="true"></i>
                                                            </b-button>
                                                          </td>
                                                          <td>
                                                            <b-form-input
                                                              v-model="ventas[index].pago.visa.descripcion"
                                                              type="text"
                                                              :disabled="bloquearInput"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              :disabled="bloquearInput"
                                                              v-model="ventas[index].pago.visa.monto"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(ventas[index].totalCart - ventas[index].pago.mastercard.monto - ventas[index].pago.yape.monto - $event.target.value)"
                                                            />
                                                          </td>
                                                        </tr>
                                                        <tr>
                                                          <td>Transferencia</td>
                                                          <td>
                                                            <b-button size="sm" @click="cargarMonto('mastercard')" variant="primary">
                                                              <i class="fa fa-check" aria-hidden="true"></i>
                                                            </b-button>
                                                          </td>
                                                          <td>
                                                            <b-form-input
                                                              v-model="ventas[index].pago.mastercard.descripcion"
                                                              :disabled="bloquearInput"
                                                              type="text"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              v-model="ventas[index].pago.mastercard.monto"
                                                              :disabled="bloquearInput"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[index].pago.visa.monto - ventas[index].pago.yape.monto - $event.target.value)"
                                                            />
                                                          </td>
                                                        </tr>
                                                        <tr>
                                                          <td>YAPE / PLIN</td>
                                                          <td>
                                                            <b-button size="sm" @click="cargarMonto('yape')" variant="primary">
                                                              <i class="fa fa-check" aria-hidden="true"></i>
                                                            </b-button>
                                                          </td>
                                                          <td>
                                                            <b-form-input
                                                              v-model="ventas[index].pago.yape.descripcion"
                                                              type="text"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              v-model="ventas[index].pago.yape.monto"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[index].pago.visa.monto - ventas[index].pago.mastercard.monto - $event.target.value)"
                                                            />
                                                          </td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                  </div>
                                                </b-popover>
                                        </td>
                                        <td><b>SubTotal</b></td>
                                        <td class="table-danger">{{ sumatoriaSubTotal  }}</td>
                                      </tr>
                                      <tr v-if="productos.some(v=>v.FLETE >0)">
                                        <td><b>Total Flete</b></td>
                                        <td class="table-danger">{{ productos.reduce((sum,producto)=>{
                                          return sum + parseFloat(producto.FLETE)
                                        },0)  }}</td>
                                      </tr>
                                      <tr v-if="sumatoriaIgv > 0">
                                        <td><b>IGV</b></td>
                                        <td class="table-danger">{{ sumatoriaIgv }}</td>
                                      </tr>
                                      <tr>
                                        <td>
                                          <b-button class="p-1 ml-1 mt-1" id="popover-opciones" variant="warning"><i class="fas fa-tags" ></i></b-button>
                                          <b-popover
                                              target="popover-opciones"
                                              placement="bottom" 
                                              custom-class="custom-popover"
                                              title="Opciones" triggers="click" auto-hide>
                                            <table class="table b-table border table-footer">
                                              <tbody>
                                                <tr>
                                                  <td class="text-center ocultarMovil">Opciones</td>
                                                </tr>
                                                <tr v-if="puntosAplicados == 0" class="ocultarMovil">
                                                  <td>
                                                    <b-input-group prepend="S/">
                                                      <input
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        :disabled="bloquearInput ? true : false"
                                                        @input="(val) => { aplicarDescuentoGeneral(val.target.value) }"
                                                        type="number"
                                                        class="form-control"
                                                      />
                                                    </b-input-group>
                                                    <b-form-text>
                                                      Descuento general a toda la venta.
                                                    </b-form-text>
                                                  </td>
                                                </tr>
                                                <tr>
                                                  <td>
                                                    <b-form-checkbox
                                                      :disabled="bloquearInput ? true : false || isNota || ventas[index].EsDelivery"
                                                      v-model="ventas[index].EsCredito"
                                                      :value="1"
                                                      :unchecked-value="0">
                                                      Venta a crédito
                                                    </b-form-checkbox>
                                                    <b-form-input 
                                                      :disabled="bloquearInput ? true : false || isNota || ventas[index].EsDelivery"
                                                      v-if="ventas[index].EsCredito==1"
                                                      v-model="ventas[index].FechaCredito"
                                                      type="date">
                                                    </b-form-input>
                                                    <b-form-checkbox 
                                                      :disabled="bloquearInput ? true : false || ventas[index].EsDelivery"
                                                      v-model="ventas[index].PorEntregar"
                                                      :value="1"
                                                      :unchecked-value="0">
                                                      Por entregar
                                                    </b-form-checkbox>
                                                    <b-form-checkbox 
                                                      :disabled="bloquearInput ? true : false || ventas[index].EsDelivery"
                                                      v-model="ventas[index].ProformaSinPrecio"
                                                      :value="1"
                                                      :unchecked-value="0">
                                                      Sin precios en proforma
                                                    </b-form-checkbox>
                                                  </td>
                                                </tr>
                                                <tr>
                                                  <td>
                                                    <b-input-group prepend="Proforma válido por:" append="días">
                                                      <b-form-input
                                                        :disabled="bloquearInput ? true : false"
                                                        v-model="ventas[index].diasValidosProf"
                                                        min="0"
                                                        value="0"
                                                        type="number"
                                                      ></b-form-input>
                                                    </b-input-group>
                                                  </td>
                                                </tr>
                                              </tbody>
                                            </table>
                                          </b-popover>  
                                        </td>
                                        <td><b>TOTAL</b></td>
                                        <td class="table-danger">{{ sumatoriaTotal }}</td>
                                      </tr>
                                      <tr>
                                        <td  colspan="2">
                                          <label>pagó con:</label>
                                          <b-form-input
                                            type="text"
                                            :disabled="bloquearInput?true:false"
                                            placeholder="Pagó con"
                                            v-model="ventas[index].pagocon"
                                            @input="asignarVuelto"
                                            
                                            ></b-form-input><br>
                                          <label>Vuelto:</label>
                                          <b>S/  {{ ventas[index].vuelto | redondear }}</b>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>G. G</td>
                                        <td>
                                          {{ 
                                            
                                           parseFloat(
                                            ventas[index].productos.reduce((sum,prod)=>{
                                              return sum + (prod.precio - prod.PrecioCosto) * prod.cantidad 
                                            },0)
                                           ).toFixed(2)
                                            
                                            
                                            }}
                                        </td>
                                      </tr>
                                    </tbody>
                                  </table>
                            </b-col>

                            <b-col cols="12"  md="4" lg="3" class="text-center" align-h="center">
                        <!--  -->
                       
                          
                            <movimiento></movimiento>
                       
                            <pago-deuda></pago-deuda>
                            <pre-orden-lista 
                                v-show="!this.$route.query.idPreOrden"
                                :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                                @onPreOrden="selectPreOrden"
                                @cargarDetalleProforma="cargarDetalleProforma"
                                :EsVenta="true"
                                ></pre-orden-lista>
                      
                            <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
                            <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                            <b-button target="_blank"  href="/views/proyectos.php?id=1" class=" btnAction" style="background: #0D47A1; color:#ffffff">CREAR/ EDITAR PROYECTOS</b-button>
                            <b-button target="_blank"  href="/views/reg_proyectos.php" class=" btnAction" style="background: #004D40; color:#ffffff">VER PROYECTOS</b-button>
                    </b-col>
                   
                </b-row>
                
                <div>
                <div class="float-right">
                    
                </div>
            </div>
            </b-form>
        </b-container>

        <b-modal v-model="cargando" hide-header hide-footer>
          <div class="spinner"></div>
          <p><b>Obteniendo los datos, un momento por favor....</b></p>
        </b-modal>
    </b-container>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarritoNuevo";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import DeliveryModal from "@/components/DeliveryModal.vue";
import Movimiento from "@/components/Movimiento";
import Swal from "sweetalert2";
export default {
  beforeMount() {
    this.initForm();
  },
  mounted() {
    // this.getNumeroVenta()
    this.getVendedor();
    //setInterval(this.updateTime, 1000)

    this.loadComisionista();
    // this.escucharPedidos();
    this.getEstadoSistema();
    this.ObtenerLstOfertas();
    this.conectarSocket();
    this.setupSocketListeners();
    this.getEstadoSistema();
    if (this.$route.query.idCliente) {
      this.$nextTick(() => {
        this.setCliente(this.$route.query.idCliente);
      });
    } else {
      this.loadClienteVarios();
    }
  },
  data() {
    return {
      cargando: false,
      ventas: [],
      index: 0,
      preVenta: {},
      vuelto: 0,
      pagocon: 0,
      mostrarDescripcion: 0,
      fieldsDocRef: [
        { key: "idDocVenta" },
        { key: "IdProductos" },
        { key: "NroDocRef", label: "Nro Doc. Referencia" },
        { key: "Total", label: "Monto" },
        { key: "action", label: "" }
      ],
      DocRefOfertas: [],
      idMetodoPago: 0,
      IdOferta: "",
      lstOfertas: [],
      isNota: false,
      dni: "",
      adelanto: 0,
      gasto: 0,
      newDniRuc: "",
      ultimaVenta: "",
      inProgress: false,
      API: config.API,
      APIADMIN: "http://204.48.21.196:5000",
      puntosAplicados: 0,
      descuentoPuntos: {},
      descuentoGeneral: 0,
      tieneIgv: false,
      venta: { IdProforma: null, descripcion: "" },
      bloquearInput: false,
      diasValidosProf: 1,
      adelantosPreOrden: [],
      itemsMotivoNota: [
        { value: null, text: "-- SELECCIONE EL MOTIVO --" },
        { value: "01", text: "ANULACION DE LA OPERACION" },
        { value: "02", text: "ANULACION POR ERROR EN EL RUC" },
        { value: "03", text: "CORRECION POR ERROR EN LA DESCRIPCION" },
        { value: "04", text: "DESCUENTO GLOBAL" },
        { value: "05", text: "DESCUENTO POR ITEM" },
        { value: "06", text: "DEVOLUCION TOTAL" },
        { value: "07", text: "DEVOLUCION POR ITEM" },
        { value: "08", text: "BONIFICACION" },
        { value: "09", text: "DISMINUCION EN EL VALOR" },
        { value: "10", text: "OTROS CONCEPTOS" }
      ],
      carrito: {
        fields: [
          {
            key: "manoobra",
            label: "M.O.",
            class: "text-center ocultarMovil"
          },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "Producto", class: "Producto", class: "clsProducto" },
          { key: "Descripcion", label: "Descripción", class: "ocultarMovil" },
          { key: "Series", label: "Serie" },
          { key: "eventPres", label: "" },
          { key: "cantxPrecio", label: "CantxP", variant: "warning" },
          // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
          // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
          /*{ key: 'ProductoMedicion', label: 'Medición' },*/
          { key: "cantidad", label: "Cant" },
          { key: "precio", label: "PU" },
          // { key: "utilidad", label: "Porcentaje Utilidad %" },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center ocultarMovil"
          },
          { key: "descuento", label: "Desc", class: "ocultarMovil" },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center"
          },
          { key: "acciones", label: "" }
        ]
      },
      pagosPendientes: 0,
      fechaUltPago: "",
      listeners: {
        nuevoPedido: null,
        connect: null,
        disconnect: null
      }
    };
  },
  watch: {
    totalCart(newVal) {
      this.ventas[this.index].totalCart = this.totalCart;
      this.ventas[this.index].pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.ventas[this.index].pago.visa.monto) +
            parseFloat(this.ventas[this.index].pago.mastercard.monto) +
            parseFloat(this.ventas[this.index].pago.yape.monto))
      ).toFixed(2);
      this.asignarVuelto();
    },
    asignarDisabledProfComputed(newVal) {
      let prods = [...this.ventas[this.index].productos];
      prods = prods.map(v => {
        return { ...v, disabledProf: newVal };
      });
      this.ventas[this.index].productos = [];
      this.ventas[this.index].productos = prods;
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
    asignarDisabledProfComputed() {
      if (
        this.ventas &&
        this.ventas[this.index] &&
        typeof this.ventas[this.index].asignarDisabledProf !== "undefined"
      ) {
        return this.ventas[this.index].asignarDisabledProf;
      }
      return null;
    },
    manoDeObraBruta() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precioBruta : 0;

      return precio;
    },
    manoDeObraNeta() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precio : 0;

      return precio;
    },
    sumatoriaSubTotal() {
      let sumatoria = 0;
      if (this.igvTotal) {
        // sumatoria = this.sumatoriaTotal - this.sumatoriaIgv
        sumatoria = this.sumatoriaTotal / 1.18;
      } else {
        this.ventas[this.index].productos.map(value => {
          sumatoria += parseFloat(value.subtotal);
          if (value.descuento) {
            sumatoria -= value.descuento;
          }
        });
      }
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaTotal() {
      let sumatoria = 0;
      this.ventas[this.index].productos.map(value => {
        sumatoria += parseFloat(value.total);
      });

      this.actualizarTotal(sumatoria);
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaIgv() {
      let sumatoria = 0;
      if (this.igvTotal || this.gravadaflag) {
        sumatoria = this.sumatoriaTotal;
        //sumatoria = sumatoria * 0.18
        sumatoria = sumatoria - sumatoria / 1.18;
      } else {
        this.productos.map(value => {
          sumatoria += parseFloat(value.igv);
        });
      }
      return this.$options.filters.redondear(sumatoria);
    },
    sumatoriaDescuento() {
      let sumatoria = 0;
      this.productos.map(value => {
        sumatoria += parseFloat(value.descuento || 0);
      });
      return this.$options.filters.redondear(sumatoria);
    }
  },
  beforeDestroy() {
    this.removeSocketListeners();
    this.$socket.disconnect();
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      actualizarPrecioConvenio: "actualizarPrecioConvenio",
      add: "agregarProducto",
      actualizarTotal: "actualizarTotalCart"
    }),

    async cargarDetalleProforma(det) {
      let detalle = det.map(item => ({
        ...item,
        setPrecio: true,
        cantidad: item.Cantidad,
        precio: item.Precio,
        EsManoDeObra: item.EsManoDeObra
      }));

      for (let i = 0; i < detalle.length; i++) {
        const res = await this.axios(
          `${this.API}/productos/stock/${
            this.ventas[this.index].almacen.IdAlmacen
          }`,
          {
            params: { idProductos: [detalle[i].IdProducto] }
          }
        );

        detalle[i].stock = res.data[0].stock ? res.data[0].stock : 0;
      }

      this.$nextTick(() => {
        this.ventas[this.index].productos = detalle;
        this.cargarMonto("efectivo");
      });
    },
    eliminarVentana() {
      if (this.ventas.length === 1) {
        window.location.reload();
        return;
      }

      this.ventas.splice(this.index, 1);
      this.index = this.index - 1;
      return;
    },
    eliminarProducto(index) {
      this.ventas[this.index].productos.splice(index, 1);
      // console.log("index", index);
    },
    setProducto(evt) {
      let prods = this.ventas[this.index].productos;
      if (prods.some(v => v.IdProducto == evt.IdProducto)) {
        let i = this.ventas[this.index].productos.findIndex(
          v => v.IdProducto == evt.IdProducto
        );
        let producto = this.ventas[this.index].productos[i];
        producto.precio = evt.PrecioContado;
        if (producto.estadoPxP === evt.estadoPxP) {
          if (producto.estadoPxP) {
            producto.cantidad = parseFloat(producto.cantidad) + 1;
          } else {
            producto.cantxPrecio = parseFloat(producto.cantxPrecio) + 1;
          }
        }
        Vue.set(prods, this.index, evt);
        // console.log(index);
      } else {
        this.ventas[this.index].productos.unshift({
          ...evt,
          precio: evt.PrecioContado,
          cantidad: 1,
          cantxPrecio: 1
        });
      }
    },
    async setProforma(e) {
      let productos = e.productos.map(v => {
        return { ...v, cantidadPres: 1 };
      });
      this.cargando = true;
      for (let index = 0; index < productos.length; index++) {
        const producto = productos[index];
        let series = await this.obtenerSeriePorProducto(producto.IdProducto);
        let p = await this.axios(
          `${this.API}/productos/stock/${
            this.ventas[this.index].almacen.IdAlmacen
          }`,
          {
            params: {
              idProductos: [producto.IdProducto]
            }
          }
        ).then(res => {
          return res.data[0];
        });
        producto.stock = p.stock;
        producto.Series = series;
        producto.PrecioCosto = p.PrecioCosto;
      }
      let ven = { ...this.preVenta, productos: productos };
      this.ventas.push(ven);
      this.$nextTick(() => {
        this.cargando = false;
        this.index = this.ventas.length - 1;
        this.getVendedor();
        let v = this.ventas[this.index];

        Promise.all([
          this.cargarMonto("efectivo"),
          this.loadAlmacen(v.almacen.IdAlmacen),
          this.loadPuntoVenta(v.puntoVenta.IdDocVentaPuntoVenta),
          this.loadComisionista(),
          this.setCliente(e),
          this.actualizarTotalCart()
        ]);
      });
    },
    actualizarTotalCart() {
      let productos = this.ventas[this.index].productos;

      this.ventas[this.index].totalCart = productos.reduce((sum, producto) => {
        return sum + parseFloat(producto.cantidad * producto.precio);
      }, 0);
    },
    asignarVuelto() {
      this.ventas[this.index].vuelto =
        this.ventas[this.index].pagocon - this.ventas[this.index].totalCart;
    },
    cargarMonto(tipo) {
      if (this.venta.pago && this.venta.pago.hasOwnProperty(tipo)) {
        Object.keys(this.venta.pago).forEach((key, index) => {
          this.venta.pago[key].monto = key === tipo ? this.totalCart : 0;
          if (key === tipo) {
            this.idMetodoPago = index + 1;
          }
        });

        // console.log("Montos actualizados:", this.venta.pago);
      } else {
        console.error(`El tipo de pago "${tipo}" no existe en this.venta.pago`);
      }
    },
    async ObtenerLstOfertas() {
      await this.axios(`${this.API}ofertas/listadoimpresion`).then(res => {
        if (res) {
          this.lstOfertas = res.data;
        }
      });
    },
    eliminarDocRef(index) {
      this.DocRefOfertas.splice(index, 1);
    },
    validarDocRefOfetar() {
      if (this.venta.tipoVenta.CodSunat == "99") {
        let TotalMontoOfertas = this.DocRefOfertas.reduce((sum, oferta) => {
          return sum + parseFloat(oferta.Total);
        }, 0);

        if (this.DocRefOfertas.length == 0) {
          return false;
        }

        let oferta = this.lstOfertas.filter(
          v => v.IdOferta == this.IdOferta
        )[0];
        if (oferta.ProductoFlag == 1) {
          let IdProducto = oferta.IdProducto;
          let IdProductos = this.DocRefOfertas.map(v => {
            return v.IdProductos;
          });

          this.$toasted.show(
            "Los documentos de referencia no cumple con los requisitos. Producto no presente en el comprobante"
          );
          return IdProductos.includes(IdProducto);
        }

        if (oferta.MontoFlag == 1) {
          if (oferta.Monto > TotalMontoOfertas) {
            this.$toasted.show(
              "Los documentos de referencia no cumple con los requisitos. La suma de los montos es menor a la promoción"
            );

            return false;
          }
        }
        return true;
      }
      return true;
    },

    mostrarSeccionOferta() {
      if (this.venta.tipoVenta.CodSunat == "99") {
        return true;
      }
      return false;
    },
    async obtenerProductoOferta() {
      this.$store.state.cart.productos = [];
      this.$nextTick(async () => {
        let query = this.lstOfertas.filter(v => v.IdOferta == this.IdOferta)[0];

        let stock = await this.axios(
          `${this.API}productos/stock?idProductos[]=${
            query.IdProductoAsociado
          }&idAlmacen=${this.venta.almacen.IdAlmacen}`
        ).then(res => {
          return res.data;
        });

        this.axios(
          `${this.API}productos/stock/2?q=${
            query.Producto
          }&sortBy=stock&sortDesc=1&limit=25`
        ).then(res => {
          if (res.data) {
            let producto = res.data[0];
            producto.cantidad = 1;
            producto.precio = 0;
            producto.disabled = true;
            producto.stock = stock[0].stock;
            this.productos.push(producto);
          }
        });
      });

      setTimeout(() => {
        const inputElement = this.$refs["inputDocRefOrden"].$el.querySelector(
          "input"
        );
        inputElement.focus();
        this.focusAutocomplete("inputDocRefOrden");
      }, 1000);
    },
    getEstadoSistema() {
      let hostUrl = sessionStorage.getItem("HostUrl");
      if (hostUrl != null) {
        this.axios
          .get(this.APIADMIN + "/api/sistema", {
            params: { Url: hostUrl }
          })
          .then(res => {
            if (res.data.success == true) {
              let info = res.data.data;
              this.pagosPendientes = info.length;
              this.fechaUltPago =
                info.slice(-1)[0].FechaVencimientoFormat || "";
            } else {
              this.pagosPendientes = 0;
            }
          })
          .catch(err => {
            this.pagosPendientes = 0;
            // console.log("Error de conexión al admin");
          });
      } else {
        this.pagosPendientes = 0;
      }
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    loadComisionista() {
      this.axios.get(this.API + "comisionista/0000").then(res => {
        if (res.data.IdCliente) {
          this.setComisionista(res.data);
        }
      });
    },
    prevent(evt) {
      if (evt.target.tagName !== "TEXTAREA") {
        evt.preventDefault();
      }
    },
    selectPreOrden(orden) {
      // console.log(orden);
      // this.venta.IdPreOrden = this.venta.IdPreOrden || []
      // if (Array.isArray(this.venta.IdPreOrden) ) {
      this.ventas[this.index].IdPreOrden.push(orden);
      //}

      // obtener total adelantos
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: orden.IdPreOrden,
            TipoCajaBanco: 0
          }
        })
        .then(res => {
          this.ventas[this.index].adelantosPreOrden = res.data;
          let suma = 0;
          res.data.map(ad => {
            if (ad.Tipo == 0) {
              suma += ad.Importe;
            }
          });
          this.ventas[this.index].adelanto = suma;
        });
    },
    loadFirstAlmacen() {
      this.axios.get(this.API + "almacenes/primero").then(res => {
        this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.ventas[this.index].almacen = res.data;
      });
    },
    loadClienteBarcode(e) {
      e.preventDefault();
      let dni = this.dni;

      if (dni) {
        this.setCliente(false, dni);
      }
    },
    focusAutocomplete(ref) {
      let val = `${ref}`;
      this.$refs[val][0].handleDoubleClick();
    },
    anadirVenta() {
      this.ventas.push({ ...this.preVenta, productos: [] });
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.getVendedor();
        this.loadClienteVarios();
        this.loadComisionista();
      });
    },
    initForm() {
      this.venta = {
        asignarDisabledProf: 0,
        descripcion: "",
        IdProforma: null,
        EsCredito: 0,
        Serie: "",
        Numero: "",
        cliente: {},
        comisionista: {},
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA ELEC.",
          LimitItems: 0,
          CodSunat: "03"
        },
        almacen: {
          IdAlmacen: 1,
          Almacen: "HUANUCO"
        },
        puntoVenta: {
          IdDocVentaPuntoVenta: 1,
          PuntoVenta: "HUANUCO"
        },
        vendedor: {},
        adelantosPreOrden: [],
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          yape: { IdMetodoPago: 4, monto: 0 }
        },
        IdPreOrden: [],
        CampoDireccion: "Direccion",
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsDelivery: false,
        nombreLlevar: "",
        DireccionEntrega: "",
        diasValidosProf: 1,
        ProformaSinPrecio: 0
      };
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
      this.preVenta = { ...this.venta, isNota: false };
      this.ventas.push({ ...this.venta, isNota: false, productos: [] });
    },
    setTipoVenta(obj) {
      this.ventas[this.index].isNota = false;
      this.bloquearInput = false;
      this.ventas[this.index].NroComprobanteModifica = "";
      this.ventas[this.index].CodSunatModifica = "";
      this.ventas[this.index].NotaIdMotivo = "";
      this.ventas[this.index].NotaDescMotivo = "";
      // this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.ventas[this.index].isNota = true;
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }
      // this.$refs.inputTipoVenta.setValue(obj.TipoDoc);
      this.ventas[this.index].tipoVenta = obj;
      this.getNumeroVenta();
    },
    setDocRefOferta(obj) {
      if (!this.DocRefOfertas.some(v => v.idDocVenta == obj.idDocVenta)) {
        this.DocRefOfertas.push(obj);
      } else {
        this.$swal.fire({
          icon: "warning",
          title: "Aviso",
          text: "El Doc. ya encuentra en la lista."
        });
      }
      // console.log(obj);
    },
    setDocRef(obj) {
      this.ventas[this.index].productos = [];
      this.ventas[this.index].CodSunatModifica = obj.CodSunat;
      this.ventas[this.index].NroComprobanteModifica = obj.NroComprobante;
      if (
        this.ventas[this.index].tipoVenta.CodSunat == "07" ||
        this.ventas[this.index].tipoVenta.CodSunat == "08"
      ) {
        this.ventas[this.index].Serie = this.ventas[this.index].Serie.replace(
          /^./,
          obj.NroComprobante[0]
        );
      }
      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.ventas[this.index].Serie,
            idTipoDoc: this.ventas[this.index].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.index].Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      //Cargar producto del documento afectado nota credito
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: this.venta.puntoVenta.IdDocVentaPuntoVenta
          }
        })
        .then(response => {
          this.axios
            .get(this.API + "cliente", {
              params: {
                idCliente: response.data[0].IdCliente
              }
            })
            .then(response => {
              if (response.data) {
                this.setCliente(response.data);
                // this.$refs.inputCliente.setValue(response.data.Cliente);
                // this.venta.cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          // this.limpiarCart();
          response.data.map(async item => {
            item.estadoPxP = false;
            let preciosProd = [];
            item.precioCont = item.PrecioContado;
            preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);

            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            let stock = await this.axios(
              `${this.API}productos/stock?idProductos[]=${
                item.IdProducto
              }&idAlmacen=${this.venta.almacen.IdAlmacen}`
            ).then(res => {
              return res.data;
            });
            this.ventas[this.index].productos.push({
              ...item,
              stock: stock[0].stock
            });
            // this.add([item, () => {}, false]);
          });
          this.ventas[this.index].NotaIdMotivo = null;
          this.bloquearInput = true;
          // this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
        });
    },
    setMotivo(ev) {
      if (ev == "01" || ev == "02" || ev == "06" || ev == null) {
        this.bloquearInput = true;
      } else {
        this.bloquearInput = false;
      }
    },
    loadAlmacen(id) {
      this.axios.get(this.API + "almacenes/id/" + id).then(res => {
        this.setAlmacen(res.data);
      });
    },
    setAlmacen(obj) {
      this.ventas[this.index].almacen = obj;
      // this.$refs.inputAlmacen.setValue(obj.Almacen);
      this.obtenerStok({
        IdAlmacen: this.ventas[this.index].almacen.IdAlmacen
      });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
        this.getNumeroVenta();
      });
    },
    getTipoDoc(id) {
      this.axios.get(this.API + "tipodoc/" + id).then(response => {
        if (response.data) this.setTipoVenta(response.data);
      });
    },
    setPuntoVenta(obj) {
      this.ventas[this.index].puntoVenta = obj;
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          this.getTipoDoc(response.data.IdTipoDoc);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      // console.log(obj)
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.ventas[this.index].vendedor = obj;
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        let ref = `inputComisionista${this.index}`;
        this.$refs[ref][0].setValue(obj.Cliente);
        this.ventas[this.index].comisionista = obj;
      } else {
        this.ventas[this.index].comisionista = {};
      }
    },
    async setCliente(obj, dni = false) {
      let ref = `inputCliente${this.index}`;
      let params = {};
      if (dni) {
        params = {
          dni: dni
        };
      } else {
        params = {
          idCliente: obj.IdCliente || obj
        };
      }

      if (typeof obj === "object") {
        this.$refs[ref][0].setValue(obj.Cliente);
        this.ventas[this.index].cliente = obj;
        this.ventas[this.index].CampoDireccion = this.ventas[
          this.index
        ].cliente.Direccion;
      } else {
        let prof = { ...this.preVenta };
        await this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              this.$refs[ref][0].setValue(response.data.Cliente);
              this.ventas[this.index].cliente = response.data;
              this.ventas[this.index].CampoDireccion = this.ventas[
                this.index
              ].cliente.Direccion;
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }

      //this.actualizarPrecioConvenio(obj)
      // Actualizar DisabledTotal
    },
    onSetProducto(obj, cant, precio) {
      if (cant && precio) {
        obj.cantidad = cant;
        let preciosPorProducto = JSON.parse(obj.PreciosPorProducto).find(
          e => e.cantidad === cant && e.precio === precio
        );
        obj.precio = preciosPorProducto.precio || 0;
        return obj;
      }
      obj.cantidad = 1;
      obj.precio = obj.PrecioContado || 0;
      return obj;
    },
    onAddProducto(obj) {
      this.actualizarDescuento();
      this.obtenerStok({
        IdAlmacen: this.venta.almacen.IdAlmacen
      });
    },
    actualizarDescuento() {
      if (this.puntosAplicados > 0) {
        this.aplicarPuntos(this.puntosAplicados);
      }

      if (this.descuentoGeneral > 0) {
        this.aplicarDescuentoGeneral(this.descuentoGeneral);
      }
    },
    aplicarPuntos(val) {
      if (parseFloat(val) > this.venta.cliente.Puntos) {
        this.puntosAplicados = this.venta.cliente.Puntos;
      }

      this.axios
        .get(this.API + "ventas/descuento/" + this.puntosAplicados)
        .then(response => {
          this.descuentoPuntos = response.data;
          this.aplicarDescuento(response.data.descuento);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    aplicarDescuentoGeneral(val) {
      this.puntosAplicados = 0;
      this.aplicarDescuento(val);
    },
    getNumeroVenta() {
      this.axios
        .get(this.API + "ventas/numero", {
          params: {
            idPuntoVenta: this.ventas[this.index].puntoVenta
              .IdDocVentaPuntoVenta,
            idTipoDoc: this.ventas[this.index].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.index].Serie = response.data.serie;
          this.ventas[this.index].Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    updateTime() {
      this.venta.FechaDoc = this.$moment().format("YYYY-MM-DDTH:mm:ss");
    },
    limpiarDespuesVenta() {
      this.inProgress = false;
      this.tieneIgv = false;
      this.initForm();
      this.limpiarCart();
      this.getVendedor();
      // this.$refs.inputCliente.setValue("");
      // this.getNumeroVenta()
      // this.$refs.inputTipoVenta.setValue('TICKET')
      // this.$refs.inputAlmacen.setValue('VENTA')
      // this.$refs.inputPuntoVenta.setValue('CAJA1')
      // this.$refs.inputVendedor.setValue('')
    },
    guardarDatosProforma(IdProforma) {
      this.venta.IdProforma = IdProforma;
      // console.log("IdProforma", IdProforma);
    },
    async guardarProforma() {
      this.venta = {};
      this.venta = { ...this.ventas[this.index] };
      this.inProgress = true;

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
        this.inProgress = false;
        return;
      }

      if (this.diasValidosProf == 0 || this.diasValidosProf == "") {
        this.$toasted.show("Debe ingresar los días de validez de la proforma");
        this.inProgress = false;
        return;
      }

      if (!this.venta.cliente.IdCliente) {
        this.$toasted.show("Debe seleccionar un cliente");
        this.inProgress = false;
        return;
      }

      if (!this.venta.vendedor.Usuario) {
        this.$toasted.show("Debe seleccionar un vendedor");
        this.inProgress = false;
        return;
      }
      if (this.venta.ProformaSinPrecio == 1) {
        this.productos.map(p => {
          p.disabledProf = 1;
        });
      }

      // this.venta.productos = this.productos;
      this.venta.diasValidosProf = this.diasValidosProf;
      await this.axios
        .post(this.API + "proformas", this.venta)
        .then(response => {
          this.$toasted.success("Proforma Guardada con exito");
          window.open(
            "/nuevo/index.html#/reportes/proforma/" + response.data.insertId,
            "_blank"
          );
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      this.inProgress = false;
      this.eliminarVentana();
    },
    async guardarVenta(evt) {
      this.venta = {};
      this.venta = this.ventas[this.index];
      // evt.preventDefault();

      this.inProgress = true;
      let totalPagos = 0;
      totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto))
        ? 0
        : parseFloat(this.venta.pago.efectivo.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto))
        ? 0
        : parseFloat(this.venta.pago.visa.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto))
        ? 0
        : parseFloat(this.venta.pago.mastercard.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.yape.monto))
        ? 0
        : parseFloat(this.venta.pago.yape.monto);

      if (
        (isNaN(parseFloat(this.venta.pago.efectivo.monto))
          ? 0
          : parseFloat(this.venta.pago.efectivo.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.visa.monto))
          ? 0
          : parseFloat(this.venta.pago.visa.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago Tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.mastercard.monto))
          ? 0
          : parseFloat(this.venta.pago.mastercard.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago Transferencia no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.yape.monto))
          ? 0
          : parseFloat(this.venta.pago.yape.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago YAPE / PLIN no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }

      if (!this.venta.EsCredito) {
        if (
          parseFloat(this.venta.totalCart).toFixed(2) !=
          parseFloat(totalPagos).toFixed(2)
        ) {
          this.$toasted.show("Los pagos no coinciden con la venta");
          this.inProgress = false;
          return;
        }
      }

      if (!this.venta.cliente.DniRuc) {
        this.$toasted.show("Debe seleccionar un cliente.");
        this.inProgress = false;
        return;
      }

      var esNumerico = Number.isInteger(parseInt(this.venta.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El RUC debe ser numérico, editar cliente.");
        this.inProgress = false;
        return;
      }

      var esEmpresa = this.venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && this.venta.tipoVenta.CodSunat == "01") {
        this.$toasted.show(
          "Una factura debe ser emitida para una empresa, no un usuario común."
        );
        this.inProgress = false;
        return;
      }
      // validar serie
      let prods = this.venta.productos;
      let val = !prods.some(v => v.Series.length > 0 && !!v.IdProductoSerie);

      // if (val) {
      //   this.$toasted.show(
      //     "Debe Seleccionar la serie del produto. Verifique."
      //   );
      //   this.inProgress = false;
      //   return;
      // }
      //Nota de Creadito
      if (
        this.venta.tipoVenta.CodSunat == "07" ||
        this.venta.tipoVenta.CodSunat == "08"
      ) {
        if (
          !this.venta.CodSunatModifica ||
          !this.venta.NroComprobanteModifica
        ) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }
        if (!this.venta.NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }
        this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.venta.NotaIdMotivo
        )[0].text;
      }

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      // NO PERMITE CANTIDAD Y PRECIOS 0
      let sinStock = 0;
      let totalCero = 0;
      let menosUtilidad = 0;
      this.venta.productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          pro.CodigoBarra !== "MANODEOBRA" &&
          (pro.stock <= 0 || parseFloat(pro.cantidad) > pro.stock) &&
          pro.EsPadre == 0
        ) {
          sinStock += 1;
        }

        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }

        /*if ((!(1/(pro.PorcentajeUtilidad*0)===1/0) || (pro.PorcentajeUtilidad=='')) && (pro.CodigoBarra !== 'COSTODELIVERY') && (pro.CodigoBarra !== 'MANODEOBRA')) {
                    menosUtilidad += 1
                }*/

        if (
          parseFloat(pro.precio) - pro.PrecioCosto < 0 &&
          pro.CodigoBarra !== "COSTODELIVERY" &&
          pro.CodigoBarra !== "MANODEOBRA"
        ) {
          menosUtilidad += 1;
        }
      });

      if (this.venta.tipoVenta.CodSunat != "99") {
        if (menosUtilidad >= 1 && this.venta.tipoVenta.CodSunat != "07") {
          this.$toasted.error(
            "No se puede vender un producto con ganancia negativa"
          );
          this.inProgress = false;
          return;
        }
      }
      if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != "07") {
        this.$toasted.error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }
      if (this.venta.tipoVenta.CodSunat != "99") {
        if (totalCero >= 1) {
          this.$toasted.error("No se puede enviar un producto en S/. 0.00");
          this.inProgress = false;
          return;
        }

        if (this.totalCart <= 0) {
          this.$toasted.show("La venta en S/. 0.00 no está permitido");
          this.inProgress = false;
          return;
        }
      }

      if (!this.validarDocRefOfetar()) {
        this.inProgress = false;
        return;
      }

      let IdOfertas = this.generarOfertas();

      // this.venta.productos = this.productos;
      // this.venta.puntosAplicados = this.puntosAplicados || 0;
      // this.venta.totalCart = this.venta.totalCart || 0;
      // this.venta.PagoCon = this.$refs.carrito.pagocon;
      // this.venta.adelantosPreOrden = this.adelantosPreOrden;
      this.venta.IdPedido = this.$route.query.idPedido || null;
      this.venta.nombreLlevar = this.venta.nombreLlevar.toUpperCase();
      // this.venta.IdOferta =  this.venta.IdOferta;
      this.venta.yapeMonto = this.venta.pago.yape.monto
        ? this.venta.pago.yape.monto
        : 0;
      this.venta.yapeDescripcion = this.venta.pago.yape.descripcion
        ? this.venta.pago.yape.descripcion
        : "";
      this.venta.visaMonto = this.venta.pago.visa.monto
        ? this.venta.pago.visa.monto
        : 0;
      this.venta.visaDescripcion = this.venta.pago.visa.descripcion
        ? this.venta.pago.visa.descripcion
        : "";
      this.venta.mastercardMonto = this.venta.pago.mastercard.monto
        ? this.venta.pago.mastercard.monto
        : 0;
      this.venta.mastercardDescripcion = this.venta.pago.mastercard.descripcion
        ? this.venta.pago.mastercard.descripcion
        : "";
      this.venta.DireccionEntrega = this.venta.EsDelivery
        ? this.infoDelivery.Direccion || ""
        : "";

      this.venta.IdDocVentas = "";
      if (this.venta.tipoVenta.CodSunat == "99") {
        let IdDocVentas = this.DocRefOfertas.map(v => v.idDocVenta);
        this.venta.IdDocVentas = IdDocVentas.join(",");
      }
      await this.axios
        .post(this.API + "ventas", this.venta)
        .then(response => {
          // limpiar preorden seleccionado
          // this.$router.replace("/ventas");
          this.$toasted.success("Venta realizada con exito");
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          let IdOfertasString = IdOfertas.join(",");
          // this.limpiarDespuesVenta();
          window.open(
            "/imprimir/index.php?IdDocVenta=" +
              response.data.insertId +
              "&IdOfertas=" +
              IdOfertasString,
            "_blank"
          );
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      // window.location.reload();
      this.inProgress = false;
      this.eliminarVentana();
    },
    async obtenerSeriePorProducto(id) {
      let productoSerie = [];
      let params = {
        IdProducto: id,
        Todos: false,
        empresa: sessionStorage.getItem("empresa"),
        IdAlmacen: this.idAlmacen
      };
      productoSerie = await this.axios(`${this.API}/productos/serie`, {
        params: {
          IdProducto: id,
          IdAlmacen: this.IdAlmacen
        }
      }).then(res => {
        return res.data;
      });

      return productoSerie;
    },
    generarOfertas() {
      let IdOferta = [];

      this.lstOfertas.forEach(oferta => {
        if (oferta.ProductoFlag == 1) {
          let producto = this.productos.filter(
            v => v.IdProducto == oferta.IdProducto
          )[0];

          if (producto) {
            if (oferta.MontoFlag == 1) {
              if (oferta.Monto <= producto.cantidad * producto.precio) {
                IdOferta.push(oferta.IdOferta);
              }
            } else if (oferta.CantidadFlag == 1) {
              if (oferta.Cantidad <= producto.cantidad) {
                IdOferta.push(oferta.IdOferta);
              }
            } else {
              IdOferta.push(oferta.IdOferta);
            }
          }
        } else if (oferta.MontoFlag == 1) {
          IdOferta.push(oferta.IdOferta);
        }
        // else if (oferta.CantidadFlag == 1) {
        //   console.log("Imprimir ofertas relacionadas con CantidadFlag");
        // }
      });
      return IdOferta;
    },
    eliminarCliente() {
      // this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
    },
    eliminarComisionista() {
      // this.$refs.inputComisionista.setValue("");
      this.venta.comisionista = {};
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      let venta = { ...this.preVenta };
      if (this.$route.query.idPedido) {
        this.infoDelivery = obj;
        venta.EsDelivery = true;
        // this.$refs.carrito.pagocon = parseFloat(obj.Cambio).toFixed(2);
        //cargar costo de delivery
        let costoDelivery = parseFloat(obj.PrecioDelivery || 0).toFixed(2);
        if (costoDelivery > 0) {
          this.axios
            .get(this.API + "costo/delivery")
            .then(response => {
              if (response.data) {
                let costoDeliverObj = response.data;
                costoDeliverObj.precio = costoDelivery;
                costoDeliverObj.cantidad = 1;
                costoDeliverObj.EsCostoDelivery = 1;
                costoDeliverObj.IdImpresora = 0;
                costoDeliverObj.descuento = 0;
                costoDeliverObj.indicacion = "";

                //Cargar Productos Delivery
                let productos = obj.Productos;
                productos = productos.map(item => {
                  return {
                    ...item,
                    cantidad: item.Cantidad,
                    precio: item.Precio,
                    indicacion: item.Indicacion
                  };
                });
                venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
                venta.DireccionEntrega = obj.Direccion;
                this.$toasted.success("PRODUCTOS DEL DELIVERY AGREGADOS");
                this.ventas.push({ ...venta, productos: productos });
                this.$nextTick(() => {
                  this.index = this.ventas.length - 1;
                  this.getVendedor();
                  let v = this.ventas[this.index];

                  Promise.all([
                    this.cargarMonto("efectivo"),
                    this.loadAlmacen(v.almacen.IdAlmacen),
                    this.loadPuntoVenta(v.puntoVenta.IdDocVentaPuntoVenta),
                    this.loadClienteVarios(),
                    this.loadComisionista(),
                    // this.setCliente(costoDeliverObj),
                    this.actualizarTotalCart()
                  ]);
                });
              } else {
                this.$toasted.show(
                  "PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'"
                );
                this.anularPagoDelivery();
              }
            })
            .catch(error => {
              console.error("Error delivery", error);
              this.$toasted.show("HUBO UN ERROR");
            });
        } else {
          //Cargar Productos Delivery
          let productos = JSON.parse(obj.Productos);
          productos.map(item => {
            this.add([
              item.IdProducto,
              () => {
                this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
              },
              {
                cantidad: item.Cantidad,
                precio: item.Precio,
                indicacion: item.Indicacion,
                isDelivery: true
              },
              false
            ]);
          });
          this.venta[this.venta].nombreLlevar = obj.Cliente || "SIN NOMBRE";
          this.venta[this.venta].DireccionEntrega = obj.Direccion;
        }
      }
    },
    anularPagoDelivery() {
      this.limpiarCart();
      this.venta.EsDelivery = false;
      this.infoDelivery = {};
      // this.$refs.carrito.pagocon = "";
      this.loadClienteVarios();
      this.loadComisionista();
      this.venta.nombreLlevar = "";
      this.venta.selectOpcionVenta = 0;
      this.venta.nombreLlevar = "";
      this.venta.DireccionEntrega = "";
      this.$router.replace("/ventas");
    },

    conectarSocket() {
      if (!this.$socket.connected) {
        // console.log("🔗 Conectando socket...");
        this.$socket.connect();
      }
    },

    /**
     * ✅ NUEVO: Configurar todos los listeners
     */
    setupSocketListeners() {
      // Evento: Conectado
      this.listeners.connect = () => {
        // console.log("✅ Socket conectado en VentasForm");
      };
      this.$socket.on("connect", this.listeners.connect);

      // Evento: Desconectado
      this.listeners.disconnect = () => {
        console.log("❌ Socket desconectado en VentasForm");
      };
      this.$socket.on("disconnect", this.listeners.disconnect);

      // Evento: Nuevo pedido
      this.listeners.nuevoPedido = data => {
        if (this.$refs.deliveryModal && this.$refs.deliveryModal.show) {
          this.$toasted.success(
            "Nuevo Pedido de " + "<strong>" + data.nombre + "</strong>"
          );
          this.$refs.deliveryModal.getPedidos();
        } else {
          if (this.$refs.deliveryModal) {
            this.$refs.deliveryModal.getPedidos();
          }
          this.$swal({
            position: "top-end",
            icon: "info",
            target: document.getElementById("conten"),
            title:
              "Pedido de Delivery de: <br> " +
              "<strong>" +
              data.nombre +
              "</strong>",
            showConfirmButton: true,
            confirmButtonText: "Mostrar Pedidos",
            confirmButtonColor: "#3880ff"
          }).then(result => {
            if (result.isConfirmed) {
              if (this.$refs.deliveryModal) {
                this.$refs.deliveryModal.show = true;
              }
            } else if (result.isDenied) {
              console.log("Denegado");
            }
          });
        }
      };
      this.$socket.on("nuevo-pedido", this.listeners.nuevoPedido);
    },

    /**
     * ✅ NUEVO: Remover todos los listeners
     */
    removeSocketListeners() {
      this.$socket.off("connect", this.listeners.connect);
      this.$socket.off("disconnect", this.listeners.disconnect);
      this.$socket.off("nuevo-pedido", this.listeners.nuevoPedido);
    }
  },
  components: {
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas,
    Movimiento,
    DeliveryModal
  }
};
</script>
<style scoped>
.alert {
  margin-bottom: 0;
}
.puntos {
  font-size: 52px;
}
.big-text {
  font-size: 30px;
}
/* .body {
  background: #e3f2fd;
} */
</style>
<style>
.holder {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
  clear: both;
  display: inline-block;
  overflow: hidden;
}
.news {
  animation: slide 20s linear infinite;
  white-space: pre;
}

@keyframes slide {
  0% {
    transform: translatex(100vw);
  }

  100% {
    transform: translatex(-120vw);
  }
}

@media only screen and (max-width: 920px) {
  /* For desktop: */
  .ocultarMovil {
    display: none !important;
  }
}
.ocultarWeb {
  display: none;
}
@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 300px);
  }
}
@media only screen and (max-width: 750px) {
  /* For desktop: */
  .ocultarWeb {
    display: inline;
  }
  .ocularMovil2 {
    display: none !important;
  }
  .mostrarSerie {
    display: inline !important;
    font-size: 1.5rem;
  }
  .lblSerie {
    text-align: center !important;
    padding: auto;
  }
}
/* @media (max-width: 850px) {
  .ocultarMovil {
    display: none !important;
  }
} */
.clsProducto {
  min-width: 200px !important;
  white-space: normal;
  word-wrap: break-word;
}

.body {
  background: #e3f2fd !important;
  min-height: 100vh;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 6px solid #ddd;
  border-top-color: #3498db; /* Color del borde superior */
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 20px auto; /* Centrado */
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
