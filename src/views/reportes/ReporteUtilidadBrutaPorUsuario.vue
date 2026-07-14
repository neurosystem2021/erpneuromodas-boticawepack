<template>
  <b-container fluid>
    <h1 class="my-2">UTILIDAD BRUTA VENTAS</h1>
    <b-row>
      <b-col cols="3">
        Desde:
        <input type="date" v-model="fechaInicio" class="form-control" />
      </b-col>
      <b-col cols="3">
        Hasta:
        <input type="date" v-model="fechaFin" class="form-control" />
      </b-col>
      <b-col cols="2">
        Usuario:
        <b-select
          :options="lstUsuario"
          value-field="Usuario"
          text-field="NombreUsuario"
          v-model="usuarioReg"
        />
      </b-col>
      <b-col cols="2" class="pt-4">
        <b-form-checkbox v-model="agrupado" switch>
          Agrupar por producto
        </b-form-checkbox>
      </b-col>
      <b-col cols="2" class="pt-4">
        <b-button variant="success" @click="getReporteUtilidadPorUsuario()"
          >Generar</b-button
        >
      </b-col>
      <b-col cols="12" class="mt-5">
        <table class="table table-bordered table-striped">
          <thead>
            <th>Id Producto</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Prom. Precio Costo</th>
            <th>Prom. Precio Venta</th>
            <th>Total Venta</th>
            <th>Total Costo</th>
            <th>Utilidad Bruta</th>
            <th>% Utilidad</th>
          </thead>
          <tbody>
            <tr v-for="(reporte, index) in lstReporte" :key="index">
              <td>{{ reporte.IdProducto }}</td>
              <td>{{ reporte.Producto }}</td>
              <td>
                <span v-if="agrupadoAplicado || reporte.Descripcion === 'UNIDAD'">
                  {{ reporte.Cantidad }}
                </span>
                <span v-else>
                  {{ reporte.CantidadVenta }} ({{ reporte.Cantidad }} unid.)
                </span>
              </td>
              <td>{{ parseFloat(reporte.PrecioCosto).toFixed(2) }}</td>
              <td>{{ parseFloat(reporte.PrecioVenta).toFixed(2) }}</td>
              <td>{{ parseFloat(reporte.TotalVenta).toFixed(2) }}</td>
              <td>{{ parseFloat(reporte.TotalCosto).toFixed(2) }}</td>
              <td>{{ parseFloat(reporte.UtilidadBruta).toFixed(2) }}</td>
              <td>{{ parseFloat(reporte.PorcentajeUti).toFixed(2) }} %</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="6">Sumatoria</td>
              <td>
                <strong class="sumTotalVenta">
                  {{ totalCompras }}
                </strong>
              </td>
              <td>
                <strong class="sumTotalCosto">{{ totalVentas }} </strong>
              </td>
              <td>
                <strong class="sumUtilidadBruta"> {{ Porcentaje }} %</strong>
              </td>
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
      lstUsuario: [],
      agrupado: false,
      agrupadoAplicado: false, // controla el formato mostrado, solo cambia al Generar
      totalCompras: 0,
      totalVentas: 0,
      Porcentaje: 0,
    };
  },
  mounted() {
    this.cargarUsuarios();
  },
  methods: {
    async cargarUsuarios() {
      await this.axios.get(`${this.API}usuarios`).then((response) => {
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
          usuarioReg: this.usuarioReg,
          agrupado: this.agrupado ? 1 : 0,
        },
      };
      await this.axios
        .get(`${this.API}reporte/utlidadbruta/usuario`, filtro)
        .then((response) => {
          if (response.data) {
            this.lstReporte = response.data;
            this.agrupadoAplicado = this.agrupado; // recién aquí se refleja el cambio visual
            this.calcularTotales(response.data);
          }
        });
    },
    calcularTotales(data) {
      let totalCompras = 0;
      let totalVentas = 0;
      data.forEach((element) => {
        totalCompras += parseFloat(element.TotalCosto);
        totalVentas += parseFloat(element.TotalVenta);
      });
      this.totalCompras = parseFloat(totalCompras).toFixed(2);
      this.totalVentas = parseFloat(totalVentas).toFixed(2);
      this.Porcentaje = parseFloat(
        (100 * (totalVentas - totalCompras)) / totalCompras
      ).toFixed(2);
    },
  },
};
</script>

<style>
</style>