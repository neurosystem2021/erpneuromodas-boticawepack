<template>
  <b-container fluid>
    <b-row>
      <b-col cols="6">
        <b-tabs v-model="ventaActiva" content-class="mt-3" fill class="mt-2 px-2">
          <b-tab v-for="(venta,index) in ventas" :key="index" :title="`Venta ${index+1}`">
            <b-container>
              <b-row>
                <b-col cols="5">
                  <b-input-group prepend="Doc. Venta" class="mt-2">
                    <autocomplete
                    required="required"
                    :ref="`inputTipoVenta${index}`"
                    :url="API + 'ventas/tipos'"
                    anchor="TipoDoc"
                    initValue="BOLETA ELEC."
                    class="font-weight-bold"
                    label="writer"
                    placeholder=""
                    :on-select="setTipoVenta"
                    :classes="{input: 'form-control'}"
                    @focus.native="focusAutocomplete(`inputTipoVenta${index}`)"
                    >
                    </autocomplete>
                  </b-input-group>
                </b-col>
                <b-col cols="3">
                  <b-form-checkbox
                    :id="`checkbox-${index}`"
                    v-model="venta.EsOrganizacion"
                    :value="true"
                    :unchecked-value="false"
                  >
                    Es Organización
                  </b-form-checkbox>  
                </b-col>
                <b-col cols="8" >
                  <b-form-input 
                  v-model="venta.nombreOrganizacion" 
                  v-if="venta.EsOrganizacion"
                  :id="`txtNombreOrganizacion-${index}`"
                  placeholder="NOMBRE DE LA ORGANIZACIÓN"></b-form-input>
                </b-col>
              </b-row>
              <b-row>
                <b-col cols="6">
                  <b-row>
                    <b-col cols="12">Clientes </b-col>
                    <b-col cols="12">Combo</b-col>
                  </b-row>
                </b-col>
                <b-col cols="6">
                  <h1>Correlativo </h1>
                  {{ fechaActual()}}
                </b-col>

              </b-row>
              <b-row>
                <b-col cols="12">
                  Observacion
                </b-col>
              </b-row>
              <b-row class="mt-2">
                <b-table striped hover :fields="headerTable" :items="venta.produtos">

                </b-table>
              </b-row>
              <b-row>
                <b-col cols="4">Botones</b-col>
                <b-col cols="4">Tipo Pago</b-col>
                <b-col cols="4">Totales</b-col>
              </b-row>
              <b-row class="centrado d-flex justify-content-center mt-5" >
                <b-button class="mx-1" variant="primary"> Crear Proforma</b-button>
                <b-button class="mx-1" variant="success"> Efectuar Venta</b-button>
                <b-button class="mx-1" variant="warning" @click="anadirVentana()"> Añadir Venta</b-button>
                <b-button class="mx-1" v-if="ventas.length>1" variant="danger" @click="eliminarVentana()"> Cancelar Venta</b-button>
              </b-row>
            </b-container>
          </b-tab>
          
        </b-tabs>
        <!-- <b-row>

        </b-row> -->

      </b-col>
      <b-col md="6">
        <b-row class="headerInfo" style="background: #f4f4f4">
          <b-col md="5">
            <b-input-group prepend="Vendedor" class="mt-2">
              <autocomplete
                required="required"
                ref="inputVendedor"
                :url="API + 'usuarios'"
                anchor="NombreUsuario"
                class="font-weight-bold"
                label="writer"
                :on-select="setVendedor"
                :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-input-group>
          </b-col>
          <b-col md="4">
            <b-input-group prepend="Alm." class="mt-2">
              <autocomplete
                required="required"
                ref="inputAlmacen"
                :url="API + 'almacenes'"
                anchor="Almacen"
                class="font-weight-bold"
                initValue="PRINCIPAL"
                label="writer"
                placeholder=""
                :on-select="setAlmacen"
                :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-input-group>
          </b-col>
          <b-col md="3">
            <b-input-group prepend="P.V. " class="mt-2">
              <autocomplete
                required="required"
                ref="inputPuntoVenta"
                :url="API + 'ventas/puntos'"
                anchor="PuntoVenta"
                class="font-weight-bold"
                initValue="PRINCIPAL"
                label="writer"
                placeholder=""
                :on-select="setPuntoVenta"
                :onFocus="focusAutocomplete.bind(null, 'inputPuntoVenta')"
                :classes="{ input: 'form-control' }"
              >
              </autocomplete>
            </b-input-group>
          </b-col>
        </b-row>
        <b-row style="background: #f4f4f4" class="buscador">
          <b-col md="8">
            <b-input-group prepend="Buscar Producto" class="mt-2 mb-2">
              <b-form-input
                placeholder="INGRESE UN PRODUCTO"
                autocomplete="off"
                v-model="busqueda"
              ></b-form-input>
              <b-input-group-append>
                <b-button variant="secondary" @click="limpiarFiltro"
                  >Limpiar</b-button
                >
              </b-input-group-append>
            </b-input-group>
          </b-col>
          <b-col md="4">
            <b-input-group class="mt-2 mb-2">
              <input
                placeholder="CODIGO DE BARRAS"
                v-model="loadCode"
                autocomplete="off"
                type="none"
                inputmode="none"
                class="form-control"
              />
              <b-input-group-append>
                <b-button variant="primary"
                  ><i class="fas fa-search"></i
                ></b-button>
              </b-input-group-append>
            </b-input-group>
          </b-col>
        </b-row>
        <b-row style="background: #f4f4f4">
          <b-tabs
            v-model="tabActive"
            style="font-size: 1.1rem !important; font-weight: 500"
          >
            <b-tab
              no-body
              v-for="(item, index) in categorias"
              :key="index"
              :title="item.ProductoCategoria"
              :ref="'tab' + index"
              @click="getProductosCategoria(item.IdProductoCategoria)"
            >
            </b-tab>
            <b-tab
              v-if="busqueda != ''"
              :active="busqueda != ''"
              no-body
              title="BUSQUEDA"
            >
            </b-tab>
          </b-tabs>
        </b-row>
        <b-row
          style="
            position: relative;
            height: 79vh;
            overflow-y: scroll;
            background: #e6e6e6;
          "
        >
          <b-col>
            <b-row class="mt-2">
              <b-col
                id="productoPunto"
                class="m-2 text-center bg-white p-2"
                cols="2"
                style="
                  border: 2px solid #e0e0e0;
                  border-radius: 10px;
                  cursor: pointer;
                "
                v-for="(producto, index2) in productosCategoria"
                :key="index2"
                @click="setProducto(producto)"
              >
                <b-row class="mb-1">
                  <b-col md="4" class="text-left" v-if="false">
                    <b-badge
                      :variant="
                        parseFloat(producto.stock) <= 0 ? 'danger' : 'success'
                      "
                      style="font-size: 0.8rem"
                      >{{
                        producto.stock
                          ? Number.isInteger(producto.stock)
                            ? producto.stock
                            : producto.stock.toFixed(2)
                          : producto.stock
                      }}</b-badge
                    >
                  </b-col>
                  <b-col md="12" class="text-center">
                    <b-badge variant="dark" style="font-size: 1rem"
                      >S/
                      {{
                        parseFloat(producto.PrecioContado).toFixed(2)
                      }}</b-badge
                    >
                  </b-col>
                </b-row>
                <b-img
                  style="border-radius: 10px"
                  fluid
                  :src="'/resources/images/productos/' + producto.Imagen"
                  @error="setAltImg"
                  alt=""
                ></b-img>
                <label
                  class="font-weight-bold"
                  style="font-size: rem; cursor: pointer"
                >
                  {{ producto.Producto }}
                </label>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
      </b-col>
    </b-row>
  </b-container>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import momemnt from "moment";
// import ClientesForm from "@/components/ClientesForm";
// import ConfigVenta from "@/components/ConfigVenta";
// import PagoDeuda from "@/components/PagoDeuda";
// import PreOrdenLista from "@/components/PreOrdenLista.vue";
// import Proformas from "@/components/ProformaLista.vue";
// //import OrdenesModal from '@/components/OrdenesModal.vue'
// //import DeliveryModal from '@/components/DeliveryModal.vue'
// import Spinner from "vue-simple-spinner";
import ProductosCarritoNuevo from "@/components/ProductosCarritoNuevo";
// import debounce from "lodash";
import Swal from "sweetalert2";
export default {
  components: { ProductosCarritoNuevo },
  data() {
    return {
      API: config.API,
      categorias: [],
      productosCategoria: [],
      headerTable: [
        { key: "stock", label: "Stock", variant: "info", class: "text-center" },
        { key: "Producto" },
        { key: "ProductoCategoria", label: "Categoria" },
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
      ],
      busqueda: "",
      loadCode: "",
      tabActive: 0,
      ventaActiva: 0,
      venta: {
        EsOrganizacion: false
      },
      ventas: [
        {
          EsOrganizacion: false
        }
      ],
      bloquearInput: false,
      precioHabilitado: false,
      touchHab: false
    };
  },
  methods: {
    fechaActual() {
      return this.$moment().format("YYYY-MM-DD H:mm:ss");
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    setVendedor(obj) {
      if (obj.IdTipoDoc) this.getTipoDoc(obj.IdTipoDoc);
      this.venta.vendedor = obj;
    },
    setAlmacen(obj) {
      this.venta.almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      this.getCategorias();
      if (this.$route.query.idPreOrden && this.$route.query.idCliente) {
        this.cargarPreOrden();
        this.setCliente(this.$route.query.idCliente);
      }
    },
    setPuntoVenta(obj) {
      this.ventas[this.ventaActiva].puntoVenta = obj;
      this.$refs.inputPuntoVenta.setValue(obj.PuntoVenta);
      this.getNumeroVenta();
    },
    focusAutocomplete(refName) {
      const inputRef = this.$refs[refName];
      if (inputRef && typeof inputRef.handleDoubleClick === "function") {
        inputRef.handleDoubleClick();
      } else {
        console.warn("handleDoubleClick no está definido o no es una función.");
      }
    },
    limpiarFiltro() {
      this.busqueda = "";
      this.tabActive = 0;
    },
    anadirVentana() {
      this.ventas.push(this.venta);
      this.$nextTick(() => {
        this.ventaActiva = this.ventas.length - 1;
      });
    },
    eliminarVentana() {
      this.ventas.splice(this.ventaActiva, 1);
    },
    setTipoVenta(val) {
      console.log(val);
    }
  }
};
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
.nomCart {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 2px solid black;
}
.contentCard {
  cursor: pointer;
}
.centrado {
  width: 100%;
  align-items: center !important;
  align-content: center !important;
  text-align: center !important;
}
</style>
<style>
/* .nav-tabs .nav-link.active,
.nav-tabs .nav-item.show .nav-link {
  background-color: #e6e6e6;
  border-bottom: none;
} */
.headerInfo .input-group-text {
  font-weight: bold;
  font-size: 0.8rem;
}
.headerInfo input {
  font-size: 0.8rem;
}
.buscador .input-group-text {
  font-weight: bold;
  font-size: 0.8rem;
}
.buscador input {
  font-size: 0.8rem;
}
.docInfo .input-group-text {
  font-weight: bold;
  font-size: 0.9rem;
}
.docInfo input {
  font-size: 0.9rem;
  font-weight: bold;
}
.docInfo select {
  font-size: 0.9rem;
  height: auto !important;
}
.clienteInfo * {
  font-size: 0.8rem;
  height: auto !important;
}
#productoPunto:hover {
  transform: scale(1.02);
  transition: 0.5s all;
  box-shadow: 0 20px 80px rgba(0, 0, 0, 0.25);
}
#modalCantidad .modal-dialog {
  max-width: 55% !important;
}
</style>
