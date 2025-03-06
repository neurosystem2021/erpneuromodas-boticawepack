<template>
  <b-container fluid>
    <b-row>
        <b-col cols="3">
            <b-row>
                <b-col cols="12">
                    Cuenta:
                    <b-form-select
                    v-model="IdCuenta"
                    :options="lstCuentas"
                    @change="cargarOperaciones()"
                    value-field="IdCuenta"
                    text-field="Cuenta"

                    ></b-form-select>

                </b-col>
                <b-col cols="12">
                    Fecha:
                    <input type="date" class="form-control" v-model="FechaDoc">
                </b-col>
            </b-row>
        </b-col>
        <b-col cols="9">ddd</b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      IdCuenta: "",
      lstCuentas: [],
      API: config.API,
      FechaDoc: ""
    };
  },
  mounted() {
    this.FechaDoc = this.$moment().format("YYYY-MM-DD");
    this.cargarCuentas();
  },
  methods: {
    async cargarCuentas() {
      await this.axios(`${this.API}/cuentas/listarcuentas`).then(res => {
        if (res.data) {
          this.lstCuentas = res.data;
        }
      });
    },
    async cargarOperaciones() {
      let params = {
        IdCuenta: this.IdCuenta,
        FechaDoc: this.FechaDoc
      };
      // controllers/server_processingCajaBanco.php?IdCuenta=1&FechaDoc=2025-03-06&_=1741274426193
      await this.axios(
        `${this.API}/controllers/server_processingCajaBanco.php`
      ).then(res => {
        if (res.data) {
          console.log(res.data);
        }
      });
    }
  }
};
</script>

<style>
</style>
