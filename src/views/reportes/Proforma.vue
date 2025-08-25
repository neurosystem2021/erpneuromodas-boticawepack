<template>
	<b-container class="px-5">
    <b-row>
      <b-col cols="4" class="clsHeader">
        <p class="my-0 py-0" style="font-size: 1.5rem; color: #1A237E; font-weight: bold;" > INVERSIONES CASPRO S.R.L. </p>
        <p class="my-0 py-0"> 
            MZA. C LOTE. 1 URB. LOS CIPRECES (A UNA CUADRA DE MOPAL) HUANUCO - HUANUCO - AMARILIS
        </p>
        <p class="my-0 py-0" style="font-weight: bold;">
          <img src="/resources/images/whatsapp_logo.webp" style="max-width: 50px;">
          966 964 014 /966 975 525 /966 897 733 /966 975 686 /973 868 018 /900 636 380 /925 998 940 /926 055 821
        </p> 
      </b-col>
      <b-col cols="4">
        <div id="logo">
          <img src="/resources/images/banner-proforma.jpg">
        </div>
        
      </b-col>
      <b-col cols="4" class="clsHeader">
        <div class="clsCotizacion">
          <h3>COTIZACIÓN</h3>
           <p class="my-0 py-0" style="color: #B71C1C; font-weight: bold;">N° 0000000{{ proforma.Numero}}</p>
        </div>
         <p class="my-0 py-0" style="font-weight: bold; color: #000000;">RUC: 20601250501</p>
         <p class="my-0 py-0">{{ fechaHoy }}</p>
      </b-col>
    </b-row>
    <b-row>
      <hr />
    </b-row>

    <b-row>
        <b-col cols="6">
         
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Señores:</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.Cliente }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">RUC</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.Documento }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Dirección</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.ClienteDireccion}}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Moneda</td>
                <td class="celda" style="text-transform: uppercase">: Soles</td>
              </tr>
            </tbody>
          </table>
        </b-col>
        <b-col cols="6">
         
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Forma De Pago</td>
                <td class="celda" style="text-transform: uppercase">: CONTADO</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Validez de la Oferta</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.DiasValidos }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Plazo de la Importación</td>
                <td class="celda" style="text-transform: uppercase">: {{ DiasEntregaImportacion.join() }} </td>
              </tr>
              
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Garantía de Referencia</td>
                <td class="celda" style="text-transform: uppercase">: {{proforma.Garantia}}</td>
              </tr>
              <!--<tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>-->
            </tbody>
          </table>
        </b-col>
        <div id="details">
        
        
      </div>
    </b-row>
		
    <b-row>
       <table class="clsTable">
        <thead>
          <tr>
            <th>#</th>
            <th>CÓDIGO</th>
            <th>DESCRIPCIÓN</th>
            <th>UBICACIÓN</th>
            <th>MARCA</th>
            <th>UNIDAD</th>
            <th>CANT.</th>
            <th>PRECIO U.</th>
            <th>TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td>{{ index+1 }}</td>
            <td><h3>{{ prod.CodigoInterno }}</h3></td>
            <td><h3>{{ prod.Producto }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.Anaquel }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.ProductoMarca }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;"><h3>{{ prod.ProductoMedicion }}</h3></td>
            <td style="text-align: center !important; align-content: center; align-items: center;">{{ prod.Cantidad }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;"> {{ prod.Precio | moneda }}</td>
            <td style="text-align: center !important; align-content: center; align-items: center;">
              <div v-if="prod.DisabledTotal === 0">
                 {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
                <span v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
        
      </table>
    </b-row>
      
    <b-row>
      <b-col cols="8"></b-col>
      <b-col cols="4" class="clsSubTotal">
        <table>
          <tr>
            <th colspan="2">SUBTOTAL</th>
            <td>USD {{ subTotal | redondear }}</td>
          </tr>
          <tr>
            <td colspan="1">18.00%</td>
            <td colspan="1">I.G.V</td>
            <td>USD {{ subTotal * 0.18 | redondear }}</td>
          </tr>
          <tr>
            <th colspan="2">TOTAL DOLARES</th>
            <td>USD {{ total | redondear }}</td>
          </tr>
          <tr>
            <td colspan="2" style="text-align: center;">Tipo de Cambio</td>
            <td>S/. {{ proforma.TipoCambio | redondear }}</td>
          </tr>
          <tr>
            <th colspan="2">TOTAL SOLES</th>
            <td>S/. {{ total *proforma.TipoCambio| redondear }}</td>
          </tr>
        </table>
      </b-col>
    </b-row>

    <b-row>
      <b-col cols="12">
        Cuentas de Bancos:
      </b-col>
      <b-col>
          <table class="clsTable">
          <thead>
            <tr>
              <th style="align-items: center;">Banco</th>
              <th style="align-content: center;">Moneda</th>
              <th style="align-content: center;">Cuenta</th>
              <th style="align-content: center;">CCI</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td  rowspan="2"  style="text-align: center !important;">
                 <img src="/resources/images/bcp_logo.png" style="max-width: 120px">
              </td>
              <td style="text-align: center !important;">SOLES</td>
              <td style="text-align: center !important;">191-1848632-0-49</td>
              <td style="text-align: center !important;">002 191 00184863204954</td>
            </tr>
            <tr>
              <td style="text-align: center !important;">DÓLARES</td>
              <td style="text-align: center !important;">191-1850493-1-57</td>
              <td style="text-align: center !important;">002 191 00185049315759</td>
            </tr>
            <tr>
              <td  rowspan="2"  style="text-align: center !important;">
                 <img src="/resources/images/scotiabank_logo.png" style="max-width: 120px">
              </td>
              <td style="text-align: center !important;">SOLES</td>
              <td style="text-align: center !important;">000-3839370</td>
              <td style="text-align: center !important;"></td>
            </tr>
            <tr>
              <td style="text-align: center !important;">DÓLARES</td>
              <td style="text-align: center !important;">000-4437275</td>
              <td style="text-align: center !important;"></td>
            </tr>
            <tr>
              <td  rowspan="2"  style="text-align: center !important;">
                 <img src="/resources/images/bbva_logo.png" style="max-width: 120px">
              </td>
              <td style="text-align: center !important;">SOLES</td>
              <td style="text-align: center !important;">0011-0023-0100017518</td>
              <td style="text-align: center !important;">011-023-000100017518-90</td>
            </tr>
            <tr>
              <td style="text-align: center !important;">DÓLARES</td>
              <td style="text-align: center !important;">0011-0023-0100017526</td>
              <td style="text-align: center !important;">011-023-000100017526-93</td>
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
        sumatoria += prod.Cantidad * prod.Precio - prod.Descuento;
      });
      return sumatoria;
    },
    total() {
      let sumatoria = 0;
      this.proforma.productos.forEach(prod => {
        sumatoria += prod.Cantidad * prod.Precio - prod.Descuento;
      });
      return sumatoria + sumatoria * 0.18;
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
.clsHeader {
  text-align: center;
  align-content: center;
  align-items: center;
}
.clsCotizacion {
  border: 1px solid #000000;
  border-radius: 10px;
  padding: 2% 2% !important;
  height: 100px;
  text-align: end;
  align-content: center;
  align-items: center;
}
.clsTable {
  border: 1px solid #000000;
}
.clsTable th {
  border: 1px solid #000000;
}
.clsTable td {
  text-align: start !important;
  /* padding:1% ; */
  border: 1px solid #000000 !important;
}
.clsSubTotal {
  border: 1px solid #000000;
  margin: 20px 0 0 0;
  border-radius: 15px;
}
</style>
