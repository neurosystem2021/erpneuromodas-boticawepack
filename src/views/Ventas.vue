<template>
    <div>
        <Menu v-if="false"></Menu>

        <br />
        <b-container fluid class="bv-example-row">
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
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col md="9">
                                <b-row>
                                    <b-col md="4" class="docInfo mt-2">
                                        <b-input-group prepend="Placa">
                                            <input v-model="venta.Placa"
                                                    type="text"
                                                    ref="inputPlaca"
                                                    style="text-transform:uppercase;"
                                                    class="form-control"
                                                    placeholder="Placa" />
                                         </b-input-group>
                                        <div v-if="venta.cliente.Cliente && ultimaVentaCliente!='' && venta.cliente.DniRuc != '00000000'" class="text-center mt-1">
                                            <b-row>
                                                <b-col md="3">
                                                    <b-img style="width:50px" src="static/last-buy.png"></b-img>
                                                </b-col>
                                                <b-col md="8" style="align-items: center; justify-content: end; display: flex;">
                                                    <label style="font-weight:bold; font-size:0.95rem">
                                                        {{ultimaVentaCliente}}
                                                    </label>
                                                </b-col>
                                            </b-row>
                                        </div>
                                    </b-col>
                                    
                                    <b-col md="8">
                                        <b-form-group>
                                            <b-input-group class="mt-2">            
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
                                            <b-form-select v-if="venta.cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-3">
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
                                <b-form-group class="text-center">
                                    <div class="big-text">
                                        {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
                                    </div>
                                    {{ venta.FechaDoc }}
                                </b-form-group>
                            </b-col>
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
                                    :bloquearInput="bloquearInput" 
                                    :fields="carrito.fields"
                                    :idAlmacen="venta.almacen.IdAlmacen"
                                    :onSetProducto="onSetProducto">

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
                                    <template slot="infoLlevar">
                                        <b-row style="justify-content: center;">
                                            <b-col>
                                                <b-button type="button" :disabled="inProgress" variant="primary" size="md" @click="guardarProforma">Crear Proforma</b-button>
                                            </b-col>
                                        </b-row>
                                        <b-row style="justify-content: center; margin-top:10px">
                                            <b-col>
                                                <b-button type="submit" :disabled="inProgress" variant="success" size="lg">Efectuar Venta</b-button>
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
                            <b-button v-b-toggle.collapse-1 variant="primary">ACCESOS DIRECTOS</b-button>
                            <b-collapse id="collapse-1" class="mt-2">
                                <b-col md="12" class="mt-1">
                                    <pago-deuda></pago-deuda>
                                </b-col>
                                <b-col md="12" class="mt-1">
                                    <pre-orden-lista :idAlmacen="venta.almacen.IdAlmacen" @onSelectPreOrden="setCliente"></pre-orden-lista>
                                </b-col>
                                <b-col md="12" class="mt-1">
                                    <proformas></proformas>
                                </b-col>
                                <b-col md="12" class="mt-1">
                                    <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
                                </b-col>        
                            </b-collapse>
                        </b-col>
                    </b-row>
                    <br>
                    <b-row  style="height:90vh; overflow-y: scroll; background: #E6E6E6; place-content: flex-start;">     
                        <b-col md="12" id="productoPunto" class="mx-0 mt-2 text-center bg-white p-0" style="border: 2px solid #E0E0E0; border-radius: 10px; cursor:pointer; height: fit-content; " v-for="(producto , index2) in productosData" :key="index2" @click="setProducto(producto)">
                            <b-row >
                                <b-col md="5">
                                    <label class="font-weight-bold" style="font-size:0.8rem; cursor:pointer">{{producto.Producto}}</label>
                                </b-col>
                                <b-col md="7" style="align-items: center; justify-content: center; display: flex;">
                                    <b-badge variant="dark" style="font-size: 0.8rem;">S/ {{  parseFloat(producto.PrecioContado).toFixed(2) }}</b-badge>
                                </b-col>
                            </b-row>
                            <b-row class="text-center" style="margin-top:2px; border-top: 1px solid #E6E6E6;">
                                <b-col>
                                    <b-img style="border-radius: 10px; width:80%; object-fit: contain; margin-bottom:5px; width:180px" fluid :src="'/resources/images/productos/' + producto.Imagen" @error="setAltImg" alt="" ></b-img>
                                </b-col>               
                            </b-row>
                        </b-col>
                    </b-row>
                </b-col>
            </b-row>
            
            <br class="d-none d-lg-block d-xl-block"/><br class="d-none d-lg-block d-xl-block"/>

        </b-container>
    </div>
</template>
<script>
import { mapActions, mapState } from 'vuex'
import config from '@/store/config.js'
import ProductosCarritoNuevo from '@/components/ProductosCarritoNuevo'
import ClientesForm from '@/components/ClientesForm'
import ConfigVenta from '@/components/ConfigVenta'
import PagoDeuda from '@/components/PagoDeuda'
import PreOrdenLista from '@/components/PreOrdenLista.vue'
import Proformas from '@/components/ProformaLista.vue'


export default {
    beforeMount () {
        this.initForm()
        this.cargarProductos()
    },
    mounted () {
        //this.getNumeroVenta()
        this.getVendedor()
        //setInterval(this.updateTime, 1000)
        this.loadClienteVarios()
    },
    data () {
        return {
            dni: '',
            isNota: false,
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
            ultimaVentaCliente:'',
            productosData:[],
            navDisabled:false,
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
                    //{ key: 'selec',label:'Selec.',class:'fontsize thsize'},
                    { key: 'stock', label: 'Stock', variant: 'info', class:'fontsize thsize' },
                    //{ key: 'CodigoBarra', label: 'Código' }, 
                    { key: 'Producto', class:'productosize thsize'  }, 
                    //{ key: 'ProductoCategoria', label: 'Categoría' },
                    //{ key: 'ProductoMarca', label: 'Marca' },
                    //{ key: 'ProductoMedicion', label: 'Medicion' },
                    { key: 'cantidad',class:'thsize' },
                    { key: 'precio', label: 'Precio',class:'thsize'  },
                    { key: 'subtotal', label: 'SubTotal', variant: 'success', class:'text-center thsize' }, 
                    { key: 'descuento', label: 'Descuento',  class:'thsize'},
                    { key: 'total', label: 'Total', variant: 'danger',class:'fontsize thsize' },
                    { key: 'acciones', label: '', class:'thsize'}
                ]
            },
            TieneIgvComprobante:false
        }
    },
    watch: {
        totalCart (newVal) {
             this.venta.pago.efectivo.monto = parseFloat(newVal - (parseFloat(this.venta.pago.visa.monto) + parseFloat(this.venta.pago.mastercard.monto) + parseFloat(this.venta.pago.transferencias.monto))).toFixed(2);
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
            obtenerStok: 'obtenerStock',
            add:'agregarProducto'
        }),
        setAltImg(event) { 
            event.target.src = "static/no-photo.png" 
        },
        cargarProductos(){
            this.axios.get(this.API+'productos/lineal')
            .then((response) => {
                this.productosData = response.data
            })
            .catch((error) => {
                console.log(error)
                this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
        },
        ocultarNav(){
            this.navDisabled=!this.navDisabled
            console.log(this.navDisabled);
            if(this.navDisabled){
                window.parent.document.getElementById('NavegadorParent').style.display = 'none'
            }else{
                window.parent.document.getElementById('NavegadorParent').style.display = 'block'
            }
        },
        metodoPagoCambio(opc){
            switch (opc) {
                case 1:
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.transferencias.monto = 0
                    this.venta.pago.efectivo.monto = this.totalCart;
                    break;
                case 2:
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.transferencias.monto = 0
                    this.venta.pago.visa.monto =this.totalCart;
                    break;
                case 3:
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.transferencias.monto = 0
                    this.venta.pago.mastercard.monto = this.totalCart;
                    break;
                case 4:
                    this.venta.pago.efectivo.monto = 0
                    this.venta.pago.visa.monto = 0
                    this.venta.pago.mastercard.monto = 0
                    this.venta.pago.transferencias.monto = this.totalCart;
                    break;
            
                default:
                    break;
            }
        },
        setProducto (obj) {
        obj = Object.assign({}, obj);
        //obj.FLETE = 0
        obj.precio = 0
        obj.cantidad = 0
        //obj.IdLote = 0
        //obj.TieneIgv = 0
        obj.descuento = 0
        obj.nuevoPrecioContado = 0
        obj = this.onSetProducto(obj)
        let existe = this.productos.find(p=>p.IdProducto == obj.IdProducto)
        if(existe==undefined){
                this.$toasted.info('Producto agregado')
        }else{
                this.$toasted.error('El producto ya esta agregado')
        }
        this.add([obj, () => {
            this.onAddProducto(obj)
        }])

        // Vue.set(this.productos, obj.IdProducto, obj)
        },
        loadClienteVarios () {
            this.axios.get(this.API + 'clientes/00000000')
                .then(res => {
                if (res.data.IdCliente) {
                    this.setCliente(res.data)
                }
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
            this.descuentoGeneral = 0
            
            this.venta = {
                EsCredito: 0,
                Serie: '',
                Placa: '',
                Numero: '',
                cliente: {},
                tipoVenta: {
                    IdTipoDoc: 7,
                    TipoDoc: 'BOLETA ELEC.',
                    LimitItems: 0,
                    CodSunat: '03',

                },
                almacen: {
                    IdAlmacen: 1
                },
                puntoVenta: {
                    IdDocVentaPuntoVenta: 1,
                    PuntoVenta: 'CAJA1'
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
                CampoDireccion: 'Direccion',
                DescripcionEmbarcacion: '',
                NroComprobanteModifica: '',
                CodSunatModifica: '',
                NotaIdMotivo: '',
                NotaDescMotivo: ''
            }

            this.isNota = false
            this.bloquearInput = false 
            this.tieneIgv = false
            //this.loadFirstAlmacen()
        },
        setTipoVenta (obj) {
            this.isNota = false
            this.bloquearInput=false
            this.venta.NroComprobanteModifica = ''
            this.venta.CodSunatModifica = ''
            this.venta.NotaIdMotivo = ''
            this.venta.NotaDescMotivo = ''
            this.TieneIgvComprobante = false

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
        },
        setDocRef (obj) {
            this.venta.CodSunatModifica = obj.CodSunat
            this.venta.NroComprobanteModifica = obj.NroComprobante
            this.TieneIgvComprobante = (obj.TieneIgv==1?true:false)
            if(this.TieneIgvComprobante != this.tieneIgv){
                if(this.TieneIgvComprobante){
                    this.$toasted.show('El comprobante de referencia tiene IGV, seleccione NOTA DE CREDITO/IGV ELEC.',{duration:3000})
                }else{
                    this.$toasted.show('El comprobante de referencia no tiene IGV, seleccione NOTA DE CREDITO ELEC.',{duration:3000})
                }
            }

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
                    this.venta.Placa = response.data[0].Placa
                    this.venta.DescripcionEmbarcacion = response.data[0].DescripcionEmbarcacion
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
                    this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen })              
                })
        },
        setMotivo(ev){
            if(ev == '01' || ev == '02' || ev == '06' || ev == null){
                this.bloquearInput=true;
            }else{
                this.bloquearInput=false;
            }
        },
        setAlmacen (obj) {
            this.venta.almacen = obj
            this.$refs.inputAlmacen.setValue(obj.Almacen)
            this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen })
        },
        setPuntoVenta (obj) {
            this.venta.puntoVenta = obj
            this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta)
            this.getNumeroVenta()
        },
        loadAlmacen(id) {
            this.axios.get(this.API + 'almacenes/id/' + id)
            .then(res => {
                this.setAlmacen(res.data)
            })
        },
        loadPuntoVenta (id) {
            this.axios.get(this.API + 'puntoventa/id/' + id)
            .then(res => {
                this.setPuntoVenta(res.data)
            })
        },
        getVendedor () {
            this.$refs.inputAlmacen.$refs.input.disabled = true
            this.$refs.inputPuntoVenta.$refs.input.disabled = true
            this.$refs.inputVendedor.$refs.input.disabled = true
            let usuario = sessionStorage.getItem('User')
            this.axios.get(this.API + 'usuarios/' + usuario)
            .then((response) => {
                this.setVendedor(response.data)
                this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                // this.$refs.inputVendedor.$refs.input.disabled = true
                this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
                this.loadAlmacen(response.data.IdAlmacen)
                this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta)
                // bloquear ambos inputs

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
                this.venta.CampoDireccion = this.venta.cliente.Direccion
                this.ultimaVentaCliente = ''
                this.getUltimaVentaCliente(this.venta.cliente.IdCliente)
            } else {
                this.ultimaVentaCliente = ''
                this.axios.get(this.API + 'cliente', {
                    params: params
                })
                .then((response) => {
                    if (response.data) {
                        this.$refs.inputCliente.setValue(response.data.Cliente)
                        this.venta.cliente = response.data
                        this.venta.CampoDireccion = this.venta.cliente.Direccion
                        this.getUltimaVentaCliente(this.venta.cliente.IdCliente)
                    } else {
                        this.$toasted.show('No se encontro al cliente')
                    }
                })
                .catch((error) => {
                    console.log(error)
                })
            }
        },
        getUltimaVentaCliente(idCliente){
            let params = {
                IdCliente:idCliente
            };
            this.axios.get(this.API + 'cliente/ultima/venta', {
                params: params
            })
            .then((response) => {
                if (response.data) {
                    this.ultimaVentaCliente = response.data.FechaDoc
                }else{
                    this.ultimaVentaCliente = ''
                }
            })
            .catch((error) => {
                console.log(error)
            })
        },
        onSetProducto (obj) {
            obj.cantidad = 1
            obj.precio = obj.PrecioContado || 0
            return obj
        },
        onAddProducto (obj) {
            this.actualizarDescuento()
            this.venta.pago.visa.monto = 0
            this.venta.pago.mastercard.monto = 0
            this.venta.pago.transferencias.monto = 0
            this.obtenerStok({
                IdAlmacen: this.venta.almacen.IdAlmacen
            })
        },
        actualizarDescuento () {
            this.venta.pago.visa.monto = 0
            this.venta.pago.mastercard.monto = 0
            this.venta.pago.transferencias.monto = 0
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
            this.loadClienteVarios()
            this.$refs.inputTipoVenta.setValue('BOLETA ELEC.')
           // this.$refs.inputAlmacen.setValue('VENTA')
            //this.$refs.inputPuntoVenta.setValue('CAJA1')
            this.$refs.inputCliente.setValue('')
            // this.$refs.inputVendedor.setValue('')
            this.$refs.carrito.pagocon = ''
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
                console.log(response)
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
            totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto))?0:parseFloat(this.venta.pago.efectivo.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto))?0:parseFloat(this.venta.pago.visa.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto))?0:parseFloat(this.venta.pago.mastercard.monto)
            totalPagos += isNaN(parseFloat(this.venta.pago.transferencias.monto))?0:parseFloat(this.venta.pago.transferencias.monto)
            if((isNaN(parseFloat(this.venta.pago.efectivo.monto))?0:parseFloat(this.venta.pago.efectivo.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago efectivo no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.visa.monto))?0:parseFloat(this.venta.pago.visa.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago tarjeta no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.mastercard.monto))?0:parseFloat(this.venta.pago.mastercard.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago yape/plin no puede ser negativo')
                this.inProgress = false            
                return  
            }
            if((isNaN(parseFloat(this.venta.pago.transferencias.monto))?0:parseFloat(this.venta.pago.transferencias.monto)).toFixed(2)<0){
                this.$toasted.show('El metodo de pago yape/plin no puede ser negativo')
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
            
            if(!this.productos.length) {
                this.$toasted.show('No hay productos para vender')
                this.inProgress = false            
                return          
            }

            if(this.venta.tipoVenta.CodSunat == '07' || this.venta.tipoVenta.CodSunat == '08') {
                if(this.TieneIgvComprobante != this.tieneIgv){
                    if(this.TieneIgvComprobante){
                        this.$toasted.show('El comprobante de referencia tiene IGV, seleccione NOTA DE CREDITO/IGV ELEC.',{duration:3000})
                    }else{
                        this.$toasted.show('El comprobante de referencia no tiene IGV, seleccione NOTA DE CREDITO ELEC.',{duration:3000})
                    }
                    
                    this.inProgress = false
                    return 
                }

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

            // NO PERMITE CANTIDAD Y PRECIOS EN CERO
            let sinStock = 0
            let totalCero = 0 
            this.productos.forEach(pro => {
                if(pro.stock <= 0) {
                    sinStock += 1
                }

                if(parseFloat(pro.total) <= 0) {
                    totalCero += 1
                }
            });

            if(totalCero >= 1) {
                this.$toasted.error('No se puede enviar un producto en S/. 0.00');
                this.inProgress = false
                return;
            }

            if(parseFloat(totalPagos) >= 600 && this.venta.tipoVenta.CodSunat == '03' && this.venta.cliente.DniRuc == '00000000' ){
                this.$toasted.error('Venta con boleta mayor a 600 soles, no puede ser emitida con cliente varios');
                this.inProgress = false
                return;
            }

            if(parseFloat(totalPagos) >= 600 && this.venta.tipoVenta.CodSunat == '03' && (''+this.venta.Placa).trim()=='' ){
                this.$toasted.error('Venta con boleta mayor a 600 soles, por favor ingrese la placa del vehiculo');
                this.inProgress = false
                this.$refs.inputPlaca.focus()
                return;
            }

            if(this.venta.tipoVenta.CodSunat == '01' && (''+this.venta.Placa).trim()=='' ){
                this.$toasted.error('Venta con factura, por favor ingrese la placa del vehiculo');
                this.inProgress = false
                this.$refs.inputPlaca.focus()
                return;
            }
            
            this.venta.productos = this.productos
            this.venta.puntosAplicados = this.puntosAplicados || 0
            this.venta.totalCart = this.totalCart || 0
            this.venta.PagoCon = this.$refs.carrito.pagocon
            this.venta.Placa = (''+this.venta.Placa).toUpperCase()
            this.axios.post(this.API + 'ventas', this.venta)
            .then((response) => {
                console.log(response)
                this.ultimaVentaCliente=''
                this.$toasted.success('Venta realizada con exito')
                this.ultimaVenta = response.data.insertId
                this.limpiarDespuesVenta()

                window.open('/imprimir/index.php?IdDocVenta=' + response.data.insertId,'_blank');
                this.$refs.carrito.pagocon = 0
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
        ProductosCarritoNuevo,
        ClientesForm,
        ConfigVenta,
        Proformas,
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
#modalConfigVenta .modal-dialog{
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
    td.fontsize{
      font-size: 0.8rem !important;
      font-weight: bold;
      text-align: center; 
      vertical-align: middle !important;
    }
    td.stocksize{
      text-align: center !important; 
      vertical-align: middle !important;
    }
    th.stocksize{
      font-size: 0.8rem !important;
      text-align: center;
    }
    th.thsize{
      font-size: 0.85rem !important;
      text-align: center;
    }
    td.thsize{
      vertical-align: middle !important;
    }
    td.productosize{
      font-size: 1.5rem !important;
      font-weight: bold;
      text-align: center; 
      vertical-align: middle !important;
    }
    .popover-body{
      background-color: #C1C1C1;
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
</style>