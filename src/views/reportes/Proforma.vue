<template>
	<div id="invoice-container">
		<header class="clearfix">
			<div id="logo">
				<img src="/resources/images/logo-banner.jpg">
			</div>
			<div id="company">
				<h2 class="name">{{ empresa.RAZONSOCIAL }}</h2>
				<div>{{ empresa.DIRECCION }}</div>
				<div>{{ empresa.TELEFONO }} - {{ empresa.CELULAR }}</div>
				<div><a :href="'mailto:' + empresa.EMAIL">{{ empresa.EMAIL }}</a></div>
			</div>
		</header>
		<main>
      <div id="details" class="clearfix">
        <div id="client">
          <div class="to">PROFORMA A:</div>
          <h2 class="name">{{ proforma.Cliente }}</h2>
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
            <th class="no">#</th>
            <th class="cod">CÓDIGO</th>
            <th class="desc">DESCRIPCIÓN</th>
            <th class="unit">PRECIO U.</th>
            <th class="qty">CANT.</th>
            <th class="total">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td class="no">{{ index+1 }}</td>
            <td class="cod">{{prod.CodigoBarra}}</td>
            <td class="desc"><h3>{{ prod.Producto }}</h3>{{ prod.ProductoDesc }}</td>
            <td class="unit">S/. {{ prod.Precio | moneda }}</td>
            <td class="qty">{{ prod.Cantidad }}</td>
            <td class="total">
              S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
              <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
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
            <td colspan="2">GRAN TOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <div id="thanks">¡Dios lo Bendiga!</div>
      <div id="notices">
        <div>NOTA:</div>
        <div class="notice">Proforma valida por 3 días posteriores, precios sujetos a cambios sin previo aviso.</div>
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
