<template>
	<b-container >
    <b-row>
      <b-col cols="4">
        <div id="logo">
          <img src="/resources/images/banner-proforma.jpg">
        </div>
      </b-col>
      <b-col cols="8" >
        <div style="border-right: 1px solid #000000;
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 1% 2%;
            margin: 1% 0;"
        >
             <b-row>
              <b-col cols="6">
                <div style="text-align: center; align-items: center; align-content: center;">
                  <p> <b> COTIZACIÓN :</b> <span style="color:red; font-weight: bold; text-align: center;">N° 0000000-  {{ proforma.Numero}} </span></p>
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
      <b-col cols="4" style="text-align: left;">
            <p class="my-0 py-0" style="font-weight: bold;" > RUC: 20601250501 </p>
            <p class="my-0 py-0" style="font-weight: bold;" > INVERSIONES CASPRO S.R.L. </p>
            <p class="my-0 py-0"> 
              MZA. C LOTE. 1 URB. LOS CIPRECES (A UNA CUADRA DE MOPAL) HUANUCO - HUANUCO - AMARILIS
            </p>
            <p>
              949434255/ 998811867/ 986779444/ 949658603
            </p>
      </b-col>
      <b-col cols="8" style="text-align: center; align-items: center;align-content: center;">
        REPUESTOS ORIGINALES Y ALTERNATIVOS
        PARA MAQUINARIA PESADA
      </b-col>
    </b-row>

    <b-row>
        <b-col cols="12" >
          <div style="border: 1px solid #000000 !important; padding: 1% 2%; margin: 1% 0;">

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
                <td style="text-align: left !important;"></td>
              </tr>
            </tbody>
          </table>
        </div>
        </b-col>
    </b-row>
		
    <b-row>
      <b-col cols="12" >
      <div>
      <table>
        <thead>
          <tr style="border-bottom: 1px solid #000000">
            <th style="border: 1px solid #000000;">#</th>
            <th style="border: 1px solid #000000;">CÓD. INTERNO </th>
            <th style="border: 1px solid #000000;">DESCRIPCIÓN</th>
            <th style="border: 1px solid #000000;">UBICACIÓN</th>
            <th style="border: 1px solid #000000;">MARCA</th>
            <th style="border: 1px solid #000000;">UNIDAD</th>
            <th style="border: 1px solid #000000;">CANT.</th>
            <th style="border: 1px solid #000000;">CANT. SUCURSAL</th>
            <th style="border: 1px solid #000000;">CANT. IMPORTACIÓN</th>
            <th style="border: 1px solid #000000;">PRECIO U.</th>
            <th style="border: 1px solid #000000;">TOTAL CANT.</th>
            <th style="border: 1px solid #000000;">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <!-- {{ prod }} -->
            <td style="border: 1px solid #000000; ">{{ index+1 }}</td>
            <td style="border: 1px solid #000000; "><h3>{{ prod.CodigoInterno }}</h3></td>
            <td style="border: 1px solid #000000; text-align: left;"><h3>{{ prod.Producto }}</h3></td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.Anaquel }}</h3></td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.ProductoMarca }}</h3></td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.ProductoMedicion }}</h3></td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;">{{ prod.Cantidad }}</td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;">{{ prod.CantidadSucursal }}</td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;">{{ prod.CantidadImportacion }}</td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;"> {{ prod.Precio | moneda }}</td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;">{{ prod.CantidadSucursal + parseFloat(prod.CantidadImportacion) }}</td>
            <td style="border: 1px solid #000000; text-align: center !important; align-content: center; align-items: center;">
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
              <td>{{ proforma.DiasValidos }} Días</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> TIEMPO DE ENTREGA: SUCURSAL: </b></td>
              <td>{{ maxDiaEntrega }} Días</td>
            </tr>
            <tr>
              <td style="text-align: left;"> <b> TIEMPO DE ENTREGA IMPORTACION: </b></td>
              <td>{{ maxDiaEntregaImportacion }} Días</td>
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
              <td style="text-align: left;"> <b> OBSERVACION : </b></td>
            </tr>
          </tbody>
        </table>
      </b-col>
      <b-col cols="4"></b-col>
      <b-col cols="4">
        <div style="border: 1px solid #000000 !important; padding: 1% 2%; margin: 1% 0;">

        <table width="100%">
          <tr>
            <th colspan="2">SUBTOTAL</th>
            <td>{{ subTotal | redondear }}</td>
          </tr>
          <tr v-if="proforma.IGV==1">
            <td colspan="1">18.00%</td>
            <td colspan="1">I.G.V</td>
            <td>{{ subTotal * 0.18 | redondear }}</td>
          </tr>
          <tr v-if="mostrarDolar">
            <th colspan="2">TOTAL DOLARES</th>
            <td>{{ total | redondear }}</td>
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
      <b-col cols="12">
        <table width="100%" style="border: 1px solid #000000 !important">
          <thead>
            <tr>
              <th>BANCO</th>
              <th>MONEDA</th>
              <th>CUENTA</th>
              <th>CCI</th>
            </tr>
          </thead>
            <tbody>
              <tr>
                <td rowspan="2">
                  <img src="/resources/images/bcp_logo.png" style="max-width: 120px" />
                </td>
                <td>USD</td>
                <td>365-2495697-1-36</td>
                <td>00236500249569713650</td>
              </tr>
              <tr>
                <td>SOLES</td>
                <td>365-2563145-0-20</td>
                <td>0023650025631452059</td>
              </tr>
              <tr>
                <td rowspan="2">
                  <img src="/resources/images/bbva_logo.png" style="max-width: 120px" />
                </td>
                <td>USD</td>
                <td></td>
                <td></td>
              </tr>
              <tr>
                <td>SOLES</td>
                <td>001102100100065438</td>
                <td>01121000010006543822</td>
              </tr>
            </tbody>
        </table>
      </b-col>    
    </b-row>
    <b-row>
       <b-col cols="12" class="mt-5">
        <img src="/resources/images/qr_yape.jpg" style="max-width: 120px">
        <p>949 434 255 </p>
        Inversiones Caspro SCRL
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
    this.fechaHoy = this.$moment().format("YYYY-MM-DD HH:mm:ss");
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

      return sumatoria + sumatoria * (this.proforma.IGV == 1 ? 0.18 : 0);
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
  border: 1px solid #000000;
  border-radius: 10px;
  padding: 2% 2% !important;
  height: 100px;
  /* text-align: end; */
  align-content: center;
  align-items: center;
}
.clsTable {
  border: 1px solid #000000;
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
  border: 1px solid #000000;
  margin: 20px 0 0 0;
  border-radius: 15px;
}
.clsPeso {
  border: 1px solid #000000;
  margin: 20px 0 0 0;
  padding: 2% 5%;
  border-radius: 15px;
}
</style>
