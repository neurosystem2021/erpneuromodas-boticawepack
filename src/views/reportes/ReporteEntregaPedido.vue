<template>
  <b-container fluid>
    <center><h2>Reporte Entrega de Pedido</h2></center>
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
            v-model="IdRepartidor"
            ></b-select>
        </b-col>
        <b-col cols="3">
            <b-button variant="success" @click="obtenerListado()">Generar</b-button>
        </b-col>
        <br>
        <b-col cols="12">
            <b-table :items="lstReporte" :fields="headers" small>
                <template slot="MedotoPago" slot-scope="data">
                    <table class="table">
                        <tr>
                            <th>Método</th>
                            <th>Monto</th>
                         </tr>
                         <tr v-for="(metodo,index) in data.item.metodospago.filter(v=>v.monto>0)" :key="index">
                        <td>{{ metodo.label }}</td>
                        <td>{{ metodo.monto }}</td>
                    </tr>
                    </table>
                  <!--   <tr>
                        <th>Metodo</th>
                        <th>Monto</th>
                    </tr>
                    <tr v-for="(metodo,index) in data.item.metodospago" :key="index">
                        <td>{{ metodo.label }}</td>
                        <td>{{ metodo.monto }}</td>
                    </tr>-->
                </template> 
            </b-table>
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
      lstReporte: [],
      repartidor: [],
      headers: [
        { thStyle: { width: "16%" }, key: "Repartidor" },
        { thStyle: { width: "16%" }, key: "Correlativo" },
        { thStyle: { width: "16%" }, key: "DniRuc", label: "Doc." },
        { thStyle: { width: "16%" }, key: "Cliente" },
        { thStyle: { width: "20%" }, key: "Dirección" },
        { thStyle: { width: "20%" }, key: "MedotoPago", label: "Metodos Pago" }
        // { key: "" }
      ],
      desde: "",
      hasta: "",
      IdRepartidor: ""
    };
  },
  mounted() {
    this.desde = this.$moment().format("YYYY-MM-DD");
    this.hasta = this.$moment().format("YYYY-MM-DD");

    this.obtenerRepartidor();
    this.obtenerListado();
  },
  methods: {
    async obtenerListado() {
      this.lstReporte = [];
      await this.axios(`${this.API}reporte/entrega_pedido`, {
        params: {
          desde: this.desde,
          hasta: this.hasta,
          IdRepartidor: this.IdRepartidor
        }
      }).then(res => {
        if (res) {
          this.lstReporte = res.data.map(item => {
            return {
              ...item,
              metodospago: JSON.parse(item.metodospago) // Convertir el string a array
            };
          });
        }
      });
    },
    async obtenerRepartidor() {
      let res = await this.axios(`${this.API}repartidor`).then(res => {
        if (res) {
          this.repartidor = res.data;
        }
      });
    }
  }
};
</script>

<style>
</style>
