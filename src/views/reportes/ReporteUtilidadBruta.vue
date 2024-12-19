<template>
  <b-container fluid>
        <b-row>
            <b-col cols="3">
                Almacén
                <b-select 
                  :options="lstAlmacen"
                  value-field="IdAlmacen"
                  text-field="Almacen"
                  >
                </b-select>

            </b-col>
            <b-col cols="3">
              Usuario
              <b-select 
                :options="lstUsuarios"
                value-field="Usuario"
                text-field="NombreUsuario"
                >
            </b-select>
             
            </b-col>
            <b-col cols="3">
              Desde
                <b-form-input v-model="desde" type="date" placeholder="Desde" />
            </b-col>
            <b-col cols="3">
              Hasta
                <b-form-input v-model="hasta" type="date" placeholder="Hasta" />
            </b-col>
            <b-col>
              <b-button variant="success"> GENERAR</b-button>
            </b-col>
            <b-col cols="12">
              tabla con reporte
            </b-col>
        </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  name: "ReporteUtilidadBrutaPorVendedor",
  data() {
    return {
      API: config.API,
      desde: "",
      hasta: "",
      lstUsuarios: [],
      lstAlmacen: []
    };
  },
  mounted() {
    this.cargarUsuarios();
  },
  methods: {
    async cargarUsuarios() {
      await this.axios(`${this.API}usuarios`).then(response => {
        this.lstUsuarios = response.data;
      });
    },
    async cargarUsuarios() {
      await this.axios(`${this.API}almacenes`).then(response => {
        this.lstAlmacen = response.data;
      });
    }
  }
};
</script>

<style>
</style>
