<template>
  <b-container>
    <b-row>
        <b-col cols="4">
            Fecha Desde: <input type="date" class="form-control" v-model="fechaDesde">
        </b-col>
        <b-col cols="4">
            Fecha Hasta: <input type="date" class="form-control" v-model="fechaHasta">
        </b-col>
        <b-col cols="4">
            <b-button variant="success" class="mt-3" @click="cargarReporte">Generar</b-button>
        </b-col>
        <b-col cols="12" class="mt-3">
            <b-table :items="productos" :fields="fields"></b-table>
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
      fechaDesde: "",
      fechaHasta: "",
      productos: [],
      fields: [
        { key: "CodigoBarra", label: "Código" },
        { key: "Producto" },
        // { key: "Forma" },
        // { key: "Farmaceutica Laboratorio" },
        { key: "Lote" },
        { key: "FechaVen", label: "Fecha Vencimiento" },
        { key: "hashMovimiento" },
        { key: "Serie" },
        { key: "Numero" },
        { key: "stock", label: "Stock" }
      ]
    };
  },
  mounted() {
    this.fechaDesde = this.$moment().format("YYYY-MM-01");
    this.fechaHasta = this.$moment().format("YYYY-MM-DD");
    this.cargarReporte();
  },
  methods: {
    cargarReporte() {
      this.axios(`${this.API}/productos_por_vencer`, {
        params: {
          fechaDesde: this.fechaDesde,
          fechaHasta: this.fechaHasta
        }
      }).then(res => {
        this.productos = res.data;
      });
    }
  }
};
</script>

<style>
</style>
