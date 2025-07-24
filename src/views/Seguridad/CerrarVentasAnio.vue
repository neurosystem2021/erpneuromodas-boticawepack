<template>
  <b-container>
    <p>Módulo Control Stock </p>
    <b-button variant="warning" @click="cerrarAniosAnteriores">Cerrar Años Anteriores</b-button>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API
    };
  },
  methods: {
    cerrarAniosAnteriores() {
      this.axios(`${this.API}cerrar_anios_pasado`);
    }
  }
};
</script>

<style>
</style>
