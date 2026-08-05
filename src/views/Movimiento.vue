<template>
  <div class="inventory-movement-page" :class="{ 'is-data-collapsed': datosOcultos }">
    <Menu v-if="false"></Menu>
    <b-form class="inventory-movement-form" @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated novalidate>
        <b-container class="inventory-shell" fluid>
          <div class="inventory-heading">
            <div class="inventory-heading__icon"><i class="fas fa-boxes"></i></div>
            <div>
              <span class="inventory-heading__eyebrow">INVENTARIO INTELIGENTE</span>
              <h1>Nuevo movimiento de inventario</h1>
              <p>Registra entradas, salidas o transferencias y mantén el stock actualizado.</p>
            </div>
            <div class="inventory-heading__actions">
              <div class="inventory-heading__status"><span></span> Movimiento en edición</div>
              <b-button class="inventory-toggle-data" type="button" @click="datosOcultos = !datosOcultos">
                <i :class="datosOcultos ? 'fas fa-eye' : 'fas fa-eye-slash'"></i>
                {{ datosOcultos ? 'Mostrar datos' : 'Ocultar datos' }}
              </b-button>
            </div>
          </div>
          <b-row v-show="!datosOcultos" class="inventory-data-grid">
              <b-col lg="8" class="inventory-card inventory-card--primary">
                <b-row class="inventory-form-row">
                  <b-col md="4">
                    <b-form-group class="inventory-field inventory-field--movement">
                      <label>Tipo de Movimiento:</label>
                      <autocomplete
                        required="required"
                        ref="inputMovimiento"
                        :url="API + 'movimientos/tipos'"
                        anchor="TipoMovimiento"
                        label="writer"
                        placeholder="Selecciona el tipo de movimiento"
                        :on-select="setTipoMovimiento"
                        :onFocus="focusAutocomplete.bind(null, 'inputMovimiento')"
                        :classes="{input: 'form-control inventory-autocomplete-input'}">
                      </autocomplete>
                    </b-form-group>
                  </b-col>
                  <b-col md="8">
                    <b-form-group class="inventory-field inventory-field--supplier">
                      <label>Proveedor:</label>
                      <b-input-group class="inventory-supplier-control">
                        <autocomplete
                          required="required"
                          ref="inputProveedor"
                          :url="API + 'proveedores'"
                          anchor="ProveedorRuc"
                          label="writer"
                          placeholder="Busca por RUC o razón social"
                          :onInput="(val) => newRuc = val"                          
                          :on-select="setProveedor"
                          :onFocus="focusAutocomplete.bind(null, 'inputProveedor')"
                          :classes="{input: 'form-control inventory-autocomplete-input'}">
                        </autocomplete>

                        <b-input-group-append>
                          <!--<proveedores-tabla></proveedores-tabla>-->
                          <proveedores-form :ruc="newRuc"></proveedores-form>
                        </b-input-group-append>
                      </b-input-group>
                    </b-form-group>
                  </b-col>
                </b-row>

                <b-row class="inventory-form-row">
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
                        initValue="PRINCIPAL"
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
                        initValue="PRINCIPAL"
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
                      <b-form-checkbox id="checkbox1"
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

                <b-row>
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
              <b-col lg="4" class="inventory-card inventory-card--document">
                <b-row class="inventory-form-row">
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
                        disabled="disabled"></b-form-input>

                    </b-form-group>
                    <b-form-group>
                      <label>Fecha Stock:</label>
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"
                        disabled="disabled"></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Tipo de cambio:</label>
                      <input v-model="movimiento.TipoCambio"
                        type="number"
                        step="0.0001"
                        placeholder="Tipo de cambio"
                        class="form-control"/>
                    </b-form-group>
                  </b-col>
                </b-row>
              </b-col>
          </b-row>
          <b-row no-gutters class="inventory-products-section">
            <b-col cols="12">
              <productos-carrito ref="carrito" tipo="mov" :fields="carrito.fields" @onAddProducto="onAddProducto" :idAlmacen="IdAlmacen">
                <template slot="movimientoAcciones">
                  <b-button class="inventory-save-button inventory-save-button--inline" variant="success" type="submit" :disabled="inProgress"><i class="fas fa-save"></i><span>Guardar movimiento</span></b-button>
                  <b-button class="inventory-barcode-button inventory-save-button--inline" v-if="ultimoMovimiento" target="_blank" variant="info" :href="'/views/codigoBarra.php?hash=' + ultimoMovimiento"><i class="fas fa-barcode"></i> Código de barra</b-button>
                </template>
              </productos-carrito>
            </b-col>
          </b-row>
          <b-row align-h="center" class="inventory-submit-row">
            <b-col cols="12" class="text-center">
              <b-button class="inventory-save-button" variant="success" size="lg" type="submit" :disabled="inProgress"><i class="fas fa-save"></i><span>Guardar movimiento</span></b-button>
              <b-button class="inventory-barcode-button" v-if="ultimoMovimiento" target="_blank" variant="info" size="lg" :href="'/views/codigoBarra.php?hash=' + ultimoMovimiento"><i class="fas fa-barcode"></i> Código de barra</b-button>
            </b-col>
          </b-row>
        </b-container>
        <br><br>
    </b-form>

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
      IdAlmacen: sessionStorage.getItem("IdAlmacen"),
      carrito: {
        fields: [
          {
            key: "stock",
            label: "Stock",
            variant: "info",
            class: "text-center"
          },
          { key: "ProductoCategoria", label: "Categoria" },
          { key: "Producto", label: "Articulo" },
          { key: "eventPres", label: "Img" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoModelo", label: "Modelo" },
          { key: "Color", label: "Color" },
          { key: "ProductoTalla", label: "Talla" },
          { key: "Genero" },
          { key: "Botapie", label: "Botapie" },
          { key: "cantidad" },
          { key: "precio", label: "P/U" },
          {
            key: "subtotal",
            label: "SubTotal",
            variant: "success",
            class: "text-center"
          },
          { key: "igv", label: "IGV" },
          { key: "precioVenta", label: "Precio Venta" },
          {
            key: "total",
            label: "Total",
            variant: "danger",
            class: "text-center"
          },
          { key: "acciones", label: "" }
        ]
      },
      inProgress: false,
      datosOcultos: false,
      newRuc: "",
      API: config.API,
      ultimoMovimiento: "",
      movimiento: this.initMovimiento(),
      idPerfil: sessionStorage.getItem("IdPerfil")
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
    onAddProducto(obj) {
      this.obtenerStok({
        IdAlmacen:
          this.movimiento.almacenDestino.IdAlmacen > 0
            ? this.movimiento.almacenDestino.IdAlmacen
            : this.movimiento.almacenOrigen.IdAlmacen > 0
              ? this.movimiento.almacenOrigen.IdAlmacen
              : 1
      });
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
            ? this.$moment().format("YYYY-MM-DDTHH:mm:ss")
            : this.$moment().format("YYYY-MM-DD HH:mm:ss"),
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
      } else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
      } else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
      }
      this.movimiento.movimientoTipo = obj;

      this.cargarNumero();
    },
    setProveedor(obj) {
      this.movimiento.proveedor = obj;
    },
    setAlmacenOrigen(obj) {
      this.movimiento.almacenOrigen = obj;
      this.obtenerStok({
        IdAlmacen:
          this.movimiento.almacenOrigen.IdAlmacen > 0
            ? this.movimiento.almacenOrigen.IdAlmacen
            : 1
      });
    },
    setAlmacenDestino(obj) {
      this.movimiento.almacenDestino = obj;
      this.obtenerStok({
        IdAlmacen:
          this.movimiento.almacenDestino.IdAlmacen > 0
            ? this.movimiento.almacenDestino.IdAlmacen
            : 1
      });
    },
    setMoneda(obj) {
      this.movimiento.Moneda = obj.Moneda;
    },
    getProveedores(value) {
      this.buscarProveedores(value);
    },
    getAlmacenes(value) {
      this.buscarAlmacenes(value);
    },
    guardarMovimiento(evt) {
      evt.preventDefault();
      const faltantes = [];
      const tipo = this.movimiento.movimientoTipo || {};
      const proveedor = this.movimiento.proveedor || {};
      if (!tipo.IdMovimientoTipo) faltantes.push("tipo de movimiento");
      if (!proveedor.IdProveedor) faltantes.push("proveedor");
      if (!String(this.movimiento.Serie || "").trim()) faltantes.push("serie");
      if (!String(this.movimiento.Numero || "").trim()) faltantes.push("número");
      if (!String(this.movimiento.Moneda || "").trim()) faltantes.push("moneda");
      if (!String(this.movimiento.MovimientoFecha || "").trim()) faltantes.push("fecha contable");
      if (!String(this.movimiento.FechaStock || "").trim()) faltantes.push("fecha de stock");
      if (!Number(this.movimiento.TipoCambio)) faltantes.push("tipo de cambio");
      if ((Number(tipo.Tipo) === 1 || Number(tipo.Tipo) === 2) && Number((this.movimiento.almacenOrigen || {}).IdAlmacen) <= 0) faltantes.push("almacén de origen");
      if ((Number(tipo.Tipo) === 0 || Number(tipo.Tipo) === 2) && Number((this.movimiento.almacenDestino || {}).IdAlmacen) <= 0) faltantes.push("almacén de destino");
      if (this.movimiento.EsCredito && !this.movimiento.FechaVenCredito) faltantes.push("fecha de vencimiento del crédito");

      if (faltantes.length) {
        this.datosOcultos = false;
        this.$toasted.show("No se puede guardar. Complete: " + faltantes.join(", ") + ".", { duration: 7000 });
        return;
      }

      this.inProgress = true;

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto");
        this.inProgress = false;
        return;
      }

      let faltaLlenar = false;
      this.productos.forEach((prod, index) => {
        if (prod.total <= 0) {
          faltaLlenar = true;
        }
      });

      if (faltaLlenar && this.movimiento.alterarProductos) {
        this.$toasted.show("Falta llenar cantidad / precio");
        this.inProgress = false;
        return;
      }

      this.axios
        .post(this.API + "movimientos", {
          movimiento: this.movimiento,
          productos: this.productos
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
            this.$toasted.show("Movimiento guardado correctamente ");
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
.inventory-movement-page {
  min-height: calc(100vh - 92px);
  padding: 12px 22px 22px;
  color: #12233f;
  background:
    radial-gradient(circle at 7% 4%, rgba(20, 184, 226, .14), transparent 26%),
    radial-gradient(circle at 95% 12%, rgba(42, 109, 240, .12), transparent 24%),
    linear-gradient(145deg, #f7fbff 0%, #edf4fb 52%, #f8fbfe 100%);
}
.inventory-movement-page.is-data-collapsed {
  min-height: auto;
  padding-bottom: 0;
}
.inventory-movement-page.is-data-collapsed .inventory-heading { margin-bottom: 8px; }
.inventory-movement-page.is-data-collapsed .inventory-products-section { margin-bottom: 0; }
.inventory-movement-page * { box-sizing: border-box; }
.inventory-shell { display:flex; flex-direction:column; max-width: 1840px; margin: 0 auto; padding: 0 !important; }
.inventory-shell > .inventory-heading { order: 10; }
.inventory-shell > .inventory-data-grid { order: 20; }
.inventory-shell > .inventory-submit-row { display:none; order: 30; padding: 4px 0 12px; }
.inventory-shell > .inventory-products-section { order: 40; }
.inventory-heading {
  display: flex; align-items: center; gap: 12px; min-height: 68px;
  margin-bottom: 10px; padding: 8px 18px; overflow: hidden; position: relative;
  border: 1px solid rgba(122,168,211,.24); border-radius: 19px; color: #fff;
  background: linear-gradient(118deg, #092954 0%, #0c4f86 62%, #0a91bd 100%);
  box-shadow: 0 18px 42px rgba(9,41,84,.18);
}
.inventory-heading::after {
  content: ""; position: absolute; width: 330px; height: 330px; right: -110px; top: -210px;
  border: 1px solid rgba(255,255,255,.16); border-radius: 50%;
  box-shadow: 0 0 0 38px rgba(255,255,255,.035), 0 0 0 80px rgba(255,255,255,.025);
}
.inventory-heading__icon {
  display: grid; place-items: center; width: 44px; height: 44px; flex: 0 0 44px;
  border: 1px solid rgba(255,255,255,.25); border-radius: 14px;
  font-size: 20px; color: #7ee9ff; background: rgba(255,255,255,.1);
}
.inventory-heading__eyebrow {
  display: block; margin-bottom: 2px; color: #83eaff;
  font-size: 11px; font-weight: 900; letter-spacing: 1.6px;
}
.inventory-heading h1 { margin: 0; color: #fff; font-size: clamp(19px, 1.55vw, 25px); font-weight: 900; letter-spacing: -.5px; }
.inventory-heading p { margin: 0; color: rgba(235,247,255,.82); font-size: 12px; }
.inventory-heading__status {
  z-index: 1; padding: 9px 14px; white-space: nowrap;
  border: 1px solid rgba(255,255,255,.2); border-radius: 999px;
  background: rgba(3,25,55,.25); font-size: 12px; font-weight: 800;
}
.inventory-heading__actions { display:flex;align-items:center;gap:9px;margin-left:auto;z-index:2; }
.inventory-toggle-data {
  min-height:38px;padding:8px 14px!important;border:1px solid rgba(255,255,255,.28)!important;
  border-radius:999px!important;color:#07345d!important;background:#fff!important;
  font-size:12px!important;font-weight:900!important;box-shadow:0 6px 14px rgba(3,25,55,.16)!important;
}
.inventory-toggle-data i { margin-right:7px; color:#087ca9; }
.inventory-toggle-data:hover { transform:translateY(-1px);background:#eaf8ff!important; }
.inventory-heading__status span {
  display: inline-block; width: 8px; height: 8px; margin-right: 7px;
  border-radius: 50%; background: #34e6a1; box-shadow: 0 0 0 4px rgba(52,230,161,.13);
}
.inventory-data-grid { margin: 0 -7px 10px !important; }
.inventory-card {
  margin: 0 7px; padding: 12px 18px 3px !important;
  border: 1px solid #dce8f4; border-radius: 22px; background: rgba(255,255,255,.94);
  box-shadow: 0 13px 34px rgba(35,67,105,.09);
}
.inventory-data-grid > .inventory-card--primary { max-width: calc(66.666667% - 14px); }
.inventory-data-grid > .inventory-card--document { max-width: calc(33.333333% - 14px); }
.inventory-card__title {
  display: flex; align-items: center; gap: 10px; margin-bottom: 10px; padding-bottom: 9px;
  border-bottom: 1px solid #e6eef6;
}
.inventory-card__title > span {
  display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px;
  color: white; background: linear-gradient(135deg,#175ed9,#0aa8d0);
  box-shadow: 0 7px 16px rgba(20,112,206,.24);
}
.inventory-card__title small { display: block; color: #6d8098; font-size: 10px; font-weight: 900; letter-spacing: 1.2px; }
.inventory-card__title strong { display: block; margin-top: 1px; font-size: 16px; font-weight: 900; }
.inventory-movement-page label,
.inventory-movement-page legend {
  margin-bottom: 6px; color: #364b66; font-size: 12px; font-weight: 800;
}
.inventory-movement-page .form-control,
.inventory-movement-page .custom-select {
  min-height: 44px; border: 1px solid #d6e2ee; border-radius: 11px;
  color: #172844; font-weight: 650; background-color: #fbfdff; box-shadow: none;
  transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.inventory-movement-page .form-control:focus,
.inventory-movement-page .custom-select:focus { border-color: #3c91ef; box-shadow: 0 0 0 4px rgba(34,130,231,.12); }
.inventory-movement-page .form-control:disabled,
.inventory-movement-page .form-control[readonly] { color: #53667d; background: #eef3f8; }
.inventory-movement-page .input-group-append .btn,
.inventory-movement-page .input-group > .btn {
  min-height: 44px; border: 0; padding: 0 17px; font-weight: 800;
}

.inventory-field {
  position: relative;
  margin-bottom: 13px;
}

.inventory-field label {
  display: block;
  margin-bottom: 8px;
  color: #102a4d;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: .02em;
}

.inventory-field .autocomplete {
  position: relative;
  width: 100%;
  min-width: 0;
  flex: 1 1 auto;
}

.inventory-field .inventory-autocomplete-input {
  width: 100%;
  height: 56px;
  padding: 0 18px;
  color: #102a4d;
  font-size: 16px;
  font-weight: 750;
  border: 1px solid #c9d9ea;
  border-radius: 15px;
  background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
  box-shadow: 0 8px 22px rgba(14, 45, 83, .07);
  transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}

.inventory-field .inventory-autocomplete-input:focus {
  border-color: #1599e6;
  box-shadow: 0 0 0 4px rgba(21, 153, 230, .14), 0 12px 26px rgba(14, 45, 83, .1);
  transform: translateY(-1px);
}

.inventory-field .autocomplete ul {
  position: absolute !important;
  z-index: 1400 !important;
  top: calc(100% + 8px) !important;
  right: 0 !important;
  left: 0 !important;
  max-height: 310px;
  margin: 0 !important;
  padding: 8px !important;
  overflow-y: auto;
  border: 1px solid rgba(27, 93, 151, .13) !important;
  border-radius: 17px !important;
  background: rgba(255, 255, 255, .98) !important;
  box-shadow: 0 20px 42px rgba(6, 35, 70, .2) !important;
  backdrop-filter: blur(14px);
}

.inventory-field .autocomplete ul li {
  margin: 2px 0;
  border: 0 !important;
  border-radius: 11px;
  overflow: hidden;
}

.inventory-field .autocomplete ul li a {
  display: flex !important;
  align-items: center;
  min-height: 43px;
  padding: 10px 13px !important;
  color: #173352 !important;
  font-size: 14px;
  font-weight: 800;
  border-radius: 11px;
  transition: color .18s ease, background .18s ease, transform .18s ease;
}

.inventory-field .autocomplete ul li a::before {
  width: 8px;
  height: 8px;
  margin-right: 10px;
  border-radius: 50%;
  background: #21b7d8;
  box-shadow: 0 0 0 4px rgba(33, 183, 216, .12);
  content: "";
}

.inventory-field .autocomplete ul li a:hover,
.inventory-field .autocomplete ul li.focus-list a,
.inventory-field .autocomplete ul li a:focus {
  color: #fff !important;
  text-decoration: none;
  background: linear-gradient(105deg, #07396f 0%, #0876bd 58%, #16a9d4 100%) !important;
  transform: translateX(2px);
}

.inventory-field .autocomplete ul li a:hover::before,
.inventory-field .autocomplete ul li.focus-list a::before {
  background: #fff;
  box-shadow: 0 0 0 4px rgba(255, 255, 255, .18);
}

.inventory-supplier-control {
  display: flex;
  flex-wrap: nowrap;
  align-items: stretch;
}

.inventory-supplier-control .input-group-append {
  margin-left: 10px;
}
.inventory-products-section {
  padding: 10px 18px 16px; border: 1px solid #d8e6f3; border-radius: 22px;
  background: rgba(255,255,255,.96); box-shadow: 0 15px 38px rgba(29,65,106,.1);
}
.inventory-section-title {
  display: flex; align-items: center; justify-content: space-between; padding: 0 2px 8px;
}
.inventory-section-title small { color: #1685c8; font-size: 10px; font-weight: 900; letter-spacing: 1.35px; }
.inventory-section-title h2 { margin: 0; font-size: 18px; font-weight: 900; }
.inventory-section-title > span {
  padding: 8px 12px; border-radius: 999px; color: #426078;
  background: #edf6fd; font-size: 11px; font-weight: 800;
}
.inventory-section-title > span i { color: #1685c8; margin-right: 5px; }
.inventory-submit-row { padding: 17px 0 0; }
.inventory-save-button, .inventory-barcode-button {
  min-width: 250px; min-height: 54px; margin: 5px; border: 0 !important;
  border-radius: 16px !important; font-size: 15px !important; font-weight: 900 !important;
  box-shadow: 0 10px 22px rgba(12,73,139,.18); transition: transform .2s ease, filter .2s ease;
}
.inventory-save-button { background: linear-gradient(135deg,#0ba96f,#18c883) !important; }
.inventory-barcode-button { background: linear-gradient(135deg,#1557c8,#1599cf) !important; }
.inventory-save-button i, .inventory-barcode-button i { margin-right: 10px; }
.inventory-save-button:hover, .inventory-barcode-button:hover { transform: translateY(-2px); filter: brightness(1.06); }
@media (max-width: 991px) {
  .inventory-movement-page { padding: 14px; }
  .inventory-heading { align-items: flex-start; }
  .inventory-heading__status { display: none; }
  .inventory-data-grid > .inventory-card {
    max-width: calc(100% - 14px); flex: 0 0 calc(100% - 14px); margin-bottom: 14px;
  }
}
@media (max-width: 575px) {
  .inventory-movement-page { padding: 8px; }
  .inventory-heading { padding: 15px; border-radius: 17px; }
  .inventory-heading__icon { width: 46px; height: 46px; flex-basis: 46px; }
  .inventory-heading p, .inventory-section-title > span { display: none; }
  .inventory-card, .inventory-products-section { padding: 14px !important; border-radius: 16px; }
  .inventory-save-button, .inventory-barcode-button { width: 100%; min-width: 0; margin: 5px 0; }
  .inventory-supplier-control { flex-wrap: wrap; }
  .inventory-supplier-control .input-group-append { width: 100%; margin: 9px 0 0; }
  .inventory-supplier-control .input-group-append > div,
  .inventory-supplier-control .input-group-append .supplier-create,
  .inventory-supplier-control .input-group-append .supplier-create__trigger { width: 100%; }
  .inventory-field .inventory-autocomplete-input { height: 52px; font-size: 14px; }
}
</style>
