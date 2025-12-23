<template>
  <div>
   
      <b-button @click="promptPassword" variant="primary" style="background: #5E35B1;" class="btnAction">Movimiento</b-button>
    
    <b-modal 
    v-model="showMovimientoModal" size="lg" title="Caja banco" ref="modalCarrito"
    hide-footer
    >
      <b-tabs content-class="mt-3" @click.stop>
        <!-- <b-tab title="Compra de chatarra">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="6">
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
                  <b-form-group>
                    <label>Gasto:</label>
                    <b-form-select v-model="selectProducto.IdProducto" 
                    :options="lstProducto"
                    value-field="IdProducto"
                    text-field="Producto"
                    required
                    @change="ObtenerCosto"
                    ref="cboProductos"
                    ></b-form-select>
                  </b-form-group>
                  <b-form-group>
                    Cantidad:
                    <input v-model="selectProducto.Cantidad"
                    @input="recalcularTotal"
                    
                    required type="number" class="form-control" step="1" />
                  </b-form-group>
                  <b-form-group>
                    Costo:
                    <input v-model="selectProducto.Gasto"  @input="recalcularTotal" required type="number" class="form-control" step="0.01" placeholder="S/." />
                  </b-form-group>
                  <b-form-group>
                    Total Costo:
                    <input v-model="selectProducto.TotalGasto" readonly required type="number" class="form-control" step="0.01" placeholder="S/." />
                  </b-form-group>
                 
                  <label>Descripción:</label>
                  <b-form-group>
                    <b-form-input v-model="selectProducto.GastoDesc" required placeholder="Descripción"></b-form-input>
                  </b-form-group>
                </b-col>
              </b-row>
            </div>
            <template #modal-footer>
              <b-btn variant="success" type="submit" @click="insertarGasto">Guardar</b-btn>
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
         -->
        <b-tab title="Ingreso">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
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
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>

        <b-tab title="Salida">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
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
              <b-btn variant="secondary" @click="show=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>

        <b-tab title="Dep. Cuenta Bancaria">
          <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent>
            <div class="my-3">
              <b-row>
                <b-col md="4">
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
            <template #modal-footer>
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
        Gasto: 0,
        GastoDesc: "",
        Ingreso: "",
        IngresoDesc: "",
        Salida: "",
        SalidaDesc: "",
        Deposito: "",
        DepositoDesc: "",
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
      this.showMovimientoModal = true;
      // await this.$swal
      //   .fire({
      //     icon: "info",
      //     title: "Escriba la contraseña del administrador",
      //     html:
      //       '<input id="swal-input1" class="swal2-input" placeholder="Usuario">' +
      //       '<input id="swal-input2" type="password" class="swal2-input" placeholder="Contraseña">',
      //     focusConfirm: false,
      //     showCloseButton: true,
      //     preConfirm: () => {
      //       const input1 = document.getElementById("swal-input1").value;
      //       const input2 = document.getElementById("swal-input2").value;
      //       if (!input1 || !input2) {
      //         this.$swal.showValidationMessage(
      //           "Por favor, complete ambos campos"
      //         );
      //       }
      //       return { usuario: input1, clave: input2 };
      //     }
      //   })
      //   .then(async result => {
      //     try {
      //       const response = await this.axios.post(this.API + "login", {
      //         user: result.value.usuario,
      //         password: result.value.clave
      //       });
      //       if (response.data) {
      //         if (response.data.IdUsuarioPerfil == 1) {
      //           // await this.obtenerProductosTipoChatarra();
      //           this.showMovimientoModal = true;
      //         } else {
      //           this.$swal.fire({
      //             icon: "error",
      //             title: "Aviso",
      //             text:
      //               "EL usuario no cuenta con los privilegios de administrador."
      //           });
      //         }
      //       } else {
      //         this.$swal.fire(
      //           "Contraseña incorrecta",
      //           "La contraseña que ingresó es incorrecta.",
      //           "error"
      //         );
      //       }
      //     } catch (error) {
      //       return;
      //     }

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
      // })
      // .catch(e => {
      //   console.log(e);
      // });
    },
    ObtenerCosto(item) {
      let valor = this.lstProducto.filter(v => v.IdProducto == item)[0];

      this.selectProducto.Cantidad = 1;
      this.selectProducto.Gasto = valor.PrecioCosto;
      this.selectProducto.TotalGasto = valor.PrecioCosto;
      this.recalcularTotal();
    },
    async insertarGasto() {
      let usuario = sessionStorage.getItem("User");

      if (this.selectProducto.Gasto <= 0) {
        return;
      }

      let r = confirm("¿Desea Insertar un gasto?");

      if (r) {
        let data = {
          producto: this.selectProducto,
          concepto:
            "Compra de chatarra " + " - " + this.selectProducto.GastoDesc
        };
        // console.log(this.almacen.IdAlmacen);
        await this.axios
          .post(this.API + "cajaybanco/gasto", {
            idAlmacen: this.almacen.IdAlmacen,
            producto: this.selectProducto,
            concepto:
              "Compra de chatarra " + " - " + this.selectProducto.GastoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            Gasto: this.selectProducto.Gasto,
            TotalGasto: this.selectProducto.TotalGasto,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            // this.cargarGastos();
            this.selectProducto = {
              Gasto: 0,
              GastoDesc: "",
              Ingreso: "",
              IngresoDesc: "",
              Salida: "",
              SalidaDesc: "",
              Deposito: "",
              DepositoDesc: "",
              TotalGasto: 0,
              IdProducto: "",
              Cantidad: 1
            };
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
            Ingreso: this.selectProducto.Ingreso,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            //this.cargarGastos();
            this.selectProducto.Ingreso = "";
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
            Salida: this.selectProducto.Salida,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            //this.cargarGastos();
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
            IdHabitacion: this.selectProducto.IdProducto,
            Deposito: this.selectProducto.Deposito,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            //this.cargarGastos();
            this.selectProducto.Deposito = "";
            this.selectProducto.Producto = "";
            this.selectProducto.DepositoDesc = "";
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
    guardarProductos() {
      this.axios
        .post(this.API + "preorden/detalle", {
          IdPreOrden: this.selectProducto.IdPreOrden,
          IdHabitacion: this.selectProducto.IdProducto
          // Otros parámetros...
        })
        .then(() => {
          this.$toasted.success("Guardado correctamente");
          // this.getHabitaciones();
          this.show = false;
        });
    },
    async obtenerProductosTipoChatarra() {
      await this.axios
        .get(this.API + "/productos/cargar_producto_charrata")
        .then(response => {
          let res = response.data;
          if (res.data) {
            this.lstProducto = res.data;
          }
          if (res.error) {
            this.$toasted.error(res.error);
          }
          // console.log(res);
        });
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
