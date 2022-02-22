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
          <div class="to">ORDEN A</div>
          <h2 class="name">SR(ES): <b>{{ proforma.Cliente }}</b></h2>
          <h2 class="name">RUC/DNI: <b>{{ proforma.DniRuc }}</b></h2>
          <div class="address">{{ proforma.ClienteDireccion }}</div>
          <div class="email"><a :href="'mailto:' + proforma.ClienteEmail">{{ proforma.ClienteEmail }}</a></div>
        </div>
        <div id="invoice">
          <h1>ORDEN DE SERVICIO: <b>{{ proforma.Anio }}-{{ proforma.Numero }}</b> </h1>
          <div class="date">Fecha de Orden: <b>{{ proforma.FechaReg }}</b> </div>
          <div class="date">Orden generada por: <b>{{ proforma.UsuarioReg }}</b> </div>
          <!--<div class="date">Due Date: 30/06/2014</div>-->
        </div>
      </div>
      <table border="0" cellspacing="0" cellpadding="0">
        <thead>
          <tr>
            <th class="no">#</th>
            <th></th>
            <th class="desc">ARTICULO</th>
            <th class="unit">MODELO</th>
            <th class="qty">DESCRIPCION</th>
            <th class="total">CANTIDAD</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in proforma.productos" :key="index">
            <td class="no">{{ index+1 }}</td>
            <td></td>
            <!--<td><img style="max-width: 65px; max-height: 65px" :src="'/resources/images/productos/' + prod.Imagen" /></td>-->
            <td class="desc"><h3>{{ prod.Producto }}</h3><!--<br>{{ prod.ProductoDesc2 }}<br>{{ prod.ProductoDesc3 }}--></td>
            <td class="unit" style="font-size:1rem !important">{{ prod.ModeloProducto }}</td>
            <td class="qty" style="font-size:1rem !important">{{ prod.ProductoDescripcion  }}</td>
            <td class="total" style="font-size:1rem !important">
              <div>
                 {{ prod.Cantidad }}<br>
              </div>
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr v-if="false">
            <td colspan="3"></td>
            <td colspan="2">SUBTOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
          <!--<tr>
            <td colspan="2"></td>
            <td colspan="2">TAX 25%</td>
            <td>$1,300.00</td>
          </tr>-->
          <tr v-if="false">
            <td colspan="3"></td>
            <td colspan="2">GRAN TOTAL</td>
            <td>S/. {{ subTotal | redondear }}</td>
          </tr>
        </tfoot>
      </table>
      <div id="accounts">
        <img style="max-width: 100%" src="/resources/images/cuentas.png" />
      </div>
      <div id="thanks" v-if="false">¡Dios lo Bendiga!</div>
      <div id="notices">
        <div>NOTA:</div>
        <div class="notice">:</div>
      </div>
      <div style="text-align:center">
        <button class="btn btn-success" id="printBtn" @click="imprimir()">IMPRIMIR</button>
        <b-button variant="info" style="color:white" id="printBtn" :href="'/imprimir/proforma.php?IdProforma='+this.$route.params.id" >IMPRIMIR TICKET</b-button>
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
        console.log(this.proforma)
			})
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
@media print {
  #printBtn {
    display: none;
  }
}
</style>
