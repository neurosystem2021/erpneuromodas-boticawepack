<template>
  <b-container fluid>
    <b-row id="header">
      <b-col cols="7" class="px-0 py-0 mx-0 ">
        <b-card no-body >
        <b-tabs v-model="ventaActiva" content-class="mt-3" pills card class="mt-2 px-0" align="center">
          <b-tab v-for="(venta,index) in ventas" :key="index" :title="`Venta ${index+1}`" title-link-class="titleTabs" @click="cambiarTab(index)" class="pt-0">
            <b-container  class="px-0 mx-0" style="min-width: 100% !important;">
              <b-row class="pt-0">
                <b-col cols="5" class="pt-0">
                  <b-input-group prepend="Doc. Venta" >
                    <autocomplete
                    required="required"
                    :id="`inputTipoVenta${index}`"
                    :url="API + 'ventas/tipos'"
                    anchor="TipoDoc"
                    initValue="BOLETA ELEC."
                    class="font-weight-bold"
                    label="writer"
                    placeholder=""
                    :on-select="setTipoVenta"
                    :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                    :classes="{input: 'form-control'}"
                    >
                  </autocomplete>
                  </b-input-group>
                </b-col>
                <b-col md="4" class="docInfo" v-if="isNota[index]">
                <b-input-group prepend="Doc. Referenc" class="mt-2" >
                   <autocomplete
                       required="required"
                       :id="`inputDocRef${index}`"
                       :ref="`inputDocRef${index}`"
                       :url="API + 'ventas/lista/'+ventas[index].puntoVenta.IdDocVentaPuntoVenta"
                       anchor="NroComprobante"
                       initValue=""
                       label="writer"
                       placeholder=""
                       :on-select="setDocRef"
                       :onFocus="focusAutocomplete.bind(null, `inputDocRef${index}`)"
                       :classes="{input: 'form-control'}">
                   </autocomplete>
                </b-input-group>
                </b-col>
                <b-col md="3" class="docInfo" v-if="isNota[index]">
                            <b-input-group class="mt-2"  style="margin-bottom:-20px">
                                <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                            </b-input-group>
                </b-col>
                <b-col cols="3">
                  <b-form-checkbox
                    :id="`checkbox-${index}`"
                    v-model="venta.EsOrganizacion"
                    :value="true"
                    :unchecked-value="false"
                  >
                    Es Organización
                  </b-form-checkbox>  
                </b-col>
                <b-col cols="8" >
                  <b-form-input 
                  v-model="venta.NombreOrganizacion" 
                  v-if="venta.EsOrganizacion"
                  :id="`txtNombreOrganizacion-${index}`"
                  placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                </b-col>
                 
              </b-row>
              <b-row>
                <b-col cols="6" class="py-0">
                  <b-row>
                    <b-col cols="12">
                      <b-form-group v-if="ventas[index].verCliente">
                        <b-input-group class="mt-2">
                            <b-input-group-append>
                              <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                            </b-input-group-append>
                            <AutoComplete  
                            :value="ventas[index].cliente.Cliente"
                            @guardarCliente="setCliente"
                            @dniRuc="setNewDniRuc"
                            />
                            <!-- <autocomplete
                                required="required"
                                :id="`inputCliente${index}`"
                                :url="API + 'clientes'"
                                anchor="ClienteDniRuc"
                                label="writer"
                                placeholder="Cliente"
                                class="font-weight-bold"
                                :initValue="initValueCliente"
                                :onInput="(val) => newDniRuc = val"
                                :on-select="setCliente" 
                                :onFocus="focusAutocomplete.bind(null, `inputCliente${index}`)"
                                :classes="{input: 'form-control'}"
                                :ref="`inputCliente${index}`"
                                 v-model="ventas[index].cliente.Cliente"
                                 
                              >
                            </autocomplete> -->
                            <b-input-group-append>
                                <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                            </b-input-group-append>
                            </b-input-group>

                        <b-form-select v-if="ventas[ventaActiva].cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-3">
                          <option :value="ventas[ventaActiva].cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                          <option :value="ventas[ventaActiva].cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                          <option :value="ventas[ventaActiva].cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                        </b-form-select>
                      </b-form-group>
                    </b-col>
                  </b-row>
                </b-col>
                <b-col cols="6" class="py-0">
                  <center>

                    <h1> 
                      {{ventas[ventaActiva].Serie}}  - {{ventas[ventaActiva].Numero}}
                      <p style="padding: 2px 0; border-radius: 25px;width:30% ; background: yellow; font-size: 1rem;"> Valor Referencial</p>
                    </h1>
                  </center>
                  </b-col>

              </b-row>
              
              <div style="height: 40vh;overflow-x: scroll;overflow-y: scroll;" class="px-2 mx-0">
              <b-row style="position: relative;max-height: 40vh;">
                <b-table striped hover :fields="headerTable"  :items="venta.productos" width="100%"> 
                  <template slot="stock" slot-scope="data">
                    <span style="font-weight: bold; font-size: 0.75rem">
                      {{ data.item.stock }}

                    </span>
                  </template>
                  <template slot="Producto" slot-scope="data">
                    <span style="font-weight: bold; font-size: 0.75rem">
                      {{ data.item.Producto }}

                    </span>
                  </template>
                  <template slot="pres" slot-scope="data">
                    <b-button   
                      v-if="data.item.tienePresentacion"
                      size="sm"
                      variant="info"
                      @click="showPresentacion(data.item,data.index)" 
                      style="margin-top:2px;font-size: 0.75rem"
                      >
                      <i class="fas fa-list"></i>
                    </b-button>
      
                     
                     
                  </template>
                  <template slot="presentacion" slot-scope="data">
                    <b-form-input
                      v-model="data.item.Descripcion"
                      type="text"
                      style="text-align:center; font-weight:bold; width: 80px;font-size: 0.8rem"
                      autocomplete="false"
                      disabled
                      ></b-form-input>
                     
                     
                  </template>
                  <template slot="cantidad" slot-scope="data">
                    <input
                      v-model="data.item.cantidad"
                      type="number"
                      style="text-align:center; font-weight:bold; width: 75px;font-size: 0.8rem"
                      autocomplete="false"
                      :id="`input${data.index}`"
                      @input="metodoPagoCambio(1)"
                      @focus="abrirModalTouch(data.index, 1)"
                      class="form-control"
                      @keydown.enter="irTxtLoadBarcode"
                      @keydown.tab="irTxtLoadBarcode"
                      />
                      <p style="color: rgb(0, 123, 255); text-align: center; font-weight: bold;" 
                      v-if="data.item.CantidadPresentacion">
                        {{ data.item.CantidadPresentacion }} unid.
                      </p>
                  </template>
                  <template slot="precio" slot-scope="data">
                    <input
                      :disabled="data.item.bloquearInputPrecio"
                      v-model="data.item.precio"
                      min="0"
                      step="0.00001"
                      type="number"
                      :class="data.item.precio < data.item.PrecioCosto ? 'clsError': ''"
                      @focus="abrirModalTouch(data.index,2)"
                      style="text-align:center; font-weight:bold; width: 85px;font-size: 0.8rem"
                      autocomplete="false"  @input="metodoPagoCambio(1)"
                      class="form-control"
                      />
                     
                  </template>
                  <template slot="descuento" slot-scope="data">
                    <input
                      v-model="data.item.descuento"
                      disabled
                      type="number"
                      style="text-align:center; font-weight:bold; width: 75px;font-size: 0.8rem"
                      autocomplete="false"
                      @focus="abrirModalTouch(data.index,3)"
                      @input="metodoPagoCambio(1)"
                      class="form-control"
                      :class="data.item.PrecioCosto * data.item.cantidad < data.item.descuento  ? 'clsError' : ''"
                      />
                      <p v-if="data.item.PrecioCosto * data.item.cantidad < data.item.descuento" style="font-size: 0.7rem; color: red;">
                        Descuento Excede al P. C.
                      </p>
                     
                  </template>
                  <template slot="subtotal" slot-scope="data">
                    <span style="font-size: 0.8rem">
                      {{ parseFloat(getSubTotalDetalle(data.item)).toFixed(2) }}

                    </span>
                     
                  </template>
                  <template slot="total" slot-scope="data">
                 <span style="font-size: 0.8rem">
                   {{ parseFloat( getTotalDetalle(data.item)).toFixed(2) }}

                 </span>
                     
                  </template>
                  <template slot="acciones" slot-scope="data">
                    <b-button 
                      class="mx-auto mt-1"
                      variant="danger"
                      size="sm" 
                      @click="quitarProducto(data.index)">
                     <i class="fas fa-times fa-lg"></i>
                    </b-button>

                    <!-- @click="quitarProducto(data.index)"> -->
                    <b-button 
                    class="mx-auto mt-1"
                      variant="info"
                      size="sm"
                      v-if="mostrarEditarProducto()"
                      @click="editarProducto(data.item,data.index)"
                      >
                     <i class="fas fa-pencil-alt fa-sm"></i>
                    </b-button>
          
                     
                  </template>

                </b-table>
              </b-row>
              </div>
              <b-row class="mt-4" style="border-top: 3px solid #C0C0C0">
                <b-col cols="4">
                  <b-row>
                    <b-col md="12" class="mt-1">
                    <b-button class="btnAction" block style="background: #B71C1C; color: #fff; font-weight: bold; font-size: 1.2rem" @click="anadirVentana()"> Nueva Ventana</b-button>
                  </b-col>
                    <b-col md="12" class="mt-1">
                      <b-button class="btnAction" block v-if="ventas.length>1" variant="dark" style="font-weight: bold; font-size: 1.2rem"  @click="eliminarVentana()"> Cancelar Venta</b-button>
                    </b-col>
                    <b-col md="12" class="mt-1">
                      <b-button class="btnAction" v-if="touchHab" variant="danger" style="font-weight: bold; font-size: 1.2rem" block @click="activarModalTouch()"><i class="fas fa-desktop"></i> Deshabilitar Touch</b-button>
                      <b-button class="btnAction" v-else style="background: #00796B; color: #fff; font-weight: bold; font-size: 1.2rem" block @click="activarModalTouch()"><i class="fas fa-keyboard"></i> Habilitar Touch</b-button>
                    </b-col>
                    <!-- <b-col md="12" class="mt-1">
                   
                      <b-button type="button" block variant="primary"><i class="fas fa-redo"></i> Actualizar</b-button>
                    </b-col> -->
                    <b-col md="12" class="mt-1">
                      <proformas :idAlmacen="ventas[ventaActiva].almacen.IdAlmacen" @onSelectProforma="cargarProforma"></proformas>
                    </b-col>
                  <b-col md="12" class="mt-1">
                    <!-- <b-button variant="secondary" block @click="probarImpresora()"><i class="fas fa-print fa-sm"></i> Probar Impresora</b-button> -->
                  </b-col>
                  <b-col md="12" class="mt-1">
                      <b-button type="button" block variant="secondary" class="btnAction" @click="setScreen" style="font-weight: bold; font-size: 1.2rem" ><i class="fas fa-desktop"></i> {{navDisabled?'Mostrar Nav':'Ocultar Nav'}}  </b-button>
                  </b-col>
                  <!-- <b-col md="12" class="mt-1">
                    <b-button block @click="toggleNav()"> Ocular Nav</b-button>
                  </b-col> -->
                  <b-col md="12" class="mt-1">
                    <pago-deuda></pago-deuda>
                  </b-col>
                  <b-col md="12" class="mt-1">
                    <Movimiento></Movimiento>
                  </b-col>
                  </b-row>
                </b-col>
                <b-col cols="4">
                  <b-col md="12" class="text-left">
                    <b-row class="centrado d-flex justify-content-center my-1" >
                      <b-button class="mx-1 btnAction" block variant="primary"  @click="guardarProforma()"> Crear Proforma</b-button>
                      <b-button class="mx-1 btnAction" block variant="success"  @click="guardarVenta()"> <i class="fas fa-shopping-cart"></i> PAGAR</b-button>
                      <delivery-modal class="mx-1 btnAction" style="width: 100%; margin: 1% 0;" block ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                      <!-- <delivery-modal class="mx-1 btnAction" block ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal> -->
                  </b-row>
                    </b-col>

                  <b-col md="12" class="text-left">
                    <b-form-radio-group 
                      :id="`radios1${ventaActiva}`"
                      v-model="ventas[ventaActiva].selectOpcionVenta"
                      @change="setOpcionVenta"
                      :options="opcionesVenta"
                      :name="`radioOptions${ventaActiva}`">
                    </b-form-radio-group>
                  </b-col>
                  <b-col md="12" v-if="ventas[ventaActiva].selectOpcionVenta > 0">
                    <b-form-group>
                      <label style="font-weight:bold">Nombre:</label>
                      <b-form-input 
                        required
                        style="text-transform:uppercase; 
                        font-size:0.9rem"
                        placeholder="Ingrese cliente"
                        autocomplete="off"
                        v-model="ventas[ventaActiva].nombreLlevar"
                        ref="inputNombreCliente" 
                        type="text" 
                        name="NombreLlevar"
                        id="NombreLlevar">
                      </b-form-input>
                    </b-form-group>
                  </b-col>
                  <b-col md="12" v-if="ventas[ventaActiva].selectOpcionVenta == 2">
                  <b-form-group>
                    <label style="font-weight:bold">Direccion Entrega:</label>
                    <b-form-textarea required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="venta.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega"></b-form-textarea>
                  </b-form-group>
                  </b-col>
                </b-col>
                <b-col cols="4">
                  <table class="table b-table border table-footer" style="font-size:0.85rem">
                    <tbody>
                      <tr>
                        <td>
                          <b-button 
                            class="p-1 ml-1 mt-1" 
                            :id="`popover-metodopago${index}`" 
                            tabindex="0" 
                            variant="success" 
                            @click="togglePopover(index)">
                            <i class="fas fa-credit-card"></i>
                          </b-button>
                          <br>
                          <b-badge v-if="ventas[ventaActiva].idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                          <b-badge v-if="ventas[ventaActiva].idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                          <b-badge v-if="ventas[ventaActiva].idMetodoPago==3" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                          <b-badge v-if="ventas[ventaActiva].idMetodoPago==4" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                          <b-popover 
                            :target="`popover-metodopago${index}`" 
                            title="Método de Pago" 
                            placement="top" 
                            triggers="manual" 
                            :show.sync="mostrar[index]" 
                            :style="{ minWidth: '600px' }"
                          >
                          <slot :name="`metodopago${ventaActiva}`" style="width: 1200px;">
                            <table class="table b-table border table-footer table-sm">
                              <tbody>
                                <tr>
                                  <td>Efectivo</td>
                                  <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(1)"><i class="fas fa-check"></i></b-button></td>
                                  <td>
                                    <b-form-input v-model="ventas[ventaActiva].pago.efectivo.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                  </td>
                                  <td>
                                    <input v-model="ventas[ventaActiva].pago.efectivo.monto" min="0" step="0.01" type="number" placeholder="S/." class="form-control"/>
                                  </td>
                                 </tr>
                                <tr>
                                  <td>Tarjeta</td>
                                  <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i class="fas fa-check"></i></b-button></td>
                                  <td>
                                    <b-form-input v-model="ventas[ventaActiva].pago.visa.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                  </td>
                                  <td>
                                    <input class="form-control" v-model="ventas[ventaActiva].pago.visa.monto" min="0" step="0.01" type="number" placeholder="S/." 
                                    @input="venta.pago.efectivo.monto = $options.filters.redondear(getTotal() - venta.pago.mastercard.monto - venta.pago.delivery.monto  - $event.target.value)"/>
                                  </td>
                                  </tr>
                                  <tr>
                                    <td>Yape/Plin</td>
                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                                    <td>
                                      <b-form-input v-model="ventas[ventaActiva].pago.mastercard.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                    </td>
                                    <td>
                                      <input class="form-control" v-model="ventas[ventaActiva].pago.mastercard.monto" min="0" step="0.01" type="number" placeholder="S/." 
                                      @input="venta.pago.efectivo.monto = $options.filters.redondear(getTotal() - venta.pago.visa.monto - venta.pago.delivery.monto - $event.target.value)"/>
                                    </td>
                                  </tr>
                                  <tr>
                                    <td>Transferencia</td>
                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                                    <td>
                                      <b-form-input v-model="ventas[ventaActiva].pago.delivery.descripcion" type="text" placeholder="Descripcion" ></b-form-input>
                                    </td>
                                    <td>
                                      <input
                                        class="form-control" v-model="ventas[ventaActiva].pago.delivery.monto" min="0" step="0.01" type="number" placeholder="S/."
                                         @input="venta.pago.efectivo.monto = $options.filters.redondear(getTotal() - venta.pago.mastercard.monto - venta.pago.visa.monto - $event.target.value)"/>
                                    </td>
                                  </tr>
                                </tbody>
                            </table>
                          </slot>
                          </b-popover>
                        </td>
                        <td class="table-success">SubTotal</td>
                        <td class="table-success">{{ getSubTotal() }}</td>
                      </tr>
                      <tr>
                        <td>
                          <b-button 
                            class="p-1 ml-1 mt-1" 
                            :id="`popover-opciones${index}`" 
                            tabindex="0" 
                            @click="togglePopoverOpcion(index)" 
                            variant="warning">
                            <i class="fas fa-tags"></i>
                          </b-button>

                          <b-popover 
                            :target="`popover-opciones${index}`" 
                            title="Opciones" 
                            triggers="manual" 
                            placement="top" 
                            :show.sync="mostrarOp[index]" 
                            :style="{ minWidth: '600px' }"
                          >
                            <slot :name="`opciones${ventaActiva}`">
                              <table class="table b-table border table-footer">
                                    <tbody>
                                        <tr v-if="ventas[ventaActiva].puntosAplicados == 0">
                                            <td>
                                                <b-input-group prepend="S/" append="Descuento">
                                                    <input
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        @input="(val)=>{aplicarDescuentoGeneral(val.target.value)}"
                                                        class="form-control"
                                                    />
                                                </b-input-group>
                                                <b-form-text style="color:black !important; font-weight:bold">
                                                    Descuento general a toda la venta.
                                                </b-form-text>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b-form-checkbox
                                                    v-model="ventas[ventaActiva].EsCredito"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Venta a credito
                                                </b-form-checkbox>
                                                <b-form-input 
                                                    v-if="ventas[ventaActiva].EsCredito"
                                                    v-model="ventas[ventaActiva].FechaCredito"
                                                    type="date">
                                                </b-form-input>
                                                <!-- <b-form-checkbox v-if="false"
                                                    v-model="venta.PorEntregar"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Por entregar
                                                </b-form-checkbox> -->
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </slot>
                            
                          </b-popover>  
                        </td>
                        <td class="table-danger">Descuento</td>
                        <td class="table-danger">
                          {{ getDescuento() }}
                        </td>
                      </tr>
                  
                      <tr>
                        <td>
                          <!-- <b-button class="p-1 ml-1 mt-1" id="popover-num" variant="primary"><i class="fas fa-calculator" ></i></b-button> -->
                      <!-- <b-popover target="popover-num" triggers="focus" placement='bottom'>
                        <table class="calc__numbers">
                          <tbody>
                              <tr>
                                  <td class="botones" @click="foldNumbers('7');">7</td>
                                  <td class="botones" @click="foldNumbers('8');">8</td>
                                  <td class="botones" @click="foldNumbers('9');">9</td>
                                  <td class="botones" @click="ventas[ventaActiva].accionBoton=1" v-bind:class="{ accionActiva: accionBoton==1 }"><span>Cant.</span></td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('4');">4</td>
                                  <td class="botones" @click="foldNumbers('5');">5</td>
                                  <td class="botones" @click="foldNumbers('6');">6</td>
                                  <td class="botones" @click="ventas[ventaActiva].accionBoton=2" v-bind:class="{ 'accionActiva': accionBoton==2 }">Desc.</td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('1');">1</td>
                                  <td class="botones" @click="foldNumbers('2');">2</td>
                                  <td class="botones" @click="foldNumbers('3');">3</td>
                                  <td class="botones" @click="ventas[ventaActiva].accionBoton=3" v-bind:class="{ 'accionActiva': accionBoton==3 }">Precio</td>
                              </tr>
                              <tr>
                                  <td class="botones" @click="foldNumbers('0');">0</td>
                                  <td class="botones" @click="pushNumbers('-');">-</td>
                                  <td class="botones" @click="pushNumbers('+');">+</td>
                                  <td class="botones" @click="result();"><i class="fas fa-backspace"></i></td>
                              </tr>
                          </tbody>
                        </table>
                      </b-popover> -->
                        </td>
                        <td><b>TOTAL</b></td>
                        <td class="table-danger">
                          {{ getTotal() }}
                        </td>
                      </tr>
                      <tr>
                        <td  colspan="2">
                          <label>pagó con:</label>
                          <b-form-input
                            type="text"
                            placeholder="Pagó con"
                            v-model="ventas[ventaActiva].pagocon"
                            >
                          </b-form-input><br>
                          </td>
                          <td>
                          <label>Vuelto:</label>
                          <b>S/ 
                              {{ parseFloat(ventas[ventaActiva].pagocon - getTotal()).toFixed(2) }}

                          </b>
                        </td>
                      </tr>
                    </tbody>
                  </table>

                </b-col>
              </b-row>
              <!-- <b-row class="centrado d-flex justify-content-center my-5" >
                <b-button class="mx-1" variant="primary" @click="guardarProforma()"> Crear Proforma</b-button>
                <b-button class="mx-1" variant="success" @click="guardarVenta()"> Efectuar Venta</b-button>
               </b-row> -->
            </b-container>
          </b-tab>
          
        </b-tabs>
        <!-- <b-row>

        </b-row> -->
      </b-card>
      </b-col>
      <b-col md="5">
        <b-row class="headerInfo" style="background: #f4f4f4">
          <b-col md="5">
            <b-input-group prepend="Vendedor" class="mt-2">
              <autocomplete
                required="required"
                ref="inputVendedor"
                :url="API + 'usuarios'"
                anchor="NombreUsuario"
                class="font-weight-bold"
                label="writer"
                :on-select="setVendedor"
                :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-input-group>
          </b-col>
          <b-col md="4">
            <b-input-group prepend="Alm." class="mt-2">
              <autocomplete
                required="required"
                ref="inputAlmacen"
                :url="API + 'almacenes'"
                anchor="Almacen"
                class="font-weight-bold"
                initValue="PRINCIPAL"
                label="writer"
                placeholder=""
                :on-select="setAlmacen"
                :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-input-group>
          </b-col>
          <b-col md="3">
            <b-input-group prepend="P.V. " class="mt-2">
              <autocomplete
                required="required"
                ref="inputPuntoVenta"
                :url="API + 'ventas/puntos'"
                anchor="PuntoVenta"
                class="font-weight-bold"
                initValue="PRINCIPAL"
                label="writer"
                placeholder=""
                :on-select="setPuntoVenta"
                :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-input-group>
          </b-col>
        </b-row>
        <b-row style="background: #f4f4f4" class="buscador">
          <b-col md="8">
            <b-input-group prepend="Buscar Producto" class="mt-2 mb-2">
              <b-form-input
                placeholder="INGRESE UN PRODUCTO"
                autocomplete="off"
                v-model="busqueda"
                @focus.prevent=""
                ></b-form-input>
                <!-- @input="getProductosCategoriaSearch" -->
              <b-input-group-append>
                <b-button variant="info" @click="startRecognition">
                  <i class="fas fa-microphone"></i>
                </b-button
                >
              </b-input-group-append>
              <!-- <b-input-group-append>
                <b-button variant="secondary" @click="limpiarFiltro"
                  >Limpiar</b-button
                >
              </b-input-group-append> -->
            </b-input-group>
          </b-col>
          <b-col md="4">
            <b-input-group class="mt-2 mb-2">
              <input
                placeholder="CODIGO DE BARRAS"
                v-model="loadCode"
                autocomplete="off"
                type="none"
                inputmode="none"
                class="form-control"
                @keydown.enter.prevent="loadBarcode"
                @keydown.tab="irAlPrimerProducto"
                id="txtLoadBarcode"
              />
              <b-input-group-append>
                <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
              </b-input-group-append>
            </b-input-group>
          </b-col>
        </b-row>
        <b-row style="background: #f4f4f4">
          <b-tabs
            v-model="tabActive"
            style="font-size: 1.1rem !important; font-weight: 500"
          >
            <b-tab
              no-body
              v-for="(item, index) in listCategoria()"
              :key="index"
              :title="item.ProductoCategoria"
              :ref="'tab' + index"
              @click="getProductosCategoria(item.IdProductoCategoria)"
            >
            </b-tab>
            <div style=" text-align: right;">
              <b-button class="my-1" style="background: #01579B; color:#fff;" v-if="!vermas" @click="vermas=!vermas"> Ver Mas Categoría</b-button>
              <b-button class="my-1" style="background: #01579B; color:#fff;" v-if="vermas" @click="vermas=!vermas"> Ver Menos Categoría</b-button>
            </div>
            <b-tab
              v-if="busqueda != ''"
              :active="busqueda != ''"
              no-body
              title="BUSQUEDA"
            >
            </b-tab>
          </b-tabs>
        </b-row>
        <b-row
          style="
            position: relative;
            height: 79vh;
            overflow-y: scroll;
            background: #e6e6e6;
          "
        >
          <b-col>
            <b-row class="mt-2">
              <b-col
                id="productoPunto"
                class="m-2 text-center bg-white p-2"
                cols="2"
                style="
                  border: 2px solid #e0e0e0;
                  border-radius: 10px;
                  cursor: pointer;
                "
                v-for="(producto, index2) in productosCategoria"
                :key="index2"
                @click="setProducto(producto)"
              >
                <b-row class="mb-1">
                  <b-col md="4" class="text-left" v-if="false">
                    <b-badge
                      :variant="
                        parseFloat(producto.stock) <= 0 ? 'danger' : 'success'
                      "
                      style="font-size: 0.8rem"
                      >{{
                        producto.stock
                          ? Number.isInteger(producto.stock)
                            ? producto.stock
                            : producto.stock.toFixed(2)
                          : producto.stock
                      }}</b-badge
                    >
                  </b-col>
                  <b-col md="12" class="text-center">
                    <b-badge variant="dark" style="font-size: 1rem"
                      >S/
                      {{
                        parseFloat(producto.PrecioContado).toFixed(2)
                      }}</b-badge
                    >
                  </b-col>
                </b-row>
                <b-img
                  style="border-radius: 10px"
                  fluid
                  :src="'/resources/images/productos/' + producto.Imagen"
                  @error="setAltImg"
                  alt=""
                ></b-img>
                <label
                  class="font-weight-bold"
                  style="font-size: rem; cursor: pointer"
                >
                  {{ producto.Producto }}
                </label>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
      </b-col>
    </b-row>
    <b-modal v-model="modalPresentacion" size="lg" hide-footer>
      <center>
        <h3 style="text-decoration: underline;">{{producto.Producto}}</h3>
      </center>
      <b-row>
          <b-col cols="6">
                <b-card
                align="center"
                class="mb-3 contentCard"
                @click="cambiarPresentacion(
                  producto,
                  false
                )"
                >
                <b-row>
                    <b-col md="6" class="nomCart">
                      <div>
                          <b-row class="nomCart2">
                            <center> <h6 class="text-uppercase"> {{producto.ProductoPresentacion}} </h6> </center>
                            </b-row>
                            Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff"> {{ producto.stock }} </span>
                      </div>
                      </b-col>
                      <b-col md="6" class="text-left">
                      <h5>x1</h5>
                      <span><b >Precio C: </b>S/ {{ producto.PrecioCosto | redondear(2) }}</span><br>
                      <b>Precio V: </b>S/ {{ producto.PrecioContado | redondear(2) }}
                    </b-col>
                </b-row>
                </b-card>
            </b-col>

            
          <b-col cols="6"  v-for="(pres,index) in producto.PreciosPorProducto" :key="index">
                <b-card
                align="center"
                class="mb-3 contentCard"
                @click="cambiarPresentacion(
                  pres,
                  true
                )"
                >
                <b-row>
                    <b-col md="6" class="nomCart">
                      <div>
                          <b-row class="nomCart2">
                            <center> <h6 class="text-uppercase"> {{pres.nombre}} </h6> </center>
                            </b-row>
                            Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff">
                            {{  parseFloat(producto.stock/pres.cantidad).toFixed(2) }}
                            </span>
                      </div>
                      </b-col>
                      <b-col md="6" class="text-left">
                      <h5>x{{ pres.cantidad }}</h5>
                      <span><b >Precio C: </b>S/ {{ pres.precioc | redondear(2) }}</span><br>
                      <b>Precio V: </b>S/ {{ pres.precio | redondear(2) }}
                    </b-col>
                </b-row>
                </b-card>
            </b-col>

            <!-- <b-col md="6" v-for="(pres,index) in producto.PreciosPorProducto" :key="index" class="nomCart">
              
              <div>
                <b-row class="nomCart2">
                  <center> <h6 class="text-uppercase"> {{pres.nombre}} </h6> </center>
                </b-row>
                  Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff"> {{ 
                  parseFloat(producto.stock/pres.cantidad).toFixed(2)
                  }} </span>
              </div>
            </b-col>
            <b-col md="6" class="text-left">
            <h5>x1</h5>
            <span><b >Precio C: </b>S/ {{ producto.PrecioCosto | redondear(2) }}</span><br>
            <b>Precio V: </b>S/ {{ producto.PrecioContado | redondear(2) }}
        </b-col> -->
      </b-row>
    </b-modal>
    <!-- MODAL TOUCH -->
     <!-- MODALS TOUCH -->

     <b-modal ref="modalIngCant" id="ingCant" size="sm" hide-footer class="ingCant" title="Cantidad" hide-header-close>
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
            <table class="calc__numbers calc__numbers2">
              <tbody>
                  <tr>
                      <td class="botones" @click="addNumber('7');">7</td>
                      <td class="botones" @click="addNumber('8');">8</td>
                      <td class="botones" @click="addNumber('9');">9</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('4');">4</td>
                      <td class="botones" @click="addNumber('5');">5</td>
                      <td class="botones" @click="addNumber('6');">6</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('1');">1</td>
                      <td class="botones" @click="addNumber('2');">2</td>
                      <td class="botones" @click="addNumber('3');">3</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber(0);">0</td>
                      <td class="botones" @click="addPoint('.');">.</td>
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
              </tbody>
            </table>
            <br>
            <div style="text-align: center; width: 100%; height: 100%;">
              <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
              <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
            </div>
        </b-modal>

        <b-modal ref="modalIngPre" id="ingPre" size="sm" hide-footer class="ingPre" title="Precio" hide-header-close>
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
            <table class="calc__numbers calc__numbers2">
              <tbody>
                  <tr>
                      <td class="botones" @click="addNumber('7');">7</td>
                      <td class="botones" @click="addNumber('8');">8</td>
                      <td class="botones" @click="addNumber('9');">9</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('4');">4</td>
                      <td class="botones" @click="addNumber('5');">5</td>
                      <td class="botones" @click="addNumber('6');">6</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('1');">1</td>
                      <td class="botones" @click="addNumber('2');">2</td>
                      <td class="botones" @click="addNumber('3');">3</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber(0);">0</td>
                      <td class="botones" @click="addPoint('.');">.</td>
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
              </tbody>
            </table>
            <br>
            <div style="text-align: center; width: 100%; height: 100%;">
              <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
              <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
            </div>
        </b-modal>

        <b-modal ref="modalIngDesc" id="ingDesc" size="sm" hide-footer class="ingDesc" title="Descuento" hide-header-close>
          <label><b style="text-decoration: underline;">{{objProd.Producto}}</b></label>
          <input type="text" inputmode="none" @keypress="(e)=>{ verificar(e)}" v-model="cantidadInput" style="font-size:20px; width:100%; text-align:center; font-weight:bold; color:#1266F1" class="form-control"/>
            <table class="calc__numbers calc__numbers2">
              <tbody>
                  <tr>
                      <td class="botones" @click="addNumber('7');">7</td>
                      <td class="botones" @click="addNumber('8');">8</td>
                      <td class="botones" @click="addNumber('9');">9</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('4');">4</td>
                      <td class="botones" @click="addNumber('5');">5</td>
                      <td class="botones" @click="addNumber('6');">6</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber('1');">1</td>
                      <td class="botones" @click="addNumber('2');">2</td>
                      <td class="botones" @click="addNumber('3');">3</td>
                  </tr>
                  <tr>
                      <td class="botones" @click="addNumber(0);">0</td>
                      <td class="botones" @click="addPoint('.');">.</td>
                      <td class="botones" @click="deleteNumber();"><i class="fas fa-backspace"></i></td>
                  </tr>
              </tbody>
            </table>
            <br>
            <div style="text-align: center; width: 100%; height: 100%;">
              <b-button style="width: 49%;" size="md" variant="success" @click="ingresaNum(capturaIndex)">Ingresar</b-button>
              <b-button style="width: 49%;" size="md" variant="danger" @click="cerrrarNum()">Cerrar</b-button>
            </div>
        </b-modal>
        <producto-form :cbGuardar="(id,pxp) => agregarObj(id,pxp)" ref="productoFormEdit" :producto-update="productoSelect" ></producto-form>
        <!-- MODALS TOUCH -->
  </b-container>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import momemnt from "moment";
import ClientesForm from "@/components/ClientesForm";

import Proformas from "@/components/ProformaLista.vue";
import ProductoForm from "@/components/ProductoForm";
import ProductosCarritoNuevo from "@/components/ProductosCarritoNuevo";
import AutoComplete from "@/components/AutoComplete.vue";
import PagoDeuda from "@/components/PagoDeuda";
import Movimiento from "@/components/Movimiento";
import DeliveryModal from "@/components/DeliveryModal";

import Swal from "sweetalert2";
export default {
  components: {
    ProductosCarritoNuevo,
    ClientesForm,
    Proformas,
    AutoComplete,
    PagoDeuda,
    Movimiento,
    DeliveryModal,
    ProductoForm
  },
  data() {
    return {
      listeners: {
        nuevoPedido: null,
        connect: null,
        disconnect: null
      },
      productoSelect: {},
      indexPos: 0,
      idPerfil: sessionStorage.getItem("IdPerfil"),
      navDisabled: false,
      vermas: false,
      tabActiva: 0,
      initValueCliente: "00000000 CLIENTE VARIOS",
      touchHab: true,
      newDniRuc: "",
      API: config.API,
      categorias: [],
      productosCategoria: [],
      isNota: [],
      tieneIgv: [],
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
      headerTable: [
        {
          key: "stock",
          label: "Stock",
          variant: "info",
          class: "text-center",
          thStyle: { width: "5%" }
        },
        { key: "Producto", thStyle: { width: "53%" } },
        { key: "pres", label: "", thStyle: { width: "5%" } },
        {
          key: "presentacion",
          label: "Pres.",
          thStyle: { width: "5%" }
        },
        { key: "cantidad", label: "Cant.", thStyle: { width: "7%" } },
        { key: "precio", label: "Precio", thStyle: { width: "5%" } },
        {
          key: "subtotal",
          label: "Sub Total",
          variant: "success",
          class: "text-center",
          thStyle: { width: "5%" }
        },
        { key: "descuento", label: "Desc.", thStyle: { width: "5%" } },
        {
          key: "total",
          label: "Total",
          variant: "danger",
          class: "text-center",
          thStyle: { width: "5%" }
        },
        { key: "acciones", label: "", thStyle: { width: "5%" } }
      ],
      ventaSinStock: 0,
      busqueda: "",
      loadCode: "",
      tabActive: 0,
      ventaActiva: 0,
      venta: {
        verCliente: true,
        pagocon: 0,
        cliente: {},
        EsCredito: 0,
        Serie: "",
        Numero: "",
        Observacion: "",
        comisionista: {},
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA ELEC.",
          LimitItems: 0,
          CodSunat: "12"
        },
        almacen: {
          IdAlmacen: 1,
          Almacen: "PRINCIPAL",
          DescuentoActivo: 0
        },
        puntoVenta: {
          IdDocVentaPuntoVenta: 1,
          PuntoVenta: "PRINCIPAL"
        },
        vendedor: {},
        //FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaCredito: this.$moment()
                .add(1, "day")
                .format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          delivery: { IdMetodoPago: 4, monto: 0 }
        },
        idProductos: [],
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        IdPreOrden: null,
        CampoDireccion: "",
        EsDelivery: false,
        EsParaLlevar: 0,
        nombreLlevar: "",
        selectOpcionVenta: 0,
        DireccionEntrega: "",
        IdMetodoPago: 1,
        EsOrganizacion: 0,
        NombreOrganizacion: "",
        productos: [],
        accionBoton: 1
      },
      ventas: [
        {
          verCliente: true,
          pagocon: 0,
          pagocon: 0,
          cliente: {},
          EsCredito: 0,
          Serie: "",
          Numero: "",
          Observacion: "",
          cliente: {},
          comisionista: {},
          tipoVenta: {
            IdTipoDoc: 7,
            TipoDoc: "BOLETA ELEC.",
            LimitItems: 0,
            CodSunat: "12"
          },
          almacen: {
            IdAlmacen: 1,
            Almacen: "PRINCIPAL",
            DescuentoActivo: 0
          },
          puntoVenta: {
            IdDocVentaPuntoVenta: 1,
            PuntoVenta: "PRINCIPAL"
          },
          vendedor: {},
          //FechaCredito: this.$moment().format("YYYY-MM-DD"),
          FechaCredito: this.$moment()
                .add(1, "day")
                .format("YYYY-MM-DD"),
          FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
          pago: {
            efectivo: { IdMetodoPago: 1, monto: 0 },
            visa: { IdMetodoPago: 2, monto: 0 },
            mastercard: { IdMetodoPago: 3, monto: 0 },
            delivery: { IdMetodoPago: 4, monto: 0 }
          },
          idProductos: [],
          NroComprobanteModifica: "",
          CodSunatModifica: "",
          NotaIdMotivo: "",
          NotaDescMotivo: "",
          IdPreOrden: null,
          CampoDireccion: "",
          EsDelivery: false,
          EsParaLlevar: 0,
          nombreLlevar: "",
          selectOpcionVenta: 0,
          DireccionEntrega: "",
          IdMetodoPago: 1,
          EsOrganizacion: 0,
          NombreOrganizacion: "",
          productos: [],
          accionBoton: 1
        }
      ],
      bloquearInput: false,
      precioHabilitado: false,
      touchHab: false,
      opcionesVenta: [
        { text: "Normal", value: 0 },
        { text: "Delivery", value: 2 }
      ],
      modalPresentacion: false,
      producto: {},
      index: 0,
      mostrar: {},
      mostrarOp: {},
      cantidadInput: "",
      objProd: {},
      capturaIndex: null
    };
  },
  mounted() {
    this.configStock();
    this.getVendedorInfo();
    this.getNumeroVenta();
    this.loadClienteVarios();
    this.$root.$on("bv::hide::popover", id => {
      const metodoPagoIndex = id.replace("popover-metodopago", "");
      const opcionesIndex = id.replace("popover-opciones", "");

      if (metodoPagoIndex !== id) {
        this.$set(this.mostrar, metodoPagoIndex, false);
      }

      if (opcionesIndex !== id) {
        this.$set(this.mostrarOp, opcionesIndex, false);
      }
    });
    this.$forceUpdate();
    this.$nextTick(() => {
      this.irTxtLoadBarcode();
    });
    this.conectarSocket();
    this.setupSocketListeners();
    this.getEstadoSistema();
  },
  beforeDestroy() {
    this.removeSocketListeners();
    this.$socket.disconnect();
  },
  methods: {
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
    configStock() {
      this.axios
        .get(this.API + "config/stock")
        .then(res => {
          //this.ventaSinStock = parseInt(res.data); // por si devuelve string
          this.ventaSinStock = parseInt(res.data.VENTASINSTOCK); // CORREGIDO
        })
        .catch(err => {
          this.ventaSinStock = 0; // fallback: sí permite la venta si hay error
        });
    },
    mostrarEditarProducto() {
      if (this.idPerfil != 1) {
        return false;
      }
      if (this.ventas.length > 1) {
        return false;
      }
      return true;
    },
    editarProducto(Pro, index) {
      this.indexPos = index;
      this.productoSelect = Pro;
      this.$refs.productoFormEdit.show = true;
      this.$refs.productoFormEdit.nuevo = false;
    },
    agregarObj(obj) {
      this.productoSelect.precio = obj.PrecioContado;
      const productos = [...this.ventas[0].productos];
      productos[this.indexPos] = this.productoSelect;
      this.ventas[0].productos = productos;
      this.$nextTick(() => {
        this.$forceUpdate();
      });
      console.log(this.ventas[0].productos);
    },
    setNewDniRuc(val) {
      this.newDniRuc = val;
    },
    activarModalTouch() {
      this.touchHab = !this.touchHab;
    },
    abrirModalTouch(index, tipo) {
      this.capturaIndex = index;
      this.cantidadInput = "";
      this.objProd = this.ventas[this.ventaActiva].productos[index];
      if (!this.touchHab) {
        return;
      }

      this.$refs["modalIngDesc"].hide();
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();

      if (tipo == 1) {
        this.$refs["modalIngCant"].show();
      }
      if (tipo == 2) {
        this.$refs["modalIngPre"].show();
      }
      if (tipo == 3) {
        this.$refs["modalIngDesc"].show();
      }
    },
    ingresaNum() {
      if (this.$refs["modalIngCant"].is_show) {
        this.objProd.cantidad = this.cantidadInput;
      }
      if (this.$refs["modalIngPre"].is_show) {
        this.objProd.precio = this.cantidadInput;
      }
      if (this.$refs["modalIngDesc"].is_show) {
        this.objProd.descuento = this.cantidadInput;
      }
      // Vue.set(this.ventas[this.ventaActiva].productos, this.index, this.objProd);
      // console.log("this.objProd");
      this.cantidadInput = "";
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    deleteNumber() {
      this.cantidadInput = this.cantidadInput.toString().slice(0, -1);
    },
    cerrrarNum() {
      this.cantidadInput = "";
      this.$refs["modalIngCant"].hide();
      this.$refs["modalIngPre"].hide();
      this.$refs["modalIngDesc"].hide();
    },
    cambiarTab(index) {
      this.tabActiva = index;

      this.$forceUpdate();
    },
    addNumber(number) {
      this.cantidadInput = this.cantidadInput + number;
    },
    toggleNav() {
      let navMenu = document.getElementById("navMenu");
      console.log(navMenu);
    },

    togglePopover(index) {
      if (this.mostrar[index]) {
        this.$root.$emit("bv::hide::popover", `popover-metodopago${index}`);
      } else {
        this.$set(this.mostrar, index, true);
        this.$nextTick(() => {
          this.$root.$emit("bv::show::popover", `popover-metodopago${index}`);
        });
      }
    },
    togglePopoverOpcion(index) {
      if (this.mostrarOp[index]) {
        this.$root.$emit("bv::hide::popover", `popover-opciones${index}`);
      } else {
        this.$set(this.mostrarOp, index, true);
        this.$nextTick(() => {
          this.$root.$emit("bv::show::popover", `popover-opciones${index}`);
        });
      }
    },
    fechaActual() {
      return this.$moment().format("YYYY-MM-DD H:mm:ss");
    },
    async loadClienteVarios() {
      await this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    getVendedorInfo() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/info/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          let almacen = {
            IdAlmacen: response.data.IdAlmacen,
            Almacen: response.data.Almacen,
            DescuentoActivo: response.data.DescuentoActivo
          };
          let puntoVenta = {
            IdDocVentaPuntoVenta: response.data.IdDocVentaPuntoVenta,
            PuntoVenta: response.data.PuntoVenta
          };
          this.APPTOKEN = response.data.APPTOKEN;
          this.setAlmacen(almacen);
          this.setPuntoVenta(puntoVenta);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          // bloquear ambos inputs
          this.$refs.inputAlmacen.$refs.input.disabled = true;
          this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          this.$refs.inputVendedor.$refs.input.disabled = true;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    focusAutocomplete(ref) {
      let element = document.getElementById(ref);
      const event = new MouseEvent("dblclick");
      element.dispatchEvent(event);
    },
    setVendedor(obj) {
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.venta.vendedor = obj;
      this.ventas[0].vendedor = obj;
    },
    setAlmacen(obj) {
      this.ventas[this.ventaActiva].almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      this.getCategorias();
      if (this.$route.query.idPreOrden && this.$route.query.idCliente) {
        this.cargarPreOrden();
        this.setCliente(this.$route.query.idCliente);
      }
    },
    getCategorias() {
      this.axios
        .get(this.API + "categorias/puntoventa")
        .then(response => {
          this.categorias = response.data;
          if (this.categorias.length > 0) {
            let masMov = {
              ProductoCategoria: "MAS MOVIDOS",
              IdProductoCategoria: 0
            };
            this.categorias.unshift(masMov);
            setTimeout(() => {
              this.tabActive = 0;
              let primerCategoria = this.categorias[0].IdProductoCategoria;
              this.getProductosCategoria(primerCategoria);
            }, 300);
          }
        })
        .catch(error => {
          this.$toasted.show("Hubo un error, no hay conexion");
        });
    },
    listCategoria() {
      let categorias = [...this.categorias];
      if (this.vermas) {
        return categorias;
      } else {
        return this.categorias.slice(0, 10);
      }
    },

    async loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;

      let prod = await this.axios.get(this.API + "productos/id/" + idProducto, {
        params: {
          codigoBarra: this.loadCode
        }
      });
      if (prod.data) {
        this.setProducto(prod.data);
      } else {
        this.$toasted.show("No se encontró ningun producto");
      }
      this.loadCode = "";
    },
    startRecognition() {
      this.busqueda = "";
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.busqueda = event.results[0][0].transcript;
          this.getProductosCategoriaSearch(this.busqueda);
          this.busqueda = "";
        }
      };

      this.recognition.onerror = event => {
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        if (!this.busqueda) {
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    getProductosCategoriaSearch(search) {
      if (!this.busqueda || !this.busqueda.trim()) return;
      if (search.length < 3) {
        return;
      }
      if (this.venta.almacen.IdAlmacen) {
        this.productosCategoria = [];
        this.cargandoProductos = true;

        this.axios
          .get(this.API + "productos/categoria", {
            params: {
              q: search,
              idAlmacen: this.ventas[this.ventaActiva].almacen.IdAlmacen,
              MasMovido: search == 0 ? 1 : 0
            }
          })
          .then(response => {
            this.cargandoProductos = false;
            this.productosCategoria = response.data;
            //console.log(this.productosCategoria,"izi");
          })
          .catch(error => {
            this.cargandoProductos = true;
            this.productosCategoria = [];
            this.$toasted.show("Hubo un error, no hay conexion");
          });
      }
    },
    irTxtLoadBarcode(event) {
      if (event) {
        event.preventDefault();
      }
      document.getElementById("txtLoadBarcode").focus();
    },

    getProductosCategoria(idProductoCategoria) {
      if (this.venta.almacen.IdAlmacen) {
        this.productosCategoria = [];
        this.cargandoProductos = true;
        this.busqueda = "";
        this.axios
          .get(this.API + "productos/categoria", {
            params: {
              q: idProductoCategoria,
              IdProductoCategoria: idProductoCategoria,
              idAlmacen: this.ventas[this.ventaActiva].almacen.IdAlmacen,
              MasMovido: idProductoCategoria == 0 ? 1 : 0
            }
          })
          .then(response => {
            this.cargandoProductos = false;
            this.productosCategoria = response.data;
            //console.log(this.productosCategoria,"izi");
          })
          .catch(error => {
            this.cargandoProductos = true;
            this.productosCategoria = [];
            this.$toasted.show("Hubo un error, no hay conexion");
          });
      }
    },
    setPuntoVenta(obj) {
      this.ventas[this.ventaActiva].puntoVenta = obj;
      // this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },

    limpiarFiltro() {
      this.busqueda = "";
      this.tabActive = 0;
    },
    anadirVentana() {
      this.initValueCliente = "00000000 CLIENTE VARIOS";

      let venta = { ...this.venta };
      venta.productos = [];
      this.ventas.push(venta);
      this.$nextTick(() => {
        this.ventaActiva = this.ventas.length - 1;
        this.getNumeroVenta();
        this.getVendedorInfo();
        this.loadClienteVarios();
      });
      let header = document.getElementById("header");
      header.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
      this.$toasted.success("Se añadió una opción más de venta");
      this.$forceUpdate();
      this.$nextTick(() => {
        this.irTxtLoadBarcode();
      });
    },
    eliminarVentana() {
      this.ventas.splice(this.ventaActiva, 1);

      this.$nextTick(() => {
        if (this.ventaActiva != 0) {
          this.ventaActiva = this.ventaActiva - 1;
        } else {
          this.ventaActiva = 0;
        }
      });
      let header = document.getElementById("header");
      header.scrollIntoView({
        behavior: "smooth",
        block: "start"
      });
      this.$toasted.info("Opción de venta cerrada.");
      this.$forceUpdate();
      this.$nextTick(() => {
        this.irTxtLoadBarcode();
      });
    },
    setTipoVenta(obj) {
      this.isNota[this.ventaActiva] = false;
      this.bloquearInput = false;
      this.ventas[this.ventaActiva].NroComprobanteModifica = "";
      this.ventas[this.ventaActiva].CodSunatModifica = "";
      this.ventas[this.ventaActiva].NotaIdMotivo = "";
      this.ventas[this.ventaActiva].NotaDescMotivo = "";
      // this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota[this.ventaActiva] = true;
        // this.limpiarCart();
        if (this.ventas[this.ventaActiva].EsDelivery) {
          // this.anularPagoDelivery();
        } else if (
          this.$route.query.idPreOrden &&
          this.$route.query.idCliente
        ) {
          // this.anularPagoOrden();
        }
      }

      if (obj.CodSunat == "01" || obj.CodSunat == "07") {
        this.ventas[this.ventaActiva].EsOrganizacion = 0;
        this.ventas[this.ventaActiva].NombreOrganizacion = "";
        // this.$refs.inputCliente.$refs.input.disabled = false;
      }

      if (obj.TieneIgv) {
        this.tieneIgv[this.ventaActiva] = true;
      } else {
        this.tieneIgv[this.ventaActiva] = false;
      }
      //this.$refs.inputTipoVenta.setValue(obj.TipoDoc)

      this.ventas[this.ventaActiva].tipoVenta = obj;
      this.getNumeroVenta();
    },
    async getNumeroVenta() {
      // await setTimeout(() => {
      this.$nextTick(async () => {
        await this.axios
          .get(this.API + "ventas/numero", {
            params: {
              idPuntoVenta: this.ventas[this.ventaActiva].puntoVenta
                .IdDocVentaPuntoVenta,
              idTipoDoc: this.ventas[this.ventaActiva].tipoVenta.IdTipoDoc
            }
          })
          .then(response => {
            this.ventas[this.ventaActiva].Serie = response.data.serie;
            this.ventas[this.ventaActiva].Numero =
              response.data.numero + this.ventaActiva;
          })
          .catch(error => {
            console.log(error);
            this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          });
      });
      // }, 1000);
    },
    async setProducto(producto) {
      console.log(producto.precio);
      let productos = this.ventas[this.ventaActiva].productos;
      if (productos.some(v => v.IdProducto == producto.IdProducto)) {
        productos.filter(
          v => v.IdProducto == producto.IdProducto
        )[0].cantidad += parseFloat(1);
        this.$nextTick(() => {
          this.metodoPagoCambio(1);
          this.irTxtLoadBarcode();
        });

        return false;
      }
      // console.log("producto", producto);
      let prod = await this.obtenerStockBuscador(producto.IdProducto);
      // console.log("prod", prod.PreciosPorProducto);
      let pres = [];
      if (prod.PreciosPorProducto) {
        let newPres = JSON.parse(prod.PreciosPorProducto);
        pres = newPres.filter(v => v.cantidad > 0);
      }
      prod.PreciosPorProducto = pres;
      prod.Descripcion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      prod.ProductoPresentacion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      prod.PrecioCostoPre = producto.PrecioCosto;
      prod.cantidad = producto.cantidad ? producto.cantidad : 1;
      prod.precio = producto.precio ? producto.precio : producto.PrecioContado;
      prod.descuento = 0;
      prod.presentacion = null;
      prod.tienePresentacion = prod.PreciosPorProducto.some(
        v => v.cantidad > 0
      );
      prod.bloquearInputPrecio = false;
      prod.CantidadPresentacion = null;
      prod.PrecioCost = producto.PrecioCosto;
      this.showPresentacion(prod, null);
      // productos.unshift(prod);

      // this.$nextTick(() => {
      //   this.irTxtLoadBarcode();
      // });
      this.metodoPagoCambio(1);
    },
    irAlPrimerProducto(event) {
      if (event) {
        event.preventDefault();
      }
      let input = document.getElementById("input0");
      if (input) {
        input.focus();
        input.select();
      }
    },
    quitarProducto(index) {
      this.ventas[this.ventaActiva].productos.splice(index, 1);
      this.$nextTick(() => {
        this.metodoPagoCambio(1);
      });
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    setDocRef(obj) {
      this.ventas[this.ventaActiva].CodSunatModifica = obj.CodSunat;
      this.ventas[this.ventaActiva].NroComprobanteModifica = obj.NroComprobante;
      if (
        this.ventas[this.ventaActiva].tipoVenta.CodSunat == "07" ||
        this.ventas[this.ventaActiva].tipoVenta.CodSunat == "08"
      ) {
        this.ventas[this.ventaActiva].Serie = this.ventas[
          this.ventaActiva
        ].Serie.replace(/^./, obj.NroComprobante[0]);
      }
      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.ventas[this.ventaActiva].Serie,
            idTipoDoc: this.ventas[this.ventaActiva].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.ventaActiva].Numero = response.data.numero;
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
            IdDocVentaPuntoVenta: this.ventas[this.ventaActiva].puntoVenta
              .IdDocVentaPuntoVenta
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
                this.setCliente(response.data.IdCliente);

                this.ventas[this.ventaActiva].CampoDireccion =
                  response.data.Direccion;
                this.ventas[this.ventaActiva].cliente = response.data;

                // let txtCliente = `inputCliente${this.ventaActiva}`;
                this.ventas[this.ventaActiva].verCliente = false;
                this.initValueCliente = this.ventas[
                  this.ventaActiva
                ].cliente.Cliente;
                this.ventas[this.ventaActiva].verCliente = true;
                this.$forceUpdate();
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });

          this.ventas.productos = [];

          response.data.forEach(async element => {
            console.log("Presentacion", element.Presentacion);
            element.Descripcion = element.Presentacion;
            let productoStock = await this.obtenerStockBuscador(
              element.IdProducto
            );
            element.stock =
              Object.keys(productoStock).length > 0 ? productoStock.stock : 0;
            this.ventas[this.ventaActiva].productos.push(element);
            // this.setProducto(element);
          });
          this.ventas[this.ventaActiva].NotaIdMotivo = null;
          this.$forceUpdate();
          this.$nextTick(() => {
            setTimeout(() => {
              this.metodoPagoCambio(1);
            }, 1000);
          });
        });
    },
    setMotivo(ev) {
      if (ev == "01" || ev == "02" || ev == "06" || ev == null) {
        this.bloquearInput = true;
      } else {
        this.bloquearInput = false;
      }
    },
    someMethod(text) {
      this.someMethod = text;
    },
    setCliente(obj, dni = false) {
      let params = {};
      if (dni) {
        params = { dni };
      } else {
        params = { idCliente: obj.IdCliente || obj };
      }

      let ref = `inputCliente${this.ventaActiva}`;
      let inputCliente = this.$refs[ref];
      if (typeof obj === "object") {
        if (inputCliente && typeof inputCliente.setValue === "function") {
          // inputCliente.setValue(obj.Cliente);
        }
        this.ventas[this.ventaActiva].cliente = obj;
        this.ventas[this.ventaActiva].CampoDireccion = obj.Direccion;
        if (obj.DniRuc !== "00000000") {
          // inputCliente.$refs.input.disabled = false;
          this.ventas[this.ventaActiva].EsOrganizacion = 0;
          this.ventas[this.ventaActiva].NombreOrganizacion = "";
        }
      } else {
        this.axios
          .get(this.API + "cliente", { params })
          .then(response => {
            if (response.data) {
              if (inputCliente && typeof inputCliente.setValue === "function") {
                // inputCliente.setValue(response.data.Cliente);
              }
              this.ventas[this.ventaActiva].cliente = response.data;
              this.ventas[this.ventaActiva].CampoDireccion =
                response.data.Direccion;
            } else {
              this.$toasted.show("No se encontró al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    async obtenerStockBuscador(IdProducto) {
      let res = {};
      await this.axios(this.API + "productos/puntoventa/stock", {
        params: {
          idProductos: [IdProducto],
          idAlmacen: this.ventas[this.ventaActiva].almacen.IdAlmacen
        }
      }).then(response => {
        res = response.data[0];
        // return response.data;
      });
      return res;
    },
    getSubTotalDetalle(prod) {
      let subtotal = prod.cantidad * prod.precio;
      return subtotal;
    },
    getTotalDetalle(prod) {
      let total = prod.cantidad * prod.precio - prod.descuento;
      return total;
    },
    getSubTotal() {
      let prod = [...this.ventas[this.ventaActiva].productos];
      if (prod.length == 0) {
        return 0;
      } else {
        let subtotal = prod.reduce((total, prod) => {
          return total + parseFloat(prod.cantidad * prod.precio);
        }, 0);
        return parseFloat(subtotal).toFixed(2);
      }
    },
    getDescuento() {
      let prod = [...this.ventas[this.ventaActiva].productos];
      if (prod.length == 0) {
        return 0;
      } else {
        let subtotal = prod.reduce((total, prod) => {
          return total + parseFloat(prod.descuento);
        }, 0);
        return parseFloat(subtotal).toFixed(2);
      }
    },
    getTotal() {
      let prod = [...this.ventas[this.ventaActiva].productos];
      if (prod.length == 0) {
        return 0;
      } else {
        let subtotal = prod.reduce((total, prod) => {
          // console.log(prod);
          return (
            total + parseFloat(prod.cantidad * prod.precio - prod.descuento)
          );
        }, 0);
        return parseFloat(subtotal).toFixed(2);
      }
    },
    setOpcionVenta(opc) {
      this.ventas[this.ventaActiva].nombreLlevar = "";
      this.ventas[this.ventaActiva].DireccionEntrega = "";
    },
    setComisionista(obj) {
      if (typeof obj === "object") {
        //this.$refs.inputComisionista.setValue(obj.Cliente)
        this.venta.comisionista = obj;
        this.ventas[this.ventaActiva].comisionista = obj;
      } else {
        this.venta.comisionista = {};
        this.ventas[this.ventaActiva].comisionista = {};
      }
    },
    metodoPagoCambio(tipo = null) {
      console.log("llegó");
      this.$nextTick(() => {
        const metodos = ["efectivo", "visa", "mastercard", "delivery"];
        const ventaActual = this.ventas[this.ventaActiva].pago;
        metodos.forEach(metodo => {
          ventaActual[metodo].descripcion = null;
          ventaActual[metodo].monto = 0;
        });
        if (tipo && metodos[tipo - 1]) {
          ventaActual[metodos[tipo - 1]].monto = this.getTotal();
        }
      });
    },
    // recalcularMontos(tipo, monto) {
    //   const metodos = ["efectivo", "visa", "mastercard", "delivery"];
    //   const ventaActual = this.ventas[this.ventaActiva].pago;
    //   ventaActual["efectivo"].monto = this.getTotal() - monto;
    //   ventaActual["visa"].monto = 0;
    //   ventaActual["mastercard"].monto = 0;
    //   ventaActual["delivery"].monto = 0;
    //   if (tipo == 2) {
    //     ventaActual["visa"].monto = monto;
    //   }
    //   if (tipo == 3) {
    //     ventaActual["mastercard"].monto = monto;
    //   }
    //   if (tipo == 4) {
    //     ventaActual["delivery"].monto = monto;
    //   }
    //   // console.log();
    // },
    async guardarVenta() {
      // this.$swal({
      //   title: "Procesando...",
      //   text: "Por favor espera",
      //   allowOutsideClick: false,
      //   showConfirmButton: false,
      //   didOpen: () => {
      //     this.$swal.showLoading();
      //   }
      // });

      let venta = this.ventas[this.ventaActiva];
      let productos = venta.productos;
      let totalPagos = 0;

      //this.$toasted.show("prueba de mensaje");
      // Validación de stock | 0=false (deshabilitado)
      if (this.ventaSinStock === 0) {
        const sinStock = productos.some(
          p => p.cantidad * (p.CantidadPresentacion || 1) > p.stock
        );

        if (sinStock) {
          this.$swal({
            title: "No se puede vender un producto sin stock",
            icon: "error",
            confirmButtonColor: "#d33",
            confirmButtonText: "Aceptar"
          });
          return;
        }
      }
      totalPagos +=
        this.obtenerMonto(venta.pago.efectivo.monto) +
        this.obtenerMonto(venta.pago.visa.monto) +
        this.obtenerMonto(venta.pago.mastercard.monto) +
        this.obtenerMonto(venta.pago.delivery.monto);
      if (this.obtenerMonto(venta.pago.efectivo.monto) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        return;
      }
      if (this.obtenerMonto(venta.pago.visa.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago tarjeta/transferencia no puede ser negativo"
        );
        return;
      }
      if (this.obtenerMonto(venta.pago.mastercard.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        return;
      }
      if (this.obtenerMonto(venta.pago.delivery.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        return;
      }
      if (totalPagos <= 0) {
        this.$toasted.show("Debe seleccionar un método de pago");
        return;
      }

      if (!venta.cliente.DniRuc) {
        //this.$toasted.show('Debe seleccionar un cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Debe seleccionar un cliente",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      var esNumerico = Number.isInteger(parseInt(venta.cliente.DniRuc));
      if (!esNumerico) {
        //this.$toasted.show('El RUC debe ser numérico, editar cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "El RUC debe ser numérico, editar cliente.",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          //// target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }

      var esEmpresa = venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && venta.tipoVenta.CodSunat == "01") {
        //this.$toasted.show('Una factura debe ser emitida para una empresa, no un usuario común.')
        this.$swal({
          position: "center",
          icon: "info",
          title:
            "Una factura debe ser emitida para una empresa, no a un usuario común",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }
      if (esEmpresa && venta.tipoVenta.CodSunat == "03") {
        //this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Una boleta no debe ser emitida para una empresa",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      if (
        venta.tipoVenta.CodSunat == "07" ||
        venta.tipoVenta.CodSunat == "08"
      ) {
        if (!venta.CodSunatModifica || !venta.NroComprobanteModifica) {
          //this.$toasted.show('Debe seleccionar un Documento de Referencia...')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Documento de Referencia...",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            // target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          return;
        }
        if (!venta.NotaIdMotivo) {
          //this.$toasted.show('Debe seleccionar un Motivo')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Motivo",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            // target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          return;
        }
        if (venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");

          this.$refs.inputDocRef.focus();
          return;
        }
        venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == venta.NotaIdMotivo
        )[0].text;
      }
      if (!productos.length) {
        this.$swal({
          position: "center",
          icon: "info",
          title: "No hay productos para vender",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      let val = true;
      productos.forEach(prod => {
        if (prod.precio * prod.cantidad < prod.descuento) {
          this.$toasted.show(
            "No puede vender un producto con descuenta mayor que la venta"
          );
          val = false;
        }
      });
      // productos.forEach(prod => {
      //   if (prod.precio * prod.cantidad < prod.descuento) {
      //     return;
      //   }
      // });

      productos.forEach(prod => {
        if (prod.precio < prod.PrecioCosto) {
          this.$toasted.show(
            "No puede vender un producto por debajo del precio de Costo"
          );
          val = false;
        }
      });

      if (!val) {
        return;
      }

      venta.nombreLlevar = venta.nombreLlevar.toUpperCase();
      // venta.productos = this.productos;
      venta.puntosAplicados = this.puntosAplicados || 0;
      // venta.totalCart = this.totalCart || 0;
      venta.IdPreOrden = this.$route.query.idPreOrden || null;
      venta.IdPedido = this.$route.query.idPedido || null;
      venta.IdProyecto = this.$route.query.idProyecto || null;
      venta.adelantosPreOrden = this.adelantosPreOrden;
      if (venta.selectOpcionVenta != 2) {
        venta.DireccionEntrega = venta.EsDelivery
          ? this.infoDelivery.Direccion || ""
          : "";
      }
      venta.PagoCon = venta.pagocon;
      venta.totalCart = this.getTotal();
      venta.Observacion = (venta.Observacion + "").toUpperCase();
      venta.idProductos = productos.map(prod => prod.IdProducto);
      if (venta.cliente.DniRuc != "00000000") {
        venta.EsOrganizacion = 0;
        venta.NombreOrganizacion = "";
      }
      if (venta.EsOrganizacion) {
        venta.cliente.Cliente = venta.NombreOrganizacion;
      }
      venta.NombreOrganizacion = ("" + venta.NombreOrganizacion)
        .toUpperCase()
        .trim();
      await this.axios.post(this.API + "ventas", venta).then(response => {
        if (response.data.insertId) {
          this.$toasted.success("Venta realizada con exito");
          window.open(
            "/imprimir/index.php?IdDocVenta=" + response.data.insertId,
            "_blank",
            "noopener"
          );
        }

        if (this.ventas.length == 1) {
          window.location.reload();
        } else {
          this.eliminarVentana();
          this.getVendedorInfo();
        }

        // if (response.data.actualizar) {
        //   this.$toasted.show(response.data.msg);
        //   return;
        // }
      });
      this.$swal.close();
    },
    async guardarProforma() {
      let venta = this.ventas[this.ventaActiva];
      let productos = venta.productos;
      let totalPagos = 0;

      totalPagos +=
        this.obtenerMonto(venta.pago.efectivo.monto) +
        this.obtenerMonto(venta.pago.visa.monto) +
        this.obtenerMonto(venta.pago.mastercard.monto) +
        this.obtenerMonto(venta.pago.delivery.monto);
      if (this.obtenerMonto(venta.pago.efectivo.monto) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        return;
      }
      if (this.obtenerMonto(venta.pago.visa.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago tarjeta/transferencia no puede ser negativo"
        );
        return;
      }
      if (this.obtenerMonto(venta.pago.mastercard.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        return;
      }
      if (this.obtenerMonto(venta.pago.delivery.monto) < 0) {
        this.$toasted.show(
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        return;
      }

      if (!venta.cliente.DniRuc) {
        //this.$toasted.show('Debe seleccionar un cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Debe seleccionar un cliente",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      var esNumerico = Number.isInteger(parseInt(venta.cliente.DniRuc));
      if (!esNumerico) {
        //this.$toasted.show('El RUC debe ser numérico, editar cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "El RUC debe ser numérico, editar cliente.",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }

      var esEmpresa = venta.cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && venta.tipoVenta.CodSunat == "01") {
        //this.$toasted.show('Una factura debe ser emitida para una empresa, no un usuario común.')
        this.$swal({
          position: "center",
          icon: "info",
          title:
            "Una factura debe ser emitida para una empresa, no a un usuario común",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }
      if (esEmpresa && venta.tipoVenta.CodSunat == "03") {
        //this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Una boleta no debe ser emitida para una empresa",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      if (
        venta.tipoVenta.CodSunat == "07" ||
        venta.tipoVenta.CodSunat == "08"
      ) {
        if (!venta.CodSunatModifica || !venta.NroComprobanteModifica) {
          //this.$toasted.show('Debe seleccionar un Documento de Referencia...')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Documento de Referencia...",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            // target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          return;
        }
        if (!venta.NotaIdMotivo) {
          //this.$toasted.show('Debe seleccionar un Motivo')
          this.$swal({
            position: "center",
            icon: "info",
            title: "Debe seleccionar un Motivo",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            // target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });

          return;
        }
        if (venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");

          this.$refs.inputDocRef.focus();
          return;
        }
        venta.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == venta.NotaIdMotivo
        )[0].text;
      }
      if (!productos.length) {
        this.$swal({
          position: "center",
          icon: "info",
          title: "No hay productos para vender",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          // target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }

      // if (
      //   productos.some(
      //     v =>
      //       v.cantidad * (v.CantidadPresentacion ? v.CantidadPresentacion : 1) >
      //       v.stock
      //   )
      // ) {
      //   this.$swal({
      //     position: "center",
      //     icon: "info",
      //     title: "No se puede vender un producto sin stock",
      //     showConfirmButton: true,
      //     confirmButtonText: "Entiendo",
      //     // target: document.getElementById("conten"),
      //     confirmButtonColor: "#0275d8"
      //   });
      //   return;
      // }

      venta.nombreLlevar = venta.nombreLlevar.toUpperCase();
      // venta.productos = this.productos;
      venta.puntosAplicados = this.puntosAplicados || 0;
      // venta.totalCart = this.totalCart || 0;
      venta.IdPreOrden = this.$route.query.idPreOrden || null;
      venta.IdPedido = this.$route.query.idPedido || null;
      venta.IdProyecto = this.$route.query.idProyecto || null;
      venta.adelantosPreOrden = this.adelantosPreOrden;
      if (venta.selectOpcionVenta != 2) {
        venta.DireccionEntrega = venta.EsDelivery
          ? this.infoDelivery.Direccion || ""
          : "";
      }
      venta.PagoCon = venta.pagocon;
      venta.totalCart = this.getTotal();
      venta.Observacion = (venta.Observacion + "").toUpperCase();
      venta.idProductos = productos.map(prod => prod.IdProducto);
      if (venta.cliente.DniRuc != "00000000") {
        venta.EsOrganizacion = 0;
        venta.NombreOrganizacion = "";
      }
      if (venta.EsOrganizacion) {
        venta.cliente.Cliente = venta.NombreOrganizacion;
      }
      venta.NombreOrganizacion = ("" + venta.NombreOrganizacion)
        .toUpperCase()
        .trim();
      await this.axios.post(this.API + "proformas", venta).then(response => {
        console.log(response);
        if (response.data.insertId) {
          this.$toasted.success("Proforma realizada con exito");
          window.open(
            "/imprimir/proforma.php?IdProforma=" + response.data.insertId,
            "_blank"
          );
        }

        // setTimeout(() => {
        //   if (this.ventas.length == 1) {
        //     window.location.reload();
        //   } else {
        //     this.eliminarVentana();
        //     this.getVendedorInfo();
        //   }
        // }, 100);
        // if (response.data.actualizar) {
        //   this.$toasted.show(response.data.msg);
        //   return;
        // }
      });
    },
    obtenerMonto(valor) {
      return isNaN(parseFloat(valor)) ? 0 : parseFloat(valor);
    },
    cargarProforma(proforma) {
      console.log(proforma);
      this.initValueCliente = proforma.DniRuc + " " + proforma.Cliente;
      let venta = { ...this.venta };
      venta.cliente = proforma;
      let productos = [];
      proforma.productos.forEach(async producto => {
        let prod = await this.obtenerStockBuscador(producto.IdProducto);
        let pres = [];
        if (prod.PreciosPorProducto) {
          let newPres = JSON.parse(prod.PreciosPorProducto);
          pres = newPres.filter(v => v.cantidad > 0);
        }
        prod.PreciosPorProducto = pres;
        prod.Descripcion = producto.ProductoPresentacion
          ? prod.ProductoPresentacion
          : "UNIDAD";
        prod.ProductoPresentacion = producto.ProductoPresentacion
          ? prod.ProductoPresentacion
          : "UNIDAD";
        prod.cantidad = 1;
        prod.precio = producto.PrecioContado;
        prod.descuento = 0;
        prod.tienePresentacion = prod.PreciosPorProducto.some(
          v => v.cantidad > 0
        );
        prod.bloquearInputPrecio = false;
        prod.CantidadPresentacion = null;
        prod.ProductoPresentacion = producto.Descripcion;
        prod.Descripcion = producto.Descripcion;
        prod.cantidad = producto.Cantidad;
        prod.precio = producto.Precio;
        prod.descuento = producto.Descuento ? producto.Descuento : 0;
        prod.stock = prod.stock;
        productos.push(prod);
        // prod.precio = prod.Precio;
        // productos.push({
        //   ProductoPresentacion: element.Descripcion
        //     ? element.Descripcion
        //     : "UNIDAD",
        //   : element.Descripcion ? element.Descripcion : "UNIDAD",
        //   Producto: prod.Producto,
        //   Producto: prod.Producto,
        //   cantidad: element.Cantidad,
        //   precio: element.Precio,
        //   tienePresentacion: element.Descuento ? element.Descuento : 0,
        //   descuento: element.Descuento ? element.Descuento : 0,
        //   stock: prod.stock
        //   // CantidadPresentacion: element.Descripcion
        // });
      });
      // console.log(proforma.productos);
      venta.productos = productos;
      this.ventas.push(venta);
      this.$nextTick(() => {
        this.ventaActiva = this.ventas.length - 1;
        this.getNumeroVenta();
        this.getVendedorInfo();
        // this.loadClienteVarios();
      });
      this.ventas[this.ventaActiva].cliente = proforma;
      this.$nextTick(() => {
        setTimeout(() => {
          console.log("llegó");
          this.metodoPagoCambio(1);
        }, 1000);
      });
    },
    showPresentacion(prod, index) {
      this.producto = { ...prod };
      this.index = index;
      this.modalPresentacion = true;
    },
    cambiarPresentacion(obj, esPresentacion) {
      let productos = this.ventas[this.ventaActiva].productos;
      let producto = { ...this.producto };
      if (esPresentacion) {
        producto.Descripcion = obj.nombre;
        producto.CantidadPresentacion = obj.cantidad;
        producto.PrecioCost = obj.precioc;
        producto.cantidad = 1;
        producto.bloquearInputPrecio = true;
        producto.precio = obj.precio;
        producto.presentacion = JSON.stringify(obj);
      } else {
        producto.PrecioCost = producto.PrecioCosto;
        producto.Descripcion = obj.ProductoPresentacion;
        producto.CantidadPresentacion = null;
        producto.cantidad = 1;
        producto.bloquearInputPrecio = false;
        producto.precio = producto.PrecioContado;
        producto.presentacion = null;
      }
      if (this.index != null) {
        Vue.set(this.ventas[this.ventaActiva].productos, this.index, producto);
      } else {
        productos.unshift(producto);
      }
      this.modalPresentacion = false;
      this.metodoPagoCambio(1);
    },
    probarImpresora() {
      if (
        this.venta.vendedor != false &&
        Object.keys(this.venta.vendedor).length > 0
      ) {
        let data = {
          Ip: this.venta.vendedor.Ip,
          NombreImpresora: this.venta.vendedor.NombreImpresora,
          NombreRed: this.venta.vendedor.NombreRed,
          Puerto: this.venta.vendedor.Puerto,
          Tipo: this.venta.vendedor.Tipo
        };
        let impresoraJson = JSON.stringify(data);
        var left = screen.width / 2 - 800 / 2;
        var top = screen.height / 2 - 800 / 2;
        let params =
          "scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top=" +
          top +
          ", left=" +
          left;
        window.open(
          "http://localhost:5000/imprimir/test.php?impresora=" +
            encodeURIComponent(impresoraJson),
          "Impresión",
          params
        );
      }
    },
    setScreen() {
      this.navDisabled = !this.navDisabled;
      if (this.navDisabled) {
        window.parent.document.getElementById("NavegadorParent").style.display =
          "none";
      } else {
        window.parent.document.getElementById("NavegadorParent").style.display =
          "block";
      }
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
    },
    //Sockets
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
            // target: document.getElementById("conten"),
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
    }
    //Fin Sockets
  },
  watch: {
    busqueda() {
      this.getProductosCategoriaSearch(this.busqueda);
    }
  }
};
</script>
<style scoped>
.clsError {
  border: 1px solid red !important;
}
.alert {
  margin-bottom: 0;
}
.puntos {
  font-size: 52px;
}
.big-text {
  font-size: 30px;
}
.nomCart {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 2px solid black;
}
.contentCard {
  cursor: pointer;
}
.centrado {
  width: 100%;
  align-items: center !important;
  align-content: center !important;
  text-align: center !important;
}
.calc__numbers {
  width: 15vw;
  height: 15vw;
}
.calc__numbers2 {
  width: 100%;
  height: 20vh;
}
.calc__numbers td {
  min-width: 40px;
  min-height: 40px;
  border: 0.5px solid #a3a3a3;
  cursor: pointer;
  text-align: center;
  vertical-align: middle;
  padding: 1.8% 1.8%;
  font-size: 0.8rem;
  font-weight: bold;
  font-family: Arial;
  color: black;
  background-color: #fff;
}
.calc__numbers td:last-child {
  background-color: #eaeaea;
}
.calc__numbers2 td:last-child {
  background-color: white;
}
.calc__numbers .botones:hover {
  color: #fff;
  background: #65ba9b;
}
.calc__numbers .accionActiva {
  color: #fff !important;
  background: #65ba9b !important;
}

.pagocon .input-group-text {
  font-size: 0.8rem;
  font-weight: bold;
}
.clsError {
  border: 1px solid red !important;
}
</style>
<style>
.titleTabs {
  font-size: 1.2rem !important;
  font-weight: bold;
}
.popover {
  min-width: 400px !important; /* Estilo global para todos los popovers */
}
.headerInfo .input-group-text {
  font-weight: bold;
  font-size: 0.8rem;
}
.headerInfo input {
  font-size: 0.8rem;
}
.buscador .input-group-text {
  font-weight: bold;
  font-size: 0.8rem;
}
.buscador input {
  font-size: 0.8rem;
}
.docInfo .input-group-text {
  font-weight: bold;
  font-size: 0.9rem;
}
.docInfo input {
  font-size: 0.9rem;
  font-weight: bold;
}
.docInfo select {
  font-size: 0.9rem;
  height: auto !important;
}
.clienteInfo * {
  font-size: 0.8rem;
  height: auto !important;
}
#productoPunto:hover {
  transform: scale(1.02);
  transition: 0.5s all;
  box-shadow: 0 20px 80px rgba(0, 0, 0, 0.25);
}
#modalCantidad .modal-dialog {
  max-width: 55% !important;
}
.hidden {
  display: none;
}
</style>
