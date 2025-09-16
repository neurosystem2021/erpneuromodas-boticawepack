<template>
  <b-container>
    ssssssssss
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      lstReporte: [],
      fechaIni: "",
      fechaFin: "",
      UsuarioReg: 0
    };
  },
  mounted() {
    this.fechaIni = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.obtenerDatos();
  },
  methods: {
    obtenerDatos() {
      this.axios(`${this.API}reporte/utilidabruta/presentacion`, {
        params: {
          fechaIni: this.fechaIni,
          fechaFin: this.fechaFin,
          UsuarioReg: this.UsuarioReg
        }
      })
        .then(res => {
          console.log(res.data);
        })
        .catch(e => {
          console.log(e);
        });
    }
  }
};
</script>

<style>
</style>
