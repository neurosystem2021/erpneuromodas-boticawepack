<template>
  <b-container fluid class="ops-page">
    <header class="ops-hero"><div class="ops-hero__icon"><i class="fas fa-chart-bar"></i></div><div><small>ANÁLISIS COMERCIAL</small><h1>Compras por cliente</h1><p>Consulta productos adquiridos, cantidades e importe acumulado por cliente.</p></div><span><i class="fas fa-file-excel"></i> Reporte exportable</span></header>
    <b-row class="ops-card">
        <b-col cols="3" class="ops-actions">
            Cliente
            <autocomplete
                required="required"
                ref="inputCliente"
                :url="API + 'clientes'"
                anchor="ClienteDniRuc"
                label="writer"
                placeholder=""
                :onInput="(val) => newDniRuc = val"
                :on-select="setCliente" 
                :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                :classes="{input: 'form-control'}">
            </autocomplete>
        </b-col>
        <b-col cols="3">
            Fecha Desde
            <input type="date" class="form-control" v-model="fechaInicio">
        </b-col>
        <b-col cols="3">
            Fecha Hasta
            <input type="date" class="form-control" v-model="fechaFin">
        </b-col>
        <b-col cols="3">
            <b-button class="mx-1 mt-3" @click="getRanking()" variant="success">Generar</b-button>
            <b-button class="mx-1 mt-3" @click="exportarReporte()" variant="info">Excel</b-button>
        </b-col>
        <b-col cols="12" class="ops-summary mb-2">
            <b-row>
                <b-col cols="3" offset="3">
                    Producto Más Vendido:
                    <input type="text" class="form-control" readonly v-model="producto">
                </b-col>
                <b-col cols="3" >
                    Cantidad Producto:
                    <input type="text" class="form-control" readonly v-model="cantidad">
                </b-col>
                <b-col cols="3" >
                    Total:
                    <input type="text" class="form-control" readonly v-model="total">
                </b-col>
            </b-row>
        </b-col>
        <b-col cols="12" class="ops-results">
            <b-table 
            striped
            hover
            :items="lstReporte"
            :fields="headers"
            :per-page="perPage"
            :current-page="currentPage"
            small
            
            >
            </b-table>

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
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      lstReporte: [],
      API: config.API,
      fechaInicio: "",
      fechaFin: "",
      IdCliente: "",
      lstCliente: [],
      headers: [
        { key: "FechaReg" },
        { key: "Producto" },
        { key: "Precio" },
        { key: "Cantidad" },
        { key: "Monto" }
      ],
      total: 0,
      producto: "",
      cantidad: 0
    };
  },
  mounted() {
    this.fechaInicio = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.fechaFin = this.$moment().endOf("month").format("YYYY-MM-DD");
  },
  methods: {
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    setCliente(obj) {
      this.IdCliente = obj.IdCliente;
    },
    async getRanking() {
      this.lstReporte = [];
      await this.axios
        .get(`${this.API}reporte/ranking/clientes`, {
          params: {
            fechaInicio: this.fechaInicio,
            fechaFin: this.fechaFin,
            IdCliente: this.IdCliente
          }
        })
        .then(res => {
          if (res) {
            this.lstReporte = res.data;
          }
        });
      this.total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
      this.calcularProductMasVendido();
    },
    calcularProductMasVendido() {
      const productoCantidad = this.lstReporte.reduce((acumulador, venta) => {
        const { Producto, Cantidad } = venta;
        if (!acumulador[Producto]) {
          acumulador[Producto] = 0;
        }
        acumulador[Producto] += Cantidad;
        return acumulador;
      }, {});

      const productoMasVendido = Object.entries(productoCantidad).reduce(
        (max, [producto, cantidad]) => {
          if (cantidad > max.cantidad) {
            return { producto, cantidad };
          }
          return max;
        },
        { producto: null, cantidad: 0 }
      );
      this.producto = productoMasVendido.producto;
      this.cantidad = productoMasVendido.cantidad;
    },
    async exportarReporte() {
      let data = {
        params: {
          fechaInicio: this.fechaInicio,
          fechaFin: this.fechaFin,
          IdCliente: this.IdCliente
        }
      };
      await this.axios
        .get(`${this.API}reporte/ranking/clientes/excel`, data)
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
