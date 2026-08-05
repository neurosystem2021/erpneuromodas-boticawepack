<template>
  <div class="supplier-create">
    <b-btn v-b-modal.modal1 class="supplier-create__trigger">
      <span class="supplier-create__trigger-icon">
        <i class="fa fa-user-plus"></i>
      </span>
      <span>Nuevo proveedor</span>
      <i class="fa fa-arrow-right supplier-create__trigger-arrow"></i>
    </b-btn>

    <b-modal
      id="modal1"
      ref="proveedorForm"
      v-model="show"
      size="lg"
      centered
      hide-header
      hide-footer
      modal-class="supplier-modal"
      content-class="supplier-modal__content"
      body-class="supplier-modal__body"
      @shown="shownProveedorForm"
    >
      <header class="supplier-modal__hero">
        <div class="supplier-modal__hero-icon">
          <i class="fa fa-truck"></i>
        </div>
        <div>
          <span class="supplier-modal__eyebrow">GESTIÓN DE PROVEEDORES</span>
          <h3>Nuevo proveedor</h3>
          <p>Registra sus datos comerciales de forma rápida y segura.</p>
        </div>
        <button
          type="button"
          class="supplier-modal__close"
          aria-label="Cerrar"
          @click="show = false"
        >
          <i class="fa fa-times"></i>
        </button>
      </header>

      <div class="supplier-modal__form">
        <b-row>
          <b-col md="12">
            <b-form-group class="supplier-modal__field">
              <label>
                <i class="fa fa-id-card-o"></i>
                RUC
              </label>
              <b-input-group class="supplier-modal__search">
                <b-form-input
                  v-model="proveedor.Ruc"
                  inputmode="numeric"
                  placeholder="Ingresa los 11 dígitos del RUC"
                ></b-form-input>
                <b-input-group-append>
                  <b-btn class="supplier-modal__search-button" @click="consultarRUC">
                    <i class="fa fa-search"></i>
                    Consultar RUC
                  </b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>

          <b-col md="12">
            <b-form-group class="supplier-modal__field">
              <label>
                <i class="fa fa-building-o"></i>
                Razón social
              </label>
              <b-form-input
                v-model="proveedor.Proveedor"
                type="text"
                placeholder="Nombre o razón social del proveedor"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="8">
            <b-form-group class="supplier-modal__field">
              <label>
                <i class="fa fa-map-marker"></i>
                Dirección
              </label>
              <b-form-input
                v-model="proveedor.Direccion"
                type="text"
                placeholder="Dirección fiscal o comercial"
              ></b-form-input>
            </b-form-group>
          </b-col>

          <b-col md="4">
            <b-form-group class="supplier-modal__field">
              <label>
                <i class="fa fa-phone"></i>
                Teléfono
              </label>
              <b-form-input
                v-model="proveedor.Telefono"
                type="text"
                inputmode="tel"
                placeholder="Número de contacto"
              ></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>

        <div class="supplier-modal__notice">
          <i class="fa fa-shield"></i>
          <span>Verifica el RUC antes de guardar para evitar registros duplicados.</span>
        </div>
      </div>

      <footer class="supplier-modal__footer">
        <b-btn class="supplier-modal__cancel" @click="show = false">
          Cancelar
        </b-btn>
        <b-btn class="supplier-modal__save" @click="guardarProveedor">
          <i class="fa fa-save"></i>
          Guardar proveedor
        </b-btn>
      </footer>
    </b-modal>
  </div>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      show: false,
      proveedor: {
        Proveedor: "",
        Ruc: "",
        Direccion: "",
        Telefono: "",
        Observacion: "",
        FechaReg: null
      }
    };
  },
  props: ["newRuc"],
  methods: {
    shownProveedorForm() {
      this.proveedor.Ruc = this.$parent.$parent.newRuc;
      this.proveedor.Direccion = "";
      this.proveedor.Proveedor = "";
      this.proveedor.Telefono = "";
    },
    consultarRUC() {
      this.axios
        .get(this.API + "consultarRUC", {
          params: {
            type: "RUC",
            numero: this.proveedor.Ruc
          }
        })
        .then(response => {
          this.$toasted.show("Proveedor encontrado, se llenarán sus datos");
          var data = response.data;
          this.proveedor.Proveedor = data.razonSocial;
          this.proveedor.Direccion = data.direccion;
        })
        .catch(error => {
          this.$toasted.show("No se encontró el RUC");
          console.log(error);
        });
    },
    guardarProveedor() {
      this.axios
        .post(this.API + "proveedores", this.proveedor)
        .then(response => {
          this.$toasted.success("Proveedor guardado correctamente");
          this.$refs.proveedorForm.hide();

          // Soporta varias formas en que la API puede devolver el ID
          const idProveedor = response.data.IdProveedor
            || response.data.id
            || response.data;

          const proveedorGuardado = {
            IdProveedor: idProveedor,
            Proveedor: this.proveedor.Proveedor,
            Ruc: this.proveedor.Ruc,
            ProveedorRuc: this.proveedor.Ruc + ' - ' + this.proveedor.Proveedor
          };

          this.$parent.$parent.setProveedor(proveedorGuardado);
          this.$parent.$parent.$refs.inputProveedor.setValue(
            this.proveedor.Ruc + ' - ' + this.proveedor.Proveedor
          );
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    }
  }
}
</script>

<style>
.supplier-create {
  height: 100%;
}

.supplier-create__trigger {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-width: 176px;
  height: 56px;
  padding: 0 18px;
  overflow: hidden;
  color: #fff !important;
  font-size: 16px;
  font-weight: 800;
  border: 0 !important;
  border-radius: 0 16px 16px 0 !important;
  background: linear-gradient(135deg, #0c4d92 0%, #0879bf 58%, #09a8cf 100%) !important;
  box-shadow: 0 10px 24px rgba(4, 71, 137, .24) !important;
  transition: transform .25s ease, box-shadow .25s ease;
}

.supplier-create__trigger::after {
  position: absolute;
  top: -80%;
  left: -45%;
  width: 40%;
  height: 260%;
  content: "";
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, .42), transparent);
  transform: rotate(24deg);
  transition: left .55s ease;
}

.supplier-create__trigger:hover {
  transform: translateY(-2px);
  box-shadow: 0 14px 28px rgba(4, 71, 137, .32) !important;
}

.supplier-create__trigger:hover::after {
  left: 120%;
}

.supplier-create__trigger-icon {
  display: grid;
  width: 32px;
  height: 32px;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .28);
  border-radius: 10px;
  background: rgba(255, 255, 255, .14);
}

.supplier-create__trigger-arrow {
  font-size: 12px;
  opacity: .8;
}

.supplier-modal .modal-dialog {
  max-width: 860px;
}

.supplier-modal__content {
  overflow: hidden;
  border: 1px solid rgba(101, 180, 232, .55) !important;
  border-radius: 24px !important;
  background: #f7fbff !important;
  box-shadow: 0 30px 80px rgba(2, 28, 64, .35) !important;
}

.supplier-modal__body {
  padding: 0 !important;
}

.supplier-modal__hero {
  position: relative;
  display: flex;
  align-items: center;
  gap: 18px;
  min-height: 132px;
  padding: 26px 30px;
  overflow: hidden;
  color: #fff;
  background: linear-gradient(125deg, #071f49 0%, #074c87 52%, #08a5c8 100%);
}

.supplier-modal__hero::after {
  position: absolute;
  right: -65px;
  bottom: -105px;
  width: 240px;
  height: 240px;
  content: "";
  border: 1px solid rgba(255, 255, 255, .18);
  border-radius: 50%;
  box-shadow: 0 0 0 38px rgba(255, 255, 255, .05);
}

.supplier-modal__hero-icon {
  z-index: 1;
  display: grid;
  flex: 0 0 62px;
  width: 62px;
  height: 62px;
  font-size: 25px;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .24);
  border-radius: 18px;
  background: rgba(255, 255, 255, .13);
  box-shadow: inset 0 1px 0 rgba(255, 255, 255, .18);
}

.supplier-modal__eyebrow {
  display: block;
  margin-bottom: 3px;
  color: #68e4ff;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 1.5px;
}

.supplier-modal__hero h3 {
  margin: 0;
  color: #fff;
  font-size: 25px;
  font-weight: 900;
}

.supplier-modal__hero p {
  margin: 4px 0 0;
  color: rgba(255, 255, 255, .76);
  font-size: 14px;
}

.supplier-modal__close {
  z-index: 2;
  display: grid;
  width: 42px;
  height: 42px;
  margin-left: auto;
  color: #fff;
  cursor: pointer;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, .22);
  border-radius: 13px;
  background: rgba(255, 255, 255, .12);
  transition: background .2s ease, transform .2s ease;
}

.supplier-modal__close:hover {
  background: rgba(255, 255, 255, .24);
  transform: rotate(5deg);
}

.supplier-modal__form {
  margin: 22px 24px 16px;
  padding: 22px;
  border: 1px solid #dce9f5;
  border-radius: 19px;
  background: #fff;
  box-shadow: 0 12px 34px rgba(16, 54, 91, .08);
}

.supplier-modal__field {
  margin-bottom: 17px;
}

.supplier-modal__field label {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-bottom: 7px;
  color: #17345b;
  font-size: 13px;
  font-weight: 800;
}

.supplier-modal__field label i {
  width: 18px;
  color: #078cca;
  text-align: center;
}

.supplier-modal__field .form-control {
  height: 50px;
  color: #0e284b;
  font-size: 14px;
  font-weight: 600;
  border: 1px solid #d5e3ef;
  border-radius: 13px;
  background: #fbfdff;
  box-shadow: none;
  transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
}

.supplier-modal__field .form-control:focus {
  border-color: #189dd4;
  background: #fff;
  box-shadow: 0 0 0 4px rgba(24, 157, 212, .12);
}

.supplier-modal__search .form-control {
  border-radius: 13px 0 0 13px;
}

.supplier-modal__search-button {
  height: 50px;
  padding: 0 20px;
  color: #fff !important;
  font-size: 13px;
  font-weight: 800;
  border: 0 !important;
  border-radius: 0 13px 13px 0 !important;
  background: linear-gradient(135deg, #1269d2, #08a6c8) !important;
  box-shadow: none !important;
}

.supplier-modal__search-button i {
  margin-right: 7px;
}

.supplier-modal__notice {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  color: #45617e;
  font-size: 12px;
  font-weight: 600;
  border: 1px solid #d8eafa;
  border-radius: 12px;
  background: #f1f8fe;
}

.supplier-modal__notice i {
  color: #0c91cc;
  font-size: 16px;
}

.supplier-modal__footer {
  display: flex;
  justify-content: flex-end;
  gap: 11px;
  padding: 0 24px 24px;
}

.supplier-modal__cancel,
.supplier-modal__save {
  height: 48px;
  padding: 0 24px;
  font-size: 14px;
  font-weight: 800;
  border: 0 !important;
  border-radius: 13px !important;
  transition: transform .2s ease, box-shadow .2s ease;
}

.supplier-modal__cancel {
  color: #38506a !important;
  background: #e8f0f7 !important;
}

.supplier-modal__save {
  min-width: 190px;
  color: #fff !important;
  background: linear-gradient(135deg, #0e68d5, #08a7c8) !important;
  box-shadow: 0 9px 20px rgba(5, 119, 184, .25) !important;
}

.supplier-modal__save i {
  margin-right: 8px;
}

.supplier-modal__cancel:hover,
.supplier-modal__save:hover {
  transform: translateY(-2px);
}

@media (max-width: 767px) {
  .supplier-create__trigger {
    width: 100%;
    min-width: 0;
    border-radius: 13px !important;
  }

  .supplier-modal .modal-dialog {
    margin: 10px;
  }

  .supplier-modal__hero {
    min-height: 112px;
    padding: 20px;
  }

  .supplier-modal__hero-icon {
    flex-basis: 50px;
    width: 50px;
    height: 50px;
  }

  .supplier-modal__hero p {
    display: none;
  }

  .supplier-modal__form {
    margin: 14px;
    padding: 16px;
  }

  .supplier-modal__search {
    flex-wrap: wrap;
  }

  .supplier-modal__search .form-control,
  .supplier-modal__search-button {
    width: 100%;
    border-radius: 12px !important;
  }

  .supplier-modal__search .input-group-append {
    width: 100%;
    margin-top: 8px;
    margin-left: 0;
  }

  .supplier-modal__footer {
    padding: 0 14px 16px;
  }

  .supplier-modal__cancel,
  .supplier-modal__save {
    flex: 1;
    min-width: 0;
    padding: 0 12px;
  }
}
</style>
