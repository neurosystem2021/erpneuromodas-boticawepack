<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated>
        <b-container class="bv-example-row px-5" fluid>
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
              <b-col md="4">
                <b-row >
                  
                  <b-col md="5">
                    <b-form-group>
                      <label>Serie:</label>                    
                      <b-form-input 
                        ref="inputSerie"
                        v-model="movimiento.Serie"
                        required="required"
                        @input="cargarNumero"
                        :disable="bloquearSerie"
                        type="text"
                        placeholder=""></b-form-input>
                    </b-form-group>
                    <b-form-group>
                      <label>Número:</label>                    
                      <b-form-input
                        ref="inputNumero"
                        v-model="movimiento.Numero"
                        required="required"
                        :disable="bloquearSerie"
                        type="text"
                        placeholder=""
                        
                        ></b-form-input>
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

                  </b-col>
                  <b-col md="7">
                    <b-form-group>
                      <label>Fecha Contable:</label>
                      <b-form-input
                        v-model="movimiento.MovimientoFecha"
                        type="date"
                        step="1"
                        placeholder="Fecha Contable"></b-form-input>

                    </b-form-group>
                    <b-input-group class="my-2" prepend="Fec. Stock:">
                      <b-form-input
                        v-model="movimiento.FechaStock"
                        type="date"
                        placeholder="Fecha Stock"
                        disabled="disabled"></b-form-input>
                    </b-input-group>
                    <b-input-group class="my-2" prepend="Tipo de cambio:">
                      <input v-model="movimiento.TipoCambio"
                        type="number"
                        class="form-control"
                        step="0.0001"
                        placeholder="Tipo de cambio"/>
                    </b-input-group>

                   <b-input-group prepend="Moneda">
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
                   </b-input-group>
                  </b-col>
                </b-row>
              </b-col>
          </b-row>
          <b-row>
            <b-col>
                <productos-carrito ref="carrito" type="mov"></productos-carrito>
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
      bloquearSerie: false,
      inProgress: false,
      newRuc: "",
      API: config.API,
      ultimoMovimiento: "",
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
      movimiento: this.initMovimiento()
    };
  },
  computed: {
    ...mapState({
      proveedoresFiltrados: state => state.proveedoresFiltrados,
      almacenes: state => state.almacenesFiltrados,
      productos: state => state.cart.productos
    }),
    isNotaCredito() {
      let val = false;
      if (Object.keys(this.movimiento.movimientoTipo).length > 0) {
        if (this.movimiento.movimientoTipo.CodSunat == "07") {
          this.movimiento.Serie = "_C01";
          this.cargarNumero();
          setTimeout(() => {
            this.$refs.inputSerie.$refs.input.disabled = true;
            this.$refs.inputNumero.$refs.input.disabled = true;
          }, 100);
          val = true;
        } else {
          setTimeout(() => {
            this.$refs.inputSerie.$refs.input.disabled = false;
            this.$refs.inputNumero.$refs.input.disabled = false;
          }, 100);
        }
      }

      return val;
    }
  },
  methods: {
    ...mapActions({
      buscarProveedores: "buscarProveedores",
      buscarAlmacenes: "buscarAlmacenes",
      limpiar: "limpiarCarrito"
    }),
    setMotivo(ev) {
      if (ev == "01" || ev == "02" || ev == "06" || ev == null) {
        this.bloquearInput = true;
      } else {
        this.bloquearInput = false;
      }
    },
    setDocRef(obj) {
      console.log(obj);
      this.movimiento.almacenDestino.IdAlmacen = obj.IdAlmacenDestino;
      this.movimiento.almacenOrigen.IdAlmacen = obj.IdAlmacenOrigen;
      this.movimiento.TipoCambio = obj.TipoCambio;
      this.setMoneda({
        Moneda: obj.Moneda,
        TipoCambio: obj.TipoCambio
      });
      this.movimiento.EsConsumoVale = obj.EsConsumoVale;
      this.movimiento.CodSunatModifica = obj.CodSunat;
      this.movimiento.NroComprobanteModifica = obj.NroComprobante;
      this.movimiento.IdComprobanteModifica = obj.IdDocVenta;
      this.movimiento.EsFacturaValeNC = obj.EsFacturaVale;
      if (obj.EsFacturaVale == 1) {
        this.$toasted.info(
          "Es una factura de vales realizados, los vales pasarán a pendiente",
          { duration: 5000 }
        );
      }

      //Cargar producto del documento afectado nota credito
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
                // this.movimiento.cliente = response.data;
                // this.movimiento.CampoDireccion = this.movimiento.cliente.Direccion;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.limpiar();
          response.data.map(item => {
            this.$store.state.cart.productos.push(item);
            //this.add([item, () => {}, false]);
          });
          this.movimiento.NotaIdMotivo = null;
          // this.bloquearInput = true;
          // this.obtenerStok({ IdAlmacen: this.movimiento.almacen.IdAlmacen });
        });
    },
    loadFirstAlmacen(cb) {
      this.axios.get(this.API + "almacenes/primero").then(res => {
        cb(res.data);
      });
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
        MovimientoFecha: this.$moment().format("YYYY-MM-DD"),
        // MovimientoFecha: (this.$browser.name == 'chrome') ? this.$moment().format("YYYY-MM-DDThh:mm:ss") : this.$moment().format("YYYY-MM-DD hh:mm:ss"),
        FechaStock: this.$moment().format("YYYY-MM-DD"),
        TipoCambio: 1,
        proveedor: { IdProveedor: 0 },
        movimientoTipo: {},
        almacenOrigen: { IdAlmacen: -1 },
        almacenDestino: { IdAlmacen: -1 }
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
    afe(e) {
      return false;
    },
    setTipoMovimiento(obj) {
      this.movimiento.IdComprobanteModifica = 0;
      this.movimiento.CodSunatModifica = "";
      this.movimiento.NotaIdMotivo = "";
      this.movimiento.NotaDescMotivo = "";
      if (obj.Tipo == 0) {
        this.movimiento.almacenOrigen = { IdAlmacen: -1 };
        // this.movimiento.almacenDestino = { IdAlmacen: 1 }
        this.loadFirstAlmacen(data => {
          this.$refs.inputAlmacenDestino.setValue(data.Almacen);
          this.movimiento.almacenDestino = data;
        });
      } else if (obj.Tipo == 1) {
        this.movimiento.almacenDestino = { IdAlmacen: -1 };
        // this.movimiento.almacenOrigen = { IdAlmacen: 1 }
        this.loadFirstAlmacen(data => {
          this.$refs.inputAlmacenOrigen.setValue(data.Almacen);
          this.movimiento.almacenOrigen = data;
        });
      } else {
        // this.movimiento.almacenDestino = { IdAlmacen: 1 }
        // this.movimiento.almacenOrigen = { IdAlmacen: 1 }
        this.loadFirstAlmacen(data => {
          this.$refs.inputAlmacenOrigen.setValue(data.Almacen);
          this.movimiento.almacenOrigen = data;

          this.$refs.inputAlmacenDestino.setValue(data.Almacen);
          this.movimiento.almacenDestino = data;
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
    },
    setAlmacenDestino(obj) {
      this.movimiento.almacenDestino = obj;
    },
    setMoneda(obj) {
      console.log(obj);
      this.movimiento.Moneda = obj.Moneda;
      this.movimiento.TipoCambio = obj.TipoCambio;
    },
    getProveedores(value) {
      this.buscarProveedores(value);
    },
    getAlmacenes(value) {
      this.buscarAlmacenes(value);
    },
    async guardarMovimiento(evt) {
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
      if (this.movimiento.movimientoTipo.CodSunat == "07") {
        this.movimiento.NotaDescMotivo = this.itemsMotivoNota.filter(
          item => item.value == this.movimiento.NotaIdMotivo
        )[0].text;
      }
      await this.axios
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
            this.$refs.inputMoneda.setValue("PEN");
            this.$toasted.show("Movimiento guardado correctamente ");
          }
          this.inProgress = false;
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      this.$refs.inputSerie.$refs.input.disabled = false;
      this.$refs.inputNumero.$refs.input.disabled = false;
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
</style>
