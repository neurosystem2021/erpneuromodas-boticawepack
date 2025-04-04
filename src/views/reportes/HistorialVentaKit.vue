<template>
  <b-container >
      <center><h4>Historial de Kits Vendidos</h4></center>
    <b-row>
        <b-col cols="3">
            <b-input-group prepend="Desde:" class="mt-3">
                <input type="date" class="form-control" v-model="fechaDesde">
            </b-input-group>
        </b-col>
        <b-col cols="3">
            <b-input-group prepend="Hasta:" class="mt-3">
                <input type="date" class="form-control" v-model="fechaHasta">
            </b-input-group>
        </b-col>
        <b-col cols="3" style="text-align: center; align-content: center;">
                <b-button variant="info" @click="obtenerListado()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
                <b-button variant="success" @click="exportarListado()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button>
        </b-col>
        <b-col cols="12">
            <b-table 
                :items="lstHistorial" 
                :fields="fields"
                :per-page="perPage"
                :current-page="currentPage"
            ></b-table>

            <b-pagination
                v-model="currentPage"
                :total-rows="lstHistorial.length"
                :per-page="perPage"
                aria-controls="my-table"
            ></b-pagination>
        </b-col>
    </b-row>
    <!-- {{lstHistorial}} -->
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      perPage: 5,
      currentPage: 1,
      lstHistorial: [],
      fields: [
        { key: "DniRuc", label: "Doc." },
        { key: "Cliente", label: "Cliente" },
        { key: "Serie", label: "Serie" },
        { key: "Numero", label: "Numero" },
        // { key: "FechaDoc", label: "FechaDoc" },
        { key: "Producto", label: "Producto" },
        { key: "UsuarioReg", label: "Usuario" },
        { key: "FechaReg", label: "Fecha Reg" },
        { key: "Cantidad", label: "Cantidad" },
        { key: "Precio", label: "Precio" },
        { key: "Descuento", label: "Descuento" }
      ],
      fechaDesde: this.$moment()
        .startOf("months")
        .format("YYYY-MM-01"),
      fechaHasta: this.$moment()
        .endOf("months")
        .format("YYYY-MM-DD")
    };
  },
  mounted() {
    this.obtenerListado();
  },
  methods: {
    async obtenerListado() {
      await this.axios(`${this.API}Ve_DocVentaHistorialKit`, {
        params: {
          fechaDesde: this.fechaDesde,
          fechaHasta: this.fechaHasta
        }
      }).then(res => {
        if (res.data) {
          this.lstHistorial = res.data;
        }
      });
    },
    async exportarListado() {
      await this.axios(`${this.API}reporte/Ve_DocVentaHistorialKit`, {
        params: {
          fechaDesde: this.fechaDesde,
          fechaHasta: this.fechaHasta
        }
      }).then(res => {
        if (res.data) {
          let url = res.data.url;
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          window.open(url, "_blank");
        }
      });
    }
  }
};
</script>

<style>
</style>
