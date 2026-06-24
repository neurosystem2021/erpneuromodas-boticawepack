<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit.prevent="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
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
                    <b-form-group v-if="isNotaCredito">
                      <label>Doc. Ref:</label>                    
                      <autocomplete
                          required="required"
                          ref="inputDocRef"
                          :url="API + 'movimientos/lista'"
                          anchor="NroComprobante"
                          initValue=""
                          label="writer"
                          placeholder=""
                          :on-select="setDocRef" 
                          :onFocus="focusAutocomplete.bind(null, 'inputDocRef')"
                          :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
                      
                    <b-form-select v-if="isNotaCredito"
                      v-model="movimiento.NotaIdMotivo" 
                      :options="itemsMotivoNota" 
                      v-on:change="setMotivo($event)" 
                      class="mb-3" 
                      size="md" 
                    required/>
                    <b-form-group>
                      <label>Moneda:</label>
                      <autocomplete
                        ref="inputMoneda"
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
              <b-col cols="12">
                <b-form-checkbox
                  id="checkbox-1"
                  v-model="gravada"
                  name="checkbox-1"
                  value="1"
                  unchecked-value="0"
                >
                  GRAVADA
                </b-form-checkbox>
                <b-form-checkbox
                  id="checkbox-2"
                  v-model="igvGeneral"
                  value="1"
                  unchecked-value="0"
                >
                  IGV
                </b-form-checkbox>
              </b-col>
          </b-row>
          <b-row>
            
            <b-col>
              <productos-carrito 
                ref="carrito"
                @onAddProducto="onAddProducto" 
                :tipoCambio="movimiento.TipoCambio"
                :esNotaCredito="isNotaCredito"
                :fields="fields"
                :gravada="gravada"
                :igvGeneral="igvGeneral"
                :bloquearInput="bloquearTodoCarrito"
                :bloquearCantidadSolo="bloquearSoloCantidad"
                :bloquearPrecioSolo="bloquearSoloPrecio"
                :esNotaCreditoDisminucion="esNotaCreditoDisminucion"
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
      gravada: 0,
      inProgress: false,
      newRuc: "",
      API: config.API,
      ultimoMovimiento: "",
      serieManualNC: false,
      bloquearTodoCarrito: false,
      bloquearSoloCantidad: false,
      bloquearSoloPrecio: false,
      itemsMotivoNota: [
        { value: null, text: "-- SELECCIONE EL MOTIVO --" },
        { value: "01", text: "ANULACION DE LA OPERACION" },
        // { value: "02", text: "ANULACION POR ERROR EN EL RUC" },
        // { value: "03", text: "CORRECION POR ERROR EN LA DESCRIPCION" },
        // { value: "04", text: "DESCUENTO GLOBAL" },
        // { value: "05", text: "DESCUENTO POR ITEM" },
        // { value: "06", text: "DEVOLUCION TOTAL" },
        { value: "07", text: "DEVOLUCION POR ITEM" },
        // { value: "08", text: "BONIFICACION" },
        { value: "09", text: "DISMINUCION EN EL VALOR" },
        // { value: "10", text: "OTROS CONCEPTOS" }
        { value: "11", text: "AJUSTE GLOBAL" },
      ],
      movimiento: this.initMovimiento(),
      isTraslado: true,
      isSalida: false,
      igvGeneral: 0,
      fields: [
        {
          key: "stock",
          label: "Stock",
          variant: "info",
          class: "text-center"
        },
        { key: "CodigoBarra", label: "Código" },
        // { key: 'ProductoCategoria', label: 'Categoria'},
        { key: "Producto", label: "Artículo" },
        { key: "Descripcion", label: "Descripción" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "ProductoPresentacion", label: "Anaquel" },
        // { key: 'ProductoMedicion', label: 'Medición' },
        { key: "obsequio", label: "Obsequio", class: "thcenter" },
        { key: "eventPres", label: "" },
        { key: "cantxPrecio", variant: "warning" },
        { key: "cantidad" },
        { key: "precio", label: "P/U" },
        { key: "subtotal", label: "SubTotal", variant: "success" },
        { key: "igv", label: "IGV" },
        { key: "flete", label: "FLETE" },
        // { key: 'lote', label: 'Lote' },
        { key: "precioVenta", label: "Precio venta (S/)" },
        { key: "total", label: "Total", variant: "danger" },
        { key: "acciones", label: "" }
      ]
    };
  },
  computed: {
    ...mapState({
      proveedoresFiltrados: state => state.proveedoresFiltrados,
      almacenes: state => state.almacenesFiltrados,
      productos: state => state.cart.productos
    }),
    esNotaCreditoDisminucion() {
      return (
        this.movimiento.movimientoTipo.CodSunat == "07" &&
        this.movimiento.NotaIdMotivo == "09"
      );
    },
    isNotaCredito() {
      return (
        Object.keys(this.movimiento.movimientoTipo).length > 0 &&
        this.movimiento.movimientoTipo.CodSunat == "07"
      );
    },
    modoNotaCredito() {
      if (!this.isNotaCredito) return null;
      return this.movimiento.NotaIdMotivo;
    },
  },
  methods: {
    ...mapActions({
      buscarProveedores: "buscarProveedores",
      buscarAlmacenes: "buscarAlmacenes",
      limpiar: "limpiarCarrito",
      obtenerStok: "obtenerStock"
    }),
    setMotivo(ev) {
      this.movimiento.NotaIdMotivo = ev;

      if (ev === "01") {
        this.bloquearTodoCarrito = true;
        this.bloquearSoloCantidad = false;
        this.bloquearSoloPrecio = false;
      } else if (ev === "09") {
        this.bloquearTodoCarrito = false;
        this.bloquearSoloCantidad = true;
        this.bloquearSoloPrecio = false;
      } else if (ev === "07") {
        this.bloquearTodoCarrito = false;
        this.bloquearSoloCantidad = false;
        this.bloquearSoloPrecio = true;
      } else if (ev === "11") {
        this.bloquearTodoCarrito = false;
        this.bloquearSoloCantidad = false;
        this.bloquearSoloPrecio = false;
      } else {
        // null o cualquier otro caso: sin bloqueo
        this.bloquearTodoCarrito = false;
        this.bloquearSoloCantidad = false;
        this.bloquearSoloPrecio = false;
      }
    },
    setDocRef(obj) {
      this.serieManualNC = false;
      this.movimiento.NotaIdMotivo = null;
      this.bloquearTodoCarrito = true;
      this.bloquearSoloCantidad = false;
      this.bloquearSoloPrecio = false;

      console.log("CodSunat recibido:", obj.CodSunat);
      console.log("Serie del doc:", obj.Serie);
      this.movimiento.almacenDestino.IdAlmacen = obj.IdAlmacenDestino;
      this.movimiento.almacenOrigen.IdAlmacen = obj.IdAlmacenDestino;
      this.movimiento.TipoCambio = obj.TipoCambio;
      this.setMoneda({
        Moneda: obj.Moneda,
        TipoCambio: obj.TipoCambio,
      });

      this.$nextTick(() => {
        if (this.$refs.inputMoneda) {
          this.$refs.inputMoneda.setValue(obj.Moneda);
        }
      });
      this.movimiento.EsConsumoVale = obj.EsConsumoVale;
      this.movimiento.CodSunatModifica = obj.CodSunat;
      this.movimiento.NroComprobanteModifica = obj.NroComprobante;
      this.movimiento.IdComprobanteModifica = obj.IdDocVenta;
      this.movimiento.EsFacturaValeNC = obj.EsFacturaVale;

      let nuevaSerie = "_C01";
      if (obj.Serie) {
        const prefix = obj.Serie.charAt(0).toUpperCase();
        if (prefix === "F") nuevaSerie = "FC01";
        else if (prefix === "B") nuevaSerie = "BC01";
      }
      this.serieManualNC = true;
      Vue.set(this.movimiento, "Serie", nuevaSerie);
      this.$nextTick(() => {
        this.cargarNumero();
      });

      if (obj.EsFacturaVale == 1) {
        this.$toasted.info(
          "Es una factura de vales realizados, los vales pasarán a pendiente",
          { duration: 5000 }
        );
      }

      // Cargar producto del documento afectado nota credito
      this.axios
        .get(this.API + "movimiento/detalle/comprobante", {
          params: {
            hashMovimiento: obj.Hash
          }
        })
        .then(response => {
          this.axios
            .get(this.API + "proveedor", {
              params: {
                idProveedor: obj.IdProveedor
              }
            })
            .then(response => {
              if (response.data) {
                this.setProveedor(response.data);
                this.$refs.inputProveedor.setValue(response.data.Proveedor);
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });

          this.limpiar();
          response.data.map(item => {
            item.nuevoPrecioContado = item.PrecioContado || 0;
            // Calcular subtotal inicial: cantidad × precio (lógica normal, sin tipo de cambio)
            const cantidad = parseFloat(item.cantidad) || 0;
            const precio = parseFloat(item.precio) || 0;
            item.subtotal = cantidad * precio;
            item._precioBase = precio;
            this.$store.state.cart.productos.push(item);
          });

          const idAlmacen = this.movimiento.almacenOrigen.IdAlmacen > 0
            ? this.movimiento.almacenOrigen.IdAlmacen
            : 1;
          this.obtenerStok({ IdAlmacen: idAlmacen });

          this.$nextTick(() => {
            this.$forceUpdate();
            if (this.$refs.carrito) {
              this.$refs.carrito.$forceUpdate();
            }
          });
          this.$nextTick(() => {
            this.$forceUpdate();
            if (this.$refs.carrito) {
              this.$refs.carrito.$forceUpdate();
              // Forzar reactividad en cada producto del subtotal
              this.$store.state.cart.productos.forEach((prod, index) => {
                if (this.$refs.carrito.getSubtotal) {
                  this.$refs.carrito.getSubtotal(index);
                }
              });
            }
          });
        });
    },
    loadFirstAlmacen(cb) {
      this.axios.get(this.API + "almacenes/primero").then(res => {
        cb(res.data);
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
      this.serieManualNC = false;

      this.bloquearTodoCarrito = false;
      this.bloquearSoloCantidad = false;
      this.bloquearSoloPrecio = false;
      this.movimiento.NotaIdMotivo = null;

      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 };
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.isTraslado = false;
        this.isSalida = false;

        this.loadFirstAlmacen(almacen => {
          this.movimiento.almacenDestino = almacen;
          this.$nextTick(() => {
            if (this.$refs.inputAlmacenDestino) {
              this.$refs.inputAlmacenDestino.setValue(almacen.Almacen);
            }
          });
        });

      } else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
        this.isTraslado = false;
        this.isSalida = true;

        this.loadFirstAlmacen(almacen => {
          this.movimiento.almacenOrigen = almacen;
          this.$nextTick(() => {
            if (this.$refs.inputAlmacenOrigen) {
              this.$refs.inputAlmacenOrigen.setValue(almacen.Almacen);
            }
          });
        });

      } else {
        this.movimiento.almacenDestino = { IdAlmacen: 1 };
        this.movimiento.almacenOrigen = { IdAlmacen: 1 };
        this.isTraslado = true;
        this.isSalida = false;

        this.loadFirstAlmacen(almacen => {
          this.movimiento.almacenDestino = almacen;
          this.movimiento.almacenOrigen = almacen;
          this.$nextTick(() => {
            if (this.$refs.inputAlmacenDestino) {
              this.$refs.inputAlmacenDestino.setValue(almacen.Almacen);
            }
            if (this.$refs.inputAlmacenOrigen) {
              this.$refs.inputAlmacenOrigen.setValue(almacen.Almacen);
            }
          });
        });
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
      if (obj.TipoCambio) {
        this.movimiento.TipoCambio = obj.TipoCambio;
      } else if (obj.Moneda === "USD") {
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
      this.$swal
        .fire({
          icon: "question",
          title: "Guardar",
          text: "Desea guardar el movimiento",
          allowOutsideClick: false,
          allowEscapeKey: false,
          confirmButtonText: `Si, Guardar`,
          denyButtonText: `No Guardar`,
          showDenyButton: true
        })
        .then(action => {
          if (action.isConfirmed) {
            this.guardar();
          }
        });
    },
    guardar() {
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

      if (this.movimiento.movimientoTipo.CodSunat == "07") {
        this.movimiento.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.movimiento.NotaIdMotivo
        )[0].text;
      }

      const esNC = this.movimiento.movimientoTipo.CodSunat == "07";
      if (sinStock >= 1 && (this.isTraslado == true || this.isSalida == true) && !esNC) {
        this.$toasted.show("No tiene stock para realizar el movimiento");
        this.inProgress = false;
        return;
      }

      const esNotaCreditoConPrecio =
        this.movimiento.movimientoTipo.CodSunat == "07" &&
        (this.movimiento.NotaIdMotivo == "09" || this.movimiento.NotaIdMotivo == "11");

      if ((this.isTraslado == true || this.isSalida == true) && !esNotaCreditoConPrecio) {
        this.movimiento.alterarProductos = 0;
      } else if (esNotaCreditoConPrecio) {
        this.movimiento.alterarProductos = 1;
      }
      let productos = [];
      productos = this.productos;
      // if (this.gravada == 1) {
      //   this.productos.forEach(element => {
      //     let pro = { ...element };
      //     element.precio = parseFloat(pro.precio / 1.18).toFixed(2);
      //     element.TieneIgv = 1;
      //     productos.push(element);
      //   });
      // } else {
      // }

      this.axios
        .post(this.API + "movimientos", {
          gravada: this.gravada,
          movimiento: this.movimiento,
          productos: productos
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
</style>
