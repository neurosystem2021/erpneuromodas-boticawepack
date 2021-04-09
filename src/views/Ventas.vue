<template>
    <div>
        <!--<b-alert variant="warning" show class="text-center" v-if="this.$route.query.idPreOrden">Se está pagando la habitación HH</b-alert>-->

        <Menu v-if="false"></Menu>

        <br />
        <b-container class="bv-example-row">
            <div>
                <div class="float-right">
                    <b-button-group size="sm">
                        <pago-deuda></pago-deuda>
                    </b-button-group>
                    <b-button-group size="sm">
                        <proformas :idAlmacen="venta.almacen.IdAlmacen" @onSelectProforma="setCliente"></proformas>
                    </b-button-group>
                    <b-button-group size="sm">
                        <pre-orden-lista 
                            v-show="!this.$route.query.idPreOrden"
                            :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                            @onPreOrden="selectPreOrden"></pre-orden-lista>
                    </b-button-group>
                    <b-button-group size="sm">
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
                            <b-col md="4">
                                <b-form-group>
                                    <label>Tipo de Venta:</label>
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
                                </b-form-group>
                                <b-form-group v-if="isNota">
                                    <label>Doc. Referencia:</label>
                                    <autocomplete
                                        required="required"
                                        ref="inputDocRef"
                                        :url="API + 'ventas/lista'"
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
                            <b-col md="4">
                                <b-form-group>
                                    <label>Almacen:</label>
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
                            <b-col md="4">
                                <b-form-group>
                                    <label>Punto de Venta:</label>
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
                            <b-col md="12">
                                <b-form-group>
                                    <label>Cliente:</label>
                                    
                                    <b-input-group>
                                        <clientes-form txtButton="Editar" v-if="venta.cliente.IdCliente" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>
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
                                        <option value="Direccion">{{ venta.cliente.Direccion }}</option>
                                        <option value="Direccion2">{{ venta.cliente.Direccion2 }}</option>
                                        <option value="Direccion3">{{ venta.cliente.Direccion3 }}</option>
                                    </b-form-select>

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
                            :bloquearInput="bloquearInput"
                            :idAlmacen="venta.almacen.IdAlmacen"
                            :onSetProducto="onSetProducto">

                            <template slot="operaciones">
                                <table class="table b-table border table-footer">
                                    <tbody>
                                        <tr>
                                            <td  class="text-center">Opciones</td>
                                        </tr>
                                        <tr v-if="puntosAplicados == 0">
                                            <td>
                                                <b-input-group prepend="S/" append="Descuento">
                                                    <b-form-input
                                                        :disabled="bloquearInput?true:false"
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        @input="aplicarDescuentoGeneral"
                                                        type="number"
                                                    ></b-form-input>
                                                </b-input-group>
                                                <b-form-text>
                                                    Descuento general a toda la venta.
                                                </b-form-text>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b-form-checkbox
                                                    :disabled="bloquearInput?true:false"
                                                    v-model="venta.EsCredito"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Venta a credito
                                                </b-form-checkbox>
                                                <b-form-input 
                                                    :disabled="bloquearInput?true:false"
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
                                            <td>
                                                <b-form-input
                                                    :disabled="bloquearInput?true:false"
                                                    v-model="venta.pago.efectivo.descripcion"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <b-form-input
                                                    :disabled="bloquearInput?true:false"
                                                    v-model="venta.pago.efectivo.monto"
                                                    min="0"
                                                    step="0.01"
                                                    type="number"
                                                    placeholder="S/."
                                                ></b-form-input>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Visa</td>
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
                                            <td>Mastercard</td>
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
                                    </tbody>
                                </table>
                            </template>
                            <template slot="adicional" v-if="!venta.EsCredito">
                                <table class="table b-table border table-footer table-sm text-center" >
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
                        <b-button type="button" :disabled="inProgress" variant="primary" size="md" @click="guardarProforma">Crear Proforma</b-button>
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
                    { key: 'manoobra', label: 'Mano de obra', class:'text-center' },
                    { key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
                    { key: 'Producto' },
                    //{ key: 'Descripcion', label: 'Descripción'},
                    { key: 'eventPres', label: '' },
                    { key: 'cantxPrecio', variant: 'warning' },
                    // { key: 'FechaAlquiler', label: 'Fecha Inicio' },
                    // { key: 'FechaAlquilerFin', label: 'Fecha Fin'},
                    /*{ key: 'ProductoMedicion', label: 'Medición' },*/
                    { key: 'cantidad' },
                    { key: 'precio', label: 'Precio' },
                    { key: 'utilidad', label: 'Porcentaje Utilidad %' },
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
             this.venta.pago.efectivo.monto = newVal - (parseFloat(this.venta.pago.visa.monto) + parseFloat(this.venta.pago.mastercard.monto))
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
                NroComprobanteModifica: '',
                CodSunatModifica: '',
                NotaIdMotivo: '',
                NotaDescMotivo: '',
                IdPreOrden: [],
                CampoDireccion: 'Direccion'
            }
            this.isNota = false
        },
        setTipoVenta (obj) {
            this.isNota = false
            this.bloquearInput=false,
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
                        NroComprobante: obj.NroComprobante
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
            this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen })
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
            } else {
                this.axios.get(this.API + 'cliente', {
                    params: params
                })
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
            }
            
            //this.actualizarPrecioConvenio(obj)
            // Actualizar DisabledTotal
        },
        onSetProducto (obj, cant, precio) {
            if (cant) {
                obj.cantidad = cant
                let preciosPorProducto = JSON.parse(obj.PreciosPorProducto).find((e) => e.cantidad === cant)
                obj.precio = preciosPorProducto.precio || 0
                return obj
            }
            obj.cantidad = 1
            obj.precio = precio || 0
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
                this.$toasted.show('Proforma Guardada con exito')
                this.limpiarDespuesVenta()
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
            this.productos.forEach(pro => {
                if((pro.CodigoBarra !== 'MANODEOBRA') && ((pro.stock <= 0) || (parseFloat(pro.cantidad) > pro.stock))) {
                    sinStock += 1
                }

                if(parseFloat(pro.total) <= 0) {
                    totalCero += 1
                }
            });

            if (sinStock >= 1) {
                this.$toasted.error('No se puede vender un producto sin stock');
                this.inProgress = false            
                return;
            }

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
            this.axios.post(this.API + 'ventas', this.venta)
            .then((response) => {

                // limpiar preorden seleccionado
                this.$router.replace('/ventas')
                this.$toasted.show('Venta realizada con exito')
                this.inProgress = false
                this.tieneIgv = false
                this.ultimaVenta = response.data.insertId
                this.initForm()
                this.limpiarCart()
                this.getVendedor()
                this.getNumeroVenta()
                this.$refs.inputTipoVenta.setValue('BOLETA ELEC.')
                this.$refs.inputAlmacen.setValue('VENTA')
                this.$refs.inputPuntoVenta.setValue('CAJA1')
                this.$refs.inputCliente.setValue('')
                this.$refs.inputVendedor.setValue('')
                this.$refs.inputComisionista.setValue('')
                this.$refs.carrito.pagocon = 0

                window.open('/imprimir/index.php?IdDocVenta=' + response.data.insertId,'_blank');
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
