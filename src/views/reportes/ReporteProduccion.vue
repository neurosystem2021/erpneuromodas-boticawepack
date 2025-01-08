<template>
  <b-container fluid>
    <center><h2>Reporte Producción</h2></center>
    <b-row>
        <b-col cols="3">
            Desde:
            <input type="date" class="form-control" v-model="desde">
        </b-col>
        <b-col cols="3">
            Desde:
            <input type="date" class="form-control" v-model="hasta">
        </b-col>
        <b-col cols="3">
            Repartidor:
            <b-select 
            :options="repartidor"
            value-field="Usuario"
            text-field="NombreUsuario"
            required
            v-model="IdUsuario"
            ></b-select>
        </b-col>
        <b-col cols="3">
            <b-button variant="success" @click="obtenerListado()">Generar</b-button>
        </b-col>
        <br>
        <b-col cols="12">
            <b-table :items="lstReporte" :fields="header"></b-table>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      desde: "",
      hasta: "",
      repartidor: [],
      lstReporte: [],
      header: [
        { key: "NombreUsuario" },
        { key: "FechaReg" ,label:"Fecha Entrega"},
        { key: "Producto" },
        { key: "CantidadPedida" },
        { key: "CantidadRealizada" },
        { key: "ExcDef", label: "Exc / Fal" }
      ],
      IdUsuario: ""
    };
  },
  mounted() {
    this.desde = this.$moment().format("YYYY-MM-DD");
    this.hasta = this.$moment().format("YYYY-MM-DD");

    this.obtenerRepartidor();
    this.obtenerListado();
  },
  methods: {
    async obtenerRepartidor() {
      let res = await this.axios(`${this.API}usuarios_general`).then(res => {
        if (res) {
          this.repartidor = res.data;
        }
      });
    },
    async obtenerListado() {
      this.lstReporte = [];
      await this.axios(`${this.API}report/pedido_entregado`, {
        params: {
          desde: this.desde,
          hasta: this.hasta,
          IdUsuario: this.IdUsuario
        }
      }).then(res => {
        if (res) {
          this.lstReporte = res.data;
        }
      });
    }
  }
};
</script>

<style>
</style>
