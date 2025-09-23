<template>
    <div>
        <Menu v-if="false"></Menu>

        <br />
        <b-container class="bv-example-row px-5" fluid>
            <b-form @submit="guardarMovimiento" @keydown.enter="evt => evt.preventDefault()">
                <b-row>
                    <b-col md="12">
                        <b-row>
                            <b-col md="2">
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
                                <label>Proveedor:</label>
                                <b-input-group>
                                    <autocomplete
                                    required="required"
                                    ref="inputProveedor"
                                    :url="API + 'proveedores'"
                                    anchor="ProveedorRuc"
                                    label="writer"
                                    placeholder=""                          
                                    :on-select="setProveedor"
                                    :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                                    :classes="{input: 'form-control'}">
                                    </autocomplete>
                                </b-input-group>
                                </b-form-group>
                            </b-col>
                            <b-col md="2">
                                <b-form-group>
                                    <label>Adquiridos:</label><br>
                                    <b-button variant="success" :disabled="movimiento.almacen.IdAlmacen==0 || movimiento.proveedor.IdProveedor==0" @click="cargarProducto">Buscar Productos</b-button>
                            </b-form-group>
                            </b-col>
                            <b-col md="2">
                              <b-form-group>
                              <label>Moneda:</label>
                              <autocomplete
                                :url="API + 'monedas'"
                                initValue="USD"
                                anchor="Moneda"
                                label="writer"
                                placeholder="Moneda"
                                :on-select="setMoneda"
                                :classes="{input: 'form-control'}">
                              </autocomplete>
                            </b-form-group>
                            <b-form-group>
                              <label>Tipo de cambio:</label>
                              <input v-model.number="TipoCambio"
                                type="number"
                                class="form-control"
                                step="0.0001"
                                placeholder="Tipo de cambio"/>
                            </b-form-group>
                          </b-col>
                          <b-col cols="2">
                             <b-form-group label="Credito: ">
                              <b-form-checkbox 
                                v-model="EsCredito"
                                :value="1"
                                :unchecked-value="0">
                                Pago pendiente
                              </b-form-checkbox>
                              <b-form-input v-model="FechaVenCredito"
                                v-if="EsCredito"
                                type="date"
                                placeholder="Fecha Vencimiento"></b-form-input>
                            </b-form-group>
                          </b-col>
                        </b-row>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col>
                        <productos-carrito
                            @onAddProducto="onAddProducto"
                            :setPrecio="true"
                            ref="carrito"
                            :isVentas="false"
                            :isOrdenCompra="true"
                            :showButton="false"
                            :bloquearInput="false"
                            :fields="carrito.fields"
                            :idAlmacen="1"
                            :onSetProducto="onSetProducto"
                            :TipoCambio="TipoCambio"
                            >
                        </productos-carrito>
                    </b-col>
                </b-row>
                <b-row align-h="center" class="text-center">
                    <b-col cols="8">
                        <br />
                        <b-button type="submit" :disabled="inProgress" variant="success" size="lg">Generar Orden</b-button>
                        <br />
                        <br />
                    </b-col>
                </b-row>
            </b-form>
        </b-container>
    </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarrito";

export default {
  beforeMount() {
    //this.initForm()
  },
  mounted() {
    this.obtnerTipoCambio();
  },
  data() {
    return {
      TipoCambio: 0,
      EsCredito: 0,
      FechaVenCredito: "",
      inProgress: false,
      newRuc: "",
      API: config.API,
      ultimoMovimiento: "",
      movimiento: this.initMovimiento(),
      carrito: {
        fields: [
          { key: "CodigoFrabricante", label: "Cód. Fabricante" },
          { key: "CodigoInterno", label: "Cód. Interno" },
          { key: "CodigoBarra", label: "Código" },
          { key: "Producto" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoFormaFarmaceutica", label: "Modelo" },
          { key: "ProductoMedicion", label: "U. Med." },
          {
            key: "StockMinimo",
            label: "Stock Min.",
            variant: "warning",
            class: "text-center fontsize "
          },
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center fontsize "
          },
          { key: "cantidad" },
          { key: "precio", label: "Precio Compra" },
          { key: "igv", label: "IGV" },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center fontsize"
          },
          { key: "acciones", label: "" }
        ]
      },
      idPerfil: sessionStorage.getItem("IdPerfil"),
      Moneda: {
        Moneda: "USD"
      }
    };
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
    async obtnerTipoCambio() {
      await this.axios("https://deperu.com/api/rest/cotizaciondolar.json").then(
        res => {
          this.tipocambio = res.data.Cotizacion[0].Venta;
          this.TipoCambio = res.data.Cotizacion[0].Venta;
        }
      );
    },
    setMoneda(obj) {
      this.Moneda = obj.Moneda;
      if (obj.Moneda === "USD") {
        this.TipoCambio = 3.7;
      } else {
        this.TipoCambio = 1;
      }
    },
    cargarProducto() {
      this.axios
        .get(this.API + "productos/proveedor", {
          params: {
            IdProveedor: this.movimiento.proveedor.IdProveedor,
            IdAlmacen: this.movimiento.almacen.IdAlmacen
          }
        })
        .then(response => {
          this.limpiarCart();
          response.data.map(item => {
            this.add([item, () => {}, false, true]);
          });

          this.obtenerStok({
            IdAlmacen: this.movimiento.almacen.IdAlmacen,
            CalculoCantidad: 1
          });
        });
    },
    setProveedor(obj) {
      this.movimiento.proveedor = obj;
      this.$store.state.cart.productos = [];
    },
    initMovimiento() {
      let currentMonth = this.$moment().month() + 1;
      currentMonth = currentMonth < 10 ? "0" + currentMonth : currentMonth;
      return {
        MovimientoFecha:
          this.$browser.name == "chrome"
            ? this.$moment().format("YYYY-MM-DDThh:mm:ss")
            : this.$moment().format("YYYY-MM-DD hh:mm:ss"),
        proveedor: { IdProveedor: 0 },
        movimientoTipo: {},
        almacen: { IdAlmacen: 0 },
        user: sessionStorage.getItem("User")
      };
    },
    onSetProducto(obj) {
      obj.cantidad = 0;
      obj.precio = obj.PrecioCosto || 0;
      return obj;
    },
    onAddProducto(obj) {
      if (this.movimiento.almacen.IdAlmacen > 0) {
        this.obtenerStok({
          IdAlmacen: this.movimiento.almacen.IdAlmacen,
          CalculoCantidad: 1
        });
      }
    },
    setAlmacen(obj) {
      this.movimiento.almacen = obj;
      this.$refs.inputAlmacen.setValue(obj.Almacen);
      if (this.productos.length) {
        this.obtenerStok({
          IdAlmacen: this.movimiento.almacen.IdAlmacen,
          CalculoCantidad: 1
        });
      }
    },
    async guardarMovimiento(evt) {
      evt.preventDefault();
      this.inProgress = true;

      if (!this.movimiento.proveedor.IdProveedor) {
        this.$toasted.show("Por favor seleccione un proveedor");
        this.inProgress = false;
        return;
      }

      if (!(this.movimiento.almacen.IdAlmacen > 0)) {
        this.$toasted.show("Por favor seleccione un almacen");
        this.inProgress = false;
        return;
      }

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto");
        this.inProgress = false;
        return;
      }

      let faltaLlenar = false;
      let faltaCantidad = false;
      this.productos.forEach((prod, index) => {
        if (prod.total <= 0) {
          faltaLlenar = true;
        }

        if (!(prod.cantidad > 0)) {
          faltaCantidad = true;
        }
      });

      if (faltaCantidad) {
        this.$toasted.show("Complete la cantidad de los productos ingresados");
        this.inProgress = false;
        return;
      }

      if (faltaLlenar) {
        this.$toasted.show("Falta llenar cantidad / precio");
        this.inProgress = false;
        return;
      }
      let mov = { ...this.movimiento };
      mov.TipoCambio = this.TipoCambio;
      mov.EsCredito = this.EsCredito;
      mov.FechaVenCredito = this.FechaVenCredito;
      mov.Moneda = this.Moneda.Moneda;
      mov.User = sessionStorage.getItem("User");
      await this.axios
        .post(this.API + "ordencompra", {
          movimiento: mov,
          productos: this.productos,
          IdAlmacen: this.movimiento.almacen.IdAlmacen
        })
        .then(async response => {
          if (response.data.insertId) {
            await this.axios(`${this.API}reporte/ordencomprapdf`, {
              params: {
                id: response.data.insertId
              }
            }).then(res => {
              if (res.data) {
                console.log(res.data);
                let url = res.data;
                url = url.replace(/\\/g, "");
                if (!url.startsWith("http://") && !url.startsWith("https://")) {
                  url = "http://" + url; // O "https://" si es necesario
                }
                window.open(url, "_blank");
              }
            });
            this.movimiento = this.initMovimiento();
            this.$refs.inputProveedor.setValue("");
            this.$refs.inputAlmacen.setValue("");
            this.limpiarCart();
            this.$toasted.success("Orden de compra guardada correctamente");
          } else {
            this.$toasted.error("Ha ocurrido un error");
          }

          this.inProgress = false;
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    focusAutocomplete(ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    }
  },
  components: {
    ProductosCarrito
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
td.fontsize {
  font-size: 0.9rem !important;
  font-weight: bold;
  text-align: center;
  vertical-align: middle !important;
}
</style>
