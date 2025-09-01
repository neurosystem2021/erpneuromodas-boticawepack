<template>
	<b-container >
    <b-row>
      <b-col cols="4">
        <div id="logo">
          <img src="/resources/images/banner-proforma.jpg">
        </div>
      </b-col>
      <b-col cols="8" >
        <div style="border-right: 5px solid #E0E0E0;
            border-top: 5px solid #E0E0E0;
            border-bottom: 5px solid #E0E0E0;
            padding: 1% 2%;
            margin: 1% 0;"
        >
             <b-row>
              <b-col cols="6">
                <div>
                  <p> <b> COTIZACIÓN :</b> N° 0000000-{{ proforma.Numero}}</p>
                  <p> <b> FECHA EMISIÓN </b>{{ fechaHoy }}</p>
                </div>
              </b-col>
              
              <b-col cols="6">
                <img src="/resources/images/cat_logo.png" style="max-width: 50px;">
                <img src="/resources/images/ctp_logo.png" style="max-width: 50px;">
              </b-col>         
            </b-row>
        </div>
       
      </b-col>
    </b-row>
    <b-row>
      <b-col cols="6" style="text-align: center;">
            <p class="my-0 py-0" style="font-weight: bold;" > RUC: 20601250501 </p>
            <p class="my-0 py-0" style="font-weight: bold;" > INVERSIONES CASPRO S.R.L. </p>
            <p class="my-0 py-0"> 
              MZA. C LOTE. 1 URB. LOS CIPRECES (A UNA CUADRA DE MOPAL) HUANUCO - HUANUCO - AMARILIS
            </p>
      </b-col>
      <b-col cols="6" style="text-align: center; align-items: center;align-content: center;">
        REPUESTOS ORIGINALES Y ALTERNATIVOS
        PARA MAQUINARIA PESADA
      </b-col>
    </b-row>

    <b-row>
        <b-col cols="12" >
          <div style="border: 5px solid #E0E0E0 !important; padding: 1% 2%; margin: 1% 0;">

            <table width="100%">
              <tbody>
                <tr>
                  <td style="text-align: left !important;" colspan="3"><b>SEÑOR(ES):</b></td>
                  <td style="text-align: left !important;" colspan="3">{{ proforma.Cliente }}</td>
                </tr>
                <tr>
                  <td style="text-align: left !important;" colspan="3"><b>DIRECCIÓN:</b></td>
                  <td style="text-align: left !important;" colspan="3">{{ proforma.ClienteDireccion}}</td>
                </tr>
                <tr>
                  <td style="text-align: left !important;"><b>DOCUMENTO</b></td>
                <td style="text-align: left !important;">{{ proforma.Documento }}</td>
                <td style="text-align: left !important;"><b>ATENCIÓN</b></td>
                <td style="text-align: left !important;">{{ proforma.UsuarioReg }}</td>
                <td style="text-align: left !important;"><b>TELÉFONO</b></td>
                <td style="text-align: left !important;">{{ proforma.UsuarioReg }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        </b-col>
    </b-row>
		
    <b-row>
      <b-col cols="12" >
      <div style="border: 5px solid #E0E0E0 !important; padding: 1%; margin: 1% 0;">
      <table>
        <thead>
          <tr style="border-bottom: 2px solid #E0E0E0">
            <th >#</th>
            <th >CÓDIGO</th>
            <th >DESCRIPCIÓN</th>
            <th >UBICACIÓN</th>
            <th >MARCA</th>
            <th >UNIDAD</th>
            <th >CANT.</th>
            <th >CANT. SUCURSAL</th>
            <th >CANT. IMPORTACIÓN</th>
            <th >PRECIO U.</th>
            <th >TOTAL CANT.</th>
            <th >TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <!-- {{ prod }} -->
            <td style="">{{ index+1 }}</td>
            <td style=""><h3>{{ prod.CodigoInterno }}</h3></td>
            <td style=""><h3>{{ prod.Producto }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.Anaquel }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.ProductoMarca }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.ProductoMedicion }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;">{{ prod.Cantidad }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;">{{ prod.CantidadSucursal }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;">{{ prod.CantidadImportacion }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;"> {{ prod.Precio | moneda }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;">{{ prod.CantidadSucursal + parseFloat(prod.CantidadImportacion) }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;">
              <div v-if="prod.DisabledTotal === 0">
                 {{ prod.Precio * (parseFloat(prod.CantidadSucursal) +parseFloat(prod.CantidadImportacion)) - prod.Descuento | redondear }}<br>
                <span v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
        
      </table>
      </div>
      </b-col>
    </b-row>
      
    <b-row>
      <b-col cols="4">
        <p><b><u>CONDICIONES COMERCIALES</u></b></p>
       
        <table>
          <tbody>
            <tr>
              <td style="text-align: left;"> <b> VALIDEZ DE OFERTA : </b></td>
              <td>{{ proforma.DiasValidos }}</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> TIEMPO DE ENTREGA: SUCURSAL: </b></td>
              <td>{{ maxDiaEntrega }}</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> TIEMPO DE ENTREGA IMPORTACION: </b></td>
              <td>{{ maxDiaEntregaImportacion }}</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> FORMA DE PAGO : </b></td>
              <td>CONTADO</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> GARANTIA :  </b></td>
              <td>{{ proforma.Garantia }}</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> TELEFONO: </b></td>
              <td> 949434255/ 998811867/ 986779444/ 949658603 </td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> OBSERVACION : </b></td>
            </tr>
          </tbody>
        </table>
      </b-col>
      <b-col cols="4"></b-col>
      <b-col cols="4">
        <div style="border: 5px solid #E0E0E0 !important; padding: 1% 2%; margin: 1% 0;">

        <table width="100%">
          <tr>
            <th colspan="2">SUBTOTAL</th>
            <td>USD {{ subTotal | redondear }}</td>
          </tr>
          <tr>
            <td colspan="1">18.00%</td>
            <td colspan="1">I.G.V</td>
            <td>USD {{ subTotal * 0.18 | redondear }}</td>
          </tr>
          <tr v-if="mostrarDolar">
            <th colspan="2">TOTAL DOLARES</th>
            <td>USD {{ total | redondear }}</td>
          </tr>

          <tr v-else>
            <th colspan="2">TOTAL SOLES</th>
            <td>S/. {{ total *proforma.TipoCambio| redondear }}</td>
          </tr>
        </table>
        </div>
      </b-col>
    </b-row>

    <b-row>
      <b-col>
          <table class="clsTable">
            <tbody>
              <tr>
                <td rowspan="1"  style="text-align: center !important;">
                  <img src="/resources/images/bcp_logo.png" style="max-width: 120px">
                </td> 
                <td>
                  <p> USD 365-2495697-1-36 / 00236500249569713650 </p>
                  <p> S/. 365-2563145-0-20 / 0023650025631452059 </p>
                </td>

                <td rowspan="1"  style="text-align: center !important;">
                   <img src="/resources/images/bbva_logo.png" style="max-width: 120px">
                </td>
                <td>
                  <p> S/. 001102100100065438 </p>
                  <p> USD 001102100100065438 </p>
                </td>
              </tr>
              <tr>
                <td  colspan="2"  style="text-align: center !important;">
                 <img src="/resources/images/yape.png" style="max-width: 60px">
                </td>
                <td colspan="2">
                  <p>949 434 255 </p>
                   Inversiones Caspro SCRL
                </td>
              </tr>
            </tbody>
        </table>
      </b-col>
    </b-row>

    <b-row>
      <div style="align-items: center; text-align: center; align-content: center;">
        <center>
          <b-button variant="success" @click="imprimir">Imprimir </b-button>
          <b-button variant="success" @click="enviarWsp">Enviar Wsp </b-button>
        </center>

      </div>
    </b-row>

      </b-container>
</template>

<script>
import { API } from "@/store/config.js";
export default {
  mounted() {
    this.fechaHoy = this.$moment().format("YYYY-MM-DD");
    this.getProforma();
    this.getEmpresa();
  },
  data() {
    return {
      API: API,
      idProforma: this.$route.params.id,
      proforma: { productos: [] },
      empresa: {},
      fechaHasta: "",
      fechaHoy: "",
      DiasEntregaImportacion: []
    };
  },
  computed: {
    subTotal() {
      let sumatoria = 0;
      this.proforma.productos.forEach(prod => {
        sumatoria +=
          (parseFloat(prod.CantidadSucursal) +
            parseFloat(prod.CantidadImportacion)) *
            prod.Precio -
          prod.Descuento;
      });
      return sumatoria;
    },
    total() {
      let sumatoria = 0;
      this.proforma.productos.forEach(prod => {
        sumatoria +=
          (parseFloat(prod.CantidadSucursal) +
            parseFloat(prod.CantidadImportacion)) *
            prod.Precio -
          prod.Descuento;
      });

      return sumatoria + sumatoria * 0.18;
    },
    mostrarDolar() {
      return this.proforma.Moneda === "USD";
    },
    maxDiaEntrega() {
      // Inicializamos el acumulador con un objeto que tenga el valor más bajo posible en DiasEntrega
      if (
        !this.proforma ||
        !this.proforma.productos ||
        this.proforma.productos.length === 0
      ) {
        return null; // O el valor que consideres apropiado para un array vacío
      }

      const dias = this.proforma.productos.map(p => p.DiasEntrega);
      return Math.max(...dias);
    },
    maxDiaEntregaImportacion() {
      if (
        !this.proforma ||
        !this.proforma.productos ||
        this.proforma.productos.length === 0
      ) {
        return null; // O el valor que consideres apropiado para un array vacío
      }

      const dias = this.proforma.productos.map(p => p.DiasEntregaImportacion);
      return Math.max(...dias);
    }
  },
  methods: {
    getProforma() {
      this.axios.get(API + "proformas/id/" + this.idProforma).then(res => {
        this.proforma = res.data;
        this.DiasEntregaImportacion = this.proforma.productos.map(v => {
          return v.DiasEntregaImportacion ? v.DiasEntregaImportacion : "";
        });
        this.fechaHasta = this.calcularFechaHasta(
          this.proforma.FechaReg.substring(0, 10),
          this.proforma.DiasValidos
        )
          .toISOString()
          .substring(0, 10);
      });
    },
    calcularFechaHasta(actual, dias) {
      var date = new Date(actual);
      date.setDate(date.getDate() + (dias - 1));
      return date;
    },
    getEmpresa() {
      this.axios.get(API + "empresa/id/" + 1).then(res => {
        this.empresa = res.data;
      });
    },
    imprimir() {
      this.axios
        .get(this.API + "proformas/imprimir?id=" + this.idProforma)
        .then(res => {
          if (res.data) {
            let url = res.data.url;
            window.open(url, "_blank");
          }
        });
      // window.print();
    },
    async enviarWsp() {
      let url = await this.axios
        .get(this.API + "proformas/imprimir?id=" + this.idProforma)
        .then(res => {
          if (res.data) {
            let url = res.data.url;
            return url;
          }
        });
      let val = prompt("Ingrese Número Telefónico");
      if (val.length == 9) {
        let wsp = `https://api.whatsapp.com/send/?phone=val&text=Puede revisar su factura en el siguiente enlace: ${url}`;
        window.open(wsp, "_blank");
      }
    }
  }
};
</script>

<style scoped>
@import "../../assets/css/invoice.css";
.celda {
  text-align: left;
  border: none !important;
  background: white !important;
}
@media print {
  #printBtn {
    display: none;
  }
}

.clsCotizacion {
  border: 1px solid #E0E0E0;
  border-radius: 10px;
  padding: 2% 2% !important;
  height: 100px;
  /* text-align: end; */
  align-content: center;
  align-items: center;
}
.clsTable {
  border: 5px solid #E0E0E0;
}
.clsTable th {
  /* border: 1px solid #E0E0E0; */
}
.clsTable td {
  text-align: start !important;
  padding: 1%;
  /* border: 1px solid #E0E0E0 !important; */
}
.clsSubTotal {
  border: 1px solid #E0E0E0;
  margin: 20px 0 0 0;
  border-radius: 15px;
}
.clsPeso {
  border: 1px solid #E0E0E0;
  margin: 20px 0 0 0;
  padding: 2% 5%;
  border-radius: 15px;
}
</style>
