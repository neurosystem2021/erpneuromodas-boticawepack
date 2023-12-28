<template>
	<div id="invoice-container">
		<header class="clearfix">
			<div id="logo">
        <img v-if="this.$route.query.empresa === 'titi'" src="/resources/images/banner-proformai.jpg">
        <img v-else src="/resources/images/banner-proformar.jpg">
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
          <h5 class="to" style="color:black">PROFORMA A:</h5>
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
                <td class="celda" style="font-weight:bold; width:25% !important;">Placa</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.PlacaFormat }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Moneda</td>
                <td class="celda" style="text-transform: uppercase">: Soles</td>
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
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>
              <!--<tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>-->
            </tbody>
          </table>
        </div>
      </div>
      <table border="0" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="no" style="font-size: 0.9em;">#</th>
            <th></th>
            <th class="desc" style="font-size: 0.9em;">DESCRIPCIÓN</th>
            <th class="qty" style="font-size: 0.9em;">CANT.</th>
            <th class="unit" style="font-size: 0.9em;">PRECIO U.</th>
            <th class="total" style="font-size: 0.9em;">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td class="no" style="text-align: center; vertical-align: middle;">{{ index+1 }}</td>
            <td style="text-align: center; vertical-align: middle;"><img style="max-width: 65px; max-height: 65px" :src="'/resources/images/productos/' + prod.Imagen" /></td>
            <td class="desc" style="font-size:0.75rem"><h3 style="color:#57B223; font-size:0.75rem">{{ prod.Producto }}</h3>{{ prod.Descripcion }}</td>
            <td class="qty" style="font-size:0.75rem">{{ prod.Cantidad }}</td>
            <td class="unit" style="font-size:0.75rem">S/. {{ prod.Precio | moneda }}</td>
            <td class="total" style="font-size:0.75rem">
              <div v-if="prod.DisabledTotal === 0">
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
                <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3"></td>
            <td colspan="2" style="font-size:0.85rem; font-weight: bold;">SUBTOTAL</td>
            <td style="font-size:0.85rem; font-weight: bold;">S/. {{ subTotal | redondear }}</td>
          </tr>
          <!--<tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td>$1,300.00</td>
          </tr>-->
          <tr>
            <td colspan="3"></td>
            <td colspan="2" style="background:#57B223; font-size:0.85rem; font-weight: bold;">TOTAL</td>
            <td style="background:#57B223; font-size:0.85rem; font-weight: bold;">S/. {{ subTotal | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <div id="accounts" style="margin-top:-10px; font-weight:bold;">
        <img style="max-width: 100%" v-if="this.$route.query.empresa === 'hoqui'" src="/resources/images/cuentasr.jpg">
        <img style="max-width: 100%" v-else src="/resources/images/cuentasi.jpg">    
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
            <td style="border: none !important; border-top:3px solid #57B223; background: white !important; width:50%"></td>
            <td style="border: none !important; border-top:3px solid black ; background: white !important; width:40%; text-align:center; color:black">FIRMA/SELLO DE AUTORIZACION</td>
            <td style="border: none !important; border-top:3px solid #57B223; background: white !important; width:10%"></td>
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
import {API} from '@/store/config.js'
export default {
  beforeMount () {
    if (this.$route.query.empresa === 'titi') {
      // Interceptos XMLHttpRequest
      let oldXHROpen = window.XMLHttpRequest.prototype.open;
      window.XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
        // do something with the method, url and etc.
        var newUrl = url.indexOf(location.protocol + '//') !== -1 ? url : location.protocol + '//' + location.host + url;
        newUrl = new URL(newUrl);
        newUrl.searchParams.append('empresa', 'titi');
        url = newUrl.toString();
        arguments[1] = url
        return oldXHROpen.apply(this, arguments);
      }
    }
  },
	mounted () {
		this.getProforma()
    this.getEmpresa()
	},
	data () {
		return {
			API: API,
			idProforma: this.$route.params.id,
			proforma: { productos: [] },
      empresa: {},
      fechaHasta:'',
		}
	},
	computed: {
		subTotal () {
			let sumatoria = 0
			this.proforma.productos.forEach(prod => {
				sumatoria += (prod.Cantidad * prod.Precio) - prod.Descuento
			})
      return sumatoria
		}
	},
  methods: {
    getProforma () {
      this.axios.get(API + 'proformas/id/' + this.idProforma)
			.then(res => {
				this.proforma = res.data
        this.fechaHasta = this.calcularFechaHasta(this.proforma.FechaReg.substring(0,10),this.proforma.DiasValidos).toISOString().substring(0,10);
        document.title = "PROFORMA " + (this.$route.query.empresa === 'titi'?'ISGIOSARA':'RSANTOS') + " " + this.proforma.Anio + "-" + this.proforma.Numero
			})
    },
    calcularFechaHasta(actual,dias){
    var date = new Date(actual);
    date.setDate(date.getDate() + (dias-1));
    return date;
    },
    getEmpresa () {
      this.axios.get(API + 'empresa/id/' + 1)
			.then(res => {
				this.empresa = res.data
			})
    },
    imprimir(){
    window.print();
    }
  }
}
</script>

<style scoped>
@import '../../assets/css/invoice.css';
.celda{
  text-align:left;
  border: none !important;
  background: white !important
}
@media print {
  #printBtn {
    display: none;
  }
}
</style>
