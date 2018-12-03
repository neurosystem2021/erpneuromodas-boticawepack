<template>
    <div>
        <Menu v-if="false"></Menu>

        <br />
        <b-container class="bv-example-row">
            <div>
                <div class="float-right">
                    <b-button-group size="sm">
                        <pago-deuda></pago-deuda>
                    </b-button-group>
                    <b-button-group size="sm">
                        <pre-orden-lista :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"></pre-orden-lista>
                    </b-button-group>
                    <b-button-group size="sm">
                        <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
                    </b-button-group>
                    
                </div>
            </div>
            <br /><br />
            <b-form @submit="guardarVenta" @keydown.enter="evt => evt.preventDefault()">
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
                            </b-col>
                            <b-col md="4">
                                <b-form-group>
                                    <label>Almacen:</label>
                                    <autocomplete
                                        required="required"
                                        ref="inputAlmacen"
                                        :url="API + 'almacenes'"
                                        anchor="Almacen"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setAlmacen" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
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
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col md="3">
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
                                                <b-input-group prepend="%" append="Descuento">
                                                    <b-form-input
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        max="100"
                                                        :readonly="(idPerfil == 3) ? true : false"
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
                                                    v-model="venta.EsCredito"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Venta a credito
                                                </b-form-checkbox>
                                                <b-form-input 
                                                    v-if="venta.EsCredito"
                                                    v-model="venta.FechaCredito"
                                                    type="date">
                                                </b-form-input>
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
                                                    v-model="venta.pago.efectivo.descripcion"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <b-form-input
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
                                                    v-model="venta.pago.visa.descripcion"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
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
                                                    v-model="venta.pago.mastercard.descripcion"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <input
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
                        </productos-carrito>
                    </b-col>
                </b-row>
                <b-row align-h="center" class="text-center">
                    <b-col cols="8">
                        <br />
                        <b-button type="submit" :disabled="inProgress" variant="success" size="lg">Efectuar Venta</b-button>
                        <!--<b-button v-if="ultimaVenta" target="_blank" variant="info" size="lg" :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>-->
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


export default {
    beforeMount () {
        this.initForm()
    },
    mounted () {
        // this.getNumeroVenta()
        this.getVendedor()
        //setInterval(this.updateTime, 1000)
        /* this.loadFirstAlmacen()
        this.loadFirstPuntoVenta() */
    },
    data () {
        return {
            dni: '',
            newDniRuc: '',
            ultimaVenta: '',
            inProgress: false,
            API: config.API,
            puntosAplicados: 0,
            descuentoPuntos: {},
            descuentoGeneral: 0,
            tieneIgv: false,
            venta: {},
            carrito: {
                fields: [
                    { key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
                    { key: 'CodigoBarra', label: 'Código' }, 
                    { key: 'Producto' }, 
                    { key: 'ProductoMarca', label: 'Marca' },
                    { key: 'ProductoFormaFarmaceutica', label: 'Lab.' }, 
                    { key: 'ProductoMedicion', label: 'Med.' },
                    { key: 'PrecioPorMayor', label: 'Precio x Mayor' },
                    { key: 'StockPorMayor', label: 'Stock x Mayor' },
                    { key: 'cantidad' },
                    { key: 'precio', label: 'Precio' },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'text-center' }, 
                    { key: 'descuento', label: 'Descuento'},
                    { key: 'total', label: 'Total', variant: 'danger', class:'text-center' },
                    { key: 'acciones', label: ''}
                ]
            },
            idPerfil: sessionStorage.getItem('idPerfil')
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
        })
    },
    methods: {
        ...mapActions({
            aplicarDescuento: 'aplicarDescuentoGeneral',
            limpiarCart: 'limpiarCarrito',
            obtenerStok: 'obtenerStock'
        }),
        loadFirstAlmacen () {
            this.axios.get(this.API + 'almacenes/primero')
                .then(res => {
                    this.$refs.inputAlmacen.setValue(res.data.Almacen)
                    this.venta.almacen = res.data
                })
        },
        loadFirstPuntoVenta () {
            this.axios.get(this.API + 'puntoventa/primero')
                .then(res => {
                    this.$refs.inputPuntoVenta.setValue(res.data.PuntoVenta)
                    this.setPuntoVenta(res.data)
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
                tipoVenta: {
                    IdTipoDoc: 7,
                    TipoDoc: 'BOLETA ELEC.',
                    LimitItems: 0
                },
                almacen: {
                    // IdAlmacen: 1,
                    // Almacen: 'VENTA'
                },
                puntoVenta: {
                    // IdDocVentaPuntoVenta: 1,
                    // PuntoVenta: 'CAJA1'
                },
                vendedor: this.venta.vendedor || {},
                FechaCredito: this.$moment().format("YYYY-MM-DD"),
                FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
                pago: {
                    efectivo: { IdMetodoPago: 1, monto: 0 },
                    visa: { IdMetodoPago: 2, monto: 0 },
                    mastercard: { IdMetodoPago: 3, monto: 0 }
                }
            }

            this.descuentoGeneral = 0
        },
        setTipoVenta (obj) {
            if (obj.TieneIgv) {
                this.tieneIgv = true
            } else {
                this.tieneIgv = false
            }
            this.venta.tipoVenta = obj
            this.getNumeroVenta()
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
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        setVendedor (obj) {
            this.venta.vendedor = obj
        },
        setCliente (obj, dni = false) {
            let params = {}
            if (dni) {
                params = {
                    dni: dni
                }
            }else {
                params = {
                    idCliente: obj
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
        },
        onSetProducto (obj) {
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
                this.$nextTick(() => {
                    var subTotal = parseFloat(this.$refs.carrito.sumatoriaSubTotal) + parseFloat(this.$refs.carrito.sumatoriaDescuento)
                    var porcentaje = (response.data.descuento * 100) / subTotal
                    this.descuentoPuntos = response.data
                    //this.aplicarDescuento(response.data.descuento)
                    this.aplicarDescuento(porcentaje)
                })

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

            if(!this.productos.length) {
                this.$toasted.show('No hay productos para vender')
                this.inProgress = false            
                return          
            }

            this.venta.productos = this.productos
            this.venta.puntosAplicados = this.puntosAplicados || 0
            this.venta.totalCart = this.totalCart || 0
            this.venta.PagoCon = this.$refs.carrito.pagocon
            this.axios.post(this.API + 'ventas', this.venta)
            .then((response) => {
                this.$toasted.show('Venta realizada con exito')
                this.inProgress = false
                this.tieneIgv = false
                this.ultimaVenta = response.data.insertId
                this.initForm()
                this.limpiarCart()
                this.getVendedor()
                // this.$refs.inputTipoVenta.setValue('BOLETA ELEC.')
                // this.$refs.inputAlmacen.setValue('VENTA')
                // this.$refs.inputPuntoVenta.setValue('CAJA1')
                this.$refs.inputCliente.setValue('')
                // this.$refs.inputVendedor.setValue('')


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
        }
    },
    components: {
        ProductosCarrito,
        ClientesForm,
        ConfigVenta,
        PagoDeuda,
        PreOrdenLista
    }
}
</script>
<style>
.puntos {
    font-size: 52px;
}
.big-text {
    font-size: 30px;
}

</style>
