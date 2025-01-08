<template>
  <b-container fluid>
    <b-row>
        <b-col cols="12"> 
            <center>
                <h2>Entrega de Pedido Asignado</h2>
            </center>
        </b-col>
        <b-col cols="12"> 
            <b-table :items="lstRepartos" :fields="headers" :per-page="perPage"
            :current-page="currentPage" small>
                <template slot="FechaHoraEntrega" slot-scope="data">
                    {{ data.item['FechaEntrega'] }} {{ data.item['HoraEntrega'] }}
                </template>
                <template slot="action" slot-scope="data">
                    <b-button variant="success" @click="abrirModalFinalizar(data.item)" >Finalizar</b-button>
                </template>
                  <template slot="DetalleProductos" slot-scope="data">
                    <tr>

                            <th width="70%"> Producto</th>
                            <th width="10%"> Cant.</th>
                            <th width="10%"> Precio</th>
                            <th width="10%"> Total</th>
                        </tr>
                   
                        <tr v-for="(detalle,index) in data.item.detalle" :key="index">
                            <td>{{ detalle.Producto }}</td>
                            <td>{{ detalle.Cantidad }}</td>
                            <td>{{ detalle.Precio }}</td>
                            <td>{{ detalle.Cantidad * detalle.Precio }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <th>{{ data.item.detalle.reduce((sum,producto)=>{
                                return sum+parseFloat(producto.Cantidad)
                            },0) }}</th>
                            <th>{{ data.item.detalle.reduce((sum,producto)=>{
                                return sum+parseFloat(producto.Precio)
                            },0) }}</th>
                            <th>{{ data.item.detalle.reduce((sum,producto)=>{
                                return sum+parseFloat(producto.Cantidad*producto.Precio)
                            },0)
                             }}</th>
                        </tr>
                    
                </template>
            </b-table>

            <b-pagination
                v-model="currentPage"
                :total-rows="lstRepartos.length"
                :per-page="perPage"
                aria-controls="my-table"
                ></b-pagination>
        </b-col>
      <b-modal v-model="finalizarFlag" hide-footer :title="`Finalizar Pedido ${item.Correlativo}`">
            <table class="table">
                <thead>
                    <tr>
                        <th width="60%">Metodo de Pago</th>
                        <th width="40%">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(metodo,index) in metodoPago" :key="index">
                        <td>{{ metodo.label }}</td>
                        <td> <input type="number" step="0.01" class="form-control" v-model="metodo.monto"> </td>
                    </tr>
                </tbody>
            </table>
            <template #modal-footer>
                <b-button variant="success" @click="finalizar()">Finalizar</b-button>
                <b-button variant="danger">Cancelar</b-button>

            </template>
      </b-modal>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import Swal from "sweetalert2";
export default {
  data() {
    return {
      finalizarFlag: false,
      perPage: 5,
      currentPage: 1,
      API: config.API,
      lstRepartos: [],
      headers: [
        { key: "Correlativo", thStyle: { width: "5%" } },
        { key: "DniRuc", label: "Documento", thStyle: { width: "10%" } },
        { key: "Cliente", thStyle: { width: "20%" } },
        { key: "Dirección", thStyle: { width: "20%" } },
        {
          key: "FechaHoraEntrega",
          label: "Fecha Entrega",
          thStyle: { width: "10%" }
        },
        { key: "DetalleProductos", thStyle: { width: "35%" } },
        { key: "action", label: "", thStyle: { width: "10%" } }
      ],
      item: {},
      metodoPago: [
        { label: "Efectivo", monto: 0 },
        { label: "Yape", monto: 0 },
        { label: "Plin", monto: 0 },
        { label: "Tarjeta", monto: 0 }
      ]
    };
  },
  mounted() {
    this.obtenerPedidosAsignado();
  },
  methods: {
    async obtenerPedidosAsignado() {
      this.lstRepartos = [];
      await this.axios(`${this.API}listado/entregas_por_repatidor`, {
        params: {
          IdRepatidor: sessionStorage.getItem("User")
        }
      }).then(res => {
        if (res) {
          this.lstRepartos = res.data;
        }
      });
    },
    abrirModalFinalizar(producto) {
      this.item = producto;
      this.finalizarFlag = true;
    },
    async finalizar() {
      let montoPagar = this.item.detalle.reduce((sum, producto) => {
        return sum + parseFloat(producto.Cantidad * producto.Precio);
      }, 0);

      let montoPagado = this.metodoPago.reduce((sum, metodo) => {
        return sum + parseFloat(metodo.monto);
      }, 0);

      if (!(montoPagar == montoPagado)) {
        this.$toasted.show("Los Monto no Coinciden");
        return false;
      }
      let data = {
        IdDocVenta: this.item.idDocVenta,
        MedoPago: this.metodoPago
      };

      await this.axios.post(`${this.API}finalizar_entrega`, data).then(res => {
        if (res) {
          this.$swal({
            icon: "success",
            title: "Confirmado",
            text: "Pedido Finalizado",
            allowEnterKey: false,
            allowEscapeKey: false,
            allowEscapeKey: false
          }).then(res => {
            if (res.isConfirmed) {
              this.finalizarFlag = false;
              this.obtenerPedidosAsignado();
            }
          });
        }
      });
    }
  }
};
</script>

<style>
</style>
