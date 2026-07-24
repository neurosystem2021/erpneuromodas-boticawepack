<template>
  <div>
    <Menu v-if="false"></Menu>
    <b-row v-if="pagosPendientes>0">
        <b-col class="holder">
            <h4 class="news" height="100" width="200" bgcolor="white">Tiene <b>{{pagosPendientes}} pago(s) pendientes</b> del servicio de facturación, porfavor regularizar para mantener la validez de los comprobantes emitidos.  <b>{{fechaUltPago!=''?' Fecha de Vencimiento: '+fechaUltPago:''}}</b></h4>
        </b-col>
    </b-row>
    <b-container class="bv-example-row" id="conten" fluid>
        <b-row>
            <b-col md="12" v-if="$route.query.idPedido">
                <div class="form-inline">
                    <h4>Pago de delivery código: <b-badge variant="info">{{$route.query.idPedido}}</b-badge></h4>
                </div>
            </b-col>
        </b-row>

        <!-- ============ MULTIVENTANAS ============ -->
        <b-card no-body v-if="ventas.length>0">
          <b-tabs card pills v-model="index" justified>
            <b-tab :title="`Venta ${i+1}`" v-for="(tab,i) in ventas" :key="i">

              <b-form @submit="guardarVenta" @keydown.enter="prevent">
                  <b-row>
                      <b-col md="7">
                          <b-row>
                              <b-col md="4">
                                  <b-form-group>
                                      <label>Tipo de Venta:  <b style="display:none" class="mostrarSerie">{{ tab.Serie }} - {{ tab.Numero }}</b></label>
                                      <b-input-group>
                                      <autocomplete
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
                                      </b-input-group>
                                  </b-form-group>
                                   <b-form-group v-if="tab.isNota">
                                      <label>Doc. Referencia:</label>
                                      <autocomplete
                                          required="required"
                                          :ref="`inputDocRef${i}`"
                                          :url="API + 'ventas/lista/'+tab.puntoVenta.IdDocVentaPuntoVenta"
                                          anchor="NroComprobante"
                                          initValue=""
                                          label="writer"
                                          placeholder=""
                                          :on-select="setDocRef"
                                          :onFocus="focusAutocomplete.bind(null, `inputDocRef${i}`)"
                                          :classes="{input: 'form-control'}">
                                      </autocomplete>
                                  </b-form-group>
                              </b-col>
                              <b-col md="4" class="ocularMovil2">
                                  <b-form-group>
                                      <label>Almacen:</label>
                                      <autocomplete
                                          required="required"
                                          :ref="`inputAlmacen${i}`"
                                          :url="API + 'almacenes'"
                                          anchor="Almacen"
                                          initValue="HUANUCO"
                                          label="writer"
                                          placeholder=""
                                          :on-select="setAlmacen"
                                          :onFocus="focusAutocomplete.bind(null, `inputAlmacen${i}`)"
                                          :classes="{input: 'form-control'}">
                                      </autocomplete>
                                  </b-form-group>
                                      <b-form-group v-if="tab.isNota">
                                      <label>Motivo :</label>
                                      <b-form-select v-model="tab.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                                  </b-form-group>
                              </b-col>
                              <b-col md="4" class="ocularMovil2">
                                  <b-form-group>
                                      <label>Punto de Venta:</label>
                                      <autocomplete
                                          required="required"
                                          :ref="`inputPuntoVenta${i}`"
                                          :url="API + 'ventas/puntos'"
                                          anchor="PuntoVenta"
                                          initValue="HUANUCO"
                                          label="writer"
                                          placeholder=""
                                          :on-select="setPuntoVenta"
                                          :onFocus="focusAutocomplete.bind(null, `inputPuntoVenta${i}`)"
                                          :classes="{input: 'form-control'}">
                                      </autocomplete>
                                  </b-form-group>
                              </b-col>
                              <b-col md="12">
                                  <b-form-group>
                                      <label>Cliente:</label>

                                      <b-input-group>
                                       <clientes-form txtButton="Editar" v-if="tab.cliente.IdCliente && tab.cliente.DniRuc != '00000000'" idModal="editCliente" @onAddCliente="setCliente" :editCliente="tab.cliente"></clientes-form>
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
                                              class="form-control ocularMovil2"
                                              placeholder="Codigo de Barra" />
                                          <b-input-group-append>
                                              <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
                                          </b-input-group-append>
                                      </b-input-group>
                                       <b-form-select v-if="tab.cliente.IdCliente" v-model="tab.CampoDireccion" class="mb-3">
                                          <option :value="tab.cliente.Direccion">{{ tab.cliente.Direccion }}</option>
                                          <option :value="tab.cliente.Direccion2">{{ tab.cliente.Direccion2 }}</option>
                                          <option :value="tab.cliente.Direccion3">{{ tab.cliente.Direccion3 }}</option>
                                      </b-form-select>
                                      <b-input-group>
                                        <label style="margin-right: 10px;">Garantía: </label></br>
                                        <b-form-radio-group
                                        v-model="tab.Garantia"
                                        :options="[
                                          { text: '3 Meses', value: 1 },
                                          { text: '6 Meses', value: 2 },
                                          { text: '12 Meses', value: 3 }
                                        ]"
                                        :disabled="bloquearInput?true:false">
                                        </b-form-radio-group>
                                      </b-input-group>
                                      <b-row v-if="$route.query.idPedido">
                                          <b-col>
                                              <b-form-group>
                                                  <label style="font-weight:bold">Cliente (Delivery):</label>
                                                  <b-form-input required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese cliente" autocomplete="off" v-model="tab.nombreLlevar" type="text" name="NombreLlevar" readonly></b-form-input>
                                              </b-form-group>
                                          </b-col>
                                          <b-col>
                                              <b-form-group>
                                                  <label style="font-weight:bold">Direccion Entrega (Delivery):</label>
                                                  <b-form-textarea required style="text-transform:uppercase; font-size:0.9rem" placeholder="Ingrese direccion" rows="3" autocomplete="off" v-model="tab.DireccionEntrega" type="text" name="DireccionEntrega" readonly></b-form-textarea>
                                              </b-form-group>
                                          </b-col>
                                      </b-row>
                                  </b-form-group>
                              </b-col>
                          </b-row>
                      </b-col>
                      <b-col md="3">
                          <b-form-group class="text-center" v-if="tab.cliente.Cliente">
                              <div class="puntos">
                                  {{ (tab.cliente.Puntos || 0) | redondeoMenor }}
                              </div>
                              Puntos del cliente
                          </b-form-group>
                          <b-input-group prepend="Puntos" :append="descuentoPuntos.descuento | redondear" v-if="tab.cliente.Puntos && descuentoGeneral == 0">
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
                          <b-form-text v-if="tab.cliente.EsVIP">
                             Cliente VIP con Precio Convenio
                          </b-form-text>
                      </b-col>
                      <b-col md="2" class="ocularMovil2">
                          <b-form-group class="text-center">
                              <div class="big-text">
                                  {{ tab.Serie }} - <b>{{ tab.Numero }}</b>
                              </div>
                              {{ tab.FechaDoc }}
                          </b-form-group>
                          <b-form-group>
                              <label>Vendedor:</label>
                              <autocomplete
                                  required="required"
                                  :ref="`inputVendedor${i}`"
                                  :url="API + 'usuarios'"
                                  anchor="NombreUsuario"
                                  label="writer"
                                  :on-select="setVendedor"
                                  :onFocus="focusAutocomplete.bind(null, `inputVendedor${i}`)"
                                  :classes="{input: 'form-control'}">
                              </autocomplete>
                          </b-form-group>
                          <b-badge variant="info" v-if="tab.adelanto">Adelanto de: S/ {{ tab.adelanto }} </b-badge>
                          <b-badge variant="success" v-if="tab.adelanto">Total a pagar: S/ {{ $options.filters.redondear(tab.totalCart - tab.adelanto) }} </b-badge>
                      </b-col>
                  </b-row>
                  <b-row>
                      <b-col>
                          <productos-carrito
                              @onInputCantidad="actualizarDescuento"
                              @onAddProducto="onAddProducto"
                              @onRemoveProducto="actualizarDescuento"
                              @setProducto="setProducto"
                              @eliminarProducto="eliminarProducto"
                              :igvTotal="tieneIgv"
                              :setPrecio="true"
                              :ref="`carrito${i}`"
                              :showButton="false"
                              :bloquearInput="bloquearInput"
                              :fields="carrito.fields"
                              :isVentas="true"
                              :idAlmacen="tab.almacen.IdAlmacen"
                              :onSetProducto="onSetProducto"
                              :esDelivery="tab.EsDelivery"
                              :productos="tab.productos"
                              :index="i">

                  <template slot="operaciones">
                      <b-button class="p-1" :id="`popover-opciones-${i}`" variant="warning"><i
                          class="fas fa-tags"></i></b-button>
                      <b-popover :target="`popover-opciones-${i}`" placement="bottom" custom-class="custom-popover"
                        title="Opciones" triggers="click blur focus" auto-hide>
                        <table class="table b-table border table-footer">
                          <tbody>
                            <tr v-if="puntosAplicados == 0">
                              <td>
                                <b-input-group prepend="S/">
                                  <input v-model="descuentoGeneral" min="0" value="0"
                                    :disabled="bloquearInput ? true : false"
                                    @input="(val) => { aplicarDescuentoGeneral(val.target.value) }" type="number"
                                    class="form-control" />
                                </b-input-group>
                                <b-form-text>
                                  Descuento general a toda la venta.
                                </b-form-text>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <b-form-checkbox
                                  :disabled="bloquearInput ? true : false || tab.isNota || tab.EsDelivery"
                                  v-model="tab.EsCredito" :value="1" :unchecked-value="0">
                                  Venta a credito
                                </b-form-checkbox>
                                <b-form-input :disabled="bloquearInput ? true : false || tab.isNota || tab.EsDelivery"
                                  v-if="tab.EsCredito" v-model="tab.FechaCredito" type="date">
                                </b-form-input>
                                <b-form-checkbox :disabled="bloquearInput ? true : false || tab.EsDelivery"
                                  v-model="tab.PorEntregar" :value="1" :unchecked-value="0">
                                  Por entregar
                                </b-form-checkbox>
                                <b-form-checkbox :disabled="bloquearInput ? true : false || tab.EsDelivery"
                                  v-model="tab.ProformaSinPrecio" :value="1" :unchecked-value="0">
                                  Sin precios en proforma
                                </b-form-checkbox>
                              </td>
                            </tr>
                            <tr>
                              <td>
                                <b-input-group prepend="Proforma válido por:" append="días">
                                  <b-form-input :disabled="bloquearInput ? true : false" v-model="tab.diasValidosProf"
                                    min="0" value="0" type="number"></b-form-input>
                                </b-input-group>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </b-popover>
                    </template>
                              <template slot="metodopago" v-if="!tab.EsCredito">
                                  <b-button class="p-1" :id="`popover-metodopago-${i}`" variant="success"><i
                                      class="fas fa-credit-card"></i></b-button>
                                    <b-badge v-if="tab.idMetodoPago == 1" variant="success">EFECTIVO</b-badge>
                                    <b-badge v-if="tab.idMetodoPago == 2" variant="info">TARJETA</b-badge>
                                    <b-badge v-if="tab.idMetodoPago == 3" variant="info">TRANSF.</b-badge>
                                    <b-badge v-if="tab.idMetodoPago == 4" variant="info">YAPE/PLIN</b-badge>
                                  <b-popover :target="`popover-metodopago-${i}`" placement="bottom" triggers="click blur focus"
                                    custom-class="custom-popover" auto-hide>
                                    <div class="table-responsive p-2">
                                      <table class="table table-bordered table-sm">
                                        <tbody>
                                          <tr>
                                            <td colspan="3" class="text-center font-weight-bold">Método de Pago</td>
                                          </tr>
                                          <tr>
                                            <td>Efectivo</td>
                                            <td>
                                              <b-button @click="cargarMonto('efectivo')" size="sm" variant="primary">
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                              </b-button>
                                            </td>
                                            <td>
                                              <b-form-input v-model="tab.pago.efectivo.descripcion" type="text"
                                                :disabled="bloquearInput" placeholder="Descripcion"></b-form-input>
                                            </td>
                                            <td>
                                              <input v-model="tab.pago.efectivo.monto" min="0" :disabled="bloquearInput" step="0.01"
                                                type="number" class="form-control" placeholder="S/." />
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>Tarjeta</td>
                                            <td>
                                              <b-button size="sm" @click="cargarMonto('visa')" variant="primary">
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                              </b-button>
                                            </td>
                                            <td>
                                              <b-form-input v-model="tab.pago.visa.descripcion" type="text" :disabled="bloquearInput"
                                                placeholder="Descripcion"></b-form-input>
                                            </td>
                                            <td>
                                              <input class="form-control" :disabled="bloquearInput" v-model="tab.pago.visa.monto"
                                                min="0" step="0.01" type="number" placeholder="S/."
                                                @input="tab.pago.efectivo.monto = $options.filters.redondear(tab.totalCart - tab.pago.mastercard.monto - tab.pago.yape.monto - $event.target.value)" />
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>Transferencia</td>
                                            <td>
                                              <b-button size="sm" @click="cargarMonto('mastercard')" variant="primary">
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                              </b-button>
                                            </td>
                                            <td>
                                              <b-form-input v-model="tab.pago.mastercard.descripcion" :disabled="bloquearInput"
                                                type="text" placeholder="Descripcion"></b-form-input>
                                            </td>
                                            <td>
                                              <input class="form-control" v-model="tab.pago.mastercard.monto" :disabled="bloquearInput"
                                                min="0" step="0.01" type="number" placeholder="S/."
                                                @input="tab.pago.efectivo.monto = $options.filters.redondear(tab.totalCart - tab.pago.visa.monto - tab.pago.yape.monto - $event.target.value)" />
                                            </td>
                                          </tr>
                                          <tr>
                                            <td>YAPE / PLIN</td>
                                            <td>
                                              <b-button size="sm" @click="cargarMonto('yape')" variant="primary">
                                                <i class="fa fa-check" aria-hidden="true"></i>
                                              </b-button>
                                            </td>
                                            <td>
                                              <b-form-input v-model="tab.pago.yape.descripcion" type="text"
                                                placeholder="Descripcion"></b-form-input>
                                            </td>
                                            <td>
                                              <input class="form-control" v-model="tab.pago.yape.monto" min="0" step="0.01"
                                                type="number" placeholder="S/."
                                                @input="tab.pago.efectivo.monto = $options.filters.redondear(tab.totalCart - tab.pago.visa.monto - tab.pago.mastercard.monto - $event.target.value)" />
                                            </td>
                                          </tr>
                                        </tbody>
                                      </table>
                                    </div>
                                  </b-popover>
                              </template>
                              <template slot="adicional" v-if="!tab.EsCredito">
                                  <table class="table b-table border table-footer table-sm text-center" >
                                      <tbody>
                                          <tr><td>Mano de obra bruta</td></tr>
                                          <tr><td><h3>{{ manoDeObraBruta | redondear }}</h3></td></tr>
                                          <tr><td>Mano de obra neta</td></tr>
                                          <tr><td><h3 :class="{'text-danger': manoDeObraNeta <= 0}">{{ manoDeObraNeta | redondear }}</h3></td></tr>
                                      </tbody>
                                  </table>
                              </template>
                              <template slot="botonPagar">
                                  <div class="text-center">
                                      <b-button class="btnAction my-2 boton-pagar-chico" type="submit" v-if="mostrarEfectuarVenta()" :disabled="inProgress" variant="success"><i class="fa fa-shopping-cart" aria-hidden="true"></i> Pagar</b-button>
                                  </div>
                                  <div class="text-center" v-if="ultimaVenta">
                                      <b-button target="_blank" variant="info" class="btnAction my-2 boton-pagar-chico" :href="'/imprimir/index.php?IdDocVenta=' + ultimaVenta">Imprimir última Venta</b-button>
                                  </div>
                              </template>
                              <template slot="botonesIzquierda">
                                  <b-button block class="btnAction my-2" style="background-color:#FF3D00; color:#fff;" type="button" v-if="!$route.query.idPedido" :disabled="inProgress" @click="guardarProforma">Crear Proforma</b-button>
                                  <proformas
                                      :idAlmacen="tab.almacen.IdAlmacen"
                                      @onSelectProforma="setProforma"
                                      @vendedorProforma="guardarVendedor" />
                                  <delivery-modal ref="deliveryModal" @onPagarDelivery="onPagarDelivery"></delivery-modal>
                                  <b-button block class="btnAction my-2" type="button" @click="anularPagoDelivery" variant="danger" v-if="$route.query.idPedido">Anular Pago Delivery</b-button>
                                  <b-button block class="btnAction my-2" style="background-color:#757575; color:#fff;" type="button" @click="anadirVenta">Añadir Ventana</b-button>
                                  <b-button block class="btnAction my-2" style="background-color:#FFD600; color:#fff;" type="button" v-if="index!=0" @click="eliminarVentana">Eliminar Ventana</b-button>
                              </template>
                              <template slot="botonesDerecha">
                                  <movimiento></movimiento>
                                  <pago-deuda></pago-deuda>
                                  <pre-orden-lista
                                      v-show="!$route.query.idPreOrden"
                                      :idAlmacen="tab.almacen.IdAlmacen" @onSelectPreOrden="setCliente"
                                      @onPreOrden="selectPreOrden"></pre-orden-lista>
                                  <config-venta @onUpdateConfig="eliminarCliente" @onUpdateConfigComision="eliminarComisionista"></config-venta>
                              </template>
                          </productos-carrito>
                      </b-col>
                  </b-row>
              </b-form>
            </b-tab>
          </b-tabs>
        </b-card>
        <!-- ============ FIN MULTIVENTANAS ============ -->

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
import Movimiento from "@/components/Movimiento";
import Swal from "sweetalert2";

export default {
  beforeMount() {
    this.initForm();
  },
  mounted() {
    this.getVendedor();
    this.loadClienteVarios();
    this.loadComisionista();
    this.escucharPedidos();
    this.getEstadoSistema();
  },
  data() {
    return {
      ventas: [],
      index: 0,
      preVenta: {},
      isNota: false,
      dni: "",
      adelanto: 0,
      gasto: 0,
      newDniRuc: "",
      ultimaVenta: "",
      inProgress: false,
      API: config.API,
      APIADMIN: "http://204.48.21.196:5000",
      puntosAplicados: 0,
      descuentoPuntos: {},
      descuentoGeneral: 0,
      tieneIgv: false,
      venta: {}, // puntero temporal a ventas[index], usado en validaciones/guardado
      bloquearInput: false,
      diasValidosProf: 1,
      idMetodoPago: 0,
      adelantosPreOrden: [],
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
          { key: "manoobra", label: "Mano de obra", class: "text-center" },
          { key: "stock", label: "Stock", variant: "info", class: "text-center" },
          { key: "Producto" },
          { key: "Descripcion", label: "Descripción" },
          { key: "eventPres", label: "" },
          { key: "cantxPrecio", variant: "warning" },
          { key: "cantidad" },
          { key: "precio", label: "Precio" },
          { key: "utilidad", label: "Porcentaje Utilidad %" },
          { key: "subtotal", label: "SubTotal", variant: "success", class: "text-center" },
          { key: "descuento", label: "Descuento" },
          { key: "total", label: "Total", variant: "danger", class: "text-center" },
          { key: "acciones", label: "" }
        ]
      },
      pagosPendientes: 0,
      fechaUltPago: ""
    };
  },
  watch: {
    index(newVal) {
      if (this.ventas[newVal]) {
        this.$store.state.cart.productos = this.ventas[newVal].productos;
      }
    },
    vendedor() {
      let ref = `inputVendedor${this.index}`;
      this.$refs[ref] && this.$refs[ref][0] && this.$refs[ref][0].setValue(this.vendedor);
    },
    totalCartActivo(newVal) {
      if (!this.ventas[this.index]) return;
      let venta = this.ventas[this.index];
      venta.totalCart = newVal;
      if (venta.idMetodoPago == 2) {
        venta.pago.visa.monto = newVal;
      } else if (venta.idMetodoPago == 3) {
        venta.pago.mastercard.monto = newVal;
      } else if (venta.idMetodoPago == 4) {
        venta.pago.yape.monto = newVal;
      } else {
        venta.idMetodoPago = 1;
        venta.pago.efectivo.monto = newVal;
      }
    },
  },
  computed: {
    ...mapState({
      vendedor: state => state.cart.vendedor
    }),
    totalCartActivo() {
      if (!this.ventas[this.index]) return 0;
      let sumatoria = 0;
      this.ventas[this.index].productos.forEach(p => {
        sumatoria += parseFloat(p.total || 0) - parseFloat(p.descuento || 0);
      });
      return parseFloat(sumatoria.toFixed(2));
    },
    manoDeObraBruta() {
      if (!this.ventas[this.index]) return 0;
      let pManoObra = this.ventas[this.index].productos.find(p => p.CodigoBarra === "MANODEOBRA");
      return pManoObra ? pManoObra.precioBruta : 0;
    },
    manoDeObraNeta() {
      if (!this.ventas[this.index]) return 0;
      let pManoObra = this.ventas[this.index].productos.find(p => p.CodigoBarra === "MANODEOBRA");
      return pManoObra ? pManoObra.precio : 0;
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

    // ---------- NUEVO: manejo de ventanas ----------
    anadirVenta() {
      this.ventas.push({ ...this.clonarVentaBase(), productos: [] });
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.$store.state.cart.productos = this.ventas[this.index].productos;
        this.getVendedor();
        this.loadClienteVarios();
        this.loadComisionista();
      });
    },
    clonarVentaBase() {
      // Clona profundamente preVenta para que cada pestaña tenga sus propios objetos anidados
      return JSON.parse(JSON.stringify(this.preVenta));
    },
    eliminarVentana() {
      if (this.ventas.length === 1) {
        window.location.reload();
        return;
      }
      this.ventas.splice(this.index, 1);
      this.index = this.index - 1;
    },
    setProducto(evt) {
      // Emitido por <productos-carrito> cuando agrega un producto nuevo
      let prods = this.ventas[this.index].productos;
      let i = prods.findIndex(v => v.IdProducto == evt.IdProducto);
      if (i >= 0) {
        prods.splice(i, 1, evt);
      } else {
        prods.unshift(evt);
      }
    },
    eliminarProducto(index) {
      this.ventas[this.index].productos.splice(index, 1);
    },
    // ------------------------------------------------

    mostrarEfectuarVenta() {
      let Perfil = sessionStorage.getItem("IdPerfil");
      if (Perfil == 3) {
        return false;
      }
      return true;
    },
    cargarMonto(tipo) {
      let venta = this.ventas[this.index];
      if (venta.pago && venta.pago.hasOwnProperty(tipo)) {
        Object.keys(venta.pago).forEach((key, idx) => {
          venta.pago[key].monto = key === tipo ? this.totalCartActivo : 0;
          if (key === tipo) {
            venta.idMetodoPago = idx + 1;
          }
        });
      } else {
        console.error(`El tipo de pago "${tipo}" no existe en venta.pago`);
      }
    },
    guardarVendedor(event) {
      let ref = `inputVendedor${this.index}`;
      this.$refs[ref][0].setValue(event);
      this.ventas[this.index].vendedor = { Usuario: event };
    },
    getEstadoSistema() {
      let hostUrl = sessionStorage.getItem("HostUrl");
      if (hostUrl != null) {
        this.axios
          .get(this.APIADMIN + "/api/sistema", { params: { Url: hostUrl } })
          .then(res => {
            if (res.data.success == true) {
              let info = res.data.data;
              this.pagosPendientes = info.length;
              this.fechaUltPago = info.slice(-1)[0].FechaVencimientoFormat || "";
            } else {
              this.pagosPendientes = 0;
            }
          })
          .catch(() => {
            this.pagosPendientes = 0;
          });
      } else {
        this.pagosPendientes = 0;
      }
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000000").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    loadComisionista() {
      this.axios.get(this.API + "comisionista/0000").then(res => {
        if (res.data.IdCliente) {
          this.setComisionista(res.data);
        }
      });
    },
    prevent(evt) {
      if (evt.target.tagName !== "TEXTAREA") {
        evt.preventDefault();
      }
    },
    selectPreOrden(orden) {
      this.ventas[this.index].IdPreOrden.push(orden);
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: { IdPreOrden: orden.IdPreOrden, TipoCajaBanco: 0 }
        })
        .then(res => {
          this.ventas[this.index].adelantosPreOrden = res.data;
          let suma = 0;
          res.data.map(ad => {
            if (ad.Tipo == 0) suma += ad.Importe;
          });
          this.ventas[this.index].adelanto = suma;
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
      const comp = this.$refs[ref][0];
      if (comp && comp.handleDoubleClick) comp.handleDoubleClick();
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
        almacen: { IdAlmacen: 1, Almacen: "HUANUCO" },
        puntoVenta: { IdDocVentaPuntoVenta: 1, PuntoVenta: "HUANUCO" },
        IdProforma: null,
        idMetodoPago: 0,
        vendedor: {},
        adelantosPreOrden: [],
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          yape: { IdMetodoPago: 4, monto: 0 }
        },
        IdPreOrden: [],
        CampoDireccion: "Direccion",
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        Garantia: 0,
        EsDelivery: false,
        nombreLlevar: "",
        DireccionEntrega: "",
        diasValidosProf: 1,
        ProformaSinPrecio: 0,
        isNota: false,
        totalCart: 0,
        adelanto: 0
      };
      this.bloquearInput = false;
      this.adelantosPreOrden = [];
      this.adelanto = 0;
      this.infoDelivery = {};
      this.idMetodoPago = 0;
      this.preVenta = JSON.parse(JSON.stringify(this.venta));
      this.ventas = [{ ...JSON.parse(JSON.stringify(this.venta)), productos: [] }];
      this.$store.state.cart.productos = this.ventas[0].productos;
    },
    setTipoVenta(obj) {
      let venta = this.ventas[this.index];
      venta.isNota = false;
      this.bloquearInput = false;
      venta.NroComprobanteModifica = "";
      venta.CodSunatModifica = "";
      venta.NotaIdMotivo = "";
      venta.NotaDescMotivo = "";
      let refDoc = `inputDocRef${this.index}`;
      this.$refs[refDoc] && this.$refs[refDoc][0] && this.$refs[refDoc][0].setValue("");

      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        venta.isNota = true;
      }
      this.tieneIgv = !!obj.TieneIgv;

      let refTipo = `inputTipoVenta${this.index}`;
      this.$refs[refTipo][0].setValue(obj.TipoDoc);
      venta.tipoVenta = obj;
      this.getNumeroVenta();
    },
    async setDocRef(obj) {
      let venta = this.ventas[this.index];
      venta.CodSunatModifica = obj.CodSunat;
      venta.NroComprobanteModifica = obj.NroComprobante;
      if (venta.tipoVenta.CodSunat == "07" || venta.tipoVenta.CodSunat == "08") {
        venta.Serie = venta.Serie.replace(/^./, obj.NroComprobante[0]);
      }
      try {
        let response = await this.axios.get(this.API + "ventas/solonumero", {
          params: { serie: venta.Serie, idTipoDoc: venta.tipoVenta.IdTipoDoc }
        });
        venta.Numero = response.data.numero;
      } catch (error) {
        console.log(error);
        this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
      }

      // Cargar producto del documento afectado (nota de credito)
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: venta.puntoVenta.IdDocVentaPuntoVenta
          }
        })
        .then(response => {
          this.axios
            .get(this.API + "cliente", { params: { idCliente: response.data[0].IdCliente } })
            .then(res2 => {
              if (res2.data) {
                this.setCliente(res2.data);
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(err => console.log(err));

          venta.productos = [];
          response.data.map(async item => {
            item.estadoPxP = false;
            let preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);
            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            let stock = await this.axios(`${this.API}productos/stock`, {
              params: { "idProductos[]": item.IdProducto, idAlmacen: venta.almacen.IdAlmacen }
            }).then(res => res.data);
            venta.productos.push({ ...item, stock: stock[0] ? stock[0].stock : 0 });
          });
          venta.NotaIdMotivo = null;
          this.bloquearInput = true;
        });
    },
    setMotivo(ev) {
      this.bloquearInput = ev == "01" || ev == "02" || ev == "06" || ev == null;
    },
    setAlmacen(obj) {
      this.ventas[this.index].almacen = obj;
      let ref = `inputAlmacen${this.index}`;
      this.$refs[ref][0].setValue(obj.Almacen);
    },
    loadAlmacen(id) {
      this.axios.get(this.API + "almacenes/id/" + id).then(res => {
        this.setAlmacen(res.data);
      });
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
      this.ventas[this.index].puntoVenta = obj;
      let ref = `inputPuntoVenta${this.index}`;
      this.$refs[ref][0].setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          let ref = `inputVendedor${this.index}`;
          this.$refs[ref][0].setValue(response.data.NombreUsuario);
          this.$refs[ref][0].$refs.input.disabled = true;
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);

          let refAlmacen = `inputAlmacen${this.index}`;
          if (
            sessionStorage.getItem("User").toUpperCase() === "NIKO" ||
            sessionStorage.getItem("User").toUpperCase() == "EDGAR"
          ) {
            this.$refs[refAlmacen][0].$refs.input.disabled = false;
          } else {
            this.$refs[refAlmacen][0].$refs.input.disabled = true;
          }
          let refPV = `inputPuntoVenta${this.index}`;
          this.$refs[refPV][0].$refs.input.disabled = true;
          this.getTipoDoc(response.data.IdTipoDoc);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.ventas[this.index].vendedor = obj;
    },
    setComisionista(obj) {
      this.ventas[this.index].comisionista = typeof obj === "object" ? obj : {};
    },
    setCliente(obj, dni = false) {
      let ref = `inputCliente${this.index}`;
      let params = dni ? { dni } : { idCliente: obj.IdCliente || obj };

      if (typeof obj === "object") {
        this.$refs[ref][0].setValue(obj.Cliente);
        this.ventas[this.index].cliente = obj;
        this.ventas[this.index].CampoDireccion = obj.Direccion;
      } else {
        this.axios
          .get(this.API + "cliente", { params })
          .then(response => {
            if (response.data) {
              this.$refs[ref][0].setValue(response.data.Cliente);
              this.ventas[this.index].cliente = response.data;
              this.ventas[this.index].CampoDireccion = response.data.Direccion;
            } else {
              this.$toasted.show("No se encontro al cliente");
            }
          })
          .catch(error => console.log(error));
      }
    },
    onSetProducto(obj, cant, precio) {
      if (cant && precio) {
        obj.cantidad = cant;
        let preciosPorProducto = JSON.parse(obj.PreciosPorProducto).find(
          e => e.cantidad === cant && e.precio === precio
        );
        obj.precio = preciosPorProducto.precio || 0;
        return obj;
      }
      obj.cantidad = 1;
      obj.precio = obj.PrecioContado || 0;
      return obj;
    },
    onAddProducto() {
      this.actualizarDescuento();
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
      let venta = this.ventas[this.index];
      if (parseFloat(val) > venta.cliente.Puntos) {
        this.puntosAplicados = venta.cliente.Puntos;
      }
      this.axios
        .get(this.API + "ventas/descuento/" + this.puntosAplicados)
        .then(response => {
          this.descuentoPuntos = response.data;
          this.aplicarDescuentoAVenta(response.data.descuento);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    aplicarDescuentoGeneral(val) {
      this.puntosAplicados = 0;
      this.aplicarDescuentoAVenta(val);
    },
    // Aplica el descuento sobre los productos de la venta ACTIVA (no sobre el store global)
    aplicarDescuentoAVenta(descuento) {
      let productos = this.ventas[this.index].productos;
      let cantidadTotal = productos.reduce((sum, p) => sum + parseFloat(p.cantidad || 0), 0);
      if (!cantidadTotal) return;
      productos.forEach(prod => {
        prod.descuento = ((descuento / cantidadTotal) * prod.cantidad).toFixed(2);
      });
    },
    getNumeroVenta() {
      let venta = this.ventas[this.index];
      this.axios
        .get(this.API + "ventas/numero", {
          params: {
            idPuntoVenta: venta.puntoVenta.IdDocVentaPuntoVenta,
            idTipoDoc: venta.tipoVenta.IdTipoDoc
          }
        })
        .then(response => {
          venta.Serie = response.data.serie;
          venta.Numero = response.data.numero;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    async setProforma(proforma) {
      let idAlmacenRef = this.ventas[this.index].almacen.IdAlmacen;

      this.axios
        .get(this.API + "proformas/detalle", { params: { idProforma: proforma.IdProforma } })
        .then(async response => {
          let Vendedor = response.data[0] ? response.data[0].UsuarioReg : "";
          let productos = [];
          for (let item of response.data) {
            item.descuento = item.Descuento;
            item.cantidad = item.Cantidad;
            item.precio = item.Precio;
            item.Descripcion = item.Descripcion == '' ? '' : item.Descripcion;
            item.PorcentajeUtilidad = item.PrecioCosto > 0
              ? this.$options.filters.redondear(((item.precio - item.PrecioCosto) / item.PrecioCosto) * 100, 2)
              : 0;
            let stock = await this.axios(`${this.API}productos/stock`, {
              params: {
                "idProductos[]": item.IdProducto,
                idAlmacen: idAlmacenRef
              }
            }).then(res => res.data);
            item.stock = stock[0] ? stock[0].stock : 0;
            productos.push(item);
          }

          // Crear una NUEVA ventana/pestaña para esta proforma, en vez de sobreescribir la actual
          let nuevaVenta = {
            ...this.clonarVentaBase(),
            productos: productos,
            IdProforma: proforma.IdProforma
          };
          this.ventas.push(nuevaVenta);

          this.$nextTick(() => {
            this.index = this.ventas.length - 1;
            this.$store.state.cart.productos = this.ventas[this.index].productos;
            this.getVendedor();
            this.loadClienteVarios();
            this.loadComisionista();

            if (Vendedor) this.guardarVendedor(Vendedor);
            if (proforma.DniRuc) {
              this.setCliente(false, proforma.DniRuc);
            }
          });
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error al cargar la proforma");
        });
    },
    limpiarDespuesVenta() {
      this.inProgress = false;
      this.tieneIgv = false;
      this.eliminarVentana();
    },
    async guardarProforma() {
      this.venta = this.ventas[this.index];
      this.inProgress = true;

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para crear la proforma");
        this.inProgress = false;
        return;
      }
      if (this.venta.diasValidosProf == 0 || this.venta.diasValidosProf == "") {
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
      if (this.venta.ProformaSinPrecio == 1) {
        this.venta.productos.map(p => { p.disabledProf = 1; });
      }

      this.venta.productos.forEach(p => {
        p.indicacion = p.indicacion || '';
        p.Descripcion = p.Descripcion || '';
      });

      await this.axios
        .post(this.API + "proformas", this.venta)
        .then(response => {
          this.$toasted.success("Proforma Guardada con exito");
          window.open("/nuevo/index.html#/reportes/proforma/" + response.data.insertId, "_blank");
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
      this.inProgress = false;
      this.limpiarDespuesVenta();
    },
    async guardarVenta(evt) {
      if (evt && evt.preventDefault) evt.preventDefault();
      this.venta = this.ventas[this.index];

      this.inProgress = true;
      let totalPagos = 0;
      totalPagos += isNaN(parseFloat(this.venta.pago.efectivo.monto)) ? 0 : parseFloat(this.venta.pago.efectivo.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.visa.monto)) ? 0 : parseFloat(this.venta.pago.visa.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.mastercard.monto)) ? 0 : parseFloat(this.venta.pago.mastercard.monto);
      totalPagos += isNaN(parseFloat(this.venta.pago.yape.monto)) ? 0 : parseFloat(this.venta.pago.yape.monto);

      if ((isNaN(parseFloat(this.venta.pago.efectivo.monto)) ? 0 : parseFloat(this.venta.pago.efectivo.monto)).toFixed(2) < 0) {
        this.$toasted.show("El metodo de pago efectivo no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if ((isNaN(parseFloat(this.venta.pago.visa.monto)) ? 0 : parseFloat(this.venta.pago.visa.monto)).toFixed(2) < 0) {
        this.$toasted.show("El metodo de pago Tarjeta no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if ((isNaN(parseFloat(this.venta.pago.mastercard.monto)) ? 0 : parseFloat(this.venta.pago.mastercard.monto)).toFixed(2) < 0) {
        this.$toasted.show("El metodo de pago Transferencia no puede ser negativo");
        this.inProgress = false;
        return;
      }
      if ((isNaN(parseFloat(this.venta.pago.yape.monto)) ? 0 : parseFloat(this.venta.pago.yape.monto)).toFixed(2) < 0) {
        this.$toasted.show("El metodo de pago YAPE / PLIN no puede ser negativo");
        this.inProgress = false;
        return;
      }

      let totalCartVenta = this.venta.totalCart || this.totalCartActivo;
      if (!this.venta.EsCredito) {
        if (parseFloat(totalCartVenta).toFixed(2) != parseFloat(totalPagos).toFixed(2)) {
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

      var esNumerico = Number.isInteger(parseInt(this.venta.cliente.DniRuc));
      if (!esNumerico) {
        this.$toasted.show("El RUC debe ser numérico, editar cliente.");
        this.inProgress = false;
        return;
      }

      var esEmpresa = this.venta.cliente.DniRuc.length > 9;
      if (!esEmpresa && this.venta.tipoVenta.CodSunat == "01") {
        this.$toasted.show("Una factura debe ser emitida para una empresa, no un usuario común.");
        this.inProgress = false;
        return;
      }

      if (this.venta.tipoVenta.CodSunat == "07" || this.venta.tipoVenta.CodSunat == "08") {
        if (!this.venta.CodSunatModifica || !this.venta.NroComprobanteModifica) {
          this.$toasted.show("Debe seleccionar un Documento de Referencia...");
          this.inProgress = false;
          return;
        }
        if (!this.venta.NotaIdMotivo) {
          this.$toasted.show("Debe seleccionar un Motivo");
          this.inProgress = false;
          return;
        }
        this.venta.NotaDescMotivo = this.itemsMotivoNota.filter(item => item.value == this.venta.NotaIdMotivo)[0].text;
      }

      if (!this.venta.productos.length) {
        this.$toasted.show("No hay productos para vender");
        this.inProgress = false;
        return;
      }

      let sinStock = 0;
      let totalCero = 0;
      let menosUtilidad = 0;
      this.venta.productos.forEach(pro => {
        if (
          pro.CodigoBarra !== "COSTODELIVERY" &&
          pro.CodigoBarra !== "MANODEOBRA" &&
          (pro.stock <= 0 || parseFloat(pro.cantidad) > pro.stock)
        ) {
          sinStock += 1;
        }
        if (parseFloat(pro.total) <= 0) {
          totalCero += 1;
        }
        if (parseFloat(pro.precio) - pro.PrecioCosto < 0 && pro.CodigoBarra !== "COSTODELIVERY" && pro.CodigoBarra !== "MANODEOBRA") {
          menosUtilidad += 1;
        }
      });

      if (menosUtilidad >= 1 && this.venta.tipoVenta.CodSunat != "07") {
        this.$toasted.error("No se puede vender un producto con ganancia negativa");
        this.inProgress = false;
        return;
      }
      if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != "07") {
        this.$toasted.error("No se puede vender un producto sin stock");
        this.inProgress = false;
        return;
      }
      if (totalCero >= 1) {
        this.$toasted.error("No se puede enviar un producto en S/. 0.00");
        this.inProgress = false;
        return;
      }
      if (totalCartVenta <= 0) {
        this.$toasted.show("La venta en S/. 0.00 no está permitido");
        this.inProgress = false;
        return;
      }

    this.venta.TipoPago =
      this.venta.idMetodoPago == 1 ? "Efectivo" :
      this.venta.idMetodoPago == 2 ? "Tarjeta" :
      this.venta.idMetodoPago == 3 ? "Transferencia" :
      this.venta.idMetodoPago == 4 ? "Yape/ Plin" : "Contado";
      this.venta.puntosAplicados = this.puntosAplicados || 0;
      this.venta.totalCart = totalCartVenta;
      let refCarrito = `carrito${this.index}`;
      this.venta.PagoCon = this.$refs[refCarrito][0].pagocon;
      this.venta.adelantosPreOrden = this.adelantosPreOrden;
      this.venta.IdPedido = this.$route.query.idPedido || null;
      this.venta.nombreLlevar = (this.venta.nombreLlevar || "").toUpperCase();
      this.venta.DireccionEntrega = this.venta.EsDelivery ? (this.infoDelivery || {}).Direccion || "" : "";

      this.venta.productos.forEach(p => {
        p.indicacion = p.indicacion || '';
        p.Descripcion = p.Descripcion || '';
      });

      await this.axios
        .post(this.API + "ventas", this.venta)
        .then(response => {
          this.$toasted.success("Venta realizada con exito");
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          window.open("/imprimir/index.php?IdDocVenta=" + response.data.insertId, "_blank");
          setTimeout(() => {
            this.eliminarVentana();
          }, 500);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    eliminarCliente() {
      let ref = `inputCliente${this.index}`;
      this.$refs[ref] && this.$refs[ref][0] && this.$refs[ref][0].setValue("");
      this.ventas[this.index].cliente = {};
    },
    eliminarComisionista() {
      this.ventas[this.index].comisionista = {};
    },
    async onPagarDelivery(obj) {
      this.$route.query.idPedido = obj.IdPedido;
      if (!this.$route.query.idPedido) return;

      let venta = this.clonarVentaBase();
      this.infoDelivery = obj;
      venta.EsDelivery = true;

      let costoDelivery = parseFloat(obj.PrecioDelivery || 0).toFixed(2);
      let productosDelivery = JSON.parse(obj.Productos || "[]").map(item => ({
        ...item,
        cantidad: item.Cantidad,
        precio: item.Precio,
        indicacion: item.Indicacion
      }));

      if (costoDelivery > 0) {
        try {
          let response = await this.axios.get(this.API + "costo/delivery");
          if (response.data) {
            let costoObj = {
              ...response.data,
              precio: costoDelivery,
              cantidad: 1,
              EsCostoDelivery: 1,
              descuento: 0,
              indicacion: ""
            };
            productosDelivery.unshift(costoObj);
          } else {
            this.$toasted.show("PRIMERO CREE EL PRODUCTO COSTO DE DELIVERY CON EL CODIGO DE BARRAS 'COSTODELIVERY'");
            this.anularPagoDelivery();
            return;
          }
        } catch (error) {
          console.error(error);
          this.$toasted.show("HUBO UN ERROR");
          return;
        }
      }

      venta.nombreLlevar = obj.Cliente || "SIN NOMBRE";
      venta.DireccionEntrega = obj.Direccion;
      venta.productos = productosDelivery;

      this.ventas.push(venta);
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.$store.state.cart.productos = this.ventas[this.index].productos;
        this.getVendedor();
        this.$toasted.success("PRODUCTOS DEL DELIVERY AGREGADOS");
      });
    },
    anularPagoDelivery() {
      this.ventas[this.index].EsDelivery = false;
      this.infoDelivery = {};
      let refCarrito = `carrito${this.index}`;
      if (this.$refs[refCarrito] && this.$refs[refCarrito][0]) this.$refs[refCarrito][0].pagocon = "";
      this.loadClienteVarios();
      this.loadComisionista();
      this.ventas[this.index].nombreLlevar = "";
      this.ventas[this.index].DireccionEntrega = "";
      this.$router.replace("/ventas");
    },
    escucharPedidos() {
      this.sockets.subscribe("nuevo-pedido", data => {
        if (this.$refs.deliveryModal && this.$refs.deliveryModal.show) {
          this.$toasted.success("Nuevo Pedido de" + "<strong>" + data.nombre + "</strong>");
          this.$refs.deliveryModal.getPedidos();
        } else {
          if (this.$refs.deliveryModal) this.$refs.deliveryModal.getPedidos();
          this.$swal({
            position: "top-end",
            icon: "info",
            target: document.getElementById("conten"),
            title: "Pedido de Delivery de: <br> " + "<strong>" + data.nombre + "</strong>",
            showConfirmButton: true,
            confirmButtonText: "Mostrar Pedidos",
            confirmButtonColor: "#3880ff"
          }).then(result => {
            if (result.isConfirmed) {
              if (this.$refs.deliveryModal) this.$refs.deliveryModal.show = true;
            }
          });
        }
      });
    }
  },
  components: {
    ProductosCarrito,
    ClientesForm,
    Movimiento,
    ConfigVenta,
    PagoDeuda,
    PreOrdenLista,
    Proformas,
    DeliveryModal
  }
};
</script>
<style scoped>
.alert { margin-bottom: 0; }
.puntos { font-size: 52px; }
.big-text { font-size: 30px; }
</style>
<style>
.holder {
  color: #721c24;
  background-color: #f8d7da;
  border-color: #f5c6cb;
  clear: both;
  display: inline-block;
  overflow: hidden;
}
.news { animation: slide 20s linear infinite; white-space: pre; }
@keyframes slide {
  0% { transform: translatex(100vw); }
  100% { transform: translatex(-120vw); }
}
@media only screen and (max-width: 700px) {
  .ocularMovil { display: none !important; }
}
@media only screen and (min-width: 777px) and (max-width: 1800px) {
  .ancho { width: calc(100% - 300px); }
}
@media only screen and (max-width: 750px) {
  .ocularMovil2 { display: none !important; }
  .mostrarSerie { display: inline !important; }
}
</style>