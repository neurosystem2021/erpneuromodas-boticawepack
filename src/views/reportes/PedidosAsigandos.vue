<template>
    <b-container fluid>
        <center><h2>PEDIDOS ASIGNADOS A REPARTIDOR</h2></center>
        <b-row>
            <b-col cols="12">
                <b-row>
                    <b-col cols="3">
                        <b-form-group>
                            <b-input-group prepend="Desde:" class="mt-2">
                                <input type="date" class="form-control" v-model="desde" />
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                    <b-col cols="3">
                        <b-form-group>
                            <b-input-group prepend="Hasta:" class="mt-2">
                                <input type="date" class="form-control" v-model="hasta" />
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                    <b-col cols="3">
                        <b-form-group>
                            <b-input-group prepend="Usuario:" class="mt-2">
                                <b-form-select
                                    :options="repartidor"
                                    value-field="Usuario"
                                    text-field="NombreUsuario"
                                    required
                                    v-model="IdRepartidor"
                                ></b-form-select>
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                    <b-col cols="3">
                        <b-button class="my-2" variant="success" @click="cargarPedidosAsignados()">Generar</b-button>
                        <!-- <b-button class="my-2" variant="danger" @click="exportar()">Exportar</b-button> -->
                    </b-col>
                </b-row>
            </b-col>
            <b-col cols="12">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Repartidor</th>
                            <th>Correlativo Doc</th>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Monto</th>
                            <th>Fecha / Hora Entrega</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(pedido, index) in lstPedidoAsignado" :key="index">
                            <td>{{ pedido.NombreUsuario }}</td>
                            <td>{{ pedido.Serie }}-{{ pedido.Numero }}</td>
                            <td>{{ pedido.Cliente }}</td>
                            <td>{{ pedido.Direccion }}</td>
                            <td>{{ pedido.Cantidad }}</td>
                            <td>{{ pedido.FechaEntrega }} {{ pedido.HoraEntrega }}</td>
                        </tr>
                        <tr style="background: #BBDEFB; color: #000000;">
                          <th colspan="4">Total:</th>
                          <th colspan="1">
                            {{
                                lstPedidoAsignado.reduce((sum, pedidos) => {
                                  return sum + parseFloat(pedidos.Cantidad || 0);
                                }, 0).toFixed(2)
                              }}
                          </th>
                          <th></th>
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
      desde: "",
      hasta: "",
      IdRepartidor: "",
      API: config.API,
      lstPedidoAsignado: [],
      repartidor: []
    };
  },
  mounted() {
    this.desde = this.$moment().format("YYYY-MM-DD");
    this.hasta = this.$moment().format("YYYY-MM-DD");
    this.obtenerRepartidor();
    this.cargarPedidosAsignados();
  },
  methods: {
    async cargarPedidosAsignados() {
      this.axios(`${this.API}reporte/pedidosasignados`, {
        params: {
          IdRepartidor: this.IdRepartidor,
          desde: this.desde,
          hasta: this.hasta
        }
      }).then(res => {
        if (res.data) {
          this.lstPedidoAsignado = res.data;
        }
      });
    },
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
    }
  }
};
</script>

<style>
</style>
