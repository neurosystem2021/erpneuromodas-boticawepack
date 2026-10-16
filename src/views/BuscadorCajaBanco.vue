<template>
<b-container fluid class="nst-screen nst-screen--buscadorcajabanco">
  <section class="nst-panel nst-panel--filters"><b-row>
      <b-col cols="12">
          <b-row>

                      <b-col cols="12" md="4">
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
                      </b-col>
                      <b-col cols="12" md="4">
                          Tipo Operación:
                          <b-form-select
                          v-model="IdTipoCajaBanco"
                          :options="lstTipoOpe"
                          value-field="IdTipoCajaBanco"
                          text-field="TipoCajaBanco"
                          ></b-form-select>
                      </b-col>
                          <b-col cols="12" md="4">
                              <b-button  @click="buscadorCajaBanco" variant="info" class="mt-3"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
                              <b-button  @click="exportarBuscadorCajaBanco" variant="success" class="mt-3">
                                  <i class="fa fa-file-excel" aria-hidden="true"></i>
                                  Exportar
                              </b-button>
                          </b-col>
          </b-row>
          <b-row>
              <b-col cols="12" md="4">
                  Fecha Inicial
                  <input type="date" class="form-control" v-model="FechaIni">
              </b-col>
              <b-col cols="12" md="4">
                  Fecha Fin
                  <input type="date" class="form-control" v-model="FechaFin">
              </b-col>
          </b-row>
          <b-row>
              
          </b-row>
      </b-col>
      <b-col cols="12">
          <b-table class="mt-2" :fields="fields" :items="lstCajaBancos"
              :per-page="perPage"
              :current-page="currentPage" responsive=""
           show-empty empty-text="No hay registros para los filtros seleccionados." empty-filtered-text="No se encontraron coincidencias." >
            <template slot="action" slot-scope="data">
              <b-button variant="danger" @click="eliminarMovimiento(data.item)"> <i class="fa fa-trash" aria-hidden="true"></i></b-button>
            </template>
          </b-table>

           <b-pagination
              v-model="currentPage"
              :total-rows="lstCajaBancos.length"
              :per-page="perPage"
              aria-controls="my-table"
          ></b-pagination>
      </b-col>
  </b-row></section>
</b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      currentPage: 1,
      perPage: 5,
      Cuenta: {},
      FechaIni: "",
      FechaFin: "",
      lstTipoOpe: [],
      IdTipoCajaBanco: "",
      lstCajaBancos: [],
      fields: [
        { key: "IdCajaBanco" },
        { key: "FechaDoc" },
        { key: "Proveedor" },
        { key: "Cliente" },
        { key: "Concepto" },
        { key: "Importe" },
        { key: "action" }
      ]
    };
  },
  mounted() {
    this.FechaIni = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.FechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    // this.cargarCuentas();
    this.ListarTipoOpe();
  },
  methods: {
    eliminarMovimiento(item) {
      this.$swal({
        icon: "question",
        title: "Eliminar Movimiento",
        allowEscapeKey: false,
        allowOutsideClick: false,
        confirmButtonText: "Si, Eliminar",
        showCancelButton: true
      }).then(async res => {
        if (res.isConfirmed) {
          await this.axios.put(`${this.API}cajabanco/eliminar`, item);
          this.buscadorCajaBanco();
        }
      });
    },
    setCuenta(obj) {
      this.Cuenta = obj;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    async ListarTipoOpe() {
      await this.axios(`${this.API}ListarTipoOpe`).then(res => {
        if (res.data) {
          this.lstTipoOpe = res.data;
        }
      });
    },
    async buscadorCajaBanco() {
      this.lstCajaBancos = [];
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });

      await this.axios(`${this.API}buscador_cajabanco`, {
        params: {
          IdCuenta: this.Cuenta.IdCuenta,
          IdTipoOperacion: this.IdTipoCajaBanco,
          FechaIni: this.FechaIni,
          FechaFin: this.FechaFin
        }
      })
        .then(res => {
          if (res.data) {
            this.lstCajaBancos = res.data;
          }
        })
        .catch(e => {
          console.log(e);
        });
      this.$swal.close();
    },
    async exportarBuscadorCajaBanco() {
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });

      await this.axios(`${this.API}exportar_buscador_cajabanco`, {
        params: {
          IdCuenta: this.Cuenta.IdCuenta,
          IdTipoOperacion: this.IdTipoCajaBanco,
          FechaIni: this.FechaIni,
          FechaFin: this.FechaFin
        }
      })
        .then(res => {
          if (res.data) {
            if (res.data) {
              window.open(res.data.file, "_blank");
              console.log(res.data);
            }
          }
        })
        .catch(e => {
          console.log(e);
        });
      this.$swal.close();
    }
  }
};
</script>

<style>
</style>
