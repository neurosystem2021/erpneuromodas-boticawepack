<template>
  <b-container fluid>
        <b-row>
            <b-col cols="3">
                Almacén
                <b-select 
                  :options="lstAlmacen"
                  value-field="IdAlmacen"
                  text-field="Almacen"
                  v-model="idAlmacen"
                  >
                </b-select>

            </b-col>
            <b-col cols="3">
              Usuario
              <b-select 
                :options="lstUsuarios"
                value-field="Usuario"
                text-field="NombreUsuario"
                v-model="idUsuario"
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
              <b-button variant="success" @click="Generar()"> GENERAR</b-button>
            </b-col>
            <b-col cols="12">
              <b-table striped hover :items="lstReporte" :fields="headers"
                :per-page="perPage"
                :current-page="currentPage"
              ></b-table>
              <b-pagination
                v-model="currentPage"
                :total-rows="lstReporte.length"
                :per-page="perPage"
                aria-controls="my-table"
              ></b-pagination>
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
      perPage: 10,
      currentPage: 1,
      API: config.API,
      desde: "",
      hasta: "",
      idAlmacen: "",
      idUsuario: "",
      lstUsuarios: [],
      lstAlmacen: [],
      headers: [
        { key: "UsuarioReg" },
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "PrecioCosto" },
        { key: "PrecioVenta" },
        { key: "TotalCosto" },
        { key: "TotalVenta" },
        { key: "PorcentajeUti" },
        { key: "UtilidadBruta" }
        // { key: "IdProducto" },
      ],
      lstReporte: []
    };
  },
  mounted() {
    this.cargarUsuarios();
    this.cargarAlmancen();
  },
  methods: {
    async cargarUsuarios() {
      await this.axios(`${this.API}usuarios`).then(response => {
        this.lstUsuarios = response.data;
      });
    },
    async cargarAlmancen() {
      await this.axios(`${this.API}almacenes`).then(response => {
        this.lstAlmacen = response.data;
      });
    },
    async Generar() {
      this.lstReporte = [];
      await this.axios(`${this.API}reporte/utlidadbruta_operador`, {
        params: {
          desde: this.desde,
          hasta: this.hasta,
          idAlmacen: this.idAlmacen,
          idUsuario: this.idUsuario
        }
      }).then(response => {
        this.lstReporte = response.data;
      });
    }
  }
};
</script>

<style>
</style>
