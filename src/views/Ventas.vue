<template>
    <div>
        <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->
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
                <b-row>
                    <b-col md="7">
                        <b-row>
                            <b-col md="4">
                                <b-form-group  class="my-0">
                                  <span class="ocularMovil">Tipo de Venta</span>
                                    <label><b style="display:none" class="mostrarSerie">{{ venta.Serie }} - {{ venta.Numero }}</b></label>
                                    <b-input-group>
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoVenta"
                                        :url="API + 'ventas/tipos'"
                                        anchor="TipoDoc"
                                        initValue="BOLETA ELEC."
                                        label="writer"
                                        placeholder=""
                                        :on-select="setTipoVenta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                    </b-input-group>
                                </b-form-group>
                                 <b-form-group v-if="isNota" >
                                    <label>Doc. Referencia:</label>
                                    <autocomplete
                                        required="required"
                                        ref="inputDocRef"
                                        :url="API + 'ventas/lista/'+venta.puntoVenta.IdDocVentaPuntoVenta"
                                        anchor="NroComprobante"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setDocRef"
                                        :onFocus="focusAutocomplete.bind(null, 'inputDocRef')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-form-group>
                                
                                </b-col>
                            <b-col md="4" class="ocularMovil2 my-0">
                                <b-form-group>
                                    <label class="my-0">Almacen:</label>
                                    <autocomplete
                                        required="required"
                                        ref="inputAlmacen"
                                        :url="API + 'almacenes'"
                                        anchor="Almacen"
                                        initValue="HUANUCO"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setAlmacen" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-form-group>
                                    <b-form-group v-if="isNota">
                                    <label>Motivo :</label>
                                    <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                                    
                                    </b-form-group>
                                    
                            </b-col>
                            <b-col md="4" class="ocularMovil2">
                                <b-form-group>
                                    <label class="my-0">Punto de Venta:</label>
                                    <autocomplete
                                        required="required"
                                        ref="inputPuntoVenta"
                                        :url="API + 'ventas/puntos'"
                                        anchor="PuntoVenta"
                                        initValue="HUANUCO"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setPuntoVenta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-form-group>
                            </b-col>
                            <b-col md="12" class="my-0">
                                <b-form-group  class="my-0">
                                  <label class="ocularMovil">Cliente</label>
                                    <b-input-group>
                                     <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                                        <autocomplete
                                            required="required"
                                            ref="inputCliente"
                                            :url="API + 'clientes'"
                                            anchor="ClienteDniRuc"
                                            label="writer"
                                            placeholder=""
                                            :onInput="(val) => newDniRuc = val"
                                            :on-select="setCliente" 
                                            :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
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
                                     <b-form-select v-if="venta.cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-0">
                                        <option :value="venta.cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                                        <option :value="venta.cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                                        <option :value="venta.cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                                    </b-form-select>

                                    <b-row v-if="this.$route.query.idPedido">
                                        <b-col>
                                            <b-form-group>
                                                <label style="font-weight:bold">Cliente (Delivery):</label>
                                                <b-form-input required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese cliente" autocomplete="off" v-model="venta.nombreLlevar" ref="inputNombreCliente" type="text" name="NombreLlevar" id="NombreLlevar" readonly></b-form-input>
                                            </b-form-group>
                                        </b-col>
                                        <b-col>
                                            <b-form-group>
                                                <label style="font-weight:bold">Direccion Entrega (Delivery):</label>
                                                <b-form-textarea required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="venta.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega" readonly></b-form-textarea>
                                            </b-form-group>
                                        </b-col>
                                    </b-row>
                                </b-form-group>
                              </b-col>
                              <b-col cols="6" v-if="mostrarSeccionOferta()">
                                
                                  <b-form-group>
                                    <label>Ofertas Activas:</label>
                                    <b-select :options="lstOfertas"  value-field="IdOferta" text-field="Hash" v-model="IdOferta" @change="obtenerProductoOferta()"/>
                                  </b-form-group>
                                 
                                
                              </b-col>
                            <b-col cols="6" v-if="mostrarSeccionOferta() && IdOferta">
                              <b-form-group >
                                    <label>Doc. Referencias Oferta:</label>
                                    <autocomplete
                                        required="required"
                                        ref="inputDocRefOrden"
                                        :url="API + '/ventas/ofertas'"
                                        anchor="NroDocRef"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setDocRefOferta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputDocRefOrden')"
                                        :customParams="{ IdOferta: IdOferta , IdCliente : venta.cliente.IdCliente}"
                                        :debounce="1000"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                  </b-form-group>
                            </b-col>
                             <b-col cols="12" md="3" lg="3">
                                <b-form-checkbox
                                v-model="venta.EsOrganizacion"
                                :value="true"
                                :unchecked-value="false"
                                >
                                Es Organización
                                </b-form-checkbox>  
                            </b-col>
                            <b-col cols="12" md="8" lg="8"  v-if="venta.EsOrganizacion" >
                                <b-form-input 
                                v-model="venta.nombreOrganizacion" 
                                v-if="venta.EsOrganizacion"
                                placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col md="3" class="ocularMovil" >
                        <b-form-group class="text-center" v-if="venta.cliente.Cliente">
                            <div class="puntos">
                                {{ (venta.cliente.Puntos || 0) | redondeoMenor }}
                            </div>
                            Puntos del cliente
                        </b-form-group>
                        <b-input-group prepend="Puntos" :append="descuentoPuntos.descuento | redondear" v-if="venta.cliente.Puntos && descuentoGeneral == 0">
                            <b-form-input
                                v-model="puntosAplicados"
                                min="0"
                                :max="venta.cliente.Puntos"
                                @input="aplicarPuntos"
                                type="number"
                            ></b-form-input>
                        </b-input-group>
                        <b-form-text v-if="descuentoPuntos.ValorPunto">
                           1 Punto equivale a: S/ {{ descuentoPuntos.ValorPunto | redondear }}
                        </b-form-text>
                        <b-form-text v-if="venta.cliente.EsVIP">
                           Cliente VIP con Precio Convenio
                        </b-form-text>
                    </b-col>
                    <b-col md="2" class="ocularMovil2">
                        <b-form-group class="text-center">
                            <div class="big-text">
                                {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
                            </div>
                            {{ venta.FechaDoc }}
                        </b-form-group>
                        <b-form-group class="ocularMovil">
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
                        <b-form-group class="ocularMovil">
                            <label>Comisionista</label>
                            <autocomplete
                                ref="inputComisionista"
                                required="required"
                                :url="API + 'comisionistas'"
                                anchor="ClienteDniRuc"
                                label="writer"
                                placeholder="Comisionista"
                                :onInput="(val) => newDniRuc = val"
                                :on-select="setComisionista" 
                                :onFocus="focusAutocomplete.bind(null, 'inputComisionista')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-form-group>
                        <b-badge variant="info" v-if="adelanto">Adelanto de: S/ {{ adelanto }} </b-badge>
                        <b-badge variant="success" v-if="adelanto">Total a pagar: S/ {{ $options.filters.redondear(totalCart - adelanto) }} </b-badge>
                    </b-col>
                </b-row>
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
                        <productos-carrito 
                            @onInputCantidad="actualizarDescuento"
                            @onAddProducto="onAddProducto"
                            @onRemoveProducto="actualizarDescuento"
                            :igvTotal="tieneIgv"
                            :setPrecio="true"
                            ref="carrito" 
                            :showButton="false" 
                            :bloquearInput="bloquearInput"
                            :fields="carrito.fields"
                            :isVentas="true"
                            :idAlmacen="venta.almacen.IdAlmacen"
                            :onSetProducto="onSetProducto"
                            :esDelivery="venta.EsDelivery">

                            
                        </productos-carrito>
                    </b-col>

                    <b-col cols="12"  md="4" lg="3" class="text-center" align-h="center">
                        <b-button class="btnAction" type="button" @click="anularPagoDelivery" variant="danger" v-if="this.$route.query.idPedido">Anular Pago Delivery</b-button>
                        <b-button class="btnAction" type="button" v-if="!this.$route.query.idPedido"  :disabled="inProgress" variant="primary" @click="guardarProforma">Crear Proforma</b-button>
                        <b-button class="btnAction" type="submit" :disabled="inProgress" variant="success" >Efectuar Venta</b-button>
                        <b-button class="btnAction" v-if="ultimaVenta" target="_blank" variant="info"  :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                        <!--  -->
                    </b-col>

                          <b-col cols="12" md="3" lg="3" v-if="!venta.EsCredito" >
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
                                            ></b-form-textarea>
                                          </td>
                                        </tr>
                                        <tr class="ocularMovil"><td colspan="2">Mano de obra bruta</td></tr>
                                        <tr class="ocularMovil"><td colspan="2"><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                        <tr class="ocularMovil"><td colspan="2">Mano de obra neta</td></tr>
                                        <tr class="ocularMovil"><td colspan="2"><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
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
                                                              v-model="venta.pago.efectivo.descripcion"
                                                              type="text"
                                                              :disabled="bloquearInput"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              v-model="venta.pago.efectivo.monto"
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
                                                              v-model="venta.pago.visa.descripcion"
                                                              type="text"
                                                              :disabled="bloquearInput"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              :disabled="bloquearInput"
                                                              v-model="venta.pago.visa.monto"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - venta.pago.yape.monto - $event.target.value)"
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
                                                              v-model="venta.pago.mastercard.descripcion"
                                                              :disabled="bloquearInput"
                                                              type="text"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              v-model="venta.pago.mastercard.monto"
                                                              :disabled="bloquearInput"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.yape.monto - $event.target.value)"
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
                                                              v-model="venta.pago.yape.descripcion"
                                                              type="text"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              v-model="venta.pago.yape.monto"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.mastercard.monto - $event.target.value)"
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
                                                  <td class="text-center ocularMovil">Opciones</td>
                                                </tr>
                                                <tr v-if="puntosAplicados == 0" class="ocularMovil">
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
                                                      :disabled="bloquearInput ? true : false || isNota || venta.EsDelivery"
                                                      v-model="venta.EsCredito"
                                                      :value="1"
                                                      :unchecked-value="0">
                                                      Venta a crédito
                                                    </b-form-checkbox>
                                                    <b-form-input 
                                                      :disabled="bloquearInput ? true : false || isNota || venta.EsDelivery"
                                                      v-if="venta.EsCredito"
                                                      v-model="venta.FechaCredito"
                                                      type="date">
                                                    </b-form-input>
                                                    <b-form-checkbox 
                                                      :disabled="bloquearInput ? true : false || venta.EsDelivery"
                                                      v-model="venta.PorEntregar"
                                                      :value="1"
                                                      :unchecked-value="0">
                                                      Por entregar
                                                    </b-form-checkbox>
                                                    <b-form-checkbox 
                                                      :disabled="bloquearInput ? true : false || venta.EsDelivery"
                                                      v-model="venta.ProformaSinPrecio"
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
                                                        v-model="diasValidosProf"
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
                                            v-model="pagocon"
                                            @input="asignarVuelto"></b-form-input><br>
                                          <label>Vuelto:</label>
                                          <b>S/  {{ vuelto | redondear }}</b>
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>G. G</td>
                                        <td>
                                          {{ 
                                            
                                           parseFloat(
                                            productos.reduce((sum,prod)=>{
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
                       
                          <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                       
                            <movimiento></movimiento>
                       
                            <pago-deuda></pago-deuda>
                            <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente"  @guardarDatosProforma="guardarDatosProforma"></proformas>
                       
                            <pre-orden-lista 

                                v-show="!this.$route.query.idPreOrden"
                                :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                                @onPreOrden="selectPreOrden"
                               
                                :EsVenta="true"
                                ></pre-orden-lista>
                      
                            <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
                            <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                       
                    </b-col>
                   
                </b-row>
                
                <div>
                <div class="float-right">
                    
                </div>
            </div>
            </b-form>
        </b-container>
    </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarrito";
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
    this.loadClienteVarios();
    this.loadComisionista();
    // this.escucharPedidos();
    this.getEstadoSistema();
    this.ObtenerLstOfertas();
    this.conectarSocket();
    this.setupSocketListeners();
    this.getEstadoSistema();
  },
  beforeDestroy() {
    this.removeSocketListeners();
    this.$socket.disconnect();
  },
  data() {
    return {
      vuelto: 0,
      pagocon: 0,
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
            class: "text-center ocularMovil"
          },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "Producto", class: "Producto", class: "clsProducto" },
          { key: "Descripcion", label: "Descripción", class: "ocularMovil" },
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
            class: "text-center"
          },
          { key: "descuento", label: "Desc", class: "ocularMovil" },
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
      this.venta.pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.venta.pago.visa.monto) +
            parseFloat(this.venta.pago.mastercard.monto) +
            parseFloat(this.venta.pago.yape.monto))
      ).toFixed(2);
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
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
        this.productos.map(value => {
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
      this.productos.map(value => {
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
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      actualizarPrecioConvenio: "actualizarPrecioConvenio",
      add: "agregarProducto",
      actualizarTotal: "actualizarTotalCart"
    }),
    conectarSocket() {
      if (!this.$socket.connected) {
        console.log("🔗 Conectando socket...");
        this.$socket.connect();
      }
    },

    /**
     * ✅ NUEVO: Configurar todos los listeners
     */
    setupSocketListeners() {
      // Evento: Conectado
      this.listeners.connect = () => {
        console.log("✅ Socket conectado en VentasForm");
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
    },
    escucharPedidos() {
      this.sockets.subscribe("nuevo-pedido", data => {
        if (this.$refs.deliveryModal.show) {
          this.$toasted.success(
            "Nuevo Pedido de" + "<strong>" + data.nombre + "</strong>"
          );
          this.$refs.deliveryModal.getPedidos();
        } else {
          this.$refs.deliveryModal.getPedidos();
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
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
              this.$refs.deliveryModal.show = true;
            } else if (result.isDenied) {
              console.log("Denegado");
            }
          });
        }
      });
    },
    asignarVuelto() {
      this.vuelto = this.pagocon - this.sumatoriaTotal;
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
          console.log("a");
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
          console.log("ab");
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
            console.log("ac");
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
        console.log("query", query);
        let stock = await this.axios(
          `${this.API}productos/stock?idProductos[]=${
            query.IdProductoAsociado
          }&idAlmacen=${this.venta.almacen.IdAlmacen}`
        ).then(res => {
          return res.data;
        });
        console.log("stock", stock);

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
              console.log(res.data.msg);
            }
          })
          .catch(err => {
            this.pagosPendientes = 0;
            console.log("Error de conexión al admin");
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
      console.log(orden);
      // this.venta.IdPreOrden = this.venta.IdPreOrden || []
      // if (Array.isArray(this.venta.IdPreOrden) ) {
      this.venta.IdPreOrden.push(orden);
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
          this.adelantosPreOrden = res.data;
          let suma = 0;
          res.data.map(ad => {
            if (ad.Tipo == 0) {
              suma += ad.Importe;
            }
          });
          this.adelanto = suma;
        });
    },
    loadFirstAlmacen() {
      this.axios.get(this.API + "almacenes/primero").then(res => {
        this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.venta.almacen = res.data;
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
      this.$refs[ref].handleDoubleClick();
    },
    initForm() {
      this.venta = {
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
      this.isNota = false;
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
    },
    setTipoVenta(obj) {
      this.isNota = false;
      (this.bloquearInput = false), (this.venta.NroComprobanteModifica = "");
      this.venta.CodSunatModifica = "";
      this.venta.NotaIdMotivo = "";
      this.venta.NotaDescMotivo = "";
      this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota = true;
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }
      this.$refs.inputTipoVenta.setValue(obj.TipoDoc);
      this.venta.tipoVenta = obj;
      this.getNumeroVenta();
    },
    setDocRefOferta(obj) {
      console.log(obj);
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
      this.venta.CodSunatModifica = obj.CodSunat;
      this.venta.NroComprobanteModifica = obj.NroComprobante;
      if (
        this.venta.tipoVenta.CodSunat == "07" ||
        this.venta.tipoVenta.CodSunat == "08"
      ) {
        this.venta.Serie = this.venta.Serie.replace(
          /^./,
          obj.NroComprobante[0]
        );
      }
      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.venta.Serie,
            idTipoDoc: this.venta.tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.venta.Numero = response.data.numero;
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
                this.$refs.inputCliente.setValue(response.data.Cliente);
                this.venta.cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiarCart();
          response.data.map(item => {
            item.estadoPxP = false;
            let preciosProd = [];
            item.precioCont = item.PrecioContado;
            preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);

            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            this.add([item, () => {}, false]);
          });
          this.venta.NotaIdMotivo = null;
          this.bloquearInput = true;
          this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
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
      this.venta.almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
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
      this.venta.puntoVenta = obj;
      this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          this.$refs.inputVendedor.$refs.input.disabled = true;
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs
          this.$refs.inputAlmacen.$refs.input.disabled = true;
          this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          //this.getNumeroVenta()
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

      this.venta.vendedor = obj;
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        this.$refs.inputComisionista.setValue(obj.Cliente);
        this.venta.comisionista = obj;
      } else {
        this.venta.comisionista = {};
      }
    },
    setCliente(obj, dni = false) {
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
        this.$refs.inputCliente.setValue(obj.Cliente);
        this.venta.cliente = obj;
        this.venta.CampoDireccion = this.venta.cliente.Direccion;
      } else {
        this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              this.$refs.inputCliente.setValue(response.data.Cliente);
              this.venta.cliente = response.data;
              this.venta.CampoDireccion = this.venta.cliente.Direccion;
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
            idPuntoVenta: this.venta.puntoVenta.IdDocVentaPuntoVenta,
            idTipoDoc: this.venta.tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.venta.Serie = response.data.serie;
          this.venta.Numero = response.data.numero;
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
      this.$refs.inputCliente.setValue("");
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
      this.inProgress = true;

      if (!this.productos.length) {
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

      this.venta.productos = this.productos;
      this.venta.diasValidosProf = this.diasValidosProf;
      await this.axios
        .post(this.API + "proformas", this.venta)
        .then(response => {
          this.$toasted.success("Proforma Guardada con exito");
          this.limpiarDespuesVenta();
          this.loadClienteVarios();
          this.loadComisionista();
          this.diasValidosProf = 1;
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
      window.location.reload();
    },
    async guardarVenta(evt) {
      evt.preventDefault();

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
        if (this.totalCart != totalPagos) {
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

      if (!this.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      // NO PERMITE CANTIDAD Y PRECIOS 0
      let sinStock = 0;
      let totalCero = 0;
      let menosUtilidad = 0;
      this.productos.forEach(pro => {
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

      this.venta.productos = this.productos;
      this.venta.puntosAplicados = this.puntosAplicados || 0;
      this.venta.totalCart = this.totalCart || 0;
      this.venta.PagoCon = this.$refs.carrito.pagocon;
      this.venta.adelantosPreOrden = this.adelantosPreOrden;
      this.venta.IdPedido = this.$route.query.idPedido || null;
      this.venta.nombreLlevar = this.venta.nombreLlevar.toUpperCase();
      this.venta.IdOferta = this.IdOferta;
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
          this.initForm();
          this.limpiarCart();
          this.getVendedor();
          this.getNumeroVenta();
          this.loadClienteVarios();
          this.loadComisionista();
          this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
          this.$refs.inputAlmacen.setValue("VENTA");
          this.$refs.inputPuntoVenta.setValue("CAJA1");
          this.$refs.inputCliente.setValue("");
          this.$refs.inputVendedor.setValue("");
          this.$refs.inputComisionista.setValue("");
          this.$refs.carrito.pagocon = 0;
          let IdOfertasString = IdOfertas.join(",");
          this.limpiarDespuesVenta();
          window.open(
            "/imprimir/index.php?IdDocVenta=" +
              response.data.insertId +
              "&IdOfertas=" +
              IdOfertasString,
            "_blank"
          );
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      window.location.reload();
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
      this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
    },
    eliminarComisionista() {
      this.$refs.inputComisionista.setValue("");
      this.venta.comisionista = {};
    },
    onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      if (this.$route.query.idPedido) {
        console.log(obj);
        this.infoDelivery = obj;
        this.venta.EsDelivery = true;
        this.$refs.carrito.pagocon = parseFloat(obj.Cambio).toFixed(2);
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
                this.add([
                  response.data,
                  () => {
                    //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })
                  },
                  { setPrecioCosto: false },
                  false,
                  false
                ]);
                //Cargar Productos Delivery
                let productos = JSON.parse(obj.Productos);
                console.log(productos);
                productos.map(item => {
                  this.add([
                    item.IdProducto,
                    () => {
                      this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
                    },
                    {
                      cantidad: item.Cantidad,
                      precio: item.Precio,
                      indicacion: item.Indicacion
                    },
                    false
                  ]);
                });
                this.venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
                this.venta.DireccionEntrega = obj.Direccion;
                this.$toasted.success("PRODUCTOS DEL DELIVERY AGREGADOS");
              } else {
                this.$toasted.show(
                  "PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'"
                );
                this.anularPagoDelivery();
              }
            })
            .catch(error => {
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
          this.venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
          this.venta.DireccionEntrega = obj.Direccion;
        }
      }
    },
    anularPagoDelivery() {
      this.limpiarCart();
      this.venta.EsDelivery = false;
      this.infoDelivery = {};
      this.$refs.carrito.pagocon = "";
      this.loadClienteVarios();
      this.loadComisionista();
      this.venta.nombreLlevar = "";
      this.venta.selectOpcionVenta = 0;
      this.venta.nombreLlevar = "";
      this.venta.DireccionEntrega = "";
      this.$router.replace("/ventas");
    }
    //Sockets
    //Fin Sockets
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
  .ocularMovil {
    display: none !important;
  }
}
@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 300px);
  }
}
@media only screen and (max-width: 750px) {
  /* For desktop: */
  .ocularMovil2 {
    display: none !important;
  }
  .mostrarSerie {
    display: inline !important;
  }
}
/* @media (max-width: 850px) {
  .ocularMovil {
    display: none !important;
  }
} */
.clsProducto {
  min-width: 200px !important;
  white-space: normal;
  word-wrap: break-word;
}
</style>
