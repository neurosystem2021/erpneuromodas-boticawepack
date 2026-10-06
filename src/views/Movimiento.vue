<template>
  <div class="inventory-movement-page" :class="{ 'is-data-collapsed': datosOcultos }">
    <Menu v-if="false"></Menu>
    <b-form class="inventory-movement-form" @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated novalidate>
        <b-container class="inventory-shell" fluid>
          <div class="inventory-heading">
            <div class="inventory-heading__icon"><i class="fas fa-boxes"></i></div>
            <div>
              <span class="inventory-heading__eyebrow">LLANTAS Y BATERÍAS · LOGÍSTICA</span>
              <h1>Nuevo movimiento de inventario</h1>
              <p>Registra entradas, salidas o transferencias y mantén el stock actualizado.</p>
            </div>
            <div class="inventory-heading__actions">
              <div class="inventory-heading__status"><span></span> Movimiento en edición</div>
              <b-button v-if="esSuperadmin" class="inventory-initial-button" type="button" @click="abrirImportacionInicial">
                <i class="fas fa-file-excel"></i>
                <span>Ingreso inicial Excel</span>
              </b-button>
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
                        placeholder="Fecha Contable"></b-form-input>

                    </b-form-group>
                    <b-form-group>
                      <label>Fecha Stock:</label>
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"></b-form-input>
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
    </b-form>

    <b-modal
      v-model="mostrarImportacionInicial"
      size="lg"
      centered
      hide-footer
      no-close-on-backdrop
      modal-class="inventory-initial-modal"
      content-class="inventory-initial-content"
      body-class="inventory-initial-body">
      <template slot="modal-title">
        <div class="inventory-initial-title">
          <span><i class="fas fa-boxes"></i></span>
          <div>
            <strong>Ingreso inicial de inventario</strong>
            <small>Se registrará un solo movimiento para el almacén actual.</small>
          </div>
        </div>
      </template>

      <input
        ref="archivoInventarioInicial"
        type="file"
        class="d-none"
        accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        @change="seleccionarArchivoInicial" />

      <button
        type="button"
        class="inventory-initial-dropzone"
        :class="{ 'is-selected': archivoInventarioInicial }"
        :disabled="analizandoInventarioInicial || guardandoInventarioInicial"
        @click="elegirArchivoInicial"
        @dragover.prevent
        @drop.prevent="soltarArchivoInicial">
        <span class="inventory-initial-dropzone__icon"><i class="fas fa-file-excel"></i></span>
        <span class="inventory-initial-dropzone__copy">
          <strong>{{ archivoInventarioInicial ? archivoInventarioInicial.name : 'Seleccionar archivo XLS o XLSX' }}</strong>
          <small>{{ archivoInventarioInicial ? formatoPesoInicial(archivoInventarioInicial.size) : 'Columnas mínimas: CODIGO y STOCK · Máximo 12 MB' }}</small>
        </span>
        <span class="inventory-initial-dropzone__action">{{ archivoInventarioInicial ? 'Cambiar' : 'Examinar' }}</span>
      </button>

      <div v-if="analizandoInventarioInicial" class="inventory-initial-loading">
        <b-spinner small></b-spinner>
        <span>Validando productos, cantidades y costos...</span>
      </div>

      <div v-if="errorInventarioInicial" class="inventory-initial-alert inventory-initial-alert--error">
        <i class="fas fa-exclamation-circle"></i>
        <span>{{ errorInventarioInicial }}</span>
      </div>

      <template v-if="analisisInventarioInicial">
        <div class="inventory-initial-stats">
          <div><strong>{{ analisisInventarioInicial.total_filas }}</strong><span>Filas leídas</span></div>
          <div><strong>{{ analisisInventarioInicial.productos_importar }}</strong><span>Productos</span></div>
          <div><strong>{{ formatoCantidadInicial(analisisInventarioInicial.total_unidades) }}</strong><span>Unidades</span></div>
          <div><strong>S/ {{ formatoImporteInicial(analisisInventarioInicial.valor_total) }}</strong><span>Valorización</span></div>
        </div>

        <div v-if="analisisInventarioInicial.movimiento_existente" class="inventory-initial-alert inventory-initial-alert--error">
          <i class="fas fa-lock"></i>
          <span>Ya existe el movimiento {{ analisisInventarioInicial.movimiento_existente.Serie }}-{{ analisisInventarioInicial.movimiento_existente.Numero }}. Se bloqueó una segunda carga para no duplicar el stock.</span>
        </div>

        <div v-if="analisisInventarioInicial.advertencias && analisisInventarioInicial.advertencias.length" class="inventory-initial-alert inventory-initial-alert--warning">
          <i class="fas fa-info-circle"></i>
          <ul>
            <li v-for="(advertencia, indice) in analisisInventarioInicial.advertencias" :key="indice">{{ advertencia }}</li>
          </ul>
        </div>

        <div v-if="analisisInventarioInicial.errores && analisisInventarioInicial.errores.length" class="inventory-initial-errors">
          <strong><i class="fas fa-times-circle"></i> Corrige estos datos antes de continuar</strong>
          <ul>
            <li v-for="error in analisisInventarioInicial.errores.slice(0, 10)" :key="error.fila + '-' + error.codigo">
              Fila {{ error.fila }}<span v-if="error.codigo"> · {{ error.codigo }}</span>: {{ error.mensaje }}
            </li>
          </ul>
        </div>

        <div v-if="analisisInventarioInicial.muestra && analisisInventarioInicial.muestra.length" class="inventory-initial-preview">
          <div class="inventory-initial-preview__heading">
            <strong>Vista previa del movimiento</strong>
            <span>Almacén actual · {{ analisisInventarioInicial.productos_importar }} productos</span>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead><tr><th>Código</th><th>Producto</th><th class="text-right">Cantidad</th><th class="text-right">P. costo</th><th class="text-right">Subtotal</th></tr></thead>
              <tbody>
                <tr v-for="fila in analisisInventarioInicial.muestra" :key="fila.fila">
                  <td><strong>{{ fila.codigo }}</strong></td>
                  <td>{{ fila.producto }}</td>
                  <td class="text-right">{{ formatoCantidadInicial(fila.cantidad) }}</td>
                  <td class="text-right">S/ {{ formatoImporteInicial(fila.precio_costo) }}</td>
                  <td class="text-right"><strong>S/ {{ formatoImporteInicial(fila.subtotal) }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>

      <div class="inventory-initial-actions">
        <button type="button" class="inventory-initial-cancel" :disabled="guardandoInventarioInicial" @click="mostrarImportacionInicial = false">Cerrar</button>
        <button type="button" class="inventory-initial-confirm" :disabled="!puedeImportarInicial || guardandoInventarioInicial || analizandoInventarioInicial" @click="confirmarImportacionInicial">
          <b-spinner v-if="guardandoInventarioInicial" small></b-spinner>
          <i v-else class="fas fa-save"></i>
          <span>{{ guardandoInventarioInicial ? 'Registrando...' : 'Registrar ingreso inicial' }}</span>
        </button>
      </div>
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
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoModelo", label: "Modelo" },
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
      idPerfil: Number(sessionStorage.getItem("IdPerfil") || sessionStorage.getItem("idPerfil") || 0),
      mostrarImportacionInicial: false,
      archivoInventarioInicial: null,
      analizandoInventarioInicial: false,
      guardandoInventarioInicial: false,
      analisisInventarioInicial: null,
      errorInventarioInicial: ""
    };
  },
  computed: {
    ...mapState({
      proveedoresFiltrados: state => state.proveedoresFiltrados,
      almacenes: state => state.almacenesFiltrados,
      productos: state => state.cart.productos
    }),
    esSuperadmin() {
      return this.idPerfil === 1;
    },
    puedeImportarInicial() {
      return Boolean(
        this.archivoInventarioInicial &&
        this.analisisInventarioInicial &&
        this.analisisInventarioInicial.puede_importar
      );
    }
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
    abrirImportacionInicial() {
      if (!this.esSuperadmin) return;
      this.archivoInventarioInicial = null;
      this.analisisInventarioInicial = null;
      this.errorInventarioInicial = "";
      this.mostrarImportacionInicial = true;
      this.$nextTick(() => {
        if (this.$refs.archivoInventarioInicial) this.$refs.archivoInventarioInicial.value = "";
      });
    },
    elegirArchivoInicial() {
      if (this.$refs.archivoInventarioInicial) this.$refs.archivoInventarioInicial.click();
    },
    soltarArchivoInicial(evento) {
      const archivo = evento.dataTransfer && evento.dataTransfer.files ? evento.dataTransfer.files[0] : null;
      if (archivo) this.prepararArchivoInicial(archivo);
    },
    seleccionarArchivoInicial(evento) {
      const archivo = evento.target.files && evento.target.files[0] ? evento.target.files[0] : null;
      if (archivo) this.prepararArchivoInicial(archivo);
    },
    prepararArchivoInicial(archivo) {
      const extension = (archivo.name.split(".").pop() || "").toLowerCase();
      this.errorInventarioInicial = "";
      this.analisisInventarioInicial = null;
      if (["xls", "xlsx"].indexOf(extension) === -1) {
        this.archivoInventarioInicial = null;
        this.errorInventarioInicial = "Seleccione un archivo en formato XLS o XLSX.";
        return;
      }
      if (archivo.size > 12 * 1024 * 1024) {
        this.archivoInventarioInicial = null;
        this.errorInventarioInicial = "El archivo supera el máximo permitido de 12 MB.";
        return;
      }
      this.archivoInventarioInicial = archivo;
      this.analizarInventarioInicial();
    },
    urlInventarioInicial() {
      return this.API.replace(/index\.php\/?$/, "inventario-inicial-importar.php");
    },
    datosInventarioInicial(modo) {
      const datos = new FormData();
      datos.append("archivo", this.archivoInventarioInicial);
      datos.append("modo", modo);
      datos.append("idAlmacen", Number(this.IdAlmacen) || 1);
      return datos;
    },
    analizarInventarioInicial() {
      if (!this.archivoInventarioInicial) return;
      this.analizandoInventarioInicial = true;
      this.errorInventarioInicial = "";
      this.axios.post(this.urlInventarioInicial(), this.datosInventarioInicial("vista_previa"), {
        headers: { "Content-Type": "multipart/form-data" }
      }).then(response => {
        this.analisisInventarioInicial = response.data.analisis;
      }).catch(error => {
        const respuesta = error.response && error.response.data ? error.response.data : {};
        this.analisisInventarioInicial = respuesta.analisis || null;
        this.errorInventarioInicial = respuesta.error || "No se pudo analizar el archivo Excel.";
      }).then(() => {
        this.analizandoInventarioInicial = false;
      });
    },
    async confirmarImportacionInicial() {
      if (!this.puedeImportarInicial) return;
      const analisis = this.analisisInventarioInicial;
      const confirmacion = await this.$swal({
        icon: "warning",
        title: "Registrar inventario inicial",
        html: `<div class="inventory-confirm-copy">Esta operación agregará stock y quedará guardada como un único movimiento. No se permitirá repetirla en el mismo almacén.</div>
          <div class="inventory-confirm-summary">
            <div><span>Productos</span><strong>${Number(analisis.productos_importar || 0)}</strong></div>
            <div><span>Unidades</span><strong>${this.formatoCantidadInicial(analisis.total_unidades)}</strong></div>
            <div class="inventory-confirm-summary__wide inventory-confirm-summary__total"><span>Valorización</span><strong>S/ ${this.formatoImporteInicial(analisis.valor_total)}</strong></div>
          </div>`,
        showCancelButton: true,
        confirmButtonText: "Sí, registrar movimiento",
        cancelButtonText: "Volver a revisar",
        buttonsStyling: false,
        customClass: {
          popup: "inventory-confirm-popup",
          icon: "inventory-confirm-icon",
          title: "inventory-confirm-title",
          content: "inventory-confirm-content",
          actions: "inventory-confirm-actions",
          confirmButton: "inventory-confirm-save",
          cancelButton: "inventory-confirm-review"
        },
        allowOutsideClick: false
      });
      if (!confirmacion.isConfirmed) return;

      this.guardandoInventarioInicial = true;
      this.errorInventarioInicial = "";
      this.axios.post(this.urlInventarioInicial(), this.datosInventarioInicial("importar"), {
        headers: { "Content-Type": "multipart/form-data" }
      }).then(response => {
        const resultado = response.data.resultado || {};
        this.ultimoMovimiento = resultado.hash || "";
        this.mostrarImportacionInicial = false;
        this.$swal({
          icon: "success",
          title: "Inventario inicial registrado",
          html: `<strong>${resultado.serie || "INICIAL"}-${resultado.numero || ""}</strong><br>${resultado.productos || 0} productos · ${this.formatoCantidadInicial(resultado.unidades)} unidades<br>Registro: INGRESO INICIAL DE INVENTARIO`,
          confirmButtonText: "Aceptar"
        });
      }).catch(error => {
        const respuesta = error.response && error.response.data ? error.response.data : {};
        this.analisisInventarioInicial = respuesta.analisis || this.analisisInventarioInicial;
        this.errorInventarioInicial = respuesta.error || "No se pudo registrar el ingreso inicial.";
      }).then(() => {
        this.guardandoInventarioInicial = false;
      });
    },
    formatoCantidadInicial(valor) {
      return Number(valor || 0).toLocaleString("es-PE", { maximumFractionDigits: 4 });
    },
    formatoImporteInicial(valor) {
      return Number(valor || 0).toLocaleString("es-PE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    },
    formatoPesoInicial(bytes) {
      if (!bytes) return "0 KB";
      if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
      return (bytes / (1024 * 1024)).toFixed(1) + " MB";
    },
    async guardarMovimiento(evt) {
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

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto");
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
        return;
      }

      const totalMovimiento = this.productos.reduce((total, producto) => {
        return total + Number(producto.total || 0);
      }, 0);
      const escaparResumen = valor => String(valor == null ? "" : valor)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
      const nombreProveedor = escaparResumen(proveedor.Proveedor || proveedor.RazonSocial || "Sin proveedor");
      const documentoMovimiento = [this.movimiento.Serie, this.movimiento.Numero]
        .filter(Boolean)
        .join("-");
      const monedaMovimiento = escaparResumen(this.movimiento.Moneda || "PEN");
      const confirmacion = await this.$swal({
        icon: "question",
        title: "Confirmar movimiento",
        html: `
          <div class="inventory-confirm-copy">Revisa el resumen antes de registrar el movimiento.</div>
          <div class="inventory-confirm-summary">
            <div><span>Tipo</span><strong>${escaparResumen(tipo.TipoMovimiento || "Movimiento")}</strong></div>
            <div><span>Documento</span><strong>${escaparResumen(documentoMovimiento || "Sin serie")}</strong></div>
            <div class="inventory-confirm-summary__wide"><span>Proveedor</span><strong>${nombreProveedor}</strong></div>
            <div><span>Productos</span><strong>${this.productos.length}</strong></div>
            <div class="inventory-confirm-summary__total"><span>Total</span><strong>${monedaMovimiento} ${totalMovimiento.toFixed(2)}</strong></div>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: "Sí, guardar",
        cancelButtonText: "Volver a revisar",
        buttonsStyling: false,
        customClass: {
          popup: "inventory-confirm-popup",
          icon: "inventory-confirm-icon",
          title: "inventory-confirm-title",
          content: "inventory-confirm-content",
          actions: "inventory-confirm-actions",
          confirmButton: "inventory-confirm-save",
          cancelButton: "inventory-confirm-review"
        },
        allowOutsideClick: false,
        allowEscapeKey: false
      });

      if (!confirmacion.isConfirmed) {
        return;
      }

      this.inProgress = true;

      const movimientoEnvio = Object.assign({}, this.movimiento, {
        MovimientoFecha: this.$moment(this.movimiento.MovimientoFecha).format("YYYY-MM-DD HH:mm:ss"),
        FechaStock: this.$moment(this.movimiento.FechaStock).format("YYYY-MM-DD HH:mm:ss")
      });

      this.axios
        .post(this.API + "movimientos", {
          movimiento: movimientoEnvio,
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
  min-height: 100vh;
  padding: 12px 22px 10px;
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
.inventory-initial-button {
  min-height:38px;padding:8px 14px!important;border:1px solid rgba(111,235,183,.38)!important;
  border-radius:999px!important;color:#fff!important;background:#0b9564!important;
  font-size:12px!important;font-weight:900!important;box-shadow:0 7px 16px rgba(2,40,31,.2)!important;
}
.inventory-initial-button i { margin-right:7px;color:#c8ffe8; }
.inventory-initial-button:hover { transform:translateY(-1px);background:#0aab70!important; }
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

/* Confirmación final del movimiento. */
.inventory-confirm-popup {
  width: min(520px, calc(100vw - 28px)) !important;
  overflow: hidden !important;
  padding: 0 0 22px !important;
  border: 1px solid #d8e6ef !important;
  border-radius: 22px !important;
  background-color: #fff !important;
  box-shadow: 0 28px 75px rgba(5, 35, 64, .3) !important;
}
.inventory-confirm-popup::before {
  display: block;
  width: 100%;
  height: 7px;
  background-color: #0879a8;
  content: "";
}
.inventory-confirm-icon {
  width: 62px !important;
  height: 62px !important;
  margin: 22px auto 10px !important;
  color: #0879a8 !important;
  border-color: #9bcddd !important;
}
.inventory-confirm-icon .swal2-icon-content { font-size: 38px !important; }
.inventory-confirm-title {
  padding: 0 28px !important;
  color: #12375a !important;
  font-size: 25px !important;
  font-weight: 900 !important;
  letter-spacing: -.35px !important;
}
.inventory-confirm-content { margin: 8px 24px 0 !important; }
.inventory-confirm-copy {
  margin-bottom: 14px;
  color: #62798c;
  font-size: 13px;
  line-height: 1.45;
}
.inventory-confirm-summary {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
  text-align: left;
}
.inventory-confirm-summary > div {
  min-width: 0;
  padding: 11px 13px;
  border: 1px solid #dce8f0;
  border-radius: 12px;
  background-color: #f7fafc;
}
.inventory-confirm-summary__wide { grid-column: 1 / -1; }
.inventory-confirm-summary > div span {
  display: block;
  margin-bottom: 3px;
  color: #71869a;
  font-size: 9px;
  font-weight: 900;
  letter-spacing: .7px;
  text-transform: uppercase;
}
.inventory-confirm-summary > div strong {
  display: block;
  overflow: hidden;
  color: #173b59;
  font-size: 13px;
  font-weight: 900;
  text-overflow: ellipsis;
  white-space: nowrap;
}
.inventory-confirm-summary__total {
  border-color: #b9e8d4 !important;
  background-color: #e8f8f0 !important;
}
.inventory-confirm-summary__total strong { color: #087149 !important; font-size: 16px !important; }
.inventory-confirm-actions {
  display: flex !important;
  width: calc(100% - 48px) !important;
  margin: 18px 24px 0 !important;
  gap: 9px !important;
}
.inventory-confirm-save,
.inventory-confirm-review {
  min-height: 46px;
  padding: 0 18px;
  border: 0;
  border-radius: 11px;
  font-size: 13px;
  font-weight: 900;
  cursor: pointer;
}
.inventory-confirm-save {
  flex: 1;
  color: #fff;
  background-color: #0aa568;
  box-shadow: 0 8px 18px rgba(10, 165, 104, .22);
}
.inventory-confirm-review { color: #415a70; background-color: #e9eff4; }
.inventory-confirm-save:hover,
.inventory-confirm-review:hover { filter: brightness(1.04); transform: translateY(-1px); }

.inventory-initial-modal .modal-dialog { max-width:900px; }
.inventory-initial-content { overflow:hidden;border:0!important;border-radius:22px!important;box-shadow:0 30px 85px rgba(4,35,62,.34)!important; }
.inventory-initial-content .modal-header { align-items:center;padding:18px 22px;border:0;color:#fff;background:linear-gradient(115deg,#043b38,#08735f); }
.inventory-initial-content .modal-header .close { color:#fff;opacity:.86;text-shadow:none; }
.inventory-initial-title { display:flex;align-items:center;gap:12px; }
.inventory-initial-title>span { display:grid;place-items:center;width:42px;height:42px;border:1px solid rgba(255,255,255,.25);border-radius:12px;background:rgba(255,255,255,.1);font-size:18px; }
.inventory-initial-title strong,.inventory-initial-title small { display:block; }
.inventory-initial-title strong { font-size:18px;font-weight:900; }
.inventory-initial-title small { margin-top:2px;color:#c9eee7;font-size:11px;font-weight:700; }
.inventory-initial-body { padding:20px 22px 22px!important;background:#f5faf9; }
.inventory-initial-dropzone { display:flex;align-items:center;width:100%;min-height:82px;padding:14px 16px;border:2px dashed #a9cec7;border-radius:16px;color:#173b3a;text-align:left;background:#fff;cursor:pointer;transition:.2s ease; }
.inventory-initial-dropzone:hover,.inventory-initial-dropzone.is-selected { border-color:#0aa873;background:#edfbf6;transform:translateY(-1px); }
.inventory-initial-dropzone:disabled { cursor:wait;opacity:.7; }
.inventory-initial-dropzone__icon { display:grid;place-items:center;width:48px;height:48px;flex:0 0 48px;border-radius:14px;color:#fff;background:#0aa873;font-size:21px;box-shadow:0 8px 18px rgba(10,168,115,.22); }
.inventory-initial-dropzone__copy { min-width:0;margin:0 14px;flex:1; }
.inventory-initial-dropzone__copy strong,.inventory-initial-dropzone__copy small { display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.inventory-initial-dropzone__copy strong { font-size:14px;font-weight:900; }
.inventory-initial-dropzone__copy small { margin-top:3px;color:#68817d;font-size:11px;font-weight:700; }
.inventory-initial-dropzone__action { padding:8px 12px;border-radius:9px;color:#087154;background:#d6f5e9;font-size:11px;font-weight:900; }
.inventory-initial-loading { display:flex;align-items:center;justify-content:center;gap:9px;margin-top:14px;padding:12px;border-radius:12px;color:#176c5b;background:#e5f7f1;font-size:12px;font-weight:800; }
.inventory-initial-stats { display:grid;grid-template-columns:repeat(4,1fr);gap:9px;margin-top:14px; }
.inventory-initial-stats>div { padding:12px;border:1px solid #d8e9e5;border-radius:13px;background:#fff; }
.inventory-initial-stats strong,.inventory-initial-stats span { display:block; }
.inventory-initial-stats strong { color:#075a4a;font-size:17px;font-weight:900; }
.inventory-initial-stats span { margin-top:2px;color:#6a817d;font-size:9px;font-weight:900;letter-spacing:.55px;text-transform:uppercase; }
.inventory-initial-alert { display:flex;align-items:flex-start;gap:10px;margin-top:12px;padding:12px 14px;border-radius:12px;font-size:12px;font-weight:750;line-height:1.45; }
.inventory-initial-alert i { margin-top:2px; }
.inventory-initial-alert ul { margin:0;padding-left:17px; }
.inventory-initial-alert--warning { border:1px solid #f2d894;color:#755613;background:#fff7dc; }
.inventory-initial-alert--error { border:1px solid #f0b8bc;color:#9c2630;background:#fff0f1; }
.inventory-initial-errors { margin-top:12px;padding:13px 15px;border:1px solid #efb9bd;border-radius:12px;color:#91252d;background:#fff; }
.inventory-initial-errors>strong { font-size:12px;font-weight:900; }
.inventory-initial-errors ul { max-height:125px;margin:8px 0 0;padding-left:18px;overflow:auto;font-size:11px; }
.inventory-initial-preview { margin-top:14px;overflow:hidden;border:1px solid #d7e6e3;border-radius:14px;background:#fff; }
.inventory-initial-preview__heading { display:flex;align-items:center;justify-content:space-between;padding:11px 14px;color:#fff;background:#075c50; }
.inventory-initial-preview__heading strong { font-size:12px;font-weight:900; }
.inventory-initial-preview__heading span { color:#cbeae5;font-size:10px;font-weight:750; }
.inventory-initial-preview table { min-width:690px;font-size:11px; }
.inventory-initial-preview th { border:0!important;color:#315b55;background:#e7f3f0;font-size:9px;font-weight:900;text-transform:uppercase; }
.inventory-initial-preview td { vertical-align:middle;border-color:#e6efed!important; }
.inventory-initial-actions { display:flex;justify-content:flex-end;gap:9px;margin-top:17px;padding-top:15px;border-top:1px solid #dce9e6; }
.inventory-initial-cancel,.inventory-initial-confirm { min-height:45px;padding:0 18px;border:0;border-radius:11px;font-size:12px;font-weight:900;cursor:pointer; }
.inventory-initial-cancel { color:#506965;background:#e3ecea; }
.inventory-initial-confirm { min-width:220px;color:#fff;background:#0aac70;box-shadow:0 9px 20px rgba(10,172,112,.23); }
.inventory-initial-confirm:disabled,.inventory-initial-cancel:disabled { cursor:not-allowed;opacity:.55; }
.inventory-initial-confirm i { margin-right:7px; }
@media (max-width: 991px) {
  .inventory-movement-page { padding: 14px; }
  .inventory-heading { align-items: flex-start; }
  .inventory-heading__status { display: none; }
  .inventory-heading__actions { flex-wrap:wrap;justify-content:flex-end; }
  .inventory-data-grid > .inventory-card {
    max-width: calc(100% - 14px); flex: 0 0 calc(100% - 14px); margin-bottom: 14px;
  }
}
@media (max-width: 575px) {
  .inventory-movement-page { padding: 8px; }
  .inventory-heading { padding: 15px; border-radius: 17px; }
  .inventory-heading { flex-wrap:wrap; }
  .inventory-heading__actions { width:100%;margin-left:0;display:grid;grid-template-columns:1fr 1fr; }
  .inventory-initial-button,.inventory-toggle-data { width:100%; }
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
  .inventory-confirm-summary { grid-template-columns: 1fr; }
  .inventory-confirm-summary__wide { grid-column: auto; }
  .inventory-confirm-actions { flex-direction: column; }
  .inventory-confirm-save, .inventory-confirm-review { width: 100%; }
  .inventory-initial-body { padding:14px!important; }
  .inventory-initial-title small { display:none; }
  .inventory-initial-dropzone__action { display:none; }
  .inventory-initial-stats { grid-template-columns:1fr 1fr; }
  .inventory-initial-preview__heading { align-items:flex-start;flex-direction:column;gap:3px; }
  .inventory-initial-actions { flex-direction:column-reverse; }
  .inventory-initial-cancel,.inventory-initial-confirm { width:100%; }
}
</style>
