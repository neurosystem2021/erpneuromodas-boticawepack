<template>
    <div>
        <Menu v-if="false"></Menu>

       
        <b-container fluid class="mt-5">
            <b-row>
                <b-col md="9" style="border-right: 3px solid #C0C0C0; background: white">
                    <b-form @submit="guardarVenta" @keydown.enter="evt => evt.preventDefault()">
                        <b-row>
                            <b-col md="4" class="docInfo mt-2">
                                <b-input-group prepend="Alm.">
                                    <autocomplete
                                        required="required"
                                        ref="inputAlmacen"
                                        :url="API + 'almacenes'"
                                        anchor="Almacen"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setAlmacen" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                            </b-col>
                            <b-col md="4"  class="docInfo mt-2">
                                 <b-input-group prepend="P. Venta">
                                    <autocomplete
                                        required="required"
                                        ref="inputPuntoVenta"
                                        :url="API + 'ventas/puntos'"
                                        anchor="PuntoVenta"
                                        initValue="CAJA1"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setPuntoVenta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                            </b-col>
                            <b-col md="3" class="docInfo mt-2">
                                <b-input-group prepend="Vend.">
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
                                </b-input-group>
                            </b-col>
                            <b-col md="1"  class="docInfo mt-2">
                                <b-button @click="ocultarNav()"><i class="fas fa-eye"></i></b-button>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col md="5"  class="docInfo mt-2">
                                <b-input-group prepend="Doc. Venta">
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoVenta"
                                        :url="API + 'ventas/tipos'"
                                        :customParams="{ EsFacturaVale: venta.EsFacturaVale}"
                                        anchor="TipoDoc"
                                        initValue="BOLETA ELEC."
                                        label="writer"
                                        placeholder=""
                                        :on-select="setTipoVenta" 
                                        readonly
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                            </b-col>
                            <b-col md="4" class="docInfo mt-2">
                                    <b-input-group prepend="Ref." v-if="venta.tipoVenta.CodSunat=='13'" >
                                        <input v-model="venta.RefVale"
                                                type="text"
                                                ref="inputKm"
                                                required
                                                style="text-transform:uppercase;"
                                                class="form-control"
                                                placeholder="Referencia del vale" />
                                    </b-input-group>
                                    <h4 v-if="venta.EsFacturaVale == 1"><b-badge variant="danger">FACTURAR VALES DE CRÉDITO S/ {{  venta.MontoVales | redondear(2) }}</b-badge></h4>
                                    <!--<b-form-checkbox
                                        v-model="venta.EsConsumoVale"
                                        :value="1"
                                        :unchecked-value="0" v-if="venta.tipoVenta.CodSunat=='01'" class="mt-1">
                                        <b>CONSUMO CON VALES</b>
                                    </b-form-checkbox> 
                                    <b-form-checkbox
                                        v-model="venta.EsVale"
                                        :value="1"
                                        @change="estadoVale()"
                                        :unchecked-value="0" v-if="venta.tipoVenta.CodSunat=='12'" class="mt-1">
                                        <b>ES UN VALE</b>
                                    </b-form-checkbox>-->
                                 <b-input-group prepend="Doc. Referenc" v-if="isNota">
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
                                </b-input-group>
                            </b-col>
                            <b-col md="3" class="docInfo mt-2">
                                <b-input-group v-if="isNota" style="margin-bottom:-20px">
                                    <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                                </b-input-group>
                                <!--<b-input-group prepend="Venta Consumo" v-if="venta.EsVale==1">
                                    <autocomplete
                                        required="required"
                                        ref="inputDocRef"
                                        :url="API + 'ventas/consumo/lista/'+venta.puntoVenta.IdDocVentaPuntoVenta"
                                        anchor="NroComprobante"
                                        initValue=""
                                        label="writer"
                                        placeholder=""
                                        :on-select="setDocRefConsumo" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputDocRef')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>-->
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col md="9">
                                <b-row>
                                    <b-col md="4" class="docInfo mt-2">
                                        <b-input-group prepend="Placa" class="mb-1">
                                            <input v-model="venta.Placa"
                                                    type="text"
                                                    ref="inputPlaca"
                                                    style="text-transform:uppercase;"
                                                    class="form-control"
                                                    placeholder="Placa" />
                                         </b-input-group>
                                        <b-input-group prepend="Km">
                                            <input v-model="venta.Km"
                                                    type="number"
                                                    style="text-transform:uppercase;"
                                                    class="form-control"
                                                    placeholder="Kilometraje" />
                                        </b-input-group>
                                        <div v-if="venta.cliente.Cliente && ultimaVentaCliente!='' && venta.cliente.DniRuc != '00000000'" class="text-center mt-1">
                                            <b-row>
                                                <b-col md="2">
                                                    <b-img style="width:35px" src="static/last-buy.png"></b-img>
                                                </b-col>
                                                <b-col md="9" style="align-items: start; justify-content: start; display: flex;">
                                                    <label style="font-weight:bold; font-size:0.95rem">
                                                        {{ultimaVentaCliente}}
                                                    </label>
                                                </b-col>
                                            </b-row>
                                        </div>
                                    </b-col>
                                    
                                    <b-col md="8">
                                        <b-form-group style="margin-bottom: 0 !important;">
                                            <b-input-group class="mt-2" prepend="Cliente" >            
                                                <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                                                <autocomplete
                                                    required="required"
                                                    ref="inputCliente"
                                                     style="width: 25%"
                                                    :url="API + 'clientes'"
                                                    anchor="ClienteDniRuc"
                                                    label="writer"
                                                    placeholder=""
                                                    :onInput="(val) => newDniRuc = val"
                                                    :on-select="setCliente" 
                                                    :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                                    :classes="{input: 'form-control'}">
                                                </autocomplete>
                                                
                                                <input v-model="dni" v-if="false"
                                                    @keydown.enter.prevent="loadClienteBarcode"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Codigo de Barra" />
                                                <b-input-group-append>
                                                    <!--<proveedores-tabla></proveedores-tabla>-->
                                                    <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>

                                                </b-input-group-append>
                                            </b-input-group>
                                            <b-form-select v-if="venta.cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-1">
                                                <option :value="venta.cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                                                <option :value="venta.cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                                                <option :value="venta.cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                                            </b-form-select>

                                        </b-form-group>
                                    </b-col>
                                    <b-col md="12" v-if="false">
                                        <b-form-group style="margin-top:-5px">
                                            <label>Descripcion:</label>
                                            <input v-model="venta.DescripcionEmbarcacion"
                                                    type="text"
                                                    class="form-control"
                                                    placeholder="Descripcion de la embarcación..." />
                                        </b-form-group>
                                    </b-col>
                                    
                                </b-row>
                            </b-col>
                            <b-col md="3" >
                                <b-form-group class="text-left mt-1" v-if="venta.ConsumoId>0">
                                    <b-badge style="font-size:13.5px" variant="primary">Monto Total: S/ {{ MontoTotal }} </b-badge>
                                    <b-badge style="font-size:13.5px" variant="danger">Monto Consumido: S/ {{ MontoConsumido }} </b-badge>
                                    <b-badge style="font-size:13.5px" variant="success">Monto Disponible: S/ {{ MontoTotal - MontoConsumido }} </b-badge>
                                </b-form-group>
                                <b-form-group class="text-center">
                                    <div class="big-text">
                                        {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
                                    </div>
                                    {{ venta.FechaDoc }}
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row class="mx-2">
                          <autocomplete
                            ref="autocompleteProducto"
                            :url="APIProductos"
                            :onShouldRenderChild="renderChild"
                            anchor="Producto"
                            label="writer"
                            placeholder="Agregar Producto"
                            :on-select="setProducto"
                            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                            :classes="{input: 'form-control'}">
                          </autocomplete>
                        </b-row>
                        <b-row class="text-center">
                            <b-col md="3" >
                                <div v-if="venta.cliente.Cliente && venta.cliente.EsVIP">
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
                                </div>
                            </b-col>
                        </b-row>
                        <br>
                        <b-row>
                        
                                <productos-carrito-nuevo style="margin-top:-15px"
                                    @onInputCantidad="actualizarDescuento"
                                    @onAddProducto="onAddProducto"
                                    @onRemoveProducto="actualizarDescuento"
                                    :igvTotal="tieneIgv"
                                    :setPrecio="true"
                                    ref="carrito" 
                                    :showButton="false" 
                                    :EsVale="venta.EsVale"
                                    :bloquearInput="bloquearInput" 
                                    :fields="carrito.fields"
                                    :idAlmacen="venta.almacen.IdAlmacen"
                                    :onSetProducto="onSetProducto"
                                    :touchHab="touchHab" 
                                    >

                                    <template slot="operaciones">
                                        <table class="table b-table border table-footer">
                                            <tbody>
                                                <tr v-if="puntosAplicados == 0">
                                                    <td>
                                                        <b-input-group prepend="S/" append="Descuento">
                                                            <input
                                                                :disabled="bloquearInput?true:false"
                                                                v-model="descuentoGeneral"
                                                                min="0"
                                                                value="0"
                                                                step="0.01"
                                                                @input="(val)=>{aplicarDescuentoGeneral(val.target.value)}"
                                                                type="number"
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
                                                            :disabled="bloquearInput?true:false || isNota"
                                                            v-model="venta.EsCredito"
                                                            :value="1"
                                                            :unchecked-value="0">
                                                            Venta a credito
                                                        </b-form-checkbox>
                                                        <b-form-input 
                                                            :disabled="bloquearInput?true:false || isNota"
                                                            v-if="venta.EsCredito"
                                                            v-model="venta.FechaCredito"
                                                            type="date">
                                                        </b-form-input>
                                                        <b-form-checkbox v-if="false"
                                                            :disabled="bloquearInput?true:false"
                                                            v-model="venta.PorEntregar"
                                                            :value="1"
                                                            :unchecked-value="0">
                                                            Por entregar
                                                        </b-form-checkbox>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </template>    
                                    <template slot="metodopago" v-if="!venta.EsCredito">
                                        <table class="table b-table border table-footer table-sm">
                                            <tbody>
                                                <tr>
                                                    <td>Efectivo</td>
                                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(1)"><i class="fas fa-check"></i></b-button></td>
                                                    <td>
                                                        <b-form-input
                                                            :disabled="bloquearInput?true:false"
                                                            v-model="venta.pago.efectivo.descripcion"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            :disabled="bloquearInput?true:false"
                                                            v-model="venta.pago.efectivo.monto"
                                                            min="0"
                                                            step="0.01"
                                                            type="number"
                                                            placeholder="S/."
                                                            class="form-control"
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Tarjeta</td>
                                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i class="fas fa-check"></i></b-button></td>
                                                    <td>
                                                        <b-form-input
                                                            :disabled="bloquearInput?true:false"
                                                            v-model="venta.pago.visa.descripcion"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            :disabled="bloquearInput?true:false"
                                                            class="form-control"
                                                            v-model="venta.pago.visa.monto"
                                                            min="0"
                                                            step="0.01"
                                                            type="number"
                                                            placeholder="S/."
                                                            @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - $event.target.value)"
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Yape / Plin</td>
                                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                                                    <td>
                                                        <b-form-input
                                                            :disabled="bloquearInput?true:false"
                                                            v-model="venta.pago.mastercard.descripcion"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            :disabled="bloquearInput?true:false"
                                                            class="form-control"
                                                            v-model="venta.pago.mastercard.monto"
                                                            min="0"
                                                            step="0.01"
                                                            type="number"
                                                            placeholder="S/."
                                                            @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - $event.target.value)"
                                                        />
                                                    </td>
                                                </tr>
                                                    <tr>
                                                    <td>Transferencias</td>
                                                    <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                                                    <td>
                                                        <b-form-input
                                                            :disabled="bloquearInput?true:false"
                                                            v-model="venta.pago.transferencias.descripcion"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            :disabled="bloquearInput?true:false"
                                                            class="form-control"
                                                            v-model="venta.pago.transferencias.monto"
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
                                    </template>
                                    <template slot="infoSurtidor">
                                        <b-row>
                                            <b-col md="12" class="text-center">
                                                <label><b>== Observaciones del comprobante ==</b></label>
                                                <b-form-textarea
                                                v-model="venta.Observacion"
                                                :disabled="bloquearInput?true:false"
                                                style="width: 100%; text-transform: uppercase; font-weight: bold; colo:black"
                                                rows="3"
                                                class="form-control"
                                                placeholder="Observaciones del comprobante" />
                                            </b-col>
                                        </b-row>
                                        <!--<b-row style="justify-content: center; margin-top:10px;">
                                            <b-col md="12" class="text-center mb-1">
                                                <b-button type="button" variant="info" size="sm" @click="limpiarPuntoVenta"><i class="fas fa-redo"></i> LIMPIAR PUNTO DE VENTA</b-button>
                                            </b-col>
                                            <b-col md="12">
                                                <table class="table b-table table-sm" id="tablaVentaSurtidorSelec">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>ID</th>
                                                            <th>Producto</th>
                                                            <th>Precio</th>
                                                            <th>Cantidad</th>
                                                            <th>Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(vs, index) in ventaSeleccionadas" :key="index">
                                                            <td :class="{'table-success': vs.Seleccion}">{{ (index+1) }}</td>
                                                            <td :class="{'table-success': vs.Seleccion}">{{ vs.veID }}</td>
                                                            <td :class="{'table-success': vs.Seleccion}">{{ vs.veProducto }}</td>
                                                            <td :class="{'table-success': vs.Seleccion}">{{ vs.vePrecio }}</td>
                                                            <td :class="{'table-success': vs.Seleccion}">{{ vs.veVolumen }}</td>
                                                            <td :class="{'table-success': vs.Seleccion}">{{ vs.veImporte }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>    
                                            </b-col>
                                        </b-row>-->
                                    </template>
                                    <template slot="infoLlevar">
                                        <!--<b-row style="justify-content: center;">
                                            <b-col>
                                                <b-button type="button" :disabled="inProgress" variant="primary" size="md" @click="guardarProforma">Crear Proforma</b-button>
                                            </b-col>
                                        </b-row>-->
                                        <b-row style="justify-content: center; margin-top:5px">
                                            <b-col md="12">
                                                <b-button :disabled="inProgress" variant="danger" size="sm" @click="limpiarPuntoVenta" class="mb-3" v-if="venta.EsFacturaVale == 1">CANCELAR FACTURACION</b-button>
                                                <b-button type="submit" :disabled="inProgress" class="btnAction my-2" variant="success" size="lg">REALIZAR VENTA</b-button>
                                                <b-button  v-if="touchHab" variant="danger" class="btnAction my-2"  @click="()=> {touchHab=!touchHab}"><i class="fas fa-desktop"></i> Deshabilitar Touch</b-button>
                                                <b-button  v-else variant="info" class="btnAction my-2"  @click="()=> {touchHab=!touchHab}"><i class="fas fa-keyboard"></i> Habilitar Touch</b-button>
                                                <!--<h6 v-if="ventaPrueba==1"><br><b-badge variant="danger">MODO PRUEBA CON EL SURTIDOR <br> <br> COMUNICARSE CON EL ADMINISTRADOR</b-badge></h6>-->
                                            </b-col>
                                        </b-row>
                                    </template>
                                </productos-carrito-nuevo>
                            
                        </b-row>
                    </b-form>
                </b-col>
                <b-col md="3">
                    <b-row>
                        <b-col class="text-center">
                            <!--<b-row>
                                <b-col>
                                    <b-button size="md" block :variant="idSurtidor>0?'dark':'danger'"><b>{{ (idSurtidor>0?'SURTIDOR '+idSurtidor:'SIN SURTIDOR') }}</b></b-button>
                                </b-col>
                            </b-row>
                            <b-row class="mt-2" style="margin-bottom: 3px;">
                                <b-col md="7">
                                    <b-form-group horizontal label="Cara" class="mb-0">
                                        <b-input-group>
                                            <b-form-select v-model="idCara" :options="itemsCaraSelect" v-on:change="setCara($event)" class="mb-3" size="md" required/>
                                        </b-input-group>
                                    </b-form-group>
                                </b-col>
                                <b-col md="5">
                                    <b-button size="md" block variant="primary" @click="abrirPanelSurtidor" :disabled="idSurtidor==0"><i class="fas fa-desktop"></i> Panel</b-button>
                                </b-col>
                            </b-row>-->
                            <b-button v-b-toggle.collapse-1 variant="warning" block class="mb-1" style="margin-top: -10px; font-weight: bold;" v-if="idPerfil==1">ACCESOS DIRECTOS</b-button>
                            <b-collapse id="collapse-1" class="mt-1 mb-1">
                              <b-col md="12" class="mt-1">
                                  
                                    <vale-lista @facturar="cargarValesInfo"></vale-lista>
                                </b-col>
                                <b-col md="12" class="mt-1">
                                    <pago-deuda></pago-deuda>
                                </b-col>
                                <!--<b-col md="12" class="mt-1">
                                    <pre-orden-lista :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"></pre-orden-lista>
                                </b-col>
                                <b-col md="12" class="mt-1">
                                    <proformas></proformas>
                                </b-col>
                                <b-col md="12" class="mt-1">
                                    <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
                                </b-col>-->      
                            </b-collapse>
                            
                        </b-col>
                    </b-row>
                    <b-row>    
                      <b-col cols="12">
                        <b-form-select 
                          v-model="IdIsla" 
                          :options="Surtidor" 
                          class="mb-3" 
                          disabled 
                          value-field="IdIsla"
                          text-field="Isla"
                        />
                      </b-col>
                        <b-col cols="6" v-for="(producto , index2) in productosData" :key="index2">
                          <b-card :class="[producto.caraActiva ? 'divProducto' : '', 'mx-1']" @click="anadirProducto(producto)">
                            <b-row style="margin: 0;">
                                <b-col md="7">
                                    <label class="font-weight-bold" style="font-size:0.7rem; cursor:pointer">{{producto.Producto}}</label>
                                </b-col>
                                <b-col md="5" style="align-items: center; justify-content: center; display: flex;">
                                    <b-badge variant="dark" style="font-size: 0.8rem;">S/ {{  parseFloat(producto.PrecioContado).toFixed(2) }}</b-badge>
                                </b-col>
                            </b-row>
                            <b-img  src="static/bombagasolina.png" @error="setAltImg" style="max-width: 150px;" :class="!producto.caraActiva?'imgDisable' :''"></b-img>
                            <center>
                              <b-row class="text-center" style="margin-top:2px; border-top: 1px solid #E6E6E6;">
                                <b-col>
                                    <b-img style="border-radius: 10px; object-fit: contain; margin-bottom:5px; width:265px; padding: 4px;" fluid :src="'/resources/images/productos/' + producto.Imagen" @error="setAltImg" alt="" ></b-img>
                                </b-col>               
                            </b-row>
                              <p class="font-weight-bold my-0">{{producto.DescripcionCara}}</p>
                            </center>
                          </b-card>
                        </b-col>

                        <!-- <b-col md="10" id="productoPunto" class="mx-0 mt-2 text-center bg-white p-0" style="border: 2px solid #E0E0E0; border-radius: 10px; cursor:pointer; height: fit-content; " v-for="(producto , index2) in productosData" :key="index2" @click="setProducto(producto)" :class="{'seleccionProducto': producto.IdProducto == idProductoSelect }">
                            <b-row style="margin: 0;">
                                <b-col md="7">
                                    <label class="font-weight-bold" style="font-size:0.7rem; cursor:pointer">{{producto.Producto}}</label>
                                </b-col>
                                <b-col md="5" style="align-items: center; justify-content: center; display: flex;">
                                    <b-badge variant="dark" style="font-size: 0.8rem;">S/ {{  parseFloat(producto.PrecioContado).toFixed(2) }}</b-badge>
                                </b-col>
                            </b-row>
                            <b-row class="text-center" style="margin-top:2px; border-top: 1px solid #E6E6E6;">
                                <b-col>
                                    <b-img style="border-radius: 10px; object-fit: contain; margin-bottom:5px; width:265px; padding: 4px;" fluid :src="'/resources/images/productos/' + producto.Imagen" @error="setAltImg" alt="" ></b-img>
                                </b-col>               
                            </b-row>
                        </b-col> -->
                    </b-row>
                </b-col>
            </b-row>
            
            <br class="d-none d-lg-block d-xl-block"/>

            <!--<b-modal id="modalPanelSurtidor" size="lg" ref="modalPanelSurtidor" title="PANEL DE VENTA">
                <div class="d-block text-center">
                    <b-row>
                        <b-col md="2"><b><h4>Items:  {{  itemTotal }}</h4></b></b-col>
                        <b-col md="5"><b><h4>Monto Total:  {{  montoTotal  | redondear(3) }}</h4> </b></b-col>
                        <b-col md="5"><b><h4>Cantidad Total:  {{  cantidadTotal | redondear(3) }}</h4></b></b-col>
                    </b-row>
                </div>
                <hr>
                <div class="d-block text-center" style="overflow-y: scroll; height: 45vh;">
                    <table class="table b-table table-sm" id="tablaVentaSurtidor">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ID</th>
                                <th>Producto</th>
                                <th>Fecha</th>
                                <th>Cara</th>
                                <th>Precio</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(vs, index) in ventaSurtidor" :key="index" @click="(e) =>  onCambioSeleccion(index, e)" class="filaVenta">
                                <td :class="{'table-success': vs.Seleccion}">{{ (index+1) }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.veID }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.veProducto }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.veHoraPC }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.veCara }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.vePrecio }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.veVolumen }}</td>
                                <td :class="{'table-success': vs.Seleccion}">{{ vs.veImporte }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div slot="modal-footer">
                    <b-btn :disabled="itemTotal==0" variant="success" class="mr-2" @click="guardarVentasSeleccionadas()">
                    Guardar
                    </b-btn>
                    <b-btn  variant="secondary" @click="$refs['modalPanelSurtidor'].hide()">
                    Cerrar
                    </b-btn>
                </div>
            </b-modal>-->
            <b-modal ref="modalCant" size="xl" id="modalCantidad" hide-footer hide-header>
                <div class="d-block text-center">
                    <h3 style="text-decoration: underline;">{{productoSelect.Producto}}</h3>
                    <h5>Precios Disponibles</h5>
                </div>
                <hr>
                <b-row>
                    <b-col cols="6">
                        <b-card
                            align="center"
                            class="mb-3 contentCard"
                            style="cursor: pointer;"
                            @click="() => {
                                setProductoUni(productoSelect, true, productoSelect.PrecioContado)
                            }"
                        >
                            <b-row>
                            <b-col md="6" class="nomCartP">
                                <h5 class="text-uppercase">VENTA</h5>
                            </b-col>
                            <b-col md="6" class="nomCartP2">
                                <b class="mr-1">Precio: </b> S/ {{ productoSelect.PrecioContado | moneda }}
                            </b-col>
                            </b-row>
                        </b-card>
                    </b-col>
                    <b-col cols="6" v-for="d in preciosProd" :key="d.id">
                        <b-card
                            align="center"
                            class="mb-3 contentCard"
                            style="cursor: pointer;"
                            @click="() => {
                            if ( parseFloat(d.precio) > 0 ) {
                                setProductoUni(productoSelect, true, d.precio)
                            }
                            }"
                        >
                            <b-row>
                            <b-col md="6" class="nomCartP">
                                <h5 class="text-uppercase">{{ d.nombre }}</h5>
                            </b-col>
                            <b-col md="6" class="nomCartP2">
                                <b class="mr-1">Precio: </b> S/ {{ d.precio | moneda }}
                            </b-col>
                            </b-row>
                        </b-card>
                    </b-col>
                </b-row>
            </b-modal>
        </b-container>
    </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarritoNuevo from "@/components/ProductosCarritoNuevo";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";
import ValeLista from "@/components/ValeLista.vue";

export default {
  beforeMount() {
    this.initForm();
  },
  mounted() {
    //this.getNumeroVenta()
    this.getVendedor();
    //setInterval(this.updateTime, 1000)
    this.loadClienteVarios();
    this.IdIsla = sessionStorage.getItem("IdSurtidor");
    if (this.IdIsla == 0) {
      this.$swal.fire({
        icon: "warning",
        title: "Isla",
        text: "No se ha seleccionado una isla"
      });
    }
    //this.obtenerSurtidor()
    //this.estadoSistema()
    // this.ObtenerLstOfertas();
    this.obtenerHistorialActivo();
    this.ObtenerIsla();
    this.cargarProductos();
  },
  data() {
    return {
      touchHab: false,
      lstOfertas: [],
      lstHistorico: [],
      IdIsla: "",
      Surtidor: [
        { value: 1, text: "Surtidor 1" },
        { value: 2, text: "Surtidor 2" },
        { value: 0, text: "Administrador" }
      ],
      Cara: "",
      dni: "",
      isNota: false,
      newDniRuc: "",
      ultimaVenta: "",
      inProgress: false,
      API: config.API,
      APISURTIDOR: "http://192.168.1.209:8008",
      puntosAplicados: 0,
      descuentoPuntos: {},
      descuentoGeneral: 0,
      tieneIgv: false,
      venta: {},
      bloquearInput: false,
      ultimaVentaCliente: "",
      productosData: [],
      navDisabled: false,
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
          //{ key: 'selec',label:'Selec.',class:'fontsize thsize'},
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "fontsize thsize"
          },
          {
            key: "DescripcionCara",
            label: "Cara",
            class: "productosize thsize"
          },
          { key: "Producto", class: "productosize thsize" },
          //{ key: 'ProductoCategoria', label: 'Categoría' },
          //{ key: 'ProductoMarca', label: 'Marca' },
          //{ key: 'ProductoMedicion', label: 'Medicion' },
          { key: "cantidad", class: "thsize" },
          { key: "precio", label: "Precio", class: "thsize" },
          { key: "eventPres", label: "", class: "productosize thsize" },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center thsize"
          },
          { key: "descuento", label: "Descuento", class: "thsize" },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "fontsize thsize"
          },
          { key: "acciones", label: "", class: "thsize" }
        ]
      },
      TieneIgvComprobante: false,
      MontoTotal: 0,
      MontoConsumido: 0,
      idCara: null,
      idSurtidor: 0,
      ventaSurtidor: [],
      itemTotal: 0,
      montoTotal: 0,
      cantidadTotal: 0,
      ventaSeleccionadas: [],
      productoSelect: {},
      idProductoSelect: null,
      surtidores: [
        {
          IdIsla: 1,
          itemsCara: [{ value: 0, text: "0" }, { value: 1, text: "1" }]
        },
        {
          IdIsla: 2,
          itemsCara: [{ value: 3, text: "3" }, { value: 4, text: "4" }]
        }
      ],
      itemsCaraSelect: [],
      idPerfil: sessionStorage.getItem("IdPerfil"),
      ventaPrueba: 0,
      preciosProd: []
    };
  },
  watch: {
    totalCart(newVal) {
      this.venta.pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.venta.pago.visa.monto) +
            parseFloat(this.venta.pago.mastercard.monto) +
            parseFloat(this.venta.pago.transferencias.monto))
      ).toFixed(2);
    }
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/stock/" + this.idAlmacen;
      }
      return this.API + "productos";
    }
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      add: "agregarProducto",
      actualizarProd: "actualizarProducto"
    }),
    async ObtenerLstOfertas() {
      await this.axios(`${this.API}ofertas/listadoimpresion`).then(res => {
        if (res) {
          this.lstOfertas = res.data;
        }
      });
    },
    setProducto(obj) {
      obj = Object.assign({}, obj);
      this.productoSelect = obj;
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      this.preciosProd = this.preciosProd.filter(p => p.precio > 0);
      if (this.preciosProd.length == 0) {
        this.setProductoUni(obj);
        return;
      }
      // this.$refs["modalCant"].show();
    },
    async ObtenerIsla() {
      await this.axios(`${this.API}isla`).then(res => {
        if (res) {
          this.Surtidor = res.data;
        }
      });
    },
    async obtenerHistorialActivo() {
      await this.axios(`${this.API}historialgalones/obtener_activos`).then(
        res => {
          if (res) {
            this.lstHistorico = res.data;
          }
        }
      );
    },
    anadirProducto(producto) {
      producto.IdHistorico = null;
      if (this.lstHistorico.length > 0) {
        let Historico = this.lstHistorico.filter(
          v =>
            v.IdProducto == producto.IdProducto &&
            v.Cara == producto.Cara &&
            v.IdIsla == this.IdIsla
        );

        producto.IdHistorico = Historico.IdHistorico;
      }
      let historico = this.lstHistorico.find(
        v =>
          v.IdProducto == producto.IdProducto &&
          v.Cara == producto.Cara &&
          v.IdIsla == producto.IdIsla
      );
      // producto.cantidad =
      // producto.IdHistorico = Historico.IdHistorico;
      producto.precio = producto.precio;
      producto.IdHistorico = historico.IdHistorico
        ? historico.IdHistorico
        : null;
      if (producto.caraActiva) {
        this.setProducto(producto);
      }
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
              } else {
                IdOferta.push(oferta.IdOferta);
              }
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
    estadoSistema() {
      this.axios
        .get(this.API + "config/sistema")
        .then(res => {
          this.ventaPrueba = res.data;
        })
        .catch(err => {
          this.ventaPrueba = 0;
        });
    },
    cargarValesInfo(obj) {
      this.venta.IdDocVentas = [];
      this.venta.EsFacturaVale = 0;
      this.axios
        .get(this.API + "vales/clientes/detalle/facturar", {
          params: { idCliente: obj.id, idDocVentas: obj.idDocVentas }
        })
        .then(response => {
          if (response.data.success) {
            this.$refs.inputCliente.setValue(response.data.cliente.Cliente);
            this.venta.cliente = response.data.cliente;
            this.venta.CampoDireccion = this.venta.cliente.Direccion;
            this.getUltimaVentaCliente(this.venta.cliente.IdCliente);
            let productos = response.data.productos;
            this.limpiarCart();
            productos.map(item => {
              this.add([item, () => {}, false]);
            });
            this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
            this.venta.MontoVales = obj.monto;
            this.venta.EsFacturaVale = 1;
            this.venta.tipoVenta = {
              IdTipoDoc: 7,
              TipoDoc: "BOLETA ELEC.",
              LimitItems: 0,
              CodSunat: "03"
            };
            this.venta.EsVale = 0;
            this.venta.ConsumoId = 0;
            this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
            this.venta.IdDocVentas = response.data.iddocventas.map(
              idv => idv.idDocVenta
            );
            this.getNumeroVenta();
            //this.bloquearInput = true
          } else {
            this.$toasted.show("No se encontro al cliente");
          }
        })
        .catch(error => {
          console.log(error);
        });
    },
    obtenerSurtidor() {
      this.idSurtidor = 0;
      this.itemsCaraSelect = [];
      this.idCara = null;
      let id = sessionStorage.getItem("IdIsla");
      if (!(id > 0)) {
        this.$toasted.show("No se especifico un surtidor, ingrese nuevamente", {
          duration: 4000
        });
        return;
      }
      let surtidor = this.surtidores.find(s => s.IdIsla == id);
      if (surtidor) {
        this.idSurtidor = surtidor.IdIsla;
        this.itemsCaraSelect = surtidor.itemsCara;
        this.idCara = surtidor.itemsCara[0].value;
      } else {
        this.$toasted.show("No se especifico un surtidor, ingrese nuevamente", {
          duration: 4000
        });
      }
    },
    guardarVentasSeleccionadas() {
      this.limpiarCart();
      let preciosub = 0;
      let preciodet = 0;
      let items = 0;
      this.ventaSeleccionadas = this.ventaSurtidor.filter(vs => vs.Seleccion);
      this.ventaSeleccionadas.map(vs => {
        items++;
        preciosub += Number(vs.veImporte);
        preciodet += Number(vs.vePrecio);
      });
      let obj = Object.assign({}, this.productoSelect);
      obj.descuento = 0;
      obj.precio = Math.round((preciodet / items) * 100) / 100;
      obj.sutotal = Math.round(preciosub * 100) / 100;
      obj.cantidad =
        Math.round((preciosub / (preciodet / items)) * 10000) / 10000;
      this.$toasted.info("Producto agregado");
      this.add([
        obj,
        () => {
          this.onAddProducto(obj);
        }
      ]);
      this.$refs["modalPanelSurtidor"].hide();
    },
    onCambioSeleccion(index, e) {
      this.ventaSurtidor[index].Seleccion = !this.ventaSurtidor[index]
        .Seleccion;
      if (this.ventaSurtidor[index].Seleccion) {
        e.target.parentNode.classList.add("seleccionado");
      } else {
        e.target.parentNode.classList.remove("seleccionado");
      }

      this.itemTotal = 0;
      this.montoTotal = 0;
      this.cantidadTotal = 0;
      this.ventaSurtidor.map(vs => {
        if (vs.Seleccion) {
          this.itemTotal++;
          this.montoTotal += Number(vs.veImporte);
          this.cantidadTotal += Number(vs.veVolumen);
        }
      });
    },
    abrirPanelSurtidor() {
      let elem = document.getElementById("tablaVentaSurtidor");
      elem.classList.remove("seleccionado");
      this.itemTotal = 0;
      this.montoTotal = 0;
      this.cantidadTotal = 0;
      this.ventaSurtidor = [];
      let params = {
        veCara: this.idCara,
        veEstado: 0,
        veProducto: this.productoSelect.CodigoBarra
      };
      this.axios
        .get(this.APISURTIDOR + "/sale/api/aventa/", {
          params: params
        })
        .then(response => {
          this.ventaSurtidor = response.data;
          this.ventaSurtidor.map(vs => {
            vs.Seleccion = false;
          });
          this.$refs["modalPanelSurtidor"].show();
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Error no hay comunicación con el panel");
        });
    },
    setCara(event) {
      this.idCara = event;
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    async cargarProductos() {
      let params = {
        IdIsla: this.IdIsla,
        Usuario: sessionStorage.getItem("User")
      };
      this.productoSelect = {};
      this.productosData = [];
      this.idProductoSelect = null;
      await this.axios
        .get(this.API + "productos/lineal", {
          params: params
        })
        .then(response => {
          let index = 0;
          response.data.forEach(producto => {
            let prodA = { ...producto };
            let prodB = { ...producto };
            prodA.caraActiva = producto.TieneCaraA == 1;
            prodA.CaraA = producto.TieneCaraA == 1;
            prodA.Cara = 1;
            prodA.DescripcionCara = "CARA A";
            prodA.index = index;
            this.productosData.push(prodA);
            index++;
            prodB.caraActiva = producto.TieneCaraB == 1;
            prodB.CaraB = producto.TieneCaraB == 1;
            prodB.Cara = 2;
            prodB.DescripcionCara = "CARA B";
            prodB.index = index;
            this.productosData.push(prodB);
            index++;
          });
          //this.productosData = response.data;
          //  if (this.productosData.length > 0) {
          //  this.productoSelect = this.productosData[0];
          // this.idProductoSelect = this.productosData[0].IdProducto;
          // }
        })
        .catch(error => {
          console.log(error);
          this.productoSelect = {};
          this.idProductoSelect = null;
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      console.log(this.productosData);
    },
    ocultarNav() {
      this.navDisabled = !this.navDisabled;
      console.log(this.navDisabled);
      if (this.navDisabled) {
        window.parent.document.getElementById("NavegadorParent").style.display =
          "none";
      } else {
        window.parent.document.getElementById("NavegadorParent").style.display =
          "block";
      }
    },
    metodoPagoCambio(opc) {
      switch (opc) {
        case 1:
          this.venta.pago.visa.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.transferencias.monto = 0;
          this.venta.pago.efectivo.monto = this.totalCart;
          break;
        case 2:
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.transferencias.monto = 0;
          this.venta.pago.visa.monto = this.totalCart;
          break;
        case 3:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.transferencias.monto = 0;
          this.venta.pago.mastercard.monto = this.totalCart;
          break;
        case 4:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.transferencias.monto = this.totalCart;
          break;

        default:
          break;
      }
    },
    setProducto(obj) {
      this.actualizarPrecio = false;
      this.$refs.autocompleteProducto.setValue("");
      obj = Object.assign({}, obj);
      this.productoSelect = obj;
      //obj.FLETE = 0
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      this.preciosProd = this.preciosProd.filter(p => p.precio > 0);
      // if (this.preciosProd.length == 0) {
      this.setProductoUni(obj);
      // return;
      // }
      // this.$refs["modalCantC"].show();

      /*
      obj = Object.assign({}, obj);
      //obj.FLETE = 0
      obj.precio = 0
      obj.cantidad = 0
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0
      obj.nuevoPrecioContado = 0
      obj = this.onSetProducto(obj)
      this.add([obj, () => {
        this.$emit('onAddProducto', obj)
      }])

      // Vue.set(this.productos, obj.IdProducto, obj)*/
    },
    setProductoUni(obj, seleccion = false, precio = false) {
      obj.precio = 0;
      obj.cantidad = 0;
      //obj.IdLote = 0
      //obj.TieneIgv = 0
      obj.descuento = 0;
      obj.sutotal = "";
      obj.nuevoPrecioContado = 0;
      obj = this.onSetProducto(obj, precio);
      obj.precio = obj.precio;
      let existe = this.productos.find(
        p => p.IdProducto == obj.IdProducto && p.Cara == obj.Cara
      );
      if (existe == undefined) {
        this.$toasted.info("Producto agregado");
        this.add([
          obj,
          () => {
            this.$emit("onAddProducto");
            this.actualizarProd(obj);
          }
        ]);
      } else {
        if (this.actualizarPrecio) {
          this.$toasted.info("El precio cambiado");
          this.actualizarProd(obj);
          this.actualizarPrecio = false;
        } else {
          this.$toasted.error("El producto ya esta agregado");
        }
      }
      // this.$refs["modalCantC"].hide();
    },
    showPresentacion(obj) {
      obj = Object.assign({}, obj);
      this.productoSelect = obj;
      this.preciosProd = JSON.parse(obj.PreciosPorProducto || "[]");
      this.preciosProd = this.preciosProd.filter(p => p.precio > 0);
      this.actualizarPrecio = true;
      this.$refs["modalCantC"].show();
    },
    limpiarPuntoVenta() {
      this.ultimaVentaCliente = "";
      this.$toasted.info("Se limpio el punto de venta", { duration: 3000 });
      this.limpiarDespuesVenta();
      this.$refs.carrito.pagocon = 0;
    },
    estadoVale(vale) {
      this.venta.EsVale = 0;
      this.venta.ConsumoId = 0;
      this.MontoConsumido = 0;
      this.MontoTotal = 0;
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
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
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    initForm() {
      this.descuentoGeneral = 0;

      this.venta = {
        EsCredito: 0,
        Serie: "",
        Placa: "",
        Km: "",
        Numero: "",
        cliente: {},
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA ELEC.",
          LimitItems: 0,
          CodSunat: "03"
        },
        almacen: {
          IdAlmacen: 1
        },
        puntoVenta: {
          IdDocVentaPuntoVenta: 1,
          PuntoVenta: "CAJA1"
        },
        vendedor: this.venta.vendedor || {},
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          transferencias: { IdMetodoPago: 4, monto: 0 }
        },
        CampoDireccion: "Direccion",
        DescripcionEmbarcacion: "",
        IdComprobanteModifica: 0,
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsConsumoVale: 0,
        EsVale: 0,
        ConsumoId: 0,
        EsFacturaVale: 0,
        EsFacturaValeNC: 0,
        IdDocVentas: [],
        MontoVales: 0,
        RefVale: "",
        Observacion: ""
      };

      this.isNota = false;
      this.bloquearInput = false;
      this.tieneIgv = false;
      this.MontoConsumido = 0;
      this.MontoTotal = 0;
      //this.loadFirstAlmacen()
    },
    setTipoVenta(obj) {
      /*if(obj.CodSunat=='12' || obj.TieneIgv==1){
                let pass = window.prompt('Escriba su contraseña')
                if ((''+pass).trim()!='') {
                    this.axios.post(this.API + 'login', {
                    user: this.venta.almacen.IdAlmacen==2?'admin2':'admin',
                    password: pass
                    }).then(r => {
                        if (r.data) {
                            this.isNota = false
                            this.bloquearInput=false
                            this.venta.NroComprobanteModifica = ''
                            this.venta.IdComprobanteModifica = 0
                            this.venta.CodSunatModifica = ''
                            this.venta.NotaIdMotivo = ''
                            this.venta.NotaDescMotivo = ''
                            this.TieneIgvComprobante = false
                            this.venta.EsConsumoVale = 0
                            this.venta.EsVale = 0
                            this.venta.ConsumoId = 0
                            this.MontoConsumido = 0
                            this.MontoTotal = 0
                            this.$refs.inputDocRef && this.$refs.inputDocRef.setValue('');
                            if (obj.CodSunat == "07" || obj.CodSunat == "08") { // 07 - nota de credito, 08 - Nota de debito
                                this.isNota = true
                                this.limpiarCart()
                                this.venta.Placa = ''
                            }
                            if (obj.TieneIgv) {
                                this.tieneIgv = true
                            } else {
                                this.tieneIgv = false
                            }
                            this.venta.tipoVenta = obj
                            this.getNumeroVenta()
                            this.$toasted.info('Autorizado')
                        }else{
                            this.noAutorizado()
                        }
                    }).catch((error)=>{
                        this.noAutorizado()
                    })
                }else{
                    this.noAutorizado()
                }
            } else{*/
      this.isNota = false;
      this.bloquearInput = false;
      this.venta.NroComprobanteModifica = "";
      this.venta.IdComprobanteModifica = 0;
      this.venta.CodSunatModifica = "";
      this.venta.NotaIdMotivo = "";
      this.venta.NotaDescMotivo = "";
      this.TieneIgvComprobante = false;
      this.venta.EsConsumoVale = 0;
      this.venta.EsVale = obj.EsVale;
      this.venta.ConsumoId = 0;
      this.MontoConsumido = 0;
      this.MontoTotal = 0;
      this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");
      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota = true;
        this.limpiarCart();
        this.venta.Placa = "";
      }
      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }
      this.venta.tipoVenta = obj;
      this.getNumeroVenta();
      //}
    },
    noAutorizado() {
      this.venta.tipoVenta = {
        IdTipoDoc: 7,
        TipoDoc: "BOLETA ELEC.",
        LimitItems: 0,
        CodSunat: "03"
      };
      this.tieneIgv = false;
      this.TieneIgvComprobante = false;
      this.venta.IdComprobanteModifica = 0;
      this.venta.EsConsumoVale = 0;
      this.venta.EsVale = 0;
      this.venta.ConsumoId = 0;
      this.MontoConsumido = 0;
      this.MontoTotal = 0;
      this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
      this.getVendedor();
      this.$toasted.error("No autorizado");
    },
    setDocRef(obj) {
      this.venta.EsConsumoVale = obj.EsConsumoVale;
      this.venta.CodSunatModifica = obj.CodSunat;
      this.venta.NroComprobanteModifica = obj.NroComprobante;
      this.venta.IdComprobanteModifica = obj.IdDocVenta;
      this.venta.EsFacturaValeNC = obj.EsFacturaVale;
      if (obj.EsFacturaVale == 1) {
        this.$toasted.info(
          "Es una factura de vales realizados, los vales pasarán a pendiente",
          { duration: 5000 }
        );
      }
      this.TieneIgvComprobante = obj.TieneIgv == 1 ? true : false;
      if (this.TieneIgvComprobante != this.tieneIgv) {
        if (this.TieneIgvComprobante) {
          this.$toasted.show(
            "El comprobante de referencia tiene IGV, seleccione NOTA DE CREDITO/IGV ELEC.",
            { duration: 3000 }
          );
        } else {
          this.$toasted.show(
            "El comprobante de referencia no tiene IGV, seleccione NOTA DE CREDITO ELEC.",
            { duration: 3000 }
          );
        }
      }

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
          this.venta.Placa = response.data[0].Placa || "";
          this.venta.Km = response.data[0].Km || "";
          this.venta.DescripcionEmbarcacion =
            response.data[0].DescripcionEmbarcacion;
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
                this.venta.CampoDireccion = this.venta.cliente.Direccion;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiarCart();
          response.data.map(item => {
            this.add([item, () => {}, false]);
          });
          this.venta.NotaIdMotivo = null;
          // this.bloquearInput = true;
          this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
        });
    },
    setDocRefConsumo(obj) {
      this.$toasted.info(
        "Se cargo información de la Venta, si desea cancelar la creacion del vale, click en LIMPIAR ubicado en la parte derecha",
        { duration: 10000 }
      );
      this.venta.ConsumoId = obj.IdDocVenta;
      this.MontoConsumido = obj.SubTotalConsumo;
      this.MontoTotal = obj.SubtotalIni;
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: this.venta.puntoVenta.IdDocVentaPuntoVenta
          }
        })
        .then(response => {
          this.venta.Placa = "";
          this.venta.Km = "";
          this.venta.DescripcionEmbarcacion = "";
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
                this.venta.CampoDireccion = this.venta.cliente.Direccion;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiarCart();
          response.data.map(item => {
            this.add([item, () => {}, false]);
          });
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
    setAlmacen(obj) {
      this.venta.almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      if (this.productos.length > 0) {
        this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
      }
    },
    setPuntoVenta(obj) {
      this.venta.puntoVenta = obj;
      this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    loadAlmacen(id) {
      this.axios.get(this.API + "almacenes/id/" + id).then(res => {
        this.setAlmacen(res.data);
      });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
      });
    },
    getVendedor() {
      this.$refs.inputAlmacen.$refs.input.disabled = true;
      this.$refs.inputPuntoVenta.$refs.input.disabled = true;
      this.$refs.inputVendedor.$refs.input.disabled = true;
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          // this.$refs.inputVendedor.$refs.input.disabled = true
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      this.venta.vendedor = obj;
    },
    setCliente(obj, dni = false) {
      let params = {};
      if (dni) {
        params = {
          dni: dni
        };
      } else {
        params = {
          idCliente: obj
        };
      }

      if (typeof obj === "object") {
        this.$refs.inputCliente.setValue(obj.Cliente);
        this.venta.cliente = obj;
        this.venta.CampoDireccion = this.venta.cliente.Direccion;
        this.ultimaVentaCliente = "";
        this.getUltimaVentaCliente(this.venta.cliente.IdCliente);
      } else {
        this.ultimaVentaCliente = "";
        this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              this.$refs.inputCliente.setValue(response.data.Cliente);
              this.venta.cliente = response.data;
              this.venta.CampoDireccion = this.venta.cliente.Direccion;
              this.getUltimaVentaCliente(this.venta.cliente.IdCliente);
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    getUltimaVentaCliente(idCliente) {
      let params = {
        IdCliente: idCliente
      };
      this.axios
        .get(this.API + "cliente/ultima/venta", {
          params: params
        })
        .then(response => {
          if (response.data) {
            this.ultimaVentaCliente = response.data.FechaDoc;
          } else {
            this.ultimaVentaCliente = "";
          }
        })
        .catch(error => {
          console.log(error);
        });
    },
    onSetProducto(obj, precio) {
      console.log(precio);
      obj.cantidad = 1;
      obj.precio = precio > 0 ? precio : obj.PrecioContado || 0;
      return obj;
    },
    onAddProducto(obj) {
      this.actualizarDescuento();
      this.venta.pago.visa.monto = 0;
      this.venta.pago.mastercard.monto = 0;
      this.venta.pago.transferencias.monto = 0;
      this.obtenerStok({
        IdAlmacen: this.venta.almacen.IdAlmacen
      });
    },
    actualizarDescuento() {
      this.venta.pago.visa.monto = 0;
      this.venta.pago.mastercard.monto = 0;
      this.venta.pago.transferencias.monto = 0;
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
      this.loadClienteVarios();
      this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
      // this.$refs.inputAlmacen.setValue('VENTA')
      //this.$refs.inputPuntoVenta.setValue('CAJA1')
      this.$refs.inputCliente.setValue("");
      // this.$refs.inputVendedor.setValue('')
      this.$refs.carrito.pagocon = "";
      this.ventaSeleccionadas = [];
    },
    guardarProforma() {
      if (!this.productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
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

      this.venta.productos = this.productos;

      this.axios
        .post(this.API + "proformas", this.venta)
        .then(response => {
          console.log(response);
          this.$toasted.show("Proforma Guardada con exito");

          this.limpiarDespuesVenta();

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
    },
    guardarVenta(evt) {
      evt.preventDefault();

      this.inProgress = true;

      /*if(this.venta.EsVale == 1 && this.venta.ConsumoId == 0){
                this.$toasted.show('Debe seleccionar una venta consumo...')
                this.inProgress = false
                return 
            }*/

      if (this.venta.EsVale == 0) {
        this.venta.ConsumoId = 0;
      }

      /*if(this.venta.EsVale == 1 && this.venta.ConsumoId > 0 && this.totalCart > (this.MontoTotal - this.MontoConsumido)){
                this.$toasted.show('El monto de la venta debe ser menor o igual al disponible: '+(this.MontoTotal - this.MontoConsumido),{duration:3000})
                this.inProgress = false            
                return  
            }

            if(this.venta.EsVale == 1 && this.venta.ConsumoId > 0 && (''+this.venta.Placa).trim() == ''){
                this.$toasted.show('Debe ingresar una placa para identificar al vehículo que consumió el vale',{duration:3000})
                this.$refs.inputPlaca.focus()
                this.inProgress = false
                return 
            }*/

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
      totalPagos += isNaN(parseFloat(this.venta.pago.transferencias.monto))
        ? 0
        : parseFloat(this.venta.pago.transferencias.monto);
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
        this.$toasted.show("El metodo de pago tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.mastercard.monto))
          ? 0
          : parseFloat(this.venta.pago.mastercard.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago yape/plin no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.transferencias.monto))
          ? 0
          : parseFloat(this.venta.pago.transferencias.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show("El metodo de pago yape/plin no puede ser negativo");
        this.inProgress = false;
        return;
      }

      if (!this.venta.EsCredito) {
        if (
          parseFloat(this.totalCart).toFixed(2) !=
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

      var esEmpresa = this.venta.cliente.DniRuc.length < 9 ? true : false;
      if (!esEmpresa && this.venta.tipoVenta.CodSunat == "03") {
        this.$toasted.show("La boleta debe ser emitida a un usuario común");
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

      if (!this.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      if (
        this.venta.tipoVenta.CodSunat == "13" &&
        ("" + this.venta.RefVale).toUpperCase().trim() == ""
      ) {
        this.$toasted.show(
          "Ingrese una referencia VÁLIDA para el VALE DE CREDITO"
        );
        this.inProgress = false;
        return;
      }

      if (
        this.venta.tipoVenta.CodSunat == "07" ||
        this.venta.tipoVenta.CodSunat == "08"
      ) {
        if (this.TieneIgvComprobante != this.tieneIgv) {
          if (this.TieneIgvComprobante) {
            this.$toasted.show(
              "El comprobante de referencia tiene IGV, seleccione NOTA DE CREDITO/IGV ELEC.",
              { duration: 3000 }
            );
          } else {
            this.$toasted.show(
              "El comprobante de referencia no tiene IGV, seleccione NOTA DE CREDITO ELEC.",
              { duration: 3000 }
            );
          }

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

      // NO PERMITE CANTIDAD Y PRECIOS EN CERO
      let sinStock = 0;
      let totalCero = 0;
      this.productos.forEach(pro => {
        if (pro.stock <= 0) {
          sinStock += 1;
        }

        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }
      });

      if (totalCero >= 1) {
        this.$toasted.error("No se puede enviar un producto en S/. 0.00");
        this.inProgress = false;
        return;
      }

      if (
        parseFloat(totalPagos) >= 600 &&
        this.venta.tipoVenta.CodSunat == "03" &&
        this.venta.cliente.DniRuc == "00000000"
      ) {
        this.$toasted.error(
          "Venta con boleta mayor a 600 soles, no puede ser emitida con cliente varios"
        );
        this.inProgress = false;
        return;
      }

      if (
        parseFloat(totalPagos) >= 600 &&
        this.venta.tipoVenta.CodSunat == "03" &&
        ("" + this.venta.Placa).trim() == ""
      ) {
        this.$toasted.error(
          "Venta con boleta mayor a 600 soles, por favor ingrese la placa del vehiculo"
        );
        this.inProgress = false;
        this.$refs.inputPlaca.focus();
        return;
      }

      if (
        this.venta.tipoVenta.CodSunat == "01" &&
        ("" + this.venta.Placa).trim() == ""
      ) {
        this.$toasted.error(
          "Venta con factura, por favor ingrese la placa del vehiculo"
        );
        this.inProgress = false;
        this.$refs.inputPlaca.focus();
        return;
      }

      this.venta.productos = this.productos;
      this.venta.IdIsla = this.IdIsla;
      this.venta.puntosAplicados = this.puntosAplicados || 0;
      this.venta.totalCart = this.totalCart || 0;
      this.venta.PagoCon = this.$refs.carrito.pagocon;
      this.venta.Placa = ("" + this.venta.Placa).toUpperCase();
      this.venta.Km = ("" + this.venta.Km).toUpperCase();
      this.venta.RefVale =
        this.venta.tipoVenta.CodSunat == "13"
          ? ("" + this.venta.RefVale).toUpperCase().trim()
          : "";
      this.venta.Observacion = ("" + this.venta.Observacion)
        .toUpperCase()
        .trim();
      this.axios
        .post(this.API + "ventas", this.venta)
        .then(async response => {
          if (response.data.error) {
            this.inProgress = false;
            this.$toasted.show(response.data.msg, { duration: 5000 });
            return;
          } else {
            /*try {
                        if(this.venta.tipoVenta.CodSunat != '07' && this.ventaSeleccionadas.length > 0 && this.ventaPrueba == 0){
                            let repuesta = await this.axios.put(this.APISURTIDOR+'/update', { ids: this.ventaSeleccionadas.map(vs => vs.veID),})
                        }
                    } catch (error) {
                        
                    }*/
            this.ultimaVentaCliente = "";
            this.$toasted.success("Venta realizada con exito");
            this.ultimaVenta = response.data.insertId;
            let IdOfertas = this.generarOfertas();
            let IdOfertasString = IdOfertas.join(",");
            this.limpiarDespuesVenta();

            window.open(
              "/imprimir/index.php?IdDocVenta=" +
                response.data.insertId +
                "&IdOfertas=" +
                IdOfertasString,
              "_blank"
            );
            this.$refs.carrito.pagocon = 0;
          }
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    eliminarCliente() {
      this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = "ss";

        // cambiar color los vencidos
        var dt = new Date();
        var anio = dt.getFullYear();
        var mes = dt.getMonth() + 1;
        var dia = dt.getDate();
        var fechaVen = new Date(data.FechaVen).getTime();

        var hoy = anio + "-" + mes + "-" + dia;
        hoy = new Date(hoy).getTime();
        var en4meses = anio + "-" + (mes + 4) + "-" + dia;
        en4meses = new Date(en4meses).getTime();

        if (fechaVen < hoy && data.stock > 0) {
          style = "danger";
        }

        // cambiar color los que faltan 4 meses por vencer
        if (fechaVen >= hoy && fechaVen <= en4meses && data.stock > 0) {
          style = "ambar";
        }

        return `
        <div class="${style}">
          <b class="bg-success" style="color: #fff; padding: 0 6px">${
            data.stock
          }</b> | <b>${data.CodigoBarra}</b> / 
          <b>${data.Producto}</b> / ${data.ProductoMarca} 
        </div>
        `;
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoMedicion}
      `;
    }
  },
  components: {
    ProductosCarritoNuevo,
    ClientesForm,
    ConfigVenta,
    Proformas,
    PagoDeuda,
    PreOrdenLista,
    ValeLista
  }
};
</script>
<style>
.puntos {
  font-size: 52px;
}
.big-text {
  font-size: 30px;
}
#modalConfigVenta .modal-dialog {
  max-width: 65% !important;
}
.contenedor {
  display: flex !important;
  flex-wrap: nowrap !important;
  overflow-y: auto !important;
}
.columna {
  flex: 0 0 auto !important;
}
</style>
<style>
td.fontsize {
  font-size: 0.8rem !important;
  font-weight: bold;
  text-align: center;
  vertical-align: middle !important;
}
td.stocksize {
  text-align: center !important;
  vertical-align: middle !important;
}
th.stocksize {
  font-size: 0.8rem !important;
  text-align: center;
}
th.thsize {
  font-size: 0.85rem !important;
  text-align: center;
}
td.thsize {
  vertical-align: middle !important;
}
td.productosize {
  font-size: 1.5rem !important;
  font-weight: bold;
  text-align: center;
  vertical-align: middle !important;
}
.popover-body {
  background-color: #c1c1c1;
}
</style>
<style>
.nav-tabs .nav-link.active,
.nav-tabs .nav-item.show .nav-link {
  background-color: #e6e6e6;
  border-bottom: none;
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
#tablaVentaSurtidor th {
  background: white;
  position: sticky;
  top: 0; /* Don't forget this, required for the stickiness */
  box-shadow: 0 2px 2px -1px rgba(0, 0, 0, 0.4);
}
#modalPanelSurtidor .modal-dialog {
  max-width: 65% !important;
}
.filaVenta td:hover {
  cursor: pointer !important;
}

.seleccionado {
  background-color: "#c3e6cb";
}

#tablaVentaSurtidorSelec {
  font-size: 0.7rem !important;
}

.seleccionProducto {
  /*border: 7.5px solid #007bff !important;*/
}
.nomCartP2 {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
}
.nomCartP {
  display: flex;
  align-items: center;
  border-right: 2px solid black;
  justify-content: center;
  align-content: center;
}
#modalCantidad .modal-dialog {
  max-width: 47% !important;
}
.divProducto {
  cursor: pointer !important;
}

.imgDisable {
  filter: grayscale(100%);
}
</style>
