<template>
    <div>
        <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->

        <Menu v-if="false"></Menu>

        <br />
        <b-container fluid class="ancho">
            <div>
                <div class="float-right">
                    <b-button-group size="sm">
                        <pago-deuda></pago-deuda>
                    </b-button-group>
                    <b-button-group size="sm">
                        <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente" ></proformas>
                    </b-button-group>
                    <b-button-group size="sm" v-show="false">
                        <pre-orden-lista 
                            v-show="!this.$route.query.idPreOrden"
                            :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                            @onPreOrden="selectPreOrden"></pre-orden-lista>
                    </b-button-group>
                    <b-button-group size="sm" v-if="idPerfil==1">
                        <!--<config-venta @onUpdateConfig="eliminarCliente"></config-venta>-->
                        <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                    </b-button-group>
                </div>
            </div>
            <br /><br />
            <b-form @submit="guardarVenta" @keydown.enter="prevent">
                <b-row>
                    <b-col md="7">
                        <b-row>
                            <b-col md="4" style="margin-top:-15px">
                                <b-form-group>
                                    <label>Tipo de Venta:  <b style="display:none" class="mostrarSerie">{{ venta.Serie }} - {{ venta.Numero }}</b></label>
                                    <autocomplete
                                        required="required"
                                        ref="inputTipoVenta"
                                        :url="API + 'ventas/tipos'"
                                        anchor="TipoDoc"
                                        initValue="NOTA DE VENTA"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setTipoVenta" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
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
                            <b-col md="4" style="margin-top:-15px" class="ocularMovil">
                                <b-form-group>
                                    <label>Almacen:</label>
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
                                </b-form-group>
                                <b-form-group v-if="isNota">
                                    <label>Motivo :</label>
                                    <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                                </b-form-group>
                            </b-col>
                            <b-col md="4" style="margin-top:-15px" class="ocularMovil">
                                <b-form-group>
                                    <label>Punto de Venta:</label>
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
                                </b-form-group>
                            </b-col>
                            <b-col md="12" style="margin-top:-15px">
                                <b-form-group>
                                    <label>Cliente:</label>
                                    
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
                                            class="form-control"
                                            placeholder="Codigo de Barra" />
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

                                    <b-input-group v-if="venta.cliente.DniRuc == '00000000'">
                                        <b-form-checkbox 
                                        :disabled="bloquearInput?true:false"
                                        v-model="venta.EsOrganizacion"
                                        :value="1"
                                        :unchecked-value="0">
                                            Es Organización
                                        </b-form-checkbox>
                                        <input v-model="venta.NombreOrganizacion"
                                                type="text"
                                                style="text-transform:uppercase;"
                                                class="form-control"
                                                required
                                                placeholder="Nombre de la Organización" v-if="venta.EsOrganizacion" />
                                    </b-input-group>

                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col md="3">
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
                    <b-col md="2" class="ocularMovil">
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
                                anchor="NombreUsuario"
                                label="writer"
                                :on-select="setVendedor" 
                                :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                                :classes="{input: 'form-control'}">
                            </autocomplete>
                        </b-form-group>
                        <b-form-group v-show="false">
                            <label>Comisionista</label>
                            <autocomplete
                                ref="inputComisionista"
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
                <br>
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
                            :fields="carrito.fields"
                            :isVentas="true"
                            :isNota="isNota"
                            :bloquearInput="bloquearInput"
                            :idAlmacen="venta.almacen.IdAlmacen"
                            :onSetProducto="onSetProducto" style="margin-top:-20px">

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
                                                        :disabled="bloquearInput?true:false"
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        @input="(val)=>{aplicarDescuentoGeneral(val.target.value)}"
                                                        type="number"
                                                        class="form-control"
                                                        step="0.01"
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
                                                <b-form-checkbox 
                                                    :disabled="bloquearInput?true:false"
                                                    v-model="venta.PorEntregar"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Por entregar
                                                </b-form-checkbox>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b-input-group prepend="Proforma válidez: " append="días">
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
                                            <td>Yape/Plin</td>
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
                                            <td style="vertical-align: middle !important;">Transferencia</td>
                                            <td><b-button variant="info" size="sm" class="mt-1" @click="metodoPagoCambio(4)"><i class="fas fa-check"></i></b-button></td>
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
                            <template slot="adicional" v-if="false">
                                <table class="table b-table border table-footer table-sm text-center" v-if="false" >
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
                <b-row align-h="center" class="text-center" style="margin-top:-15px">
                    <b-col cols="8">
                        <br />
                        <b-button type="button" :disabled="inProgress" variant="primary" size="md" @click="guardarProforma">Crear Proforma</b-button>
                        <b-button type="submit" :disabled="inProgress" variant="success" size="lg">Efectuar Venta</b-button>
                        <b-button v-if="ultimaVenta" target="_blank" variant="info" size="lg" @click="imprimirUltimaVenta">Imprimir última Venta</b-button>
                        <br />
                        <br />
                    </b-col>
                </b-row>
            </b-form>
        </b-container>
    </div>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import config from '@/store/config.js'
import ProductosCarrito from '@/components/ProductosCarrito'
import ClientesForm from '@/components/ClientesForm'
import ConfigVenta from '@/components/ConfigVenta'
import PagoDeuda from '@/components/PagoDeuda'
import PreOrdenLista from '@/components/PreOrdenLista.vue'
import Proformas from '@/components/ProformaLista.vue'

export default {
    beforeMount () {
        this.initForm()
    },
    mounted () {
        // this.getNumeroVenta()
        this.getVendedor()
        //setInterval(this.updateTime, 1000)
        this.loadClienteVarios()
        this.configStock()
    },
    data () {
        return {
            dni: '',
            isNota: false,
            adelanto: 0,
            gasto: 0,
            newDniRuc: '',
            ultimaVenta: '',
            inProgress: false,
            API: config.API,
            puntosAplicados: 0,
            descuentoPuntos: {},
            descuentoGeneral: 0,
            tieneIgv: false,
            venta: {},
            bloquearInput:false,
            diasValidosProf:0,
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
                    //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
                    { key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
                    //{ key: 'movimiento', label: 'Lote', variant: 'info', class:'text-center'  },
                    { key: 'Producto' },
                    //{ key: 'Descripcion', label: 'Descripción'},
                    { key: 'ProductoMarca', label: 'Marca' },
                    //{ key: 'ProductoModelo', label: 'Modelo/Color' , tdClass: 'widthTable'}, 
                    // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
                    // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
                    /*{ key: 'ProductoMedicion', label: 'Medición' },*/
                    { key: 'eventPresImagen', label: '' },
                    { key: 'eventPres', label: '' },
                    //{ key: 'cantxPrecio', variant: 'warning' },
                    { key: 'eventPresent', label: '' },
                    { key: 'Presentacion', label: 'Presentación' },
                    { key: 'cantidad' },
                    { key: 'precio', label: 'Precio' },
                    //{ key: 'utilidad', label: 'Porcentaje Utilidad %' },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'text-center' }, 
                    { key: 'descuento', label: 'Descuento'},
                    { key: 'total', label: 'Total', variant: 'danger', class:'text-center' },
                    { key: 'acciones', label: ''}
                ]
            },
            ventaSinStock:0,
            idPerfil: sessionStorage.getItem('IdPerfil')
        }
    },
    watch: {
        totalCart (newVal) {
             this.venta.pago.efectivo.monto = parseFloat(newVal - (parseFloat(this.venta.pago.visa.monto) + parseFloat(this.venta.pago.mastercard.monto) + parseFloat(this.venta.pago.transferencia.monto))).toFixed(2);
        }
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
            add:'agregarProducto'
        }),
        metodoPagoCambio(opc){
            switch (opc) {
                case 1:
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.transferencia.monto = 0
                    this.venta.pago.efectivo.monto = this.totalCart
                    break;
                case 2:
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.transferencia.monto = 0
                    this.venta.pago.visa.monto =this.totalCart
                    break;
                case 3:
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.transferencia.monto = 0
                    this.venta.pago.mastercard.monto = this.totalCart
                    break;
                case 4:
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.transferencia.monto = this.totalCart
                    break;
            
                default:
                    break;
            }
        },
        configStock () {
            this.axios.get(this.API + 'config/stock')
                .then(res => {
                    this.ventaSinStock = res.data
                })
                .catch((err)=>{
                    this.ventaSinStock = 0
                })
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
        /*cargarLotes(lote){
            this.$refs.carrito.loadMovimientoLotes(lote)
        },*/
        selectPreOrden (orden) {
            // console.log(orden)
           // this.venta.IdPreOrden = this.venta.IdPreOrden || []
           // if (Array.isArray(this.venta.IdPreOrden) ) {
            this.venta.IdPreOrden.push(orden)
            //}

            // obtener total adelantos
            this.axios.get(this.API + 'preorden/cajaybanco', { 
                params: { 
                    IdPreOrden      : orden.IdPreOrden,
                    TipoCajaBanco 	: 0
                } 
            })
            .then(res => {
                let suma = 0
                res.data.map(ad => {
                    if (ad.Tipo == 0) {
                        suma += ad.Importe
                    }
                })
                this.adelanto = suma
            })
        },
        loadFirstAlmacen () {
            this.axios.get(this.API + 'almacenes/primero')
                .then(res => {
                    this.$refs.inputAlmacen.setValue(res.data.Almacen)
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
                    Almacen: 'HUANUCO'
                },
                puntoVenta: {
                    IdDocVentaPuntoVenta: 1,
                    PuntoVenta: 'HUANUCO'
                },
                vendedor: {},
                FechaCredito: this.$moment().format("YYYY-MM-DD"),
                FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
                pago: {
                    efectivo: { IdMetodoPago: 1, monto: 0 },
                    visa: { IdMetodoPago: 2, monto: 0 },
                    mastercard: { IdMetodoPago: 3, monto: 0 },
                    transferencia: { IdMetodoPago: 4, monto: 0 }
                },
                idProductos:[],
                NroComprobanteModifica: '',
                CodSunatModifica: '',
                NotaIdMotivo: '',
                NotaDescMotivo: '',
                IdPreOrden: [],
                CampoDireccion: '',
                diasValidosProf: 0,
                EsOrganizacion: 0,
                NombreOrganizacion: ''
            }
            this.isNota = false
            this.bloquearInput=false
            this.puntosAplicados = 0
            this.descuentoPuntos = {}
            this.descuentoGeneral = 0
        },
        setTipoVenta (obj) {
            console.log(obj);
            if(this.isNota==true){
                //this.limpiarCart();
            }
            this.isNota = false
            this.bloquearInput = false
            this.venta.NroComprobanteModifica = ''
            this.venta.CodSunatModifica = ''
            this.venta.NotaIdMotivo = ''
            this.venta.NotaDescMotivo = ''
            this.$refs.inputDocRef && this.$refs.inputDocRef.setValue('');
            if (obj.CodSunat == "07" || obj.CodSunat == "08") { // 07 - nota de credito, 08 - Nota de debito
                this.limpiarCart();
                this.isNota = true
            }

            if (obj.TieneIgv) {
                this.tieneIgv = true
            } else {
                this.tieneIgv = false
            }
            this.$refs.inputTipoVenta.setValue(obj.TipoDoc)
            this.venta.tipoVenta = obj
            console.log(this.venta.tipoVenta);
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
                    this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 })                   
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
            if (this.productos.length) {
                this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen })
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
                    if (response.data) this.setTipoVenta(response.data)
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
                this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                this.$refs.inputVendedor.$refs.input.disabled = true
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
                this.$refs.inputComisionista.setValue(obj.Cliente)
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
                    idCliente: obj.IdCliente
                }
            }

            if (typeof obj === 'object') {
                this.$refs.inputCliente.setValue(obj.Cliente)
                this.venta.cliente = obj
                this.venta.CampoDireccion = this.venta.cliente.Direccion
                if(this.venta.cliente.DniRuc == '00000000'){
                    this.venta.EsOrganizacion = 0
                    this.venta.NombreOrganizacion = ''
                }
            } else {
                this.axios.get(this.API + 'cliente', {
                    params: params
                })
                .then((response) => {
                    if (response.data) {
                        this.$refs.inputCliente.setValue(response.data.Cliente)
                        this.venta.cliente = response.data
                        this.venta.CampoDireccion = this.venta.cliente.Direccion
                        if(this.venta.cliente.DniRuc == '00000000'){
                            this.venta.EsOrganizacion = 0
                            this.venta.NombreOrganizacion = ''
                        }
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
        onSetProducto (obj, precio = false, precioc = false) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
            if(precio){
                obj.precio = precio || 0
            }
           
            return obj
        },
        onAddProducto (obj) {
            this.actualizarDescuento()
            this.venta.pago.visa.monto = 0
            this.venta.pago.mastercard.monto = 0
            this.venta.pago.transferencia.monto = 0
            this.obtenerStok({
                IdAlmacen: this.venta.almacen.IdAlmacen
            })
        },
        actualizarDescuento () {
            this.venta.pago.visa.monto = 0
            this.venta.pago.mastercard.monto = 0
            this.venta.pago.transferencia.monto = 0
            if (this.puntosAplicados > 0) {
                this.aplicarPuntos(this.puntosAplicados)
            }

            if(this.descuentoGeneral > 0) {
                this.aplicarDescuentoGeneral(this.descuentoGeneral)
            }
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
            this.getVendedor()
            this.$refs.inputCliente.setValue('')
            // this.getNumeroVenta()
            // this.$refs.inputTipoVenta.setValue('TICKET')
            // this.$refs.inputAlmacen.setValue('VENTA')
            // this.$refs.inputPuntoVenta.setValue('CAJA1')
            // this.$refs.inputVendedor.setValue('')
        },
        guardarProforma () {
            this.inProgress = true

            if(this.diasValidosProf == 0 || this.diasValidosProf=='') {
                this.$toasted.show('Debe ingresar los días de validez de la proforma')
                this.inProgress = false
                return
            }

            if(!this.productos.length) {
                this.$toasted.show('No hay productos para crear la proforma')
                this.inProgress = false            
                return          
            }

            if(!this.venta.cliente.IdCliente) {
                this.$toasted.show('Debe seleccionar un cliente')
                this.inProgress = false
                return
            }

            if(!this.venta.vendedor.Usuario) {
                this.$toasted.show('Debe seleccionar un vendedor')
                this.inProgress = false
                return
            }

            this.venta.productos = this.productos
            this.venta.diasValidosProf = this.diasValidosProf
            this.axios.post(this.API + 'proformas', this.venta)
            .then((response) => {
                this.$toasted.show('Proforma Guardada con exito')
                this.limpiarDespuesVenta()
                this.loadClienteVarios()
                this.diasValidosProf=0;
                window.open('/nuevo/index.html#/reportes/proforma/' + response.data.insertId,'_blank');
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                this.inProgress = false
            })
        },
        guardarVenta (evt) {
            evt.preventDefault();
            //console.log(this.productos);
            //return;
            this.inProgress = true
            let totalPagos = 0
            totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto))?0:parseFloat(this.venta.pago.efectivo.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto))?0:parseFloat(this.venta.pago.visa.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto))?0:parseFloat(this.venta.pago.mastercard.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.transferencia.monto))?0:parseFloat(this.venta.pago.transferencia.monto)

            if((isNaN(parseFloat(this.venta.pago.efectivo.monto))?0:parseFloat(this.venta.pago.efectivo.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago Efectivo no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.visa.monto))?0:parseFloat(this.venta.pago.visa.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago Tarjeta no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.mastercard.monto))?0:parseFloat(this.venta.pago.mastercard.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago Yape / Plin no puede ser negativo')
                this.inProgress = false            
                return  
            }

            if((isNaN(parseFloat(this.venta.pago.transferencia.monto))?0:parseFloat(this.venta.pago.transferencia.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago Transferencia no puede ser negativo')
                this.inProgress = false            
                return  
            }

            if (!this.venta.EsCredito) {
                if (parseFloat(this.totalCart).toFixed(2) != parseFloat(totalPagos).toFixed(2) ) {
                    this.$toasted.show('Los pagos no coinciden con la venta')
                    this.inProgress = false            
                    return                
                }
            }

            if (!this.venta.cliente.DniRuc) {
                this.$toasted.show('Debe seleccionar un cliente.')
                this.inProgress = false
                return
            }

            var esNumerico = Number.isInteger(parseInt(this.venta.cliente.DniRuc))
            if (!esNumerico) {
                this.$toasted.show('El RUC debe ser numérico, editar cliente.')
                this.inProgress = false
                return
            }

            var esEmpresa = this.venta.cliente.DniRuc.length > 9 ? true : false
            if (!esEmpresa && this.venta.tipoVenta.CodSunat == '01') {
                this.$toasted.show('Una factura debe ser emitida para una empresa, no un usuario común.')
                this.inProgress = false
                return
            }

            //No permitir la venta con Ruc si es boleta
            /*
            if (esEmpresa && this.venta.tipoVenta.CodSunat == '03') {
                this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
                this.inProgress = false
                return
            }*/

            if(this.venta.tipoVenta.CodSunat == '07' || this.venta.tipoVenta.CodSunat == '08') {
                if (!this.venta.CodSunatModifica || !this.venta.NroComprobanteModifica) {
                    this.$toasted.show('Debe seleccionar un Documento de Referencia...')
                    this.inProgress = false
                    return 
                }

                if(!this.venta.NotaIdMotivo) {
                    this.$toasted.show('Debe seleccionar un Motivo')
                    this.inProgress = false
                    return 
                }
                if(this.venta.NroComprobanteModifica == ''){
                    this.$toasted.show('Debe seleccionar un Documento de Referencia')
                    this.inProgress = false
                    this.$refs.inputDocRef.focus();
                    return 
                }
                
                if(this.venta.CodSunatModifica=='12'){
                    this.$toasted.show('No se puede hacer NOTA DE CREDITO de una NOTA DE VENTA')
                    this.inProgress = false
                    this.$refs.inputDocRef.focus();
                    return 
                }
                this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(item => item.value == this.venta.NotaIdMotivo)[0].text
            }

            if(!this.productos.length) {
                this.$toasted.show('No hay productos para vender')
                this.inProgress = false            
                return          
            }

            // NO PERMITE CANTIDAD Y PRECIOS 0  Y QUE NO SEAN MISMO LOTE
            let sinStock = 0
            let totalCero = 0 
            //let igualLote = 0
            this.productos.forEach(pro => {
                console.log(pro);
                if((parseFloat(pro.stock) <= 0 || parseFloat(pro.stock).toFixed(2)  < parseFloat(pro.cantidad*pro.CantidadPresentacion) || pro.stock == undefined )) {
                    //this.$toasted.error('Se requiere: '+(pro.cantidad*pro.CantidadPresentacion)+" unidades de "+pro.Producto+" para la presentacion "+ pro.Presentacion,{duration: 3000});
                    sinStock += 1
                }

                if(parseFloat(pro.total) <= 0) {
                    totalCero += 1
                }

                pro.Descripcion = (''+pro.Presentacion).trim()
                /*let lote = this.productos.filter(p => p.hashMovimiento == pro.hashMovimiento && p.IdProducto == pro.IdProducto).length
                if(lote > 1){
                    igualLote += 1 
                }*/
                
            });

            if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != '07' && this.ventaSinStock != 1) {
                this.$toasted.error('No se puede vender un producto sin stock, con cuenta con uniades para la presentacion.',{duration: 3000});
                this.inProgress = false            
                return;
            }

            /*if (igualLote >= 1) {
                this.$toasted.error('Debe seleccionar diferente lote por producto');
                this.inProgress = false            
                return;
            }*/

            if(totalCero >= 1) {
                this.$toasted.error('No se puede enviar un producto en S/. 0.00');
                this.inProgress = false
                return;
            }

            if (this.totalCart <= 0) {
                this.$toasted.show('La venta en S/. 0.00 no está permitido')
                this.inProgress = false            
                return 
            }

            this.venta.productos = this.productos
            this.venta.puntosAplicados = this.puntosAplicados || 0
            this.venta.totalCart = this.totalCart || 0
            this.venta.PagoCon = this.$refs.carrito.pagocon
            this.venta.idProductos = this.productos.map(prod => prod.IdProducto)
            this.venta.NombreOrganizacion = (''+this.venta.NombreOrganizacion).trim().toUpperCase()
            this.axios.post(this.API + 'ventas', this.venta)
            .then((response) => {

                if(response.data.actualizar){
                    this.inProgress = false
                    this.obtenerStok({IdAlmacen: this.venta.almacen.IdAlmacen})
                    this.$toasted.show(response.data.msg)
                    return; 
                }else{
                    try {
                        this.venta.productos.map((pro)=>{
                            delete pro.nuevoPrecioContado
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
                            delete pro.IdProductoMarca
                            delete pro.IdProductoFormaFarmaceutica
                            delete pro.IdProductoMedicion
                            delete pro.IdProductoCategoria
                            delete pro.ProductoDesc
                            delete pro.ProductoDesc2
                            delete pro.ProductoDesc3
                            delete pro.ProductoDescCorto
                            delete pro.PrecioContado
                            delete pro.Codigo
                            delete pro.Dosis
                            delete pro.PrecioEspecial
                            delete pro.PrecioPorMayor
                            delete pro.StockPorMayor
                            delete pro.StockMinimo
                            delete pro.Anulado
                            delete pro.FechaReg
                            delete pro.UsuarioReg
                            delete pro.FechaMod
                            delete pro.UsuarioMod
                            delete pro.Hash
                            delete pro.ControlaStock
                            delete pro.VentaEstrategica
                            delete pro.PorcentajeUtilidad
                            delete pro.IdBloque
                            delete pro.Genero
                            delete pro.Color
                            delete pro.Botapie
                            delete pro.ProductoModelo,
                            delete pro.ProductoPresentacion,
                            delete pro.EsPadre
                            delete pro.precioConvenio
                            delete pro.EsHabitacion
                            delete pro.EstadoHabitacion
                            delete pro.Piso
                            delete pro.IdPreOrden
                            delete pro.IdClienteReserva
                            delete pro.FechaReserva
                            delete pro.FechaAlquiler
                            delete pro.Vuelo
                            delete pro.FechaAlquiler
                            delete pro.FechaAlquilerFin
                            delete pro.EstaSucio
                            delete pro.ControlaStockMinimo
                            delete pro.ProductoCategoria
                            delete pro.ProductoMarca
                            delete pro.ProductoMedicion
                            delete pro.StockIngresoUnd
                            delete pro.StockSalidaUnd
                            delete pro.StockSalidaVentaUnd
                            delete pro.StockSalidaVentaCaja
                            delete pro.EsObsequio
                            delete pro.cantxPrecio
                            delete pro.subt
                            delete pro.cantidadPres
                            delete pro.subtotal
                            delete pro.total
                        });
                        delete this.venta.cliente.Anulado
                        delete this.venta.cliente.IdCliente
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
                        delete this.venta.NroComprobanteModifica
                        delete this.venta.CodSunatModifica
                        delete this.venta.NotaIdMotivo
                        delete this.venta.IdPreOrden
                        delete this.venta.almacen
                        delete this.venta.FechaDoc
                        delete this.venta.pago
                        delete this.venta.NotaDescMotivo
                        delete this.venta.diasValidosProf
                        delete this.venta.EsOrganizacion
                        delete this.venta.NombreOrganizacion
                        delete this.venta.Serie
                        delete this.venta.Numero

                        /*let dataInfoDocVenta = {
                          Serie:  response.data.serie,
                          Numero: response.data.numero,
                          Fecha: response.data.fecha,
                        }
                        this.venta.dataInfoDocVenta = dataInfoDocVenta
                        this.venta.reps = 2
                        this.ultimaVentaData = this.venta
                        let ventaJson= JSON.stringify(this.venta)
                        let left = (screen.width/2)-(800/2)
                        let top = (screen.height/2)-(800/2)
                        let winName='Impresion';
                        let winURL='http://localhost:5000/imprimir/getdata.php';
                        let windowoption='resizable=yes,height=300,width=300,location=0,menubar=0,scrollbars=1,top='+top+', left='+left;     
                        let form = document.createElement("form");
                        form.setAttribute("method", "post");
                        form.setAttribute("action", winURL);
                        form.setAttribute("target",winName);  
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'venta';
                        input.value = ventaJson;
                        form.appendChild(input);
                        document.body.appendChild(form);                       
                        window.open('', winName,windowoption);
                        form.target = winName;
                        form.submit();                 
                        document.body.removeChild(form);*/
                    } catch (error) {
  
                    }

                // limpiar preorden seleccionado
                    this.$router.replace('/ventas')
                    this.$toasted.success('Venta realizada con exito')
                    this.inProgress = false
                    this.tieneIgv = false
                    this.ultimaVenta = response.data.insertId
                    this.initForm()
                    this.limpiarCart()
                    this.getVendedor()
                    this.getNumeroVenta()
                    this.loadClienteVarios()
                    this.$refs.inputTipoVenta.setValue('NOTA DE VENTA')
                    this.$refs.inputAlmacen.setValue('PRINCIPAL')
                    this.$refs.inputPuntoVenta.setValue('PRINCIPAL')
                    this.$refs.inputCliente.setValue('')
                    this.$refs.inputVendedor.setValue('')
                    this.$refs.inputComisionista.setValue('')
                    this.$refs.carrito.pagocon = 0
                    window.open('/imprimir/index.php?IdDocVenta=' + response.data.insertId,'_blank','noopener');

                }
                 
                   

            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                this.inProgress = false
            })
        },
        imprimirUltimaVenta(){
            try {
                let ventaJson= JSON.stringify(this.ultimaVentaData)
                let left = (screen.width/2)-(800/2)
                let top = (screen.height/2)-(800/2)
                let winName='Impresion';
                let winURL='http://localhost:5000/imprimir/getdata.php';
                let windowoption='resizable=yes,height=300,width=300,location=0,menubar=0,scrollbars=1,top='+top+', left='+left;     
                let form = document.createElement("form");
                form.setAttribute("method", "post");
                form.setAttribute("action", winURL);
                form.setAttribute("target",winName);  
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'venta';
                input.value = ventaJson;
                form.appendChild(input);
                document.body.appendChild(form);
                window.open('', winName,windowoption);
                form.target = winName;
                form.submit();                 
                document.body.removeChild(form);  
            } catch (error) {
            }
        },
        eliminarCliente () {
            this.$refs.inputCliente.setValue('')
            this.venta.cliente = {}
        },
        eliminarComisionista () {
            if(this.$refs.inputComisionista){
                this.$refs.inputComisionista.setValue('')
                this.venta.comisionista = {}
            }
        },
    },
    components: {
        ProductosCarrito,
        ClientesForm,
        ConfigVenta,
        PagoDeuda,
        PreOrdenLista,
        Proformas
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

</style>
<style>
@media only screen and (min-width: 700px) and (max-width: 1800px) {
  /* For desktop: */
    .ancho{
        width: calc(100% - 300px) !important;
    }
}
@media only screen and (max-width: 750px) {
  /* For desktop: */
    .ocularMovil{
        display: none;
    }
    .mostrarSerie{
      display: inline !important;
    }
}

@media only screen and (max-width: 900px) {
    #modalPagoDeuda .modal-dialog{
        max-width: 100% !important;
        margin: 5px !important;
    }

    #modalPagoDeuda{
        padding: 0 !important;
    }

}
</style>
