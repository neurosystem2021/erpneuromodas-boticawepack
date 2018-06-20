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
                        
                    </b-col>
                    <b-col md="2" >
                        <b-form-group class="text-center">
                            <div class="big-text">
                                0001 - <b>464</b>
                            </div>
                            2018-06-19 hh:mm:ss
                        </b-form-group>
                        <b-form-group>
                            <label>Vendedor:</label>
                            <autocomplete
                                ref="inputVendedor"
                                :url="API + 'usuarios'"
                                anchor="NombreUsuario"
                                label="writer"
                                placeholder="Vendedor"
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
                            ref="carrito" 
                            :showButton="false" 
                            :fields="carrito.fields"
                            :onSetProducto="onSetProducto"></productos-carrito>
                    </b-col>
                </b-row>
            </b-container>
        </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'
import ProductosCarrito from '@/components/ProductosCarrito'

export default {
    beforeMount () {
        this.initForm()
    },
    data () {
        return {
            API: config.API,
            venta: {},
            carrito: {
                fields: [
                    { key: 'Producto' }, 
                    { key: 'ProductoModelo', label: 'Modelo' }, 
                    { key: 'ProductoMarca', label: 'Marca' },
                    { key: 'ProductoTalla', label: 'Talla' },
                    { key: 'Genero' },
                    { key: 'cantidad' },
                    { key: 'PrecioContado', label: 'Precio' },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success' }, 
                    { key: 'descuento', label: 'Descuento'},
                    { key: 'total', label: 'Total', variant: 'danger' },
                    { key: 'acciones', label: ''}
                ]
            }
        }
    },
    methods: {
        focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
        },
        initForm () {
            this.venta = {
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
                vendedor: {}
            }
        },
        setTipoVenta (obj) {
            this.venta.tipoVenta = obj
        },
        setAlmacen (obj) {
            this.venta.almacen = obj
        },
        setPuntoVenta (obj) {
            this.venta.puntoVenta = obj
        },
        setVendedor (obj) {
            this.venta.vendedor = obj
        },
        setCliente (obj) {
            this.venta.cliente = obj
        },
        onSetProducto (obj) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado
            return obj
        }
    },
    components: {
        ProductosCarrito
    }
}
</script>
<style>

.big-text {
    font-size: 30px;
}

</style>
