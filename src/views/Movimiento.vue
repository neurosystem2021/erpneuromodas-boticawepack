<template>
  <div class="movjobi">
    <b-button-group>
      <b-button @click="promptPassword" variant="primary" class="jobi-btn-action movjobi-trigger">
        <span class="movjobi-trigger-icon"><i class="fas fa-exchange-alt"></i></span>
        <span>Movimiento</span>
      </b-button>
    </b-button-group>

    <b-modal
      v-model="showMovimientoModal"
      size="lg"
      centered
      scrollable
      hide-header
      hide-footer
      body-class="movjobi-body"
      dialog-class="movjobi-dialog"
      content-class="movjobi-content"
      ref="modalCarrito">

      <!-- ===== Header propio, estilo pos-panel-header ===== -->
      <div class="movjobi-header">
        <div class="movjobi-header-icon"><i class="fas fa-wallet"></i></div>
        <div class="movjobi-header-text">
          <div class="movjobi-header-title">Caja &amp; Banco</div>
          <div class="movjobi-header-sub">Registra gastos, ingresos, salidas y depósitos</div>
        </div>
        <button type="button" class="movjobi-close" @click="showMovimientoModal = false" aria-label="Cerrar">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- ===== Tab-bar propio (evita conflictos con b-tabs) ===== -->
      <div class="movjobi-tabbar">
        <button type="button"
          class="movjobi-tab-pill movjobi-tab-orange"
          :class="{ active: activeTab === 'gasto' }"
          @click="activeTab = 'gasto'">
          <i class="fas fa-arrow-circle-down"></i> Gasto
        </button>
        <button type="button"
          class="movjobi-tab-pill movjobi-tab-green"
          :class="{ active: activeTab === 'ingreso' }"
          @click="activeTab = 'ingreso'">
          <i class="fas fa-arrow-circle-up"></i> Ingreso
        </button>
        <button type="button"
          class="movjobi-tab-pill movjobi-tab-pink"
          :class="{ active: activeTab === 'salida' }"
          @click="activeTab = 'salida'">
          <i class="fas fa-sign-out-alt"></i> Salida
        </button>
        <button type="button"
          class="movjobi-tab-pill movjobi-tab-blue"
          :class="{ active: activeTab === 'deposito' }"
          @click="activeTab = 'deposito'">
          <i class="fas fa-landmark"></i> Dep. Bancario
        </button>
      </div>

      <!-- ================= GASTO ================= -->
      <b-form v-show="activeTab === 'gasto'" @submit.prevent="guardarProductos" @keydown.enter.prevent class="movjobi-form">
        <div class="movjobi-card movjobi-card-orange">
          <div class="movjobi-card-title"><i class="fas fa-arrow-circle-down"></i> Registrar Gasto</div>

          <label class="movjobi-label"><i class="fas fa-university movjobi-label-icon"></i> Cuenta</label>
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
            :classes="{input: 'form-control'}">
          </autocomplete>

          <label class="movjobi-label"><i class="fas fa-coins movjobi-label-icon"></i> Monto</label>
          <b-form-group class="mb-2">
            <b-input-group prepend="S/" class="movjobi-input-monto">
              <input v-model="selectProducto.Gasto" required type="number" class="form-control" step="0.01" placeholder="0.00" />
            </b-input-group>
          </b-form-group>

          <label class="movjobi-label"><i class="fas fa-align-left movjobi-label-icon"></i> Descripción</label>
          <b-form-group class="mb-1">
            <b-form-input v-model="selectProducto.GastoDesc" required placeholder="Descripción del gasto"></b-form-input>
          </b-form-group>
        </div>

        <div class="movjobi-actions">
          <b-btn class="movjobi-btn-save movjobi-btn-orange" type="submit" @click="insertarGasto">
            <i class="fas fa-check-circle"></i> Guardar
          </b-btn>
          <b-btn class="movjobi-btn-cancel" @click="showMovimientoModal = false">
            <i class="fas fa-times"></i> Cerrar
          </b-btn>
        </div>
      </b-form>

      <!-- ================= INGRESO ================= -->
      <b-form v-show="activeTab === 'ingreso'" @submit.prevent="guardarProductos" @keydown.enter.prevent class="movjobi-form">
        <div class="movjobi-card movjobi-card-green">
          <div class="movjobi-card-title"><i class="fas fa-arrow-circle-up"></i> Registrar Ingreso</div>

          <label class="movjobi-label"><i class="fas fa-university movjobi-label-icon"></i> Cuenta</label>
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
            :classes="{input: 'form-control'}">
          </autocomplete>

          <label class="movjobi-label"><i class="fas fa-coins movjobi-label-icon"></i> Monto</label>
          <b-form-group class="mb-2">
            <b-input-group prepend="S/" class="movjobi-input-monto">
              <input v-model="selectProducto.Ingreso" required type="number" class="form-control" step="0.01" placeholder="0.00" />
            </b-input-group>
          </b-form-group>

          <label class="movjobi-label"><i class="fas fa-align-left movjobi-label-icon"></i> Descripción</label>
          <b-form-group class="mb-1">
            <b-form-input v-model="selectProducto.IngresoDesc" required placeholder="Descripción del ingreso"></b-form-input>
          </b-form-group>
        </div>

        <div class="movjobi-actions">
          <b-btn class="movjobi-btn-save movjobi-btn-green" type="submit" @click="insertarIngreso">
            <i class="fas fa-check-circle"></i> Guardar
          </b-btn>
          <b-btn class="movjobi-btn-cancel" @click="showMovimientoModal = false">
            <i class="fas fa-times"></i> Cerrar
          </b-btn>
        </div>
      </b-form>

      <!-- ================= SALIDA ================= -->
      <b-form v-show="activeTab === 'salida'" @submit.prevent="guardarProductos" @keydown.enter.prevent class="movjobi-form">
        <div class="movjobi-card movjobi-card-pink">
          <div class="movjobi-card-title"><i class="fas fa-sign-out-alt"></i> Registrar Salida</div>

          <label class="movjobi-label"><i class="fas fa-university movjobi-label-icon"></i> Cuenta</label>
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
            :classes="{input: 'form-control'}">
          </autocomplete>

          <label class="movjobi-label"><i class="fas fa-coins movjobi-label-icon"></i> Monto</label>
          <b-form-group class="mb-2">
            <b-input-group prepend="S/" class="movjobi-input-monto">
              <input v-model="selectProducto.Salida" required type="number" class="form-control" step="0.01" placeholder="0.00" />
            </b-input-group>
          </b-form-group>

          <label class="movjobi-label"><i class="fas fa-align-left movjobi-label-icon"></i> Descripción</label>
          <b-form-group class="mb-1">
            <b-form-input v-model="selectProducto.SalidaDesc" required placeholder="Descripción de la salida"></b-form-input>
          </b-form-group>
        </div>

        <div class="movjobi-actions">
          <b-btn class="movjobi-btn-save movjobi-btn-pink" type="submit" @click="insertarSalida">
            <i class="fas fa-check-circle"></i> Guardar
          </b-btn>
          <b-btn class="movjobi-btn-cancel" @click="showMovimientoModal = false">
            <i class="fas fa-times"></i> Cerrar
          </b-btn>
        </div>
      </b-form>

      <!-- ================= DEP. CUENTA BANCARIA ================= -->
      <b-form v-show="activeTab === 'deposito'" @submit.prevent="guardarProductos" @keydown.enter.prevent class="movjobi-form">
        <div class="movjobi-card movjobi-card-blue">
          <div class="movjobi-card-title"><i class="fas fa-landmark"></i> Depósito en Cuenta Bancaria</div>

          <label class="movjobi-label"><i class="fas fa-university movjobi-label-icon"></i> Cuenta</label>
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
            :classes="{input: 'form-control'}">
          </autocomplete>

          <label class="movjobi-label"><i class="fas fa-coins movjobi-label-icon"></i> Monto</label>
          <b-form-group class="mb-2">
            <b-input-group prepend="S/" class="movjobi-input-monto">
              <input v-model="selectProducto.Deposito" required type="number" class="form-control" step="0.01" placeholder="0.00" />
            </b-input-group>
          </b-form-group>

          <label class="movjobi-label"><i class="fas fa-align-left movjobi-label-icon"></i> N° Cuenta - Descripción</label>
          <b-form-group class="mb-1">
            <b-form-input v-model="selectProducto.DepositoDesc" required placeholder="Descripción"></b-form-input>
          </b-form-group>
        </div>

        <div class="movjobi-actions">
          <b-btn class="movjobi-btn-save movjobi-btn-blue" type="submit" @click="insertarDeposito">
            <i class="fas fa-check-circle"></i> Guardar
          </b-btn>
          <b-btn class="movjobi-btn-cancel" @click="showMovimientoModal = false">
            <i class="fas fa-times"></i> Cerrar
          </b-btn>
        </div>
      </b-form>
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
      activeTab: "gasto",
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
                this.activeTab = "gasto";
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
            this.cargarGastos();
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
/* =========================================================
   JoBi-style redesign — modal "Movimiento" (Caja & Banco)
   Mismo lenguaje visual del POS: navy/teal/purple, tarjetas
   redondeadas, botones pill, iconos FontAwesome.
   Requiere FontAwesome 5 cargado globalmente.
   ========================================================= */

.movjobi {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-purple: #7C3AED;
    --jobi-green: #17A554;
    --jobi-orange: #F5A623;
    --jobi-pink: #F43F7E;
    display: inline-block;
}

/* -------- Botón disparador -------- */
.movjobi-trigger {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border-radius: 999px !important;
    background: var(--jobi-blue) !important;
    border: none !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: .03em;
    padding: 10px 20px !important;
    box-shadow: 0 8px 18px rgba(47,111,237,.30);
}
.movjobi-trigger-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* -------- Contenedor del modal -------- */
/* Se usa el prop "scrollable" de b-modal (clase modal-dialog-scrollable
   que agrega Bootstrap) para el alto/scroll, en vez de flex/max-height
   manual: eso era lo que hacía que el contenido se desbordara fuera de
   la pantalla y no se viera el botón Guardar. Aquí solo estilizamos. */
.movjobi >>> .movjobi-content {
    border: none;
    border-radius: 18px;
    box-shadow: 0 20px 50px rgba(11,42,74,.35);
}
.movjobi >>> .movjobi-body {
    padding: 0 22px 22px;
    background: #EEF3F8;
    border-radius: 0 0 18px 18px;
}

/* -------- Header navy con icono -------- */
.movjobi-header {
    display: flex;
    align-items: center;
    gap: 14px;
    width: 100%;
    padding: 18px 20px;
    margin: 0 -22px 18px;
    width: calc(100% + 44px);
    border-radius: 18px 18px 0 0;
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    color: #fff;
}
.movjobi-header-icon {
    flex: 0 0 auto;
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255,255,255,.14);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}
.movjobi-header-text {
    flex: 1 1 auto;
    min-width: 0;
}
.movjobi-header-title {
    font-weight: 800;
    font-size: 1.15rem;
    letter-spacing: .01em;
}
.movjobi-header-sub {
    font-size: .78rem;
    color: rgba(255,255,255,.65);
    margin-top: 2px;
}
.movjobi-close {
    flex: 0 0 auto;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: rgba(255,255,255,.14);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color .15s ease;
}
.movjobi-close:hover {
    background: rgba(255,255,255,.28);
}

/* -------- Tab-bar propio (botones nativos, sin depender de b-tabs) -------- */
.movjobi-tabbar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 0 0 16px;
}
.movjobi-tab-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 999px;
    font-weight: 800;
    font-size: .78rem;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #4d6a8a;
    background: #fff;
    border: 1.5px solid #dde8f2;
    box-shadow: 0 3px 8px rgba(11,42,74,.06);
    cursor: pointer;
    line-height: 1;
    transition: box-shadow .18s ease, border-color .18s ease, background-color .18s ease, color .18s ease;
}
.movjobi-tab-pill:hover {
    box-shadow: 0 6px 14px rgba(11,42,74,.12);
}
.movjobi-tab-pill:focus {
    outline: none;
}
.movjobi-tab-orange.active { background: var(--jobi-orange); color: #fff; border-color: var(--jobi-orange); box-shadow: 0 8px 16px rgba(245,166,35,.30); }
.movjobi-tab-green.active  { background: var(--jobi-green);  color: #fff; border-color: var(--jobi-green);  box-shadow: 0 8px 16px rgba(23,165,84,.30); }
.movjobi-tab-pink.active   { background: var(--jobi-pink);   color: #fff; border-color: var(--jobi-pink);   box-shadow: 0 8px 16px rgba(244,63,126,.30); }
.movjobi-tab-blue.active   { background: var(--jobi-blue);   color: #fff; border-color: var(--jobi-blue);   box-shadow: 0 8px 16px rgba(47,111,237,.30); }

/* -------- Tarjeta del formulario -------- */
.movjobi-form {
    margin-top: 2px;
}
.movjobi-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 6px 16px rgba(11,42,74,.08);
    padding: 16px 18px 10px;
    border-top: 4px solid transparent;
}
.movjobi-card-orange { border-top-color: var(--jobi-orange); }
.movjobi-card-green  { border-top-color: var(--jobi-green); }
.movjobi-card-pink   { border-top-color: var(--jobi-pink); }
.movjobi-card-blue   { border-top-color: var(--jobi-blue); }

.movjobi-card-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: .92rem;
    color: var(--jobi-navy);
    margin-bottom: 14px;
    text-transform: uppercase;
    letter-spacing: .02em;
}

.movjobi-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6b85a3;
    margin-bottom: 4px;
    margin-top: 10px;
}
.movjobi-label-icon {
    font-size: .82em;
    color: #9fb3c8;
}

.movjobi-card >>> input,
.movjobi-card >>> select,
.movjobi-card >>> .form-control {
    border-radius: 10px !important;
    border: 1.5px solid #dde8f2 !important;
    height: 42px;
    font-weight: 600;
    color: var(--jobi-navy);
}

.movjobi-input-monto >>> .input-group-text {
    background: var(--jobi-navy) !important;
    color: #fff !important;
    border: none !important;
    font-weight: 800;
    border-radius: 10px 0 0 10px !important;
}
.movjobi-input-monto >>> input {
    border-radius: 0 10px 10px 0 !important;
    text-align: right;
    font-weight: 800;
}

/* -------- Acciones (Guardar / Cerrar) -------- */
.movjobi-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 4px 6px;
}

.movjobi-btn-save {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: none !important;
    border-radius: 999px !important;
    padding: 10px 22px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: .82rem;
    color: #fff !important;
    transition: transform .15s ease, box-shadow .15s ease;
}
.movjobi-btn-save:hover {
    transform: translateY(-1px);
}
.movjobi-btn-orange { background: var(--jobi-orange) !important; box-shadow: 0 8px 18px rgba(245,166,35,.30); }
.movjobi-btn-green  { background: var(--jobi-green)  !important; box-shadow: 0 8px 18px rgba(23,165,84,.30); }
.movjobi-btn-pink   { background: var(--jobi-pink)   !important; box-shadow: 0 8px 18px rgba(244,63,126,.30); }
.movjobi-btn-blue   { background: var(--jobi-blue)   !important; box-shadow: 0 8px 18px rgba(47,111,237,.30); }

.movjobi-btn-cancel {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #edf2f7 !important;
    color: #526b86 !important;
    border: none !important;
    border-radius: 999px !important;
    padding: 10px 20px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    letter-spacing: .03em;
    font-size: .82rem;
    transition: background-color .15s ease;
}
.movjobi-btn-cancel:hover {
    background: #dfe8f2 !important;
    color: #526b86 !important;
}

/* Original rules kept intact */
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

/* ============================================================
   FIX CRÍTICO — NO QUITAR
   El componente padre (POS) aplica, para "disfrazar" el botón
   disparador dentro de la cuadrícula de accesos rápidos, esta
   regla profunda (sin límite de profundidad):

     .pos-quick-label >>> .btn,
     .pos-quick-label >>> button {
         background: transparent !important;
         border: none !important;
         color: #fff !important;
         padding: 0 !important;
         box-shadow: none !important;
         pointer-events: none;
     }

   Como el modal de ESTE componente se renderiza dentro del mismo
   árbol del DOM (no se "porta" fuera de .pos-quick-label), esa
   regla también alcanza a los botones del modal (pestañas y
   Guardar/Cerrar): los deja transparentes, con texto blanco
   invisible y sin click (pointer-events:none). Se restauran aquí
   con selectores de mayor especificidad y !important para ganar
   la cascada. El botón disparador (.movjobi-trigger, fuera del
   modal) NO se toca a propósito: a él sí le corresponde el
   disfraz del padre.
   ============================================================ */
.movjobi .movjobi-tabbar .movjobi-tab-pill {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    width: auto !important;
    padding: 9px 18px !important;
    margin: 0 !important;
    border-radius: 999px !important;
    border: 1.5px solid #dde8f2 !important;
    background: #fff !important;
    color: #4d6a8a !important;
    font-weight: 800 !important;
    font-size: .78rem !important;
    text-transform: uppercase !important;
    letter-spacing: .03em !important;
    line-height: 1 !important;
    box-shadow: 0 3px 8px rgba(11,42,74,.06) !important;
    cursor: pointer !important;
    pointer-events: auto !important;
}
.movjobi .movjobi-tabbar .movjobi-tab-orange.active {
    background: #F5A623 !important;
    border-color: #F5A623 !important;
    color: #fff !important;
    box-shadow: 0 8px 16px rgba(245,166,35,.30) !important;
}
.movjobi .movjobi-tabbar .movjobi-tab-green.active {
    background: #17A554 !important;
    border-color: #17A554 !important;
    color: #fff !important;
    box-shadow: 0 8px 16px rgba(23,165,84,.30) !important;
}
.movjobi .movjobi-tabbar .movjobi-tab-pink.active {
    background: #F43F7E !important;
    border-color: #F43F7E !important;
    color: #fff !important;
    box-shadow: 0 8px 16px rgba(244,63,126,.30) !important;
}
.movjobi .movjobi-tabbar .movjobi-tab-blue.active {
    background: #2F6FED !important;
    border-color: #2F6FED !important;
    color: #fff !important;
    box-shadow: 0 8px 16px rgba(47,111,237,.30) !important;
}

.movjobi .movjobi-actions .movjobi-btn-save,
.movjobi .movjobi-actions .movjobi-btn-cancel {
    display: inline-flex !important;
    align-items: center !important;
    gap: 7px !important;
    width: auto !important;
    margin: 0 !important;
    border-radius: 999px !important;
    padding: 10px 22px !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: .03em !important;
    font-size: .82rem !important;
    line-height: 1 !important;
    cursor: pointer !important;
    pointer-events: auto !important;
}
.movjobi .movjobi-actions .movjobi-btn-save {
    border: none !important;
    color: #fff !important;
}
.movjobi .movjobi-actions .movjobi-btn-orange { background: #F5A623 !important; box-shadow: 0 8px 18px rgba(245,166,35,.30) !important; }
.movjobi .movjobi-actions .movjobi-btn-green  { background: #17A554 !important; box-shadow: 0 8px 18px rgba(23,165,84,.30) !important; }
.movjobi .movjobi-actions .movjobi-btn-pink   { background: #F43F7E !important; box-shadow: 0 8px 18px rgba(244,63,126,.30) !important; }
.movjobi .movjobi-actions .movjobi-btn-blue   { background: #2F6FED !important; box-shadow: 0 8px 18px rgba(47,111,237,.30) !important; }
.movjobi .movjobi-actions .movjobi-btn-cancel {
    background: #edf2f7 !important;
    color: #526b86 !important;
    border: none !important;
    box-shadow: none !important;
}
</style>