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
                <td class="celda" style="text-transform: uppercase">: CONTADO O TRANSFERENCIA</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Plazo De Entrega</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.DescripcionProforma }}</td>
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
            <th class="no">#</th>
            <th></th>
            <th width="40%" class="desc">DESCRIPCIÓN</th>
            <th width="10%" class="qty">CANT.</th>
            <th width="20%" class="unit">PRECIO U.</th>
            <th width="20%" class="unit">IGV.</th>
            <th width="20%" class="total">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td class="no">{{ index+1 }}</td>
            <td><img style="max-width: 85px; max-height: 85px" :src="'/resources/images/productos/' + prod.Imagen" /></td>
            <td class="desc" style="font-size:0.75rem"><h3 style="color:#57B223; font-size:1rem">{{ prod.Producto }}</h3> ({{ prod.Descripcion }})</td>
            <td class="qty" style="font-size:1.1rem">{{ prod.Cantidad }}</td>
            <td v-if="proforma.EsIgv == 0" class="unit" style="font-size:1.1rem">S/. {{ prod.Precio | moneda }}</td>
            <td v-if="proforma.EsIgv == 0" class="unit" style="font-size:1.1rem">-</td>
            <td v-if="proforma.EsIgv == 0" class="total" style="font-size:1.1rem">
              <div >
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
                <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
            <td v-if="proforma.EsIgv == 1" class="unit" style="font-size:1.1rem">S/. {{ (prod.Precio / 1.18) | moneda }}</td>
            <td v-if="proforma.EsIgv == 1" class="unit" style="font-size:1.1rem">S/. {{ (prod.Precio * 18 / 118) | moneda }}</td>
            <td v-if="proforma.EsIgv == 1" class="total" style="font-size:1.1rem"><div >
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
                <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr v-if="proforma.EsIgv == 1">
            <td colspan="3"></td>
            <td colspan="3">SUBTOTAL</td>
            <td>S/. {{ subTotalIgvBase | redondear }}</td>
          </tr>
          <tr v-if="proforma.EsIgv == 1">
            <td colspan="3"></td>
            <td colspan="3">IGV</td>
            <td>S/. {{ subTotalIgv | redondear }}</td>
          </tr>
          <tr v-if="proforma.EsIgv == 0">
            <td colspan="3"></td>
            <td colspan="3">SUBTOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
          <!--<tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td>$1,300.00</td>
          </tr>-->
          <tr>
            <td colspan="3"></td>
            <td colspan="3" style="background:#57B223">TOTAL</td>
            <td style="background:#57B223">S/. {{ Total | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <div id="accounts" style="margin-top:-10px; font-weight:bold;">
        <img style="max-width: 100%" src="/resources/images/cuentas.png" />
      </div>
      <br>
      <div id="notices">
        <div>NOTA:</div>
        <div class="notice">LAS PRODUCTOS SERAN ENTREGADOS PREVIA COORDINACION, DOMICILIO O EN EL LOCAL COMERCIAL</div>
        <div class="notice">ESTA PROFORMA SOLO TIENE VALIDEZ DE 7 - 10 DIAS.</div>
        <div class="notice">NUMERO DE CONTACTO: 967151582</div>
        <div class="notice" style="font-weight: bold;">PRODUCTOS SIN IGV</div>
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
    subTotalIgvBase() {
      let sumatoria = 0
      const igv = 0.18
      this.proforma.productos.forEach(prod => {
        const totalConIGV = (prod.Cantidad * prod.Precio) - prod.Descuento
        const baseSinIGV = parseFloat((totalConIGV / (1 + igv)).toFixed(2))
        sumatoria += baseSinIGV
      })
      return parseFloat(sumatoria.toFixed(2))
    },
    subTotalIgv () {
      let sumatoria = 0
      this.proforma.productos.forEach(prod => {
        const totalConIgv = (prod.Cantidad * prod.Precio) - prod.Descuento
        const igvPorProducto = parseFloat((prod.Precio * 18 / 118).toFixed(2))
        const igvTotal = igvPorProducto * prod.Cantidad
        sumatoria += igvTotal
      })
      return parseFloat(sumatoria.toFixed(2))
    },
    subTotal () {
      let sumatoria = 0
			this.proforma.productos.forEach(prod => {
				sumatoria += (prod.Cantidad * prod.Precio) - prod.Descuento
			})
      return sumatoria
		},
    Total () {
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
    date.setDate(date.getDate() + (dias));
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