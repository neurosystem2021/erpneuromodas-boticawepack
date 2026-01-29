<template>
  <div>
    <b-button @click="promptPassword"  class="btnAction" style="background-color: #183b76; color:white; font-weight: bold;">Movimiento</b-button>
    <b-modal v-model="showMovimientoModal" size="lg" title="Caja banco" ref="modalCarrito">
      <b-tabs content-class="mt-3">
        <b-tab title="Gasto" style="text-align: left;">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
                  <label>Cuenta:</label>
                  <autocomplete
                  required="required"
                  ref="inputCaja"
                  :url="API + 'cuentas'"
                  anchor="Cuenta"
                  label="writer"
                  placeholder=""
                  :on-select="setCuenta" 
                  :onFocus="focusAutocomplete.bind(null, 'inputCaja')"
                  :onInput="(val) => newDniRuc = val"
                  :classes="{input: 'form-control'}"
                  >
                  </autocomplete>
                  <br>
                  <label>Gasto:</label>
                  <b-form-group>
                    <input v-model="selectProducto.Gasto" required type="number" class="form-control" step="0.01" placeholder="S/." />
                  </b-form-group>
                  <label>Descripción:</label>
                  <b-form-group>
                    <b-form-input v-model="selectProducto.GastoDesc" required placeholder="Descripción"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </div>
            <template>
              <b-btn variant="success" type="submit" @click="insertarGasto">Guardar</b-btn>
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
        
        <b-tab title="Ingreso" style="text-align: left;">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
                  <label>Cuenta:</label>
                  <autocomplete
                  required="required"
                  ref="inputCaja2"
                  :url="API + 'cuentas'"
                  anchor="Cuenta"
                  label="writer"
                  placeholder=""
                  :on-select="setCuenta" 
                  :onFocus="focusAutocomplete.bind(null, 'inputCaja2')"
                  :onInput="(val) => newDniRuc = val"
                  :classes="{input: 'form-control'}"
                  >
                  </autocomplete>
                  <br>
                  <label>Ingreso:</label>
                  <b-form-group>
                    <input v-model="selectProducto.Ingreso" required type="number" class="form-control" step="0.01" placeholder="S/." />
                  </b-form-group>
                  <label>Descripción:</label>
                  <b-form-group>
                    <b-form-input v-model="selectProducto.IngresoDesc" required placeholder="Descripción"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </div>
            <template>
              <b-btn variant="success" type="submit" @click="insertarIngreso">Guardar</b-btn>
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
        <b-tab title="Salida" style="text-align: left;">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
                  <label>Cuenta:</label>
                  <autocomplete
                  required="required"
                  ref="inputCaja3"
                  :url="API + 'cuentas'"
                  anchor="Cuenta"
                  label="writer"
                  placeholder=""
                  :on-select="setCuenta" 
                  :onFocus="focusAutocomplete.bind(null, 'inputCaja3')"
                  :onInput="(val) => newDniRuc = val"
                  :classes="{input: 'form-control'}"
                  >
                  </autocomplete>
                  <br>
                  <label>Salida:</label>
                  <b-form-group>
                    <input v-model="selectProducto.Salida" required type="number" class="form-control" step="0.01" placeholder="S/." />
                  </b-form-group>
                  <label>Descripción:</label>
                  <b-form-group>
                    <b-form-input v-model="selectProducto.SalidaDesc" required placeholder="Descripción"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </div>
            <template>
              <b-btn variant="success" type="submit" @click="insertarSalida">Guardar</b-btn>
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
        <b-tab title="Dep. Cuenta Bancaria" style="text-align: left;">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
                  <label>Cuenta:</label>
                  <autocomplete
                  required="required"
                  ref="inputCaja4"
                  :url="API + 'cuentas'"
                  anchor="Cuenta"
                  label="writer"
                  placeholder=""
                  :on-select="setCuenta" 
                  :onFocus="focusAutocomplete.bind(null, 'inputCaja4')"
                  :onInput="(val) => newDniRuc = val"
                  :classes="{input: 'form-control'}"
                  >
                  </autocomplete>
                  <br>
                  <label>Monto:</label>
                  <b-form-group>
                    <input v-model="selectProducto.Deposito" required type="number" class="form-control" step="0.01" placeholder="S/." />
                  </b-form-group>
                  <label>N° Cuenta - Descripción:</label>
                  <b-form-group>
                    <b-form-input v-model="selectProducto.DepositoDesc" required placeholder="Descripción"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </div>
            <template>
              <b-btn variant="success" type="submit" @click="insertarDeposito">Guardar</b-btn>
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
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
import PreOrdenFormDet from "@/components/PreOrdenFormDet";
import ClientesForm from "@/components/ClientesForm";
import Proformas from "@/components/ProformaLista.vue";
import Swal from "sweetalert2";
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
    async promptPassword() {
      // this.$swal.fire("golsss");
      /** await swal
        .fire({
          title: "Ingrese sus datos Administrador",
          html:
            '<input id="swal-input1" class="swal2-input" placeholder="Nombre">' +
            '<input id="swal-input2" type="password" class="swal2-input" placeholder="Apellido">',
          focusConfirm: false,
          preConfirm: () => {
            const input1 = document.getElementById("swal-input1").value;
            const input2 = document.getElementById("swal-input2").value;
            if (!input1 || !input2) {
              Swal.showValidationMessage("Por favor, complete ambos campos");
            }
            return { usuario: input1, clave: input2 };
          },
        })
        .then(async (result) => {
          if (result.isConfirmed) {
            let res = await this.validarUsuarioAdmin({
              usuario: result.value.usuario,
              clave: result.value.clave,
            });
            console.log(res);
            val = res.estadoflag;
            msg = res.mensaje;
            // Aquí puedes manejar los datos ingresados
          }
        }); */
      await this.$swal.fire({
        icon: "info",
        title: "Escriba la contraseña del administrador",
        html:
          '<input id="swal-input1" class="swal2-input" placeholder="Usuario">' +
          '<input id="swal-input2" type="password" class="swal2-input" placeholder="Contraseña">',
        focusConfirm: false,
        showCloseButton: true,
        preConfirm: () => {
          const input1 = document.getElementById("swal-input1").value;
          const input2 = document.getElementById("swal-input2").value;
          if (!input1 || !input2) {
            this.$swal.showValidationMessage("Por favor, complete ambos campos");
          }
          return { usuario: input1, clave: input2 };
        }
      })
        .then(async result => {
          try {
            const response = await this.axios.post(this.API + "login", {
              user: result.value.usuario,
              password: result.value.clave
            });
            if (response.data) {
              console.log(response.data);
              if (response.data.IdUsuarioPerfil == 1) {
                this.showMovimientoModal = true;
              } else {
                this.$swal.fire({
                  icon: "error",
                  title: "Aviso",
                  text:
                    "EL usuario no cuenta con los privilegios de administrador."
                });
              }
            } else {
              this.$swal.fire(
                "Contraseña incorrecta",
                "La contraseña que ingresó es incorrecta.",
                "error"
              );
            }
          } catch (error) {
            return;
          }
          // if (result.isConfirmed) {
          //   let res = await this.validarUsuarioAdmin({
          //     usuario: result.value.usuario,
          //     clave: result.value.clave
          //   });
          //   console.log(res);
          //   val = res.estadoflag;
          //   msg = res.mensaje;
          //   // Aquí puedes manejar los datos ingresados
          // }
        })
        .catch(e => {
          console.log(e);
        });
    },
    focusAutocomplete (ref) {
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
            // this.cargarGastos();
            this.selectProducto.IdCuenta = "";
            this.selectProducto.Gasto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.GastoDesc = "";
          });
      }
    },
    setCuenta (obj) {
        this.Cuenta = obj
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
    insertarSalida() {
      let usuario = sessionStorage.getItem("User");
      if (this.selectProducto.Salida <= 0) {
        return;
      }
      let r = confirm("¿Desea Insertar una salida?");
      if (r) {
        this.axios
          .post(this.API + "cajaybanco/salida", {
            producto: this.selectProducto,
            concepto:
              "Vale de Egreso " + " - " + this.selectProducto.SalidaDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            IdCuenta: this.Cuenta.IdCuenta,
            Salida: this.selectProducto.Salida,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            //this.cargarGastos();
            this.$refs.inputCaja3.setValue('');
            this.selectProducto.IdCuenta = "";
            this.selectProducto.Salida = "";
            this.selectProducto.Producto = "";
            this.selectProducto.SalidaDesc = "";
          });
      }
    },
    insertarDeposito() {
      let usuario = sessionStorage.getItem("User");
      if (this.selectProducto.Deposito <= 0) {
        return;
      }
      let r = confirm("¿Desea Insertar un depósito?");
      if (r) {
        this.axios
          .post(this.API + "cajaybanco/deposito", {
            producto: this.selectProducto,
            concepto: "Deposito " + " - " + this.selectProducto.DepositoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdCuenta: this.Cuenta.IdCuenta,
            IdHabitacion: this.selectProducto.IdProducto,
            Deposito: this.selectProducto.Deposito,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            //this.cargarGastos();
            this.$refs.inputCaja4.setValue('');
            this.selectProducto.IdCuenta = "";
            this.selectProducto.Deposito = "";
            this.selectProducto.Producto = "";
            this.selectProducto.DepositoDesc = "";
          });
      }
    },
    guardarProductos() {
      this.axios
        .post(this.API + "preorden/detalle", {
          IdPreOrden: this.selectProducto.IdPreOrden,
          IdHabitacion: this.selectProducto.IdProducto
          // Otros parámetros...
        })
        .then(() => {
          this.$toasted.success("Guardado correctamente");
          this.getHabitaciones();
          this.show = false;
        });
    }
    // Otros métodos...
  },
  components: {
    PreOrdenFormDet,
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