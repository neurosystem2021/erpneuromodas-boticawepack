<template>
	<div id="invoice-container">
		<header class="clearfix">
			<div id="logo">
				<img src="/resources/images/banner-proforma.jpg">
			</div>
			<!--<div id="company">
				<h2 class="name">{{ empresa.RAZONSOCIAL }}</h2>
				<div>{{ empresa.DIRECCION }}</div>
				<div>{{ empresa.TELEFONO }} - {{ empresa.CELULAR }}</div>
				<div><a :href="'mailto:' + empresa.EMAIL">{{ empresa.EMAIL }}</a></div>
			</div>-->
		</header>
		<main>
      <div id="details" class="clearfix">
        <div id="client">
          <br>
          <h5 class="to" style=":black">PROFORMA A:</h5>
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Cliente</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.Cliente }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Documento</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.Documento }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Dirección</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.ClienteDireccion}}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Moneda</td>
                <td class="celda" style="text-transform: uppercase">: Soles</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div id="invoice">
          <h1>PROFORMA {{ proforma.Anio }}-{{ proforma.Numero }}</h1>
          <div style="height:15px"></div>
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Válido Desde</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.FechaReg?proforma.FechaReg.substring(0,10):proforma.FechaReg }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Válido Hasta</td>
                <td class="celda" style="text-transform: uppercase">: {{fechaHasta}}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Forma De Pago</td>
                <td class="celda" style="text-transform: uppercase">: CONTADO</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Plazo De Entrega</td>
                <td class="celda" style="text-transform: uppercase">: INMEDIATO</td>
              </tr>
              <!--<tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>-->
            </tbody>
          </table>
        </div>
      </div>
      <table border="0" cellspacing="0" cellpadding="0" min-width="100%" class="tblProductos">
        <thead>
          <tr>
            <th class="total">#</th>
            <th class="qty">CANT.</th>
            <th colspan="2" class="desc">DESCRIPCIÓN</th>
            <th class="unit">U.M.</th>
            <th class="unit">PRECIO U.</th>
            <th class="total">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
           
            <td class="total">{{ index+1 }}</td>
            <td class="qty" style="font-size:1.1rem">{{ prod.Cantidad }}</td>
            <td colspan="2"  class="desc" style="font-size:0.75rem">
              <h3 style="color:#000; font-size:1rem">{{ prod.Producto }} <p v-if="presentacionProducto(prod)">({{ presentacionProducto(prod)}})</p> </h3><p v-if='prod.ProductoDesc'>{{ prod.ProductoDesc }}</p><p v-if="prod.ProductoDesc2"> {{prod.ProductoDesc2}}</p><p v-if="prod.ProductoDesc3">{{ prod.ProductoDesc3 }}</p>
            </td>
             <td class="unit" style="font-size:1.1rem">
              {{ getPresentacion(prod)}}</td>
            <td class="unit" style="font-size:1.1rem">S/. {{ prod.Precio | moneda }}</td>
            <td class="total" style="font-size:1.1rem">
              <div v-if="prod.DisabledTotal === 0">
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br><span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3"></td>
            <td colspan="2">SUBTOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
          <!--<tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td>$1,300.00</td>
          </tr>-->
          <tr>
            <td colspan="3"></td>
            <td colspan="2" style="background:#010067 ; color:#fff">TOTAL</td>
            <td style="background:#010067 ; color:#fff">S/. {{ subTotal | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <div id="accounts" style="margin-top:-10px; font-weight:bold;">
        <img style="max-width: 100%" src="/resources/images/cuentas.png" />
      </div>
      <br>
      <div id="notices">
        <div>NOTA:</div>
        <div class="notice">LAS PRODUCTOS SERAN ENTREGADOS MISMO EN EL LOCAL COMERCIAL</div>
        <div class="notice">ESTA PROFORMA SOLO TIENE VALIDEZ DE <span style="font-weight:bold">{{proforma.DiasValidos}}</span> DIAS.</div>
      </div>
      <div>
        <br>
        <br>
        <br>
        <table>
          <tbody>
            <td style="border: none !important; border-top:3px solid #FD8100; background: white !important; width:50%"></td>
            <td style="border: none !important; border-top:3px solid black ; background: white !important; width:40%; text-align:center; color:black">FIRMA/SELLO DE AUTORIZACION</td>
            <td style="border: none !important; border-top:3px solid #FD8100; background: white !important; width:10%"></td>
          </tbody>
        </table>
      </div>
      <div style="text-align:center">
        <button class="btn btn-success" id="printBtn" @click="imprimir()">IMPRIMIR</button>
      </div>
    </main>
    <!--<footer>
      Invoice was created on a computer and is valid without the signature and seal.
    </footer>-->
	</div>
</template>

<script>
import { API } from "@/store/config.js";
export default {
  mounted() {
    this.getProforma();
    this.getEmpresa();
  },
  data() {
    return {
      API: API,
      idProforma: this.$route.params.id,
      proforma: { productos: [] },
      empresa: {},
      fechaHasta: ""
    };
  },
  computed: {
    subTotal() {
      let sumatoria = 0;
      this.proforma.productos.forEach(prod => {
        sumatoria += prod.Cantidad * prod.Precio - prod.Descuento;
      });
      return sumatoria;
    }
  },
  methods: {
    getProforma() {
      this.axios.get(API + "proformas/id/" + this.idProforma).then(res => {
        this.proforma = res.data;
        this.fechaHasta = this.calcularFechaHasta(
          this.proforma.FechaReg.substring(0, 10),
          this.proforma.DiasValidos
        )
          .toISOString()
          .substring(0, 10);
      });
    },
    presentacionProducto(item) {
      if (item.presentacion) {
        let presentacion = JSON.parse(item.presentacion);
        return `${presentacion.nombre} de ${presentacion.cantidad} uds`;
      }

      return "";
    },

    calcularFechaHasta(actual, dias) {
      var date = new Date(actual);
      date.setDate(date.getDate() + (dias - 1));
      return date;
    },
    getPresentacion(prod) {
      if (prod.presentacion) {
        let presentacion = JSON.parse(prod.presentacion);
        return presentacion.nombre;
      } else {
        return prod.ProductoMedicion;
      }
    },
    getEmpresa() {
      this.axios.get(API + "empresa/id/" + 1).then(res => {
        this.empresa = res.data;
      });
    },
    imprimir() {
      window.print();
    }
  }
};
</script>

<style scoped>
@import "../../assets/css/invoice.css";

.total {
  background: #010067 !important;
  color: #fff !important;
}

.tblProductos {
  background: #FD8100 !important;
}

.celda {
  text-align: left !important;
  border: none !important;
  background: white !important;
}

table.tblProductos tr {
  background: #FD8100 !important;
}

table.tblProductos td.celda {
  text-align: left !important;
  border: none !important;
  background: white !important;
}

@media print {
  #printBtn {
    display: none;
  }
}
</style>
