<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <div style="text-align: end;">
      <b-button variant="info" @click="abrirModalOrdenCompra">Orden de Compra</b-button>
    </div>
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
        <b-container fluid class="ancho bv-example-row">
          <b-row>
              <b-col md="8">

                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Tipo de Movimiento:</label>
                      <autocomplete
                        required="required"
                        ref="inputMovimiento"
                        :url="API + 'movimientos/tipos'"
                        anchor="TipoMovimiento"
                        label="writer"
                        placeholder=""
                        :on-select="setTipoMovimiento"
                        :onFocus="focusAutocomplete.bind(null, 'inputMovimiento')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="8">
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
                          :onInput="(val) => newRuc = val"                          
                          :on-select="setProveedor"
                          :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                          :classes="{input: 'form-control'}">
                        </autocomplete>

                        <b-input-group-append>
                          <!--<proveedores-tabla></proveedores-tabla>-->
                          <proveedores-form :ruc="newRuc"></proveedores-form>
                        </b-input-group-append>
                      </b-input-group>
                    </b-form-group>
                  </b-col>
                </b-row>

                <b-row>
                  <b-col md="3">
                    <b-form-group>
                      <label>Periodo Tributario:</label>
                      <b-form-input v-model="movimiento.FechaPeriodoTributario"
                        type="text"
                        readonly="readonly"
                        placeholder="Periodo Tributario"></b-form-input>
                    </b-form-group>
                    
                  </b-col>
                  <b-col md="3">
                    <b-form-group
                      v-if="movimiento.movimientoTipo.Tipo == 1 || movimiento.movimientoTipo.Tipo == 2">
                      <label>Almacen Origen:</label>
                      <autocomplete
                        ref="inputAlmacenOrigen"
                        required="required"
                        initValue=""
                        :url="API + 'almacenes'"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenOrigen"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenOrigen')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="3">
                    <b-form-group
                      v-if="movimiento.movimientoTipo.Tipo == 0 || movimiento.movimientoTipo.Tipo == 2">
                      <label>Almacen Destino:</label>
                      <autocomplete
                        ref="inputAlmacenDestino"
                        required="required"
                        initValue=""
                        :url="API + 'almacenes'"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenDestino"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenDestino')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="3">
                    <b-form-group label="Credito: ">
                      <b-form-checkbox 
                        v-model="movimiento.EsCredito"
                        :value="1"
                        :unchecked-value="0">
                        Pago pendiente
                      </b-form-checkbox>
                      <b-form-input v-model="movimiento.FechaVenCredito"
                        v-if="movimiento.EsCredito"
                        type="date"
                        placeholder="Fecha Vencimiento"></b-form-input>
                    </b-form-group>
                  </b-col>
                </b-row>

                <b-row v-if="!isTraslado">
                  <b-col md="6">
                    <b-form-group>
                      <b-form-checkbox id="alterar-productos"
                        v-model="movimiento.alterarProductos"
                        :value="1"
                        :unchecked-value="0">
                        Alterar precio productos
                      </b-form-checkbox>
                    </b-form-group>
                  </b-col>
                </b-row>
            
              </b-col>
              <b-col md="4">
                <b-row>
                  <b-col md="4">
                    <b-form-group>
                      <label>Serie:</label>                    
                      <b-form-input v-model="movimiento.Serie"
                        required="required"
                        @input="cargarNumero"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Número:</label>                    
                      <b-form-input v-model="movimiento.Numero"
                        required="required"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Moneda:</label>
                      <autocomplete
                        :url="API + 'monedas'"
                        initValue="PEN"
                        anchor="Moneda"
                        label="writer"
                        placeholder="Moneda"
                        :on-select="setMoneda"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="8">
                    <b-form-group>
                      <label>Fecha Contable:</label>
                      <b-form-input
                        v-model="movimiento.MovimientoFecha"
                        type="datetime-local"
                        step="1"
                        placeholder="Fecha Contable"
                        ></b-form-input>

                    </b-form-group>
                    <b-form-group>
                      <label>Fecha Stock:</label>
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"
                        ></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Tipo de cambio:</label>
                      <input v-model.number="movimiento.TipoCambio"
                        type="number"
                        class="form-control"
                        step="0.0001"
                        placeholder="Tipo de cambio"/>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-col>
          </b-row>
          <b-row>
            <b-col>
              <productos-carrito 
                ref="carrito"
                @onAddProducto="onAddProducto" 
                :tipoCambio="movimiento.TipoCambio"
                tipo="mov" :tipoMov="this.movimiento.movimientoTipo.Tipo" :onSetProducto="onSetProducto"></productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Guardar</b-button>
              <b-button v-if="ultimoMovimiento" target="_blank" variant="info" size="lg" :href="'/views/codigoBarra.php?hash=' + ultimoMovimiento">Código de Barra</b-button>
            </b-col>
          </b-row>
        </b-container>
        <br><br>
    </b-form>

    <b-modal v-model="modalMovimientos" id="modalOrdenCompra" no-close-on-backdrop no-close-on-esc>
          <b-table :items="productosInf"  :fields="fieldsOrdenCompra">
            <template slot="action" slot-scope="data">
              <b-button variant="info" @click="addPreOrdenCompra(data.item)"><i class="fa fa-plus" aria-hidden="true"></i></b-button>
            </template>
          </b-table>
    </b-modal>

  </div>
</template>

<script>
import Menu from "@/components/Menu";
import ProductosCarrito from "@/components/ProductosCarrito";
//import ProveedoresTabla from 'components/ProveedoresTabla'
import ProveedoresForm from "@/components/ProveedoresForm";
import { mapState, mapActions } from "vuex";
import config from "@/store/config.js";

export default {
  mounted() {},
  data() {
    return {
      fieldsOrdenCompra: [
        { key: "Correlativo" },
        { key: "Ruc" },
        { key: "Total" },
        { key: "FechaReg", label: "Fecha" },
        { key: "Proveedor" },
        { key: "action", label: "" }
      ],
      ordenCompra: {},
      productosInf: [],
      inProgress: false,
      newRuc: "",
      API: config.API,
      ultimoMovimiento: "",
      movimiento: this.initMovimiento(),
      isTraslado: true,
      isSalida: false,
      modalMovimientos: false
    };
  },
  computed: {
    ...mapState({
      proveedoresFiltrados: state => state.proveedoresFiltrados,
      almacenes: state => state.almacenesFiltrados,
      productos: state => state.cart.productos
    })
  },
  methods: {
    ...mapActions({
      buscarProveedores: "buscarProveedores",
      buscarAlmacenes: "buscarAlmacenes",
      limpiar: "limpiarCarrito",
      obtenerStok: "obtenerStock"
    }),
    async abrirModalOrdenCompra() {
      this.verArticulos();
      this.modalMovimientos = true;
    },
    verArticulos() {
      this.productosInf = [];
      this.axios
        .get(this.API + "movimiento/ordencompra", {
          params: {
            // fechaDesde: "1990-01-01",
            fechaHasta: this.$moment().format("YYYY-MM-DD"),
            estado: 1
          }
        })
        .then(response => {
          this.productosInf = response.data;
        });
    },
    addPreOrdenCompra(item) {
      console.log(item);
      let proveedor = {
        IdProveedor: item.IdProveedor,
        Proveedor: item.Proveedor
      };
      this.ordenCompra = item;
      console.log(this.ordenCompra);
      this.setProveedor(proveedor);
      this.$refs.inputProveedor.setValue(item.Proveedor);
      this.mostrarDetalles(item);
      this.modalMovimientos = false;
    },
    mostrarDetalles(data) {
      this.$store.state.cart.productos = [];
      this.axios
        .get(this.API + "ordencompra/detalle", {
          params: {
            IdOrdenCompra: data.IdOrdenCompra,
            IdAlmacen: this.movimiento.almacenDestino.IdAlmacen
          }
        })
        .then(response => {
          let detalle = response.data;
          detalle.forEach(element => {
            element.Presentacion = "UNIDAD";
            this.$store.state.cart.productos.unshift(element);
          });
          console.log(detalle);
        })
        .catch(error => {
          console.log(error);
        });
    },

    onSetProducto(obj, cant, precio) {
      if (cant) {
        obj.cantidad = cant;
        obj.precio = obj.PrecioCosto || 0;
        obj.nuevoPrecioContado = obj.PrecioContado || 0;
        return obj;
      }
      obj.cantidad = 1;
      obj.precio = obj.PrecioCosto || 0;
      obj.nuevoPrecioContado = obj.PrecioContado || 0;
      return obj;
    },
    onAddProducto(obj) {
      if (this.isSalida) {
        this.obtenerStok({
          IdAlmacen:
            this.movimiento.almacenOrigen.IdAlmacen > 0
              ? this.movimiento.almacenOrigen.IdAlmacen
              : 1
        });
        console.log(this.movimiento.almacenOrigen.IdAlmacen);
      } else {
        this.obtenerStok({
          IdAlmacen: !this.isTraslado
            ? this.movimiento.almacenDestino.IdAlmacen > 0
              ? this.movimiento.almacenDestino.IdAlmacen
              : 1
            : this.movimiento.almacenOrigen.IdAlmacen > 0
              ? this.movimiento.almacenOrigen.IdAlmacen
              : 1
        });
      }
    },
    afe(e) {
      return false;
    },
    cargarNumero() {
      this.axios
        .get(this.API + "movimientos/numero", {
          params: {
            serie: this.movimiento.Serie,
            idMovimientoTipo: this.movimiento.movimientoTipo.IdMovimientoTipo
          }
        })
        .then(response => {
          Vue.set(
            this.movimiento,
            "Numero",
            response.data.NuevoNumero || "0001"
          );
        })
        .catch(error => {
          console.log(error);
          // this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        });
    },
    initMovimiento() {
      let currentMonth = this.$moment().month() + 1;
      currentMonth = currentMonth < 10 ? "0" + currentMonth : currentMonth;
      return {
        EsCredito: 0,
        alterarProductos: 1,
        FechaVenCredito: "",
        Moneda: "PEN",
        FechaPeriodoTributario: this.$moment().year() + "" + currentMonth,
        // MovimientoFecha: this.$moment().format("YYYY-MM-DDTH:mm"),
        MovimientoFecha:
          this.$browser.name == "chrome"
            ? this.$moment().format("YYYY-MM-DDThh:mm:ss")
            : this.$moment().format("YYYY-MM-DD hh:mm:ss"),
        FechaStock: this.$moment().format("YYYY-MM-DD"),
        TipoCambio: 1,
        proveedor: { IdProveedor: 0 },
        movimientoTipo: {},
        almacenOrigen: { IdAlmacen: -1 },
        almacenDestino: { IdAlmacen: -1 },
        user: sessionStorage.getItem("User")
      };
    },
    onReset(evt) {
      evt.preventDefault();
      /* Reset our form values */
      /*this.form.email = '';
      this.form.name = '';
      this.form.food = null;
      this.form.checked = [];
      /* Trick to reset/clear native browser form validation state */
      /*this.show = false;
      this.$nextTick(() => { this.show = true });*/
    },
    setTipoMovimiento(obj) {
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 };
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.isTraslado = false;
        this.isSalida = false;
        if (this.$refs.inputAlmacenDestino) {
          this.$refs.inputAlmacenDestino.setValue("");
        }
      } else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
        if (this.$refs.inputAlmacenOrigen) {
          this.$refs.inputAlmacenOrigen.setValue("");
        }
        this.isTraslado = false;
        this.isSalida = true;
      } else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
        this.isTraslado = true;
        this.isSalida = false;
        if (this.$refs.inputAlmacenDestino) {
          this.$refs.inputAlmacenDestino.setValue("");
        }
        if (this.$refs.inputAlmacenOrigen) {
          this.$refs.inputAlmacenOrigen.setValue("");
        }
      }
      this.movimiento.movimientoTipo = obj;

      this.cargarNumero();
    },
    setProveedor(obj) {
      this.movimiento.proveedor = obj;
    },
    setAlmacenOrigen(obj) {
      this.movimiento.almacenOrigen = obj;
      if (this.isSalida) {
        this.obtenerStok({
          IdAlmacen:
            this.movimiento.almacenOrigen.IdAlmacen > 0
              ? this.movimiento.almacenOrigen.IdAlmacen
              : 1
        });
      } else {
        this.obtenerStok({
          IdAlmacen: !this.isTraslado
            ? this.movimiento.almacenDestino.IdAlmacen > 0
              ? this.movimiento.almacenDestino.IdAlmacen
              : 1
            : this.movimiento.almacenOrigen.IdAlmacen > 0
              ? this.movimiento.almacenOrigen.IdAlmacen
              : 1
        });
      }
    },
    setAlmacenDestino(obj) {
      this.movimiento.almacenDestino = obj;
      this.obtenerStok({
        IdAlmacen: !this.isTraslado
          ? this.movimiento.almacenDestino.IdAlmacen > 0
            ? this.movimiento.almacenDestino.IdAlmacen
            : 1
          : this.movimiento.almacenOrigen.IdAlmacen > 0
            ? this.movimiento.almacenOrigen.IdAlmacen
            : 1
      });
    },
    setMoneda(obj) {
      this.movimiento.Moneda = obj.Moneda;
      if (obj.Moneda === "USD") {
        this.movimiento.TipoCambio = 3.7;
      } else {
        this.movimiento.TipoCambio = 1;
      }
    },
    getProveedores(value) {
      this.buscarProveedores(value);
    },
    getAlmacenes(value) {
      this.buscarAlmacenes(value);
    },
    guardarMovimiento(evt) {
      evt.preventDefault();
      this.inProgress = true;

      if (!this.movimiento.proveedor.IdProveedor) {
        this.$toasted.show("Por favor seleccione un proveedor");
        this.inProgress = false;
        return;
      }

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto");
        this.inProgress = false;
        return;
      }

      let faltaLlenar = false;
      let sinStock = 0;
      let faltaCantidad = false;
      this.productos.forEach((prod, index) => {
        if (
          parseFloat(prod.stock) <= 0 ||
          parseFloat(prod.stock).toFixed(2) < parseFloat(prod.cantidad) ||
          prod.stock == undefined
        ) {
          sinStock += 1;
        }

        if (prod.total <= 0 && prod.EsObsequio != 1) {
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

      if (faltaLlenar && this.movimiento.alterarProductos) {
        this.$toasted.show("Falta llenar cantidad / precio");
        this.inProgress = false;
        return;
      }

      if (sinStock >= 1 && (this.isTraslado == true || this.isSalida == true)) {
        this.$toasted.show("No tiene stock para realizar el movimiento");
        this.inProgress = false;
        return;
      }

      if (this.isTraslado == true || this.isSalida == true) {
        this.movimiento.alterarProductos = 0;
      }

      this.axios
        .post(this.API + "movimientos", {
          movimiento: this.movimiento,
          productos: this.productos,
          ordenCompra: this.ordenCompra
        })
        .then(response => {
          if (response.data.error) {
            this.$toasted.show(response.data.error);
          } else {
            this.ultimoMovimiento = response.data.hash;
            this.movimiento = this.initMovimiento();
            this.limpiar();
            this.$refs.inputMovimiento.setValue("");
            this.$refs.inputProveedor.setValue("");
            this.$toasted.success("Movimiento guardado correctamente ");
          }
          this.inProgress = false;
        })
        .catch(error => {
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
    Menu,
    ProductosCarrito,
    ProveedoresForm
  }
};
</script>
<style>
.input-group {
  /*margin: 0.25rem 0 !important;*/
}
@media only screen and (min-width: 777px) and (max-width: 1800px) {
  /* For desktop: */
  .ancho {
    width: calc(100% - 200px);
  }
}

#modalOrdenCompra .modal-dialog {
  min-width: 70% !important;
}
</style>
