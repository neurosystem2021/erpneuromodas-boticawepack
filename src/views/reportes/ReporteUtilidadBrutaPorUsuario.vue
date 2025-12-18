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
          <b-form-checkbox
           
            value="1"
            unchecked-value="0"
            v-model="ServicioFlag"
          >
            Servicios
          </b-form-checkbox>


          <b-form-checkbox
            value="1"
            unchecked-value="0"
            v-model="MercaderiaFlag"
          >
            Mercaderías
          </b-form-checkbox>
        </b-col> 
        <b-col cols="12" class="mt-2"> 
            <b-button variant="success" @click="getReporteUtilidadPorUsuario()">Generar</b-button>
            <b-button variant="danger" @click="exportar()">Exportar PDF</b-button>
        </b-col>
        <b-col cols="12" class="mt-5"> 
          <b-table :fields="fields" :items="lstReporte" :per-page="perPage"
          :current-page="currentPage"/>
          
          <b-pagination
            v-model="currentPage"
            :total-rows="lstReporte.length"
            :per-page="perPage" 
            aria-controls="my-table"
          ></b-pagination>
          
         <table width="100%" v-if="lstReporte.length>0">
          <thead>
            <tr>
              <th  colspan="5">Resumen</th>
            
              <th>Sum Total Venta</th>
              <th>Sum Total Costo</th>
              <th>Sum Utilidad Bruta</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
                <tr>
                <th colspan="5">
                </th>
                <td><strong class="sumTotalVenta">
                    {{ parseFloat(lstReporte.reduce((sum,reporte)=>{
                        return sum+parseFloat(reporte.TotalVenta)
                    },0)).toFixed(2) }}
                </strong></td>
                <td><strong class="sumTotalCosto">
                    {{ parseFloat(lstReporte.reduce((sum,reporte)=>{
                        return sum+parseFloat(reporte.TotalCosto)
                    },0)).toFixed(2) }}
                </strong></td>
                <td><strong class="sumUtilidadBruta">
                    {{ parseFloat(lstReporte.reduce((sum,reporte)=>{
                        return sum+parseFloat(reporte.UtilidadBruta)
                    },0)).toFixed(2) }}
                </strong></td>
                </tr>
            </tbody>
         </table>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import momemnt from "moment";
export default {
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      lstReporte: [],
      API: config.API,
      fechaInicio: "",
      fechaFin: "",
      usuarioReg: "",
      ServicioFlag: 1,
      MercaderiaFlag: 1,
      lstUsuario: [],
      fields: [
        { label: "Fecha", key: "FechaReg" },
        { label: "Id Producto", key: "IdProducto" },
        { label: "Producto", key: "Producto" },
        { label: "Cantidad", key: "Cantidad" },
        { label: "Prom. Precio Costo", key: "PrecioCosto" },
        { label: "Prom. Precio Venta", key: "PrecioVenta" },
        { label: "Total Venta", key: "TotalVenta" },
        { label: "Total Costo", key: "TotalCosto" },
        { label: "Utilidad Bruta", key: "UtilidadBruta" }
      ]
    };
  },
  mounted() {
    this.cargarUsuarios();
    this.fechaInicio = this.$moment()
      .startOf("day")
      .format("YYYY-MM-DD");

    this.fechaFin = this.$moment().format("YYYY-MM-DD");
    this.usuarioReg = sessionStorage.getItem("User");
  },
  methods: {
    async cargarUsuarios() {
      await this.axios.get(`${this.API}lstusuarios`).then(response => {
        if (response.data) {
          this.lstUsuario = response.data;
        }
      });
    },
    async getReporteUtilidadPorUsuario() {
      this.lstReporte = [];
      const inicio = this.$moment(this.fechaInicio, "YYYY-MM-DD");
      const fin = this.$moment(this.fechaFin, "YYYY-MM-DD");
      const diffMeses = fin.diff(inicio, "months", true);
      if (diffMeses > 3) {
        this.$toasted.error("El rango de fechas no puede ser mayor a 3 meses");

        return;
      }

      let filtro = {
        params: {
          fechaInicio: this.fechaInicio,
          fechaFin: this.fechaFin,
          ServicioFlag: this.ServicioFlag,
          MercaderiaFlag: this.MercaderiaFlag,
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
    },
    async exportar() {
      const inicio = this.$moment(this.fechaInicio, "YYYY-MM-DD");
      const fin = this.$moment(this.fechaFin, "YYYY-MM-DD");
      const diffMeses = fin.diff(inicio, "months", true);
      if (diffMeses > 3) {
        this.$toasted.error("El rango de fechas no puede ser mayor a 3 meses");

        return;
      }
      await this.axios(`${this.API}reporte/utlidadbruta/usuario/exportar`, {
        params: {
          fechaInicio: this.fechaInicio,
          fechaFin: this.fechaFin,
          ServicioFlag: this.ServicioFlag,
          MercaderiaFlag: this.MercaderiaFlag,
          usuarioReg: this.usuarioReg
        }
      }).then(res => {
        if (res) {
          let url = res.data;
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
