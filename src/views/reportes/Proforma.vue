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
      <h6 v-if="productosVehichulo.length > 0" class="mt-1">Vehículos:</h6>
      <b-row v-for="(prod, index) in productosVehichulo" :key="index" class="mt-1">
        <b-col md="6" class="text-center" style="border-bottom: 3px solid #EEEEEE;">
          <img style="max-width: 230px; max-height: 230px" :src="'/resources/images/'+(prod.EsVehiculo==1?'colores':'productos')+'/' + prod.ImagenPro" />
        </b-col>
        <b-col md="6" style="border-bottom: 3px solid #EEEEEE;">
          <b-row>
            <b-col md="12">
              <h3 style="color:#0063f7; font-size:0.9rem; font-weight: bold; margin: 0;"><b>{{ prod.Producto }} - {{ prod.ProductoModelo }}</b></h3>
              <b-form-text v-if="prod.EsVehiculo==1" style="text-transform: uppercase; font-size: 0.7rem; font-weight: 600; text-wrap: unset; color: black !important;">{{ (prod.IdColor>1?'COLOR: ':'') }}<span v-if="prod.IdColor>1" :style="{color:prod.HexColor}"><b>{{ prod.ProductoColor+' ' }}</b></span><br/>
                <span
                  style="color: black;"
                  v-for="(line,lineNumber) of prod.ProductoDesc.split('\n')" 
                  v-bind:key="lineNumber" >
                  {{ line }}<br/>
                </span>
              </b-form-text>
              <b-form-text style="text-transform: uppercase; font-size: 0.7rem; font-weight: 600; text-wrap: unset; color: black !important;">{{ prod.ProductoDesc2 }}</b-form-text>
            </b-col>
          </b-row>
          <b-row class="mt-1">
            <b-col md="12">
              <table>
                <tbody>
                  <tr>
                    <th class="text-center" style="font-size: 0.7rem; color: black;">Cantidad</th>
                    <th class="text-center" style="font-size: 0.7rem; color: black;">Precio Unitario</th>
                    <th class="text-center" style="font-size: 0.7rem; color: black;">Total</th>
                  </tr>
                  <tr>
                    <td class="text-center" style="font-weight: bold; color: black;">{{ prod.Cantidad }}</td>
                    <td class="text-center" style="font-weight: bold; color: black;">S/ {{ prod.Precio | redondear }}</td>
                    <td class="text-center" style="font-weight: bold; color: black;">S/ {{ ((prod.Precio * prod.Cantidad) - prod.Descuento) | redondear  }} <span style="font-size: 0.65rem;" v-if="prod.Descuento"><br> Dsct: - {{ prod.Descuento | redondear }} </span></td>
                  </tr>
                </tbody>
              </table>
            </b-col>
          </b-row>
        </b-col>
      </b-row>
      <h6 v-if="productosNoVehiculo.length > 0 && productosVehichulo.length > 0" class="mt-1">Adicional:</h6>
      <table border="0" cellspacing="0" cellpadding="0" v-if="productosNoVehiculo.length > 0">
        <thead>
          <tr>
            <th class="no">#</th>
            <th></th>
            <th class="desc">DESCRIPCIÓN</th>
            <th class="desc">MARCA</th>
            <th class="qty">CANT.</th>
            <th class="unit">PRECIO U.</th>
            <th class="total" style="color: white;">TOTAL</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(prod, index) in productosNoVehiculo" :key="index">
            <td class="no text-center">{{ index+1 }}</td>
            <td class="text-center"><img style="max-width: 80px; max-height: 80px" :src="'/resources/images/'+(prod.EsVehiculo==1?'colores':'productos')+'/' + prod.ImagenPro" /></td>
            <td class="desc" style="font-size:0.75rem"><h3 style="color:#0063f7; font-size:0.85rem; font-weight: bold;">{{ prod.Producto }}</h3><b-form-text style="text-transform: uppercase;">{{ prod.ProductoDesc }}</b-form-text></td>
            <td class="desc" style="font-size:0.75rem"><h3 style="color:#0063f7; font-size:0.85rem; font-weight: bold;">{{ prod.ProductoMarca }}</h3></td>
            <td class="qty" style="font-size:0.75rem; font-weight: bold;">{{ prod.Cantidad }}</td>
            <td class="unit" style="font-size:0.75rem; font-weight: bold;" width="15%">S/. {{ prod.Precio | moneda }}</td>
            <td class="total" style="font-size:0.75rem; color:white; font-weight: bold;" width="15%">
              <div v-if="prod.DisabledTotal === 0">
                S/. {{ (prod.Precio * prod.Cantidad) - prod.Descuento | redondear }}<br>
                <span class="dsct" v-if="prod.Descuento" >Dsct: {{ prod.Descuento | moneda('S/. ') }} </span>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <table class="mb-2">
        <tbody>
          <tr>
            <td colspan="4" width="40%"></td>
            <td colspan="2" style="background:#0063f7; font-size:0.95rem; font-weight: bold; color: white;">VALOR TOTAL DE PROFORMA</td>
            <td style="background:#0063f7; font-size:0.95rem; font-weight: bold; color: white;">S/. {{ subTotal | redondear }}</td>
          </tr>
        </tbody>
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
            <td style="border: none !important; border-top:3px solid #0063f7; background: white !important; width:50%"></td>
            <td style="border: none !important; border-top:3px solid black ; background: white !important; width:40%; text-align:center; color:black">FIRMA/SELLO DE AUTORIZACION</td>
            <td style="border: none !important; border-top:3px solid #0063f7; background: white !important; width:10%"></td>
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
      productosVehichulo: [],
      productosNoVehiculo: []
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
        this.proforma.productos.map((p)=>{
          if(p.EsVehiculo == 1){
            this.productosVehichulo.push(p)
          }else{
            this.productosNoVehiculo.push(p)
          } 
        })
        this.fechaHasta = this.calcularFechaHasta(this.proforma.FechaReg.substring(0,10),this.proforma.DiasValidos).toISOString().substring(0,10);
        document.title = "PROFORMA " + this.proforma.Anio + "-" + this.proforma.Numero
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
