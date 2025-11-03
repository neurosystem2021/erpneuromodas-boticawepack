<template>
  <b-container class="px-5 pt-5">
    <b-row>
        <b-col cols="12">
            <center>
                <h2>INVERSIONES CASPRO S.C.R.L</h2>
                <h6> FECHA Y HORA: {{ venta.FechaReg }}</h6>
            </center>
        </b-col>
        <b-col cols="6" class="my-2">
            <center>
                <b>SERIE:</b> {{ venta.Serie }} - <b>NUMERO:</b> {{ venta.Numero }}
            </center>
        </b-col>
        <b-col cols="6" class="my-2">
             <center>
                <b>FORMA PAGO</b>: {{ venta.EsCredito ==1?'CRÉDITO' : "CONTADO"}}
            </center>
        </b-col>
        <b-col cols="6" class="my-2">
            <b>RUC:</b> {{ venta.DniRuc }}    
        </b-col>
        <b-col cols="6" class="my-2">
            <b>VENDEDOR:</b> {{ venta.NombreUsuario }}
        </b-col>
        <b-col cols="12" class="my-2">
            <b>CLIENTE:</b> {{ venta.Cliente }}
        </b-col>
        <b-col cols="12" class="my-2">
            <b>NOMBRE:</b> <span>_______________________________________________________________________________________________________________________</span>
        
        </b-col>
        <b-col cols="6" class="my-2">
            <b>DNI:</b> <span>_________________________________________________________</span>
        
        </b-col>
        <b-col cols="6" class="my-2">
            <b>FIRMA:</b> <span>_____________________________________________________</span>
        
        </b-col>

        <b-col cols="12 mt-4">
            <table width="100%">
                <thead>
                    <tr >
                        <th>ITEM</th>
                        <th>CODIGO</th>
                        <th>PRODUCTO</th>
                        <th>CANTIDAD</th>
                        <th>SUCURSAL</th>
                        <th>IMPORTADO</th>
                        <th>UBICACION</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(det,index) in detalle" :key="index">
                        <td>{{index+1}}</td>
                        <td>{{det.CodigoFrabricante}}</td>
                        <td>{{det.Producto}}</td>
                        <td>{{ det.Cantidad }}</td>
                        <td>{{ det.CantidadSucursal }}</td>
                        <td>{{ det.CantidadImportacion }}</td>
                        <td>{{det.Anaquel ? det.Anaquel : '- -'}}</td>
                    </tr>
                    <tr>
                        <th colspan="3">TOTAL</th>
                        <th>{{ parseFloat(detalle.reduce((total,det)=>{return total +parseFloat(det.Cantidad)},0)).toFixed(2)  }}</th>
                        <th>{{ parseFloat(detalle.reduce((total,det)=>{return total +parseFloat(det.CantidadSucursal)},0)).toFixed(2)  }}</th>
                        <th>{{ parseFloat(detalle.reduce((total,det)=>{return total +parseFloat(det.CantidadImportacion)},0)).toFixed(2)  }}</th>
                    </tr>
                </tbody>
            </table>
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      IdDocVenta: 0,
      venta: {},
      detalle: []
    };
  },
  async mounted() {
    this.IdDocVenta = this.$route.params.id;

    await this.obtenerDatos();
    setTimeout(() => {
      window.print();
    }, 500);
  },
  methods: {
    async obtenerDatos() {
      //   console.log(this.IdDocVenta);
      await this.axios(`${this.API}/shipment`, {
        params: { IdDocVenta: this.IdDocVenta }
      }).then(res => {
        let data = res.data;
        this.venta = data.venta;
        this.detalle = data.detalle;
      });
    }
  }
};
</script>

<style>
</style>
