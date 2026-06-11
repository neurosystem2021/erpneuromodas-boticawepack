<template>
  <div style="background-color: #CFEAFF;">
    <Menu v-if="false"></Menu>
    <b-container class="bv-example-row" id="conten" fluid>

      <!-- ══════════════════════════════════════════
                 BOTONES GLOBALES (encima de los tabs)
            ══════════════════════════════════════════ -->
      <!-- <div class="float-right mb-2">
        <b-button-group size="sm">
          <pago-deuda></pago-deuda>
        </b-button-group>
        <b-button-group size="sm">
          <proformas></proformas>
        </b-button-group>
        <b-button-group size="sm">
          <pre-orden-lista :idAlmacen="ventas[ventaActiva].almacen.IdAlmacen" @onSelectPreOrden="setCliente">
          </pre-orden-lista>
        </b-button-group>
        <b-button-group size="sm">
          <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
        </b-button-group>
      </div> -->

      <br />

      <!-- ══════════════════════════════════════════
                 TABS DE VENTAS
            ══════════════════════════════════════════ -->
      <b-card no-body style="background-color: #E3F2FD;" >
        <b-tabs v-model="ventaActiva" pills card content-class="mt-0">
          <b-tab v-for="(venta, index) in ventas" :key="index" :title="`Venta ${index + 1}`">
            <b-form @submit="(evt) => guardarVenta(evt, index)" @keydown.enter="evt => evt.preventDefault()">
              <b-row>
                <!-- ── Columna izquierda: tipo venta / almacén / cliente ── -->
                <b-col md="7">
                  <b-row>
                    <b-col md="4">
                      <b-form-group>
                        <label>Tipo de Venta:</label>
                        <autocomplete required="required" :ref="`inputTipoVenta${index}`" :url="API + 'ventas/tipos'"
                          anchor="TipoDoc" initValue="BOLETA ELEC." label="writer" placeholder=""
                          :on-select="(obj) => setTipoVenta(obj, index)"
                          :onFocus="focusAutocomplete.bind(null, `inputTipoVenta${index}`)"
                          :classes="{ input: 'form-control' }">
                        </autocomplete>
                      </b-form-group>
                      <b-form-group v-if="isNota[index]">
                        <label>Doc. Referencia:</label>
                        <autocomplete required="required" :ref="`inputDocRef${index}`"
                          :url="API + 'ventas/lista/' + venta.puntoVenta.IdDocVentaPuntoVenta" anchor="NroComprobante"
                          initValue="" label="writer" placeholder="" :on-select="(obj) => setDocRef(obj, index)"
                          :onFocus="focusAutocomplete.bind(null, `inputDocRef${index}`)"
                          :classes="{ input: 'form-control' }">
                        </autocomplete>
                      </b-form-group>
                    </b-col>

                    <b-col md="4">
                      <b-form-group>
                        <label>Almacen:</label>
                        <autocomplete disabled required="required" :ref="`inputAlmacen${index}`"
                          :url="API + 'almacenes'" anchor="Almacen" label="writer" placeholder=""
                          :on-select="(obj) => setAlmacen(obj, index)"
                          :onFocus="focusAutocomplete.bind(null, `inputAlmacen${index}`)"
                          :classes="{ input: 'form-control' }">
                        </autocomplete>
                      </b-form-group>
                      <b-form-group v-if="isNota[index]">
                        <label>Motivo:</label>
                        <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota"
                          v-on:change="setMotivo($event, index)" class="mb-3" size="md" required />
                      </b-form-group>
                    </b-col>

                    <b-col md="4">
                      <b-form-group>
                        <label>Punto de Venta:</label>
                        <autocomplete required="required" :ref="`inputPuntoVenta${index}`" :url="API + 'ventas/puntos'"
                          anchor="PuntoVenta" label="writer" placeholder=""
                          :on-select="(obj) => setPuntoVenta(obj, index)"
                          :onFocus="focusAutocomplete.bind(null, `inputPuntoVenta${index}`)"
                          :classes="{ input: 'form-control' }">
                        </autocomplete>
                      </b-form-group>
                    </b-col>

                    <!-- Cliente -->
                    <b-col md="12">
                      <b-form-group>
                        <label>Cliente:</label>
                        <b-input-group>
                          <clientes-form txtButton="Editar"
                            v-if="venta.cliente.IdCliente && venta.cliente.DniRuc != '00000000'" idModal="editCliente"
                            @onAddCliente="(obj) => setCliente(obj, false, index)" :editCliente="venta.cliente">
                          </clientes-form>
                          <autocomplete required="required" :ref="`inputCliente${index}`" :url="API + 'clientes'"
                            anchor="ClienteDniRuc" label="writer" placeholder="" :onInput="(val) => newDniRuc = val"
                            :on-select="(obj) => setCliente(obj, false, index)"
                            :onFocus="focusAutocomplete.bind(null, `inputCliente${index}`)"
                            :classes="{ input: 'form-control' }">
                          </autocomplete>
                          <input v-model="dni" @keydown.enter.prevent="(e) => loadClienteBarcode(e, index)" type="text"
                            class="form-control" placeholder="Codigo de Barra" />
                          <b-input-group-append>
                            <clientes-form :ruc="newDniRuc" @onAddCliente="(obj) => setCliente(obj, false, index)">
                            </clientes-form>
                          </b-input-group-append>
                        </b-input-group>

                        <b-form-select v-if="venta.cliente.IdCliente" v-model="venta.CampoDireccion" class="mb-3">
                          <option :value="venta.cliente.Direccion">{{ venta.cliente.Direccion }}</option>
                          <option :value="venta.cliente.Direccion2">{{ venta.cliente.Direccion2 }}</option>
                          <option :value="venta.cliente.Direccion3">{{ venta.cliente.Direccion3 }}</option>
                        </b-form-select>

                        <b-input-group v-if="venta.cliente.DniRuc == '00000000'">
                          <b-form-checkbox :disabled="bloquearInput[index] ? true : false"
                            v-model="venta.EsOrganizacion" :value="1" :unchecked-value="0">
                            Es Organización
                          </b-form-checkbox>
                          <input v-model="venta.NombreOrganizacion" v-if="venta.EsOrganizacion" type="text"
                            style="text-transform:uppercase;" class="form-control" required
                            placeholder="Nombre de la Organización" />
                        </b-input-group>
                      </b-form-group>
                    </b-col>
                  </b-row>
                </b-col>

                <!-- ── Columna central: puntos ── -->
                <b-col md="3">
                  <b-form-group class="text-center" v-if="venta.cliente.Cliente">
                    <div class="puntos">
                      {{ (venta.cliente.Puntos || 0) | redondeoMenor }}
                    </div>
                    Puntos del cliente
                  </b-form-group>
                  <b-input-group prepend="Puntos" :append="venta.descuentoPuntos.descuento | redondear"
                    v-if="venta.cliente.Puntos && venta.descuentoGeneral == 0">
                    <b-form-input v-model="venta.puntosAplicados" min="0" :max="venta.cliente.Puntos"
                      @input="(val) => aplicarPuntos(val, index)" type="number">
                    </b-form-input>
                  </b-input-group>
                  <b-form-text v-if="venta.descuentoPuntos.ValorPunto">
                    1 Punto equivale a: S/ {{ venta.descuentoPuntos.ValorPunto | redondear }}
                  </b-form-text>
                </b-col>

                <!-- ── Columna derecha: serie/número/vendedor ── -->
                <b-col md="2">
                  <b-form-group class="text-center">
                    <div class="big-text">
                      {{ venta.Serie }} - <b>{{ venta.Numero }}</b>
                    </div>
                    {{ venta.FechaDoc }}
                  </b-form-group>
                  <b-form-group>
                    <label>Vendedor:</label>
                    <autocomplete required="required" :ref="`inputVendedor${index}`" :url="API + 'usuarios'"
                      anchor="NombreUsuario" label="writer" :on-select="(obj) => setVendedorManual(obj, index)"
                      :onFocus="focusAutocomplete.bind(null, `inputVendedor${index}`)"
                      :classes="{ input: 'form-control' }">
                    </autocomplete>
                  </b-form-group>
                </b-col>
              </b-row>
              <!-- ── Carrito ── -->
              <b-row>
                <b-col>
                  <productos-carrito :productos-locales="venta.productos"
                    @onInputCantidad="() => actualizarDescuento(index)"
                    @onAddProducto="(prods) => onAddProducto(index, prods)"
                    @onRemoveProducto="(prods) => onRemoveProducto(index, prods)" :igvTotal="tieneIgv[index]"
                    :setPrecio="true" :ref="`carrito${index}`" :showButton="false" :bloquearInput="bloquearInput[index]"
                    :fields="carrito.fields" :onSetProducto="onSetProducto">
                    
                    <template slot="pagar">
                      <div class="d-flex justify-content-center">
                        <b-button class="btnAction" type="submit" style="max-width: 300px; width: 100%;" :disabled="inProgress" variant="success" size="lg"><i class="fas fa-shopping-cart"></i>
                          Efectuar Venta
                        </b-button>
                      </div>
                    </template>

                    <template slot="acciones">
                      <div class="d-flex flex-column">
                        <b-button class="btnAction" type="button" :disabled="inProgress" variant="warning" size="sm"
                          @click="anadirVentana">
                          + Nueva Venta
                        </b-button>
                        <b-button class="btnAction" v-if="ventas.length > 1" type="button" variant="danger" size="sm"
                          @click="eliminarVentana(index)">
                          Cancelar Venta {{ index + 1 }}
                        </b-button>
                        <b-button class="btnAction" type="button" :disabled="inProgress" variant="primary" size="md"
                          @click="guardarProforma(index)">
                          Crear Proforma
                        </b-button>
                          <pago-deuda></pago-deuda>
                          <Movimiento></Movimiento>
                          <proformas></proformas>
                          <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
                          <pre-orden-lista :idAlmacen="ventas[ventaActiva].almacen.IdAlmacen" @onSelectPreOrden="setCliente"></pre-orden-lista>
                        <b-button v-if="ultimasVentas[index]" class="btnAction" target="_blank" variant="info" size="lg"
                          :href="'/imprimir/index.php?IdDocVenta=' + ultimasVentas[index]">
                          Imprimir Comprobante
                        </b-button>
                      </div>
                    </template>

                    <template slot="operaciones">
                      <table class="table b-table border table-footer">
                        <tbody>
                          <tr>
                            <td class="text-center">Opciones</td>
                          </tr>
                          <tr v-if="venta.puntosAplicados == 0">
                            <td>
                              <b-input-group prepend="S/" append="Descuento">
                                <input v-model="venta.descuentoGeneral" :disabled="bloquearInput[index] ? true : false"
                                  min="0" value="0" @input="(val) => aplicarDescuentoGeneral(val.target.value, index)"
                                  type="number" class="form-control" step="0.01" />
                              </b-input-group>
                              <b-form-text>
                                Descuento general a toda la venta.
                              </b-form-text>
                            </td>
                          </tr>
                          <tr>
                            <td>
                              <b-form-checkbox v-model="venta.EsCredito"
                                :disabled="bloquearInput[index] ? true : false || isNota[index]" :value="1"
                                :unchecked-value="0">
                                Venta a credito
                              </b-form-checkbox>
                              <b-form-input v-if="venta.EsCredito" v-model="venta.FechaCredito"
                                :disabled="bloquearInput[index] ? true : false || isNota[index]" type="date">
                              </b-form-input>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </template>

                    <template slot="metodopago" v-if="!venta.EsCredito">
                      <b-row>
                        <!-- ── Métodos de pago ── -->
                        <b-col md="12" class="text-center">
                          <table class="table b-table border table-footer table-sm mx-auto" style="max-width:600px">
                            <thead>
                              <tr>
                                <th colspan="4">Método de Pago</th>
                              </tr>
                            </thead>
                            <tbody>
                              <!-- EFECTIVO -->
                              <tr>
                                <td><b>Efectivo</b></td>
                                <td class="text-center">
                                  <b-button variant="primary" size="sm" :disabled="bloquearInput[index] ? true : false"
                                    @click="asignarTotalA('efectivo', index)">
                                    <i class="fas fa-check"></i>
                                  </b-button>
                                </td>
                                <td>
                                  <b-form-input v-model="venta.pago.efectivo.descripcion"
                                    :disabled="bloquearInput[index] ? true : false" type="text"
                                    placeholder="Descripción">
                                  </b-form-input>
                                </td>
                                <td>
                                  <input v-model="venta.pago.efectivo.monto"
                                    :disabled="bloquearInput[index] ? true : false" min="0" step="0.01" type="number"
                                    placeholder="S/." class="form-control" />
                                </td>
                              </tr>
                              <!-- TARJETA -->
                              <tr>
                                <td><b>Tarjeta</b></td>
                                <td class="text-center">
                                  <b-button variant="primary" size="sm" :disabled="bloquearInput[index] ? true : false"
                                    @click="asignarTotalA('visa', index)">
                                    <i class="fas fa-check"></i>
                                  </b-button>
                                </td>
                                <td>
                                  <b-form-input v-model="venta.pago.visa.descripcion"
                                    :disabled="bloquearInput[index] ? true : false" type="text"
                                    placeholder="Descripción">
                                  </b-form-input>
                                </td>
                                <td>
                                  <input class="form-control" v-model="venta.pago.visa.monto"
                                    :disabled="bloquearInput[index] ? true : false" min="0" step="0.01" type="number"
                                    placeholder="S/." @input="recalcularEfectivo(index, $event.target.value, 'visa')" />
                                </td>
                              </tr>
                              <!-- TRANSFERENCIA -->
                              <tr>
                                <td><b>Transferencia</b></td>
                                <td class="text-center">
                                  <b-button variant="primary" size="sm" :disabled="bloquearInput[index] ? true : false"
                                    @click="asignarTotalA('mastercard', index)">
                                    <i class="fas fa-check"></i>
                                  </b-button>
                                </td>
                                <td>
                                  <b-form-input v-model="venta.pago.mastercard.descripcion"
                                    :disabled="bloquearInput[index] ? true : false" type="text"
                                    placeholder="Descripción">
                                  </b-form-input>
                                </td>
                                <td>
                                  <input class="form-control" v-model="venta.pago.mastercard.monto"
                                    :disabled="bloquearInput[index] ? true : false" min="0" step="0.01" type="number"
                                    placeholder="S/."
                                    @input="recalcularEfectivo(index, $event.target.value, 'mastercard')" />
                                </td>
                              </tr>
                              <!-- YAPE/PLIN -->
                              <tr>
                                <td><b> Yape/Plin </b></td>
                                <td class="text-center">
                                  <b-button variant="primary" size="sm" :disabled="bloquearInput[index] ? true : false"
                                    @click="asignarTotalA('yape', index)">
                                    <i class="fas fa-check"></i>
                                  </b-button>
                                </td>
                                <td>
                                  <b-form-input v-model="venta.pago.yape.descripcion"
                                    :disabled="bloquearInput[index] ? true : false" type="text"
                                    placeholder="Descripción">
                                  </b-form-input>
                                </td>
                                <td>
                                  <input class="form-control" v-model="venta.pago.yape.monto"
                                    :disabled="bloquearInput[index] ? true : false" min="0" step="0.01" type="number"
                                    placeholder="S/." @input="recalcularEfectivo(index, $event.target.value, 'yape')" />
                                </td>
                              </tr>
                            </tbody>
                          </table>
                        </b-col>
                      </b-row>
                    </template>
                    
                  </productos-carrito>
                </b-col>
              </b-row>
            </b-form>

          </b-tab>
        </b-tabs>
      </b-card>

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
import Movimiento from '@/components/Movimiento'
import PreOrdenLista from '@/components/PreOrdenLista.vue'
import Proformas from '@/components/ProformaLista.vue'
import Vue from 'vue'

export default {
  // ─────────────────────────────────────────────────────────
  //  LIFECYCLE
  // ─────────────────────────────────────────────────────────
  beforeMount() {
    this.loadClienteVarios(0)
  },
  mounted() {
    this.getVendedor(null, 0)
  },

  // ─────────────────────────────────────────────────────────
  //  DATA
  // ─────────────────────────────────────────────────────────
  data() {
    return {
      dni: '',
      newDniRuc: '',
      inProgress: false,
      API: config.API,

      /** índice del tab activo */
      ventaActiva: 0,

      /**
       * Array de objetos venta — uno por tab.
       * Cada venta tiene su propio estado independiente.
       */
      ventas: [this.crearVenta()],

      /** Guarda el último IdDocVenta impreso por tab */
      ultimasVentas: [null],

      /** Estado de "es nota" por tab */
      isNota: [false],

      /** Estado de "tiene IGV" por tab */
      tieneIgv: [false],

      /** Estado de bloqueo de inputs por tab */
      bloquearInput: [false],

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
          { key: 'stock', label: 'Stock', variant: 'info', class: 'text-center' },
          { key: 'Producto' },
          { key: 'ProductoModelo', label: 'Modelo' },
          { key: 'ProductoMarca', label: 'Marca' },
          { key: 'ProductoTalla', label: 'Talla' },
          { key: 'Genero' },
          { key: 'cantidad' },
          { key: 'precio', label: 'Precio' },
          { key: 'subtotal', label: 'SubTotal', variant: 'success', class: 'text-center' },
          { key: 'descuento', label: 'Descuento' },
          { key: 'total', label: 'Total', variant: 'danger', class: 'text-center' },
          { key: 'acciones', label: '' }
        ]
      }
    }
  },

  // ─────────────────────────────────────────────────────────
  //  WATCH
  // ─────────────────────────────────────────────────────────
  watch: {
    // Cuando el store Vuex cambia (agrega/quita producto),
    // sincronizar el efectivo del tab activo automáticamente.
    totalCart(nuevoTotal) {
      this.$nextTick(() => {
        this.sincronizarEfectivoConTotal(this.ventaActiva)
      })
    }
  },

  // ─────────────────────────────────────────────────────────
  //  COMPUTED
  // ─────────────────────────────────────────────────────────
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    })
  },

  // ─────────────────────────────────────────────────────────
  //  METHODS
  // ─────────────────────────────────────────────────────────
  methods: {
    ...mapActions({
      aplicarDescuento: 'aplicarDescuentoGeneral',
      limpiarCart: 'limpiarCarrito',
      obtenerStok: 'obtenerStock',
      add: 'agregarProducto'
    }),

    // ── Factoría: crea un objeto venta vacío ──────────────
    crearVenta() {
      return {
        productos: [],
        EsCredito: 0,
        Serie: '',
        Numero: '',
        cliente: {},
        puntosAplicados: 0,
        descuentoPuntos: {},
        descuentoGeneral: 0,
        pagocon: 0,
        tipoVenta: {
          IdTipoDoc: 7,
          TipoDoc: 'BOLETA ELEC.',
          LimitItems: 8,
          CodSunat: '03'
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
        FechaCredito: this.$moment().format('YYYY-MM-DD'),
        FechaDoc: this.$moment().format('YYYY-MM-DDTH:mm:ss'),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0, descripcion: '' },
          visa: { IdMetodoPago: 2, monto: 0, descripcion: '' },
          mastercard: { IdMetodoPago: 3, monto: 0, descripcion: '' },
          yape: { IdMetodoPago: 4, monto: 0, descripcion: '' }
        },
        CampoDireccion: '',
        EsOrganizacion: 0,
        NombreOrganizacion: '',
        NroComprobanteModifica: '',
        CodSunatModifica: '',
        NotaIdMotivo: '',
        NotaDescMotivo: ''
      }
    },

    // ── Cálculos de totales por tab ───────────────────────
    // Lee del carrito hijo; si no está disponible, usa el store Vuex.
    calcularSubTotal(index) {
      const prods = this.ventas[index].productos  // ← usar local
      if (!prods || !prods.length) return '0.00'
      return parseFloat(
        prods.reduce((acc, p) => acc + parseFloat((p.cantidad || 0) * (p.precio || 0)), 0)
      ).toFixed(2)
    },

    calcularDescuento(index) {
      const prods = this.ventas[index].productos  // ← usar local
      if (!prods || !prods.length) return '0.00'
      return parseFloat(
        prods.reduce((acc, p) => acc + parseFloat(p.descuento || 0), 0)
      ).toFixed(2)
    },

    calcularTotal(index) {
      return parseFloat(
        parseFloat(this.calcularSubTotal(index)) - parseFloat(this.calcularDescuento(index))
      ).toFixed(2)
    },

    calcularVuelto(index) {
      const pagocon = parseFloat(this.ventas[index].pagocon) || 0
      const total = parseFloat(this.calcularTotal(index)) || 0
      return parseFloat(pagocon - total).toFixed(2)
    },

    // ── Obtiene el totalCart del carrito de un tab ────────
    getTotalCart(index) {
      return parseFloat(this.calcularTotal(index))
    },

    // ── Recalcular efectivo cuando cambia tarjeta/transferencia ──
    recalcularEfectivo(index, nuevoValor, metodo) {
      const venta = this.ventas[index]
      const total = parseFloat(this.calcularTotal(index)) || 0
      const visa = metodo === 'visa' ? (parseFloat(nuevoValor) || 0) : (parseFloat(venta.pago.visa.monto) || 0)
      const mastercard = metodo === 'mastercard' ? (parseFloat(nuevoValor) || 0) : (parseFloat(venta.pago.mastercard.monto) || 0)
      const yape = metodo === 'yape' ? (parseFloat(nuevoValor) || 0) : (parseFloat(venta.pago.yape.monto) || 0)
      const efectivo = parseFloat((total - visa - mastercard - yape).toFixed(2))
      venta.pago.efectivo.monto = efectivo >= 0 ? efectivo : 0
    },

    // ── Sincronizar efectivo cuando cambia el total del carrito ──
    sincronizarEfectivoConTotal(index) {
      const venta = this.ventas[index]
      const total = parseFloat(this.calcularTotal(index)) || 0
      const visa = parseFloat(venta.pago.visa.monto) || 0
      const mastercard = parseFloat(venta.pago.mastercard.monto) || 0
      const yape = parseFloat(venta.pago.yape.monto) || 0
      const efectivo = parseFloat((total - visa - mastercard - yape).toFixed(2))
      venta.pago.efectivo.monto = efectivo >= 0 ? efectivo : 0
      // Si no usó otros métodos, actualizar pagocon también
      if (visa === 0 && mastercard === 0 && yape === 0) {
        venta.pagocon = total
      }
    },

    // ── Gestión de ventanas (tabs) ────────────────────────
    anadirVentana() {
      this.ventas.push(this.crearVenta())
      this.ultimasVentas.push(null)
      Vue.set(this.isNota, this.ventas.length - 1, false)
      Vue.set(this.tieneIgv, this.ventas.length - 1, false)
      Vue.set(this.bloquearInput, this.ventas.length - 1, false)

      this.$nextTick(() => {
        const nuevoIndex = this.ventas.length - 1
        this.ventaActiva = nuevoIndex
        this.loadClienteVarios(nuevoIndex)
        this.getVendedor(null, nuevoIndex)
      })

      this.$toasted.success('Nueva ventana de venta añadida')
    },

    eliminarVentana(index) {
      this.ventas.splice(index, 1)
      this.ultimasVentas.splice(index, 1)
      this.isNota.splice(index, 1)
      this.tieneIgv.splice(index, 1)
      this.bloquearInput.splice(index, 1)

      this.$nextTick(() => {
        this.ventaActiva = Math.max(0, index - 1)
      })

      this.$toasted.info('Ventana de venta cerrada')
    },

    // ── Cliente por defecto (varios) ──────────────────────
    loadClienteVarios(index) {
      this.axios.get(this.API + 'clientes/00000000')
        .then(res => {
          if (res.data.IdCliente) {
            this.setCliente(res.data, false, index)
          }
        })
    },

    // ── Barcode cliente ───────────────────────────────────
    loadClienteBarcode(e, index) {
      e.preventDefault()
      if (this.dni) {
        this.setCliente(false, this.dni, index)
        this.dni = ''
      }
    },

    // ── Focus helpers ─────────────────────────────────────
    focusAutocomplete(ref) {
      const r = this.$refs[ref]
      if (r) {
        if (Array.isArray(r)) r[0].handleDoubleClick()
        else r.handleDoubleClick()
      }
    },

    // ── Inicializar / limpiar un tab ──────────────────────
    limpiarTab(index) {
      Vue.set(this.ventas, index, this.crearVenta())
      Vue.set(this.isNota, index, false)
      Vue.set(this.tieneIgv, index, false)
      Vue.set(this.bloquearInput, index, false)
      Vue.set(this.ultimasVentas, index, null)

      this.$nextTick(() => {
        const refs = [
          `inputTipoVenta${index}`,
          `inputAlmacen${index}`,
          `inputPuntoVenta${index}`,
          `inputCliente${index}`,
          `inputVendedor${index}`
        ]
        refs.forEach(r => {
          const el = this.$refs[r]
          if (el) {
            const target = Array.isArray(el) ? el[0] : el
            if (target.setValue) {
              if (r.includes('TipoVenta')) target.setValue('BOLETA ELEC.')
              else if (r.includes('Almacen')) target.setValue('VENTA')
              else if (r.includes('PuntoVenta')) target.setValue('CAJA1')
              else target.setValue('')
            }
          }
        })
      })
    },

    // ── Tipo de venta ─────────────────────────────────────
    setTipoVenta(obj, index) {
      Vue.set(this.isNota, index, false)
      Vue.set(this.bloquearInput, index, false)

      const venta = this.ventas[index]
      venta.NroComprobanteModifica = ''
      venta.CodSunatModifica = ''
      venta.NotaIdMotivo = ''
      venta.NotaDescMotivo = ''

      const docRef = this.$refs[`inputDocRef${index}`]
      if (docRef) {
        const target = Array.isArray(docRef) ? docRef[0] : docRef
        target.setValue('')
      }

      if (obj.CodSunat === '07' || obj.CodSunat === '08') {
        Vue.set(this.isNota, index, true)
      }

      Vue.set(this.tieneIgv, index, !!obj.TieneIgv)

      venta.tipoVenta = obj
      this.getNumeroVenta(index)
    },

    // ── Doc. Referencia (notas crédito/débito) ────────────
    setDocRef(obj, index) {
      const venta = this.ventas[index]
      venta.CodSunatModifica = obj.CodSunat
      venta.NroComprobanteModifica = obj.NroComprobante

      if (venta.tipoVenta.CodSunat === '07' || venta.tipoVenta.CodSunat === '08') {
        venta.Serie = venta.Serie.replace(/^./, obj.NroComprobante[0])
      }

      this.axios.get(this.API + 'ventas/solonumero', {
        params: {
          serie: venta.Serie,
          idTipoDoc: venta.tipoVenta.IdTipoDoc
        }
      })
        .then(response => { venta.Numero = response.data.numero })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })

      // Cargar productos del comprobante afectado
      this.axios.get(this.API + 'venta/detalle/comprobante', {
        params: {
          NroComprobante: obj.NroComprobante,
          idDocVenta: obj.IdDocVenta,
          IdDocVentaPuntoVenta: venta.puntoVenta.IdDocVentaPuntoVenta
        }
      })
        .then(response => {
          // Cargar cliente del comprobante
          this.axios.get(this.API + 'cliente', {
            params: { idCliente: response.data[0].IdCliente }
          })
            .then(resCliente => {
              if (resCliente.data) {
                const inputCliente = this.$refs[`inputCliente${index}`]
                if (inputCliente) {
                  const target = Array.isArray(inputCliente) ? inputCliente[0] : inputCliente
                  target.setValue(resCliente.data.Cliente)
                }
                venta.cliente = resCliente.data
                venta.CampoDireccion = resCliente.data.Direccion
              } else {
                this.$toasted.show('No se encontro al cliente')
              }
            })

          // Cargar productos — se delega al carrito del tab
          const carritoRef = this.$refs[`carrito${index}`]
          if (carritoRef) {
            venta.productos = []  // Limpiar productos locales
            response.data.forEach(item => {
              venta.productos.push(item)
            })
          }

          venta.NotaIdMotivo = null
          Vue.set(this.bloquearInput, index, true)
          this.obtenerStok({
            IdAlmacen: venta.almacen.IdAlmacen,
            productos: venta.productos
          })
        })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error al cargar el comprobante')
        })
    },

    setMotivo(ev, index) {
      const bloq = (ev === '01' || ev === '02' || ev === '06' || ev === null)
      Vue.set(this.bloquearInput, index, bloq)
    },

    // ── Almacén ───────────────────────────────────────────
    loadAlmacen(id, index) {
      this.axios.get(this.API + 'almacenes/id/' + id)
        .then(res => this.setAlmacen(res.data, index))
    },

    setAlmacen(obj, index) {
      this.ventas[index].almacen = obj
      const ref = this.$refs[`inputAlmacen${index}`]
      if (ref) {
        const target = Array.isArray(ref) ? ref[0] : ref
        target.setValue(obj.Almacen)
      }
      if (this.ventas[index].productos && this.ventas[index].productos.length) {
        this.obtenerStok({
          IdAlmacen: obj.IdAlmacen,
          productos: this.ventas[index].productos
        })
      }
    },

    // ── Punto de venta ────────────────────────────────────
    loadPuntoVenta(id, index) {
      this.axios.get(this.API + 'puntoventa/id/' + id)
        .then(res => {
          this.setPuntoVenta(res.data, index)
          this.getNumeroVenta(index)
        })
    },

    setPuntoVenta(obj, index) {
      this.ventas[index].puntoVenta = obj
      const ref = this.$refs[`inputPuntoVenta${index}`]
      if (ref) {
        const target = Array.isArray(ref) ? ref[0] : ref
        target.setValue(obj.PuntoVenta)
      }
      this.getNumeroVenta(index)
    },

    // ── Número de venta ───────────────────────────────────
    getNumeroVenta(index) {
      const venta = this.ventas[index]
      this.axios.get(this.API + 'ventas/numero', {
        params: {
          idPuntoVenta: venta.puntoVenta.IdDocVentaPuntoVenta,
          idTipoDoc: venta.tipoVenta.IdTipoDoc
        }
      })
        .then(response => {
          venta.Serie = response.data.serie
          venta.Numero = response.data.numero
        })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error al obtener el número de venta')
        })
    },

    // ── Vendedor ──────────────────────────────────────────
    getVendedor(user, index) {
      const usuario = user || sessionStorage.getItem('User')
      this.axios.get(this.API + 'usuarios/' + usuario)
        .then(response => {
          this.setVendedor(response.data, index)

          const refVendedor = this.$refs[`inputVendedor${index}`]
          if (refVendedor) {
            const target = Array.isArray(refVendedor) ? refVendedor[0] : refVendedor
            target.setValue(response.data.NombreUsuario)
          }

          this.loadAlmacen(response.data.IdAlmacen, index)
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta, index)

          // Bloquear almacén y punto de venta
          this.$nextTick(() => {
            const refAlm = this.$refs[`inputAlmacen${index}`]
            if (refAlm) {
              const t = Array.isArray(refAlm) ? refAlm[0] : refAlm
              if (t.$refs && t.$refs.input) t.$refs.input.disabled = true
            }
            const refPV = this.$refs[`inputPuntoVenta${index}`]
            if (refPV) {
              const t = Array.isArray(refPV) ? refPV[0] : refPV
              if (t.$refs && t.$refs.input) t.$refs.input.disabled = true
            }
          })
        })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error al cargar el vendedor')
        })
    },

    setVendedor(obj, index) {
      this.ventas[index].vendedor = obj
    },

    setVendedorManual(obj, index) {
      this.ventas[index].vendedor = obj
      this.getVendedor(obj.Usuario, index)
    },

    // ── Cliente ───────────────────────────────────────────
    setCliente(obj, dni = false, index = 0) {
      const params = dni ? { dni } : { idCliente: obj }

      if (typeof obj === 'object') {
        const refCliente = this.$refs[`inputCliente${index}`]
        if (refCliente) {
          const target = Array.isArray(refCliente) ? refCliente[0] : refCliente
          target.setValue(obj.Cliente)
        }
        this.ventas[index].cliente = obj
        this.ventas[index].CampoDireccion = obj.Direccion

        if (obj.DniRuc === '00000000') {
          this.ventas[index].EsOrganizacion = 0
          this.ventas[index].NombreOrganizacion = ''
        }
      } else {
        this.axios.get(this.API + 'cliente', { params })
          .then(response => {
            if (response.data) {
              const refCliente = this.$refs[`inputCliente${index}`]
              if (refCliente) {
                const target = Array.isArray(refCliente) ? refCliente[0] : refCliente
                target.setValue(response.data.Cliente)
              }
              this.ventas[index].cliente = response.data
              this.ventas[index].CampoDireccion = response.data.Direccion

              if (response.data.DniRuc === '00000000') {
                this.ventas[index].EsOrganizacion = 0
                this.ventas[index].NombreOrganizacion = ''
              }
            } else {
              this.$toasted.show('No se encontro al cliente')
            }
          })
          .catch(error => console.log(error))
      }
    },

    // ── Producto ──────────────────────────────────────────
    onSetProducto(obj) {
      obj.cantidad = 1
      obj.precio = obj.PrecioContado || 0
      return obj
    },

    onAddProducto(index, producto) {
      const venta = this.ventas[index]

      const prepararProducto = (obj) => {
        // Asegurar campos numéricos base
        obj.cantidad = parseFloat(obj.cantidad) || 1
        obj.precio = parseFloat(obj.precio) || 0
        obj.descuento = parseFloat(obj.descuento) || 0
        obj.igv = parseFloat(obj.igv) || 0
        // Pre-calcular subtotal y total para que la tabla renderice
        const subtotal = parseFloat((obj.cantidad * obj.precio).toFixed(2))
        Vue.set(obj, 'subtotal', subtotal)
        Vue.set(obj, 'total', parseFloat((subtotal - obj.descuento).toFixed(2)))
        Vue.set(obj, 'stock', obj.stock || 0)
        return obj
      }

      const agregarOSumar = (obj) => {
        const existenteIndex = venta.productos.findIndex(p => p.IdProducto === obj.IdProducto)

        if (existenteIndex >= 0) {
          const prod = venta.productos[existenteIndex]
          const nuevaCantidad = (parseFloat(prod.cantidad) || 0) + 1
          Vue.set(prod, 'cantidad', nuevaCantidad)
          const nuevoSubtotal = parseFloat((nuevaCantidad * parseFloat(prod.precio)).toFixed(2))
          Vue.set(prod, 'subtotal', nuevoSubtotal)
          Vue.set(prod, 'total', parseFloat((nuevoSubtotal - parseFloat(prod.descuento || 0)).toFixed(2)))
        } else {
          const nuevoProd = prepararProducto(Object.assign({}, obj))
          venta.productos.push(nuevoProd)
        }

        venta.pago.visa.monto = 0
        venta.pago.mastercard.monto = 0
        venta.pago.yape.monto = 0

        this.$nextTick(() => {
          this.actualizarDescuento(index)
          this.sincronizarEfectivoConTotal(index)
        })

        this.obtenerStok({
          IdAlmacen: venta.almacen.IdAlmacen,
          productos: venta.productos
        })

        this.$toasted.success('Producto añadido correctamente')
      }

      // Caso 1: objeto completo desde autocomplete
      if (typeof producto === 'object' && producto !== null && producto.IdProducto) {
        agregarOSumar(producto)

        // Caso 2: array con ID desde código de barras → [idRaw]
      } else {
        const idProducto = Array.isArray(producto) ? producto[0] : producto

        if (!idProducto) return

        this.axios.get(this.API + 'productos/id/' + idProducto)
          .then(response => {
            if (response.data) {
              const obj = response.data
              obj.cantidad = 1
              obj.precio = obj.PrecioContado || 0
              obj.descuento = 0
              agregarOSumar(obj)
            } else {
              this.$toasted.show('Producto no encontrado')
            }
          })
          .catch(err => {
            console.log(err)
            this.$toasted.show('Error al cargar el producto')
          })
      }
    },

    onRemoveProducto(index, productos) {
      this.ventas[index].productos = productos  // ← guardar en tab
      this.actualizarDescuento(index)
    },

    // ── Descuentos / puntos ───────────────────────────────
    actualizarDescuento(index) {
      const venta = this.ventas[index]

      if (venta.puntosAplicados > 0) {
        // Si ya hay un descuento por puntos calculado, simplemente reaplícalo
        if (venta.descuentoPuntos && venta.descuentoPuntos.descuento) {
          this.aplicarDescuentoLocal(venta.descuentoPuntos.descuento, index)
        } else {
          // Si no está calculado aún, obtenerlo
          this.aplicarPuntos(venta.puntosAplicados, index)
        }
      } else if (venta.descuentoGeneral > 0) {
        this.aplicarDescuentoLocal(venta.descuentoGeneral, index)
      }

      this.$nextTick(() => this.sincronizarEfectivoConTotal(index))
    },

    aplicarPuntos(val, index) {
      const venta = this.ventas[index]
      if (parseFloat(val) > venta.cliente.Puntos) {
        venta.puntosAplicados = venta.cliente.Puntos
      }

      this.axios.get(this.API + 'ventas/descuento/' + venta.puntosAplicados)
        .then(response => {
          venta.descuentoPuntos = response.data
          // Aplicar descuento localmente a los productos del tab
          this.aplicarDescuentoLocal(response.data.descuento, index)
        })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },

    aplicarDescuentoGeneral(val, index) {
      this.ventas[index].puntosAplicados = 0
      // Aplicar descuento localmente a los productos del tab
      this.aplicarDescuentoLocal(val, index)
    },

    aplicarDescuentoLocal(descuentoTotal, index) {
      const venta = this.ventas[index]
      if (!venta.productos || venta.productos.length === 0) {
        return
      }

      // Calcular el subtotal de todos los productos (sin descuento)
      let subtotalTotal = 0
      venta.productos.forEach(prod => {
        subtotalTotal += parseFloat((prod.cantidad || 0) * (prod.precio || 0))
      })

      if (subtotalTotal === 0) {
        return
      }

      // Encontrar el producto con mayor subtotal para asignarle el ajuste de redondeo
      let maxSubtotalIndex = 0
      let maxSubtotal = 0
      venta.productos.forEach((prod, idx) => {
        const subtotal = parseFloat((prod.cantidad || 0) * (prod.precio || 0))
        if (subtotal > maxSubtotal) {
          maxSubtotal = subtotal
          maxSubtotalIndex = idx
        }
      })

      // Aplicar descuento proporcional al subtotal de cada producto
      // PRIMERO calcula para todos EXCEPTO el de mayor valor
      let descuentoAcumulado = 0
      venta.productos.forEach((prod, indiceProducto) => {
        if (indiceProducto !== maxSubtotalIndex) {
          const subtotalProducto = parseFloat((prod.cantidad || 0) * (prod.precio || 0))
          const descuentoProducto = parseFloat(((descuentoTotal / subtotalTotal) * subtotalProducto).toFixed(2))
          Vue.set(prod, 'descuento', descuentoProducto)
          descuentoAcumulado += descuentoProducto
        }
      })

      // FINALMENTE, asigna al producto de mayor valor lo que falta
      const productoMaximo = venta.productos[maxSubtotalIndex]
      const descuentoProductoMaximo = parseFloat((descuentoTotal - descuentoAcumulado).toFixed(2))
      Vue.set(productoMaximo, 'descuento', descuentoProductoMaximo)
    },

    asignarTotalA(metodo, index) {
      const venta = this.ventas[index]
      const total = parseFloat(this.calcularTotal(index)) || 0

      // Poner todo a 0 primero
      venta.pago.efectivo.monto = 0
      venta.pago.visa.monto = 0
      venta.pago.mastercard.monto = 0
      venta.pago.yape.monto = 0

      // Asignar el total al método seleccionado
      venta.pago[metodo].monto = total
    },

    // ── Guardar Proforma ──────────────────────────────────
    guardarProforma(index) {
      const venta = this.ventas[index]

      if (!venta.productos || !venta.productos.length) {
        this.$toasted.show('No hay productos para crear la proforma')
        return
      }
      if (!venta.cliente.IdCliente) {
        this.$toasted.show('Debe seleccionar un cliente')
        return
      }
      if (!venta.vendedor.Usuario) {
        this.$toasted.show('Debe seleccionar un vendedor')
        return
      }

      this.axios.post(this.API + 'proformas', venta)
        .then(response => {
          this.$toasted.show('Proforma Guardada con exito')
          window.open('/nuevo/index.html#/reportes/proforma/' + response.data.insertId, '_blank')

          // ── Cerrar o limpiar la ventana ──────────────────────
          if (this.ventas.length > 1) {
            // Si hay más de una ventana, cerrar esta tab
            this.eliminarVentana(index)
          } else {
            // Si es la única, solo limpiar
            this.limpiarTabDespuesVenta(index)
          }
        })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },

    // ── Guardar Venta ─────────────────────────────────────
    guardarVenta(evt, index) {
      evt.preventDefault()
      this.inProgress = true

      const venta = this.ventas[index]
      const carritoRef = this.$refs[`carrito${index}`]
      const totalVenta = this.getTotalCart(index)

      // Validar pagos
      let totalPagos = 0
      const pagoEfectivo = isNaN(parseFloat(venta.pago.efectivo.monto)) ? 0 : parseFloat(venta.pago.efectivo.monto)
      const pagoVisa = isNaN(parseFloat(venta.pago.visa.monto)) ? 0 : parseFloat(venta.pago.visa.monto)
      const pagoMastercard = isNaN(parseFloat(venta.pago.mastercard.monto)) ? 0 : parseFloat(venta.pago.mastercard.monto)
      const pagoYape = isNaN(parseFloat(venta.pago.yape.monto)) ? 0 : parseFloat(venta.pago.yape.monto)
      totalPagos = pagoEfectivo + pagoVisa + pagoMastercard + pagoYape

      if (pagoEfectivo < 0) {
        this.$toasted.show('El metodo de pago efectivo no puede ser negativo')
        this.inProgress = false
        return
      }
      if (pagoVisa < 0) {
        this.$toasted.show('El metodo de pago tarjeta no puede ser negativo')
        this.inProgress = false
        return
      }
      if (pagoMastercard < 0) {
        this.$toasted.show('El metodo de pago transferencia no puede ser negativo')
        this.inProgress = false
        return
      }
      if (pagoYape < 0) {
        this.$toasted.show('El metodo de pago transferencia no puede ser negativo')
        this.inProgress = false
        return
      }

      if (!venta.EsCredito) {
        if (parseFloat(totalVenta).toFixed(2) !== parseFloat(totalPagos).toFixed(2)) {
          this.$toasted.show('Los pagos no coinciden con la venta')
          this.inProgress = false
          return
        }
      }

      // Validar cliente
      if (!venta.cliente.DniRuc) {
        this.$toasted.show('Debe seleccionar un cliente.')
        this.inProgress = false
        return
      }
      if (!Number.isInteger(parseInt(venta.cliente.DniRuc))) {
        this.$toasted.show('El RUC debe ser numérico, editar cliente.')
        this.inProgress = false
        return
      }
      const esEmpresa = venta.cliente.DniRuc.length > 9
      if (!esEmpresa && venta.tipoVenta.CodSunat === '01') {
        this.$toasted.show('Una factura debe ser emitida para una empresa.')
        this.inProgress = false
        return
      }

      // Validar productos
      if (!venta.productos || !venta.productos.length) {
        this.$toasted.show('No hay productos para vender')
        this.inProgress = false
        return
      }

      let sinStock = 0
      let totalCero = 0
      venta.productos.forEach(pro => {
        if (pro.stock <= 0) sinStock++
        if (parseFloat(pro.total) <= 0) totalCero++
      })

      if (totalCero >= 1) {
        this.$toasted.error('No se puede enviar un producto en cero')
        this.inProgress = false
        return
      }
      if (sinStock >= 1 && venta.tipoVenta.CodSunat !== '07') {
        this.$toasted.error('No se puede vender un producto sin stock')
        this.inProgress = false
        return
      }

      // Validar nota crédito/débito
      if (venta.tipoVenta.CodSunat === '07' || venta.tipoVenta.CodSunat === '08') {
        if (!venta.CodSunatModifica || !venta.NroComprobanteModifica) {
          this.$toasted.show('Debe seleccionar un Documento de Referencia...')
          this.inProgress = false
          return
        }
        if (!venta.NotaIdMotivo) {
          this.$toasted.show('Debe seleccionar un Motivo')
          this.inProgress = false
          return
        }
        venta.NotaDescMotivo = this.itemsMotivoNota.find(i => i.value === venta.NotaIdMotivo).text
      }

      // Armar payload
      venta.puntosAplicados = venta.puntosAplicados || 0
      venta.totalCart = totalVenta
      venta.NombreOrganizacion = ('' + venta.NombreOrganizacion).trim().toUpperCase()

      if (carritoRef) {
        const target = Array.isArray(carritoRef) ? carritoRef[0] : carritoRef
        venta.PagoCon = target.pagocon || 0
      }

      this.axios.post(this.API + 'ventas', venta)
        .then(response => {
          this.$toasted.success('Venta realizada con exito')
          this.inProgress = false
          Vue.set(this.ultimasVentas, index, response.data.insertId)

          window.open(
            '/imprimir/index.php?IdDocVenta=' + response.data.insertId,
            '_blank',
            'noopener'
          )

          // ── Cerrar o limpiar la ventana ──────────────────────
          if (this.ventas.length > 1) {
            this.eliminarVentana(index)
          } else {
            this.limpiarTabDespuesVenta(index)
          }
        })
        .catch(error => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
          this.inProgress = false
        })
    },

    // ── Limpiar tab después de una venta exitosa ──────────
    limpiarTabDespuesVenta(index) {
      this.limpiarCart()
      this.ventas[index].productos = []
      this.limpiarTab(index)
      this.getVendedor(null, index)
      this.getNumeroVenta(index)
      this.loadClienteVarios(index)
    },

    // ── Eliminar cliente (llamado desde config-venta) ─────
    eliminarCliente() {
      const ref = this.$refs[`inputCliente${this.ventaActiva}`]
      if (ref) {
        const target = Array.isArray(ref) ? ref[0] : ref
        target.setValue('')
      }
      this.ventas[this.ventaActiva].cliente = {}
    }
  },

  // ─────────────────────────────────────────────────────────
  //  COMPONENTS
  // ─────────────────────────────────────────────────────────
  components: {
    ProductosCarrito,
    ClientesForm,
    ConfigVenta,
    PagoDeuda,
    Movimiento,
    PreOrdenLista,
    Proformas
  }
}
</script>

<style scoped>
.puntos {
  font-size: 52px;
}

.big-text {
  font-size: 30px;
}
</style>

<style>
#modalConfigVenta .modal-dialog {
  max-width: 65% !important;
}

.nav-pills .nav-link {
  font-size: 1rem;
  font-weight: bold;
}
</style>