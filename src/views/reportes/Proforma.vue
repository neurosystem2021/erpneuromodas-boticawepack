<template>
	<div id="invoice-container">
		<header class="clearfix" style="height: 120px;">
			<div id="logo">
        <img v-if="proforma.IdAlmacen !== 3 && proforma.IdAlmacen !== 4" src="/resources/images/logo.png">
				<img v-if="proforma.IdAlmacen == 3 || proforma.IdAlmacen == 4" src="/resources/images/logotowan.png">
			</div>
			<div id="company">
				<h2 class="name">{{ empresa.RAZONSOCIAL }}</h2>
        <div>RUC: <b>{{ empresa.RUC }}</b></div>
				<div>{{ proforma.AlmacenDireccion }}</div>
			</div>
		</header>
		<main>
      <div id="details" class="clearfix">
        <div id="client">
          <div class="to">PROFORMA A:</div>
          <h2 class="name">{{ proforma.Cliente }}</h2>

          <div><b>{{ proforma.ClienteDniRuc }}</b></div>
          <div class="address">{{ proforma.ClienteDireccion }}</div>
          <div class="email"><a :href="'mailto:' + proforma.ClienteEmail">{{ proforma.ClienteEmail }}</a></div>
        </div>
        <div id="invoice">
          <h1>PROFORMA {{ proforma.Anio }}-{{ proforma.Numero }}</h1>
          <div class="date">Fecha Proforma: {{ proforma.FechaReg }}</div>
          <!--<div class="date">Due Date: 30/06/2014</div>-->
        </div>
      </div>
      <table border="0" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="no">COD.</th>
            <th class="qty">CANT.</th>
            <th class="">MEDIDA</th>
            <th class="desc prod">PRODUCTO</th>
            <th class="desc">LABO.</th>
            <th class="desc">FECHA VEN.</th>
            <th class="desc">LOTE</th>
            <th class="unit">PRECIO U.</th>
            
            <th class="total">VALOR DE V.</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td class="no">{{ prod.Codigo }}</td>
            <td class="qty">{{ prod.Cantidad }}</td>
            <td>{{prod.ProductoMedicion}}</td>
            <td class="desc"><h3>{{ prod.Producto }}</h3></td>
            <td class="">{{ prod.ProductoMarca }}</td>
            <td class="">{{ prod.FechaVen }}</td>
            <td class="">{{ prod.IdLote }}</td>
            <td class="unit">S/. {{ prod.Precio | moneda }}</td>
            
            <td class="total">
              S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
              <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="5"></td>
            <td colspan="2">SUBTOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
          <!--<tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td>$1,300.00</td>
          </tr>-->
          <tr>
            <td colspan="5"></td>
            <td colspan="2">TOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <div id="thanks">¡Muchas Gracias!</div>
      <div id="notices">
        <div>NOTA:</div>
        <div class="notice">Esta proforma solo tiene validez durante los 30 dias posteriores.</div>
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
      empresa: {}
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
			})
    },
    getEmpresa () {
      this.axios.get(API + 'empresa/id/' + 1)
			.then(res => {
				this.empresa = res.data
			})
    }
  }
}
</script>

<style scoped>
@import '../../assets/css/invoice.css';
</style>
