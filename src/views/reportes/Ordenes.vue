<template>
    <div>
      <br>
        <b-container fluid style="width: calc(100% - 20px)" class="bv-example-row">
          <b-row class="text-center">
            <b-col>
              <h3>Registro de Ordenes de Compra</h3>
            </b-col>
          </b-row>
          <hr>
          <b-row>
            <b-col md="2" class="my-1">
              <b-form-group>
                <label><b>Estado:</b></label>
                <b-form-select v-model="estadoSelect" :options="estadoData" class="mb-3" v-on:change="cambioEstado($event)"/>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Inicio:</label>
                <b-form-input
                  v-model="fechaDesde"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="3" class="my-1">
              <b-form-group>
                <label>Fecha Fin:</label>
                <b-form-input
                  v-model="fechaHasta"
                  type="date"
                  step="1"
                  placeholder="Fecha Inicio"></b-form-input>
              </b-form-group>
            </b-col>
            <b-col md="4" class="my-1">
                <br>
              <b-button variant="success" size="md" @click="verArticulos">Generar</b-button>
            </b-col>
          </b-row>

          <b-row>
            <table class="table b-table table-striped table-hover table-sm">
              <thead>
                <tr>
                  <th class="text-center" style="width: 4% !important">ID</th>
                  <th>Correlativo</th>
                  <th>Fecha</th>
                  <th>Ruc</th>
                  <th>Proveedor</th>
                  <th>Total</th>
                  <th>Ver</th>
                  <th>Editar</th>
                  <th colspan="2" class="text-center">Reporte</th>
                  <th>Anular</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(prod, i) in productosInf" :key="i">
                  <td class="text-center" :class="{'table-danger': prod.Estado == 1}" style="width: 5% !important;">{{ prod.IdOrdenCompra }}</td>
                  <td :class="{'table-danger': prod.Estado == 1}">{{ prod.Correlativo }}</td>
                  <td :class="{'table-danger': prod.Estado == 1}">{{ prod.FechaOrdenCompra }}</td>
                  <td :class="{'table-danger': prod.Estado == 1}">{{ prod.Ruc }}</td>
                  <td :class="{'table-danger': prod.Estado == 1}">{{ prod.Proveedor }}</td>
                  <td :class="{'table-danger': prod.Estado == 1}">{{prod.Moneda}} {{ prod.Total | moneda }}</td>
                  <td :class="{'table-danger': prod.Estado == 1}"><b-button variant="primary" @click="mostrarDetalles(prod)"><i class="fas fa-eye"></i> Ver</b-button></td>
                  <td :class="{'table-danger': prod.Estado == 1}"><b-button variant="info" v-if="prod.Estado == 0" @click="openEditar(prod)"><i class="fas fa-pencil-alt"></i> Editar</b-button></td>
                  <td :class="{'table-danger': prod.Estado == 1}"><a v-if="prod.Estado == 0" class="btn btn-warning" @click="generarPdf(prod.IdOrdenCompra)" ><i class="fas fa-file-pdf"></i> Pdf</a></td>
                  <td :class="{'table-danger': prod.Estado == 1}"><a v-if="prod.Estado == 0" class="btn btn-success" target="_blank" :href="'/api/index.php/reporte/ordencompra/id?idordencompra=' + prod.IdOrdenCompra"><i class="fas fa-file-excel"></i> Excel</a></td>
                  <td :class="{'table-danger': prod.Estado == 1}"><b-button  v-if="prod.Estado == 0" variant="danger" @click="anularOrden(prod)"><i class="fas fa-ban"></i> Anular</b-button></td>
                </tr>
              </tbody>
            </table>
          </b-row>
          <b-modal size="lg" id="modalDetalle" ref="modalDetalle" :title="'Orden de Compra: '+infoOrden.Correlativo" ok-only ok-title="Cerrar">
            <b-row>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">RUC</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Ruc }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Proveedor</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Proveedor }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Dirección</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Direccion }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>

              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Almacen</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Almacen }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaOrdenCompra }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Moneda</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Moneda }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            <br>
            <b-row>
              <b-col md="12">
                <table class="table b-table table-sm table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">Codigo</th>
                            <th class="text-center">Producto</th>
                            <th class="text-center">Laboratorio</th>
                            <th class="text-center">Forma</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Precio</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(prod, i) in detalle" :key="i">
                            <td class="text-center">{{ prod.CodigoBarra }}</td>
                            <td class="text-center">{{ prod.Producto }}</td>
                            <td class="text-center">{{ prod.ProductoMarca }}</td>
                            <td class="text-center">{{ prod.ProductoFormaFarmaceutica }}</td>
                            <td class="text-center">{{ prod.Cantidad }}</td>
                            <td class="text-center">{{ prod.Precio }}</td>
                            <td class="text-center">{{ (prod.Precio *  prod.Cantidad) | moneda }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                    <tr>
                      <td colspan="5"></td>
                      <td class="text-center"><b>TOTAL</b></td>
                      <td class="table-success text-center" style="vertical-align: middle !important;">{{infoOrden.Total | moneda}} </td>
                    </tr>
                  </tfoot>
                </table>
              </b-col>
            </b-row>
          </b-modal>
          <b-form @submit="actualizarDet" validated @keydown.enter="prevent">
          <b-modal size="lg" id="modalEdicion" ref="modalEdicion" :title="'Editar detalle Compra: '+infoOrden.Correlativo" ok-only ok-title="Cerrar">
            <b-row>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">RUC</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Ruc }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Proveedor</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Proveedor }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Dirección</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Direccion }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
              <b-col md="6">
                <table>
                  <tbody>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Almacen</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Almacen }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Fecha</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaOrdenCompra }}</td>
                    </tr>
                    <tr>
                      <td class="celda" style="font-weight:bold; width:35% !important;">Moneda</td>
                      <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Moneda }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            <br>
            <b-row>
              <b-col md="12">
                <productos-carrito
                  @onAddProducto="onAddProducto"
                  :setPrecio="true"
                  ref="carrito"
                  :isVentas="false"
                  :isOrdenCompra="true"
                  :showButton="false"
                  :bloquearInput="false"
                  :fields="carrito.fields"
                  :idAlmacen="infoOrden.IdAlmacen"
                  :onSetProducto="onSetProducto">
                </productos-carrito>
              </b-col>
            </b-row>
            <div slot="modal-footer" >
            <b-btn  variant="success" :disabled="inProgress" type="submit">
              Actualizar
            </b-btn>
            <b-btn  variant="secondary" @click="()=>{$refs.modalEdicion.hide()}">
              Cerrar
            </b-btn>
          </div>
          </b-modal>
          </b-form>

        </b-container>
    </div>
</template>
<script>
import config from "@/store/config.js";
import ProductosCarrito from "@/components/ProductosCarrito";
import { mapActions, mapState } from "vuex";
export default {
  mounted() {
    this.verArticulos();
  },
  data() {
    return {
      API: config.API,
      productosInf: [],
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      detalle: [],
      detalleEdit: [],
      infoOrden: {
        Anio: null,
        Correlativo: null,
        Direccion: null,
        EsCredito: null,
        FechaCredito: null,
        FechaOrdenCompra: null,
        FechaRecojo: null,
        IdOrdenCompra: null,
        IdProveedor: null,
        Almacen: "",
        NumCuenta: null,
        Numero: null,
        Proveedor: null,
        Ruc: null,
        Telefono: null,
        UsuarioReg: null
      },
      carrito: {
        fields: [
          { key: "CodigoBarra", label: "Código" },
          { key: "Producto" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoFormaFarmaceutica", label: "Lab." },
          { key: "ProductoMedicion", label: "Med." },
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
      inProgress: false,
      estadoData: [
        { value: -1, text: "TODOS" },
        { value: 0, text: "ACTIVOS" },
        { value: 1, text: "ANULADOS" }
      ],
      estadoSelect: -1
    };
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos,
      totalCart: state => state.cart.totalCart
    }),
    hrefXls() {
      return (
        "/api/index.php/reporte/ordenescompra?fechaDesde=" +
        this.fechaDesde +
        "&fechaHasta=" +
        this.fechaHasta
      );
    }
  },
  methods: {
    ...mapActions({
      aplicarDescuento: "aplicarDescuentoGeneral",
      limpiarCart: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      add: "agregarProducto"
    }),
    cambioEstado() {},
    async generarPdf(IdOrdenCompra) {
      console.log("IdOrdenCompra", IdOrdenCompra);
      await this.axios(`${this.API}reporte/ordencomprapdf`, {
        params: {
          id: IdOrdenCompra
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
    },
    actualizarDet(e) {
      e.preventDefault();
      this.inProgress = true;

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto");
        this.inProgress = false;
        return;
      }

      let faltaLlenar = false;
      let faltaCantidad = false;
      this.productos.forEach((prod, index) => {
        if (!(prod.precio > 0)) {
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
        this.$toasted.show("Falta ingresar el precio");
        this.inProgress = false;
        return;
      }

      this.axios
        .post(this.API + "ordencompra/actualizar", {
          productos: this.productos,
          idOrdenCompra: this.infoOrden.IdOrdenCompra
        })
        .then(response => {
          if (response.data.success) {
            this.verArticulos();
            this.$refs.modalEdicion.hide();
            this.$toasted.info(response.data.msg);
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
    prevent(evt) {
      if (evt.target.tagName !== "TEXTAREA") {
        evt.preventDefault();
      }
    },
    onSetProducto(obj) {
      obj.cantidad = 0;
      obj.precio = obj.PrecioCosto || 0;
      return obj;
    },
    onAddProducto(obj) {
      if (this.infoOrden.IdAlmacen > 0) {
        this.obtenerStok({
          IdAlmacen: this.infoOrden.IdAlmacen,
          CalculoCantidad: 0
        });
      }
    },
    openEditar(data) {
      this.limpiarCart();
      this.detalleEdit = [];
      this.axios
        .get(this.API + "ordencompra/detalle", {
          params: {
            IdOrdenCompra: data.IdOrdenCompra
          }
        })
        .then(response => {
          this.detalleEdit = response.data;
          this.infoOrden = data;
          this.limpiarCart();
          response.data.map(item => {
            this.add([item, () => {}, false, true]);
            if (this.infoOrden.IdAlmacen > 0) {
              this.obtenerStok({
                IdAlmacen: this.infoOrden.IdAlmacen,
                CalculoCantidad: 0
              });
            }
          });
          this.$refs["modalEdicion"].show();
        })
        .catch(error => {
          console.log(error);
        });
    },
    anularOrden(orden) {
      let r = confirm(
        "¿Está seguro de anular la orden: " + orden.Correlativo + "?"
      );
      if (r) {
        this.axios
          .post(this.API + "ordencompra/anular", {
            idOrdenCompra: orden.IdOrdenCompra
          })
          .then(res => {
            this.verArticulos();
          })
          .catch(error => {
            this.$toasted.error("Ha ocurrido un error");
          });
      }
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    mostrarDetalles(data) {
      this.axios
        .get(this.API + "ordencompra/detalle", {
          params: {
            IdOrdenCompra: data.IdOrdenCompra
          }
        })
        .then(response => {
          this.detalle = response.data;
          this.infoOrden = data;
          this.$refs["modalDetalle"].show();
        })
        .catch(error => {
          console.log(error);
        });
    },
    verArticulos() {
      this.productosInf = [];
      this.axios
        .get(this.API + "ordencompra", {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta,
            estado: this.estadoSelect
          }
        })
        .then(response => {
          this.productosInf = response.data;
        });
    }
  },
  components: {
    ProductosCarrito
  }
};
</script>
<style>
#modalDetalle .modal-dialog {
  max-width: 75% !important;
}
#modalEdicion .modal-dialog {
  max-width: 75% !important;
}
</style>
