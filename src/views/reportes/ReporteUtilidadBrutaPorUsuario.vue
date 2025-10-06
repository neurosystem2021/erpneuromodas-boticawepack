<template>
  <b-container fluid>
    <h1 class="my-2"> UTILIDAD BRUTA VENTAS</h1>
    <b-row>
        <b-col cols="3">
            Desde:
            <input type="date" v-model="fechaInicio" class="form-control">
        </b-col>
        <b-col cols="3">
            Hasta:
            <input type="date" v-model="fechaFin" class="form-control">
        </b-col>
        <b-col cols="3">
            Usuario: 
            <b-select 
                :options="lstUsuario" 
                value-field="Usuario"
                text-field="NombreUsuario"
                v-model="usuarioReg"
            />
        </b-col>
        <b-col cols="3"> 
            <b-button variant="success" @click="getReporteUtilidadPorUsuario()">Generar</b-button>
        </b-col>
        <b-col cols="12" class="mt-5"> 
          <table class="table table-bordered table-striped">
            <thead>
                <th>Id Producto</th>
                <th>Producto</th>
                <th>Modelo</th>
                <th>Marca</th>
                <th>Talla</th>
                <th>Color</th>
                <th>Genero</th>
                <th>Botapie</th>
                <th>Cantidad</th>
                <th>Prom. Precio Costo</th>
                <th>Prom. Precio Venta</th>
                <th>Total Venta</th>
                <th>Total Costo</th>
                <th>Utilidad Bruta</th>
            </thead>
            <tbody>
                <tr v-for="(reporte,index) in lstReporte" :key="index"> 
                    <td>{{ reporte.IdProducto }}</td>
                    <td>{{ reporte.Producto }}</td>
                    <td>{{ reporte.ProductoModelo }}</td>
                    <td>{{ reporte.ProductoMarca }}</td>
                    <td>{{ reporte.ProductoTalla }}</td>
                    <td>{{ reporte.Color }}</td>
                    <td>{{ reporte.Genero }}</td>
                    <td>{{ reporte.Botapie }}</td>
                    <td>{{ reporte.Cantidad }}</td>
                    <td>{{ reporte.PrecioCosto }}</td>
                    <td>{{ reporte.PrecioVenta }}</td>
                    <td>{{ reporte.TotalVenta }}</td>
                    <td>{{ reporte.TotalCosto }}</td>
                    <td>{{ reporte.UtilidadBruta }}</td>
                </tr>

            </tbody>
            <tfoot>
                <tr>
                <td colspan="11"> Sumatoria
                </td>
                <td><strong class="sumTotalVenta">
                    {{ lstReporte.reduce((sum,reporte)=>{
                        return sum+parseFloat(reporte.TotalVenta)
                    },0) }}
                </strong></td>
                <td><strong class="sumTotalCosto">
                    {{ lstReporte.reduce((sum,reporte)=>{
                        return sum+parseFloat(reporte.TotalCosto)
                    },0) }}
                </strong></td>
                <td><strong class="sumUtilidadBruta">
                    {{ lstReporte.reduce((sum,reporte)=>{
                        return sum+parseFloat(reporte.UtilidadBruta)
                    },0) }}
                </strong></td>
                </tr>
            </tfoot>
                </table>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      lstReporte: [],
      API: config.API,
      fechaInicio: "",
      fechaFin: "",
      usuarioReg: "",
      lstUsuario: []
    };
  },
  mounted() {
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.cargarUsuarios();
    this.getReporteUtilidadPorUsuario();
  },
  methods: {
    async cargarUsuarios() {
      await this.axios.get(`${this.API}usuarios`).then(response => {
        if (response.data) {
          this.lstUsuario = response.data;
        }
      });
    },
    async getReporteUtilidadPorUsuario() {
      let filtro = {
        params: {
          fechaInicio: this.fechaInicio,
          fechaFin: this.fechaFin,
          usuarioReg: this.usuarioReg
        }
      };
      await this.axios
        .get(`${this.API}reporte/utlidadbruta/usuario`, filtro)
        .then(response => {
          if (response.data) {
            this.lstReporte = response.data;
          }
        });
    }
  }
};
</script>

<style>
</style>
