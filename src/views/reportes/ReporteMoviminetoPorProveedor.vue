<template>
  <b-container fluid>
    <h3>Reporte de Ventas por Proveedor</h3>
    <b-row>
        <b-col cols="3">
            Fecha Desde:
            <input type="date" v-model="desde" class="form-control" >
        </b-col>
        <b-col cols="3">
            Fecha Desde:
            <input type="date" v-model="hasta"  class="form-control" >
        </b-col>
        <b-col cols="3">
            Proveedor:
            <b-select :options="lstProveedores"
            text-field="Proveedor"
            value-field="IdProveedor"
            v-model="id_proveedor"
            />
        </b-col>
        <b-col cols="3">
            <b-button class="mx-1 mt-3" variant="success" @click="getObtenerReporte()">Generar</b-button>
            <b-button class="mx-1 mt-3" variant="info" @click="exportarReporte()">Exportar</b-button>
        </b-col>
        <b-col cols="12" class="mb-2">
            <b-row>
                <b-col cols="3" offset="9">
                    Total:
                    <input type="text" class="form-control" readonly v-model="total">
                </b-col>
            </b-row>
        </b-col>
        <b-col cols="12">
            
            <b-table 
                striped
                hover
                :items="lstReporte"
                :fields="headers"
                :per-page="perPage"
                :current-page="currentPage"
                small
            ></b-table>
            <b-pagination
            v-model="currentPage"
            :total-rows="lstReporte.length"
            :per-page="perPage"
            aria-controls="my-table"
            ></b-pagination>
        </b-col>
       
        <b-col></b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      lstProveedores: [],
      lstReporte: [],
      desde: "",
      hasta: "",
      id_proveedor: "",
      API: config.API,
      headers: [
        { key: "FechaReg" },
        { key: "Producto" },
        { key: "Precio" },
        { key: "Cantidad" },
        { key: "Monto" }
      ],
      total: 0
    };
  },
  mounted() {
    this.cargarProveedores();
  },
  methods: {
    async cargarProveedores() {
      await this.axios.get(`${this.API}/proveedores`).then(response => {
        if (response.data) {
          this.lstProveedores = response.data;
        }
      });
    },
    async getObtenerReporte() {
      let data = {
        params: {
          id_proveedor: this.id_proveedor,
          fechaDesde: this.desde,
          fechaHasta: this.hasta
        }
      };
      await this.axios
        .get(`${this.API}reporte/movimiento/proveedores`, data)
        .then(response => {
          if (response.data) {
            this.lstReporte = response.data;
          }
        });
      this.total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
    },
    async exportarReporte() {
      let data = {
        params: {
          id_proveedor: this.id_proveedor,
          fechaDesde: this.desde,
          fechaHasta: this.hasta
        }
      };
      await this.axios
        .get(`${this.API}reporte/movimiento/proveedores/excel`, data)
        .then(response => {
          if (response.data) {
            window.open(response.data);
            // console.log(response.data);
          }
        });
      this.total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
    }
  }
};
</script>

<style>
</style>
