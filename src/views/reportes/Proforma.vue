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
          <h5 class="to" style=":black">COTIZACION A:</h5>
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
              <!-- <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Moneda</td>
                <td class="celda" style="text-transform: uppercase">: Soles</td>
              </tr> -->
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.UsuarioReg }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">T. Pago:</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.EsCredito == 1 ? 'CRÉDITO' :'CONTADO' }}</td>
                
              </tr>
            </tbody>
          </table>
        </div>
        <div id="invoice">
          <h1>COTIZACION {{ proforma.Anio }}-{{ proforma.Numero }}</h1>
          <div style="height:15px"></div>
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Fecha de emisión</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.FechaReg?proforma.FechaReg.substring(0,10):proforma.FechaReg }}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Tiempo Validez</td>
                <td class="celda" style="text-transform: uppercase">: {{proforma.DiasValidos}} d</td>
              </tr>
              <!-- <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Forma De Pago</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.EsCredito == 1 ? 'CREDITO' : 'CONTADO'}}</td>
              </tr> -->
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Tiempo de Entrega</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.PorEntregar == 1 ? proforma.PorEntregarDias +' D' : 'INMEDIATO'}}</td>
              </tr>
              <!-- <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;"> Fecha Crédito</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.FechaCredito}}</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;"> Días de Crédito</td>
                <td class="celda" style="text-transform: uppercase">: {{ proforma.DiasCredito}}</td>
              </tr> -->
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
        <!-- <div class="notice">ESTA COTIZACION SOLO TIENE VALIDEZ DE <span style="font-weight:bold">{{proforma.DiasValidos}}</span> DIAS.</div> -->
        <div class="notice">PAGOS: - Factura a {{DiasCredito}} días - S/. {{ subTotal | redondear }}</div>
        <div class="notice">Observación(es): {{proforma.Observacion}}</div>
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
import moment from "moment";
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
      fechaHasta: "",
      DiasCredito: 0
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
        );
        this.DiasCredito = this.calcularDiasCredito(
          this.proforma.FechaReg,
          this.proforma.FechaCredito
        );
      });
    },
    presentacionProducto(item) {
      if (item.presentacion) {
        let presentacion = JSON.parse(item.presentacion);
        return `${presentacion.nombre} de ${presentacion.cantidad} uds`;
      }

      return "";
    },
    calcularDiasCredito(FechaReg, FechaCredito) {
      let FechaRegFormat = moment(FechaReg, "YYYY-MM-DD");
      let FechaCreditoFormat = moment(FechaCredito, "YYYY-MM-DD");
      return FechaCreditoFormat.diff(FechaRegFormat, "days");
    },
    calcularFechaHasta(actual, dias) {
      var new_date = moment(actual).add(dias, "days");
      return new_date.format("YYYY-MM-DD");
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
    // window.print();
    imprimir() {
      let baseURL = window.location.origin;
      let html = `
      <html>
        <head>
            <title>Impresión Proforma. </title>
            	<div id="logo">
                <img src="${baseURL}/resources/images/banner-proforma.jpg">
              </div>
        </head>
        <style>
        @media print{
          .total {
           /* background: #010067 !important;*/
           /* color: #fff !important;*/
          }

          .tblProductos {
            background: #fd8100 !important;
          }

          .celda {
            text-align: left !important;
            border: none !important;
            background: white !important;
          }

          table.tblProductos tr {
            background: #fd8100 !important;
          }

          table.tblProductos td.celda {
            text-align: left !important;
            border: none !important;
            background: white !important;
          }
      
         .clearfix:after {
            content: "";
            display: table;
            clear: both;
          }
          a {
          color: #0087C3;
          text-decoration: none;
        }
        
        #invoice-container {
          position: relative;
          width: 21cm;  
          margin: 0 auto; 
          color: #555555;
          background: #FFFFFF; 
          font-family: Arial, sans-serif; 
          font-size: 14px; 
        }
        
        header {
          padding: 10px 0;
          margin-bottom: 20px;
          border-bottom: 1px solid #AAAAAA;
        }
        
        #logo {
          float: left;
          margin-top: 8px;
          text-align: center;
          width: 100%;
        }
        
        #logo img {
          max-width: 100%;
          height: auto;
        }
        
        #company {
          float: right;
          text-align: right;
        }
        
        
        #details {
          margin-bottom: 9px;
        }
        
        #client {
          padding-left: 6px;
          border-left: 6px solid #FD8100;
          float: left;
          width: 12cm;
        }

        #client .to {
    color: #777777;
  }
  
        h2.name {
    font-size: 1.2em;
    font-weight: normal;
    margin: 0;
  }
  
  
  /* #invoice {
    float: right;
    text-align: right;
    width: 5cm;
  }*/
   #invoice h1 {
    color: #0087C3;
    font-size: 1em;
    line-height: 1em;
    font-weight: normal;
    margin: 0  0 10px 0;
  }
    /*
     #invoice .date {
    font-size: 1.1em;
    color: #777777;
  }
*/
   
  /*table {
    width: 100%;
    border-collapse: collapse;
    border-spacing: 0;
    margin-bottom: 7px;
  }*/
  
    table th,
  table td {
    /* padding: 10px; */
    padding: 3px;
    background: #EEEEEE;
    text-align: center;
    border-bottom: 1px solid #FFFFFF;
  }
    table th {
    white-space: nowrap;        
    font-weight: bold;
  }
  
  table td {
    text-align: right;
  }
  
  table td h3{
    color: #FD8100;
    font-size: 1em;
    font-weight: normal;
    margin: 0 0 0.2em 0;
  }
    table .no {
    color: #FFFFFF;
    font-size: 1em;
    /* background: #FD8100;*/
  }
  
  table .desc {
    text-align: left;
 /*   white-space: pre-wrap;*/
/*      white-space: pre-line;*/
    color:black;
  }
  
  table .unit {
    color:black;
    font-size: 1em;
    background: #DDDDDD;
    text-align: center;
  }
  
  table .qty {
    color: black;
    text-align: center;
  }
    table .total {
    /* background: #FD8100;*/
    color: black;
    text-align: right;
  }
  table .total .dsct {
    font-size: .7em;
    font-style: italic;
  }
  table td.unit,
  table td.qty,
  table td.total {
    font-size: .9em;
    color: black;
  }
  
  table tbody tr:last-child td {
    border: none;
  }
  
  table tfoot td {
    padding: 7px 20px;
    background: #FFFFFF;
    border-bottom: none;
    font-size: 1.2em;
    white-space: nowrap; 
    border-top: 1px solid #AAAAAA; 
  }
    
  table tfoot tr:first-child td {
    border-top: none; 
  }
  
  table tfoot tr:last-child td {
    color: black;
    font-size: 1.4em;
    border-top: 1px solid #FD8100; 
  
  }
  
  table tfoot tr td:first-child {
    border: none;
  }
  #accounts{
    font-size: .9em;
    margin-bottom: 12px;
    margin-top: 12px;
  }

  #thanks{
    font-size: 2em;
    margin-bottom: 9px;
  }
  
  #notices{
    padding-left: 6px;
    border-left: 6px solid #FD8100;
  }
  
  #notices .notice {
    font-size: 1em;
  }
  
  footer {
    color: #777777;
    width: 100%;
    height: 30px;
    position: absolute;
    bottom: 0;
    border-top: 1px solid #AAAAAA;
    padding: 8px 0;
    text-align: center;
  }
    }   
      </style>
      <body>
      <div id="details" class="clearfix">
        <div id="client">
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Cliente</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.Cliente
                }</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Documento</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.Documento
                }<td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Dirección</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.ClienteDireccion
                }</td>
              </tr>

              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">Atendido Por</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.UsuarioReg
                }</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:25% !important;">T. Pago:</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.EsCredito == 1 ? "CRÉDITO" : "CONTADO"
                }</td>
                
              </tr>
            </tbody>
          </table>
        </div>
        <div id="invoice">
          <h1>COTIZACION ${this.proforma.Anio}-${this.proforma.Numero}</h1>
          <table>
            <tbody>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Fecha de emisión</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.FechaReg
                    ? this.proforma.FechaReg.substring(0, 10)
                    : this.proforma.FechaReg
                }</td>
              </tr>
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Tiempo Validez</td>
                <td class="celda" style="text-transform: uppercase"> ${
                  this.proforma.DiasValidos
                } D </td>
              </tr>
             
              <tr>
                <td class="celda" style="font-weight:bold; width:40% !important;">Tiempo de Entrega</td>
                <td class="celda" style="text-transform: uppercase">: ${
                  this.proforma.PorEntregar == 1
                    ? this.proforma.PorEntregarDias + " D"
                    : "INMEDIATO"
                }</td>
              </tr>

            </tbody>
          </table>
        </div>
      </div>
      <table border="0" cellspacing="0" cellpadding="0" min-width="100%" class="tblProductos">
        <thead>
          <tr>
            <th width="2%" class="total">#</th>
            <th width="5%" class="qty">CANT.</th>
            <th width="48%" class="desc">DESCRIPCIÓN</th>
            <th width="12%" class="unit">U.M.</th>
            <th width="11%" class="unit">P.U.</th>
            <th width="12%" class="total">TOTAL</th>
          </tr>
        </thead>
        <tbody>
    `;
      this.proforma.productos.forEach((prod, index) => {
        html += `
          <tr>
           
            <td class="total">${index + 1}</td>
            <td class="qty" style="font-size:1.1rem">${prod.Cantidad}</td>
            <td class="desc" style="font-size:0.75rem">
              <h3 style="color:#000; font-size:1rem"> ${prod.Producto} `;
        if (this.presentacionProducto(prod)) {
          html += ` <p>(${this.presentacionProducto(prod)})</p> `;
        }
        if (prod.ProductoDesc) {
          html += ` </h3><p>${prod.ProductoDesc}</p>`;
        }
        if (prod.ProductoDesc2) {
          html += ` </h3><p>${prod.ProductoDesc2}</p>`;
        }
        if (prod.ProductoDesc3) {
          html += ` </h3><p>${prod.ProductoDesc3}</p>`;
        }
        html += `
            </td>
             <td class="unit" style="font-size:1.1rem">
              ${this.getPresentacion(prod)}</td>
            <td class="unit" style="font-size:1.1rem">S/. ${parseFloat(
              prod.Precio
            ).toFixed(2)}</td>
            <td class="total" style="font-size:1.1rem">
              <div v-if="prod.DisabledTotal === 0">
                S/. ${parseFloat(
                  prod.Precio * prod.Cantidad - prod.Descuento
                ).toFixed(2)}<br>
                `;
        if (prod.Descuento) {
          html += `<span class="dsct">Dsct: ${prod.Descuento |
            this.moneda("S/. ")} </span> `;
        }
        html += `
              </div>
            </td>
          </tr>
       
      `;
      });
      html += ` 
        
            <tr>
              <td colspan="3"></td>
              <td colspan="2">SUBTOTAL</td>
              <td>S/. ${parseFloat(this.subTotal).toFixed(2)}</td>
            </tr>
            <tr>
              <td colspan="3"></td>
              <td colspan="2">TOTAL</td>
              <td >S/. ${parseFloat(
                this.subTotal
              ).toFixed(2)}</td>
            </tr>
          
          </tbody>
        </table>
        <div id="accounts" style="font-weight:bold;">
          <img style="max-width: 100%" src="${baseURL}/resources/images/cuentas.png" />
        </div>
        <br>
        <div id="notices">
          <div>NOTA:</div>
          <div class="notice">LAS PRODUCTOS SERAN ENTREGADOS MISMO EN EL LOCAL COMERCIAL</div>
          <div class="notice">PAGOS: - Factura a ${parseFloat(
            this.DiasCredito ? this.DiasCredito : 0
          ).toFixed(2)} días - S/. ${this.subTotal}</div>
          <div class="notice">Observación(es): ${
            this.proforma.Observacion ? this.proforma.Observacion : ""
          }</div>
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
        
        </body>
        </html>
      `;
      // let ventana = window.open("", "", "height=600,width=800");
      let ventana = window.open("", "", " width=793,height=1122");
      ventana.document.write(html);
      ventana.document.close();
      ventana.focus();

      ventana.print();
      ventana.close();
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
  background: #fd8100 !important;
}

.celda {
  text-align: left !important;
  border: none !important;
  background: white !important;
}

table.tblProductos tr {
  background: #fd8100 !important;
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
