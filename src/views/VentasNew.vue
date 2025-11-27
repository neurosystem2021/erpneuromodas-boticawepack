<template>
    <b-container fluid >
        <b-row>
            <b-col cols="12">
                <div>
                    ************
                            <!-- <div class="float-right">
                                <b-button-group size="sm">
                                    <pago-deuda></pago-deuda>
                                </b-button-group>
                                <b-button-group size="sm">
                                    <proformas :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectProforma="setCliente"></proformas>
                                </b-button-group>
                                <b-button-group size="sm">
                                    <pre-orden-lista :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectPreOrden="setCliente"></pre-orden-lista>
                                </b-button-group>
                                <b-button-group size="sm">
                                    <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
                                </b-button-group>

                            </div> -->
                        </div>
            </b-col>
            <b-col cols="12">
                <b-tabs pills card v-model="index">
                    <b-tab lazy v-for="(tab,i) in ventas" :key="i" :title="`Ventana ${i+1}`">
                        <b-row>
                            <b-col cols="12" md="6">
                                <b-row>
                                    <b-col cols="12" md="4">
                                        <b-form-group>
                                            <label>Tipo de Venta:</label>                                             <autocomplete
                                                required="required"
                                                :ref="`inputTipoVenta${i}`"
                                                :url="API + 'ventas/tipos'"
                                                anchor="TipoDoc"
                                                initValue="BOLETA ELEC."
                                                label="writer"
                                                placeholder=""
                                                :on-select="setTipoVenta"
                                                :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${i}`)"
                                                :classes="{input: 'form-control'}">
                                            </autocomplete>
                                        </b-form-group>
                                        <b-form-group v-if="isNota"> *********
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
                                    <b-col md="4">
                                        <b-form-group>
                                            <label>Almacen:</label>
                                            <input type="text" disabled class="form-control" v-model="tab.almacen.Almacen">

                                        </b-form-group>
                                        <b-form-group v-if="isNota"> *********
                                            <label>Motivo :</label>
                                            <b-form-select v-model="tab.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="4">
                                        <b-form-group>
                                            <label>Punto de Venta:</label>
                                            <input type="text" disabled class="form-control" v-model="tab.puntoVenta.PuntoVenta">
                                        </b-form-group>
                                    </b-col>
                                    <b-col md="12">
                                        <b-form-group>
                                            <label>Cliente:</label>
                                            <b-input-group>
                                            <clientes-form txtButton="Editar" v-if="tab.cliente.IdCliente && tab.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="venta.cliente"></clientes-form>

                                                <autocomplete
                                                    required="required"
                                                    :ref="`inputCliente${i}`"
                                                    :url="API + 'clientes'"
                                                    anchor="ClienteDniRuc"
                                                    label="writer"
                                                    placeholder=""
                                                    :onInput="(val) => newDniRuc = val"
                                                    :on-select="setCliente"
                                                    :onFocus="focusAutocomplete.bind(null, `inputCliente${i}`)"
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
                                            <b-form-select v-if="tab.cliente.IdCliente" v-model="tab.CampoDireccion" class="mb-3">
                                                <option :value="tab.cliente.Direccion">{{ tab.cliente.Direccion }}</option>
                                                <option :value="tab.cliente.Direccion2">{{ tab.cliente.Direccion2 }}</option>
                                                <option :value="tab.cliente.Direccion3">{{ tab.cliente.Direccion3 }}</option>
                                            </b-form-select>
                                        </b-form-group>
                                    </b-col>
                                
                                </b-row>
                            </b-col>
                            <b-col cols="12" md="3"> 
                                **********
                                <div v-if="tab.cliente.Cliente && tab.cliente.EsVIP">
                                    <b-form-group class="text-center" v-if="tab.cliente.Cliente">
                                        <div class="puntos">
                                            {{ (tab.cliente.Puntos || 0) | redondeoMenor }}
                                        </div>
                                        Puntos del cliente
                                    </b-form-group>
                                    <b-input-group prepend="Puntos" :append="descuentoPuntos.descuento | redondear" v-if="venta.cliente.Puntos && descuentoGeneral == 0">
                                        <b-form-input
                                            v-model="puntosAplicados"
                                            min="0"
                                            :max="tab.cliente.Puntos"
                                            @input="aplicarPuntos"
                                            type="number"
                                        ></b-form-input>
                                    </b-input-group>
                                    <b-form-text v-if="descuentoPuntos.ValorPunto">
                                    1 Punto equivale a: S/ {{ descuentoPuntos.ValorPunto | redondear }}
                                    </b-form-text>
                                </div>
                            </b-col>
                            <b-col md="3" >
                                <b-form-group class="text-center">
                                    <div class="big-text"> 
                                        {{ tab.Serie }} - <b>{{ tab.Numero }}</b>
                                    </div>
                                    {{ tab.FechaDoc }}
                                </b-form-group>
                                <b-form-group>
                                    <label>Vendedor:</label>
                                    <input type="text" disabled class="form-control" v-model="tab.vendedor.NombreUsuario">
                                </b-form-group>
                            </b-col>
                        </b-row>
                        <b-row>
                            <b-col>
                                <productos-carrito
                                    :key="'carrito-component-' + i"
                                    :ref="`carrito${i}`"
                                    @onInputCantidad="actualizarDescuento"
                                    @onAddProducto="onAddProducto"
                                    @onRemoveProducto="actualizarDescuento"
                                    :igvTotal="tieneIgv"
                                    :setPrecio="true"
                                    :isVentas="true"
                                    :showButton="false"
                                    :bloquearInput="bloquearInput"
                                    :fields="carrito.fields"
                                    :idAlmacen="tab.almacen.IdAlmacen"
                                    :onSetProducto="onSetProducto"
                                    :productos="tab.productos"
                                    v-if="productoFlag"
                                >
                                </productos-carrito>
                            </b-col>
                        </b-row>
                    </b-tab>
                    
                </b-tabs>
            </b-col>
            <b-col cols="12">
                <button @click="anadirVenta()">  Añadir Vntana</button>
            </b-col>
       
        
        <!--
            <div>
                <Menu v-if="false"></Menu>

                <br />
                <b-container class="bv-example-row" fluid>
                    
                    <br /><br />
                    <b-form @submit="guardarVenta" @keydown.enter="evt => evt.preventDefault()">
                        <b-row>
                            <b-col>
                                <productos-carrito
                                    @onInputCantidad="actualizarDescuento"
                                    @onAddProducto="onAddProducto"
                                    @onRemoveProducto="actualizarDescuento"
                                    :igvTotal="tieneIgv"
                                    :setPrecio="true"
                                    ref="carrito"
                                    :isVentas="true"
                                    :showButton="false"
                                    :bloquearInput="bloquearInput"
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
                                                                :disabled="bloquearInput?true:false"
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
                                                            :disabled="bloquearInput?true:false"
                                                            :value="1"
                                                            :unchecked-value="0">
                                                            Venta a credito
                                                        </b-form-checkbox>
                                                        <b-form-input
                                                            v-if="venta.EsCredito"
                                                            :disabled="bloquearInput?true:false"
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
                                                        <b-button variant="info" size="sm" @click=cambiarMetodoPago(1)>
                                                        <i class="fa fa-check" aria-hidden="true"></i>
                                                        </b-button>
                                                    </td>
                                                    <td>
                                                        <b-form-input
                                                            v-model="venta.pago.efectivo.descripcion"
                                                            :disabled="bloquearInput?true:false"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            v-model="venta.pago.efectivo.monto"
                                                            :disabled="bloquearInput?true:false"
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
                                                    <td>
                                                    <b-button variant="info" size="sm" @click=cambiarMetodoPago(2)>
                                                        <i class="fa fa-check" aria-hidden="true"></i>
                                                        </b-button>
                                                    </td>
                                                    <td>
                                                        <b-form-input
                                                            v-model="venta.pago.visa.descripcion"
                                                            :disabled="bloquearInput?true:false"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            class="form-control"
                                                            v-model="venta.pago.visa.monto"
                                                            :disabled="bloquearInput?true:false"
                                                            min="0"
                                                            step="0.01"
                                                            type="number"
                                                            placeholder="S/."
                                                            @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - $event.target.value)"
                                                        />
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td>Yape/ Plin</td>
                                                    <td>
                                                    <b-button variant="info" size="sm" @click=cambiarMetodoPago(3)>
                                                        <i class="fa fa-check" aria-hidden="true"></i>
                                                        </b-button>
                                                    </td>
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
                                                    <td>Transferencia</td>
                                                    <td>
                                                    <b-button variant="info" size="sm" @click=cambiarMetodoPago(4)>
                                                        <i class="fa fa-check" aria-hidden="true"></i>
                                                        </b-button>
                                                    </td>
                                                    <td>
                                                        <b-form-input
                                                            v-model="venta.pago.otro.descripcion"
                                                            :disabled="bloquearInput?true:false"
                                                            type="text"
                                                            placeholder="Descripcion"
                                                        ></b-form-input>
                                                    </td>
                                                    <td>
                                                        <input
                                                            class="form-control"
                                                            v-model="venta.pago.otro.monto"
                                                            :disabled="bloquearInput?true:false"
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
                                <b-button class="btnAction" type="button" :disabled="inProgress" variant="primary" size="lg" @click="guardarProforma">Crear Proforma</b-button>
                                <b-button class="btnAction" type="submit" :disabled="inProgress" variant="success" size="lg">Efectuar Venta</b-button>
                                <b-button v-if="ultimaVenta" target="_blank" variant="info" size="lg" :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                                <br />
                                <br />
                            </b-col>
                        </b-row>
                    </b-form>
                </b-container>
            </div>
            -->
    </b-row>
    </b-container>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarritoNuevo";
import ClientesForm from "@/components/ClientesForm";
import ConfigVenta from "@/components/ConfigVenta";
import PagoDeuda from "@/components/PagoDeuda";
import PreOrdenLista from "@/components/PreOrdenLista.vue";
import Proformas from "@/components/ProformaLista.vue";

export default {
  beforeMount() {
    this.loadClienteVarios();
  },
  mounted() {
    this.initForm();
    // this.getNumeroVenta()
    this.getVendedor();
    // this.initTabs();
    //setInterval(this.updateTime, 1000)
    /* this.loadFirstAlmacen()
        this.loadFirstPuntoVenta() */
  },
  data() {
    return {
      productoFlag: true,
      ventas: [],
      preVenta: {},
      index: 0,
      dni: "",
      isNota: false,
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
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "CodigoBarra", label: "Código" },
          { key: "Producto" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoFormaFarmaceutica", label: "Lab." },
          { key: "ProductoMedicion", label: "Med." },
          { key: "PrecioPorMayor", label: "Precio x Mayor" },
          { key: "StockPorMayor", label: "Stock x Mayor" },
          { key: "cantidad" },
          { key: "precio", label: "Precio" },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center"
          },
          { key: "descuento", label: "Descuento" },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center"
          },
          { key: "acciones", label: "" }
        ]
      },
      idPerfil: sessionStorage.getItem("IdPerfil")
    };
  },
  watch: {
    totalCart(newVal) {
      this.ventas[this.index].pago.efectivo.monto =
        newVal -
        (parseFloat(this.ventas[this.index].pago.visa.monto) +
          parseFloat(this.ventas[this.index].pago.mastercard.monto) +
          parseFloat(this.ventas[this.index].pago.otro.monto));
    }
  },
  computed: {
    ...mapState({
      //   productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    })
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      add: "agregarProducto"
    }),
    anadirNuevaVenta() {},
    initTabs() {
      let venta = {
        EsCredito: 0,
        Serie: "",
        Numero: "",
        cliente: {},
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA ELEC.",
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
          mastercard: { IdMetodoPago: 3, monto: 0 },
          otro: { IdMetodoPago: 4, monto: 0 }
        },
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: ""
      };
      this.ventas.push(venta);
      //   this.isNota = false;
      //   this.bloquearInput = false;
      //   this.descuentoGeneral = 0;
      //   this.puntosAplicados = 0;
    },
    cambiarMetodoPago(type = null) {
      this.ventas[this.index].pago.efectivo.monto = 0;
      this.ventas[this.index].pago.visa.monto = 0;
      this.ventas[this.index].pago.mastercard.monto = 0;
      this.ventas[this.index].pago.otro.monto = 0;

      if (type == 1) {
        this.ventas[this.index].pago.efectivo.monto = parseFloat(
          this.totalCart
        );
      }
      if (type == 2) {
        this.ventas[this.index].pago.visa.monto = parseFloat(this.totalCart);
      }
      if (type == 3) {
        this.ventas[this.index].pago.mastercard.monto = parseFloat(
          this.totalCart
        );
      }
      if (type == 4) {
        this.ventas[this.index].pago.otro.monto = parseFloat(this.totalCart);
      }
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
        console.log("res", res);
        // this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.ventas[this.index].almacen = res.data;
      });
    },
    loadFirstPuntoVenta() {
      this.axios.get(this.API + "puntoventa/primero").then(res => {
        // this.$refs.inputPuntoVenta.setValue(res.data.PuntoVenta);
        this.setPuntoVenta(res.data);
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
      console.log(ref);
      this.$refs[ref][0].handleDoubleClick();
      // this.$refs[ref].type = ""
      //   this.$refs[ref].handleDoubleClick();
    },
    anadirVenta() {
      this.ventas.push({ ...this.preVenta, productos: [] });
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.getNumeroVenta();
      });
    },
    initForm() {
      let venta = {
        EsCredito: 0,
        Serie: "",
        Numero: "",
        cliente: {},
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: "BOLETA ELEC.",
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
          mastercard: { IdMetodoPago: 3, monto: 0 },
          otro: { IdMetodoPago: 4, monto: 0 }
        },
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: ""
      };
      this.isNota = false;
      this.bloquearInput = false;
      this.descuentoGeneral = 0;
      this.puntosAplicados = 0;
      this.ventas.push({ ...venta, productos: [] });
      this.preVenta = { ...venta };
    },
    setTipoVenta(obj) {
      this.isNota = false;
      this.bloquearInput = false;
      this.ventas[this.index].NroComprobanteModifica = "";
      this.ventas[this.index].CodSunatModifica = "";
      this.ventas[this.index].NotaIdMotivo = "";
      this.ventas[this.index].NotaDescMotivo = "";
      // let
      //   this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");

      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota = true;
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }

      this.ventas[this.index].tipoVenta = obj;
      this.getNumeroVenta();
    },
    setDocRef(obj) {
      this.ventas[this.index].CodSunatModifica = obj.CodSunat;
      this.ventas[this.index].NroComprobanteModifica = obj.NroComprobante;

      if (
        this.ventas[this.index].tipoVenta.CodSunat == "07" ||
        this.ventas[this.index].tipoVenta.CodSunat == "08"
      ) {
        this.ventas[this.index].Serie = this.ventas[this.index].Serie.replace(
          /^./,
          obj.NroComprobante[0]
        );
      }

      this.axios
        .get(this.API + "ventas/solonumero", {
          params: {
            serie: this.ventas[this.index].Serie,
            idTipoDoc: this.ventas[this.index].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.index].Numero = response.data.numero;
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
            IdDocVentaPuntoVenta: this.ventas[this.index].puntoVenta
              .IdDocVentaPuntoVenta
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
                // this.$refs.inputCliente.setValue(response.data.Cliente);
                this.ventas[this.index].cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiarCart();
          response.data.map(item => {
            this.add([item, () => {}, false, true]);
          });
          this.ventas[this.index].NotaIdMotivo = null;
          this.bloquearInput = true;
          this.obtenerStok({
            IdAlmacen: this.ventas[this.index].almacen.IdAlmacen
          });
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
      this.ventas[this.index].almacen = obj;
      this.preVenta.almacen = obj;
      //   this.$refs.inputAlmacen.setValue(obj.Almacen);
      this.obtenerStok({
        IdAlmacen: this.ventas[this.index].almacen.IdAlmacen
      });
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
        this.getNumeroVenta();
      });
    },
    setPuntoVenta(obj) {
      this.ventas[this.index].puntoVenta = obj;
      this.preVenta.puntoVenta = obj;
      //   this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    async getVendedor() {
      let usuario = sessionStorage.getItem("User");
      await this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          //   this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          //   this.$refs.inputVendedor.$refs.input.disabled = true;
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs
          //   this.$refs.inputAlmacen.$refs.input.disabled = true;
          //   this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          //this.getNumeroVenta()
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      this.$forceUpdate();
    },
    setVendedor(obj) {
      this.preVenta.vendedor = obj;
      this.ventas[this.index].vendedor = obj;
      console.log(this.ventas[this.index]);
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
        let ref = `inputCliente${this.index}`;
        let component = this.$refs[ref][0];
        component.setValue(obj.Cliente);
        this.ventas[this.index].cliente = obj;
        this.ventas[this.index].CampoDireccion = this.ventas[
          this.index
        ].cliente.Direccion;
      } else {
        this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              // this.$refs.inputCliente.setValue(response.data.Cliente);
              this.ventas[this.index].cliente = response.data;
              this.ventas[this.index].CampoDireccion = this.ventas[
                this.index
              ].cliente.Direccion;
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => {
            console.log(error);
          });
      }
    },
    onSetProducto(obj) {
      obj.cantidad = 1;
      console.log(obj);
      if (obj.cantidad >= obj.StockPorMayor && obj.StockPorMayor !== 0) {
        obj.precio = obj.PrecioPorMayor;
      } else {
        obj.precio = obj.PrecioContado || 0;
      }
      return obj;
    },
    onAddProducto(obj) {
      this.ventas[this.index].productos.push({ ...obj });
      this.actualizarDescuento();
      this.obtenerStok({
        IdAlmacen: this.ventas[this.index].almacen.IdAlmacen
      });
    },
    actualizarDescuento() {
      if (this.puntosAplicados > 0) {
        this.aplicarPuntos(this.puntosAplicados);
      }

      if (this.descuentoGeneral > 0) {
        this.aplicarDescuentoGeneral(this.descuentoGeneral);
      }
    },
    aplicarPuntos(val) {
      if (parseFloat(val) > this.ventas[this.index].cliente.Puntos) {
        this.puntosAplicados = this.ventas[this.index].cliente.Puntos;
      }

      this.axios
        .get(this.API + "ventas/descuento/" + this.puntosAplicados)
        .then(response => {
          this.$nextTick(() => {
            var subTotal =
              parseFloat(this.$refs.carrito.sumatoriaSubTotal) +
              parseFloat(this.$refs.carrito.sumatoriaDescuento);
            var porcentaje = (response.data.descuento * 100) / subTotal;
            this.descuentoPuntos = response.data;
            //this.aplicarDescuento(response.data.descuento)
            this.aplicarDescuento(porcentaje);
          });
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
            idPuntoVenta: this.ventas[this.index].puntoVenta
              .IdDocVentaPuntoVenta,
            idTipoDoc: this.ventas[this.index].tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          this.ventas[this.index].Serie = response.data.serie;
          this.ventas[this.index].Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    updateTime() {
      this.ventas[this.index].FechaDoc = this.$moment().format(
        "YYYY-MM-DDTH:mm:ss"
      );
    },
    guardarProforma() {
      if (!this.productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
        this.inProgress = false;
        return;
      }

      if (!this.ventas[this.index].cliente.IdCliente) {
        this.$toasted.show("Debe seleccionar un cliente");
        this.inProgress = false;
        return;
      }

      if (!this.ventas[this.index].vendedor.Usuario) {
        this.$toasted.show("Debe seleccionar un vendedor");
        this.inProgress = false;
        return;
      }

      this.ventas[this.index].productos = this.productos;

      this.axios
        .post(this.API + "proformas", this.ventas[this.index])
        .then(response => {
          this.$toasted.show("Proforma Guardada con exito");
          this.inProgress = false;
          this.tieneIgv = false;

          this.initForm();
          this.limpiarCart();
          this.getVendedor();
          // this.$refs.inputCliente.setValue("");

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
      let totalPagos = 0;
      totalPagos += parseFloat(this.ventas[this.index].pago.efectivo.monto);
      totalPagos += parseFloat(this.ventas[this.index].pago.visa.monto);
      totalPagos += parseFloat(this.ventas[this.index].pago.mastercard.monto);
      totalPagos += parseFloat(this.ventas[this.index].pago.otro.monto);

      if (!this.ventas[this.index].EsCredito) {
        if (this.totalCart != totalPagos) {
          this.$toasted.show("Los pagos no coinciden con la venta");
          this.inProgress = false;
          return;
        }
      }

      if (!this.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      var sinStock = 0;
      this.productos.map(pro => {
        if (pro.stock <= 0 && pro.ControlaStock) {
          if (this.ventas[this.index].tipoVenta.CodSunat != "07") {
            this.$toasted.show(
              "El producto " +
                pro.Producto +
                " no tiene stock. No puede vender un producto sin stock"
            );
          }
          sinStock += 1;
        }
      });
      if (sinStock >= 1 && this.ventas[this.index].tipoVenta.CodSunat != "07") {
        this.inProgress = false;
        return;
      }

      if (
        this.ventas[this.index].tipoVenta.CodSunat == "07" ||
        this.ventas[this.index].tipoVenta.CodSunat == "08"
      ) {
        if (
          !this.ventas[this.index].CodSunatModifica ||
          !this.ventas[this.index].NroComprobanteModifica
        ) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }

        if (!this.ventas[this.index].NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }

        this.ventas[this.index].NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.ventas[this.index].NotaIdMotivo
        )[0].text;
      }

      // check if factura DNI
      var esEmpresa =
        this.ventas[this.index].cliente.DniRuc.length > 9 ? true : false;
      if (!esEmpresa && this.ventas[this.index].tipoVenta.CodSunat == "01") {
        this.$toasted.show(
          "Una factura debe ser emitida para una empresa, no un usuario común."
        );
        this.inProgress = false;
        return;
      }

      this.ventas[this.index].productos = this.productos;
      this.ventas[this.index].puntosAplicados = this.puntosAplicados || 0;
      this.ventas[this.index].totalCart = this.totalCart || 0;
      this.ventas[this.index].PagoCon = this.$refs.carrito.pagocon;
      // console.log(this.ventas[this.index]);return;
      this.axios
        .post(this.API + "ventas", this.ventas[this.index])
        .then(response => {
          this.$toasted.success("Venta realizada con exito");
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          this.initForm();
          this.limpiarCart();
          this.getVendedor();
          this.$refs.carrito.pagocon = 0;
          this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
          // this.$refs.inputAlmacen.setValue('VENTA')
          // this.$refs.inputPuntoVenta.setValue('CAJA1')
          // this.$refs.inputCliente.setValue("");
          // this.$refs.inputVendedor.setValue('')

          window.open(
            "/imprimir/index.php?IdDocVenta=" + response.data.insertId,
            "_blank"
          );
          this.emitirauto(response.data.insertId);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    eliminarCliente() {
      // this.$refs.inputCliente.setValue("");
      this.ventas[this.index].cliente = {};
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
    }
  },
  components: {
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas
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
</style>
