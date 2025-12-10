<template>
    <b-container fluid>
      <b-card no-body>
        <b-tabs card pills  v-model="index">
          <b-tab :title="`VENTA ${i+1}`" v-for="(tab,i) in ventas" :key="i">
            <b-row>
                <b-col md="6">
                  <b-row>
                    <b-col md="4">
                     <b-form-group  class="my-0">
                        <span class="ocultarMovil">Tipo de Venta</span>
                          <label class="lblSerie"><b style="display:none" class="mostrarSerie">{{ tab.Serie }} - {{ tab.Numero }}</b></label>
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
                    </b-col>
                     <b-col md="4">
                        <b-form-group>
                            <label>Almacen:</label>
                            <input type="text" v-model="tab.almacen.Almacen" class="form-control" disabled>
                        </b-form-group>
                        <!-- <b-form-group v-if="isNota">
                            <label>Motivo :</label>
                            <b-form-select v-model="venta.NotaIdMotivo" :options="itemsMotivoNota" v-on:change="setMotivo($event)" class="mb-3" size="md" required/>
                        </b-form-group> -->
                    </b-col>
                    <b-col md="4">
                        <b-form-group>
                            <label>Punto de Venta:</label>
                            <input type="text" v-model="tab.puntoVenta.PuntoVenta" class="form-control" disabled>
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
                <b-col md="3">
                    <div v-if="tab.cliente.Cliente && tab.cliente.EsVIP">
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
                        <input type="text" class="form-control" v-model="tab.vendedor.NombreUsuario" disabled>
                    </b-form-group>
                </b-col>
              </b-row>
              <b-row>
                <div class="divCarrito"></div>
                 <b-col cols="12">
                     <productos-carrito
                        :igvTotal="tieneIgv"
                        :setPrecio="true"
                        ref="carrito"
                        :isVentas="true"
                        :showButton="false"
                        :bloquearInput="bloquearInput"
                        :fields="carrito.fields"
                        :idAlmacen="tab.almacen.IdAlmacen"
                        :productos="tab.productos"
                        :onSetProducto="onSetProducto"
                        @onAddProducto="setProducto"
                      >
                      </productos-carrito>
                 </b-col>
                
                 
              </b-row>
          </b-tab>
        </b-tabs>
      <b-row>
        <b-col cols="4">
          <b-button class="btnAction"  block :disabled="inProgress" variant="primary" @click="guardarProforma">Crear Proforma</b-button>
          <b-button class="btnAction" block  :disabled="inProgress" variant="success"  @click="guardarVenta">Efectuar Venta</b-button>
          <b-button class="btnAction" block style="background: #263238; color: #ffffff;"  @click="anadirVentana()" variant="success">Añadir Ventana</b-button>
          <b-button class="btnAction" v-if="index>0" block style="background: #DD2C00; color: #ffffff;"  @click="eliminarVentana()" >Eliminar Ventana</b-button>    
        </b-col>
        <b-col cols="4">
          <table class="table b-table border table-footer">
            <tbody>
              <tr>
                <td>
                  <b-button class="p-1 ml-1 mt-1" id="popover-opciones" variant="warning"><i class="fas fa-tags" ></i></b-button>
                     <b-popover
                         target="popover-opciones"
                         placement="bottom" 
                         custom-class="custom-popover"
                         title="Opciones" triggers="click" auto-hide>
                    <table class="table b-table border table-footer">
                      <tbody>
                                    <tr v-if="puntosAplicados == 0" class="ocultarMovil">
                                      <td>
                                        <b-input-group prepend="%">
                                          <input
                                            v-model="descuentoGeneral"
                                            min="0"
                                            value="0"
                                            :disabled="bloquearInput ? true : false"
                                            @input="(val) => { aplicarDescuentoGeneral(val.target.value) }"
                                            type="number"
                                            class="form-control"
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
                                          :disabled="bloquearInput ? true : false || isNota || ventas[index].EsDelivery"
                                          v-model="ventas[index].EsCredito"
                                          :value="1"
                                          :unchecked-value="0">
                                          Venta a crédito
                                        </b-form-checkbox>
                                        <b-form-input 
                                          :disabled="bloquearInput ? true : false || isNota || ventas[index].EsDelivery"
                                          v-if="ventas[index].EsCredito==1"
                                          v-model="ventas[index].FechaCredito"
                                          type="date">
                                        </b-form-input>
                                        <b-form-checkbox 
                                          :disabled="bloquearInput ? true : false || ventas[index].EsDelivery"
                                          v-model="ventas[index].PorEntregar"
                                          :value="1"
                                          :unchecked-value="0">
                                          Por entregar
                                        </b-form-checkbox>
                                        <!-- <b-form-checkbox 
                                          :disabled="bloquearInput ? true : false || ventas[index].EsDelivery"
                                          v-model="ventas[index].ProformaSinPrecio"
                                          :value="1"
                                          :unchecked-value="0">
                                          Sin precios en proforma
                                        </b-form-checkbox> -->
                                      </td>
                                    </tr>
                                    <tr>
                                      <td>
                                        <b-input-group prepend="Proforma válido por:" append="días">
                                          <b-form-input
                                            :disabled="bloquearInput ? true : false"
                                            v-model="ventas[index].diasValidosProf"
                                            min="0"
                                            value="0"
                                            type="number"
                                          ></b-form-input>
                                        </b-input-group>
                                      </td>
                                    </tr>
                      </tbody>
                    </table>
                  </b-popover>  
                </td>
                <td><b>SubTotal</b></td>
                <td class="table-success">{{ sumatoriaSubTotal }}</td>
              </tr>
              <tr>
                            <td>
                                             
                                        </td>
                            
                          <td>Descuento</td>
                          <td class="table-danger">- {{ sumatoriaDescuento }}</td>
              </tr>
              <tr>
                          <td>
                              <b-button class="p-1 ml-1 mt-1" id="popover-metodopago" variant="success"><i class="fas fa-credit-card" ></i></b-button>
                                                               <br>
                                              <b-badge v-if="ventas[index].idMetodoPago==1" variant="success" style="font-size: 0.70rem;">EFECTIVO</b-badge>
                                              <b-badge v-if="ventas[index].idMetodoPago==2" variant="info" style="font-size: 0.70rem;">TARJETA</b-badge>
                                              <b-badge v-if="ventas[index].idMetodoPago==3" variant="info" style="font-size: 0.70rem;">YAPE/PLIN</b-badge>
                                              <b-badge v-if="ventas[index].idMetodoPago==4" variant="info" style="font-size: 0.70rem;">TRANSF.</b-badge>
                                              <b-popover 
                                                  target="popover-metodopago" 
                                                  placement="bottom" 
                                                  triggers="click" 
                                                  custom-class="custom-popover"
                                                  auto-hide
                                                >
                                                  <div class="table-responsive p-2">
                                                    <table class="table table-bordered table-sm">
                                                      <tbody>
                                                        <tr>
                                                          <td colspan="4" class="text-center font-weight-bold">Método de Pago</td>
                                                        </tr>
                                                        <tr>
                                                          <td>Efectivo</td>
                                                          <td>
                                                            <b-button @click="cargarMonto('efectivo')" size="sm" variant="primary">
                                                              <i class="fa fa-check" aria-hidden="true"></i>
                                                            </b-button>
                                                          </td>
                                                          <td>
                                                            <b-form-input
                                                              v-model="ventas[index].pago.efectivo.descripcion"
                                                              type="text"
                                                              :disabled="bloquearInput"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              v-model="ventas[index].pago.efectivo.monto"
                                                              min="0"
                                                              :disabled="bloquearInput"
                                                              step="0.01"
                                                              type="number"
                                                              class="form-control"
                                                              placeholder="S/."
                                                            />
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
                                                            <b-form-input
                                                              v-model="ventas[index].pago.visa.descripcion"
                                                              type="text"
                                                              :disabled="bloquearInput"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              :disabled="bloquearInput"
                                                              v-model="ventas[index].pago.visa.monto"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(ventas[index].totalCart - ventas[index].pago.mastercard.monto - ventas[index].pago.otro.monto - $event.target.value)"
                                                            />
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
                                                            <b-form-input
                                                              v-model="ventas[index].pago.mastercard.descripcion"
                                                              :disabled="bloquearInput"
                                                              type="text"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              v-model="ventas[index].pago.mastercard.monto"
                                                              :disabled="bloquearInput"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[index].pago.visa.monto - ventas[index].pago.otro.monto - $event.target.value)"
                                                            />
                                                          </td>
                                                        </tr>
                                                        <tr>
                                                          <td>YAPE / PLIN</td>
                                                          <td>
                                                            <b-button size="sm" @click="cargarMonto('otro')" variant="primary">
                                                              <i class="fa fa-check" aria-hidden="true"></i>
                                                            </b-button>
                                                          </td>
                                                          <td>
                                                            <b-form-input
                                                              v-model="ventas[index].pago.otro.descripcion"
                                                              type="text"
                                                              placeholder="Descripcion"
                                                            ></b-form-input>
                                                          </td>
                                                          <td>
                                                            <input
                                                              class="form-control"
                                                              v-model="ventas[index].pago.otro.monto"
                                                              min="0"
                                                              step="0.01"
                                                              type="number"
                                                              placeholder="S/."
                                                              @input="ventas[index].pago.efectivo.monto = $options.filters.redondear(totalCart - ventas[index].pago.visa.monto - ventas[index].pago.mastercard.monto - $event.target.value)"
                                                            />
                                                          </td>
                                                        </tr>
                                                      </tbody>
                                                    </table>
                                                  </div>
                                                </b-popover>
                          </td>
                          <td colspan="1"><b>TOTAL</b></td>
                          <td>{{ sumatoriaTotal }}</td>
              </tr>
              <tr>
                <td  colspan="2">
                  <label>pagó con:</label>
                  <b-form-input
                    type="text"
                    placeholder="Pagó con"
                    v-model="ventas[index].pagocon"
                    :disabled="bloquearInput?true:false"
                    />
                    <!-- @input="asignarVuelto"  -->
                  <label>Vuelto:</label>
                  <b>S/  {{ ventas[index].vuelto | redondear }}</b>
                </td>
              </tr>
            </tbody>
          </table>
        </b-col>
        <b-col cols="4">
           <pago-deuda></pago-deuda>
           <proformas :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectProforma="setCliente" @seleccionarProforma="cargaProforma"></proformas>
           <pre-orden-lista :idAlmacen="ventas[index].almacen.IdAlmacen" @onSelectPreOrden="setCliente"></pre-orden-lista>
           <config-venta @onUpdateConfig="eliminarCliente"></config-venta>
        </b-col>
      </b-row>
      </b-card>
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
    this.initForm();
  },
  mounted() {
    // this.getNumeroVenta()
    this.getVendedor();
    this.loadClienteVarios();
    //setInterval(this.updateTime, 1000)
    /* this.loadFirstAlmacen()
        this.loadFirstPuntoVenta() */
  },
  data() {
    return {
      index: 0,
      ventas: [],
      preVenta: [],
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
    productos() {},
    totalCart(newVal) {
      this.ventas[this.index].pago.efectivo.monto =
       parseFloat( newVal -
        (parseFloat(this.ventas[this.index].pago.visa.monto) +
          parseFloat(this.ventas[this.index].pago.mastercard.monto) +
          parseFloat(this.ventas[this.index].pago.otro.monto))).toFixed(2);
    }
  },
  computed: {
    ...mapState({
      // productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
    productos() {
      if (this.ventas.length > 0) {
        return this.ventas[this.index].productos;
      }
      return [];
    },
    sumatoriaSubTotal() {
      if (
        Object.keys(this.ventas[this.index]).length > 0 &&
        this.ventas[this.index].productos.length > 0
      ) {
        let productos = this.ventas[this.index].productos;
        let suma = productos.reduce((sum, value) => {
          return sum + parseFloat(value.subtotal);
        }, 0);
        return parseFloat(suma).toFixed(2);
      }
      return 0;
    },
    sumatoriaTotal() {
      if (
        Object.keys(this.ventas[this.index]).length > 0 &&
        this.ventas[this.index].productos.length > 0
      ) {
        let productos = this.ventas[this.index].productos;
        let suma = productos.reduce((sum, value) => {
          return sum + parseFloat(value.total);
        }, 0);
        return parseFloat(suma).toFixed(2);
      }
      return 0;
    },
    sumatoriaDescuento() {
      if (
        Object.keys(this.ventas[this.index]).length > 0 &&
        this.ventas[this.index].productos.length > 0
      ) {
        let productos = this.ventas[this.index].productos;
        let suma = productos.reduce((sum, value) => {
          return sum + parseFloat(value.descuento || 0);
        }, 0);
        return suma;
      }
      return 0;
    },
    sumatoriaIgv() {
      // if (Object.keys(this.ventas[this.index]).length > 0) {
      //   let productos = this.ventas[this.index].productos;
      //   let suma = productos.reduce((sum, productos) => {
      //     return sum + productos;
      //   });
      //   return suma;
      // }
      return 0;
    }
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      add: "agregarProducto"
    }),
    async cargaProforma(proforma) {
      let p = proforma.productos;
      let stock = await this.axios(`${this.API}/productos/puntoventa/stock`, {
        params: {
          idProductos: p.map(prod => prod.IdProducto),
          idAlmacen: this.ventas[this.index].almacen.IdAlmacen,
          noLimit: 1
        }
      }).then(res => {
        return res.data;
      });
      // this.preVenta
      let productos = stock.map(prod => {
        let producto = p.find(v => v.IdProducto == prod.IdProducto);
        return {
          ...prod,
          cantidad: producto.Cantidad,
          descuento: producto.Descuento,
          precio: producto.Precio
        };
      });
      this.ventas.push({
        ...this.preVenta,
        cliente: proforma,
        proforma: proforma,
        productos: productos
      });
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.setCliente(proforma);
        this.getVendedor();
      });
      console.log(productos);
    },
    finalizarVentaOProforma() {
      if (this.index > 0) {
        this.ventas[this.index].splice(this.index, 1);
        this.index = this.ventas.length - 1;
        return;
      }
      window.location.reload();
    },
    setProducto(prod) {
      let productos = this.ventas[this.index].productos;
      if (productos.some(v => v.IdProducto == prod.IdProducto)) {
        let producto = productos.find(v => v.IdProducto == prod.IdProducto);
        producto.cantidad += 1;
        return;
      }
      productos.unshift({
        ...prod,
        precio: prod.PrecioContado,
        cantidad: 1
      });
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
        // this.$refs.inputAlmacen.setValue(res.data.Almacen);
        this.venta.almacen = res.data;
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
      let comp = this.$refs[ref][0];
      comp.handleDoubleClick();
    },
    initForm() {
      this.venta = {
        idMetodoPago: 1,
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
          efectivo: { IdMetodoPago: 1, monto: 0, descripcion: "" },
          visa: { IdMetodoPago: 2, monto: 0, descripcion: "" },
          mastercard: { IdMetodoPago: 3, monto: 0, descripcion: "" },
          otro: { IdMetodoPago: 4, monto: 0, descripcion: "" }
        },
        NroComprobanteModifica: "",
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        pagocon: 0,
        vuelto: 0
      };
      this.preVenta = { ...this.venta };
      this.ventas.push({ ...this.venta, productos: [] });
      this.isNota = false;
      this.bloquearInput = false;
      this.descuentoGeneral = 0;
      this.puntosAplicados = 0;
    },
    setTipoVenta(obj) {
      this.isNota = false;
      this.bloquearInput = false;
      this.ventas[this.index].NroComprobanteModifica = "";
      this.ventas[this.index].CodSunatModifica = "";
      this.ventas[this.index].NotaIdMotivo = "";
      this.ventas[this.index].NotaDescMotivo = "";

      // this.$refs.inputDocRef && this.$refs.inputDocRef.setValue("");

      if (obj.CodSunat == "07" || obj.CodSunat == "08") {
        // 07 - nota de credito, 08 - Nota de debito
        this.isNota = true;
      }

      if (obj.TieneIgv) {
        this.tieneIgv = true;
      } else {
        this.tieneIgv = false;
      }

      this.venta.tipoVenta = obj;
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
      this.preVenta.almacen = obj;
      this.ventas[this.index].almacen = obj;
      // this.$refs.inputAlmacen.setValue(obj.Almacen);
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
      this.preVenta.puntoVenta = obj;
      this.ventas[this.index].puntoVenta = obj;
      this.getNumeroVenta();
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          this.setVendedor(response.data);
          //this.$refs.inputVendedor.setValue(response.data.NombreUsuario);
          //this.$refs.inputVendedor.$refs.input.disabled = true;
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
          // bloquear ambos inputs
          //this.$refs.inputAlmacen.$refs.input.disabled = true;
          //this.$refs.inputPuntoVenta.$refs.input.disabled = true;
          //this.getNumeroVenta()
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    setVendedor(obj) {
      this.preVenta.vendedor = obj;
      this.ventas[this.index].vendedor = obj;
    },
    async setCliente(obj, dni = false) {
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
        let comp = this.$refs[ref][0].setValue(obj.Cliente);
        console.log(comp);
        // this.$refs.inputCliente.setValue(obj.Cliente);
        this.ventas[this.index].cliente = obj;
        this.ventas[this.index].CampoDireccion = this.ventas[
          this.index
        ].cliente.Direccion;
      } else {
        await this.axios
          .get(this.API + "cliente", {
            params: params
          })
          .then(response => {
            if (response.data) {
              // .setValue(response.data.Cliente);
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
    async guardarProforma() {
      this.venta = this.ventas[this.index];
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

      this.venta.productos = this.productos;

      await this.axios
        .post(this.API + "proformas", this.venta)
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
      this.finalizarVentaOProforma();
    },
    async guardarVenta() {
      this.venta = this.ventas[this.index];
      this.inProgress = true;
      let totalPagos = 0;
      totalPagos += parseFloat(this.venta.pago.efectivo.monto);
      totalPagos += parseFloat(this.venta.pago.visa.monto);
      totalPagos += parseFloat(this.venta.pago.mastercard.monto);
      totalPagos += parseFloat(this.venta.pago.otro.monto);

      if (!this.venta.EsCredito) {
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
          if (this.venta.tipoVenta.CodSunat != "07") {
            this.$toasted.show(
              "El producto " +
                pro.Producto +
                " no tiene stock. No puede vender un producto sin stock"
            );
          }
          sinStock += 1;
        }
      });
      if (sinStock >= 1 && this.venta.tipoVenta.CodSunat != "07") {
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

      this.venta.productos = this.productos;
      this.venta.puntosAplicados = this.puntosAplicados || 0;
      this.venta.totalCart = this.totalCart || 0;
      // this.venta.PagoCon = this.$refs.carrito.pagocon;
      // console.log(this.venta);return;
      await this.axios
        .post(this.API + "ventas", this.venta)
        .then(response => {
          this.$toasted.success("Venta realizada con exito");
          this.inProgress = false;
          this.tieneIgv = false;
          this.ultimaVenta = response.data.insertId;
          this.initForm();
          this.limpiarCart();
          this.getVendedor();
          // this.$refs.carrito.pagocon = 0;
          // this.$refs.inputTipoVenta.setValue("BOLETA ELEC.");
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
      this.finalizarVentaOProforma();
    },
    eliminarCliente() {
      this.$refs.inputCliente.setValue("");
      this.venta.cliente = {};
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
    },
    anadirVentana() {
      this.ventas.push({ ...this.preVenta, productos: [] });
      this.$nextTick(() => {
        this.index = this.ventas.length - 1;
        this.getVendedor();
      });
    },
    eliminarVentana() {
      this.ventas.splice(this.index, 1);
      this.index = this.ventas.length - 1;
    },
    cargarMonto(tipo) {
      if (
        this.ventas[this.index].pago &&
        this.ventas[this.index].pago.hasOwnProperty(tipo)
      ) {
        Object.keys(this.ventas[this.index].pago).forEach((key, index) => {
          this.ventas[this.index].pago[key].monto =
            key === tipo ? this.totalCart : 0;
          if (key === tipo) {
            this.idMetodoPago = index + 1;
          }
        });
      } else {
        console.error(`El tipo de pago "${tipo}" no existe en pago`);
      }
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
.divCarrito {
  min-height: 480px !important;
}

.popover {
  min-width: 400px;
}

.custom-popover {
  z-index: 99999 !important;
}
</style>
