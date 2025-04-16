<template>
  <b-container fluid>
    <b-row>
        <b-col cols="12">
            <b-row>
                <b-col cols="1">
                    Año
                    <input type="number" class="form-control" v-model="anno">
                </b-col>
                <b-col cols="2">
                    Producto
                    <autocomplete
                        ref="autocompleteProducto"
                        :url="APIProductos"
                        :onShouldRenderChild="renderChild"
                        anchor="Producto"
                        label="writer"
                        placeholder="Agregar Producto"
                        :on-select="setProducto"
                        :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                </b-col>
                <b-col cols="1">
                    Stock Inicial
                     <input type="number" class="form-control" v-model="stock">
                </b-col>
                <b-col cols="1">
                    Costo
                     <input type="number" class="form-control" v-model="precio">
                </b-col>
                <b-col cols="2">
                    Mes:
                    <select class="form-control" v-model="Mes">
                        <option value="" selected disabled> -- Seleccione un mes -- </option>
                        <option :value="mes.value" v-for="(mes,index) in mounth" :key="index">
                            {{ mes.text }}
                        </option>
                    </select>
                    <!-- {{ mounth }} -->
                </b-col>
                <b-col cols="3" class="py-3">
                    <b-button @click="obtenerListado()" variant="info">
                      <i class="fa fa-search" aria-hidden="true"></i> Generar
                    </b-button> 
                    
                    <b-button @click="exportPDF()" variant="danger" :disabled="disableEXCEL">
                      <i class="fa fa-file-pdf" aria-hidden="true"></i> Generar PDF
                    </b-button> 

                    <b-button @click="exportEXCEL()" variant="success" :disabled="disableEXCEL">
                      <i class="fa fa-file-excel" aria-hidden="true"></i> Generar EXCEL
                    </b-button> 
                </b-col>
            </b-row>
           
        </b-col>
        <b-col cols="12" class="my-3">
            <table width="100%" class="table table-hover">
                <tbody>
                    <tr v-for="(lst,index) in lstReporte.filter(v=> v.d1!=' ')" :key="index" :class="getClass(lst)"> 
                        <td>{{ lst.d1 }}</td>
                        <td>{{ retornarDecimal(lst.d2) }}</td>
                        <td>{{ retornarDecimal(lst.d3) }}</td>
                        <td>{{ retornarDecimal(lst.d4) }}</td>
                        <td>{{ retornarDecimal(lst.d5) }}</td>
                        <td>{{ retornarDecimal(lst.d6) }}</td>
                        <td>{{ retornarDecimal(lst.d7) }}</td>
                        <td>{{ retornarDecimal(lst.d8) }}</td>
                        <td>{{ retornarDecimal(lst.d9) }}</td>
                        <td>{{ retornarDecimal(lst.d10) }}</td>
                        <td>{{ retornarDecimal(lst.d11) }}</td>
                    </tr>
                </tbody>
            </table>
            <!-- {{ lstReporte }} -->
        </b-col>
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      Tipo: 0,
      anno: "",
      Mes: "",
      mounth: [
        { value: "01", text: "ENERO" },
        { value: "02", text: "FEBREO" },
        { value: "03", text: "MARZO" },
        { value: "04", text: "ABRIL" },
        { value: "05", text: "MAYO" },
        { value: "06", text: "JUNIO" },
        { value: "07", text: "JULIO" },
        { value: "08", text: "AGOSTO" },
        { value: "09", text: "SEPTIEMBRE" },
        { value: "10", text: "OCTUBRE" },
        { value: "11", text: "NOVIEMBRE" },
        { value: "12", text: "DICIEMBRE" }
      ],
      stock: 0,
      precio: 0,
      idAlmacen: 1,
      producto: {},
      lstReporte: []
    };
  },
  mounted() {
    this.anno = this.$moment().format("YYYY");
  },
  methods: {
    renderChild(data) {
      return `
          <b>${data.Producto}</b>
        `;
    },
    setProducto(obj) {
      this.producto = obj;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    async obtenerListado() {
      if (!Object.keys(this.producto).length > 0) {
        this.$toasted.show("Seleccione un producto.");
        return;
      }
      await this.axios(`${this.API}producto/listarstockvalorizado`, {
        params: {
          producto: this.producto.Producto,
          stock: this.stock,
          precio: this.precio,
          anno: this.anno,
          Tipo: this.Tipo
        }
      }).then(response => {
        this.generarListado(response.data);
      });
    },
    generarListado(datos) {
      const excluirValores = [
        "KARDEX VALORIZADO",
        "PRODUCTO",
        "AÑO",
        "DESDE",
        "HASTA"
      ];

      const datosFiltrados = datos.filter(item => {
        const valor = (item.d1 || "").trim();
        return valor !== "" && !excluirValores.includes(valor);
      });

      let header = datosFiltrados[0];

      datosFiltrados.shift();

      let datosFinales = datosFiltrados;
      console.log("this.Mes", this.Mes);
      if (this.Mes) {
        datosFinales = [];
        datosFiltrados.forEach(element => {
          if (this.$moment(element.d1).format("MM") == this.Mes) {
            datosFinales.push(element);
          }
        });
      }
      datosFinales.unshift(header);
      this.lstReporte = datosFinales;
    },
    retornarDecimal(valor) {
      if (typeof valor === "number") {
        return parseFloat(valor.toFixed(2));
      }

      if (typeof valor === "string" && /^-?\d+(\.\d+)?$/.test(valor.trim())) {
        const numero = parseFloat(valor);
        return parseFloat(numero.toFixed(2));
      }

      return valor;
    },
    getClass(item) {
      let clasName = "";
      let value = item.d1.trim();
      switch (value) {
        case "KARDEX VALORIZADO":
          clasName = "clsTitle";
          break;
        case "PRODUCTO":
          clasName = "clsProducto";
          break;
        case "AÑO":
          clasName = "clsAnno";
          break;
        case "DESDE":
          console.log(item.d1);
          clasName = "clsDesde";
          break;
        case "HASTA":
          console.log(item.d1);
          clasName = "clsHasta";
          break;
        case "FECHA":
          clasName = "clsFecha";
          break;

        default:
          clasName = "";
          break;
      }
      return clasName;
    },
    async exportEXCEL() {
      let data = {
        data: this.lstReporte
      };
      await this.axios
        .post(`${this.API}producto/listarstockvalorizado/excel`, data)
        .then(res => {
          let url = res.data;
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          window.open(url, "_blank");
        });
    },
    async exportPDF() {
      await this.axios(`${this.API}reporte/kardexvalorizadopdf`, {
        params: {
          producto: this.producto.Producto,
          stock: this.stock,
          precio: this.precio,
          anno: this.anno,
          Tipo: this.Tipo
        }
      }).then(res => {
        if (res.data) {
          let url = res.data.url;
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          console.log(url);
          window.open(url, "_blank");
        }
      });
    }
  },

  computed: {
    APIProductos() {
      return this.API + "producto/inv";
    },
    disableEXCEL() {
      if (this.lstReporte.length == 0) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style scoped>
.clsTitle {
  background: #5c6bc0;
  font-weight: bold;
  color: white;
}
.clsProducto {
  background: #7986cb;
}
.clsAnno {
  background: #9fa8da;
}
.clsDesde {
  background: #c5cae9;
}
.clsHasta {
  background: #c5cae9;
}
.clsFecha {
  background: #5c6bc0;
  font-weight: bold;
  color: white;
}
</style>
