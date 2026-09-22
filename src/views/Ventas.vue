<template>
    <div>
        <Menu v-if="false"></Menu>

        <br />

          <b-container fluid class="ancho px-20 py-0" id="conten">
            <b-form @submit="guardarVenta" @keydown.enter="evt => evt.preventDefault()">
                <b-row>
                    <b-col md="6">
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
                    <b-col md="3" >
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
                            :isVentas="true"
                            :isNota="isNota"
                            :bloquearInput="bloquearInput"
                            :fields="carrito.fields"
                            :idAlmacen="venta.almacen.IdAlmacen"
                            :onSetProducto="onSetProducto">

                            <template slot="operaciones">
                                <table class="table b-table border table-footer">
                                  <tbody>
                                    <div class="acciones-pos" v-if="idPerfil != 3">
                                        <div class="fila-botones">

                                            <pago-deuda></pago-deuda>

                                            <proformas
                                                :idAlmacen="venta.almacen.IdAlmacen"
                                                @onSelectProforma="setCliente">
                                            </proformas>

                                        </div>

                                        <div class="fila-botones">

                                            <pre-orden-lista
                                                :idAlmacen="venta.almacen.IdAlmacen"
                                                @onSelectPreOrden="setCliente">
                                            </pre-orden-lista>

                                            <config-venta
                                                @onUpdateConfig="eliminarCliente">
                                            </config-venta>

                                        </div>
                                    </div>
                                    </tbody>

                                    <tbody>
                                        <tr>
                                            <td  class="text-center">Opciones</td>
                                        </tr>
                                        <tr v-if="puntosAplicados == 0 && idPerfil != 3">
                                            <td>
                                                <b-input-group prepend="%" append="Descuento">
                                                    <input
                                                        v-model="descuentoGeneral"
                                                        :disabled="bloquearInput?true:false"
                                                        min="0"
                                                        value="0"
                                                        max="100"
                                                        :readonly="(idPerfil == 3) ? true : false"
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
                                                    v-model="venta.EsCredito"
                                                    :disabled="bloquearInput?true:false || isNota"
                                                    :value="1"
                                                    :unchecked-value="0">
                                                    Venta a credito
                                                </b-form-checkbox>
                                                <b-form-input 
                                                    v-if="venta.EsCredito"
                                                    v-model="venta.FechaCredito"
                                                    :disabled="bloquearInput?true:false || isNota"
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
                                          <td>Efectivo</td>

                                          <td>
                                              <b-button
                                                  variant="info"
                                                  size="sm"
                                                  class="mt-1"
                                                  @click="metodoPagoCambio(1)">
                                                  <i class="fas fa-check"></i>
                                              </b-button>
                                          </td>

                                          <td>
                                              <b-form-input
                                                  v-model="venta.pago.efectivo.descripcion"
                                                  type="text"
                                                  placeholder="Descripcion">
                                              </b-form-input>
                                          </td>

                                          <td>
                                              <input
                                                  v-model="venta.pago.efectivo.monto"
                                                  min="0"
                                                  step="0.01"
                                                  type="number"
                                                  placeholder="S/."
                                                  class="form-control"/>
                                          </td>
                                      </tr>

                                      <tr>
                                          <td>Tarjeta</td>

                                          <td>
                                              <b-button
                                                  variant="info"
                                                  size="sm"
                                                  class="mt-1"
                                                  @click="metodoPagoCambio(2)">
                                                  <i class="fas fa-check"></i>
                                              </b-button>
                                          </td>

                                          <td>
                                              <b-form-input
                                                  v-model="venta.pago.visa.descripcion"
                                                  type="text"
                                                  placeholder="Descripcion">
                                              </b-form-input>
                                          </td>

                                          <td>
                                              <input
                                                  class="form-control"
                                                  v-model="venta.pago.visa.monto"
                                                  min="0"
                                                  step="0.01"
                                                  type="number"
                                                  placeholder="S/."
                                                  @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.mastercard.monto - venta.pago.transferencia.monto - $event.target.value)"
                                              />
                                          </td>
                                      </tr>

                                      <tr>
                                          <td>Yape/Plin</td>

                                          <td>
                                              <b-button
                                                  variant="info"
                                                  size="sm"
                                                  class="mt-1"
                                                  @click="metodoPagoCambio(3)">
                                                  <i class="fas fa-check"></i>
                                              </b-button>
                                          </td>

                                          <td>
                                              <b-form-input
                                                  v-model="venta.pago.mastercard.descripcion"
                                                  type="text"
                                                  placeholder="Descripcion">
                                              </b-form-input>
                                          </td>

                                          <td>
                                              <input
                                                  class="form-control"
                                                  v-model="venta.pago.mastercard.monto"
                                                  min="0"
                                                  step="0.01"
                                                  type="number"
                                                  placeholder="S/."
                                                  @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.transferencia.monto - $event.target.value)"
                                              />
                                          </td>
                                      </tr>

                                      <tr>
                                          <td>Transferencia</td>

                                          <td>
                                              <b-button
                                                  variant="info"
                                                  size="sm"
                                                  class="mt-1"
                                                  @click="metodoPagoCambio(4)">
                                                  <i class="fas fa-check"></i>
                                              </b-button>
                                          </td>

                                          <td>
                                              <b-form-input
                                                  v-model="venta.pago.transferencia.descripcion"
                                                  type="text"
                                                  placeholder="Descripcion">
                                              </b-form-input>
                                          </td>

                                          <td>
                                              <input
                                                  class="form-control"
                                                  v-model="venta.pago.transferencia.monto"
                                                  min="0"
                                                  step="0.01"
                                                  type="number"
                                                  placeholder="S/."
                                                  @input="venta.pago.efectivo.monto = $options.filters.redondear(totalCart - venta.pago.visa.monto - venta.pago.mastercard.monto - $event.target.value)"
                                              />
                                          </td>
                                      </tr>

                                      <!-- NUEVO: switch Efectivo Parcial -->
                                      <tr>
                                          <td colspan="4" class="text-center py-2">
                                              <b-form-checkbox
                                                  v-model="venta.EfectivoParcial"
                                                  :value="1"
                                                  :unchecked-value="0"
                                                  :disabled="isNota"
                                                  switch
                                                  size="lg">
                                                  <i class="fas fa-coins"></i> <b>EFECTIVO PARCIAL</b>
                                              </b-form-checkbox>
                                          </td>
                                      </tr>

                                  </tbody>
                                </table>
                            </template>
                            <template slot="accionesventa">

                              <div class="acciones-venta">
                                  <b-button
                                      class="btn-venta btn-efectuar"
                                      type="submit"
                                      :disabled="inProgress">

                                      <i class="fas fa-check-circle mr-2"></i>
                                      Efectuar Venta
                                  </b-button>

                                  <b-button
                                      class="btn-venta btn-proforma"
                                      type="button"
                                      :disabled="inProgress"
                                      @click="guardarProforma">

                                      <i class="fas fa-file-alt mr-2"></i>
                                      Crear Proforma

                                  </b-button>
                              </div>

                          </template>
                        </productos-carrito>
                    </b-col>
                </b-row>
            </b-form>
        </b-container>
        <b-modal v-model="validarEsNota" hide-footer hide-header>
            <b-row>
              <b-col cols="12">
                <center>
                  <h1 style="font-size: 5rem;"> ?</h1>

                </center>
              </b-col>
              <b-col cols="12"> 
                <center>

                  <h2>Productos Nota de venta</h2>
                  <h3>Existe uno o más productos  que solo debe ir en nota de venta. ¿Desea continuar? </h3>
                </center>
                </b-col>
              <b-col>
                <center>

                  <b-button variant="danger" @click="continuarGuardarVenta()">Si, Continuar</b-button>
                  <b-button variant="success" @click="validarEsNota=false" >No, Cancelar</b-button>
                </center>
              </b-col>
            </b-row>
        </b-modal>
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
// import * as Swal from "sweetalert2";
import { Bar } from 'vue-chartjs';

export default {
  beforeMount() {
    this.initForm();
  },
  created() {
    window.addEventListener("keyup", e => {
      if (e.which === 120) {
        let r = confirm("¿Está seguro de realizar la venta?");
        if (r) {
          this.guardarVenta();
        }
      }
    });
  },
  mounted() {
    // console.log(Swal.fire);
    // this.getNumeroVenta()
    this.getVendedor();
    //setInterval(this.updateTime, 1000)
    /* this.loadFirstAlmacen()
        this.loadFirstPuntoVenta() */
    this.loadClienteVarios();
  },
  data() {
    return {
      validarEsNota: false,
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
        //{ value: "04", text: "DESCUENTO GLOBAL" },
        //{ value: "05", text: "DESCUENTO POR ITEM" },
        { value: "06", text: "DEVOLUCION TOTAL" },
        { value: "07", text: "DEVOLUCION POR ITEM" },
        //{ value: "08", text: "BONIFICACION" },
        //{ value: "09", text: "DISMINUCION EN EL VALOR" },
        //{ value: "10", text: "OTROS CONCEPTOS" }
      ],
      carrito: {
        fields: [
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center stocksize"
          },
          //{ key: 'Lote', class:'text-center stocksize' },
          //{ key: 'CodigoBarra', label: 'Código', class:'stocksize' },
          { key: "Producto", class: "text-left stocksizepro" },
          {
            key: "movimiento",
            label: "",
            class: "text-center vertical stocksize2"
          },
          {
            key: "ProductoFormaFarmaceutica",
            label: "For. F.",
            class: "text-left stocksize2"
          },
          {
            key: "ProductoMarca",
            label: "Lab.",
            class: "text-left stocksize2"
          },
          //{ key: 'ProductoDescCorto', label: 'Anaquel', class:'text-center stocksize' },
          {
            key: "Presentacion",
            label: "Present",
            class: "text-center stocksize stocksize2th stocksize2th2"
          },
          { key: "eventPres", label: "", class: "text-center stocksize" },
          {
            key: "cantxPrecio",
            variant: "warning",
            class: "text-center stocksize stocksize2th"
          },
          //{ key: 'PrecioPorMayor', label: 'Precio x Mayor' },
          //{ key: 'StockPorMayor', label: 'Stock x Mayor' },
          //{ key: 'cantidad', class:'stocksize' },
          {
            key: "precio",
            label: "Precio",
            class: "text-center stocksize stocksize stocksize2th"
          },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center stocksize"
          },
          // {
          //   key: "descuento",
          //   label: "Descuento",
          //   class: "text-center stocksize stocksize2th"
          // },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center stocksize"
          },
          { key: "acciones", label: "", class: "stocksize" }
        ]
      },
      idPerfil: sessionStorage.getItem("IdPerfil"),
    };
  },
  watch: {
    // NUEVO: si se marca crédito, se desmarca efectivo parcial (son excluyentes)
    "venta.EsCredito"(val) {
      if (val) this.venta.EfectivoParcial = 0;
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
    })
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      add: "agregarProducto"
    }),
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    loadFirstAlmacen() {
      this.axios.get(this.API + "almacenes/primero").then(res => {
        this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.venta.almacen = res.data;
      });
    },
    loadFirstPuntoVenta() {
      this.axios.get(this.API + "puntoventa/primero").then(res => {
        this.$refs.inputPuntoVenta.setValue(res.data.PuntoVenta);
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
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    initForm() {
      this.venta = {
        EsCredito: 0,
        EfectivoParcial: 0, // NUEVO
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
          transferencia: { IdMetodoPago: 4, monto: 0 }
        },
        CampoDireccion: "direccion",
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: ""
      };

      this.isNota = false;
      this.bloquearInput = false;
      this.descuentoGeneral = 0;
      this.puntosAplicados = 0;
      this.descuentoPuntos = {};
    },
    setTipoVenta(obj) {
      if (this.isNota == true) {
        this.limpiarCart();
      }
      this.isNota = false;
      this.bloquearInput = false;
      this.venta.NroComprobanteModifica = "";
      this.venta.CodSunatModifica = "";
      this.venta.NotaIdMotivo = "";
      this.venta.NotaDescMotivo = "";

      this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");

      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.limpiarCart();
        this.isNota = true;
        this.venta.EfectivoParcial = 0; // NUEVO
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }

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
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiarCart();
          response.data.map(item => {
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
      if (this.productos.length > 0) {
        this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
      }
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
        this.getNumeroVenta();
      });
    },
    setPuntoVenta(obj) {
      this.venta.puntoVenta = obj;
      this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          this.$refs.inputVendedor.$refs.input.disabled = true;
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs
          this.$refs.inputAlmacen.$refs.input.disabled = true;
          this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          //this.getNumeroVenta()
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      this.venta.vendedor = obj;
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
    },
    onSetProducto(obj, presentacion) {
      if (presentacion) {
        obj.cantidad = 1;
        obj.precio = presentacion.precioventa || 0;
        return obj;
      }
      obj.cantidad = 1;
      obj.precio = obj.PrecioContado || 0;
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
    guardarProforma() {
      this.inProgress = true;
      if (!this.productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
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

      this.venta.productos = this.productos;

      this.axios
        .post(this.API + "proformas", this.venta)
        .then(response => {
          this.$toasted.show("Proforma Guardada con exito");
          this.inProgress = false;
          this.tieneIgv = false;

          this.initForm();
          this.limpiarCart();
          this.getVendedor();
          this.$refs.inputCliente.setValue("");

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

      // Evita doble click
      if (this.inProgress) {
        return;
      }

      this.inProgress = true;

      if (this.venta.tipoVenta.IdTipoDoc != 3) {
        if (this.productos.some(v => v.NotaVenta == 1)) {
          this.validarEsNota = true;
          this.inProgress = false; // vuelve a habilitar
        } else {
          this.continuarGuardarVenta();
        }
      } else {
        this.continuarGuardarVenta();
      }
  },
    continuarGuardarVenta() {
      if (this.venta.tipoVenta.CodSunat !== "07") {
        // Demanda por producto + lote (incluye regalos), en unidades base
        const demanda = {};
        this.productos.forEach(p => {
          // Regalo sin info de lote/stock (otro producto): no se puede validar aquí
          if (p.EsRegalo && p.stockMovimiento === undefined) return;

          const key = p.IdProducto + "|" + (p.hashMovimiento || "sin-lote");
          if (!demanda[key]) {
            demanda[key] = {
              nombre: p.Producto.replace(" (REGALO)", ""),
              stock: Number(p.stockMovimiento || p.stock || 0),
              total: 0
            };
          }
          demanda[key].total +=
            Number(p.cantxPrecio || 0) * Number(p.cantidadPress || 1);
        });

        const faltantes = Object.values(demanda).filter(d => d.total > d.stock);

        if (faltantes.length) {
          const detalle = faltantes
            .map(d => `${d.nombre}: necesita ${d.total} und. y el lote tiene ${d.stock}`)
            .join("<br>");

          this.$swal({
            position: "center",
            icon: "info",
            title: "Stock insuficiente en el lote",
            html: detalle + "<br><small>Elige otro lote o reduce la cantidad.</small>",
            showConfirmButton: true,
            confirmButtonText: "Entiendo",
            target: document.getElementById("conten"),
            confirmButtonColor: "#0275d8"
          });
          this.inProgress = false;
          return;
        }
      }

  this.validarEsNota = false;
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
    this.$toasted.show("El metodo de pago tarjeta no puede ser negativo");
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
      "El metodo de pago mastercard no puede ser negativo"
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
      "El metodo de pago transferencia no puede ser negativo"
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

  if (!this.productos.length) {
    this.$toasted.show("No hay productos para vender");
    this.inProgress = false;
    return;
  }

  let igualLote = 0;
  let precioBajo = 0;
  let totalCero = 0;
  this.productos.forEach(pro => {
    if (pro.EsRegalo) return
    // En una Nota de Crédito las líneas reflejan exactamente lo que ya se vendió
    // (incluidas líneas a S/0, como regalos): no se debe re-validar como venta nueva.
    if (this.isNota) return
    if (parseFloat(pro.cantidad * pro.precio) <= 0) {
      this.$toasted.error("No se puede vender productos con total cero");
      totalCero += 1;
    }

    let lote = this.productos.filter(
      p =>
        !p.EsRegalo &&
        p.hashMovimiento == pro.hashMovimiento &&
        p.IdProducto == pro.IdProducto
    ).length;
    if (lote > 1) {
      igualLote += 1;
    }

    if (parseFloat(pro.precio) < parseFloat(pro.PrecioContado)) {
      this.$toasted.show(
        "El precio de venta del producto " +
          pro.Producto +
          " no debe ser menor al precio configurado en el sistema (" +
          pro.PrecioContado +
          ")"
      );
      precioBajo += 1;
    }
  });

  if (precioBajo >= 1) {
    this.inProgress = false;
    return;
  }

  if (totalCero >= 1) {
    this.inProgress = false;
    return;
  }

  if (igualLote >= 1) {
    this.$toasted.error("Debe seleccionar diferente lote por producto");
    this.inProgress = false;
    return;
  }

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

    this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(
      item => item.value == this.venta.NotaIdMotivo
    )[0].text;
  }

      // check if factura DNI
  var esEmpresa = this.venta.cliente.DniRuc.length > 9 ? true : false;
  if (!esEmpresa && this.venta.tipoVenta.CodSunat == "01") {
    this.$toasted.show(
      "Una factura debe ser emitida para una empresa, no un usuario común."
    );
    this.inProgress = false;
    return;
  }
  this.productos.forEach(pro => {
    pro.cantidad = pro.cantxPrecio;
  });

  this.venta.productos = this.productos.map(pro => {
    if (!pro.EsRegalo) return pro;

    const origen = this.productos.find(p => p.uid === pro.IdLineaOrigen);
    // Solo hereda si el regalo es el mismo producto que se compró
    if (!origen || origen.IdProducto != pro.IdProducto) return pro;

    const costoUnitario = parseFloat(origen.PrecioCostoF || 0) / parseFloat(origen.cantidadPress || 1);

    return Object.assign({}, pro, {
      FechaVen: origen.FechaVen || null,
      hashMovimiento: origen.hashMovimiento || null,
      Lote: origen.Lote || null,
      Registro: origen.Registro || null,
      PrecioCostoF: costoUnitario > 0 ? costoUnitario : null
    });
  });
  this.venta.puntosAplicados = this.puntosAplicados || 0;
  this.venta.totalCart = this.totalCart || 0;
  this.venta.PagoCon = this.$refs.carrito.pagocon;
      // console.log(this.venta);return;
  this.axios
    .post(this.API + "ventas", this.venta)
    .then(response => {
      this.$toasted.success("Venta realizada con exito");
      this.inProgress = false;
      this.tieneIgv = false;
      this.ultimaVenta = response.data.insertId;
      this.$refs.carrito.pagocon = 0;
      this.initForm();
      this.limpiarCart();
      this.getVendedor();
      this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
          // this.$refs.inputAlmacen.setValue('VENTA')
          // this.$refs.inputPuntoVenta.setValue('CAJA1')
          // this.$refs.inputCliente.setValue('')
          // this.$refs.inputVendedor.setValue('')

      window.open(
        "/imprimir/index.php?IdDocVenta=" + response.data.insertId,
        "_blank"
      );
      this.loadClienteVarios();
    })
    .catch(error => {
      console.log(error);
      this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
      this.inProgress = false;
    });
},
    metodoPagoCambio(tipo) {

      this.venta.pago.efectivo.monto = 0;
      this.venta.pago.visa.monto = 0;
      this.venta.pago.mastercard.monto = 0;
      this.venta.pago.transferencia.monto = 0;

      switch (tipo) {
        case 1:
          this.venta.pago.efectivo.monto = this.totalCart;
          break;

        case 2:
          this.venta.pago.visa.monto = this.totalCart;
          break;

        case 3:
          this.venta.pago.mastercard.monto = this.totalCart;
          break;

        case 4:
          this.venta.pago.transferencia.monto = this.totalCart;
          break;
      }
    },
    eliminarCliente() {
      this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
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
.big-text{
    font-size:45px;
    font-weight:500;
    line-height:1;
}
-- Diseños de los botones de acciones
.acciones-pos{
    padding:5px;
}

.fila-botones{
    display:flex;
    gap:8px;
    margin-bottom:8px;
}

.fila-botones > *{
    flex:1;
}

.acciones-pos{
    width:100%;
}

.btn-pos{
    min-width:218px;
    height:42px;
    border-radius:12px !important;
    font-size:18px !important;
    font-weight:600 !important;
}
</style>

<style scoped>
.Baracciones-venta{
    margin-top:10px;

    display:flex;
    flex-direction:column;

    gap:12px;

    padding:15px;
}

.btn-venta{
    width:100%;
    height:50px;

    font-size:18px;
    font-weight:700;

    margin-top:8px;

    border-radius:12px !important;

    transition:all .25s ease;

    box-shadow:
        0 3px 8px rgba(0,0,0,.12);
}

.btn-venta:hover{


    box-shadow:
        0 8px 18px rgba(0,0,0,.20);
}

.btn-venta:active{

    box-shadow:
        0 4px 10px rgba(0,0,0,.15);
}

.btn-proforma{
    background:#1677ff !important;
    border-color:#1677ff !important;
}

.btn-proforma:hover{
    background:#0f67e6 !important;
    border-color:#0f67e6 !important;
}

.btn-efectuar{
    background:#28a745 !important;
    border-color:#28a745 !important;
}

.btn-efectuar:hover{
    background:#23913d !important;
    border-color:#23913d !important;
}

</style>