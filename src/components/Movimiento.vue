<template>
  <div style="width: 100%">
    <b-button @click="promptPassword" variant="primary" style="background: #5E35B1;" class="btnAction">
      Movimiento
    </b-button>
    <b-modal 
    :no-close-on-backdrop="true" v-model="showMovimientoModal" size="lg" title="Caja banco" ref="modalCarrito"
    hide-footer
    >
      <b-tabs content-class="mt-3" @click.stop>
        <b-tab title="Ingreso">
          <b-form @submit.prevent @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
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
            <template #modal-footer>
              <b-btn variant="success" type="submit" @click="insertarIngreso">Guardar</b-btn>
              <!-- <b-btn variant="secondary" @click="show=false">Cerrar</b-btn> -->
            </template>
          </b-form>
        </b-tab>

        <b-tab title="Salida">
          <b-form @submit.prevent @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
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
            <template #modal-footer>
              <b-btn variant="success" type="submit" @click="insertarSalida">Guardar</b-btn>
              <!-- <b-btn variant="secondary" @click="show=false">Cerrar</b-btn> -->
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
// import PreOrdenFormDet from "@/components/PreOrdenFormDet";
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
        Gasto: 0,
        GastoDesc: "",
        Ingreso: "",
        IngresoDesc: "",
        Salida: "",
        SalidaDesc: "",
        Deposito: "",
        DepositoDesc: "",
        Yape: "",
        YapeDesc: "",
        TotalGasto: 0,
        IdProducto: "",
        Cantidad: 1
      },
      lstProducto: [],
      almacen: {}
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
      await this.$swal
        .fire({
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
              this.$swal.showValidationMessage(
                "Por favor, complete ambos campos"
              );
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
              if (response.data.IdUsuarioPerfil == 1) {
                // await this.obtenerProductosTipoChatarra();
                this.showMovimientoModal = true;
              } else {
                this.$swal.fire({
                  icon: "error",
                  title: "Aviso",
                  text:
                    "El usuario no cuenta con los privilegios de administrador."
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

    insertarIngreso() {
      let usuario = sessionStorage.getItem("User");
      let idAlmacen = sessionStorage.getItem("IdAlmacen");
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
            Ingreso: this.selectProducto.Ingreso,
            Usuario: usuario,
            IdAlmacen: idAlmacen
          })
          .then(res => {
            this.selectProducto.Ingreso = "";
            this.selectProducto.Producto = "";
            this.selectProducto.IngresoDesc = "";

            this.showMovimientoModal = false;

            this.$swal.fire({
              icon: "success",
              title: "¡Éxito!",
              text: "Ingreso registrado correctamente.",
              timer: 2000,
              showConfirmButton: false
            });
          });
      }
    },

    insertarSalida() {
      let usuario = sessionStorage.getItem("User");
      let idAlmacen = sessionStorage.getItem("IdAlmacen");

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
            Salida: this.selectProducto.Salida,
            Usuario: usuario,
            IdAlmacen: idAlmacen
          })
          .then(res => {
            this.selectProducto.Salida = "";
            this.selectProducto.Producto = "";
            this.selectProducto.SalidaDesc = "";

            this.showMovimientoModal = false;

            this.$swal.fire({
              icon: "success",
              title: "¡Éxito!",
              text: "Salida registrada correctamente.",
              timer: 2000,
              showConfirmButton: false
            });
          });
      }
    },

    setAlmacen(obj) {
      this.almacen = obj;
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    recalcularTotal() {
      if (this.selectProducto.IdProducto) {
        let valor = this.lstProducto.filter(
          v => v.IdProducto == this.selectProducto.IdProducto
        )[0];

        this.selectProducto.TotalGasto = parseFloat(
          this.selectProducto.Cantidad * valor.PrecioCosto
        ).toFixed(2);
      }
    }
  },
  components: {
    // PreOrdenFormDet,
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
  .modal-footer {
    visibility: hidden;
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