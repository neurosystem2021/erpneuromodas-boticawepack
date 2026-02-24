<template>
  <b-container fluid class="px-5 mb-5">
    <b-row>
       <b-col cols="12">
        <b-breadcrumb :items="items"></b-breadcrumb>
       </b-col>
        <b-col cols="3">
            Fecha
            <input type="date" class="form-control" v-model="fechaInicio">
        </b-col>
        <b-col cols="3">
            Fecha
            <input type="date" class="form-control" v-model="fechaFin">
        </b-col>
        <b-col cols="3">
            Almacén
            <b-form-select v-model="almacen" 
                 value-field="IdAlmacen"
                 text-field="Almacen"
                :options="lstAlmances"></b-form-select>
        </b-col>
        <b-col cols="3" class="pt-4">
            <b-button variant="info" @click="generarReporte()"><i class="fa fa-search" aria-hidden="true"></i> Generar</b-button>
            <b-button variant="success" @click="exportarReporte()"><i class="fa fa-file-excel" aria-hidden="true"></i> Exportar</b-button>
        </b-col>
        <b-col cols="12" class="mt-4">
            <table class="table table-hover table-striped ">
                <thead>
                    <tr>
                        <th style="text-align: center;">IdProducto </th>
                        <th>Producto </th>
                        <th style="text-align: center;">Cantidad </th>
                        <th style="text-align: center;">Precio Costo </th>
                        <th style="text-align: center;">Precio Venta </th>
                        <th style="text-align: center;">Total Venta </th>
                        <th style="text-align: center;">Total Costo </th>
                        <th style="text-align: center;">Utilidad Bruta </th>
                        <th style="text-align: center;">% Utilidad </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(reporte,index) in lstReporte" :key="index">
                        <td style="text-align: center;">{{reporte.IdProducto}}</td>
                        <td>{{reporte.Producto}}</td>
                        <td style="text-align: center;">{{reporte.Cantidad}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.PrecioCosto).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.PrecioVenta).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.TotalVenta).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.TotalCosto).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.UtilidadBruta).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(reporte.PorcentajeUti).toFixed(2)}}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="background: #006064; color: #ffffff; font-weight: bold;">
                        <td colspan="5">{{footer.Producto}}</td>
                       
                        <td style="text-align: center;">{{ parseFloat(footer.TotalVenta).toFixed(2)}}</td>
                        <td style="text-align: center;">{{ parseFloat(footer.TotalCosto).toFixed(2)}}</td>
                        <td style="text-align: center;">{{ parseFloat(footer.UtilidadBruta).toFixed(2)}}</td>
                        <td style="text-align: center;">{{ parseFloat(footer.PorcentajeUti).toFixed(2)}}</td>
                    </tr>
                </tfoot>
            </table>
            <!-- <b-table
                id="my-table"
                :items="lstReporte"
                :per-page="perPage"
                :current-page="currentPage"
                small
                :fields="header"
                ></b-table> -->
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      fechaInicio: "",
      fechaFin: "",
      almacen: 0,
      API: config.API,
      lstReporte: [],
      items: [
        {
          text: "VENTAS",
          href: "#"
        },
        {
          text: "REPORTE",
          href: "#"
        },
        {
          text: "UTILIDAD BRUTA VENTAS",
          active: true
        }
      ],
      header: [
        { key: "IdProducto" },
        { key: "Producto" },
        { key: "Cantidad" },
        { key: "PrecioCosto" },
        { key: "PrecioVenta" },
        { key: "TotalVenta" },
        { key: "TotalCosto" },
        { key: "UtilidadBruta" },
        { key: "PorcentajeUti" }
      ],
      lstAlmances: [],
      footer: {
        Producto: "TOTAL",
        Cantidad: 0,
        PrecioCosto: 0,
        PrecioVenta: 0,
        TotalVenta: 0,
        TotalCosto: 0,
        UtilidadBruta: 0,
        PorcentajeUti: 0
      }
    };
  },
  mounted() {
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.generarReporte();
    this.obtenerAlmance();
  },
  methods: {
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
          this.lstAlmances.unshift({
            Almacen: "TODOS",
            IdAlmacen: 0
          });
        }
      });
    },
    async exportarReporte() {
      // http://localhost/api/index.php/reporte/utilidadbruta/excel?fechaInicio=2024-01-01&fechaFin=2026-01-01&almacen=0

      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });

      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/utilidadbruta/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
      this.$swal.close();
    },
    async generarReporte() {
      this.$swal({
        title: "Procesando...",
        text: "Por favor espera",
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
          this.$swal.showLoading();
        }
      });

      let params = {
        fechaInicio: this.fechaInicio,
        fechaFin: this.fechaFin,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/utlidadbruta`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data;
          this.lstReporte = data;
          let Cantidad = 0;
          let PrecioCosto = 0;
          let PrecioVenta = 0;
          let TotalVenta = 0;
          let TotalCosto = 0;
          let UtilidadBruta = 0;
          let PorcentajeUti = 0;
          data.forEach(element => {
            Cantidad += element.Cantidad;
            PrecioCosto += element.PrecioCosto;
            PrecioVenta += element.PrecioVenta;
            TotalVenta += element.TotalVenta;
            TotalCosto += element.TotalCosto;
            UtilidadBruta += element.UtilidadBruta;
            PorcentajeUti += element.PorcentajeUti;
          });
          this.footer = {
            Producto: "TOTAL",
            Cantidad: parseFloat(Cantidad).toFixed(2),
            PrecioCosto: parseFloat(PrecioCosto).toFixed(2),
            PrecioVenta: parseFloat(PrecioVenta).toFixed(2),
            TotalVenta: parseFloat(TotalVenta).toFixed(2),
            TotalCosto: parseFloat(TotalCosto).toFixed(2),
            UtilidadBruta: parseFloat(UtilidadBruta).toFixed(2),
            PorcentajeUti: parseFloat(PorcentajeUti).toFixed(2)
          };
        }
      });
      this.$swal.close();
    }
  }
};
</script>

<style>
</style>
