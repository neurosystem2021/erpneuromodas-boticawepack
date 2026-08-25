<template>
    <div class="pos-jobi">
        <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->

        <Menu v-if="false"></Menu>

        <br class="ocularMovil"/>
        <b-container class="bv-example-row pos-container" fluid>
            <b-form class="pos-card" @submit="guardarVenta" @keydown.enter="prevent">

<!-- ================= CABECERA RESTRUCTURADA ================= -->
<div class="pos-panel pos-panel-header">
    <b-row class="align-items-stretch pos-header-row">
        <!-- Columna de Campos Principales -->
        <b-col md="5" class="pos-header-col pos-header-col-fields">
            <b-row>
                <b-col md="4">
                    <b-form-group class="pos-field pos-field-doc mb-2">
                        <label><i class="fas fa-file-invoice pos-label-icon"></i> Doc. Venta <b style="display:none" class="mostrarSerie">{{ venta.Serie }} - {{ venta.Numero }}</b></label>
                        <autocomplete required="required" ref="inputTipoVenta" :url="API + 'ventas/tipos'" anchor="TipoDoc" initValue="BOLETA ELEC." label="writer" placeholder="" :on-select="setTipoVenta" :onFocus="focusAutocomplete.bind(null, 'inputTipoVenta')" :classes="{input: 'form-control'}"></autocomplete>
                    </b-form-group>
                    <b-form-group v-if="isNota" class="pos-field pos-field-doc mb-2">
                        <label><i class="fas fa-file-alt pos-label-icon"></i> Doc. Referencia</label>
                        <autocomplete required="required" ref="inputDocRef" :url="API + 'ventas/lista/'+venta.puntoVenta.IdDocVentaPuntoVenta" anchor="NroComprobante" initValue="" label="writer" placeholder="" :on-select="setDocRef" :onFocus="focusAutocomplete.bind(null, 'inputDocRef')" :classes="{input: 'form-control'}"></autocomplete>
                    </b-form-group>
                </b-col>

                <b-col md="4">
                    <b-form-group class="pos-field pos-field-alm mb-2">
                        <label><i class="fas fa-warehouse pos-label-icon"></i> Alm.</label>
                        <autocomplete required="required" ref="inputAlmacen" :url="API + 'almacenes'" anchor="Almacen" initValue="HUANUCO" label="writer" placeholder="" :on-select="setAlmacen" :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')" :classes="{input: 'form-control'}"></autocomplete>
                    </b-form-group>
                    <b-form-group v-if="isNota" class="pos-field pos-field-alm mb-2">
                        <label><i class="fas fa-exclamation-circle pos-label-icon"></i> Motivo</label>
                        <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                    </b-form-group>
                </b-col>

                <b-col md="4" class="ocularMovil">
                    <b-form-group class="pos-field pos-field-pv mb-2">
                        <label><i class="fas fa-cash-register pos-label-icon"></i> P.V.</label>
                        <autocomplete required="required" ref="inputPuntoVenta" :url="API + 'ventas/puntos'" anchor="PuntoVenta" initValue="HUANUCO" label="writer" placeholder="" :on-select="setPuntoVenta" :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')" :classes="{input: 'form-control'}"></autocomplete>
                    </b-form-group>
                </b-col>

                <b-col md="12">
                    <b-form-group class="pos-field pos-field-cliente mb-0">
                        <label><i class="fas fa-user pos-label-icon"></i> Cliente</label>
                        <b-input-group class="pos-cliente-group">
                           <span class="pos-cliente-pin" title="Dirección del cliente"><i class="fas fa-map-marker-alt"></i></span>
                           <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
                            <autocomplete required="required" ref="inputCliente" :url="API + 'clientes'" anchor="ClienteDniRuc" label="writer" placeholder="" :onInput="(val) => newDniRuc = val" :on-select="setCliente" :onFocus="focusAutocomplete.bind(null, 'inputCliente')" :classes="{input: 'form-control'}"></autocomplete>
                            <b-input-group-append>
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
        <!-- Espacio Central: Comprobante Actual -->
        <b-col md="4" class="ocularMovil d-flex align-items-stretch justify-content-center pos-header-col">
            <div class="pos-stat-card pos-stat-comprobante w-100 my-2">
                <div class="pos-stat-icon"><i class="fas fa-receipt"></i></div>
                <div class="pos-stat-body text-center">
                    <div class="pos-stat-label">Comprobante actual</div>
                    <div class="big-text">{{ venta.Serie }} - <b>{{ venta.Numero }}</b></div>
                    <div class="pos-stat-sub">{{ venta.FechaDoc }}</div>
                </div>
            </div>
        </b-col>

        <!-- Columna Derecha: Vendedor -->
        <b-col md="3" class="ocularMovil pos-stat-col pos-header-col">
            <div class="pos-stat-card pos-stat-vendedor w-100">
                <div class="pos-stat-icon"><i class="fas fa-user-tie"></i></div>
                <div class="pos-stat-body pos-stat-body-full">
                    <div class="pos-stat-label">Vendedor asignado</div>
                    <autocomplete required="required" ref="inputVendedor" :url="API + 'usuarios'" anchor="NombreUsuario" label="writer" :on-select="setVendedor" :onFocus="focusAutocomplete.bind(null, 'inputVendedor')" :classes="{input: 'form-control'}"></autocomplete>
                </div>
            </div>
            <b-badge variant="info" class="pos-badge-adelanto" v-if="adelanto"><i class="fas fa-hand-holding-usd"></i> Adelanto de: S/ {{ adelanto }} </b-badge>
            <b-badge variant="success" class="pos-badge-adelanto" v-if="adelanto"><i class="fas fa-check-circle"></i> Total a pagar: S/ {{ $options.filters.redondear(totalCart - adelanto) }} </b-badge>
        </b-col>
    </b-row>
</div>

                <!-- ================= CARRITO ================= -->
                <b-row>
                    <b-col>
                    <div>
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
                            :bloquearInput="bloquearInput"
                            :idAlmacen="venta.almacen.IdAlmacen"
                            :onSetProducto="onSetProducto">
                            <!-- ===== ACCIONES PRINCIPALES: ahora justo debajo del carrito y ===== -->
                            <!-- ===== arriba de Método de pago / Opciones ===== -->
                            <template slot="acciones-superior">
                                <div class="pos-main-actions pos-main-actions-top">
                                    <b-button
                                        class="jobi-btn-action pos-btn-venta pos-btn-pagar"
                                        type="submit"
                                        :disabled="inProgress"
                                        variant="success"
                                        size="lg">
                                        <i class="fas fa-credit-card pos-btn-icon"></i>
                                        <span>Efectuar Venta</span>
                                    </b-button>

                                    <b-button
                                        type="button"
                                        class="pos-btn-proforma"
                                        :disabled="inProgress"
                                        @click="guardarProforma">
                                        <i class="fas fa-file-signature"></i>
                                        <span>Crear Proforma</span>
                                    </b-button>

                                    <b-button
                                        v-if="ultimaVenta"
                                        class="pos-btn-imprimir"
                                        target="_blank"
                                        variant="info"
                                        size="lg"
                                        :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">
                                        <i class="fas fa-print"></i>
                                        <span>Imprimir última venta</span>
                                    </b-button>
                                </div>
                            </template>

                            <template slot="operaciones">
                              <div class="pos-operaciones-col">
                                <div class="pos-quickgrid-row3">
                                    <div class="pos-quick-btn pos-quick-teal" ref="wrapPagoDeuda"
                                         role="button" tabindex="0"
                                         @click.prevent="abrirModalInterno('pagoDeuda')"
                                         @keydown.enter.prevent="abrirModalInterno('pagoDeuda')"
                                         @keydown.space.prevent="abrirModalInterno('pagoDeuda')">
                                        <span class="pos-quick-icon"><i class="fas fa-wallet"></i></span>
                                        <span class="pos-quick-label"><pago-deuda ref="pagoDeuda"></pago-deuda></span>
                                    </div>
                                    <div class="pos-quick-btn pos-quick-blue" ref="wrapMovimiento"
                                         role="button" tabindex="0"
                                         @click.prevent="abrirModalInterno('movimiento')"
                                         @keydown.enter.prevent="abrirModalInterno('movimiento')"
                                         @keydown.space.prevent="abrirModalInterno('movimiento')">
                                        <span class="pos-quick-icon"><i class="fas fa-exchange-alt"></i></span>
                                        <span class="pos-quick-label"><movimiento ref="movimiento"></movimiento></span>
                                    </div>
                                    <div class="pos-quick-btn pos-quick-purple" ref="wrapProformas"
                                         role="button" tabindex="0"
                                         @click.prevent="abrirModalInterno('proformas')"
                                         @keydown.enter.prevent="abrirModalInterno('proformas')"
                                         @keydown.space.prevent="abrirModalInterno('proformas')">
                                        <span class="pos-quick-icon"><i class="fas fa-file-invoice"></i></span>
                                        <span class="pos-quick-label">
                                            <proformas ref="proformas" :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente"></proformas>
                                        </span>
                                    </div>
                                </div>
                                <div class="pos-mini-panel">
                                    <div class="pos-mini-panel-title"><i class="fas fa-sliders-h"></i> Opciones</div>
                                    <div v-if="puntosAplicados == 0" class="pos-mini-row ocularMovil">
                                        <span class="pos-mini-icon"><i class="fas fa-tag"></i></span>
                                        <b-input-group prepend="S/" class="pos-input-descuento">
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
                                    </div>
                                    <b-form-text class="pos-mini-hint">
                                        Descuento general a toda la venta.
                                    </b-form-text>
                                    <div class="pos-mini-row">
                                        <b-form-checkbox
                                            :disabled="bloquearInput?true:false || isNota"
                                            v-model="venta.EsCredito"
                                            :value="1"
                                            :unchecked-value="0">
                                            <i class="fas fa-calendar-alt pos-label-icon"></i> Venta a credito
                                        </b-form-checkbox>
                                    </div>
                                    <b-form-input 
                                        :disabled="bloquearInput?true:false || isNota"
                                        v-if="venta.EsCredito"
                                        v-model="venta.FechaCredito"
                                        type="date">
                                    </b-form-input>
                                    <b-form-checkbox  v-if="false"
                                        :disabled="bloquearInput?true:false"
                                        v-model="venta.PorEntregar"
                                        :value="1"
                                        :unchecked-value="0">
                                        Por entregar
                                    </b-form-checkbox>
                                </div>
                                </div>
                            </template>    
                            <template slot="metodopago" v-if="!venta.EsCredito">
                                <div class="pos-payment-card ocularMovil pos-payment-panel" style="margin-left: 32px;margin-right: 32px;">
                                    <div class="pos-payment-header">
                                        <div class="pos-payment-header-icon"><i class="fas fa-money-check-alt"></i></div>
                                        <div class="pos-payment-header-text">
                                            <div class="pos-payment-header-title">Formas de pago</div>
                                            <div class="pos-payment-header-sub">Distribuye el importe de la venta · Total: S/ {{ $options.filters.redondear(totalCart) }}</div>
                                        </div>
                                    </div>
                                    <div class="pos-payment-body">
                                        <div class="pos-pay-row-v2" :class="{ 'is-selected': metodoPagoSeleccionado === 'efectivo' }">
                                            <div class="pos-pay-badge pos-pay-badge-green">
                                                <span class="pos-pay-badge-icon"><i class="fas fa-money-bill-wave"></i></span>
                                                <span class="pos-pay-badge-label">Efectivo</span>
                                            </div>
                                            <button type="button"
                                                class="pos-pay-check pos-pay-check-green"
                                                :class="{ 'active': metodoPagoSeleccionado === 'efectivo' }"
                                                :disabled="bloquearInput ? true : false"
                                                @click="seleccionarMetodoPago('efectivo')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <input
                                                :disabled="bloquearInput ? true : false"
                                                v-model="venta.pago.efectivo.descripcion"
                                                type="text"
                                                placeholder="Referencia"
                                                class="form-control pos-pay-input-ref"
                                            />
                                            <input
                                                :disabled="bloquearInput ? true : false"
                                                v-model="venta.pago.efectivo.monto"
                                                min="0"
                                                step="0.01"
                                                type="number"
                                                placeholder="S/."
                                                class="form-control pos-pay-input-monto"
                                            />
                                        </div>

                                        <div class="pos-pay-row-v2" :class="{ 'is-selected': metodoPagoSeleccionado === 'visa' }">
                                            <div class="pos-pay-badge pos-pay-badge-purple">
                                                <span class="pos-pay-badge-icon"><i class="fas fa-mobile-alt"></i></span>
                                                <span class="pos-pay-badge-label">Yape / Plin</span>
                                            </div>
                                            <button type="button"
                                                class="pos-pay-check pos-pay-check-purple"
                                                :class="{ 'active': metodoPagoSeleccionado === 'visa' }"
                                                :disabled="bloquearInput ? true : false"
                                                @click="seleccionarMetodoPago('visa')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <input
                                                :disabled="bloquearInput ? true : false"
                                                v-model="venta.pago.visa.descripcion"
                                                type="text"
                                                placeholder="Operación"
                                                class="form-control pos-pay-input-ref"
                                            />
                                            <input
                                                :disabled="bloquearInput ? true : false"
                                                class="form-control pos-pay-input-monto"
                                                v-model="venta.pago.visa.monto"
                                                min="0"
                                                step="0.01"
                                                type="number"
                                                placeholder="S/."
                                                @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - $event.target.value)"
                                            />
                                        </div>

                                        <div class="pos-pay-row-v2" :class="{ 'is-selected': metodoPagoSeleccionado === 'mastercard' }">
                                            <div class="pos-pay-badge pos-pay-badge-orange">
                                                <span class="pos-pay-badge-icon"><i class="fas fa-university"></i></span>
                                                <span class="pos-pay-badge-label">Transferencia</span>
                                            </div>
                                            <button type="button"
                                                class="pos-pay-check pos-pay-check-orange"
                                                :class="{ 'active': metodoPagoSeleccionado === 'mastercard' }"
                                                :disabled="bloquearInput ? true : false"
                                                @click="seleccionarMetodoPago('mastercard')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <input
                                                :disabled="bloquearInput ? true : false"
                                                v-model="venta.pago.mastercard.descripcion"
                                                type="text"
                                                placeholder="Operación"
                                                class="form-control pos-pay-input-ref"
                                            />
                                            <input
                                                :disabled="bloquearInput ? true : false"
                                                class="form-control pos-pay-input-monto"
                                                v-model="venta.pago.mastercard.monto"
                                                min="0"
                                                step="0.01"
                                                type="number"
                                                placeholder="S/."
                                                @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - $event.target.value)"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template slot="adicional" v-if="!venta.EsCredito">
                                <table class="table b-table border table-footer table-sm text-center"  v-if="false" >
                                    <tbody>
                                        <tr><td>Mano de obra bruta</td></tr>
                                        <tr><td><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                        <tr><td>Mano de obra neta</td></tr>
                                        <tr><td><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                                    </tbody>
                                </table>
                            </template>
                        </productos-carrito>
                    </div>
                    </b-col>
                </b-row>

                <!-- ================= ACCIONES RAPIDAS ================= -->
                <div class="pos-quickgrid">
                    

                    <div v-if="false">
                        <!-- Reservado para futuras opciones (pre-orden / configuración) -->
                        <b-button-group size="sm">
                            <pre-orden-lista 
                                v-show="!this.$route.query.idPreOrden"
                                :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                                @onPreOrden="selectPreOrden"></pre-orden-lista>
                        </b-button-group>
                        <b-button-group size="sm">
                            <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                        </b-button-group>
                    </div>
                </div>
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
import Movimiento from '@/components/Movimiento'

export default {
    beforeMount () {
        this.initForm()
    },
    mounted () {
        // this.getNumeroVenta()
        this.getVendedor()
        //setInterval(this.updateTime, 1000)
        this.loadClienteVarios()
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
            metodoPagoSeleccionado: 'efectivo',
            venta: {},
            bloquearInput:false,
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
                    //{ key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
                    { key: 'Producto' },
                    { key: 'Descripcion', label: 'Descripción'},
                    // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
                    // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
                    //{ key: 'ProductoMarca', label: 'Marca' },
                    //{ key: 'ProductoMedicion', label: 'Medición' },
                    { key: 'eventPres', label: '' },
                    { key: 'cantxPrecio', variant: 'warning' },
                    { key: 'cantidad' },
                    { key: 'precio', label: 'Precio' },
                    //{ key: 'utilidad', label: 'Porc. Utilidad %' },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'text-center' }, 
                    { key: 'descuento', label: 'Descuento'},
                    { key: 'total', label: 'Total', variant: 'danger', class:'text-center' },
                    { key: 'acciones', label: ''}
                ]
            }
        }
    },
    watch: {
        totalCart (newVal) {
            if (!this.venta.pago) return

            const total = Number(parseFloat(newVal || 0).toFixed(2))

            if (this.metodoPagoSeleccionado === 'efectivo') {
                this.venta.pago.efectivo.monto = total
            } else if (this.metodoPagoSeleccionado === 'visa') {
                this.venta.pago.visa.monto = total
            } else if (this.metodoPagoSeleccionado === 'mastercard') {
                this.venta.pago.mastercard.monto = total
            }
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
        abrirModalInterno (refName) {
            const comp = this.$refs[refName]
            if (!comp) return

            if (typeof comp.openModal === 'function') {
                comp.openModal()
                return
            }

            if (comp.show !== undefined) {
                comp.show = true
                return
            }

            const el = comp.$el || comp
            if (el && typeof el.click === 'function') {
                el.click()
            }
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
                    IdTipoDoc: 7,
                    TipoDoc: 'BOLETA ELEC.',
                    LimitItems: 0,
                    CodSunat: '03'
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
                    mastercard: { IdMetodoPago: 3, monto: 0 }
                },
                idProductos:[],
                NroComprobanteModifica: '',
                CodSunatModifica: '',
                NotaIdMotivo: '',
                NotaDescMotivo: '',
                IdPreOrden: [],
                EsOrganizacion: 0,
                NombreOrganizacion: '',
                CampoDireccion: ''
            }
            this.isNota = false
            this.bloquearInput = false
            this.puntosAplicados = 0
            this.descuentoPuntos = {}
            this.descuentoGeneral = 0
            this.metodoPagoSeleccionado = 'efectivo'
        },
        setTipoVenta (obj) {
            this.isNota = false
            this.bloquearInput = false
            this.venta.NroComprobanteModifica = ''
            this.venta.CodSunatModifica = ''
            this.venta.NotaIdMotivo = ''
            this.venta.NotaDescMotivo = ''
            this.$refs.inputDocRef && this.$refs.inputDocRef.setValue('');
            if (obj.CodSunat == "07" || obj.CodSunat == "08") { // 07 - nota de credito, 08 - Nota de debito
                this.isNota = true
            }

            if (obj.TieneIgv) {
                this.tieneIgv = true
            } else {
                this.tieneIgv = false
            }
            this.$refs.inputTipoVenta.setValue(obj.TipoDoc)
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
                            this.venta.cliente = response.data
                            this.venta.CampoDireccion = this.venta.cliente.Direccion
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
        onSetProducto (obj, cant, precio) {
            if (cant) {
                obj.cantidad = cant
                obj.precio = precio || obj.PrecioContado
                return obj
            }
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
            return obj
        },
        onAddProducto (obj) {
            this.actualizarDescuento()
            this.obtenerStok({
                IdAlmacen: this.venta.almacen.IdAlmacen
            })
        },
        actualizarDescuento () {
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

            this.axios.post(this.API + 'proformas', this.venta)
            .then((response) => {
                this.inProgress = false
                this.$toasted.show('Proforma Guardada con exito')
                this.limpiarDespuesVenta()
                 this.loadClienteVarios()
                window.open('/imprimir/proforma.php?IdProforma=' + response.data.insertId,'_blank');
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                this.inProgress = false
            })
        },
        seleccionarMetodoPago (metodo) {
            const total = Number(parseFloat(this.totalCart || 0).toFixed(2))

            this.metodoPagoSeleccionado = metodo

            Object.keys(this.venta.pago).forEach((key) => {
                this.venta.pago[key].monto = key === metodo ? total : 0
            })
        },
        guardarVenta (evt) {
            evt.preventDefault();

            this.inProgress = true
            let totalPagos = 0
            totalPagos += parseFloat(this.venta.pago.efectivo.monto)
            totalPagos += parseFloat(this.venta.pago.visa.monto)
            totalPagos += parseFloat(this.venta.pago.mastercard.monto)

            if (!this.venta.EsCredito) {
                if (this.totalCart != totalPagos) {
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
            if (esEmpresa && this.venta.tipoVenta.CodSunat == '03') {
                this.$toasted.show('Una boleta no debe ser emitida para una empresa.')
                this.inProgress = false
                return
            }

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

                this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(item => item.value == this.venta.NotaIdMotivo)[0].text
            }

            if(!this.productos.length) {
                this.$toasted.show('No hay productos para vender')
                this.inProgress = false            
                return          
            }

            // NO PERMITE CANTIDAD Y PRECIOS 0
            let sinStock = 0
            let totalCero = 0 
            let noDescrip = 0
            let infoDescrip = ''
            this.productos.forEach((pro,index) => {
                if((pro.CodigoBarra !== 'MANODEOBRA') && ((pro.stock <= 0) || (parseFloat(pro.cantidad) > pro.stock))) {
                    sinStock += 1
                }

                if(parseFloat(pro.total) <= 0) {
                    totalCero += 1
                }
                pro.Descripcion = (''+pro.Descripcion).trim()
                if(pro.Descripcion ==''){
                    noDescrip +=1
                    infoDescrip = (index+1) +" "+ pro.Producto
                }
            });

/*             if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != '07') {
                this.$toasted.error('No se puede vender un producto sin stock');
                this.inProgress = false            
                return;
            } */

            /*if(noDescrip >= 1) {
                this.$toasted.error('El item '+ infoDescrip + ' no tiene descripcion, si no tiene debe colocar un punto',{duration:3000});
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
            this.venta.NombreOrganizacion = (''+this.venta.NombreOrganizacion).trim().toUpperCase()
            this.venta.PagoCon = this.$refs.carrito.pagocon
            this.venta.idProductos = this.productos.map(prod => prod.IdProducto)
            this.axios.post(this.API + 'ventas', this.venta)
            .then((response) => {

                if(response.data.actualizar){
                    this.inProgress = false
                    this.obtenerStok({IdAlmacen: this.venta.almacen.IdAlmacen})
                    this.$toasted.show(response.data.msg)
                    return; 
                }else{
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
                    this.$refs.inputTipoVenta.setValue('BOLETA ELEC.')
                    this.$refs.inputAlmacen.setValue('PRINCIPAL')
                    this.$refs.inputPuntoVenta.setValue('CAJA1')
                    this.$refs.inputCliente.setValue('')
                    this.$refs.inputVendedor.setValue('')
                    //this.$refs.inputComisionista.setValue('')
                    this.$refs.carrito.pagocon = 0
                    window.open('/imprimir/index.php?IdDocVenta=' + response.data.insertId,'_blank');

                }
                 
                   

            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
                this.inProgress = false
            })
        },
        eliminarCliente () {
            this.$refs.inputCliente.setValue('')
            this.venta.cliente = {}
        },
        eliminarComisionista () {
            this.$refs.inputComisionista.setValue('')
            this.venta.comisionista = {}
        },
    },
    components: {
        ProductosCarrito,
        ClientesForm,
        Movimiento,
        ConfigVenta,
        PagoDeuda,
        PreOrdenLista,
        Proformas
    }
}
</script>
<style scoped>
/* =========================================================
   JoBi-style redesign v3
   Iconos FontAwesome (fas fa-*) en vez de emojis + estilo
   más alineado al módulo POS moderno (badge de sesión,
   tarjetas de estadística, botones-icono en pill)
   Requiere: FontAwesome 5 cargado globalmente
   (<link href="https://use.fontawesome.com/releases/v5.1.0/css/all.css">)
   =========================================================*/

.pos-jobi {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-orange: #F5A623;
    --jobi-pink: #F43F7E;
    --jobi-maroon: #9F1D3F;
    --jobi-bg: #EEF3F8;
    --jobi-card-bg: #FFFFFF;
    --jobi-stat-bg: #EAF3FC;
    background: var(--jobi-bg);
    min-height: 100vh;
    padding-bottom: 10px !important;
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    position: relative;
    width: 100%;
    min-height: 100vh;
    overflow-x: hidden;
}

.pos-container {
    padding-top: 10px !important;
    max-width: 100% !important;
}

.pos-venta-badge {
    position: absolute;
    top: 6px;
    left: 24px;
    z-index: 5;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--jobi-green);
    color: #fff;
    font-weight: 800;
    font-size: .76rem;
    letter-spacing: .03em;
    text-transform: uppercase;
    padding: 7px 16px 7px 12px;
    border-radius: 999px;
    box-shadow: 0 6px 14px rgba(23,165,84,.35);
}
.pos-venta-badge-icon {
    font-size: .9rem;
}

.pos-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(11,42,74,.10);
    padding: 12px 16px 8px !important;
}

.pos-panel-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    border-radius: 14px;
    padding: 18px 18px 10px;
    margin-bottom: 18px;
    color: #fff;
}

.pos-header-row {
    row-gap: 14px;
}

.pos-header-col {
    display: flex;
}

.pos-header-col-fields {
    display: block;
}

@media (min-width: 992px) {
    .pos-header-col + .pos-header-col {
        border-left: 1px solid rgba(255,255,255,.12);
        padding-left: 22px;
    }
}

.pos-panel-header label {
    color: rgba(255,255,255,.72);
    font-size: .70rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.pos-label-icon {
    font-size: .82em;
    opacity: .85;
}

.pos-field >>> input,
.pos-field >>> select,
.pos-field >>> .form-control {
    border-radius: 10px !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 700;
    height: 42px;
    border: none !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}

.pos-panel-header >>> .custom-control-label,
.pos-panel-header .form-text {
    color: rgba(255,255,255,.75);
}

.pos-cliente-group {
    align-items: center;
}
.pos-cliente-pin {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255,255,255,.12);
    color: #fff;
    margin-right: 6px;
    font-size: 1.05rem;
}
.pos-field-cliente >>> .btn {
    border-radius: 10px !important;
    background: var(--jobi-purple) !important;
    border: none !important;
    font-weight: 700;
    box-shadow: 0 4px 10px rgba(124,58,237,.30);
}
.pos-select-direccion {
    border-radius: 10px;
}
.pos-organizacion {
    align-items: center;
    gap: 10px;
}

.pos-stat-col {
    flex-direction: column;
    gap: 10px;
    justify-content: center;
    width: 100%;
}

.pos-stat-card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    padding: 10px 12px;
    margin-bottom: 0 !important;
}

.pos-header-col.d-flex.align-items-stretch .pos-stat-comprobante {
    align-self: center;
    width: 100%;
}

.pos-stat-icon {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: #fff;
    color: var(--jobi-navy);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}

.pos-stat-body {
    flex: 1 1 auto;
    min-width: 0;
}
.pos-stat-body-full >>> input,
.pos-stat-body-full >>> .form-control {
    border-radius: 8px !important;
    height: 36px;
    border: none !important;
    background: #fff !important;
    box-shadow: 0 2px 6px rgba(0,0,0,.15);
}

.pos-stat-label {
    font-size: .86rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: rgba(255,255,255,.65);
    font-weight: 800;
    margin-bottom: 2px;
}

.pos-stat-comprobante {
    background: rgba(255, 255, 255, 0.12) !important;
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    padding: 32px 14px!important;
}

.pos-stat-comprobante .big-text {
    font-size: 45px !important;
    line-height: 1.1;
    color: #fff;
    font-weight: 800;
}

.pos-stat-sub {
    font-size: .80rem;
    color: rgba(255,255,255,.55);
}

.pos-badge-adelanto {
    display: block;
    margin-top: 8px;
    border-radius: 999px !important;
    padding: 6px 10px;
    font-weight: 700;
}
.pos-badge-adelanto i {
    margin-right: 4px;
}

.posjobi-cart-shell {
    background: #fff;
    border-radius: 14px;
    padding: 10px;
    box-shadow: 0 6px 18px rgba(11,42,74,.06);
    margin-top: 4px;
}

.posjobi-cart-shell >>> thead th {
    background: var(--jobi-navy) !important;
    color: #fff !important;
    text-transform: uppercase;
    font-size: .72rem;
    letter-spacing: .04em;
    border: none !important;
}

.posjobi-cart-shell >>> .table td {
    vertical-align: middle;
}

.posjobi-cart-shell >>> .input-group {
    border-radius: 999px;
    overflow: hidden;
    box-shadow: 0 3px 10px rgba(11,42,74,.08);
}
.posjobi-cart-shell >>> .input-group .form-control {
    border: none !important;
}
.posjobi-cart-shell >>> .input-group-text,
.posjobi-cart-shell >>> .input-group-prepend .btn,
.posjobi-cart-shell >>> .input-group-append .btn {
    border: none !important;
}

.pos-main-actions-top {
    width: 100%;
    justify-content: center;
    padding: 10px 4px 16px;
    margin-top: 4px;
    margin-bottom: 6px;
    border-bottom: 1px dashed #dbe6f1;
}

.pos-operaciones-col {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.pos-mini-panel {
    background: #fff;
    border-radius: 14px;
    margin-top: 10px;
    box-shadow: 0 6px 16px rgba(11,42,74,.08);
    padding: 14px;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
}

.pos-mini-panel2 {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(11,42,74,.08);
    padding: 14px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.pos-payment-panel {
    flex: 1;
    height: auto;
    min-height: 100%;
}

/* -------- Formas de pago (estilo tarjeta redondeada tipo mockup) -------- */
.pos-payment-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 8px 22px rgba(11,42,74,.10);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.pos-payment-header {
    display: flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(120deg, #16a1c7 0%, #0e6fae 100%);
    padding: 14px 18px;
    color: #fff;
}

.pos-payment-header-icon {
    flex: 0 0 auto;
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: rgba(255,255,255,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.pos-payment-header-text {
    min-width: 0;
}

.pos-payment-header-title {
    font-weight: 900;
    font-size: 1rem;
    letter-spacing: .01em;
}

.pos-payment-header-sub {
    font-size: .72rem;
    color: rgba(255,255,255,.85);
    font-weight: 600;
}

.pos-payment-body {
    padding: 14px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1 1 auto;
}

.pos-pay-row-v2 {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 1.5px solid #e7edf5;
    border-radius: 16px;
    padding: 9px 10px;
    transition: all .18s ease;
}

.pos-pay-row-v2:hover {
    box-shadow: 0 5px 12px rgba(11,42,74,.08);
}

.pos-pay-row-v2.is-selected {
    border-color: #d3e0f0;
    background: #f8fbfe;
}

.pos-pay-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 0 0 auto;
    min-width: 128px;
}

.pos-pay-badge-icon {
    flex: 0 0 auto;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .95rem;
    box-shadow: 0 4px 10px rgba(0,0,0,.12);
}

.pos-pay-badge-label {
    font-weight: 800;
    font-size: .82rem;
    color: var(--jobi-navy);
    white-space: nowrap;
}

.pos-pay-badge-green .pos-pay-badge-icon { background: var(--jobi-green); }
.pos-pay-badge-purple .pos-pay-badge-icon { background: var(--jobi-purple); }
.pos-pay-badge-orange .pos-pay-badge-icon { background: var(--jobi-orange); }

.pos-pay-check {
    flex: 0 0 auto;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: .82rem;
    cursor: pointer;
    opacity: .35;
    transition: all .18s ease;
}

.pos-pay-check:hover:not(:disabled) {
    transform: translateY(-1px);
}

.pos-pay-check.active {
    opacity: 1;
    box-shadow: 0 5px 12px rgba(0,0,0,.20);
}

.pos-pay-check-green { background: var(--jobi-green); }
.pos-pay-check-purple { background: var(--jobi-purple); }
.pos-pay-check-orange { background: var(--jobi-orange); }

.pos-pay-check:disabled {
    cursor: not-allowed;
}

.pos-pay-input-ref {
    flex: 1 1 auto;
    min-width: 0;
    height: 38px;
    border-radius: 999px !important;
    border: 1.5px solid #e3ebf4 !important;
    font-size: .82rem;
    padding-left: 14px;
    background: #f8fbfe;
}

.pos-pay-input-monto {
    flex: 0 0 110px;
    width: 110px;
    height: 46px;
    border-radius: 999px !important;
    border: 1.5px solid #e3ebf4 !important;
    text-align: center;
    font-weight: 800;
    font-size: 1.05rem;
    color: var(--jobi-navy);
    background: #f8fbfe;
}

@media (max-width: 700px) {
    .pos-pay-row-v2 {
        flex-wrap: wrap;
    }
    .pos-pay-badge {
        min-width: 0;
        flex: 1 1 auto;
    }
    .pos-pay-input-ref {
        flex: 1 1 100%;
        order: 4;
    }
    .pos-pay-input-monto {
        flex: 0 0 100px;
        height: 42px;
    }
}

.pos-mini-panel-title {
    font-weight: 800;
    text-transform: uppercase;
    font-size: .74rem;
    letter-spacing: .04em;
    color: #fff;
    background: var(--jobi-navy);
    margin: -14px -14px 12px -14px;
    padding: 10px 14px;
    border-radius: 14px 14px 0 0;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pos-mini-panel-teal .pos-mini-panel-title { background: var(--jobi-teal); }
.pos-mini-panel-blue .pos-mini-panel-title { background: var(--jobi-blue); }

.pos-mini-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.pos-mini-icon {
    font-size: 1rem;
    color: var(--jobi-orange);
    width: 18px;
    text-align: center;
}
.pos-mini-hint {
    font-size: .72rem;
    color: #6b85a3;
    margin-bottom: 10px;
}

.pos-input-descuento {
    flex: 1 1 auto;
}
.pos-input-descuento >>> .input-group-text {
    background: var(--jobi-orange) !important;
    color: #fff !important;
    border: none !important;
    font-weight: 700;
    border-radius: 10px 0 0 10px;
}
.pos-input-descuento >>> input {
    border-radius: 0 10px 10px 0 !important;
    border: 1.5px solid #f3d9ad !important;
}

.pos-btn-pagar {
    min-height: 52px;
    border-radius: 999px !important;
    background: var(--jobi-green) !important;
    border: none !important;
    font-weight: 900;
    letter-spacing: .05em;
    text-transform: uppercase;
    padding: 0 30px !important;
    font-size: 1rem;
    box-shadow: 0 10px 22px rgba(23,165,84,.35);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all .18s ease;
}

.pos-btn-pagar:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 12px 25px rgba(23,165,84,.40);
}
.pos-btn-icon {
    font-size: 1.1rem;
}

.pos-main-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
    padding: 14px 8px 18px;
}

.pos-main-actions .btn {
    margin: 0 !important;
}

.pos-btn-proforma,
.pos-btn-imprimir {
    min-height: 52px;
    border-radius: 999px !important;
    border: none !important;
    padding: 0 24px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: .035em;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 8px 18px rgba(11,42,74,.14);
}

.pos-btn-proforma {
    background: var(--jobi-orange) !important;
    color: #fff !important;
}

.pos-btn-proforma:hover:not(:disabled) {
    background: #e8940c !important;
    color: #fff !important;
    transform: translateY(-1px);
}

.pos-btn-imprimir {
    background: var(--jobi-blue) !important;
    color: #fff !important;
}

.pos-btn-imprimir:hover {
    color: #fff !important;
    transform: translateY(-1px);
}

/* -------- Cuadrícula de acciones rápidas -------- */
.pos-quickgrid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 4px 4px 22px;
}
.pos-quickgrid-col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    flex: 1 1 320px;
}

.pos-quickgrid-row3 {
    display: flex;
    flex-wrap: nowrap;
    gap: 8px;
    align-content: start;
}

.pos-quick-btn {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    justify-content: flex-start;
    gap: 6px;
    height: 74px;
    flex: 1 1 0;
    min-width: 0;
    border-radius: 14px;
    border: 2px solid transparent;
    cursor: pointer;
    color: #fff;
    font-weight: 700;
    font-size: .78rem;
    text-align: center;
    padding: 8px 4px;
    box-sizing: border-box;
    box-shadow: 0 6px 14px rgba(11,42,74,.16);
    transition: box-shadow .18s ease, border-color .18s ease, filter .18s ease;
    user-select: none;
    outline: none;
}

.pos-quick-btn:hover {
    box-shadow: 0 10px 22px rgba(11,42,74,.30);
    border-color: rgba(255,255,255,.55);
    filter: brightness(1.05);
}

.pos-quick-btn:active {
    box-shadow: 0 3px 8px rgba(11,42,74,.30) inset;
    filter: brightness(0.97);
}

.pos-quick-btn:focus-visible {
    box-shadow: 0 0 0 3px rgba(255,255,255,.65), 0 10px 22px rgba(11,42,74,.30);
}

.pos-quick-icon {
    flex: 0 0 auto;
    align-self: center;
    width: 30px;
    height: 30px;
    border-radius: 9px;
    background: rgba(255,255,255,.22);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.pos-quick-label {
    flex: 1 1 auto;
    min-height: 0;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.1;
    overflow: hidden;
}

.pos-quick-label >>> .pos-quick-trigger {
    background: transparent !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700 !important;
    padding: 0 !important;
    box-shadow: none !important;
    text-transform: none !important;
    letter-spacing: normal !important;
    font-size: .72rem !important;
    line-height: 1.1;
    cursor: pointer;
    display: block;
    width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pos-quick-teal   { background: linear-gradient(135deg, var(--jobi-teal), #0c7d73); }
.pos-quick-blue   { background: linear-gradient(135deg, var(--jobi-blue), #1f52b8); }
.pos-quick-purple { background: linear-gradient(135deg, var(--jobi-purple), #5f22c9); }
.pos-quick-orange { background: linear-gradient(135deg, var(--jobi-orange), #d68300); }
.pos-quick-pink   { background: linear-gradient(135deg, var(--jobi-pink), #c22a5f); }

/* Original rules kept intact */
.alert {
    margin-bottom: 0;
}
.puntos {
    font-size: 52px;
}
.big-text {
    font-size: 30px;
}
@media (min-width: 1200px) {
    .pos-panel-header {
        padding: 14px 16px 8px;
        margin-bottom: 12px;
    }

    .posjobi-cart-shell {
        padding: 8px;
    }

    .pos-main-actions {
        padding-top: 8px;
        padding-bottom: 12px;
    }
}

@media (max-width: 900px) {
    .pos-main-actions {
        gap: 8px;
    }

    .pos-main-actions .btn {
        flex: 1 1 220px;
    }

    .pos-pay-fields {
        min-width: 120px;
    }
}

@media only screen and (max-width: 750px) {
    .ocularMovil{
        display: none;
    }
    .mostrarSerie{
      display: inline !important;
    }
}

@media (max-width: 600px) {
    .pos-quickgrid-row3 {
        flex-wrap: wrap;
    }
    .pos-quick-btn {
        flex: 1 1 100%;
        flex-direction: row;
        justify-content: flex-start;
        height: 52px;
        padding: 0 14px;
    }
    .pos-quick-label >>> .pos-quick-trigger {
        text-align: left;
    }
}

/* -------- Neutraliza el estilo propio de botones anidados (Movimiento, PagoDeuda, Proformas) -------- */
.pos-quick-label >>> button,
.pos-quick-label >>> .btn,
.pos-quick-label >>> .btn-group {
    all: unset !important;
    box-sizing: border-box !important;
    display: flex !important;
    width: 100% !important;
    height: 100% !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
}

.pos-quick-label >>> .movjobi-trigger {
    color: #fff !important;
    font-weight: 700 !important;
    font-size: .72rem !important;
    text-transform: none !important;
    letter-spacing: normal !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    cursor: pointer !important;
}

.pos-quick-label >>> .movjobi-trigger-icon,
.pos-quick-label >>> i,
.pos-quick-label >>> svg {
    display: none !important;
}
</style>