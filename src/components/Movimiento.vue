<template>
  <div class="movjobi">

    <!-- ================= BOTÓN MOVIMIENTO ================= -->
    <b-button-group>
      <b-button
        @click="promptPassword"
        variant="primary"
        class="jobi-btn-action movjobi-trigger"
      >
        <span class="movjobi-trigger-icon">
        </span>
        <span>Movimiento</span>
      </b-button>
    </b-button-group>

    <!-- ================= MODAL ================= -->
    <b-modal
      container="body"
      append-to-body
      v-model="showMovimientoModal"
      size="lg"
      centered
      scrollable
      hide-header
      hide-footer
      body-class="movjobi-body"
      dialog-class="movjobi-dialog"
      content-class="movjobi-content"
      ref="modalCarrito"
    >

      <!-- ================= HEADER ================= -->
      <div class="movjobi-header">

        <div class="movjobi-header-icon">
          <i class="fas fa-wallet"></i>
        </div>

        <div class="movjobi-header-text">
          <div class="movjobi-header-title">
            Caja &amp; Banco
          </div>

          <div class="movjobi-header-sub">
            Registra gastos, ingresos, salidas y depósitos
          </div>
        </div>

        <button
          type="button"
          class="movjobi-close"
          @click="showMovimientoModal = false"
          aria-label="Cerrar"
        >
          <i class="fas fa-times"></i>
        </button>

      </div>

      <!-- ================= TABS ================= -->
      <div class="movjobi-tabbar">

        <button
          type="button"
          class="movjobi-tab-pill movjobi-tab-orange"
          :class="{ active: activeTab === 'gasto' }"
          @click="activeTab = 'gasto'"
        >
          <i class="fas fa-arrow-circle-down"></i>
          Gasto
        </button>

        <button
          type="button"
          class="movjobi-tab-pill movjobi-tab-green"
          :class="{ active: activeTab === 'ingreso' }"
          @click="activeTab = 'ingreso'"
        >
          <i class="fas fa-arrow-circle-up"></i>
          Ingreso
        </button>

        <button
          type="button"
          class="movjobi-tab-pill movjobi-tab-pink"
          :class="{ active: activeTab === 'salida' }"
          @click="activeTab = 'salida'"
        >
          <i class="fas fa-sign-out-alt"></i>
          Salida
        </button>

        <button
          type="button"
          class="movjobi-tab-pill movjobi-tab-blue"
          :class="{ active: activeTab === 'deposito' }"
          @click="activeTab = 'deposito'"
        >
          <i class="fas fa-landmark"></i>
          Dep. Bancario
        </button>

      </div>

      <!-- ===================================================== -->
      <!-- ======================= GASTO ======================= -->
      <!-- ===================================================== -->

      <b-form
        v-show="activeTab === 'gasto'"
        @submit.prevent="insertarGasto"
        @keydown.enter.prevent
        class="movjobi-form"
      >

        <div class="movjobi-card movjobi-card-orange">

          <div class="movjobi-card-title">
            <i class="fas fa-arrow-circle-down"></i>
            Registrar Gasto
          </div>

          <!-- CUENTA -->
          <label class="movjobi-label">
            <i class="fas fa-university movjobi-label-icon"></i>
            Cuenta
          </label>

          <autocomplete
            required="required"
            ref="inputCaja"
            :url="API + 'cuentas'"
            anchor="Cuenta"
            label="writer"
            placeholder=""
            :on-select="setCuenta"
            :onFocus="focusAutocomplete.bind(null, 'inputCaja')"
            :onInput="val => newDniRuc = val"
            :classes="{ input: 'form-control' }"
          >
          </autocomplete>

          <!-- MONTO -->
          <label class="movjobi-label">
            <i class="fas fa-coins movjobi-label-icon"></i>
            Monto
          </label>

          <b-form-group class="mb-2">

            <b-input-group
              prepend="S/"
              class="movjobi-input-monto"
            >

              <input
                v-model="selectProducto.Gasto"
                required
                type="number"
                min="0.01"
                step="0.01"
                class="form-control"
                placeholder="0.00"
              />

            </b-input-group>

          </b-form-group>

          <!-- DESCRIPCIÓN -->
          <label class="movjobi-label">
            <i class="fas fa-align-left movjobi-label-icon"></i>
            Descripción
          </label>

          <b-form-group class="mb-1">

            <b-form-input
              v-model="selectProducto.GastoDesc"
              required
              placeholder="Descripción del gasto"
            >
            </b-form-input>

          </b-form-group>

        </div>

        <div class="movjobi-actions">

          <b-button
            class="movjobi-btn-save movjobi-btn-orange"
            type="submit"
          >
            <i class="fas fa-check-circle"></i>
            Guardar
          </b-button>

          <b-button
            type="button"
            class="movjobi-btn-cancel"
            @click="showMovimientoModal = false"
          >
            <i class="fas fa-times"></i>
            Cerrar
          </b-button>

        </div>

      </b-form>


      <!-- ===================================================== -->
      <!-- ====================== INGRESO ====================== -->
      <!-- ===================================================== -->

      <b-form
        v-show="activeTab === 'ingreso'"
        @submit.prevent="insertarIngreso"
        @keydown.enter.prevent
        class="movjobi-form"
      >

        <div class="movjobi-card movjobi-card-green">

          <div class="movjobi-card-title">
            <i class="fas fa-arrow-circle-up"></i>
            Registrar Ingreso
          </div>

          <!-- CUENTA -->
          <label class="movjobi-label">
            <i class="fas fa-university movjobi-label-icon"></i>
            Cuenta
          </label>

          <autocomplete
            required="required"
            ref="inputCaja2"
            :url="API + 'cuentas'"
            anchor="Cuenta"
            label="writer"
            placeholder=""
            :on-select="setCuenta"
            :onFocus="focusAutocomplete.bind(null, 'inputCaja2')"
            :onInput="val => newDniRuc = val"
            :classes="{ input: 'form-control' }"
          >
          </autocomplete>

          <!-- MONTO -->
          <label class="movjobi-label">
            <i class="fas fa-coins movjobi-label-icon"></i>
            Monto
          </label>

          <b-form-group class="mb-2">

            <b-input-group
              prepend="S/"
              class="movjobi-input-monto"
            >

              <input
                v-model="selectProducto.Ingreso"
                required
                type="number"
                min="0.01"
                step="0.01"
                class="form-control"
                placeholder="0.00"
              />

            </b-input-group>

          </b-form-group>

          <!-- DESCRIPCIÓN -->
          <label class="movjobi-label">
            <i class="fas fa-align-left movjobi-label-icon"></i>
            Descripción
          </label>

          <b-form-group class="mb-1">

            <b-form-input
              v-model="selectProducto.IngresoDesc"
              required
              placeholder="Descripción del ingreso"
            >
            </b-form-input>

          </b-form-group>

        </div>

        <div class="movjobi-actions">

          <b-button
            class="movjobi-btn-save movjobi-btn-green"
            type="submit"
          >
            <i class="fas fa-check-circle"></i>
            Guardar
          </b-button>

          <b-button
            type="button"
            class="movjobi-btn-cancel"
            @click="showMovimientoModal = false"
          >
            <i class="fas fa-times"></i>
            Cerrar
          </b-button>

        </div>

      </b-form>


      <!-- ===================================================== -->
      <!-- ======================= SALIDA ====================== -->
      <!-- ===================================================== -->

      <b-form
        v-show="activeTab === 'salida'"
        @submit.prevent="insertarSalida"
        @keydown.enter.prevent
        class="movjobi-form"
      >

        <div class="movjobi-card movjobi-card-pink">

          <div class="movjobi-card-title">
            <i class="fas fa-sign-out-alt"></i>
            Registrar Salida
          </div>

          <!-- CUENTA -->
          <label class="movjobi-label">
            <i class="fas fa-university movjobi-label-icon"></i>
            Cuenta
          </label>

          <autocomplete
            required="required"
            ref="inputCaja3"
            :url="API + 'cuentas'"
            anchor="Cuenta"
            label="writer"
            placeholder=""
            :on-select="setCuenta"
            :onFocus="focusAutocomplete.bind(null, 'inputCaja3')"
            :onInput="val => newDniRuc = val"
            :classes="{ input: 'form-control' }"
          >
          </autocomplete>

          <!-- MONTO -->
          <label class="movjobi-label">
            <i class="fas fa-coins movjobi-label-icon"></i>
            Monto
          </label>

          <b-form-group class="mb-2">

            <b-input-group
              prepend="S/"
              class="movjobi-input-monto"
            >

              <input
                v-model="selectProducto.Salida"
                required
                type="number"
                min="0.01"
                step="0.01"
                class="form-control"
                placeholder="0.00"
              />

            </b-input-group>

          </b-form-group>

          <!-- DESCRIPCIÓN -->
          <label class="movjobi-label">
            <i class="fas fa-align-left movjobi-label-icon"></i>
            Descripción
          </label>

          <b-form-group class="mb-1">

            <b-form-input
              v-model="selectProducto.SalidaDesc"
              required
              placeholder="Descripción de la salida"
            >
            </b-form-input>

          </b-form-group>

        </div>

        <div class="movjobi-actions">

          <b-button
            class="movjobi-btn-save movjobi-btn-pink"
            type="submit"
          >
            <i class="fas fa-check-circle"></i>
            Guardar
          </b-button>

          <b-button
            type="button"
            class="movjobi-btn-cancel"
            @click="showMovimientoModal = false"
          >
            <i class="fas fa-times"></i>
            Cerrar
          </b-button>

        </div>

      </b-form>


      <!-- ===================================================== -->
      <!-- ====================== DEPÓSITO ===================== -->
      <!-- ===================================================== -->

      <b-form
        v-show="activeTab === 'deposito'"
        @submit.prevent="insertarDeposito"
        @keydown.enter.prevent
        class="movjobi-form"
      >

        <div class="movjobi-card movjobi-card-blue">

          <div class="movjobi-card-title">
            <i class="fas fa-landmark"></i>
            Depósito en Cuenta Bancaria
          </div>

          <!-- CUENTA -->
          <label class="movjobi-label">
            <i class="fas fa-university movjobi-label-icon"></i>
            Cuenta
          </label>

          <autocomplete
            required="required"
            ref="inputCaja4"
            :url="API + 'cuentas'"
            anchor="Cuenta"
            label="writer"
            placeholder=""
            :on-select="setCuenta"
            :onFocus="focusAutocomplete.bind(null, 'inputCaja4')"
            :onInput="val => newDniRuc = val"
            :classes="{ input: 'form-control' }"
          >
          </autocomplete>

          <!-- MONTO -->
          <label class="movjobi-label">
            <i class="fas fa-coins movjobi-label-icon"></i>
            Monto
          </label>

          <b-form-group class="mb-2">

            <b-input-group
              prepend="S/"
              class="movjobi-input-monto"
            >

              <input
                v-model="selectProducto.Deposito"
                required
                type="number"
                min="0.01"
                step="0.01"
                class="form-control"
                placeholder="0.00"
              />

            </b-input-group>

          </b-form-group>

          <!-- DESCRIPCIÓN -->
          <label class="movjobi-label">
            <i class="fas fa-align-left movjobi-label-icon"></i>
            N° Cuenta - Descripción
          </label>

          <b-form-group class="mb-1">

            <b-form-input
              v-model="selectProducto.DepositoDesc"
              required
              placeholder="Descripción"
            >
            </b-form-input>

          </b-form-group>

        </div>

        <div class="movjobi-actions">

          <b-button
            class="movjobi-btn-save movjobi-btn-blue"
            type="submit"
          >
            <i class="fas fa-check-circle"></i>
            Guardar
          </b-button>

          <b-button
            type="button"
            class="movjobi-btn-cancel"
            @click="showMovimientoModal = false"
          >
            <i class="fas fa-times"></i>
            Cerrar
          </b-button>

        </div>

      </b-form>

    </b-modal>

  </div>
</template>


<script>

import { mapState } from "vuex";
import config from "@/store/config.js";

import PreOrdenFormDet from "@/components/PreOrdenFormDet";
import ClientesForm from "@/components/ClientesForm";
import Proformas from "@/components/ProformaLista.vue";

export default {

  mounted() {
    // this.getVendedor();
    this.$nextTick(() => {
      try {
        const modal = this.$refs.modalCarrito && (this.$refs.modalCarrito.$el || this.$refs.modalCarrito)
        if (modal && modal.parentNode !== document.body) document.body.appendChild(modal)
      } catch (e) {}
    })
  },

  data() {
    return {

      API: config.API,

      showMovimientoModal: false,

      activeTab: "gasto",

      // CORREGIDO
      Cuenta: null,

      newDniRuc: "",

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

    };
  },

  computed: {

    ...mapState({

      productos: state => state.cart.productos

    })

  },

  methods: {

    // =========================================================
    // PASSWORD ADMINISTRADOR
    // =========================================================

    async promptPassword() {

      const result = await this.$swal.fire({

        icon: "info",

        title: "Escriba la contraseña del administrador",

        html:
          '<input id="swal-input1" class="swal2-input" placeholder="Usuario">' +
          '<input id="swal-input2" type="password" class="swal2-input" placeholder="Contraseña">',

        focusConfirm: false,

        showCloseButton: true,

        showCancelButton: true,

        cancelButtonText: "Cancelar",

        confirmButtonText: "Ingresar",

        preConfirm: () => {

          const input1 =
            document.getElementById("swal-input1").value;

          const input2 =
            document.getElementById("swal-input2").value;

          if (!input1 || !input2) {

            this.$swal.showValidationMessage(
              "Por favor, complete ambos campos"
            );

            return false;
          }

          return {
            usuario: input1,
            clave: input2
          };

        }

      });

      if (!result.isConfirmed) {
        return;
      }

      try {

        const response = await this.axios.post(
          this.API + "login",
          {
            user: result.value.usuario,
            password: result.value.clave
          }
        );

        console.log("LOGIN:", response.data);

        if (response.data) {

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

          this.$swal.fire({

            icon: "error",

            title: "Contraseña incorrecta",

            text:
              "La contraseña que ingresó es incorrecta."

          });

        }

      } catch (error) {

        console.error("Error login:", error);

        this.$swal.fire({

          icon: "error",

          title: "Error",

          text:
            "No se pudo validar el usuario."

        });

      }

    },

    // Exposed helper so parent can open this modal programmatically
    openModal() {
      this.promptPassword()
    },


    // =========================================================
    // AUTOCOMPLETE
    // =========================================================

    focusAutocomplete(ref) {

      if (
        this.$refs[ref] &&
        typeof this.$refs[ref].handleDoubleClick === "function"
      ) {

        this.$refs[ref].handleDoubleClick();

      }

    },


    // =========================================================
    // SELECCIONAR CUENTA
    // =========================================================

    setCuenta(obj) {

      console.log("CUENTA SELECCIONADA:", obj);

      this.Cuenta = obj;

      if (obj) {

        this.selectProducto.IdCuenta =
          obj.IdCuenta;

      }

    },


    // =========================================================
    // VALIDAR CUENTA
    // =========================================================

    validarCuenta() {

      if (
        !this.Cuenta ||
        !this.Cuenta.IdCuenta
      ) {

        this.$swal.fire({

          icon: "warning",

          title: "Seleccione una cuenta",

          text:
            "Debe seleccionar una cuenta antes de continuar."

        });

        return false;
      }

      return true;

    },


    // =========================================================
    // INSERTAR GASTO
    // =========================================================

    async insertarGasto() {

      const usuario =
        sessionStorage.getItem("User");

      const monto =
        parseFloat(this.selectProducto.Gasto);

      if (!this.validarCuenta()) {
        return;
      }

      if (!monto || monto <= 0) {

        this.$swal.fire({

          icon: "warning",

          title: "Monto inválido",

          text:
            "Ingrese un monto mayor a 0."

        });

        return;
      }

      const confirmacion =
        await this.$swal.fire({

          icon: "question",

          title: "Registrar gasto",

          text:
            "¿Desea insertar este gasto?",

          showCancelButton: true,

          confirmButtonText: "Sí, guardar",

          cancelButtonText: "Cancelar"

        });

      if (!confirmacion.isConfirmed) {
        return;
      }

      try {

        await this.axios.post(
          this.API + "cajaybanco/gasto",
          {

            producto:
              this.selectProducto,

            concepto:
              "Gasto - " +
              this.selectProducto.GastoDesc,

            IdPreOrden:
              this.selectProducto.IdPreOrden,

            IdHabitacion:
              this.selectProducto.IdProducto,

            IdCuenta:
              this.Cuenta.IdCuenta,

            Gasto:
              this.selectProducto.Gasto,

            Usuario:
              usuario

          }
        );

        if (
          this.$refs.inputCaja &&
          this.$refs.inputCaja.setValue
        ) {

          this.$refs.inputCaja.setValue("");

        }

        this.limpiarGasto();

        this.$swal.fire({

          icon: "success",

          title: "Gasto registrado",

          text:
            "El gasto se registró correctamente.",

          timer: 1500,

          showConfirmButton: false

        });

      } catch (error) {

        console.error(
          "Error insertar gasto:",
          error
        );

        this.$swal.fire({

          icon: "error",

          title: "Error",

          text:
            "No se pudo registrar el gasto."

        });

      }

    },


    // =========================================================
    // INSERTAR INGRESO
    // =========================================================

    async insertarIngreso() {

      const usuario =
        sessionStorage.getItem("User");

      const monto =
        parseFloat(this.selectProducto.Ingreso);

      if (!this.validarCuenta()) {
        return;
      }

      if (!monto || monto <= 0) {

        this.$swal.fire({

          icon: "warning",

          title: "Monto inválido",

          text:
            "Ingrese un monto mayor a 0."

        });

        return;
      }

      const confirmacion =
        await this.$swal.fire({

          icon: "question",

          title: "Registrar ingreso",

          text:
            "¿Desea insertar este ingreso?",

          showCancelButton: true,

          confirmButtonText: "Sí, guardar",

          cancelButtonText: "Cancelar"

        });

      if (!confirmacion.isConfirmed) {
        return;
      }

      try {

        await this.axios.post(
          this.API + "cajaybanco/ingreso",
          {

            producto:
              this.selectProducto,

            concepto:
              "Vale de Ingreso - " +
              this.selectProducto.IngresoDesc,

            IdPreOrden:
              this.selectProducto.IdPreOrden,

            IdHabitacion:
              this.selectProducto.IdProducto,

            IdCuenta:
              this.Cuenta.IdCuenta,

            Ingreso:
              this.selectProducto.Ingreso,

            Usuario:
              usuario

          }
        );

        if (
          this.$refs.inputCaja2 &&
          this.$refs.inputCaja2.setValue
        ) {

          this.$refs.inputCaja2.setValue("");

        }

        this.limpiarIngreso();

        this.$swal.fire({

          icon: "success",

          title: "Ingreso registrado",

          text:
            "El ingreso se registró correctamente.",

          timer: 1500,

          showConfirmButton: false

        });

      } catch (error) {

        console.error(
          "Error insertar ingreso:",
          error
        );

        this.$swal.fire({

          icon: "error",

          title: "Error",

          text:
            "No se pudo registrar el ingreso."

        });

      }

    },


    // =========================================================
    // INSERTAR SALIDA
    // =========================================================

    async insertarSalida() {

      const usuario =
        sessionStorage.getItem("User");

      const monto =
        parseFloat(this.selectProducto.Salida);

      if (!this.validarCuenta()) {
        return;
      }

      if (!monto || monto <= 0) {

        this.$swal.fire({

          icon: "warning",

          title: "Monto inválido",

          text:
            "Ingrese un monto mayor a 0."

        });

        return;
      }

      const confirmacion =
        await this.$swal.fire({

          icon: "question",

          title: "Registrar salida",

          text:
            "¿Desea insertar esta salida?",

          showCancelButton: true,

          confirmButtonText: "Sí, guardar",

          cancelButtonText: "Cancelar"

        });

      if (!confirmacion.isConfirmed) {
        return;
      }

      try {

        await this.axios.post(
          this.API + "cajaybanco/salida",
          {

            producto:
              this.selectProducto,

            concepto:
              "Vale de Egreso - " +
              this.selectProducto.SalidaDesc,

            IdPreOrden:
              this.selectProducto.IdPreOrden,

            IdHabitacion:
              this.selectProducto.IdProducto,

            IdCuenta:
              this.Cuenta.IdCuenta,

            Salida:
              this.selectProducto.Salida,

            Usuario:
              usuario

          }
        );

        if (
          this.$refs.inputCaja3 &&
          this.$refs.inputCaja3.setValue
        ) {

          this.$refs.inputCaja3.setValue("");

        }

        this.limpiarSalida();

        this.$swal.fire({

          icon: "success",

          title: "Salida registrada",

          text:
            "La salida se registró correctamente.",

          timer: 1500,

          showConfirmButton: false

        });

      } catch (error) {

        console.error(
          "Error insertar salida:",
          error
        );

        this.$swal.fire({

          icon: "error",

          title: "Error",

          text:
            "No se pudo registrar la salida."

        });

      }

    },


    // =========================================================
    // INSERTAR DEPÓSITO
    // =========================================================

    async insertarDeposito() {

      const usuario =
        sessionStorage.getItem("User");

      const monto =
        parseFloat(this.selectProducto.Deposito);

      if (!this.validarCuenta()) {
        return;
      }

      if (!monto || monto <= 0) {

        this.$swal.fire({

          icon: "warning",

          title: "Monto inválido",

          text:
            "Ingrese un monto mayor a 0."

        });

        return;
      }

      const confirmacion =
        await this.$swal.fire({

          icon: "question",

          title: "Registrar depósito",

          text:
            "¿Desea insertar este depósito?",

          showCancelButton: true,

          confirmButtonText: "Sí, guardar",

          cancelButtonText: "Cancelar"

        });

      if (!confirmacion.isConfirmed) {
        return;
      }

      try {

        await this.axios.post(
          this.API + "cajaybanco/deposito",
          {

            producto:
              this.selectProducto,

            concepto:
              "Depósito - " +
              this.selectProducto.DepositoDesc,

            IdPreOrden:
              this.selectProducto.IdPreOrden,

            IdCuenta:
              this.Cuenta.IdCuenta,

            IdHabitacion:
              this.selectProducto.IdProducto,

            Deposito:
              this.selectProducto.Deposito,

            Usuario:
              usuario

          }
        );

        if (
          this.$refs.inputCaja4 &&
          this.$refs.inputCaja4.setValue
        ) {

          this.$refs.inputCaja4.setValue("");

        }

        this.limpiarDeposito();

        this.$swal.fire({

          icon: "success",

          title: "Depósito registrado",

          text:
            "El depósito se registró correctamente.",

          timer: 1500,

          showConfirmButton: false

        });

      } catch (error) {

        console.error(
          "Error insertar depósito:",
          error
        );

        this.$swal.fire({

          icon: "error",

          title: "Error",

          text:
            "No se pudo registrar el depósito."

        });

      }

    },


    // =========================================================
    // LIMPIAR GASTO
    // =========================================================

    limpiarGasto() {

      this.selectProducto.Gasto = "";

      this.selectProducto.GastoDesc = "";

      this.selectProducto.IdCuenta = "";

      this.Cuenta = null;

    },


    // =========================================================
    // LIMPIAR INGRESO
    // =========================================================

    limpiarIngreso() {

      this.selectProducto.Ingreso = "";

      this.selectProducto.IngresoDesc = "";

      this.selectProducto.IdCuenta = "";

      this.Cuenta = null;

    },


    // =========================================================
    // LIMPIAR SALIDA
    // =========================================================

    limpiarSalida() {

      this.selectProducto.Salida = "";

      this.selectProducto.SalidaDesc = "";

      this.selectProducto.IdCuenta = "";

      this.Cuenta = null;

    },


    // =========================================================
    // LIMPIAR DEPÓSITO
    // =========================================================

    limpiarDeposito() {

      this.selectProducto.Deposito = "";

      this.selectProducto.DepositoDesc = "";

      this.selectProducto.IdCuenta = "";

      this.Cuenta = null;

    }

  },


  components: {

    PreOrdenFormDet,

    ClientesForm,

    Proformas

  }

};

</script>


<!-- ========================================================= -->
<!-- IMPORTANTE:
     NO USAR "scoped" AQUÍ.
     b-modal se renderiza fuera de .movjobi.
-->
<!-- ========================================================= -->

<style>

:root {

  --jobi-navy: #0B2A4A;
  --jobi-navy-2: #123A63;

  --jobi-teal: #0F9B8E;
  --jobi-blue: #2F6FED;
  --jobi-purple: #7C3AED;

  --jobi-green: #17A554;
  --jobi-orange: #F5A623;
  --jobi-pink: #F43F7E;

}


/* ========================================================= */
/* CONTENEDOR */
/* ========================================================= */

.movjobi {
  display: inline-block;
}


/* ========================================================= */
/* BOTÓN PRINCIPAL */
/* ========================================================= */

.movjobi-trigger {

  display: inline-flex !important;

  align-items: center;
  justify-content: center;

  gap: 8px;

  border-radius: 999px !important;

  background: #2F6FED !important;

  border: none !important;

  color: #FFFFFF !important;

  font-weight: 800 !important;

  text-transform: uppercase;

  letter-spacing: .03em;

  padding: 10px 20px !important;

  box-shadow: 0 8px 18px rgba(47,111,237,.30);

}

.movjobi-trigger * {

  color: #FFFFFF !important;

}

.movjobi-trigger-icon {

  display: inline-flex;

  align-items: center;

  justify-content: center;

}


/* ========================================================= */
/* MODAL */
/* ========================================================= */

.movjobi-dialog {
  max-width: 760px !important;
}

.movjobi-content {

  border: none !important;

  border-radius: 18px !important;

  overflow: hidden !important;

  box-shadow:
    0 20px 50px rgba(11,42,74,.35) !important;

}

.movjobi-body {

  padding: 0 22px 22px !important;

  background: #EEF3F8 !important;

  border-radius: 0 0 18px 18px;

}


/* ========================================================= */
/* HEADER */
/* ========================================================= */

.movjobi-header {

  display: flex;

  align-items: center;

  gap: 14px;

  width: calc(100% + 44px);

  box-sizing: border-box;

  padding: 18px 20px;

  margin: 0 -22px 18px;

  border-radius: 18px 18px 0 0;

  background:
    linear-gradient(
      135deg,
      #0B2A4A 0%,
      #123A63 100%
    ) !important;

  color: #FFFFFF !important;

}

.movjobi-header * {

  color: #FFFFFF !important;

}

.movjobi-header-icon {

  flex: 0 0 auto;

  width: 44px;
  height: 44px;

  border-radius: 12px;

  background:
    rgba(255,255,255,.14) !important;

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

  color: #FFFFFF !important;

}

.movjobi-header-sub {

  font-size: .78rem;

  color:
    rgba(255,255,255,.70) !important;

  margin-top: 2px;

}


/* ========================================================= */
/* BOTÓN CERRAR X */
/* ========================================================= */

.movjobi-close {

  flex: 0 0 auto;

  width: 32px;
  height: 32px;

  padding: 0 !important;

  border-radius: 50% !important;

  border: none !important;

  background:
    rgba(255,255,255,.14) !important;

  color: #FFFFFF !important;

  display: flex;

  align-items: center;
  justify-content: center;

  cursor: pointer;

}

.movjobi-close i {

  color: #FFFFFF !important;

}

.movjobi-close:hover {

  background:
    rgba(255,255,255,.28) !important;

}


/* ========================================================= */
/* TABS */
/* ========================================================= */

.movjobi-tabbar {

  display: flex;

  flex-wrap: wrap;

  gap: 10px;

  padding: 0 0 16px;

}

.movjobi-tab-pill {

  appearance: none !important;

  -webkit-appearance: none !important;

  display: inline-flex !important;

  align-items: center;
  justify-content: center;

  gap: 7px;

  padding: 9px 18px !important;

  border-radius: 999px !important;

  font-weight: 800 !important;

  font-size: .78rem;

  text-transform: uppercase;

  letter-spacing: .03em;

  color: #4D6A8A !important;

  background: #FFFFFF !important;

  border: 1.5px solid #DDE8F2 !important;

  box-shadow:
    0 3px 8px rgba(11,42,74,.06);

  cursor: pointer;

  line-height: 1;

}

.movjobi-tab-pill i {

  color: inherit !important;

}


/* TAB GASTO */

.movjobi-tab-orange.active {

  background: #F5A623 !important;

  color: #FFFFFF !important;

  border-color: #F5A623 !important;

  box-shadow:
    0 8px 16px rgba(245,166,35,.30);

}


/* TAB INGRESO */

.movjobi-tab-green.active {

  background: #17A554 !important;

  color: #FFFFFF !important;

  border-color: #17A554 !important;

  box-shadow:
    0 8px 16px rgba(23,165,84,.30);

}


/* TAB SALIDA */

.movjobi-tab-pink.active {

  background: #F43F7E !important;

  color: #FFFFFF !important;

  border-color: #F43F7E !important;

  box-shadow:
    0 8px 16px rgba(244,63,126,.30);

}


/* TAB DEPÓSITO */

.movjobi-tab-blue.active {

  background: #2F6FED !important;

  color: #FFFFFF !important;

  border-color: #2F6FED !important;

  box-shadow:
    0 8px 16px rgba(47,111,237,.30);

}


/* ========================================================= */
/* FORMULARIO */
/* ========================================================= */

.movjobi-form {

  margin-top: 2px;

}


/* ========================================================= */
/* CARD */
/* ========================================================= */

.movjobi-card {

  background: #FFFFFF !important;

  border-radius: 14px !important;

  box-shadow:
    0 6px 16px rgba(11,42,74,.08);

  padding: 16px 18px 10px;

  border-top: 4px solid transparent;

}

.movjobi-card-orange {
  border-top-color: #F5A623 !important;
}

.movjobi-card-green {
  border-top-color: #17A554 !important;
}

.movjobi-card-pink {
  border-top-color: #F43F7E !important;
}

.movjobi-card-blue {
  border-top-color: #2F6FED !important;
}


/* ========================================================= */
/* TÍTULO CARD */
/* ========================================================= */

.movjobi-card-title {

  display: flex;

  align-items: center;

  gap: 8px;

  font-weight: 800;

  font-size: .92rem;

  color: #0B2A4A !important;

  margin-bottom: 14px;

  text-transform: uppercase;

  letter-spacing: .02em;

}

.movjobi-card-title i {

  color: inherit !important;

}


/* ========================================================= */
/* LABELS */
/* ========================================================= */

.movjobi-label {

  display: flex;

  align-items: center;

  gap: 6px;

  font-size: .72rem;

  font-weight: 800;

  text-transform: uppercase;

  letter-spacing: .04em;

  color: #6B85A3 !important;

  margin-bottom: 4px;

  margin-top: 10px;

}

.movjobi-label-icon {

  font-size: .82em;

  color: #9FB3C8 !important;

}


/* ========================================================= */
/* INPUTS */
/* ========================================================= */

.movjobi-card input,
.movjobi-card select,
.movjobi-card .form-control {

  border-radius: 10px !important;

  border: 1.5px solid #DDE8F2 !important;

  min-height: 42px;

  font-weight: 600;

  color: #0B2A4A !important;

  background: #FFFFFF !important;

}

.movjobi-card input:focus,
.movjobi-card select:focus,
.movjobi-card .form-control:focus {

  border-color: #2F6FED !important;

  box-shadow:
    0 0 0 .15rem rgba(47,111,237,.12) !important;

}


/* ========================================================= */
/* INPUT MONTO */
/* ========================================================= */

.movjobi-input-monto {

  width: 100%;

}

.movjobi-input-monto .input-group-text {

  background: #0B2A4A !important;

  color: #FFFFFF !important;

  border: none !important;

  font-weight: 800 !important;

  border-radius:
    10px 0 0 10px !important;

}

.movjobi-input-monto input {

  border-radius:
    0 10px 10px 0 !important;

  text-align: right;

  font-weight: 800 !important;

}


/* ========================================================= */
/* ACCIONES */
/* ========================================================= */

.movjobi-actions {

  display: flex;

  justify-content: flex-end;

  gap: 10px;

  padding: 16px 4px 6px;

}


/* ========================================================= */
/* BOTÓN GUARDAR */
/* ========================================================= */

.movjobi-btn-save {

  display: inline-flex !important;

  align-items: center;
  justify-content: center;

  gap: 7px;

  border: none !important;

  border-radius: 999px !important;

  padding: 10px 22px !important;

  font-weight: 800 !important;

  text-transform: uppercase;

  letter-spacing: .03em;

  font-size: .82rem;

  color: #FFFFFF !important;

  opacity: 1 !important;

  box-shadow: none;

}

.movjobi-btn-save i {

  color: #FFFFFF !important;

}


/* GASTO */

.movjobi-btn-orange {

  background: #F5A623 !important;

  color: #FFFFFF !important;

  box-shadow:
    0 8px 18px rgba(245,166,35,.30) !important;

}


/* INGRESO */

.movjobi-btn-green {

  background: #17A554 !important;

  color: #FFFFFF !important;

  box-shadow:
    0 8px 18px rgba(23,165,84,.30) !important;

}


/* SALIDA */

.movjobi-btn-pink {

  background: #F43F7E !important;

  color: #FFFFFF !important;

  box-shadow:
    0 8px 18px rgba(244,63,126,.30) !important;

}


/* DEPÓSITO */

.movjobi-btn-blue {

  background: #2F6FED !important;

  color: #FFFFFF !important;

  box-shadow:
    0 8px 18px rgba(47,111,237,.30) !important;

}

.movjobi-btn-save:hover {

  transform: translateY(-1px);

  filter: brightness(.96);

}


/* ========================================================= */
/* BOTÓN CERRAR */
/* ========================================================= */

.movjobi-btn-cancel {

  display: inline-flex !important;

  align-items: center;
  justify-content: center;

  gap: 7px;

  background: #EDF2F7 !important;

  color: #526B86 !important;

  border: none !important;

  border-radius: 999px !important;

  padding: 10px 20px !important;

  font-weight: 800 !important;

  text-transform: uppercase;

  letter-spacing: .03em;

  font-size: .82rem;

  opacity: 1 !important;

}

.movjobi-btn-cancel i {

  color: #526B86 !important;

}

.movjobi-btn-cancel:hover {

  background: #DFE8F2 !important;

  color: #526B86 !important;

}


/* ========================================================= */
/* MODAL SCROLL */
/* ========================================================= */

.movjobi-content .modal-body {

  overflow-y: auto;

}


/* ========================================================= */
/* RESPONSIVE */
/* ========================================================= */

@media (max-width: 576px) {

  .movjobi-body {

    padding: 0 14px 16px !important;

  }

  .movjobi-header {

    width: calc(100% + 28px);

    margin-left: -14px;

    margin-right: -14px;

    padding: 15px;

  }

  .movjobi-tabbar {

    gap: 7px;

  }

  .movjobi-tab-pill {

    flex: 1 1 calc(50% - 7px);

    padding:
      9px 10px !important;

    font-size: .70rem;

  }

  .movjobi-actions {

    flex-direction: column-reverse;

  }

  .movjobi-btn-save,
  .movjobi-btn-cancel {

    width: 100%;

  }

}


/* ========================================================= */
/* PRINT */
/* ========================================================= */

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


/* ========================================================= */
/* REGLAS ORIGINALES */
/* ========================================================= */

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