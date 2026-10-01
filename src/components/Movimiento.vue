<template>
    <div>
      <b-button-group>
        <b-button @click="promptPassword" variant="primary" class="btn-pos btn-accion"><i class="fas fa-shopping-cart mr-2"></i>Caja/Banco</b-button>
      </b-button-group>
      <b-modal v-model="showMovimientoModal" size="lg" title="Caja y banco" ref="modalCarrito"
        hide-footer centered modal-class="cash-modal">
        <b-tabs content-class="mt-3">
          <b-tab title="Compra de chatarra" active>
            <b-form class="scrap-purchase-form" @submit.prevent="insertarCompraChatarra">
              <div class="scrap-purchase-intro">
                <span class="scrap-purchase-intro__icon"><i class="fas fa-recycle"></i></span>
                <div>
                  <strong>Ingreso de chatarra con salida de caja</strong>
                  <small>Al guardar, el stock aumenta y el mismo total se registra como egreso.</small>
                </div>
              </div>

              <div class="scrap-purchase-grid">
                <b-form-group label="Almacén:" label-for="scrap-warehouse">
                  <b-form-select id="scrap-warehouse" v-model="compraChatarra.IdAlmacen" required>
                    <option disabled value="">Seleccione el almacén</option>
                    <option v-for="almacen in almacenesChatarra" :key="almacen.IdAlmacen" :value="almacen.IdAlmacen">
                      {{ almacen.Almacen }}
                    </option>
                  </b-form-select>
                </b-form-group>

                <b-form-group label="Cuenta de salida:" label-for="scrap-account">
                  <b-form-select id="scrap-account" v-model="compraChatarra.IdCuenta" required>
                    <option disabled value="">Seleccione la cuenta</option>
                    <option v-for="cuenta in cuentasChatarra" :key="cuenta.IdCuenta" :value="cuenta.IdCuenta">
                      {{ cuenta.Cuenta }}
                    </option>
                  </b-form-select>
                </b-form-group>

                <b-form-group class="scrap-product-field" label="Producto marcado como chatarra:" label-for="scrap-product">
                  <b-form-select id="scrap-product" v-model="compraChatarra.IdProducto" required @change="seleccionarProductoChatarra($event)">
                    <option disabled value="">Seleccione el producto</option>
                    <option v-for="producto in productosChatarra" :key="producto.IdProducto" :value="producto.IdProducto">
                      {{ etiquetaProductoChatarra(producto) }}
                    </option>
                  </b-form-select>
                  <small v-if="!cargandoChatarra && !productosChatarra.length" class="scrap-empty-help">
                    No hay productos marcados como “Es chatarra”. Actívelo desde la ficha del producto.
                  </small>
                </b-form-group>

                <b-form-group label="Cantidad (entera):" label-for="scrap-quantity">
                  <b-form-input id="scrap-quantity" v-model.number="compraChatarra.Cantidad" required type="number" min="1" step="1" inputmode="numeric" @input="actualizarCantidadChatarra"></b-form-input>
                </b-form-group>

                <b-form-group label="Costo unitario:" label-for="scrap-cost">
                  <b-input-group prepend="S/">
                    <b-form-input id="scrap-cost" v-model.number="compraChatarra.Costo" required type="number" min="0.01" step="0.01"></b-form-input>
                  </b-input-group>
                </b-form-group>

                <b-form-group label="Total de costo:" label-for="scrap-total">
                  <b-input-group prepend="S/">
                    <b-form-input id="scrap-total" :value="totalCompraChatarra.toFixed(2)" readonly></b-form-input>
                  </b-input-group>
                </b-form-group>

                <b-form-group class="scrap-description-field" label="Descripción / comprobante:" label-for="scrap-description">
                  <b-form-input id="scrap-description" v-model.trim="compraChatarra.Descripcion" required maxlength="180" placeholder="Ej.: compra de batería usada, recibo o referencia"></b-form-input>
                </b-form-group>
              </div>

              <div class="scrap-purchase-summary">
                <span>Total que saldrá de caja</span>
                <strong>S/ {{ totalCompraChatarra.toFixed(2) }}</strong>
              </div>

              <b-btn variant="success" type="submit" :disabled="guardandoChatarra || cargandoChatarra || !productosChatarra.length">
                <i class="fas fa-save mr-2"></i>{{ guardandoChatarra ? "Guardando..." : "Guardar compra" }}
              </b-btn>
              <b-btn variant="secondary" type="button" @click="showMovimientoModal=false">Cerrar</b-btn>
            </b-form>
          </b-tab>

          <b-tab title="Gasto">
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
              <b-btn variant="secondary" @click="showMovimientoModal=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
        
        <b-tab title="Ingreso">
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
              <b-btn variant="secondary" @click="showMovimientoModal=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
        <b-tab title="Salida">
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
              <b-btn variant="secondary" @click="showMovimientoModal=false">Cerrar</b-btn>
            </template>
          </b-form>
        </b-tab>
        <b-tab title="Dep. Cuenta Bancaria">
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
              <b-btn variant="secondary" @click="showMovimientoModal=false">Cerrar</b-btn>
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
      cargandoChatarra: false,
      guardandoChatarra: false,
      almacenesChatarra: [],
      productosChatarra: [],
      cuentasChatarra: [],
      compraChatarra: {
        IdAlmacen: "",
        IdCuenta: "",
        IdProducto: "",
        Cantidad: 1,
        Costo: "",
        Descripcion: ""
      },
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
    }),
    totalCompraChatarra() {
      const cantidad = Math.trunc(Number(this.compraChatarra.Cantidad));
      const costo = Number(this.compraChatarra.Costo);
      if (!Number.isFinite(cantidad) || !Number.isFinite(costo) || cantidad <= 0 || costo < 0) return 0;
      return Math.round(cantidad * costo * 100) / 100;
    }
  },
  methods: {
    async promptPassword() {
      this.showMovimientoModal = true;
      this.cargarDatosCompraChatarra();
      return;

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
    etiquetaProductoChatarra(producto) {
      const codigo = String(producto.CodigoBarra || producto.Codigo || "").trim();
      const nombre = String(producto.Producto || "").trim();
      const modelo = String(producto.ProductoModelo || "").trim();
      return [codigo, nombre, modelo].filter(Boolean).join(" · ");
    },
    actualizarCantidadChatarra(valor) {
      if (valor === "" || valor === null || valor === undefined) {
        this.compraChatarra.Cantidad = "";
        return;
      }
      const cantidad = Number(valor);
      this.compraChatarra.Cantidad = Number.isFinite(cantidad)
        ? Math.max(1, Math.trunc(cantidad))
        : "";
    },
    async cargarDatosCompraChatarra() {
      if (this.cargandoChatarra) return;
      this.cargandoChatarra = true;
      try {
        const respuestas = await Promise.all([
          this.axios.get(this.API + "almacenes"),
          this.axios.get(this.API + "productos/chatarra"),
          this.axios.get(this.API + "cuentas")
        ]);
        this.almacenesChatarra = respuestas[0].data || [];
        this.productosChatarra = respuestas[1].data || [];
        this.cuentasChatarra = respuestas[2].data || [];

        if (!this.compraChatarra.IdAlmacen && this.almacenesChatarra.length) {
          const principal = this.almacenesChatarra.find(almacen => Number(almacen.EsPrincipal) === 1);
          this.compraChatarra.IdAlmacen = (principal || this.almacenesChatarra[0]).IdAlmacen;
        }
        if (!this.compraChatarra.IdCuenta && this.cuentasChatarra.length) {
          this.compraChatarra.IdCuenta = this.cuentasChatarra[0].IdCuenta;
        }
        if (this.compraChatarra.IdProducto) {
          this.seleccionarProductoChatarra(this.compraChatarra.IdProducto);
        }
      } catch (error) {
        const respuesta = error.response && error.response.data;
        this.$toasted.error(
          (respuesta && (respuesta.error || respuesta.message)) ||
          "No se pudieron cargar los datos para la compra de chatarra."
        );
      } finally {
        this.cargandoChatarra = false;
      }
    },
    obtenerCostoProductoChatarra(producto) {
      if (!producto) return "";
      const valores = [producto.PrecioCosto, producto.PrecioCompra, producto.Costo];
      const valor = valores.find(item => item !== null && item !== undefined && item !== "" && Number.isFinite(Number(item)));
      return valor === undefined ? "" : Math.max(0, Number(valor));
    },
    seleccionarProductoChatarra(idProducto) {
      const seleccionado = idProducto !== undefined && idProducto !== null
        ? idProducto
        : this.compraChatarra.IdProducto;
      const producto = this.productosChatarra.find(
        item => String(item.IdProducto) === String(seleccionado)
      );
      const costo = this.obtenerCostoProductoChatarra(producto);
      this.compraChatarra.Costo = costo === "" ? "" : Number(costo.toFixed(2));
    },
    async insertarCompraChatarra() {
      if (this.guardandoChatarra) return;
      const cantidadOriginal = Number(this.compraChatarra.Cantidad);
      const cantidad = Math.trunc(cantidadOriginal);
      const costo = Number(this.compraChatarra.Costo);
      if (!this.compraChatarra.IdAlmacen || !this.compraChatarra.IdCuenta || !this.compraChatarra.IdProducto) {
        this.$toasted.error("Seleccione almacén, cuenta y producto.");
        return;
      }
      if (!Number.isInteger(cantidadOriginal) || !(cantidad > 0) || !(costo > 0) || !(this.totalCompraChatarra > 0)) {
        this.$toasted.error("Ingrese una cantidad entera y un costo mayores a cero.");
        return;
      }
      if (!String(this.compraChatarra.Descripcion || "").trim()) {
        this.$toasted.error("Ingrese la descripción o referencia de la compra.");
        return;
      }

      const confirmacion = await this.$swal.fire({
        icon: "question",
        title: "Registrar compra de chatarra",
        text: `Se aumentará el stock y saldrán S/ ${this.totalCompraChatarra.toFixed(2)} de caja.`,
        showCancelButton: true,
        confirmButtonText: "Sí, guardar",
        cancelButtonText: "Cancelar"
      });
      if (!confirmacion.isConfirmed) return;

      this.guardandoChatarra = true;
      try {
        const response = await this.axios.post(this.API + "cajaybanco/compra-chatarra", {
          ...this.compraChatarra,
          Cantidad: cantidad,
          Costo: costo,
          TotalCosto: this.totalCompraChatarra,
          Usuario: sessionStorage.getItem("User") || ""
        });
        const resultado = response.data || {};
        await this.$swal.fire({
          icon: "success",
          title: "Compra registrada",
          text: `Stock actualizado: ${resultado.StockActual}. Salida de caja: S/ ${Number(resultado.TotalCosto || this.totalCompraChatarra).toFixed(2)}.`
        });
        this.compraChatarra.IdProducto = "";
        this.compraChatarra.Cantidad = 1;
        this.compraChatarra.Costo = "";
        this.compraChatarra.Descripcion = "";
        this.$emit("stock-actualizado", resultado);
      } catch (error) {
        const respuesta = error.response && error.response.data;
        this.$swal.fire({
          icon: "error",
          title: "No se guardó la compra",
          text: (respuesta && (respuesta.error || respuesta.message)) || "No se pudo actualizar caja y stock. No se realizó ningún movimiento."
        });
      } finally {
        this.guardandoChatarra = false;
      }
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

/deep/ .cash-modal .modal-dialog {
  max-width: 980px;
}

/deep/ .cash-modal .modal-content {
  overflow: hidden;
  border: 1px solid rgba(56, 189, 248, .35);
  border-radius: 24px;
  background: linear-gradient(145deg, #ffffff 0%, #f5f9ff 100%);
  box-shadow: 0 28px 70px rgba(2, 26, 65, .35), 0 0 0 5px rgba(255, 255, 255, .25);
}

/deep/ .cash-modal .modal-header {
  min-height: 82px;
  align-items: center;
  padding: 18px 26px;
  border: 0;
  color: #fff;
  background: linear-gradient(120deg, #071e48 0%, #084d8c 62%, #0798c7 100%);
}

/deep/ .cash-modal .modal-title {
  display: flex;
  align-items: center;
  gap: 12px;
  color: #fff;
  font-size: 1.35rem;
  font-weight: 900;
  letter-spacing: .02em;
}

/deep/ .cash-modal .modal-title::before {
  display: inline-flex;
  width: 44px;
  height: 44px;
  align-items: center;
  justify-content: center;
  border: 1px solid rgba(255, 255, 255, .35);
  border-radius: 14px;
  background: rgba(255, 255, 255, .15);
  content: "\f555";
  font-family: "Font Awesome 5 Free";
  font-weight: 900;
  box-shadow: inset 0 1px rgba(255, 255, 255, .25);
}

/deep/ .cash-modal .close {
  width: 40px;
  height: 40px;
  margin: 0;
  padding: 0;
  border-radius: 12px;
  color: #fff;
  background: rgba(255, 255, 255, .13);
  opacity: 1;
  text-shadow: none;
  transition: .2s ease;
}

/deep/ .cash-modal .close:hover {
  background: #ef4444;
  transform: rotate(8deg);
}

/deep/ .cash-modal .modal-body {
  padding: 24px 26px 26px;
}

/deep/ .cash-modal .nav-tabs {
  display: grid;
  grid-template-columns: repeat(5, minmax(0, 1fr));
  gap: 9px;
  padding: 6px;
  border: 1px solid #d7e5f4;
  border-radius: 16px;
  background: #edf4fb;
}

/deep/ .cash-modal .nav-tabs .nav-link {
  min-height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0;
  padding: 9px 12px;
  border: 0;
  border-radius: 11px;
  color: #425773;
  background: transparent;
  font-size: .93rem;
  font-weight: 800;
  text-align: center;
  transition: .22s ease;
}

/deep/ .cash-modal .nav-tabs .nav-link:hover {
  color: #075a9f;
  background: rgba(255, 255, 255, .78);
  transform: translateY(-1px);
}

/deep/ .cash-modal .nav-tabs .nav-link.active {
  color: #fff;
  background: linear-gradient(135deg, #0b3c78, #087dc2);
  box-shadow: 0 8px 18px rgba(8, 85, 154, .25);
}

/deep/ .cash-modal .tab-content {
  margin-top: 18px !important;
  padding: 22px;
  border: 1px solid #dce8f5;
  border-radius: 18px;
  background: rgba(255, 255, 255, .92);
  box-shadow: 0 12px 30px rgba(15, 54, 93, .08);
}

.scrap-purchase-intro {
  display: flex;
  align-items: center;
  gap: 13px;
  margin-bottom: 20px;
  padding: 14px 16px;
  border: 1px solid #b9ded5;
  border-radius: 14px;
  color: #083f36;
  background: #eaf7f3;
}

.scrap-purchase-intro__icon {
  display: inline-flex;
  width: 46px;
  height: 46px;
  flex: 0 0 46px;
  align-items: center;
  justify-content: center;
  border-radius: 13px;
  color: #fff;
  background: #075e54;
  font-size: 1.15rem;
}

.scrap-purchase-intro strong,
.scrap-purchase-intro small {
  display: block;
}

.scrap-purchase-intro strong {
  font-size: 1rem;
  font-weight: 900;
}

.scrap-purchase-intro small {
  margin-top: 3px;
  color: #47726b;
  font-weight: 700;
}

.scrap-purchase-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px 18px;
}

.scrap-purchase-grid .form-group {
  margin: 0;
}

.scrap-purchase-grid /deep/ label {
  display: block;
  margin-bottom: 7px !important;
}

.scrap-product-field,
.scrap-description-field {
  grid-column: 1 / -1;
}

.scrap-empty-help {
  display: block;
  margin-top: 7px;
  color: #b45309;
  font-weight: 800;
}

.scrap-purchase-summary {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 18px;
  padding: 14px 18px;
  border-radius: 14px;
  color: #fff;
  background: #06483e;
  font-weight: 800;
}

.scrap-purchase-summary strong {
  color: #fff;
  font-size: 1.35rem;
  font-weight: 950;
}

/deep/ .cash-modal .tab-pane .my-3 {
  margin: 0 !important;
}

/deep/ .cash-modal .tab-pane .col-md-4 {
  flex: 0 0 100%;
  max-width: 100%;
  display: grid;
  grid-template-columns: 150px minmax(0, 1fr);
  gap: 10px 14px;
  align-items: center;
}

/deep/ .cash-modal .tab-pane label {
  margin: 0;
  color: #334762;
  font-size: .88rem;
  font-weight: 800;
}

/deep/ .cash-modal .tab-pane br {
  display: none;
}

/deep/ .cash-modal .tab-pane .form-group {
  margin: 0;
}

/deep/ .cash-modal .tab-pane .form-control {
  min-height: 48px;
  border: 1px solid #cbdcec;
  border-radius: 12px;
  background: #f9fbfe;
  color: #12233f;
  font-weight: 700;
  box-shadow: inset 0 1px 2px rgba(14, 47, 82, .03);
  transition: .2s ease;
}

/deep/ .cash-modal .tab-pane .form-control:focus {
  border-color: #1d9fe0;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(29, 159, 224, .13);
}

/deep/ .cash-modal .autocomplete,
/deep/ .cash-modal .autocomplete-wrapper {
  position: relative;
  width: 100% !important;
  min-width: 0 !important;
}

/deep/ .cash-modal .autocomplete ul,
/deep/ .cash-modal .autocomplete-results {
  position: absolute !important;
  z-index: 10600 !important;
  top: calc(100% + 8px) !important;
  right: 0 !important;
  left: 0 !important;
  width: 100% !important;
  min-width: 100% !important;
  max-height: 230px !important;
  margin: 0 !important;
  padding: 8px !important;
  overflow-x: hidden;
  overflow-y: auto;
  border: 1px solid rgba(31, 136, 203, .28) !important;
  border-radius: 14px !important;
  background: rgba(255, 255, 255, .98) !important;
  box-shadow: 0 18px 38px rgba(8, 39, 78, .22), 0 0 0 4px rgba(56, 189, 248, .08) !important;
  list-style: none !important;
  transform-origin: top center;
  animation: cashDropdownOpen .18s ease-out;
  backdrop-filter: blur(16px);
}

/deep/ .cash-modal .autocomplete ul::before,
/deep/ .cash-modal .autocomplete-results::before {
  position: static !important;
  display: block;
  width: auto !important;
  height: auto !important;
  padding: 5px 10px 9px;
  border: 0 !important;
  color: #7690ac;
  content: "CUENTAS DISPONIBLES";
  font-size: .68rem;
  font-weight: 900;
  letter-spacing: .11em;
}

/deep/ .cash-modal .autocomplete ul li {
  margin: 3px 0;
  padding: 0 !important;
  border: 0 !important;
  list-style: none !important;
}

/deep/ .cash-modal .autocomplete ul li > a,
/deep/ .cash-modal .autocomplete-results > div,
/deep/ .cash-modal .autocomplete .trDetails {
  position: relative;
  display: flex !important;
  width: 100% !important;
  min-height: 46px;
  align-items: center;
  gap: 11px;
  padding: 10px 14px 10px 45px !important;
  border: 1px solid transparent !important;
  border-radius: 10px !important;
  color: #1d334f !important;
  background: transparent !important;
  font-size: .9rem !important;
  font-weight: 800 !important;
  line-height: 1.25;
  text-align: left;
  text-decoration: none !important;
  transition: .18s ease;
}

/deep/ .cash-modal .autocomplete-anchor-text {
  display: block;
  color: inherit;
  font-size: .91rem;
  font-weight: 900;
  letter-spacing: .01em;
}

/deep/ .cash-modal .autocomplete-anchor-label {
  display: block;
  margin-top: 2px !important;
  color: #7690ac !important;
  font-size: .72rem !important;
  font-weight: 700;
}

/deep/ .cash-modal .autocomplete ul li > a::before,
/deep/ .cash-modal .autocomplete-results > div::before,
/deep/ .cash-modal .autocomplete .trDetails::before {
  position: absolute;
  left: 11px;
  display: inline-flex;
  width: 26px;
  height: 26px;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  color: #087bbd;
  background: #e4f5ff;
  content: "\f19c";
  font-family: "Font Awesome 5 Free";
  font-size: .78rem;
  font-weight: 900;
}

/deep/ .cash-modal .autocomplete ul li > a:hover,
/deep/ .cash-modal .autocomplete ul li.focus-list > a,
/deep/ .cash-modal .autocomplete-results > div:hover,
/deep/ .cash-modal .autocomplete .trDetails:hover {
  border-color: rgba(56, 189, 248, .28) !important;
  color: #fff !important;
  background: linear-gradient(135deg, #0b4e91, #0798c7) !important;
  box-shadow: 0 7px 15px rgba(7, 92, 155, .20);
  transform: translateX(3px);
}

/deep/ .cash-modal .autocomplete ul li > a:hover::before,
/deep/ .cash-modal .autocomplete ul li.focus-list > a::before,
/deep/ .cash-modal .autocomplete-results > div:hover::before,
/deep/ .cash-modal .autocomplete .trDetails:hover::before {
  color: #fff;
  background: rgba(255, 255, 255, .17);
}

/deep/ .cash-modal .autocomplete ul li > a:hover .autocomplete-anchor-label,
/deep/ .cash-modal .autocomplete ul li.focus-list > a .autocomplete-anchor-label {
  color: rgba(255, 255, 255, .78) !important;
}

/deep/ .cash-modal .autocomplete ul::-webkit-scrollbar {
  width: 7px;
}

/deep/ .cash-modal .autocomplete ul::-webkit-scrollbar-track {
  border-radius: 10px;
  background: #edf5fb;
}

/deep/ .cash-modal .autocomplete ul::-webkit-scrollbar-thumb {
  border-radius: 10px;
  background: linear-gradient(#1478bd, #16b6d4);
}

@keyframes cashDropdownOpen {
  from { opacity: 0; transform: translateY(-6px) scale(.985); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

/deep/ .cash-modal .tab-pane form > .btn {
  width: auto !important;
  min-width: 150px;
  min-height: 48px;
  margin-top: 20px;
  padding: 10px 22px;
  border: 0;
  border-radius: 13px;
  font-size: .92rem;
  font-weight: 900;
  letter-spacing: .02em;
  box-shadow: 0 8px 18px rgba(15, 61, 105, .18);
  transition: .2s ease;
}

/deep/ .cash-modal .tab-pane form > .btn-success {
  float: right;
  margin-left: 10px;
  background: linear-gradient(135deg, #08a66c, #20c875);
}

/deep/ .cash-modal .tab-pane form > .btn-secondary {
  float: right;
  color: #34465e;
  background: #e7eef6;
}

/deep/ .cash-modal .tab-pane form > .btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 12px 24px rgba(15, 61, 105, .24);
}

/deep/ .cash-modal .tab-pane form::after {
  display: block;
  clear: both;
  content: "";
}

@media (max-width: 767px) {
  /deep/ .cash-modal .modal-dialog {
    margin: 10px;
  }
  /deep/ .cash-modal .nav-tabs {
    grid-template-columns: repeat(2, 1fr);
  }
  .scrap-purchase-grid {
    grid-template-columns: 1fr;
  }
  .scrap-product-field,
  .scrap-description-field {
    grid-column: auto;
  }
  /deep/ .cash-modal .tab-pane .col-md-4 {
    grid-template-columns: 1fr;
    gap: 6px;
  }
  /deep/ .cash-modal .tab-pane form > .btn {
    width: calc(50% - 6px) !important;
    min-width: 0;
  }
}
</style>
