<template>
  <b-row>
    <b-container class="ticket">
        <center><img src="/resources/images/logo-ticket.png" style="max-width:100%; width: 40mm" alt="img-logo-ticket-empresa"></center>
        <center><p style="font-weight: bold;">COMERCIALIZADORA MIGUELITO S.A.C.</p></center>
        <center><p style="font-weight: bold;">RUC: 20486408422</p></center>
        <p><b>PROFORMA NRO:</b> {{ proforma.Numero }}</p>
        <p><b>FECHA:</b> {{ proforma.FechaReg }}</p>
        <p><b>VALIDEZ POR:</b> {{ proforma.DiasValidos }}  DIAS</p>
        <hr>
        <p> <b>SR(ES) :</b> {{ proforma.Cliente }}</p>
        <p><b>RUC/DNI :</b> {{ proforma.Documento }}</p>
        <p> <b>DIR :</b> {{ proforma.ClienteDireccion }}</p>
         <table class="tablaProductos" width="100%">
            <thead>
              <tr>
                <th style="text-align: left;">CANT</th>
                <th style="text-align: left;">PRODUCTO</th>
                <th style="text-align: center;">P/U</th>
                <th style="text-align: center;">TOT</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(prod,index) in productos" :key="index">
                 <td style="text-align: left;">{{ prod.Cantidad }}</td>
                 <td style="text-align: left;">{{ prod.Producto }}</td>
                 <td style="text-align: center;">{{ prod.Precio }}</td>
                 <td style="text-align: center;">{{ prod.Precio * prod.Cantidad  }}</td>
              </tr>
              <tr  style="border-top: 1px solid #000000 !important;">
                <td colspan="3" style="text-align: right;"><b>SUBTOTAL</b></td>
                <td style="text-align: center;">
                  {{  parseFloat(productos.reduce((sum,prod)=>{
                  return sum + parseFloat(prod.Cantidad * prod.Precio)
                    },0)).toFixed(2) }}
                </td>
              </tr>
              <tr  style="border-top: 1px solid #000000 !important;">
                <td colspan="3" style="text-align: right;"><b>TOTAL</b></td>
                <td style="text-align: center;">
                  {{  parseFloat(productos.reduce((sum,prod)=>{
                  return sum + parseFloat(prod.Cantidad * prod.Precio)
                    },0)).toFixed(2) }}
                </td>
              </tr>

            </tbody>
            
         </table>
         <p> {{ numeroALetras(Total) }} </p>
         <br>
         <center>

           <p>
             JR. MIGUEL GRAU NRO. SN (FRENTE AL HOTEL PARAISO) PASCO - OXAPAMPA - CONSTITUCION
             PASCO - OXAPAMPA - CONSTITUCION
            </p>
            <p>
              GRACIAS POR SU PREFERENCIA
            </p> 
          </center>
      <b-col></b-col>
    </b-container>
  </b-row>
</template>

<script>
import { API } from "@/store/config.js";
export default {
  beforeMount() {
    if (this.$route.query.empresa === "modasm") {
      // Interceptos XMLHttpRequest
      let oldXHROpen = window.XMLHttpRequest.prototype.open;
      window.XMLHttpRequest.prototype.open = function(
        method,
        url,
        async,
        user,
        password
      ) {
        // do something with the method, url and etc.
        var newUrl =
          url.indexOf(location.protocol + "//") !== -1
            ? url
            : location.protocol + "//" + location.host + url;
        newUrl = new URL(newUrl);
        newUrl.searchParams.append("empresa", "modasgio");
        url = newUrl.toString();
        arguments[1] = url;
        return oldXHROpen.apply(this, arguments);
      };
    }
  },
  async mounted() {
    console.log("Componente mondato");
    let r = "";

    const hash = location.hash;

    const params = new URLSearchParams(hash.split("?")[1]);

    const id_empresa = params.get("id_empresa");

    this.id_empresa = id_empresa;

    this.getProforma();
    this.getEmpresa();
    setTimeout(() => {
      this.imprimir();
    }, 1000);
  },
  data() {
    return {
      API: API,
      idProforma: this.$route.params.id,
      // empresa: this.$route.params.id_empresa,
      proforma: { productos: [] },
      empresa: {},
      fechaHasta: "",
      id_empresa: "",
      productos: [],
      Total: 0,
      unidades: [
        "", // 👈 este índice 0 es obligatorio para evitar "undefined"
        "UNO",
        "DOS",
        "TRES",
        "CUATRO",
        "CINCO",
        "SEIS",
        "SIETE",
        "OCHO",
        "NUEVE",
        "DIEZ",
        "ONCE",
        "DOCE",
        "TRECE",
        "CATORCE",
        "QUINCE",
        "DIECISÉIS",
        "DIECISIETE",
        "DIECIOCHO",
        "DIECINUEVE",
        "VEINTE"
      ],
      decenas: [
        "",
        "",
        "VEINTE",
        "TREINTA",
        "CUARENTA",
        "CINCUENTA",
        "SESENTA",
        "SETENTA",
        "OCHENTA",
        "NOVENTA"
      ],
      centenas: [
        "",
        "CIENTO",
        "DOSCIENTOS",
        "TRESCIENTOS",
        "CUATROCIENTOS",
        "QUINIENTOS",
        "SEISCIENTOS",
        "SETECIENTOS",
        "OCHOCIENTOS",
        "NOVECIENTOS"
      ]
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
    async getProforma() {
      await this.axios
        .get(
          API +
            `obtener_proformas?id=${this.idProforma}&id_empresa=${
              this.id_empresa
            }`
        )
        .then(res => {
          this.proforma = res.data;
          this.ObtenerDatosDellate(res.data.productos);
          this.fechaHasta = this.calcularFechaHasta(
            this.proforma.FechaReg.substring(0, 10),
            this.proforma.DiasValidos
          )
            .toISOString()
            .substring(0, 10);
        });
    },
    ObtenerDatosDellate(productos) {
      productos.forEach(producto => {
        this.axios(`${this.API}ver_productos`, {
          params: {
            id_empresa: producto.Empresa,
            id: producto.IdProducto
          }
        }).then(res => {
          producto.Producto = res.data.Producto;
          this.productos.unshift(producto);
        });
      });
      this.Total = parseFloat(
        productos.reduce((sum, prod) => {
          return sum + parseFloat(prod.Cantidad * prod.Precio);
        }, 0)
      ).toFixed(2);
      console.log(this.Total);
    },
    calcularFechaHasta(actual, dias) {
      var date = new Date(actual);
      date.setDate(date.getDate() + (dias - 1));
      return date;
    },
    getEmpresa() {
      this.axios
        .get(API + "empresa/id/" + 1, {
          params: {
            id_empresa: this.id_empresa
          }
        })
        .then(res => {
          this.empresa = res.data;
        });
    },
    imprimir() {
      window.print();
    },
    convertirMenor1000(n) {
      if (n === 0) return "";
      if (n === 100) return "CIEN";
      if (n < 21) return this.unidades[n];

      if (n < 100) {
        let decena = this.decenas[Math.floor(n / 10)];
        let unidad = n % 10 ? this.unidades[n % 10] : "";
        return decena + (unidad ? " Y " + unidad : "");
      }

      return (
        this.centenas[Math.floor(n / 100)] +
        " " +
        this.convertirMenor1000(n % 100)
      ).trim();
    },

    convertir(n) {
      if (n === 0) return "CERO";
      let millones = Math.floor(n / 1000000);
      let miles = Math.floor((n - millones * 1000000) / 1000);
      let resto = n % 1000;

      let texto = "";
      if (millones > 0)
        texto +=
          millones === 1
            ? "UN MILLÓN "
            : this.convertir(millones) + " MILLONES ";
      if (miles > 0)
        texto += miles === 1 ? "MIL " : this.convertir(miles) + " MIL ";
      if (resto > 0) texto += this.convertirMenor1000(resto);

      return texto.trim();
    },

    numeroALetras(num) {
      let [entero, decimal] = Number(num)
        .toFixed(2)
        .split(".");
      let textoEntero = this.convertir(parseInt(entero));

      return (
        "SON: " +
        textoEntero +
        " CON " +
        decimal.padStart(2, "0") +
        "/100 SOLES"
      );
    }
  }
};
</script>

<style scoped>
/* @import "../../assets/css/invoice.css"; */
* {
  padding: 0;
  margin: 0;
}
.tablaProductos th {
  border-top: 1px solid #000000;
  border-bottom: 1px solid #000000;
  padding: 0 1px;
  /* border-bottom: 1px solid #000000; */
  /* background: #ffffff !important; */
}
.tablaProductos th >>> td {
  /* border: 1px solid #000000 !important; */
}
.ticket {
  width: 600px; /* o 40mm */
}
@media print {
  * {
    width: 380px; /* o 40mm */
    font-size: 1em;
    /* text-align: center; */
    padding: 5px 5px;
  }
  .tablaProductos {
    /* background: #ffffff !important; */
  }
}
</style>
