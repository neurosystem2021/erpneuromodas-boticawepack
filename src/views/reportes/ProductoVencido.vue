<template>
  <b-container fluid>
    <b-row>

        <b-col cols="3">
          Almacén
            <b-form-select v-model="IdAlmacen" 
            value-field="IdAlmacen"
            text-field="Almacen"
            :options="lstAlmances"></b-form-select>
        </b-col>
        <b-col cols="3">
          Fecha Inicial:
          <input type="date" class="form-control" v-model="FechaIni">
        </b-col>
        <b-col cols="3">
          Fecha Fin:
          <input type="date" class="form-control" v-model="FechaFin">
        </b-col>
        <b-col cols="3">
          <b-button variant="info" @click="obtenerReporte">Generar</b-button>
          <b-button variant="success" @click="exportarReporte">Exportar</b-button>
        </b-col>
        <b-col>
            <b-table 
            :items="lstProductosVencidos"
             small
             class="mt-3" 
            :fields="cabeceras"
            :per-page="perPage"
            :current-page="currentPage"
            >
              <template slot="Estado" slot-scope="data">
                <span v-if="data.item.Estado==-1"  
                      style="border: 1px solid #D50000; padding: 5px 10px; border-radius: 25px; background: #D50000;  color: #ffffff;">
                  Vencido
                </span>
                <span v-if="data.item.Estado==0"  
                      style="border: 1px solid #FFD600; padding: 5px 10px; border-radius: 25px; background: #FFD600;  color: #000000;">
                  Por Vencer
                </span>
                <span v-if="data.item.Estado==1"  
                      style="border: 1px solid #FFD600; padding: 5px 10px; border-radius: 25px; background: #FFD600;  color: #ffffff;">
                  Vigente
                </span>

               
                </template>
            </b-table>

            <b-pagination
                v-model="currentPage"
                :total-rows="lstProductosVencidos.length"
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
      currentPage: 1,
      perPage: 15,
      IdAlmacen: 1,
      API: config.API,
      lstAlmances: [],
      lstProductosVencidos: [],

      // headers: [
      //   { text: "Correlativo", key: Correlativo },
      //   { text: "Proveedor", key: Proveedor },
      //   { text: "Codigo", key: Codigo },
      //   { text: "Producto", key: Producto },
      //   { text: "CodigoBarra", key: CodigoBarra },
      //   { text: "Cantidad", key: Cantidad },
      //   { text: "Presentacion", key: Presentacion },
      //   { text: "FechaVen", key: FechaVen },
      //   { text: "Estado", key: Estado }
      // ],
      FechaIni: "",
      FechaFin: "",
      cabeceras: [
        { text: "Id", key: "IdProducto" },
        { text: "Producto", key: "Producto" },
        { text: "Proveedor", key: "Proveedor" },
        { text: "Correlativo", key: "Correlativo" },
        // { text: "Codigo", key: "Codigo" },
        // { text: "CodigoBarra", key: "CodigoBarra" },
        { text: "Cantidad", key: "Cantidad" },
        { text: "Fecha Ven", key: "FechaVen" },
        { text: "Estado", key: "Estado" }
      ]
      // fields: [{ text: "Correlativo", key: Correlativo }]
    };
  },
  async mounted() {
    console.log(this.cabeceras);
    this.FechaIni = this.$moment().format("YYYY-MM-01");
    this.FechaFin = this.$moment().format("YYYY-MM-DD");
    await Promise.all([this.obtenerAlmance(), this.obtenerReporte()]);
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
    async obtenerReporte() {
      let params = {
        IdAlmacen: this.IdAlmacen,
        FechaIni: this.FechaIni,
        FechaFin: this.FechaFin
      };
      await this.axios(`${this.API}productos/vencidos`, {
        params: params
      }).then(res => {
        if (res.data) {
          this.lstProductosVencidos = res.data;
        }
      });
    },
    async exportarReporte() {
      let params = {
        IdAlmacen: this.IdAlmacen,
        FechaIni: this.FechaIni,
        FechaFin: this.FechaFin
      };
      await this.axios(`${this.API}productos/vencidos/exportar`, {
        params: params
      }).then(res => {
        if (res.data) {
          window.open(res.data, "_blank");
        }
      });
    }
  }
};
</script>

<style>
</style>
