<template>
	<div id="invoice-container">
		<header class="clearfix">
			<div id="logo">
        <div id="accounts" style="margin-top:30px; font-weight:bold;">
        <img style="max-width: 100%" src="/resources/images/banner-proforma.jpg" />
			</div>
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
              <tr v-if="proforma.Telefono">
                <td class="celda" style="font-weight:bold; width:25% !important;">Telefono</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.Telefono }}</td>
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
              <!-- <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Forma De Pago</td>
                <td class="celda" style="text-transform: uppercase">: CONTADO</td>
              </tr> -->
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Forma De Pago</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.EsCreditoProforma == 1 ? 'CRÉDITO' : 'CONTADO'}}</td>
              </tr>
              <tr v-if="proforma.EsCreditoProforma == 1">
                <td class="celda" style="font-weight:bold; width:40% !important;">Fecha De Pago</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.FechaCreditoProforma }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Plazo De Entrega</td>
                <td class="celda" style="text-transform: uppercase">: INMEDIATO</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Descripción</td>
                <td class="celda" style="text-transform: uppercase">: {{proforma.Descripcion}}</td>
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
            <th width="3%" class="no">#</th>
            <!--<th></th>-->
            <th width="40%" class="desc">DESCRIPCIÓN</th>
            <th width="20%" class="de">PRESENTACIÓN</th>
            <th width="8%" class="um">U.M.</th>
            <th width="10%" class="qty">CANT.</th>
            <th width="10%" class="unit">PRECIO U.</th>
            <th width="10%"class="total">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td width="3%" class="no">{{ index+1 }}</td>
            <!--<td></td>-->
            <!--<td><img style="max-width: 65px; max-height: 65px" :src="'/resources/images/productos/' + prod.Imagen" /></td>-->
            <td width="40%" class="desc"><h3 style="color:black !important;">{{ prod.Producto }}</h3>{{ prod.ProductoDesc }}<!--<br>{{ prod.ProductoDesc2 }}<br>{{ prod.ProductoDesc3 }}--></td>
            <td width="20%" class="de" style="text-align:center">{{ prod.Descripcion }}<!--<br>{{ prod.ProductoDesc2 }}<br>{{ prod.ProductoDesc3 }}--></td>
            <td width="8%" class="um" style="text-align:center">{{ prod.ProductoMedicion }}<!--<br>{{ prod.ProductoDesc2 }}<br>{{ prod.ProductoDesc3 }}--></td>
            <!--<td><img style="max-width: 65px; max-height: 65px" :src="'/resources/images/productos/' + prod.Imagen" /></td>-->
             <!--<td class="desc"><h3>{{ prod.Producto }}</h3>{{ prod.ProductoDesc }}<br>{{ prod.ProductoDesc2 }}<br>{{ prod.ProductoDesc3 }}</td>-->
            <td width="10%" class="qty">{{ prod.Cantidad }}</td>
            <td width="10%" class="unit">S/. {{ prod.Precio | moneda }}</td>
            <td width="10%" class="total">
              <div v-if="prod.DisabledTotal === 0">
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
                <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="2"></td>
            <td colspan="4">SUBTOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
          <!--<tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td>$1,300.00</td>
          </tr>-->
          <tr>
            <td colspan="2"></td>
            <td colspan="4" style="background:#E7BC2F">TOTAL</td>
            <td style="background:#E7BC2F">S/. {{ subTotal | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <!-- <div id="accounts" style="margin-top:-10px; font-weight:bold;">
        <h3 style="font-weight:bold">Información de pago</h3>
        <div style="margin-bottom:10px;">
          CTA AHORROS SOLES 
        </div>
      </div> -->
      <div id="accounts" style="margin-top:30px; font-weight:bold;">
        <img style="max-width: 100%" src="/resources/images/cuentas.png" />
      </div>
      <br>
      <div id="notices">
        <div  style="font-weight:bold">TÉRMINOS Y CONDICIONES:</div>
        <div style="font-style: italic; font-size: 15px;">Esta cotización esta sujeta a los términos y condiciones que se enuncian acontinuación:</div>
        <div class="notice">1. Duración de la oferta <span style="font-weight:bold">{{proforma.DiasValidos}} DIAS.</span> </div>
        <div class="notice">2. Para iniciar la <span style="font-weight:bold">produccón o servicio</span> se realizará el anticipo de <span style="font-weight:bold">50%</span></div>
        <div class="notice">3. Entregado el trabajo o ejecutado el servicio no existen <span style="font-weight:bold">devoluciones</span></div>
        <div style="font-style: italic;">La aceptación del cliente (firmar a continuación)</div>
      </div>
      <div>
        <br>
        <br>
        <br>
        <table>
          <tbody>
            <td style="border: none !important; border-top:3px solid #E7BC2F; background: white !important; width:10%;"></td>
            <td style="border: none !important; border-top:3px solid black ; background: white !important; width:40%; text-align:left; color:black">NOMBRE DEL CLIENTE:</td>
            <td style="border: none !important; border-top:3px solid #E7BC2F; background: white !important; width:50%"></td>
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