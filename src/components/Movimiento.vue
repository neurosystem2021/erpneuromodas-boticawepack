<template>
  <div>
    <b-button-group>
      <b-button @click="showMovimientoModal = true" variant="primary">
        <i class="fas fa-exchange-alt mx-1" aria-hidden="true"></i> Movimiento
      </b-button>
    </b-button-group>
    <b-modal v-model="showMovimientoModal" size="lg" title="Caja banco" ref="modalCarrito">
      <b-tabs content-class="mt-3">
        <b-tab title="Salida Yape">
          <b-form @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
                  <label>Cuenta:</label>
                  <autocomplete required ref="inputCajaYape" :url="API + 'cuentas'" anchor="Cuenta" label="writer"
                    placeholder="" :on-select="setCuenta" :onFocus="focusAutocomplete.bind(null, 'inputCajaYape')"
                    :classes="{ input: 'form-control' }">
                  </autocomplete>
                  <br>
                  <label>Monto Salida Yape:</label>
                  <b-form-group>
                    <input v-model="selectProducto.SalidaYape" type="number" class="form-control" step="0.01"
                      placeholder="S/." />
                  </b-form-group>
                  <label>Descripción:</label>
                  <b-form-group>
                    <b-form-input v-model="selectProducto.SalidaYapeDesc"
                      placeholder="Motivo de la salida"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </div>
            <template>
              <b-btn variant="danger" @click="insertarSalidaYape">Registrar Salida</b-btn>
              <b-btn variant="secondary" @click="showMovimientoModal = false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
      </b-tabs>
    </b-modal>
  </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";
import Proformas from "@/components/ProformaLista.vue";
export default {
  mounted() {
    // this.getVendedor();
  },
  data() {
    return {
      API: config.API,
      showMovimientoModal: false,
      selectProducto: {
        IdCuenta: "",
        Gasto: "",
        GastoDesc: "",
        Ingreso: "",
        IngresoDesc: "",
        Salida: "",
        SalidaDesc: "",
        Deposito: "",
        DepositoDesc: ""
      }
      // Otros datos...
    };
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos
    })
  },
  methods: {
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick()
    },
    insertarGasto() {
      let usuario = sessionStorage.getItem("User");
      if (this.selectProducto.Gasto <= 0) {
        return;
      }
      let r = confirm("¿Desea Insertar un gasto?");
      if (r) {
        this.axios
          .post(this.API + "cajaybanco/gasto", {
            producto: this.selectProducto,
            concepto:
              "Gasto " + " - " + this.selectProducto.GastoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            IdCuenta: this.Cuenta.IdCuenta,
            Gasto: this.selectProducto.Gasto,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            this.$refs.inputCaja.setValue('');
            this.cargarGastos();
            this.selectProducto.IdCuenta = "";
            this.selectProducto.Gasto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.GastoDesc = "";
          });
      }
    },
    setCuenta(obj) {
      this.Cuenta = obj
    },
    insertarSalidaYape() {
      let usuario = sessionStorage.getItem("User");
      if (!this.selectProducto.SalidaYape || this.selectProducto.SalidaYape <= 0) {
        this.$swal.fire("Aviso", "Ingrese un monto válido", "warning");
        return;
      }
      if (!this.Cuenta || !this.Cuenta.IdCuenta) {
        this.$swal.fire("Aviso", "Seleccione una cuenta", "warning");
        return;
      }
      let r = confirm("¿Desea registrar una salida Yape?");
      if (r) {
        this.axios
          .post(this.API + "cajaybanco/salidayape", {
            concepto: "Salida Yape - " + this.selectProducto.SalidaYapeDesc,
            IdCuenta: this.Cuenta.IdCuenta,
            Salida: this.selectProducto.SalidaYape,
            Usuario: usuario
          })
          .then(res => {
            this.$swal.fire("Éxito", "Salida Yape registrada", "success");
            this.$refs.inputCajaYape.setValue('');
            this.selectProducto.SalidaYape = "";
            this.selectProducto.SalidaYapeDesc = "";
            this.showMovimientoModal = false;
            this.$emit('actualizar'); // para recargar el cierre si lo conectas
          })
          .catch(() => {
            this.$swal.fire("Error", "No se pudo registrar la salida", "error");
          });
      }
    },
    insertarIngreso() {
      let usuario = sessionStorage.getItem("User");
      if (this.selectProducto.Ingreso <= 0) {
        return;
      }
      let r = confirm("¿Desea Insertar un ingreso?");
      if (r) {
        this.axios
          .post(this.API + "cajaybanco/ingreso", {
            producto: this.selectProducto,
            concepto:
              "Vale de Ingreso " + " - " + this.selectProducto.IngresoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            IdCuenta: this.Cuenta.IdCuenta,
            Ingreso: this.selectProducto.Ingreso,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            //this.cargarGastos();
            this.$refs.inputCaja2.setValue('');
            this.selectProducto.Ingreso = "";
            this.selectProducto.Gasto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.IngresoDesc = "";
          });
      }
    },
    // Otros métodos...
  },
  components: {
    ClientesForm,
    Proformas
  }
};
</script>
<style scoped>
@media print {

  body .printable,
  body .modal {
    display: block;
  }

  .modal {
    overflow: visible !important;
    position: absolute;
  }
}

h5 {
  font-size: 1.2rem;
}

h6 {
  font-size: 1rem;
}

.card-footer {
  padding: 0;
}

.habitacion {
  margin-bottom: 10px;
}

.detalle {
  list-style: none;
  padding: 0;
  margin-bottom: 0;
  text-align: left;
  font-size: 1rem;
}
</style>