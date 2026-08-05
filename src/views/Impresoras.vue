<template>
  <main class="printer-catalog">
    <section class="printer-hero">
      <div class="printer-hero__content">
        <span class="printer-hero__icon">
          <i class="fas fa-print"></i>
        </span>
        <div>
          <span class="printer-hero__eyebrow">CONFIGURACIÓN DE IMPRESIÓN</span>
          <h1>Impresoras de red</h1>
          <p>Administra los equipos que imprimen comprobantes y documentos.</p>
        </div>
      </div>

      <button type="button" class="printer-new-btn" @click="abrirNuevo()">
        <span><i class="fas fa-plus"></i></span>
        Nueva impresora
      </button>
    </section>

    <section class="printer-list-card">
      <header class="printer-list-card__header">
        <div>
          <span class="printer-list-card__eyebrow">DISPOSITIVOS CONFIGURADOS</span>
          <h2>Equipos disponibles</h2>
        </div>
        <div class="printer-count">
          <i class="fas fa-network-wired"></i>
          {{ lstImpresoras.length }}
          {{ lstImpresoras.length === 1 ? "impresora" : "impresoras" }}
        </div>
      </header>

      <div v-if="lstImpresoras.length" class="printer-table-wrap">
        <table class="printer-table">
          <thead>
            <tr>
              <th>Impresora</th>
              <th>Dirección IP</th>
              <th>Puerto</th>
              <th>Ancho de impresión</th>
              <th class="printer-table__actions-title">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in lstImpresoras" :key="item.IdImpresora">
              <td data-label="Impresora">
                <div class="printer-device">
                  <span class="printer-device__icon">
                    <i class="fas fa-print"></i>
                    <b class="printer-device__status"></b>
                  </span>
                  <div>
                    <strong>{{ item.NombreImpresora }}</strong>
                    <small>Lista para operar</small>
                  </div>
                </div>
              </td>
              <td data-label="Dirección IP">
                <span class="printer-data printer-data--ip">
                  <i class="fas fa-wifi"></i>
                  {{ item.Ip }}
                </span>
              </td>
              <td data-label="Puerto">
                <span class="printer-data">
                  <i class="fas fa-plug"></i>
                  {{ item.Puerto }}
                </span>
              </td>
              <td data-label="Ancho de impresión">
                <span class="printer-width">
                  <strong>{{ item.LongitudLinea }}</strong>
                  <small>caracteres por línea</small>
                </span>
              </td>
              <td data-label="Acciones">
                <div class="printer-actions">
                  <button
                    type="button"
                    class="printer-action printer-action--edit"
                    title="Editar impresora"
                    @click="abrirEditar(item)"
                  >
                    <i class="fas fa-pencil-alt"></i>
                    <span>Editar</span>
                  </button>
                  <button
                    type="button"
                    class="printer-action printer-action--delete"
                    title="Eliminar impresora"
                    @click="eliminar(item)"
                  >
                    <i class="fas fa-trash-alt"></i>
                    <span>Eliminar</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="printer-empty">
        <span><i class="fas fa-print"></i></span>
        <h3>Aún no hay impresoras configuradas</h3>
        <p>Registra un equipo para comenzar a imprimir desde el sistema.</p>
        <button type="button" @click="abrirNuevo()">
          <i class="fas fa-plus"></i> Agregar impresora
        </button>
      </div>
    </section>

    <b-modal
      v-model="modalNuevaImpresora"
      hide-footer
      centered
      modal-class="printer-form-modal"
      title="Nueva impresora"
    >
      <div class="printer-form-intro">
        <span><i class="fas fa-print"></i></span>
        <div>
          <strong>Conectar un nuevo equipo</strong>
          <small>Completa los datos de red de la impresora.</small>
        </div>
      </div>
      <div class="printer-form-grid">
        <label class="printer-field printer-field--wide">
          <span>Nombre de la impresora</span>
          <div><i class="fas fa-tag"></i><input type="text" v-model="impresora.NombreImpresora" placeholder="Ej. Punto de venta principal"></div>
        </label>
        <label class="printer-field printer-field--wide">
          <span>Dirección IP</span>
          <div><i class="fas fa-network-wired"></i><input type="text" v-model="impresora.Ip" placeholder="192.168.1.100"></div>
        </label>
        <label class="printer-field">
          <span>Puerto</span>
          <div><i class="fas fa-plug"></i><input type="text" v-model="impresora.Puerto" placeholder="9100"></div>
        </label>
        <label class="printer-field">
          <span>Longitud de línea</span>
          <div><i class="fas fa-ruler-horizontal"></i><input type="text" v-model="impresora.LongitudLinea" placeholder="48"></div>
        </label>
      </div>
      <div class="printer-form-actions">
        <button type="button" class="printer-form-btn printer-form-btn--cancel" @click="modalNuevaImpresora = false">
          Cancelar
        </button>
        <button type="button" class="printer-form-btn printer-form-btn--save" @click="registrar()">
          <i class="fas fa-check"></i> Guardar impresora
        </button>
      </div>
    </b-modal>

    <b-modal
      v-model="modalEditarImpresora"
      hide-footer
      centered
      modal-class="printer-form-modal"
      title="Editar impresora"
    >
      <div class="printer-form-intro">
        <span><i class="fas fa-tools"></i></span>
        <div>
          <strong>Actualizar configuración</strong>
          <small>Modifica los datos de conexión del equipo.</small>
        </div>
      </div>
      <div class="printer-form-grid">
        <label class="printer-field printer-field--wide">
          <span>Nombre de la impresora</span>
          <div><i class="fas fa-tag"></i><input type="text" v-model="impresora.NombreImpresora"></div>
        </label>
        <label class="printer-field printer-field--wide">
          <span>Dirección IP</span>
          <div><i class="fas fa-network-wired"></i><input type="text" v-model="impresora.Ip"></div>
        </label>
        <label class="printer-field">
          <span>Puerto</span>
          <div><i class="fas fa-plug"></i><input type="text" v-model="impresora.Puerto"></div>
        </label>
        <label class="printer-field">
          <span>Longitud de línea</span>
          <div><i class="fas fa-ruler-horizontal"></i><input type="text" v-model="impresora.LongitudLinea"></div>
        </label>
      </div>
      <div class="printer-form-actions">
        <button type="button" class="printer-form-btn printer-form-btn--cancel" @click="modalEditarImpresora = false">
          Cancelar
        </button>
        <button type="button" class="printer-form-btn printer-form-btn--save" @click="editar()">
          <i class="fas fa-check"></i> Guardar cambios
        </button>
      </div>
    </b-modal>
  </main>
</template>

<script>
import config from "@/store/config.js";
import Swal from "sweetalert2";
export default {
  data() {
    return {
      modalNuevaImpresora: false,
      modalEditarImpresora: false,
      lstImpresoras: [],
      fields: [
        // { key: "IdImpresora" },
        { key: "NombreImpresora" },
        { key: "Ip" },
        { key: "Puerto" },
        // { key: "EsCocina" },
        { key: "LongitudLinea" },
        { key: "action", label: "" }
      ],
      API: config.API,
      impresora: {
        IdImpresora: "",
        NombreImpresora: "",
        Ip: "",
        Puerto: "",
        LongitudLinea: ""
      }
    };
  },
  mounted() {
    this.obtenerListado();
  },
  methods: {
    async obtenerListado() {
      await this.axios(`${this.API}impresora`).then(res => {
        this.lstImpresoras = res.data;
      });
    },
    abrirNuevo() {
      this.impresora = {
        IdImpresora: "",
        NombreImpresora: "",
        Ip: "",
        Puerto: "",
        LongitudLinea: ""
      };
      this.modalNuevaImpresora = true;
    },
    abrirEditar(impresora) {
      this.impresora = impresora;
      this.modalEditarImpresora = true;
    },
    async registrar() {
      await this.axios
        .post(`${this.API}impresora/nuevo`, this.impresora)
        .then(res => {});
      this.obtenerListado();
      this.modalNuevaImpresora = false;
    },
    async editar() {
      await this.axios
        .put(`${this.API}impresora/editar`, this.impresora)
        .then(res => {});
      this.obtenerListado();
      this.modalEditarImpresora = false;
    },
    eliminar(item) {
      this.$swal({
        icon: "question",
        title: "Eliminar",
        text: `¿Desea Eliminar la impresora ${item.NombreImpresora}?`,
        confirmButtonText: "Si, eliminar",
        showCancelButton: true,
        cancelButtonText: "No.",
        cancelButtonColor: "red"
      }).then(res => {
        if (res.isConfirmed) {
          console.log("Eliminar");
        }
      });
    }
  }
};
</script>

<style>
.printer-catalog {
  min-height: calc(100vh - 90px);
  padding: 28px clamp(18px, 4vw, 72px) 64px;
  color: #10233f;
  background:
    radial-gradient(circle at 92% 4%, rgba(20, 184, 220, 0.13), transparent 24rem),
    radial-gradient(circle at 4% 88%, rgba(39, 101, 224, 0.08), transparent 27rem),
    linear-gradient(180deg, #f4f9ff 0%, #eef4fa 100%);
}

.printer-hero {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 30px;
  max-width: 1420px;
  margin: 0 auto 22px;
  padding: 26px 30px;
  overflow: hidden;
  border: 1px solid rgba(108, 211, 249, 0.35);
  border-radius: 24px;
  color: #fff;
  background: linear-gradient(125deg, #071f49 0%, #0a4e86 58%, #079fc4 100%);
  box-shadow: 0 20px 42px rgba(4, 38, 76, 0.2);
}

.printer-hero::before,
.printer-hero::after {
  content: "";
  position: absolute;
  border: 1px solid rgba(133, 231, 255, 0.2);
  border-radius: 50%;
}

.printer-hero::before {
  width: 250px;
  height: 250px;
  right: 11%;
  top: -170px;
}

.printer-hero::after {
  width: 120px;
  height: 120px;
  right: 2%;
  bottom: -82px;
}

.printer-hero__content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 18px;
}

.printer-hero__icon {
  display: grid;
  width: 64px;
  height: 64px;
  flex: 0 0 64px;
  place-items: center;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 18px;
  font-size: 27px;
  color: #76e8ff;
  background: rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 1px rgba(255, 255, 255, 0.17);
}

.printer-hero__eyebrow,
.printer-list-card__eyebrow {
  display: block;
  margin-bottom: 4px;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.16em;
}

.printer-hero__eyebrow {
  color: #70e5ff;
}

.printer-hero h1 {
  margin: 0;
  font-size: clamp(25px, 2.3vw, 36px);
  font-weight: 900;
  letter-spacing: -0.03em;
}

.printer-hero p {
  margin: 5px 0 0;
  color: rgba(255, 255, 255, 0.76);
  font-size: 14px;
  font-weight: 600;
}

.printer-new-btn {
  position: relative;
  z-index: 1;
  display: inline-flex;
  min-height: 52px;
  padding: 7px 20px 7px 8px;
  align-items: center;
  gap: 11px;
  border: 1px solid rgba(255, 255, 255, 0.25);
  border-radius: 16px;
  color: #06334b;
  background: linear-gradient(135deg, #78f0da, #36d9b0);
  box-shadow: 0 12px 24px rgba(6, 23, 52, 0.28);
  font-size: 14px;
  font-weight: 900;
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

.printer-new-btn span {
  display: grid;
  width: 37px;
  height: 37px;
  place-items: center;
  border-radius: 11px;
  color: #fff;
  background: rgba(4, 58, 70, 0.24);
}

.printer-new-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 16px 28px rgba(5, 34, 63, 0.34);
}

.printer-list-card {
  max-width: 1420px;
  margin: 0 auto;
  overflow: hidden;
  border: 1px solid #dce8f4;
  border-radius: 24px;
  background: rgba(255, 255, 255, 0.94);
  box-shadow: 0 18px 45px rgba(20, 53, 86, 0.11);
}

.printer-list-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
  padding: 22px 26px;
  border-bottom: 1px solid #e4edf6;
  background: linear-gradient(120deg, #fff 0%, #f6fbff 100%);
}

.printer-list-card__eyebrow {
  color: #1689c2;
}

.printer-list-card h2 {
  margin: 0;
  font-size: 21px;
  font-weight: 900;
  letter-spacing: -0.02em;
}

.printer-count {
  display: inline-flex;
  padding: 10px 14px;
  align-items: center;
  gap: 8px;
  border: 1px solid #d6e8f7;
  border-radius: 13px;
  color: #185783;
  background: #edf8ff;
  font-size: 13px;
  font-weight: 800;
}

.printer-table-wrap {
  overflow-x: auto;
  padding: 8px 20px 22px;
}

.printer-table {
  width: 100%;
  min-width: 850px;
  border-collapse: separate;
  border-spacing: 0 10px;
}

.printer-table th {
  padding: 10px 16px 3px;
  color: #60738c;
  font-size: 11px;
  font-weight: 900;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.printer-table td {
  padding: 15px 16px;
  border-top: 1px solid #e5edf5;
  border-bottom: 1px solid #e5edf5;
  background: #fbfdff;
  vertical-align: middle;
}

.printer-table td:first-child {
  border-left: 1px solid #e5edf5;
  border-radius: 16px 0 0 16px;
}

.printer-table td:last-child {
  border-right: 1px solid #e5edf5;
  border-radius: 0 16px 16px 0;
}

.printer-table tbody tr {
  transition: transform 0.2s ease, filter 0.2s ease;
}

.printer-table tbody tr:hover {
  transform: translateY(-2px);
  filter: drop-shadow(0 8px 12px rgba(18, 56, 91, 0.1));
}

.printer-table__actions-title {
  text-align: right;
}

.printer-device {
  display: flex;
  align-items: center;
  gap: 12px;
}

.printer-device__icon {
  position: relative;
  display: grid;
  width: 46px;
  height: 46px;
  flex: 0 0 46px;
  place-items: center;
  border-radius: 14px;
  color: #fff;
  background: linear-gradient(135deg, #1065d8, #0bb4cf);
  box-shadow: 0 8px 16px rgba(21, 119, 191, 0.24);
}

.printer-device__status {
  position: absolute;
  right: -2px;
  bottom: -2px;
  width: 12px;
  height: 12px;
  border: 2px solid #fff;
  border-radius: 50%;
  background: #22c989;
}

.printer-device strong,
.printer-device small,
.printer-width strong,
.printer-width small {
  display: block;
}

.printer-device strong {
  color: #10233f;
  font-size: 14px;
  font-weight: 900;
}

.printer-device small,
.printer-width small {
  margin-top: 2px;
  color: #75869b;
  font-size: 11px;
  font-weight: 600;
}

.printer-data {
  display: inline-flex;
  min-height: 38px;
  padding: 8px 11px;
  align-items: center;
  gap: 8px;
  border: 1px solid #dce8f2;
  border-radius: 11px;
  color: #2d4561;
  background: #fff;
  font-size: 13px;
  font-weight: 800;
}

.printer-data i {
  color: #168dc5;
}

.printer-data--ip {
  font-family: Consolas, "Courier New", monospace;
}

.printer-width strong {
  color: #0c6fab;
  font-size: 17px;
  font-weight: 900;
}

.printer-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.printer-action {
  display: inline-flex;
  min-height: 39px;
  padding: 8px 13px;
  align-items: center;
  gap: 7px;
  border: 0;
  border-radius: 12px;
  color: #fff;
  font-size: 12px;
  font-weight: 900;
  box-shadow: 0 7px 14px rgba(20, 51, 85, 0.16);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.printer-action:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 18px rgba(20, 51, 85, 0.23);
}

.printer-action--edit {
  background: linear-gradient(135deg, #126ad8, #168fcf);
}

.printer-action--delete {
  background: linear-gradient(135deg, #ee3b55, #ff5c6f);
}

.printer-empty {
  padding: 60px 24px 68px;
  text-align: center;
}

.printer-empty > span {
  display: grid;
  width: 76px;
  height: 76px;
  margin: 0 auto 16px;
  place-items: center;
  border-radius: 22px;
  color: #fff;
  background: linear-gradient(135deg, #116ad9, #0eb3c9);
  box-shadow: 0 15px 28px rgba(18, 120, 190, 0.24);
  font-size: 30px;
}

.printer-empty h3 {
  margin: 0 0 6px;
  font-size: 20px;
  font-weight: 900;
}

.printer-empty p {
  margin: 0 0 18px;
  color: #6f8197;
}

.printer-empty button {
  padding: 11px 18px;
  border: 0;
  border-radius: 13px;
  color: #fff;
  background: linear-gradient(135deg, #1269d7, #0a99c9);
  font-weight: 800;
}

.printer-form-modal .modal-content {
  overflow: hidden;
  border: 1px solid rgba(102, 211, 248, 0.38);
  border-radius: 22px;
  box-shadow: 0 30px 70px rgba(3, 25, 50, 0.32);
}

.printer-form-modal .modal-header {
  padding: 19px 22px;
  border: 0;
  color: #fff;
  background: linear-gradient(125deg, #071f49, #087cae);
}

.printer-form-modal .modal-title {
  font-size: 18px;
  font-weight: 900;
}

.printer-form-modal .close {
  width: 36px;
  height: 36px;
  padding: 0;
  border-radius: 10px;
  color: #fff;
  background: rgba(255, 255, 255, 0.13);
  opacity: 1;
  text-shadow: none;
}

.printer-form-modal .modal-body {
  padding: 22px;
  background: #f7fbff;
}

.printer-form-intro {
  display: flex;
  margin-bottom: 20px;
  padding: 13px;
  align-items: center;
  gap: 12px;
  border: 1px solid #dcebf7;
  border-radius: 15px;
  background: #fff;
}

.printer-form-intro > span {
  display: grid;
  width: 42px;
  height: 42px;
  flex: 0 0 42px;
  place-items: center;
  border-radius: 12px;
  color: #fff;
  background: linear-gradient(135deg, #126bd9, #0aa7c9);
}

.printer-form-intro strong,
.printer-form-intro small {
  display: block;
}

.printer-form-intro strong {
  color: #10233f;
  font-weight: 900;
}

.printer-form-intro small {
  margin-top: 2px;
  color: #72839a;
}

.printer-form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 15px;
}

.printer-field {
  margin: 0;
}

.printer-field--wide {
  grid-column: 1 / -1;
}

.printer-field > span {
  display: block;
  margin: 0 0 6px 2px;
  color: #334a64;
  font-size: 12px;
  font-weight: 900;
}

.printer-field > div {
  display: flex;
  height: 47px;
  overflow: hidden;
  align-items: center;
  border: 1px solid #cfdeeb;
  border-radius: 13px;
  background: #fff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.printer-field > div:focus-within {
  border-color: #2c91e5;
  box-shadow: 0 0 0 3px rgba(31, 132, 227, 0.12);
}

.printer-field i {
  width: 44px;
  text-align: center;
  color: #1688c7;
}

.printer-field input {
  width: calc(100% - 44px);
  height: 100%;
  padding: 0 13px 0 0;
  border: 0;
  outline: 0;
  color: #172b46;
  background: transparent;
  font-weight: 700;
}

.printer-form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 22px;
  padding-top: 17px;
  border-top: 1px solid #dfebf5;
}

.printer-form-btn {
  min-height: 44px;
  padding: 10px 18px;
  border: 0;
  border-radius: 13px;
  font-size: 13px;
  font-weight: 900;
}

.printer-form-btn--cancel {
  color: #40536a;
  background: #e7eef5;
}

.printer-form-btn--save {
  color: #fff;
  background: linear-gradient(135deg, #1268d7, #0a9ec6);
  box-shadow: 0 9px 17px rgba(13, 110, 183, 0.22);
}

@media (max-width: 767px) {
  .printer-catalog {
    padding: 16px 12px 42px;
  }

  .printer-hero {
    padding: 21px 18px;
    align-items: stretch;
    flex-direction: column;
  }

  .printer-hero__icon {
    width: 52px;
    height: 52px;
    flex-basis: 52px;
    border-radius: 15px;
  }

  .printer-new-btn {
    justify-content: center;
  }

  .printer-list-card__header {
    padding: 18px;
    align-items: flex-start;
    flex-direction: column;
  }

  .printer-table-wrap {
    padding: 3px 12px 15px;
  }

  .printer-form-grid {
    grid-template-columns: 1fr;
  }

  .printer-field--wide {
    grid-column: auto;
  }

  .printer-form-actions {
    flex-direction: column-reverse;
  }

  .printer-form-btn {
    width: 100%;
  }
}
</style>
