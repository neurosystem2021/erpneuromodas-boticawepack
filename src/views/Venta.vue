<template>
    <div>
        <b-container fluid class="bv-example-row" id="conten">
            <b-row>
                <b-col md="6" style="border-right: 3px solid #C0C0C0; background: white">
                    <b-form @submit="guardarVenta" @keydown.enter="prevent">
                    <b-row>
                        <b-col md="12" v-if="precioHabilitado" style="margin-bottom:-10px">
                            <div class="form-inline">
                                <h5><b-badge variant="warning">EL CAMBIO DE PRECIO SE HABILITO SOLO PARA ESTA VENTA</b-badge></h5>
                            </div>
                        </b-col>
                        <b-col md="12" v-if="this.$route.query.idProyecto">
                            <div class="form-inline">
                                <h4>Pago de pedido personalizado: <b-badge variant="dark">{{this.$route.query.idProyecto}}</b-badge> </h4>
                            </div>
                        </b-col>
                        <b-col md="12" v-if="this.$route.query.idPreOrden">
                            <div class="form-inline">
                                <h4>Pago de la mesa: <b-badge variant="danger">{{mesa}}</b-badge> </h4>
                                <h4 class="ml-3">Atendido por: <b-badge variant="danger">{{mesero}}</b-badge></h4>
                            </div>
                        </b-col>
                        <b-col md="12" v-if="this.$route.query.idPedido">
                            <div class="form-inline">
                                <h4>Pago de delivery código: <b-badge variant="info">{{this.$route.query.idPedido}}</b-badge></h4>
                            </div>
                        </b-col>
                        <b-col md="5" class="docInfo">
                            <b-input-group prepend="Doc. Venta" class="mt-2">
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoVenta"
                                        :url="API + 'ventas/tipos'"
                                        anchor="TipoDoc"
                                        initValue="NOTA DE VENTA"
                                        class="font-weight-bold"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setTipoVenta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                            </b-input-group>
                        </b-col>
                        <b-col md="4" class="docInfo">
                            <b-input-group prepend="Doc. Referenc" class="mt-2" v-if="isNota">
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
                        <b-col md="3" class="docInfo">
                            <b-input-group class="mt-2" v-if="isNota" style="margin-bottom:-20px">
                                <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                            </b-input-group>
                        </b-col>
                        <b-col md="8" class="clienteInfo" style="margin-bottom:-20px">
                            <b-form-group>
                                <b-input-group class="mt-2">
                                    <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                                    <autocomplete
                                        required="required"
                                        ref="inputCliente"
                                        :url="API + 'clientes'"
                                        anchor="ClienteDniRuc"
                                        label="writer"
                                        placeholder="Cliente"
                                        class="font-weight-bold"
                                        :onInput="(val) => newDniRuc = val"
                                        :on-select="setCliente" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                    <b-input-group-append>
                                        <!--<proveedores-tabla></proveedores-tabla>-->
                                        <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                                        
                                    </b-input-group-append>
                                </b-input-group>
                                <b-form-select v-if="venta.cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-3">
                                    <option :value="venta.cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                                    <option :value="venta.cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                                    <option :value="venta.cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                                </b-form-select>
                            </b-form-group>

                        </b-col>
                        <b-col md="4" style="  display: flex; align-items: center; justify-content: center;">
                            <b-form-group class="text-center">
                                <div class="big-text">
                                    {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
                                </div>
                                {{ venta.FechaDoc }}
                            </b-form-group>
                        </b-col>
                    </b-row>
                    <b-row  v-if="adelanto" style="margin-top:-5px; margin-bottom:5px">
                        <b-col  class="text-center">
                            <b-badge variant="info" style="font-size:1.2rem">Adelanto de: S/ {{ adelanto }} </b-badge>
                            <b-badge variant="success" style="font-size:1.2rem">Total a pagar: S/ {{ $options.filters.redondear(totalCart - adelanto) }} </b-badge>
                        </b-col>
                    </b-row>
                    <b-row>
                        <productos-carrito-nuevo ref="carrito" :bloquearInput="bloquearInput" @onRemoveProducto="actualizarDescuento" :esDelivery="venta.EsDelivery" :precioHabilitado="precioHabilitado" :descuentoActivo="venta.almacen.DescuentoActivo" :isVentas="true" :touchHab="touchHab">
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
                                            <td>Visa</td>
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
                                            <td>Yape</td>
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
                                            <td>Delivery</td>
                                            <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                                            <td>
                                                <b-form-input
                                                    :disabled="bloquearInput?true:false"
                                                    v-model="venta.pago.delivery.descripcion"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
                                                    :disabled="bloquearInput?true:false"
                                                    class="form-control"
                                                    v-model="venta.pago.delivery.monto"
                                                    min="0"
                                                    step="0.01"
                                                    type="number"
                                                    placeholder="S/."
                                                    @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - $event.target.value)"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                            <template slot="operaciones">
                                <table class="table b-table border table-footer">
                                    <tbody>
                                        <tr v-if="puntosAplicados == 0">
                                            <td>
                                                <b-input-group prepend="S/" append="Descuento">
                                                    <input
                                                        :disabled="bloquearInput?true:false || (venta.almacen.DescuentoActivo==0)"
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        @input="(val)=>{aplicarDescuentoGeneral(val.target.value)}"
                                                        type="number"
                                                        step="0.01"
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
                            <template slot="botones">
                                <b-row class="m-1">
                                    <!--<b-col md="12" class="mt-1">
                                        <b-button v-if="precioHabilitado" variant="danger" block @click="habilitarPrecio()"><i class="fas fa-lock fa-sm"></i> Bloquear Precio</b-button>
                                        <b-button v-else variant="warning" block @click="habilitarPrecio()"><i class="fas fa-unlock fa-sm"></i> Habilitar Precio</b-button>
                                    </b-col>-->
                                    <!--<b-col md="12" class="mt-1">
                                        <b-button type="button" block variant="success" @click="setScreen"><i class="fas fa-desktop"></i> {{navDisabled?'Mostrar Nav':'Ocultar Nav'}}  </b-button>
                                    </b-col>-->
                                    <b-col md="12" class="mt-1">
                                        <b-button v-if="touchHab" variant="danger" block @click="()=> {touchHab=!touchHab}"><i class="fas fa-desktop"></i> Deshabilitar Touch</b-button>
                                        <b-button v-else variant="success" block @click="()=> {touchHab=!touchHab}"><i class="fas fa-keyboard"></i> Habilitar Touch</b-button>
                                    </b-col>
                                    <b-col md="12" class="mt-1">
                                        <b-button type="button" block variant="primary" @click="LimpiarPuntoVenta"><i class="fas fa-redo"></i> Actualizar</b-button>
                                    </b-col>
                                    <b-col md="12" class="mt-1">
                                        <pago-deuda></pago-deuda>
                                    </b-col>
                                    <!--<b-col md="12" class="mt-1">
                                        <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                                    </b-col>-->
                                    <!--<b-col md="12" class="mt-1">
                                        <ordenes-modal @onPagarOrden="onPagarOrden" :vendedor="venta.vendedor" ></ordenes-modal>
                                    </b-col>-->
                                    <b-col md="12" class="mt-1">
                                        <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente"></proformas>
                                    </b-col>
                                    <!--<b-col md="12" class="mt-1">
                                        <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                                    </b-col>-->
                                    <b-col md="12" class="mt-1">
                                        <b-button variant="secondary" block @click="probarImpresora()"><i class="fas fa-print fa-sm"></i> Probar Impresora</b-button>
                                    </b-col>
                                </b-row>
                            </template>
                            <template slot="infoLlevar">
                                <b-row class="m-1" v-if="!this.$route.query.idPreOrden">
                                    <b-col md="12" class="text-left">
                                        <b-form-radio-group id="radios1"
                                                            @change="setOpcionVenta"
                                                            v-model="venta.selectOpcionVenta"
                                                            :options="opcionesVenta"
                                                            name="radioOptions">
                                        </b-form-radio-group>
                                    </b-col>
                                    <b-col md="12" v-if="venta.selectOpcionVenta > 0">
                                        <b-form-group>
                                            <label style="font-weight:bold">Nombre:</label>
                                            <b-form-input required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese cliente" autocomplete="off" v-model="venta.nombreLlevar" ref="inputNombreCliente" type="text" name="NombreLlevar" id="NombreLlevar"></b-form-input>
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="12" v-if="venta.selectOpcionVenta == 2">
                                        <b-form-group>
                                            <label style="font-weight:bold">Direccion Entrega:</label>
                                            <b-form-textarea required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="venta.DireccionEntrega" ref="inputDireccionEntrega" type="text" name="DireccionEntrega" id="DireccionEntrega"></b-form-textarea>
                                        </b-form-group>
                                    </b-col>
                                </b-row>
                            </template>
                        </productos-carrito-nuevo> 
                    </b-row>
                    <b-row align-h="center" class="text-center mt-2">
                        <!--<b-col cols="12" v-if="impresoraCocina.length === 0 ">
                            <label>No se cargo la impresora de comanda porfavor actualice la pagina antes de vender</label>
                        </b-col>-->
                        <b-col cols="12">
                            <b-button type="button" @click="LimpiarPuntoVenta" variant="dark" v-if="this.$route.query.idProyecto">Anular Pago Pedido</b-button>
                            <b-button type="button" @click="anularPagoOrden" variant="danger" v-if="this.$route.query.idPreOrden">Anular Pago Orden</b-button>
                            <b-button type="button" @click="anularPagoDelivery" variant="danger" v-if="this.$route.query.idPedido">Anular Pago Delivery</b-button>
                            <b-button type="button" size="sm" v-if="!this.$route.query.idPreOrden && !this.$route.query.idPedido" :disabled="inProgress" variant="primary"  @click="guardarProforma">Crear Proforma</b-button>
                            <b-button type="submit" :disabled="inProgress" variant="success" >Efectuar Venta</b-button>
                            <b-button v-if="ultimaVenta"  variant="primary" @click="imprimirUltimaVenta">Imprimir última Venta</b-button>
                            <b-button v-if="Object.keys(ultimaComanda).length !==0 " variant="info" @click="imprimirUltimaComanda">Imprimir última Comanda</b-button>
                        </b-col>
                    </b-row>
                    </b-form>
                </b-col>
                <b-col md="6">
                    <b-row class="headerInfo" style="background: #F4F4F4;">
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
                                        :classes="{input: 'form-control'}">
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
                                    :classes="{input: 'form-control'}">
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
                                    :classes="{input: 'form-control'}">
                                </autocomplete>
                            </b-input-group>
                        </b-col> 
                    </b-row>


                    <b-row style="background: #F4F4F4;" class="buscador">
                        <b-col md="8">
                            <b-input-group prepend="Buscar Producto" class="mt-2 mb-2">
                                <b-form-input placeholder="INGRESE UN PRODUCTO" ref="inputSearchProducto" autocomplete="off" @input="(val)=>{debounceInput(val); cargandoProductos = true; productosCategoria = [];}" v-model="busqueda"></b-form-input>
                                <b-input-group-append>
                                    <b-button variant="secondary" @click="limpiarFiltro">Limpiar</b-button>
                                </b-input-group-append>
                            </b-input-group>
                        </b-col>
                        <b-col md="4">
                            <b-input-group class="mt-2 mb-2">
                                <input placeholder="CODIGO DE BARRAS" v-model="loadCode"
                                    @keydown.enter.prevent="loadBarcode"
                                    autocomplete="off"
                                    ref="barcodeInput"
                                    type="none"
                                    inputmode="none"
                                    class="form-control"
                                />
                                <b-input-group-append>
                                    <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
                                </b-input-group-append>
                            </b-input-group> 
                        </b-col>
                    </b-row>
                    <b-row style="background: #F4F4F4;">
                            <b-tabs v-model="tabActive" style="font-size:1.1rem !important; font-weight:500">
                                <b-tab no-body v-for="(item, index) in categorias" :key="index"  :title="item.ProductoCategoria" :ref="'tab'+index" @click="getProductosCategoria(item.IdProductoCategoria)">
                                </b-tab>
                                <b-tab v-if="busqueda!=''" :active="busqueda!=''" no-body title="BUSQUEDA">
                                </b-tab>
                            </b-tabs>
                        
                    </b-row>
                    <b-row style="position: relative; height:79vh; overflow-y: scroll; background: #E6E6E6;">
                        <b-col>
                            <b-row class="mt-2">
                                <b-col v-if="cargandoProductos" style="display: flex; justify-content: center;  align-items: center; height:70.5vh;">
                                    <vue-simple-spinner size="huge"></vue-simple-spinner>
                                </b-col>
                                <b-col id="productoPunto" class="m-2 text-center bg-white p-2" cols="2" style="border: 2px solid #E0E0E0; border-radius: 10px; cursor:pointer" v-for="(producto , index2) in productosCategoria" :key="index2" @click="setProducto(producto)">
                                    <b-row class="mb-1">
                                        <b-col md="4" class="text-left" v-if="false">
                                            <b-badge :variant="parseFloat(producto.stock)<=0?'danger':'success'" style="font-size: 0.8rem;">{{producto.stock? Number.isInteger(producto.stock)?producto.stock:producto.stock.toFixed(2):producto.stock}}</b-badge>
                                        </b-col>
                                        <b-col md="12" class="text-center">
                                            <b-badge variant="dark" style="font-size: 1rem;">S/ {{  parseFloat(producto.PrecioContado).toFixed(2) }}</b-badge>
                                        </b-col>
                                    </b-row>
                                    <b-img style="border-radius: 10px;" fluid :src="'/resources/images/productos/' + producto.Imagen" @error="setAltImg" alt="" ></b-img>
                                    <label class="font-weight-bold" style="font-size:rem; cursor:pointer">
                                        {{producto.Producto}}
                                    </label>
                                </b-col>
                            </b-row>
                        </b-col> 
                    </b-row>
                </b-col>
            </b-row>
        
            <b-modal ref="modalCant" size="xl" id="modalCantidad" hide-footer hide-header>
                <div class="d-block text-center">
                <h3 style="text-decoration: underline;">PRESENTACIONES</h3>
                <h5>{{objProd.Producto}}</h5>
                </div>
                <hr>
                <b-row>
                <b-col cols="6">
                    <b-card
                    align="center"
                    class="mb-3 contentCard"
                    @click="setProductoUni(objProd)"
                    >
                    <b-row>
                        <b-col md="7" class="nomCart">
                        <h5 class="text-uppercase">{{medicionPro}}</h5>
                        </b-col>
                        <b-col md="5" class="text-left">
                        <b>Precio: </b>S/ {{ precioCont }}
                        </b-col>
                    </b-row>
                    </b-card>
                </b-col>

                <b-col cols="6" v-for="d in preciosProd" :key="d.id">
                    <b-card
                    v-if="parseFloat(d.cantidad) > 0"
                    align="center"
                    class="mb-3 contentCard"
                    :class="{'disabledMouse': !parseFloat(d.cantidad)}"
                    @click="() => {
                        if ( parseFloat(d.cantidad) ) {
                        setProductoCant(d)
                        }
                    }"
                    >
                    <b-row>
                        <b-col md="7" class="nomCart" >
                        <h5 style="text-transform:uppercase">{{ d.nombre }}</h5>
                        </b-col>
                        <b-col md="5" class="text-left">
                        <h5>x{{ d.cantidad }}</h5>
                        <b>Precio: </b>S/ {{ d.precio }}
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
import { mapActions, mapState } from 'vuex'
import config from '@/store/config.js'
import ClientesForm from '@/components/ClientesForm'
import ConfigVenta from '@/components/ConfigVenta'
import PagoDeuda from '@/components/PagoDeuda'
import PreOrdenLista from '@/components/PreOrdenLista.vue'
import Proformas from '@/components/ProformaLista.vue'
//import OrdenesModal from '@/components/OrdenesModal.vue'
//import DeliveryModal from '@/components/DeliveryModal.vue'
import Spinner from 'vue-simple-spinner'
import ProductosCarritoNuevo from '@/components/ProductosCarritoNuevo'
import debounce  from 'lodash'
import Swal from 'sweetalert2'
export default {
    beforeMount () {
        this.initForm()
        
    },
    beforeDestroy(){
        //this.sockets.unsubscribe('nuevo-pedido');
    },
    mounted () {
        // this.getNumeroVenta()
       // this.getVendedor()
      //setInterval(this.updateTime, 1000)
      //this.escucharPedidos()
      this.getVendedorInfo()
      //this.getCategorias()
      //this.getImpresoraCocina()
      //if(this.$route.query.idProyecto)
      if(this.$route.query.idProyecto && this.$route.query.idCliente ){
          console.log("Probar");
          this.getProyecto()
      }else{
        this.loadClienteVarios()
      }
      //this.$refs.inputNombreCliente.$refs.input.required = true  
    },
    data () {
        return {
            dni: '',
            isNota: false,
            adelanto: 0,
            gasto: 0,
            newDniRuc: '',
            ultimaVenta: '',
            ultimaVentaData:{},
            inProgress: false,
            mesa:'',
            mesero:'',
            API: config.API,
            medicionPro:'UNIDAD',
            movimientoLotes: {},
            precioCont: 0,
            preciosProd: [],
            objProd: {},
            touchHab: false,
            puntosAplicados: 0,
            descuentoPuntos: {},
            descuentoGeneral: 0,
            tieneIgv: false,
            venta: {},
            bloquearInput:false,
            adelantosPreOrden:[],
            itemsMotivoNota: [
                { value: null, text: '-- SELECCIONE EL MOTIVO --' },
                { value: '01', text: 'ANULACION DE LA OPERACION' },
                { value: '02', text: 'ANULACION POR ERROR EN EL RUC' },
                { value: '03', text: 'CORRECION POR ERROR EN LA DESCRIPCION' },
                { value: '04', text: 'DESCUENTO GLOBAL' },
                { value: '05', text: 'DESCUENTO POR ITEM' },
                { value: '06', text: 'DEVOLUCION TOTAL' },
                { value: '07', text: 'DEVOLUCION POR ITEM' },
                { value: '08', text: 'BONIFICACION' },
                { value: '09', text: 'DISMINUCION EN EL VALOR' },
                { value: '10', text: 'OTROS CONCEPTOS' }
            ],
            carrito: {
                fields: [
                   // { key: 'manoobra', label: 'Mano de obra', class:'text-center' },
                    //{ key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
                    { key: 'Producto' },
                    { key: 'ProductoCategoria', label: 'Categoria'},
                   // { key: 'eventPres', label: '' },
                   // { key: 'cantxPrecio', variant: 'warning' },
                    // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
                    // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
                    /*{ key: 'ProductoMedicion', label: 'Medición' },*/
                    { key: 'cantidad' },
                    { key: 'precio', label: 'Precio' },
                    //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'text-center' }, 
                    { key: 'descuento', label: 'Descuento'},
                    { key: 'total', label: 'Total', variant: 'danger', class:'text-center' },
                    { key: 'acciones', label: ''}
                ]
            },
            infoDelivery:{},
            //Nuevos datos
            categorias: [],
            productosCategoria:[],
            cargandoProductos:true,
            busqueda:'',
            tabActive:0,
            loadCode: '',
            impresoraCocina:[],
            ultimaComanda:{},
            opcionesVenta: [
                { text: 'Normal', value: 0 },
                { text: 'Delivery', value: 2 },
            ],
            navDisabled:false,
            APPTOKEN:'',
            precioHabilitado: false
        }
    },
    watch: {
        totalCart (newVal) {
             this.venta.pago.efectivo.monto = newVal - (parseFloat(this.venta.pago.visa.monto) + parseFloat(this.venta.pago.mastercard.monto))
        },
    },
    computed: {
        ...mapState({
            productos: state => state.cart.productos,
            totalCart: state => state.cart.totalCart
        }),
        manoDeObraBruta () {
            let pManoObra = this.productos.find(p => p.CodigoBarra === 'MANODEOBRA');
            let precio = pManoObra ? pManoObra.precioBruta : 0;
            return precio
        },
        manoDeObraNeta () {
            let pManoObra = this.productos.find(p => p.CodigoBarra === 'MANODEOBRA');
            let precio = pManoObra ? pManoObra.precio : 0;
            return precio
        }
    },
    methods: {
        ...mapActions({
            aplicarDescuento: 'aplicarDescuentoGeneral',
            limpiarCart: 'limpiarCarrito',
            obtenerStok: 'obtenerStock',
            actualizarPrecioConvenio: 'actualizarPrecioConvenio',
            add:'agregarProducto',
            actualizarProd : 'actualizarProducto',
        }),
        //Nuevos metodos de venta
        habilitarPrecio(){
            if(this.precioHabilitado){
                let result = confirm("¿Se bloqueará el cambio de precios de los productos?" );
                if (result == true){
                    this.precioHabilitado = false
                     this.$toasted.info("¡Precios bloqueados!")
                    return;
                }
            }
            let result = confirm("¿Desea habilitar el cambio de precios de los productos?" );
            if (result == true) {
                this.$swal({
                title: 'Autorización de Administrador',
                html: `<form onsubmit="event.preventDefault();"><label>Habilitar el cambio de precios</label>
                <input type="password" id="password" class="swal2-input" placeholder="Ingrese su contraseña"><form>`,
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Autorizar',
                cancelButtonText: 'Cancelar',
                target: document.getElementById('conten'),
                focusConfirm: false,
                    preConfirm: () => {
                    const password = this.$swal.getPopup().querySelector('#password').value
                    if (!password) {
                    this.$swal.showValidationMessage('Ingrese la contraseña de administrador')
                    }
                    return {password: password }
                }
                }).then((result) => {
                    if (result.value.password) {
                        this.axios.post(this.API + 'login', {
                        user: 'admin',
                        password: result.value.password
                        }).then(r => {
                            if (r.data) {
                                this.precioHabilitado=true
                            }else{
                                this.$toasted.error("¡Error de contraseña, No autorizado cambio de precios!")
                            }
                        })
                    }else{
                        this.$toasted.error("¡No autorizado para el cambio de precios!")
                    }
                })
            }
        },
        getProyecto(){
            this.axios.get(this.API + 'proyecto', { params: {
				IdProyecto		: this.$route.query.idProyecto,
			} }).then(res => {
				if(res.data){
                    this.adelanto=res.data.Adelantos
                    this.adelantosPreOrden = res.data.AdelantosArreglo
                    this.$refs.inputCliente.setValue(res.data.Cliente)
                    this.venta.CampoDireccion = res.data.Direccion
                    this.venta.cliente.IdCliente = res.data.IdCliente
                    this.venta.cliente.Cliente = res.data.Cliente
                    this.venta.cliente.Direccion = res.data.Direccion
                    this.venta.cliente.DniRuc = res.data.DniRuc
                    console.log( this.venta.cliente.IdCliente);
                    res.data.Detalle.map((item) => {
                        this.add([item, () => {
                           
                        }, {cantidad: item.Cantidad, precio: item.Precio } ])
                    })
				}
			})
        },
        getVendedorInfo(){
            let usuario = sessionStorage.getItem('User')
            this.axios.get(this.API + 'usuarios/info/' + usuario)
            .then((response) => {
                this.setVendedor(response.data)
                let almacen = {
                    IdAlmacen: response.data.IdAlmacen,
                    Almacen: response.data.Almacen,
                    DescuentoActivo: response.data.DescuentoActivo
                }
                let puntoVenta ={
                    IdDocVentaPuntoVenta: response.data.IdDocVentaPuntoVenta,
                    PuntoVenta:  response.data.PuntoVenta
                }
                this.APPTOKEN = response.data.APPTOKEN
                this.setAlmacen(almacen)
                this.setPuntoVenta(puntoVenta)
                this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                // bloquear ambos inputs
                this.$refs.inputAlmacen.$refs.input.disabled = true
                this.$refs.inputPuntoVenta.$refs.input.disabled = true
                this.$refs.inputVendedor.$refs.input.disabled = true
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        getCategorias(){
            this.axios.get(this.API + 'categorias/puntoventa').then((response) => {
                this.categorias = response.data
                if(this.categorias.length>0){
                    let masMov = {ProductoCategoria:'MAS MOVIDOS',IdProductoCategoria:0}
                    this.categorias.unshift(masMov)
                    setTimeout(() => {
                        this.tabActive = 0
                        let primerCategoria = this.categorias[0].IdProductoCategoria
                        this.getProductosCategoria(primerCategoria) 
                    }, 300);
                }
            }).catch(error=>{
                this.$toasted.show('Hubo un error, no hay conexion')
            })
        },
        getProductosCategoria(idProductoCategoria){
            if(this.venta.almacen.IdAlmacen){
                this.productosCategoria = []
                this.cargandoProductos = true
                this.busqueda = ''
                this.axios.get(this.API + 'productos/categoria', {
                    params: {
                        IdProductoCategoria: idProductoCategoria,
                        idAlmacen:this.venta.almacen.IdAlmacen,
                        MasMovido: idProductoCategoria==0?1:0
                    }
                }).then((response) => {
                    this.cargandoProductos = false;
                    this.productosCategoria = response.data
                    //console.log(this.productosCategoria,"izi");
                }).catch(error=>{
                    this.cargandoProductos = true;
                    this.productosCategoria = [];
                    this.$toasted.show('Hubo un error, no hay conexion')
                })
            }
        },
        getImpresoraCocina(){
            this.axios.get(this.API + 'impresora/cocina').then((response) => {
                if(response.data){
                    this.impresoraCocina = response.data
                }
            }).catch(error=>{
                this.$toasted.show('Hubo un error, no hay conexion')
            })
        },
        setAltImg(event) { 
            event.target.src = "static/no-photo.png" 
        },
        debounceInput: _.debounce(function (e) {
            console.log("entra");
            if(e.trim()==''){
                return ;
            }
            this.buscarProducto(e)
        }, 250),
        buscarProducto(val){
            this.productosCategoria = [];
            this.cargandoProductos = true;
            this.axios.get(this.API + 'productos/categoria', {
                params: {
                    q: val
                }
            }).then((response) => {
                this.cargandoProductos = false;
                this.productosCategoria = response.data
                this.$refs.barcodeInput.focus()
                this.$refs.inputSearchProducto.focus()
            }).catch(error=>{
                this.cargandoProductos = true;
                this.productosCategoria = [];
                this.$toasted.show('Hubo un error, no hay conexion')
            })
        },
        limpiarFiltro(){
            this.busqueda = ''
            this.tabActive = 0
        },
        obtenerStockBuscador(){
            axios.get(API + 'calculo/productos/stock', {
                params: {
                    idProductos: this.productosCategoria.map(prod => prod.IdProducto),
                    idAlmacen: this.venta.almacen.IdAlmacen
                }
            }).then((response) => {
                this.productosCategoria = this.productosCategoria.map((prod) => {
                    let index = response.data.findIndex((item => item.IdProducto == prod.IdProducto));
                    prod.stock = items[index] && items[index].stock
                    return prod
                })
            })
            .catch((error) => {
            console.log(error)
            })
        },
        setScreen() {
            this.navDisabled=!this.navDisabled
            if(this.navDisabled){
                window.parent.document.getElementById('NavegadorParent').style.display = 'none'
            }else{
                window.parent.document.getElementById('NavegadorParent').style.display = 'block'
            }
        },
        setProducto(obj) {
            this.precioCont = obj.PrecioContado
            this.medicionPro = obj.ProductoMedicion
            this.loadCode = ''
            obj.EsVentaNew = true;
            this.preciosProd = []
            this.objProd = {}
            this.preciosProd = JSON.parse(obj.PreciosPorProducto || '[]')
            //this.preciosProd.push({nombre:"P. Pub.", cantidad: 1, precio:obj.PrecioContado})
            
            /*if(obj.precioConvenio){
                precios++;
                this.preciosProd.push({nombre:"P. Dis.", cantidad: 1, precio:obj.precioConvenio})
            }
            if(obj.PrecioEspecial){
                precios++;
            this.preciosProd.push({nombre:"P. Esp", cantidad: 1,  precio:obj.PrecioEspecial})
            }*/

            let fPP = this.preciosProd.filter(p => p.cantidad === 0 || p.cantidad === '0')

            if(fPP.length==4){
                obj.cantxPrecio=1
                this.setProductoUni(obj)
                return
            }
            this.indexProdSelect = null
            this.$refs['modalCant'].show()
            this.objProd = obj
        },
        setProductoUni(obj) {
            this.objProd.cantxPrecio = 1
            this.venta.pago.visa.monto = 0
            this.venta.pago.mastercard.monto = 0
            this.venta.pago.delivery.monto = 0
            obj.precio = obj.PrecioContado
            obj.cantidad = 0
            //obj.cantxPrecio = 1
            obj.EsVentaNew = true;
            obj.descuento = 0
            obj.nuevoPrecioContado = 0
            obj.indicacion = ''
            obj.precioHabilitado = this.precioHabilitado
            obj = this.onSetProducto(obj)
            obj.cantidadPres = obj.cantidad
            //this.$refs['modalCant'].show()
            this.add([obj, () => {
                   this.$refs.carrito.productoChange = null
                    this.$refs.carrito.productoChange = obj.IdProducto
                    setTimeout(() => {
                         this.$refs.carrito.productoChange = null
                    }, 300);
                this.$toasted.success('Producto añadido correctamente')
                //this.loadMovimientoLotes(obj.IdProducto)
                this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen || 1 })
            }, { setPrecioCosto: false }, false, false])
            this.loadCode=''

            this.$refs['modalCant'].hide()

            this.$refs.barcodeInput.focus()
        },
        setProductoCant (prod) {
            this.objProd.cantxPrecio = 1
            this.cant = prod.cantidad
            let obj = this.objProd
            obj.subt = ''
            //this.$refs.autocompleteProducto.setValue('')
            //obj.FLETE = 0
            // this.objProd.precio = 0
            // this.objProd.cantidad = 0
            obj.Descripcion = prod.nombre
            obj.EsVentaNew = true;
            obj.precio = 0
            obj.cantidad = 0
            //obj.IdLote = 0
            //obj.TieneIgv = 0
            obj.descuento = 0
            obj.nuevoPrecioContado = 0
            obj = this.onSetProducto(obj, prod.cantidad,prod.precio)
            obj.cantidadPres = this.cant
            if(this.indexProdSelect == null){
                this.add([obj, () => {
                this.$emit('onAddProducto', obj)
                this.$toasted.success('Producto añadido correctamente')
                //this.loadMovimientoLotes(obj.IdProducto)
                this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen || 1 })
            }, false, false])

            }else{
                this.actualizarProd({Producto:obj,Pos:this.indexProdSelect});
                this.indexProdSelect = null
            }

            this.$refs['modalCant'].hide()
            
            //this.actualizarProd(obj)
            this.$refs.barcodeInput.focus()
        },
        showPresentacion (obj, index=null) {
            if (!obj.PreciosPorProducto !== '') {
                this.preciosProd = obj.PreciosPorProducto ? JSON.parse(obj.PreciosPorProducto) : []
                this.objProd = obj
                this.indexProdSelect = index
                this.precioCont = obj.PrecioContado
                this.medicionPro = obj.ProductoMedicion
                this.$refs['modalCant'].show()
                return
            }
        },
        LimpiarPuntoVenta(){
            this.$refs.inputTipoVenta.setValue('NOTA DE VENTA')
            this.$refs.inputAlmacen.setValue('PRINCIPAL')
            this.$refs.inputPuntoVenta.setValue('PRINCIPAL')
            this.$refs.inputCliente.setValue('')
            this.$refs.carrito.pagocon = ''
            //this.$refs.inputNombreCliente.$refs.input.required = true  
            this.tieneIgv = false
            if(this.venta.EsDelivery){
                this.anularPagoDelivery()
            }else if(this.$route.query.idPreOrden && this.$route.query.idCliente){
                this.anularPagoOrden()
            }
            this.initForm()
            this.limpiarCart()
            this.getVendedorInfo()
            this.loadClienteVarios()
            //this.getImpresoraCocina()
            this.$router.replace('/venta')
            this.$toasted.info('Se actualizó la página')
        },
        metodoPagoCambio(opc){
            switch (opc) {
                case 1:
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.delivery.monto = 0
                    this.venta.pago.efectivo.monto = this.totalCart;
                    break;
                case 2:
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.delivery.monto = 0
                    this.venta.pago.visa.monto =this.totalCart;
                    break;
                case 3:
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.delivery.monto = 0
                    this.venta.pago.mastercard.monto = this.totalCart;
                    break;
                case 4:
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.delivery.monto = this.totalCart;
                    break;
            
                default:
                    break;
            }
        },
        changeMovimiento(hash, idProducto, index) {
            if  (hash) {
                // Stock
                let stock = this.movimientoLotes[idProducto].find(m => m.value === hash).Stock
                let serieNumero = this.movimientoLotes[idProducto].find(m => m.value === hash).SerieNumero
                if (stock) {
                // this.productos[index].stockMovimiento = stock
                this.$nextTick(() => {
                    Vue.set(this.productos[index], 'stockMovimiento', stock)
                    Vue.set(this.productos[index], 'SerieNumero', serieNumero)
                })
                } else {
                this.$nextTick(() => {
                    Vue.set(this.productos[index], 'stockMovimiento', 0)
                    Vue.set(this.productos[index], 'SerieNumero', '----')
                })
                }
                // Fecha de vencimiento
                var fechaVen = this.movimientoLotes[idProducto].find(m => m.value === hash).FechaVen
                let hoy      = this.$moment().unix();
                let fechaVenUnix = this.$moment(fechaVen).unix();
                let en5dias  = this.$moment().add(5, 'days').unix();
                let style = 'bg-success'
                if(fechaVenUnix && fechaVenUnix < hoy) {
                style = 'bg-danger'
                }
                if (fechaVenUnix >= hoy && fechaVenUnix <= en5dias) {
                style = 'bg-warning'
                }
                if (fechaVen) {
                this.$nextTick(() => {
                    this.productos[index].FechaVen = this.$moment(fechaVen).format("YYYY-MM-DD")
                    //console.log(this.$moment(fechaVen).format("YYYY-MM-DD"));
                    //Vue.set(this.productos[index], 'fechaVen', this.$moment(fechaVen).format("YYYY-MM-DD"))
                    this.actualizarProd({Producto:this.productos[index],Pos:index})

                    Vue.set(this.productos[index], 'fechaVenMovimiento', `<small style="font-weight:bold" class='venci ${style}'>F. Venci: ${ this.$moment(fechaVen).format("DD-MM-YYYY") }</small>`)
                })
                } else {
                this.$nextTick(() => {
                    Vue.set(this.productos[index], 'fechaVenMovimiento', '')
                })
                }
                // return `<small class='venci ${style}'>Venci: ${fechaVen}</small>`
            }
            },
        loadMovimientoLotes ($idProducto) {
            this.movimientoLotes[$idProducto]=[]
            this.axios.get(this.API + '/movimientolotes/' + $idProducto+'/'+this.venta.almacen.IdAlmacen)
                .then((response) => {
                let indexProducto = this.productos.findIndex(p => p.IdProducto === $idProducto);
                // Cargar por defecto lote próximo a vencer.
                let primeroVencer = response.data.reduce((a,b) => {
                    return (a.FechaVen && (new Date(a.FechaVen) < new Date(b.FechaVen))) ? a : b;
                })
                
                this.$nextTick(() => {
                    Vue.set(this.productos[indexProducto], 'hashMovimiento', primeroVencer.hashMovimiento);
                    this.changeMovimiento(primeroVencer.hashMovimiento, $idProducto, indexProducto)
                })
                //
                this.movimientoLotes[$idProducto] = response.data.map(l => ({
                    value: l.hashMovimiento,
                    text : l.hashMovimiento,
                    Stock : l.Stock,
                    FechaVen: l.FechaVen,
                    SerieNumero: l.Serie + '-' + l.Numero
                }))
                })
                .catch((error) => {
                console.log(error)
                })
            },
        loadBarcode (e) {
            e.preventDefault()
            let idProducto = this.loadCode;
            if (idProducto) {
                let params = {codigoBarra: idProducto}
                this.axios.get(this.API + 'productosbarra/id/' + idProducto, {params: params})
                .then((response) => {
                if (response.data.IdProducto) {
                    this.setProducto(response.data)
                } else {
                    this.loadCode = ''
                    this.$toasted.show('No se encontró ningun producto')    
                }
                })
            }
        },
        //Fin de nuevos metodos de venta
         //Sockets
        escucharPedidos(){
            /*this.sockets.subscribe('nuevo-pedido', (data) => {
            if(this.$refs.deliveryModal.show){
                this.$toasted.success('Nuevo Pedido de' + "<strong>"+data.nombre+"</strong>");  
                this.$refs.deliveryModal.getPedidos();  
            }else{
                this.$refs.deliveryModal.getPedidos();
                this.$swal({
                    position: 'top-end',
                    icon: 'info',
                    target: document.getElementById('conten'),
                    title: 'Pedido de Delivery de: <br> ' + "<strong>"+data.nombre+"</strong>",
                    showConfirmButton: true,
                    confirmButtonText: 'Mostrar Pedidos',
                    confirmButtonColor:'#3880ff'
                }).then((result) => {
                //Read more about isConfirmed, isDenied below
                if (result.isConfirmed) {
                this.$refs.deliveryModal.show=true;
                } else if (result.isDenied) {
                    console.log("Denegado");
                }
                })
            }
            });*/
        },
        //Fin Sockets
        cargarPreOrden(){
            this.axios.get(this.API + '/preorden/estado', {
                params: {
                    idPreOrden: this.$route.query.idPreOrden
                }
            }).then((response) => {
                this.mesa=response.data.Mesa
                this.mesero = response.data.UsuarioReg
                this.cargarVendedorPreOrden(response.data.UsuarioReg)
                this.axios.get(this.API + 'preorden/detalle/agrupado', {
                    params: {
                        idPreOrden: this.$route.query.idPreOrden
                    }
                }).then((response) => {
                    this.limpiarCart()
                    response.data.map((item) => {
                        this.add([item, () => {
                        }, false ])
                    }) 
                    //this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen })
                }).catch(error=>{
                    this.$toasted.show('Hubo un error, no hay conexion')
                })
            }).catch(error=>{
                this.$toasted.show('Hubo un error, no hay conexion')
            })
        },
        cargarVendedorPreOrden(user){
            let usuario = user || sessionStorage.getItem('User')
            this.axios.get(this.API + 'usuarios/' + usuario)
            .then((response) => {
                //this.setVendedor(response.data)
                this.venta.vendedor.Usuario = response.data.Usuario
                this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        anularPagoOrden(){
            this.venta.selectOpcionVenta = 0
            this.venta.nombreLlevar = ''
            this.venta.DireccionEntrega = ''
            this.$refs.carrito.pagocon = ''
            this.bloquearInput = false
            this.limpiarCart()
            this.getVendedorInfo()
            this.loadClienteVarios()
            this.$router.replace('/venta')
            //this.$refs.inputNombreCliente.$refs.input.required = true 
        },
        loadClienteVarios () {
            this.axios.get(this.API + 'clientes/00000000')
                .then(res => {
                    if (res.data.IdCliente) {
                        this.setCliente(res.data)
                    }
                })
        },
        prevent (evt) {
            if (evt.target.tagName !== 'TEXTAREA') {
                evt.preventDefault();
            }
        },
        selectPreOrden (orden) {
            this.venta.IdPreOrden=orden               
        },
        loadFirstAlmacen () {
            this.axios.get(this.API + 'almacenes/primero')
                .then(res => {
                    //this.$refs.inputAlmacen.setValue(res.data.Almacen)
                    this.venta.almacen = res.data
                })
        },
        loadClienteBarcode (e) {
            e.preventDefault()
            let dni = this.dni;
            if (dni) {
                this.setCliente(false, dni)
            }
        },
        focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
        initForm () {
            this.venta = {
                EsCredito: 0,
                Serie: '',
                Numero: '',
                cliente: {},
                comisionista: {},
                tipoVenta: {
                    IdTipoDoc: 3,
                    TipoDoc: 'NOTA DE VENTA',
                    LimitItems: 0,
                    CodSunat: '12'
                },
                almacen: {
                    IdAlmacen: 1,
                    Almacen: 'PRINCIPAL',
                    DescuentoActivo: 0
                },
                puntoVenta: {
                    IdDocVentaPuntoVenta: 1,
                    PuntoVenta: 'PRINCIPAL'
                },
                vendedor: {},
                FechaCredito: this.$moment().format("YYYY-MM-DD"),
                FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
                pago: {
                    efectivo: { IdMetodoPago: 1, monto: 0 },
                    visa: { IdMetodoPago: 2, monto: 0 },
                    mastercard: { IdMetodoPago: 3, monto: 0 },
                    delivery: { IdMetodoPago: 4, monto: 0 },
                },
                idProductos:[],
                NroComprobanteModifica: '',
                CodSunatModifica: '',
                NotaIdMotivo: '',
                NotaDescMotivo: '',
                IdPreOrden: null,
                CampoDireccion: '',
                EsDelivery: false,
                EsParaLlevar: 0,
                nombreLlevar: '',
                selectOpcionVenta: 0,
                DireccionEntrega: ''
            }
            this.isNota = false
            this.bloquearInput = false
            this.infoDelivery = {}
            this.adelanto = 0
            this.adelantosPreOrden=[]
            this.descuentoGeneral = 0
            this.precioHabilitado = false
        },
        setOpcionVenta(opc){
            this.venta.nombreLlevar = ''
            this.venta.DireccionEntrega = ''
        },
        setTipoVenta (obj) {
            this.isNota = false
            this.bloquearInput = false
            this.venta.NroComprobanteModifica = ''
            this.venta.CodSunatModifica = ''
            this.venta.NotaIdMotivo = ''
            this.venta.NotaDescMotivo = ''
            //this.$refs.inputDocRef && this.$refs.inputDocRef.setValue('');
            if (obj.CodSunat == "07" || obj.CodSunat == "08") { // 07 - nota de credito, 08 - Nota de debito
                this.isNota = true
                this.limpiarCart();
                if(this.venta.EsDelivery){
                    this.anularPagoDelivery()
                }else if(this.$route.query.idPreOrden && this.$route.query.idCliente){
                    this.anularPagoOrden()
                }
            }
            if (obj.TieneIgv) {
                this.tieneIgv = true
            } else {
                this.tieneIgv = false
            }
            //this.$refs.inputTipoVenta.setValue(obj.TipoDoc)
            this.venta.tipoVenta = obj
            this.getNumeroVenta()
        },
        setDocRef (obj) {
            this.venta.CodSunatModifica = obj.CodSunat
            this.venta.NroComprobanteModifica = obj.NroComprobante
            if (this.venta.tipoVenta.CodSunat == '07' || this.venta.tipoVenta.CodSunat == '08') {
                this.venta.Serie = this.venta.Serie.replace(/^./, obj.NroComprobante[0])
            }
            this.axios.get(this.API + 'ventas/solonumero', {
                params: {
                    serie: this.venta.Serie,
                    idTipoDoc: this.venta.tipoVenta.IdTipoDoc
                }
            })
            .then((response) => {
                this.venta.Numero = response.data.numero
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
           //Cargar producto del documento afectado nota credito
             this.axios.get(this.API + 'venta/detalle/comprobante', {
                    params: {
                        NroComprobante: obj.NroComprobante,
                        idDocVenta: obj.IdDocVenta,
                        IdDocVentaPuntoVenta: this.venta.puntoVenta.IdDocVentaPuntoVenta
                    }
                }).then((response) => {
                this.axios.get(this.API + 'cliente', {
                    params: {
                        idCliente: response.data[0].IdCliente
                    }})
                    .then((response) => {
                        if (response.data) {
                            this.$refs.inputCliente.setValue(response.data.Cliente)
                            this.venta.CampoDireccion = response.data.Direccion
                            this.venta.cliente = response.data
                        } else {
                            this.$toasted.show('No se encontro al cliente')
                        }
                    })
                    .catch((error) => {
                        console.log(error)
                    })
                     this.limpiarCart()
                     response.data.map((item) => {
                        this.add([item, () => {
                        }, false ])
                    })     
                    this.venta.NotaIdMotivo=null
                    this.bloquearInput=true
                    this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen || 1 })              
                })
        },
        setMotivo(ev){
            if(ev == '01' || ev == '02' || ev == '06' || ev == null){
                this.bloquearInput=true;
            }else{
                this.bloquearInput=false;
            }
        },
        loadAlmacen(id) {
            this.axios.get(this.API + 'almacenes/id/' + id)
            .then(res => {
                this.setAlmacen(res.data)
            })
        },
        setAlmacen (obj) {
            this.venta.almacen = obj
            this.$refs.inputAlmacen.setValue(obj.Almacen)
            this.getCategorias()
            if (this.productos.length) {
                //this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen })
            }
            if(this.$route.query.idPreOrden && this.$route.query.idCliente) {
                this.cargarPreOrden()
                this.setCliente(this.$route.query.idCliente)
             }
            
        },
        loadPuntoVenta (id) {
            this.axios.get(this.API + 'puntoventa/id/' + id)
            .then(res => {
                this.setPuntoVenta(res.data)
                this.getNumeroVenta()
            })
        },
        getTipoDoc (id) {
            this.axios.get(this.API + 'tipodoc/' + id)
                .then(response => {
                    if (response.data) {
                        this.setTipoVenta(response.data)
                        this.$refs.inputTipoVenta.setValue(response.data.TipoDoc)
                    }
                })
        },
        setPuntoVenta (obj) {
            this.venta.puntoVenta = obj
            this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta)
            this.getNumeroVenta()
        },
        getVendedor () {
            let usuario = sessionStorage.getItem('User')
            this.axios.get(this.API + 'usuarios/' + usuario)
            .then((response) => {
                this.setVendedor(response.data)
                //this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                // this.$refs.inputVendedor.$refs.input.disabled = true
                this.loadAlmacen(response.data.IdAlmacen)
                this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta)
                // bloquear ambos inputs
                this.$refs.inputAlmacen.$refs.input.disabled = true
                this.$refs.inputPuntoVenta.$refs.input.disabled = true
                //this.getNumeroVenta()
                this.getTipoDoc(response.data.IdTipoDoc)
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        setVendedor (obj) {
            // console.log(obj)
            if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
            this.venta.vendedor = obj
        },
        setComisionista (obj) {
            if (typeof obj === 'object') {
                //this.$refs.inputComisionista.setValue(obj.Cliente)
                this.venta.comisionista = obj
            } else {
                this.venta.comisionista = {}
            }
        },
        setCliente (obj, dni = false) {
            let params = {}
            if (dni) {
                params = {
                    dni: dni
                }
            }else {
                params = {
                    idCliente: obj.IdCliente || obj
                }
            }
            if (typeof obj === 'object') {
                this.$refs.inputCliente.setValue(obj.Cliente)
                this.venta.cliente = obj
                this.venta.CampoDireccion = this.venta.cliente.Direccion
            } else {
                this.axios.get(this.API + 'cliente', {
                    params: params
                })
                .then((response) => {
                    if (response.data) {
                        this.$refs.inputCliente.setValue(response.data.Cliente)
                        this.venta.cliente = response.data
                        this.venta.CampoDireccion = this.venta.cliente.Direccion
                    } else {
                        this.$toasted.show('No se encontro al cliente')
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            }
            
            //this.actualizarPrecioConvenio(obj)
            // Actualizar DisabledTotal
        },
        onSetProducto (obj, cant, precio) {
            if (cant) {
                obj.cantidad = cant
                let preciosPorProducto = JSON.parse(obj.PreciosPorProducto).find((e) => e.cantidad === cant)
                obj.precio = preciosPorProducto.precio || precio
                return obj
            }
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
            return obj
        },
        onAddProducto (obj) {
            this.actualizarDescuento()
            //this.obtenerStok({
            //    IdAlmacen: this.venta.almacen.IdAlmacen
            //})
        },
        onPagarOrden(obj){
            this.anularPagoDelivery()
            this.$route.query.idPreOrden = obj.IdPreOrden
            this.$route.query.idCliente = obj.IdCliente
            this.venta.selectOpcionVenta = 0
            this.venta.nombreLlevar = ''
            this.venta.DireccionEntrega = ''
            if(this.$route.query.idPreOrden && this.$route.query.idCliente) {
                this.cargarPreOrden()
                this.setCliente(this.$route.query.idCliente)
                this.bloquearInput = true
            }
        },
        onPagarDelivery(obj){
            this.anularPagoOrden()
            this.$route.query.idPedido = obj.IdPedido
            if(this.$route.query.idPedido){
                console.log(obj);
                this.infoDelivery = obj
                this.venta.EsDelivery = true
                this.$refs.carrito.pagocon = parseFloat(obj.Cambio).toFixed(2)
                //cargar costo de delivery
                let costoDelivery = parseFloat(obj.PrecioDelivery || 0).toFixed(2)
                if (costoDelivery>0) {
                    this.axios.get(this.API +'costo/delivery')
                    .then((response) => {
                        if(response.data){
                            let costoDeliverObj = response.data
                                costoDeliverObj.precio = costoDelivery
                                costoDeliverObj.cantidad = 1
                                costoDeliverObj.EsCostoDelivery = 1
                                costoDeliverObj.IdImpresora = 0
                                costoDeliverObj.descuento = 0
                                costoDeliverObj.indicacion = ''
                                this.add([response.data, () => {
                                    //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) 
                                }, { setPrecioCosto: false }, false ,false ])
                                //Cargar Productos Delivery
                                let productos = JSON.parse(obj.Productos)
                                console.log(productos);
                                productos.map((item) => {
                                    this.add([item.IdProducto, () => {
                                        //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) 
                                    }, { cantidad: item.Cantidad, precio:item.Precio, indicacion: item.Indicacion, isDelivery:true  }, false ])
                                })
                                this.venta.selectOpcionVenta = 2
                                this.venta.nombreLlevar = (obj.Cliente || 'SIN NOMBRE')
                                this.venta.DireccionEntrega = obj.Direccion
                                this.$toasted.success('PRODUCTOS DEL DELIVERY AGREGADOS')
                        }else{
                            this.$toasted.show("PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'")   
                            this.anularPagoDelivery()
                        }
                    }).catch(error=>{
                        this.$toasted.show('HUBO UN ERROR')
                    })
                } else {
                    //Cargar Productos Delivery
                    let productos = JSON.parse(obj.Productos)
                    productos.map((item) => {
                        this.add([item.IdProducto, () => {
                            //this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 }) 
                        }, { cantidad: item.Cantidad, precio:item.Precio, indicacion: item.Indicacion, isDelivery:true  }, false ])
                    })
                    this.venta.selectOpcionVenta = 2
                    this.venta.nombreLlevar = (obj.Cliente || 'SIN NOMBRE')
                    this.venta.DireccionEntrega = obj.Direccion
                }
                 
            }
        },
        anularPagoDelivery(){
            this.limpiarCart()
            this.venta.EsDelivery = false
            this.infoDelivery = {}
            this.$refs.carrito.pagocon = ''
            this.loadClienteVarios()
            this.venta.nombreLlevar = ''
            this.venta.selectOpcionVenta = 0
            this.venta.nombreLlevar = ''
            this.venta.DireccionEntrega = ''
            this.$router.replace('/venta')
        },
        actualizarDescuento () {
            this.venta.pago.visa.monto = 0
            this.venta.pago.mastercard.monto = 0   
            this.venta.pago.delivery.monto = 0   
/*             if (this.puntosAplicados > 0) {
                this.aplicarPuntos(this.puntosAplicados)
            }
            if(this.descuentoGeneral > 0) {
                this.aplicarDescuentoGeneral(this.descuentoGeneral)
            } */
        },
        aplicarPuntos (val) {
            if (parseFloat(val) > this.venta.cliente.Puntos) { 
                this.puntosAplicados = this.venta.cliente.Puntos
            }
            this.axios.get(this.API + 'ventas/descuento/' + this.puntosAplicados)
            .then((response) => {
                this.descuentoPuntos = response.data
                this.aplicarDescuento(response.data.descuento)
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        aplicarDescuentoGeneral (val) {
            this.puntosAplicados = 0
            this.aplicarDescuento(val)
        },
        getNumeroVenta () {
            this.axios.get(this.API + 'ventas/numero', {
                params: {
                    idPuntoVenta: this.venta.puntoVenta.IdDocVentaPuntoVenta,
                    idTipoDoc: this.venta.tipoVenta.IdTipoDoc
                }
            })
            .then((response) => {
                this.venta.Serie = response.data.serie
                this.venta.Numero = response.data.numero
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        updateTime () {
            this.venta.FechaDoc = this.$moment().format("YYYY-MM-DDTH:mm:ss")
        },
        limpiarDespuesVenta () {
            this.inProgress = false
            this.tieneIgv = false
            this.initForm()
            this.limpiarCart()
            this.getVendedorInfo()
            //this.$refs.inputCliente.setValue('')
            // this.getNumeroVenta()
            // this.$refs.inputTipoVenta.setValue('TICKET')
            // this.$refs.inputAlmacen.setValue('VENTA')
            // this.$refs.inputPuntoVenta.setValue('CAJA1')
            // this.$refs.inputVendedor.setValue('')
        },
        guardarProforma () {
            this.inProgress = true
            if(!this.productos.length) {
                //this.$toasted.show('No hay productos para crear la proforma')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'No hay productos para crear la proforma',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false            
                return          
            }
            if(!this.venta.cliente.IdCliente) {
                //this.$toasted.show('Debe seleccionar un cliente')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Debe seleccionar un cliente',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }
            if(!this.venta.vendedor.Usuario) {
                //this.$toasted.show('Debe seleccionar un vendedor')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Debe seleccionar un vendedor',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }
            this.venta.productos = this.productos
            this.axios.post(this.API + 'proformas', this.venta)
            .then((response) => {
                //this.$toasted.show('Proforma Guardada con exito')
                this.$swal({
                    position: 'center',
                    icon: 'success',
                    title: 'Proforma Guardada con exito',
                    showConfirmButton: true,
                    confirmButtonText: 'Aceptar',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                this.limpiarDespuesVenta()
                this.loadClienteVarios()
                //window.open('/nuevo/index.html#/reportes/proforma/' + response.data.insertId,'_blank');
                window.open('/imprimir/proforma.php?IdProforma=' + response.data.insertId,'_blank');
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                this.inProgress = false
            })
        },
        guardarVenta (evt) {
            evt.preventDefault();
            this.inProgress = true
            let totalPagos = 0
            totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto))?0:parseFloat(this.venta.pago.efectivo.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto))?0:parseFloat(this.venta.pago.visa.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto))?0:parseFloat(this.venta.pago.mastercard.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.delivery.monto))?0:parseFloat(this.venta.pago.delivery.monto)
            if((isNaN(parseFloat(this.venta.pago.efectivo.monto))?0:parseFloat(this.venta.pago.efectivo.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago efectivo no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.visa.monto))?0:parseFloat(this.venta.pago.visa.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago tarjeta/transferencia no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.mastercard.monto))?0:parseFloat(this.venta.pago.mastercard.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago Yape o Plin no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.delivery.monto))?0:parseFloat(this.venta.pago.delivery.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago Yape o Plin no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if (!this.venta.EsCredito) {
                if (parseFloat(this.totalCart).toFixed(2) != parseFloat(totalPagos).toFixed(2)) {
                    //this.$toasted.show('Los pagos no coinciden con la venta')
                    this.$swal({
                        position: 'center',
                        icon: 'info',
                        title: 'Los pagos no coinciden con la venta',
                        showConfirmButton: true,
                        confirmButtonText: 'Entiendo',
                        target: document.getElementById('conten'),
                        confirmButtonColor:'#0275d8'
                    })
                    this.inProgress = false            
                    return                
                }
            }
            if (!this.venta.cliente.DniRuc) {
                //this.$toasted.show('Debe seleccionar un cliente.')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Debe seleccionar un cliente',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }
            var esNumerico = Number.isInteger(parseInt(this.venta.cliente.DniRuc))
            if (!esNumerico) {
                //this.$toasted.show('El RUC debe ser numérico, editar cliente.')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'El RUC debe ser numérico, editar cliente.',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }
            var esEmpresa = this.venta.cliente.DniRuc.length > 9 ? true : false
            if (!esEmpresa && this.venta.tipoVenta.CodSunat == '01') {
                //this.$toasted.show('Una factura debe ser emitida para una empresa, no un usuario común.')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Una factura debe ser emitida para una empresa, no a un usuario común',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                this.inProgress = false
                return
            }
            //No permitir la venta con Ruc si es boleta
            if (esEmpresa && this.venta.tipoVenta.CodSunat == '03') {
                //this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'Una boleta no debe ser emitida para una empresa',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return
            }
            if(this.venta.tipoVenta.CodSunat == '07' || this.venta.tipoVenta.CodSunat == '08') {
                if (!this.venta.CodSunatModifica || !this.venta.NroComprobanteModifica) {
                    //this.$toasted.show('Debe seleccionar un Documento de Referencia...')
                    this.$swal({
                        position: 'center',
                        icon: 'info',
                        title: 'Debe seleccionar un Documento de Referencia...',
                        showConfirmButton: true,
                        confirmButtonText: 'Entiendo',
                        target: document.getElementById('conten'),
                        confirmButtonColor:'#0275d8'
                    })
                    this.inProgress = false
                    return 
                }
                if(!this.venta.NotaIdMotivo) {
                    //this.$toasted.show('Debe seleccionar un Motivo')
                    this.$swal({
                        position: 'center',
                        icon: 'info',
                        title: 'Debe seleccionar un Motivo',
                        showConfirmButton: true,
                        confirmButtonText: 'Entiendo',
                        target: document.getElementById('conten'),
                        confirmButtonColor:'#0275d8'
                    })
                    this.inProgress = false
                    return 
                }
                this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(item => item.value == this.venta.NotaIdMotivo)[0].text
            }
            if(!this.productos.length) {
                //this.$toasted.show('No hay productos para vender')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'No hay productos para vender',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false            
                return          
            }
            // NO PERMITE CANTIDAD Y PRECIOS 0
            let sinStock = 0
            let totalCero = 0 
            this.productos.forEach(pro => {
                if((pro.CodigoBarra !== 'COSTODELIVERY') && ((parseFloat(pro.stock) <= 0 || parseFloat(pro.stock).toFixed(2)  < parseFloat(pro.cantidad) || pro.stock == undefined))) {
                    sinStock += 1
                }
                if(parseFloat(pro.total) <= 0) {
                    totalCero += 1
                }
            });
            /*if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != '07') {
                this.$toasted.error('No se puede vender un producto sin stock');
                this.inProgress = false            
                return;
            }*/
            if(totalCero >= 1) {
                //this.$toasted.error('No se puede enviar un producto en S/. 0.00');
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'No se puede enviar un producto en S/. 0.00',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false
                return;
            }
            if (this.totalCart <= 0) {
                //this.$toasted.show('La venta en S/. 0.00 no está permitido')
                this.$swal({
                    position: 'center',
                    icon: 'info',
                    title: 'La venta en S/. 0.00 no está permitido',
                    showConfirmButton: true,
                    confirmButtonText: 'Entiendo',
                    target: document.getElementById('conten'),
                    confirmButtonColor:'#0275d8'
                })
                this.inProgress = false            
                return 
            }
            this.venta.nombreLlevar=this.venta.nombreLlevar.toUpperCase();
            this.venta.productos = this.productos
            this.venta.puntosAplicados = this.puntosAplicados || 0
            this.venta.totalCart = this.totalCart || 0
            this.venta.IdPreOrden = this.$route.query.idPreOrden || null
            this.venta.IdPedido = this.$route.query.idPedido || null
            this.venta.IdProyecto = this.$route.query.idProyecto || null
            this.venta.adelantosPreOrden = this.adelantosPreOrden
            if(this.venta.selectOpcionVenta !=2){
                this.venta.DireccionEntrega = this.venta.EsDelivery?(this.infoDelivery.Direccion || ''): '';
            } 
            this.venta.PagoCon = this.$refs.carrito.pagocon
            this.venta.idProductos = this.productos.map(prod => prod.IdProducto)
            this.axios.post(this.API + 'ventas', this.venta)
            .then((response) => {
                if(response.data.actualizar){
                    this.inProgress = false
                    //this.obtenerStok({IdAlmacen: this.venta.almacen.IdAlmacen})
                    this.$toasted.show(response.data.msg)
                    return; 
                }else if(response.data.error){
                    this.inProgress = false
                    this.$swal({
                        position: 'center',
                        icon: 'warning',
                        title: response.data.msg,
                        showConfirmButton: true,
                        confirmButtonText: 'Entiendo',
                        target: document.getElementById('conten'),
                        confirmButtonColor:'#0275d8'
                    })
                    return;
                }else if(response.data.pagado){
                    this.inProgress = false
                    this.$toasted.show(response.data.error)    
                }else {
                    try {
                        /*this.venta.productos.map((pro)=>{
                            delete pro.nuevoPrecioContado
                            delete pro.Descripcion
                            delete pro.disabledProf
                            delete pro.normalPrecioContado
                            delete pro.Imagen
                            delete pro.indicacion
                            delete pro.IdImpresora
                            delete pro.PreciosPorProducto
                            delete pro.PrecioCosto
                            delete pro.precioHabilitado
                            delete pro.CodigoBarra
                            delete pro.stock
                        });
                        delete this.venta.cliente.Anulado
                        delete this.venta.cliente.FechaReg
                        delete this.venta.cliente.UsuarioReg
                        delete this.venta.cliente.FechaMod
                        delete this.venta.cliente.UsuarioMod
                        delete this.venta.cliente.FechaNacimiento
                        delete this.venta.cliente.Nacionalidad
                        delete this.venta.cliente.Direccion3
                        delete this.venta.cliente.Telefono
                        delete this.venta.cliente.Email
                        delete this.venta.cliente.Puntos
                        delete this.venta.cliente.EsVIP
                        delete this.venta.cliente.Direccion2
                        delete this.venta.cliente.Sexo
                        delete this.venta.cliente.Ocupacion
                        delete this.venta.cliente.NombreComercial
                        delete this.venta.cliente.esComisionista
                        delete this.venta.idProductos
                        delete this.venta.vendedor.APPTOKEN
                        delete this.venta.vendedor.APPURL
                        delete this.venta.vendedor.Anulado
                        delete this.venta.vendedor.FechaReg
                        delete this.venta.vendedor.UsuarioReg
                        delete this.venta.vendedor.FechaMod
                        delete this.venta.vendedor.UsuarioMod
                        delete this.venta.vendedor.NombreImpresora
                        delete this.venta.tipoVenta.LimitItems
                        delete this.venta.almacen.Almacen
                        delete this.venta.puntoVenta.PuntoVenta
                        delete this.venta.vendedor.Almacen
                        delete this.venta.vendedor.IdUsuarioPerfil
                        delete this.venta.vendedor.Password
                        delete this.venta.comisionista
                        delete this.venta.IdPedido
                        delete this.venta.adelantosPreOrden
                        delete this.venta.almacen.DescuentoActivo
                        delete this.venta.vendedor.DescuentoActivo
                        delete this.venta.vendedor.IdAlmacen
                        delete this.venta.vendedor.IdDocVentaPuntoVenta
                        delete this.venta.vendedor.IdTipoDoc
                        delete this.venta.puntosAplicados
                        delete this.venta.totalCart
                        delete this.venta.puntoVenta
                        let dataInfoDocVenta = {
                          Serie:  response.data.serie,
                          Numero: response.data.numero,
                          Fecha: response.data.fecha,
                        }
                        this.venta.dataInfoDocVenta = dataInfoDocVenta
                        let ventaJson= JSON.stringify(this.venta)
                        var left = (screen.width/2)-(800/2)
                        var top = (screen.height/2)-(800/2)
                        let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top='+top+', left='+left
                        window.open('http://localhost:5000/imprimir/getdata.php?venta='+encodeURIComponent(ventaJson), "Impresión", params)
                        */
                    } catch (error) {
  
                    }
                    /*this.$swal({
                        position: 'center',
                        icon: 'success',
                        title: '¡Venta <strong>'+ response.data.serie +' - '+ response.data.numero + '</strong> realizada con éxito!',
                        showConfirmButton: true,
                        confirmButtonText: 'Ok',
                        target: document.getElementById('conten'),
                        confirmButtonColor:'#1D9828',
                    })*/
                    this.$refs.inputTipoVenta.setValue('NOTA DE VENTA')
                    this.$refs.inputAlmacen.setValue('PRINCIPAL')
                    this.$refs.inputPuntoVenta.setValue('PRINCIPAL')
                    this.$refs.inputCliente.setValue('')
                    this.$refs.carrito.pagocon = ''
                    this.$toasted.success('Venta realizada con exito')
                    window.open('/imprimir/index.php?IdDocVenta=' + response.data.insertId,'_blank','noopener');
                    this.inProgress = false
                    this.tieneIgv = false
                    this.ultimaVenta = response.data.insertId
                    this.ultimaVentaData = this.venta
                    this.initForm()
                    this.limpiarCart()
                    this.getVendedorInfo()
                    //this.getNumeroVenta()
                    this.infoDelivery = {}
                    this.loadClienteVarios()
                    this.$router.replace('/venta')
                    //this.$refs.inputNombreCliente.$refs.input.required = true  
                     this.emitirauto(response.data.insertId)
                }
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                this.inProgress = false
            })
        },
        imprimirUltimaVenta(){
            window.open('/imprimir/index.php?IdDocVenta=' +this.ultimaVenta,'_blank');
            return;
            let tablet = this.venta.vendedor.EsTablet
            if(tablet==1){
                window.open('/imprimir/index.php?IdDocVenta=' +this.ultimaVenta+'&preview=1','_blank');
            } else if(tablet==2){
                window.open('neuroprinterred://factura.vip?iddocventa='+this.ultimaVenta+'&token=' + this.APPTOKEN)
            }else{
                try {
                let ventaJson= JSON.stringify(this.ultimaVentaData)
                var left = (screen.width/2)-(800/2)
                var top = (screen.height/2)-(800/2)
                let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top='+top+', left='+left
                window.open('http://localhost:5000/imprimir/getdata.php?venta='+encodeURIComponent(ventaJson), "Impresión", params)
                } catch (error) {
                }
            }
        },
        probarImpresora(){
            if(this.venta.vendedor!=false && Object.keys(this.venta.vendedor).length > 0){
                let data = {
                    Ip: this.venta.vendedor.Ip,
                    NombreImpresora: this.venta.vendedor.NombreImpresora,
                    NombreRed: this.venta.vendedor.NombreRed,
                    Puerto: this.venta.vendedor.Puerto,
                    Tipo: this.venta.vendedor.Tipo,
                }
                let impresoraJson= JSON.stringify(data)
                var left = (screen.width/2)-(800/2)
                var top = (screen.height/2)-(800/2)
                let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top='+top+', left='+left
                window.open('http://localhost:5000/imprimir/test.php?impresora='+encodeURIComponent(impresoraJson), "Impresión", params)
            }
        },
        imprimirUltimaComanda(){
            try {
               let ventaJson= JSON.stringify(this.ultimaComanda)
               var left = (screen.width/2)-(800/2)
               var top = (screen.height/2)-(800/2)
               let params = 'scrollbars=no,resizable=no,status=no,location=no,toolbar=no,menubar=no,width=300,height=300,top='+top+', left='+left
               window.open('http://localhost:5000/imprimir/getcomanda.php?comandaJson='+encodeURIComponent(ventaJson), "Impresión", params)
            } catch (error) {
            }
        },
        emitirauto(idDocVenta){
            this.axios.post(this.API + 'emitirauto', {IdDocVenta:idDocVenta})
            .then((response) => {
                if(response.data.success){
                    if(response.data.Estado == 1) {
                    this.$toasted.error("SUNAT: "+response.data.msj_sunat)                  
                    } else {
                    this.$toasted.info("SUNAT: "+response.data.msj_sunat)
                    }
                }
            })
            .catch((error) => {
                this.$toasted.show('Ha ocurrido un error, en la emision a sunat')
            })
        },
        eliminarCliente () {
           // this.$refs.inputCliente.setValue('')
            this.venta.cliente = {}
        },
        eliminarComisionista () {
           // this.$refs.inputComisionista.setValue('')
            this.venta.comisionista = {}
        },
    },
    components: {
        ClientesForm,
        ConfigVenta,
        PagoDeuda,
        PreOrdenLista,
        Proformas,
        //OrdenesModal,
        //DeliveryModal,
        'vue-simple-spinner':Spinner,
        ProductosCarritoNuevo
    }
}
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
.nomCart {
    display: flex;
    align-items: center;
    justify-content: center;
    border-right: 2px solid black;
}
.contentCard {
    cursor: pointer;
}
</style>
<style>
.nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link{
    background-color: #E6E6E6;
    border-bottom: none;
}
.headerInfo .input-group-text{
    font-weight: bold;
    font-size: 0.8rem;
}
.headerInfo input{
    font-size: 0.8rem;
}
.buscador .input-group-text{
    font-weight: bold;
    font-size: 0.8rem;
}
.buscador input{
    font-size: 0.8rem;
}
.docInfo .input-group-text{
    font-weight: bold;
    font-size: 0.9rem;
}
.docInfo input{
    font-size: 0.9rem;
    font-weight: bold;
}
.docInfo select{
    font-size: 0.9rem;
    height: auto !important;
}
.clienteInfo *{
    font-size: 0.8rem;
    height: auto !important;
}
#productoPunto:hover{
  transform: scale(1.02);
  transition: 0.5s all;
  box-shadow: 0 20px 80px rgba(0,0,0,0.25);
}
#modalCantidad .modal-dialog{
    max-width: 55% !important;
}
</style>