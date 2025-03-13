<template>
  <b-container fluid>
    <center><h2>Reporte Entrega de Pedido</h2></center>
    <b-row>
        <b-col cols="3">
            Desde:
            <input type="date" class="form-control" v-model="desde">
        </b-col>
        <b-col cols="3">
            Hasta:
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
            :disabled="idPerfil != 1"
            ></b-select>
            
        </b-col>
        <b-col cols="3">
            <b-button variant="success" @click="obtenerListado()">Generar</b-button>
            <b-button variant="danger" @click="exportar()">Exportar</b-button>
        </b-col>
        <br>
        <b-col cols="2" class="mt-2">
          <b-button variant="info" @click="mostrarResumeflag=!mostrarResumeflag">
            <span v-if="!mostrarResumeflag">Ver Resumen</span>
            <span v-else>Ocultar Resumen</span>
          </b-button>
        </b-col>
        <b-col cols="5" class="mt-2" v-if="mostrarResumeflag">
          <h3>Resumen Método Pago:</h3>
         
          <table class="table" style="max-width: 80%;">
            <thead>
              <tr>
                <th style="text-align: center;">Método Pago</th>
                <th style="text-align: center;">Cantidad</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="resumen.Efectivo>0">
                <td >Efectivo</td>
                <td style="text-align: center;">{{ resumen.Efectivo }}</td>
              </tr>
              <tr v-if="resumen.Yape>0">
                <td >Yape</td>
                <td style="text-align: center;">{{ resumen.Yape }}</td>
              </tr>
              <tr v-if="resumen.Plin>0">
                <td >Plin</td>
                <td style="text-align: center;">{{ resumen.Plin }}</td>
              </tr>
              <tr v-if="resumen.Tarjeta>0">
                <td >Tarjeta</td>
                <td style="text-align: center;">{{ resumen.Tarjeta }}</td>
              </tr>
              <tr>
                <th>Total:</th>
                <th> {{ parseFloat(resumen.Efectivo) + parseFloat(resumen.Yape) + parseFloat(resumen.Plin) + parseFloat(resumen.Tarjeta) }}</th>
              </tr>
            </tbody>
          </table>
          </b-col>

        <b-col cols="5" class="mt-2" v-if="mostrarResumeflag">
          <h3>Resumen Deuda:</h3>
         
          <table class="table" style="max-width: 80%;">
            <tbody>
              <tr>
                <td >Monto a Cobrar</td>
                <td style="text-align: center;">{{ resumenDeuda.monto }}</td>
              </tr>
              
              <tr >
                <td >Cobrado</td>
                <td style="text-align: center;">{{ montoCobrado }}</td>
              </tr>
              <tr>
                <th>Por Cobrar:</th>
                <th style="text-align: center;">{{  resumenDeuda.monto - montoCobrado }}</th>
              </tr>
            </tbody>
          </table>
          </b-col>
        <b-col cols="12">
          <h2>Detalle</h2>
            <b-table :items="lstReporte" :fields="headers" small>
                <template slot="acuentaflag" slot-scope="data">
                  {{ data.item.acuentaflag ==1 ? 'A Cuenta' : 'Completo' }}
                  </template>
                <template slot="action" slot-scope="data">
                  <b-button v-if="mostrarCompletarPago(data.item)" @click="abrirPagarDeuda(data.item)" variant="success"> Cobrar Deuda </b-button>
                </template>
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
                        <tr>
                          <th>Total:</th>
                          <th> {{ data.item.metodospago.reduce((suma,metodo)=>{
                            return suma + parseFloat(metodo.monto)
                          },0) }} </th>
                        </tr>
                    </table>
                </template> 
            </b-table>
        </b-col>
    </b-row>
    <b-modal v-model="pagarDeuda" :title="`Cobrar Deuda`"  hide-footer >
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
                        <td> <b-checkbox value="1" unchecked-value="0" size="lg" v-model="metodo.selected" @change="cambiarMonto(metodo)"></b-checkbox>  </td>
                        <td> <input type="number" step="0.01" class="form-control" v-model="metodo.monto"> </td>
                    </tr>
                    
                </tbody>
            </table>
            <b-button variant="success" @click="guardarCobrarDeuda()">Cobrar</b-button>
            <b-button variant="danger" @click="pagarDeuda=!pagarDeuda" > Cerrar</b-button>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      mostrarResumeflag: false,
      pagarDeuda: false,
      lstReporte: [],
      repartidor: [],
      montoCobrado: 0,
      headers: [
        { thStyle: { width: "10%" }, key: "Repartidor" },
        { thStyle: { width: "5%" }, key: "Correlativo" },
        { thStyle: { width: "10%" }, key: "DniRuc", label: "Doc." },
        { thStyle: { width: "12%" }, key: "Cliente" },
        { thStyle: { width: "15%" }, key: "Dirección" },
        { thStyle: { width: "5%" }, key: "MontoPago", label: "Monto" },
        { thStyle: { width: "5%" }, key: "deuda", label: "Deuda" },
        { thStyle: { width: "12%" }, key: "created_at", label: "Hora Entrega" },
        {
          thStyle: { width: "5%" },
          key: "acuentaflag",
          label: "Tipo"
        },
        { thStyle: { width: "26%" }, key: "MedotoPago", label: "Metodos Pago" },
        { key: "action", label: "" }
      ],
      headersResumen: [{ key: "MetodoPago" }, { key: "Monto" }],
      desde: "",
      hasta: "",
      IdRepartidor: "",
      idPerfil: sessionStorage.getItem("IdPerfil"),
      resumen: {
        Efectivo: 0,
        Yape: 0,
        Plin: 0,
        Tarjeta: 0
      },
      resumenDeuda: {
        monto: 0,
        pagado: 0
      },
      MondoDeuda: 0,
      metodoPago: [
        { label: "Efectivo", monto: 0 },
        { label: "Yape", monto: 0 },
        { label: "Plin", monto: 0 },
        { label: "Tarjeta", monto: 0 }
      ],
      item: {},
      montoPagado: 0
    };
  },
  mounted() {
    // this.desde = "2025-01-01";
    this.desde = this.$moment().format("YYYY-MM-DD");
    this.hasta = this.$moment().format("YYYY-MM-DD");

    this.obtenerRepartidor();
    // if (this.idPerfil != 1) {
    this.IdRepartidor = sessionStorage.getItem("User");
    // }
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
            let monto = item.MontoPago;
            let medotodospago = JSON.parse(item.metodospago);
            let sum = medotodospago.reduce((suma, metodo) => {
              return suma + parseFloat(metodo.monto);
            }, 0);
            // console.log(sum);
            return {
              ...item,
              deuda: parseFloat(monto).toFixed(2) - parseFloat(sum).toFixed(2),
              metodospago: medotodospago
            };
          });
        }
      });
      this.generarResumenRerpote();
    },
    mostrarCompletarPago(item) {
      let monto = item.MontoPago;
      let sum = item.metodospago.reduce((suma, metodo) => {
        return suma + parseFloat(metodo.monto);
      }, 0);
      return monto > sum;
    },
    abrirPagarDeuda(item) {
      this.item = item;
      let monto = item.MontoPago;
      this.montoPagado = item.metodospago.reduce((suma, metodo) => {
        return suma + parseFloat(metodo.monto);
      }, 0);
      this.MondoDeuda = monto - this.montoPagado;
      this.metodoPago = [
        { label: "Efectivo", monto: this.MondoDeuda, selected: 1 },
        { label: "Yape", monto: 0, selected: 0 },
        { label: "Plin", monto: 0, selected: 0 },
        { label: "Tarjeta", monto: 0, selected: 0 }
      ];
      this.pagarDeuda = true;
    },
    cambiarMonto(metodos) {
      this.metodoPago.forEach(metodo => {
        if (metodo.label == metodos.label) {
          metodo.monto = this.MondoDeuda;
          metodo.selected = 1;
        } else {
          metodo.monto = 0;
          metodo.selected = 0;
        }
        // console.log(metodos.;
      });
      // console.log(metodo);
    },
    async guardarCobrarDeuda() {
      let preMetodo = this.item.metodospago;
      let nuevoCobro = [];
      for (let index = 0; index < preMetodo.length; index++) {
        nuevoCobro.push({
          label: preMetodo[index].label,
          monto:
            parseFloat(preMetodo[index].monto) +
            parseFloat(this.metodoPago[index].monto)
        });
      }
      let data = {
        ...this.item,
        cobro: nuevoCobro
      };
      await this.axios
        .post(`${this.API}HistorialEntrega/actualizarcobro`, data)
        .then(async res => {
          await this.obtenerListado();
          await this.generarResumenRerpote();
          this.pagarDeuda = false;
        });
      window.location.reload();
    },
    generarResumenRerpote() {
      this.lstReporte.forEach(reporte => {
        reporte.metodospago.forEach(metodo => {
          if (metodo.label == "Efectivo") {
            this.resumen.Efectivo += parseFloat(metodo.monto);
          }
          if (metodo.label == "Yape") {
            this.resumen.Yape += parseFloat(metodo.monto);
          }
          if (metodo.label == "Plin") {
            this.resumen.Plin += parseFloat(metodo.monto);
          }
          if (metodo.label == "Tarjeta") {
            this.resumen.Tarjeta += parseFloat(metodo.monto);
          }
        });

        this.resumenDeuda.monto = 0;
        this.resumenDeuda.monto = this.lstReporte.reduce((sum, rep) => {
          return sum + parseFloat(rep.MontoPago);
        }, 0);
        this.montoCobrado =
          parseFloat(this.resumen.Efectivo) +
          parseFloat(this.resumen.Yape) +
          parseFloat(this.resumen.Plin) +
          parseFloat(this.resumen.Tarjeta);
      });
    },
    async exportar() {
      await this.axios(`${this.API}reporte/entrega_pedido/exportar`, {
        params: {
          desde: this.desde,
          hasta: this.hasta,
          IdRepartidor: this.IdRepartidor,
          resumen: this.resumen,
          resumenDeuda: this.resumenDeuda,
          montoCobrado: this.montoCobrado
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
