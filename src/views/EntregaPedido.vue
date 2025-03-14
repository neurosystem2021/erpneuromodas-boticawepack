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
                            <td>{{ parseFloat(detalle.Cantidad * detalle.Precio).toFixed(2) }}</td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <th>{{ parseFloat(data.item.detalle.reduce((sum,producto)=>{
                                return sum+parseFloat(producto.Cantidad)
                            },0) ).toFixed(2)
                            }}</th>
                            <th>{{ parseFloat(data.item.detalle.reduce((sum,producto)=>{
                                return sum+parseFloat(producto.Precio)
                            },0) ).toFixed(2)
                            }}</th>
                            <th>{{ parseFloat(data.item.detalle.reduce((sum,producto)=>{
                                return sum+parseFloat(producto.Cantidad*producto.Precio)
                            },0)).toFixed(2)
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
      
    </b-row>

    <!-- ddd -->
    <b-modal v-model="finalizarFlag" hide-footer :title="`Finalizar Pedido ${item.Correlativo}`">
            <table class="table">
                <thead>
                    <tr>
                      <th width="35%">Metodo de Pago</th>
                        <th width="30%"></th>
                        <th width="35%">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(metodo,index) in metodoPago" :key="index">
                        <td>{{ metodo.label }}</td>
                        <td> <b-checkbox  size="lg"  value="1" unchecked-value="0" v-model="metodo.selected" @change="cambiarMonto(metodo)"></b-checkbox>  </td>
                        <td> <input type="number" step="0.01" class="form-control" v-model="metodo.monto" @input="cambiarMontoPagar(metodo)"> </td>
                        
                    </tr>
                    <tr>
                      <td>
                        <b-checkbox size="lg"   v-model="acuentaflag" value="1" unchecked-value="0">A Cuenta</b-checkbox>
                      </td>

                    </tr>
                </tbody>
            </table>
            <template #modal-footer>
                <b-button variant="success" @click="finalizar()">Finalizar</b-button>
                <b-button variant="danger" @click="finalizarFlag=!finalizarFlag">Cancelar</b-button>

            </template>
      </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import Swal from "sweetalert2";
export default {
  data() {
    return {
      API: config.API,
      finalizarFlag: false,
      perPage: 5,
      currentPage: 1,
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
      ],
      acuentaflag: 0,
      montoPagar: 0
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
      this.montoPagar = parseFloat(
        this.item.detalle.reduce((sum, producto) => {
          return sum + parseFloat(producto.Cantidad * producto.Precio);
        }, 0)
      ).toFixed(2);

      this.metodoPago = [
        { label: "Efectivo", monto: this.montoPagar, selected: 1 },
        { label: "Yape", monto: 0, selected: 0 },
        { label: "Plin", monto: 0, selected: 0 },
        { label: "Tarjeta", monto: 0, selected: 0 }
      ];
    },
    cambiarMonto(metodos) {
      this.metodoPago.forEach(metodo => {
        if (metodo.label == metodos.label) {
          metodo.monto = this.montoPagar;
          metodo.selected = 1;
        } else {
          metodo.monto = 0;
          metodo.selected = 0;
        }
        // console.log(metodos.;
      });
      // console.log(metodo);
    },
    cambiarMontoPagar(metodos) {
      if (metodos.label != "Efectivo") {
        let metodo = this.metodoPago.filter(v => v.label == "Efectivo");
        metodo[0].monto = 0;
        metodo[0].selected = 0;
        let newMetodo = this.metodoPago.filter(v => v.label == metodos.label);
        newMetodo[0].selected = 1;
      }
    },
    async finalizar() {
      if (this.acuentaflag == 0) {
        let montoPagar = parseFloat(
          this.item.detalle.reduce((sum, producto) => {
            return sum + parseFloat(producto.Cantidad * producto.Precio);
          }, 0)
        ).toFixed(2);

        let montoPagado = parseFloat(
          this.metodoPago.reduce((sum, metodo) => {
            return sum + parseFloat(metodo.monto);
          }, 0)
        ).toFixed(2);

        if (!(montoPagar == montoPagado)) {
          this.$toasted.show("Los Monto no Coinciden");
          return false;
        }
      } else {
        let montoPagar = parseFloat(
          this.item.detalle.reduce((sum, producto) => {
            return sum + parseFloat(producto.Cantidad * producto.Precio);
          }, 0)
        ).toFixed(2);

        let montoPagado = parseFloat(
          this.metodoPago.reduce((sum, metodo) => {
            return sum + parseFloat(metodo.monto);
          }, 0)
        ).toFixed(2);
        if (parseFloat(montoPagar) < parseFloat(montoPagado)) {
          this.$toasted.show("Los Montos no pueden ser mayores");
          return false;
        }
      }
      let data = {
        IdDocVenta: this.item.idDocVenta,
        acuentaflag: this.acuentaflag,
        MedoPago: this.metodoPago
      };

      await this.axios.post(`${this.API}finalizar_entregas`, data).then(res => {
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
              this.metodoPago = [
                { label: "Efectivo", monto: 0 },
                { label: "Yape", monto: 0 },
                { label: "Plin", monto: 0 },
                { label: "Tarjeta", monto: 0 }
              ];
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
