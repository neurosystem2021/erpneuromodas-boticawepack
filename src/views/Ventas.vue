<template>
    <div>
        <Menu v-if="false"></Menu>
        <br />
        <b-form>
            <b-container class="bv-example-row">
                <b-row>
                    <b-col md="7">
                        <b-row>
                            <b-col md="4">
                                <b-form-group>
                                    <label>Tipo de Venta:</label>
                                    <autocomplete
                                        ref="inputTipoVenta"
                                        :url="API + 'ventas/tipos'"
                                        anchor="TipoDoc"
                                        initValue="BOLETA"
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
                                        ref="inputAlmacen"
                                        :url="API + 'almacenes'"
                                        anchor="Almacen"
                                        initValue="VENTA"
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
                                </b-form-group>
                            </b-col>
                            <b-col md="12">
                                <b-form-group>
                                    <label>Cliente:</label>
                                    <autocomplete
                                        ref="inputCliente"
                                        :url="API + 'clientes'"
                                        anchor="ClienteDniRuc"
                                        label="writer"
                                        placeholder=""
                                        :on-select="setCliente" 
                                        :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                                        :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-form-group>
                            </b-col>
                        </b-row>
                    </b-col>
                    <b-col md="3">
                        <b-form-group class="text-center" v-if="venta.cliente.Cliente">
                            <div class="puntos">
                                {{ venta.cliente.Puntos || 0 }}
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
                            @onAddProducto="actualizarDescuento"
                            @onRemoveProducto="actualizarDescuento"
                            :igvTotal="tieneIgv"
                            ref="carrito" 
                            :showButton="false" 
                            :fields="carrito.fields"
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
                                                        v-model="descuentoGeneral"
                                                        min="0"
                                                        value="0"
                                                        @input="aplicarDescuentoGeneral"
                                                        type="number"
                                                    ></b-form-input>
                                                </b-input-group>
                                                <b-form-text>
                                                    Si aplica este descuento, los puntos del cliente se mantendrán intactos. Este descuento es independiente.
                                                </b-form-text>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>    
                            <template slot="metodopago">
                                <table class="table b-table border table-footer table-sm">
                                    <tbody>
                                        <tr>
                                            <td colspan="3" class="text-center">Metodo de Pago</td>
                                        </tr>
                                        <tr>
                                            <td>Efectivo</td>
                                            <td>
                                                <b-form-input
                                                    min="0"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <b-form-input
                                                    min="0"
                                                    type="number"
                                                    placeholder="S/."
                                                ></b-form-input>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Visa</td>
                                            <td>
                                                <b-form-input
                                                    min="0"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <b-form-input
                                                    min="0"
                                                    type="number"
                                                    placeholder="S/."
                                                ></b-form-input>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>Mastercard</td>
                                            <td>
                                                <b-form-input
                                                    min="0"
                                                    type="text"
                                                    placeholder="Descripcion"
                                                ></b-form-input>
                                            </td>
                                            <td>
                                                <b-form-input
                                                    min="0"
                                                    type="number"
                                                    placeholder="S/."
                                                ></b-form-input>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </template>
                        </productos-carrito>
                    </b-col>
                </b-row>
            </b-container>
        </b-form>
    </div>
</template>
<script>
import { mapActions } from 'vuex'
import config from '@/store/config.js'
import ProductosCarrito from '@/components/ProductosCarrito'

export default {
    beforeMount () {
        this.initForm()
    },
    mounted () {
        this.getNumeroVenta()
        setInterval(this.updateTime, 1000)
    },
    data () {
        return {
            API: config.API,
            puntosAplicados: 0,
            descuentoPuntos: {},
            descuentoGeneral: 0,
            tieneIgv: false,
            venta: {},
            carrito: {
                fields: [
                    { key: 'Producto' }, 
                    { key: 'ProductoModelo', label: 'Modelo' }, 
                    { key: 'ProductoMarca', label: 'Marca' },
                    { key: 'ProductoTalla', label: 'Talla' },
                    { key: 'Genero' },
                    { key: 'cantidad' },
                    { key: 'precio', label: 'Precio' },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success' }, 
                    { key: 'descuento', label: 'Descuento'},
                    { key: 'total', label: 'Total', variant: 'danger' },
                    { key: 'acciones', label: ''}
                ]
            }
        }
    },
    methods: {
        ...mapActions({
            aplicarDescuento: 'aplicarDescuentoGeneral',
        }),
        focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
        initForm () {
            this.venta = {
                Serie: '',
                Numero: '',
                cliente: {},
                tipoVenta: {
                    IdTipoDoc: 2,
                    TipoDoc: 'BOLETA',
                    LimitItems: 8
                },
                almacen: {
                    IdAlmacen: 1,
                    Almacen: 'VENTA'
                },
                puntoVenta: {
                    IdDocVentaPuntoVenta: 1,
                    PuntoVenta: 'CAJA1'
                },
                vendedor: {},
                FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss")
            }
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
        setAlmacen (obj) {
            this.venta.almacen = obj
        },
        setPuntoVenta (obj) {
            this.venta.puntoVenta = obj
            this.getNumeroVenta()
        },
        setVendedor (obj) {
            this.venta.vendedor = obj
        },
        setCliente (obj) {
            this.venta.cliente = obj
        },
        onSetProducto (obj) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
            return obj
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
        }
    },
    components: {
        ProductosCarrito
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
