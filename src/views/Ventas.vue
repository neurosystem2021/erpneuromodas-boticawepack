<template>
    <div>
        <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->

        <Menu v-if="false"></Menu>

        <b-container fluid class="ancho"  id="conten">
            <div>
                <div class="float-right">
                    <!--<b-button-group size="sm" class="ocularMovil">
                        <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                    </b-button-group>-->
                    <b-button-group size="sm" class="ocularMovil">
                        <ventas-pedido :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente" @onFacturar="facturarVentaPedido"></ventas-pedido>
                    </b-button-group>
                    <b-button-group size="sm" class="ocularMovil">
                        <pago-deuda></pago-deuda>
                    </b-button-group>
                    <b-button-group size="sm">
                        <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente"></proformas>
                    </b-button-group>
                    <b-button-group size="sm" class="ocularMovil" v-if="false">
                        <pre-orden-lista 
                            v-show="!this.$route.query.idPreOrden"
                            :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                            @onPreOrden="selectPreOrden"></pre-orden-lista>
                    </b-button-group>
                    <b-button-group size="sm" class="ocularMovil">
                        <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
                        <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                    </b-button-group>
                </div>
            </div>
            <br /><br />
            <b-row>
                <b-col md="12" v-if="this.$route.query.idPedido">
                    <div class="form-inline">
                        <h4>Pago de delivery código: <b-badge variant="info">{{this.$route.query.idPedido}}</b-badge></h4>
                    </div>
                </b-col>
            </b-row>
            <b-form @submit="guardarVenta" @keydown.enter="prevent">
                <b-row style="margin-bottom:-10px">
                    <b-col md="8">
                        <b-row>
                            <b-col md="12">
                                <b-form-group>
                                <b-input-group prepend="Tipo" class="mt-2">
                                    <b-form-radio-group style="margin-left: 12px; margin-top: 7px;"
                                        id="radio-group-1"
                                        v-model="selectedtv"
                                        :options="optionstv"
                                        v-on:change="cambioTipoVenta($event)"
                                        name="radio-options"
                                    ></b-form-radio-group>
                                </b-input-group>
                                </b-form-group>
                            </b-col>
                            <b-col md="4" style="margin-top:-15px">
                                <b-form-group>
                                    <b-input-group prepend="Doc." class="mt-2">
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
                                 <b-form-group v-if="isNota">
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
                            <b-col md="4" style="margin-top:-15px">
                                <b-form-group>
                                    <b-input-group prepend="Alm." class="mt-2">
                                    <autocomplete
                                        required="required"
                                        ref="inputAlmacen"
                                        :url="API + 'almacenes'"
                                        anchor="Almacen"
                                        initValue="PRINCIPAL"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setAlmacen" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                    </b-input-group>
                                </b-form-group>
                                    <b-form-group v-if="isNota">
                                    <label>Motivo :</label>
                                    <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                                </b-form-group>
                            </b-col>
                            <b-col md="4" style="margin-top:-15px">
                                <b-form-group>
                                    <b-input-group prepend="P.V." class="mt-2">
                                    <autocomplete
                                        required="required"
                                        ref="inputPuntoVenta"
                                        :url="API + 'ventas/puntos'"
                                        anchor="PuntoVenta"
                                        initValue="PRINCIPAL"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setPuntoVenta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                    </b-input-group>
                                </b-form-group>
                            </b-col>
                            <b-col md="12" style="margin-top:-10px">
                                <b-form-group>
                                    <b-input-group prepend="Cliente ">
                                     <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                                        <autocomplete
                                            style="width:40%"
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
                                            class="form-control"
                                            placeholder="Cod. Barra" />
                                        <b-input-group-append>
                                            <!--<proveedores-tabla></proveedores-tabla>-->
                                            <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                                            
                                        </b-input-group-append>
                                    </b-input-group>
                                     <b-form-select v-if="venta.cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-3">
                                        <option :value="venta.cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                                        <option :value="venta.cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                                        <option :value="venta.cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                                    </b-form-select>

                                    <b-input-group>
                                        <b-form-checkbox 
                                        :disabled="bloquearInput?true:false"
                                        v-model="venta.EsGuia"
                                        :value="1"
                                        :unchecked-value="0">
                                            Es Guia
                                        </b-form-checkbox>
                                        <input v-model="venta.NombreGuia"
                                                type="text"
                                                style="text-transform:uppercase;"
                                                class="form-control"
                                                placeholder="Guía" v-if="venta.EsGuia" />
                                        <input v-model="venta.OrdenGuia"
                                                type="text"
                                                style="text-transform:uppercase;"
                                                class="form-control mx-1"
                                                placeholder="O. C." v-if="venta.EsGuia" />
                                        <b-form-textarea v-model="venta.ObservacionGuia"
                                                type="text"
                                                style="text-transform:uppercase;"
                                                class="form-control mx-1"
                                                placeholder="Observación(es)" v-if="venta.EsGuia" />
                                    </b-input-group>

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
                        </b-row>
                    </b-col>
                    <b-col md="2">
                        <!--<b-form-group class="text-center" v-if="venta.cliente.Cliente">
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
                        </b-form-text>-->
                        <br>
                        <div v-if="venta.idVentaPedido != null" style="border: 0.5px dashed #dc3545; padding:10px">
                            <h5 style="text-align:center">Facturacion de la Venta a Pedido</h5>
                            <h5 style="text-align:center; color:#dc3545">{{venta.correlativoVentaPedido}}</h5>
                            <b-badge variant="info" style="font-size:0.8rem" v-if="adelanto">Adelanto de: S/ {{ $options.filters.redondear(adelanto) }} </b-badge>
                            <b-badge variant="success" style="font-size:0.8rem" v-if="adelanto">Total a pagar: S/ {{ $options.filters.redondear(totalCart - adelanto) }} </b-badge>
                        </div>
                    </b-col>
                    <b-col md="2" >
                        <b-form-group class="text-center">
                            <div class="big-text">
                                {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
                            </div>
                            {{ venta.FechaDoc }}
                        </b-form-group>
                        <b-form-group>
                            <label>Vendedor:</label>
                            <autocomplete
                                required="required"
                                ref="inputVendedor"
                                :url="API + 'usuarios'"
                                :customParams="{ 'soloVendedor':soloVendedor, 'idAlmacen': almacenRaiz }"
                                anchor="NombreUsuario"
                                label="writer"
                                :on-select="setVendedorManual"
                                :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-form-group>
                        <b-form-group v-if="false">
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
                        <!--<b-badge variant="info" v-if="adelanto">Adelanto de: S/ {{ adelanto }} </b-badge>
                        <b-badge variant="success" v-if="adelanto">Total a pagar: S/ {{ $options.filters.redondear(totalCart - adelanto) }} </b-badge>-->
                    </b-col>
                </b-row>
                <b-row>
                    <b-col> 
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
                            :tipoVenta="selectedtv"
                            :esDelivery="venta.EsDelivery"
                            :tipoDocSunat="venta.tipoVenta.CodSunat"
                            :descCorporativoProp="descCorporativoProp"
                            :descInstitucionalProp="descInstitucionalProp">

                            <template slot="operaciones">
                                <table class="table b-table border table-footer">
                                    <tbody>
                                        <tr>
                                            <td  class="text-center">Opciones</td>
                                        </tr>
                                        <tr v-if="puntosAplicados == 0">
                                            <td>
                                                <b-input-group prepend="S/" append="Descuento">
                                                    <input
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        step="0.01"
                                                        :disabled="bloquearInput?true:false || idPerfil!=1"
                                                        @input="(val)=>{aplicarDescuentoGeneral(val.target.value)}"
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
                                                    :disabled="bloquearInput?true:false || isNota || venta.EsDelivery"
                                                    v-model="venta.EsCredito"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Venta a credito
                                                </b-form-checkbox>
                                                
                                                <b-form-input type="number" placeholder="Cantidad Días" v-model="cantidaDias" v-if="venta.EsCredito"/>


                                                <b-form-input 
                                                    :disabled="bloquearInput?true:false || isNota || venta.EsDelivery"
                                                    v-if="venta.EsCredito"
                                                    v-model="venta.FechaCredito"
                                                    readonly
                                                    class="mt-2"
                                                    type="date">
                                                </b-form-input>
                                                <b-form-textarea v-model="venta.Observacion"
                                                  type="text"
                                                    class="form-control mt-2 textArea"
                                                    placeholder="Observación(es)" 
                                                    v-if="venta.EsCredito"
                                                  />
                                                

                                                <b-form-checkbox 
                                                    :disabled="bloquearInput?true:false || venta.EsDelivery"
                                                    v-model="venta.PorEntregar"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Por entregar
                                                </b-form-checkbox>

                                                <b-form-input type="number" placeholder="Cantidad Días" v-model="PorEntregarDias" 
                                                v-if="venta.PorEntregar==1"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b-input-group prepend="Proforma válido por:" append="días">
                                                <b-form-input
                                                    :disabled="bloquearInput?true:false"
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
                            </template>    
                            <template slot="metodopago" v-if="!venta.EsCredito">
                                <table class="table b-table border table-footer table-sm">
                                    <tbody>
                                        <tr>
                                            <td colspan="3" class="text-center">Metodo de Pago</td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align: middle !important;">Efectivo</td>
                                            <td><b-button :disabled="isNota" variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(1)"><i class="fas fa-check"></i></b-button></td>
                                            <td>
                                                <b-form-input
                                                    v-model="venta.pago.efectivo.descripcion"
                                                    type="text"
                                                    :disabled="bloquearInput?true:false"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
                                                    v-model="venta.pago.efectivo.monto"
                                                    min="0"
                                                    :disabled="bloquearInput?true:false"
                                                    step="0.01"
                                                    type="number"
                                                    class="form-control"
                                                    placeholder="S/."
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align: middle !important;">Tarjeta</td>
                                            <td><b-button :disabled="isNota" variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(2)"><i class="fas fa-check"></i></b-button></td>
                                            <td>
                                                <b-form-input
                                                    v-model="venta.pago.visa.descripcion"
                                                    type="text"
                                                    :disabled="bloquearInput?true:false"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
                                                    class="form-control"
                                                    :disabled="bloquearInput?true:false"
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
                                            <td style="vertical-align: middle !important;">Yape / Plin</td>
                                            <td><b-button :disabled="isNota" variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(3)"><i class="fas fa-check"></i></b-button></td>
                                            <td>
                                                <b-form-input
                                                    v-model="venta.pago.mastercard.descripcion"
                                                    :disabled="bloquearInput?true:false"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
                                                    class="form-control"
                                                    v-model="venta.pago.mastercard.monto"
                                                    :disabled="bloquearInput?true:false"
                                                    min="0"
                                                    step="0.01"
                                                    type="number"
                                                    placeholder="S/."
                                                    @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - $event.target.value)"
                                                />
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="vertical-align: middle !important;">Tranferencia</td>
                                            <td><b-button :disabled="isNota" variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
                                            <td>
                                                <b-form-input
                                                    v-model="venta.pago.transferencia.descripcion"
                                                    :disabled="bloquearInput?true:false"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
                                                    class="form-control"
                                                    v-model="venta.pago.transferencia.monto"
                                                    :disabled="bloquearInput?true:false"
                                                    min="0"
                                                    step="0.01"
                                                    type="number"
                                                    placeholder="S/."
                                                    @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - $event.target.value)"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                            <template slot="adicional" v-if="!venta.EsCredito">
                                <table class="table b-table border table-footer table-sm text-center" v-if="false">
                                    <tbody>
                                        <tr><td>Mano de obra bruta</td></tr>
                                        <tr><td><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                        <tr><td>Mano de obra neta</td></tr>
                                        <tr><td><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                                    </tbody>
                                </table>
                            </template>
                        </productos-carrito>
                    </b-col>
                </b-row>
                <b-row align-h="center" class="text-center">
                    <b-col cols="8">
                        <br />
                        <b-button type="button" @click="anularPagoDelivery" variant="danger" v-if="this.$route.query.idPedido">Anular Pago Delivery</b-button>
                        <b-button type="button" @click="anularPagoVentaPedido" variant="danger" v-if="venta.idVentaPedido!=null">Cancelar Facturacion VP</b-button>
                        <b-button type="button" v-if="!this.$route.query.idPedido && venta.tipoVenta.CodSunat != '99' && venta.idVentaPedido == null"  :disabled="inProgress" variant="primary" size="md" @click="guardarProforma">Crear Cotizacion</b-button>
                        <b-button type="submit" :disabled="inProgress" variant="success" size="lg">Efectuar Venta</b-button>
                        <b-button v-if="ultimaVenta" target="_blank" variant="info" size="lg" :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                        <br />
                        <br />
                    </b-col>
                </b-row>
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
import VentasPedido from "@/components/VentasPedido.vue";
import Swal from "sweetalert2";
import moment from "moment";
export default {
  beforeMount() {
    this.$store.state.esOrdenCompra = false;
    this.initForm();
  },

  mounted() {
    // this.getNumeroVenta()
    this.getVendedor();
    //setInterval(this.updateTime, 1000)
    this.loadClienteVarios();
    this.escucharPedidos();
    this.obtenerDescuentoVenta();
  },
  data() {
    return {
      cantidaDias: 0,
      PorEntregarDias: 0,
      isNota: false,
      isVentaPedido: false,
      dni: "",
      adelanto: 0,
      gasto: 0,
      newDniRuc: "",
      ultimaVenta: "",
      inProgress: false,
      API: config.API,
      puntosAplicados: 0,
      descuentoPuntos: {},
      descuentoGeneral: 0,
      tieneIgv: false,
      venta: {},
      bloquearInput: false,
      diasValidosProf: 1,
      adelantosPreOrden: [],
      adelantosVentaPedido: [],
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
          //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "Producto" },
          { key: "ProductoPresentacion", label: "Rack" },
          { key: "Descripcion", label: "Descripción" },
          { key: "eventPres", label: "" },
          // { key: "obsequio" },
          { key: "cantxPrecio", variant: "warning" },
          // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
          // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
          { key: "cantidad" },
          // { key: "igv" },
          { key: "precio", label: "Precio" },
          //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center"
          },
          { key: "descuento", label: "% Desc." },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center"
          },
          { key: "acciones", label: "" }
        ]
      },
      selectedtv: 2, // Must be an array reference!
      optionstv: [
        { text: "TV 1 (Corporativos)", value: 1 },
        { text: "TV 2 (Sala)", value: 2 },
        { text: "TV 3 (Institucional)", value: 3 }
      ],
      descCorporativoProp: 0,
      descInstitucionalProp: 0,
      almacenRaiz: sessionStorage.getItem("IdAlmacen") || 0,
      soloVendedor: sessionStorage.getItem("IdPerfil") == 1 ? 0 : 1,
      idPerfil: sessionStorage.getItem("IdPerfil")
    };
  },
  watch: {
    cantidaDias() {
      let nuevaFecha = moment().add(this.cantidaDias, "days");
      let fechaFormateada = nuevaFecha.format("YYYY-MM-DD");
      this.venta.FechaCredito = fechaFormateada;
    },
    totalCart(newVal) {
      this.venta.pago.efectivo.monto = parseFloat(
        newVal -
          (parseFloat(this.venta.pago.visa.monto) +
            parseFloat(this.venta.pago.mastercard.monto) +
            parseFloat(this.venta.pago.transferencia.monto))
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
    }
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      actualizarPrecioConvenio: "actualizarPrecioConvenio",
      add: "agregarProducto"
    }),

    facturarVentaPedido(data) {
      //this.venta.IdPreOrden.push(orden)
      // obtener total adelantos
      this.venta.idVentaPedido = data.idDocVenta;
      this.venta.correlativoVentaPedido = data.Correlativo;
      this.axios
        .get(this.API + "ventas/pedido/adelantos", {
          params: {
            IdDocVenta: data.idDocVenta
          }
        })
        .then(res => {
          this.adelantosVentaPedido = res.data;
          let suma = 0;
          res.data.map(ad => {
            suma += ad.Importe;
          });
          this.adelanto = suma;
        });
    },
    obtenerDescuentoVenta() {
      this.axios
        .get(this.API + "ventas/descuento")
        .then(response => {
          this.descCorporativoProp = response.data.DescCorporativo;
          this.descInstitucionalProp = response.data.DescInstitucional;
        })
        .catch(error => {
          console.log(error);
          this.descCorporativoProp = 0;
          this.descInstitucionalProp = 0;
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    prevent(evt) {
      if (evt.target.tagName !== "TEXTAREA") {
        evt.preventDefault();
      }
    },
    selectPreOrden(orden) {
      // console.log(orden)
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
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    initForm() {
      this.venta = {
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
          Almacen: "PRINCIPAL"
        },
        puntoVenta: {
          IdDocVentaPuntoVenta: 1,
          PuntoVenta: "PRINCIPAL"
        },
        vendedor: {},
        adelantosPreOrden: [],
        adelantosVentaPedido: [],
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          transferencia: { IdMetodoPago: 4, monto: 0 }
        },
        IdPreOrden: [],
        CampoDireccion: "Direccion",
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsGuia: 0,
        NombreGuia: "",
        EsDelivery: false,
        nombreLlevar: "",
        DireccionEntrega: "",
        diasValidosProf: 1,
        idVentaPedido: null,
        correlativoVentaPedido: null
      };
      this.isNota = false;
      this.isVentaPedido = false;
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelantosVentaPedido = [];
      this.adelanto = 0;
      this.infoDelivery = {};
      this.descuentoGeneral = 0;
      this.puntosAplicados = 0;
      this.selectedtv = 2;
    },
    metodoPagoCambio(opc) {
      switch (opc) {
        case 1:
          this.venta.pago.visa.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.efectivo.monto = this.totalCart;
          break;
        case 2:
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.visa.monto = this.totalCart;
          break;
        case 3:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.transferencia.monto = 0;
          this.venta.pago.mastercard.monto = this.totalCart;
          break;

        case 4:
          this.venta.pago.efectivo.monto = 0;
          this.venta.pago.visa.monto = 0;
          this.venta.pago.mastercard.monto = 0;
          this.venta.pago.transferencia.monto = this.totalCart;
          break;

        default:
          break;
      }
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

      if (this.isVentaPedido == true && obj.CodSunat != "99") {
        this.limpiarCart();
      }

      if (obj.CodSunat == "99") {
        this.isVentaPedido = true;
      } else {
        this.isVentaPedido = false;
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
    cambioTipoVenta(val) {
      this.obtenerDescuentoVenta();
    },
    getVendedor(user) {
      let usuario = sessionStorage.getItem("User");
      if (user) {
        usuario = user;
      }
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          //this.$refs.inputVendedor.$refs.input.disabled = true
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
    setVendedorManual(obj) {
      this.venta.vendedor = obj;
      this.getVendedor(obj.Usuario);
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
      if (cant) {
        obj.cantidad = cant;
        let preciosPorProducto = JSON.parse(obj.PreciosPorProducto).find(
          e => e.cantidad === cant
        );
        obj.precio = preciosPorProducto.precio || 0;
        return obj;
      }
      obj.cantidad = 1;
      obj.precio = precio || 0;
      return obj;
    },
    onAddProducto(obj) {
      this.actualizarDescuento();
      this.venta.pago.visa.monto = 0;
      this.obtenerStok({
        IdAlmacen: this.venta.almacen.IdAlmacen
      });
    },
    actualizarDescuento() {
      this.venta.pago.visa.monto = 0;
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
    guardarProforma() {
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
      let prods = [...this.productos];

      // prods.forEach(element => {
      //   if (element.tipo == "PRESENTACION") {
      //     element.precio =
      //       element.precio * element.cantidadPres * element.cantxPrecio;
      //     // element.cantidad = element.cantxPrecio;
      //   }
      // });
      this.venta.productos = prods;
      this.venta.diasValidosProf = this.diasValidosProf;
      this.venta.PorEntregarDias = this.PorEntregarDias;
      this.axios
        .post(this.API + "proformas", this.venta)
        .then(response => {
          this.$toasted.info("Proforma Guardada con exito");
          this.limpiarDespuesVenta();
          this.loadClienteVarios();
          this.diasValidosProf = 1;
          this.cantidaDias = 0;
          this.inProgress = false;
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
      totalPagos += isNaN(parseFloat(this.venta.pago.transferencia.monto))
        ? 0
        : parseFloat(this.venta.pago.transferencia.monto);
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
          "El metodo de pago Yape o Plin no puede ser negativo"
        );
        this.inProgress = false;
        return;
      }
      if (
        (isNaN(parseFloat(this.venta.pago.transferencia.monto))
          ? 0
          : parseFloat(this.venta.pago.transferencia.monto)
        ).toFixed(2) < 0
      ) {
        this.$toasted.show(
          "El metodo de pago Transferencia no puede ser negativo"
        );
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

      if (
        this.venta.idVentaPedido != null &&
        (this.venta.tipoVenta.CodSunat == "99" ||
          this.venta.tipoVenta.CodSunat == "07")
      ) {
        this.$toasted.show(
          "No se puede facturar una venta a pedido con otra venta a pedido o nota de credito",
          { duration: 3000 }
        );
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
        if (this.venta.NroComprobanteModifica == "") {
          this.$toasted.show("Debe seleccionar un Documento de Referencia");
          this.inProgress = false;
          this.$refs.inputDocRef.focus();
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
      let descuentoTotal = 0;
      this.productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          pro.CodigoBarra !== "MANODEOBRA" &&
          (pro.stock <= 0 ||
            parseFloat(pro.cantidad) > pro.stock ||
            pro.stock == undefined)
        ) {
          sinStock += 1;
        }

        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }

        descuentoTotal += pro.descuento || 0;
      });

      if (
        sinStock >= 1 &&
        this.venta.tipoVenta.CodSunat != "07" &&
        this.venta.tipoVenta.CodSunat != "99"
      ) {
        this.$toasted.error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }
      // cantidadPres
      // let val = true;
      // if (this.productos.some(v => v.tipo == "PRESENTACION")) {
      //   this.productos
      //     .filter(v => v.tipo == "PRESENTACION")
      //     .forEach(element => {
      //       val = element.cantidadPres * element.cantidad <= element.stock;
      //     });
      // }

      // if (!val) {
      //   this.$toasted.error("No se puede vender un producto sin stock");
      //   this.inProgress = false;
      //   return;
      // }
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

      if (
        parseFloat(this.venta.pago.visa.monto) > 0 &&
        this.selectedtv == 2 &&
        descuentoTotal > 0
      ) {
        this.$swal({
          icon: "error",
          title: "Descuento no permitido",
          text:
            "El descuento en el TIPO DE VENTA 2 solo esta permitido en el metodo de pago EFECTIVO - TRANSFERENCIA - YAPE/PLIN",
          target: document.getElementById("conten"),
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          confirmButtonColor: "#3880ff"
        });
        this.inProgress = false;
        return;
      }
      let prods = [...this.productos];

      // console.log(prods);
      // this.inProgress = false;
      // return true;
      this.venta.productos = prods;
      this.venta.PorEntregarDias = this.PorEntregarDias;
      this.venta.puntosAplicados = this.puntosAplicados || 0;
      this.venta.totalCart = this.totalCart || 0;
      this.venta.PagoCon = this.$refs.carrito.pagocon;
      this.venta.NombreGuia = ("" + this.venta.NombreGuia).trim().toUpperCase();
      this.venta.adelantosPreOrden = this.adelantosPreOrden;
      this.venta.adelantosVentaPedido = this.adelantosVentaPedido.map(
        adpv => adpv.IdCajaBanco
      );
      this.venta.IdPedido = this.$route.query.idPedido || null;
      this.venta.nombreLlevar = this.venta.nombreLlevar.toUpperCase();
      this.venta.DireccionEntrega = this.venta.EsDelivery
        ? this.infoDelivery.Direccion || ""
        : "";
      this.venta.TipoVenta = this.selectedtv;
      this.inProgress = false;
      // return false;
      await this.axios
        .post(this.API + "ventas", this.venta)
        .then(response => {
          if (response.data.errorUser) {
            this.$swal({
              position: "center",
              icon: "error",
              title:
                "¡No se especifico el usuario, por favor salir del sistema e ingresar nuevamente!",
              showConfirmButton: true,
              confirmButtonText: "Aceptar",
              target: document.getElementById("conten"),
              confirmButtonColor: "#007bff",
              allowOutsideClick: false,
              allowEscapeKey: false
            });
            this.inProgress = false;
            return;
          }

          // limpiar preorden seleccionado
          let idPerfil = this.venta.vendedor.IdUsuarioPerfil;
          this.$router.replace("/ventas");
          //this.$toasted.success('Venta realizada con exito')
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          this.initForm();
          this.limpiarCart();
          this.getVendedor();
          this.getNumeroVenta();
          this.loadClienteVarios();
          this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
          this.$refs.inputAlmacen.setValue("VENTA");
          this.$refs.inputPuntoVenta.setValue("CAJA1");
          this.$refs.inputCliente.setValue("");
          this.$refs.inputVendedor.setValue("");
          //this.$refs.inputComisionista.setValue('')
          this.$refs.carrito.pagocon = 0;
          if (idPerfil != 3) {
            window.open(
              "/imprimir/index.php?IdDocVenta=" + response.data.insertId,
              "_blank",
              "noopener"
            );
          }
          this.$swal({
            position: "center",
            icon: "success",
            title: "¡La venta realizada correctamente!",
            showConfirmButton: true,
            confirmButtonText: "Ok",
            target: document.getElementById("conten"),
            confirmButtonColor: "#1D9828",
            allowOutsideClick: false,
            allowEscapeKey: false
          });
          this.emitirauto(response.data.insertId);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      setTimeout(() => {
        window.location.reload();
      }, 100);
    },
    emitirauto(idDocVenta) {
      this.axios
        .post(this.API + "emitirauto", { IdDocVenta: idDocVenta })
        .then(response => {
          if (response.data.success) {
            if (response.data.Estado == 1) {
              this.$toasted.error("SUNAT: " + response.data.msj_sunat);
            } else {
              this.$toasted.info("SUNAT: " + response.data.msj_sunat);
            }
          }
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, en la emision a sunat");
        });
    },
    eliminarCliente() {
      //this.$refs.inputCliente.setValue('')
      //this.venta.cliente = {}
      this.obtenerDescuentoVenta();
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
      this.venta.nombreLlevar = "";
      this.venta.selectOpcionVenta = 0;
      this.venta.nombreLlevar = "";
      this.venta.DireccionEntrega = "";
      this.$router.replace("/ventas");
    },
    anularPagoVentaPedido() {
      this.$router.replace("/ventas");
      this.inProgress = false;
      this.tieneIgv = false;
      this.venta.idVentaPedido = null;
      this.venta.correlativoVentaPedido = null;
      this.initForm();
      this.limpiarCart();
      this.getVendedor();
      this.getNumeroVenta();
      this.loadClienteVarios();
      this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
      this.$refs.inputCliente.setValue("");
      this.$refs.inputVendedor.setValue("");
      this.$refs.carrito.pagocon = 0;
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
    }
    //Fin Sockets
  },
  components: {
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas,
    DeliveryModal,
    VentasPedido
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
.textArea {
  resize: none;
  field-sizing: content;
  min-height: 1lh;
}
@media only screen and (max-width: 700px) {
  /* For desktop: */
  .ocularMovil {
    display: none;
  }
}

@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 300px);
  }
}
</style>
