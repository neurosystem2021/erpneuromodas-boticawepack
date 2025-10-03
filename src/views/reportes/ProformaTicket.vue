<template>
  <b-row>
    <b-container>
      <img src="/resources/images/logo-ticket.png" style="max-width:100%; width: 40mm" alt="img-logo-ticket-empresa">
      {{ proforma }}
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
      productos: []
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
      console.log(productos);
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
    }
  }
};
</script>

<style scoped>
@import "../../assets/css/invoice.css";
.celda {
  text-align: left;
  border: none !important;
  background: white !important;
}
@media print {
  #printBtn {
    display: none;
  }
}
</style>
