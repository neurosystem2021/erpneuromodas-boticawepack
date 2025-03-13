<template>
  <b-container>
    <b-row>
        <b-col cols="12">
            <b-row>
                <b-col cols="3">
                    <b-form-group>
                        <b-input-group prepend="Desde:" class="mt-2">
                        <input type="date" class="form-control" v-model="desde">
                    </b-input-group>
                </b-form-group>
                </b-col>
                <b-col cols="3">
                    <b-form-group>
                        <b-input-group prepend="Hasta:" class="mt-2">
                            <input type="date" class="form-control" v-model="hasta">
                        </b-input-group>
                    </b-form-group>
                </b-col>
                <b-col cols="3">
                    <b-form-group>
                        <b-input-group prepend="Usuario:" class="mt-2">
                            <b-select 
                            :options="repartidor"
                            value-field="Usuario"
                            text-field="NombreUsuario"
                            required
                            v-model="IdRepartidor"
                            :disabled="idPerfil != 1"
                            ></b-select>
                        </b-input-group>
                    </b-form-group>
                </b-col>
                <b-col cols="3">
                    <b-button class="my-2" variant="success" @click="obtenerDeudasPorCobrar()">Generar</b-button>
                    <b-button class="my-2" variant="danger" @click="exportar()">Exportar</b-button>
                </b-col>
            </b-row>
        </b-col>
        <b-col cols="12">
            <table class="table table-hover">
                <thead>
                    <tr style="background: #0D47A1; color: #ffffff;">
                        <th>Usuario</th>
                        <th>Fecha</th>
                        <th style="text-align: center;">Monto</th>
                        <th style="text-align: center;">Metodo</th>
                        <th style="text-align: center;">Monto Cobrado</th>
                        <th style="text-align: center;">Deuda</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(deuda,index) in lstDeudas" :key="index">
                        <td>{{deuda.NombreUsuario}}</td>
                        <td>{{deuda.FechaReg}}</td>
                        <td style="text-align: center;">{{parseFloat(deuda.MontoPago).toFixed(2)}}</td>
                        <td style="text-align: center;">
                            <tr>
                                <th class="my-0 py-0">Método</th>
                                <th class="my-0 py-0">Monto</th>
                            </tr>
                            <tr v-for="(metodo,index) in deuda.metodospago.filter(v=>v.monto!=0)" :key="index" class="my-0 py-0">
                                    <td class="my-0 py-0">{{ metodo.label }}</td>
                                    <td class="my-0 py-0">{{ metodo.monto }}</td>
                            </tr>
                        </td>
                        <td style="text-align: center;">{{parseFloat(deuda.montocobrado).toFixed(2)}}</td>
                        <td style="text-align: center;">{{parseFloat(deuda.Deuda).toFixed(2)}}</td>

                    </tr>
                    <tr style="background: #E3F2FD;">
                        <th colspan="3">Total</th>
                        <th colspan="1" style="text-align: center;">
                            {{ parseFloat(lstDeudas.reduce((sum,deuda)=>{
                                return sum + parseFloat(deuda.MontoPago)
                            },0) ).toFixed(2)}}
                        </th>
                        <th colspan="1" style="text-align: center;">
                            {{parseFloat( lstDeudas.reduce((sum,deuda)=>{
                                return sum + parseFloat(deuda.montocobrado)
                            },0)).toFixed(2) }}
                        </th>
                        <th colspan="1" style="text-align: center;">
                            {{parseFloat( lstDeudas.reduce((sum,deuda)=>{
                                return sum + parseFloat(deuda.Deuda)
                            },0)).toFixed(2) }}
                        </th>
                    </tr>
                </tbody>
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
      API: config.API,
      lstDeudas: [],
      repartidor: [],
      IdRepartidor: "",
      desde: "",
      hasta: "",
      idPerfil: sessionStorage.getItem("IdPerfil")
    };
  },
  mounted() {
    this.desde = this.$moment().format("YYYY-MM-DD");
    this.hasta = this.$moment().format("YYYY-MM-DD");
    this.IdRepartidor = sessionStorage.getItem("User");
    this.obtenerRepartidor();
    this.obtenerDeudasPorCobrar();
  },
  methods: {
    async obtenerRepartidor() {
      let res = await this.axios(`${this.API}repartidor`).then(res => {
        if (res) {
          this.repartidor = res.data;
          this.repartidor.unshift({
            Usuario: "",
            NombreUsuario: "TODOS"
          });
        }
      });
    },
    obtenerDeudasPorCobrar() {
      this.axios(`${this.API}reporte/deudatotal`, {
        params: {
          desde: this.desde,
          hasta: this.hasta,
          IdRepartidor: this.IdRepartidor
        }
      })
        .then(res => {
          if (res.data) {
            let data = res.data;
            let newData = data.map(element => ({
              ...element,
              metodospago: JSON.parse(element.metodospago),
              Deuda:
                parseFloat(element.MontoPago) - parseFloat(element.montocobrado)
            }));
            this.lstDeudas = newData;
          }
        })
        .catch(error => {
          console.error("Error al obtener deudas:", error);
        });
    },
    exportar() {
      this.axios
        .post(this.API + "exportar/reporte/deudatotal", {
          desde: this.desde,
          hasta: this.hasta,
          IdRepartidor: this.IdRepartidor,
          data: this.lstDeudas
        })
        .then(response => {
          if (response.data.success) {
            window.open(response.data.url, "_blank"); // Abrir en nueva pestaña
          } else {
            console.error(
              "Error al generar el reporte:",
              response.data.message
            );
          }
        })
        .catch(error => console.error("Error en la exportación:", error));
    }
  }
};
</script>

<style>
</style>
